<?php
/**
 * Authenticator class
 *
 * @version 1.0.0
 */
class Authenticator {

	// Private Properties
	protected $_model = null;
	protected $_db = null;



	/**
	 * Constructor
	 */
	public function __construct($model) {
		$this->_model = $model;
		$this->_db = $this->_model->get('db');
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Authenticate the given username and password against the Kit-Catalogue user database.
	 *
	 * @param  string  $username
	 * @param  string  $password
	 *
	 * @return  array  An assoc-array of user info, keys ('id', 'username', 'forename', 'surname', 'email'). On fail, null.
	 */
	public function authenticateDatabase($username, $password) {
		$user = $this->_model->get('userstore')->authenticateDb($username, $password);
		if (empty($user)) { return null; }

		return array (
			'id'        => $user['id'] ,
			'username'  => $user['username'] ,
			'forename'  => $user['forename'] ,
			'surname'   => $user['surname'] ,
			'email'     => $user['email'] ,
		);
	}// /method



	/**
	 * Authenticate the given username and password against the LDAP server.
	 *
	 * @param  string  $username
	 * @param  string  $password
	 *
	 * @return  array  An assoc-array of user info, keys ('id', 'username', 'forename', 'surname', 'email'). On fail, null.
	 */
	public function authenticateLdap($username, $password) {
		$ldap = Ecl::factory('Ecl_Ldap', array (
			'host'        => $this->_model->get('ldap.host') ,
			'port'        => $this->_model->get('ldap.port') ,
			'username'    => $username . $this->_model->get('ldap.username_suffix') ,
			'password'    => $password ,
			'options'     => $this->_model->get('ldap.options', array()) ,
			'use_secure'  => $this->_model->get('ldap.use_secure', false) ,
			'debug'       => false ,
		));

		try {
			$ldap->connect();

			$base_dn = $this->_model->get('ldap.dn');
			$filter = "name={$username}";
			$attrs = array('employeenumber', 'name', 'givenname', 'sn', 'mail');

			$entry_count = $ldap->search($base_dn, $filter, $attrs);

			if ($entry_count>0) {
				$ldap_row = $ldap->getRow();
				$user_row['id'] = (isset($ldap_row['employeenumber'])) ? $ldap_row['employeenumber'] : null ;
				$user_row['username'] = (isset($ldap_row['name'])) ? $ldap_row['name'] : null ;
				$user_row['forename'] = (isset($ldap_row['givenname'])) ? $ldap_row['givenname'] : null ;
				$user_row['surname'] = (isset($ldap_row['sn'])) ? $ldap_row['sn'] : null ;
				$user_row['email'] = (isset($ldap_row['mail'])) ? $ldap_row['mail'] : null ;

				return $user_row;
			}
		} catch (Ecl_Exception_Ldap_Bind $e) {
			return null;
		} catch (Ecl_Exception_Ldap $e) {
			return null;
		}
		return null;
	}// /method



	/**
	 * Check if the current session contains Shibboleth authentication information.
	 *
	 * The 'id' user info (mapped from employeeNumber) is optional.
	 *
	 * @return  array  An assoc-array of user info, keys ('id', 'username', 'forename', 'surname', 'email'). On fail, null.
	 */
	public function authenticateShibboleth() {
		$attrs = array('username', 'email', 'id', 'forename', 'surname');
		$user_row = array();

		foreach($attrs as $attr) {
			$name = $this->_model->get("shib.{$attr}.attr");
			$regex = $this->_model->get("shib.{$attr}.regex");

			if ( ($name) && (isset($_SERVER[$name])) ) {
				if (!$regex) {
					$user_row[$attr] = $_SERVER[$name];
				} else {
					$matches = null;
					if (preg_match($regex, $_SERVER[$name], $matches)) {
						$user_row[$attr] = $matches[1];
					}
				}
			} else {
				$user_row[$attr] = '';
			}
		}

		if (empty($user_row['username'])) { return null; }

		return ($user_row) ? $user_row : null ;
	}// /method



	public function loginUser($user_info) {
		session_regenerate_id();

		if ($this->_model->get('signin.log')) {
			$this->_model->get('db')->insert('log_signin', array (
				'date_signin'  => $this->_model->get('db')->formatDate(time()) ,
				'user_id'      => (isset($user_info['id'])) ? $user_info['id'] : '' ,
				'username'     => $user_info['username'] ,
			));
		}

		$user_info['params'] = array();

		if (!empty($user_info['email'])) {
			$owned_items = $this->_model->get('itemstore')->findForContact($user_info['email']);
			if ($owned_items->count()>0) {
				$user_info['params'][KC__USER_HASITEMS] = true;
			}
		}

		$this->_model->get('userstore')->setUserSession($user_info);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>