<?php

require('DBConnection.php');
include('./utils/simple_html_dom.php');

/******************************************************************************************
	Kottu 7.8 

	index - just a random interface for now

	Version history:
	0.1	16/08/11	Janith		Started writing index
	0.2	17/08/11	Janith		Updating to a more robust structure
						Wrote some CSS - fancy gradients and shit
	0.3	18/08/11	Janith		Messed around a bit, added languages/sidebar
	0.4	23/08/11	Janith		Reverted back to dynamic generation 
	0.5	24/08/11	Janith		Adding pagination for results
	0.6	28/08/11	Indi		Sexed up the sidebar (Flickr, Twitter), copy
	0.7	31/08/11	Janith		Changed the social share widgets under posts
	0.8	08/09/11	Janith		Tiny interface changes / added thumbnails :)

******************************************************************************************/

$l = isset($_GET['l']) ? $_GET['l'] : '';	//language
$t = isset($_GET['t']) ? $_GET['t'] : '';	//time period - popular / latest
$p = isset($_GET['p']) ? $_GET['p'] : 1;	//pagination
output($l, $t, $p);

function output($lang, $time, $pagination)
{
	if($lang === 'all' || $lang === '') { $lang = '%'; $l = ''; }
	else { $l = 'l='.$lang; }

	$t = ($time !== '' ? '&t='.$time : '');

	$ps = 0;

	if($pagination > 1 && is_numeric($pagination))
	{
		$ps = 20 * (int)($pagination - 1);
	}

	$output=<<<OUT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Kottu</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="style.css" />
	<link rel="icon" href="./images/kottu.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="./images/kottu.ico" type="image/x-icon" />
</head>
<body>
	<div class="header">
		<a href="./"><img src="images/logo.png"/></a>
		<div id="menu">
			<ul>
				<li>By Language: </li>
				<li id=en><a href="?l=en$t">English</a></li>
				<li id=si><a href="?l=si$t">සිංහල</a></li>
				<li id=ta><a href="?l=ta$t">தமிழ்</a></li>
				<li id=%><a href="?l=all$t">All</a></li>
			</ul>

		</div>
	
		<div id="search">
			<form method="get" action="search.php">
				<input type="text" name="q" id="search-text"/>
			</form>

		</div>
	</div>
	<div class="main">
	<div class="sidebar">

<!-- Start of Flickr Badge -->

<table id="flickr_badge_uber_wrapper" cellpadding="0" cellspacing="0" border="0"><tr><td><table cellpadding="0" cellspacing="0" border="0" id="flickr_badge_wrapper">
<script type="text/javascript" src="http://www.flickr.com/badge_code_v2.gne?count=1&display=latest&size=m&layout=v&source=all_tag&tag=lanka%2C+srilanka"></script>
</table>
</td></tr></table>
<!-- End of Flickr Badge -->

	<h3>About</h3>
	<p>Kottu aggregates over 1,000 Sri Lankan blogs (<a href='http://kottu.org/p/blogroll.php'>Blogroll</a>).</p>
<p><a href="./p/about.php">About/Join</a></p>
	<br/>
	<h3>Hot Hot Kottu</h3>
	<ul>
		<li id=today><a href="?$l&t=today">Today</a></li>
		<li id=week><a href="?$l&t=week">This Week</a></li>
		<li id=month><a href="?$l&t=month">This Month</a></li>
	</ul>
	<br/>

<h3>Tweets</h3>
<script src="http://widgets.twimg.com/j/2/widget.js"></script>
<script>
new TWTR.Widget({
  version: 2,
  type: 'search',
  search: 'geocode:7.716,81.7,200mi',
  interval: 30000,
  title: '',
  subject: 'Sri Lankan Tweets',
  width: 240,
  height: 400,
  theme: {
    shell: {
      background: '#999999',
      color: '#ffffff'
    },
    tweets: {
      background: '#ffffff',
      color: '#444444',
      links: '#1985b5'
    }
  },
  features: {
    scrollbar: false,
    loop: false,
    live: true,
    hashtags: true,
    timestamp: true,
    avatars: true,
    toptweets: true,
    behavior: 'all'
  }
}).render().start();
</script>

<br/>

	<h3>Meta</h3>
	<ul>
		<li><a href="./feed/" title="RSS 2.0 feed for Latest Posts">Latest Posts <small>(RSS 2.0)</small></a></li>
		<li><a href="./feed/popular" title="RSS 2.0 feed for Popular Posts">Popular Posts <small>(RSS 2.0)</small></a></li>
		<li><a href="http://my.statcounter.com/project/standard/stats.php?project_id=610934&guest=1" title="Stats">Stats</a></li>
	</ul>


	</div>
	<div class="content">

OUT;

	$output = str_replace("<li id=$time>",'<li id=current>', $output);
	echo str_replace("<li id=$lang>",'<li id=selected>', $output);

	$DBConnection = new DBConnection();

	if($time === '')
	{
		$resultset = $DBConnection->query("SELECT p.link, p.title, p.postContent, p.postTimestamp, p.postBuzz, b.blogURL, b.blogName, p.tweetCount, p.fbCount FROM posts AS p, blogs AS b WHERE b.bid = p.blogID AND language LIKE :lang ORDER BY serverTimestamp DESC LIMIT $ps, 20", array(':lang'=>$lang)); // no choice here but to put a var in :(
	}
	else
	{
		$day = time() - (24 * 60 * 60);

		if($time === 'week') { $day =  time() - (7 * 24 * 60 * 60); }
		elseif($time === 'month') { $day = time() - (30 * 24 * 60 * 60); }

		$resultset = $DBConnection->query("SELECT p.link, p.title, p.postContent, p.postTimestamp, p.postBuzz, b.blogURL, b.blogName, p.tweetCount, p.fbCount FROM posts AS p, blogs AS b WHERE b.bid = p.blogID AND serverTimestamp > :time AND language LIKE :lang ORDER BY postBuzz DESC LIMIT $ps, 20", array(':lang'=>$lang,':time'=>$day));

	}

	if($resultset) { content($resultset); }

	echo '<p><a href="?p='.($pagination + 1)."&$t&$l\">Older Posts</a>";

	if($pagination > 1)
	{
		echo ' | <a href="?p='.($pagination - 1)."&$t&$l\">Newer Posts</a>";
	}

	echo<<<OUT
	</p>

	</div>
	</div>
</body>
</html>
OUT;
}

function content($resultset)
{
	/*
	Here, we'll loop through all of the items in the feed, and $item represents the current item in the loop.
	*/
	while($array = $resultset->fetch())
	{

		$link = $array[0];
		$title = $array[1];
		$content = strip_tags($array[2]);
		$timestamp = $array[3];
		$buzz = (int)($array[4] * 100);
		$blogurl = $array[5];
		$blogname = $array[6];

		$tw = $array[7];
		$fb = $array[8];

		if($buzz > 100)
		{
			$buzz = 100;
		}

		if($buzz < 0)
		{
			$buzz = 0;
		}

		if($buzz <= 1)	{ $style = '<div id="buzz1"class="buzz"><a>1 chili</a></div>'; }
		else if($buzz <= 15)	{ $style = '<div id="buzz2"class="buzz"><a>2 chilis</a></div>'; }
		else if($buzz <= 35)	{ $style = '<div id="buzz3"class="buzz"><a>3 chilis</a></div>'; }
		else if($buzz <= 55)	{ $style = '<div id="buzz4"class="buzz"><a>4 chilis</a></div>'; }
		else { $style = '<div id="buzz5"class="buzz"><a>5 chilis</a></div>'; }

		// timestamp made human readable

		$now = time();

		if(($now - $timestamp) < (60 * 60))
		{
			$timestamp = (int) (($now - $timestamp) / 60);
			if($timestamp == 1) { $timestamp .= ' minute ago'; }
			else { $timestamp .= ' minutes ago'; }
		}
		else if(($now - $timestamp) < (24 * 60 * 60))
		{
			$timestamp = (int) (($now - $timestamp) / (60 * 60));
			if($timestamp == 1) { $timestamp .= ' hour ago'; }
			else { $timestamp .= ' hours ago'; }
		}
		else if(($now - $timestamp) < (48 * 60 * 60))
		{
			$timestamp = 'Yesterday';
		}
		else
		{
			$timestamp = 'on ' . date('j F Y', $timestamp);
		}

		// post thumbnails

		$html = str_get_html($array[2]);
		$img = '';

		foreach($html->find('img') as $element)
		{
			if(preg_match('/(\.jpg|\.png)/i', $element))
			{
				$content = '<div class="thumb"><img height="80px" src="' . $element->src . '"/></div>' . "<p>$content</p>";
			}
		}

echo<<<OUT
		<div class="item">
			<h2><a href="go.php?url=$link">$title</a></h2>
			<p><small><a href="$blogurl">$blogname</a></small></p>
			$content
			<div id=footer><div id=timestamp>Posted $timestamp</div><div id=twitter><a herf="#" onClick="window.open('https://twitter.com/intent/tweet?source=tweetbutton&url=$link', 'Share on Twitter', 'toolbar=no, scrollbars=yes, width=500, height=400');">$tw tweets</a></div><div id=fb><a href="#"  onClick="window.open('http://www.facebook.com/share.php?u=$link&t=$title', 'Share on Facebook', 'toolbar=no, scrollbars=yes, width=500, height=400');">$fb shares</a></div>$style</div>
		</div>

OUT;
 
	}
}

?>
