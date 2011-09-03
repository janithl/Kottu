<?php
require('DBConnection.php');

/******************************************************************************************

	Kottu 7.8 

	Go.php -> acts as an intermediate page and takes click counts

	Version history:
	0.1	13/08/11	Janith		Started go.php

******************************************************************************************/

$dbh = new DBConnection();

if(isset($_GET['url']))
{
	$ip = $_SERVER['REMOTE_ADDR'];
	$url = $_GET['url'];
	$timestamp = time();

	$resultset = $dbh->query("SELECT timestamp FROM clicks WHERE ip = :ip AND url = :url ORDER BY timestamp DESC", array(':ip' => $ip, ':url' => $url));

	if($resultset)
	{
		$row = $resultset->fetch();
		if($row[0] < ($timestamp - (12 * 60 * 60))) // validity of one ip is 12 hours
		{
			insert_click($ip, $url, $timestamp, $dbh);	
		}
	}
	else
	{
		insert_click($ip, $url, $timestamp, $dbh);
	}

	header("location: $url");
}
else
{
	header("location: http://kottu.org");
}
			
function insert_click($ipadr, $url, $ts, $dbh)
{

	$resultset = $dbh->query("INSERT INTO clicks(ip, url, timestamp) VALUES (:ip, :url, :timestamp)", array(':ip' => $ipadr, ':url' => $url, ':timestamp' => $ts));
}

?>
