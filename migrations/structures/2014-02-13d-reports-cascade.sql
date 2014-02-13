ALTER TABLE `addons_reports`
DROP FOREIGN KEY `addons_reports_ibfk_2`,
ADD FOREIGN KEY `addons_reports_ibfk_2` (`addonId`) REFERENCES `addons` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;
