-- Adminer 3.3.3 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `addons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `repository` varchar(250) NOT NULL COMMENT 'repository url (git or svn)',
  `description` text NOT NULL COMMENT 'in Texy! syntax',
  `updated_at` datetime NOT NULL COMMENT 'time of last update (of anything)',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `addons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addons_dependencies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addon_id` int(10) unsigned NOT NULL,
  `dependency_id` int(10) unsigned DEFAULT NULL,
  `package_name` varchar(100) DEFAULT NULL,
  `version` varchar(20) NOT NULL,
  `type` enum('require','suggest','provide','replace','conflict','recommend') NOT NULL DEFAULT 'require',
  PRIMARY KEY (`id`),
  KEY `addonId` (`addon_id`),
  KEY `dependencyId` (`dependency_id`),
  CONSTRAINT `addons_dependencies_ibfk_1` FOREIGN KEY (`addon_id`) REFERENCES `addons_versions` (`id`),
  CONSTRAINT `addons_dependencies_ibfk_2` FOREIGN KEY (`dependency_id`) REFERENCES `addons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addons_tags` (
  `addon_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`addon_id`,`tag_id`),
  KEY `tagid` (`tag_id`),
  CONSTRAINT `addons_tags_ibfk_1` FOREIGN KEY (`addon_id`) REFERENCES `addons` (`id`),
  CONSTRAINT `addons_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addons_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addon_id` int(10) unsigned NOT NULL,
  `version` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addonId` (`addon_id`),
  CONSTRAINT `addons_versions_ibfk_1` FOREIGN KEY (`addon_id`) REFERENCES `addons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'user friendly form',
  `slug` varchar(50) NOT NULL,
  `level` smallint(5) unsigned NOT NULL COMMENT '1 = category, 2 = subcategory, 9 = others',
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'visible on homepage',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `password` char(40) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addons_votes` (
  `addon_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `vote` tinyint(4) NOT NULL COMMENT '+1 / -1',
  `comment` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`addon_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `addons_votes_ibfk_1` FOREIGN KEY (`addon_id`) REFERENCES `addons` (`id`),
  CONSTRAINT `addons_votes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2012-04-14 17:32:34

-- added addon.short_description
ALTER TABLE `addons`
ADD `short_description` varchar(250) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'short description' AFTER `repository`,
COMMENT=''
REMOVE PARTITIONING;

-- added composer.json to version
ALTER TABLE `addons_versions`
ADD `composer_json` text COLLATE 'utf8_general_ci' NULL AFTER `version`,
COMMENT=''
REMOVE PARTITIONING;

-- added vendor name to addon
ALTER TABLE `addons`
ADD `vendor_name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL AFTER `name`,
COMMENT=''
REMOVE PARTITIONING;

-- versions for addon must be unique
ALTER TABLE `addons_versions`
ADD UNIQUE `addon_id_version` (`addon_id`, `version`);

-- dependecies of versions are unique
ALTER TABLE `addons_dependencies`
ADD UNIQUE `addon_id_dependency_id_package_name_version` (`addon_id`, `dependency_id`, `package_name`, `version`);


ALTER TABLE `addons`
CHANGE `name` `name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'user friendly form' AFTER `id`,
CHANGE `vendor_name` `composer_name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'vendor / package' AFTER `name`;

ALTER TABLE `addons_versions`
ADD `license` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'separed by comma' AFTER `version`;

ALTER TABLE `addons`
ADD `demo` varchar(500) COLLATE 'utf8_general_ci' NULL COMMENT 'url to demo' AFTER `description`;

ALTER TABLE `addons`
ADD UNIQUE (`composer_name`);

-- nullable repository
ALTER TABLE `addons`
	ALTER `repository` DROP DEFAULT;
ALTER TABLE `addons`
	CHANGE COLUMN `repository` `repository` VARCHAR(250) NULL COMMENT 'repository url (git or svn)' AFTER `user_id`;


-- added filename for version
ALTER TABLE `addons_versions`
	ADD COLUMN `filename` VARCHAR(250) NULL COMMENT 'filename on local filesystem' AFTER `composer_json`;
