<?php
/*
 *
 */
class Controller_Customfield extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		//$this->layout()->addBreadcrumb('Browse by ', $this->router()->makeAbsoluteUri('/item/'));
	}// /method



	public function actionIndex() {
		$this->router()->action('404', 'error');
		return true;
	}// /method



	public function actionListvalues() {
		$field_id = $this->param('fieldid');

		$field = $this->model('customfieldstore')->find($field_id);

		if (empty($field)) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->layout()->addBreadcrumb("Browse by {$field->name}", $this->router()->makeAbsoluteUri("/customfield/{$field->slug}"));

		$this->view()->customfield = $field;
		$this->view()->values = $this->model('customfieldstore')->findUsedCustomFieldValues($field->id, 0, $this->model('user')->param('visibility'));

		$this->view()->render('customfield_listvalues');
	}// /method



	public function actionViewvalue() {
		$field_id = $this->param('fieldid');
		$value = $this->param('fieldvalue');

		$field = $this->model('customfieldstore')->find($field_id);

		if (empty($field)) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->layout()->addBreadcrumb("Browse by {$field->name}", $this->router()->makeAbsoluteUri("/customfield/{$field->slug}"));
		$this->layout()->addBreadcrumb("{$value}", $this->router()->makeAbsoluteUri("/customfield/{$field->slug}/". urlencode($value)));

		$this->view()->customfield = $field;
		$this->view()->value = $value;
		$this->view()->items = $this->model('itemstore')->findForCustomFieldValue($field->id, $value, $this->model('user')->param('visibility'));

		$this->view()->render('customfield_viewvalue');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class



?>