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
	0.2	27/09/11	Janith		Simplified select query
	0.3	06/10/11	Janith		Fixed design flaw- now using postIDs instead
						of URLs to track clicks. Safer and better.

******************************************************************************************/

$dbh = new DBConnection();

if(isset($_GET['url']))
{
	$ip = $_SERVER['REMOTE_ADDR'];
	$url = $_GET['url'];

	$postid = isset($_GET['pid']) ? $_GET['pid'] : 0;

	$timestamp = time();

	$resultset = $dbh->query("SELECT timestamp FROM clicks WHERE timestamp > (unix_timestamp(now()) - 43200) "
		. "AND ip = :ip AND pid = :pid ORDER BY timestamp DESC", array(':ip' => $ip, ':pid' => $postid)); 
	// validity of one ip is 12 hours, 43200 seconds)

	if($resultset && $resultset->fetch() == false)
	{
		insert_click($ip, $postid, $timestamp, $dbh);
	}

	header("location: $url");
}
else
{
	header("location: http://kottu.org");
}
			
function insert_click($ipadr, $pid, $ts, $dbh)
{

	$resultset = $dbh->query("INSERT INTO clicks(ip, pid, timestamp) VALUES (:ip, :pid, :timestamp)", 
					array(':ip' => $ipadr, ':pid' => $pid, ':timestamp' => $ts));
}

?>
