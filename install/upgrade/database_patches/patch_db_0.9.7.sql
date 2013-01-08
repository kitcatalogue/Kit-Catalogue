--
-- Kit-Catalogue Database Upgrade Script
--
-- Patch to v0.9.7
--



--
-- Setup table to store system and database information.
--

CREATE TABLE IF NOT EXISTS `system_info` (
  `name` varchar(25) NOT NULL default '',
  `value` varchar(50) NOT NULL default '',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT IGNORE INTO `system_info` (name, value) VALUES
  ('database_version', '') ,
  ('database_updated', '');



--
-- Setup table to store item view logs.
--

CREATE TABLE IF NOT EXISTS `log_view` (
	`log_view_id` int(10) unsigned NOT NULL auto_increment ,
	`date_view` datetime NOT NULL default '0000-00-00 00:00:00' ,
	`user_id` varchar(250) NOT NULL default '' ,
	`username` varchar(250) NOT NULL default '' ,
	`item_id` int(10) unsigned NOT NULL default '0' ,
	PRIMARY KEY (`log_view_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Setup support for CPV codes configuration (i.e. which are visible/active).
--

ALTER TABLE `cpv_code` ADD COLUMN `visible` TINYINT(3) UNSIGNED DEFAULT 1 AFTER `jumpable`;


--
-- Changes to system_authorisation (Set to UTF8 and apply max key-size fix)
--

ALTER TABLE `system_authorisation`
 MODIFY COLUMN `agent` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
 MODIFY COLUMN `item` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
 MODIFY COLUMN `auth` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
 CHARACTER SET utf8 COLLATE utf8_general_ci;



--
-- Update system_info table to reflect database changes.
--

UPDATE `system_info` SET value='0.9.7' WHERE name='database_version';

UPDATE `system_info` SET value=NOW() WHERE name='database_updated';


