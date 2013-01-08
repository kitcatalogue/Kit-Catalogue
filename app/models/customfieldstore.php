<?php



include_once('customfield.php');



/**
 * Customfield Store class
 *
 * @version 1.0.0
 */
class Customfieldstore {

	// Private Properties
	protected $_db = null;



	/**
	 * Constructor
	 */
	public function __construct($model) {
		$this->_model = $model;
		$this->_db = $this->_model->get('db');
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
		$row = array (
			'field_id' => $object->id ,
			'name'     => $object->name ,
		);

		return $row;
	}// /method



	/**
	 * Convert a database row to a domain object
	 *
	 * @param  array  $row  The database row.
	 *
	 * @return  object  The object the row represents.
	 */
	public function convertRowToObject($row) {
		$object = $this->newCustomfield();

		$object->id = $row['field_id'];
		$object->name = $row['name'];

		return $object;
	}// /method



	/**
	 * Delete a custom field.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {

		$id = $this->_db->prepareValue($id);

		$this->_db->delete('item_custom', "field_id={$id}");

		$affected_count = $this->_db->delete('custom_field', "field_id={$id}");

		return ($affected_count>0);
	}// /method



	/**
	 * Find the custom field specified.
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
				FROM custom_field
				WHERE field_id IN $sql__id_set
				ORDER BY field_id ASC
			", null, array($this, 'convertRowToObject'));
		} else {
			$sql__id = $this->_db->prepareValue( (int) $id);

			$this->_db->query("
				SELECT *
				FROM custom_field
				WHERE field_id=$sql__id
				LIMIT 1
			");
			return $this->_db->getObject(array($this, 'convertRowToObject'));
		}
	}// /method



	/**
	 * Find all custom fields.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
				SELECT *
				FROM custom_field
				ORDER BY field_id ASC
			", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Get the values used for a custom field, ranked by the number of items represented.
	 *
	 * A $limit of '0' means all matching fields will be returned.
	 *
	 * @param  integer
	 * @param  integer  $limit  (default: 0)
	 * @param  integer  $visibility  (default: null)
	 *
	 * @return  mixed  The object, or array of objects, requested.  On fail, null.
	 */
	public function findUsedCustomFieldValues($field_id, $limit = 0, $visibility = null) {
		$field_id = (int) $field_id;
		$limit = (int) $limit;

		$where_clause = "WHERE field_id={$field_id} AND TRIM(ic.value)<>'' ";

		$limit_clause = ($limit > 0) ? "LIMIT $limit" : '' ;

		$sql__vis_condition = $this->_model->get('itemstore')->getVisibilitySqlCondition($visibility, 'i');
		$where_clause .= (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;


		$this->_db->query("
			SELECT ic.item_id, ic.value, count(ic.item_id) AS count
			FROM item_custom ic
				INNER JOIN item i ON ic.item_id=i.item_id
			$where_clause
			GROUP BY ic.value
			ORDER BY count DESC, value ASC
			$limit_clause
		", null, array($this, 'convertRowToObject'));

		return ($this->_db->hasResult()) ? $this->_db->getResult() : array() ;
	}// /method



	/**
	 * Get the custom fields associated with the given item.
	 *
	 * @param  integer  $item_id
	 *
	 * @return  array  Assoc-array of field IDs and current values.
	 */
	public function getItemCustomFields($item_id) {

		$binds = array (
			'item_id'  => $item_id ,
		);

		$this->_db->query("
			SELECT field_id, value
			FROM item_custom
			WHERE item_id=:item_id
			ORDER BY field_id ASC
		", $binds);

		return ($this->_db->hasResult()) ? $this->_db->getResultAssoc('field_id', 'value') : array() ;
	}// /method



	/**
	 * Insert a new custom field.
	 *
	 * @param  object  $customfield  The field to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($customfield) {

		$name = $this->_db->prepareValue($customfield->name);

		$num_rows = $this->_db->query("
			SELECT field_id
			FROM custom_field
			WHERE name=$name
		");

		if ($num_rows>0) {
			return null;
		} else {
			$binds = $this->convertObjectToRow($customfield);

			unset($binds['field_id']);   // Don't insert the id, we want a new one

			$new_id = $this->_db->insert('custom_field', $binds);

			return ($new_id>0) ? $new_id : null ;
		}
	}// /method



	/**
	 * Get a new instance of a Customfield object.
	 *
	 * @return  object
	 */
	public function newCustomfield() {
		return new Customfield();
	}// /method



	/**
	 * Update an existing custom field.
	 *
	 * @param  object  $object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($object) {
		$binds = $this->convertObjectToRow($object);

		$id = $this->_db->prepareValue($object->id);

		$affected_count = $this->_db->update('custom_field', $binds, "field_id=$id");

		return ($affected_count>0);
	}// /method



	/* ----------------------------------------------------------------------
	 * Private Methods
	 */



}// /class
?>