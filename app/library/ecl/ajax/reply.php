<?php
/**
 * Ajax Reply class
 *
 * A class to faciliate structured responses to AJAX calls.
 *
 * Supports JSON and JSONP replies.
 *
 * @package  Ecl
 * @static
 * @version  1.0.0
 */
Class Ecl_Ajax_Reply {

	// Class constants
	const STATUS_FAIL = 'fail';
	const STATUS_OK = 'ok';

	// Private properties
	protected $_id = null;                  // Client ID (only set by client's request)
	protected $_status = self::STATUS_OK;   // The response status, i.e. 'ok' or 'fail'
	protected $_code = 0;                   // Any code accompanying this response
	protected $_message = '';               // Any messages accompanying this response
	protected $_data = null;                // The content of the response

	protected $_jsonp_callback = null;      // The JSONP callback from the querystring (i.e. ?callback=<function-name>)



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /->__construct()



	public function __toString() {
		return $this->toJson();
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	public function clear() {
		$this->_id = null;
		$this->_status = self::STATUS_OK;
		$this->_code = 0;
		$this->_message = null;
		$this->_data = null;
	}// /method



	/**
	 * Get/Set the reply code.
	 *
	 * @param  integer  $code  (optional) The new code.
	 *
	 * @return  integer  The current code.
	 */
	public function code($code = null) {
		if (func_num_args()>0) {
			$this->_code = (int) $code;
		}
		return $this->_code;
	}// /method



	public function getData($key, $default = null) {
		return (array_key_exists($key, $this->_data)) ? $this->_data[$key] : $default ;
	}// /method



	public function removeData($key) {
		if (array_key_exists($key, $this->_data)) {
			unset($this->_data[$key]);
		}
		return true;
	}// /method



	/**
	 * Set a data entry.
	 *
	 * Entries with null or empty values will still be returned in the reply.
	 * To remove an entry completely, see ->removeData()
	 *
	 * @param  string  $key
	 * @param  mixed  $value  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setData($key, $value = null) {
		$key = (string) $key;
		$this->_data[$key] = $value;
		return true;
	}// /method



	/**
	 * Get/Set the reply message.
	 *
	 * e.g. On 'fail', the error message.
	 *
	 * @param  string  $message  (optional) The new message.
	 *
	 * @return  string  The current message.
	 */
	public function message($message = null) {
		if (func_num_args()>0) {
			$this->_message = utf8_encode($message);
		}
		return $this->_message;
	}// /method



	/**
	 * Get/Set the reply status.
	 *
	 * One of 'ok' or 'fail'.
	 *
	 * @param  string  $status  (optional) The new status.
	 *
	 * @return  string  The current status.
	 */
	public function status($status = null) {
		if (func_num_args()>0) {
			if (!in_array($status, array (self::STATUS_FAIL, self::STATUS_OK))) {
				$this->_status = $status;
			}
		}
		return $this->_status;
	}// /method



	public function setFail($message = null, $code = null) {
		return $this->setStatus(self::STATUS_FAIL, $code, $message);
	}// /method



	public function setOk($message = null) {
		return $this->setStatus(self::STATUS_OK, null, $message);
	}// /method



	/**
	 * Set the status of the AJAX reply.
	 *
	 * @param  string  $status  One of 'ok' or 'fail'.
	 * @param  string  $message  (optional) Any message to accompany the reply (e.g. on 'fail', the error message).
	 * @param  integer  $code  (optional) Any code for the response.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setStatus($status, $message = null, $code = 0) {
		$this->status($status);
		$this->code($code);
		$this->message($message);
		return true;
	}// /method



	/**
     * Setup the AJAX message from the given Ecl_Request object
     *
     * @param  object  $request  A label to output before the dump.
     *
	 * @return  boolean  True in all cases.
	 */
	public function setupFromRequest($request) {
		if (!($request instanceof Ecl_Request)) { return false; }

		$this->clear();

		// Determine where to fetch the request parameters from
		$method = strtolower($request->httpMethod());
		if (!in_array($method, array('get', 'post', 'put'))) { return false; }

		$this->_id = $request->$method('id', null);

		$this->_jsonp_callback = $request->get('callback', null);
		// Sanitize the callback function name
		if ($this->_jsonp_callback) {
			$this->_jsonp_callback = preg_replace('#[^a-zA-Z0-9_."\']#', '', $this->_jsonp_callback);
		}

		return true;
	}// /method



	/**
	 * Return the JSON representation of this reply.
	 *
	 * The "_status" entry is ALWAYS returned.
	 * The "_id", "_code" and "_message" entries only appear if not empty.
	 *
	 * Any data keys that clash with one of the 'official' entries above, will be overriden.
	 * e.g. data['_status'] = 'something' WILL be replaced by the correct 'ok' or 'fail'.
	 *
	 * @return  string
	 */
	public function toJson() {
		$reply = new StdClass();

		if (is_array($this->_data)) {
			foreach($this->_data as $k => $v) {
				$reply->$k = $v;
			}
		}

		// The status and content properties are mandatory, but the others are optional
		// If they're empty, don't show them
		$reply->_status = $this->_status;
		if (!empty($this->_id)) { $reply->_id = $this->_id; }
		if (!empty($this->_code)) { $reply->_code = $this->_code; }
		if (!empty($this->_message)) { $reply->_message = $this->_message; }

		if ($this->_jsonp_callback) {
			return "{$this->_jsonp_callback}(".json_encode($reply, JSON_FORCE_OBJECT) .");";
		} else {
			return json_encode($reply, JSON_FORCE_OBJECT);
		}
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>