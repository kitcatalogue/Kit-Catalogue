<?php
/**
 * EngCETL Coding Library.
 *
 * This class must be called statically.
 * On include, Ecl::start() will be called.  This method automatically checks for a timezone setting.
 * If no default timezone is found, it will be set to "Europe/London" and the locale set to "en_UK.UTF8".
 * An Ecl autoloader will be registered.
 *
 * @static
 * @version  6.3.0
 */
class Ecl {

	// Public Properties

	// Private Properties
	protected static $_started = false;   // Has the Ecl system been started?

	protected static $_version = '6.5';   // Current ECL version

	protected static $_ecl_root = null;

	protected static $_factoried_objects = array();



	/**
	 * Constructor
	 */
	final private function __construct() {
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Check if the given bits are set (any bits or all bits)
	 *
	 * @param  integer  $bits  The number to examine.
	 * @param  integer  $want_bits  The bit-flags to check for.
	 * @param  boolean  $must_have_all  To pass, $bits must contain all the flags in $want_bits.  If false, any flag will pass.
	 *
	 * @return  boolean  The number contains the given binary flags.
	 */
	public static function checkBits($bits = 0, $want_bits = 0, $must_have_all = false) {
		return ($must_have_all) ? (($bits & $want_bits) == $want_bits) : (($bits & $want_bits) > 0);
	}// /method



	/**
	 * Show the object or class description in output.
	 *
	 * @param  mixed   $obj  The object/class name to describe.
	 *
	 * @return  boolean  True in all cases.
	 */
	public static function describe($obj) {
    	return Ecl_Debug::describe($obj);
	}// /method



    /**
     * Show information about a variable in output.
     *
     * @deprecated
     *
     * @param  mixed   $var  The variable to output.
     * @param  string  $label  (optional) A label to output before the dump.
     * @param  boolean  $use_html_entities  (optional) Encodes any HTML entities before output. (default: true)
	 *
	 * @return  boolean  True in all cases.
     */
    public static function dump($var, $label = null, $use_html_entities = true) {

    	$trace = debug_backtrace();
	    $file = $trace[0]['file'];

	    if (strpos($file, $_SERVER['DOCUMENT_ROOT'])===0) {
	    	$file = substr($file, strlen($_SERVER['DOCUMENT_ROOT']));
	    }

	    $loc = "(Line {$trace[0]['line']} in $file)";

    	return Ecl_Debug::dump($var, "$label $loc", $use_html_entities);
    }// /method



    /**
     * Create a new Ecl_Exception instance.
     *
     * This method only creates an exception, it does not throw it automatically.
     *
     * @param  string  $message
     * @param  integer  $code
     * @param  mixed  $previous  The previous Exception object. (default: null)
     *
     * @return  object  An Ecl_Exception object.
     */
    public static function exception($message = '', $code = 0, Exception $previous = null) {
		return new Ecl_Exception ($message, $code, $previous);
	}// /method



	/**
	 * Load an Ecl-based class and instantiate it.
	 *
	 * If the class has a singleton() method then that will be called instead of the normal constructor.
	 * For instance reuse to work properly, all calls to factory() to request a particular object should set $reuse = true.
	 *
	 * @param  string  $class  The class name to load.
	 * @param  mixed  $config  (optional) Assoc-array of config info to pass to the constructor.  (default: null)
	 * @param  boolean  $reuse  (optional) Re-use existing instances of this class.  (default: false)
	 *
	 * @return  mixed  A new object.  On fail, null.
	 */
	public static function factory($class, $config = null, $reuse = false) {
		if (self::load($class)) {

			if ($reuse) {
				if (isset(self::$_factoried_objects[$class])) {
					return self::$_factoried_objects[$class];
				}
			}

			if ( (!$reuse) || (!isset(self::$_factoried_objects[$class])) || (!is_object(self::$_factoried_objects[$class]))) {
				// Does the class have a singleton() method?
				if (is_callable(array($class, 'singleton'))) {
					$obj = $class::singleton($config);   //call_user_func(array($class, 'singleton'));
					if (!is_object($obj)) {
						$obj = null;
					}
				} else {
					$obj = new $class($config);
				}

				if (!$reuse) {
					return $obj;
				} else {
					if (!isset(self::$_factoried_objects[$class])) {
						self::$_factoried_objects[$class] = $obj;
					}
					return self::$_factoried_objects[$class];
				}
			}
		}

		return null;
	}// /method



	/**
	 * Is the given value empty?
	 *
	 * Wrapper for empty() so function/method returns can be check directly.
	 * Enables use of empty() on methods and variable-functions.
	 *
	 * @param  mixed  $var  Variable, Function or Method result to test.
	 *
	 * @return  boolean  The given variable is empty (blank/0/null).
	 */
	public static function isEmpty($var) {
		return empty($var);
	}// /method



    /**
     * Loads an ECL class file using the auto-discovered path.
     *
     * @param  string  $file_path  Path of the file to load.
	 *
     * @return  boolean  The loading was successful.
     */
	public static function load($class_name) {

		if (class_exists($class_name)) { return true; }

		$file_path = strtolower($class_name);
		$file_path = str_replace('_', '/', $file_path);
		$file_path = self::$_ecl_root."/{$file_path}.php";

		if (file_exists($file_path)) {
			include($file_path);
			return (class_exists($class_name));
		}
		return false;
	}// /method



	/**
	 * Initialise the Ecl library
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function start() {

		if (self::$_started===true) { return true; }

		$_started = true;

		// Set the include root for Ecl classes
		self::$_ecl_root = dirname(__FILE__);

		// Check locale settings, set defaults if necessary
//		if (Ecl::isEmpty(ini_get('date.timezone'))) {
//			date_default_timezone_set('GMT');
//			setlocale(LC_ALL, 'en_UK.UTF8');
//		}

		// Register the ECL autoloader
		spl_autoload_register(array('Ecl', 'load'), false);

		return true;
	}// /method



	/**
     * Show backtrace information in output.
     *
     * @param  string  $label  (optional) A label to output before the dump.
     *
	 * @return  boolean  True in all cases.
	 */
	public static function trace($label = null) {
    	return Ecl_Debug::trace($label);
	}// /method



	/**
	 * Get the current ECL version string.
	 *
	 * @return  string  The version string.
	 */
	public static function version() {
		return self::$_version;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class



Ecl::start();



?>