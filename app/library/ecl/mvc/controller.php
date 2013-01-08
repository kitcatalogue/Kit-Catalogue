<?php



Ecl::load('Ecl_Mvc');



class Ecl_Mvc_Controller_Exception extends Ecl_Mvc_Exception {}
class Ecl_Mvc_Controller_InvalidActionException extends Ecl_Mvc_Exception {}



/**
 * The base class for all Controllers containing actions.
 *
 * It is the clients responsibility to create the appropriate action methods in child classes, including the actionDefault() or equivalent.
 * Parameters in the associated front controller are available as properties of this class, or by using the get() method.
 *
 * The return results from action???() methods are ignored when called by the router.
 *
 * @abstract
 * @package  Ecl
 * @version  1.0.0
 */
abstract class Ecl_Mvc_Controller extends Ecl_Mvc {

	// Public Properties

	// Private Properties
	protected $_action = null;

	protected $_abort = false;   // Flag to indicate that the active action should not be called



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function abort() {
		$this->_abort = true;
	}// /method



	public function aborting() {
		return $this->_abort;
	}



	/**
	 * Call the appropriate method.
	 *
	 * The router will call this method to initiate an action, rather than call the action???() method directly.
	 * Controllers can override this method and intercept action calls to provide custom functionality.
	 * This method will still return true if controllers use ->abort() to terminate early in ->beforeAction()
	 *
	 * @param  string  $action_name
	 *
	 * @throws Ecl_Mvc_Controller_InvalidActionException
	 *
	 * @return  boolean  The action was processed normally.
	 */
	public function action($action_name) {
		$action_method = 'action' . ucfirst(strtolower($action_name));

		// If the method doesn't exist throw an exception
		if (!method_exists($this, $action_method)) {
			throw new Ecl_Mvc_Controller_InvalidActionException("Unable to find action '$action_method' on controller '". $this->getName() ."'.", 1);
			return false;
		}

		$this->_action = $action_method;
		$this->_abort = false;

		$this->beforeAction();
		if (!$this->aborting()) {
			$this->$action_method();
			$this->afterAction();
		}

		return true;
	}// /method



	/**
	 * Method called following any action???() call.
	 */
	public function afterAction() { }



	/**
	 * Method called before any action???() call.
	 */
	public function beforeAction() { }



	/**
	 * @return  string  The controller name.
	 */
	public function getName() {
		$name = strtolower(get_class($this));
		$name = substr($name, strlen('controller_'));
		return $name;
	}// /method



	/**
	 * Initialise the controller.
	 *
	 * @param  object  $router  The router that called this controller.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function init($router) {
		parent::init($router);
		return true;
	}// /method



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



}// /class
?>