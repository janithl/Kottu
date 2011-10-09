<?php
error_reporting(E_ERROR); 

require('../DBConnection.php');
require('Stats.php');

/******************************************************************************************

Kottu 8

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

******************************************************************************************

This is the SpiceCalc class, originally created on 16/08/11 (as BuzzCalc.php). The code 
was completely refactored for Kottu 8, but the basic idea remains: Take a bunch of posts 
and determines how trending (or spicy) they are. We consider the number of Tweets, FB
likes/shares and clicks that each post has got.

Version history:
0.1	16/08/11	Janith		Started writing buzzcalc
0.2	21/08/11	Janith		Combined everything here (calling tweetget + fbget)
------------------------------------------------------------------------------------------

1.0	08/10/11	Janith		Major Refactoring (and renaming)

******************************************************************************************/

if(isset($_GET['fancyauthentication']) && sha1($_GET['fancyauthentication']) === 'someauthentication')
{
	$DBConn = new DBConnection();

	// Weights of various factors (feel free to edit) - adds up to 1
	$tweetweight = 0.35;
	$fbookweight = 0.20;
	$clickweight = 0.45;

	echo "spicecalc: began run\t\ttw\tfb\tcl\tsp\n";	// for debugging
	$starting_time = microtime();

	// Get 30 of the most recent posts. We order by api timestamp because we update that when we
	// mess with them, ensuring a queue which continously feeds us the least recently polled posts
	$resultset = $DBConn->query("SELECT postID, link FROM posts WHERE ".
		"serverTimestamp > (unix_timestamp(now()) - 86400) ORDER BY api_ts ASC LIMIT 20", array());

	if($resultset)
	{
		// an empty array to hold the stats
		$results = array();

		// a counter, for debugging and statistics
		$counter = 0;

		//We get the maximum counts for all the metrics, for calculation purposes
		$maxarr = Stats::getMaximums($DBConn);

		//if($maxarr == false) { die("spicecalc could not get maximums and died"); }

		while($array = $resultset->fetch())
		{
			$postid = $array[0];
			$url 	= $array[1];

			$tweets = Stats::getTweetCount($url);
			$fbooks = Stats::getFBCount($url);
			$clicks = Stats::getClicks($postid, $DBConn);

			$results[$postid] = array($tweets, $fbooks, $clicks);

			// we check if any of the new tweet/fb counts are bigger than the max
			// if they are so, we have to update the "max".
			if ($maxarr['maxtweets'] < $tweets) { $maxarr['maxtweets'] = $tweets; } 
			if ($maxarr['maxfbooks'] < $fbooks) { $maxarr['maxfbooks'] = $fbooks; }

			$counter++;
		}

		$now = time();

		// now we do the calculations and write the stats back to db, post by post
		foreach($results as $key => $value)
		{
			$tweetbuzz = unskew($value[0] / ($maxarr['maxtweets'] + 1));
			$fbookbuzz = unskew($value[1] / ($maxarr['maxfbooks'] + 1));
			$clickbuzz = unskew($value[2] / ($maxarr['totalclicks'] + 1));

			// we do the calculations...
			$spice = ($tweetbuzz * $tweetweight) + ($fbbuzz * $fbookweight) + ($clickbuzz * $clickweight);

			// ...and put away our toys
			$DBConn->query("UPDATE posts SET postBuzz = :spice,tweetCount = :tweets,fbCount = :fb,api_ts = :ts WHERE postID = :id",
					array(':spice'=>$spice,':tweets'=>$value[0],':fb'=>$value[1],':ts'=>$now,':id'=>$key));

			echo "spicecalc: $key\t\t{$value[0]}\t{$value[1]}\t{$value[2]}\t".(int)(100 * $spice)."%\n";	// for debugging
		}
		
		// debugging message
		printf("spicecalc: ended run in %2.4f s. spice for %d posts calculated\n", (microtime() - $starting_time), $counter);
	}
}
else
{
	echo "Ummm. Something went wrong";
}

function unskew($x)		// make sure values are between 0 and 1
{
	return ($x > 1) ? 1 : (($x < 0) ? 0 : $x);
} 

?>
