<?php
/*
 * @todo : Perform automatic clean up of upload and processing files
 */
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



	public function zactionCustomise() {
		$this->router()->layout()->addBreadcrumb('Custom Fields', $this->router()->makeAbsoluteUri('/admin/items/customise/'));


		if ($this->request()->isPost()) {
			$errors = false;

			if (!$this->model('user')->checkSessionKey($this->request()->post('session_key'))) {
				$errors[] = 'The form details supplied appear to be forged.';
			}

			$customfield = $this->model('customfieldstore')->newCustomfield();

			$customfield->name = $this->request()->post('name');
			if (empty($customfield->name)) {
				$errors[] = 'You must provide a name for your new field.';
			}

			if ($errors) {
				$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'The following errors were found:', '', $errors);
			} else {
				$new_id = $this->model('customfieldstore')->insert($customfield);

				if ($new_id) {
					$this->layout()->addFeedback(KC__FEEDBACK_SUCCESS, "The field '{$customfield->name}' has been added");
				} else {
					$this->layout()->addFeedback(KC__FEEDBACK_ERROR, 'There was an error adding the field.  Check the field name is unique and try again.');
				}
			}
		}

		$this->view()->custom_fields = $this->model('customfieldstore')->findAll();
		$this->view()->render('items_customise');
	}// /method



	public function actionEdit() {

		$saved_ok = false;

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
				$this->model('itemstore')->delete($item->id);

				// Rebuild cached item counts
				$this->model('categorystore')->rebuildItemCounts();
				$this->model('departmentstore')->rebuildItemCounts();
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

			$item->short_description = $this->request()->post('short_description');
			$item->full_description = $this->request()->post('full_description');
			$item->specification = $this->request()->post('specification');
			$item->technique = $this->request()->post('technique');
			$item->acronym = $this->request()->post('acronym');
			$item->keywords = $this->request()->post('keywords');

			$item->manufacturer_website = $this->request()->post('manufacturer_website');


			// Access & Usage
			$vis = $this->request()->post('visibility');
			//if (in_array($vis, array(KC__VISIBILITY_INTERNAL, KC__VISIBILITY_PUBLIC, KC__VISIBILITY_DRAFT))) {
			if (in_array($vis, array(KC__VISIBILITY_INTERNAL, KC__VISIBILITY_PUBLIC))) {
				$item->visibility = $vis;
			}

			$item->access = $this->request()->post('access');
			$item->availability = $this->request()->post('availability');
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


			// Location

			$allow_newdept = false;
			$allow_selectdept = false;
			$allow_anydept = false;

			$dept_ids = array();

			// If Dept Admin
			if ($this->model('security')->checkAuth(KC__AUTH_CANEDIT)) {
				$allow_selectdept = true;

				$dept_ids = $this->model('security')->findDeptsForAuth(KC__AUTH_CANEDIT);
			}

			// If System Admin
			if ($this->model('security')->checkAuth(KC__AUTH_CANADMIN)) {
				$allow_newdept = true;
				$allow_selectdept = true;
				$allow_anydept = true;
			}

			if ($allow_selectdept) {
				$dept_id = $this->request()->post('department');
				if ( ($allow_anydept) || (in_array($dept_id, $dept_ids)) ) {
					$item->department = $dept_id;
				} else {
					$errors[] = "You are not authorised to associate items with the selected {$this->model->lang['dept.label']}";
				}
			}

			if ($allow_newdept) {
				$new_dept = trim($this->request()->post('new_department'));
				if (!empty($new_dept)) {
					$department = $this->model('departmentstore')->findForName($new_dept);
					if ($department) {
						$item->department = $department->id;
					} else {
						// Create new department, and use it
						$department = $this->model('departmentstore')->newDepartment();
						$department->name = $new_dept;
						$new_id = $this->model('departmentstore')->insert($department);
						if (!$new_id) {
							$errors[] = "Unable to create new {$this->model->lang['dept.label']} : '{$new_dept}'";
						} else {
							$item->department = $new_id;
						}
					}
				}
			}

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

			$item->date_of_purchase = $this->request()->postDmyt('date_of_purchase');
			$item->PAT = $this->request()->postDmyt('PAT');


			$item->copyright_notice = $this->request()->post("copyright_notice");


			// Validate the new item
			if (empty($item->manufacturer)) { $errors[] = 'You must supply a manufacturer\'s name.'; }
			if (empty($item->model)) { $errors[] = 'You must supply a model name or number.'; }
			if (empty($item->visibility)) { $errors[] = 'You must select the visibility level of this item.'; }
			if (empty($item->contact_1_email)) { $errors[] = 'You must enter at least the first staff contact\'s email address.'; }
			if (empty($item->department)) { $errors[] = 'You must select the department in which this item resides.'; }


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
					$deletes = $this->request()->post('delete');
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
							$extension = strtolower(Ecl_Helper_Filesystem::getFileExtension($file->filename));
							if (in_array($extension, $image_ext)) {
								$image_files[] = $file;
							}
						}
					}


					$image_count = count($image_files);
					if (0 == $image_count) {
						$item->image = '';
					} elseif (1 == $image_count) {
						$item->image = $image_files[0]->filename;
					} else {
						$item->image = $this->request()->post('use_image', '');
					}

					// If the item's main image is still blank, but there are images, use the first one
					if ( ('' == $item->image) && ($image_count > 0) ) {
						$item->image = $image_files[0]->filename;
					}


					// Rebuild cached item counts
					$this->model('categorystore')->rebuildItemCounts();
					$this->model('departmentstore')->rebuildItemCounts();
					$this->model('supplierstore')->rebuildItemCounts();

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
				$department_id = $this->request()->post('department');
				if (empty($department_id)) {
					$this->view()->items = $this->model('itemstore')->findAll();
				} else {
					$this->view()->items = $this->model('itemstore')->findForDepartmentCategory($department_id, null);
				}
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
			'department'      => 'departmentstore' ,
			'site'            => 'sitestore' ,
			'supplier'        => 'supplierstore' ,
		);



		if ($this->request()->post('submitback')) {
			if ($step>2) { $step -= 2; }
		}


		switch ($step) {
			// ----------------------------------------------------------------
			case 1 :
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


				// Add proper headers to the CSV data
				$datacsv = array_merge(array ($this->_getRowHeaders()), $datacsv);

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
				$standard_columns_count = count($standard_columns);

				$custom_columns = Ecl_Helper_Array::extractColumn($this->model('customfieldstore')->findAll()->toArray(), 'name', true);

				$column_count = $standard_columns_count + count($custom_columns);


				$items = array();    // Assoc-array of items being imported and their 'issues'
				$are_issues = false;

				foreach($datacsv as $i => $row) {

					// Make sure the rows are of the correct length
					$row = array_pad($row, $column_count, '');

					$issues = null;

					$item = $this->model('itemstore')->newItem();


					// Process each column in turn

					$item->title = Ecl_Helper_String::parseString($row[0], 250);
					$item->manufacturer = Ecl_Helper_String::parseString($row[1], 100);
					if (empty($item->manufacturer)) {
						$issues['manufacturer'] = '';
					}

					$item->model = Ecl_Helper_String::parseString($row[2], 100);
					if (empty($item->model)) {
						$issues['model'] = '';
					}


					$item->short_description = Ecl_Helper_String::parseString($row[3], 250);
					$item->full_description = Ecl_Helper_String::parseString($row[4], 65535);
					$item->specification = Ecl_Helper_String::parseString($row[5], 65535);

					$item->acronym = Ecl_Helper_String::parseString($row[6], 15);
					$item->keywords = Ecl_Helper_String::parseString($row[7], 250);

					// 8 = category
					$temp = Ecl_Helper_String::parseString($row[8], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['category']));
					if (false === $temp_id) {
						$issues['category'] = $temp;
					} else {
						$item->category = $temp_id;
					}

					// 9 = technique
					$temp = Ecl_Helper_String::parseString($row[9], 100);
					if (empty($temp)) {
						$issues['technique'] = '';
					} else {
						$item->technique = $temp;
					}

					// 10 = department
					// We process department here so it is checked first of all
					$temp = Ecl_Helper_String::parseString($row[10], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['department']));
					if (false === $temp_id) {
						$issues['department'] = $temp;
					} else {
						$item->department = $temp_id;
					}

					$item->usergroup = Ecl_Helper_String::parseString($row[11], 250);

					// 12 = access
					$temp = Ecl_Helper_String::parseString($row[12], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['access']));
					if (false === $temp_id) {
						$issues['access'] = $temp;
					} else {
						$item->access = $temp_id;
					}

					$item->availability = Ecl_Helper_String::parseString($row[13], 250);

					// 14 = visibility
					$visibility = Ecl_Helper_String::parseString($row[14], 10);
					if ('public' == strtolower($visibility)) {
						$item->visibility = KC__VISIBILITY_PUBLIC;
					} else {
						$item->visibility = KC__VISIBILITY_INTERNAL;
					}

					// 15 = site
					$temp = Ecl_Helper_String::parseString($row[15], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['site']));
					if (false === $temp_id) {
						$issues['site'] = $temp;
					} else {
						$item->site = $temp_id;
					}

					// 16 = building
					$temp = Ecl_Helper_String::parseString($row[16], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['building']));
					if (false === $temp_id) {
						$issues['building'] = $temp;
					} else {
						$item->building = $temp_id;
					}

					$item->room = Ecl_Helper_String::parseString($row[17], 250);

					$item->contact_1_name = Ecl_Helper_String::parseString($row[18], 250);

					$item->contact_1_email = Ecl_Helper_String::parseString($row[19], 250);
					if (empty($item->contact_1_email)) {
						$issues['contact_1_email'] = '';
					}

					$item->contact_2_name = Ecl_Helper_String::parseString($row[20], 250);
					$item->contact_2_email = Ecl_Helper_String::parseString($row[21], 250);

					$item->manufacturer_website = preg_replace("/^https?:\/\/(.+)$/i","\\1", trim($row[22]));
					$item->copyright_notice = Ecl_Helper_String::parseString($row[23], 250);

					$item->training_required = Ecl_Helper_String::parseBoolean($row[24], null);
					$item->training_provided = Ecl_Helper_String::parseBoolean($row[25], null);

					$item->quantity = Ecl_Helper_String::parseString($row[26], 5);
					$item->quantity_detail = Ecl_Helper_String::parseString($row[27], 250);

					$item->PAT = Ecl_Helper_String::parseDate($row[28], null);

					$item->calibrated = $row[29];

					$temp = strtolower($row[29]);
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

					$item->last_calibration_date = Ecl_Helper_String::parseDate($row[30], null);
					$item->next_calibration_date = Ecl_Helper_String::parseDate($row[31], null);

					$item->asset_no = Ecl_Helper_String::parseString($row[32], 50);
					$item->finance_id = Ecl_Helper_String::parseString($row[33], 50);
					$item->serial_no = Ecl_Helper_String::parseString($row[34], 50);
					$item->year_of_manufacture = Ecl_Helper_String::parseString($row[35], 4);

					// 36 = supplier
					$temp = Ecl_Helper_String::parseString($row[36], 250);
					$temp_id = (array_search(strtolower($temp), $lookups['supplier']));
					if (false === $temp_id) {
						$issues['supplier'] = $temp;
					} else {
						$item->supplier_id = $temp_id;
					}

					$item->date_of_purchase = Ecl_Helper_String::parseDate($row[37], null);


					// Process Custom Fields
					$custom_fields = null;
					$field_num = $standard_columns_count;
					foreach($custom_columns as $j => $header) {
						$custom_fields[$header] = (isset($row[$field_num])) ? Ecl_Helper_String::parseString($row[$field_num], 250) : null ;
						$field_num++;
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
						$list = $this->model($model_name)->findAll()->toAssoc('id', 'name');

						// If the lookup allows blanks for this value, add that as an option
						if (in_array($lookup_name, array('department')) ) {
							$list = array(self::IMPORT_USEWILLFAIL => '-- select a value --') + $list;
						} else {
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
								case 'model':
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


							// Set custom fields
							$custom = array();
							if (!empty($custom_fields)) {
								foreach($custom_fields as $field) {
									if ( (isset($info['custom'][$field->name])) && (!empty($info['custom'][$field->name])) ) {
										$custom[$field->id] = $info['custom'][$field->name];
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
				$this->model('departmentstore')->rebuildItemCounts();
				$this->model('supplierstore')->rebuildItemCounts();


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



	/**
	 * Read the contents of a CSV file, process it, and convert it to an array.
	 *
	 * @param  string  $filepath  The file to process.
	 *
	 * @return  array  An array of data.
	 */
	protected function _readCsvFile($filepath) {
		if (!file_exists($filepath)) { return null; }

		$csv_parser = Ecl::factory('Ecl_Parser_Csv');
		$data_csv = file_get_contents($filepath);
		$data_csv = utf8_encode($data_csv);
		$data_csv = $csv_parser->parse($data_csv);

		$data_csv = Ecl_Helper_array::removeEmptyRows($data_csv);


		// The merging-upwards of partial rows (where manufacturer is blank) has been
		// removed to avoid confusion. Items MUST now exist in a single row of the spread sheet.
		// $data_csv = Ecl_Helper_Array::mergePartialRows($data_csv, 1, "\n");

		return $data_csv;
	}// /method



	protected function _getStandardRowHeaders() {
		return array (
			'item_title',
			'manufacturer',
			'model',
			'short_description',
			'full_description',
			'specification',
			'acronym',
			'keywords',
			'category',
			'technique',
			'department',
			'usergroup',
			'access',
			'availability',
			'visibility',
			'site',
			'building',
			'room',
			'contact_1_name',
			'contact_1_email',
			'contact_2_name',
			'contact_2_email',
			'manufacturer_website',
			'copyright_notice',
			'training_required',
			'training_provided',
			'quantity',
			'quantity_detail',
			'PAT',
			'calibrated',
			'last_calibration_date',
			'next_calibration_date',
			'asset_no',
			'finance_id',
			'serial_no',
			'year_of_manufacture',
			'supplier',
			'date_of_purchase',
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
			case 'department':
				$dept = $this->model('departmentstore')->newDepartment();
				$dept->name = $value;
				return $this->model('departmentstore')->insert($dept);
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



}// /class
?>