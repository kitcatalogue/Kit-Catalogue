<?php
Ecl::load('Ecl_Helper_String');



/**
 * Class representing a URI.
 *
 * @package  Ecl
 * @version 1.0.0
 */
class Ecl_Uri {

	// Private Properties
	protected $_scheme = null;
	protected $_host = null;
	protected $_port = null;
	protected $_username = null;
	protected $_password = null;

	protected $_path = null;
	protected $_path_segments = array();

	protected $_query_parts = array();

	protected $_fragment = null;

	protected $_cached_uri = null;
	protected $_cached_href = null;



	/**
	 * Constructor
	 *
	 * @param  string  $uri  (optional) The URI to represent.
	 */
	public function __construct($uri = null) {
		$this->_processUri($uri);
	}// /->__construct()



	/**
	 * Instantiate from an assoc-array of URL parts.
	 *
	 * @param  array  $parts
	 *
	 * @return  object  Ecl_Uri object.
	 */
	public static function fromParts($parts) {
		return new Ecl_Uri(Ecl_Helper_String::buildUrl($parts));
	}// /method



	/**
	 * To String
	 */
	public function __toString() {
		return (string) $this->uri();
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Get or set the fragment.
	 *
	 * @param  string  $port  (optional) The new fragment. (default: null)
	 *
	 * @return  mixed  If setting the fragment, true on success.  If getting, the current fragment.
	 */
	public function fragment($fragment = null) {
		if (is_null($fragment)) {
			return $this->_fragment;
		} else {
			$this->_destroyCaches();
			$this->_fragment = $fragment;
			return true;
		}
	}// method



	/**
	 * Get or set the host.
	 *
	 * @param  string  $host  (optional) The new host. (default: null)
	 *
	 * @return  mixed  If setting the host, true on success.  If getting, the current host.
	 */
	public function host($host = null) {
		if (is_null($host)) {
			return $this->_host;
		} else {
			$this->_destroyCaches();
			$this->_host = $host;
			return true;
		}
	}// method



	/**
	 * Get or set the password.
	 *
	 * @param  integer  $password  (optional) The new password. (default: null)
	 *
	 * @return  mixed  If setting the password, true on success.  If getting, the current password.
	 */
	public function password($password = null) {
		if (is_null($password)) {
			return $this->_password;
		} else {
			$this->_destroyCaches();
			$this->_password = $password;
			return true;
		}
	}// method



	/**
	 * Get or set the path.
	 *
	 * @param  integer  $path  (optional) The new path. (default: null)
	 *
	 * @return  mixed  If setting the path, true on success.  If getting, the current path.
	 */
	public function path($path = null) {
		if (is_null($path)) {
			return $this->_path;
		} else {
			$this->_destroyCaches();
			$this->_path = $path;
			$this->_processPathSegments($this->_path);
			return true;
		}
	}// /method



	/**
	 * Get or set the port value.
	 *
	 * @param  integer  $port  (optional) The new port number. (default: null)
	 *
	 * @return  mixed  If setting the port, true on success.  If getting, the current port number.
	 */
	public function port($port = null) {
		if (is_null($port)) {
			return $this->_port;
		} else {
			$this->_destroyCaches();
			$this->_port = (int) $port;
			return true;
		}
	}// method



	/**
	 * Get or set the scheme.
	 *
	 * @param  integer  $scheme  (optional) The new scheme. (default: null)
	 *
	 * @return  mixed  If setting the scheme, true on success.  If getting, the current scheme.
	 */
	public function scheme($scheme = null) {
		if (is_null($scheme)) {
			return $this->_scheme;
		} else {
			$this->_destroyCaches();
			$this->_scheme = $scheme;
			return true;
		}
	}// method



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
	 * @return  array  Array of path segments. On fail, null.
	 */
	public function segments() {
		return (is_array($this->_path_segments)) ? $this->_path_segments : null ;
	}// /method



	/**
	 * Get or set the username.
	 *
	 * @param  integer  $username  (optional) The new username. (default: null)
	 *
	 * @return  mixed  If setting the username, true on success.  If getting, the current username.
	 */
	public function username($username = null) {
		if (is_null($username)) {
			return $this->_username;
		} else {
			$this->_destroyCaches();
			$this->_username = $username;
			return true;
		}
	}// method



/* ----------------------------------------
 * Querystring Methods
 */



	/**
	 * Add the given querystring parameter.
	 *
	 * If $key already exists, it will become an array with the new value appended.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addQuery($key, $value) {
		$this->_destroyCaches();

		if (isset($this->_query_parts[$key])) {
			$this->_query_parts[$key] = (array) $this->_query_parts[$key];
			$this->_query_parts[$key][] = $value;
		} else {
			$this->_query_parts[$key] = $value;
		}
		return true;
	}// /method



	/**
	 * Get the current value of a querystring key.
	 *
	 * @param  string  $key
	 *
	 * @return  mixed  The value.  On fail, null.
	 */
	public function query($key) {
		return (isset($this->_query_parts[$key])) ? $this->_query_parts[$key] : null ;
	}// /method



	/**
	 * Set the given querystring parameter, overwriting any existing key.
	 *
	 * @param  string  $key
	 * @param  mixed  $value
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setQuery($key, $value) {
		$this->_destroyCaches();

		$this->_query_parts[$key] = $value;
		return true;
	}// /method



	/**
	 * Get the full querystring component.
	 *
	 * @param  boolean  $href_mode  (optional) Encode the '&' characters as '&amp'. (default: false)
	 */
	public function querystring($href_mode = false) {
		if ($href_mode) {
			return Ecl_Helper_String::buildQuerystring($this->_query_parts, '&amp', '=');
		} else {
			return Ecl_Helper_String::buildQuerystring($this->_query_parts, '&');
		}
	}// /method



/* ----------------------------------------
 * Other Methods
 */



	/**
	 * Get just the host URI information.
	 *
	 * Includes the scheme, host, port, username and password.
	 *
	 * @return  string  The host URI string.
	 */
	public function hostUri() {
		$uri_string = '';

		if (!empty($this->_scheme)) {
			$uri_string .= $this->_scheme;
			$uri_string .= (strtolower($this->_scheme)=='mailto') ? ':' : '://' ;
		}

		$uri_string .= (!empty($this->_username)) ? $this->_username . ':' : '' ;
		$uri_string .= (!empty($this->_password)) ? $this->_password . '@' : '' ;
		$uri_string .= (!empty($this->_host)) ? $this->_host : '' ;
		if ( (!empty($this->_port)) & ($this->_port!=80) ) {
			$uri_string .= ':' . $this->_port;
		}

		return $uri_string;
	}// /method



	/**
	 * @return  string  The href encoded URI string.
	 */
	public function href() {
		if (!empty($this->_cached_href)) { return $this->_cached_href; }

		$uri_string = $this->hostUri();

		$uri_string .= $this->_path;

		$qs = $this->querystring(true);
		$uri_string .= (!empty($qs)) ? '?' . $qs : '' ;

		$uri_string .= (!empty($this->_fragment)) ? '#' . $this->_fragment : '' ;

		$this->_cached_href = $uri_string;

		return $uri_string;
	}// /method



	/**
	 * @return  boolean  Is this an https:// or ssl:// request?
	 */
	public function isSecure() {
		return (in_array(strtolower($this->_parts['scheme']), array ('https', 'ssl') ));
	}// /method



	/**
	 * @return  array  The URI's assoc-array representation.
	 */
	public function toParts() {
		return Ecl_Helper_String::parseUrl($this->uri());
	}// /method



	/**
	 * @return  string  The URI string.
	 */
	public function uri() {
		if (!empty($this->_cached_uri)) { return $this->_cached_uri; }

		$uri_string = $this->hostUri();

		$uri_string .= $this->_path;

		$qs = $this->querystring(false);
		$uri_string .= (!empty($qs)) ? '?' . $qs : '' ;

		$uri_string .= (!empty($this->_fragment)) ? '#' . $this->_fragment : '' ;

		$this->_cached_uri = $uri_string;

		return $uri_string;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	protected function _destroyCaches() {
		$this->_cached_uri = null;
		$this->_cached_href = null;
		return true;
	}// /method



	protected function _processUri($uri) {
		$parts = Ecl_Helper_String::parseUrl($uri);

		if (!is_array($parts)) { return false; }

		$this->_scheme = (isset($parts['scheme'])) ? $parts['scheme'] : null ;
		$this->_host = (isset($parts['host'])) ? $parts['host'] : null ;
		$this->_port = (isset($parts['port'])) ? $parts['port'] : 80 ;

		$this->_username = (isset($parts['user'])) ? $parts['user'] : null ;
		$this->_password = (isset($parts['pass'])) ? $parts['pass'] : null ;

		$this->_fragment = (isset($parts['fragment'])) ? $parts['fragment'] : null ;

		if (isset($parts['query'])) {
			// Process query key-values
			$this->_query_parts = Ecl_Helper_String::parseQuerystring($parts['query']);
		}

		if (isset($parts['path'])) {
			$this->path($parts['path']);
		}

		return true;
	}// /method



	/**
	 * Process the URI and determine the path segments (the bits between the slashes)
	 *
	 * @return  boolean  The operation was successful.
	 */
	protected function _processPathSegments($path) {
		// Process path segments
		if (empty($path)) { $this->_path_segments = array(); }

		foreach(explode('/', $path) as $i => $segment) {
			$this->_path_segments[] = Ecl_Helper_String::cleanString($segment);
		}

		return true;
	}// /method



}// /class
?>