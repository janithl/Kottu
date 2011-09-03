<?php
require('../FacebookSDK/facebook.php');

/******************************************************************************************
Kottu 7.8 

Fbget - gets fb likes and shares and puts posts into our database

Version history:
0.1	15/08/11	Janith		Started writing tweetget
0.2	16/08/11	Janith		Converted a copy of tweetget into fbget
0.3	20/08/11	Janith		Cleanup and 'logifying' ;)

******************************************************************************************/

class FBGet
{
	public function __construct() 
	{
		
	}

	public function GetFB($pollingtimes, $DBConnection)
	{

		// Create FB Application instance
		$rootfb = new Facebook(array(
			'appId'  => 'create an fb app and enter app id here',
			'secret' => 'app secret code',));

		// for debugging purposes //

		$debug = "[ fb get ]\t\tbegan run at ".date('j F Y h:i:s A', time())."\n";

		foreach($pollingtimes as $api=>$time)
		{
			$count = 0;						// for debugging purposes

			$timestamp = time() - ($time * 60 * 60); 		// convert to format relative to timestamp

			$resultset = $DBConnection->query("SELECT postID, link FROM posts WHERE apiCount_f = :api AND serverTimestamp < :time ORDER BY serverTimestamp ASC LIMIT 5",
							array(':api'=>$api,':time'=>$timestamp));

			if($resultset)
			{
				$count = $this->update_fbcount($resultset, $DBConnection, $rootfb);
			}

			$debug .= "[ fb get ]\t\t\tcounted fb stats for $count posts older than $time hours\n";
		}

		$debug .= "[ fb get ]\t\tended run\n";

		return $debug;
	}

	private function update_fbcount($resultset, $dbh, $rootfb)
	{
		$count = 0;

		while($array = $resultset->fetch())
		{
			$postid = $array[0];
			$link = $array[1];

			// get fb stats //

			$fql = 'SELECT share_count, like_count FROM link_stat WHERE url="'.$link.'"';
			$result = $rootfb->api(array('method' => 'fql.query','query' => $fql,));
			$fbcount = $result['0']['share_count'] + $result['0']['like_count'];

			// set fb count in db

			$dbh->query("UPDATE posts SET fbCount = :count, apiCount_f = (apiCount_f + 1) WHERE postID = :pid",
								array(':count'=>$fbcount,':pid'=>$postid));

			// debugging counter

			$count++;
		}

		return $count;
	}
}

?>	
