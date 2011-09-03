<?
require('../DBConnection.php');

/******************************************************************************************
	Kottu 7.8 

	feed - generates the RSS feed

	Version history:
	0.1	23/08/11	Janith		Modified an older file into this

******************************************************************************************/

header('Content-type: application/rss+xml');

$dbh = new DBConnection();

$resultset = $dbh->query("SELECT p.link, p.title, p.postContent, p.postTimestamp, p.postBuzz, b.blogURL, b.blogName FROM posts AS p, blogs AS b WHERE b.bid = p.blogID ORDER BY serverTimestamp DESC LIMIT 15", array());

$date = date('D, d M Y H:i:s O', time());

$output = <<<OUT
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>

<channel>
	<title>Kottu</title>
	<atom:link href="http://kottu.org${_SERVER['REQUEST_URI']}" rel="self" type="application/rss+xml" />
	<link>http://kottu.org</link>
	<description>Kottu is a Sri Lankan blog aggregator</description>
	<lastBuildDate>$date</lastBuildDate>
	<language>en</language>
	<sy:updatePeriod>hourly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>
	<generator>Kottu FeedGen.php</generator>

OUT;

while($array = $resultset->fetch())
{
	$link = $array[0];
	$title = $array[1];
	$content = str_replace("\n", " ", $array[2]);
	$timestamp = date('D, d M Y H:i:s O', $array[3]);

	$output .= <<<OUT
	<item>
	<title>$title</title>
	<link>$link</link>
	<guid>$link</guid>
	<comments>$link#comments</comments>
	<pubDate>$timestamp</pubDate>
	<dc:creator>kottu</dc:creator>
	<description><![CDATA[$content]]></description>
	</item>

OUT;
}

$output .= <<<OUT
</channel>
</rss>
OUT;

echo $output;

?>
