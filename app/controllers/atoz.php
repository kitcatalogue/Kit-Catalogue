<?php
/*
 *
 */
class Controller_Atoz extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		$this->layout()->addBreadcrumb('Manufacturer A-Z', $this->router()->makeAbsoluteUri('/a-z/'));
	}// /method



	public function actionIndex() {
		$user = $this->model('user');

		$letter = ucwords( $this->request()->get('letter', 'A') );

		$alphabet = $this->model('itemstore')->findUsedAToZ($user->param('visibility'));
		// If no letter selected, auto-select the first letter with items in
		if ( (!in_array($letter, $alphabet)) && (count($alphabet)>0) ) {
			$letter = ucwords($alphabet[0]);
		}

		$this->view()->letter = $letter;
		$this->view()->alphabet = $alphabet;


		if ('Other' == $letter) {
			$this->view()->items = $this->model('itemstore')->findForManufacturerLetter(null, $user->param('visibility'));
		} else {
			$this->view()->items = $this->model('itemstore')->findForManufacturerLetter($letter, $user->param('visibility'));
		}


		$this->view()->render('atoz_index');
	}// /method



	public function actionViewitem() {
		$user = $this->model('user');

		$item = $this->model('itemstore')->find($this->param('itemid'));

		$letter = ucwords( $this->request()->get('letter', 'A') );

		$alphabet = $this->model('itemstore')->findUsedAToZ($user->param('visibility'));
		// If no letter selected, auto-select the first letter with items in
		if ( (!in_array($letter, $alphabet)) && (count($alphabet)>0) ) {
			$letter = ucwords($alphabet[0]);
		}

		$this->view()->letter = $letter;

		if (empty($item)) {
			$this->router()->action('404', 'error');
			return true;
		}

		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->view()->item = $item;

		$this->view()->render('atoz_viewitem');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>