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
-- Definition of table `faculty`
--

CREATE TABLE IF NOT EXISTS `faculty` (
  `faculty_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL DEFAULT '',
  `url` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`faculty_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



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
-- Changes to table `site`
--

ALTER TABLE `site`
	ADD COLUMN `url` VARCHAR(250) NOT NULL DEFAULT '' AFTER `name`;



--
-- Update system_info table to reflect database changes.
--

UPDATE `system_info` SET value='1.2.0' WHERE name='database_version';

UPDATE `system_info` SET value=NOW() WHERE name='database_updated';


