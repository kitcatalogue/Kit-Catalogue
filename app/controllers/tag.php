<?php
/*
 *
 */
class Controller_Tag extends Ecl_Mvc_Controller {



	public function beforeAction() {
		if ( (false == $this->model('app.allow_anonymous')) && ($this->model('user')->isAnonymous()) ) {
			$this->abort();
			$this->router()->action('index', 'signin');
			return;
		}

		$this->layout()->addBreadcrumb('Tags', $this->router()->makeAbsoluteUri('/tag/'));
	}// /method



	public function actionIndex() {
		$tags = $this->view()->tags = $this->model('itemstore')->findAllTags();

		$sort = strtolower($this->request()->get('sort', 'name'));

		if ('count' == $sort) {
			$this->view()->sort = 'count';
			$temp = $tags;
			uksort($temp, function ($a, $b) use ($tags) {
				if ($tags[$a] == $tags[$b]) { return strnatcasecmp($a, $b); }
			 	return ($tags[$a] > $tags[$b]) ? -1 : 1 ;
			});
			$tags = $temp;
		} else {
			$this->view()->sort = 'name';
			ksort($tags);
		}

		$this->view()->tags = $tags;
		$this->view()->render('tag_index');
	}// /method



	public function actionView() {
		$tag = urldecode($this->param('tag'));

		$this->view()->tag = $tag;
		$this->view()->items = $this->model('itemstore')->findForTag($tag, $this->model('user')->param('visibility'),'');
		$this->view()->render('tag_view');
	}// /method



	public function actionViewitem() {
		$tag = urldecode($this->param('tag'));

		$item = $this->model('itemstore')->find($this->param('itemid'));

		if (empty($item)) {
			$this->router()->action('404', 'error');
			return true;
		}

		if ( (KC__VISIBILITY_PUBLIC != $item->visibility) && ($this->model('user')->isAnonymous()) ) {
			$this->router()->action('404', 'error');
			return true;
		}

		$this->view()->tag = $tag;
		$this->view()->item = $item;
		$this->view()->render('tag_viewitem');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>