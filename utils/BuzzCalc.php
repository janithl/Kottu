<?php
require('../DBConnection.php');
require('TweetGet.php');
require('FBGet.php');

error_reporting(0);

/******************************************************************************************
	Kottu 7.8

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



	buzzcalc - calculates the post buzz and puts posts into our database

	Version history:
	0.1	16/08/11	Janith		Started writing buzzcalc
	0.2	21/08/11	Janith		Combined everything here

******************************************************************************************/

$DBConnection = new DBConnection();

// Weights of various factors (feel free to edit) - adds up to 1

$tweetweight = 0.35;
$fbookweight = 0.20;
$clickweight = 0.45;

$fizzlefactor = 0.10;	// fizzling is reduction in buzz as time goes on

// we calculate buzz for 24 hours after you post

$period = time() - (24 * 60 * 60);
$debug = "[buzzcalc]\tbegan run at ".date('j F Y h:i:s A', time())."\n";	// for debugging

// get the feeds and twitter/fb stats

$pollingtimes = array('0'=>1,'1'=>3,'2'=>6,'3'=>9,'4'=>12,'5'=>18); // times at which we poll: api count => hour

//$feeds = new FeedGet();
//$debug .= $feeds->GetFeeds($DBConnection);

$tw = new TweetGet();
$debug .= $tw->GetTweets($pollingtimes, $DBConnection);

$fb = new FBGet();
$debug .= $fb->GetFB($pollingtimes, $DBConnection);

// sql queries

$resultset1 = $DBConnection->query("SELECT postID, serverTimestamp, tweetCount, fbCount, link FROM posts WHERE serverTimestamp > :days", array(':days'=>$period));

$resultset2 = $DBConnection->query("SELECT COUNT(*) FROM clicks WHERE timestamp > :days", array(':days'=>$period));

$resultset3 = $DBConnection->query("SELECT MAX(tweetCount) FROM posts WHERE serverTimestamp > :days", array(':days'=>$period));

$resultset4 = $DBConnection->query("SELECT MAX(fbCount) FROM posts WHERE serverTimestamp > :days", array(':days'=>$period));

if($resultset1 && $resultset2 && $resultset3 && $resultset4)
{
	// we get max of various metrics

	$array = $resultset2->fetch();
	$total_clicks = $array[0];

	$array = $resultset3->fetch();
	$max_tweets = $array[0];

	$array = $resultset4->fetch();
	$max_fb = $array[0];
	
	while($array = $resultset1->fetch())
	{
		$postid = $array[0];
		$servert = $array[1];
		$tweets = $array[2];
		$fb = $array[3];
		$posturl = $array[4];

		$resultset5 = $DBConnection->query("SELECT COUNT(ip) FROM clicks WHERE url= :url GROUP BY url", array(':url'=>$posturl)); // get click count

		if($resultset5)
		{
			$array = $resultset5->fetch();
			$clicks = $array[0];
		}
		else
		{
			$clicks = 0;
		}

		//  and add 1 to avoid any divide by zero errors and unskew

		$tweetbuzz = unskew($tweets / ($max_tweets + 1));
		$fbbuzz = unskew($fb / ($max_fb + 1));
		$clickbuzz = unskew($clicks / ($total_clicks + 1));

		$buzz = ($tweetbuzz * $tweetweight) + ($fbbuzz * $fbookweight) + ($clickbuzz * $clickweight); 

		$buzz -= $fizzlefactor * (time() - $servert) / $period; // old timers fizzle out

		$debug .= "[buzzcalc]\t\tbuzz for $posturl calculated to be $buzz\n";

		$DBConnection->query("UPDATE posts SET postBuzz = :buzz WHERE postID = :id", array(':buzz'=>$buzz,':id'=>$postid)); // update buzz into db
	}

	$debug .= "[buzzcalc]\tended run at ".date('j F Y h:i:s A', time())."\n\n";

	echo "<pre>\n".$debug."\n</pre>\n";

	$des = "./stats.html";		// reporting
	$file = fopen($des, 'a');
	fwrite($file, $debug);
	fclose($file);
}

function unskew($x)		// make sure values are between 0 and 1
{
	$x = $x > 1 ? 1 : $x;
	return $x < 0 ? 0 : $x;
} 
?>
	
