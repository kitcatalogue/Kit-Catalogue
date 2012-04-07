<?php
/*
 *
 */
class Controller_Support_Help extends Ecl_Mvc_Controller {



	public function beforeAction() {
		$this->router()->layout()->addBreadcrumb('Support', $this->router()->makeAbsoluteUri('/support/'));
	}// /method



	public function actionView() {
		$help_id = $this->param('id');
		$this->router()->layout()->addBreadcrumb('Help', $this->router()->makeAbsoluteUri('/support/help/'));
		$this->router()->layout()->addBreadcrumb($help_id, $this->router()->makeAbsoluteUri('/support/help/view/'.urlencode($help_id)));
		$this->_showHelpPage($help_id);
	}// /method



	public function actionIndex() {
		$this->view()->render('help_index');
	}// /method



	public function actionPopup() {
		$this->_showHelpPage($this->param('id'));
		$this->router()->layout(null);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	protected function _showHelpPage($help_id) {
		$help_id = strtolower($help_id);
		if (empty($help_id)) {
			$this->view()->content = '';
			$this->view()->render('support_help');
			return;
		}

		switch ($help_id) {
			case 'wikitext' :
				$this->view()->render('help_wikitext');
				break;
			default:
				$this->view()->content = '';
				$this->view()->render('help_index');
				break;
		}
	}// /method



}// /class
?>