<?php
/**
 * A class representing a user.
 *
 * @version  1.0.0
 */
class Ecl_User {

	// Public properties
	public $id = null;
	public $forename = 'Anonymous';
	public $surname = '';
	public $email = null;
	public $username = null;

	// Private properties
	protected $_params = array();
	protected $_session_key = null;



	/**
	 * Constructor
	 */
	public function __construct() {
		$this->clear();
	}// /->__construct()



	public function __get($name) {
		switch ($name) {
			case 'name':
				return "{$this->forename} {$this->surname}";
			case 'sortname':
				return "{$this->surname}, {$this->forename}";
			case 'url_username':
				return urlencode($this->username);
			default:
				throw new InvalidArgumentException("Unknown 'virtual' property: '$name'");
		}
	}// /method



/* ================================================================================
 * Public Methods
 */



	/**
	 * Check if the given session key is valid.
	 *
	 * Empty keys, or keys that
	 *
	 * @param  integer  $key
	 *
	 * @return  boolean  The session key is valid.
	 */
	public function checkSessionKey($key) {
		return (!empty($key)) && ($this->getSessionKey() == $key);
	}// /method



	/**
	 * Clear this class' state.
	 */
	public function clear() {
		$this->id        = null;
		$this->forename  = 'Anonymous';
		$this->surname   = '';
		$this->email     = null;
		$this->username  = null;

		$this->_session_key  = null;
		$this->_params       = array();

		$this->generateSessionKey();

		return true;
	}// /method



	/**
	 * Set the properties of this class using the given assoc-array of fields.
	 *
	 * @param  array  $array  Assoc-array of fields representing a user.
	 * @return  void
	 */
	public function fromAssoc($array) {
		$this->id         = $array['id'];
		$this->forename   = $array['forename'];
		$this->surname    = $array['surname'];
		$this->username   = $array['username'];
		$this->email      = $array['email'];
		$this->_session_key  = (isset($array['session_key'])) ? $array['session_key'] : null ;
		$this->_params       = (isset($array['params'])) ? unserialize($array['params']) : array() ;
	}// /method



	/**
	 * Get this User's current session key.
	 *
	 * @return  int  The current session key.
	 */
	public function getSessionKey() {
		return $this->_session_key;
	}// /method



	/**
	 * Generate a session key for this User.
	 *
	 * @return  boolean  The generation was successful.
	 */
	public function generateSessionKey() {
		$this->_session_key = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);   // 5 digit session key
		return true;
	}// /method



	/**
	 * @param  string  $key  The param to check.
	 *
	 * @return  boolean  The given param is set.
	 */
	public function hasParam($key) {
		return isset($this->_params[$key]);
	}// /method



	/**
	 * @return  boolean  The user is anonymous.
	 */
	public function isAnonymous() {
		return (empty($this->username));
	}// /method



	/**
	 * @param  string  $key  The key to retrieve.
	 * @param  mixed  $default  (optional)  (default: null)
	 *
	 * @return  mixed  The requested value. On fail, the default.
	 */
	public function param($key, $default = null) {
		return (isset($this->_params[$key])) ? $this->_params[$key] : $default ;
	}// /method



	/**
	 * @param  mixed  $key  The key to remove.
	 *
	 * @return  boolean  The key was removed successfully.
	 */
	public function removeParam($key) {
		unset($this->_params[$key]);
		return true;
	}// /method



	/**
	 * Set the given param.
	 *
	 * @param  mixed  $key  The key, or array of keys, to set.
	 * @param  mixed  $value  (optional) The required value (default: true).
	 *
	 * @return  boolean  The entry was set successfully.
	 */
	public function setParam($key, $value = true) {
		if (is_array($key)) {
			foreach($key as $i => $key_name) {
				$this->_params[$key_name] = $value;
			}
		} else {
			$this->_params[$key] = $value;
		}
		return true;
	}// /method



	/**
	 * Get the assoc-array representation of this User.
	 *
	 * @return  array  An assoc-array of fields representing this User object.
	 */
	public function toAssoc() {
		$fields['id'] = $this->id;
		$fields['forename'] = $this->forename;
		$fields['surname'] = $this->surname;
		$fields['email'] = $this->email;
		$fields['username'] = $this->username;
		$fields['session_key'] = $this->_session_key;
		$fields['params'] = serialize($this->_params);
		return $fields;
	}// /method



/* ================================================================================
 * Private Methods
 */



}// /class
?>