<?php
/*
 *
 */
class Controller_Ou extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		$this->router()->layout()->addBreadcrumb($this->model()->lang['ou.label.plural'], $this->router()->makeAbsoluteUri('/ou/'));
	}// /method



	public function actionIndex() {
		$this->view()->render('ou_all');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>