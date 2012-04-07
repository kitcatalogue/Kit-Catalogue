<?php



class Ecl_Request_Exception extends Ecl_Exception {}



/**
 * Request object allows access to information regarding the current page request.
 *
 * Provides a convenience wrapper for many inbuilt PHP functions and superglobals (e.g. $_GET, $_POST, etc)
 *
 * @package  Ecl
 * @version  1.0.0
 */
Class Ecl_Request {

	// Public properties

	// Private properties
	protected static $_instance = null;   // Internal object reference

	protected $_pathinfo = null;

	protected $_accepted_types = null;
	protected $_accepted_langs = null;

	protected $_get_params = array();
	protected $_path_segments = array();
	protected $_put_params = array();

	protected $_content = null;   // The body content (as in a PUT request)
	protected $_fetched_content = false;

	protected $_uri = null;

	protected $_server_uri = null;

	protected $_base_uri = null;
	protected $_relative_path = null;
	protected $_relative_uri = null;



	/**
	 * Constructor
	 *
	 * See {@link singleton()}
	 */
	protected function __construct() {
		$scheme = ($this->server('HTTPS', 'off')=='on') ? 'https' : 'http';
		$host = $this->server('SERVER_NAME', '');
		$scheme_and_host = $scheme .'://'. $host;

		if ('http' == $scheme) {
			$port_component = ($this->server('SERVER_PORT')!='80') ? ':'.$this->server('SERVER_PORT') : '' ;
		} elseif ('https' == $scheme) {
			$port_component = ($this->server('SERVER_PORT')!='443') ? ':'.$this->server('SERVER_PORT') : '' ;
		}

		// The contents of $_SERVER['REQUEST_URI'] are pretty much our only hope of reliable URL finding.
		$request_uri = $this->server('REQUEST_URI');

		// If $request_uri already starts with something like "http://example.com" then remove it.
		if (strpos($request_uri, $scheme_and_host)===0) {
			$request_uri = substr($request_uri, strlen($scheme_and_host));
		}

		$this->_uri = $scheme_and_host . $port_component . $request_uri;


		// Process GET params
		$this->_get_params = Ecl_Helper_String::parseQuerystring($_SERVER['QUERY_STRING']);

		// Process PUT params
		if ($this->isPut()) {
			$this->_put_params = Ecl_Helper_String::parseQuerystring(file_get_contents('php://input'));
		}

		// Process the URI and populate related properties
		$this->_processUri($this->_uri);
	}// /->__construct()



	/**
	 * Get a reference to the singleton.
	 *
	 * @return  object  The object instance.
	 */
	public static function singleton() {
		if (!is_object(self::$_instance)) {
			self::$_instance = new self();
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
		throw new Ecl_Request_Exception('You cannot clone a singleton class');
	}// /->__clone()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Get/Set the URI from which relative URIs will be derived.
	 *
	 * e.g.
	 * Using a URI of http://example.com/base/path/to/resource/
	 *
	 * Using either...
	 * ->baseUri('http://example.com/base')
	 * or
	 * ->baseUri('/base')
	 *
	 * Should produce...
	 * ->relativeUri();  =>  '/path/to/resource'
	 *
	 * @param  string  $base_uri  (optional)
	 *
	 * @return  string  The current base URI.
	 */
	public function baseUri($base_uri = null) {
		if (is_null($base_uri)) { return $this->_base_uri; }

		// Strip out any server URI prefix
		if (strpos($base_uri, $this->serverUri())===0) {
			$base_uri = substr($base_uri, strlen($this->serverUri()));
		}

		// If the URI needs a preceding slash, add one
		if ( (empty($base_uri)) || ($base_uri[0]!='/') ) { $base_uri = '/' . $base_uri; }

		$this->_base_uri = $base_uri;
		// Set default relative info
		$this->_relative_uri = $this->_uri;
		$this->_relative_path = $this->path();

		// If the URI contains the request path (which it should!) then produce the real relative info
		if (strpos($this->path(), $base_uri)===0) {
			$this->_relative_uri = substr($this->_uri, strlen($this->_server_uri . $this->_base_uri));
			if ( (empty($this->_relative_uri)) || ($this->_relative_uri[0]!='/') ) { $this->_relative_uri = '/' . $this->_relative_uri; }

			$this->_relative_path = substr($this->path(), strlen($this->_base_uri));
			if ( (empty($this->_relative_path)) || ($this->_relative_path[0]!='/') ) { $this->_relative_path = '/' . $this->_relative_path; }
		}

		return $this->_base_uri;
	}// /method



	/**
	 * Fetch the content sent with the request.
	 *
	 * @return  mixed  The content sent with the request. On fail, null.
	 */
	public function content() {
		if (!$this->_fetched_content) {
			$data = null;

			$fp = fopen('php://input','r');
			while (!feof($fp)) {
				$data .= fgets($fp);
			}
			fclose($fp);

			$this->_fetched_content = true;
			$this->_content = $data;
		}

		return $this->_content;
	}// /method



	/**
	 * Fetch a variable from the cookie.
	 *
	 * @param  string  $key
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The value of the variable. On fail, the default.
	 */
	public function cookie($key, $default = null) {
		return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
	}// /method



	/**
	 * Fetch a file from the uploaded files data.
	 *
	 * @param  string  $key
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The value of the variable. On fail, the default.
	 */
	public function file($key, $default = null) {
		return (isset($_FILES[$key])) ? $_GET[$key] : $default;
	}// /method



	/**
	 * Get the file extension of the requested path.
	 *
	 * @param  mixed  $default  (optional)  (default: null)
	 */
	public function extension($default = null) {
		$ext = pathinfo($this->_pathinfo['path'], PATHINFO_EXTENSION);
		if (empty($ext)) { return $default; }
	}// /method



	/**
	 * Fetch a variable from the querystring.
	 *
	 * Multiple occurences of individual parameters will be returned as an array of values for the appropriate key.
 	 * The same goes for traditional PHP-array parameters (x[]=1&x[]=2..)
 	 * Keys without values become keys with a value of boolean true.
 	 *
	 * For example:
	 * index.php?k[]=1&k[]=2  =>  ->get('k')  =>  array (1, 2)
	 * index.php?k=1&k=2      =>  ->get('k')  =>  array (1, 2)
	 * index.php?k            =>  ->get('k')  =>  true
	 *
	 * @param  string  $key
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The value of the variable. On fail, the default.
	 */
	public function get($key, $default = null) {
		return (isset($this->_get_params[$key])) ? $this->_get_params[$key] : $default;
	}// /method



	/**
	 * Fetch all the posted parameters.
	 *
	 * @return  array  All the POSTed parameters.
	 */
	public function getAllPost() {
		return $_POST;
	}// /method



	/**
	 * Get the full path portion of the current request.
	 *
	 * @return  string  The current request path.
	 */
	public function path() {
		return ($this->_pathinfo['path']);
	}// /method



	/**
	 * Fetch a variable from the form post.
	 *
	 * @param  string  $key
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The value of the variable. On fail, the default.
	 */
	public function post($key, $default = null) {
		return (isset($_POST[$key])) ? $_POST[$key] : $default;
	}// /method



	/**
	 * Get the date entered into a form constructed by Html->formSelectsDmyt().
	 *
	 * Reads the day/month/year/time fields from $_POST, and converts them to the corresponding datetime.
	 *
	 * @param  string  $name_stub  The name with which all the form-fields began
	 * @param  datetime  $default  (optional) The default value to return, if part of the date could not be found.
	 *
	 * @return  mixed  On success, the date entered. On fail, the given default value.
	 */
	public function postDmyt($name_stub, $default = null) {
		$day   = $this->post("{$name_stub}_day", null);
		$month = $this->post("{$name_stub}_month", null);
		$year  = $this->post("{$name_stub}_year", null);
		$time  = $this->post("{$name_stub}_time", null);

		if ( (empty($day)) || (empty($month)) || (empty($year)) || (empty($time)) ) {
			return $default;
		} else {
			return strtotime("{$year}-{$month}-{$day}T{$time}:00");
		}
	}// /method



	/**
	 * Get an array of key-value pairs where each form field's key is prefixed with a certain string.
	 *
	 * For example, if your form contains a series of checkboxes of the form:
	 * <input type="checkbox" name="student_0001" value="1" />
	 * <input type="checkbox" name="student_0002" value="1" />
	 * ...
	 * You can use:  ->postPrefixed('student_', true)
	 * To get an assoc-array :  array ( '0001' => 1 , '0002' => 1 , ... )
	 *
	 * @param  string  $prefix  The prefix to check for when reading form-fields.
	 * @param  boolean  $strip_prefix  (optional) Remove the prefix from the key when creating the array. (Default: false)
	 *
	 * @return  mixed  Assoc-array of results. On fail, null.
	 */
	public function postPrefixed($prefix, $strip_prefix = false) {
		Ecl::load('Ecl_Helper_Array');
		return Ecl_Helper_Array::filterKeysPrefixed($_POST, $prefix, $strip_prefix);
	}// /method



	/**
	 * Fetch a variable from the 'PUT' request body.
	 *
	 * Multiple occurences of individual parameters will be returned as an array of values for the appropriate key.
 	 * The same goes for traditional PHP-array parameters (x[]=1&x[]=2..)
 	 * Keys without values become keys with a value of boolean true.
 	 *
 	 * Analagous to get() for querystring parameters.
	 *
	 * @param  string  $key
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The value of the variable. On fail, the default.
	 */
	public function put($key, $default = null) {
		return (isset($this->_put_params[$key])) ? $this->_put_params[$key] : $default;
	}// /method



	/**
	 * Get the path segment at the corresponding index position.
	 *
	 * @param  integer  $index  The path segment to find. Negative $index positions count backwards from the end.
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The path segment requested. On fail, the default.
	 */
	public function segment($index, $default = null) {
		if ($index<0) {
			$index = abs($index) - 1;
			$segments = array_reverse($this->_path_segments);
			return (isset($segments[$index])) ? $segments[$index] : $default ;
		} else {
			return (isset($this->_path_segments[$index])) ? $this->_path_segments[$index] : $default ;
		}
	}// /method



	/**
	 * Fetch a variable from the server variables.
	 *
	 * @param  string  $key
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The value of the variable. On fail, the default.
	 */
	public function server($key, $default = null) {
		return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
	}// /method



	/**
	 * Fetch a variable from the session.
	 *
	 * @param  string  $key
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The value of the variable. On fail, the default.
	 */
	public function session($key, $default = null) {
		return (isset($_SESSION[$key])) ? $_SESSION[$key] : $default;
	}// /method



	/**
	 * Fetch a request parameter.
	 *
	 * Get the value of the given superglobal key.
	 * Retrieves keys preferentially in the order: POST > GET > COOKIE > SERVER > ENV
	 *
	 * @param  string  $key
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The appropriate value. On fail, null.
	 */
	public function fetch($key, $default = null) {
		if (isset($_POST[$key])) { return $_POST[$key]; }
		if (isset($this->_get_params[$key])) { return $this->_get_params[$key]; }
		if (isset($_COOKIE[$key])) { return $_COOKIE[$key]; }
		if (isset($_SERVER[$key])) { return $_SERVER[$key]; }
		if (isset($_ENV[$key])) { return $_ENV[$key]; }
		return $default;
	}// /method



	/**
	 * Get the HTTP method used in the request.
	 *
	 * If the method is not found, "get" is returned.
	 *
	 * @return  string  The method used.
	 */
	public function httpMethod() {
		return strtolower($this->server('REQUEST_METHOD', 'get'));
	}// /method



	/**
	 * Is the request from an AJAX client.
	 *
	 * Only checks for the X-Requested-With header, so may not work with some javascript libraries.
	 *
	 * @return  boolean  Is this an AJAX generated request?
	 */
	public function isAjax() {
		return ($this->server('X-Requested-With')=='XMLHttpRequest');
	}// /method



	/**
	 * @return  boolean  Was the request a DELETE.
	 */
	public function isDelete() {
		return (strtolower($this->httpMethod()) == 'delete');
	}// /method



	/**
	 * @return  boolean  Was the request a GET.
	 */
	public function isGet() {
		return (strtolower($this->httpMethod()) == 'get');
	}// /method



	/**
	 * @return  boolean  Was the request a HEAD.
	 */
	public function isHead() {
		return (strtolower($this->httpMethod()) == 'head');
	}// /method



	/**
	 * @return  boolean  Was the request a POST.
	 */
	public function isPost() {
		return (strtolower($this->httpMethod()) == 'post');
	}// /method



	/**
	 * @return  boolean  Was the request a PUT.
	 */
	public function isPut() {
		return (strtolower($this->httpMethod()) == 'put');
	}// /method



	/**
	 * @return  boolean  Is this an https:// or ssl:// request?
	 */
	public function isSecure() {
		return in_array(strtolower($this->_pathinfo['scheme']), array ('https', 'ssl'));
	}// /method



	/**
	 * Check the mime type the client prefers against a list of available types.
	 *
	 * Reads the preference levels from the HTTP Accept header.
	 * All media types will be converted to lower case.
	 * e.g. ->negotiateHttpAccept( array ('application/json', 'text/csv', 'application/xml') );
	 * If there's no match, the first available content type provided is used ('application/json' in this example).
	 *
	 * @param  array  $available_types  An array of content types available in this application
	 *
	 * @return  string  The accepted type.
	 */
	public function negotiateHttpAccept($available_types) {

		if ( (!is_array($available_types)) || (empty($available_types)) ) { throw Exception('$available_types must be an array.'); }

		array_map('strtolower', (array) $available_types);
		$default = (string) $available_types[0];

		if (empty($this->_accepted_types)) {
			$http_types = $this->server('HTTP_ACCEPT');
			$this->_accepted_types = Ecl_Http::parseQualityList($http_types);

			if (empty($this->_accepted_types)){ return $default; }
		}

		foreach($this->_accepted_types as $i => $media) {
			if (in_array($media, $available_types)) {
				return $media;
			} else {
				list($type, $subtype) = explode('/', $media);
				if ( ($type!='*') && ($subtype=='*') ) {
					$type .= '/';
					foreach($available_types as $j => $available_media) {
						if (strpos($available_media, $type)===0) { return $available_media; }
					}
				}
			}
		}

		return $default;
	}// /method



	/**
	 * Check the language the client prefers against a list of available languages.
	 *
	 * Reads the preference levels from the HTTP Accept-Language header.
	 * All languages will be converted to lower case.
	 * e.g. ->negotiateHttpAcceptedLanguage( array ('en-gb', 'en', 'fr', 'es') );
	 * If there's no match, the first available language provided is used ('en-gb' in this example).
	 *
	 * @param  array  $available_langs  An array of language available in this application
	 *
	 * @return  string  The accepted language.
	 */
	public function negotiateHttpAcceptLanguage($available_langs) {

		if ( (!is_array($available_langs)) || (empty($available_langs)) ) { throw Exception('$available_langs must be an array.'); }

		array_map('strtolower', (array) $available_langs);
		$default = (string) $available_langs[0];

		if (empty($this->_accepted_langs)) {
			$http_langs = $this->server('HTTP_ACCEPT_LANGUAGE');
			$this->_accepted_langs = Ecl_Http::parseQualityList($http_langs);

			if (empty($this->_accepted_langs)){ return $default; }
		}

		foreach($this->_accepted_langs as $i => $lang) {
			if (in_array($lang, $available_langs)) {
				return $lang;
			} else {
				$pos = strpos($lang, '-');
				if ($pos!==false) {
					$prefix = substr($lang, 0, $pos);
					if (in_array($prefix, $available_langs)) {
						return $prefix;
					}
				}
			}
		}

		return $default;
	}// /method



	/**
	 * Override the requested URI.
	 *
	 * Path related properties, e.g. segments, are also overriden
	 * GET variables are *NOT* overriden, regardless of any query string parameters included in $uri.
	 *
	 * @return	string	The new uri to use.
	 */
	public function overrideUri($uri) {
		$this->_uri = $uri;
		$this->_processUri($uri);
		return true;
	}// /method



	/**
	 * Get the URI path relative to the base URI.
	 *
 	 * If no baseUri is set, this will return just the path.
	 * e.g.  /some/path/
	 *
	 * @see setBaseUri()
	 *
	 * @return	string	The relative path.
	 */
	public function relativePath() {
		return $this->_relative_path;
	}// /method



	/**
	 * Get the URI relative to the base URI.
	 *
	 * If no baseUri is set, this will return the normal path-URI.
	 * e.g.  /some/path/?a=1&b=2
	 *
	 * @see setBaseUri()
	 *
	 * @return	string	The relative uri.
	 */
	public function relativeUri() {
		return $this->_relative_uri;
	}// /method



	/**
	 * Get the server URI (scheme/host/port).
	 *
	 * Default ports (80 and 443) are ignored if the scheme is correct.
	 * e.g.
	 * http://example.com
	 * http://example.com:8080
	 * https://example.com
	 *
	 * @return	string	The server uri.
	 */
	public function serverUri() {
		return $this->_server_uri;
	}// /method



	/**
	 * Get the URI.
	 *
	 * @return	string	The uri.
	 */
	public function uri() {
		return $this->_uri;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	/**
	 * Process the given URI and populate related properties.
	 *
	 * @param  string  $uri
	 *
	 * @return  boolean  The operation was successful.
	 */
	protected function _processUri($uri) {

		// Process the query
		$this->_pathinfo = Ecl_Helper_String::parseUrl($this->_uri);
		if (!$this->_pathinfo) {
			$this->_pathinfo = null;
			throw new Ecl_Request_Exception("Invalid URI: $uri", 1);
		}


		// Build server URI
		$scheme = $this->_pathinfo['scheme'];
		$host = $this->_pathinfo['host'];
		$port = $this->_pathinfo['port'];

		$port_component = null;
		if ('http' == $scheme) {
			$port_component = ($port!='80') ? ':'.$port : '' ;
		} elseif ('https' == $scheme) {
			$port_component = ($port!='443') ? ':'.$port : '' ;
		}

		$this->_server_uri = $scheme . '://' . $host . $port_component;


		// Build relative vars
		$this->_relative_uri = $this->_uri;

		$this->_relative_uri = substr($this->_uri, strlen($this->_server_uri . $this->_base_uri));
		if ( (empty($this->_relative_uri)) || ($this->_relative_uri[0]!='/') ) { $this->_relative_uri = '/' . $this->_relative_uri; }

		$this->_relative_path = $this->path();
		if ( (empty($this->_relative_path)) || ($this->_relative_path[0]!='/') ) { $this->_relative_path = '/' . $this->_relative_path; }


		// Process Path Segments
		if (array_key_exists('path', $this->_pathinfo)) {
			$path = preg_replace('#/*(.+?)/*$#', '\\1', $this->_pathinfo['path']);

			foreach(explode('/', $path) as $i => $segment) {
				$this->_path_segments[] = Ecl_Helper_String::cleanString($segment);
			}
		}

		return true;
	}// /method


}// /Class
?>