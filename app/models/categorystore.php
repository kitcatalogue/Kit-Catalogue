<?php



include_once('category.php');



/**
 * Category Store class
 *
 * @version 1.0.0
 */
class Categorystore {

	// Private Properties
	protected $_db = null;



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
			'category_id'  => $object->id ,
			'name'         => $object->name ,

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
		$object = $this->newCategory();

		$object->id = $row['category_id'];
		$object->name = $row['name'];

		$object->item_count_internal = $row['item_count_internal'];
		$object->item_count_public = $row['item_count_public'];

		return $object;
	}// /method



	/**
	 * Delete a category.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$sql__id = $this->_db->prepareValue($id);

		$this->_db->delete('category_code', "category_id=$sql__id");
		$this->_db->delete('item_category', "category_id=$sql__id");
		$affected_count = $this->_db->delete('category', "category_id=$sql__id");

		return ($affected_count>0);
	}// /method



	/**
	 * Find the category or categories specified.
	 *
	 * @param  mixed  $id  The ID, or array of IDs, to find.
	 *
	 * @return  mixed  The object, or Ecl_Db_Recordset of objects requested.
	 */
	public function find($id) {
		if (is_array($id)) {
			$id_set = $this->_db->prepareSet($id);

			return $this->_db->newRecordset("
				SELECT *
				FROM category
				WHERE category_id IN $id_set
				ORDER BY name ASC
			", null, array($this, 'convertRowToObject'));
		} else {
			$binds = array (
				'category_id'  => (int) $id ,
			);

			$row_count = $this->_db->query("
				SELECT *
				FROM category
				WHERE category_id=:category_id
				LIMIT 1
			", $binds);

			return $this->_db->getObject(array($this, 'convertRowToObject'));
		}
	}// /method



	/**
	 * Find all categories.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
			SELECT *
			FROM category
			ORDER BY name ASC
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find all categories used by an item of equipment, for the given visibility.
	 *
	 * @param  integer  $visibility  (optional) The item visibility to check
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAllUsed($visibility = null) {

		switch ($visibility) {
			case KC__VISIBILITY_INTERNAL:
				$where_clause = "WHERE item_count_internal>'0'";
				break;
			case KC__VISIBILITY_PUBLIC:
				$where_clause = "WHERE item_count_public>'0'";
				break;
			default:
				$where_clause = null;
				break;
		}// /switch

		return $this->_db->newRecordset("
			SELECT *
			FROM category
			$where_clause
			ORDER BY name ASC
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find categories for department.
	 *
	 * @param  string  $department_id  The department ID to check for.
	 * @param  integer  $visibility
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findForDepartment($department_id, $visibility) {

		$binds = array (
			'department'  => $department_id ,
		);

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause = (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;

		return $this->_db->newRecordset("
			SELECT DISTINCT c.*
			FROM category c
				INNER JOIN item_category ic ON c.category_id=ic.category_id
				INNER JOIN item i ON ic.item_id=i.item_id
			WHERE i.department_id=:department $where_clause
			ORDER BY name ASC
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find a category by its name.
	 *
	 * @param  string  $name
	 *
	 * @return  mixed  The category object found.  On fail, null.
	 */
	public function findForName($name) {

		$binds = array (
			':name'  => $name ,
		);

		$this->_db->query("
			SELECT *
			FROM category
			WHERE name=:name
			LIMIT 1
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all categories for the item specified.
	 *
	 * @param  integer  $item_id  The item to find.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	public function findForItem($item_id) {

		$binds = array (
			':item_id'  => $item_id ,
		);

		return $this->_db->newRecordset("
			SELECT *
			FROM category c
				INNER JOIN item_category ic ON c.category_id=ic.category_id
			WHERE ic.item_id=:item_id
			ORDER BY c.name
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Get the vocabulary codes associated with the given category
	 *
	 * @param  integer  $category_id
	 * @param  string  $vocabulary
	 *
	 * @return  array  The array of codes.
	 */
	public function getCategoryCodes($category_id, $vocabulary) {
		$binds = array (
			'category_id'  => $category_id ,
			'vocabulary'   => $vocabulary ,
		);

		$this->_db->query("
			SELECT code
			FROM category_code
			WHERE category_id=:category_id
				AND vocabulary=:vocabulary
			ORDER BY code
		", $binds);

		return ($this->_db->hasResult()) ? $this->_db->getColumn() : array() ;
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
	 * Find the requested number of categories, ranked by item usage, for the given visibility.
	 *
	 * @param  integer  $num  The number of categories to return.
	 * @param  integer  $visibility  (optional) The item visibility to check.
	 *
	 * @return  mixed  An array of objects.
	 */
	public function findUsedRanked($num = 0, $visibility = null) {

		$num = (int) $num;
		$limit_clause = ($num>0) ? "LIMIT $num" : null ;

		switch ($visibility) {
			case KC__VISIBILITY_INTERNAL:
				$where_clause = "WHERE item_count_internal>'0'";
				$order_clause = 'ORDER BY item_count_internal DESC, name ASC';
				break;
			case KC__VISIBILITY_PUBLIC:
				$where_clause = "WHERE item_count_public>'0'";
				$order_clause = 'ORDER BY item_count_public DESC, name ASC';
				break;
			default:
				$where_clause = null;
				$order_clause = 'ORDER BY name ASC';
				break;
		}// /switch

		return $this->_db->newRecordset("
			SELECT *
			FROM category
			$where_clause
			$order_clause
			$limit_clause
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Insert a new category.
	 *
	 * @param  object  $object  The Category to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($object) {

		$binds = $this->convertObjectToRow($object);

		unset($binds['category_id']);   // Don't insert the id, we want a new one

		$new_id = $this->_db->insert('category', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a Category object.
	 *
	 * @return  object  A Category object.
	 */
	public function newCategory() {
		return new Category();
	}// /method



	/**
	 * Rebuild all the item counts per category.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function rebuildItemCounts() {

		$visibility_types = array (
			'internal' => '
					SELECT ic.category_id, count(ic.item_id) AS count
					FROM item_category ic
						INNER JOIN item i ON ic.item_id=i.item_id
					GROUP BY ic.category_id
					ORDER BY ic.category_id
				' ,
			'public' => '
					SELECT ic.category_id, count(ic.item_id) AS count
					FROM item_category ic
						INNER JOIN item i ON ic.item_id=i.item_id AND i.visibility = \''. KC__VISIBILITY_PUBLIC .'\'
					GROUP BY ic.category_id
					ORDER BY ic.category_id
				' ,
		);


		// Get all the counts for each category
		$update_info = null;

		foreach($visibility_types as $type => $sql) {
			$row_count = $this->_db->query($sql);

			if ($row_count>0) {
				$counts = $this->_db->getResultAssoc('category_id', 'count');

				if ($counts) {
					foreach($counts as $category_id => $item_count) {
						$category_id = (int) $category_id;
						$update_info[$category_id][$type] = $item_count;
					}
				}
			}
		}


		// Reset all the category item counts to 0
		$binds = array (
			'item_count_internal'  => 0 ,
			'item_count_public'    => 0 ,
		);
		$this->_db->update('category', $binds);

		// If there are counts to update in the database
		if ($update_info) {
			foreach($update_info as $category_id => $counts) {
				$binds = array (
					'item_count_internal'  => (isset($counts['internal'])) ? $counts['internal'] : 0 ,
					'item_count_public'    => (isset($counts['public'])) ? $counts['public'] : 0 ,
				);

				$this->_db->update('category', $binds, "category_id='$category_id'");
			}
		}

		return true;
	}// /method



	/**
	 * Set the associated vocabulary codes for the given category.
	 *
	 * Replaces any existing associations for the given category and vocabulary.
	 *
	 * @param  integer  $category_id
	 * @param  string  $vocabulary
	 * @param  array  $codes
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setCategoryCodes($category_id, $vocabulary, $codes) {

		$codes = (array) $codes;

		// Delete existing codes for the given vocabulary
		$binds = array (
			'category_id'  => $category_id ,
			'vocabulary'   => $vocabulary ,
		);

		$this->_db->delete('category_code', "category_id=:category_id AND vocabulary=:vocabulary", $binds);

		// Insert new codes, if any
		if (!empty($codes)) {

			$codes = array_unique($codes);

			$binds = array();

			foreach($codes as $i => $code) {
				$binds[] = array (
					'category_id'  => $category_id ,
					'vocabulary'   => $vocabulary ,
					'code'         => $code ,
				);
			}

			$this->_db->insertMulti('category_code', $binds);
		}

		return true;
	}// /method



	/**
	 * Update an existing Category.
	 *
	 * @param  object  $object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($object) {
		$binds = $this->convertObjectToRow($object);

		$id = $this->_db->prepareValue($object->id);

		$affected_count = $this->_db->update('category', $binds, "category_id=$id");

		return ($affected_count>0);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
