--
-- Kit-Catalogue Database Upgrade Script
--
-- Patch to v1.1.0
--


-- 
-- Changes to table `item`
--

ALTER TABLE `item` DROP INDEX `textsearch`;



ALTER TABLE `item`
	ADD COLUMN `upgrades` TEXT DEFAULT NULL AFTER `specification`,
	ADD COLUMN `future_upgrades` TEXT DEFAULT NULL AFTER `upgrades`,
	ADD COLUMN `restrictions` VARCHAR(250) DEFAULT NULL AFTER `availability`,
	ADD COLUMN `portability` VARCHAR(250) DEFAULT '' AFTER `access_id`,
	ADD COLUMN `organisation` INTEGER UNSIGNED DEFAULT NULL AFTER `portability`,
	ADD COLUMN `cost` VARCHAR(100) DEFAULT '' AFTER `date_of_purchase`,
	ADD COLUMN `replacement_cost` VARCHAR(100) DEFAULT '' AFTER `cost`,
	ADD COLUMN `end_of_life` DATETIME DEFAULT NULL AFTER `replacement_cost`,
	ADD COLUMN `maintenance` VARCHAR(250) DEFAULT NULL AFTER `end_of_life`,
	ADD COLUMN `is_disposed_of` VARCHAR(5) DEFAULT '' AFTER `maintenance`,
	ADD COLUMN `date_disposed_of` DATETIME DEFAULT NULL AFTER `is_disposed_of`,
	ADD COLUMN `date_archived` DATETIME DEFAULT NULL AFTER `archived`,
	ADD COLUMN `is_parent` TINYINT UNSIGNED DEFAULT '0' AFTER `date_archived`,
	ADD COLUMN `last_updated_username` VARCHAR(250) DEFAULT '' AFTER `date_updated`,
	ADD COLUMN `last_updated_email` VARCHAR(250) DEFAULT '' AFTER `last_updated_username`,
	ADD COLUMN `comments` TEXT DEFAULT NULL AFTER `date_disposed_of`;



ALTER TABLE `item`
	ADD INDEX `is_disposed_of`(`is_disposed_of`),
	ADD INDEX `is_parent`(`is_parent`);



-- 
-- Definition of table `item_child`
--

CREATE TABLE IF NOT EXISTS `item_child` (
  `item_id` int(10) unsigned NOT NULL,
  `child_item_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`item_id`,`child_item_id`),
  KEY `child_item_id` (`child_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- 
-- Definition of table `log_item_update`
--

CREATE TABLE IF NOT EXISTS `log_item_update` (
  `log_item_update_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_updated` DATETIME NOT NULL,
  `item_id` INTEGER UNSIGNED NOT NULL,
  `username` VARCHAR(250),
  `email` VARCHAR(250),
  PRIMARY KEY (`log_item_update_id`),
  INDEX `item_id`(`item_id`),
  INDEX `date_updated`(`date_updated`),
  INDEX `email`(`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- 
-- Definition of table `organisation`
--

CREATE TABLE `organisation` (
  `organisation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`organisation_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



--
-- Update system_info table to reflect database changes.
--

UPDATE `system_info` SET value='1.1.0' WHERE name='database_version';

UPDATE `system_info` SET value=NOW() WHERE name='database_updated';


