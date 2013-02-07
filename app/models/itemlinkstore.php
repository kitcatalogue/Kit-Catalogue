<?php



include_once('itemlink.php');



/**
 * Item Link Store class
 *
 * @version 1.0.0
 */
class Itemlinkstore {

	// Private Properties
	protected $_db = null;



	/**
	 * Constructor
	 */
	public function __construct($database) {
		$this->_db = $database;
	}// /->__construct()



	/* ----------------------------------------------------------------------
	 * Public Methods
	 */



	/**
	 * Convert the domain object to a database row
	 *
	 * @param  object  $object  The object.
	 *
	 * @return  array  The row representing the object.
	 */
	public function convertObjectToRow($object) {
		return array (
			'id' => $object->id ,

			'item_id' => $object->item_id ,

			'name'      => $object->name ,
			'url'       => $object->url ,
			'file_type' => $object->type ,
		);
	}// /method



	/**
	 * Convert a database row to a domain object
	 *
	 * @param  array  $row  The database row.
	 *
	 * @return  object  The object the row represents.
	 */
	public function convertRowToObject($row) {
		$object = $this->newItemlink();

		$object->id = $row['id'];

		$object->item_id = $row['item_id'];

		$object->name = $row['name'];
		$object->url = $row['url'];
		$object->type = $row['file_type'];

		return $object;
	}// /method



	/**
	 * Delete an item link.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('item_link', "id=$id");

		return ($affected_count>0);
	}// /method



	public function deleteLinkFromItem($link_id, $item_id) {
		$binds = array (
			'id'       => $link_id ,
			'item_id'  => $item_id ,
		);
		$affected_count = $this->_db->delete('item_link', "item_id=:item_id AND id=:id", $binds);

		return ($affected_count>0);
	}



	/**
	 * Find the item link(s) specified.
	 *
	 * @param  mixed  $id  The ID, or array of IDs, to find.
	 *
	 * @return  mixed  The object, or recordset of objects, requested.  On fail, null.
	 */
	public function find($id) {
		if (is_array($id)) {
			$sql__id_set = $this->_db->prepareSet($id);

			return $this->_db->newRecordset("
				SELECT *
				FROM item_link
				WHERE id IN $sql__id_set
				ORDER BY $order_by
			", null, array($this, 'convertRowToObject'));
		} else {
			$sql__id = $this->_db->prepareValue( (int) $id);

			$this->_db->query("
				SELECT *
				FROM item_link
				WHERE id=$sql__id
				LIMIT 1
			");
			return $this->_db->getObject(array($this, 'convertRowToObject'));
		}
	}// /method



	/**
	 * Get the file types available.
	 *
	 * @return  mixed  The array of objects requested.
	 */
	public function findAllFileTypes() {

		// @idea : Move to own model?

		$this->_db->query("
			SELECT *
			FROM file_type
			ORDER BY name
		");

		return ($this->_db->hasResult()) ? $this->_db->getResultAssoc('file_type_id', 'name') : array() ;
	}// /method



	/**
	 * Find the links associated with the given item.
	 *
	 * @param  integer  $item_id  The item to use.
	 *
	 * @return  array  Array of links.
	 */
	public function findForItem($item_id) {
		$binds = array(
			'item_id'  => $item_id ,
		);

		return $this->_db->newRecordset("
			SELECT *
			FROM `item_link`
			WHERE item_id=:item_id
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Insert a new item link
	 *
	 * @param  object  $link  The link to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($link) {
		$binds = $this->convertObjectToRow($link);

		unset($binds['id']);   // Don't insert the id, we want a new one

		$new_id = $this->_db->insert('item_link', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a Itemlink object.
	 *
	 * @return  object
	 */
	public function newItemlink() {
		return new Itemlink();
	}// /method



	/**
	 * Update an existing item link.
	 *
	 * @param  object  $link  The link to update.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($link) {
		$binds = $this->convertObjectToRow($link);

		$id = $this->_db->prepareValue($link->id);

		$affected_count = $this->_db->update('item_link', $binds, "id=$id");

		return ($affected_count>0);
	}// /method




	/* ----------------------------------------------------------------------
	 * Private Methods
	 */



}// /class
?>