CREATE TABLE `cron` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `server` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `start` datetime NOT NULL,
  `stop` datetime DEFAULT NULL,
  `time` int(10) unsigned DEFAULT NULL,
  `memory` float unsigned DEFAULT NULL,
  `result` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
