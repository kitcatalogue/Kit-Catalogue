<?php


class Ecl_Exception_Ldap extends Ecl_Exception { }
class Ecl_Exception_Ldap_Connect extends Ecl_Exception_Ldap { }
class Ecl_Exception_Ldap_Bind extends Ecl_Exception_Ldap { }



/**
 * LDAP Access Class
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Ldap {

	// Public properties


	// Private properties
	protected $_connection = null;   // LDAP Connection object

	/**
	 * Configuration info of server to connect to
	 *
	 * 'host'          => (string)   Database Server
	 * 'port'          => (integer)  Port number
	 * 'username'      => (string)   Username to use
	 * 'password'      => (string)   Password to use
	 * 'options'       => (integer)  Options to use when connecting
	 * 'debug'         => (boolean)  Use debug mode (verbose error messages)
	 */
	protected $_config = array(
        'host'         => 'localhost' ,
		'port'         => 389 ,
        'username'     => null ,
        'password'     => null ,
        'options'      => array() ,

		'use_tls'      => false ,

		'debug'        => false ,
    );

	protected $_result_set = null;   // The result of the last query

	protected $_error = null;   // Last database error received



	/**
	 * Constructor
	 *
	 * @param  array  $config  (optional) The server connection settings.
	 *
	 * @return  object  A new instance of this class.
	 */
	public function __construct($config = null) {
		$this->_config = array_merge($this->_config, (array) $config);
	} // /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Clear results and associated info.
	 *
	 * @return  boolean  Returns true in all cases.
	 */
	public function clear() {
		if (is_resource($this->_result)) { ldap_free_result($this->_result); }

		$this->_error = null;

		return true;
	} // /method



	/**
	 * Close the current connection.
	 *
	 * @return  boolean  Returns true in all cases.
	 */
	public function close() {
		@ldap_close($this->_connection);
		$this->_connection = null;
		$this->clear();
	} // /method



	/**
	 * Open ldap connection.
	 *
	 * @return  boolean  Connection was successful
	 */
	public function connect() {
		if ($this->_connection) { return true; }

		if ($this->_config['debug']) {
			// Set the debug level
			@ldap_set_option(NULL, LDAP_OPT_DEBUG, 7);
		}

		$this->_connection = ldap_connect($this->_config['host'], $this->_config['port']);

		if (!$this->_connection) {
			$error = 'Unable to connect to LDAP server.';
		} else {
			if ( (is_array($this->_config['options'])) && (!empty($this->_config['options'])) ) {
				foreach($this->_config['options'] as $option => $setting) {
					@ldap_set_option($this->_connection, $option, $setting);
				}
			}

			if ($this->_config['use_secure']) {
				if (!ldap_start_tls($this->_connection)) {
					$error = 'Unable to secure LDAP connection with start-TLS.';
				}
			}

			// If using anonymous binding
			if (empty($this->_config['username'])) {
				if (@ldap_bind($this->_connection)) {
					return true;
				}
				$error = 'Unable to anonymously bind to LDAP server.  Try providing a username and password.';
			} else {
				// Bind using username/password
				if (@ldap_bind($this->_connection, $this->_config['username'], $this->_config['password'])) {
					return true;
				}
				$error = 'Unable to bind to LDAP server.  Invalid credentials. Check the username/password provided.';
			}
		}

		$this->_connection = null;
		$this->_throwError($error, $this->getErrorNumber());
		return false;
	} // /method



	/**
	 * Convert an LDAP entry resource into a collapsed assoc-array of attributes.
	 *
	 * Duplicate attribute values are ignored and only the first will be used.
	 *
	 * @param  resource  $entry  The LDAP entry to process.
	 * @param  array  $attrs  The attributes to look for (LOWER CASE ONLY).
	 *
	 * @return  array  An assoc-array of attribtues results. On fail, null.
	 */
	public function convertEntryToAssoc($entry, $attrs) {
		if (!is_resource($entry)) { return null; }

		$entry_attrs = ldap_get_attributes($this->_connection, $entry);
		$entry_attrs = array_change_key_case($entry_attrs);

		$info = null;
		foreach($attrs as $i => $attr) {
			if (isset($entry_attrs[$attr][0])) {
				$info[$attr]= $entry_attrs[$attr][0];
			}
		}

		return $info;
	}// /method



/* --------------------------------------------------------------------------------
 * Other Methods
 */



	/**
	 * Get the current LDAP connection resource.
	 *
	 * @return  mixed  A connection resource. On fail, null.
	 */
	public function getConnectionResource() {
		$this->connect();
		return $this->_connection;
	}// /method



	/**
	 * Get the last LDAP error message
	 *
	 * @return  string
	 */
	public function getError() {
		if ($this->_connection) {
			return 'Error '. ldap_errno($this->_connection) .' : '. ldap_error($this->_connection);
		} else {
			return 'No LDAP Connection';
		}
	}// /method



	/**
	 * Get the last LDAP error number
	 *
	 * @return  integer  The error number.  On fail, 0.
	 */
	public function getErrorNumber() {
		return ($this->_connection) ? ldap_errno($this->_connection) : 0 ;
	}// /method



	/**
	 * Get an LDAP search result resource representing the query result.
	 *
	 * @return  mixed  A result resource. On fail, null.
	 */
	public function getResultResource() {
		if (!$this->hasResult()) { return null; }
		return $this->_result_set;
	}// /method



	/**
	 * Get the LDAP results..
	 *
	 * Returns a mixed array of data, key-value pairs of attributes and numeric indexes of the attribute keys.
	 * See the PHP LDAP documentation for more information.
	 *
	 * @return  array  An array of entry data. On fail, null.
	 */
	public function getResult() {
		if (!$this->hasResult()) { return null; }
		return ldap_get_entries($this->_connection, $this->_result_set);
	}// /method



	/**
	 * Get the LDAP search result resource representing the query result.
	 *
	 * @return  array  An assoc array of results. On fail, null.
	 */
	public function getResultAssoc() {
		if (!$this->hasResult()) { return null; }

		$entry = ldap_first_entry($this->_connection, $this->_result_set);
		$wanted_attrs = $this->getSearchAttributes();

		$rows = array();
		while (is_resource($entry)) {
			$info = $this->convertEntryToAssoc($entry, $wanted_attrs);
			if ($info) {
				$rows[] = $info;
			}
			$entry = ldap_next_entry($this->_connection, $entry);
		}
		return $rows;
	}// /method



	/**
	 * Get a row of LDAP search result data.
	 *
	 * @param  integer  $row  (optional) The row to return.  (default: 0)
	 *
	 * @return  array  An assoc array of results. On fail, null.
	 */
	public function getRow($row = 0) {
		if (!$this->hasResult()) { return null; }

		$entry = ldap_first_entry($this->_connection, $this->_result_set);
		$pos = 0;

		while ( ($pos<$row) && (is_resource($entry)) ) {
			$entry = ldap_next_entry($this->_connection, $entry);
		}

		if ($pos==$row) {
			$wanted_attrs = $this->getSearchAttributes();
			return $this->convertEntryToAssoc($entry, $wanted_attrs);
		}

		return null;
	}// /method



	/**
	 * Get an array of attributes for the last search query.
	 *
	 * @return  array  Array of attributes.
	 */
	public function getSearchAttributes() {
		return $this->_attrs;
	}// /method



	/**
	 * Is there a current result-set
	 *
	 * @return  boolean  There is a result-set.
	 */
	public function hasResult() {
		return is_resource($this->_result_set);
	}// /method



/* --------------------------------------------------------------------------------
 * Methods for preparing elements of an LDAP query
 */



	/**
	 * Run the given query.
	 *
	 * @param  string  $base_dn
	 * @param  string  $filter
	 * @param  array  $attrs
	 *
	 * @return  integer  The number of entries returned by the query.
	 */
	public function search($base_dn, $filter, $attrs) {
		$this->connect();
		$this->_attrs = array_change_key_case($attrs);
		$this->_result_set = ldap_search($this->_connection, $base_dn, $filter, $attrs);
		return ($this->_result_set) ? ldap_count_entries($this->_connection, $this->_result_set) : 0 ;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	/**
	 * Throw an exception.
	 *
	 * @param  string  $err_msg  The error message to return.
	 * @param  string  $
	 *
	 * @return  boolean  False in all cases.
	 */
	protected function _throwError($err_msg, $err_code) {
		$host = ($this->_connection) ? 'no connection' : $this->_config['host'] ;
		throw new Ecl_Exception_Ldap("LDAP Error ({$host}). $err_msg :: ". $this->getError());
		return false;
	}// /method



}// /class
?>