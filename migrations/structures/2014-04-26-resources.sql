CREATE TABLE `addons_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addonId` int(10) unsigned NOT NULL,
  `type` varchar(32) NOT NULL,
  `resource` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addonId` (`addonId`),
  KEY `type` (`type`),
  CONSTRAINT `addons_resources_ibfk_1` FOREIGN KEY (`addonId`) REFERENCES `addons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
