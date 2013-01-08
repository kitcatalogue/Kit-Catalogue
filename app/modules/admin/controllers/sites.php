<?php
/*
 *
 */
class Controller_Admin_Sites extends Ecl_Mvc_Controller {



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
		$this->router()->layout()->addBreadcrumb('Sites', $this->router()->makeAbsoluteUri('/admin/sites/index/'));
		$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	/**
	 * Create a new site.
	 */
	public function actionCreate() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->request()->isPost()) {
			$errors = false;

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$site_name = $this->request()->post('name');
			if (empty($site_name)) {
				$errors[] = 'You must provide the name of your new site.';
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$new_site = $this->model('sitestore')->newSite();
				$new_site->name = $site_name;
				$new_id = $this->model('sitestore')->insert($new_site);

				if ($new_id) {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The campus site '{$new_site->name} has been added");
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an unspecified error adding your new campus site.');
				}
			}
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Unable to create site.  No information posted.');
		}

		$this->action('index');
	}// /method



	/**
	 * Edit an existing site.
	 */
	public function actionEdit() {

		$site = $this->model('sitestore')->find($this->param('id'));

		if (empty($site)) {
			$this->router()->action('404', 'error');
			return;
		}


		if ($this->request()->isPost()) {

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The form details supplied appear to be forged.');
			} else {


				if ($this->request()->post('submitdelete')) {
					$this->model('sitestore')->delete($site->id);
					$this->layout()->clearBreadcrumbs(2);
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The site has been deleted');
					$this->action('index');
					return;
				}


				if ($this->request()->post('submittransfer')) {

					$target_site = $this->model('sitestore')->find($this->request()->post('destination'));
					if (empty($target_site)) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The items could not be transferred.', 'The destination site selected could not be found.');
					} else {
						$this->model('itemstore')->transferSiteItems($site->id, $target_site->id);

						if (1 == $this->request()->post('delete_on_transfer')) {
							$this->model('sitestore')->delete($site->id);
							$this->layout()->clearBreadcrumbs(2);
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "All items have been transfered and the original site deleted.");
							$this->action('index');
							return;
						} else {
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'All items have been transfered.');
						}
					}
				}


				if ($this->request()->post('submitsave')) {
					$errors = false;

					$site->name = $this->request()->post('name');
					if (empty($site->name)) {
						$errors[] = 'You must provide a name for the campus site.';
					}

					if ($errors) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
					} else {
						$saved_ok = $this->model('sitestore')->update($site);

						if (!$saved_ok ) {
							$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
						} else {
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
						}
					}
				}


			}// /if(session key OK)

		}

		$this->view()->site = $site;
		$this->view()->render('sites_edit');
	}// /method



	/**
	 *
	 */
	public function actionIndex() {
		$this->view()->sites = $this->model('sitestore')->findAll();
		$this->view()->render('sites_index');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>