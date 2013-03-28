<?php
/*
 * The Administration homepage controller.
 */
class Controller_Admin_Home extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->abort();
			$this->router()->action('404', 'error');
			return false;
		}

<<<<<<< HEAD
		$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));

=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
		$this->router()->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/admin/'));
	}



	public function actionIndex() {
		$this->view()->render('home_index');
	}



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>