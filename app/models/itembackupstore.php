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
    public function insert($item) {
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
        $this->insert($item);
    }
    
} //class
?>