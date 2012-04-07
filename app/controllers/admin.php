<?php
/*
 * The Administration menu controller.
 *
 * Check in modules/admin for the administrative controllers, etc.
 */
class Controller_Admin extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->abort();
			$this->router()->action('404', 'error');
			return false;
		}

		$this->router()->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/admin/'));
		$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	public function actionBuildings() {
		$this->response()->setRedirect($this->router()->makeAbsoluteUri('/admin/buildings/index/'));
	}



	public function actionCategories() {
		$this->response()->setRedirect($this->router()->makeAbsoluteUri('/admin/categories/index/'));
	}



	public function actionDepartments() {
		$this->response()->setRedirect($this->router()->makeAbsoluteUri('/admin/departments/index/'));
	}



	public function actionIndex() {
		$this->view()->render('admin_index');
	}// /method



	public function actionItems() {
		$this->response()->setRedirect($this->router()->makeAbsoluteUri('/admin/items/index/'));
	}



	public function actionReports() {
		$this->response()->setRedirect($this->router()->makeAbsoluteUri('/admin/reports/index/'));
	}



	public function actionSites() {
		$this->response()->setRedirect($this->router()->makeAbsoluteUri('/admin/sites/index/'));
	}



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>