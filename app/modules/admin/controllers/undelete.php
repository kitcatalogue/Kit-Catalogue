<?php
/*
 *
 */
class Controller_Admin_Undelete extends Ecl_Mvc_Controller {



	/**
	 * Before action method.
	 */
	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->abort();
			$this->router()->action('unauthorised', 'error');
			return false;
		}

		$this->router()->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/admin/'));
		$this->router()->layout()->addBreadcrumb('Access Levels', $this->router()->makeAbsoluteUri('/admin/accesslevels/index/'));
		$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	/**
	 * Create a new access level.
	 */
	public function actionCreate() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->request()->isPost()) {
			$errors = false;

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$accesslevel_name = $this->request()->post('name');
			if (empty($accesslevel_name)) {
				$errors[] = 'You must provide the name of your new access level.';
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$new_accesslevel = $this->model('accesslevelstore')->newAccesslevel();
				$new_accesslevel->name = $accesslevel_name;
				$new_id = $this->model('accesslevelstore')->insert($new_accesslevel);

				if ($new_id) {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The access level '{$new_accesslevel->name} has been added");
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an unspecified error adding your new access level.');
				}
			}
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Unable to create access level.  No information posted.');
		}

		$this->action('index');
	}// /method



	/**
	 * Edit an existing access level.
	 */
	public function actionEdit() {

		$accesslevel = $this->model('accesslevelstore')->find($this->param('id'));

		if (empty($accesslevel)) {
			$this->router()->action('404', 'error');
			return;
		}


		if ($this->request()->isPost()) {

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The form details supplied appear to be forged.');
			} else {


				if ($this->request()->post('submitdelete')) {
					$this->model('accesslevelstore')->delete($accesslevel->id);
					$this->layout()->clearBreadcrumbs(2);
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The access level has been deleted');
					$this->action('index');
					return;
				}


				if ($this->request()->post('submitsave')) {
					$errors = false;

					$accesslevel->name = $this->request()->post('name');
					if (empty($accesslevel->name)) {
						$errors[] = 'You must provide a name for the access level.';
					}

					if ($errors) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
					} else {
						$saved_ok = $this->model('accesslevelstore')->update($accesslevel);

						if (!$saved_ok ) {
							$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
						} else {
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
						}
					}
				}


			}// /if(session key OK)

		}

		$this->view()->accesslevel = $accesslevel;
		$this->view()->render('accesslevels_edit');
	}// /method



	/**
	 *
	 */
	public function actionIndex() {
    // work here:
		$this->view()->accesslevels = $this->model('accesslevelstore')->findAll();
		$this->view()->render('accesslevels_index');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>