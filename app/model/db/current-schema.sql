-- Adminer 3.5.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `addons`;
CREATE TABLE `addons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'user friendly form',
  `composerName` varchar(100) NOT NULL COMMENT 'vendor / package',
  `userId` int(10) unsigned NOT NULL,
  `repository` varchar(500) DEFAULT NULL COMMENT 'repository url (git or svn)',
  `repositoryHosting` enum('github') DEFAULT NULL COMMENT 'repository hosting',
  `shortDescription` varchar(250) NOT NULL COMMENT 'short description',
  `description` text NOT NULL COMMENT 'in Texy! syntax',
  `descriptionFormat` enum('texy','markdown') NOT NULL COMMENT 'texy',
  `demo` varchar(500) DEFAULT NULL COMMENT 'url to demo',
  `updatedAt` datetime NOT NULL COMMENT 'time of last update (of anything)',
  `defaultLicense` varchar(100) NOT NULL COMMENT 'used as default for new versions',
  PRIMARY KEY (`id`),
  UNIQUE KEY `composerName` (`composerName`),
  KEY `userId` (`userId`),
  CONSTRAINT `addons_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `addons_dependencies`;
CREATE TABLE `addons_dependencies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `versionId` int(10) unsigned NOT NULL,
  `dependencyId` int(10) unsigned DEFAULT NULL,
  `packageName` varchar(100) NOT NULL,
  `version` varchar(20) NOT NULL,
  `type` enum('require','suggest','provide','replace','conflict','recommend') NOT NULL DEFAULT 'require',
  PRIMARY KEY (`id`),
  UNIQUE KEY `versionId_type_packageName` (`versionId`,`type`,`packageName`),
  KEY `dependencyId` (`dependencyId`),
  CONSTRAINT `addons_dependencies_ibfk_4` FOREIGN KEY (`versionId`) REFERENCES `addons_versions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `addons_dependencies_ibfk_2` FOREIGN KEY (`dependencyId`) REFERENCES `addons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `addons_tags`;
CREATE TABLE `addons_tags` (
  `addonId` int(10) unsigned NOT NULL,
  `tagId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`addonId`,`tagId`),
  KEY `tagid` (`tagId`),
  CONSTRAINT `addons_tags_ibfk_3` FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `addons_tags_ibfk_4` FOREIGN KEY (`tagId`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `addons_versions`;
CREATE TABLE `addons_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addonId` int(10) unsigned NOT NULL,
  `version` varchar(100) NOT NULL,
  `license` varchar(100) NOT NULL COMMENT 'separed by comma',
  `distType` enum('zip','tarball') NOT NULL COMMENT 'type of distribution archive',
  `distUrl` varchar(500) NOT NULL COMMENT 'link to distribution archive',
  `sourceType` enum('git','hg','svn') DEFAULT NULL COMMENT 'VCS type',
  `sourceUrl` varchar(500) DEFAULT NULL COMMENT 'repository URL, usually the same as addon.repository',
  `sourceReference` varchar(100) DEFAULT NULL COMMENT 'Git, Mercurial or SVN reference (usually branch or tag name)',
  `composerJson` text NOT NULL COMMENT 'composer.json (with source & dist) cache',
  PRIMARY KEY (`id`),
  UNIQUE KEY `addonId_version` (`addonId`,`version`),
  CONSTRAINT `addons_versions_ibfk_1` FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `addons_votes`;
CREATE TABLE `addons_votes` (
  `addonId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `vote` tinyint(4) NOT NULL COMMENT '+1 / -1',
  `comment` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`addonId`,`userId`),
  KEY `userId` (`userId`),
  CONSTRAINT `addons_votes_ibfk_3` FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `addons_votes_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'user friendly form',
  `slug` varchar(50) NOT NULL,
  `level` smallint(5) unsigned NOT NULL COMMENT '1 = category, 2 = subcategory, 9 = others',
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'visible on homepage',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `password` char(40) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','moderator') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2012-09-29 12:37:09
