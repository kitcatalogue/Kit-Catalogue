<?php
/*
 *
 */
class Controller_Admin_Organisationalunits extends Ecl_Mvc_Controller {



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
		$this->router()->layout()->addBreadcrumb($this->model('lang')->get('ou.label.adminsection'), $this->router()->makeAbsoluteUri('/admin/organisationalunits/index/'));
		$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	/**
	 * Create a new organisational unit.
	 */
	public function actionCreate() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->request()->isPost()) {
			$errors = false;

			$ou_store = $this->model('organisationalunitstore');

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$parent_ou = $ou_store->find($this->request()->post('ouid'));
			if (empty($parent_ou)) {
				$errors[] = 'Unable to find parent organisational unit. You cannot add to the organisational structure without the parent unit.';
			}

			$ou_name = $this->request()->post('name');
			if (empty($ou_name)) {
				$errors[] = 'You must provide the name of your new organisational unit.';
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$new_ou = $ou_store->newOrganisationalunit();

				$new_ou->level = $parent_ou->level + 1;

				// @todo : separate tree from ou

				$siblings = $ou_store->findChildrenForLeftRight($parent_ou->left, $parent_ou->right);
				if (empty($siblings)) {

				}

				$new_ou->name = $organisation_name;
				$new_ou->url = $this->request()->post('url');

				$new_id = $ou_store->insert($new_ou);

				if ($new_id) {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The organisational unit '{$new_ou->name} has been added");
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an unspecified error adding your new organisational unit.');
				}
			}
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Unable to create organisational unit.  No information posted.');
		}

		$this->action('index');
	}// /method



	/**
	 *
	 */
	public function actionIndex() {
		$this->view()->organisationalunits = $this->model('organisationalunitstore')->findTree();
		$this->view()->render('organisationalunits_index');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>