<?php
class Ecl_Db_Migration {

	protected $_db = null;
	protected $_schema = null;
	protected $_params = array();



	public function __construct($db, $schema, $params) {
		$this->_db = $db;
		$this->_schema = $schema;
		$this->_params = (array) $params;
	}



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function down() {
		return false;
	}



	public function getParam($key, $default = null) {
		return (array_key_exists($key, $this->_params)) ? $this->_params[$key] : $default ;
	}



	public function up() {
		return false;
	}



}


