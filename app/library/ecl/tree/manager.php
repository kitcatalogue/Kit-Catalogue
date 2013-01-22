<?php



/**
 * Class for managing tree information stored in a database table.
 *
 * Uses the modified pre-order tree mechanism.
 * Must be linked with a second table containing the node's actual information.
 *
 * @package  Ecl
 * @version  1.0.0
 */
Class Ecl_Tree_Manager {

	// Public properties

	// Private properties
	protected $_db = null;

	protected $_table = '';

	protected $_linked_table = '';      // Linked table (for extra node data)
	protected $_linked_pk_field = '';   // Primary Key field for linked table

	protected $_row_function = null;

	protected $_config = array (
		'ordered'  => false ,  // Order nodes by name when adding
	);



	/**
	 * Constructor
	 */
	public function __construct($db, $table, $config = array()) {
		$this->_db = $db;
		$this->_table = $table;
		$this->_config = array_merge($this->_config, $config);
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Add a node as the child of another.
	 *
	 * All properties of $node except 'name' and 'ref' will be overwritten.
	 *
	 * @param  mixed  $parent
	 * @param  object  $node
	 *
	 * @return  object  The new $node object. On fail, false.
	 */
	public function addChild($parent, $node) {
		$parent = $this->_findNode($parent, true);
		if (empty($parent)) { return false; }

		$left = $this->_locateNewNodeLeft($parent, $node->name);
		if ($left === false) { return false; }

		$node->tree_left  = $left;
		$node->tree_right = $node->tree_left + 1;
		$node->tree_level = $parent->tree_level + 1;

		// Add node to tree
		$this->_makeSpaceForNode($node, 1);
		$node->id = $this->_insertNode($node);

		return ($node->id) ? $node : false ;
	}



	/**
	 * Add a subtree to the given parent.
	 *
	 * The subtree must be an array of tree nodes, properly configured for the sub-tree structure.
	 * The nodes left/right/level positions will be overwritten and adapted to the parent tree
	 * during the addition.
	 *
	 * @param  mixed  $parent
	 * @param  array  $nodes
	 *
	 * return  boolean  The operation was successful.
	 */
	public function addSubTree($parent, $nodes) {
		if (empty($nodes)) { return true; }

		$parent = $this->_findNode($parent, true);
		if (empty($parent)) { return false; }

		// Work out how much space to make in the tree
		$node = clone $nodes[0];

		$left = $this->_locateNewNodeLeft($parent, $node->name);
		if ($left === false) { return false; }

		$node->tree_left = $left;
		$node->tree_right = $parent->tree_left + 1;
		$node->tree_level = $parent->tree_level + 1;

		$node_count = count($nodes);

		// Make space for subtree
		$this->_makeSpaceForNode($node, $node_count);

		// Now add all nodes
		$node_tree_offset = $nodes[0]->tree_left - $left;
		$node_level_offset = $nodes[0]->tree_level;

		foreach($nodes as $node) {
			$node->tree_left = $node->tree_left - $node_tree_offset;
			$node->tree_right = $node->tree_right - $node_tree_offset;
			$node->tree_level = ($node->tree_level - $node_level_offset) + $parent->tree_level + 1;
			$this->_insertNode($node);
		}

		return true;
	}



	public function countChildren($parent) {
		return count($this->findChildren($parent));
	}



	public function countDescendents($parent) {
		$parent = $this->_findNode($parent);
		if (empty($parent)) { return 0; }

		$binds = array(
			'id' => $parent->id ,
		);

		$this->_db->query("
			SELECT ( ( (tree_right - tree_left) -1) / 2) as count
			FROM `{$this->_table}`
			WHERE id=:id
		", $binds);

		return (int) $this->_db->getValue();
	}



	public function convertRowToObject($row) {
		return $this->newNode($row);
	}



	/**
	 * Delete a single leaf node.
	 *
	 * Nodes must not have any children.
	 * @see deleteSubtree()
	 *
	 * @param  mixed  $node
	 *
	 * return  boolean  The operation was successful.
	 */
	public function delete($node) {
		$node = $this->_findNode($node);

		if (empty($node)) { return false; }
		if (0 == $node->tree_level) { return false; }

		$count = $this->countDescendents($node);
		if ($count>0) { return false; }


		$binds = array (
			'old_left'  => $node->tree_left ,
		);

		$this->_db->execute("
			UPDATE `{$this->_table}`
			SET tree_left = tree_left - 2
			WHERE tree_left>=:old_left
		", $binds);

		$this->_db->execute("
			UPDATE `{$this->_table}`
			SET tree_right = tree_right - 2
			WHERE tree_right>=:old_left
		", $binds);

		$sql__node_id = $this->_db->prepareValue($node->id);
		$this->_db->delete($this->_table, "id={$sql__node_id}");

		return true;
	}



	/**
	 * Delete a node and all its descendents.
	 *
	 * @see delete()
	 *
	 * @param  mixed  $node
	 *
	 * return  boolean  The operation was successful.
	 */
	public function deleteSubtree($node) {
		$node = $this->_findNode($node);

		if (empty($node)) { return false; }
		if (0 == $node->tree_level) { return false; }

		// Remove resulting the space from the tree
		$node_count = $this->countDescendents($node) + 1;

		// Delete all nodes
		$this->_db->delete($this->_table, "tree_left>={$node->tree_left} AND tree_right<={$node->tree_right}");


		$binds = array (
			'old_left'  => $node->tree_left ,
			'space'     => $node_count * 2 ,
		);

		$this->_db->execute("
			UPDATE `{$this->_table}`
			SET tree_left = tree_left - :space
			WHERE tree_left>=:old_left
		", $binds);

		$this->_db->execute("
			UPDATE `{$this->_table}`
			SET tree_right = tree_right - :space
			WHERE tree_right>=:old_left
		", $binds);

		return true;
	}



	public function findTree($node = null) {
		if (empty($node)) {
			$binds = null;
			$where = null;
		} else {
			$node = $this->_findNode($node);
			if (empty($node)) { return array(); }

			$binds = array (
				'tree_left'  => $node->tree_left ,
				'tree_right' => $node->tree_right ,
			);

			$where = "WHERE tree_left>=:tree_left
				AND tree_left<=:tree_right
			";

		}

		return $this->_db->newRecordset("
			SELECT *
			FROM `{$this->_table}`
			$where
			ORDER BY tree_left
		", $binds, array($this, 'newNode'))->toArray();
	}



	/**
	 * Find children of the current node.
	 *
	 * Only returns nodes from the level below the given node.
	 * To return all descendent nodes, @see findDescendents()
	 *
	 * @param  mixed  $node  The node, or node id, to check.
	 *
	 * @return  array  The array of child nodes.
	 */
	public function findChildren($node) {
		$node = $this->_findNode($node);
		if (empty($node))  { return array(); }

		$binds = array (
			'tree_left'   => $node->tree_left ,
			'tree_right'  => $node->tree_right ,
			'tree_level'  => $node->tree_level + 1 ,
		);

		$order_by = $this->_getSqlOrderBy();

		$this->_db->query("
			SELECT *
			FROM `{$this->_table}`
			WHERE tree_left>:tree_left
				AND tree_left<:tree_right
				AND tree_level=:tree_level
			$order_by
		", $binds);

		return $this->_db->getResultObjects(array($this, 'convertRowToObject'));
	}



	/**
	 * Find all nodes descended from the given node.
	 *
	 * To return just the direct children, @see findChildren()
	 *
	 * @param  mixed  $node  The node, or node id, to check.
	 *
	 * @return  array  The array of child nodes.
	 */
	public function findDescendents($node) {
		$node = $this->_findNode($node);
		if (empty($node))  { return array(); }

		$binds = array (
			'tree_left'   => $node->tree_left ,
			'tree_right'  => $node->tree_right ,
		);

		$this->_db->query("
			SELECT *
			FROM `{$this->_table}`
			WHERE tree_left>:tree_left
				AND tree_left<:tree_right
		", $binds);

		return $this->_db->getResultObjects(array($this, 'convertRowToObject'));
	}



	public function findAncestors($node) {
		$node = $this->_findNode($node);
		if (empty($node))  { return array(); }

		$binds = array (
			'tree_left'   => $node->tree_left ,
			'tree_right'  => $node->tree_right ,
		);

		$this->_db->query("
			SELECT *
			FROM `{$this->_table}`
			WHERE tree_left<:tree_left
				AND tree_right>:tree_right
			ORDER BY tree_left
		", $binds);

		return $this->_db->getResultObjects(array($this, 'convertRowToObject'));
	}



	public function fetchTreeLinked() {

		if (empty($node)) {
			$binds = null;
			$where = null;
		} else {
			$node = $this->_findNode($node);
			if (empty($node)) { return array(); }

			$binds = array (
				'tree_left'  => $node->tree_left ,
				'tree_right' => $node->tree_right ,
			);

			$where = "WHERE tree_left>=:tree_left
				AND tree_left<=:tree_right
			";

		}

		$sql__select_from = $this->_getSqlSelectFrom();

		return $this->_db->newRecordset("
			$sql__select_from
			$where
			ORDER BY tree_left
		", null, $this->_row_function);
	}



	public function find($id) {
		$binds = array (
			'id'  => $id ,
		);

		$this->_db->query("
			SELECT *
			FROM `{$this->_table}`
			WHERE id=:id
			LIMIT 1
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject'));
	}



	/**
	 * Find the tree node for the given ref value.
	 *
	 * Refs should be unique.  This method returns the first node that contains $ref.
	 *
	 * @param  integer  $ref
	 *
	 * @return  object  The node found.
	 */
	public function findForRef($ref) {
		$binds = array (
			'ref'  => $ref ,
		);

		$this->_db->query("
			SELECT *
			FROM `{$this->_table}`
			WHERE ref=:ref
			LIMIT 1
		", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject'));
	}



	public function findLinkedRecord($id) {
		$sql__select_from = $this->_getSqlSelectFrom();

		if (is_array($id)) {
			$id_set = $this->_db->prepareSet($id);

			return $this->_db->newRecordset("
				$sql__select_from
				WHERE tree_node_id IN $id_set
				ORDER BY tree_node_id ASC
			", null, array($this, '_row_function') );
		} else {

			$binds = array (
				'tree_node_id'  => (int) $id ,
			);

			$row_count = $this->_db->query("
				$sql__select_from
				WHERE id=:id
				LIMIT 1
			", $binds);

			return $this->_db->getObject(array($this, '_row_function') );
		}
	}



	public function findParent($node) {
		$node = $this->_findNode($node);
		if (empty($node)) { return null; }

		// Root has no parents
		if (1 > $node->tree_level) { return null; }

		$binds = array (
			'tree_left'   => $node->tree_left ,
			'tree_right'  => $node->tree_right ,
			'tree_level'   => $node->tree_level - 1 ,
		);

		$this->_db->query("
			SELECT *
			FROM `{$this->_table}`
			WHERE tree_level=:tree_level
				AND tree_left<:tree_left
				AND tree_right>:tree_right
			LIMIT 1
		", $binds);

		return $this->newNode($this->_db->getRow());
	}



	public function findSiblings($node) {
		$node = $this->_findNode($node);
		if (empty($node)) { return array(); }

		$parent = $this->findParent($node);
		if (empty($parent)) { return array(); }

		$binds = array (
			'tree_left'    => $parent->tree_left ,
			'tree_right'   => $parent->tree_right ,
			'tree_level'   => $parent->tree_level + 1 ,
			'node_id'      => $node->id ,
		);

		$order_by = $this->_getSqlOrderBy();

		$this->_db->query("
			SELECT *
			FROM `{$this->_table}`
			WHERE tree_left>:tree_left
				AND tree_left<:tree_right
				AND tree_level=:tree_level
				AND id<>:node_id
			$order_by
		", $binds);

		return $this->_db->getResultObjects(array($this, 'convertRowToObject'));
	}



	public function install() {
		$this->_db->execute("
			CREATE TABLE IF NOT EXISTS `{$this->_table}` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`tree_left` int(10) unsigned NOT NULL,
				`tree_right` int(10) unsigned NOT NULL,
				`tree_level` int(10) unsigned NOT NULL,
				`name` varchar(250) DEFAULT '',
				`ref` int(10) unsigned DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `tree_left` (`tree_left`),
				KEY `tree_right` (`tree_right`),
				KEY `tree_level` (`tree_level`),
				KEY `ref` (`ref`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$root = $this->newNode();
		$root->name = 'Root';
		$root->tree_left = 1;
		$root->tree_right = 2;
		$root->tree_level = 0;

		$this->_insertNodeInfo($root);

		return true;
	}



	public function newNode($assoc = null) {
		$obj = new Ecl_Tree_Node;

		if (empty($assoc)) {
			$obj->id = null;
			$obj->tree_left= null;
			$obj->tree_right = null;
			$obj->tree_level = null;
			$obj->name = '';
			$obj->ref = null;
		} else {
			$obj->id =(int) $assoc['id'];
			$obj->tree_left= (int) $assoc['tree_left'];
			$obj->tree_right = (int) $assoc['tree_right'];
			$obj->tree_level = (int) $assoc['tree_level'];
			$obj->name = $assoc['name'];
			$obj->ref = $assoc['ref'];
		}
		return $obj;
	}



	public function setLinkedTable($table, $pk_field) {
		$this->_linked_table = $table;
		$this->_linked_pk_field = $pk_field;
		return true;
	}



	public function setRowFunction($func) {
		if (empty($func)) {
			$this->_row_function = null;
		} else {
			$this->_row_function = $func;
		}
		return true;
	}



	/**
	 * Transplant a node, and any descendents, to another part of the tree.
	 *
	 * @param  mixed  $node
	 * @param  mixed  $parent
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function transplant($node, $new_parent) {
		$node = $this->_findNode($node);
		if (empty($node)) { return false; }

		$new_parent = $this->_findNode($new_parent, true);
		if (empty($new_parent)) { return false; }

		$subtree = $this->findTree($node);
		$node_count = count($subtree);

		$this->deleteSubtree($node);

		$this->addSubtree($new_parent, $subtree);

		return true;
	}



	public function update($node) {
		$sql__node_id = $this->_db->prepareValue($node->id);
		unbind($node['id']);
		$affected_count = $this->_db->update($this->_table, $binds, "id={$sql__node_id}");

		return ($affected_count>0);;
	}



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	/**
	 * Fetch the requested node from the database.
	 *
	 * If $node is an Ecl_Tree_Node, with an ID, then it will be returned
	 * without querying the database.
	 *
	 * Use $force_refresh to ALWAYS fetch the latest node info from the database.
	 *
	 * @param  mixed  $node
	 * @param  boolean  $force_refresh
	 *
	 * @return  object  The node requested. On fail, null.
	 */
	protected function _findNode($node, $force_refresh = false) {
		if ($node instanceof Ecl_Tree_Node) {
			if (empty($node->id)) {
				return null;
			}

			if ($force_refresh) {
				$node = $node->id;
			} else {
				return $node;
			}
		}

		if (empty($node)) { return null; }

		$binds = array (
			'id'  => (int) $node ,
		);

		$this->_db->query("
			SELECT *
			FROM `{$this->_table}`
			WHERE id=:id
			LIMIT 1
		", $binds);

		if ($this->_db->hasResult()) {
			return $this->newNode($this->_db->getRow());
		}
		return null;
	}



	protected function _getSqlOrderBy() {
		$order_by = ($this->_config['ordered']) ? 'tree_left' : 'name' ;
		return "ORDER BY $order_by";
	}



	protected function _getSqlSelectFrom() {
		if (empty($this->_linked_table)) {
			return "
				SELECT t.*
				FROM `{$this->_table}` t
			";
		} else {
			return "
				SELECT l.*, t.id AS tree_node_id, t.tree_level AS tree_level
				FROM `{$this->_table}` t
					LEFT JOIN `{$this->_linked_table}` l ON t.ref=l.{$this->_linked_pk_field}
			";
		}
	}



	/**
	 * Insert a node into the tree.
	 *
	 * Space for the new node is created automatically made in the existing tree structure.
	 *
	 * @param  object  $node
	 *
	 * @return  integer  The newly inserted Node id.
	 */
	protected  function _insertNode($node) {
		$binds = (array) $node;
		unset($binds['id']);

		return $this->_db->insert($this->_table, $binds);
	}



	/**
	 * Locate the new 'left' position that $name should be in, if inserted below $parent.
	 *
	 * @param  object  $parent
	 * @param  string  $name
	 *
	 * return  integer  The new left location. On fail, false.
	 */
	protected function _locateNewNodeLeft($parent, $name) {
		$parent = $this->_findNode($parent);
		if (empty($parent)) { return false; }

		// Determine tree-left position
		if (!$this->_config['ordered']) { return $parent->tree_right; }

		$children = $this->findChildren($parent);

		if (empty($children)) {	return $parent->tree_right; }
		if ($name < $children[0]->name) { return $children[0]->tree_left; }
		if ($name > end($children)->name) { return $parent->tree_right;	}

		$left = false;
		foreach($children as $child) {
			if ($name < $child->name) {
				$left = $child->tree_left;
				break;
			}
		}
		return $left;
	}



	protected function _makeSpaceForNode($node, $node_count = 1) {
		$node_count = (int) $node_count;

		$binds = array (
			'new_left'  => $node->tree_left ,
			'space'     => $node_count * 2 ,
		);

		$this->_db->execute("
			UPDATE `{$this->_table}`
			SET tree_left = tree_left + :space
			WHERE tree_left>=:new_left
		", $binds);

		$this->_db->execute("
			UPDATE `{$this->_table}`
			SET tree_right = tree_right + :space
			WHERE tree_right>=:new_left
		", $binds);

		return true;
	}



}// /class


