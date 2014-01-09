<?php
class Migration_5 extends Ecl_Db_Migration {



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function down() {
		return false;
	}



	public function up() {

		$this->_schema->patchTable('item', array (
			'homepage_link' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'manufacturer_website',
				),
			'booking_link' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'homepage_link',
				),
		));

		return true;
	}



}


