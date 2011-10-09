<?php
include('./simple_html_dom.php');

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

This is the new Posts class. We create posts and commit them to the database
here, as well as handle thumbnail issues, etc.

Version history:
0.1	09/10/11	Janith		Began Posts.php

******************************************************************************************/

class Posts
{
	private $blogid;
	private $title;
	private $content;
	private $thumbnail;
	private $url;
	private $timestamp;
	private $tags;
	private $language;
	private $tagcount = 0;

	public function __construct() { }

	// basic function to add a post
	public function setDetails($bid, $postTitle, $postUrl, $postCont, $ts)
	{
		// see if a post is future dated and if it is 
		// change its timestamp to the current time
		$now = time();
		if($ts > $now) { $ts = $now; }

		// if a post doesn't have a title, give it one
		if(strlen($postTitle) < 2) { $postTitle = "Untitled Post"; }

		// generate post thumbnail
		$this->addThumbnail($postCont);

		// strip all the html tags from the post content
		$postCont = strip_tags($postCont);

		// removing those stupid multiple spaces
		$postCont = preg_replace("/(&nbsp;|\s|&nbsp;\s)+/", ' ', $postCont);

		// summary generator
		if(strlen($postCont) > 400)
		{
			$paragraph = explode(' ', $postCont);
			$paragraph = array_slice($paragraph, 0, 60);
			$postCont = implode(' ', $paragraph);
			$postCont .= " ...";
		}

		// language filter using unicode ranges
		$lang = 'en';
		if(preg_match('/[\x{0D80}-\x{0DFF}]{3,5}/u', $postCont.$postTitle))
		{
			$lang = 'si';
		}
		else if(preg_match('/[\x{0B80}-\x{0BFF}]{3,5}/u', $postCont.$postTitle))
		{
			$lang = 'ta';
		}

		// put all the data into the object vars
		$this->blogid	 = $bid;
		$this->title	 = $postTitle;
		$this->content	 = $postCont;
		$this->url	 = $postUrl;
		$this->timestamp = $ts;
		$this->language	 = $lang;
	}

	// adding tags to a post
	public function addTag($tag)
	{
		if($this->tagcount < 3)
		{
			$tag = trim(strtolower($tag));
			$this->tags .= "$tag,";
			$this->tagcount++;
		}
	}

	// todo: thumbnail creator. have to get permission.
	public function addThumbnail($postContent)
	{
		// finding images in the post content
		$html = str_get_html($postContent);
		$imglink = null;

		if(is_object($html))
		{

			foreach($html->find('img') as $element)
			{
				if(preg_match('/(\.jpg|\.png)/i', $element))
				{
					$imglink = $element->src;
					break;
				}
			}

			// thumbnail generator to be implemented here. meh meh.
		}

		$this->thumbnail = $imglink;
	}

	// this commits all the post data to the database
	public function commit($dbh)
	{
		$rs = $dbh->query("INSERT INTO posts(postID, blogID, link, title, postContent, serverTimestamp, thumbnail, language, tags) ".
			"VALUES (NULL, :bid, :link, :title, :content, :ts, :thumb, :lang, :tags)", array(
					':bid'		=> $this->blogid,
					':link'		=> $this->url,
					':title'	=> $this->title,
					':content'	=> $this->content,
					':ts'		=> $this->timestamp,
					':thumb'	=> $this->thumbnail,
					':lang'		=> $this->language,
					':tags'		=> $this->tags));

		// for debugging
		echo "posts.php: added post to db: " . $this->url . " \n";
	}
}

?>
