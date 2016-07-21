<?php
/*
 *
 */
class Controller_Admin_Undelete extends Ecl_Mvc_Controller {



	/**
	 * Before action method.
	 */
	public function beforeAction() {
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			$this->abort();
			$this->router()->action('unauthorised', 'error');
			return false;
		}

		$this->router()->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/admin/'));
		$this->router()->layout()->addBreadcrumb('Undo Delete Items', $this->router()->makeAbsoluteUri('/admin/undelete/index/'));
		$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
	}// /method



	/**
	 * Create a new access level.
	 */
	public function actionUndo() {
		$this->layout()->clearBreadcrumbs(2);
		$this->layout()->clearFeedback();

		if ($this->request()->isPost()) {
			$errors = false;

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$item_ids = $this->request()->post('ids');
			if (empty($item_ids)) {
				$errors[] = 'You must provide the name of your new access level.';
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				 
        foreach($item_ids as $item_id){
        $items = $this->model('itembackupstore')->findAllDeleted("item_id=$item_id");
            foreach($items as $item){
            $new_ids[] = $this->model('itembackupstore')->insert($item);
            $this->model('itembackupstore')->deleteBackup($item_id);   
            }
        }
        
				if ($new_ids) {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "the Item/s have been restored (files and tags can not be recovered)");
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an unspecified error adding your new access level.');
				}
			}
		} else {
			$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Unable to create access level.  No information posted.');
		}

		$this->action('index');
	}// /method



	/**
	 * Edit an existing access level.
	 */
	public function actionEdit() {

		$accesslevel = $this->model('accesslevelstore')->find($this->param('id'));

		if (empty($accesslevel)) {
			$this->router()->action('404', 'error');
			return;
		}


		if ($this->request()->isPost()) {

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The form details supplied appear to be forged.');
			} else {


				if ($this->request()->post('submitdelete')) {
					$this->model('accesslevelstore')->delete($accesslevel->id);
					$this->layout()->clearBreadcrumbs(2);
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The access level has been deleted');
					$this->action('index');
					return;
				}


				if ($this->request()->post('submitsave')) {
					$errors = false;

					$accesslevel->name = $this->request()->post('name');
					if (empty($accesslevel->name)) {
						$errors[] = 'You must provide a name for the access level.';
					}

					if ($errors) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
					} else {
						$saved_ok = $this->model('accesslevelstore')->update($accesslevel);

						if (!$saved_ok ) {
							$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
						} else {
							$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
						}
					}
				}


			}// /if(session key OK)

		}

		$this->view()->accesslevel = $accesslevel;
		$this->view()->render('accesslevels_edit');
	}// /method



	/**
	 *
	 */
	public function actionIndex() {
    // work here:
		$this->view()->deletedItems = $this->model('itembackupstore')->findAllDeleted();
		$this->view()->render('undelete_index');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>