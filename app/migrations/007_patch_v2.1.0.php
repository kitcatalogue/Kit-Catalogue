<?php
class Patch_v2_1_0 extends Ecl_Db_Migration {


	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function down() {
		return false;
	}



	public function up() {

		$model = $this->getParam('model');
		if (!$model) { return false; }


		// Try and re-apply new field, as per v2.0.7 patch


		$this->_schema->patchTable('building', array (
			'item_count_internal' => array (
				'type'    => 'int(10) unsigned',
				'default' => '0',
				'after'   => 'longitude',
            ),
            'item_count_public' => array (
				'type'    => 'int(10) unsigned',
				'default' => '0',
				'after'   => 'item_count_internal',
			),
		));


		$test= $model->get('buildingstore')->rebuildItemCounts();
        if ($test!=true){
        die("error: $test");
        }
        
		$this->_db->replaceMulti('system_info', array (
			array (
				'name'  => 'database_version',
				'value' => '2.1.0',
				),
			array (
				'name'  => 'database_updated',
				'value' => date('c'),
				),
            ));
        //copy item table schema to a new *backup* table
        $this->_db->execute('CREATE TABLE
                            item_backup
                            LIKE
                            item;');



		return true;
	}



}


