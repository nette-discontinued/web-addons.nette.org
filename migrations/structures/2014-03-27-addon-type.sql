ALTER TABLE `addons` ADD `type` enum('composer','download') NOT NULL;
UPDATE `addons` SET `type`='composer' WHERE `repositoryHosting` IS NOT NULL;
UPDATE `addons` SET `type`='download' WHERE `repositoryHosting` IS NULL;
