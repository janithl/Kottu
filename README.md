Kottu, the Sri Lankan Blog Aggregator
=====================================

Coded by Janith Leanage and Indi Samarajiva
Project started on 09/Aug/2011, with the following primary aims:

1. Rebuild Kottu as a 'social feed reader'
	* Aggregates feeds
	* Allows visitors to like/tweet/plus whatever them
	* Calculates and displays the 'best posts'
2. Open source the code
3. Have it spit out content for print magazines (a tech one to start)

First public release was on 03/Sep/2011
This (markdown) readme file was created on 27/Sep/2011 

Licence
-------

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>. 

(see [license.txt](https://github.com/janithl/Kottu/blob/master/license.txt) for full AGPL licence)

File Tree
---------

	.
	|-- .htacess				// Server htacess config
	|-- DBConnection.php		// database connection
	|-- FacebookSDK				// Facebook SDK, for FB integration
	|   |-- base_facebook.php
	|   `-- facebook.php
	|-- error.php				// 404 Error page
	|-- feed
	|   `-- index.php			// Latest/Popular posts RSS 2.0 Feed
	|-- go.php					// Redirector page that takes click count
	|-- images					// Images folder
	|   |-- chili.png
	|   |-- fb.png
	|   |-- grad.png
	|   |-- kottu.ico
	|   |-- logo.png
	|   |-- search.png
	|   |-- updog.png
	|   `-- tw.png
	|-- index.php				// Home page
	|-- kottu.sql				// MySQL database needed for Kottu to function
	|-- license.txt				// License file
	|-- p
	|   |-- about.php			// About Kottu
	|   `-- blogroll.php		// Kottu blogroll - displays all blogs in DB
	|-- README
	|-- search.php				// Search page
	|-- SimplePie				// SimplePie feedreader library, reads our member blog RSS feeds
	|   |-- LICENSE.txt
	|   `-- simplepie.inc
	|-- style.css				// Site stylesheet
	`-- utils					// MOST IMPORTANT FOLDER EVER!!! ;P
	    |-- BuzzCalc.php		// Calculates "buzz" or "Spice" for a post up to 24 hours after first reading it
	    |-- cache				// Cache folder for storing blog posts - used by SimplePie
	    |-- FBGet.php			// Get Facebook stats
	    |-- FeedGet.php			// Reads feeds of member blogs using SimplePie
	    `-- TweetGet.php		// Get Twitter stats

Walkthrough
-----------

A potential failure will follow where I try to explain the code:

1. In FeedGet.php (cron job'd to run every 5 minutes) we get 50 blogs that were least recently polled (`ORDER BY access_ts ASC LIMIT 50` where access_ts is set to the current UNIX timestamp everytime we poll that blog's feed)

2. Using SimplePie, we read those feeds and enter any new posts to the database, Posts table.

3. Using BuzzCalc.php (cron job'd to run every 15 minutes), we get the posts that were posted in the last 24 hours. We run TweetGet.php and FBGet.php from inside BuzzCalc. 

4. TweetGet.php : We have something called APIcount_t in the database, which keeps track of how many times a post has been polled using Twitter. This means a post is only polled 6 times max. (This is necessary because Twitter has strict API call limitations)

		$pollingtimes = array('0'=>1,'1'=>3,'2'=>6,'3'=>9,'4'=>12,'5'=>18); // times at which we poll: api count => hour

	The code above shows the correlation between the API count and the hours after you post that we poll. A post with API count 0 needs to be at least an hour old before we poll, a post with API count 1 needs to be 3 hours old before we poll etc. All of this means we make maximum use of limited API calls and polls your post while giving it time to breathe and gain shares.

		query("SELECT postID, link FROM posts WHERE apiCount_t = :api AND serverTimestamp < :time ORDER BY serverTimestamp ASC LIMIT 5", array(':api'=>$api,':time'=>$timestamp));

	The `LIMIT 5` ensures that the maximum number of requests that can go out from any one run of TweetGet is 30. Since TweetGet runs four times an hour, this keeps us inside 120 API calls an hour, which I believe puts us in the clear.

	Getting tweets themselves is done by a simple API call, which returns a json string, from which we extract the count:

		$json = @file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.$link);
		$twitter = json_decode($json);
		$tweetcount = $twitter->{'count'};

5. FBGet.php : The process is very much similar to what happens in TweetGet, the only difference being that use the Facebook PHP SDK to get counts from the Facebook API.
	
		$fql = 'SELECT share_count, like_count FROM link_stat WHERE url="'.$link.'"';
		$result = $rootfb->api(array('method' => 'fql.query','query' => $fql,));
		$fbcount = $result['0']['share_count'] + $result['0']['like_count'];

	As you can see in the last line, the number of Facebook "shares" and "likes" the post gets are added together.

6. Getting back to BuzzCalc.php (which called both FBCount and TweetCount), this is the formula used to calculate actual post "spice" (the number of chilies your post gets on Kottu - this is internally handled as a float)

	First we declare the weight given to each factor in our calculation (weights should ideally add up to 1)

		$tweetweight = 0.35;
		$fbookweight = 0.20;
		$clickweight = 0.45;
		
		$fizzlefactor = 0.10;	// fizzling is reduction in buzz as time goes on

	More weight is given to Twitter because Facebook stats can be skewed by using FB ads (theoretically)

	Getting the number of clicks off Kottu in the last 24 hours, and the maximum number of tweets and Facebook likes + shares a post got in the last 24 hours:

		$resultset2 = $DBConnection->query("SELECT COUNT(*) FROM clicks WHERE timestamp > :days", array(':days'=>$period));
		$resultset3 = $DBConnection->query("SELECT MAX(tweetCount) FROM posts WHERE serverTimestamp > :days", array(':days'=>$period));
		$resultset4 = $DBConnection->query("SELECT MAX(fbCount) FROM posts WHERE serverTimestamp > :days", array(':days'=>$period));

	Then we calculate the "spice". Tweetbuzz is the number of tweets a certain post has got divided by the maximum number of tweets a post got in the last 24 hours. Fbbuzz is the same. Clickbuzz is number of clicks your post got divided by the total number of clicks in the last 24 hours (don't ask me about this seemingly inconsistant stat, it must've made sense when I made it ;) LOL) 

		$tweetbuzz = unskew($tweets / ($max_tweets + 1));
		$fbbuzz = unskew($fb / ($max_fb + 1));
		$clickbuzz = unskew($clicks / ($total_clicks + 1));
		
		$buzz = ($tweetbuzz * $tweetweight) + ($fbbuzz * $fbookweight) + ($clickbuzz * $clickweight);

	In case you were wondering, we get clicks for a post by using go.php, through which we redirect links that appear on Kottu. For example, if a post had URL `http://myblog.com/post1.html`, we would link to it with `http://kottu.org/go.php?url=http://myblog.com/post1.html`.

7. go.php attempts to avoid gaming the system by recording your IP address every time you click on a link. Your IP address will not register a "click" for that link for the next 12 hours. We check in the database if there are clicks from your IP that happened in the last 12 hours. If there aren't any, we register your click for the post under your IP.

		$resultset = $dbh->query("SELECT timestamp FROM clicks WHERE timestamp > (unix_timestamp() - 43200) AND ip = :ip AND url = :url ORDER BY timestamp DESC", array(':ip' => $ip, ':url' => $url)); 
		// validity of one ip is 12 hours, 43200 seconds)

		if($resultset && mysql_num_rows($resultset) == 0)
		{
			insert_click($ip, $url, $timestamp, $dbh);
		}

8. Finally, index.php, which is the simplest of the lot, really. We look at the query strings being passed and generate the page accordingly.

		$l = isset($_GET['l']) ? $_GET['l'] : '';	//language
		$t = isset($_GET['t']) ? $_GET['t'] : '';	//time period - popular / latest
		$p = isset($_GET['p']) ? $_GET['p'] : 1;	//pagination
		$c = isset($_GET['c']) ? $_GET['c'] : '%';	//categories
		
		$l = (strlen($l) > 3) ? substr($l, 0, 2) : $l;	// convert english into en, tamil into ta etc.
		$start_time = microtime();			// timing
		
		output($l, $t, $p, $c);

	The tags are handled in quite a quirky way. Please do let me know if this method is unsafe, or if you know of a better, parameterized method:

		$catarray = array( 
		'tech' 		=> array('science','linux','windows','security','mobile','software','phones','android','electronic','physics','mathematics','maths','web,'),
		'travel'	=> array('food','hotel','hike','hiking','beach'),
		'nature'	=> array('environment','conservation','animal','wildlife','pollution','forest'),
		'sports'	=> array('cricket','rugby','football','soccer','volleyball','athlet','tennis'),
		'news'		=> array('breaking','security','election'),
		'personal'	=> array('life,','love','romance','exam','emotion','thought','story','stories','social','boredom','rant','ramblings'),
		'entertainment'	=> array('art,','music','song','album','movie','film','cinema',' tv ','video','literature','literary','magazine'),
		'poetry'	=> array('poem'),
		'business'	=> array('industry','bank','economy','economics','development'),
		'politics'	=> array('election','peace','war,','conflict','security','economy','development','youth'),
		'photo'		=> array('image'),
		'faith'		=> array('religion','belief','buddhis','christian','hindu','islam','muslim','god,','atheis'),
		'education'	=> array('exam','university','school','teach'),
		'other'		=> array('uncategorized','random','general'));

		$cats = preg_replace("/[^a-z]/", "", $cats);	// sanitizing

		if(is_array($catarray[$cats]))
		{
			$catext = "p.tags LIKE '%$cats%'" ;

			foreach($catarray[$cats] as $key => $value)
			{
				$catext .= " OR p.tags LIKE '%$value%'";
			}
		}
		else
		{
			$catext = '';
		}

	And *then* we add the entire $catext string into the select query. `O_O`

	Notice that some tags seem to be hilariously misspelled (my personal favourite is *buddhis*, which is how *I* used to pronounce the word). This is a trick to get more matches, i.e. `%buddhis%` would match both `buddhist` and `buddhism` etc. 

In Conclusion
-------------

This was my first proper documentation file, and there are probably plenty of gaps, and I've probably left you with plenty of questions too. Do feel free to contact me if you have any issues (umm, not *those* issues! LOL) [on Twitter](http://twitter.com/chav_) or [on Google+](https://plus.google.com/116783522121096138585) :) 

