<?php
/**
 * LDAP Resultset Iterator Class
 *
 * Allows you to iterate over the results of an LDAP search.
 * Limited to only returning one item per attribute (where multiple entries exist for the same attribute, they are ignored)
 * Implements lazy-loading.  The LDAP search is not executed until the data is requested.
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Ldap_Resultset implements Iterator {

	// Public properties


	// Private properties
	protected $_ldap = null;  // The Ecl_Ldap instance to use when searching
	protected $_connection = null;  // The current LDAP connection resource

	protected $_base_dn = '';   // The base DN in which to search.
	protected $_filter = '';    // The filter to execute
	protected $_attrs = null;   // The attributes to return

	protected $_executed = false;   // Has the current search been executed?

	protected $_result_set = null;   // The array of results

	protected $_count = null;   // The number of entries returned

	protected $_entry = null;            // The current entry data

	protected $_position = 0;   // Current iterator position

	protected $_entry_function = null;   // Entry conversion function (called on each entry returned)



	/**
	 * Constructor
	 *
	 * @param  object  $ldap  The ldap object to use when searching.
	 * @param  mixed  $base_dn  The base DN to search.
	 * @param  string  $filter  The filter to execute.
	 * @param  array  $attrs  The attributes to return.
	 * @param  callback  $entry_function  (optional) The entry callback function to use to convert entries. (default: null)
	 *
	 * @return  object  A new instance of this class.
	 */
	public function __construct($ldap, $base_dn, $filter, $attrs, $entry_function = null) {
		$this->_ldap = $ldap;

		$this->_connection = $this->_ldap->getConnectionResource();

		$this->_base_dn = $base_dn;
		$this->_filter = $filter;
		$this->_attrs = array_map('strtolower', $attrs);

		$this->_entry_function = $entry_function;
	}// /method



	public function __destruct() {
		@ldap_free_result($this->_result_set);
	}// /method



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function count() {
		if (!$this->_executed) { $this->_execute(); }
		if (is_null($this->_count)) { $this->_count = (int) ldap_count_entries($this->_connection, $this->_result_set); }
		return $this->_count;
	}// /method



	public function current() {
		$entry_attrs = ldap_get_attributes($this->_connection, $this->_entry);
		$entry_attrs = array_change_key_case($entry_attrs);

		$row = array();
		foreach($this->_attrs as $i => $attr) {
			if (isset($entry_attrs[$attr][0])) {
				$row[$attr] = $entry_attrs[$attr][0];
			}
		}


		if ($this->_entry_function) {
			return $$this->_entry_function($row);
		} else {
			// Simplify the attributes and dump all the numeric indexes
			return $row;
		}
	}// /method



	public function key() {
		return $this->_position;
	}// /method



	public function next() {
		$this->_position++;
		$this->_entry = ldap_next_entry($this->_connection, $this->_entry);
	}// /method



	public function rewind() {
		if (!$this->_executed) { $this->_execute(); }
		$this->_position = 0;
		if ($this->_result_set) {
			$this->_entry = ldap_first_entry($this->_connection, $this->_result_set);
		}
	}// /method



	public function valid() {
		return (is_resource($this->_entry));
	}// /method



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



	protected function _execute() {
		$this->_result_set = null;
		$this->_position = 0;
		$this->_entry = null;
		$this->_count = null;
		$this->_executed = true;

		$count = $this->_ldap->search($this->_base_dn, $this->_filter, $this->_attrs);

		if ($count>0) {
			$this->_result_set = $this->_ldap->getResultResource();
		}
		return true;

		/*
		$first_entry = ldap_first_entry($this->_connection, $this->_ldap->getResultResource());
		$entry_attrs = ldap_get_attributes($this->_connection, $first_entry);
		$this->_array = array_merge($this->_attrs, $entry_attrs);   // Simplify the attributes and dump all the numeric indexes
		 */
	}// /method



}// /class
?>