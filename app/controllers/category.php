<?php
/*
 *
 */
class Controller_Category extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {

			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		$this->router()->layout()->addBreadcrumb($this->model()->lang['cat.label.plural'], $this->router()->makeAbsoluteUri('/category/'));
	}// /method



	public function actionIndex() {
		$user = $this->model('user');

		$this->view()->categories = $this->model('categorystore')->findAllUsed($user->param('visibility'));

		$this->view()->render('category_all');
	}// /method



	public function actionView() {
		$user = $this->model('user');

		$category = $this->model('categorystore')->find($this->param('catid'));

		if (empty($category)) {
			$this->router()->action('error', '404');
			return true;
		}

		$this->router()->layout()->addBreadcrumb($category->name, $this->router()->makeAbsoluteUri("/category/{$category->url_suffix}"));

		$this->view()->category = $category;
		$this->view()->items = $this->model('itemstore')->findForCategory($category->id, $user->param('visibility'));

		$this->view()->render('category_view');
	}// /method



	public function actionViewitem() {
		$item_name  = $this->param('itemname');
		$item_id = $this->param('itemid');

		$category = $this->model('categorystore')->find($this->param('catid'));

		$item = $this->model('itemstore')->find($item_id);


		if ( (empty($category)) || (empty($item)) ) {
			$this->router()->action('error', '404');
			return true;
		}


		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}


		$url_category = urlencode(strtolower($category->name));
		$this->router()->layout()->addBreadcrumb($category->name, $this->router()->makeAbsoluteUri("/category/{$category->url_suffix}"));
		$this->router()->layout()->addBreadcrumb($item->manufacturer.' '.$item->model, $this->router()->makeAbsoluteUri("/category/{$category->url_suffix}/item/{$item->url_suffix}"));


		$this->view()->category = $category;
		$this->view()->item = $item;


		$this->view()->render('category_viewitem');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>