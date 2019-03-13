<?php
/*
 *
 */
class Controller_Signin extends Ecl_Mvc_Controller {



	public function actionCheck() {
		if ( $this->model('signin.use_shibboleth_only') && $this->model('signin.use_shibboleth') ) {
			$this->response()->setRedirect($this->model('app.www'). '/signin/?msg=notsupported');
			return;
		}


		$username = $this->request()->post('username');
		$password = $this->request()->post('password');

		session_regenerate_id();

		$user_info = null;

		// @idea : Include throttling to prevent login spam

		// Call the different authentication options in turn...

		if ($this->model('signin.use_ldap')) {
			$user_info = $this->model('authenticator')->authenticateLdap($username, $password);
		}

		if ( (empty($user_info)) && ($this->model('signin.use_database')) ) {
			$user_info = $this->model('authenticator')->authenticateDatabase($username, $password);
		}

		if (empty($user_info)) {
			$user_info = $this->model('plugins')->executeUntilResult('signin.authenticate', array($username, $password));
		}

		if (empty($user_info)) {
			// Unsuccessful, go back to login page
			$this->response()->setRedirect($this->model('app.www'). '/signin/?msg=failed');
		} else {
			// Successful
			$this->model('authenticator')->loginUser($user_info);
			$this->response()->setRedirect($this->router()->makeAbsoluteUri('/', $this->model('app.use_https')));
		}
	}// /method



	public function actionIndex() {
		if ( $this->model('signin.use_shibboleth_only') && $this->model('signin.use_shibboleth') ) {
			$sso_url = $this->model('shib.idp.url');
			if (empty($sso_url)) {
				$sso_url = $this->router()->makeAbsoluteUri('/sso/');
			}
			$this->response()->setRedirect($sso_url);
		} else {
			$this->view()->render('signin_index');
		}
	}// /method



	public function actionSignout() {
		$this->model('userstore')->clearUserSession();
		$this->model()->setObject('user', $this->model('userstore')->newUser());
		$this->response()->setRedirect($this->model('app.www'). '?msg=signedout');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
