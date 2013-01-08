<?php
/*
 *
 */
class Controller_Browse extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

	}// /method



	public function actionIndex() {
		$this->response()->redirectTo($this->router()->makeAbsoluteUri('/'));
	}// /method



	public function actionView() {

		$user = $this->model('user');
		$lang = $this->model('lang');

		$selected_params = array();
		$uri_params = array();
		$main_param = null;

		$this->_fillParamVariables($selected_params, $uri_params, $main_param);

		$this->view()->main_param = $main_param;
		$this->view()->selected_params = $selected_params;
		$this->View()->uri_params = $uri_params;
		$this->view()->items = $this->model('itemstore')->findForSearchParams($selected_params, $user->param('visibility'));
		$this->view()->render('browse_view');
	}// /method



	public function actionViewitem() {
		$item_name  = $this->param('itemname');
		$item_id = $this->param('itemid');

		$item = $this->model('itemstore')->find($item_id);

		if (empty($item)) {
			$this->router()->action('error', '404');
			return true;
		}


		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}

		$selected_params = array();
		$search_params = array();
		$uri_params = array();
		$main_param = null;

		$this->_fillParamVariables($selected_params, $uri_params, $main_param);

		$this->view()->main_param = $main_param;
		$this->view()->selected_params = $selected_params;
		$this->View()->uri_params = $uri_params;
		$this->view()->item = $item;
		$this->view()->render('browse_viewitem');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	protected function _fillParamVariables(&$selected_params, &$uri_params, &$main_param) {
		$lang = $this->model('lang');

		for($i=1; $i<=4; $i++) {
			$param = $this->param("param{$i}");
			if (!empty($param)) {
				$bits = explode('-', $param, 3);
				$bits_count = count($bits);

				if (2 <= $bits_count) {
					$param_key = strtolower($bits[0]);

					switch ($param_key) {
						case strtolower($lang['cat.label']):
							$uri_params['category'] = urlencode($param);
							$selected_params['category'] = (int) $bits[1];
							break;
						case strtolower($lang['dept.label']):
							$uri_params['department'] = urlencode($param);
							$selected_params['department'] = (int) $bits[1];
							break;
						case strtolower($lang['item.form.manufacturer']):
							$uri_params['manufacturer'] = urlencode($param);
							unset($bits[0]);
							$selected_params['manufacturer'] = implode('-', $bits);
							break;
						case strtolower($lang['item.label.technique']):
							$uri_params['technique'] = urlencode($param);
							unset($bits[0]);
							$selected_params['technique'] = implode('-', $bits);
							break;
						default:
							break;
					}

					if (1 == $i) {
						$main_param = $param_key;
					}
				}
			}
		}// /foreach
	}



}// /class
?>