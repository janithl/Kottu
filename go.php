<?php
require('DBConnection.php');

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
