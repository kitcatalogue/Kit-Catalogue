<?php
/*
 *
 */
class Controller_Building extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {

			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		$this->router()->layout()->addBreadcrumb($this->model()->lang['building.label.plural'], $this->router()->makeAbsoluteUri('/'. $this->model()->lang['building.route'].'/'));
	}// /method



	public function actionIndex() {
		$user = $this->model('user');

		$this->view()->buildings = $this->model('buildingstore')->findAllUsed($user->param('visibility'));

		$this->view()->render('building_all');
	}// /method



	public function actionView() {
		$user = $this->model('user');

		$building = $this->model('buildingstore')->find($this->param('catid')); // :catid should, confusingly be correct

		if (empty($building)) {
			$this->router()->action('error', '404');
			return true;
		}

		$this->view()->building = $building;
		$this->view()->items = $this->model('itemstore')->findForCategory($building->id, $user->param('visibility'));

		$this->view()->render('building_view');
	}// /method



	public function actionViewitem() {
		$item_name  = $this->param('itemname');
		$item_id = $this->param('itemid');

		$building = $this->model('buildingstore')->find($this->param('catid'));

		$item = $this->model('itemstore')->find($item_id);


		if ( (empty($building)) || (empty($item)) ) {
			$this->router()->action('error', '404');
			return true;
		}


		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->view()->building = $building;
		$this->view()->item = $item;

		$this->view()->render('building_viewitem');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
