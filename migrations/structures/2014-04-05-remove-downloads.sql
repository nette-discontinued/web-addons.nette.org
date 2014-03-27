DROP TRIGGER `addons_downloads_bi`;
DROP TABLE `addons_downloads`;
ALTER TABLE `addons_versions` DROP `downloadsCount`, DROP `installsCount`;
ALTER TABLE `addons` DROP `totalDownloadsCount`, DROP `totalInstallsCount`;
