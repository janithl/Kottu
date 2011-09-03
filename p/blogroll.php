<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
        "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Kottu: Blogroll</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="../style.css" />
	<link rel="icon" href="../images/kottu.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="../images/kottu.ico" type="image/x-icon" />
</head>
<body>
	<div class="header">
		<a href="../index.php"><img src="../images/logo.png"/></a>

		<div id="menu">
			<ul>
				<li><a href="about.php">About/Join</a></li>
				<li id="selected"><a href="blogroll.php">Blogroll</a></li>
			</ul>

		</div>

	
		<div id="search">
			<form method="get" action="../search.php">
				<input type="text" name="q" id="search-text"/>
			</form>

		</div>
	</div>
	<div class="main">
	<div class="content">
	<h2>Blogroll</h2>
		<div class="item">
			<p>This is a list of all the blogs currently syndicated on kottu.org. To add your (Sri Lankan) blog just email indi[at]indi[dot]ca</p>
			<p><ul>
<?php
	include('../DBConnection.php');

	$DBConnection = new DBConnection();

	$resultset = $DBConnection->query("SELECT blogName, blogURL FROM blogs", array());

	if($resultset)
	{
		while($array = $resultset->fetch())
		{
			echo "<li><a href=\"${array[1]}\">${array[0]}</a></li>";
		}
	}
?>
			</ul></p>
		<div>
	</div>
	</div>

</body>
</html>
