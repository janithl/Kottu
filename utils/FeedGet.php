<?php
error_reporting(E_ERROR | E_PARSE);

require('../SimplePie/simplepie.inc');
require('../DBConnection.php');
include('./Posts.php');

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

FeedGet - gets posts from RSS feeds and puts them into our database

For Kottu 8, moved much of the codebase to Posts.php

Version history:
0.1	15/08/11	Janith		Started writing feedget
0.2	17/08/11	Janith		made improvements - see old post oldification 
0.3	18/08/11	Janith		Badly implemented a language filter
0.4	20/08/11	Janith		Added debugging/reporting feature
0.5	22/08/11	Janith		Fixed a major issue in that it reads the same blogs
					over and over - added this access_ts thing to blogs
0.6	06/09/11	Janith		Added tags, messed with the lang filter, stopped stripping <img>
0.7	16/09/11	Janith		Made a small fix for the wordpress.com thumbnail issue
------------------------------------------------------------------------------------------

1.0	09/10/11	Janith		New FeedGet.php - refactoring and stuff

******************************************************************************************/

if(isset($_GET['fancyauthentication']) && sha1($_GET['fancyauthentication']) === 'someauthentication')
{
	$DBConnection = new DBConnection();

	// here we get 50 blogs we last accessed according to access time
	$resultset = $DBConnection->query("SELECT bid, blogRSS FROM blogs ORDER BY access_ts ASC LIMIT 50", array());

	if($resultset)	
	{
		// for debugging
		echo "feedget began run\n";
		$counter = 0;
		$now = time();

		while($array = $resultset->fetch())
		{
			$blogID = $array[0];

			// update blog access timestamp
			$DBConnection->query("UPDATE blogs SET access_ts = :time WHERE bid = :bid", 
							array(':time'=>$now,':bid'=>$blogID));

			// thank god for simplepie

			$feed = new SimplePie();
			$feed->set_feed_url($array[1]);
			$feed->init();
			$feed->handle_content_type();

			foreach ($feed->get_items() as $item)
			{
				// get post url and check if already in database
				$link = $item->get_permalink();
				if(postNotInDB($link, $DBConnection))
				{
					// if not, get all the post info
					$title = $item->get_title();
					$content = $item->get_content();
					$ts = strtotime($item->get_date());

					// create a new post object and assign the values to it
					$post = new Posts();
					$post->setDetails($blogID, $title, $link, $content, $ts);

					// add tags
					foreach ($item->get_categories() as $category)
					{
						$post->addTag($category->get_label());
					}

					// commit post to database
					$post->commit($DBConnection);

					// and delete the object
					unset($post);

					$counter++;
				}
			}

			$feed->__destruct();
			unset($feed);
		}

		// for debugging
		printf("feedget: ended run in %2.4f s. %d posts added to database\n", (microtime() - $starting_time), $counter);
	}
}

// used to check if a post is already in the database
function postNotInDB($url, $dbh)
{
	$resultset = $dbh->query("SELECT * FROM posts WHERE link LIKE :url", array('url'=>$url));
	if($resultset && $resultset->fetch() == false)
	{
		return true;
	}
	else
	{
		return false;
	}
}

?>
