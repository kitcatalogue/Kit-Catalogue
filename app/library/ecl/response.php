<?php



class Ecl_Response_Exception extends Ecl_Exception {}
class Ecl_Response_Exception_HeadersSentException extends Ecl_Response_Exception {}
class Ecl_Response_Exception_InvalidHttpStatusCodeException extends Ecl_Response_Exception {}



/**
 * A class for handling responses to the client.
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Response {

	// Public Properties


	// Private Properties

	protected $_http_response_code = 200;

	protected $_is_redirect = false;   // Is this response a HTTP 3xx redirect?

	protected $_headers = array();   // Array of headers

	protected $_content = null;   // The body content to send



	public function __construct() {

	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Append to the current response content.
	 *
	 * @param  string  $content
	 *
	 * @return  boolean  The operation was succesful.
	 */
	public function append($content) {
		$this->_content .= (string) $content;
		return true;
    }// /method



    public function clear() {
    	$this->_http_response_code = 200;
    	$this->_is_redirect = false;
    	$this->_headers = array();
    	$this->_content = null;
    }// /method



    /**
     * Get/Set the response contents.
     *
     * @return  string  The body content.
     */
    public function content($content = null) {
    	if (func_num_args()>0) { $this->_content = $content; }
    	return $this->_content;
    }// /method



    /**
	 * Prepend to the current response content.
	 *
	 * @param  string  $content
	 *
	 * @return  boolean  The operation was succesful.
	 */
	public function prepend($content) {
		$this->_content = (string) $content . $this->_content;
		return true;
    }// /method



    /**
     * Send the response.
     *
     * @return  boolean  The operation was successful.
     */
    public function send() {

		$this->_sendHeaders();

    	if (!$this->_is_redirect) {
    		$this->_sendContent();
    	}

    	return true;
    }// /method



	/**
	 * Set a HTTP response header.
	 *
	 * @param  string  $name
	 * @param  string  $content
	 * @param  boolean  $replace  (optional) Replace any existing headers with the same name. (default: false)
	 *
	 * @return  boolean  The operation was succesful.
	 */
	public function setHeader($name, $content, $replace = false) {

		$name = Ecl_Http::normaliseHeader($name);

		if ($replace) {
			foreach ($this->_headers as $i => $header) {
                if ($name == $header['name']) {
                    unset($this->_headers[$i]);
                }
            }
		}

		$this->_headers[] = array (
			'name'     => $name ,
			'content'  => $content ,
		);

		return true;
	}// /method



	/**
	 * @param  integer  $code
	 *
	 * @return  boolean  The operation was succesful.
	 */
	public function setHttpResponseCode($code) {
		if (is_null(Ecl_Http::getStatusMessage($code))) {
			throw new Ecl_Response_Exception_InvalidHttpStatusCodeException("Invalid HTTP Response code given: '$code'", 1);
		}
		$this->_http_response_code = (int) $code;
		$this->_is_redirect = ( (300<=$code) && (307>=$code) );
		return true;
	}// /method



   /**
     * Set a Location redirect header to the given URL
     *
     * @param  string  $url
     * @param  integer  $code  (optional)  The HTTP Response Code, 300-307.  (default: 302)
     *
     * @return   boolean  The operation was successful.
     */
    public function setRedirect($url, $code = 302) {
        $this->setHeader('Location', $url, true);
        $this->setHttpResponseCode($code);
        return true;
    }// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



    protected function _sendContent() {
		echo $this->content();
    	return true;
    }// /method



    protected function _sendHeaders() {
    	if (headers_sent($file, $line)) {
    		throw new Ecl_Response_Exception_HeadersSentException("Headers already sent in file: '$file' on line '$line'", 1);
    	} else {
			header('HTTP/1.1 ' . $this->_http_response_code);

			if (!empty($this->_headers)) {
				foreach($this->_headers as $i => $header) {
					header($header['name'] .': '. $header['content']);
				}
			}
		}
		return true;
    }// /method



}// /class
?>