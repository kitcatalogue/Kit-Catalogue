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
			//maintainance
			///'username'    => $username . $this->_model->get('ldap.username_suffix') ,
			'username'    => 'uid='.$username.','. $this->_model->get('ldap.dn') ,
			'password'    => $password ,
			'options'     => $this->_model->get('ldap.options', array()) ,
			'use_secure'  => $this->_model->get('ldap.use_secure', false) ,
			'debug'       => $this->_model->get('ldap.debug', false),
		));
		
		///new vardump

		try {
			$ldap->connect();

			$base_dn = $this->_model->get('ldap.dn');
			
			//non flexible, old implementation
			//$attrs = array('uidNumber', 'uid', 'givenname', 'sn', 'mail');
			$attrs = array($this->_model->get('ldap.id_column', "employeenumber"),
					$this->_model->get('ldap.username_column', "name"),
					$this->_model->get('ldap.forename_column', "givenname"),
					$this->_model->get('ldap.surname_column', "sn"),
					$this->_model->get('ldap.mail_column', "mail"),
					);
			
			$filter = $attrs[1]."={$username}";
			$entry_count = $ldap->search($base_dn, $filter, $attrs);

			if ($entry_count>0) {
				$ldap_row = $ldap->getRow();

				$user_row['id'] = (isset($ldap_row[$attrs[0]])) ? $ldap_row[$attrs[0]] : null ;
				$user_row['username'] = (isset($ldap_row[$attrs[1]])) ? $ldap_row[$attrs[1]] : null ;
				$user_row['forename'] = (isset($ldap_row[$attrs[2]])) ? $ldap_row[$attrs[2]] : null ;
				$user_row['surname'] = (isset($ldap_row[$attrs[3]])) ? $ldap_row[$attrs[3]] : null ;
				$user_row['email'] = (isset($ldap_row[$attrs[4]])) ? $ldap_row[$attrs[4]] : null ;

				return $user_row;
			}
		} catch (Ecl_Exception_Ldap_Bind $e) {
			return null;
		} catch (Ecl_Exception_Ldap $e) {
			return null;
		}

		///TODO If we reach this we could authenticate with the LDAP server, but when asking for our own information this was not possible. This will most likely be connected to the wrong attributes in the "$attrs" var or when reading the ldap_rows!
		return null;
	}// /method

	/**
	 * Check if the current session contains Shibboleth authentication information.
	 _Exception_Ldap 
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

function p($data)
{
	echo "<pre>";
	print_r($data);
	echo "</pre>";
}

}// /class

       
?>

