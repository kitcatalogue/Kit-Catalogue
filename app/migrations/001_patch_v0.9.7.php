<?php
class Patch_v0_9_7 extends Ecl_Db_Migration {


	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function down() {
		return false;
	}



	public function up() {

		// Create system_info table

		$this->_schema->createTable('system_info', array (
			'name' => array (
				'type'    => 'varchar(25)',
				'null'    => false,
				'default' => '',
				'primary' => true,
				),
			'value' => array (
				'type'    => 'varchar(50)',
				'null'    => false,
				'default' => '' ,
				),
		));

		$this->_db->replaceMulti('system_info', array (
			array (
				'name'  => 'database_version',
				'value' => '',
			),
			array (
				'name'  => 'database_updated',
				'value' => '',
			),
		));



		// Create log_view table

		$this->_schema->createTable('log_view', array (
			'log_view_id' => array (
				'type'           => 'int(10) unsigned',
				'null'           => false,
				'auto_increment' => true,
				'primary'        => true,
				),
			'date_view' => array (
				'type'    => 'datetime',
				'null'    => false,
				'default' => '0000-00-00 00:00:00',
			),
			'user_id' => array (
				'type'    => 'varchar(250)',
				'null'    => false,
				'default' => '',
			),
			'username' => array (
				'type'    => 'varchar(250)',
				'null'    => false,
				'default' => '',
			),
			'item_id' => array (
				'type'    => 'int(10) unsigned',
				'null'    => false,
				'default' => 0,
			),
		));



		// Allow CPV codes configuration (i.e. which are visible/active).

		$this->_schema->patchTable('cpv_code', array (
			'visible' => array (
					'type'    => 'tinyint(3) unsigned',
					'default' => 1,
					'after'   => 'jumpable',
				),
		));



		// Changes to system_authorisation (Set to UTF8 and apply max key-size fix)

		$this->_db->execute("
			ALTER TABLE `system_authorisation`
				MODIFY COLUMN `agent` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				MODIFY COLUMN `item` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				MODIFY COLUMN `auth` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				CHARACTER SET utf8 COLLATE utf8_general_ci;
		");



		// Set system information settings

		$this->_db->replaceMulti('system_info', array (
			array (
				'name'  => 'database_version',
				'value' => '0.9.7',
			),
			array (
				'name'  => 'database_updated',
				'value' => date('c'),
			),
		));


		return true;
	}



}


