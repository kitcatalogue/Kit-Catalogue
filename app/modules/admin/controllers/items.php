<?php
class Controller_Admin_Items extends Ecl_Mvc_Controller {

	const IMPORT_USEBLANK = '__useblank__';
	const IMPORT_USEIMPORTEDVALUE = '__useimportedvalue__';
	const IMPORT_USEWILLFAIL = '__fail__';



	// Private Properties
	protected $_row_headers = null;



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	public function beforeAction() {
		if ('actionEdit' == $this->_action) {
			// If editing, actionEdit() will do the proper check
			$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
		} else {
			if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
				$this->abort();
				$this->router()->action('unauthorised', 'error');
				return false;
			}

			$this->router()->layout()->addStylesheet($this->router()->makeAbsoluteUri('/css/admin.css'));
			$this->router()->layout()->addBreadcrumb('Administration', $this->router()->makeAbsoluteUri('/admin/'));
			$this->router()->layout()->addBreadcrumb('Items', $this->router()->makeAbsoluteUri('/admin/items/index/'));
		}
	}// /method



	public function actionCsvtemplate() {
		$this->view()->row_headers = $this->_getRowHeaders();
		$this->view()->render('items_csvtemplate');
	}// /method



	public function actionEdit() {
		$lang = $this->model('lang');

		$saved_ok = false;
		$added_ok = false;

		$item_id = $this->param('id');

		// We're editing a 'new' item
		if ('new' == strtolower($item_id)) {
			$new_item = true;
			$item = $this->model('itemstore')->newItem();

			// If the item was new, but we've saved it once already..
			$item_id = $this->request()->post('item_id');
			if (!empty($item_id)) {
				$new_item = false;
				$item = $this->model('itemstore')->find($item_id);
			} else {
				// Load any preset values
				$presets = $this->request()->get('preset');

				if ($presets && is_array($presets)) {
					foreach($presets as $k => $v) {
						switch ($k) {
							case 'category':
								break;
							case 'manufacturer':
								$item->manufacturer = $v;
								break;
							case 'ou':
								$item->ou_id = $v;
								break;
							case 'technique':
								$item->technique = $v;
								break;
							default:
								break;
						}
					}
				}
			}
		} else {
			// The item is a pre-existing one
			$new_item = false;
			$item = $this->model('itemstore')->find($item_id);
		}


		if (empty($item)) {
			$this->router()->action('404', 'error');
			return;
		}


		// Check user permissions allow editing of this item
		if (!$this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
			if (!$this->model('security')->checkItemPermission($item, 'site.item.edit')) {
				$this->router()->action('unauthorised', 'error');
				return;
			}
		}


		$backlink = $this->router()->makeAbsoluteUri(base64_decode($this->request()->get('backlink')));

		if ($this->request()->post('submitdelete')) {
			if (!$new_item) {
        //backup item first, then delete
        $this->model('itembackupstore')->backup($item->id);
				$this->model('itemstore')->delete($item->id);

				// Rebuild cached item counts
				$this->model('categorystore')->rebuildItemCounts();
				$this->model('organisationalunitstore')->rebuildItemCounts();
				$this->model('supplierstore')->rebuildItemCounts();
				$this->model('buildingstore')->rebuildItemCounts();
			}
			$this->layout()->clearBreadcrumbs(2);
			$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'The item has been deleted');

			// If the backlink is not item-specific, redirect there.
			if (!preg_match("#/(item|id)/(.*)/{$item->id}$#", $backlink)) {
				$this->response()->setRedirect($backlink);
			} else {
				$this->response()->setRedirect($this->router()->makeAbsoluteUri('/'));
			}

			return;
		}



		// We need a 'blank' uploader so we can access its methods, even if we don't upload any files
		$uploader = Ecl::factory('Ecl_Uploader', array());



		if ($this->request()->isPost()) {

			$errors = false;
			$saved_ok = false;
			$added_ok = false;

			// Check if an over-sized file has been posted
			if ( empty($_POST) && empty($_FILES) ) {

				$max_size = Ecl_Helper_String::formatBytes($uploader->getPhpUploadMaxSize());

				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Maximum upload size exceeded.', "
					<p>You attempted to upload files bigger than the maximum size allowed - The maximum allowed is ~$max_size).</p>
					<p>This restriction is set by your server, and is not controlled by the catalogue software. You should contact your server technicians if you wish to upload large files.</p>
					<p>Due to the oversized upload, all changes have been lost.</p>
					<p>Please check the item details again and avoid uploading any large files.</p>
				");

				$this->view()->item = $item;
				$this->view()->item_path = null;
				$this->view()->backlink = $backlink;
				$this->view()->saved_ok = false;
				$this->view()->php_max_upload = $uploader->getPhpUploadMaxSize();
				$this->view()->render('items_edit');
				return;
			}


			// Populate the item with the new form information
			// Some properties, categories, files and custom fields, are processed after the item record is saved


			// Main Details
			$item->title = $this->request()->post('title');
			$item->manufacturer = $this->request()->post('manufacturer');
			$item->model = $this->request()->post('model');


			// OU
			$can_change_ou = false;
			$allow_any_ou = false;
			$valid_ou = array();

			if ($this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
				$can_change_ou = true;
				$allow_any_ou = true;
			} else {
				$valid_ou = $this->model('security')->findOUsForAuth(KC__AUTH_CANOUADMIN);
				if ($valid_ou) {
					$can_change_ou = true;
					$allow_any_ou = false;
				}
			}

			if ($can_change_ou) {
				$ou_id = $this->request()->post('ou');
				if ( ($allow_any_ou) || (in_array($ou_id, $valid_ou)) ) {
					$item->ou = $ou_id;
				} else {
					$errors[] = "You are not authorised to associate items with the selected {$lang['ou.label']}.";
				}
			}


			$item->short_description = $this->request()->post('short_description');
			$item->full_description = $this->request()->post('full_description');
			$item->specification = $this->request()->post('specification');

			$item->upgrades = $this->request()->post('upgrades');
			$item->future_upgrades = $this->request()->post('future_upgrades');

			$item->technique = $this->request()->post('technique');
			$item->acronym = $this->request()->post('acronym');
			$item->keywords = $this->request()->post('keywords');

			$item->manufacturer_website = $this->request()->post('manufacturer_website');

			// Parent Facility
			$item->is_parent = (bool) $this->request()->post('is_parent', false);


			// Access & Usage
			$vis = $this->request()->post('visibility');
			//if (in_array($vis, array(KC__VISIBILITY_INTERNAL, KC__VISIBILITY_PUBLIC, KC__VISIBILITY_DRAFT))) {
			if (in_array($vis, array(KC__VISIBILITY_INTERNAL, KC__VISIBILITY_PUBLIC))) {
				$item->visibility = $vis;
			}

			$item->access = $this->request()->post('access');
			$item->portability = $this->request()->post('portability');
			$item->availability = $this->request()->post('availability');
			$item->restrictions = $this->request()->post('restrictions');
			$item->usergroup = $this->request()->post('usergroup');

			$item->training_required = Ecl_Helper_String::parseBoolean($this->request()->post('training_required'), null);
			$item->training_provided = Ecl_Helper_String::parseBoolean($this->request()->post('training_provided'), null);

			$temp = strtolower($this->request()->post('calibrated', Item::CALIB_NOTAPP));
			switch ($temp) {
				case Item::CALIB_YES:
				case Item::CALIB_NO:
				case Item::CALIB_AUTO:
					$item->calibrated= $temp;
					break;
				default:
					$item->calibrated = '';
					break;
			}

			$item->last_calibration_date = $this->request()->postDmyt('last_calibration_date');
			$item->next_calibration_date = $this->request()->postDmyt('next_calibration_date');

			$temp = (int) $this->request()->post('quantity', 1);
			$item->quantity = ($temp<1) ? 1 : $temp ;

			$item->quantity_detail = $this->request()->post('quantity_detail');


			// Contact Information
			$item->contact_1_name = $this->request()->post('contact_1_name');

			$email = $this->request()->post('contact_1_email');
			$item->contact_1_email = (!empty($email)) ? $email : trim($this->request()->post('new_contact_1_email', '')) ;


			$item->contact_2_name = $this->request()->post('contact_2_name');

			$email = $this->request()->post('contact_2_email');
			$item->contact_2_email = (!empty($email)) ? $email : trim($this->request()->post('new_contact_2_email', '')) ;


			$allow_all_ou = false;

			if ($this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
				$allow_all_ou = true;
			} elseif ($this->model('security')->checkOUAuth($item->ou, KC__AUTH_CANOUADMIN)) {
				$allow_select_ou = true;
				$allow_all_ou = false;
				$valid_ou = $this->model('security')->findOUsForAuth(KC__AUTH_CANOUADMIN);
			}


			// Location
			$item->site = $this->request()->post('site');
			$item->building = $this->request()->post('building');
			$item->room = $this->request()->post('room');


			// Asset & Finance Information
			$item->asset_no = $this->request()->post('asset_no');
			$item->finance_id = $this->request()->post('finance_id');
			$item->serial_no = $this->request()->post('serial_no');
			$item->year_of_manufacture = $this->request()->post('year_of_manufacture');

			// Firstly, are we adding a supplier?
			$new_supplier = trim($this->request()->post('new_supplier'));

			if (empty($new_supplier)) {
				$item->supplier = $this->request()->post('supplier');
			} else {
				$existing_supplier = $this->model('supplierstore')->findForName($new_supplier);
				if ($existing_supplier) {
					$item->supplier = $existing_supplier->id;
				} else {
					// Create new supplier, and use it
					$supplier = $this->model('supplierstore')->newSupplier();
					$supplier->name = $new_supplier;
					$new_supplier_id = $this->model('supplierstore')->insert($supplier);
					if (!$new_supplier_id) {
						$errors[] = "Unable to create new supplier: '{$new_supplier}'";
					} else {
						$item->supplier = $new_supplier_id;
					}
				}
			}

			$item->PAT = $this->request()->postDmyt('PAT');

			$item->date_of_purchase = $this->request()->postDmyt('date_of_purchase');
			$item->cost = $this->request()->post('cost');
			$item->replacement_cost = $this->request()->post('replacement_cost');
			$item->end_of_life = $this->request()->postDmyt('end_of_life');
			$item->maintenance = $this->request()->post('maintenance');

			$item->date_of_purchase = $this->request()->postDmyt('date_of_purchase');

			$item->is_disposed_of = $this->request()->post('is_disposed_of');
			$item->date_disposed_of = $this->request()->postDmyt('date_disposed_of');

			$item->archived = (bool) $this->request()->post('archived', false);
			if ($item->archived) {
				if (is_null($item->date_archived)) { $item->date_archived = time(); }
			}

			$item->comments = $this->request()->post('comments');

			$item->copyright_notice = $this->request()->post('copyright_notice');

			if ($this->model('item.allow_embedded_content')) {
				$item->embedded_content = $this->request()->post('embedded_content');
			}


			// Validate the new item
			if ( (empty($item->manufacturer)) && (empty($item->title)) ) { $errors[] = "You must supply either the item's {$lang['item.form.title']} or {$lang['item.form.manufacturer']}."; }
			if (empty($item->ou)) { $errors[] = "You must select the {$lang['ou.label']} in which this item resides."; }
			if (empty($item->visibility)) { $errors[] = "You must select the {$lang['item.form.visibility']} of this item."; }
			if (empty($item->contact_1_email)) { $errors[] = "'You must enter at least the {$lang['item.form.contact_1']} email address."; }

			// Save the item information
			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found while saving your changes:', '', $errors);
			} else {

				$continue_saving = false;

				if ($new_item) {
					$id = $this->model('itemstore')->insert($item);

					if (empty($id)) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your new item could not be created.');
					} else {
						$continue_saving = true;
						$added_ok = true;

						$item->id = $id;
						$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your new item has been created.');
					}
				} else {
					$continue_saving = $this->model('itemstore')->update($item);
					if (!$continue_saving) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'Your changes could not be saved.');
					} else {
						$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, 'Your changes have been saved.');
					}
				}


				// Path to item files
				$item_path = $this->model()->get('app.upload_root'). '/items'. $item->getFilePath();

				if ($continue_saving) {

					// Process Editors
					if ($this->model('admin.item.editors.enabled')) {
						$new_editors = $this->request()->post('new_editors');
						if (!empty($new_editors)) {
							$editors = explode("\n", $new_editors);
							foreach($editors as $username) {
								$username = trim($username);
								if (!empty($username)) {
									$editor = $this->model('itemeditorstore')->newItemEditor();
									$editor->item_id = $item->id;
									$editor->username = $username;
									$this->model('itemeditorstore')->insert($editor);
								}
							}
						}
						$editors = null;


						$remove_editors = $this->request()->post('remove_editors');
						if (!empty($remove_editors)) {
							foreach($remove_editors as $editor) {
								$this->model('itemeditorstore')->deleteEditorFromItem($editor, $item->id);
							}
						}
						$remove_editors = null;
					}


					// Process Categories
					$categories = $this->request()->post('category');

					$other_category = trim($this->request()->post('other_category'));
					if (!empty($other_category)) {
						$existing_cat = $this->model('categorystore')->findForName($other_category);
						if ($existing_cat) {
							$categories[] = $existing_cat->id;
						} else {
							$category = $this->model('categorystore')->newCategory();
							$category->name = $other_category;

							$new_category_id = $this->model('categorystore')->insert($category);
							if ($new_category_id) {
								$categories[] = $new_category_id;
							}
						}
					}
					$this->model('itemstore')->setItemCategories($item->id, $categories);


					// Process Custom Fields
					$fields = $this->model('customfieldstore')->findAll();
					$custom_fields = array();
					foreach($fields as $field) {
						$custom_fields[$field->id] = $this->request()->post('custom_field_'.$field->id);
					}

					$this->model('itemstore')->setItemCustomFields($item->id, $custom_fields);


					// Process Tags
					$tags = $this->request()->post('tags');
					$tags = explode(',', $tags);
					$this->model('itemstore')->setItemTags($item->id, $tags);


					// Process Children
					$children = $this->request()->post('children');
					$this->model('itemstore')->setItemChildren($item->id, $children);


					// Process Parents
					$parents = $this->request()->post('parents');
					$this->model('itemstore')->setItemParents($item->id, $parents);



					/*
					 * Process Links
					 */

					// Link changes
					$links = $this->request()->post('link');
					if ($links) {
						foreach($links as $id => $link_info) {
							$link = $this->model('itemlinkstore')->find($id);
							if ( (!empty($link)) && ($link->item_id==$item->id) ){
								$link->name = $link_info['name'];
								$link->url = $link_info['url'];
								$link->type = $link_info['type'];

								if (!empty($link->url)) {
									$this->model('itemlinkstore')->update($link);
								}
							}
						}
					}

					// Add new links
					$new_links = $this->request()->post('newlink');
					if (!empty($new_links)) {
						foreach($new_links as $link_info) {
							$newlink = new Itemlink();
							$newlink->item_id = $item->id;
							$newlink->type = 0;
							$newlink->name = $link_info['name'];
							$newlink->url = $link_info['url'];

							if (!empty($newlink->url)) {
								$this->model('itemlinkstore')->insert($newlink);
							}
						}
					}


					// Delete links
					$delete_links = $this->request()->post('delete_link');
					if (!empty($delete_links)) {
						foreach($delete_links as $link_id) {
							$this->model('itemlinkstore')->deleteLinkFromItem($link_id, $item->id);
						}
					}



					/*
					 * Process Files
					 */


					// File changes
					$files = $this->model('itemstore')->findFilesForItem($item);
					if ($files) {
						foreach($files as $file) {
							$b64_filename = base64_encode($file->filename);
							$file->type = $this->request()->post("file_type_{$b64_filename}", 0);
							$file->name = $this->request()->post("file_name_{$b64_filename}", '');
							$this->model('itemstore')->setFileInfo($file);
						}
					}


					// Deletions
					$deletes = $this->request()->post('delete_file');
					if (!empty($deletes)) {
						foreach($deletes as $filename) {
							$this->model('itemstore')->deleteFile($item, $filename);
						}
					}

					// File uploads

					// Create uploader class (even if we're not saving, we need this!)
					$uploader = Ecl::factory('Ecl_Uploader', array (
						'path'       => $item_path ,
						'flags'      => Ecl_Uploader::ALLOW_OVERWRITE + Ecl_Uploader::ALLOW_CREATEPATH ,
					));


					if ($uploader->isUpload()) {
						$files = $uploader->getUploadedFiles();
						foreach($files as $file) {
							$new_file = $this->model('itemstore')->newItemFile();

							$filename = basename($file['path']);
							$extension = strtolower(Ecl_Helper_Filesystem::getFileExtension($filename));

							$new_file->item_id = $item->id;
							$new_file->filename = $filename;
							$new_file->type = 0;
							$new_file->name = '';
							$this->model('itemstore')->setFileInfo($new_file);
						}

						if ($uploader->isMessage()) {
							$this->layout()->addFeedback(KC__FEEDBACK_WARNING, 'Unable to upload files.', '', $uploader->getMessages());
						}

					}


					// Check selected image settings
					$files = $this->model('itemstore')->findFilesForItem($item);
					$image_files = array();
					if (!empty($files)) {
						$image_ext = array ('jpg', 'jpeg', 'gif', 'png');
						foreach($files as $file) {
							$file_path = "{$item_path}/{$file->filename}";
							if (file_exists($file_path)) {
								$extension = strtolower(Ecl_Helper_Filesystem::getFileExtension($file->filename));
								if (in_array($extension, $image_ext)) {
									if ( (null !== $this->model('item.image.max_width')) || (null !== $this->model('item.image.max_height')) ) {
										$img = Ecl_Image::createFromFile($file_path);
										if ($img) {
											$img->resizeWithinLimits($this->model('item.image.max_width'), $this->model('item.image.max_height'));
											$img->save();
										}
									}
									$image_files[] = $file;
								}
							}
						}// /foreach(file)
					}


					$image_count = count($image_files);
					if (0 == $image_count) {
						$item->image = '';
					} elseif (1 == $image_count) {
						$item->image = $image_files[0]->filename;
					} else {
						$item->image = $this->request()->post('use_image', '');
					}

					// If the item's main image is still blank, but there are images available, use the first one
					if ( ('' == $item->image) && ($image_count > 0) ) {
						$item->image = $image_files[0]->filename;
					}


					// Rebuild cached item counts
					$this->model('categorystore')->rebuildItemCounts();
					$this->model('organisationalunitstore')->rebuildItemCounts();
					$this->model('supplierstore')->rebuildItemCounts();
					$this->model('buildingstore')->rebuildItemCounts();

					//Final update of item
					$this->model('itemstore')->update($item);

					$saved_ok = true;

				}// /if (continue saving)
			}// if-else (no errors)

		}


		// Path to item files
		$item_path = ($new_item) ? null : $this->model()->get('app.upload_root'). DIRECTORY_SEPARATOR . 'items'. $item->getFilePath() ;


		$this->view()->item = $item;
		$this->view()->item_path = $item_path;
		$this->view()->backlink = $backlink;
		$this->view()->saved_ok = $saved_ok;
		$this->view()->added_ok = $added_ok;
		$this->view()->php_max_upload = $uploader->getPhpUploadMaxSize();
		$this->view()->render('items_edit');
	}



	public function actionExport() {
		$this->router()->layout()->addBreadcrumb('Export Wizard', $this->router()->makeAbsoluteUri('/admin/items/export'));

		$step = $this->request()->get('step', 1);

		if ($this->request()->isGet()) {
			$this->view()->render('items_exportwizard1');
			return;
		}

		if ($this->request()->post('submitcancel')) {
			$this->response()->setRedirect($this->router()->makeAbsoluteUri('/admin/items/index/'));
			return;
		}


		switch ($step) {
			// ----------------------------------------------------------------
			case 1 :
				$this->view()->render('items_exportwizard1');
				break;
			// ----------------------------------------------------------------
			case 2 :
				$ou_id = $this->request()->post('ou');
				$visibility = $this->request()->post('visibility', null);
				$options = $this->request()->post('options');

				$include_subtree = (in_array('include_subtree', $options));

				$this->view()->items = $this->model('itemstore')->findForOU($ou_id, $include_subtree, $visibility);
				$this->view()->render('items_exportwizard2');
				break;
			default:
				$this->router()->action('404', 'error');
				break;
		}// /switch

	}



	public function actionImport() {
		$this->router()->layout()->addBreadcrumb('Import Wizard', $this->router()->makeAbsoluteUri('/admin/items/import'));

		// Pre-configure the file paths and filenames required

		$upload_path = $this->model()->get('app.upload_root') .DIRECTORY_SEPARATOR. 'data';
		$processing_path = $this->model()->get('app.upload_root') .DIRECTORY_SEPARATOR. 'processing';

		$datafilename = null;
		$procfilename = null;

		$filename = $this->request()->post('filename');
		if ($filename) {
			$datafilename = Ecl_Helper_Filesystem::fixPath($upload_path .DIRECTORY_SEPARATOR. $filename);
			if (!Ecl_Helper_Filesystem::isPathBelowRoot($datafilename, $upload_path)) { $datafilename = null; }

			$procfilename = Ecl_Helper_Filesystem::fixPath($processing_path .DIRECTORY_SEPARATOR. $filename . '.tmp');
			if (!Ecl_Helper_Filesystem::isPathBelowRoot($procfilename, $processing_path)) { $procfilename = null; }
		}


		$step = $this->request()->get('step', 1);


		if ($this->request()->isGet()) {
			$this->_deleteOldImportFiles();
			$this->view()->render('items_importwizard1');
			return;
		}

		// If we're cancelling, delete the old file
		if ($this->request()->post('submitcancel')) {
			if (!empty($datafilename)) { Ecl_Helper_Filesystem::deleteFile($datafilename); }
			if (!empty($procfilename)) { Ecl_Helper_Filesystem::deleteFile($procfilename); }

			$this->response()->setRedirect($this->router()->makeAbsoluteUri('/admin/items/index/'));
			return;
		}



		// Prepare lookup info for validating incoming data
		$lookup_models = array (
			'access'          => 'accesslevelstore' ,
			'building'        => 'buildingstore' ,
			'category'        => 'categorystore' ,
			'ou'              => 'organisationalunitstore' ,
			'site'            => 'sitestore' ,
			'supplier'        => 'supplierstore' ,
		);



		if ($this->request()->post('submitback')) {
			if ($step>2) { $step -= 2; }
		}


		switch ($step) {
			// ----------------------------------------------------------------
			case 1 :
				// We only call this if it's a POST.  See above for GET...
				$this->view()->render('items_importwizard1');
				break;
			// ----------------------------------------------------------------
			case 2 :
				// Validate the form

				// If the datafilename param was posted, then we came from another page in the wizard.
				// Check the existing upload, rather than import it again.
				if (!file_exists($datafilename)) {
					$uploader = Ecl::factory('Ecl_Uploader', array (
						'path'       => $upload_path ,
						'flags'      => Ecl_Uploader::ALLOW_AUTORENAME + Ecl_Uploader::ALLOW_CREATEPATH ,
						'filenames'  => array (
						'datafile'   => 'itemimport_'. date('Ymd_His') .'_'. rand(1, 10000000) .'.csv' ,
						) ,
					));

					if (!$uploader->isUpload()) {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'No file was uploaded', '', $uploader->getMessages());
						$this->view()->render('items_importwizard1');
						return;
					}

					$datafilename = $uploader->getUploadedFile('datafile');
				}


				$datacsv = $this->_readCsvFile($datafilename);
				if (empty($datacsv)) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'No data could be read from the file');
					$this->view()->render('items_importwizard1');
					return;
				}


				$this->view()->expected_headers = Ecl_Helper_Array::changeValueCase($this->_getRowHeaders(), CASE_LOWER);
				$this->view()->datacsv = $datacsv;
				$this->view()->filename = basename($datafilename);
				$this->view()->ignore_rows = $this->request()->post('ignore_rows', 1);
				$this->view()->render('items_importwizard2');
				break;
			// ----------------------------------------------------------------
			case 3 :
				// Validate the form

				$datacsv = $this->_readCsvFile($datafilename);


				if (empty($datacsv)) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'No data could be read from the uploaded file');
					$this->view()->render('items_importwizard1');
					return;
				}


				// Ignore rows as appropriate
				$ignore_rows = (int) $this->request()->post('ignore_rows', 0);
				if ($ignore_rows>0) {
					for($i=0; $i<$ignore_rows; $i++) {
						if (array_key_exists($i, $datacsv)) {
							unset($datacsv[$i]);
						}
					}
				}


				// Prepare lookup info for validating incoming data
				$lookups = array();
				foreach($lookup_models as $lookup_name => $model_name) {
					$list = $this->model($model_name)->findAll()->toAssoc('id', 'name');

					array_walk($list, function(&$v, $k) {
						$v = strtolower($v);
					});

					$lookups[$lookup_name] = $list;
				}


				// Begin the import.
				$standard_columns = (array) $this->_getStandardRowHeaders();

				$standard_keys = Ecl_Helper_Array::changeValueCase($standard_columns, CASE_LOWER);

				$custom_columns = Ecl_Helper_Array::extractColumn($this->model('customfieldstore')->findAll()->toArray(), 'name', true);
				$custom_columns = Ecl_Helper_Array::changeValueCase($custom_columns, CASE_LOWER);

				$items = array();    // Assoc-array of items being imported and their 'issues'
				$are_issues = false;

				$date_format_mdy = (true === $this->model('import.date_format_mdy'));


				foreach($datacsv as $i => $row) {

					$temp = array_fill_keys($standard_keys, '');
					$row = array_merge($temp, $row);

					$item = $this->model('itemstore')->newItem();

					$issues = null;
					$custom_fields = null;

					// Process each column in turn

                    $item->title = Ecl_Helper_String::parseString($row['item_title'], 250);
					$item->manufacturer = Ecl_Helper_String::parseString($row['manufacturer'], 100);

					if ( (empty($item->title)) && (empty($item->manufacturer)) ) {
						$issues['title'] = '';
					}

					$item->model = Ecl_Helper_String::parseString($row['model'], 100);

					$item->short_description = Ecl_Helper_String::parseString($row['short_description'], 250);
					$item->full_description = Ecl_Helper_String::parseString($row['full_description'], 65535);
					$item->specification = Ecl_Helper_String::parseString($row['specification'], 65535);
					$item->upgrades = Ecl_Helper_String::parseString($row['upgrades'], 250);
					$item->future_upgrades = Ecl_Helper_String::parseString($row['future_upgrades'], 250);
					$item->acronym = Ecl_Helper_String::parseString($row['acronym'], 15);
					$item->keywords = Ecl_Helper_String::parseString($row['keywords'], 250);

					// Tags
					// @todo : Import tags
					$temp = Ecl_Helper_String::parseString($row['tags'], 250);
					if (empty($temp)) {
						$item->tags = '';
					} else {
						$item->tags = explode(',', $row['tags']);
					}

					// Technique
					$temp = Ecl_Helper_String::parseString($row['technique'], 100);
					if (empty($temp)) {
						$issues['technique'] = '';
					} else {
						$item->technique = $temp;
					}

					$item->availability = Ecl_Helper_String::parseString($row['availability'], 250);
					$item->restrictions = Ecl_Helper_String::parseString($row['restrictions'], 250);
					$item->usergroup = Ecl_Helper_String::parseString($row['usergroup'], 250);

					// Access
					$temp = Ecl_Helper_String::parseString($row['access'], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['access']));
					if (false === $temp_id) {
						$issues['access'] = $temp;
					} else {
						$item->access = $temp_id;
					}

					$item->portability = Ecl_Helper_String::parseString($row['portability'], 250);

					// Category
					$temp = Ecl_Helper_String::parseString($row['category'], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['category']));
					if (false === $temp_id) {
						$issues['category'] = $temp;
					} else {
						$item->category = $temp_id;
					}

					// OU
					$temp = Ecl_Helper_String::parseString($row['organisational_unit'], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['ou']));
					if (false === $temp_id) {
						$issues['ou'] = $temp;
					} else {
						$item->ou = $temp_id;
					}

					// Site
					$temp = Ecl_Helper_String::parseString($row['site'], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['site']));
					if (false === $temp_id) {
						$issues['site'] = $temp;
					} else {
						$item->site = $temp_id;
					}

					// Building
					$temp = Ecl_Helper_String::parseString($row['building'], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['building']));
					if (false === $temp_id) {
						$issues['building'] = $temp;
					} else {
						$item->building = $temp_id;
					}

					$item->room = Ecl_Helper_String::parseString($row['room'], 250);

					$item->contact_1_name = Ecl_Helper_String::parseString($row['contact_1_name'], 250);

					$item->contact_1_email = Ecl_Helper_String::parseString($row['contact_1_email'], 250);
					if (empty($item->contact_1_email)) {
						$issues['contact_1_email'] = '';
					}

					$item->contact_2_name = Ecl_Helper_String::parseString($row['contact_2_name'], 250);
					$item->contact_2_email = Ecl_Helper_String::parseString($row['contact_2_email'], 250);

					// Visibility
					$visibility = Ecl_Helper_String::parseString($row['visibility'], 10);
					if ('public' == strtolower($visibility)) {
						$item->visibility = KC__VISIBILITY_PUBLIC;
					} else {
						$item->visibility = KC__VISIBILITY_INTERNAL;
					}

					$item->manufacturer_website = preg_replace("/^https?:\/\/(.+)$/i","\\1", trim($row['manufacturer_website']));
					$item->copyright_notice = Ecl_Helper_String::parseString($row['copyright_notice'], 250);

					$item->training_required = Ecl_Helper_String::parseBoolean($row['training_required'], null);
					$item->training_provided = Ecl_Helper_String::parseBoolean($row['training_provided'], null);

					$item->quantity = Ecl_Helper_String::parseString($row['quantity'], 5);
					$item->quantity_detail = Ecl_Helper_String::parseString($row['quantity_detail'], 250);

					$item->PAT = Ecl_Helper_String::parseDate($row['pat'], null, $date_format_mdy);

					// Calibrated
					$temp = strtolower($row['calibrated']);
					switch ($temp) {
						case Item::CALIB_YES:
						case Item::CALIB_NO:
						case Item::CALIB_AUTO:
							$item->calibrated= $temp;
							break;
						default:
							$item->calibrated = '';
							break;
					}

					$item->last_calibration_date = Ecl_Helper_String::parseDate($row['last_calibration_date'], null, $date_format_mdy);
					$item->next_calibration_date = Ecl_Helper_String::parseDate($row['next_calibration_date'], null, $date_format_mdy);

					$item->asset_no = Ecl_Helper_String::parseString($row['asset_no'], 50);
					$item->finance_id = Ecl_Helper_String::parseString($row['finance_id'], 50);
					$item->serial_no = Ecl_Helper_String::parseString($row['serial_no'], 50);
					$item->year_of_manufacture = Ecl_Helper_String::parseString($row['year_of_manufacture'], 4);

					// Supplier
					$temp = Ecl_Helper_String::parseString($row['supplier'], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['supplier']));
					if (false === $temp_id) {
						$issues['supplier'] = $temp;
					} else {
						$item->supplier_id = $temp_id;
					}

					$item->date_of_purchase = Ecl_Helper_String::parseDate($row['date_of_purchase'], null, $date_format_mdy);
					$item->cost = Ecl_Helper_String::parseString($row['purchase_cost'], 100);
					$item->replacement_cost = Ecl_Helper_String::parseString($row['replacement_cost'], 100);
					$item->end_of_life = Ecl_Helper_String::parseDate($row['end_of_life'], null, $date_format_mdy);
					$item->maintenance = Ecl_Helper_String::parseString($row['maintenance'], 100);

					// Is Disposed Of
					$temp = Ecl_Helper_String::parseString($row['is_disposed_of'], 100);
					switch ($temp) {
						case Item::DISPOSED_NO:
						case Item::DISPOSED_SCRAP:
						case Item::DISPOSED_SOLD:
							$item->is_disposed_of = $temp;
							break;
						default:
							$item->is_disposed_of = Item::DISPOSED_NO;
							break;
					}

					$item->date_disposed_of = Ecl_Helper_String::parseDate($row['date_disposed_of'], null, $date_format_mdy);
					$item->comments = Ecl_Helper_String::parseString($row['comments'], 65500);

					// Process Custom Fields
					foreach($custom_columns as $j => $field) {
						if (array_key_exists($field, $row)) {
							$custom_fields[$field] = Ecl_Helper_String::parseString($row[$field], 250);
						}
					}

					// Add the item
					$items[$i]['item'] = $item;
					$items[$i]['issues'] = $issues;
					$items[$i]['custom'] = $custom_fields;

					if (is_array($issues)) { $are_issues = true; }
				}

				$datacsv = null;

				// Save the items and issues to a temporary file
				if (0 == count($items)) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'No items could be created from the imported data');
				} else {
					$procfilename = Ecl_Helper_Filesystem::fixPath($processing_path .DIRECTORY_SEPARATOR. $filename . '.tmp');

					if (!file_exists($processing_path)) { Ecl_Helper_Filesystem::createFolder($processing_path); }

					Ecl_Helper_Filesystem::setFileContents($procfilename, serialize($items));
				}


				if ($are_issues) {
					$this->layout()->addFeedback(KC__FEEDBACK_WARNING, 'There were some issues with the data being imported.', '<p>Please address the issues highlighted below before continuing.</p>');

					/* Prepare lookup info for user selection of 'issue' data
					 * We did this before the item creation, but that was lower-cased.
					 * Now we do it again with the correct case.
					 */
					$lookups = array();
					foreach($lookup_models as $lookup_name => $model_name) {
						if (in_array($lookup_name, array('ou')) ) {
							$ou_list = $this->model('organisationalunitstore')->findTree();
							$list = array();
							foreach($ou_list as $ou) {
								$list[$ou->id] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $ou->tree_level) . $ou->name;
							}
							$list = array(self::IMPORT_USEWILLFAIL => '-- select a value --') + $list;
						} else {
							$list = $this->model($model_name)->findAll()->toAssoc('id', 'name');
							$list = array(self::IMPORT_USEBLANK => '-- leave blank --') + $list;
						}

						$lookups[$lookup_name] = $list;
					}

				}

				$this->view()->lookups = $lookups;
				$this->view()->items = $items;
				$this->view()->are_issues = $are_issues;
				$this->view()->filename = $filename;
				$this->view()->ignore_rows = $ignore_rows;
				$this->view()->use_imported = self::IMPORT_USEIMPORTEDVALUE;
				$this->view()->render('items_importwizard3');
				break;
			// ----------------------------------------------------------------
			case 4 :
				if (!file_exists($procfilename)) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The pre-processed upload file could not be found.');
					$this->view()->render('items_importwizard1');
					return;
				}

				$temp = Ecl_Helper_Filesystem::readFileContents($procfilename);
				if (empty($temp)) {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'No data could be read from the pre-processed upload file.');
					$this->view()->render('items_importwizard1');
					return;
				}

				$items = unserialize($temp);
				$temp = null;

				$errors = null;
				$inserted_an_item = false;

				$successful_items = array();


				$custom_fields = $this->model('customfieldstore')->findAll()->toArray();


				// Prepare lookup info for checking if creation is required
				$lookups = array();
				foreach($lookup_models as $lookup_name => $model_name) {
					$list = $this->model($model_name)->findAll()->toAssoc('id', 'name');

					array_walk($list, function(&$v, $k) {
						$v = strtolower($v);
					});

					$lookups[$lookup_name] = $list;
				}


				// @todo : More error checking


				foreach($items as $rownum => $info) {

					$item = $info['item'];

					// Process any issues and merge into $item where appropriate
					if (isset($info['issues'])) {
						foreach($info['issues'] as $k => $v) {

							// Get the user selected value
							$value = $this->request()->post("{$k}_{$rownum}", '');

							switch ($k) {
								case 'manufacturer':
								case 'technique':
									$item->$k = $value;
									break;
								case 'contact_1_email':
									$temp = trim($this->request()->post("new_{$k}_{$rownum}", ''));
									if (!empty($temp)) {
										$item->contact_1_email = $temp;
									} else {
										$item->contact_1_email = $value;
									}
									break;
								case 'tags':
									// Do nothing, process them later
									break;
								default:
									if (self::IMPORT_USEBLANK == $value) {
										$item->$k = '';
									} elseif (self::IMPORT_USEWILLFAIL == $value) {
										$item->$k = null;
										$errors[] = "You did not select a valid value to use for '$k'. (row $rownum).";
									} elseif (self::IMPORT_USEIMPORTEDVALUE == $value) {

										/* Check if the row already exists in the lookups
										 * It won't have existed on the previous step, but it might have been already created
										 * during this import process
										 */

										$lc_value = strtolower($v);

										if (in_array($lc_value, $lookups[$k])) {
											$item->$k = array_search($lc_value, $lookups[$k]);
										} else {
											if (!empty($v)) {
												// Create a new row in the appropriate lookup table
												$new_id = $this->_processNewLookupValue($k, $v);
												if ($new_id) {
													$lookups[$k][$new_id] = $lc_value;
													$item->$k = $new_id;
												} else {
													$item->$k = null;
													$errors[] = "Failed to create new $k named '$v' (row $rownum).";
												}
											}
										}
									} elseif (null !== $value) {
										$item->$k = $value;
									}
									break;
							}

						}// /foreach(issue)
					}

					// Insert item
					$validation_errors = null;
					if (!$item->validate($validation_errors)) {
						$errors[] = "Unable to create item: \"{$item->name}\" (row $rownum). Reasons for failure were.. ". implode(' ', $validation_errors);
					} else {
						$item_id = $this->model('itemstore')->insert($item);

						if ($item_id) {
							$inserted_an_item = true;
							$item->id = $item_id;

							$successful_items[$item->id] = $item->name;

							// Set tags
							if (!empty($item->tags)) {
								$this->model('itemstore')->setItemTags($item->id, $item->tags);
							}

							// Set custom fields
							$custom = array();
							if (!empty($custom_fields)) {
								foreach($custom_fields as $field) {
									$field_name = strtolower($field->name);
									if ( (isset($info['custom'][$field_name])) && (!empty($info['custom'][$field_name])) ) {
										$custom[$field->id] = $info['custom'][$field_name];
									}
								}
							}


							if (!empty($custom)) {
								$this->model('itemstore')->setItemCustomFields($item->id, $custom);
							}

							// Map item-to-category
							if ($item->category) {
								$this->model('itemstore')->setItemCategories($item->id, $item->category);
							}

						}
					}
				}// /foreach(item)


				// Rebuild the item counts
				$this->model('categorystore')->rebuildItemCounts();
				$this->model('organisationalunitstore')->rebuildItemCounts();
				$this->model('supplierstore')->rebuildItemCounts();
				$this->model('buildingstore')->rebuildItemCounts();


				if (!$errors) {
					Ecl_Helper_Filesystem::deleteFile($procfilename);
	 				$this->layout()->addFeedback(KC__FEEDBACK_WARNING, 'Import successful', '<p>All the imported items have been created successfully. You should review the latest additions to ensure the details look OK.</p><p>To begin browsing your new items, visit your <a href="'. $this->model('app.www') .'">Catalogue Homepage</a>.</p>', $errors);
 				} else {
 					if ($inserted_an_item) {
						$this->layout()->addFeedback(KC__FEEDBACK_WARNING, 'Some data could not be imported', '', $errors);
					} else {
						$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'No items were imported', '', $errors);
					}
				}
				$this->view()->successful_items = $successful_items;
				$this->view()->render('items_importwizard4');
				return;
				break;
			default:
				$this->router()->action('404', 'error');
				break;
		}// /switch

	}// /method



	public function actionIndex() {
		$this->backlink = base64_decode($this->request()->get('backlink'));
		$this->view()->render('items_index');
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	protected function _deleteOldImportFiles() {
		$upload_path = $this->model()->get('app.upload_root') .DIRECTORY_SEPARATOR. 'data';
		$processing_path = $this->model()->get('app.upload_root') .DIRECTORY_SEPARATOR. 'processing';

		$old_files = Ecl_Helper_Filesystem::getFilesOlderThan($upload_path, 24*60*60);
		foreach($old_files as $filename) {
			Ecl_Helper_Filesystem::deleteFile($upload_path .'/'. $filename);
		}

		$old_files = Ecl_Helper_Filesystem::getFilesOlderThan($processing_path, 24*60*60);
		foreach($old_files as $filename) {
			Ecl_Helper_Filesystem::deleteFile($processing_path .'/'. $filename);
		}

		return true;
	}



	protected function _getStandardRowHeaders() {
		return array(
			'item_title' ,
			'manufacturer' ,
			'model' ,
			'short_description' ,
			'full_description' ,
			'specification' ,
			'upgrades' ,
			'future_upgrades' ,
			'acronym' ,
			'keywords' ,
			'tags' ,
			'technique' ,
			'availability' ,
			'restrictions' ,
			'usergroup' ,
			'access' ,
			'portability' ,
			'category' ,
			'organisational_unit' ,
			'site' ,
			'building' ,
			'room' ,
			'contact_1_name' ,
			'contact_1_email' ,
			'contact_2_name' ,
			'contact_2_email' ,
			'visibility' ,
			'manufacturer_website' ,
			'copyright_notice' ,
			'training_required' ,
			'training_provided' ,
			'quantity' ,
			'quantity_detail' ,
			'PAT' ,
			'calibrated' ,
			'last_calibration_date' ,
			'next_calibration_date' ,
			'asset_no' ,
			'finance_id' ,
			'serial_no' ,
			'year_of_manufacture' ,
			'supplier' ,
			'date_of_purchase' ,
			'purchase_cost' ,
			'replacement_cost' ,
			'end_of_life' ,
			'maintenance' ,
			'is_disposed_of' ,
			'date_disposed_of' ,
			'comments' ,
		);
	}// /method



	protected function _getRowHeaders() {
		if (null !== $this->_row_headers) { return $this->_row_headers; }

		$this->_row_headers = $this->_getStandardRowHeaders();

		$custom_fields = $this->model('customfieldstore')->findAll();

		if (count($custom_fields)>0) {
			foreach($custom_fields as $i => $field) {
				$this->_row_headers[] = $field->name;
			}
		}

		return $this->_row_headers;
	}// /method



	protected function _processNewLookupValue($model_name, $value) {

		if (empty($value)) { return false; }

		switch($model_name) {
			case 'access':
				$access = $this->model('accesslevelstore')->newAccessLevel();
				$access->name = $value;
				return $this->model('accesslevelstore')->insert($access);
				break;
			// ----------------------------------------
			case 'building':
				// Note : No site is defined for buildings added this way.
				$building = $this->model('buildingstore')->newBuilding();
				$building->name = $value;
				return $this->model('buildingstore')->insert($building);
				break;
			// ----------------------------------------
			case 'category':
				$category = $this->model('categorystore')->newCategory();
				$category->name = $value;
				return $this->model('categorystore')->insert($category);
				break;
			// ----------------------------------------
			case 'organisational_unit':
				$ou = $this->model('organisationalunitstore')->newDepartment();
				$ou->name = $value;
				return $this->model('organisationalunitstore')->insert($ou, 1);
				break;
			// ----------------------------------------
			case 'site':
				$site = $this->model('sitestore')->newSite();
				$site->name = $value;
				return $this->model('sitestore')->insert($site);
				break;
			// ----------------------------------------
			case 'supplier':
				$supplier = $this->model('supplierstore')->newSupplier();
				$supplier->name = $value;
				return $this->model('supplierstore')->insert($supplier);
				break;
			// ----------------------------------------
			default:
				break;
		}

	}// /method



	/**
	 * Read the contents of a CSV file, process it, and convert it to an array.
	 *
	 * @param  string  $filepath  The file to process.
	 *
	 * @return  array  An array of data.
	 */
	protected function _readCsvFile($filepath) {
		if (!file_exists($filepath)) { return null; }

		$csv_parser = Ecl::factory('Ecl_Parser_Csv', array (
			'parse.assoc' => true ,
			'parse.keep_header_row' => true ,
			'parse.assoc_lower_case' => true ,
		));

		$data_csv = file_get_contents($filepath);
		$data_csv = utf8_encode($data_csv);
		$data_csv = $csv_parser->parse($data_csv);

		$data_csv = Ecl_Helper_array::removeEmptyRows($data_csv);

		return $data_csv;
	}// /method



}// /class
?>
