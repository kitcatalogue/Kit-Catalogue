<?php
class Patch_v2_0_7 extends Ecl_Db_Migration {


	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function down() {
		return false;
	}



	public function up() {

		$model = $this->getParam('model');
		if (!$model) { return false; }

		$lang = $model->get('lang');



		$this->_schema->patchTable('category', array (
			'external_schema_uri' => array (
				'type'    => 'varchar(250)',
				'default' => '',
				'after'   => 'name',
				),
		));



		$this->_db->replaceMulti('system_info', array (
			array (
				'name'  => 'database_version',
				'value' => '2.0.7',
				),
			array (
				'name'  => 'database_updated',
				'value' => date('c'),
				),
		));



		return true;
	}



}


