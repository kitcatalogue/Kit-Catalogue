<?php



class Ecl_Mvc_Exception extends Ecl_Exception {}



/**
 * The base class for MVC component classes.
 *
 * @abstract
 * @package  Ecl
 * @version  1.0.0
 */
abstract class Ecl_Mvc {

	// Public Properties

	// Private Properties
	protected $_router = null;



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	/**
	 * Initialise the controller.
	 *
	 * @param  object  $router  The router that called this controller.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function init($router) {
		$this->_router = $router;
	}// /method



	/**
	 * @return  object  The current layout.
	 */
	public function layout() {
		return $this->_router->layout();
	}// /method



	/**
	 * Get a named value/object from the model.
	 *
	 * Using a null $name parameter will return the model itself.
	 *
	 * @param  string  $name  (default: null.
	 *
	 * @return  mixed  The model item requested. On fail, null.
	 */
	public function model($name = null) {
		return (is_null($name)) ? $this->_router->model() : $this->_router->model()->get($name);
	}// /method



	/**
	 * Fetch the named request parameter.
	 *
	 * @param  string  name
	 * @param  mixed  $default  (default: null)
	 *
	 * @return  mixed  The appropriate value. On fail, $default.
	 */
	public function param($name, $default = null) {
		return $this->router()->param($name, $default);
	}// /method



	/**
	 * @return  object  The current request.
	 */
	public function request() {
		return $this->_router->request();
	}// /method



	/**
	 * @return  object  The current response.
	 */
	public function response() {
		return $this->_router->response();
	}// /method



	/**
	 * @return  object  The router that dispatched this controller.
	 */
	public function router() {
		return $this->_router;
	}// /method



	/**
	 * @return  object  The current view.
	 */
	public function view() {
		return $this->_router->view();
	}// /method



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



}// /class
?>