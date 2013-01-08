<?php



Ecl::load('Ecl_Http');



/**
 * HTTP Request class
 *
 * @package Ecl
 * @version 1.0.0
 */
class Ecl_Http_Request {

	// Public Properties

	// Private Properties
	protected $_method = 'GET';

	protected $_url = null;   // Assoc-array of url parts

	protected $_username = '';
	protected $_password = '';

	protected $_headers = array();       // Assoc-array of array : (name => array (value, value) )

	protected $_cookies = array();       // Assoc-array : (field => value)
	protected $_form = array();          // Assoc-array : (field => value)

	protected $_content = null;



	/**
	 * Constructor
	 */
	public function __construct() {
		// Set default headers.
		$this->addHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
		$this->addHeader('Accept-Charset', 'ISO-8859-1,utf-8;q=0.7,*;q=0.7');
		$this->addHeader('Accept-Language', 'en-gb,en;q=0.5');
		$this->addHeader('Connection', 'close');
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Add a cookie to the request.
	 *
	 * @param  string  $name  The cookie name.
	 * @param  mixed  $value  The cookie value.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addCookie($name, $value) {
		$this->_cookies[$name] = $value;
		return true;
	}// /method



	/**
	 * Add a form field to post.
	 *
	 * Form fields will only be posted with a request if you change the method to POST.
	 *
	 * @param  string  $name  The field name.
	 * @param  mixed  $value  The field value.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addFormField($name, $value) {
		$this->_form[$name] = $value;
		return true;
	}// /method



	/**
	 * Add a header to the request.
	 *
	 * Some headers should be set through the appropriate method.
	 *
	 * @param  string  $name  The header name.
	 * @param  mixed  $value  The header value.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addHeader($name, $value) {
		$name = Ecl_Http::prepareHeaderName($name);

		switch ($name) {
			case 'Cookie':
				return $this->addCookie($name, $value);
				break;
			case 'Post':
				return $this->addFormField($name, $value);
				break;
			default:
				$this->_headers[$name][] = $value;
				return true;
				break;
		}
		return false;
	}// /method



	/**
	 * @return  mixed  The header, or array of headers, corresponing to the request name.
	 *
	 * @param  string  $name  The header name.
	 * @param  mixed  $default  (optional) The default value to return. (default: null)
	 *
	 * @return  mixed  The value of the requested header, or default if not present.
	 */
	public function getHeader($name, $default = null) {
		$name = Ecl_Http::prepareHeaderName($name);
		return (array_key_exists($name, $this->_headers)) ? $this->_headers[$name] : $default ;
	}// /method



	/**
	 * Get the path, including querystring and fragment, for the current request.
	 *
	 * @return  string  The requested path.
	 */
	public function getHttpPath() {
		$path = $this->_url['path'];

		if (array_key_exists('query', $this->_url)) {
			$path .= '?' . $this->_url['query'];
		}

		if (array_key_exists('fragment', $this->_url)) {
			$path .= '#' . $this->_url['fragment'];
		}
		return $path;
	}// /method



	/**
	 * Get the host used by the current request (i.e. The domain name).
	 *
	 * For example, 'www.example.com'.
	 *
	 * @return  mixed  The host. On fail, null.
	 */
	public function getHost() {
		return (array_key_exists('host', $this->_url)) ? $this->_url['host'] : null ;
	}// /method



	/**
	 * Get the port used by the current request.
	 *
	 * For example, '80' or '443'.
	 *
	 * @return  mixed  The port. On fail, null.
	 */
	public function getPort() {
		return (array_key_exists('port', $this->_url)) ? $this->_url['port'] : null ;
	}// /method



	/**
	 * Get the scheme used by the current request.
	 *
	 * For example, 'http' or 'ftp'.
	 *
	 * @return  mixed  The scheme. On fail, null.
	 */
	public function getScheme() {
		return (array_key_exists('scheme', $this->_url)) ? $this->_url['scheme'] : null ;
	}// /method



	/**
	 * Get the URL of the current request.
	 *
	 * @return  string  The current URL.
	 */
	public function getUrl() {
		return Ecl_Helper_String::buildUrl($this->_url);
	}// /method



	/**
	 * Remove the given header.
	 *
	 * @param  string  $name  The header name.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function removeHeader($name) {
		unset($this->_headers[$name]);
		return true;
	}// /method



	/**
	 * Set the HTTP request content.
	 *
	 * If $content is blank, related headers (such as Content-Type and Content-Length) will be removed.
	 *
	 * @param  string  $content  The content of the request.
	 * @param  mixed  $content_type  (optional) The content mime-type.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setContent($content, $content_type = null) {
		$this->_content = $content;

		if (empty($this->_content)) {
			$this->removeHeader('Content-Length');
			$this->setHeader('Content-Length', 0);
		} else {
			if ($content_type) { $this->setHeader('Content-Type', $content_type); }
			$this->setHeader('Content-Length', strlen($content));
		}
		return true;
	}// /method



	/**
	 * Set the given header to the value requested.
	 *
	 * This method will overwrite any existing header information of the same name.
	 * Use ->addHeader() to avoid overwriting, and add multiple headers of the same name.
	 * Values are not escaped.
	 *
	 * @param  string  $name  The header name.
	 * @param  string  $value  The header value.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setHeader($name, $value) {
		$name = Ecl_Http::prepareHeaderName($name);
		$this->_headers[$name] = array ($value);
		return true;
	}// /method



	/**
	 * Set login credentials.
	 *
	 * @param  string  $username  The username.
	 * @param  string  $password  The password.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setLogin($username, $password) {
		$this->_username = $username;
		$this->_password = $password;
		return true;
	}// /method



	/**
	 * Set the HTTP Request method.
	 *
	 * If the given method is invalid, GET will be assumed.
	 *
	 * @param  string  $method  The HTTP transport method to use.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setMethod($method) {
		$method = strtoupper($method);
		switch($method) {
			case 'DELETE':
			case 'GET':
			case 'HEAD':
			case 'OPTIONS':
			case 'POST':
			case 'PUT':
			case 'TRACE':
				$this->_method = $method;
				break;
			default:
				$this->_method = 'GET';
		}
		return true;
	}// /method



	/**
	 * Set URL to request.
	 *
	 * @param  string  $url  The URL.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setUrl($url) {
		$this->_url = Ecl_Http::parseUrl($url);
		return (!empty($this->_url));
	}// /method



	/**
	 * Get the string representation of this object.
	 *
	 * @return  string  The string representation.
	 */
	public function toString() {
		$get_path = $this->getHttpPath();


		// start HTTP header
		$lines = array();
		$lines[] = "{$this->_method} {$get_path} HTTP/1.1";
		$lines[] = 'Host: '. $this->getHost();


		// If username given.. attempt login using username/password
		if ($this->_username && $this->_password) {
			$lines[] = 'Authorization: Basic '. base64_encode("{$this->_username}:{$this->_password}");
		}


		// Process content
		$content = null;

		if ($this->_method=='POST') {
			$content .= Ecl_Helper_String::buildQuerystring($this->_form, '&');
			$this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
		}

		$content .= "\r\n\r\n" . $this->_content;


		// Process basic headers
		foreach($this->_headers as $k => $v) {
			if (is_array($v)) {
				foreach($this->_headers[$k] as $i => $line) {
					$lines[] = Ecl_Http::prepareHeaderName($k) .': '. $line;
				}
			}
		}


		// Check if there are Cookie headers to send
		if ($this->_cookies) {
			$cookie_str = '';
			foreach($this->_cookies as $k => $v) {
				if (!empty($cookie_str)) { $cookie_str .='; '; }

				if ( (strtolower($k)=='secure') && (empty($v)) ) {
					$cookie_str .= 'Secure';
				} else {
					$v = str_replace(' ', '+', $v);
					$cookie_str .= "$k=$v";
				}
			}

			$lines[] = 'Cookie: '. $cookie_str;
		}


		// Build the final string
		$request_string = implode("\r\n", $lines);
		$request_string .= $content;

		// @debug : Ecl::dump($request_string, 'Request String', true);

		return $request_string;
	}// /method



}// /class
?>