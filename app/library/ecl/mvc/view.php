<?php



class Ecl_Mvc_View_Exception extends Ecl_Mvc_Exception {}



/**
 * A class for handling views.
 *
 * You can use templates straight away with this class.
 * Alternatively, inherit from this class to provide extra functionality such as specific view methods.
 *
 * To define generic headers, footers and other layout you want applying to every page, use/change the router's layout object.
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Mvc_View extends Ecl_Mvc {

	// Public Properties


	// Private Properties
	protected $_encoding = 'utf-8';

	protected $_params = array();



	/**
	 * @see param()
	 */
	public function __get($key) {
		return $this->param($key, null);
	}// /method



	public function __isset($key) {
		return isset($this->_params[$key]);
	}// /method



	/**
	 * @see setParam()
	 */
	public function __set($key, $value) {
		$this->setParam($key, $value);
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Escape the given string for output.
	 *
	 * @param  string  $string
	 * @param  string  $charset  (optional)
	 *
	 * @return  string  The escaped string.
	 */
	public function escape($string, $charset = 'UTF-8') {
		return htmlspecialchars($string, ENT_COMPAT | ENT_IGNORE, $charset, false);
	}// /method



	/**
	 * @return  string  The view name.
	 */
	public function getName() {
		$name = strtolower(get_class($this));
		$name = substr($name, strlen('view_'));
		return $name;
	}// /method



	/**
	 * Initialise the view.
	 *
	 * @param  object  $router  The router that created this view object.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function init($router) {
		parent::init($router);
		$this->_html = Ecl::factory('Ecl_Html');
		return true;
	}// /method



	/**
	 * @parm  mixed  $value  The value/object/etc to encode.
	 *
	 * @return  string  The JSON encoding.
	 */
	public function json($value) {
		return json_encode($value);
	}// /method



	/**
	 * Load an assoc-array of key-value pairs as view parameters.
	 *
	 * @param  array  $assoc
	 *
	 * @return  The operation was succesful.
	 */
	public function loadParams($assoc) {
		if ( (!empty($assoc)) && (is_array($assoc)) ) {
			foreach($assoc as $key => $value) {
				$this->setParam($key, $value);
			}
		}
		return true;
	}// /method



	/**
	 * Echo out the escaped version of the given value.
	 *
	 * The value will be escaped for HTML.
	 *
	 * @param  mixed  $value
	 *
	 * return  string  The operation was successful.
	 */
	public function out($value) {
		echo $this->escape($value);
	}// /method



	/**
	 * Echo out the escaped value, if it is not empty.
	 *
	 * @param  mixed  $value
	 * @param  string  $format  (default: '%s')
	 *
 	 * return  boolean  The operation was successful.
	 */
	public function outf($value, $format = '%s') {
		if (!empty($value)) {
			printf($format, $this->escape($value));
		}
		return true;
	}



	/**
	 * Echo out the escaped version of the requested parameter's value.
	 *
	 * The value will be escaped for HTML.
	 *
	 * @param  string  $key
	 * @param  string  $default  (optional)
	 *
	 * return  string  The operation was successful.
	 */
	public function outParam($key, $default = null) {
		echo $this->escape($this->param($key, $default));
		return true;
	}// /method



	/**
	 * Get the value of the given view parameter.
	 *
	 * @param  string  $key  The key to retrieve.
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The requested value. On fail, the default.
	 */
	public function param($key, $default = null) {
		return (isset($this->_params[$key])) ? $this->_params[$key] : $default ;
	}// /method



	/**
	 * Render and echo out a view.
	 *
	 * @param  string  $view_name
	 * @param  string  $module_name  (optional)  If not given, the router's current module will be .
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function render($view_name, $module_name = null) {
		if (empty($module_name)) { $module_name = $this->router()->getCurrentModule(); }

		$include_path = $this->router()->viewPath($view_name, $module_name);

		if (!file_exists($include_path)) {
			if (empty($module_name)) {
				throw new Ecl_Mvc_View_Exception("Unknown view: '$view_name'.", 1);
			} else {
				throw new Ecl_Mvc_View_Exception("Unknown view: '$view_name' in module '$module_name'.", 1);
			}
		} else {
			include($include_path);
		}
	}// /method



	/**
	 * Render a view and return it as a string.
	 *
	 * @return  string  The output of the view.
	 */
	public function renderToString($view_template) {
		ob_start();
		$this->render($view_template);
		return ob_get_clean();
	}// /method



	/**
	 * Set a view parameter.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 *
	 * @return  The operation was succesful.
	 */
	public function setParam($key, $value) {
		$this->_params[$key] = $value;
		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>