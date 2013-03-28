<?php
/*
 *
 */
class Controller_Facility extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		$this->layout()->addBreadcrumb($this->model('lang')->get('facility.label.plural'), $this->router()->makeAbsoluteUri('/tag/'));
	}// /method



	public function actionIndex() {
		$this->view()->items = $this->model('itemstore')->findAllParents(null, $this->model('user')->param('visibility'));
		$this->view()->render('facility_index');
	}// /method



	public function actionViewitem() {
		$item = $this->model('itemstore')->find($this->param('itemid'));

		if (empty($item)) {
			$this->router()->action('404', 'error');
			return true;
		}

		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->view()->item = $item;
		$this->view()->render('facility_viewitem');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>