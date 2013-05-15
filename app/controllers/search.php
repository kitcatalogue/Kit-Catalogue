<?php
/*
 *
 */
class Controller_Search extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

	}// /method



	public function actionResults() {
		$query = $this->request()->get('q');
		$query = urldecode($query);

		if ('Search...' == $query) {
			$query = '';
		}

		$this->view()->query = $query;

		$query = str_replace('%', '\%', $query);

		$search_options = array(
			'prioritise_facilities' => ($this->model('search.prioritise_facilities')),
		);
		$this->view()->items = $this->model('itemstore')->searchItems($query, $this->model('user')->param('visibility'), $search_options);
		$this->view()->render('search_results');
	}// /method



	public function actionViewitem() {
		$item_name  = $this->param('itemname');
		$item_id = $this->param('itemid');

		$item = $this->model('itemstore')->find($item_id);


		if (empty($item)) {
			$this->router()->action('error', '404');
			return true;
		}


		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}

		$query = $this->request()->get('q');
		$query = urldecode($query);

		if ('Search...' == $query) {
			$query = '';
		}

		$this->view()->query = $query;
		$this->view()->item = $item;

		$this->view()->render('search_viewitem');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>