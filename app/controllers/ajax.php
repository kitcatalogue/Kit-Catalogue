<?php
/*
 * Actions methods need not send the reply object,
 * as it will be sent automatically in ->afterAction()
 */
class Controller_Ajax extends Ecl_Mvc_Controller {

	// Public Properties
	public $reply = null;



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	public function beforeAction() {
		$this->router()->layout(null);

		$this->response()->setHeader('Content-Type', 'application/json');

		$this->reply = Ecl::factory('Ecl_Ajax_Reply');
		$this->reply->setupFromRequest($this->request());
	}// /method



	public function afterAction() {
		echo($this->reply->toJson());
	}// /method



	public function actionIndex() {
		$this->reply->setOk();
		$this->reply->setData('AJAX API entry point.');
	}// /method



	public function actionCpvmatch() {
		$this->reply->setOk();

		$query = $this->request()->get('q');

		$matches = $this->model('cpvstore')->findMatches($query);
		if (count($matches)>0) {
			$this->reply->setData('matches', $matches->toCustomArray( function ($row) {
				$x = new stdClass();
				$x->id = $row->id;
				$x->name = $row->name;
				return $x;
			}));
		}
	}// /method



	public function actionFindou() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->reply->setFail('Access denied');
			return;
		}

		$ou_id = $this->request()->get('id');

		$ou = $this->model('organisationalunitstore')->find($ou_id);
		if (!$ou) {
			$this->reply->setFail('Unknown OU requested.');
			return;
		} else {
			if (0 == $ou->tree_level) {
				$this->reply->setFail('Access denied to root OU.');
			} else {
				$this->reply->setData('ou', $ou);
			}
		}
	}// /method
    public function actionGetNameSuggestions() {
        // TODO: Change to Regular user?
        if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->reply->setFail('Access denied');
			return;
		}

		$query = $this->request()->get('q');

		$results = $this->model('userstore')->findPartialMatch($query);
        $this->reply->SetData('result', $results);
	}// /method



}// /class
?>
