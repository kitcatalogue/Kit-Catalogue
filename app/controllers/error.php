<?php
/*
 *
 */
class Controller_Error extends Ecl_Mvc_Controller {



	public function beforeAction() {
		$this->response()->clear();
		$this->layout()->addBreadcrumb('Error');
	}// /method



	public function actionIndex() {
		$this->view()->error_title = 'A site error occurred and the selected resource could not be displayed.';
		$this->view()->error_desc = 'Sorry, but this is likely an internal system problem, and not something that will fix itself.';
		$this->view()->error_whatnext = array (
			'Go back to the previous page and trying a different link.' ,
		);

		$this->view()->render('error_view');
	}// /method



	public function action404() {
		$this->response()->setHttpResponseCode(404);
		$this->view()->render('error_404');
	}// /method



	public function actionException() {
		$this->view()->error_title = 'There was an exception error while processing your request.';
		$e = $this->router()->param('exception');
		if ($e) {
			$this->view()->error_desc = $e->getMessage();
		} else {
			$this->view()->error_desc = 'Unfortunately, the reason for the error is not clear at this time';
		}

		$this->view()->error_desc .= ' Current module \''. $this->router()->getCurrentModule() .'\'.';

		$this->view()->error_whatnext = array (
			'Go back to the previous page and trying a different link.' ,
		);

		$this->view()->render('error_view');
	}// /method



	public function actionUnauthorised() {
		$this->response()->setHttpResponseCode(404);
		$this->view()->render('error_unauthorised');
	}// /method



}// /class
?>