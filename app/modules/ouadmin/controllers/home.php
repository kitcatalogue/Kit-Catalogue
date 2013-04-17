<?php
/*
 * The OU-Administration homepage controller.
 */
class Controller_Ouadmin_Home extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANOUADMIN)) {
			$this->abort();
			$this->router()->action('404', 'error');
			return false;
		}

		$this->router()->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/ouadmin/'));
	}



	public function actionIndex() {
		$this->view()->render('home_index');
	}



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>