<?php



//include_once('itemfile.php');
//include_once('item.php');
include_once('itemstore.php');



/**
 * Item Backup store class
 * 
 * @version 0.1.0
 */
class Itembackupstore extends Itemstore {
    
    public function __construct($model) {
       // BEWARE future self:
       // I don't understand why this works...
       $this->_db = $model;
    } // /->__construct()
    /**
     * All other functions should be inherited  from *itemstore.php*
     * The only difference here is the table name in _db->insert()
     */
    public function insert_backup($item) {
        $binds = $this->convertObjectToRow($item);
        unset($binds['item_id']); // Don't insert the id, we want a new one
        if ($binds['short_description'] == null) { // If we have full description, create short from full:
            if ($binds['full_description'] != null) {
                $binds['short_description'] = substr($binds['full_description'], 0, 245) . '...';
            }
        }
        $binds['date_added']   = $this->_db->formatDate(time());
        $binds['date_updated'] = $this->_db->formatDate(time());
        
        $new_id = $this->_db->insert('item_backup', $binds);
        
        return ($new_id > 0) ? $new_id : null;
    } // /method
    /* This is a wrapper around the original find() function to make it less 
     * confusing
     *
     */
    public function find_live($item_id){
    return $this->find($item_id);
    }
    
    public function backup($item_id) {
        // we are using the find function from *itemstore.php*!
        $item = $this->find_live($item_id);
        $this->insert_backup($item);
    }
    public function findAllDeleted(){
    return $this->_findDeleted();
    }
    public function findDeletedByID($id){
    	$sql__id = $this->_db->prepareValue($id);
      return $this->_findDeleted("item_id=$id");
    }
    	public function deleteBackup($id) {
    // Delete the item itself
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('item_backup', "item_id=$id");

		return ($affected_count>0);
	}// /method
    /**
	 * Find all items using the given where clause.
	 *
	 * @param  string  $where  (optional) The where clause to use.
	 * @param  string  $order_by  (optional) The order-by clause to use.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
   protected function _findDeleted($where = '', $order_by = null){
   	if (null === $order_by) {
			$order_by = "
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
			";
		}


		if (empty($where)) {
			return $this->_db->newRecordset("
				SELECT *
				FROM item_backup
				ORDER BY $order_by
			", null, array($this, 'convertRowToObject'));
		} else {
			return $this->_db->newRecordset("
				SELECT *
				FROM item_backup
				WHERE $where
				ORDER BY $order_by
			", null, array($this, 'convertRowToObject'));
		}
   
   } 
} //class
?>