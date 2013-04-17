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

		$this->_schema->patchTable('item', array(
			'title' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'item_id',
				),
			'manufacturer' => array (
				'type'    => 'varchar(100)',
				'default' => '',
				),
			'model' => array (
				'type'    => 'varchar(100)',
				'default' => '',
				),
			'short_description' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				),
			'full_description' => array (
				'type'    => 'TEXT',
				'default' => '',
				),
			'specification' => array (
				'type'    => 'TEXT',
				'default' => '',
				),
			'acronym' => array (
				'type'    => 'varchar(15)',
				'default' => '',
				),
			'keywords' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				),
			'technique' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'keywords',
				),
			'availability' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				),
			'department_id' => array (
				'type'    => 'int(10) unsigned',
				'default' => null,
				),
			'usergroup' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				),
			'access_id' => array (
				'type'    => 'int(10) unsigned',
				'default' => null,
				),
			'site_id' => array (
				'type'    => 'int(10) unsigned',
				'default' => null,
				),
			'building_id' => array (
				'type'    => 'int(10) unsigned',
				'default' => null,
				),
			'room' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'null'    => false,
				'after'   => 'building_id',
				),
			'contact_1_name' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'room',
				),
			'contact_email' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'rename'  => 'contact_1_email',
				'after'   => 'contact_1_name',
				),
			'contact_2_name' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'contact_1_email',
				),
			'contact_2_email' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'contact_2_name',
				),
			'visibility' => array (
				'type'    => 'tinyint(3)',
				'default' => 0,
				),
			'image' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'null'    => false,
				),
			'manufacturer_website' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				),
			'copyright_notice' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				),
			'date_added' => array (
				'type'    => 'datetime',
				'null'    => false,
				'default' => '0000-00-00 00:00:00',
				),
			'date_updated' => array (
				'type'    => 'datetime',
				'null'    => false,
				'default' => '0000-00-00 00:00:00',
				),
			'training_required' => array (
				'type'    => 'tinyint(3)',
				'default' => null,
				),
			'training_provided' => array (
				'type'    => 'tinyint(3)',
				'default' => null,
				),
			'quantity' => array (
				'type'    => 'int(5)',
				'default' => 1,
				),
			'quantity_detail' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				),
			'PAT' => array (
				'type'    => 'datetime',
				'default' => null,
				),
			'calibrated' => array (
				'type'    => 'varchar(4)',
				'default' => '',
				),
			'last_calibration_date' => array (
				'type'    => 'datetime',
				'default' => null,
				),
			'next_calibration_date' => array (
				'type'    => 'datetime',
				'default' => null,
				),
			'asset_no' => array (
				'type'    => 'varchar(50)',
				'default' => null,
				),
			'finance_id' => array (
				'type'    => 'varchar(50)',
				'default' => null,
				),
			'serial_no' => array (
				'type'    => 'varchar(50)',
				'default' => null,
				),
			'year_of_manufacture' => array (
				'type'    => 'varchar(4)',
				'default' => null,
				),
			'supplier_id' => array (
				'type'    => 'int(10)',
				'default' => null,
				),
			'date_of_purchase' => array (
				'type'    => 'datetime',
				'default' => null,
				),
			'archived' => array (
				'type'    => 'tinyint(3)',
				'default' => 0,
				),
		));

		try {
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
		} catch (\Exception $e) {
			// Do nothing
		}


		// Create supplier table

		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS `supplier` (
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


