<?php



include_once('organisation.php');



/**
 * Organisation Store class
 *
 * @version 1.0.0
 */
class Organisationstore {

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
			'organisation_id'  => $object->id ,
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
		$object = $this->newOrganisation();

		$object->id = $row['organisation_id'];
		$object->name = $row['name'];

		return $object;
	}// /method



	/**
	 * Delete an organisation.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('organisation', "organisation_id=$id");

		$binds = array (
			'organisation_id'  => null ,
		);

		$this->_db->update('item', $binds, "organisation_id=$id");

		return ($affected_count>0);
	}// /method



	/**
	 * Find the organisation(s) specified.
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
				FROM organisation
				WHERE organisation_id IN $id_set
				ORDER BY name ASC
			", null, array($this, 'convertRowToObject') );
		} else {

			$binds = array (
				'organisation_id'  => (int) $id ,
			);

			$row_count = $this->_db->query("
				SELECT *
				FROM organisation
				WHERE organisation_id=:organisation_id
				LIMIT 1
			", $binds);

			return $this->_db->getObject(array($this, 'convertRowToObject') );
		}
	}// /method



	/**
	 * Find all organisations.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
			SELECT *
			FROM organisation
			ORDER BY name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all organisations used by at least one item of equipment, for the given visibility.
	 *
	 * @param  integer  $visibility  (optional) The item visibility to check
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAllUsed($visibility = null) {

		$sql__visibility = $this->_db->escapeString($visibility);

		return $this->_db->newRecordset("
			SELECT o.*
			FROM organisation o INNER JOIN item i ON o.organisation_id=i.site_id
			WHERE (i.visibility & $sql__visibility)=$sql__visibility
			ORDER BY o.name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find the organisations used for equipment in a department.
	 *
	 * @param  string  $department_id  The department ID to check for.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findForDepartment($department_id) {

		$binds = array (
			'department'  => $department_id ,
		);

		return $this->_db->newRecordset("
			SELECT DISTINCT o.*
			FROM organisation o
				INNER JOIN item i ON o.organisation_id=i.organisation
			WHERE i.organisation=:organisation
			ORDER BY name ASC
		", $binds, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find an organisation by its name.
	 *
	 * @param  string  $name
	 *
	 * @return  mixed  The Organisation.  On fail, null.
	 */
	public function findForName($name) {

		$binds = array (
			'name'  => $name ,
		);

		$row_count = $this->_db->query("
			SELECT *
			FROM organisation
			WHERE name=:name
			LIMIT 1
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find an existing organisation with the given name, or create a new one with the name.
	 *
	 * If $org_to_create is given, then it will be inserted and returned as the new organisation.
	 * If it is left empty, then a new organisation will be created.
	 * Any newly created organisation will use the given name.
	 *
	 * @param  string  $name
	 * @param  mixed  $org_to_create  (optional)
	 *
	 * @return  object  The Organisation
	 */
	public function findOrCreateForName($name, $org_to_create = null) {
		$org = $this->findForName($name);
		if (!empty($org)) { return $org; }

		if (empty($org_to_create)) {
			$org = $this->newOrganisation();
		}
		$org->name = $name;
		$org->id = $this->insert($org);
		return $org;
	}// /method



	/**
	 * Lookup the name of a organisation using its ID.
	 *
	 * Use find() if you only want a single lookup.
	 * This method uses caching to speed up subsequent lookups, so will be faster if you need more than one.
	 *
	 * @param  integer  $organisation_id
	 * @param  string  $default  (optional) The default name to return.
	 *
	 * @return  string  The organisation's name.  On fail, $default.
	 */
	public function lookupName($organisation_id, $default = '') {
		if (null === $this->_lookup) {
			$this->_lookup = $this->findAll()->toAssoc('id', 'name');
		}
		return (isset($this->_lookup[$organisation_id])) ? $this->_lookup[$organisation_id] : $default ;
	}// /method



	/**
	 * Insert a new organisation.
	 *
	 * @param  object  $object  The Organisation to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($object) {
		$binds = $this->convertObjectToRow($object);

		unset($binds['organisation_id']);   // Don't insert the id, we want a new one

		$new_id = $this->_db->insert('organisation', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a Organisation object.
	 *
	 * @return  object  A Organisation object.
	 */
	public function newOrganisation() {
		return new Organisation();
	}// /method



	/**
	 * Update an existing organisation.
	 *
	 * @param  object  $object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($object) {
		$binds = $this->convertObjectToRow($object);

		$id = $this->_db->prepareValue($object->id);

		$affected_count = $this->_db->update('organisation', $binds, "organisation_id=$id");

		return ($affected_count>0);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>