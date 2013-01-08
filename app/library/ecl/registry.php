<?php



class Ecl_Registry_Exception extends Ecl_Exception {}



/**
 * Registry object for managing application-wide variables and object references.
 *
 * The registry operates as a singleton.
 * It stores a registry of key-value pairs, manages the instantiation of any named objects registered with it,
 * and provide access to stored functions .
 *
 * @package  Ecl
 * @version  1.2.0
 */
Class Ecl_Registry {

	// Public properties

	// Private properties
	protected static $_instance = null;   // Internal object reference

	protected $_function_map = array();   // Array of named functions
	protected $_object_map = array();   // Array of named classes and include paths

	protected $_registry = array();      // Array of object references being stored



	/**
	 * Constructor
	 */
	protected function __construct($config = null) {
	}// /->__construct()



	/**
	 * Call
	 */
	public function __call($name, $args) {
		if ( (isset($this->_function_map[$key])) && (is_callable($this->_function_map[$key])) ) {
			return call_user_func_array($this->_function_map[$key], $args);
		}
	}// /__call()



	/**
	 * Clone
	 *
	 * This class is a singleton and should not be cloned
	 *
	 * @return  object  A new instance of this class.
	 */
	protected function __clone() {
		throw Ecl_Registry_Exception('You cannot clone a singleton class');
	}// /->__clone()



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
	 * Get the requested entry.
	 *
	 * If the requested registry entry does not exist already, the function map will be checked.
	 * If the entry does not exist in the function map, the object map will be checked.
	 * If the entry exists in the object map, it will be instantiated and added to the registry.
	 * If the entry does not exist in either the registry or the object map, the default will be returned.
	 *
	 *
	 * @param  string  $key  The name of the entry.
	 * @param  mixed  $default  The default to return if not found, and not a registered object.
	 *
	 * @return  mixed  The requested entry. On fail, the default.
	 */
	public function get($key, $default = null) {
		if (isset($this->_registry[$key])) {
			return $this->_registry[$key];
		} elseif (isset($this->_function_map[$key])) {
			return $this->$key($this);
		} elseif (isset($this->_object_map[$key])) {

			if (require_once($this->_object_map[$key]['include'])) {
				if (is_null($this->_object_map['config'])) {
					$object = new $this->_object_map[$key]['class']();
				} else {
					$object = new $this->_object_map[$key]['class']($this->_object_map[$key]['config']);
				}

				if ( ($this->_object_map['singleton']) && (is_object($object)) ) {
					$this->set($key, $object);
					return $object;
				}

			}// /if (included OK)
		}// /if (name exists)

		return $default;
	}// /method



	/**
	 * Check the given registry entry exists.
	 *
	 * @param  string  $key  The name of the entry.
	 *
	 * @return  boolean  The entry exists.
	 */
	public function exists($key) {
		return (array_key_exists($key, $this->_registry)) || (array_key_exists($key, $this->_object_map))|| (array_key_exists($key, $this->_function_map));
	}// /method



	/**
	 * Set the given function.
	 *
	 *
	 * When retrieved through __call() or get() the given function is run and the result returned.
	 * Function declarations *should* include the $registry parameter which allows the function access to the registry itself.
	 * For complex object creation, dependency injection, or non-singleton creation, you may find this method to be more useful than setObject().
	 * Retrieval through get() will only use the $registry parameter. To access more parameters, call the stored function as if it were a method, e.g. $reg->myFunc($a, $b, $c, ..)
	 *
	 * Example: basic object factory
	 *
	 * ->setFunction('mydatabase', function ($registry) {
	 *   require('path/to/mydatabase.php');
	 *   $db = new MyDatabase('server', 'port', 'user', 'pass');
	 *   $db->setDebug(true);
	 *   return $db;
	 * });
	 *
	 * Example: singleton factory
	 *
	 * ->setFunction('mydatabase', function ($registry) {
	 *   static $mydb;
	 *
	 *   require('path/to/mydatabase.php');
	 *
	 *   if (is_null($mydb)); {
	 *     $mydb = new MyDatabase('server', 'port', 'user', 'pass');
	 *     $mydb->setDebug(true);
	 *   }
	 *   return $mydb;
	 * });
	 *
	 * @param  string  $key  The name of the function.
	 * @param  string  $callable  The function to use.
	 *
	 * @param  boolean  The operation was successful.
	 */
	public function setFunction($key, $callable) {
		$this->remove($key);
		if (!is_callable($callable)) {
			return false;
		} else {
			$this->_function_map[$key] = $callable;
			return true;
		}
	}// /method



	/**
	 * Set the given object using the given instantiation information.
	 *
	 * Allows the registry object to operate as an object factory, using ->get()
	 * to return the instance of an object registered with it.
	 * For more complex object creation, use setFunction() and create the object within the function
	 *
	 * Example:
	 * ->setObject('database', 'MyDatabase', '/www/example/www/include/', true, array('host' = 'localhost' , .. ) )
	 *
	 * @param  string  $key  The name of the object.
	 * @param  string  $class  The class name of the object.
	 * @param  string  $include_file  The include file the class is in.
	 * @param  boolean  $singleton  The object should operate as a single and only be created once.  (default: false)
	 * @param  mixed  $config  (optional) The constuctor configuration settings, as an assoc-array.  (default: null)
	 *
	 * @param  boolean  The operation was successful.
	 */
	public function setObject($key, $class, $include_file, $singleton = false, $config = null) {
		$this->remove($key);
		$this->_object_map[$key] = array (
			'class'      => $class ,
			'include'    => $include_file ,
			'singleton'  => (bool) $singleton ,
			'config'     => $config ,
		);
		return true;
	}// /method



	/**
	 * Remove the reference to the given variable.
	 *
	 * @param  string  $key  The name of the variable.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function remove($key) {
		unset($this->_registry[$key]);
		unset($this->_function_map[$key]);
		unset($this->_object_map[$key]);
		//if (isset($this->_registry[$key])) { unset($this->_registry[$key]); }
		//if (isset($this->_function_map[$key])) { unset($this->_function_map[$key]); }
		//if (isset($this->_object_map[$key])) { unset($this->_object_map[$key]); }
		return true;
	}// /method



	/**
	 * Set the given named variable to the given value.
	 *
	 * As objects are stored by reference, if the original object changes so will the context object's version.
	 *
	 * @param  string  $key  The name of the entry.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function set($key, $var) {
		$this->remove($key);
		$this->_registry[$key] = $var;
		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /Class
?>