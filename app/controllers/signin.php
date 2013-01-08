<?php
/*
 *
 */
class Controller_Signin extends Ecl_Mvc_Controller {



	public function actionCheck() {
		$username = $this->request()->post('username');
		$password = $this->request()->post('password');

		session_regenerate_id();

		$user_info = null;

		// @idea : Include throttling to prevent login spam

		// @idea : Change the built-in authentication options so they are plugins like the local-config ones

		// Call the different authentication options in turn...

		if ($this->model('signin.use_ldap')) {
			$user_info = $this->_authenticateLdap($username, $password);
		}

		if ( (empty($user_info)) && ($this->model('signin.use_database')) ) {
			$user_info = $this->_authenticateDatabase($username, $password);
		}

		if (empty($user_info)) {
			$user_info = $this->model('plugins')->executeUntilResult('signin.authenticate', array($username, $password));
		}

		if (empty($user_info)) {
			// Unsuccessful, go back to login page
			$this->response()->setRedirect($this->model('app.www'). '/signin/?msg=failed');
		} else {

			if ($this->model('signin.log')) {
				$this->model('db')->insert('log_signin', array (
					'date_signin'  => $this->model('db')->formatDate(time()) ,
					'user_id'      => $user_info['id'] ,
					'username'     => $username ,
				));
			}

			$user_info['params'] = array();


			if (!empty($user_info['email'])) {
				$owned_items = $this->model('itemstore')->findForContact($user_info['email']);
				if ($owned_items->count()>0) {
					$user_info['params'][KC__USER_HASITEMS] = true;
				}
			}

			$this->model('userstore')->setUserSession($user_info);

			$this->response()->setRedirect($this->model('app.www'));
		}
	}// /method



	public function actionIndex() {
		$this->view()->render('signin_index');
	}// /method



	public function actionSignout() {
		$this->model('userstore')->clearUserSession();
		$this->model()->setObject('user', $this->model('userstore')->newUser());
		$this->response()->setRedirect($this->model('app.www'). '?msg=signedout');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	/**
	 * Authenticate the given username and password against the Kit-Catalogue user database.
	 *
	 * @param  string  $username
	 * @param  string  $password
	 *
	 * @return  array  An assoc-array of user info, keys ('id', 'username', 'forename', 'surname', 'email'). On fail, null.
	 */
	protected function _authenticateDatabase($username, $password) {
		$user = $this->model('userstore')->authenticateDb($username, $password);
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
	protected function _authenticateLdap($username, $password) {
		$ldap = Ecl::factory('Ecl_Ldap', array (
			'host'        => $this->model('ldap.host') ,
			'port'        => $this->model('ldap.port') ,
			'username'    => $username . $this->model('ldap.username_suffix') ,
			'password'    => $password ,
			'options'     => $this->model('ldap.options', array()) ,
			'use_secure'  => $this->model('ldap.use_secure', false) ,
			'debug'       => false ,
		));

		try {
			$ldap->connect();

			$base_dn = $this->model('ldap.dn');
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



}// /class
?>