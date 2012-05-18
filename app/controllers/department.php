<?php
/*
 *
 */
class Controller_Department extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		$this->router()->layout()->addBreadcrumb($this->model()->lang['dept.label.plural'], $this->router()->makeAbsoluteUri("/{$this->model()->lang['dept.route']}/"));
	}// /method



	public function actionIndex() {
		$user = $this->model('user');
		$this->view()->departments = $this->model('departmentstore')->findAllUsed($user->param('visibility'));
		$this->view()->render('department_all');
	}// /method



	public function actionView() {

		$user = $this->model('user');

		$department  = $this->model('departmentstore')->find($this->param('deptid'));

		if (empty($department)) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->router()->layout()->addBreadcrumb($department->name, $this->router()->makeAbsoluteUri("/{$this->model()->lang['dept.route']}/{$department->url_suffix}"));


		$category_id = $this->param('catid', null);
		if (!$category_id) {
			$category = null;
			$category_id = null;
			$this->router()->layout()->addBreadcrumb('All Equipment', $this->router()->makeAbsoluteUri("/{$this->model()->lang['dept.route']}/{$department->url_suffix}"));
		} else {
			$category = $this->model('categorystore')->find($category_id);
			if ($category) {
				$category_id = $category->id;
				$this->router()->layout()->addBreadcrumb($category->name, $this->router()->makeAbsoluteUri("/{$this->model()->lang['dept.route']}/{$department->url_suffix}/category/{$category->url_suffix}"));
			}
		}

		$this->view()->department = $department;
		$this->view()->category = $category;
		$this->view()->categories = $this->model('categorystore')->findForDepartment($department->id, $user->param('visibility'));
		$this->view()->items = $this->model('itemstore')->findForDepartmentCategory($department->id, $category_id, $user->param('visibility'));
		$this->view()->render('department_view');
	}// /method



	public function actionViewitem() {
		$item_name  = $this->param('itemname');
		$item_id = $this->param('itemid');

		$department = $this->model('departmentstore')->find($this->param('deptid'));
		$item = $this->model('itemstore')->find($item_id);

		if ( (empty($department)) || (empty($item)) ) {
			$this->router()->action('error', '404');
			return true;
		}


		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}


		$url_department = urlencode(strtolower($department->name));
		$this->router()->layout()->addBreadcrumb($department->name, $this->router()->makeAbsoluteUri("/{$this->model()->lang['dept.route']}/{$department->url_suffix}"));
		$this->router()->layout()->addBreadcrumb($item->manufacturer.' '.$item->model, $this->router()->makeAbsoluteUri("/{$this->model()->lang['dept.route']}/{$department->url_suffix}/item/{$item->url_suffix}"));

		$this->view()->department = $department;
		$this->view()->item = $item;

		$this->view()->render('department_viewitem');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>