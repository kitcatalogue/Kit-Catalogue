<?php
/*
 *
 */
class Controller_Item extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		$this->layout()->addBreadcrumb('Items', $this->router()->makeAbsoluteUri('/item/'));
	}// /method



	public function actionDownloadfile () {
		$item = $this->model('itemstore')->find($this->param('itemid'));

		$filename = $this->param('filename');

		if ($this->model('user')->isAnonymous()) {
			$this->router()->action('unauthorised', 'error');
			return true;
		}

		if (empty($item)) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->view()->filename = $this->model('app.upload_root').'/items'. $item->getFilePath() .'/'. $filename;
		$this->view()->render('item_downloadfile');
	}// /method



	public function actionIndex() {
		$user = $this->model('user');

		$letter = ucwords( $this->request()->get('letter', 'A') );

		$alphabet = $this->model('itemstore')->findUsedAToZ($user->param('visibility'));

		// If no letter selected, auto-select the first letter with items in
		//if ( (!in_array($letter, $alphabet)) && (count($alphabet)>0) ) {
		//	$letter = ucwords($alphabet[0]);
		//}

		$this->view()->letter = $letter;
		$this->view()->alphabet = $alphabet;

		if ('Other' == $letter) {
			$this->view()->items = $this->model('itemstore')->findForManufacturerLetter(null, $user->param('visibility'));
		} else {
			$this->view()->items = $this->model('itemstore')->findForManufacturerLetter($letter, $user->param('visibility'));
		}

		$this->layout()->addBreadcrumb("{$letter}..", $this->router()->makeAbsoluteUri("/item/?letter={$letter}"));

		$this->view()->render('item_atoz');
	}// /method



	public function actionSearch() {
		$query = $this->request()->get('q');
		$query = urldecode($query);

		$this->layout()->addBreadcrumb("Searching for \"$query\"", $this->router()->makeAbsoluteUri("/search/?q={$query}"));

		$this->view()->query = $query;

		$query = str_replace('%', '\%', $query);

		$this->view()->items = $this->model('itemstore')->searchItems($query, $this->model('user')->param('visibility'));
		$this->view()->render('item_searchresults');
	}// /method



	public function actionView() {
		$item = $this->model('itemstore')->find($this->param('itemid'));

		if (empty($item)) {
			$this->router()->action('404', 'error');
			return true;
		}

		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->layout()->addBreadcrumb("{$item->manufacturer} {$item->model}", $this->router()->makeAbsoluteUri("/item/{$item->url_suffix}"));

		$this->view()->item = $item;

		$this->view()->render('item_view');
	}// /method




/* --------------------------------------------------------------------------------
 * Private Methods
 */



	protected function _getCategoryForNameParam($param_name) {
		return $this->model('categorystore')->findForName(strtolower(urldecode($this->param($param_name))));
	}// /method



}// /class
?>