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
	0.9	16/09/11	Janith		Categorization, finally!
	0.91	25/09/11	Janith		Added the catarray for easier maintenance, not sure
						about how secure methods are, but we do sanitize
	0.92	05/10/11	Janith		Added JS scroller to track rural kids' blogs

******************************************************************************************/

$l = isset($_GET['l']) ? $_GET['l'] : '';	//language
$t = isset($_GET['t']) ? $_GET['t'] : '';	//time period - popular / latest
$p = isset($_GET['p']) ? $_GET['p'] : 1;	//pagination
$c = isset($_GET['c']) ? $_GET['c'] : '%';	//categories

$l = (strlen($l) > 3) ? substr($l, 0, 2) : $l;	// convert english into en, tamil into ta etc.

$start_time = microtime();			// timing



output($l, $t, $p, $c);

function output($lang, $time, $pagination, $cats)
{
	if($lang === 'all' || $lang === '') { $lang = '%'; $l = ''; }
	else { $l = 'l='.$lang; }

	$t = ($time !== '') ? ('&t='.$time) : '';

	$c = ($cats !== '%') ? ('&c='.$cats) : '';

	$ps = 0;

	if($pagination > 1 && is_numeric($pagination))
	{
		$ps = 20 * (int)($pagination - 1);
	}

	$langselect =<<<OUT

		<li id=en><a href="?l=en$t$c">English</a></li>
		<li id=si><a href="?l=si$t$c">සිංහල</a></li>
		<li id=ta><a href="?l=ta$t$c">தமிழ்</a></li>

OUT;
		
	$langselect = str_replace("<li id=$lang>",'<li id=selected>', $langselect);

	$timeselect =<<<OUT

		<li id=today><a href="?$l$c&t=today">Today</a></li>
		<li id=week><a href="?$l$c&t=week">This Week</a></li>
		<li id=month><a href="?$l$c&t=month">This Month</a></li>


OUT;

	$timeselect = str_replace("<li id=$time>",'<li id=current>', $timeselect);

	echo<<<OUT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Kottu</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="style.css" />
	<link rel="icon" href="./images/kottu.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="./images/kottu.ico" type="image/x-icon" />
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="http://kottu.org/feed/" />
	
	<script type="text/javascript">

	/***********************************************
	* Pausing up-down scroller- © Dynamic Drive (www.dynamicdrive.com)
	* This notice MUST stay intact for legal use
	* Visit http://www.dynamicdrive.com/ for this script and 100s more.
	***********************************************/

	function pausescroller(d,a,c,b){this.content=d;this.tickerid=a;this.delay=b;this.mouseoverBol=0;this.hiddendivpointer=1;document.write('<div id="'+a+'" class="'+c+'" style="position: relative; overflow: hidden"><div class="innerDiv" style="position: absolute; width: 100%" id="'+a+'1">'+d[0]+'</div><div class="innerDiv" style="position: absolute; width: 100%; visibility: hidden" id="'+a+'2">'+d[1]+"</div></div>");var e=this;if(window.addEventListener){window.addEventListener("load",function(){e.initialize()},false)}else{if(window.attachEvent){window.attachEvent("onload",function(){e.initialize()})}else{if(document.getElementById){setTimeout(function(){e.initialize()},500)}}}}pausescroller.prototype.initialize=function(){this.tickerdiv=document.getElementById(this.tickerid);this.visiblediv=document.getElementById(this.tickerid+"1");this.hiddendiv=document.getElementById(this.tickerid+"2");this.visibledivtop=parseInt(pausescroller.getCSSpadding(this.tickerdiv));this.visiblediv.style.width=this.hiddendiv.style.width=this.tickerdiv.offsetWidth-(this.visibledivtop*2)+"px";this.getinline(this.visiblediv,this.hiddendiv);this.hiddendiv.style.visibility="visible";var a=this;document.getElementById(this.tickerid).onmouseover=function(){a.mouseoverBol=1};document.getElementById(this.tickerid).onmouseout=function(){a.mouseoverBol=0};if(window.attachEvent){window.attachEvent("onunload",function(){a.tickerdiv.onmouseover=a.tickerdiv.onmouseout=null})}setTimeout(function(){a.animateup()},this.delay)};pausescroller.prototype.animateup=function(){var a=this;if(parseInt(this.hiddendiv.style.top)>(this.visibledivtop+5)){this.visiblediv.style.top=parseInt(this.visiblediv.style.top)-5+"px";this.hiddendiv.style.top=parseInt(this.hiddendiv.style.top)-5+"px";setTimeout(function(){a.animateup()},50)}else{this.getinline(this.hiddendiv,this.visiblediv);this.swapdivs();setTimeout(function(){a.setmessage()},this.delay)}};pausescroller.prototype.swapdivs=function(){var a=this.visiblediv;this.visiblediv=this.hiddendiv;this.hiddendiv=a};pausescroller.prototype.getinline=function(b,a){b.style.top=this.visibledivtop+"px";a.style.top=Math.max(b.parentNode.offsetHeight,b.offsetHeight)+"px"};pausescroller.prototype.setmessage=function(){var c=this;if(this.mouseoverBol==1){setTimeout(function(){c.setmessage()},100)}else{var a=this.hiddendivpointer;var b=this.content.length;this.hiddendivpointer=(a+1>b-1)?0:a+1;this.hiddendiv.innerHTML=this.content[this.hiddendivpointer];this.animateup()}};pausescroller.getCSSpadding=function(a){if(a.currentStyle){return a.currentStyle.paddingTop}else{if(window.getComputedStyle){return window.getComputedStyle(a,"").getPropertyValue("padding-top")}else{return 0}}};

</script>

</head>
<body>
	<div class="header">
	<div id="menu">
		<a href="./"><span id="logo"/></a>
		
		<div id="lan">
		<ul>
			$langselect
		</ul>
		</div>

		<div id="search">
			<form method="get" action="search.php">
				<input type="text" name="q" id="search-text"/>
			</form>

		</div>

	</div>	
	</div>
	<div class="main">
	<div class="content">
	<!--[if lte IE 7]>
		<br/><br/><br/>
		<a href="?l=en$t$c">English</a> | <a href="?l=si$t$c">සිංහල</a> | <a href="?l=ta$t$c">தமிழ்</a>
		<br/><br/>
		<strong>You are running Internet Explorer 7 (or older), which our website does not support. Please <a href="http://www.mozilla.org/en-US/firefox/features/">consider upgrading</a> to a <a href="http://www.google.com/chrome">standards compliant</a> browser. Thank you!</strong>
	<![endif]-->

OUT;

	$DBConnection = new DBConnection();

	// tag magic - the end , in some are to match the tag delimiters in the db

	$catarray = array( 
		'tech' 		=> array('science','linux','windows','virus',',mobile','software','phones','android','electronic','physics','mathematics','maths','web,','sharepoint','internet'),
		'travel'	=> array('food','recipes','hotel','hike','hiking','beach'),
		'nature'	=> array('environment','conservation','animal','wildlife','pollution','forest'),
		'sports'	=> array('cricket','rugby','football','soccer','volleyball','athlet','tennis'),
		'news'		=> array('breaking','security','election','media',',press'),
		'personal'	=> array('life,','love','family','romance','exam','emotion','thought','story','stories','social','friend','boredom','rant','ramblings','work'),
		'entertainment'	=> array('art,','music','song','album','movie','film','cinema',' tv ','video','literature','literary','magazine','event'),
		'poetry'	=> array('poem','poetry'),
		'business'	=> array('industry','bank','economy','economics','development','agricultur'),
		'politics'	=> array('election','peace','war,','conflict','security','economy','development','youth','tigers','community'),
		'photo'		=> array('image'),
		'faith'		=> array('religion','belief','buddhis','christian','hindu','islam','muslim','god,','atheis'),
		'education'	=> array('exam','university','school','teach'),
		'other'		=> array('uncategorized','random','general'));

	$cats = preg_replace("/[^a-z]/", "", $cats);	// sanitizing

	if(is_array($catarray[$cats]))
	{
		$catext = "p.tags LIKE '%$cats%'" ;

		foreach($catarray[$cats] as $key => $value)
		{
			$catext .= " OR p.tags LIKE '%$value%'";
		}
	}
	else
	{
		$catext = '';
	}

	if($catext !== '') { $catext = "AND ($catext)"; }

	if($time === '')
	{
		$resultset = $DBConnection->query("SELECT p.link, p.title, p.postContent, p.serverTimestamp, p.postBuzz, b.blogURL, b.blogName, p.tweetCount, p.fbCount, p.postID FROM posts AS p, blogs AS b WHERE b.bid = p.blogID AND language LIKE :lang $catext ORDER BY serverTimestamp DESC LIMIT $ps, 20", array(':lang'=>$lang)); // no choice here but to put a var in :(
	}
	else
	{
		$day = time() - (24 * 60 * 60);

		if($time === 'week') { $day =  time() - (7 * 24 * 60 * 60); }
		elseif($time === 'month') { $day = time() - (30 * 24 * 60 * 60); }

		$resultset = $DBConnection->query("SELECT p.link, p.title, p.postContent, p.serverTimestamp, p.postBuzz, b.blogURL, b.blogName, p.tweetCount, p.fbCount, p.postID FROM posts AS p, blogs AS b WHERE b.bid = p.blogID AND serverTimestamp > :time AND language LIKE :lang $catext ORDER BY postBuzz DESC LIMIT $ps, 20", array(':lang'=>$lang,':time'=>$day));

	}

	if($resultset) { content($resultset); }

	echo "<p><a href=\"?$l$c$t&p=".($pagination + 1).'">Older Posts</a>';

	if($pagination > 1)
	{
		echo " | <a href=\"?$l$c$t&p=".($pagination - 1).'">Newer Posts</a>';
	}

	$scrollertext = sidescroller($DBConnection);

	echo<<<OUT
	</p>

	</div>
	<div class="sidebar">

	<!-- Start of Flickr Badge -->

	<table id="flickr_badge_uber_wrapper" cellpadding="0" cellspacing="0" border="0"><tr><td><table cellpadding="0" cellspacing="0" border="0" id="flickr_badge_wrapper">
	<script type="text/javascript" src="http://www.flickr.com/badge_code_v2.gne?count=1&display=latest&size=m&layout=v&source=all_tag&tag=lanka%2C+srilanka"></script>
	</table>
	</td></tr></table>
	<!-- End of Flickr Badge -->
	<br/>
	<div class="tagcloud">
		<span class="tag tagsize2">
		<a href="?$l$t&c=tech">science & tech</a>
		</span>
		<span class="tag tagsize1">
		<a href="?$l$t&c=nature">nature</a>
		</span>
		<span class="tag tagsize2">
		<a href="?$l$t&c=news">news</a>
		</span>
		<span class="tag tagsize1">
		<a href="?$l$t&c=education">education</a>
		</span>
		<span class="tag tagsize1">
		<a href="?$l$t&c=travel">food & travel</a>
		</span>
		<span class="tag tagsize3">
		<a href="?$l$t&c=sports">sports</a>
		</span>
		<span class="tag tagsize3">
		<a href="?$l$t&c=personal">personal</a>
		</span>
		<span class="tag tagsize1">
		<a href="?$l$t&c=poetry">poetry</a>
		</span>
		<span class="tag tagsize2">
		<a href="?$l$t&c=business">business</a>
		</span>
		<span class="tag tagsize1">
		<a href="?$l$t&c=faith">faith</a>
		</span>
		<span class="tag tagsize2">
		<a href="?$l$t&c=entertainment">art & entertainment</a>
		</span>
		<span class="tag tagsize3">
		<a href="?$l$t&c=politics">politics</a>
		</span>
		<span class="tag tagsize2">
		<a href="?$l$t&c=photo">photography</a>
		</span>
		<span class="tag tagsize2">
		<a href="?$l$t&c=other">uncategorized</a>
		</span>
	</div>
	<br/>

	<h3>Hot Hot Kottu</h3>
	<ul>
		$timeselect
	</ul>
	<br/>

	<h3>Encourage them!</h3>
	<script type="text/javascript">
	
	var blogcontent=new Array();

	blogcontent[0]='<img src="./images/banner.png"/>';
	$scrollertext

	new pausescroller(blogcontent, "scroller", "someclass", 5000);
	</script>

	<h3>About</h3>
	<p>Kottu aggregates over 1,000 Sri Lankan blogs (<a href='./blogroll'>Blogroll</a>).</p>
	<p><a href="./about">About/Join</a></p>
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
		<li><a href="https://github.com/janithl/Kottu" title="GitHub">Source Code</a></li>
	</ul>


	</div>
	</div>
<!-- Start of StatCounter Code -->
<script type="text/javascript" language="javascript">
var sc_project=610934; 
var sc_invisible=0; 
var sc_partition=4; 
var sc_security="0af09d7d"; 
</script>

<script type="text/javascript" language="javascript" src="http://www.statcounter.com/counter/counter.js"></script><noscript><a href="http://www.statcounter.com/" target="_blank"><img  src="http://c5.statcounter.com/counter.php?sc_project=610934&java=0&security=0af09d7d&invisible=0" alt="blog stats" border="0"></a> </noscript>

<!-- End of StatCounter Code -->

<script src="http://www.google-analytics.com/urchin.js" type="­tex­t/­javas­cript"></script>
<script type="­tex­t/­javas­cript"  type="text/javascript" language="javascript">
_uacct = "UA-182033-5";
urch­in­Track­er­();
</script>


OUT;


printf("<!-- Page generated in %2.4f seconds. All is good with the Kottu baas! -->\n\n</body>\n</html>", (microtime() - $start_time));

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
		$postid = $array[9];

		if($buzz <= 1)		{ $style = '<div id="buzz1"class="buzz"><a>1 chili</a></div>'; }
		else if($buzz <= 15)	{ $style = '<div id="buzz2"class="buzz"><a>2 chilis</a></div>'; }
		else if($buzz <= 35)	{ $style = '<div id="buzz3"class="buzz"><a>3 chilis</a></div>'; }
		else if($buzz <= 55)	{ $style = '<div id="buzz4"class="buzz"><a>4 chilis</a></div>'; }
		else			{ $style = '<div id="buzz5"class="buzz"><a>5 chilis</a></div>'; }

		// timestamp made human readable

		$now = time();

		if(($now - $timestamp) <= 0)
		{
			$timestamp = 'less than a minute ago';
		}
		else if(($now - $timestamp) < (60 * 60))
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

		if(is_object($html))
		{

			foreach($html->find('img') as $element)
			{
				if(preg_match('/(\.jpg|\.png)/i', $element))
				{
					$content = '<div class="thumb"><img height="80px" src="' . $element->src . '"/></div>' . "<p>$content</p>";
				}
			}
		}

echo<<<OUT

	<div class="item">
	<h2><a href="go.php?pid=$postid&url=$link">$title</a></h2>
	<p><small><a href="$blogurl">$blogname</a></small></p>
	$content
	<div id=footer><div id=timestamp>Posted $timestamp</div><div id=twitter><a herf="#" onClick="window.open('https://twitter.com/intent/tweet?source=tweetbutton&url=$link', 'Share on Twitter', 'toolbar=no, scrollbars=yes, width=500, height=400');">$tw tweets</a></div><div id=fb><a href="#"  onClick="window.open('http://www.facebook.com/share.php?u=$link&t=$title', 'Share on Facebook', 'toolbar=no, scrollbars=yes, width=500, height=400');">$fb shares</a></div>$style</div>
	</div>

OUT;
 
	}
}

// generates sidebar scroller
function sidescroller($dbh)
{
	$resultset = $dbh->query("SELECT p.link, p.title, p.serverTimestamp, p.postContent FROM posts AS p WHERE p.blogid IN (1407, 1403, 419, 278)"
	. " ORDER BY serverTimestamp DESC LIMIT 9", array());

	$output = '';

	if($resultset)
	{
		$count = 1;
		while($array = $resultset->fetch())
		{

			$link = $array[0];
			$title = $array[1];
			$timestamp = $array[2];

			$content = strip_tags($array[3]);
			if(strlen($content) > 220)	// summary generator
			{
				$paragraph = explode(' ', $content);
				$paragraph = array_slice($paragraph, 0, 26);
				$content = implode(' ', $paragraph) . " ...";
			}

			$now = time();

			if(($now - $timestamp) <= 0)
			{
				$timestamp = 'less than a minute ago';
			}
			else if(($now - $timestamp) < (60 * 60))
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

			$output .= <<<OUT

	blogcontent[$count]='<strong><a href="$link">$title</a></strong><br /><small>$timestamp</small><br /><br />$content<br /><br />(<a href="$link">read more</a>)';

OUT;

			$count++;
		}
	}

	return $output;
}

?>
