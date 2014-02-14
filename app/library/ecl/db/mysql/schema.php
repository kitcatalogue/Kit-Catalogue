<?php
/**
 * MySQL Database Schema Class
 *
 * @package  Ecl
 * @version  1.1.0
 */
class Ecl_Db_Mysql_Schema {

	// Public properties


	// Private properties
	protected $_db = null;

	protected $_default_charset = 'utf8';

	protected $_use_info_schema = false;   // Hard-coded to false, to support versions less than MySQL v5.0



	/**
	 * Constructor
	 *
	 * @param  object  $db  An Ecl_Db_Mysql instance.
	 */
	public function __construct($db) {
		$this->_db = $db;
	} // /method



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	public function addIndex($table, $indexes) {
		if ($this->_use_info_schema) {
			$binds = array (
				'database' => $this->_db->getDatabaseName(),
				'table'    => $table,
				'index'    => $index,
			);

			$this->_db->query("
				SELECT COUNT(*)
				FROM information_schema.statistics
				WHERE table_schema=:database
					AND table_name=:table
					AND index_name=:index
				", $binds);

			if (0 == $this->_db->getValue()) { return false; }
		} else {
			$table_name = $this->_db->prepareTableName($table);
			$this->_db->query("
				SHOW INDEXES FROM {$table_name}
			");

			$indexes = $this->_db->getColumn(2);
			if (!in_array($index, $indexes)) {
				return false;
			}
		}

		$sql__table = $this->_db->prepareTableName($table);
		$sql__index = $this->_db->prepareFieldName($index);

		$this->_db->execute("
			ALTER TABLE $sql__table ADD INDEX $sql__index;
		");

		return true;
	}



	public function createTable($table, $fields, $drop_if_exists = false) {
		$sql__table = $this->_db->prepareTableName($table);

		if (empty($fields)) { return false; }

		if ($drop_if_exists) {
			$this->_db->execute("DROP TABLE IF EXISTS $sql__table");
		} else {
			if($this->tableExists($table)) {
				return $this->morphTable($table, $fields);
			}
		}

		// Process Fields
		$field_sql = array();

		$indices = array();

		foreach($fields as $name => $field_info) {
			$field_sql[] = $this->_getSqlForField(array($name => $field_info));

			if ( (array_key_exists('primary', $field_info)) && (true == $field_info['primary']) ) {
				$indices['primary'][] = $name;
			} else {
				if ( (array_key_exists('key', $field_info)) && (!empty($field_info['key'])) ) {
					if ('UNI' == $field_info['key']) {
						$indices['unique'][] = $name;
					} else {
						$indices['key'][] = $name;
					}
				}
			}
		}


		// Process Indexes
		$index_sql = array();

		if (!empty($indices)) {

			foreach($indices as $type => $field_names) {
				switch ($type) {
					case 'primary':
						$index_sql[] = $this->_getSqlForIndex('primary', $field_names);
						break;
					case 'unique':
					case 'key':
					default:
						foreach($field_names as $field_name) {
							$index_sql[] = $this->_getSqlForIndex($type, $field_name);
						}
						break;
				}
			}
		}


		$sql__definitions = implode(",\n", array_merge($field_sql, $index_sql));

		$this->_db->execute("
			CREATE TABLE $sql__table (
				$sql__definitions
			) DEFAULT CHARSET={$this->_default_charset};
		");

		return true;
	}



	public function dropIndex($table, $index) {
		$binds = array (
			'database' => $this->_db->getDatabaseName(),
			'table'    => $table,
			'index'    => $index,
		);

		if ($this->_use_info_schema) {
			$this->_db->query("
				SELECT COUNT(*)
				FROM information_schema.statistics
				WHERE table_schema=:database
					AND table_name=:table
					AND index_name=:index
				", $binds);

			if (0 == $this->_db->getValue()) { return false; }
		} else {
			$table_name = $this->_db->prepareTableName($table);
			$this->_db->query("
				SHOW INDEXES FROM {$table_name}
			");

			$indexes = $this->_db->getColumn(2);
			if (!in_array($index, $indexes)) {
				return false;
			}
		}

		$sql__table = $this->_db->prepareTableName($table);
		$sql__index = $this->_db->prepareFieldName($index);

		$this->_db->execute("
			ALTER TABLE $sql__table DROP INDEX $sql__index;
		");

		return true;
	}


	public function dropTable($table) {
		if (is_array($table)) {
			array_walk($table, function(&$v) {
				$v = $this->_db->prepareTableName($table);
			});
			$sql__table = implode(', ', $table);
		}
		$sql__table = $this->_db->prepareTableName($table);

		$this->_db->execute("
			DROP TABLE IF EXISTS $sql__table
		");

		return true;
	}



	/**
	 * Get a table's field meta data.
	 *
	 * If $verbose is false, only pertinent field information is returned, so if
	 * a field has the default properties (e.g. not primary key, not auto_increment, etc)
	 * those properties will be ommitted.
	 *
	 * @param  string  $table
	 * @param  boolean  $verbose  Output all field information.
	 *
	 * @return  array  Assoc-array of field information. On fail, empty array.
	 */
	public function getFieldInfo($table, $verbose = true) {
		if ($this->_use_info_schema) {
			$this->_db->query("
				SELECT COLUMN_NAME    as 'Field',
				       COLUMN_TYPE    as 'Type',
				       IS_NULLABLE    as 'Null',
				       COLUMN_KEY     as 'Key',
				       COLUMN_DEFAULT as 'Default',
				       EXTRA          as 'Extra'
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA=:database
					AND TABLE_NAME=:table
			", array (
				'database' => $this->_db->getDatabaseName() ,
				'table'    => $table ,
			));
		} else {
			$table_name = $this->_db->prepareTableName($table);
			$database_name = $this->_db->prepareDatabaseName($this->_db->getDatabaseName());
			$this->_db->query("SHOW COLUMNS FROM {$database_name}.{$table_name}");
		}

		if (!$this->_db->hasResult()) { return array(); }

		$rows = $this->_db->getResult();
		$info = array();

		if ($verbose) {
			foreach($rows as $row) {
				$row = array_change_key_case($row, CASE_LOWER);
				$key = strtoupper($row['key']);

				$info[$row['field']] = array (
					'type'           => $row['type'] ,
					'primary'        => ('PRI' == $key) ,
					'key'            => $key ,
					'null'           => ('YES' == strtolower($row['null'])) ,
					'default'        => $row['default'] ,
					'auto_increment' => (false !== strpos(strtoupper($row['extra']), 'AUTO_INCREMENT')) ,
					'extra'          => $row['extra'] ,
				);
			}
		} else {
			foreach($rows as $row) {
				$row = array_change_key_case($row, CASE_LOWER);

				$info[$row['field']] = array (
					'type'    => $row['type'] ,
					'null'    => ('yes' == strtolower($row['null'])) ,
					'default' => $row['default'] ,
				);

				if (!empty($row['key'])) {
					$key = strtoupper($row['key']);
					if ('PRI' == $key) {
						$info[$row['field']]['primary'] = true;
					}
					$info[$row['field']]['key'] = $key;
				}

				if (!empty($row['extra'])) {
					if (false !== strpos($row['extra'], 'auto_increment')) {
						$info[$row['field']]['auto_increment'] = true;
					}
					$info[$row['field']]['extra'] = $row['extra'];
				}
			}
		}

		return $info;
	}



	/**
	 * Change the entire table structure to match the given field definitions.
	 *
	 * If changes are required, the necessary ALTER TABLE statement will be executed.
	 * Fields not included in the $fields definition will be removed, fields that don't
	 * exist will be created.
	 * Indices are not affected.
	 * To partial updates to certain fields @see patchTable()
	 *
	 * @param  string  $table  The table to morph.
	 * @param  array  $fields  Array of field information.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function morphTable($table, $fields) {
		$sql__table = $this->_db->prepareTableName($table);

		if (empty($fields)) { return false; }

		$org_fields = $this->getFieldInfo($table, true);
		if (empty($org_fields)) {
			return $this->createTable($table, $fields);
		}


		// Process Fields
		$changes = array();
		$sql__columns = '';

		$fields_to_keep = array();


		foreach($fields as $name => $field) {
			$field_sql = $this->_getSqlForField(array ($name => $field));

			$location = '';
			if (array_key_exists('after', $field)) {
				$location = ' AFTER '. $this->_db->prepareFieldName($field['after']);
			}

			$fields_to_keep[] = $name;

			if (!array_key_exists($name, $org_fields)) {
				$changes[] = "ADD COLUMN $field_sql $location";
			} else {
				if (array_key_exists('rename', $field)) {
					if (!array_key_exists($field['rename'], $org_fields)) {
						$fields_to_keep[] = $rename;   // Protect the renamed field from being dropped
						$field_sql = $this->_getSqlForField(array($name => $org_fields[$name]), $field['rename']);
						$changes[] = "CHANGE COLUMN $field_sql $location";
					}
				} else {
					if ($field_sql != $this->_getSqlForField(array($name => $org_fields[$name]))) {
						$changes[] = "MODIFY COLUMN $field_sql $location";
					}
				}
			}
		}// /foreach(given field)

		foreach($org_fields as $name => $field) {
			if (!in_array($name, $fields_to_keep)) {
				$sql__name = $this->_db->prepareFieldName($name);
				$changes[] = "DROP COLUMN $sql__name";
			}
		}


		$sql__definitions = implode(",\n", $changes);


		if (empty($sql__definitions)) { return true; }


		$this->_db->execute("
			ALTER TABLE $sql__table
			$sql__definitions
		");

		return true;
	}



	/**
	 * Change the table structure using the given partial field definitions.
	 *
	 * This is similar to morphTable() but does not drop columns not included in the field definitions.
	 * If changes are required, the necessary ALTER TABLE statement will be executed.
	 * You can rename fields using the 'rename' setting.
	 * Indices are not affected.
	 * @see morphTable()
	 *
	 * @param  string  $table  The table to morph.
	 * @param  array  $fields  Array of field information.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function patchTable($table, $fields) {
		$sql__table = $this->_db->prepareTableName($table);

		if (empty($fields)) { return false; }

		$org_fields = $this->getFieldInfo($table, true);
		if (empty($org_fields)) {
			return $this->create($table, $fields, $indexes);
		}


		// Process Fields
		$changes = array();
		$sql__columns = '';

		foreach($fields as $name => $field) {
			$field_sql = $this->_getSqlForField(array ($name => $field));

			$location = '';
			if (array_key_exists('after', $field)) {
				$location = ' AFTER '. $this->_db->prepareFieldName($field['after']);
			}

			if (!array_key_exists($name, $org_fields)) {
				$changes[] = "ADD COLUMN $field_sql $location";
			} else {
				if (array_key_exists('rename', $field)) {
					$field_sql = $this->_getSqlForField(array($name => $org_fields[$name]), $field['rename']);
					$changes[] = "CHANGE COLUMN $field_sql $location";
				} else {
					if ($field_sql != $this->_getSqlForField(array($name => $org_fields[$name]))) {
						$changes[] = "MODIFY COLUMN $field_sql $location";
					}
				}
			}
		}


		$sql__definitions = implode(",\n", $changes);

		if (empty($sql__definitions)) { return true; }

		$this->_db->execute("
			ALTER TABLE $sql__table
			$sql__definitions
			");

		return true;
	}



	public function renameTable($table, $new_table) {
		$sql__table = $this->_db->prepareTableName($table);
		$sql__newtable = $this->_db->prepareTableName($new_table);

		$this->_db->execute("
			RENAME TABLE $sql__table TO $sql__newtable;
		");

		return true;
	}



	public function setDefaultCharset($charset = 'utf8') {
		$this->_default_charset = $charset;
	}



	public function tableExists($table) {
		if ($this->_use_info_schema) {
			$this->_db->query("
				SELECT TABLE_NAME AS table_name
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA=:database
					AND TABLE_NAME=:table
			", array (
				'database' => $this->_db->getDatabaseName() ,
				'table'    => $table ,
			));
		} else {
			$this->_db->query("
				SHOW TABLES LIKE :table
			", array (
				'table' => $table ,
			));
		}

		return ($this->_db->hasResult());
	}



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



	/**
	 * Get the SQL representation of a single field definition.
	 *
	 * $field must be a single key-value pair of the form:
	 *
	 * array ( 'myfield' => array (
	 *   'type'    => 'varchar(10)' ,
	 *   'primary' => false ,
	 *   'default  => '' ,
	 *   ...
	 * ));
	 *
	 * The corresponding SQL output would be the string:
	 *
	 * "`myfield` varchar(10) DEFAULT ''"
	 *
	 * @param  array  $field  Assoc array of field information.
	 * @param  string  $new_name  New name for the column (for CHANGE COLUMN definitions)
	 *
	 * @return  string  The SQL representation.
	 */
	protected function _getSqlForField($field, $new_name = '') {
		$field_name = key($field);
		$field_info = current($field);

		$sql = $this->_db->prepareFieldName($field_name);
		if (!empty($new_name)) {
			$sql .= ' '. $this->_db->prepareFieldName($new_name);
		}
		$sql .= " {$field_info['type']}";

		$use_default = (array_key_exists('default', $field_info));

		if ( (array_key_exists('null', $field_info)) && (false !== $field_info['null']) ) {
			$sql .= ' NOT NULL';

			if (($use_default) && (null !== $field_info['default'])) {
				$sql .= ' DEFAULT '. $this->_db->prepareValue($field_info['default']);
			}
		} else {
			if ($use_default) {
				$sql .= ' DEFAULT '. $this->_db->prepareValue($field_info['default']);
			}
		}

		if ( (array_key_exists('auto_increment', $field_info)) && (true == $field_info['auto_increment']) ) {
			$sql .= ' AUTO_INCREMENT';
		}

		return $sql;
	}



	public function _getSqlForIndex($type, $field_names) {
		$field_names = (array) $field_names;
		$field_names = array_map(array($this->_db, 'prepareFieldName'), $field_names);

		$sql__fieldlist = implode(', ', $field_names);

		switch ($type) {
			case 'primary':
				$sql = "PRIMARY KEY ($sql__fieldlist)";
				break;
			case 'unique':
				$sql = "UNIQUE KEY ($sql__fieldlist)";
				break;
			case 'key':
			default:
				$sql = "KEY ($sql__fieldlist)";
				break;
		}

		return $sql;
	}


}// /class
?>