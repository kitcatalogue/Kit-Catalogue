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


		$this->_schema->patchTable('item', array (
			'upgrades' => array (
				'type'    => 'text',
				'default' => null,
				'after'   => 'specification',
				),
			'future_upgrades' => array (
				'type'    => 'text',
				'default' => null,
				'after'   => 'upgrades',
				),
			'restrictions' => array (
				'type'    => 'varchar(250)',
				'default' => null,
				'after'   => 'availability',
				),
			'portability' => array (
				'type'    => 'varchar(250)',
				'default' => null,
				'after'   => 'access_id',
				),
			'organisation' => array (
				'type'    => 'integer',
				'default' => null,
				'after'   => 'portability',
				),
			'cost' => array (
				'type'    => 'varchar(100)',
				'default' => '',
				'after'   => 'date_of_purchase',
				),
			'replacement_cost' => array (
				'type'    => 'varchar(100)',
				'default' => null,
				'after'   => 'cost',
				),
			'end_of_life' => array (
				'type'    => 'datetime',
				'default' => null,
				'after'   => 'replacement_cost',
				),
			'maintenance' => array (
				'type'    => 'varchar(250)',
				'default' => null,
				'after'   => 'end_of_life',
				),
			'is_disposed_of' => array (
				'type'    => 'varchar(5)',
				'default' => '',
				'after'   => 'maintenance',
				),
			'date_disposed_of' => array (
				'type'    => 'datetime',
				'default' => null,
				'after'   => 'is_disposed_of',
				),
			'date_archived' => array (
				'type'    => 'datetime',
				'default' => null,
				'after'   => 'archived',
				),
			'is_parent' => array (
				'type'    => 'tinyint',
				'default' => 0,
				'after'   => 'date_archived',
				),
			'last_updated_username' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'date_updated',
				),
			'last_updated_email' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'last_updated_username',
				),
			'comments' => array (
				'type'    => 'text',
				'default' => null,
				'after'   => 'date_disposed_of',
				),
		));


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


