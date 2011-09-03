-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 03, 2011 at 05:30 PM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `kottu`
--

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE IF NOT EXISTS `blogs` (
  `bid` int(11) NOT NULL AUTO_INCREMENT,
  `blogName` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `blogURL` varchar(64) NOT NULL,
  `blogRSS` varchar(128) NOT NULL,
  `access_ts` int(11) NOT NULL DEFAULT '1303306000',
  PRIMARY KEY (`bid`),
  UNIQUE KEY `blogURL` (`blogURL`),
  UNIQUE KEY `blogURL_2` (`blogURL`,`blogRSS`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1365 ;

-- --------------------------------------------------------

--
-- Table structure for table `clicks`
--

CREATE TABLE IF NOT EXISTS `clicks` (
  `url` varchar(64) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`url`,`ip`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `postID` int(11) NOT NULL AUTO_INCREMENT,
  `blogID` int(11) NOT NULL,
  `link` varchar(256) NOT NULL,
  `title` varchar(192) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `postContent` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `language` set('en','si','ta','dv') NOT NULL DEFAULT 'en',
  `postTimestamp` int(11) NOT NULL,
  `serverTimestamp` int(11) NOT NULL,
  `tweetCount` int(11) NOT NULL DEFAULT '0',
  `fbCount` int(11) NOT NULL DEFAULT '0',
  `apiCount_t` int(11) NOT NULL DEFAULT '0',
  `apiCount_f` int(11) NOT NULL DEFAULT '0',
  `postBuzz` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`postID`),
  UNIQUE KEY `link` (`link`),
  KEY `blogID` (`blogID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=146151 ;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `url` varchar(64) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `tag` set('technology','travel','nature','personal','entertainment','business','politics','sports','poetry','photos','other') NOT NULL,
  PRIMARY KEY (`url`,`ip`,`timestamp`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `clicks`
--
ALTER TABLE `clicks`
  ADD CONSTRAINT `clicks_ibfk_1` FOREIGN KEY (`url`) REFERENCES `posts` (`link`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`blogID`) REFERENCES `blogs` (`bid`);
