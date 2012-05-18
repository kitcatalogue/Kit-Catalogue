<?php
/*
 *
 */
class Controller_Admin_Homepageblocks extends Ecl_Mvc_Controller {



	/**
	 * Before action method.
	 */
	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->abort();
			$this->router()->action('unauthorised', 'error');
			return false;
		}

		$this->layout()->addBreadcrumb('Homepageblocks', $this->router()->makeAbsoluteUri('/admin/homepageblocks/index/'));
		$this->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	/**
	 * Create a new homepageblock.
	 */
	public function actionCreate() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->request()->isPost()) {
			$errors = false;

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$block_name = $this->request()->post('new_block');
			if (empty($block_name)) {
				$errors[] = 'You must provide the name of your new homepageblock.';
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$new_block = $this->model('homepageblockstore')->newHomepageblock();
				$new_block->block_name = $block_name;
				$new_id = $this->model('homepageblockstore')->insert($new_block);

				if ($new_id) {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The homepageblock '{$new_block->name} has been added");
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an unspecified error adding your new homepageblock.');
				}
			}
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Unable to create homepageblock.  No information posted.');
		}

		$this->action('index');
	}// /method



	/**
	 * Edit an existing homepageblock.
	 */
	public function actionEdit() {

		$block = $this->model('homepageblockstore')->find($this->param('id'));

		if (empty($block)) {
			$this->router()->action('404', 'error');
			return;
		}


		if ($this->request()->isPost()) {


			if ($this->request()->post('submitdelete')) {
				$this->model('homepageblockstore')->delete($block->block_id);
				$this->layout()->clearBreadcrumbs(2);
				$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The homepageblock has been deleted');
				$this->action('index');
				return;
			}


			if ($this->request()->post('submitsave')) {
				$errors = false;

				if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
					$errors[] = 'The form details supplied appear to be forged.';
				}

				$block->block_name = $this->request()->post('block_name');
				if (empty($block->block_name)) {
					$errors[] = 'You must provide a name for the homepageblock.';
				}

				if ($errors) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
				} else {
					// Update $block
					$block->block_to_find = $this->request()->post('block_to_find');
					$this->request()->post('block_enabled') == 'Yes' ? $block->block_enabled = '1' : $block->block_enabled = '0';
					$block->visibility    = $this->request()->post('visibility');
					$saved_ok = $this->model('homepageblockstore')->update($block);

					if (!$saved_ok ) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
					} else {
						$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
					}
				}
			}
		}

		$this->view()->block = $block;
		$this->view()->render('homepageblocks_edit');
	}// /method



	/**
	 *
	 */
	public function actionIndex() {
		$this->view()->blocks = $this->model('homepageblockstore')->findAll();
		$this->view()->render('homepageblocks_index');
	}// /method



	/**
	 * Rebuild the item counts.
	 */
	public function actionRebuildcounts() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->model('homepageblockstore')->rebuildItemCounts()) {
			$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The homepageblock item counts have been rebuilt.');
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an error rebuilding the homepageblock item counts.');
		}

		$this->action('index');
		return;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
