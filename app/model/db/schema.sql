-- Adminer 3.3.3 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `addon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `repository` varchar(250) NOT NULL COMMENT 'repository url (git or svn)',
  `description` text NOT NULL COMMENT 'in Texy! syntax',
  `updated_at` datetime NOT NULL COMMENT 'time of last update (of anything)',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `addon_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addon_dependency` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addon_id` int(10) unsigned NOT NULL,
  `dependency_id` int(10) unsigned DEFAULT NULL,
  `package_name` varchar(100) DEFAULT NULL,
  `version` varchar(20) NOT NULL,
  `type` enum('require','suggest','provide','replace','conflict','recommend') NOT NULL DEFAULT 'require',
  PRIMARY KEY (`id`),
  KEY `addonId` (`addon_id`),
  KEY `dependencyId` (`dependency_id`),
  CONSTRAINT `addon_dependency_ibfk_1` FOREIGN KEY (`addon_id`) REFERENCES `addon_version` (`id`),
  CONSTRAINT `addon_dependency_ibfk_2` FOREIGN KEY (`dependency_id`) REFERENCES `addon` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addon_tag` (
  `addon_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`addon_id`,`tag_id`),
  KEY `tagid` (`tag_id`),
  CONSTRAINT `addon_tag_ibfk_1` FOREIGN KEY (`addon_id`) REFERENCES `addon` (`id`),
  CONSTRAINT `addon_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addon_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addon_id` int(10) unsigned NOT NULL,
  `version` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addonId` (`addon_id`),
  CONSTRAINT `addon_version_ibfk_1` FOREIGN KEY (`addon_id`) REFERENCES `addon` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'user friendly form',
  `slug` varchar(50) NOT NULL,
  `level` smallint(5) unsigned NOT NULL COMMENT '1 = category, 2 = subcategory, 9 = others',
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'visible on homepage',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `password` char(40) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addon_votes` (
  `addon_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `vote` tinyint(4) NOT NULL COMMENT '+1 / -1',
  `comment` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`addon_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `addon_votes_ibfk_1` FOREIGN KEY (`addon_id`) REFERENCES `addon` (`id`),
  CONSTRAINT `addon_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2012-04-14 17:32:34

-- added addon.short_description
ALTER TABLE `addon`
ADD `short_description` varchar(250) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'short description' AFTER `repository`,
COMMENT=''
REMOVE PARTITIONING;

-- added composer.json to version
ALTER TABLE `addon_version`
ADD `composer_json` text COLLATE 'utf8_general_ci' NULL AFTER `version`,
COMMENT=''
REMOVE PARTITIONING;

-- added vendor name to addon
ALTER TABLE `addon`
ADD `vendor_name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL AFTER `name`,
COMMENT=''
REMOVE PARTITIONING;

-- versions for addon must be unique
ALTER TABLE `addon_version`
ADD UNIQUE `addon_id_version` (`addon_id`, `version`);

-- dependecies of versions are unique
ALTER TABLE `addon_dependency`
ADD UNIQUE `addon_id_dependency_id_package_name_version` (`addon_id`, `dependency_id`, `package_name`, `version`);


ALTER TABLE `addon`
CHANGE `name` `name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'user friendly form' AFTER `id`,
CHANGE `vendor_name` `composer_name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'vendor / package' AFTER `name`;

ALTER TABLE `addon_version`
ADD `license` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'separed by comma' AFTER `version`;

ALTER TABLE `addon`
ADD `demo` varchar(500) COLLATE 'utf8_general_ci' NULL COMMENT 'url to demo' AFTER `description`;

ALTER TABLE `addon`
ADD UNIQUE (`composer_name`);
