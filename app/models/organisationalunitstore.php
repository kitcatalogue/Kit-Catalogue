<?php



include_once('organisationalunit.php');



/**
 * Organisational Unit Store class
 *
 * @version 1.0.0
 */
class Organisationalunitstore {

	// Private Properties
	protected $_db = null;
	protected $_model = null;

	protected $_lookup = null;



	/**
	 * Constructor
	 *
	 * @param  object  $model  An Ecl_Mvc_Model object.
	 * @param  object  $database  An Ecl_Db data access object.
	 */
	public function __construct($model, $database) {
		$this->_model = $model;
		$this->_db = $database;
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Convert a domain object to a database row
	 *
	 * @param  object  $object
	 *
	 * @return  array
	 */
	public function convertObjectToRow($object) {
		$row = array (
			'ou_id'  => $object->id ,
			'name'   => $object->name ,
			'url'    => $object->url ,

			'item_count_internal'  => $object->item_count_internal ,
			'item_count_public'    => $object->item_count_public ,
		);

		return $row;
	}// /method



	/**
	 * Convert a database row to a domain object
	 *
	 * @param  array  $row
	 *
	 * @return  object
	 */
	public function convertRowToObject($row) {
		$object = $this->newOrganisationalunit();

		$object->id = $row['ou_id'];
		$object->name = $row['name'];
		$object->url = $row['url'];

		$object->item_count_internal = $row['item_count_internal'];
		$object->item_count_public = $row['item_count_public'];

		$object->tree_level = (array_key_exists('tree_level', $row)) ? $row['tree_level'] : null ;

		return $object;
	}// /method



	/**
	 * Delete an organisational unit.
	 *
	 * Removes all sub-units from the org tree.
	 *
	 * @param  integer  $id  The record to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($id) {
		$sql__id = $this->_db->prepareValue($id);

		// Delete from the tree
		$ou_tree = $this->_model->get('ou_tree');
		$node = $ou_tree->findForRef($id);

		if (!$node) { return false; }

		$parent = $ou_tree->findParent($node);
		if (!$parent) { return false; }

		if (!$ou_tree->deleteAndPromoteChildren($node)) { return false; }

		$binds = array (
			'ou_id'  => $parent->id ,
		);

		// Update existing items
		$this->_db->update('item', $binds, "ou_id=$sql__id");

		// Delete OU
		$affected_count = $this->_db->delete('ou', "ou_id=$sql__id");

		// Delete related user authorisations
		$sysauth = $this->_model->get('sysauth');
		$sysauth->deleteForItem($id);

		return ($affected_count>0);
	}// /method



	/**
	 * Find the organisational unit(s) specified.
	 *
	 * @param  mixed  $id  The ID, or array of IDs, to find.
	 *
	 * @return  mixed  The object, or array of objects, requested.  On fail, null.
	 */
	public function find($id) {
		if (is_array($id)) {
			$id_set = $this->_db->prepareSet($id);

			return $this->_db->newRecordset("
				SELECT ou.*, ot.tree_level
				FROM ou
					INNER JOIN ou_tree ot ON ou.ou_id=ot.ref
				WHERE ou_id IN $id_set
				ORDER BY ou.name ASC
			", null, array($this, 'convertRowToObject') );
		} else {

			$binds = array (
				'ou_id'  => (int) $id ,
			);

			$row_count = $this->_db->query("
				SELECT ou.*, ot.tree_level
				FROM ou
					INNER JOIN ou_tree ot ON ou.ou_id=ot.ref
				WHERE ou_id=:ou_id
				LIMIT 1
			", $binds);

			return $this->_db->getObject(array($this, 'convertRowToObject') );
		}
	}// /method



	/**
	 * Find all organisational units.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
			SELECT *
			FROM ou
				INNER JOIN ou_tree ot ON ou.ou_id=ot.ref
			ORDER BY ou.name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all organisational units used by at least one item of equipment, for the given visibility.
	 *
	 * @param  integer  $visibility  (optional) The item visibility to check
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAllUsed($visibility = null) {

		$sql__visibility = $this->_db->escapeString($visibility);

		return $this->_db->newRecordset("
			SELECT ou.*
			FROM ou
				INNER JOIN ou_tree ot ON ou.ou_id=ot.ref
				INNER JOIN item i ON ou.ou_id=i.ou
			WHERE (i.visibility & $sql__visibility)=$sql__visibility
			ORDER BY ou.name ASC
		", null, array($this, 'convertRowToObject') );
	}// /method



	public function findChildrenForLeftRight($parent_left, $parent_right) {

		$binds = array(
			'parent_left'  => $parent_left ,
			'parent_right' => $parent_right ,
		);

		return $this->_db->newRecordset("
			SELECT *
			FROM ou
			WHERE tree_left>:parent_left AND tree_right<:parent_right
			ORDER BY name ASC
		", $binds, array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find an organisational unit by its name.
	 *
	 * @param  string  $name
	 *
	 * @return  mixed  The Organisation.  On fail, null.
	 */
	public function findForName($name) {

		$binds = array (
			'name'  => $name ,
		);

		$row_count = $this->_db->query("
			SELECT *
			FROM ou
			WHERE name=:name
			LIMIT 1
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Find all organisational units in tree-order.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findTree() {
		return $this->_model->get('ou_tree')->fetchTreeLinked();
	}// /method



	/**
	 * Find the requested number of OUs, ranked by item usage, for the given visibility.
	 *
	 * @param  integer  $num  The number of results to return.
	 * @param  integer  $visibility  (optional) The item visibility to check.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findUsedRanked($num = 0, $visibility = null) {

		$num = (int) $num;
		$limit_clause = ($num>0) ? "LIMIT $num" : null ;

		switch ($visibility) {
			case KC__VISIBILITY_INTERNAL:
				$where_clause = 'WHERE item_count_internal>0';
				$order_clause = 'ORDER BY item_count_internal DESC, name ASC';
				break;
			case KC__VISIBILITY_PUBLIC:
				$where_clause = 'WHERE item_count_public>0';
				$order_clause = 'ORDER BY item_count_public DESC, name ASC';
				break;
			default:
				$where_clause = null;
				$order_clause = 'ORDER BY name ASC';
				break;
		}// /switch

		return $this->_db->newRecordset("
			SELECT *
			FROM ou
			$where_clause
			$order_clause
			$limit_clause
		", null, array($this, 'convertRowToObject'));
	}// /method



	/**
	 * Lookup the name of a organisational unit using its ID.
	 *
	 * Use find() if you only want a single lookup.
	 * This method uses caching to speed up subsequent lookups, so will be faster if you need more than one.
	 *
	 * @param  integer  $ou_id
	 * @param  string  $default  (optional) The default name to return.
	 *
	 * @return  string  The organisational unit's name.  On fail, $default.
	 */
	public function lookupName($ou_id, $default = '') {
		if (null === $this->_lookup) {
			$this->_lookup = $this->findAll()->toAssoc('id', 'name');
		}
		return (isset($this->_lookup[$ou_id])) ? $this->_lookup[$ou_id] : $default ;
	}// /method



	public function getLevelLabels() {
		return $this->_db->newRecordset("
			SELECT id, name
			FROM ou_tree_label
			ORDER BY id ASC
		")->toAssoc('id', 'name');
	}



	/**
	 * Insert a new organisational unit.
	 *
	 * @param  object  $object  The Organisational Unit to create.
	 * @param  integer  $parent_id  The parent OU for the new OU.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($object, $parent_id) {
		$binds = $this->convertObjectToRow($object);

		unset($binds['ou_id']);

		$ou_tree = $this->_model->get('ou_tree');
		$parent_node = $ou_tree->findForRef($parent_id);
		if (!$parent_node) { return false; }

		$new_id = $this->_db->insert('ou', $binds);

		if ($new_id>0) {
			$node = $ou_tree->newNode();
			$node->name = $binds['name'];
			$node->ref = $new_id;
			$ou_tree->addChild($parent_node, $node);
		}

		return ($new_id>0) ? $new_id : null ;
	}// /method



	/**
	 * Get a new instance of a Organisation object.
	 *
	 * @return  object  A Organisation object.
	 */
	public function newOrganisationalunit() {
		return new Organisationalunit();
	}// /method



	/**
	 * Rebuild all the item counts per organisational unit.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function rebuildItemCounts() {

		$visibility_types = array (
			'internal' => '
					SELECT ou_id, count(item_id) AS count
					FROM item
					GROUP BY ou_id
					ORDER BY ou_id
				' ,
			'public' => '
					SELECT ou_id, count(item_id) AS count
					FROM item
					WHERE visibility = \''. KC__VISIBILITY_PUBLIC .'\'
					GROUP BY ou_id
					ORDER BY ou_id
				' ,
		);


		// Get all the counts for each OU
		$update_info = null;

		foreach($visibility_types as $type => $sql) {
			$row_count = $this->_db->query($sql);

			if ($row_count>0) {
				$counts = $this->_db->getResultAssoc('ou_id', 'count');
				if ($counts) {
					foreach($counts as $ou_id => $item_count) {
						$ou_id = (int) $ou_id;
						$update_info[$ou_id][$type] = $item_count;
					}
				}
			}
		}


		// Reset all the ou item counts to 0
		$binds = array (
			'item_count_internal'  => 0 ,
			'item_count_public'    => 0 ,
		);
		$this->_db->update('ou', $binds);

		// If there are counts to update in the database
		if ($update_info) {

			foreach($update_info as $ou_id => $counts) {
				$binds = array (
					'item_count_internal'  => (isset($counts['internal'])) ? $counts['internal'] : 0 ,
					'item_count_public'    => (isset($counts['public'])) ? $counts['public'] : 0 ,
				);

				$this->_db->update('ou', $binds, "ou_id='$ou_id'");
			}
		}

		return true;
	}// /method



	public function setLevelLabels($assoc) {
		$binds = array();
		foreach($assoc as $id => $name) {
			$binds[] = array (
				'id'   => $id ,
				'name' => $name ,
			);
		}
		return $this->_db->replaceMulti('ou_tree_label', $binds);
	}



	/**
	 * Update an existing organisation.
	 *
	 * @param  object  $object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($object) {
		$binds = $this->convertObjectToRow($object);

		$id = $this->_db->prepareValue($object->id);

		$affected_count = $this->_db->update('ou', $binds, "ou_id=$id");

		if ($affected_count > 0) {
			$ou_tree = $this->_model->get('ou_tree');
			$node = $ou_tree->findForRef($object->id);
			if ($node) {
				$node->name = $binds['name'];
				$ou_tree->update($node);
			}
		}

		return true; //($affected_count>0);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>