-- phpMyAdmin SQL Dump
-- version 2.11.11.1
-- http://www.phpmyadmin.net
--
-- Host: mysql
-- Server version: 5.0.77
-- PHP Version: 5.2.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
--

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE IF NOT EXISTS `blogs` (
  `bid` int(11) NOT NULL auto_increment,
  `blogName` varchar(64) character set utf8 collate utf8_unicode_ci NOT NULL,
  `blogURL` varchar(64) NOT NULL,
  `blogRSS` varchar(128) NOT NULL,
  `access_ts` int(11) NOT NULL default '1303306000',
  PRIMARY KEY  (`bid`),
  UNIQUE KEY `blogURL` (`blogURL`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1396 ;

-- --------------------------------------------------------

--
-- Table structure for table `clicks`
--

CREATE TABLE IF NOT EXISTS `clicks` (
  `url` varchar(64) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY  (`url`,`ip`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `plainkottu`
--
CREATE TABLE IF NOT EXISTS `plainkottu` (
`link` varchar(256)
,`title` varchar(192)
,`postContent` varchar(512)
,`serverTimestamp` int(11)
,`postBuzz` float
,`blogURL` varchar(64)
,`blogName` varchar(64)
,`tweetCount` int(11)
,`fbCount` int(11)
);
-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `postID` int(11) NOT NULL auto_increment,
  `blogID` int(11) NOT NULL,
  `link` varchar(256) NOT NULL,
  `title` varchar(192) character set utf8 collate utf8_unicode_ci NOT NULL,
  `postContent` varchar(512) character set utf8 collate utf8_unicode_ci NOT NULL,
  `tags` varchar(32) character set utf8 collate utf8_unicode_ci NOT NULL,
  `language` set('en','si','ta','dv') NOT NULL default 'en',
  `postTimestamp` int(11) NOT NULL,
  `serverTimestamp` int(11) NOT NULL,
  `tweetCount` int(11) NOT NULL default '0',
  `fbCount` int(11) NOT NULL default '0',
  `apiCount_t` int(11) NOT NULL default '0',
  `apiCount_f` int(11) NOT NULL default '0',
  `postBuzz` float NOT NULL default '0',
  PRIMARY KEY  (`postID`),
  UNIQUE KEY `link` (`link`),
  KEY `blogID` (`blogID`),
  KEY `serverTimestamp` (`serverTimestamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=167696 ;

-- --------------------------------------------------------

--
-- Structure for view `plainkottu`
--
DROP TABLE IF EXISTS `plainkottu`;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clicks`
--
ALTER TABLE `clicks`
  ADD CONSTRAINT `clicks_ibfk_1` FOREIGN KEY (`url`) REFERENCES `posts` (`link`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`blogID`) REFERENCES `blogs` (`bid`) ON DELETE CASCADE;
