<?php



include_once('itemfile.php');
include_once('item.php');



/**
 * Item Store class
 *
 * @version 1.0.0
 */
class Itemstore {

	// Private Properties
	protected $_model = null;
	protected $_db = null;



	/**
	 * Constructor
	 */
	public function __construct($model) {
		$this->_model = $model;
		$this->_db = $this->_model->get('db');
	}// /->__construct()



	/* ----------------------------------------------------------------------
	 * Public Methods
	 */



	/**
	 * Convert the domain object to a database row
	 *
	 * @param  object  $object  The object.
	 *
	 * @return  array  The row representing the object.
	 */
	public function convertObjectToRow($object) {
		$row = array (
			'item_id'  => $object->id ,

			'manufacturer'       => $object->manufacturer ,
			'model'              => $object->model ,
			'short_description'  => $object->short_description ,
			'full_description'   => $object->full_description ,
			'specification'      => $object->specification ,
			'acronym'            => $object->acronym ,
			'keywords'           => $object->keywords ,

			'technique'      => $object->technique ,
			'availability'   => $object->availability ,
			'department_id'  => $object->department ,
			'usergroup'      => $object->usergroup ,
			'access_id'      => $object->access ,

			'visibility'  => $object->visibility ,

			'site_id'      => $object->site ,
			'building_id'  => $object->building ,
			'room'         => $object->room ,

			'contact_email'  => $object->contact_email ,

			'image'    => $object->image ,
			'manufacturer_website'  => $object->manufacturer_website ,

			'copyright_notice'   => $object->copyright_notice ,
		);

		return $row;
	}// /method



	/**
	 * Convert a database row to a domain object
	 *
	 * @param  array  $row  The database row.
	 *
	 * @return  object  The object the row represents.
	 */
	public function convertRowToObject($row) {
		$object = $this->newItem();

		$object->id = $row['item_id'];

		$object->manufacturer = $row['manufacturer'];
		$object->model = $row['model'];
		$object->short_description = $row['short_description'];
		$object->full_description = $row['full_description'];
		$object->specification = $row['specification'];
		$object->acronym = $row['acronym'];
		$object->keywords = $row['keywords'];

		$object->technique = $row['technique'];
		$object->availability = $row['availability'];

		$object->department = $row['department_id'];
		$object->usergroup = $row['usergroup'];
		$object->access = $row['access_id'];

		$object->visibility = $row['visibility'];

		$object->site = $row['site_id'];
		$object->building = $row['building_id'];
		$object->room = $row['room'];

		$object->contact_email = $row['contact_email'];

		$object->image = $row['image'];
		$object->manufacturer_website = $row['manufacturer_website'];

		$object->copyright_notice = $row['copyright_notice'];

		return $object;
	}// /method



	/**
	 * Delete a item.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$item = $this->find($id);

		// Delete the files available for this item
		$files = $this->findFilesForItem($item);
		if ($files) {
			foreach($files as $filename => $file) {
				$this->deleteFile($item, $file->filename);
			}
		}

		$upload_path = $this->_model->get('app.upload_root'). DIRECTORY_SEPARATOR .'items'. $item->getFilePath();
		if (file_exists($upload_path)) {
			Ecl_Helper_Filesystem::deleteFolder($upload_path, true);
		}

		// delete category entries
		$this->setItemCategories($id, array());

		// delete custom fields
		$this->setItemCustomFields($id, array());

		// delete tags
		$this->setItemTags($id, array());


		// Delete the item itself
		$id = $this->_db->prepareValue($id);
		$affected_count = $this->_db->delete('item', "item_id=$id");

		return ($affected_count>0);
	}// /method



	/**
	 * Delete a file.
	 *
	 * @param  object  $item  The item to delete from.
	 * @param  string  $filename  The file to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function deleteFile($item, $filename) {

		$filename = basename($filename);

		$upload_root = $this->_model->get('app.upload_root'). DIRECTORY_SEPARATOR . 'items'. $item->getFilePath();
		$file_path = $upload_root .'/'. $filename;
		if (Ecl_Helper_FileSystem::isPathBelowRoot($file_path, $upload_root)) {
			Ecl_Helper_FileSystem::deleteFile($file_path);
		}

		$item_id = $this->_db->prepareValue($item->id);
		$filename = $this->_db->prepareValue($filename);

		$affected_count = $this->_db->delete('item_file', "item_id=$item_id AND filename=$filename");

		return ($affected_count>0);
	}// /method



	/**
	 * Create an SQL condition appropriate for the given visibility.
	 *
	 * The condition for internal-visibility will be empty, as all items should be returned.
	 *
	 * @param  integer  $visibility
	 * @param  string  $table_alias  (optional) Any alias the `item` table has in the query.  (default: '')
	 *
	 * @return  string  The SQL condition, which may be an empty string if appropriate.
	 */
	public function getVisibilitySqlCondition($visibility, $table_alias = '') {
		$table_alias = (!empty($table_alias)) ? "{$table_alias}." : null ;
		if (KC__VISIBILITY_PUBLIC == $visibility) {
			return "{$table_alias}visibility='$visibility'";
		} else {
			return '';
		}
	}// /method



	/**
	 * Find the item or items specified.
	 *
	 * @param  mixed  $id  The ID, or array of IDs, to find.
	 *
	 * @return  mixed  The object, or array of objects, requested.  On fail, null.
	 */
	public function find($id) {
		if (is_array($id)) {
			$sql__id_set = $this->_db->prepareSet($id);

			return $this->_find("item_id IN $sql__id_set");
		} else {
			$sql__id = $this->_db->prepareValue( (int) $id);

			$this->_db->query("
				SELECT *
				FROM item
				WHERE item_id=$sql__id
				LIMIT 1
			");
			return $this->_db->getObject(array($this, 'convertRowToObject'));
		}
	}// /method



	/**
	 * Find all items.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	public function findAll() {
		return $this->_find();
	}// /method



	/**
	 * Find all the distinct contact emails for all equipment.
	 *
	 * @param  integer  $visibility
	 *
	 * @return  Array  An array of email addresses.
	 */
	public function findAllContacts($visibility) {

		// @todo : Move to own model?

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		if ($sql__vis_condition) { $sql__vis_condition = ' AND '. $sql__vis_condition; }

		$sql = "
			SELECT DISTINCT contact_email
			FROM `item`
			WHERE contact_email<>'' $sql__vis_condition
			ORDER BY contact_email
		";

		$this->_db->query($sql);
		return ($this->_db->hasResult()) ? $this->_db->getColumn() : array() ;
	}// /method



	/**
	* Get the file types available.
	*
	* @return  mixed  The array of objects requested.
	*/
	public function findAllFileTypes() {

		// @todo : Move to own model?

		$this->_db->query("
				SELECT *
				FROM file_type
				ORDER BY name
			");

		return ($this->_db->hasResult()) ? $this->_db->getResultAssoc('file_type_id', 'name') : array() ;
	}// /method



	/**
	 * Find file counts for items.
	 *
	 * @param  array  $item_ids  The items to check.
	 *
	 * @return  mixed  An assoc-array ( item_id => file-count ).  On fail, null.
	 */
	public function findFileCountsForItems($item_ids) {
		$file_counts = null;

		$item_id_set = $this->_db->prepareSet($item_ids);

		$sql = "
			SELECT item_id, COUNT(filename)
			FROM `item_file`
			WHERE item_id IN $item_id_set
			GROUP BY item_id
			ORDER BY item_id
		";

		$this->_db->query($sql);
		if ($this->_db->hasResult()) {
			$file_counts = $this->_db->getResultAssoc();
		}

		return $file_counts;
	}// /method



	/**
	 * Find the files associated with the given item.
	 *
	 * @param  integer  $item_id  The item to use.
	 *
	 * @return  mixed  The object, or array of objects, requested.  On fail, null.
	 */
	public function findFilesForItem($item) {
		$files = null;

		if (is_object($item)) {
			$item_path = $this->_model->get('app.upload_root').'/items' . $item->getFilePath();
			$physical_files = Ecl_Helper_FileSystem::getFiles($item_path);
			if ($physical_files) {

				$binds = array(
					'item_id'  => $item->id ,
				);

				$sql = "
					SELECT *
					FROM `item_file`
					WHERE item_id=:item_id
				";

				$row_count = $this->_db->query($sql, $binds);

				$db_files = ($row_count) ? $this->_db->getResult() : array() ;

				foreach($physical_files as $filename) {
					$file = new ItemFile();
					$file->item_id = $item->id;
					$file->filename = $filename;

					// Find any settings for this file, and set them appropriately
					if (!empty($db_files)) {
						$row_file = Ecl_Helper_Array::search($filename, $db_files, 'filename');
						if (!is_null($row_file)) {
							$file->type = $row_file['file_type'];
							$file->name = $row_file['name'];
						}
					}
					$files[$file->filename] = $file;
				}// /foreach(physical file)

			}// /if(physical files)
		}

		return $files;
	}// /method



	/**
	 * Find all items in the building specified.
	 *
	 * @param  mixed  $building_id  The building, or array of buildings, to find.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	public function findForBuilding($building_id, $visibility) {
		if (is_array($building_id)) {
			$id_set = $this->_db->prepareSet($building_id);
			$where_clause = "$building_id IN $id_set";
		} else {
			$sql__building_id = $this->_db->prepareValue( (int) $building_id);
			$where_clause = "building_id=$sql__building_id";
		}


		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause .= (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;


		return $this->_db->newRecordset("
			SELECT *
			FROM item
			WHERE $where_clause
			ORDER BY manufacturer, model, acronym
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find all items in the category specified.
	 *
	 * @param  mixed  $category_id  The category, or array of categories, to find.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	public function findForCategory($category_id, $visibility) {
		if (is_array($category_id)) {
			$id_set = $this->_db->prepareSet($category_id);
			$where_clause = "ic.category_id IN $id_set";
		} else {
			$sql__category_id = $this->_db->prepareValue( (int) $category_id);
			$where_clause = "ic.category_id=$sql__category_id";
		}


		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause .= (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;


		return $this->_db->newRecordset("
			SELECT *
			FROM item i
				INNER JOIN item_category ic ON i.item_id=ic.item_id
			WHERE $where_clause
			ORDER BY i.manufacturer, i.model, i.acronym
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find items for the given contact person.
	 *
	 * @param  string  $email  The email of the contact to find.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	public function findForContact($email) {
		$sql__contact = $this->_db->prepareValue($person_id);
		return $this->_find("staff_contact=$sql__contact");
	}// /method



	/**
	 * Find any items in the department and category specified.
	 *
	 * @param  integer  $department_id  The department to find.
	 * @param  integer  $category_id  The category to find.
 	 * @param  integer  $visibility
	 *
	 * @return  mixed  The array of objects requested.  On fail, null.
	 */
	public function findForDepartmentCategory($department_id, $category_id = null, $visibility) {
		$binds = array (
			'department'   => $department_id ,
		);

		$join_clause = null;
		if (null !== $category_id) {
			$binds['category_id'] = $category_id;
			$join_clause .= 'INNER JOIN item_category ic ON i.item_id=ic.item_id  AND ic.category_id=:category_id';
		}

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause = (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;

		return $this->_db->newRecordset("
			SELECT i.*
			FROM item i
				$join_clause
			WHERE department_id=:department $where_clause
			ORDER BY manufacturer, model
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find any items where the manufacturer starts with the given letter.
	 *
	 * @param  string  $letter
	 * @param  integer  $visibility
	 *
	 * @return  mixed  The array of objects requested.  On fail, null.
	 */
	public function findForManufacturerLetter($letter, $visibility) {

		if ( (!empty($letter)) && ('Other' != $letter) ) {
			$binds = array (
				':letter'      => $letter ,
			);
			$where_clause = 'WHERE UPPER(LEFT(manufacturer, 1))=:letter';
		} else {
			$binds = null;
			$where_clause = "WHERE UPPER(LEFT(manufacturer, 1)) NOT BETWEEN 'A' AND 'Z'";
		}

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause .= (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;

		return $this->_db->newRecordset("
			SELECT *
			FROM item
			$where_clause
			ORDER BY manufacturer, model, acronym
		", $binds, array($this, 'convertRowToObject'));
	}// /method




	/**
	 * Find all items in the site specified.
	 *
	 * @param  mixed  $site_id  The site, or array of sites, to find.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	public function findForSite($site_id, $visibility) {
		if (is_array($site_id)) {
			$id_set = $this->_db->prepareSet($site_id);
			$where_clause = "site_id IN $id_set";
		} else {
			$sql__site_id = $this->_db->prepareValue( (int) $site_id);
			$where_clause = "site_id=$sql__site_id";
		}


		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause .= (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;


		return $this->_db->newRecordset("
			SELECT *
			FROM item
			WHERE $where_clause
			ORDER BY manufacturer, model, acronym
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find public items in the category specified.
	 *
	 * @param  integer  $category_id  The category to find.
	 *
	 * @return  mixed  The array of objects requested.  On fail, null.
	 */
	public function findPublicForCategory($category_id) {
		$binds = array (
			'category_id'  => $category_id ,
			'visibility'   => KC__VISIBILITY_PUBLIC ,
		);

		return $this->_db->newRecordset("
			SELECT i.*
			FROM item i
				INNER JOIN item_category ic ON i.item_id=ic.item_id AND ic.category_code=:category_id
			WHERE visibility=:visibility
			ORDER BY $order_by
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find public items in the department and category specified.
	 *
	 * @param  integer  $department_id  The department to find.
	 * @param  integer  $category_id  The category to find.
	 *
	 * @return  mixed  The array of objects requested.  On fail, null.
	 */
	public function findPublicForDepartmentCategory($department_id, $category_id) {
		$binds = array (
			'category_id'  => $category_id ,
			'department'   => $department_id ,
			'visibility'   => KC__VISIBILITY_PUBLIC ,
		);

		return $this->_db->newRecordset("
			SELECT i.*
			FROM item i
				INNER JOIN item_category ic ON i.item_id=ic.item_id AND ic.category_code=:category_id
			WHERE department=$department AND visibility=:visibility
			ORDER BY $order_by
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find the A-to-Z alphabetic list of manufactuers used in the catalogue
	 *
	 * All numbers and punctuation found as the first character of a manufacturer's name will cause the 'Other'
	 * item to appear in the results.
	 *
	 * @param  integer  $visibility
	 *
	 * @return  array  The array of letters found.
	 */
	public function findUsedAToZ($visibility) {
		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause = (!empty($sql__vis_condition)) ? "WHERE $sql__vis_condition" : null ;

		$row_count = $this->_db->query("
			SELECT DISTINCT UPPER(SUBSTRING(manufacturer, 1, 1)) AS letter
			FROM item
			$where_clause
			ORDER BY letter
		");


		if ($row_count>0) {
			$atoz = $this->_db->getColumn();

			$org_count = count($atoz);
			$atoz = array_filter($atoz, function ($v) {
				$ord = ord($v);
				return ( ($ord>=65) && ($ord<=90) );
			});

			if (count($atoz)<$org_count) {
				$atoz = array_merge($atoz);
				$atoz[] = 'Other';
			}

			return $atoz;
		} else {
			return array();
		}
	}// /method



	/**
	 * Insert a new item.
	 *
	 * @param  object  $item  The item to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($item) {
		$binds = $this->convertObjectToRow($item);

		unset($binds['item_id']);   // Don't insert the id, we want a new one

		$binds['date_added'] = $this->_db->formatDate(time());
		$binds['date_updated'] = $this->_db->formatDate(time());

		$new_id = $this->_db->insert('item', $binds);

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a Item object.
	 *
	 * @return  object
	 */
	public function newItem() {
		return new Item();
	}// /method



	/**
	 * Get a new instance of a Itemfile object.
	 *
	 * @return  object
	 */
	public function newItemFile() {
		return new ItemFile();
	}// /method



	/**
	 * Search items for the given keywords
	 *
	 * @param  string  $keywords  The terms to search for.
	 * @param  integer  $visibility  The visibility of the items to search for.
	 *
	 * @return  mixed  The array of objects found.  On fail, null.
	 */
	public function searchItems($keywords, $visibility) {
		$items = null;

		// @todo : Add category search, and show items with matching categories

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility, 'i');
		$sql__vis_condition = (!empty($sql__vis_condition)) ? "AND $sql__vis_condition" : null ;

		if (empty($keywords)) {
			return array();
		} else {
			// Strip out any commas
			$keywords = str_replace(',','', $keywords);
			$keywords = str_replace('"','', $keywords);

			$keywords = '%'. $keywords .'%';

			$sql__keywords = $this->_db->prepareValue($keywords);

			return $this->_db->newRecordset("
				SELECT i.*
				FROM item i
				WHERE (
					manufacturer LIKE $sql__keywords
					OR model LIKE $sql__keywords
					OR technique LIKE $sql__keywords
					OR acronym LIKE $sql__keywords
					OR keywords LIKE $sql__keywords
					OR full_description LIKE $sql__keywords
					)
					$sql__vis_condition
				ORDER BY manufacturer, model
			", null, array($this, 'convertRowToObject'));
		}
	}// /method



	/**
	 * Set the categories associated with the given item.
	 *
	 * @param  integer  $item_id
	 * @param  array  $categories
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setItemCategories($item_id, $categories) {
		$categories = (array) $categories;

		$sql__item_id = $this->_db->prepareValue($item_id);
		$this->_db->delete('item_category', "item_id=$sql__item_id");

		if (!empty($categories)) {
			$binds = null;
			foreach($categories as $k => $v) {
				$binds[] = array (
					'item_id'      => $item_id ,
					'category_id'  => $v ,
				);
			}

			if (!empty($binds)) {
				$this->_db->insertMulti('item_category', $binds);
			}
		}
		return true;
	}// /method



	/**
	 * Set the tags associated with the given item.
	 *
	 * Passing an empty $tags array will effectively delete all existing item-tag links.
	 *
	 * @param  integer  $item_id
	 * @param  array  $tags
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setItemTags($item_id, $tags) {
		$tags = array_map('trim', (array) $tags);

		$sql__item_id = $this->_db->prepareValue($item_id);


		// Delete existing tags
		$this->_db->delete('item_tag', "item_id=$sql__item_id");

		// Create new tags as required
		if (empty($tags)) { return true; }

		$binds = null;
		foreach($tags as $tag) {
			$binds[] = array (
				'tag'  => $tag ,
			);
		}

		if (!empty($binds)) {
			$this->_db->insertMulti('tag', $binds, true);
		}

		// Create new item-tag links
		$sql__tag_set = $this->_db->prepareSet($tags);

		$this->_db->execute("
			INSERT INTO `item_tag` (item_id, tag_id)
			SELECT $sql__item_id AS item_id, tag_id
			FROM `tag`
			WHERE tag IN $sql__tag_set
		");

		return true;
	}// /method



	/**
	 * Set the catalogue information for a particular file.
	 *
	 * @param  object  $file  The file to save.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setFileInfo($file) {

		$binds = array(
			'item_id'       => $file->item_id ,
			'filename'      => $file->filename ,
			'file_type'     => $file->type ,
			'name'          => $file->name ,
		);

		$this->_db->replace('item_file', $binds);

		return true;
	}// /method



	/**
	 * Transfer all items from the source building, to the target.
	 *
	 * The target building ID is not checked for validity.
	 *
	 * @param  integer  $source
	 * @param  integer  $target
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function transferBuildingItems($source, $target) {
		$source = $this->_db->prepareValue( (int) $source);
		$binds = array (
			'building_id'  => (int) $target ,
		);
		$count =  $this->_db->update('item', $binds, "building_id={$source}");
		return ($count>0);
	}// /method



	/**
	 * Transfer all items from the source category, to the target.
	 *
	 * The target category ID is not checked for validity.
	 *
	 * @param  integer  $source
	 * @param  integer  $target
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function transferCategoryItems($source, $target) {
		$source = $this->_db->prepareValue( (int) $source);
		$binds = array (
			'category_id'  => (int) $target ,
		);
		$count =  $this->_db->update('item_category', $binds, "category_id={$source}");
		return ($count>0);
	}// /method



	/**
	 * Transfer all items from the source department, to the target.
	 *
	 * The target department ID is not checked for validity.
	 *
	 * @param  integer  $source
	 * @param  integer  $target
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function transferDepartmentItems($source, $target) {
		$source = $this->_db->prepareValue( (int) $source);
		$binds = array (
			'department_id'  => (int) $target ,
		);
		$count =  $this->_db->update('item', $binds, "department_id={$source}");
		return ($count>0);
	}// /method



	/**
	 * Transfer all items from the source site, to the target.
	 *
	 * The target site ID is not checked for validity.
	 *
	 * @param  integer  $source
	 * @param  integer  $target
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function transferSiteItems($source, $target) {
		$source = $this->_db->prepareValue( (int) $source);
		$binds = array (
			'site_id'  => (int) $target ,
		);
		$count =  $this->_db->update('item', $binds, "site_id={$source}");
		return ($count>0);
	}// /method



	/**
	 * Update the existing Item.
	 *
	 * @param  object  $item  The Item to update.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($item) {
		$binds = $this->convertObjectToRow($item);

		$id = $this->_db->prepareValue($item->id);

		$binds['date_updated'] = $this->_db->formatDate(time());

		$affected_count = $this->_db->update('item', $binds, "item_id=$id");

		return ($affected_count>0);
	}// /method



	/* --------------------------------------------------------------------------------
	 * Custom Field Methods
	 */



	/**
	 * Add a custom field.
	 *
	 * @param  string  $field_name  The field to add.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addCustomField($field_name) {

		$binds = array (
			'name'  => $field_name ,
		);

		// Check if field already exists, before adding it

		$num_rows = $this->_db->query('
			SELECT field_id
			FROM custom_field
			WHERE name=:name
		', $binds);

		if ($num_rows>0) {
			return null;
		} else {
			$new_id = $this->_db->insert('custom_field', $binds);
		}

		return (!empty($new_id));
	}// /method



	/**
	 * Delete one or more custom fields from the system.
	 *
	 * Clears all data stored in the given fields.
	 *
	 * @param  mixed  $field_id  The id, or array of ids, to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function deleteCustomFields($field_id) {
		$sql_field_set = $this->_db->prepareSet($field_id);
		$this->_db->delete('item_custom', "field_id IN $sql_field_set");
		$this->_db->delete('custom_field', "field_id IN $sql_field_set");
		return true;
	}



	/**
	 * Get the custom fields available.
	 *
	 * @return  mixed  The object, or array of objects, requested.  On fail, null.
	 */
	public function getCustomFields() {
		$this->_db->query("
			SELECT *
			FROM custom_field
			ORDER BY field_id
		");

		return ($this->_db->hasResult()) ? $this->_db->getResultAssoc('field_id', 'name') : array() ;
	}// /method



	/**
	 * Get the custom fields associated with the given item.
	 *
	 * @param  integer  $item_id
	 *
	 * @return  array  Assoc-array of field IDs and current values.
	 */
	public function getItemCustomFields($item_id) {

		$binds = array (
			'item_id'  => $item_id ,
		);

		$this->_db->query("
			SELECT field_id, value
			FROM item_custom
			WHERE item_id=:item_id
			ORDER BY field_id ASC
		", $binds);

		return ($this->_db->hasResult()) ? $this->_db->getResultAssoc('field_id', 'value') : array() ;
	}// /method



	/**
	 * Get the tags associated with the given item.
	 *
	 * @param  integer  $item_id
	 *
	 * @return  array  Assoc-array of tags.
	 */
	public function getItemTags($item_id) {
		$binds = array (
			'item_id'  => $item_id ,
		);

		$this->_db->query("
			SELECT tag
			FROM item_tag it INNER JOIN tag t ON it.tag_id=t.tag_id AND it.item_id=:item_id
			ORDER BY tag ASC
		", $binds);

		return ($this->_db->hasResult()) ? $this->_db->getColumn('tag') : array() ;
	}// /method



	/**
	 * Set the custom fields associated with the given item.
	 *
	 * @param  integer  $item_id
	 * @param  array  $fields  Assoc-array of fields and their values.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setItemCustomFields($item_id, $fields) {

		// Delete existing custom fields for this item
		$sql_item_id = $this->_db->prepareValue($item_id);
		$this->_db->delete('item_custom', "item_id=$sql_item_id");

		if (!empty($fields)) {
			$binds = null;
			foreach($fields as $k => $v) {
				if (!empty($v)) {
					$binds[] = array (
						'item_id'   => $item_id ,
						'field_id'  => $k ,
						'value'     => $v ,
					);
				}
			}

			if (!empty($binds)) {
				$this->_db->insertMulti('item_custom', $binds);
			}
		}
		return true;
	}// /method



	/* ----------------------------------------------------------------------
	 * Private Methods
	 */



	/**
	 * Find all items using the given where clause.
	 *
	 * @param  string  $where  (optional) The where clause to use.
	 * @param  string  $order_by  (optional) The order-by clause to use.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	protected function _find($where = '', $order_by = 'manufacturer, model') {

		if (empty($where)) {
			return $this->_db->newRecordset("
				SELECT *
				FROM item
				ORDER BY $order_by
			", null, array($this, 'convertRowToObject'));
		} else {
			return $this->_db->newRecordset("
				SELECT *
				FROM item
				WHERE $where
				ORDER BY $order_by
			", null, array($this, 'convertRowToObject'));
		}
	}// /method



}// /class
?>