-- Adminer 3.6.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `addons`;
CREATE TABLE `addons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'user friendly form',
  `composerVendor` varchar(50) NOT NULL COMMENT 'composer valid package vendor name',
  `composerName` varchar(50) NOT NULL COMMENT 'composer valid package name',
  `userId` int(10) unsigned NOT NULL,
  `repository` varchar(500) DEFAULT NULL COMMENT 'repository url (git or svn)',
  `repositoryHosting` enum('github') DEFAULT NULL COMMENT 'repository hosting',
  `shortDescription` varchar(250) NOT NULL COMMENT 'short description',
  `description` text NOT NULL COMMENT 'in Texy! syntax',
  `descriptionFormat` enum('texy','markdown') NOT NULL COMMENT 'texy',
  `demo` varchar(500) DEFAULT NULL COMMENT 'url to demo',
  `updatedAt` datetime NOT NULL COMMENT 'time of last update (of anything)',
  `defaultLicense` varchar(100) NOT NULL COMMENT 'used as default for new versions',
  `totalDownloadsCount` int(11) NOT NULL DEFAULT '0' COMMENT 'total times this addon was downloaded',
  `totalInstallsCount` int(11) NOT NULL DEFAULT '0' COMMENT 'total times this addon was installed using composer',
  PRIMARY KEY (`id`),
  UNIQUE KEY `composerFullName` (`composerVendor`, `composerName`),
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
  `type` enum('require','require-dev','suggest','provide','replace','conflict','recommend') NOT NULL DEFAULT 'require',
  PRIMARY KEY (`id`),
  UNIQUE KEY `versionId_type_packageName` (`versionId`,`type`,`packageName`),
  KEY `dependencyId` (`dependencyId`),
  CONSTRAINT `addons_dependencies_ibfk_4` FOREIGN KEY (`versionId`) REFERENCES `addons_versions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `addons_dependencies_ibfk_2` FOREIGN KEY (`dependencyId`) REFERENCES `addons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `addons_downloads`;
CREATE TABLE `addons_downloads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `versionId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned DEFAULT NULL,
  `ipAddress` varchar(39) NOT NULL COMMENT 'ipv6 has <=39 characters',
  `userAgent` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  `type` enum('download','install') NOT NULL COMMENT 'download via web / install via composer',
  `fake` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `versionId` (`versionId`),
  KEY `userId` (`userId`),
  CONSTRAINT `addons_downloads_ibfk_2` FOREIGN KEY (`versionId`) REFERENCES `addons_versions` (`id`),
  CONSTRAINT `addons_downloads_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
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
  `downloadsCount` int(11) NOT NULL DEFAULT '0' COMMENT 'number of downloads',
  `installsCount` int(11) NOT NULL DEFAULT '0' COMMENT 'number of installs using composer',
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
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`addonId`,`userId`),
  KEY `userId` (`userId`),
  CONSTRAINT `addons_votes_ibfk_3` FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `addons_votes_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `g_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `g_title` varchar(50) NOT NULL DEFAULT '',
  `g_user_title` varchar(50) DEFAULT NULL,
  `g_read_board` tinyint(1) NOT NULL DEFAULT '1',
  `g_post_replies` tinyint(1) NOT NULL DEFAULT '1',
  `g_post_topics` tinyint(1) NOT NULL DEFAULT '1',
  `g_post_polls` tinyint(1) NOT NULL DEFAULT '1',
  `g_edit_posts` tinyint(1) NOT NULL DEFAULT '1',
  `g_delete_posts` tinyint(1) NOT NULL DEFAULT '1',
  `g_delete_topics` tinyint(1) NOT NULL DEFAULT '1',
  `g_set_title` tinyint(1) NOT NULL DEFAULT '1',
  `g_search` tinyint(1) NOT NULL DEFAULT '1',
  `g_search_users` tinyint(1) NOT NULL DEFAULT '1',
  `g_edit_subjects_interval` smallint(6) NOT NULL DEFAULT '300',
  `g_post_flood` smallint(6) NOT NULL DEFAULT '30',
  `g_search_flood` smallint(6) NOT NULL DEFAULT '30',
  PRIMARY KEY (`g_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'user friendly form',
  `slug` varchar(50) NOT NULL,
  `level` smallint(5) unsigned NOT NULL COMMENT '1 = category, 2 = subcategory, 9 = others',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'visible on homepage',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL DEFAULT '4',
  `username` varchar(200) NOT NULL DEFAULT '',
  `password` varchar(40) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(50) DEFAULT NULL,
  `realname` varchar(40) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `jabber` varchar(75) DEFAULT NULL,
  `icq` varchar(12) DEFAULT NULL,
  `msn` varchar(50) DEFAULT NULL,
  `aim` varchar(30) DEFAULT NULL,
  `yahoo` varchar(30) DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL,
  `use_avatar` tinyint(1) NOT NULL DEFAULT '0',
  `signature` text,
  `disp_topics` tinyint(3) unsigned DEFAULT NULL,
  `disp_posts` tinyint(3) unsigned DEFAULT NULL,
  `email_setting` tinyint(1) NOT NULL DEFAULT '1',
  `save_pass` tinyint(1) NOT NULL DEFAULT '1',
  `notify_with_post` tinyint(1) NOT NULL DEFAULT '0',
  `show_smilies` tinyint(1) NOT NULL DEFAULT '1',
  `show_img` tinyint(1) NOT NULL DEFAULT '1',
  `show_img_sig` tinyint(1) NOT NULL DEFAULT '1',
  `show_avatars` tinyint(1) NOT NULL DEFAULT '1',
  `show_sig` tinyint(1) NOT NULL DEFAULT '1',
  `timezone` float NOT NULL DEFAULT '0',
  `language` varchar(25) NOT NULL DEFAULT 'English',
  `style` varchar(25) NOT NULL DEFAULT 'Oxygen',
  `num_posts` int(10) unsigned NOT NULL DEFAULT '0',
  `last_post` int(10) unsigned DEFAULT NULL,
  `registered` int(10) unsigned NOT NULL DEFAULT '0',
  `registration_ip` varchar(15) NOT NULL DEFAULT '0.0.0.0',
  `last_visit` int(10) unsigned NOT NULL DEFAULT '0',
  `admin_note` varchar(30) DEFAULT NULL,
  `activate_string` varchar(50) DEFAULT NULL,
  `activate_key` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_registered_idx` (`registered`),
  KEY `users_username_idx` (`username`(8))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `users_details`;
CREATE TABLE `users_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `apiToken` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2012-10-21 23:11:03
