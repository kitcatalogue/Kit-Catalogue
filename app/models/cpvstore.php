<?php



include_once('cpvcode.php');



/**
 * CPV Code Store class
 *
 * @version 1.0.0
 */
class Cpvstore {

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
		return array (
			'cpv_id'  => $object->id ,
			'name'    => $object->name ,
			'jumpable'  => (($object->jumpable) ? 1 : 0) ,
			'visible'   => (($object->visible) ? 1 : 0) ,
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
		$object = $this->newCpvcode();

		$object->id = $row['cpv_id'];
		$object->name = $row['name'];
		$object->jumpable = (1 == $row['jumpable']);
		$object->visible = (1 == $row['visible']);

		return $object;
	}// /method



	/**
	 * Find the CPV code(s) specified.
	 *
	 * @param  mixed  $id  The ID, or array of IDs, to find.
	 *
	 * @return  mixed  The object, or Ecl_Db_Recordset of object,s requested.
	 */
	public function find($id) {
		if (is_array($id)) {
			$id_set = $this->_db->prepareSet($id);

			return $this->_db->newRecordset("
				SELECT *
				FROM cpv_code
				WHERE cpv_id IN $id_set
				ORDER BY cpv_id ASC
			", null, array($this, 'convertRowToObject') );
		} else {

			$binds = array (
				'cpv_id'  => (int) $id ,
			);

			$row_count = $this->_db->query("
				SELECT *
				FROM cpv_code
				WHERE cpv_id=:cpv_id
				LIMIT 1
			", $binds);

			return $this->_db->getObject(array($this, 'convertRowToObject') );
		}
	}// /method



	/**
	 * Find all CPV codes, regardless of visibility.
	 *
	 * @return  object  Ecl_Recordset.  On fail, null.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
			SELECT *
			FROM cpv_code
			ORDER BY cpv_id ASC
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find all CPV codes hilighted as jumpable points.
	 *
	 * @return  object  Ecl_Recordset.  On fail, null.
	 */
	public function findAllJumpable() {
		return $this->_db->newRecordset("
			SELECT *
			FROM cpv_code
			WHERE jumpable='1'
			ORDER BY name ASC
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find all CPV codes that are visible.
	 *
	 * @return  object  Ecl_Recordset.  On fail, null.
	 */
	public function findAllVisible() {
		return $this->_db->newRecordset("
			SELECT *
			FROM cpv_code
			WHERE visible='1'
			ORDER BY cpv_id ASC
		", null, array($this, 'convertRowToObject'));
	}// /method



	public function findMatches($query) {

		$query = trim($query);

		if (empty($query)) { return null; }

		$binds = array (
			':query'      => $query ,
			':likequery'  => '%'. $query .'%' ,
		);

		/*
		 * Sorting by relevancy checking is currently disabled
		 *
 		return $this->_db->newRecordset("
			( SELECT cpv_id, name, MATCH (name) AGAINST (:query) AS relevancy
			FROM cpv_code
			WHERE MATCH (name) AGAINST (:query) )
			UNION (
			SELECT cpv_id, name, 0 AS relevancy
			FROM cpv_code
			WHERE name LIKE :likequery
			LIMIT 20 )
			ORDER BY relevancy DESC, cpv_id;
		", $binds, array($this, 'convertRowToObject'));
		 */

		/*
		 * The first sub-query returns the fulltext search results
		 * The second sub-query returns the basic LIKE search.
		 * This helps when locating small words missed by fulltext searching
		 */

		return $this->_db->newRecordset("
			(
				SELECT *
				FROM cpv_code
				WHERE visible='1' AND MATCH (name) AGAINST (:query)
			)
			UNION DISTINCT
			(
				SELECT *
				FROM cpv_code
				WHERE visible='1' AND name LIKE :likequery
				LIMIT 10
			)
			ORDER BY name ASC
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find all the higher-level code categories.
	 *
	 * This returns the codes that start with only 2 significant digits.
	 *
	 * @return  object  Ecl_Recordset.  On fail, null.
	 */
	public function findTopLevelCodes() {
		return $this->_db->newRecordset("
			SELECT *
			FROM cpv_code
			WHERE cpv_id LIKE '__000000-_'
			ORDER BY cpv_id ASC
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Get a new instance of a Category object.
	 *
	 * @return  object  A Category object.
	 */
	public function newCpvcode() {
		return new Cpvcode();
	}// /method



	/**
	 * Set which sections of CPV codes should be jumpable.
	 *
	 * Jumpable codes are a way of providing easy access to often used sections of the CPV list.
	 *
	 * @param  array  $codes
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setJumpableCodes($codes) {
		$binds = array (
			'jumpable'  => 0 ,
		);
		$this->_db->update('cpv_code', $binds);

		$binds['jumpable'] = 1;
		$this->_db->update('cpv_code', $binds, 'cpv_id IN '.$this->_db->prepareSet($codes));

		return true;
	}// /method



	/**
	 * Set which sections of CPV codes should be visible.
	 *
	 * $subcodes should be the left-most digits of the CPV code to enable, which will therefore
	 * enable the top level, and all the associated CPV sub categories.
	 * e.g. '16' would enable "Agricultural machinery" and all its sub-codes (anything of the form 16xxxxx-x)
	 *
	 * @param  array  $subcodes
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setVisibleSubcodes($subcodes) {
		$binds = array (
			'visible'  => 0 ,
		);
		$this->_db->update('cpv_code', $binds);


		array_walk($subcodes, function (&$v, $k) {
			$v = "(cpv_id LIKE '$v%')";
		});

		$where = implode(' OR ', $subcodes);

		$binds['visible'] = 1;
		$this->_db->update('cpv_code', $binds, $where);

		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
