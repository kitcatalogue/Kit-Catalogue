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
		$lang = $this->model('lang');
		$lower_ou_adminsection = strtolower($lang['ou.label.adminsection']);
		$lower_ou_label = strtolower($lang['ou.label']);

		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();


		if ($this->request()->isPost()) {
			$errors = false;

			$ou_store = $this->model('organisationalunitstore');

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$parent_ou = $ou_store->find($this->request()->post('ou_id'));
			if (empty($parent_ou)) {
				$errors[] = "Unable to find parent $lower_ou_label. You cannot add to the $lower_ou_adminsection without the parent unit.";
			}

			$ou_name = $this->request()->post('ou_name');
			if (empty($ou_name)) {
				$errors[] = "You must provide the name of your new $lower_ou_label.";
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$new_ou = $ou_store->newOrganisationalunit();

				$new_ou->name = $ou_name;
				$new_ou->url = $this->request()->post('ou_url');

				$new_id = $ou_store->insert($new_ou, $parent_ou->id);
				if ($new_id) {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The $lower_ou_label '{$new_ou->name}' has been added");
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an unspecified error adding your new $lower_ou_label.');
				}
			}
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Unable to create $lower_ou_label.  No information posted.');
		}

		$this->action('index');
	}// /method



	/**
	 * Delete an existing organisational unit.
	 */
	public function actionDelete() {
		$lang = $this->model('lang');
		$lower_ou_label = strtolower($lang['ou.label']);

		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->request()->isPost()) {
			$errors = false;

			$ou_store = $this->model('organisationalunitstore');
			$ou = $ou_store->find($this->request()->post('ou_id'));
			if (empty($ou)) {
				$errors[] = "Unable to find $lower_ou_label.";
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				if (0 == $ou->tree_level) {
					$errors[] = "Unable to delete root $lower_ou_label";
				} else {
					$ou_store->delete($ou->id);
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The $lower_ou_label '{$ou->name}' has been deleted.");
				}
			}
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, "Unable to delete $lower_ou_label.  No information posted.");
		}

		$this->action('index');
	}



	/**
	 * Edit an existing organisational unit.
	 */
	public function actionEdit() {
		$lang = $this->model('lang');
		$lower_ou_adminsection = strtolower($lang['ou.label.adminsection']);
		$lower_ou_label = strtolower($lang['ou.label']);

		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if (!$this->request()->isPost()) {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, "Unable to edit $lower_ou_label.  No information posted.");
		} else {
			$errors = false;

			$ou_store = $this->model('organisationalunitstore');

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$ou = $ou_store->find($this->request()->post('ou_id'));
			if (empty($ou)) {
				$errors[] = "Unable to find $lower_ou_label.";
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				// Change of location
				$new_location = $this->request()->post('ou_location');

				if ( (!empty($new_location)) && ($ou->id != $new_location) ) {
					$node = $this->model('ou_tree')->findForRef($ou->id);
					$new_parent = $this->model('ou_tree')->findForRef($new_location);

					if ($node && $new_parent) {
						if ($this->model('ou_tree')->isDescendedFrom($node, $new_parent)) {
							$errors[] = "You cannot move any $lower_ou_label to a location below itself in the $lower_ou_adminsection.";
						} else {
							$this->model('ou_tree')->transplant($node, $new_parent);
						}
					}
				}

				$ou->name = $this->request()->post('ou_name');
				$ou->url = $this->request()->post('ou_url');

				if (empty($ou->name)) {
					$errors[] = "You must provide a name for your $lower_ou_label.";
				}

				if ($errors) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
				} else {
					$saved_ok = $ou_store->update($ou);

					if (!$saved_ok ) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
					} else {
						$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
					}
				}
			}
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



	public function actionLabels() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if (!$this->request()->isPost()) {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, "Unable to edit $lower_ou_label.  No information posted.");
		} else {
			$levels = $this->request()->postPrefixed('level_', true);
			if (!empty($levels)) {
				$saved_ok = $this->model('organisationalunitstore')->setLevelLabels($levels);

				if (!$saved_ok ) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
				}
			}
		}

		$this->action('index');
	}



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>