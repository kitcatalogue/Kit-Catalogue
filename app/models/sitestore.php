<?php



include_once('site.php');



/**
 * Site Store class
 *
 * @version 1.0.0
 */
class Sitestore {

	// Private Properties
	protected $_db = null;

<<<<<<< HEAD
	protected $_lookup = null;

=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd


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
			'site_id'  => $object->id ,
			'name'     => $object->name ,
<<<<<<< HEAD
			'url'      => $object->url ,
=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
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
		$object = $this->newSite();

		$object->id = $row['site_id'];
		$object->name = $row['name'];
<<<<<<< HEAD
		$object->url = $row['url'];
=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

		return $object;
	}// /method



	/**
	 * Delete a site.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('site', "site_id=$id");

		$binds = array (
			'site_id'  => null ,
		);

		$this->_db->update('item', $binds, "site_id=$id");

		return ($affected_count>0);
	}// /method



	/**
	 * Find the site(s) specified.
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
				FROM site
				WHERE site_id IN $id_set
				ORDER BY name ASC
			", null, array($this, 'convertRowToObject') );
		} else {

			$binds = array (
				'site_id'  => (int) $id ,
			);

			$row_count = $this->_db->query("
				SELECT *
				FROM site
				WHERE site_id=:site_id
				LIMIT 1
			", $binds);

			return $this->_db->getObject(array($this, 'convertRowToObject') );
		}
	}// /method



	/**
	 * Find all sites.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
			SELECT *
			FROM site
			ORDER BY name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all sites used by at least one item of equipment, for the given visibility.
	 *
	 * @param  integer  $visibility  (optional) The item visibility to check
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAllUsed($visibility = null) {

<<<<<<< HEAD
		if (empty($visibility)) { $visibility = KC__VISIBILITY_INTERNAL; }
=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
		$sql__visibility = $this->_db->escapeString($visibility);

		return $this->_db->newRecordset("
			SELECT s.*
			FROM site s INNER JOIN item i ON s.site_id=i.site_id
			WHERE (i.visibility & $sql__visibility)=$sql__visibility
			ORDER BY s.name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
<<<<<<< HEAD
	 * Find the sites used for equipment in a OU.
	 *
	 * @param  string  $ou_id  The OU ID to check for.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findForOU($ou_id) {

		$binds = array (
			'ou_id'  => $ou_id ,
=======
	 * Find the sites used for equipment in a department.
	 *
	 * @param  string  $department_id  The department ID to check for.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findForDepartment($department_id) {

		$binds = array (
			'department'  => $department_id ,
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
		);

		return $this->_db->newRecordset("
			SELECT DISTINCT s.*
			FROM site s
				INNER JOIN item i ON s.site_id=i.site
<<<<<<< HEAD
			WHERE i.ou_id=:ou_id
=======
			WHERE i.site=:site
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
			ORDER BY name ASC
		", $binds, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find a site by its name.
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
			FROM site
			WHERE name=:name
			LIMIT 1
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject') );
	}// /method



	/**
<<<<<<< HEAD
	 * Lookup the name of a site using its ID.
	 *
	 * Use find() if you only want a single lookup.
	 * This method uses caching to speed up subsequent lookups, so will be faster if you need more than one.
	 *
	 * @param  integer  $site_id
	 * @param  string  $default  (optional) The default name to return.
	 *
	 * @return  string  The site's name.  On fail, $default.
	 */
	public function lookupName($site_id, $default = '') {
		if (null === $this->_lookup) {
			$this->_lookup = $this->findAll()->toAssoc('id', 'name');
		}
		return (isset($this->_lookup[$site_id])) ? $this->_lookup[$site_id] : $default ;
	}// /method



	/**
=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	 * Insert a new site.
	 *
	 * @param  object  $object  The Site to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($object) {
		$binds = $this->convertObjectToRow($object);

		unset($binds['site_id']);   // Don't insert the id, we want a new one

		$new_id = $this->_db->insert('site', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a Site object.
	 *
	 * @return  object  A Site object.
	 */
	public function newSite() {
		return new Site();
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

		$affected_count = $this->_db->update('site', $binds, "site_id=$id");

		return ($affected_count>0);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
