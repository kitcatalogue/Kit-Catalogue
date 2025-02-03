<?php



Ecl::load('Ecl_Mvc');



class Ecl_Mvc_Router_Exception extends Ecl_Mvc_Exception {}
class Ecl_Mvc_Router_Exception_InvalidRouteException extends Ecl_Mvc_Router_Exception {}
class Ecl_Mvc_Router_Exception_InvalidControllerException extends Ecl_Mvc_Router_Exception {}
class Ecl_Mvc_Router_Exception_InvalidActionException extends Ecl_Mvc_Router_Exception {}



/**
 * A class for managing simple controller routing.
 *
 * The router operates as a singleton.
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Mvc_Router extends Ecl_Mvc {

	// Public properties

	// Private properties
	protected static $_instance = null;   // Internal object reference

	protected $_config = array(
		'mvc_root'         => '.' ,

		'uri_root'         => null,

		'default_controller'  => 'index' ,
		'default_action'      => 'index' ,
	);

	protected $_valid_methods = array('delete', 'get', 'head', 'post', 'put');

	protected $_time_started = null;

	protected $_is_dispatched = false;

	protected $_model = null;

	protected $_params = array();

	protected $_request = null;

	protected $_response = null;

	protected $_routes = array();

	protected $_module = null;   // The current module name

	protected $_controller = null;   // The current controller object

	protected $_layout = null;   // The current layout object

	protected $_view = null;   // The current controller object



	/**
	 * Constructor
	 *
	 * See {@link singleton()}
	 */
	protected function __construct($config = null) {
		$this->_time_started = time();

		$this->_config = array_merge($this->_config, (array) $config);

		$this->_request = Ecl::factory('Ecl_Request');
		$this->_response = Ecl::factory('Ecl_Response');

		$this->baseUri($this->_config['uri_root']);
	}// /->__construct()



	/**
	 * Get a reference to the singleton.
	 *
	 * @return  object  The object instance.
	 */
	public static function singleton($config = null) {
		if (!is_object(self::$_instance)) {
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}// /method



	/**
	 * Clone
	 *
	 * This class is a singleton and should not be cloned
	 *
	 * @return  object  A new instance of this class.
	 */
	protected function __clone() {
		throw Ecl_Router_Exception('You cannot clone a singleton class');
	}// /->__clone()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Call the appropriate controller and action directly.
	 *
	 * @param  string  $action
	 * @param  string  $controller  (optional) If no controller is given, the current controller is assumed.  If no current controller, the default is used.  (default: null)
	 * @param  string  $module  (optional)  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function action($action, $controller = null, $module = null) {
		$this->_loadController($controller, $module);
		$this->setCurrentModule($module);

		// Call the action
		$this->_controller->init($this);
		$this->_controller->action($action);
		return true;
	}// /method



	/**
	 * Get/Set the default action.
	 *
	 * @param  string  $action  (optional)
	 *
	 * @return  string  The default action.
	 */
	public function actionDefault($action = null) {
		if (!is_null($action)) { $this->_config['default_action'] = $action; }
		return $this->_config['default_action'];
	}// /method



	/**
	 * Add a controller route.
	 *
	 * Routes map url-formats to the appropriate controller and action to call.
	 * Routes are processed in reverse order, so add default/generic routes first and more specific routes later.
	 *
	 * @param  string  $method  The HTTP method (*, get, post, etc).
	 * @param  string  $route  The url-format to match against.
	 * @param  array  $defaults  (optional)  The default values of the controller/action and other parameters.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addRoute($method, $route, $defaults = array()) {
		$method = strtolower($method);
		if ( ('*' != $method) && (!in_array($method, $this->_valid_methods)) ) {
			throw Ecl_Mvc_Router_Exception_InvalidRouteException('Invalid HTTP method for routing.', 1);
		}

		$this->_routes[] = new Ecl_Mvc_Router_Route($method, $route, $defaults);
		return true;
	}// /method



	/**
	 * Get/Set the root URI that this front controller serves.
	 *
	 * @return	string	The url root.
	 */
	public function baseUri($base_uri = null) {
		if (!is_null($base_uri)) {
			if (empty($base_uri)) {
				$this->_config['uri_root'] = $base_uri;
				$this->_request->baseUri($base_uri);
			} else {
				if ($base_uri[strlen($base_uri)-1]!='/') { $base_uri .= '/'; }
				$this->_config['uri_root'] = $base_uri;
				$this->_request->baseUri($base_uri);
			}
		}
		return $this->_config['uri_root'];
	}// /method



	/**
	 * Get/Set the name of the controller to use for unmatched routes.
	 *
	 * @param  string  $controller  (optional)
	 *
	 * @return  string  The default controller.
	 */
	public function controllerDefault($controller = null) {
		if (!is_null($controller)) { $this->_config['default_controller'] = $controller; }
		return $this->_config['default_controller'];
	}// /method



	/**
	 * Get the path to the requested controller.
	 *
	 * @param  string  $controller_name
	 * @param  string  $module_name  (optional)
	 *
	 * @return  string  The path.
	 */
	public function controllerPath($controller_name, $module_name = null) {
		$controller_name = strtolower($controller_name);
		$include_path = $this->mvcRoot();

		if (!empty($module_name)) {
			$include_path .= DIRECTORY_SEPARATOR . 'modules'. DIRECTORY_SEPARATOR . strtolower($module_name);
		}

		$include_path .= DIRECTORY_SEPARATOR. 'controllers' .DIRECTORY_SEPARATOR. "{$controller_name}.php";

		return Ecl_Helper_Filesystem::fixPath($include_path);
	}// /method



	/**
	 * Dispatch the requested uri to the appropriate action and controller.
	 *
	 * @param  string  $uri  (optional) The URI to process.  If omitted, use the current URI. (default: null)
	 *
	 * @return  boolean  The uri was handled and dispatched successfully.
	 */
	public function dispatch($uri = null) {

		$this->setDispatched(false);


		if (!is_null($uri)) {
			$this->request()->overrideUri($uri);
		}


		// If there are no routes loaded, add a basic route
		if (count($this->_routes)==0) {
			$this->addRoute('*', ':controller/:action',	array (
				'controller'  => $this->controllerDefault() ,
				'action'      => $this->actionDefault() ,
			));
		}


		// Match against available routes
		$params = null;

		end($this->_routes);
		while ( (!is_array($params)) && (!is_null(key($this->_routes))) ) {
			$route = current($this->_routes);

			// Check for a route match
			$params = $route->match($this->request());

			prev($this->_routes);
		}// /while (routes to process)



		// If there's no params, then there was a problem routing the request
		if (!$params) {
			return $this->_dispatchAction('404', 'error');
		}


		$this->setDispatched(true);
		$this->_params = array_merge($this->_params, $params);


		// If there's a redirect parameter, stop processing and redirect.
		$redirect = $this->param('redirect', null);
		if (!empty($redirect)) {
			$this->response()->redirect($redirect);
			return true;
		}


		// We must've matched a route, dispatch the call
		$module_name = $this->param('module', null);
		$controller_name = $this->param('controller', $this->controllerDefault() );
		$action_name = $this->param('action', $this->actionDefault() );

		return $this->_dispatchAction($action_name, $controller_name, $module_name);
	}// /method



	/**
	 * Get the current active module.
	 *
	 * The default module is null.
	 *
	 * @return  mixed  The current module.
	 */
	public function getCurrentModule() {
		return $this->_module;
	}// /method



	/**
	 * Overridden inherited init() method
	 */
	public function init($router) { return false; }



	/**
	 * @return  boolean  Has the current request been dispatched
	 */
	public function isDispatched() {
		return $this->_is_dispatched;
	}// /method



	/**
	 * Get/Set the current layout.
	 *
	 * @return  object  The current layout.
	 */
	public function layout($layout = null) {
		if (func_num_args()>0) {
			$this->_layout = $layout;
			if (is_object($this->_layout)) { $this->_layout->init($this); }
		}
		return $this->_layout;
	}// /method



	/**
	 * Get the path to the requested layout.
	 *
	 * @param  string  $layout_name
	 * @param  string  $module_name  (optional)
	 *
	 * @return  string  The path.
	 */
	public function layoutPath($layout_name, $module_name = null) {
		$layout_name = strtolower($layout_name);
		$include_path = $this->mvcRoot();

		if (!empty($module_name)) {
			$include_path .= DIRECTORY_SEPARATOR. strtolower($module_name);
		}

		$bits = explode('_', $layout_name);

		$include_path .= DIRECTORY_SEPARATOR. 'layouts' .DIRECTORY_SEPARATOR. implode(DIRECTORY_SEPARATOR, $bits) . '.phtml';

		return Ecl_Helper_Filesystem::fixPath($include_path);
	}// /method



	/**
	 * Make a relative URI in to an absolute URI based on the current router's base URI.
	 *
	 * If $relative_url is ommited, then the router's base URI will be returned.
	 *
	 * @param  string  $relative_url  (optional)  (default: null)
	 * @param  boolean  $https  (optional)  (default: false)
	 *
	 * @return  string  The new absolute URL.
	 */
	public function makeAbsoluteUri($relative_url = null, $https = false) {
		// If the relative URL has a leading '/', remove it
		if ('/' == substr($relative_url, 0, 1)) {
			$relative_url = substr($relative_url, 1);
		}

		$url = $this->baseUri() . $relative_url;

		if ($https) {
			$url = preg_replace('#^http:#', 'https:', $url);
		}
		return $url;
	}// /method



	/**
	 * Get/Set the current model object.
	 *
	 * @param  object  $model  (optional)
	 *
	 * @return  object  The current model.
	 */
	public function model($model = null) {
		if (func_num_args()>0) { $this->_model = $model; }
		return $this->_model;
	}// /method



	/**
	 * Get the given parameter.
	 *
	 * @param  string  $key
	 * @param  mixed  $deafult  (optional)
	 *
	 * @return  mixed  The current value of $key.
	 */
	public function param($key, $default = null) {
		return (isset($this->_params[$key])) ? $this->_params[$key] : $default ;
	}// /method



	/**
	 * Get all the request parameters.
	 *
	 * @return	array  The parameters.
	 */
	public function params() {
		return $this->_params;
	}// /method



	/**
	 * @return  object  The current request.
	 */
	public function request() {
		return $this->_request;
	}// /method



	/**
	 * @return  object  The current response.
	 */
	public function response() {
		return $this->_response;
	}// /method



	/**
	 * Set the current active module.
	 *
	 * The default module for main MVC controllers, views, etc is empty/null.
	 * Module names are converted to lower case when set.
	 *
	 * @param  string  $module_name
	 */
	public function setCurrentModule($module_name) {
		$this->_module = (!empty($module_name)) ? strtolower($module_name) : null ;
		return $this->_module;
	}// /method



	/**
	 * Set whether the current request was dispatched successfully
	 *
	 * @param  boolean  $success
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setDispatched($success) {
		$this->_is_dispatched = ($success == true);
		return true;
	}// /method



	/**
	 * Set the given parameter.
	 *
	 * Use this method to supply default values, or simply pass extra information to action calls.
	 * Parameters can be overriden by matching route components.
	 *
	 * @param  string  $key
	 * @param  mixed  $value  (optional)
	 *
	 * @return  mixed  The current value of $key.
	 */
	public function setParam($key, $value = null, $default = null) {
		$this->_params[$key] = $value;
		return $value;
	}// /method



	/**
	 * Get/Set the include path for views.
	 *
	 * @param  string  $path  (optional)
	 *
	 * @return  string  The current view include path.
	 */
	public function templateRoot($path = null) {
		if (!is_null($path)) { $this->_config['template_root'] = $path; }
		return $this->_config['template_root'];
	}// /method



	/**
	 * Get/Set the current view object.
	 *
	 * @param  object  $view  (optional)
	 *
	 * @return  object  The current view.
	 */
	public function view($view = null) {
		if (func_num_args()>0) {
			$this->_view = $view;
			if (is_object($this->_view)) { $this->_view->init($this); }
		} else {
			// Initialise the view, or create a view object if necessary
			if (empty($this->_view)) { $this->view(Ecl::factory('Ecl_Mvc_View')); }
		}
		return $this->_view;
	}// /method



	/**
	 * Get the path to the requested view.
	 *
	 * @param  string  $view_name
	 * @param  string  $module_name  (optional)
	 *
	 * @return  string  The path.
	 */
	public function viewPath($view_name, $module_name = null) {
		$view_name = strtolower($view_name);
		$include_path = $this->mvcRoot();

		if (null === $module_name) {
			$module_name = $this->getCurrentModule();
		}

		if (!empty($module_name)) {
			$include_path .= DIRECTORY_SEPARATOR . 'modules'. DIRECTORY_SEPARATOR . strtolower($module_name);
		}

		$bits = explode('_', $view_name);

		$include_path .= DIRECTORY_SEPARATOR. 'views' .DIRECTORY_SEPARATOR. implode(DIRECTORY_SEPARATOR, $bits) . '.phtml';

		return Ecl_Helper_Filesystem::fixPath($include_path);
	}// /method



	/**
	 * Get/Set the include path for views.
	 *
	 * @param  string  $path  (optional)
	 *
	 * @return  string  The current view include path.
	 */
	public function mvcRoot($path = null) {
		if (!is_null($path)) { $this->_config['mvc_root'] = $path; }
		return $this->_config['mvc_root'];
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	protected function _dispatchAction($action, $controller, $module = null) {
		$buffer_level = ob_get_level();
		ob_start();

		try {
			$this->action($action, $controller, $module);
		} catch (Ecl_Mvc_Router_Exception $e) {
			ob_clean();
			$this->action('404', 'error', '');
		} catch (Exception $e) {
			$this->layout()->clear();
			ob_clean();
			$this->setParam('exception', $e);
			$this->action('exception', 'error', '');
		}

		// Output whatever we've responded with
		try {
			// If using a layout
			if (is_object($this->layout())) {
				$this->layout()->content(ob_get_clean());
				$this->response()->content($this->layout()->render());
			} else {
				$this->response()->content(ob_get_clean());
			}
			$this->response()->send();
			return true;
		} catch (Ecl_Response_Exception_HeadersSentException $e) {
			Ecl::dump($e, 'Headers Already Sent');
		} catch (Ecl_Response_Exception_InvalidHttpStatusCodeException $e) {
			Ecl::dump($e, 'Invalid HTTP Status Code');
		} catch (Exception $e) {
			Ecl::dump($e, 'An unknown exception occured while outputting the response');
		}


		// Must've been an exception thrown, clean buffer and abort
		$current_buffer_level = ob_get_level();
		while ( ($current_buffer_level > $buffer_level) || (0 != $current_buffer_level) ) {
			ob_end_clean();
			$current_buffer_level = ob_get_level();
		}

		return false;
	}// /method



	/**
	 * Load the requested controller object using the given module if required
	 *
	 * @param  string  $controller_name
	 * @param  string  $module_name  (optional)
	 *
	 * @throws Ecl_Mvc_Router_Exception_InvalidControllerException
	 *
	 * @return  boolean  The operation was successful.
	 */
	protected function _loadController($controller_name, $module_name = null) {
		if (empty($controller_name)) { $controller_name = $this->controllerDefault(); }

		// Build the controller class name
		$controller_class = ucfirst(strtolower($controller_name));


		if (!empty($module_name)) {
			$controller_class = ucfirst(strtolower($module_name)) .'_'. $controller_class;
		}

		$controller_class = 'Controller_'. $controller_class;

		// If the controller we want is already loaded, we're done.
		if ((is_object($this->_controller)) && ($controller_class == get_class($this->_controller)) ) { return true; }


		// If we're here, then the controller needs loading
		$include_path = $this->controllerPath($controller_name, $module_name);

		if (!file_exists($include_path)) {
			throw new Ecl_Mvc_Router_Exception_InvalidControllerException("Unable to include controller file: '$include_path'. Using module '$module_name'", 1);
		}

		include_once($include_path);

		if (!class_exists($controller_class, false)) {
			throw new Ecl_Mvc_Router_Exception_InvalidControllerException("Unable to find controller class '$controller_class' in include file '$include_path'.", 11);
		}

		// Instantiate the controller
		$this->_controller = new $controller_class();

		return true;
	}// /method



}// /class
?>
