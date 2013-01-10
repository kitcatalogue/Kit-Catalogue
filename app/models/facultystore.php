<?php



include_once('faculty.php');



/**
 * Faculty Store class
 *
 * @version 1.0.0
 */
class facultystore {

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
			'faculty_id'  => $object->id ,
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
		$object = $this->newfaculty();

		$object->id = $row['faculty_id'];
		$object->name = $row['name'];

		return $object;
	}// /method



	/**
	 * Delete an faculty.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('faculty', "faculty_id=$id");

		$binds = array (
			'faculty_id'  => null ,
		);

		$this->_db->update('item', $binds, "faculty_id=$id");

		return ($affected_count>0);
	}// /method



	/**
	 * Find the faculty(s) specified.
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
				FROM faculty
				WHERE faculty_id IN $id_set
				ORDER BY name ASC
			", null, array($this, 'convertRowToObject') );
		} else {

			$binds = array (
				'faculty_id'  => (int) $id ,
			);

			$row_count = $this->_db->query("
				SELECT *
				FROM faculty
				WHERE faculty_id=:faculty_id
				LIMIT 1
			", $binds);

			return $this->_db->getObject(array($this, 'convertRowToObject') );
		}
	}// /method



	/**
	 * Find all facultys.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
			SELECT *
			FROM faculty
			ORDER BY name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all facultys used by at least one item of equipment, for the given visibility.
	 *
	 * @param  integer  $visibility  (optional) The item visibility to check
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAllUsed($visibility = null) {

		$sql__visibility = $this->_db->escapeString($visibility);

		return $this->_db->newRecordset("
			SELECT f.*
			FROM faculty f INNER JOIN item i ON f.faculty_id=i.site_id
			WHERE (i.visibility & $sql__visibility)=$sql__visibility
			ORDER BY f.name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find the faculties used for equipment in an organisation.
	 *
	 * @param  string  $organisation_id  The organisation ID to check for.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findForOrganisation($organisation_id) {

		$binds = array (
			'organisation'  => $organisation_id ,
		);

		return $this->_db->newRecordset("
			SELECT DISTINCT f.*
			FROM faculty f
				INNER JOIN item i ON f.faculty_id=i.faculty
			WHERE i.organisation=:organisation
			ORDER BY name ASC
		", $binds, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find an faculty by its name.
	 *
	 * @param  string  $name
	 *
	 * @return  mixed  The faculty.  On fail, null.
	 */
	public function findForName($name) {

		$binds = array (
			'name'  => $name ,
		);

		$row_count = $this->_db->query("
			SELECT *
			FROM faculty
			WHERE name=:name
			LIMIT 1
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find an existing faculty with the given name, or create a new one with the name.
	 *
	 * If $faculty_to_create is given, then it will be inserted and returned as the new faculty.
	 * If it is left empty, then a new faculty will be created.
	 * Any newly created faculty will use the given name.
	 *
	 * @param  string  $name
	 * @param  mixed  $faculty_to_create  (optional)
	 *
	 * @return  object  The faculty
	 */
	public function findOrCreateForName($name, $faculty_to_create = null) {
		$faculty = $this->findForName($name);
		if (!empty($faculty)) { return $faculty; }

		if (empty($faculty_to_create)) {
			$faculty = $this->newfaculty();
		}
		$faculty->name = $name;
		$faculty->id = $this->insert($faculty);
		return $faculty;
	}// /method



	/**
	 * Lookup the name of a faculty using its ID.
	 *
	 * Use find() if you only want a single lookup.
	 * This method uses caching to speed up subsequent lookups, so will be faster if you need more than one.
	 *
	 * @param  integer  $faculty_id
	 * @param  string  $default  (optional) The default name to return.
	 *
	 * @return  string  The faculty's name.  On fail, $default.
	 */
	public function lookupName($faculty_id, $default = '') {
		if (null === $this->_lookup) {
			$this->_lookup = $this->findAll()->toAssoc('id', 'name');
		}
		return (isset($this->_lookup[$faculty_id])) ? $this->_lookup[$faculty_id] : $default ;
	}// /method



	/**
	 * Insert a new faculty.
	 *
	 * @param  object  $object  The faculty to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($object) {
		$binds = $this->convertObjectToRow($object);

		unset($binds['faculty_id']);   // Don't insert the id, we want a new one

		$new_id = $this->_db->insert('faculty', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a faculty object.
	 *
	 * @return  object  A faculty object.
	 */
	public function newfaculty() {
		return new faculty();
	}// /method



	/**
	 * Update an existing faculty.
	 *
	 * @param  object  $object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($object) {
		$binds = $this->convertObjectToRow($object);

		$id = $this->_db->prepareValue($object->id);

		$affected_count = $this->_db->update('faculty', $binds, "faculty_id=$id");

		return ($affected_count>0);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>