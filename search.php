<?php

//error_reporting(0); 

require('DBConnection.php');

/******************************************************************************************
	Kottu 7.8 

	search.php

	Version history:
	0.1	17/08/11	Janith		Wrote the search thing
	0.2	03/09/11	Janith		Major expansion - added advanced search

******************************************************************************************/

$DBConnection = new DBConnection();

if(isset($_GET['q']))		// make sure we have a search query
{
	$string = html_entity_decode($_GET['q']);

	$adv = false;	// advanced search off by default

	// if the search string is enclosed in double quotes, search for perfect matches to that *word*
	// if not, search for occurences of that string within other words and stuff.

	if(preg_match('/^_.*_$/', $string))
	{
		$string = '%'.str_ireplace("_", ' ', $string).'%';
	}
	else
	{
		$string = '%'.str_replace(' ', '%', $string).'%';
	}

	// if any of these are set, jump into advanced search. otherwise set the parameter to basically something 
	// that includes all, to avoid ugh running queries without parameters? :)

	if(isset($_GET['l']) && $_GET['l'] != '') { $lang = $_GET['l']; $adv = true; } // get language
	else { $lang = '%'; }

	if(isset($_GET['t']) && $_GET['t'] == 1) { $time = true; $adv = true; } // get sort by time
	else { $time = false; }
	
	if(isset($_GET['s']) && $_GET['s'] != '') 	// get start time
	{
		$starta = explode("%2F",  $_GET['s']);

		if(count($starta) == 3)
		{
			$start = mktime (0, 0, 0, $starta[1], $starta[0], $starta[2]);
		}
		else
		{
			$start = 0;
		}

		$adv = true; 
	} 
	else 
	{
		$start = 0; 
	}

	if(isset($_GET['e']) && $_GET['e'] != '') 	// get end time
	{
		$enda = explode("%2F",  $_GET['e']);

		if(count($enda) == 3)
		{
			$end = mktime (0, 0, 0, $enda[1], $enda[0]+1, $enda[2]);	// +1 is to add one day to the end date, to include the end date
		}
		else
		{
			$end = time();
		}

		$adv = true; 
	} 
	else 
	{
		$end = time(); 
	}

	if($adv)
	{
		if($time)
		{
			$resultset = $DBConnection->query("SELECT p.link, p.title, p.postContent, p.postTimestamp, p.postBuzz, b.blogURL, p.postID FROM posts AS p, blogs AS b WHERE b.bid = p.blogID AND (postContent LIKE :string OR title LIKE :string) AND p.language LIKE :lang AND p.postTimestamp >= :start AND p.postTimestamp <= :end ORDER BY postTimestamp DESC LIMIT 25", array(':string'=>$string, ':lang'=>$lang, ':start'=>$start, ':end'=>$end));
		}
		else
		{

			$resultset = $DBConnection->query("SELECT p.link, p.title, p.postContent, p.postTimestamp, p.postBuzz, b.blogURL, p.postID FROM posts AS p, blogs AS b WHERE b.bid = p.blogID AND (postContent LIKE :string OR title LIKE :string) AND p.language LIKE :lang AND p.postTimestamp >= :start AND p.postTimestamp <= :end ORDER BY postBuzz DESC LIMIT 25", array(':string'=>$string, ':lang'=>$lang, ':start'=>$start, ':end'=>$end));
		}
	}
	else
	{
		$resultset = $DBConnection->query("SELECT p.link, p.title, p.postContent, p.postTimestamp, p.postBuzz, b.blogURL, p.postID FROM posts AS p, blogs AS b WHERE b.bid = p.blogID AND (postContent LIKE :string OR title LIKE :string) ORDER BY postBuzz DESC LIMIT 25", array(':string'=>$string));

	}

	if($resultset)
	{
		head('Search results');
		content($resultset, $_GET['q']);
		tail();
	}	
}
else			// redirect to home page if no query string
{ 
	header('location: ./index.php');
}

function head($string)
{

echo<<<OUT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Kottu: $string</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="style.css" />
	<link rel="icon" href="./images/kottu.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="./images/kottu.ico" type="image/x-icon" />
</head>
<body>
	<div class="header">
	<div id="menu">
		<a href="./"><span id="logo"/></a>
		<div id="lan">
		</div>

	
		<div id="search">
			<form method="get" action="../search.php">
				<input type="text" name="q" id="search-text"/>
			</form>

		</div>
	</div>
	</div>
	<div class="main">
	<div class="sidebar">
	<h3>Note:</h3>
	To search for a particular word (and not a phrase), use underscores to enclose the search string.<br/><br/>
	E.g. _india_ instead of india.

	</div>
	<div class="content">
	<br/>
	<span id="asnotify">Want more options? <a href="#" onClick="document.getElementById('advancedsearch').style.display = 'block'; document.getElementById('asnotify').style.display = 'none'; return false;">Show advanced search</a></span>
	<div class="item" id="advancedsearch" style="display:none;">
	<form method="get" action="search.php">
	<label>Search term(s): </label><input type="text" name="q" tabindex=2 value="${_GET['q']}"/><br/><br/>
	<label>Language: </label>
	<select name="l" tabindex=5>
		<option value="">All</option>
		<option value="en">English</option>
		<option value="si">Sinhala</option>
		<option value="ta">Tamil</option>
	</select>
	<br/><br/>

	<input type="radio" name="t" value="1" /> Order by age of post<br />
	<input type="radio" name="t" value="0" /> Order by popularity<br/><br/>

	<input class="button" type="submit" style="width:140px;" value="Advanced search" tabindex=6 />
	<input class="button" type="reset" style="width:140px;" value="Reset" tabindex=7 />
	</form>

	<br/>
	</div>
	<div>
	<h2>Search results:</h2>
	</div>

OUT;

}

function content($resultset, $searchstring)
{
	// Here, we'll loop through all of the items in the feed, and $item represents the current item in the loop.

	$count = 0;		// count of number of elements

	while($array = $resultset->fetch())
	{

		$link = $array[0];
		$title = $array[1];
		$content = strip_tags($array[2]);
		$timestamp = date('j F Y', $array[3]);
		$buzz = (int)($array[4] * 100);
		$blogurl = $array[5];
		$postid = $array[6];

		if($buzz <= 1)		{ $style = '<div id="buzz1"class="buzz"><a>1 chili</a></div>'; }
		else if($buzz <= 15)	{ $style = '<div id="buzz2"class="buzz"><a>2 chilis</a></div>'; }
		else if($buzz <= 35)	{ $style = '<div id="buzz3"class="buzz"><a>3 chilis</a></div>'; }
		else if($buzz <= 55)	{ $style = '<div id="buzz4"class="buzz"><a>4 chilis</a></div>'; }
		else			{ $style = '<div id="buzz5"class="buzz"><a>5 chilis</a></div>'; }

		// highlighing searchstring in results

		$searchstring = str_replace('_', '', $searchstring);
		$content = str_ireplace($searchstring, '<strong><u>'.$searchstring.'</u></strong>', $content);

echo<<<OUT
	<div class="item">
		<h2><a href="go.php?pid=$postid&url=$link">$title</a></h2>
		<p><small><a href="$blogurl">$blogurl</a></small></p>
		<p>$content</p>
		<div id=footer><div id=timestamp>Posted $timestamp</div>$style</div>
	</div>

OUT;

		$count++;
	}

	if($count == 0)
	{
		echo<<<OUT
		<div class="item">
			<h2>No Results Found</h2>
			<p>We're sorry, but your search string didn't match any entries in our database. Please try again.</p>
		</div>

OUT;
	}
}

function tail()
{

echo<<<OUT

</div>
</div>
</body>
</html>
OUT;

}

?>
