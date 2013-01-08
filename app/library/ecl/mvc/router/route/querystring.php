<?php
/**
 * Route URLs to the correct controller and action using querystring paramters.
 *
 * @package  Ecl
 * @version  1.0.0
 */
Class Ecl_Mvc_Router_Route_Querystring extends Ecl_Mvc_Router_Route {

	// Public properties

	// Private properties



	/**
	 * Constructor
	 *
	 * @param  string  $route  The route to match against.
	 * @param  mixed  $defaults  (optional) The default paramater values. (default: null)
	 */
	public function __construct($route, $defaults = array() ) {
		parent::__construct($route, $defaults);
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Check if the given request matches this route.
	 *
	 * @param  object  $request  The Request object to check.
	 *
	 * @return  mixed  An array of parameter values. If no match, returns false.
	 */
	public function match($request) {

		$parts = Ecl_Helper_String::parseQuerystring($this->_route);

		$route_parts = null;

		// Identify which querystring values will be mapped to param values
		if (is_array($parts)) {
			foreach($parts as $k => $v) {
				if (strlen($v)>0) {
					if ((substr($v, 0, 1)==$this->_urlVariable) && (substr($v, 1, 1)!=$this->_urlVariable)) {
						$param_name = substr($v, 1);
						$route_parts[$k] = $param_name;
					}
				}
			}
		}

		// Set the default params
		$params = (array) $this->_defaults;

		// Process the found params
		$actual_count = 0;

		if ($route_parts) {
			foreach($route_parts as $k => $param_name) {
				$actual_param = $request->get($k, false);
				if ($actual_param!==false) {
					$params[$param_name] = $actual_param;
					$actual_count++;
				}
			}
		}

		if ($actual_count!=count($route_parts)) { return false; }

		return $params;
	}// /method




/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /Class
?>