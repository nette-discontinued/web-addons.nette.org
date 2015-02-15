ALTER TABLE `addons_dependencies`
CHANGE `version` `version` varchar(100) COLLATE 'utf8_general_ci' NOT NULL AFTER `packageName`;
