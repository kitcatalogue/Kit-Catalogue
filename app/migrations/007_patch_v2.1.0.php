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


		$buildings= $model->get('buildingstore')->findAll();

        foreach($buildings as $building){
        // we need to iterate every building and count all items that are
        // assigned to the building;
        // Then, we need to insert the values back to db.  
            //query all items
            $res = $this->_db->query("
                SELECT COUNT(building_id)
                FROM item
                WHERE building_id=:id
                ", array('id'=> $building->id));
            //query public items only
            die(var_dump($res));
            $res_publica = $this->_db->query("
                SELECT COUNT(building_id)
                FROM item
                WHERE building_id=:id
                AND visibility=:vis
                ", array('id'=> $building->id, 
                'vis'=>KC__VISIBILITY_PUBLIC));
 
            $this->_db->update('building', array(
                'item_count_internal'=>$res, 
                'item_count_public'=>$res_publica
            ), "building_id={$building->id}");

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



		return true;
	}



}


