<?php
/**
 * Database Iterator Class
 *
 * Allows you to iterate over the results of a query.
 * Implements lazy-loading.  The SQL query is not executed until the data is requested.
 * Supply a row function to convert database rows at runtime (e.g. to objects).
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Db_Legacy_Recordset implements ArrayAccess, Countable, Iterator {

	// Public properties


	// Private properties
	protected $_db = null;  // The Ecl_Db_Mysql instance to use when querying

	protected $_sql = '';   // The SQL to execute

	protected $_executed = false;   // Has the current SQL been executed?

	protected $_result_set = null;   // The query result set

	protected $_column_names = null;   // Cached column names

	protected $_count = null;   // The number of rows returned

	protected $_position = 0;   // Current iterator position

	protected $_row = false;   // The current row data

	protected $_row_function = null;   // Row conversion function (called on each row returned)



	/**
	 * Constructor
	 *
	 * @param  object  $db  The database object to use when querying.
	 * @param  string  $sql  The SQL to execute.
	 * @param  callback  $row_function  (optional) The row callback function to use to convert rows. (default: null)
	 *
	 * @return  object  A new instance of this class.
	 */
	public function __construct($db, $sql, $row_function = null) {
		$this->_db = $db;
		$this->_sql = $sql;

		$this->_row_function = $row_function;
	}// /method



	public function __destruct() {
		if (is_resource($this->_result_set)) { mysql_free_result($this->_result_set); }
	}// /method



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	/* Interface Methods */



	public function count() {
		if (!$this->_executed) { $this->_execute(); }

		if (!is_null($this->_count)) {
			return $this->_count;
		} else {
			if (!is_resource($this->_result_set)) {
				$this->_count = 0;
			} else {
				$this->_count = (int) mysql_num_rows($this->_result_set);
			}
		}
		return $this->_count;
	}// /method



	public function current() {
		if (!$this->_executed) { $this->_execute(); }
		if ($this->_row_function) {
			$func = $this->_row_function;
			return call_user_func($func, $this->_row);
		} else {
			return $this->_row;
		}
	}// /method



	public function key() {
		return $this->_position;
	}// /method



	public function next() {
		if (!$this->_executed) { $this->_execute(); }
		if ($this->_result_set) {
			$this->_position++;
			$this->_row = mysql_fetch_assoc($this->_result_set);
			return true;
		}
		return false;
	}// /method



	public function offsetSet($offset, $value) {
		// Fake implementation to satisy ArrayAccess Interface
		// Setting recordset entries is a no-no
	}// /method



	public function offsetExists($offset) {
		return ($this->count() > $offset);
	}// /method



	public function offsetUnset($offset) {
		// Fake implementation to satisy ArrayAccess Interface
		// Unsetting recordset entries is a no-no
	}// /method



	public function offsetGet($offset) {
		if (!$this->_executed) { $this->_execute(); }
		if (!$this->offsetExists($offset)) { return null; }

		@mysql_data_seek($this->_result_set, $offset);
		$this->_row = mysql_fetch_assoc($this->_result_set);

		if ($this->_row_function) {
			$func = $this->_row_function;
			return call_user_func($func, $this->_row);
		} else {
			return $this->_row;
		}
	}// /method



	public function rewind() {
		if (!$this->_executed) { $this->_execute(); }
		if ($this->_result_set) {
			$this->_position = 0;
			@mysql_data_seek($this->_result_set, 0);
			$this->_row = mysql_fetch_assoc($this->_result_set);
			return true;
		}
		return false;
	}// /method



	public function valid() {
		if (!$this->_executed) { $this->_execute(); }
		return ($this->_row!==false);
	}// /method



	/* Data Access Methods */



	public function columnNames() {
		if (null !== $this->_column_names) { return $this->_column_names; }

		if (!$this->_result_set) { return array(); }

		$this->_column_names = array();

		$row = $this->offsetGet(0);

		if (isset($row)) {
			$this->_column_names = array_keys( (array) $row);
		} else {
			$field_count = mysql_num_fields($this->_result_set);
			if ($field_count==0) {

			} else {
				for($i=0; $i<$field_count; $i++) {
					$this->_column_names[] = mysql_field_name($this->_result_set, $i);
				}
			}
		}
		return $this->_column_names;
	}// /method



	/**
	 * Return the current row as returned by the database.
	 *
	 * @return  mixed  Assoc-array of row data. On fail, false.
	 */
	public function currentRow() {
		if (!$this->_executed) { $this->_execute(); }
		return $this->_row;
	}// /method


	/**
	 * Get an array representation of the result.
	 *
	 * If the recordset has uses a row callback for object conversion,
	 * then the 'rows' will be objects NOT database fields.
	 *
	 * @return  array  The resulting array. On fail, array()
	 */
	public function toArray() {
		if (!$this->_executed) { $this->_execute(); }

		return iterator_to_array($this, true);
	}// /method



	/**
	 * Get an assoc-array representation from the result.
	 *
	 * If the recordset has uses a row callback for object conversion,
	 * then the key/value columns will be object properties NOT database fields.
	 *
	 * Example output using only $key_column:
	 * array (
	 *   key1 => row1,
	 *   key2 => row2,
	 *   ...
	 * )
	 *
	 * Example output using $key_column and $value_column:
	 * array (
	 *   key1 => value1,
	 *   key2 => value2,
	 *   ...
	 * )
	 *
	 * If a key-column's value is shared by one or more rows, only the last occurence will be returned.
	 * For multiple rows per key-column, use ->toGroupedAssoc().
	 *
	 * @param  string  $key_column
	 * @param  string  $value_column  (optional) (default: null)
	 *
	 * @return  array  The resulting array. On fail, array()
	 */
	public function toAssoc($key_column, $value_column = null) {
		if (!$this->_executed) { $this->_execute(); }

		if (!in_array($key_column, $this->columnNames())) { return array(); }

		$assoc = array();

		// If we want assoc k => row
		if (null === $value_column) {
			foreach($this as $i => $row) {
				$row = (array) $row;
				$assoc[$row[$key_column]] = $row;
			}
		} else {
			if (!in_array($value_column, $this->columnNames())) { return array(); }
			foreach($this as $i => $row) {
				$row = (array) $row;
				$assoc[$row[$key_column]] = $row[$value_column];
			}
		}

		return $assoc;
	}// /method



	/**
	 * Get an array of values for one column in the result.
	 *
	 * If the recordset has uses a row callback for object conversion,
	 * then the 'rows' will be objects NOT database fields.
	 *
	 * @param  string  $column
	 *
	 * @return  array  The resulting array. On fail, array()
	 */
	public function toColumn($column) {
		if (!$this->_executed) { $this->_execute(); }

		if (!in_array($column, $this->columnNames())) { return array(); }

		$col = array();
		foreach($this as $i => $row) {
			$row = (array) $row;
			$col[] = $row[$column];
		}

		return $col;
	}// /method



	/**
	 * Get a custom array of results.
	 *
	 * Calls each row in turn using the give call-back function.
	 * The return value of the function becomes the row value in the result.
	 * For example, you could return a customised array of columns, or a condensed summary of the fields, etc.
	 *
	 * @return  mixed  The result.
	 */
	public function toCustomArray($callable) {
		if (!is_callable($callable)) { return array(); }
		if (!$this->_executed) { $this->_execute(); }

		$result = array();
		foreach($this as $i => $row) {
			$result[] = $callable($row);
		}

		return $result;
	}// /method



	/**
	 * Get an grouped assoc-array representation from the result, using the given key.
	 *
	 * If the recordset has uses a row callback for object conversion,
	 * then the key/value columns will be object properties NOT database fields.
	 *
	 * Example output with only $key_column:
	 * array (
	 *   key1 => array ( row1, row2, ... ) ,
	 *   key2 => array ( row3, ... ) ,
	 *   key3 => array ( row4, row5, ... ) ,
	 *   ...
	 * );
	 *
	 * Example output with $key_column and $value_column:
	 * array (
	 *   key1 => array ( value1, value2, ... ) ,
	 *   key2 => array ( value3, ... ) ,
	 *   key3 => array ( value4, value5, ... ) ,
	 *   ...
	 * );
	 *
	 * @param  string  $key_column
	 * @param  string  $value_column  (optional) (default: null)
	 *
	 * @return  array  The resulting array. On fail, array()
	 */
	public function toGroupedAssoc($key_column, $value_column = null) {
		if (!$this->_executed) { $this->_execute(); }

		if (!in_array($key_column, $this->columnNames())) { return array(); }

		$assoc = array();

		// If we want assoc k => row
		if (is_null($value_column)) {
			foreach($this as $i => $row) {
				$row = (array) $row;
				$assoc[$row[$key_column]][] = $row;
			}
		} else {
			if (!in_array($value_column, $this->columnNames())) { return array(); }
			foreach($this as $i => $row) {
				$row = (array) $row;
				$assoc[$row[$key_column]][] = $row[$value_column];
			}
		}

		return $assoc;
	}// /method



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



	protected function _execute() {
		$this->_db->query($this->_sql);
		$this->_result_set = $this->_db->getResultResource();
		$this->_executed = true;
	}// /method



}// /class
?>