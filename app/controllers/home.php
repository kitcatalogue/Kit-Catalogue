<?php
/*
 *
 */
class Controller_Home extends Ecl_Mvc_Controller {



	public function actionIndex() {
		if ( ($this->model('app.allow_anonymous')===true) || (!$this->model('user')->isAnonymous()) ) {
			$this->view()->render('home_index');
		} else {
			$this->router()->action('index', 'signin');
		}
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */







}// /class
?>