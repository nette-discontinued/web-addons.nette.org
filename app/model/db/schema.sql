-- Adminer 3.1.0 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `addon_dependencies`;
CREATE TABLE `addon_dependencies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addonId` int(10) unsigned NOT NULL,
  `dependencyId` int(10) unsigned DEFAULT NULL,
  `packageName` varchar(100) DEFAULT NULL,
  `version` varchar(20) NOT NULL,
  `type` enum('require','suggest','provide','replace','conflict','recommend') NOT NULL DEFAULT 'require',
  PRIMARY KEY (`id`),
  KEY `addonId` (`addonId`),
  KEY `dependencyId` (`dependencyId`),
  CONSTRAINT `addon_dependencies_ibfk_1` FOREIGN KEY (`dependencyId`) REFERENCES `addons` (`id`),
  CONSTRAINT `addon_dependencies_ibfk_2` FOREIGN KEY (`addonId`) REFERENCES `addon_versions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `addon_tags`;
CREATE TABLE `addon_tags` (
  `addonId` int(10) unsigned NOT NULL,
  `tagid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`addonId`,`tagid`),
  KEY `tagid` (`tagid`),
  CONSTRAINT `addon_tags_ibfk_1` FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`),
  CONSTRAINT `addon_tags_ibfk_2` FOREIGN KEY (`tagid`) REFERENCES `tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `addon_versions`;
CREATE TABLE `addon_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addonId` int(10) unsigned NOT NULL,
  `version` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addonId` (`addonId`),
  CONSTRAINT `addon_versions_ibfk_1` FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `addons`;
CREATE TABLE `addons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `authorId` int(10) unsigned NOT NULL,
  `repository` varchar(250) NOT NULL COMMENT 'repository url (git or svn)',
  `description` text NOT NULL COMMENT 'in Texy! syntax',
  `updatedAt` datetime NOT NULL COMMENT 'time of last update (of anything)',
  PRIMARY KEY (`id`),
  KEY `authorId` (`authorId`),
  CONSTRAINT `addons_ibfk_1` FOREIGN KEY (`authorId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'user friendly form',
  `slug` varchar(50) NOT NULL,
  `level` smallint(5) unsigned NOT NULL COMMENT '1 = category, 2 = subcategory, 9 = others',
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'visible on homepage',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `password` char(40) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2012-04-14 14:53:58
