<?php
require('../FacebookSDK/facebook.php');

/*****************************************************************************************

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

This is the new Stats class. It's where we get social and click information on a URL,
as well as maximums of those stats for calculations. Most of this code is from 
BuzzCalc.php, TweetGet.php and FBGet.php in Kottu 7.8

Version history:
0.1	08/10/11	Janith		Began. More like refactoring existing code. :)

******************************************************************************************/

class Stats
{
	// Facebook app instance var
	private static $fbapp = null;
	
	// Getting tweetcount :
	// pretty straightforward, we send the url to the Twitter API, which
	// replies with a json, which we decode.
	public static function getTweetCount($url)
	{
		$tweetcount = 0;

		$json = @file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.$url);
		if($json)
		{
			$twitter = json_decode($json);
			$tweetcount = $twitter->{'count'};
		}

		return $tweetcount;
	}

	// Initializing a Facebook app instance
	public static function initFB()
	{
		self::$fbapp = new Facebook(array(
			'appId'  => /*app id*/,
			'secret' => /*secret key*/,));
	}

	// Getting Facebook like/share count :
	// first we check if an fb app instance exists, if not we call the above function
	// and create one. then we use FQL to get the fb count
	public static function getFBCount($url)
	{
		if(self::$fbapp == null)
		{
			self::initFB();
		}

		$fbcount = 0;

		try 
		{
			$fql = 'SELECT share_count, like_count FROM link_stat WHERE url="'.$url.'"';
			$result = self::$fbapp->api(array('method' => 'fql.query','query' => $fql,));
		}
		catch (FacebookApiException $e) 
		{
			echo $e;
			$result = null;
		}

		if($result != null)
		{
			$fbcount = $result['0']['share_count'] + $result['0']['like_count'];
		}

		return $fbcount;
	}

	// Getting Click count :
	// This is simple, we just check our database for clicks going to that post id (NOT URL!)
	public static function getClicks($pid, $dbh)
	{
		$clicks = 0;

		$resultset = $dbh->query("SELECT COUNT(ip) FROM clicks WHERE pid= :pid", array(':pid'=>$pid));

		if($resultset)
		{
			$array = $resultset->fetch();
			$clicks = $array[0];
		}

		return $clicks;
	}

	// Getting maximums ;
	// This is done for the calculations, in the start of BuzzCalc, and done very 
	// uglily. Let's make it neat.
	public static function getMaximums($dbh)
	{
		$results = array();

		$rs1 = $dbh->query("SELECT COUNT(*) FROM clicks WHERE timestamp > (unix_timestamp(now()) - 86400)", array());
		$rs2 = $dbh->query("SELECT MAX(tweetCount), MAX(fbCount) FROM posts ".
						"WHERE serverTimestamp > (unix_timestamp(now()) - 86400)", array());

		if($rs1 && $rs2)
		{
			$arr1 = $rs1->fetch();
			$arr2 = $rs2->fetch();

			$results['totalclicks']	= $arr1[0];
			$results['maxtweets']	= $arr2[0];
			$results['maxfbooks']	= $arr2[1];
		}
		else
		{
			$results = false;
		}

		return $results;
	}
}
