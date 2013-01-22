<?php
class Ecl_Tree_Node {

	protected $_id = null;
	protected $_left = null;
	protected $_right = null;
	protected $_level = null;

	protected $_name = '';
	protected $_ref = null;



	public function __construct($id=null, $left=null, $right=null, $level=null, $name = '', $linked_id=null) {
		$this->_id = $id;
		$this->_left = $left;
		$this->_right = $right;
		$this->_level = $level;

		$this->_name = $name;
		$this->_ref = $ref;
	}



	public static function createFromAssoc($assoc) {
		$new = new self();
		$new->_id($assoc['id']);
		$new->_left($assoc['left']);
		$new->_right($assoc['right']);
		$new->_level($assoc['level']);
		$new->setName($assoc['name']);
		$new->setLinkedId($row['linked_id']);
		return $new;
	}


	public static function createFromNameRef($name, $ref) {

	}



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	public function getId() {
		return $this->_id;
	}



	public function getLeft() {
		return $this->_left;
	}



	public function getLevel() {
		return $this->_level;
	}



	public function getName() {
		return $this->_name;
	}


	public function getRef() {
		return $this->_ref;
	}


	public function getRight() {
		return $this->_right;
	}



	public function setName($name) {
		$this->_name = $name;
	}



	public function setRef($ref) {
		$this->_ref = $ref;
	}



	public function toAssoc() {
		return array (
			'id' => $this->_id ,
		);
	}


}