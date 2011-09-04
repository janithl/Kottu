<?php

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



Tweetget - gets tweets and puts posts into our database

Version history:
0.1	15/08/11	Janith		Started writing tweetget
0.2	20/08/11	Janith		Code cleanup and ummm... object orientation ;)

******************************************************************************************/

class TweetGet
{
	public function __construct() 
	{
		
	}

	public function GetTweets($pollingtimes, $DBConnection)
	{

		// for debugging purposes //

		$debug = "[tweetget]\t\tbegan run at ".date('j F Y h:i:s A', time())."\n";

		foreach($pollingtimes as $api=>$time)
		{
			$count = 0;						// for debugging purposes

			$timestamp = time() - ($time * 60 * 60); 		// convert to format relative to timestamp

			$resultset = $DBConnection->query("SELECT postID, link FROM posts WHERE apiCount_t = :api AND serverTimestamp < :time ORDER BY serverTimestamp ASC LIMIT 5",
							array(':api'=>$api,':time'=>$timestamp));

			if($resultset)
			{
				$count = $this->update_tweetcount($resultset, $DBConnection);
			}

			$debug .= "[tweetget]\t\t\tcounted tweets for $count posts older than $time hours\n";
		}

		$debug .= "[tweetget]\t\tended run\n";

		return $debug;

	}

	private function update_tweetcount($resultset, $dbh)
	{
		$count = 0;

		while($array = $resultset->fetch())
		{
			$postid = $array[0];
			$link = $array[1];

			// get tweetcount //

			$json = @file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url='.$link);
			$twitter = json_decode($json);
			$tweetcount = $twitter->{'count'};

			// set tweetcount in db //

			$dbh->query("UPDATE posts SET tweetCount = :count, apiCount_t = (apiCount_t + 1) WHERE postID = :pid",
						array(':count'=>$tweetcount,':pid'=>$postid));

			// debugging counter

			$count++;
		}

		return $count;
	}
}
?>
	
