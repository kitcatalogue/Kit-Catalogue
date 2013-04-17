<?php



include_once('accesslevel.php');



/**
 * Site Store class
 *
 * @version 1.0.0
 */
class Accesslevelstore {

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
			'access_id'  => $object->id ,
			'name'     => $object->name ,
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
		$object = $this->newAccesslevel();

		$object->id = $row['access_id'];
		$object->name = $row['name'];

		return $object;
	}// /method



	/**
	 * Delete an access level.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('access', "access_id=$id");

		$binds = array (
			'access_id'  => null ,
		);

		$this->_db->update('item', $binds, "access_id=$id");

		return ($affected_count>0);
	}// /method



	/**
	 * Find the access level(s) specified.
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
				FROM access
				WHERE access_id IN $id_set
				ORDER BY name ASC
			", array($this, 'convertRowToObject') );
		} else {

			$binds = array (
				'access_id'  => (int) $id ,
			);

			$this->_db->query("
				SELECT *
				FROM access
				WHERE access_id=:access_id
				LIMIT 1
			", $binds);

			return $this->_db->getObject(array($this, 'convertRowToObject') );
		}
	}// /method



	/**
	 * Find all access levels.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
			SELECT *
			FROM access
			ORDER BY name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all access levels used by at least one item of equipment, for the given visibility.
	 *
	 * @param  integer  $visibility  (optional) The item visibility to check
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAllUsed($visibility = null) {

		$sql__visibility = $this->_db->escapeString($visibility);

		return $this->_db->newRecordset("
			SELECT a.*
			FROM access a INNER JOIN item i ON s.accesslevel_id=i.access_id
			WHERE (i.visibility & $sql__visibility)=$sql__visibility
			ORDER BY a.name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find the access levels used for equipment in an OU.
	 *
	 * @param  string  $ou_id  The OU ID to check for.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findForOU($ou_id) {

		$binds = array (
			'ou_id'  => $ou_id ,
		);

		return $this->_db->newRecordset("
			SELECT DISTINCT a.*
			FROM access a
				INNER JOIN item i ON a.access_id=i.access
			WHERE i.ou_id=:ou_id
			ORDER BY name ASC
		", $binds, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find an access level by its name.
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
			FROM access
			WHERE name=:name
			LIMIT 1
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject') );
	}// /method



	/**
	* Lookup the name of an access level using its ID.
	*
	* Use find() if you only want a single lookup.
	* This method uses caching to speed up subsequent lookups, so will be faster if you need more than one.
	*
	* @param  integer  $access_level_id
	* @param  string  $default  (optional) The default name to return.
	*
	* @return  string  The access level.  On fail, ''.
	*/
	public function lookupName($access_level_id, $default = '') {
		if (null === $this->_lookup) {
			$this->_lookup = $this->findAll()->toAssoc('id', 'name');
		}
		return (isset($this->_lookup[$access_level_id])) ? $this->_lookup[$access_level_id] : $default ;
	}// /method



	/**
	 * Insert a new access level.
	 *
	 * @param  object  $object  The Site to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($object) {
		$binds = $this->convertObjectToRow($object);

		unset($binds['access_id']);   // Don't insert the id, we want a new one

		$new_id = $this->_db->insert('access', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of an Accesslevel object.
	 *
	 * @return  object  An Accesslevel object.
	 */
	public function newAccesslevel() {
		return new Accesslevel();
	}// /method



	/**
	 * Update an existing Site.
	 *
	 * @param  object  $object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($object) {
		$binds = $this->convertObjectToRow($object);

		$id = $this->_db->prepareValue($object->id);

		$affected_count = $this->_db->update('access', $binds, "access_id=$id");

		return ($affected_count>0);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
