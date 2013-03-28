<?php
/*
 *
 */
class Controller_Admin_Departments extends Ecl_Mvc_Controller {



	/**
	 * Before action method.
	 */
	public function beforeAction() {
<<<<<<< HEAD

		// Department admin disabled
		$this->abort();
		$this->router()->action('404', 'error');
		return false;



=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->abort();
			$this->router()->action('unauthorised', 'error');
			return false;
		}

		$this->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/admin/'));
		$this->layout()->addBreadcrumb($this->model()->lang['dept.label.plural'], $this->router()->makeAbsoluteUri('/admin/departments/index/'));
		$this->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	/**
	 * Create a new department.
	 */
	public function actionCreate() {



		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->request()->isPost()) {
			$errors = false;

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$dept_name = $this->request()->post('new_dept');
			if (empty($dept_name)) {
				$errors[] = 'You must provide the name of your new '. strtolower($this->model()->lang['dept.label'].'.');
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$new_dept = $this->model('departmentstore')->newDepartment();
				$new_dept->name = $dept_name;
				$new_id = $this->model('departmentstore')->insert($new_dept);

				if ($new_id) {
<<<<<<< HEAD
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The '. strtolower($this->model()->lang['dept.label']) ." '{$new_dept->name}' has been added");
=======
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The '. strtolower($this->model()->lang['dept.label']) ." '{$new_dept->name} has been added");
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an unspecified error adding your new '. strtolower($this->model()->lang['dept.label']).'.');
				}
			}
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Unable to create the new '. strtolower($this->model()->lang['dept.label']) .'.  No information posted.');
		}

		$this->action('index');
	}// /method



	/**
	 * Edit an existing department.
	 */
	public function actionEdit() {

		$dept = $this->model('departmentstore')->find($this->param('id'));

		if (empty($dept)) {
			$this->router()->action('404', 'error');
			return;
		}


		if ($this->request()->isPost()) {


			if ($this->request()->post('submitdelete')) {
				$this->model('departmentstore')->delete($dept->id);
				$this->layout()->clearBreadcrumbs(2);
				$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The '. strtolower($this->model()->lang['dept.label']) .' has been deleted');
				$this->action('index');
				return;
			}


			if ($this->request()->post('submittransfer')) {

				$target_dept = $this->model('departmentstore')->find($this->request()->post('destination'));
				if (empty($target_dept)) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The items could not be transferred.', 'The destination'. strtolower($this->model()->lang['dept.label']) .' selected could not be found.');
				} else {
					$this->model('itemstore')->transferDepartmentItems($dept->id, $target_dept->id);
					$this->model('departmentstore')->rebuildItemCounts();

					if (1 == $this->request()->post('delete_on_transfer')) {
						$this->model('departmentstore')->delete($dept->id);
						$this->layout()->clearBreadcrumbs(2);
						$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "All items have been transferred and the original department deleted.");
						$this->action('index');
						return;
					} else {
						$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'All items have been transfered.');
						// Reload current department (to get the newly reset item counts)
						$dept = $this->model('departmentstore')->find($dept->id);
					}
				}
			}


			if ($this->request()->post('submitsave')) {
				$errors = false;

				if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
					$errors[] = 'The form details supplied appear to be forged.';
				}

				$dept->name = $this->request()->post('name');
				if (empty($dept->name)) {
					$errors[] = 'You must provide a name for the '. strtolower($this->model()->lang['dept.label']) .'.';
				}

				if ($errors) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
				} else {
					$saved_ok = $this->model('departmentstore')->update($dept);

					if (!$saved_ok ) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
					} else {
						$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
					}
				}
			}
		}

		$this->view()->dept = $dept;
		$this->view()->render('departments_edit');
	}// /method



	/**
	 *
	 */
	public function actionIndex() {
		$this->view()->depts = $this->model('departmentstore')->findAll();
		$this->view()->render('departments_index');
	}// /method



	/**
	 * Rebuild the item counts.
	 */
	public function actionRebuildcounts() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->model('departmentstore')->rebuildItemCounts()) {
			$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The '. strtolower($this->model()->lang['dept.label']) .' item counts have been rebuilt.');
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an error rebuilding the '. strtolower($this->model()->lang['dept.label']) .' item counts.');
		}

		$this->action('index');
		return;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>