<?php
error_reporting(E_ERROR); 

require('../SimplePie/simplepie.inc');
require('../DBConnection.php');

/******************************************************************************************
Kottu 7.8 

Feedget - gets RSS feeds and puts posts into our database

Version history:
0.1	15/08/11	Janith		Started writing feedget
0.2	17/08/11	Janith		made improvements - see old post oldification 
0.3	18/08/11	Janith		Badly implemented a language filter
0.4	20/08/11	Janith		Added debugging/reporting feature
0.5	22/08/11	Janith		Fixed a major issue in that it reads the same blogs
					over and over - added this access_ts thing to blogs
0.6	06/09/11	Janith		Added tags, messed with the lang filter, stopped stripping <img>
0.7	16/09/11	Janith		Made a small fix for the wordpress.com thumbnail issue

******************************************************************************************/

if(isset($_GET['fancyauthentication']) && sha1($_GET['fancyauthentication']) === 'someauthentication')
{
	$DBConnection = new DBConnection();

	$resultset = $DBConnection->query("SELECT bid, blogRSS FROM blogs ORDER BY access_ts ASC LIMIT 50", array());	// here we get 50 blogs we last accessed according to access time

	if($resultset) { feedgetter($resultset, $DBConnection); }
}
else
{
	echo "Ummm. Something went wrong";
}

function feedgetter($resultset, $DBConnection)
{
	// for debugging purposes //

	echo "<pre>\n[feedget ]\tbegan run at ".date('j F Y h:i:s A', time())."\n";

	$counter_all = 0;
	$counter_ins = 0;

	while($array = $resultset->fetch())
	{
		// thank god for simplepie

		$feed = new SimplePie();
		$feed->set_feed_url($array[1]);
 
		$feed->init();
		$feed->handle_content_type();

		foreach ($feed->get_items() as $item)
		{
			$blogID = $array[0];
			$link = $item->get_permalink();
			$title = $item->get_title();

			if(strlen($title) < 2) { $title = "Untitled Post"; }			

			$post_cont = strip_tags($item->get_content(), '<img>');	// include img tags in desc, for thumbnails

			$post_cont = preg_replace("/(&nbsp;|\s|&nbsp;\s)+/", ' ', $post_cont);	// removing those stupid multiple spaces

			if(strlen($post_cont) > 400)	// summary generator
			{
				$paragraph = explode(' ', $post_cont);
				$paragraph = array_slice($paragraph, 0, 60);
				$post_cont = implode(' ', $paragraph);
				$post_cont .= " ...";
			}

			$post_ts = strtotime($item->get_date());
			$server_ts = time();

			// tags!

			$tags = '';
			$count = 0;

			foreach ($item->get_categories() as $category)
			{
				$t = trim(strtolower($category->get_label()));

				if($count < 3) { $tags .= "$t,"; }
				$count++;	
			}

			// old post oldification

			if($server_ts > $post_ts)
			{
				$server_ts = $post_ts;
			}

			// below : badly implemented language filter

			if(preg_match('/[\x{0D80}-\x{0DFF}]{3,5}/u', $post_cont.$title))
			{
				$lang = 'si';
			}
			else if(preg_match('/[\x{0B80}-\x{0BFF}]{3,5}/u', $post_cont.$title))
			{
				$lang = 'ta';
			}
			else
			{
				$lang = 'en';
			}

			$counter_all++;

			$resset = $DBConnection->query("INSERT INTO posts(postID, blogID, link, title, postContent, serverTimestamp, postTimestamp, language, tags) VALUES (NULL, :bid, :link, :title, :content, :serv, :post, :lang, :tags)", 
				array(':bid'=>$blogID, ':link'=>$link, ':title'=>$title, ':content'=>$post_cont, ':serv'=>$server_ts, ':post'=>$post_ts, ':lang'=>$lang, ':tags'=>$tags));

			if($resset)
			{
				echo "[feedget ]\t\tadded post $title ($post_ts)\n";
				$counter_ins++;
			}
		}

		$ts_s = time();
		$DBConnection->query("UPDATE blogs SET access_ts = :time WHERE bid = :bid", array(':time'=>$ts_s, ':bid'=>$array[0]));

		$feed->__destruct();
		unset($feed);
	}

	echo "[feedget ]\tended run at ".date('j F Y h:i:s A', time()).". $counter_all post(s) were hit and $counter_ins post(s) inserted\n</pre>\n";
}

?>
