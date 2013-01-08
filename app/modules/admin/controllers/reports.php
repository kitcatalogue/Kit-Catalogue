<?php
/*
 *
 */
class Controller_Admin_Reports extends Ecl_Mvc_Controller {



	/**
	 * Before action method.
	 */
	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->abort();
			$this->router()->action('unauthorised', 'error');
			return false;
		}

		$this->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/admin/'));
		$this->layout()->addBreadcrumb('Reports', $this->router()->makeAbsoluteUri('/admin/reports/index/'));
		$this->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	public function actionIndex() {
		$this->view()->render('reports_index');
	}// /method



	public function actionItems() {
		$this->layout()->addBreadcrumb('Items', $this->router()->makeAbsoluteUri('/admin/reports/items/'));

		$report_id = $this->param('id', 'index');

		if ('index' == $report_id) {
			$this->view()->render('reports_items_index');
			return;
		}

		$is_download = ($this->request()->get('downloadreport', false));
		if ($is_download) { $this->router()->layout(null); }

		$this->view()->is_download = $is_download;
		$this->view()->render("reports_items_{$report_id}");
	}// /method



	public function actionStaffcontacts() {
		$this->layout()->addBreadcrumb('Staff Contacts', $this->router()->makeAbsoluteUri('/admin/reports/staffcontacts/'));

		$report_id = $this->param('id', 'index');

		if ('index' == $report_id) {
			$this->view()->render('reports_staffcontacts_index');
			return;
		}

		$is_download = ($this->request()->get('downloadreport', false));
		if ($is_download) { $this->router()->layout(null); }

		$this->view()->is_download = $is_download;
		$this->view()->render("reports_staffcontacts_{$report_id}");
	}// /method



	public function actionManufacturers() {
		$this->layout()->addBreadcrumb('Manufacturers', $this->router()->makeAbsoluteUri('/admin/reports/manufacturers/'));

		$report_id = $this->param('id', 'index');

		if ('index' == $report_id) {
			$this->view()->render('reports_manufacturers_index');
			return;
		}

		$is_download = ($this->request()->get('downloadreport', false));
		if ($is_download) { $this->router()->layout(null); }

		$this->view()->is_download = $is_download;
		$this->view()->render("reports_manufacturers_{$report_id}");
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>