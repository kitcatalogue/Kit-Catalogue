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
<<<<<<< HEAD
=======

		//$this->layout()->addBreadcrumb('Browse by ', $this->router()->makeAbsoluteUri('/item/'));
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
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



<<<<<<< HEAD
	public function actionViewitem() {
		$field_id = $this->param('fieldid');
		$value = $this->param('fieldvalue');

		$item_name  = $this->param('itemname');
		$item_id = $this->param('itemid');


		$field = $this->model('customfieldstore')->find($field_id);

		$item = $this->model('itemstore')->find($item_id);


		if ( (empty($field)) || (empty($item)) ) {
			$this->router()->action('error', '404');
			return true;
		}


		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->view()->customfield = $field;
		$this->view()->value = $value;
		$this->view()->item = $item;

		$this->view()->render('customfield_viewitem');
	}// /method



=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	public function actionViewvalue() {
		$field_id = $this->param('fieldid');
		$value = $this->param('fieldvalue');

		$field = $this->model('customfieldstore')->find($field_id);

		if (empty($field)) {
			$this->router()->action('404', 'error');
			return true;
		}

<<<<<<< HEAD
=======
		$this->layout()->addBreadcrumb("Browse by {$field->name}", $this->router()->makeAbsoluteUri("/customfield/{$field->slug}"));
		$this->layout()->addBreadcrumb("{$value}", $this->router()->makeAbsoluteUri("/customfield/{$field->slug}/". urlencode($value)));

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
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