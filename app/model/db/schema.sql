-- Adminer 3.3.3 MySQL dump

SET NAMES 'utf8';
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
ADD `shortDescription` varchar(250) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'short description' AFTER `repository`;

-- added composer.json to version
ALTER TABLE `addons_versions`
ADD `composerJson` text COLLATE 'utf8_general_ci' NULL AFTER `version`;

-- added vendor name to addon
ALTER TABLE `addons`
ADD `vendor_name` varchar(100) COLLATE 'utf8_general_ci' NOT NULL AFTER `name`;

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

-- added users.role
ALTER TABLE `users`
ADD `role` enum('admin','moderator') COLLATE 'utf8_general_ci' NULL;

-- foreign keys in addons_tags are now "cascade"
ALTER TABLE `addons_tags`
DROP FOREIGN KEY `addons_tags_ibfk_1`,
DROP FOREIGN KEY `addons_tags_ibfk_2`,
ADD FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY (`tagId`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- addons_dependencies.versionId key is now "cascade"
ALTER TABLE `addons_dependencies`
DROP FOREIGN KEY `addons_dependencies_ibfk_3`,
ADD FOREIGN KEY (`versionId`) REFERENCES `addons_versions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- addons_votes.addonId key is now "cascade"
ALTER TABLE `addons_votes`
DROP FOREIGN KEY `addons_votes_ibfk_1`,
ADD FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- removed useless index in addons.versions
ALTER TABLE `addons_versions`
DROP INDEX `addonId`;

-- added column addons.repositoryHosting
ALTER TABLE `addons`
ADD `repositoryHosting` enum('github') COLLATE 'utf8_general_ci' NULL COMMENT 'repository hosting' AFTER `repository`;

-- addons.repository length increased to 500
ALTER TABLE `addons`
CHANGE `repository` `repository` varchar(500) COLLATE 'utf8_general_ci' NULL COMMENT 'repository url (git or svn)' AFTER `userId`;

-- addon support for markdown
ALTER TABLE `addons`
ADD `descriptionFormat` enum('texy','markdown') COLLATE 'utf8_general_ci' NOT NULL COMMENT 'texy' AFTER `description`,
COMMENT=''
REMOVE PARTITIONING;

-- added downloads count
ALTER TABLE `addons`
ADD `totalDownloadsCount` int NOT NULL DEFAULT '0' COMMENT 'total times this addon was downloaded',
COMMENT='';
ALTER TABLE `addons_versions`
ADD `downloadsCount` int NOT NULL DEFAULT '0' COMMENT 'number of downloads' AFTER `distUrl`,
COMMENT='';

-- api token not only for github
ALTER TABLE `users`
ADD `apiToken` varchar(100) COLLATE 'utf8_general_ci' NULL;

-- subcategories
ALTER TABLE `tags`
ADD `parent_id` int(10) unsigned NOT NULL DEFAULT '0' AFTER `level`,
COMMENT='';

-- different column for installations count
ALTER TABLE `addons`
ADD `totalInstallsCount` int(11) NOT NULL DEFAULT '0' COMMENT 'total times this addon was installed using composer',
COMMENT='';
ALTER TABLE `addons_versions`
ADD `installsCount` int(11) NOT NULL DEFAULT '0' COMMENT 'number of installs using composer' AFTER `downloadsCount`,
COMMENT='';

-- added addons_votes.datetime
ALTER TABLE `addons_votes`
ADD `datetime` datetime NOT NULL;

-- split composerName to composerVendor and composerName
ALTER TABLE `addons`
ADD `composerVendor` varchar(50) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'composer valid package vendor name' AFTER `name`,
CHANGE `composerName` `composerName` varchar(50) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'composer valid package name' AFTER `composerVendor`,
COMMENT='';
ALTER TABLE `addons`
ADD UNIQUE `composerFullName` (`composerVendor`, `composerName`),
DROP INDEX `composerName`;

-- addon downloads statistics
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

-- use trigger for updating download statistics counters
DELIMITER ;;

CREATE TRIGGER `addons_downloads_bi` BEFORE INSERT ON `addons_downloads` FOR EACH ROW
BEGIN

	SET NEW.fake = (
		SELECT LEAST(COUNT(*), 1)
		FROM `addons_downloads`
		WHERE
			`versionId` = NEW.versionId AND
			`ipAddress` = NEW.ipAddress AND
			`fake` = 0 AND
			TIMESTAMPDIFF(HOUR, `time`, NEW.`time`) = 0
	);

	IF NEW.fake = 0 THEN
		IF NEW.type = 'download' THEN
			UPDATE `addons_versions`
			SET `downloadsCount` = `downloadsCount` + 1
			WHERE `id` = NEW.versionId;

			UPDATE `addons`
			SET `totalDownloadsCount` = `totalDownloadsCount` + 1
			WHERE `id` = (SELECT `addonId` FROM `addons_versions` WHERE `id` = NEW.versionId);

		ELSEIF NEW.type = 'install' THEN
			UPDATE `addons_versions`
			SET `installsCount` = `installsCount` + 1
			WHERE `id` = NEW.versionId;

			UPDATE `addons`
			SET `totalInstallsCount` = `totalInstallsCount` + 1
			WHERE `id` = (SELECT `addonId` FROM `addons_versions` WHERE `id` = NEW.versionId);
		END IF;
	END IF;

END;;

DELIMITER ;
