<?php
/*
 *
 */
class Controller_Id extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}
	}// /method



	public function actionIndex() {
		$user = $this->model('user');

		$this->view()->categories = $this->model('categorystore')->findAllUsed($user->param('visibility'));

		$this->view()->render('category_all');
	}// /method



	public function actionCategory() {
		$user = $this->model('user');

		$category = $this->model('categorystore')->find($this->param('id'));

		if (empty($category)) {
			$this->router()->action('error', '404');
			return true;
		}

		$this->layout()->addBreadcrumb('Categories', $this->router()->makeAbsoluteUri('/category/'));
		$this->layout()->addBreadcrumb($category->name, $this->router()->makeAbsoluteUri("category/{$category->url_suffix}"));

		$this->view()->category = $category;
		$this->view()->items = $this->model('itemstore')->findForCategory($category->id, $user->param('visibility'));

		$this->view()->render('category_view');
	}// /method



	public function actionDepartment() {
		$user = $this->model('user');

		$department  = $this->model('departmentstore')->find($this->param('id'));

		if (empty($department)) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->layout()->addBreadcrumb('Departments', $this->router()->makeAbsoluteUri("/department/"));
		$this->layout()->addBreadcrumb($department->name, $this->router()->makeAbsoluteUri("/department/{$department->url_suffix}"));

		$this->view()->department = $department;
		$this->view()->category = null;
		$this->view()->categories = $this->model('categorystore')->findForDepartment($department->id, $user->param('visibility'));
		$this->view()->items = $this->model('itemstore')->findForDepartmentCategory($department->id, null, $user->param('visibility'));
		$this->view()->render('department_view');
	}// /method



	public function actionItem() {
		$item = $this->model('itemstore')->find($this->param('id'));

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



}// /class
?>