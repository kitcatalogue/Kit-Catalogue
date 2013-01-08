<?php



Ecl::load('Ecl_Http');



/**
 * HTTP Response class
 *
 * @package Ecl
 * @version 1.0.0
 */
class Ecl_Http_Response {

	// Public Properties

	// Private Properties
	protected $_req_scheme = null;
	protected $_req_host = null;
	protected $_req_port = null;
	protected $_req_path = null;
	protected $_req_query = null;
	protected $_req_fragment = null;
	protected $_req_username = null;
	protected $_req_password = null;

	protected $_headers = array();
	protected $_content = null;
	protected $_is_content = false;
	protected $_status = null;

	protected $_cookies = array();       // Assoc-array : (field => value)



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Add a cookie.
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
	 * Add a header to the request.
	 *
	 * Some headers should be set through the appropriate method.
	 * Adding a header better handled elsewhere will return false.
	 *
	 * @param  string  $name  The header name.
	 * @param  mixed  $value  The header value.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addHeader($name, $value) {
		$name = Ecl_Http::prepareHeaderName($name);

		switch ($name) {
			// --------------------
			case 'Location':
				$bits = parse_url($value);
				if (is_array($bits)) {
					// Check if the location field is missing information (e.g. it's a relative URL instead of a full URL)
					$rebuild = false;
					if (!array_key_exists('scheme', $bits)) {
						$bits['scheme'] = $this->_req_scheme;
						$rebuild = true;
					}
					if (!array_key_exists('host', $bits)) {
						$bits['host'] = $this->_req_host;
						$rebuild = true;
					}

					if ($rebuild) {
						$value = $bits['scheme'] . '://' . $bits['host'] . $bits['path'];
					}
				}
				$this->_headers[$name][] = $value;
				break;
			// --------------------
			case 'Set-Cookie':
				$bits = explode(';', $value);
				if (is_array($bits)) {
					foreach($bits as $i => $bit) {
						$output = Ecl_Helper_String::parseQuerystring($bit);
						if (is_array($output)) {
							foreach($output as $k => $v) {
								$this->addCookie(trim($k), $v);
							}
						}
					}
				}
				break;

			// --------------------
			default:
				$this->_headers[$name][] = $value;
				break;
		}
		return true;
	}// /method



	/**
	 * Set the response information using the given string.
	 *
	 * @param  string  $response_string  Populate the response using the given HTTP reponse string.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function fromString($response_string) {

		$headers = null;
		$content = null;

		$bits = explode("\r\n\r\n", $response_string, 2);
		if (count($bits)==2) {
			$headers = $bits[0];
			$content = trim($bits[1]);
		} else {
			$headers = $response_string;
		}

		$headers_array = explode("\r\n", $headers);
		if (is_array($headers_array)) {

			$first_line = trim($headers_array[0]);
			if (strpos($first_line,'HTTP/')===0) {
				$bits = explode(' ', $first_line);
				if (array_key_exists(1, $bits)) {
					$status_code = (int) $bits[1];
					if (array_key_exists($status_code, Ecl_Http::$status_codes)) {
						$this->_status = $status_code;
					}
				}
			}

			foreach($headers_array as $i => $v) {
				$colon_pos = strpos($v, ':');
				if ($colon_pos !== false) {
					$name = substr($v, 0, $colon_pos);
					$value = trim(substr($v, $colon_pos+1));
					$this->addHeader($name, $value);
				}
			}
		}

		if (!empty($content)) {
			if ($this->getHeader('Transfer-Encoding')) {
				$this->_content = Ecl_Helper_String::dechunkString($content);
			} else {
				$this->_content = $content;
			}
			$this->_is_content = true;
		}


		return true;
	}// /method



	/**
	 * Get the content of the response string.
	 *
	 * @return  mixed  The response content. On fail, null.
	 */
	public function getContent() {
		return $this->_content;
	}// /method



	/**
	 * Get any cookies set by the response.
	 *
	 * @return  mixed  An array of cookie key-value pairs. On fail, null.
	 */
	public function getCookies() {
		if ($this->_cookies) {
			return $this->_cookies;
		} else {
			return null;
		}
	}// /method



	/**
	 * Get the value of a header in the response.
	 *
	 * @param  string  $name  The header name.
	 * @param  mixed  $default  (optional) The default value to return, if the header is not present.
	 *
	 * @return  mixed  The header, or array of headers, corresponding to the request name. On fail, the default.
	 */
	public function getHeader($name, $default = null) {
		$name = Ecl_Http::prepareHeaderName($name);
		if (array_key_exists($name, $this->_headers)) {
			$last_header = count($this->_headers[$name])-1;
			return $this->_headers[$name][$last_header];
		} else {
			return $default;
		}
	}// /method



	/**
	 * Get all the headers returned in the response.
	 *
	 * @return  mixed  An assoc-array of headers. On fail, null.
	 */
	public function getHeaders() {
		return $this->_headers;
	}// /method



	public function getStatus() {
		return $this->_status;
	}// /method



	/**
	 * Set the given header to the value requested.
	 *
	 * This method will overwrite any existing header information of the same name.
	 * Use ->addHeader() to avoid overwriting, and add multiple headers of the same name.
	 * Values are not escaped.
	 */
	public function setHeader($name, $value) {
		$name = Ecl_Http::prepareHeaderName($name);
		$this->_headers[$name] = array ($value);
	}// /method



	/**
	 * Set the URL that was called to generate this response.
	 *
	 * You must provide a full and complete URL.
	 * Use the HttpRequest ->getUrl() method for a definitive URL.
	 *
	 * @param  string  $url  The URL.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setRequestedUrl($url) {
		$url_bits = parse_url(trim($url));

		$this->_req_scheme = (isset($url_bits['scheme'])) ? $url_bits['scheme'] : 'http' ;
		$this->_req_host = (isset($url_bits['host'])) ? $url_bits['host'] : null ;
		$this->_req_port = (isset($url_bits['port'])) ? $url_bits['port'] : 80 ;
		$this->_req_path = (isset($url_bits['path'])) ? $url_bits['path'] : '/' ;
		$this->_req_query = (isset($url_bits['query'])) ? '?' . $url_bits['query'] : null ;
		$this->_req_fragment = (isset($url_bits['fragment'])) ? '#' . $url_bits['fragment'] : null ;

		$this->_req_username = (isset($url_bits['user'])) ? $url_bits['user'] : null ;
		$this->_req_password = (isset($url_bits['pass'])) ? $url_bits['pass'] : null ;

		return true;
	}// /method



}// /class
?>