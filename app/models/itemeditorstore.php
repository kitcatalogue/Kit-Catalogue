<?php



include_once('itemeditor.php');



/**
 * Item Editor Store class
 *
 * @version 1.0.0
 */
class Itemeditorstore {

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
			'id'       => $object->id ,
			'item_id'  => $object->item_id ,
			'username' => $object->username ,
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
		$object = $this->newItemeditor();

		$object->id = $row['id'];
		$object->item_id = $row['item_id'];
		$object->username = $row['username'];

		return $object;
	}// /method



	/**
	 * Delete an item editor.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('item_editor', "id=$id");

		return ($affected_count>0);
	}// /method



	public function deleteEditorFromItem($editor_id, $item_id) {
		$binds = array (
			'id'       => $editor_id ,
			'item_id'  => $item_id ,
		);
		$affected_count = $this->_db->delete('item_editor', "item_id=:item_id AND id=:id", $binds);

		return ($affected_count>0);
	}



	/**
	 * Find the item editor(s) specified.
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
				FROM item_editor
				WHERE id IN $sql__id_set
				ORDER BY $order_by
			", null, array($this, 'convertRowToObject'));
		} else {
			$sql__id = $this->_db->prepareValue( (int) $id);

			$this->_db->query("
				SELECT *
				FROM item_editor
				WHERE id=$sql__id
				LIMIT 1
			");
			return $this->_db->getObject(array($this, 'convertRowToObject'));
		}
	}// /method



	/**
	 * Find the editors associated with the given item.
	 *
	 * @param  integer  $item_id  The item to use.
	 *
	 * @return  array  Array of editors.
	 */
	public function findForItem($item_id) {
		$binds = array(
			'item_id'  => $item_id ,
		);

		return $this->_db->newRecordset("
			SELECT *
			FROM `item_editor`
			WHERE item_id=:item_id
			ORDER BY username
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find the editors associated with the given username.
	 *
	 * @param  string  $username
	 *
	 * @return  array  Array of editors.
	 */
	public function findForUsername($username) {
		$binds = array(
			'username'  => $username ,
		);

		return $this->_db->newRecordset("
			SELECT *
			FROM `item_editor`
			WHERE username=:username
			ORDER BY item_id
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Insert a new item editor
	 *
	 * @param  object  $editor  The editor to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($editor) {
		$binds = $this->convertObjectToRow($editor);

		unset($binds['id']);   // Don't insert the id, we want a new one

		$new_id = $this->_db->insert('item_editor', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a Itemeditor object.
	 *
	 * @return  object
	 */
	public function newItemeditor() {
		return new Itemeditor();
	}// /method



	/**
	 * Update an existing item editor.
	 *
	 * @param  object  $editor  The editor to update.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($editor) {
		$binds = $this->convertObjectToRow($editor);

		$id = $this->_db->prepareValue($editor->id);

		$affected_count = $this->_db->update('item_editor', $binds, "id=$id");

		return ($affected_count>0);
	}// /method




	/* ----------------------------------------------------------------------
	 * Private Methods
	 */



}// /class
?>