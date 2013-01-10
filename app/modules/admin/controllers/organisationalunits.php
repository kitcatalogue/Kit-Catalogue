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
		$this->router()->layout()->addBreadcrumb('Organisationalunits', $this->router()->makeAbsoluteUri('/admin/organisationalunits/index/'));
		$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	/**
	 * Create a new organisation.
	 */
	public function actionCreate() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->request()->isPost()) {
			$errors = false;

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$organisation_name = $this->request()->post('name');
			if (empty($organisation_name)) {
				$errors[] = 'You must provide the name of your new organisation.';
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$new_org = $this->model('organisationstore')->newOrganisation();
				$new_org->name = $organisation_name;
				$new_id = $this->model('organisationstore')->insert($new_org);

				if ($new_id) {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The organisation '{$new_org->name} has been added");
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an unspecified error adding your new organisation.');
				}
			}
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Unable to create organisation.  No information posted.');
		}

		$this->action('index');
	}// /method



	/**
	 * Edit an existing organisation.
	 */
	public function actionEdit() {

		$organisation = $this->model('organisationstore')->find($this->param('id'));

		if (empty($organisation)) {
			$this->router()->action('404', 'error');
			return;
		}


		if ($this->request()->isPost()) {

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The form details supplied appear to be forged.');
			} else {


				if ($this->request()->post('submitdelete')) {
					$this->model('organisationstore')->delete($organisation->id);
					$this->layout()->clearBreadcrumbs(2);
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The organisation has been deleted');
					$this->action('index');
					return;
				}


				if ($this->request()->post('submittransfer')) {

					$target_organisation = $this->model('organisationstore')->find($this->request()->post('destination'));
					if (empty($target_organisation)) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The items could not be transferred.', 'The destination organisation selected could not be found.');
					} else {
						$this->model('itemstore')->transferOrganisationItems($organisation->id, $target_organisation->id);

						if (1 == $this->request()->post('delete_on_transfer')) {
							$this->model('organisationstore')->delete($organisation->id);
							$this->layout()->clearBreadcrumbs(2);
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "All items have been transfered and the original organisation deleted.");
							$this->action('index');
							return;
						} else {
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'All items have been transfered.');
						}
					}
				}


				if ($this->request()->post('submitsave')) {
					$errors = false;

					$organisation->name = $this->request()->post('name');
					if (empty($organisation->name)) {
						$errors[] = 'You must provide a name for the organisation.';
					}

					if ($errors) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
					} else {
						$saved_ok = $this->model('organisationstore')->update($organisation);

						if (!$saved_ok ) {
							$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
						} else {
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
						}
					}
				}


			}// /if(session key OK)

		}

		$this->view()->organisationalunit = $organisationalunit;
		$this->view()->render('organisationalunits_edit');
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