<?php
/**
 * A hook dictionary for registering and executing multiple functions against a named key.
 *
 * Similar to a listener list, but instead of notifying objects, this class executes functions.
 *
 * @package  Ecl
 * @version  1.0.0
 */
Class Ecl_Hooks {

	// Public properties

	// Private properties
	protected $_force_params = array();
	protected $_registry = array();



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Add the given callable function to the named key.
	 *
	 * @param  string  $key
	 * @param  callback  $callable
	 * @param  integer  $priority  The call priority for this function, lower is higher.  (default: 10)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function add($key, $callable, $priority = 10) {
		$priority = (integer) $priority;
		$this->_registry[$key][$priority][] = $callable;
		return true;
	}// /method



	/**
	 * Remove all the function references on the given key.
	 *
	 * @param  string  $key.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function clear($key) {
		unset($this->_registry[$key]);
		return true;
	}// /method



	/**
	 * Check a function exists for the given key.
	 *
	 * @param  string  $key  The name of the entry.
	 *
	 * @return  boolean  The entry exists.
	 */
	public function exists($key) {
		return (array_key_exists($key, $this->_registry));
	}// /method



	/**
	 * Execute any and all functions registered with the given key.
	 *
	 * @param  string  $key  The name of the entry.
	 * @param  array  $args  An array of parameters.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function executeAll($key, $args) {
		if (!$this->exists($key)) { return false; }

		ksort($this->_registry[$key]);

		$func_args = func_get_args($args);
		unset($func_args['key']);

		foreach((array) $this->_registry[$key] as $i => $priority_functions) {
			foreach($priority_functions as $j => $function) {
				call_user_func_array($function, $func_args);
			}
		}
		return true;
	}// /method



	/**
	 * Execute any functions registered with the given key, but stop processing when a function returns $result.
	 *
	 * @param  string  $key  The name of the entry.
	 * @param  array  $args  An array of parameters.
	 *
	 * @return  mixed  The function result. On fail, null.
	 */
	public function executeUntilResult($key, $args) {
		if (!$this->exists($key)) { return false; }

		$res = null;
		foreach((array) $this->_registry[$key] as $i => $priority_functions) {
			foreach($priority_functions as $j => $function) {
				$res = call_user_func_array($function, $args);
				if (!empty($res)) { return $res; }
			}
		}
		return $res;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /Class
?>