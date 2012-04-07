<?php
/*
 *
 */
class Controller_Admin_Buildings extends Ecl_Mvc_Controller {



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
		$this->router()->layout()->addBreadcrumb('Buildings', $this->router()->makeAbsoluteUri('/admin/buildings/index/'));
		$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	/**
	 * Create a new building.
	 */
	public function actionCreate() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->request()->isPost()) {
			$errors = false;

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$building_name = $this->request()->post('name');
			if (empty($building_name)) {
				$errors[] = 'You must provide the name of your new building.';
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$new_building = $this->model('buildingstore')->newBuilding();
				$new_building->code = $this->request()->post('code');
				$new_building->name = $building_name;
				$new_building->site_id = $this->request()->post('site_id');
				$new_id = $this->model('buildingstore')->insert($new_building);

				if ($new_id) {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The building '{$new_building->name} has been added");
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an unspecified error adding your new building.');
				}
			}
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Unable to create building.  No information posted.');
		}

		$this->action('index');
	}// /method



	/**
	 * Edit an existing building.
	 */
	public function actionEdit() {

		$building = $this->model('buildingstore')->find($this->param('id'));

		if (empty($building)) {
			$this->router()->action('404', 'error');
			return;
		}


		if ($this->request()->isPost()) {

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The form details supplied appear to be forged.');
			} else {


				if ($this->request()->post('submitdelete')) {
					$this->model('buildingstore')->delete($building->id);
					$this->layout()->clearBreadcrumbs(2);
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The building has been deleted');
					$this->action('index');
					return;
				}



				if ($this->request()->post('submittransfer')) {
					$target_building = $this->model('buildingstore')->find($this->request()->post('destination'));
					if (empty($target_building)) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The items could not be transferred.', 'The destination building selected could not be found.');
					} else {
						$this->model('itemstore')->transferBuildingItems($building->id, $target_building->id);

						if (1 == $this->request()->post('delete_on_transfer')) {
							$this->model('buildingstore')->delete($building->id);
							$this->layout()->clearBreadcrumbs(2);
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "All items have been transfered and the original building deleted.");
							$this->action('index');
							return;
						} else {
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'All items have been transfered.');
						}
					}
				}



				if ($this->request()->post('submitsave')) {
					$errors = false;

					$building->name = $this->request()->post('name');
					if (empty($building->name)) {
						$errors[] = 'You must provide a name for the building.';
					}

					if ($errors) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
					} else {
						$saved_ok = $this->model('buildingstore')->update($building);

						if (!$saved_ok ) {
							$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
						} else {
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
						}
					}
				}


			}// /if(session key OK)

		}

		$this->view()->building = $building;
		$this->view()->render('buildings_edit');
	}// /method



	/**
	 *
	 */
	public function actionIndex() {
		$this->view()->buildings = $this->model('buildingstore')->findAll();
		$this->view()->render('buildings_index');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>