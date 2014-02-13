-- Adminer 3.6.1 MySQL dump

SET NAMES utf8;

DELIMITER ;;

CREATE TRIGGER `addons_downloads_bi` BEFORE INSERT ON `addons_downloads` FOR EACH ROW
BEGIN

	SET NEW.fake = (
		SELECT LEAST(COUNT(*), 1)
		FROM `addons_downloads`
		WHERE
			`type` = NEW.type AND
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

-- 2012-10-24 01:42:03
