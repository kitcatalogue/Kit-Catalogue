<?php
/*
 *
 */
class Controller_Myprofile extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		$this->layout()->addBreadcrumb('My Profile', null);
	}// /method



	public function actionIndex() {
		$this->actionItems();
	}// /method



	public function actionItems() {
		$user = $this->model('user');

		$this->view()->items = $this->model('itemstore')->findForContact($user->email);
		$this->view()->render('myprofile_items');
	}// /method



	public function actionViewitem() {
		$item_name  = $this->param('itemname');
		$item_id = $this->param('itemid');

		$item = $this->model('itemstore')->find($item_id);


		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}


		$this->view()->item = $item;
		$this->view()->render('myprofile_viewitem');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>