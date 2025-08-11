<?php


include_once('itemfile.php');
include_once('item.php');
include_once('importitem.php');


/**
 * Item Store class
 *
 * @version 1.0.0
 */
class Itemstore {

	// Private Properties
	protected $_model = null;
	protected $_db = null;

	protected $_cache_customfield_names = null;



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
	 * Build a query suitable for searching the given parameters, and order the results accordingly.
	 *
	 * @param  array  $params
	 * @param  array  $order_by
	 *
	 * @return  string  The SQL requested. On fail, empty string.
	 */
	public function buildSearchQuery($params, $order_by = array('name' => 'ASC')) {
		if (!is_array($params)) { return ''; }

		$filter_join_map = array (
			'category'  => 'INNER JOIN item_category ic ON i.item_id=ic.item_id ' ,
		);

		$order_join_map = array (
			'building'  => 'LEFT JOIN building b ON i.building_id=b.building_id ' ,
			'category'  => 'LEFT JOIN item_category ic ON i.item_id=ic.item_id ' ,
			'ou'        => 'LEFT JOIN ou ON i.ou_id=ou.ou_id ' ,
			'site'      => 'LEFT JOIN site ON i.item_id=s.site_id' ,
			'supplier'  => 'LEFT JOIN supplier s ON i.supplier_id=s.supplier_id ' ,
		);

		$joins = array();

		$sql__join_clause = '';
		$sql__where_clause = '';
		$sql__orderby_clause = '';


		$where_conditions = array();
		$binds = array();

		if (isset($params['category'])) {
			$binds['category_id'] = $params['category'];
			$joins['category'] = $filter_join_map['category'] . ' AND ic.category_id=:category_id';
			unset($params['category']);
		}


		if (isset($params['contact'])) {
			$binds['contact'] = $params['contact'];
			$where_conditions[] = "(contact_1_email=:contact OR contact_2_email=:contact)";
			unset($params['contact']);
		}


		if (isset($params['building_id'])) {
			$sql_set = $this->_db->prepareSet($params['building_id']);
			$where_conditions[] = "(i.building_id IN $sql_set)";
			unset($params['building_id']);
		}


		if (isset($params['ou_id'])) {
			$sql_set = $this->_db->prepareSet($params['ou_id']);
			$where_conditions[] = "(i.ou_id IN $sql_set)";
			unset($params['ou_id']);
		}


		// @todo : Deprecated - remove
		if (isset($params['department'])) {
			$binds['department_id'] = $params['department'];
			$where_conditions[] = '(i.department_id=:department_id)';
			unset($params['department']);
		}


		foreach($params as $k => $v) {
			$sql_val = $this->_db->prepareValue($v);
			$where_conditions[] = "($k=$sql_val)";
		}


		$where_conditions = implode(' AND ', $where_conditions);
		if (!empty($where_conditions)) { $sql__where_clause = "WHERE $where_conditions"; }


		// Process orderby
		if (empty($order_by)) {	$order_by = array('name' => 'ASC');	}

		$order_by_parts = array();

		$orderby_map = array(
			'name'      => "CASE WHEN title<>'' THEN title ELSE manufacturer END" ,
			'building'  => 'b.name' ,
			'ou'        => 'ou.name' ,
			'supplier'  => 's.name' ,
		);

		foreach($order_by as $field => $direction) {
			$direction = ('DESC' == strtoupper($direction)) ? 'DESC' : 'ASC' ;

			if (array_key_exists($field, $orderby_map)) {
				if ( (!array_key_exists($field, $joins)) && (array_key_exists($field, $order_join_map)) ) {
					$joins[$field] = $order_join_map[$field];
				}
				$field = $orderby_map[$field];
			}
			$order_by_parts[] = "$field $direction";
		}

		$sql__orderby_clause = 'ORDER BY '. implode(', ', $order_by_parts);


		// Process joins
		if (!empty($joins)) {
			$sql__join_clause = implode("\n", $joins);
		}

		return $this->_db->prepareQuery("
			SELECT i.*
			FROM item i
				$sql__join_clause
			$sql__where_clause
			$sql__orderby_clause
		", $binds);
	}



	/**
	 * Convert the domain object to an export row.
	 *
	 * This method converts the object to a row of data suitable for inclusion in
	 * an exported file (e.g. CSV).  It is as close as we come to a "dump" of all
	 * information about an entire item.
	 *
	 * WARNING: Involves sub-queries to capture categories and tag information.
	 *
	 * WARNING: Only the name of the main image is included - other uploaded
	 * files/images are ignored.
	 *
	 * @param  object  $object  The object.
	 *
	 * @return  array  The row representing the object.
	 */
	public function convertObjectToExport($object) {
		// cache customfield info
		if (null === $this->_cache_customfield_names) {
			$this->_cache_customfield_names = $this->_model->get('customfieldstore')->findAll();
		}


		$row = array (
			'item_id' => $object->id ,

			'title' => $object->title ,
			'manufacturer' => $object->manufacturer ,
			'model' => $object->model ,

			'short_description' => $object->short_description ,
			'full_description' => $object->full_description ,
			'specification' => $object->specification ,

			'upgrade' => $object->upgrades ,
			'future_upgrade' => $object->future_upgrades ,

			'acronym' => $object->acronym ,
			'keywords' => $object->keywords ,

			'tags'  => implode(", ", $this->getItemTags($object->id)) ,

			'category'  => implode(", ", $this->_model->get('categorystore')->getNamesForItem($object->id)) ,

			'technique' => $object->technique ,

			'usergroup'   => $object->usergroup ,

			'access'      => $this->_model->get('accesslevelstore')->lookupName($object->access, '') ,

			'portability' => $object->portability ,

			'availability'   => $object->availability ,

			'visibility'  => $object->visibility ,

			'ou' => $this->_model->get('organisationalunitstore')->lookupName($object->ou, '') ,

			'site'         => $this->_model->get('sitestore')->lookupName($object->site, '') ,
			'building'     => $this->_model->get('buildingstore')->lookupName($object->building, '') ,
			'room'         => $object->room ,

			'contact_1_name'  => $object->contact_1_name ,
			'contact_1_email'  => $object->contact_1_email ,
			'contact_2_name'  => $object->contact_2_name ,
			'contact_2_email'  => $object->contact_2_email ,

			'image' => $object->image ,

			'manufacturer_website' => $object->manufacturer_website ,
			'copyright_notice' => $object->copyright_notice ,

			'restrictions'   => $object->restrictions ,

			'training_required' => ($object->training_required === true) ? 'yes' : ( ($object->training_required === false) ? 'no' : '' ) ,
			'training_provided' => ($object->training_provided === true) ? 'yes' : ( ($object->training_provided === false) ? 'no' : '' ) ,

			'quantity' => $object->quantity ,
			'quantity_detail' => $object->quantity_detail ,

			'PAT' => $this->_db->formatDate($object->PAT, true) ,

			'calibrated' => $object->calibrated ,
			'last_calibration_date' => $this->_db->formatDate($object->last_calibration_date, true) ,
			'next_calibration_date' => $this->_db->formatDate($object->next_calibration_date, true) ,

			'asset_no'             => $object->asset_no ,
			'finance_id'           => $object->finance_id ,
			'serial_no'            => $object->serial_no ,
			'year_of_manufacture'  => $object->year_of_manufacture ,
			'supplier_id'          => $this->_model->get('supplierstore')->lookupName($object->supplier, '') ,
			'date_of_purchase'     => $this->_db->formatDate($object->date_of_purchase, true) ,

			'purchase_cost' => $object->cost ,
			'replacement_cost' => $object->replacement_cost ,
			'end_of_life' => $this->_db->formatDate($object->end_of_life, true) ,
			'maintenance' => $object->maintenance ,

			'date_added' => $this->_db->formatDate($object->date_added) ,
			'date_updated' => $this->_db->formatDate($object->date_updated) ,
			'last_updated_username' => $object->last_updated_username ,
			'last_updated_email' => $object->last_updated_email ,

			'is_disposed_of' => $object->is_disposed_of ,
			'date_disposed_of' => $this->_db->formatDate($object->date_disposed_of, true) ,

			'comments' => $object->comments ,

			'archived' => ($object->archived) ? 'yes' : 'no' ,
			'date_archived' => $this->_db->formatDate($object->date_archived, true) ,

			'is_parent' => ($object->is_parent) ? 'yes' : 'no' ,
		);

		if (!empty($this->_cache_customfield_names)) {
			$customfield_values = $this->getItemCustomFields($object->id);
			foreach($this->_cache_customfield_names as $customfield) {
				$row[$customfield->name] = (array_key_exists($customfield->id, $customfield_values)) ? $customfield_values[$customfield->id] : '' ;
			}
		}

		// Excel will screw up the display of fields starting with some characters
		// so values with non-alphanumeric characters are prefixed with a space.
		array_walk($row, function(&$v) {
			if ($v && (strlen($v)>0) && (!ctype_alnum($v[0])) ) {
				$v = ' '. $v;
			}
		});

		return $row;
	}// /method



	/**
	 * Convert the domain object to a database row
	 *
	 * @param  object  $object  The object.
	 *
	 * @return  array  The row representing the object.
	 */
	public function convertObjectToRow($object) {
		return array (
			'item_id' => $object->id ,

			'title' => $object->title ,
			'manufacturer' => $object->manufacturer ,
			'model' => $object->model ,

			'short_description' => $object->short_description ,
			'full_description' => $object->full_description ,
			'specification' => $object->specification ,

			'upgrades'        => $object->upgrades ,
			'future_upgrades' => $object->future_upgrades ,

			'acronym' => $object->acronym ,
			'keywords' => $object->keywords ,

			'technique' => $object->technique ,

			'availability' => $object->availability ,
			'restrictions' => $object->restrictions ,

			'usergroup'      => $object->usergroup ,
			'access_id'      => ($object->access) ?: null ,
			'portability'    => $object->portability ,

			'department_id'  => $object->department ,   // @todo : Deprecated - remove
			'organisation' => $object->organisation ,   // @todo : Deprecated - remove

			'ou_id' => $object->ou ,


			'site_id'      => ($object->site) ?: null ,
			'building_id'  => ($object->building) ?: null ,
			'room'         => $object->room ,

			'contact_1_name'  => $object->contact_1_name ,
			'contact_1_email'  => $object->contact_1_email ,
			'contact_2_name'  => $object->contact_2_name ,
			'contact_2_email'  => $object->contact_2_email ,

			'visibility'  => $object->visibility ,

			'image' => $object->image ,

			'embedded_content' => $object->embedded_content,

			'manufacturer_website' => $object->manufacturer_website ,
			'copyright_notice' => $object->copyright_notice ,

			'training_required' => $object->training_required ,
			'training_provided' => $object->training_provided ,

			'quantity' => ($object->quantity) ?: null ,
			'quantity_detail' => $object->quantity_detail ,

			'PAT' => $this->_db->formatDate($object->PAT, true) ,

			'calibrated' => $object->calibrated ,
			'last_calibration_date' => $this->_db->formatDate($object->last_calibration_date, true) ,
			'next_calibration_date' => $this->_db->formatDate($object->next_calibration_date, true) ,

			'date_added' => $this->_db->formatDate($object->date_added) ,
			'date_updated' => $this->_db->formatDate($object->date_updated) ,
			'last_updated_username' => $object->last_updated_username ,
			'last_updated_email' => $object->last_updated_email ,

			'asset_no'             => $object->asset_no ,
			'finance_id'           => $object->finance_id ,
			'serial_no'            => $object->serial_no ,
			'year_of_manufacture'  => $object->year_of_manufacture ,
			'supplier_id'          => $object->supplier ,
			'date_of_purchase'     => $this->_db->formatDate($object->date_of_purchase, true) ,

			'cost' => $object->cost ,
			'replacement_cost' => $object->replacement_cost ,
			'end_of_life' => $this->_db->formatDate($object->end_of_life, true) ,
			'maintenance' => $object->maintenance ,

			'is_disposed_of'   => $object->is_disposed_of ,
			'date_disposed_of' => $this->_db->formatDate($object->date_disposed_of, true) ,

			'archived'      => (int) $object->archived ,
			'date_archived' => $this->_db->formatDate($object->date_archived, true) ,

			'comments' => $object->comments ,

			'is_parent' => (int) $object->is_parent ,
		);
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

		$object->title = $row['title'];
		$object->manufacturer = $row['manufacturer'];
		$object->model = $row['model'];

		$object->short_description = $row['short_description'];
		$object->full_description = $row['full_description'];
		$object->specification = $row['specification'];

		$object->upgrades = $row['upgrades'];
		$object->future_upgrades = $row['future_upgrades'];

		$object->acronym = $row['acronym'];
		$object->keywords = $row['keywords'];

		$object->technique = $row['technique'];

		$object->availability = $row['availability'];
		$object->restrictions = $row['restrictions'];

		$object->usergroup = $row['usergroup'];
		$object->access = $row['access_id'];
		$object->portability = $row['portability'];

		$object->ou = $row['ou_id'];

		$object->organisation = $row['organisation'];   // @todo : Deprecated - to be removed
		$object->department = $row['department_id'];    // @todo : Deprecated - to be removed

		$object->site = $row['site_id'];
		$object->building = $row['building_id'];
		$object->room = $row['room'];

		$object->contact_1_name = $row['contact_1_name'];
		$object->contact_1_email = $row['contact_1_email'];
		$object->contact_2_name = $row['contact_2_name'];
		$object->contact_2_email = $row['contact_2_email'];

		$object->visibility = $row['visibility'];

		$object->image = $row['image'];

		$object->embedded_content = $row['embedded_content'];

		$object->manufacturer_website = $row['manufacturer_website'];
		$object->copyright_notice = $row['copyright_notice'];

		$object->training_required = Ecl_Helper_String::parseBoolean($row['training_required'], null);
		$object->training_provided = Ecl_Helper_String::parseBoolean($row['training_provided'], null);

		$object->quantity = $row['quantity'];
		$object->quantity_detail = $row['quantity_detail'];

		$object->PAT = (is_null($row['PAT'])) ? null : strtotime($row['PAT']) ;

		$object->calibrated = $row['calibrated'];
		$object->last_calibration_date = (is_null($row['last_calibration_date'])) ? null : strtotime($row['last_calibration_date']) ;
		$object->next_calibration_date = (is_null($row['next_calibration_date'])) ? null : strtotime($row['next_calibration_date']) ;

		$object->date_added = (is_null($row['date_added'])) ? null : strtotime($row['date_added']);
		$object->date_updated = (is_null($row['date_updated'])) ? null : strtotime($row['date_updated']);
		$object->last_updated_username = $row['last_updated_username'];
		$object->last_updated_email = $row['last_updated_email'];

		$object->asset_no = $row['asset_no'];
		$object->finance_id = $row['finance_id'];
		$object->serial_no = $row['serial_no'];
		$object->year_of_manufacture = $row['year_of_manufacture'];
		$object->supplier = $row['supplier_id'];
		$object->date_of_purchase = (is_null($row['date_of_purchase'])) ? null : strtotime($row['date_of_purchase']) ;

		$object->cost = $row['cost'];
		$object->replacement_cost = $row['replacement_cost'];
		$object->end_of_life = (is_null($row['end_of_life'])) ? null : strtotime($row['end_of_life']);
		$object->maintenance = $row['maintenance'];

		$object->is_disposed_of  = $row['is_disposed_of'];
		$object->date_disposed_of = (is_null($row['date_disposed_of'])) ? null : strtotime($row['date_disposed_of']);

		$object->archived = (bool) $row['archived'];
		$object->date_archived = (is_null($row['date_archived'])) ? null : strtotime($row['date_archived']);

		$object->comments = $row['comments'];

		$object->is_parent = (bool) $row['is_parent'];

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
		if (empty($visibility)) {
			return '';
		}  elseif (KC__VISIBILITY_PUBLIC == $visibility) {
			return "{$table_alias}visibility='$visibility'";
		}  elseif (KC__VISIBILITY_INTERNAL == $visibility) {
			return '';
		}
	}// /method



	/**
	 * Find the item or items specified.
	 *
	 * @param  mixed  $id  The ID, or array of IDs, to find.
	 *
	 * @return  mixed  The object, or recordset of objects, requested.  On fail, null.
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
	 * @param  mixed  $visibility  (optional)
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	public function findAll($visibility = null) {
		$visibility_clause = $this->getVisibilitySqlCondition($visibility);
		return $this->_find($visibility_clause);
	}// /method



	/**
	 * Get the list of tag used, and the item count for each tag.
	 *
	 * @param  mixed  $visibility  (optional)
	 *
	 * @return  array  An assoc array (tag => item count).
	 */
	public function findAllTags($visibility = null) {
		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause = (!empty($sql__vis_condition)) ? "WHERE {$sql__vis_condition}" : null ;

		$num_rows = $this->_db->query("
			SELECT t.tag, count(it.item_id) AS item_count
			FROM tag t
				INNER JOIN item_tag it ON t.tag_id=it.tag_id
				INNER JOIN item i ON it.item_id=i.item_id
			$where_clause
			GROUP BY tag
			ORDER BY tag ASC
		");

		return ($this->_db->hasResult()) ? $this->_db->getResultAssoc('tag', 'item_count') : array() ;
	}// /method



	/**
	 * Find all the distinct techniques for all equipment.
	 *
	 * @param  integer  $visibility
	 *
	 * @return  Array  An array of techniques.
	 */
	public function findAllTechniques($visibility = null) {
		$sql = "
			SELECT DISTINCT technique
			FROM `item`
			WHERE technique<>''
			ORDER BY technique
		";

		$this->_db->query($sql);
		return ($this->_db->hasResult()) ? $this->_db->getColumn() : array() ;
	}// /method



	/**
	 * Find all the distinct contact emails for all equipment.
	 *
	 * @param  integer  $visibility
	 *
	 * @return  Array  An array of email addresses.
	 */
	public function findAllContacts($visibility) {

		// @idea : Move to own model?

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		if ($sql__vis_condition) { $sql__vis_condition = ' AND '. $sql__vis_condition; }

		$sql = "
			SELECT DISTINCT contact_1_email AS contact_email
			FROM `item`
			WHERE contact_1_email<>'' $sql__vis_condition
			UNION DISTINCT
			SELECT DISTINCT contact_2_email AS contact_email
			FROM `item`
			WHERE contact_2_email<>'' $sql__vis_condition
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

		// @idea : Move to own model?

		$this->_db->query("
			SELECT *
			FROM file_type
			ORDER BY name
		");

		return ($this->_db->hasResult()) ? $this->_db->getResultAssoc('file_type_id', 'name') : array() ;
	}// /method

	/**
	 * Find all the distinct manufacturers for all equipment.
	 *
	 * @param  integer  $visibility
	 *
	 * @return  Array  An array of manufacturers.
	 */
	public function findAllManufacturers($visibility) {

		// @idea : Move to own model?

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		if ($sql__vis_condition) { $sql__vis_condition = ' AND '. $sql__vis_condition; }

		$sql = "
			SELECT DISTINCT manufacturer
			FROM `item`
			WHERE manufacturer<>'' $sql__vis_condition
			ORDER BY manufacturer
		";

		$this->_db->query($sql);
		return ($this->_db->hasResult()) ? $this->_db->getColumn() : array() ;
	}// /method



	public function findAllParents($exclude_item_id = null, $visibility = null) {
		$exclude_item_id = (int) $exclude_item_id;

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		if (!empty($sql__vis_condition)) { $sql__vis_condition = ' AND '. $sql__vis_condition; }

		if ($exclude_item_id) {
			return $this->_find("is_parent='1' AND item_id<>'{$exclude_item_id}' {$sql__vis_condition}");
		} else {
			return $this->_find("is_parent='1' {$sql__vis_condition}");
		}
	}// /method



	public function findChildren($id, $visibility = null) {
		$sql__id = $this->_db->prepareValue( (int) $id);

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause = (!empty($sql__vis_condition)) ? "WHERE {$sql__vis_condition}" : null ;

		return $this->_db->newRecordset("
			SELECT i.*
			FROM item i
				INNER JOIN item_child ic ON i.item_id=ic.child_item_id AND ic.item_id=$sql__id
			$where_clause
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
		", null, array($this, 'convertRowToObject'));
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
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
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
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find any items relating to the custom field specified.
	 *
	 * @param  integer  $field_id
 	 * @param  integer  $visibility  (default: null)
	 *
	 * @return  mixed  The array of objects requested.  On fail, null.
	 */
	public function findForCustomField($field_id, $visibility = null) {
		$binds = array (
			'field_id'   => $field_id ,
		);

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause = (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;

		return $this->_db->newRecordset("
			SELECT i.*
			FROM item i
				INNER JOIN item_custom ic ON i.item_id=ic.item_id
			WHERE field_id=:field_id AND ic.value!='' $where_clause
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find any items with the given value for the custom field specified.
	 *
	 * @param  integer  $field_id
	 * @param  mixed  $value
 	 * @param  integer  $visibility  (default: null)
	 *
	 * @return  mixed  The array of objects requested.  On fail, null.
	 */
	public function findForCustomFieldValue($field_id, $value, $visibility = null) {
		$binds = array (
			'field_id'  => $field_id ,
			'value'     => $value ,
		);

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause = (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;

		return $this->_db->newRecordset("
			SELECT i.*
			FROM item i
				INNER JOIN item_custom ic ON i.item_id=ic.item_id
			WHERE field_id=:field_id
				AND ic.value=:value
				$where_clause
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find any items relating to the tag specified.
	 *
	 * @param  string  $tag  The tag to find.
 	 * @param  integer  $visibility
	 *
	 * @return  mixed  The recordset requested.
	 */
	public function findForTag($tag, $visibility) {
		$binds = array (
			'tag'  => $tag ,
		);

		$this->_db->query('
			SELECT tag_id
			FROM tag
			WHERE tag=:tag
		', $binds);

		if (!$this->_db->hasResult()) { return new Ecl_Db_Emptyrecordset($this->_db, $this->_db->getSql()); }

		$tag_id = $this->_db->getValue();

		$binds = array (
			'tag_id'  => $tag_id ,
		);


		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause = (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;

		return $this->_db->newRecordset("
			SELECT i.*
			FROM item i
				INNER JOIN item_tag it ON i.item_id=it.item_id
			WHERE tag_id=:tag_id
				$where_clause
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
		", $binds, array($this, 'convertRowToObject'));
	}// /method



	public function findParents($id) {
		$sql__id = $this->_db->prepareValue( (int) $id);

		return $this->_db->newRecordset("
			SELECT i.*
			FROM item i
				INNER JOIN item_child ic ON i.item_id=ic.item_id AND child_item_id=$sql__id
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
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
		$sql__contact = $this->_db->prepareValue($email);
		return $this->_find("contact_1_email=$sql__contact OR contact_2_email=$sql__contact");
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
	public function findForDepartmentCategory($department_id, $category_id = null, $visibility = null) {
		$binds = array (
			'department'  => $department_id ,
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
			WHERE department_id=:department
				$where_clause
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
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

		if ( (!empty($letter)) && ($letter != 'Other') ) {
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
	 * Find all items in the organisation specified.
	 *
	 * @param  mixed  $organisation_id  The organisation, or array of organisations, to find.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	public function findForOrganisation($organisation_id, $visibility) {
		if (is_array($organisation_id)) {
			$id_set = $this->_db->prepareSet($organisation_id);
			$where_clause = "organisation IN $id_set";
		} else {
			$sql__organisation_id = $this->_db->prepareValue( (int) $organisation_id);
			$where_clause = "organisation=$sql__organisation_id";
		}


		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause .= (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;


		return $this->_db->newRecordset("
			SELECT *
			FROM item
			WHERE $where_clause
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find any items in the department and category specified.
	 *
	 * @param  integer  $ou_id  The OU to find.
	 * @param  boolean  $include_descendents  Include items from descendent OUs.
 	 * @param  integer  $visibility
	 *
	 * @return  mixed  The array of objects requested.  On fail, null.
	 */
	public function findForOU($ou_id, $include_descendents = true, $visibility = null) {

		$ou_list = array($ou_id);

		if ($include_descendents) {
			$node = $this->_model->get('ou_tree')->findForRef($ou_id);
			if (!$node) { return array(); }

			$descendent_ou_list = $this->_model->get('ou_tree')->findDescendentRefs($node);

			$ou_list = array_merge($ou_list, (array) $descendent_ou_list);
		}

		$sql__ou_set = $this->_db->prepareSet($ou_list);

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$where_clause = (!empty($sql__vis_condition)) ? " AND $sql__vis_condition" : null ;

		return $this->_db->newRecordset("
			SELECT i.*
			FROM item i
			WHERE ou_id IN $sql__ou_set
				$where_clause
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find any items matching the given parameters.
	 *
	 * Parameters are matched using "AND".
	 * e.g.
	 * array (
	 *   'ou'            => 4 ,
	 *   'manufacturer'  => 'IBM' ,
	 * )
	 *
	 * will only find items manufactured by IBM that are also in organisational unit 4.
	 *
	 * @param  array  $params  Assoc array of parameters.
 	 * @param  mixed  $order_by  Assoc array of order parameters and directions.
	 *
	 * @return  mixed  The array of objects requested.  On fail, null.
	 */
	public function findForSearchParams($params, $order_by = array('name' => 'asc'), $row_function = null) {
		$query = $this->buildSearchQuery($params, $order_by);

		if (empty($query)) { return array(); }

		if (null === $row_function) {
			$row_function = array($this, 'convertRowToObject');
		}

		return $this->_db->newRecordset($query, null, $row_function);
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
			ORDER BY
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Find items associated with the given user.
	 *
	 * Finds custodial items and items the user is editor for.
	 *
	 * @param  object  $user  The user to find.
	 *
	 * @return  object  An Ecl_Db_Recordset of objects requested.
	 */
	public function findForUser($user) {
		$binds = array (
			'username' => $user->username,
			'email'    => $user->email,
			);

		return $this->_db->newRecordset("
			(
				SELECT *
				FROM item
				WHERE contact_1_email=:email OR contact_2_email=:email
			)
			UNION DISTINCT
			(
				SELECT i.*
				FROM item i
				INNER JOIN item_editor ie ON ie.item_id=i.item_id AND ie.username=:username
			)
			ORDER BY
			CASE
			  WHEN title<>'' THEN title
			  ELSE manufacturer
			  END ASC, model, acronym
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

		if (0 == $row_count) { return array(); }

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
	 * Get a new instance of an ImportItem object.
	 *
	 * @return  object
	 */
	public function newImportItem() {
		return new importitem();
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
	 * @param  array  $options  Assoc array of search options.
	 *
	 * @return  mixed  The RecordSet of objects found.  On fail, null.
	 */
	public function searchItems($keywords, $visibility, $options = array()) {
		$default_options = array (
			'prioritise_facilities' => false ,   // Facility items are returned first
			'whole_term_only'       => false ,   // Only the entire keyword string is searched for
		);
		$options = array_merge($default_options, $options);


		$items = null;

		$keywords = trim($keywords);

		if (empty($keywords)) { return new Ecl_Db_Emptyrecordset(null, ''); }

		// Remove common wildcard characters
		$keywords = str_replace('*', ' ', $keywords);
		$keywords = str_replace('%', ' ', $keywords);

		// MySQL's default FULLTEXT search has character limitations, so we do things the hard way
		// Grab the terms and run LIKE queries on everything we need.

		$terms = array();
		if ($options['whole_term_only']) {
			if (strlen($keywords)>=2) {
				$terms = array("%{$keywords}%");
			}
		} else {
			$temp_terms = explode(' ', $keywords);

			foreach($temp_terms as $term) {
				$term = trim($term);
				if ( (!empty($term)) && (strlen($term)) >=2) {
					$terms[] = "%{$term}%";
				}
			}
			$temp_terms = null;
		}


		if (empty($terms)) { return new Ecl_Db_Emptyrecordset(null, ''); }


		$queries = array();

		$sql__vis_condition = $this->getVisibilitySqlCondition($visibility);
		$sql__vis_condition = (!empty($sql__vis_condition)) ? "AND $sql__vis_condition" : null ;

		// If the search term is an integer then search for matching Item ID
		if (is_numeric($keywords)) {
			$sql__id = $this->_db->prepareValue((int) $keywords);
			$queries[] = "
				SELECT DISTINCT i.*, 50 AS search_relevancy
				FROM item i
				WHERE (item_id=$sql__id)
					$sql__vis_condition
			";
		}

		// Whole term searching on major fields
		$fields = array('title', 'manufacturer', 'model', 'short_description', 'keywords');
		$conditions = array();
		foreach($fields as $field) {
			$conditions[] = $this->_db->prepareFilter($field, "%{$keywords}%", 'OR', 'LIKE');
		}
		$sql__conditions = implode("\n OR " , $conditions);

		$queries[] = "
			SELECT DISTINCT i.*, 15 AS search_relevancy
			FROM item i
			WHERE ($sql__conditions)
				$sql__vis_condition
		";


		// Search the primary fields
		$fields = array('title', 'manufacturer', 'model', 'acronym', 'keywords');
		$conditions = array();
		foreach($fields as $field) {
			$conditions[] = $this->_db->prepareFilter($field, $terms, 'OR', 'LIKE');
		}
		$sql__conditions = implode("\n OR " , $conditions);

		$queries[] = "
			SELECT DISTINCT i.*, 8 AS search_relevancy
			FROM item i
			WHERE ($sql__conditions)
				$sql__vis_condition
		";


		// Search the secondary fields
		$fields = array('short_description', 'full_description', 'technique');
		$conditions = array();
		foreach($fields as $field) {
			$conditions[] = $this->_db->prepareFilter($field, $terms, 'OR', 'LIKE');
		}
		$sql__conditions = implode("\n OR " , $conditions);

		$queries[] = "
			SELECT DISTINCT i.*, 7 AS search_relevancy
			FROM item i
			WHERE ($sql__conditions)
				$sql__vis_condition
		";


		// Search the categories
		if ($this->_model->get('search.include_categories')) {
			$sql__conditions = $this->_db->prepareFilter('c.name', $terms, 'OR', 'LIKE');

			$queries[] = "
				SELECT DISTINCT i.*, 5 AS search_relevancy
				FROM item i
					INNER JOIN item_category ic ON ic.item_id=i.item_id
					INNER JOIN category c ON c.category_id=ic.category_id
				WHERE ($sql__conditions)
					$sql__vis_condition
			";
		}


		// Search the custodian information
		if ($this->_model->get('search.include_custodians')) {
			$fields = array('contact_1_name', 'contact_1_email', 'contact_2_name', 'contact_2_email');
			$conditions = array();
			foreach($fields as $field) {
				$conditions[] = $this->_db->prepareFilter($field, $terms, 'OR', 'LIKE');
			}
			$sql__conditions = implode("\n OR " , $conditions);

			$queries[] = "
				SELECT DISTINCT i.*, 3 AS search_relevancy
				FROM item i
				WHERE ($sql__conditions)
					$sql__vis_condition
			";
		}


		// Search the organisational units
		if ($this->_model->get('search.include_ou')) {

			if (!$this->_model->get('search.include_ou_descendents')) {
				$sql__conditions = $this->_db->prepareFilter('ou.name', $terms, 'OR', 'LIKE');

				$queries[] = "
					SELECT DISTINCT i.*, 1 AS search_relevancy
					FROM item i
						INNER JOIN ou ON i.ou_id=ou.ou_id
					WHERE ($sql__conditions)
						$sql__vis_condition
				";
			} else {
				$sql__conditions = $this->_db->prepareFilter('name', $terms, 'OR', 'LIKE');

				$this->_db->query("
					SELECT ou_id
					FROM ou
					WHERE $sql__conditions
					ORDER BY ou_id
				");

				if ($this->_db->hasResult()) {
					$ou_list = $this->_db->getColumn();

					$ou_id_list = array();
					foreach($ou_list as $ou_id) {
						$ou_ids = $this->_model->get('ou_tree')->findSubRefsForRef($ou_id);
						if (!empty($ou_ids)) {
							$ou_id_list = array_merge($ou_id_list, $ou_ids);
						}
					}

					$ou_id_list = array_unique($ou_id_list);

					if (!empty($ou_id_list)) {

						$sql__ou_set = $this->_db->prepareSet($ou_id_list);
						$queries[] = "
							SELECT DISTINCT i.*, 1 AS search_relevancy
							FROM item i
							INNER JOIN ou ON i.ou_id=ou.ou_id
							WHERE (ou.ou_id IN $sql__ou_set)
								$sql__vis_condition
						";
					}
				}
			}
		}


		// Search the tags
		if ($this->_model->get('search.include_tags')) {
			$sql__conditions = $this->_db->prepareFilter('t.tag', $terms, 'OR', 'LIKE');

			$queries[] = "
				SELECT DISTINCT i.*, 5 AS search_relevancy
				FROM item i
					INNER JOIN item_tag it ON it.item_id=i.item_id
					INNER JOIN tag t ON t.tag_id=it.tag_id
				WHERE ($sql__conditions)
					$sql__vis_condition
			";
		}


		// Search the custom fields
		if ($this->_model->get('search.include_custom_fields')) {
			$sql__conditions = $this->_db->prepareFilter('icf.value', $terms, 'OR', 'LIKE');

			$queries[] = "
				SELECT DISTINCT i.*, 1 AS search_relevancy
				FROM item i
					INNER JOIN item_custom icf ON icf.item_id=i.item_id
				WHERE ($sql__conditions)
					$sql__vis_condition
			";
		}



		if (1 >= count($queries)) {
			$sql = $queries[0];
		} else {
			$sql = $this->_db->unionise($queries, 'ALL');
		}

		if ($options['prioritise_facilities']) {
			$sql .= "
				ORDER BY is_parent DESC, search_relevancy DESC,
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, manufacturer, model, acronym
			";
		} else {
			$sql .= "
				ORDER BY search_relevancy DESC,
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, manufacturer, model, acronym
			";
		}

		$rows = $this->_db->newRecordset($sql, null)->toArray();

		if (empty($rows)) { return new Ecl_Db_Emptyrecordset(null, ''); }

		// Create a unique recordset
		$unique_ids = array();
		$unique_rows = array();

		foreach($rows as $row) {
			if (!in_array($row['item_id'], $unique_ids)) {
				$unique_rows[] = $row;
				$unique_ids[] = $row['item_id'];
			}
		}
		$rows = null;
		$unique_ids = null;

		return new Ecl_Db_Arrayrecordset($unique_rows, array($this, 'convertRowToObject'));
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
	 * Set the child items associated with the given item.
	 *
	 * @param  integer  $item_id
	 * @param  array  $children  Array of child item IDs.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setItemChildren($item_id, $children) {
		$children = (array) $children;

		$sql__item_id = $this->_db->prepareValue($item_id);
		$this->_db->delete('item_child', "item_id=$sql__item_id");

		if (!empty($children)) {
			$binds = null;
			foreach($children as $child_item_id) {
				$binds[] = array (
					'item_id'       => $item_id ,
					'child_item_id' => $child_item_id ,
				);
			}

			if (!empty($binds)) {
				$this->_db->insertMulti('item_child', $binds);
			}
		}
		return true;
	}// /method



	/**
	 * Set the parents associated with the given item.
	 *
	 * @param  integer  $item_id
	 * @param  array  $parents  Array of parent item IDs.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setItemParents($item_id, $parents) {
		$parents = (array) $parents;

		$sql__item_id = $this->_db->prepareValue($item_id);
		$this->_db->delete('item_child', "child_item_id=$sql__item_id");

		if (!empty($parents)) {
			$binds = null;
			foreach($parents as $parent_item_id) {
				$binds[] = array (
					'item_id'       => $parent_item_id ,
					'child_item_id' => $item_id ,
				);
			}

			if (!empty($binds)) {
				$this->_db->insertMulti('item_child', $binds);
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

		array_walk($tags, function(&$v, $k) {
			$v = preg_replace('/[^A-Za-z0-9\-]+/', '-', $v);
		});

		$tags = array_unique($tags);


		// Delete tag associations
		$sql__item_id = $this->_db->prepareValue($item_id);
		$this->_db->delete('item_tag', "item_id=$sql__item_id");

		// Remove all blank tags
		$temp = $tags;
		$tags = array();
		foreach($temp as $temp_tag) {
			if (!empty($temp_tag)) {
				$tags[] = $temp_tag;
			}
		}

		if (empty($tags)) { return true; }


		// Find tags that already exist, and those that need creating
		$new_binds = array();

		$sql__all_tags_set = $this->_db->prepareSet($tags);

		$this->_db->query("
			SELECT MIN(tag_id) AS tag_id, tag
			FROM tag
			WHERE tag IN $sql__all_tags_set
			GROUP BY tag
		");

		if (!$this->_db->hasResult()) {
			foreach($tags as $tag) {
				$new_binds[] = array (
					'tag'  => $tag ,
				);
			}
		} else {
			$existing_tags = $this->_db->getResultAssoc('tag_id', 'tag');

			foreach($tags as $tag) {
				$found = false;
				foreach($existing_tags as $k => $v) {
					if (strtolower($tag) == strtolower($v)) {
						$found = true;
						break;
					}
				}

				if (!$found) {
					$length = strlen($tag);

					if ( ($length>1) && ('#' == $tag[0]) )  {
						if ($length>2) {
							$tag = substr($tag, 1);
						} else {
							$tag = '';
						}
					}

					if (!empty($tag)) {
						$new_binds[] = array (
							'tag'  => $tag ,
						);
					}
				}
			}// /foreach(tag)
		}


		// Create new tags (if applicable)
		if (!empty($new_binds)) {
			$this->_db->insertMulti('tag', $new_binds, true);
		}


		// Create new item-tag links
		$sql__tag_set = $this->_db->prepareSet($tags);

		$this->_db->execute("
			INSERT INTO `item_tag` (item_id, tag_id)
			SELECT $sql__item_id AS item_id, MIN(tag_id)
			FROM tag
			WHERE tag IN $sql__tag_set
			GROUP BY tag
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

		$this->_db->execute("
			REPLACE INTO item_category (item_id, category_id)
			SELECT item_id, {$target} AS category_id
			FROM item_category
			WHERE category_id={$source}
		");

		$this->_db->delete('item_category', "category_id={$source}");

		return true;
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
	 * Transfer all items from the source organisation, to the target.
	 *
	 * The target organisation ID is not checked for validity.
	 *
	 * @param  integer  $source
	 * @param  integer  $target
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function transferOrganisationItems($source, $target) {
		$source = $this->_db->prepareValue( (int) $source);
		$binds = array (
			'organisation_id'  => (int) $target ,
		);
		$count =  $this->_db->update('item', $binds, "organisation_id={$source}");
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

		$user = $this->_model->get('user');

		unset($binds['date_added']);
		$binds['date_updated'] = $this->_db->formatDate(time());
		$binds['last_updated_username'] = $user->username;
		$binds['last_updated_email'] = $user->email;

		$affected_count = $this->_db->update('item', $binds, "item_id=$id");


		// Log item update
		if ($user) {
			$binds = array (
				'date_updated'  => $this->_db->formatDate(time()) ,
				'item_id'       => $item->id ,
				'username'      => $user->username ,
				'email'         => $user->email ,
			);
			$this->_db->insert('log_item_update', $binds);
		}


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
	protected function _find($where = '', $order_by = null) {

		if (null === $order_by) {
			$order_by = "
				CASE
					WHEN title<>'' THEN title
					ELSE manufacturer
				END ASC, model, acronym
			";
		}


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
