-- Adminer 3.3.3 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `addons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `repository` varchar(250) NOT NULL COMMENT 'repository url (git or svn)',
  `description` text NOT NULL COMMENT 'in Texy! syntax',
  `updatedAt` datetime NOT NULL COMMENT 'time of last update (of anything)',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `addons_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addons_dependencies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addonId` int(10) unsigned NOT NULL,
  `dependencyId` int(10) unsigned DEFAULT NULL,
  `packageName` varchar(100) DEFAULT NULL,
  `version` varchar(20) NOT NULL,
  `type` enum('require','suggest','provide','replace','conflict','recommend') NOT NULL DEFAULT 'require',
  PRIMARY KEY (`id`),
  KEY `addonId` (`addonId`),
  KEY `dependencyId` (`dependencyId`),
  CONSTRAINT `addons_dependencies_ibfk_1` FOREIGN KEY (`addonId`) REFERENCES `addons_versions` (`id`),
  CONSTRAINT `addons_dependencies_ibfk_2` FOREIGN KEY (`dependencyId`) REFERENCES `addons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addons_tags` (
  `addonId` int(10) unsigned NOT NULL,
  `tagId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`addonId`,`tagId`),
  KEY `tagid` (`tagId`),
  CONSTRAINT `addons_tags_ibfk_1` FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`),
  CONSTRAINT `addons_tags_ibfk_2` FOREIGN KEY (`tagId`) REFERENCES `tags` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `addons_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `addonId` int(10) unsigned NOT NULL,
  `version` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addonId` (`addonId`),
  CONSTRAINT `addons_versions_ibfk_1` FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`)
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
  `addonId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `vote` tinyint(4) NOT NULL COMMENT '+1 / -1',
  `comment` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`addonId`,`userId`),
  KEY `userId` (`userId`),
  CONSTRAINT `addons_votes_ibfk_1` FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`),
  CONSTRAINT `addons_votes_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2012-04-14 17:32:34

-- added addon.shortDescription
ALTER TABLE `addons`
ADD `shortDescription` varchar(250) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'short description' AFTER `repository`,
COMMENT=''
REMOVE PARTITIONING;

-- added composer.json to version
ALTER TABLE `addons_versions`
ADD `composerJson` text COLLATE 'utf8_general_ci' NULL AFTER `version`,
COMMENT=''
REMOVE PARTITIONING;

-- added vendor name to addon
ALTER TABLE `addons`
ADD `vendor_name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL AFTER `name`,
COMMENT=''
REMOVE PARTITIONING;

-- versions for addon must be unique
ALTER TABLE `addons_versions`
ADD UNIQUE `addonId_version` (`addonId`, `version`);

-- dependecies of versions are unique
ALTER TABLE `addons_dependencies`
ADD UNIQUE `addonId_dependencyId_packageName_version` (`addonId`, `dependencyId`, `packageName`, `version`);


ALTER TABLE `addons`
CHANGE `name` `name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'user friendly form' AFTER `id`,
CHANGE `vendor_name` `composerName` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'vendor / package' AFTER `name`;

ALTER TABLE `addons_versions`
ADD `license` varchar(100) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'separed by comma' AFTER `version`;

ALTER TABLE `addons`
ADD `demo` varchar(500) COLLATE 'utf8_general_ci' NULL COMMENT 'url to demo' AFTER `description`;

ALTER TABLE `addons`
ADD UNIQUE (`composerName`);

-- nullable repository
ALTER TABLE `addons`
	ALTER `repository` DROP DEFAULT;
ALTER TABLE `addons`
	CHANGE COLUMN `repository` `repository` VARCHAR(250) NULL COMMENT 'repository url (git or svn)' AFTER `userId`;


-- added filename for version
ALTER TABLE `addons_versions`
	ADD COLUMN `filename` VARCHAR(250) NULL COMMENT 'filename on local filesystem' AFTER `composerJson`;

-- addons_versions.composerJson can no longer be NULL
ALTER TABLE `addons_versions`
CHANGE `composerJson` `composerJson` text COLLATE 'utf8_general_ci' NOT NULL AFTER `license`;

-- addons_versions.filename replaced by link
ALTER TABLE `addons_versions`
CHANGE `filename` `link` varchar(250) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'download link' AFTER `composerJson`;

-- added default license for addon
ALTER TABLE `addons`
ADD `defaultLicense` varchar(100) NOT NULL COMMENT 'used as default for new versions' AFTER `updatedAt`;

-- packageID if required for each dependency
ALTER TABLE `addons_dependencies`
CHANGE `packageName` `packageName` varchar(100) COLLATE 'utf8_general_ci' NOT NULL AFTER `dependencyId`;

-- updated unique key for dependencies and removed useless addonId key
ALTER TABLE `addons_dependencies`
ADD UNIQUE `addonId_packageName_version` (`addonId`, `packageName`, `version`),
DROP INDEX `addonId_dependencyId_packageName_version`,
DROP INDEX `addonId`;

-- tags.slug must be unique
ALTER TABLE `tags`
ADD UNIQUE (`slug`);

-- added "source fields" to addons_versions
ALTER TABLE `addons_versions`
ADD `sourceType` enum('git','hg','svn') COLLATE 'utf8_general_ci' NULL COMMENT 'VCS type' AFTER `link`,
ADD `sourceUrl` varchar(500) COLLATE 'utf8_general_ci' NULL COMMENT 'repository URL, usually the same as addon.repository' AFTER `sourceType`,
ADD `sourceReference` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Git, Mercurial or SVN reference (usually branch or tag name)' AFTER `sourceUrl`;

-- changed columns order (composerJson moved to end)
ALTER TABLE `addons_versions`
CHANGE `link` `link` varchar(250) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'download link' AFTER `license`,
CHANGE `sourceType` `sourceType` enum('git','hg','svn') COLLATE 'utf8_general_ci' NULL COMMENT 'VCS type' AFTER `link`,
CHANGE `sourceUrl` `sourceUrl` varchar(500) COLLATE 'utf8_general_ci' NULL COMMENT 'repository URL, usually the same as addon.repository' AFTER `sourceType`,
CHANGE `sourceReference` `sourceReference` varchar(100) COLLATE 'utf8_general_ci' NULL COMMENT 'Git, Mercurial or SVN reference (usually branch or tag name)' AFTER `sourceUrl`,
CHANGE `composerJson` `composerJson` text COLLATE 'utf8_general_ci' NOT NULL COMMENT 'composer.json (with source & dist) cache' AFTER `sourceReference`;

-- addons_versions.link replaced by distType & distUrl
ALTER TABLE `addons_versions`
ADD `distType` enum('zip','tarball') COLLATE 'utf8_general_ci' NOT NULL COMMENT 'type of distribution archive' AFTER `license`,
ADD `distUrl` varchar(500) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'link to distribution archive' AFTER `distType`,
DROP `link`;

-- addons_dependencies.addonId renamed to versionId and changed the unique key
ALTER TABLE `addons_dependencies`
DROP FOREIGN KEY `addons_dependencies_ibfk_1`;
ALTER TABLE `addons_dependencies`
CHANGE `addonId` `versionId` int(10) unsigned NOT NULL AFTER `id`,
DROP INDEX `addonId_packageName_version`,
ADD UNIQUE `versionId_type_packageName` (`versionId`, `type`, `packageName`),
ADD FOREIGN KEY (`versionId`) REFERENCES `addons_versions` (`id`);

-- increased addons_versions.version length from 20 to 100 chars, because of versions such as "dev-jm-nette-extension"
ALTER TABLE `addons_versions`
CHANGE `version` `version` varchar(100) COLLATE 'utf8_general_ci' NOT NULL AFTER `addonId`;
