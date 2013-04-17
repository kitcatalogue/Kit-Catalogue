<?php
class Patch_v1_1_0 extends Ecl_Db_Migration {


	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function down() {
		return false;
	}



	public function up() {

		// Changes item table

		try {
			$this->_db->execute("
				ALTER TABLE `item` DROP INDEX `textsearch`;
			");
		} catch (\Exception $e) {
			// Do nothing
		}

		$this->_db->execute("
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
		");

		try {
			$this->_db->execute("
				ALTER TABLE `item`
					ADD INDEX `is_disposed_of`(`is_disposed_of`),
					ADD INDEX `is_parent`(`is_parent`);
			");
		} catch (\Exception $e) {
			// Do nothing
		}


		// Create item_child table

		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS `item_child` (
				`item_id` int(10) unsigned NOT NULL,
				`child_item_id` int(10) unsigned NOT NULL,
				PRIMARY KEY (`item_id`,`child_item_id`),
				KEY `child_item_id` (`child_item_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");



		// create log_item_update table

		$this->_db->execute("
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
		");



		// create organisation table

		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS `organisation` (
				`organisation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`name` varchar(250) NOT NULL DEFAULT '',
				PRIMARY KEY (`organisation_id`),
				UNIQUE KEY `name` (`name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");



		// Set system information settings

		$this->_db->replaceMulti('system_info', array (
			array (
				'name'  => 'database_version',
				'value' => '1.1.0',
			),
			array (
				'name'  => 'database_updated',
				'value' => date('c'),
			),
		));



		return true;
	}



}


