<?php
class Patch_v0_9_8 extends Ecl_Db_Migration {


	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function down() {
		return false;
	}



	public function up() {

		// Create homepageblock table

		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS `homepageblock` (
				`block_id` int(10) unsigned NOT NULL auto_increment,
				`block_name` varchar(250) NOT NULL DEFAULT '',
				`block_to_find` varchar(250) NOT NULL DEFAULT '',
				`block_enabled` tinyint(3) unsigned DEFAULT '1',
				`visibility` tinyint(3) unsigned DEFAULT '1',
				PRIMARY KEY (`block_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");



		// Alter building table to include latitude and longditude

		$this->_schema->patchTable('building', array(
			'latitude' => array (
				'type' => 'varchar(15)',
				),
			'longitude' => array (
				'type' => 'varchar(15)',
			),
		));



		// Alter item table to add extra fields
		$this->_schema->dropIndex('item', 'textsearch');

		$this->_db->execute("
			ALTER TABLE `item`
				ADD COLUMN `title` VARCHAR(250) DEFAULT '' AFTER item_id,
				CHANGE `manufacturer` `manufacturer` VARCHAR(100) DEFAULT '',
				CHANGE `model` `model` VARCHAR(100) DEFAULT '',
				CHANGE `short_description` `short_description` VARCHAR(250) DEFAULT '',
				CHANGE `full_description` `full_description` TEXT DEFAULT '',
				CHANGE `specification` `specification` TEXT DEFAULT '',
				CHANGE `acronym` `acronym` VARCHAR(15) DEFAULT '',
				CHANGE `keywords` `keywords` VARCHAR(250) DEFAULT '',
				CHANGE `technique` `technique` VARCHAR(250) DEFAULT '' AFTER `keywords`,
				CHANGE `availability` `availability` VARCHAR(250) DEFAULT '',
				CHANGE `department_id` `department_id` INT(10) UNSIGNED DEFAULT NULL,
				CHANGE `usergroup` `usergroup` VARCHAR(250) DEFAULT '',
				CHANGE `access_id` `access_id` INT(10) UNSIGNED DEFAULT NULL,
				CHANGE `site_id` `site_id` INT(10) UNSIGNED DEFAULT NULL,
				CHANGE `building_id` `building_id` INT(10) UNSIGNED DEFAULT NULL,
				CHANGE `room` `room` VARCHAR(250) NOT NULL DEFAULT '' AFTER `building_id`,
				ADD COLUMN `contact_1_name` VARCHAR(250) DEFAULT '' AFTER `room`,
				CHANGE `contact_email` `contact_1_email` VARCHAR(250) DEFAULT '' AFTER `contact_1_name`,
				ADD COLUMN `contact_2_name` VARCHAR(250) DEFAULT '' AFTER `contact_1_email`,
				ADD COLUMN `contact_2_email` VARCHAR(250) DEFAULT '' AFTER `contact_2_name`,
				CHANGE `visibility` `visibility` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
				CHANGE `image` `image` VARCHAR(250) NOT NULL DEFAULT '',
				CHANGE `manufacturer_website` `manufacturer_website` VARCHAR(250) DEFAULT '',
				CHANGE `copyright_notice` `copyright_notice` VARCHAR(250) DEFAULT '',
				CHANGE `date_added` `date_added` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				CHANGE `date_updated` `date_updated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				ADD COLUMN `training_required` TINYINT(3) DEFAULT NULL,
				ADD COLUMN `training_provided` TINYINT(3) DEFAULT NULL,
				ADD COLUMN `quantity` INT(5) DEFAULT '1',
				ADD COLUMN `quantity_detail` VARCHAR(250) DEFAULT '',
				ADD COLUMN `PAT` DATETIME DEFAULT NULL,
				ADD COLUMN `calibrated` VARCHAR(4) DEFAULT '',
				ADD COLUMN `last_calibration_date` DATETIME DEFAULT NULL,
				ADD COLUMN `next_calibration_date` DATETIME DEFAULT NULL,
				ADD COLUMN `asset_no` VARCHAR(50) DEFAULT NULL,
				ADD COLUMN `finance_id` VARCHAR(50) DEFAULT NULL,
				ADD COLUMN `serial_no` VARCHAR(50) DEFAULT NULL,
				ADD COLUMN `year_of_manufacture` VARCHAR(4) DEFAULT NULL,
				ADD COLUMN `supplier_id` INT DEFAULT NULL,
				ADD COLUMN `date_of_purchase` DATETIME DEFAULT NULL,
				ADD COLUMN `archived` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0';
		");

		$this->_db->execute("
			ALTER TABLE `item`
				ADD KEY PAT (PAT),
				ADD KEY archived (archived),
				ADD KEY asset_no (asset_no),
				ADD KEY finance_id (finance_id),
				ADD KEY serial_no (serial_no),
				ADD KEY supplier_id (supplier_id),
				ADD FULLTEXT KEY `textsearch` (`title`,`manufacturer`,`model`,`full_description`,`acronym`,`technique`,`keywords`);
		");



		// Create supplier table

		$this->_db->execute("
			CREATE TABLE `supplier` (
				`supplier_id` INT NOT NULL AUTO_INCREMENT,
				`name` VARCHAR(250) NOT NULL,
				`item_count_internal` int(10) unsigned NOT NULL DEFAULT '0',
				`item_count_public` int(10) unsigned NOT NULL DEFAULT '0',
				PRIMARY KEY (`supplier_id`),
				UNIQUE KEY `name` (`name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");



		// Set system information settings

		$this->_db->replaceMulti('system_info', array (
			array (
				'name'  => 'database_version',
				'value' => '0.9.8',
			),
			array (
				'name'  => 'database_updated',
				'value' => date('c'),
			),
		));



		return true;
	}



}


