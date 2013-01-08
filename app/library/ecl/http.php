<?php



Ecl::load('Ecl_Helper_String');
Ecl::load('Ecl_Http_Request');
Ecl::load('Ecl_Http_Response');



/**
 * Class which provides several HTTP related functions, and acts as a factory for HTTP requests and responses.
 *
 * Call statically.
 *
 * @static
 * @package Ecl
 * @version 1.0.0
 */
class Ecl_Http {

	// Class Constants
	const MAX_REDIRECTS = 10;

	// Public Properties
	public static $status_codes = array (
		100  => 'Continue' ,
		101  => 'Switching Protocols' ,

		200  => 'OK' ,
		201  => 'Created' ,
		202  => 'Accepted' ,
		203  => 'Non-Authoritative Information' ,
		204  => 'No Content' ,
		205  => 'Reset Content' ,
		206  => 'Partial Content' ,
		207  => 'Multi-Status (WebDAV)' ,

		300  => 'Multiple Choices' ,
		301  => 'Moved Permanently' ,
		302  => 'Found' ,
		303  => 'See Other' ,
		304  => 'Not Modified' ,
		305  => 'Use Proxy' ,
		306  => 'Switch Proxy' ,
		307  => 'Temporary Redirect' ,

		400  => 'Bad Request' ,
		401  => 'Unauthorized' ,
		402  => 'Payment Required' ,
		403  => 'Forbidden' ,
		404  => 'Not Found' ,
		405  => 'Method Not Allowed' ,
		406  => 'Not Acceptable' ,
		407  => 'Proxy Authentication Required' ,
		408  => 'Request Timeout' ,
		409  => 'Conflict' ,
		410  => 'Gone' ,
		411  => 'Length Required' ,
		412  => 'Precondition Failed' ,
		413  => 'Request Entity Too Large' ,
		414  => 'Request-URI Too Long' ,
		415  => 'Unsupported Media Type' ,
		416  => 'Requested Range Not Satisfiable' ,
		417  => 'Expectation Failed' ,
		422  => 'Unprocessable Entity (WebDAV)' ,
		423  => 'Locked (WebDAV)' ,
		424  => 'Failed Dependency (WebDAV)' ,
		425  => 'Unordered Collection (WebDAV)' ,
		426  => 'Upgrade Required' ,
		449  => 'Retry With' ,

		500  => 'Internal Server Error' ,
		501  => 'Not Implemented' ,
		502  => 'Bad Gateway' ,
		503  => 'Service Unavailable' ,
		504  => 'Gateway Timeout' ,
		505  => 'HTTP Version Not Supported' ,
		506  => 'Variant Also Negotiates' ,
		507  => 'Insufficient Storage (WebDAV)' ,
		509  => 'Bandwidth Limit Exceeded' ,
		510  => 'Not Extended' ,
	);

	// Private Properties
	protected static $_use_auto_cookies = true;

	protected static $_domain_cookies = array();

	protected static $_redirect_count = 0;



	/**
	 * Constructor
	 */
	protected function __construct() {
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * De-chunk a multi-line split string into a single string.
	 *
	 * @param  string  $str  The string to de-chunk.
	 *
	 * @return  string  The de-chunked string.
	 */
	public static function dechunkString($str) {

		$dechunked_str = '';

		while (!empty($str)) {
			$str = ltrim($str);
			$eol_pos = strpos($str, "\r\n");

			if ($eol_pos === false) {
				$dechunked_str .= $str;
				return $dechunked_str;
			} else {
				$len = hexdec(substr($str, 0, $eol_pos));
				if ( (!is_numeric($len)) || ($len<1) ) {
					$dechunked_str .= $str;
					return $dechunked_str;
				}
				$dechunked_str .= substr($str, ($eol_pos + 2), $len);
				$str = ltrim( substr($str, ($len + $eol_pos + 2)));
			}
		}

		return $dechunked_str;
	}// /method



	/**
	 * Get the escaped form of the given header value.
	 *
 	 * @param  mixed  $value  The value to escape.
 	 *
 	 * @return  string  The escape header value.
	 */
	public static function escapeHeaderValue($value) {
		return urlencode($value);
	}// /method



	/**
	 * Get the value of the requested cookie for the given domain.
	 *
	 * @param  string  $domain  The domain to check. (case insensitive)
	 * @param  string  $key  The cookie key to find.
	 * @param  mixed  $default  (optional) The default value if the cookie cannot be found. (default: null)
	 *
	 * @return  mixed  The cookie value. On fail, the default.
	 */
	public static function getDomainCookie($domain, $key, $default = null) {

		$domain = strtolower($domain);

		if (array_key_exists($domain, self::$_domain_cookies)) {
			if (array_key_exists($key, self::$_domain_cookies[$domain])) {
				return self::$_domain_cookies[$domain][$key];
			}
		}

		return $default;
	}// /method



	/**
	 * Get all the cookies stored for the given domain.
	 *
	 * @param  string  $domain  The domain to check. (case insensitive)
	 *
	 * @return  mixed  The assoc-array of cookie key-value pairs. On fail, null.
	 */
	public static function getDomainCookies($domain) {

		$domain = strtolower($domain);

		if (!array_key_exists($domain, self::$_domain_cookies)) {
			return null;
		} else {
			return self::$_domain_cookies[$domain];
		}
	}// /method



	/**
	 * Retrieve the descriptive message string for the given HTTP status code.
	 *
	 * @param  integer  $status_code  The status code.
	 *
	 * @return  mixed  The status message string. On fail, null.
	 */
	public static function getStatusMessage($status_code) {
		$status_code = (int) $status_code;
		return (array_key_exists($status_code, self::$status_codes)) ? self::$status_codes[$status_code] : null ;
	}// /method



	/**
	 * Open an HTTP 1.0 GET request to the target URL return the result
	 *
	 * If given a username and password, uses HTTP Basic Authentication to access the target
	 * For more complicated URL fetching use an HttpRequest object.
	 *
	 * @param  string  $url  The URL to open.
	 * @param  integer  $timeout  (optional) The timeout duration in seconds. (default: 5)
	 * @param  string  $username  (optional) The username to use for HTTP Basic Authentication. (overrides URL username, if any)
	 * @param  string  $password  (optional) The password to use for HTTP Basic Authentication. (overrides URL password, if any)
	 *
	 * @return  mixed  The HTTP response. On failure, null.
	 */
	public static function fetchUrl($url, $timeout = 5, $username = null, $password = null) {
		$url_bits = self::parseUrl($url);

		switch (strtolower($url_bits['scheme'])) {
			case 'https':
			case 'ssl':
				$scheme = 'ssl://';
				break;
			default:
				$scheme = '';
				break;
		}// /switch(scheme)

		$port = (isset($url_bits['port'])) ? $url_bits['port'] : 80 ;

		$handle = @fsockopen($scheme.$url_bits['host'], $port, $err_num, $err_string, $timeout);

		if ($handle) {
			$path = (isset($url_bits['path'])) ? $url_bits['path'] : '/' ;
			$query = (isset($url_bits['query'])) ? '?' . $url_bits['query'] : null ;
			$fragment = (isset($url_bits['fragment'])) ? '#' . $url_bits['fragment'] : null ;

			$get_path = $path . $query . $fragment;

			// start HTTP header
			$headers = array();
			$headers[] = "GET $get_path HTTP/1.0";
			$headers[] = "Host: {$url_bits['host']}";

			// If username given.. attempt login using username and password
			if (!empty($username)) {
				$headers[] = 'Authorization: Basic '. base64_encode("$username:$password");
			} else {
				if ( (isset($url_bits['username'])) && (isset($url_bits['password'])) ) {
					$headers[] = 'Authorization: Basic '. base64_encode("{$url_bits['username']}:{$url_bits['password']}");
				}
			}

			$headers[] = "User-Agent: Mozilla/4.0 (compatible; MSIE 7.0; PHP; en-GB;)";

			$headers[] = "Connection: close";

			// HTTP headers are separated by \r\n newlines, and end an empty line
			$http_header = implode("\r\n", $headers) . "\r\n\r\n";

			fputs($handle, $http_header);

			// Get contents of page
			$contents = '';
			while (!feof($handle)) {
				$contents .= fread($handle, 8192);
			}
			fclose($handle);
			return $contents;
		} else {
			return null;
		}
	}// /method


	/**
	 * Get only the response content from an HTTP request.
	 *
	 * @see fetchUrl()
	 *
	 * @return  mixed  The text of the given page. On failure, null.
	 */
	public static function fetchUrlContent($url, $timeout = 5, $username = null, $password = null) {
		$content = self::fetchUrl($url, $timeout, $username, $password);

		// Find the end of the HTTP headers, and return the rest of the response
		if (!empty($content)) {
			$content = substr($content, strpos($content, "\r\n\r\n")+4);
		}

		return $content;
	}// /method



	/**
	 * Check whether the given URL strings contain the same domain name.
	 *
	 * @param  string  $url_1  The first URL to check.
	 * @param  string  $url_2  The second URL to check.
	 *
	 * @return  boolean  The two URLs are from the same domain.
	 */
	public static function isSameDomain($url_1, $url_2) {
		$bits_1 = parse_url($url_1);
		$bits_2 = parse_url($url_2);

		if ( (is_array($bits_1)) && (is_array($bits_2)) ) {
			$host_1 = (array_key_exists('host', $bits_1)) ? $bits_1['host'] : null ;
			$host_2 = (array_key_exists('host', $bits_2)) ? $bits_2['host'] : null ;

			return ( strtolower($host_1)==strtolower($host_2) );
		}

		return false;
	}// /method



	/**
	 * Get a new HttpRequest object
	 *
	 * @param  string  $url  (optional) The url to request. (default: null)
	 *
	 * @return  object  HttpRequest object.
	 */
	public static function newHttpRequest($url = null) {
		$req = new Ecl_Http_Request();

		if ($url) { $req->setUrl($url); }
		return $req;
	}// /method



	/**
	 * Get a new HttpResponse object.
	 *
	 * @return  object  An HttpResponse object.
	 */
	public static function newHttpResponse() {
		return new Ecl_Http_Response();
	}// /method



	/**
	 * Normalise the given header name, so it uses Capitalised words.
	 *
	 * @param  string  $header_name
	 *
	 * @return  string  The normalised header
	 */
	public static function normaliseHeader($header_name) {
		$header_name = str_replace('-', ' ', $header_name);   // Remove hypens so ucwords() will work next!
		$header_name = ucwords(strtolower($header_name));
		$header_name = str_replace(' ', '-', $header_name);
		return $header_name;
	}// /method



	/**
	 * Process a HTTP quality-rated list of parameters in to an array.
	 *
	 * Useful for headers like Accept and Accept-Language.
	 * Returns an assoc-array of the form array ( item => quality, .. )
	 * Any item with a quality of 0 will be omitted from the list.
	 *
	 * @param  string  $list_string  The list to process.
	 *
	 * @return  array  The array produced.
	 */
	public static function parseQualityList($list_string) {
		if (empty($list_string)) { return null; }

		$list_string = strtolower( str_replace(' ', '', $list_string) );

		$item_array = array();

		$list = explode(',', $list_string);
		if (is_array($list)) {
			foreach($list as $i => $item) {
				$temp = explode(';q=', $item);
				if (count($temp)==2) {
					$entry = $temp[0];
					$quality = sprintf('%01.3f', $temp[1]);
				} else {
					$entry = $temp[0];
					$quality = sprintf('%01.3f', 1);
				}

				if ($quality>1) { $quality = sprintf('%01.3f', 1); }

				if ($quality!=0) {
					$item_array["$quality"][] = $entry;
				}
			}
			krsort($item_array);

			$final_array = null;
			foreach($item_array as $k => $list) {
				foreach($list as $i => $item) {
					$final_array[] = $item;
				}
			}
		}
		return $final_array;
	}// /method



	/**
	 * @see Ecl_Helper_String::parseUrl()
	 *
	 * @param  string  $url  The URL to parse.
	 *
	 * @return  mixed  Array of URL parts.  On fail, null.
	 */
	public static function parseUrl($url) {
		return Ecl_Helper_String::parseUrl($url);
	}// /method



	/**
	 * Convert a date to a format suitable for HTTP headers.
	 *
	 * @param  datetime  $date  (optional) The date to convert. (default: null = current date/time)
	 */
	public static function prepareDate($date = null) {
		if (is_null($date)) { $date = time(); }
		return gmdate('D, d M Y H:i:s \G\M\T', $date);  // RFC 1123 format - the inbuilt PHP constant for this appears to be wrong?
	}// /method



	/**
	 * Prepare a header name, so it uses the correct capitalisation and syntax.
	 *
	 * @param  string  The header_name to process.
	 *
	 * @return  string  The properly formed version of the header name.
	 */
	public static function prepareHeaderName($header_name) {
		return join('-', array_map('ucwords', explode('-', $header_name) ) );
	}// /method



	/**
	 * Process a HTTPRequest object and fetch the response.
	 *
	 * @param  object  $request  The Ecl_Http_Request object to send.
	 * @param  integer  $timeout  (optional) The timeout in seconds. (default: 5)
	 * @param  boolean  $follow_redirects  (optional) Follow any server redirects and only return the final server response. (default: false)
	 *
	 * @return  mixed  The HttpResponse object returned. On fail, null.
	 */
	public static function sendRequest($request, $timeout = 10, $follow_redirects = false) {
		self::$_redirect_count = 0;

		return self::_sendRequestObject($request, $timeout, $follow_redirects);
	}// /method



	/**
	 * Set a cookie for the given domain.
	 *
	 * @param  string  $domain  The domain to use. (case insensitive)
	 * @param  string  $key  The cookie key to set.
	 * @param  mixed  $default  The value to use.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setDomainCookie($domain, $key, $value) {

		$domain = strtolower($domain);

		if (!array_key_exists($domain, self::$_domain_cookies)) {
			self::$_domain_cookies[$domain] = array();
		}

		self::$_domain_cookies[$domain][$key] = $value;

		return true;
	}// /method



	/**
	 * Set whether to automatically use any domain cookies received when a request is redirected multiple times.
	 *
	 * Turn this on if a URL request is redirected multiple times, and later urls in the redirect sequence
	 * expect the follow-on requests to include previously set domain cookies.
	 * e.g. The LUSI portal login systems.
	 *
	 * @param  boolean  $on  The state of automatic domain cookies
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function useAutomaticDomainCookies($on) {
		self::$_use_auto_cookies = ($on!=false);
		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	/**
	 * Process a HTTPRequest object and fetch the response.
	 *
	 * @param  object  $request  The Ecl_Http_Request object to send.
	 * @param  integer  $timeout  (optional) The timeout in seconds. (default: 5)
	 * @param  boolean  $follow_redirects  (optional) Follow any server redirects and only return the final server response. (default: false)
	 *
	 * @return  mixed  The HttpResponse object returned. On fail, null.
	 */
	protected static function _sendRequestObject($request, $timeout = 10, $follow_redirects = false) {
		$raw_response = null;

		// Get the connection details
		$hostname = strtolower($request->getHost());

		$scheme = strtolower($request->getScheme());
		switch ($scheme) {
			case 'https':
			case 'ssl':
			case 'tls':
				$conn_hostname = 'ssl://'. $hostname;
				break;
			case 'http':
			default:
				$conn_hostname = $hostname;
				break;
		}


		// Check for any domain cookies that need sending
		if (array_key_exists($hostname, self::$_domain_cookies)) {
			foreach(self::$_domain_cookies[$hostname] as $k => $v) {
				$request->addCookie($k, $v);
			}
		}


		// Send the HTTP Request
		$raw_response = self::_sendRequestString($conn_hostname, $request->getPort(), $request->toString(), $timeout);

		// Build the response object
		$response = self::newHttpResponse();
		$response->setRequestedUrl($request->getUrl());
		$response->fromString($raw_response);

		// If we're following redirects, check if we need to redirect
		if ($follow_redirects) {
			self::$_redirect_count++;

			// Store any domain cookies
			if (self::$_use_auto_cookies) {
				$cookies = $response->getCookies();
				if ($cookies) {
					foreach($cookies as $k => $v) {
						self::$_domain_cookies[$hostname][$k] = $v;
					}
				}
			}

			if (self::$_redirect_count<self::MAX_REDIRECTS) {

				$status = (string) $response->getStatus();
				// If we're redirecting, send a new request to the redirect location
				if ( ($status) && ($status[0]=='3') ) {
					$redirect_to = $response->getHeader('Location');

					if (!empty($redirect_to)) {
						$request = self::newHttpRequest($redirect_to);
						$response = self::_sendRequestObject($request, $timeout, $follow_redirects);
					}
				}
			}
		}
		return $response;
	}// /method



	/**
	 * Send the HTTP request string, and return the server response string.
	 *
	 * @param  string  $hostname  The host to send the request to.
	 * @param  string  $port  The port to send the request to.
	 * @param  string  $request_string  The request to send.
	 * @param  integer  $timeout  The time in seconds to wait for a response.
	 *
	 * @return  string  The raw HTTP response string. On fail, null.
	 */
	protected static function _sendRequestString($hostname, $port, $request_string, $timeout) {

		$raw_response = null;

		if (empty($request_string)) { return null; }
		$err_num = null;
		$err_string = null;

		$handle = @fsockopen($hostname, $port, $err_num, $err_string, $timeout);

		if (!$handle) {
			return null;
		} else {
			// @debug :	Ecl::dump($request_string, 'HTTP :: full request');

			// Sent the request and get the response
			fwrite($handle, $request_string);
			$raw_response = stream_get_contents($handle);
			fclose($handle);
		}

		// @debug : Ecl::dump($raw_response, 'HTTP :: full response');

		return $raw_response;
	}// /method



}// /class
?>