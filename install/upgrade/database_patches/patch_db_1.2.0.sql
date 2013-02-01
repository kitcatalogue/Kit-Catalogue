--
-- Kit-Catalogue Database Upgrade Script
--
-- Patch to v1.2.0
--



-- 
-- Changes to table `building`
--

ALTER TABLE `building`
	ADD COLUMN `url` VARCHAR(250) NOT NULL DEFAULT '' AFTER `name`;



-- 
-- Changes to table `department`
--

ALTER TABLE `department`
	ADD COLUMN `url` VARCHAR(250) NOT NULL DEFAULT '' AFTER `name`;



--
-- Changes to table `item`
--

ALTER TABLE `item`
	ADD COLUMN `ou_id` INTEGER UNSIGNED DEFAULT 0 AFTER `portability`;



-- 
-- Definition of table `log_enquiry`
--

CREATE TABLE IF NOT EXISTS `log_enquiry` (
  `log_enquiry_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_enquiry` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_name` varchar(250) NOT NULL DEFAULT '',
  `user_name` varchar(250) NOT NULL DEFAULT '',
  `user_email` varchar(250) NOT NULL DEFAULT '',
  `user_phone` varchar(20) NOT NULL DEFAULT '',
  `user_org` varchar(250) NOT NULL DEFAULT '',
  `user_role` varchar(250) NOT NULL DEFAULT '',
  `user_deadline` varchar(50) NOT NULL DEFAULT '',
  `enquiry_type` varchar(15) NOT NULL DEFAULT '',
  `enquiry_text` MEDIUMTEXT,
  PRIMARY KEY (`log_enquiry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- 
-- Changes to table `organisation`
--

ALTER TABLE `organisation`
	ADD COLUMN `url` VARCHAR(250) NOT NULL DEFAULT '' AFTER `name`;



-- 
-- Changes to table `ou`
--

CREATE TABLE  `ou` (
  `ou_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `url` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ou_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `ou` (ou_id, name, url)
VALUES (1, 'Catalogue', '');



-- 
-- Changes to table `ou_tree`
--

CREATE TABLE  `ou_tree` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tree_left` int(10) unsigned NOT NULL,
  `tree_right` int(10) unsigned NOT NULL,
  `tree_level` int(10) unsigned NOT NULL,
  `name` varchar(250) DEFAULT '',
  `ref` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tree_left` (`tree_left`),
  KEY `tree_right` (`tree_right`),
  KEY `tree_level` (`tree_level`),
  KEY `ref` (`ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `ou_tree` (id, tree_left, tree_right, tree_level, name, ref)
VALUES (1, 1, 2, 0, 'Catalogue', null);



-- 
-- Changes to table `ou_tree_label`
--

CREATE TABLE `ou_tree_label` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- 
-- Changes to table `site`
--

ALTER TABLE `site`
	ADD COLUMN `url` VARCHAR(250) NOT NULL DEFAULT '' AFTER `name`;



--
-- Update system_info table to reflect database changes.
--

UPDATE `system_info` SET value='1.2.0' WHERE name='database_version';

UPDATE `system_info` SET value=NOW() WHERE name='database_updated';


