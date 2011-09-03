<?php

//error_reporting(0); 

require('DBConnection.php');

/******************************************************************************************
	Kottu 7.8 

	search.php

	Version history:
	0.1	17/08/11	Janith		Wrote the search thing

******************************************************************************************/

$DBConnection = new DBConnection();

if(isset($_GET['q']))		// mode switch
{
	$string = '%'.str_replace(' ', '%', $_GET['q']).'%';

	$resultset = $DBConnection->query("SELECT p.link, p.title, p.postContent, p.postTimestamp, p.postBuzz, b.blogURL FROM posts AS p, blogs AS b WHERE b.bid = p.blogID AND (postContent LIKE :string OR title LIKE :string) ORDER BY postBuzz DESC LIMIT 25", array(':string'=>$string));

	if($resultset)
	{
		head('Search results');
		content($resultset);
		tail();
	}
	else
	{
		head('Search results');
echo<<<OUT
		<div class="item">
			<h2>No Results Found</h2>
			<p>We're sorry, but your search string didn't match any entries in our database. Please try again.</p>
		</div>

OUT;
		tail();
	}
		
}
else
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
		<a href="index.php"><img src="images/logo.png"/></a>
		<div id="menu">
			<ul><li></li></ul>

		</div>
	
		<div id="search">
			<form method="get" action="search.php">
				<input type="text" name="q" id="search-text"/>
			</form>

		</div>
	</div>
	<div class="main">
	<div class="content">
	<div class="colhead">
	<h2>Search results:</h2>
	</div>

OUT;

}

function content($resultset)
{
	/*
	Here, we'll loop through all of the items in the feed, and $item represents the current item in the loop.
	*/
	while($array = $resultset->fetch())
	{

		$link = "go.php?url=".$array[0];
		$title = $array[1];
		$content = $array[2];
		$timestamp = date('j F Y | g:i a', $array[3]);
		$buzz = (int)($array[4] * 100);
		$blogurl = $array[5];

		if($buzz > 100)
		{
			$buzz = 100;
		}

		if($buzz < 0)
		{
			$buzz = 0;
		}

echo<<<OUT
		<div class="item">
			<h2><a href="$link">$title</a></h2>
			<p><small><a href="$blogurl">$blogurl</a></small></p>
			<p>$content</p>
			<p><small>Posted on $timestamp | Buzz percentage: $buzz%</small></p>
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
