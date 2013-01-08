<?php
/*
 *
 */
class Controller_Admin_Users extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->abort();
			$this->router()->action('unauthorised', 'error');
			return false;
		}

		$this->router()->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/admin/'));
		$this->router()->layout()->addBreadcrumb('Users', $this->router()->makeAbsoluteUri('/admin/users/index/'));
		$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	public function actionCreate() {
		// If we're cancelling
		if ($this->request()->post('submitcancel')) {
			$this->response()->setRedirect($this->router()->makeAbsoluteUri('/admin/users/index/'));
			return;
		}

		$this->router()->layout()->addBreadcrumb('Add New User', null);

		if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
			$errors[] = 'The form details supplied appear to be forged.';
		}


		$user = $this->model('userstore')->newUser();

		if ($this->request()->isPost()) {
			$user->username = $this->request()->post('username');
			$user->forename = $this->request()->post('forename');
			$user->surname = $this->request()->post('surname');
			$user->email = $this->request()->post('email');

			$password = $this->request()->post('password');
			$confirm_password = $this->request()->post('confirm_password');

			$errors = false;
			if (empty($user->username)) { $errors[] = 'You must provide a username.'; }
			if (empty($user->forename)) { $errors[] = 'You must provide a forename.'; }
			if (empty($user->surname)) { $errors[] = 'You must provide a surname.'; }

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$existing_user = $this->model('userstore')->findForUsername($user->username);
				if ($existing_user) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, "The username '{$user->username}' is already in use.");
				} else {
					$new_id = $this->model('userstore')->insert($user);
					if ($new_id) {
						if (!empty($password)) {
							if ($password != $confirm_password) {
								$errors[] = 'The new password and its confirmation did not match.';
							} else {
								$this->model('userstore')->setPassword($user->username, $password);
							}
						}
						$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The new user '{$user->name}' has been added.");
						$this->response()->setRedirect($this->router()->makeAbsoluteUri("/admin/users/edit/{$user->url_username}"));
					} else {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an unspecified error adding your new user.');
					}
				}
			}
		}

		$this->view()->user = $user;
		$this->view()->render('users_create');
	}// /method



	public function actionEdit() {

		// If we're cancelling
		if ($this->request()->post('submitcancel')) {
			$this->response()->setRedirect($this->router()->makeAbsoluteUri('/admin/users/index/'));
			return;
		}

		$user = $this->model('userstore')->findForUsername($this->param('id'));

		if (empty($user)) {
			$this->layout()->clearBreadcrumbs(2);
			$this->layout()->clearFeedback();
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Unable to load the requested user.');
			$this->view()->render('users_index');
			return;
		}


		if ($this->request()->isPost()) {
			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}


			if ($this->request()->post('submitdelete')) {
				if ($this->model('user')->username != $user->username) {
					$this->model('userstore')->delete($user->username);
					$this->layout()->clearBreadcrumbs(2);
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The user has been deleted');
					$this->action('index');
					return;
				} else {
					$errors[] = 'You cannot delete your own user account.';
				}
			}


			$user->username = $this->request()->post('username');
			$user->forename = $this->request()->post('forename');
			$user->surname = $this->request()->post('surname');
			$user->email = $this->request()->post('email');

			$password = $this->request()->post('password');
			$confirm_password = $this->request()->post('confirm_password');

			$errors = false;
			if (empty($user->username)) { $errors[] = 'You must provide a username.'; }
			if (empty($user->forename)) { $errors[] = 'You must provide a forename.'; }
			if (empty($user->surname)) { $errors[] = 'You must provide a surname.'; }


			// Being processing authorisations
			$authorisations = array ();

			// Process Department Authorisations
			$dept_auths = $this->request()->post('auth_dept');
			if (!empty($dept_auths)) {
				foreach($dept_auths as $dept_perm_id) {
					$authorisations[$dept_perm_id] = KC__AUTH_CANEDIT;
				}
			}

			// Process System Authorisations
			// If the current user is editing themselves, ignore any System Authorisation changes
			if ($this->model('user')->username != $user->username) {
				$system_auths = $this->request()->post('auth_system', array());
				if (!empty($system_auths)) { $authorisations['system'] = $system_auths; }
			} else {
				if ($this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
					$authorisations['system'] = array (KC__AUTH_CANADMIN);
				}
			}

			$this->model('sysauth')->replaceForAgent($user->username, $authorisations);


			if (!$errors) {
				$this->model('userstore')->update($user);
				if (!empty($password)) {
					if ($password != $confirm_password) {
						$errors[] = 'The new password and its confirmation did not match.';
					} else {
						$this->model('userstore')->setPassword($user->username, $password);
					}
				}
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "Your changes to '{$user->name}' have been saved.");
			}

		}

		$this->view()->user = $user;
		$this->view()->existing_user = true;
		$this->view()->render('users_edit');
	}// /method



	public function actionIndex() {
		$this->view()->render('users_index');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>