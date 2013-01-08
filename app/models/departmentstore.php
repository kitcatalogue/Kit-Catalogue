<?php



include_once('department.php');



/**
 * Department Store class
 *
 * @version 1.0.0
 */
class Departmentstore {

	// Private Properties
	protected $_db = null;

	protected $_lookup = null;



	/**
	 * Constructor
	 *
	 * @param  object  $database  An Ecl_Db data access object.
	 */
	public function __construct($database) {
		$this->_db = $database;
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Convert a domain object to a database row
	 *
	 * @param  object  $object
	 *
	 * @return  array
	 */
	public function convertObjectToRow($object) {
		$row = array (
			'department_id'        => $object->id ,
			'name'                 => $object->name ,
			'item_count_internal'  => $object->item_count_internal ,
			'item_count_public'    => $object->item_count_public ,
		);

		return $row;
	}// /method



	/**
	 * Convert a database row to a domain object
	 *
	 * @param  array  $row
	 *
	 * @return  object
	 */
	public function convertRowToObject($row) {
		$object = $this->newDepartment();

		$object->id = $row['department_id'];
		$object->name = $row['name'];

		$object->item_count_internal = $row['item_count_internal'];
		$object->item_count_public = $row['item_count_public'];

		return $object;
	}// /method



	/**
	 * Delete a department.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('department', "department_id=$id");

		$binds = array (
			'department_id'  => null ,
		);

		$this->_db->update('item', $binds, "department_id=$id");

		return ($affected_count>0);
	}// /method



	/**
	 * Find the department(s) specified.
	 *
	 * @param  mixed  $id  The ID, or array of IDs, to find.
	 *
	 * @return  mixed  The object, or array of objects, requested.  On fail, null.
	 */
	public function find($id) {
		if (is_array($id)) {
			$id_set = $this->_db->prepareSet($id);

			return $this->_db->newRecordset("
				SELECT *
				FROM department
				WHERE department_id IN $id_set
				ORDER BY name ASC
			", null, array($this, 'convertRowToObject') );
		} else {

			$binds = array (
				'department_id'  => (int) $id ,
			);

			$row_count = $this->_db->query("
				SELECT *
				FROM department
				WHERE department_id=:department_id
				LIMIT 1
			", $binds);

			return $this->_db->getObject(array($this, 'convertRowToObject') );
		}
	}// /method



	/**
	 * Find all departments.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
			SELECT *
			FROM department
			ORDER BY name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all departments used by at least one item of equipment, for the given visibility.
	 *
	 * @param  integer  $visibility  (optional) The item visibility to check
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAllUsed($visibility = null) {
		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause = (!empty($sql__vis_condition)) ? "WHERE $sql__vis_condition" : null ;

		return $this->_db->newRecordset("
			SELECT DISTINCT d.*
			FROM department d INNER JOIN item i ON d.department_id=i.department_id
			$where_clause
			ORDER BY d.name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all department IDs and Names, and their respective counts, for the items specified.
	 *
	 * @param  array  $id  The array of item IDs to find.
	 *
	 * @return  array  The information retrieved.
	 */
	public function findCountsForItems($id) {
		$sql__id_set = $this->_db->prepareSet((array) $id);

		$this->_db->query("
			SELECT d.department_id, d.name, count(d.department_id) AS `count`
			FROM department d
				INNER JOIN item i ON d.department_id=i.department_id AND i.item_id IN $sql__id_set
			GROUP BY d.department_id, d.name
			ORDER BY d.name
		");

		return $this->_db->getResult();
	}// /method



	/**
	 * Find a department by its name.
	 *
	 * @param  string  $name
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findForName($name) {

		$binds = array (
			'name'  => $name ,
		);

		$row_count = $this->_db->query("
			SELECT *
			FROM department
			WHERE name=:name
			LIMIT 1
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find the requested number of departments, ranked by item usage, for the given visibility.
	 *
	 * @param  integer  $num  The number of results to return.
	 * @param  integer  $visibility  (optional) The item visibility to check.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findUsedRanked($num = 0, $visibility = null) {

		$num = (int) $num;
		$limit_clause = ($num>0) ? "LIMIT $num" : null ;

		switch ($visibility) {
			case KC__VISIBILITY_INTERNAL:
				$where_clause = 'WHERE item_count_internal>0';
				$order_clause = 'ORDER BY item_count_internal DESC, name ASC';
				break;
			case KC__VISIBILITY_PUBLIC:
				$where_clause = 'WHERE item_count_public>0';
				$order_clause = 'ORDER BY item_count_public DESC, name ASC';
				break;
			default:
				$where_clause = null;
				$order_clause = 'ORDER BY name ASC';
				break;
		}// /switch

		return $this->_db->newRecordset("
			SELECT *
			FROM department
			$where_clause
			$order_clause
			$limit_clause
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Create an SQL condition appropriate for the given visibility.
	 *
	 * The condition for internal-visibility will be empty, as all items should be returned.
	 *
	 * @param  integer  $visibility
	 * @param  string  $table_alias  (optional) Any alias the `item` table has in the query.  (default: '')
	 *
	 * @return  string  The SQL condition, which may be an empty string if appropriate.
	 */
	public function getVisibilitySqlCondition($visibility, $table_alias = '') {
		$table_alias = (!empty($table_alias)) ? "{table_alias}." : null ;
		if (KC__VISIBILITY_PUBLIC == $visibility) {
			return "{$table_alias}visibility='$visibility'";
		} else {
			return '';
		}
	}// /method



	/**
	 * Insert a new department.
	 *
	 * @param  object  $object  The Department to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($object) {
		$binds = $this->convertObjectToRow($object);

		unset($binds['department_id']);   // Don't insert the id, we want a new one

		$new_id = $this->_db->insert('department', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a Department object.
	 *
	 * @return  object  A Department object.
	 */
	public function newDepartment() {
		return new Department();
	}// /method



	/**
	 * Lookup the name of a department using its ID.
	 *
	 * Use find() if you only want a single lookup.
	 * This method uses caching to speed up subsequent lookups, so will be faster if you need more than one.
	 *
	 * @param  integer  $dept_id
	 * @param  string  $default  (optional) The default name to return.
	 *
	 * @return  string  The department's name.  On fail, $default.
	 */
	public function lookupName($dept_id, $default = '') {
		if (null === $this->_lookup) {
			$this->_lookup = $this->findAll()->toAssoc('id', 'name');
		}
		return (isset($this->_lookup[$dept_id])) ? $this->_lookup[$dept_id] : $default ;
	}// /method



	/**
	 * Rebuild all the item counts per department.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function rebuildItemCounts() {

		$visibility_types = array (
			'internal' => '
					SELECT department_id, count(item_id) AS count
					FROM item
					GROUP BY department_id
					ORDER BY department_id
				' ,
			'public' => '
					SELECT department_id, count(item_id) AS count
					FROM item
					WHERE visibility = \''. KC__VISIBILITY_PUBLIC .'\'
					GROUP BY department_id
					ORDER BY department_id
				' ,
		);


		// Get all the counts for each category
		$update_info = null;

		foreach($visibility_types as $type => $sql) {
			$row_count = $this->_db->query($sql);


			if ($row_count>0) {
				$counts = $this->_db->getResultAssoc('department_id', 'count');
				if ($counts) {
					foreach($counts as $department_id => $item_count) {
						$department_id = (int) $department_id;
						$update_info[$department_id][$type] = $item_count;
					}
				}
			}
		}


		// Reset all the department item counts to 0
		$binds = array (
			'item_count_internal'  => 0 ,
			'item_count_public'    => 0 ,
		);
		$this->_db->update('department', $binds);

		// If there are counts to update in the database
		if ($update_info) {

			foreach($update_info as $department_id => $counts) {
				$binds = array (
					'item_count_internal'  => (isset($counts['internal'])) ? $counts['internal'] : 0 ,
					'item_count_public'    => (isset($counts['public'])) ? $counts['public'] : 0 ,
				);

				$this->_db->update('department', $binds, "department_id='$department_id'");
			}
		}

		return true;
	}// /method



	/**
	 * Update an existing Department.
	 *
	 * @param  object  $object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($object) {
		$binds = $this->convertObjectToRow($object);

		$id = $this->_db->prepareValue($object->id);

		$affected_count = $this->_db->update('department', $binds, "department_id=$id");

		return ($affected_count>0);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
