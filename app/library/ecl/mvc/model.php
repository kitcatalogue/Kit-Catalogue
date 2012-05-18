<?php



Ecl::load('Ecl_Mvc');



class Ecl_Mvc_Model_Exception extends Ecl_Mvc_Exception {}



/**
 * A class for handling access to model-level objects.
 *
 * This class operates as a singleton.
 * Key-Value pairs, Objects and Functions can be retrieved/called with ->get($name)
 * Objects and Functions only can also be called as methods, with ->name()
 *
 * @package  Ecl
 * @version  1.2.0
 */
Class Ecl_Mvc_Model {

	// Public properties

	// Private properties
	protected static $_instance = null;   // Internal object reference

	protected $_include_paths = array('.');

	protected $_function_map = array();   // Array of named functions
	protected $_instance_map = array();   // Array of named objects
	protected $_registry = array();       // Array of named key-value pairs

	protected $_default_factory = null;   // Factory function to call if requested name does not exist



	/**
	 * Constructor
	 */
	protected function __construct() {
		spl_autoload_register(array(__CLASS__, '_autoload'), false, true);
	}// /->__construct()



	/**
	 * Call
	 */
	public function __call($name, $args) {
		if (array_key_exists($name, $this->_instance_map)) {
			return $this->_instance_map[$name];
		}

		if ( (isset($this->_function_map[$name])) && (is_callable($this->_function_map[$name])) ) {
			$result = $this->_function_map[$name]($this);
			$this->set($name, $result);
			return $result;
		}

		// Try using the default factory instead
		if (is_callable($this->_default_factory)) {
			$result = call_user_func($this->_default_factory, $name, $this);
			if ($result) {
				$this->set($name, $result);
				return $result;
			}
		}
		return null;
	}// /__call()



	/**
	 * Clone
	 *
	 * This class is a singleton and should not be cloned
	 *
	 * @return  object  A new instance of this class.
	 */
	protected function __clone() {
		throw new Ecl_Router_Model_Exception('Unable to clone a singleton class', 1);
	}// /->__clone()



	public function __get($name) {
		return $this->get($name);
	}


	/**
	 * Get the instance of this object.
	 *
	 * Always returns a reference to the same singleton object.
	 *
	 * @return  object  The object instance.
	 */
	public static function singleton() {
		if (!is_object(self::$_instance)) {
			$class_name = __CLASS__;
			self::$_instance = new $class_name;
		}
		return self::$_instance;
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Add an include path to check when autoloading classes.
	 *
	 * @param  string  $path
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addIncludePath($path) {
		$this->_include_paths[] = Ecl_Helper_Filesystem::fixPath($path);
		return true;
	}// /method



	/**
	 * Check the given entry exists.
	 *
	 * @param  string  $name
	 *
	 * @return  boolean  The entry exists.
	 */
	public function exists($name) {
		return (array_key_exists($name, $this->_registry)) || (array_key_exists($name, $this->_instance_map)) || (array_key_exists($name, $this->_function_map));
	}// /method



	/**
	 * Get the corresponding value or object, or the result of the named function.
	 *
	 * @param  string  $name
	 *
	 * @return  mixed  The result.
	 */
	public function get($name) {
		if (array_key_exists($name, $this->_registry)) {
			return $this->_registry[$name];
		} else {
			$name = (string) $name;
			return $this->$name();
		}
	}// /method



	/**
	 * Load the given assoc-array of key-value pairs.
	 *
	 * The assoc values can also be objects or functions.
	 *
	 * @param  array  $assoc
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function load($assoc) {
		foreach($assoc as $k => $v) {
			$this->set($k, $v);
		}
		return true;
	}// /method



	/**
	 * Set the given key-value, object or function.
	 * @param  string  $name
	 * @param  mixed  $value  The value, object or function to set.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function set($name, $value) {
		if (is_callable($value)) { return $this->setFunction($name, $value); }
		if (is_object($value)) { return $this->setObject($name, $value); }
		return $this->setValue($name, $value);
	}// /method



	/**
	 * Set the default factory method to use if a requested model name is not found.
	 *
	 * The function should be of the form:
	 * function ($name, $model) { ... }
	 *
	 * @param  callback  The factory function.
	 *
	 * @return  mixed  The resulting object.
	 */
	public function setDefaultFactory($callable = null) {
		if (is_callable($callable)) {
			$this->_default_factory = $callable;
			return true;
		}

		return true;
	}// /method



	/**
	 * Set the given object creation function.
	 *
	 * Once set, functions (and their corresponding objects) are retrieved via $model->functionname();
	 * Functions can only be called once.  Once a function has run, it will be replaced in the model by the function's result.
	 * Function declarations MUST include the $model parameter which allows the function access back to the model itself.
	 *
	 * Example:
	 *
	 * ->set('itemStore', function ($model) {
	 *   require('path/to/class/item_store.php');
	 *   $item_store = new itemStore($model->db());
	 *   return $item_store;
	 * });
	 *
	 * @param  string  $name  The name of the function.
	 * @param  string  $callable  The function to use.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setFunction($name, $callable) {
		$this->remove($name);
		if (!is_callable($callable)) {
			return false;
		} else {
			$this->_function_map[$name] = $callable;
			return true;
		}
	}// /method



	/**
	 * Set the given value.
	 *
	 * @param  string  $name
	 * @param  mixed  $value
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setValue($name, $value) {
		$this->remove($name);
		return $this->_registry[$name] = $value;
	}// /method



	/**
	 * Set the object to use for a given name.
	 *
	 * @param  string  $name
	 * @param  string  $object
	 *
	 * @param  boolean  The operation was successful.
	 */
	public function setObject($name, $object) {
		$this->remove($name);
		if (!is_object($object)) {
			return false;
		} else {
			$this->_instance_map[$name] = $object;
			return true;
		}
	}// /method



	/**
	 * Remove the reference to the given function/object.
	 *
	 * @param  string  $name
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function remove($name) {
		unset($this->_registry[$name]);
		unset($this->_function_map[$name]);
		unset($this->_instance_map[$name]);
		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	/**
	 * Autoloader for classes intended for use in the model.
	 *
	 * Use ->addIncludePath() to add paths to automatically search when autoloading.
	 *
	 * @param  string  $class_name  The class to load
	 *
	 * @return  boolean  The operation was successful.
	 */
	protected function _autoload($class_name) {
		if (class_exists($class_name)) { return true; }

		if (empty($this->_include_paths)) { return false; }

		$file_path = strtolower($class_name);
		$file_path = str_replace('_', '/', $file_path);
		$file_path .= '.php';

		foreach($this->_include_paths as $i => $path) {
			$class_path = $path . '/'. $file_path;

			if (file_exists($class_path)) {
				include($class_path);
				return (class_exists($class_name));
			}
		}
		return false;
	}// /method



}// /Class
?>