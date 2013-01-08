<?php
/**
 * Route URLs to the correct controller and action using placeholders and/or defaults. *
 *
 * Reserved default-parameters: 'module', 'controller', 'action', 'format', 'redirect'
 *
 * @package  Ecl
 * @version  1.0.0
 */
Class Ecl_Mvc_Router_Route {

	// Public properties

	// Private properties
	protected $_method = '';
	protected $_route = '';
	protected $_defaults = array();



	/**
	 * Constructor
	 *
	 * @param  string  $route  The route to match against.
	 * @param  mixed  $defaults  (optional) The default paramater values. (default: null)
	 */
	public function __construct($method, $route, $defaults = array() ) {
		$this->_method = $method;
		$this->_route = $route;
		$this->_defaults = (array) $defaults;
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Check if the given request matches this route.
	 *
	 * If the route matches, the resulting array will be of the form:
	 * array (
	 *   'controller'  => 'controller to call' ,
	 *   'action'      => 'action to call' ,
	 *   ...   // Any other parameters defined in the route path, or the defaults array.
	 * )
	 *
	 * @param  object  $request  The Request object to check.
	 *
	 * @return  mixed  An array of parameter values. If no match, returns false.
	 */
	public function match($request) {

  		$delimiter_char = '/';
		$variable_char = ':';

		$path = $request->relativePath();

		$path_parts = explode($delimiter_char, trim($path, $delimiter_char) );

		$route_parts = explode($delimiter_char, trim($this->_route, $delimiter_char) );


		if ( ($this->_method!='*') && ($this->_method!=$request->httpMethod()) ) {
			return false;
		}


		// @idea : Possibly handle wildcard route paths??  e.g. /path/*   matches  /path/any/other/path


		// If the number of parts doesn't match, then the route doesn't match
		if (count($path_parts)!=count($route_parts)) { return false; }

		// Set the default params
		$params = (array) $this->_defaults;

		// Check the route parts
		$unnamed_count = 0;
		foreach($route_parts as $i => $part) {

			// If the part is a named parameter, find its value
			if ((substr($part, 0, 1)==$variable_char) && (substr($part, 1, 1)!=$variable_char)) {
				$param_name = substr($part, 1);
				if (!empty($param_name)) {
					$params[$param_name] = urldecode($path_parts[$i]);
				} else {
					return false;   // Match failed as a parameter was missing
				}
			} else {
				if ($part!=$path_parts[$i]) {
					return false;
				}
			}
		}// /foreach(route part)

		return $params;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /Class
?>