<?php
/**
 * Route static URLs to the correct controller and action.
 *
 * All routing information MUST be given in the constructor's $defaults parameter
 *
 * @package  Ecl
 * @version  1.0.0
 */
Class Ecl_Mvc_Router_Route_Static extends Ecl_Mvc_Router_Route {

	// Public properties

	// Private properties



	/**
	 * Constructor
	 *
	 * @param  string  $route  The route to match against.
	 * @param  mixed  $defaults  The default paramater values.
	 */
	public function __construct($route, $defaults ) {
		$defaults = (array) $defaults;
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
		$path = $request->relativePath();

		if ($path == $this->_route) {
			return $this->_defaults;
		} else {
			return false;
		}
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /Class
?>