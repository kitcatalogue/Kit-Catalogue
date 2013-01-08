<?php
class Controller_Admin_Customfields extends Ecl_Mvc_Controller {



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->abort();
			$this->router()->action('unauthorised', 'error');
			return false;
		}

		$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
		$this->router()->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/admin/'));
		$this->router()->layout()->addBreadcrumb('Items', $this->router()->makeAbsoluteUri('/admin/items/index/'));
	}// /method



	public function actionCreate() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		$errors = false;

		if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
			$errors[] = 'The form details supplied appear to be forged.';
		}

		$customfield = $this->model('customfieldstore')->newCustomfield();

		$customfield->name = $this->request()->post('name');
		if (empty($customfield->name)) {
			$errors[] = 'You must provide a name for your new field.';
		}

		if ($errors) {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
		} else {
			$new_id = $this->model('customfieldstore')->insert($customfield);

			if ($new_id) {
				$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The field '{$customfield->name}' has been added");
			} else {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an error adding the field.  Check the field name is unique and try again.');
			}
		}

		$this->action('index');
	}



	public function actionIndex() {
		$this->view()->custom_fields = $this->model('customfieldstore')->findAll();
		$this->view()->render('customfields_index');
	}// /method



	public function actionEdit() {

		$field = $this->model('customfieldstore')->find($this->param('id'));

		if (empty($field)) {
			$this->router()->action('404', 'error');
			return;
		}

		if ($this->request()->isPost()) {

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The form details supplied appear to be forged.');
			} else {

				if ($this->request()->post('submitdelete')) {
					$this->model('customfieldstore')->delete($field->id);
					$this->layout()->clearBreadcrumbs(2);
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The custom field has been deleted');
					$this->action('index');
					return;
				}


				if ($this->request()->post('submitsave')) {
					$errors = false;

					$field->name = $this->request()->post('name');

					if (empty($field->name)) {
						$errors[] = 'You must provide a name for the custom field.';
					}

					if ($errors) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
					} else {
						$saved_ok = $this->model('customfieldstore')->update($field);

						if (!$saved_ok ) {
							$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
						} else {
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
						}
					}
				}

			}// /if(session key OK)
		}

		$this->view()->field = $field;
		$this->view()->render('customfields_edit');
	}



}// /class
?>