<?php



include_once('homepageblock.php');



/**
 * Homepageblock Store class
 *
 * @version 1.0.0
 */
class Homepageblockstore {

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
			'block_id'        => $object->block_id ,
			'block_name'      => $object->block_name ,
			'block_to_find'   => $object->block_to_find ,
			'block_enabled'   => $object->block_enabled ,
			'visibility'      => $object->visibility ,
			'block_to_find_name' => $object->block_to_find_name ,
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
		$object = $this->newHomepageblock();

		$object->block_id = $row['block_id'];
		$object->block_name = $row['block_name'];
		$object->block_to_find = $row['block_to_find'];
		$object->block_enabled = $row['block_enabled'];
		$object->visibility = $row['visibility'];
		$object->block_to_find_name = $row['name'];

		return $object;
	}// /method



	/**
	 * Delete a homepageblock.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('homepageblock', "block_id=$id");

		return ($affected_count>0);
	}// /method



	/**
	 * Find the homepageblock(s) specified.
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
				FROM homepageblock
				LEFT JOIN custom_field c
				  ON h.block_to_find = c.field_id
				WHERE block_id IN $id_set
				ORDER BY block_name ASC LIMIT 5
			", null, array($this, 'convertRowToObject') );
		} else {

			$binds = array (
				'block_id'  => (int) $id ,
			);

			$row_count = $this->_db->query("
				SELECT *
				FROM homepageblock h
				LEFT JOIN custom_field c
				  ON h.block_to_find = c.field_id
				WHERE block_id=:block_id
				LIMIT 1
			", $binds);

			return $this->_db->getObject(array($this, 'convertRowToObject') );
		}
	}// /method



	/**
	 * Find all homepageblocks.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAll($visibility = null) {
		$query = "SELECT *
			FROM homepageblock h
			LEFT JOIN custom_field c
				ON h.block_to_find = c.field_id
			WHERE h.block_enabled = 1
		";

		if (null != $visibility) {
			$query .= " AND ((visibility & $visibility)>0)";
		}

		$query .= " ORDER BY block_name ASC LIMIT 5";

		return $this->_db->newRecordset($query, null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all related items to this custom field block.
	 *
	 * @param integer $custom_id - the id of the custom field
	 * @param integer $visibility
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findRelatedContent($custom_id, $visibility = null) {
		$query = "SELECT *
				FROM item i
				LEFT JOIN item_custom ic ON i.item_id=ic.item_id
				GROUP BY ic.value";
		if (null != $visibility) {
			$query .= " AND visibility = $visibility";
		}
		$result = $this->_db->newRecordset($query, null);
		//print_r($result);
	}// /method

	/**
	 * Find a homepageblock by its name.
	 *
	 * @param  string  $name
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findForName($name) {

		$binds = array (
			'block_name'  => $name ,
		);

		$row_count = $this->_db->query("
			SELECT *
			FROM homepageblock
			WHERE block_name=:block_name
			LIMIT 1
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject') );
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
	 * Insert a new homepageblock.
	 *
	 * @param  object  $object  The Homepageblock to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($object) {
		$binds = $this->convertObjectToRow($object);

		unset($binds['block_id']);   // Don't insert the id, we want a new one
		unset($binds['block_to_find_name']); // Don't try to inset this either - it's just a handy readable name for later

		$new_id = $this->_db->insert('homepageblock', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a Homepageblock object.
	 *
	 * @return  object  A Homepageblock object.
	 */
	public function newHomepageblock() {
		return new Homepageblock();
	}// /method



	/**
	 * Lookup the name of a homepageblock using its ID.
	 *
	 * Use find() if you only want a single lookup.
	 * This method uses caching to speed up subsequent lookups, so will be faster if you need more than one.
	 *
	 * @param  integer  $dept_id
	 *
	 * @return  string  The homepageblock's name.  On fail, ''.
	 */
	public function lookupName($block_id) {
		if (null === $this->_lookup) {
			$this->_lookup = $this->findAll()->toAssoc('block_id', 'block_name');
		}
		return (isset($this->_lookup[$block_id])) ? $this->_lookup[$block_id] : '' ;
	}// /method


	/**
	 * Lookup the reason for a homepageblock using its ID.
	 *
	 * Use find() if you only want a single lookup.
	 * This method uses caching to speed up subsequent lookups, so will be faster if you need more than one.
	 *
	 * @param  integer  $dept_id
	 *
	 * @return  string  The homepageblock's name.  On fail, ''.
	 */
	public function lookupReason($block_id) {
		if (null === $this->_lookup) {
			$this->_lookup = $this->findAll()->toAssoc('block_id', 'block_to_find_name');
		}
		return (isset($this->_lookup[$block_id])) ? $this->_lookup[$block_id] : '' ;
	}// /method

	/**
	 * Rebuild all the item counts per homepageblock.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function rebuildItemCounts() {

		$visibility_types = array (
			'internal' => '
					SELECT block_id, count(block_id) AS count
					FROM homepageblock
				' ,
			'public' => '
					SELECT block_id, count(block_id) AS count
					FROM homepageblock
					WHERE visibility = \''. KC__VISIBILITY_PUBLIC .'\'
				' ,
		);


		// Get all the counts for each category
		$update_info = null;

		foreach($visibility_types as $type => $sql) {
			$row_count = $this->_db->query($sql);

			if ($row_count>0) {
				$counts = $this->_db->getResultAssoc('block_id', 'count');
				if ($counts) {
					foreach($counts as $block_id => $item_count) {
						$block_id = (int) $block_id;
						$update_info[$hblock_id][$type] = $item_count;
					}
				}
			}
		}


		// Reset all the homepageblock item counts to 0
		$binds = array (
			'item_count_internal'  => 0 ,
			'item_count_public'    => 0 ,
		);
		$this->_db->update('homepageblock', $binds);

		// If there are counts to update in the database
		if ($update_info) {

			foreach($update_info as $block_id => $counts) {
				$binds = array (
					'item_count_internal'  => (isset($counts['internal'])) ? $counts['internal'] : 0 ,
					'item_count_public'    => (isset($counts['public'])) ? $counts['public'] : 0 ,
				);

				$this->_db->update('homepageblock', $binds, "block_id='$homepageblock_id'");
			}
		}

		return true;
	}// /method


	/**
	 * Update an existing Homepageblock.
	 *
	 * @param  object  $object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($object) {
		$binds = $this->convertObjectToRow($object);
		// before updating, we have to remove the 'block_to_find_name'
		unset($binds['block_to_find_name']);

		$id = $this->_db->prepareValue($object->block_id);
		$affected_count = $this->_db->update('homepageblock', $binds, "block_id=$id");

		return ($affected_count>0);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
