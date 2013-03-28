<?php
/*
 *
 */
class Controller_Ouadmin_Reports extends Ecl_Mvc_Controller {



	/**
	 * Before action method.
	 */
	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANOUADMIN)) {
			$this->abort();
			$this->router()->action('unauthorised', 'error');
			return false;
		}

		$this->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/ouadmin/'));
		$this->layout()->addBreadcrumb('Reporting', $this->router()->makeAbsoluteUri('/ouadmin/reports/index/'));
		$this->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	public function actionIndex() {
		$this->view()->render('reports_index');
	}// /method



	public function actionItems() {
		$lang = $this->model('lang');


		$this->layout()->addBreadcrumb('Items', $this->router()->makeAbsoluteUri('/ouadmin/reports/items/'));


		$this->view()->available_output_fields = array (
			'id'                => 'ID' ,
			'name'              => 'Name' ,
			'title'             => $lang['item.form.title'] ,
			'manufacturer'      => $lang['item.form.manufacturer'] ,
			'model'             => $lang['item.form.model'] ,
			'short_description' => $lang['item.form.short_description'] ,
			'full_description'  => $lang['item.form.full_description'] ,
			'specification'     => $lang['item.form.specification'] ,
			'upgrades'          => $lang['item.form.upgrades'] ,
			'future_upgrades'   => $lang['item.form.future_upgrades'] ,
			'acronym'           => $lang['item.form.acronym'] ,
			'keywords'          => $lang['item.form.keywords'] ,
			'tags'              => $lang['tag.label.plural'] ,
			'category'          => $lang['cat.label.plural'] ,
			'technique'         => $lang['item.form.technique'] ,
			'usergroup'         => $lang['item.form.usergroup'] ,
			'access'            => $lang['access.label'] ,
			'portability'       => $lang['item.form.portability'] ,
			'availability'      => $lang['item.form.availability'] ,
			'visibility'        => $lang['item.form.visibility'] ,
			'ou'                => $lang['ou.label'] ,
			'site'              => $lang['site.label'] ,
			'building'          => $lang['building.label'] ,
			'room'              => $lang['item.form.room'] ,
			'contact_1_name'    => $lang['item.form.contact_1_name'] ,
			'contact_1_email'   => $lang['item.form.contact_1_email'] ,
			'contact_2_name'    => $lang['item.form.contact_2_name'] ,
			'contact_2_email'   => $lang['item.form.contact_2_email'] ,
			'image'             => $lang['item.form.image'] ,
			'quantity'          => $lang['item.form.quantity'] ,
			'quantity_detail'   => $lang['item.form.quantity_detail'] ,
			'PAT'               => $lang['item.form.PAT'] ,
			'calibrated'        => $lang['item.form.calibrated'] ,
			'last_calibration_date' => $lang['item.form.last_calibration_date'] ,
			'next_calibration_date' => $lang['item.form.next_calibration_date'] ,
			'asset_no'          => $lang['item.form.asset_no'] ,
			'finance_id'        => $lang['item.form.finance_id'] ,
			'serial_no'         => $lang['item.form.serial_no'] ,
			'year_of_manufacture' => $lang['item.form.year_of_manufacture'] ,
			'supplier'          => $lang['item.form.supplier'] ,
			'date_of_purchase'  => $lang['item.form.date_of_purchase'] ,
			'cost'              => $lang['item.form.cost'] ,
			'replacement_cost'  => $lang['item.form.replacement_cost'] ,
			'end_of_life'       => $lang['item.form.end_of_life'] ,
			'maintenance'       => $lang['item.form.maintenance'] ,
			'is_disposed_of'    => $lang['item.form.is_disposed_of'] ,
			'date_disposed_of'  => $lang['item.form.date_disposed_of'] ,
			'date_added'        => $lang['item.form.date_added'] ,
			'date_updated'      => $lang['item.form.date_updated'] ,
			//'archived'        => $lang['item.form.archived'] ,
			//'date_archived'   => $lang['item.form.date_archived'] ,
			'last_updated_email'  => 'Last Updated By' ,
		);

		$orderby_fields = array (
			'name'                 => 'Name (title/manufacturer)' ,
			'title'                => $lang['item.form.title'] ,
			'manufacturer'         => $lang['item.form.manufacturer'] ,
			'ou'                   => $lang['ou.label'] ,
			'building'             => $lang['building.label'] ,
			'room'                 => $lang['item.form.room'] ,
			'supplier'             => $lang['item.form.supplier'] ,
			'asset_no'             => $lang['item.form.asset_no'] ,
			'finance_id'           => $lang['item.form.finance_id'] ,
			'cost'                 => $lang['item.form.cost'] ,
			'end_of_life'          => $lang['item.form.end_of_life'] ,
			'year_of_manufacture'  => $lang['item.form.year_of_manufacture'] ,
			'PAT'                  => $lang['item.form.PAT'] ,
		);


		// If GET, show the report form
		if ($this->request()->isGet()) {
			$this->view()->orderby_fields = $orderby_fields;
			$this->view()->render('reports_items_index');
			return;
		}


		$report_id = 'show';


		// Process filters
		$params = array();

		$valid_filter_fields = array (
			'ou_id', 'manufacturer', 'category', 'contact', 'building_id', 'visibility',
		);

		foreach($valid_filter_fields as $k) {
			$k = strtolower($k);
			$filter_value = $this->request()->post($k);
			if (!empty($filter_value)) {
				if ('ou_id' == $k) {
					$filter_value = $this->model('ou_tree')->findSubRefsForRef($filter_value);
				}
				$params[$k] = $filter_value;
			}
		}


		// Process order by
		$order_by = array();

		$temp_orderby = $this->request()->post('orderby');
		$temp_orderdirections = $this->request()->post("orderdirection");
		foreach($temp_orderby as $i => $k) {
			if (array_key_exists($k, $orderby_fields)) {
				$order_by[$k] = strtoupper($temp_orderdirections[$i]);
			}
		}

		$this->view()->is_download = ($this->request()->post('submitdownload', false));
		$this->view()->items = $this->model('itemstore')->findForSearchParams($params, $order_by);
		$this->view()->output_fields = (array) $this->request()->post('output');
		$this->view()->render("reports_items_{$report_id}");
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>