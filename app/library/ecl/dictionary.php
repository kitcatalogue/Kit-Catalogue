<?php
/**
 * Dictionary for managing array based key-value pairs.
 *
 * @package  Ecl
 * @version  1.0.0
 */
Class Ecl_Dictionary implements ArrayAccess {

	// Public properties

	// Private properties
	protected $_registry = array();   // Array of key-value pairs



	/**
	 * Constructor
	 */
	public function __construct($config = null) {
		if (isset($config['dictionary'])) {
			$this->load($config['dictionary']);
		}
	}// /->__construct()



	public function __get($name) {
		return $this->get($name);
	}



	public function __set($name, $value) {
		$this->set($name, $value);
		return $value;
	}



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Check the given registry entry exists.
	 *
	 * @param  string  $key  The name of the entry.
	 *
	 * @return  boolean  The entry exists.
	 */
	public function exists($key) {
		return (array_key_exists($key, $this->_registry));
	}// /method



	/**
	 * Get the requested entry.
	 *
	 * @param  string  $key  The name of the entry.
	 * @param  mixed  $default  The default to return if not found.
	 *
	 * @return  mixed  The requested entry. On fail, the default.
	 */
	public function get($key, $default = null) {
		return (array_key_exists($key, $this->_registry)) ? $this->_registry[$key] : $default ;
	}// /method



	/**
	 * Load the keys listed in the given assoc array.
	 *
	 * @param  array  $assoc  .
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function load($assoc) {
		$this->_registry = array_merge($this->_registry, $assoc);
		return true;
	}// /method



	public function offsetExists($offset) {
		return $this->exists($offset);
	}



	public function offsetGet($offset) {
		return $this->get($offset);
	}



	public function offsetSet($offset, $value) {
		$this->set($offset, $value);
	}



	public function offsetUnset($offset) {
		remove($offset);
	}



	/**
	 * Remove the reference to the given variable.
	 *
	 * @param  string  $key  The name of the variable.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function remove($key) {
		unset($this->_registry[$key]);
		return true;
	}// /method



	/**
	 * Set the given named variable to the given value.
	 *
	 * @param  string  $key  The name of the entry.
	 * @param  mixed  $value  The new value.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function set($key, $value) {
		$this->_registry[$key] = $value;
		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /Class
?>