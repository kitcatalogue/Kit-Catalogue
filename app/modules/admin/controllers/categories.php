<?php
/*
 *
 */
class Controller_Admin_Categories extends Ecl_Mvc_Controller {



	/**
	 * Before action method.
	 */
	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->abort();
			$this->router()->action('unauthorised', 'error');
			return false;
		}

		$this->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/admin/'));
		$this->layout()->addBreadcrumb('Categories', $this->router()->makeAbsoluteUri('/admin/categories/index/'));
		$this->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	/**
	 * Create a new category.
	 */
	public function actionCreate() {
		if (!$this->request()->isPost()) {
			$this->router()->action('index', 'categories', 'admin');
			return false;
		}


		if ($this->request()->post('submitcancel')) {
			$this->layout()->clear();
			$this->action('index');
			return true;
		}


		$step = $this->request()->get('step', 1);

		$category = $this->model('categorystore')->newCategory();
		$category->name = $this->request()->post('name');

		switch ($step) {
			case 1:  // Show the category tagging form
				if (empty($category->name)) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'You must enter a name for your category');
				}

				$this->view()->category = $category;
				$this->view()->render('categories_create');
				break;
			case 2 :  // Validate the form
				$cpv_codes = $this->request()->post('cpv_code');

				$this->view()->category = $category;
				$this->view()->cpv_codes  = $cpv_codes;

				if (empty($category->name)) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'You must enter a name for your category');
					$this->view()->render('categories_create');
					break;
				}

				$category->id = $this->model('categorystore')->insert($category);

				if (!$category->id) {
					$this->action('create');
				} else {
					$this->model('categorystore')->setCategoryCodes($category->id, KC__VOCABULARY_CPV, $cpv_codes);

					$this->layout()->clearBreadcrumbs(2);
					$this->layout()->clearFeedback();
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "Your new category has been saved : '{$category->name}'.");
					$this->action('index');
				}

				break;
			default:
				$this->router()->action('404', 'error');
				break;
		}// /switch

	}// /method



	/**
	 * Configure which CPV codes are visible by default.
	 */
	public function actionCpvcodes() {


		if ($this->request()->post('submitcancel')) {
			$this->layout()->clear();
			$this->action('index');
			return ;
		}


		if ($this->request()->post('submitsave')) {
			$visible_subcodes = $this->request()->post('visible');
			$jumpable_codes = $this->request()->post('jumpable');

			$this->model('cpvstore')->setVisibleSubcodes($visible_subcodes);
			$this->model('cpvstore')->setJumpableCodes($jumpable_codes);

			$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
		}

		$this->view()->render('categories_cpvcodes');
	}// /method



	/**
	 * Edit an existing category.
	 */
	public function actionEdit() {
		if ($this->request()->post('submitcancel')) {
			$this->layout()->clearBreadcrumbs(2);
			$this->action('index');
			return ;
		}

		$id = $this->request()->fetch('id');
		if (empty($id)) { return $this->action('index'); }

		$category = $this->model('categorystore')->find($id);

		if (!$category) {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The category requested could not be found.');
			$this->layout()->clearBreadcrumbs(2);
			return $this->action('index');
		}

		switch($this->request()->httpMethod()) {
			default:
			case 'get':
				$category = $this->model('categorystore')->find($id);
				$cpv_codes = $this->model('categorystore')->getCategoryCodes($id, KC__VOCABULARY_CPV);
				break;
			case 'post':

				if ($this->request()->post('submitdelete')) {
					$this->model('categorystore')->delete($category->id);
					$this->layout()->clearBreadcrumbs(2);
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The category has been deleted');
					$this->action('index');
					return;
				}


				if ($this->request()->post('submittransfer')) {
					$target_category = $this->model('categorystore')->find($this->request()->post('destination'));
					if (empty($target_category)) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The items could not be transferred.', 'The destination category selected could not be found.');
					} else {
						$this->model('itemstore')->transferCategoryItems($category->id, $target_category->id);
						$this->model('categorystore')->rebuildItemCounts();

						if (1 == $this->request()->post('delete_on_transfer')) {
							$this->model('categorystore')->delete($category->id);
							$this->layout()->clearBreadcrumbs(2);
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "All items have been transfered and the original category deleted.");
							$this->action('index');
							return;
						} else {
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'All items have been transfered.');
						}
					}
				}


				$category->name = $this->request()->post('name');
				$cpv_codes = $this->request()->post('cpv_code');

				$ok = $this->model('categorystore')->update($category);
				$this->model('categorystore')->setCategoryCodes($category->id, KC__VOCABULARY_CPV, $cpv_codes);

				if ($ok) {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved..');
				}

				break;
		}// /switch


		$this->view()->category = $category;
		$this->view()->cpv_codes = $cpv_codes;
		$this->view()->render('categories_edit');
	}// /method



	/**
	 * Category admin index.
	 */
	public function actionIndex() {
		$this->view()->categories = $this->model('categorystore')->findAll();
		$this->view()->render('categories_index');
	}// /method



	/**
	 * Rebuild the item counts for categories.
	 */
	public function actionRebuildcounts() {

		$res = $this->model('categorystore')->rebuildItemCounts();

		$this->layout()->clear();

		if (true === $res) {
			$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The category item counts have been rebuilt.');
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an error rebuilding the category item counts.');
		}

		$this->action('index');
		return;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>