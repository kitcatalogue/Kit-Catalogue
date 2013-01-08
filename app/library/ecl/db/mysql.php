<?php
/**
 * MySQL Database Class
 *
 * @package  Ecl
 * @version  6.3.0
 */
class Ecl_Db_Mysql {

	// Public properties


	// Private properties
	protected $_connection = null;   // DB Connection object

	/**
	 * Configuration info of server to connect to
	 */
	protected $_config = array(
        'host'         => 'localhost' ,
		'port'         => 3306 ,
        'username'     => null ,
        'password'     => null ,
        'database'     => null ,
        'persistent'   => false ,                   // Use persistant connection (default: false)
        'client_flags' => MYSQL_CLIENT_COMPRESS ,   // Client Flags if any
    );

	protected $_sql = null;   // Last query run

	protected $_result_set = null;   // MySQL Result Set resource from the last query

	protected $_query_info = null;   // Info on the last query

	protected $_error = null;   // Last database error received
	protected $_use_error_exceptions = false;   // Use exceptions when encountering errors

	protected $_debug = false;   // debug mode - Echos verbose error messages



	/**
	 * Constructor
	 *
	 * If you do not supply connection details here, you must use setConnection().
	 *
	 * @param  array  $config  (optional) The server connection settings. @see setConnection for config settings.
	 *
	 * @return  object  A new instance of this class.
	 */
	public function __construct($config = null) {
		$this->setConnection($config);
	} // /method



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	/**
	 * Clear results and associated info.
	 *
	 * @return  boolean  Returns true in all cases.
	 */
	public function clear() {
		$this->_sql = null;
		$this->_query_info = null;
		$this->_error = null;

		$this->_result_set = null;

		return true;
	} // /method



	/**
	 * Close the current database connection
	 *
	 * @return  boolean  Returns true in all cases.
	 */
	public function close() {
		$this->_connection = null;
		$this->clear();
	} // /method



	/**
	 * Open database connection
	 *
	 * @return  boolean  Connection was successful
	 */
	public function connect() {
		if ($this->_connection) { return true; }

		// Connect to server
		if ($this->_config['persistent']) {
			$this->_connection = @mysql_pconnect("{$this->_config['host']}:{$this->_config['port']}", $this->_config['username'], $this->_config['password'], (!$reuse_connection), $this->_config['client_flags']);
		} else {
			$this->_connection = @mysql_connect("{$this->_config['host']}:{$this->_config['port']}", $this->_config['username'], $this->_config['password'], (!$reuse_connection), $this->_config['client_flags']);
		}

		// Check connection
		if (!$this->_connection) {
			$this->_throwError('Connecting to Database');
			return false;
		}

		// Select database
		$result = @mysql_select_db($this->_config['database'], $this->_connection);

		if ($result) {
			return true;
		} else {
			$this->_throwError('Selecting Database');
			return false;
		}
	} // /method



	/* Methods for executing SQL queries */



	/**
	 * Delete rows from the given table.
	 *
	 * The $bind array is only processed if you provide a where clause, as otherwise it would have no use.
	 *
	 * @param  string  $table  The table to update.
	 * @param  string  $where  (optional) The where clause to use when updating (format: " field='value' ").
	 * @param  mixed  $bind  (optional) An assoc-array of placeholder-value pairs, or null for no bindings.
	 *
	 * @return  integer  The number of rows affected.  If none, returns 0.
	 */
	public function delete($table, $where = null, $bind = null) {
		if (is_null($where)) {
			$sql = "DELETE FROM `$table`";
		} else {
			$sql = $this->prepareQuery("DELETE FROM `$table` WHERE $where ", $bind);
		}
		$this->_processQuery($sql, false);
		return $this->getNumAffected();
	}// /method



	/**
	 * Executes the given SQL query and ignores any result set (ie, not a SELECT).
	 *
	 * @param  string  $sql  The SQL statement to execute.
	 * @param  mixed  $bind  (optional) An assoc-array of placeholder-value pairs, or null for no bindings.
	 *
	 * @return  null  Returns null in all cases.
	 */
	public function execute($sql, $bind = null) {

		if ($bind) {
			$sql = $this->prepareQuery($sql, $bind);
		}

		return $this->_processQuery($sql, false);
	}// /method



	/**
	 * Executes an SQL dump file, running one command at a time.
	 *
	 * Supports ALTER, CREATE, DROP, INSERT AND UPDATE statements.
	 *
	 * This function processes the entire SQL script in one go,
	 * for large dump files this could consume large amounts of memory.
	 *
	 * @param  string  $sql  The SQL script to execute.
	 * @param  boolean  $fail_on_error  Ignore any SQL Errors, and continue processing.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function executeSqlDump($sql, $fail_on_error = true) {
		// Standardise line breaks
		$sql = preg_replace("%\r?\n%", "\r\n", $sql);

		// Strip SQL comments
		$sql = preg_replace("%^\s*(/\*.*?\*/[;]*|//[^\r\n]+)|--[^\r\n]*%ims", '', $sql);

		// Mark the SQL statements we're interested in
		$rand = rand(1,999999);
		$marker ="!~%MARKER:{$rand}%~!";

		$sql = preg_replace('%^\s*(ALTER|CREATE|DROP|INSERT|UPDATE)%ims', "$marker\\1", $sql);

		// Split the SQL into an array of SQL statements
		$statements = explode($marker, $sql);
		$sql = null;

		// Execute the statements
		if (count($statements)>0) {

			if ($fail_on_error) {
				foreach($statements as $statement) {
					$statement = trim($statement);
					if (!empty($statement)) {
						$this->execute($statement);
					}
				}
			} else {
				foreach($statements as $statement) {
					$statement = trim($statement);
					if (!empty($statement)) {
						try {
							$this->execute($statement);
						} catch (Exception $e) {
							// Do nothing
						}
					}
				}
			}
		}

		return true;
	}// /method



	/**
	 * Insert a row into the given table.
	 *
	 * @param  string  $table  The table to insert into.
	 * @param  array  $bind  An assoc-array of field-value pairs.
	 * @param  boolean  $use_ignore  (optional) Ignore duplicate key errors. (default: false)
	 *
	 * @return  integer  The last inserted ID. If not applicable, returns null.
	 */
	public function insert($table, $bind, $use_ignore = false) {
		$cols = implode(',', array_keys($bind));
		$values = array();
		foreach($bind as $field => $value) {
			$values[] = $this->prepareValue($value);
		}
		$values = implode(',', $values);

		$command = ($use_ignore) ? 'INSERT IGNORE' : 'INSERT' ;

		$sql = "$command INTO $table ($cols) VALUES ($values)";

		$this->_processQuery($sql, false);
		return $this->getInsertId();
	}// /method



	/**
	 * Insert multiple rows into the given table (MySQL 4+ only).
	 *
	 * Uses a single insert statement with multiple VALUES sections to insert
	 * multiple records in a single transaction.
	 *
	 * @param  string  $table  The table to insert into.
	 * @param  array  $bind  An array of assoc-arrays of field-value pairs.
 	 * @param  boolean  $use_ignore  (optional) Ignore duplicate key errors. (default: false)
	 *
	 * @return  integer  The last inserted ID. If not applicable, returns null.
	 */
	public function insertMulti($table, $bind_array, $use_ignore = false) {
		$cols = implode(',', array_keys($bind_array[0]));

		$values_array = array();

		$command = ($use_ignore) ? 'INSERT IGNORE' : 'INSERT' ;

		$sql = "$command INTO $table ($cols) VALUES ";
		foreach($bind_array as $i => $bind) {
			$values = array();
			foreach($bind as $field => $value) {
				$values[] = $this->prepareValue($value);
			}
			$values_array[] = implode(',', $values);
		}
		$sql .= '('. implode('), (', $values_array) .') ';

		$this->_processQuery($sql, false);
		return $this->getInsertId();
	}// /method



	/**
	 * Run the given query and store the result.
	 *
	 * @param  string  $sql  The sql to execute.
	 * @param  mixed  $bind  (optional) An assoc-array of placeholder-value pairs, or null for no bindings.
	 *
	 * @return  integer  The number of rows returned by the query.
	 */
	public function query($sql, $bind = null) {

		if ($bind) {
			$sql = $this->prepareQuery($sql, $bind);
		}

		$this->_processQuery($sql, true);
		return $this->getNumRows();
	}// /method



	/**
	 * Replace/insert a row in the given table
	 *
	 * @param  string  $table  The table to replace into
	 * @param  array  $bind  An assoc-array of field-value pairs
	 *
	 * @return  integer  The number of rows affected (1 = row inserted. 2 = row deleted then replaced. >2 = multiple rows deleted/replaced)
	 */
	public function replace($table, $bind) {
		$cols = implode(',', array_keys($bind));
		$values = array();
		foreach($bind as $field => $value) {
			$values[] = $this->prepareValue($value);
		}
		$values = implode(',', $values);

		$sql = "REPLACE INTO $table ($cols) VALUES ($values)";

		$this->_processQuery($sql, false);
		return $this->getNumAffected();
	}// /method



	/**
	 * Replace/insert multiple rows in the given table.
	 *
	 * Uses a single replace statement with multiple VALUES sections to replace
	 * multiple records in a single transaction.
	 *
	 * @param  string  $table  The table to replace into.
	 * @param  array  $bind  An array of assoc-arrays of field-value pairs.
	 *
	 * @return  integer  The number of rows affected (1 = row inserted. 2 = row deleted then replaced. >2 = multiple rows deleted/replaced)
	 */
	public function replaceMulti($table, $bind_array) {
		$cols = implode(',', array_keys($bind_array[0]));
		$ids = null;

		$values_array = array();

		$sql = "REPLACE INTO $table ($cols) VALUES ";
		foreach($bind_array as $i => $bind) {
			$values = array();
			foreach($bind as $field => $value) {
				$values[] = $this->prepareValue($value);
			}
			$values_array[] = implode(',', $values);
		}
		$sql .= '('. implode('), (', $values_array) .') ';

		$this->_processQuery($sql, false);
		return $this->getNumAffected();
	}// /method



	/**
	 * Combine the given SQL statements using a UNION operator.
	 *
	 * Use $order_by to control the ordering.  e.g.  $order_by = 'count DESC, name ASC'
	 *
	 * @param  array  $sql_statements  An array of SQL statements.
	 * @param  string  $union  The union type to use, either 'ALL' or 'DISTINCT'. (default: 'DISTINCT')
	 * @param  string  $order_by  The SQL order by column list to use for the entire union result.  (default: null)
	 *
	 * @return  mixed  The resulting SQL statement. On fail, null.
	 */
	public function unionise($sql_statements, $union = 'DISTINCT', $order_by = null) {
		$union = strtoupper($union);
		if (!in_array($union, array('ALL', 'DISTINCT') )) {
			return null;
		} else {
			$union = ") UNION $union (";
		}

		$sql = '('. implode($union, $sql_statements) .')';
		if (!empty($order_by)) {
			$sql .="ORDER BY $order_by";
		}

		return $sql;
	}// /method



	/**
	 * Update rows in the given table.
	 *
	 * @param  string  $table  The table to update.
	 * @param  array  $bind  An assoc-array of field-value pairs.
	 * @param  string  $where  (optional) The WHERE condition to use when updating.
	 *
	 * @return  integer  The number of rows affected.  If none, returns 0.
	 */
	public function update($table, $bind, $where = null) {
		$set = array();

		foreach($bind as $field => $value) {
			$set[] = "$field = ". $this->prepareValue($value) ."\n";
		}
		$set = implode(', ', $set);

		if (null === $where) {
			$sql = "UPDATE $table SET $set";
		} else {
			$sql = "UPDATE $table SET $set WHERE $where";
		}

		$this->_processQuery($sql, false);
		return $this->getNumAffected();
	}// /method



	/* Methods for accessing query results */



	/**
	 * Get a single column of values from the result as a numeric array.
	 *
	 * @param  mixed  $column  (optional) The column name to use. Defaults to first column.
	 *
	 * @return  mixed  The array of values for the column (0-based). On fail, null.
	 */
 	public function getColumn($column = 0)	{
		$result = null;

		// If we want a numeric index, find it
		if (is_int($column)) {
			while ( $row = @mysql_fetch_row($this->_result_set) ) {
				$result[] = $row[$column];
			}
		} else {
			while ( $row = @mysql_fetch_assoc($this->_result_set) ) {
				$result[] = $row[$column];
			}
		}
		return $result;
	}// /method



	/**
	 * Get a single column of unique values from the result as a numeric array.
	 *
	 * Returns a column from the results as if the query was a SELECT DISTINCT (ie, unique values only).
	 * This method is case insensitive.
	 *
	 * @param  mixed  $column  (optional) The column name to use. Defaults to first column.
	 *
	 * @return  mixed  The array of values for the column (0-based). On fail, null.
	 */
 	public function getColumnDistinct($column = 0)	{
		$columns = $this->getColumn($column);
		return array_values( array_intersect_key($columns, array_unique(array_map('strtolower', $columns)) ) );
	}// /method



 	/**
	 * Return an array of column names from the last query.
	 *
	 * @return  mixed  Array of column names. On fail, null.
	 */
	public function getColumnNames() {
		if (!$this->hasResult()) { return null; }
		$row = $this->getRow();
		@mysql_data_seek($this->_result_set);
		return ($row) ? array_keys($row) : null ;
	}// /method



	/**
	 * Get the first row from the query results as an object.
	 *
	 * Help functions should accept a single parameter, the assoc-array of database fields, and return an object.
	 *
	 * @param  callback  $converter_callback  A callback to the converter function which can load an object from a database row.
	 *
	 * @return  mixed  An object. On fail, null.
	 */
	public function getObject($converter_callback) {
		if (!$this->hasResult()) { return null; }
		return call_user_func($converter_callback, $this->getRow());
	}// /method



	/**
	 * Get all rows from the query results.
	 *
	 * @return  mixed  An array of rows. On fail, null.
	 */
	public function getResult() {
		if (!$this->hasResult()) { return null; }

		$result = null;
		@mysql_data_seek($this->_result_set, 0);
		while ( $row = mysql_fetch_assoc($this->_result_set) ) {
			$result[] = $row;
		}
		return $result;
	}// /method



	/**
	 * Get the query results as an assoc-array.
	 *
	 * Supplying both parameters returns an assoc-array ( key-field => value-field )
	 * If you only supply $key_field, it effectively returns the results as an assoc-array of rows, each indexed by the given field.
	 * e.g.  array ( key-field => row )
	 * If a key-field's value is shared by one or more rows, only the last row will be returned.
	 * For multiple rows per key-field, use ->getResultGroupedRows().
	 *
	 * @param  mixed  $key_field  The field to use as the assoc-key.
	 * @param  mixed  $value_field  (optional) The field to use as the assoc-value. If not given, the entire row will be used.
	 *
	 * @return  mixed  Assoc-array of results. On fail, null.
	 */
	public function getResultAssoc($key_field, $value_field = null) {
		if (!$this->hasResult()) { return null; }

		$cols = $this->getColumnNames();

		if (!in_array($key_field, $cols)) { return null; }

		$new_array = null;
		@mysql_data_seek($this->_result_set, 0);
		// If we want assoc k => v
		if ($value_field) {
			if (!in_array($value_field, $cols)) { return null; }
			while ( $row = @mysql_fetch_assoc($this->_result_set) ) {
				$new_array[$row[$key_field]] = $row[$value_field];
			}
		} else {
			// Convert rows to associative rows
			while ( $row = @mysql_fetch_assoc($this->_result_set) ) {
				$new_array[$row[$key_field]] = $row;
			}
		}
		return $new_array;
	}// /method



	/**
	 * Get results of the given query as an assoc-array of objects.
	 *
	 * @param  string  $key_field  The field to use as the assoc-array key.
	 * @param  callback  $converter_callback  A callback to the converter function which can load an object from a database row.
	 *
	 * @return  mixed  An array of objects. On fail, null.
	 */
	public function getResultAssocObjects($key_field, $converter_callback) {
		if (!$this->hasResult()) { return null; }

		if (!in_array($key_field, $this->getColumnNames())) { return null; }

		$new_array = null;
		@mysql_data_seek($this->_result_set, 0);
		while ( $row = @mysql_fetch_assoc($this->_result_set) ) {
			$new_array[$row[$key_field]] = call_user_func($converter_callback, $row);
		}
		return $new_array;
	}// /method



	/**
	 * Get results of the given query as an assoc-array of grouped rows.
	 *
	 * Example output:
	 * array (
	 *    'key1'  => array ( row1, row2, .. )  ,
	 *    'key2'  => array ( row3 )  ,
	 *    'key3'  => array ( row4, row5, row6, .. )  ,
	 *    ..
	 * );
	 *
	 * @param  string  $key_field  The field to use as the assoc-array key.
	 *
	 * @return  mixed  An assoc-array of rows. On fail, null.
	 */
	public function getResultGroupedRows($key_field) {
		if (!$this->hasResult()) { return null; }

		if (!in_array($key_field, $this->getColumnNames())) { return null; }

		$new_array = null;
		@mysql_data_seek($this->_result_set, 0);
		while ( $row = @mysql_fetch_assoc($this->_result_set) ) {
			$new_array[$row[$key_field]][] = $row;
		}
		return $new_array;
	}// /method



	/**
	 * Get results of the given query as an assoc-array of grouped objects.
	 *
	 * Example output:
	 * array (
	 *    'key1'  => array ( obj1, obj2, .. )  ,
	 *    'key2'  => array ( obj3 )  ,
	 *    'key3'  => array ( obj4, obj5, obj6, .. )  ,
	 *    ..
	 * );
	 *
	 * @param  string  $key_field  The field to use as the assoc-array key.
	 * @param  callback  $converter_callback  A callback to the converter function which can load an object from a database row.
	 *
	 * @return  mixed  An assoc array of objects. On fail, null.
	 */
	public function getResultGroupedObjects($key_field, $converter_callback) {
		if (!$this->hasResult()) { return null; }

		if (!in_array($key_field, $this->getColumnNames())) { return null; }

		$new_array = null;
		@mysql_data_seek($this->_result_set, 0);
		while ( $row = @mysql_fetch_assoc($this->_result_set) ) {
			$new_array[$row[$key_field]][] = call_user_func($converter_callback, $row);;
		}
		return $new_array;
	}// /method



	/**
	 * Get results of the given query as an assoc-array of grouped values.
	 *
	 * Example output:
	 * array (
	 *    'key1'  => array ( value1, value2, .. )  ,
	 *    'key2'  => array ( value3 )  ,
	 *    'key3'  => array ( value4, value5, value6, .. )  ,
	 *    ..
	 * );
	 *
	 * @param  string  $key_field  The field to use as the assoc-array key.
	 * @param  string  $value_field  The field to use as the grouped value.
	 *
	 * @return  mixed  An assoc-array. On fail, null.
	 */
	public function getResultGroupedValues($key_field, $value_field) {
		if (!$this->hasResult()) { return null; }

		$cols = $this->getColumnNames();
		if (!in_array($key_field, $cols)) { return null; }
		if (!in_array($value_field, $cols)) { return null; }

		$new_array = null;
		@mysql_data_seek($this->_result_set, 0);
		while ( $row = @mysql_fetch_assoc($this->_result_set) ) {
			$new_array[$row[$key_field]][] = $row[$value_field];
		}
		return $new_array;
	}// /method



	/**
	 * Get results of the given query as an array of objects.
	 *
	 * @param  callback  $converter_callback  A callback to the converter function which can load an object from a database row.
	 *
	 * @return  mixed  An array of objects. On fail, null.
	 */
	public function getResultObjects($converter_callback) {
		if (!$this->hasResult()) { return null; }

		$objects = null;
		@mysql_data_seek($this->_result_set, 0);
		while ( $row = @mysql_fetch_assoc($this->_result_set) ) {
			$objects[] = call_user_func($converter_callback, $row);
		}
		return $objects;
	}// /method



	/**
	 * Get a MySQL resource representing the query result.
	 *
	 * @return  mixed  A MySQL result resource. On fail, null.
	 */
	public function getResultResource() {
		if (!$this->hasResult()) { return null; }

		@mysql_data_seek($this->_result_set, 0);
		return $this->_result_set;
	}// /method



	/**
	 * Get a single row from the query results
	 *
	 * @param  integer  $row  (optional) The row to return (0-based). On fail, null.
	 *
	 * @return  mixed  An array of fields in the row. On fail, null.
	 */
	public function getRow($row = 0) {
		if (!$this->hasResult()) { return null; }

		@mysql_data_seek($this->_result_set, (int) $row);
		$row = @mysql_fetch_assoc($this->_result_set);
		return ($row) ? $row : null ;
	}// /method



	/**
	 * Get a single value from the result
	 *
	 * @param  mixed  $column  (optional) The column name to use. Defaults to first column.
	 * @param  integer  $row  (optional) The index of the row to use (0-based). Defaults to first row.
	 *
	 * @return  mixed  On success, the values of the given field in the given row. On fail, null.
	 */
 	public function getValue($column = 0, $row = 0) {
 		if (!$this->hasResult()) { return null; }

 		@mysql_data_seek($this->_result_set, 0);
		$value = @mysql_result($this->_result_set, $row, $column);
		return ($value) ? $value : null ;
	}// /method



	/**
	 * Is there a current result-set
	 *
	 * @return  boolean  There is a result-set.
	 */
	public function hasResult() {
		return (is_resource($this->_result_set)) && (mysql_num_rows($this->_result_set)>0);
	}// /method



	/* Other Methods */



	/**
	 * Escape a character string for use in the database.
	 *
	 * @param  string  $str  The string to escape.
	 *
	 * @return  string  The escaped string.
	 */
	public function escapeString($str) {
		if (!$this->_connection) { $this->connect(); }
		return mysql_real_escape_string($str);
	}// /method



	/**
	 * Convert a PHP timestamp (unix timestamp) to the database's datetime format
	 *
	 * @param  integer  $unix_timestamp
	 * @param  boolean  $nullable  If empty, use a null date. (default: true)
	 *
	 * @return  mixed  MySQL date string, or null if empty datetime.
	 */
	public function formatDate($unix_timestamp, $nullable = true) {
		if ($nullable && empty($unix_timestamp)) {
			return null;
		} else {
			return date('Y-m-d H:i:s', $unix_timestamp);	// MySQL datatime format (almost ISO-8601)
		}
	}// /method



 	/**
	 * Return the PHP timestamp represented by the given database formatted string
	 *
	 * @return  datetime  The datetime value.  On fail, null.
	 */
	public function getDateTime($db_value) {
		return strtotime($db_value);
	}// /method



	/**
	 * Get the last database error
	 *
	 * @return  string  The last error message encountered
	 */
	public function getError() {
		return ($this->_connection) ? mysql_error($this->_connection) : 'No MySQL Connection';
	}// /method



	/**
	 * Get the last insert ID
	 *
	 * @return  integer  If a new ID was added, returns (int). If not applicable, returns 0
	 */
	public function getInsertId() {
		return ($this->_connection) ? mysql_insert_id($this->_connection) : 0 ;
	}// /method



	/**
	 * Get the number of rows affected by the last query
	 *
	 * @return  integer  The number of rows affected
	 */
	public function getNumAffected() {
		return ($this->_connection) ? mysql_affected_rows($this->_connection) : 0 ;
	}// /method



	/**
	 * Get the number of columns returned by the last query
	 *
	 * @return  integer  The number of columns in the results returned
	 */
	public function getNumColumns() {
		return ($this->_result_set) ? mysql_num_fields($this->_result_set) : 0;
	}// /method



	/**
	 * Get the number of rows returned by the last query
	 *
	 * @return  integer  The number of rows returned
	 */
	public function getNumRows() {
		return ($this->_result_set) ? mysql_num_rows($this->_result_set) : 0 ;
	}// /method



	/**
	 * Get information about the last query executed
	 *
	 * @return  mixed  An Assoc-array of query information
	 */
	public function getQueryInfo() {

		// If the query info has already been retrieved, return it
		if (!is_null($this->_query_info)) { return $this->_query_info; }


		if ($this->_result_set) {
			$this->_query_info['rows'] = (int) mysql_num_rows($this->_result_set);
			$this->_query_info['columns'] = (int) mysql_num_fields($this->_result_set);
		}

		if ($this->_connection) {
			$this->_query_info['affected'] = (int) mysql_affected_rows($this->_connection);
			// Only try and get an insert-id if affected rows > 0
			// Will hopefully stop any 'permanent' connection problems with incorrect insert-ids being returned on query-fail
			$this->_query_info['insert_id'] = ($this->_query_info['affected']>0) ? mysql_insert_id($this->_connection) : 0 ;
		}

		// Get full mysql_info (if possible)
		$str_info = @mysql_info($this->_connection);
		if ($str_info) {
			ereg("Records: ([0-9]*)", $str_info, $records);
			ereg("Duplicates: ([0-9]*)", $str_info, $duplicates);
			ereg("Warnings: ([0-9]*)", $str_info, $warnings);
			ereg("Deleted: ([0-9]*)", $str_info, $deleted);
			ereg("Skipped: ([0-9]*)", $str_info, $skipped);
			ereg("Rows matched: ([0-9]*)", $str_info, $rows_matched);
			ereg("Changed: ([0-9]*)", $str_info, $changed);

			$this->_query_info['records'] = (int) $records[1];
			$this->_query_info['duplicates'] = (int) $duplicates[1];
			$this->_query_info['warnings'] = (int) $warnings[1];
			$this->_query_info['deleted'] = (int) $deleted[1];
			$this->_query_info['skipped'] = (int) $skipped[1];
			$this->_query_info['matched'] = (int) $rows_matched[1];
			$this->_query_info['changed'] = (int) $changed[1];
		}

		return $this->_query_info;
	}// /method



	/**
	 * Return the last query run
	 */
	public function getSql() {
		return $this->_sql;
	}// /method



	/**
	 * Get a recordset iterator.
	 *
	 * Recordset Iterators offer two main bits of functionality.
	 * 1) You can use them in foreach() statements to iterate through query results automatically.
	 * 2) They use lazy initialisation, so the query only executes if the results are requested.
	 * @see Ecl_Db_Recordset
	 *
	 * @param  string  $sql  The SQL to execute.
	 * @param  array  $binds  Any bound parameters to process.
	 * @param  callback  $row_function  (optional) The row callback function to use to convert rows. (default: null)
	 *
	 * @return  object  A new recordset object.
	 */
	public function newRecordset($sql, $binds = null, $row_function = null) {
		return new Ecl_Db_Recordset($this, $this->prepareQuery($sql, $binds), $row_function);
	}// /method



	/* Methods for preparing elements of an SQL query */



	/**
	 * Build a filter clause of the form (aaa='xxx' OR aaa='yyy' OR aaa='zzz' ... ).
	 *
	 * @param  string  $field_name  The field to check (aaa in example above).
	 * @param  array  $filter_values  The values to compare against.
	 * @param  string  $logical_operator  The operator to use to concatenate the filters ('OR', 'XOR', etc).
	 * @param  string  $comparison_operator  The operator to use within the filter ('=' OR 'LIKE', etc).
	 *
	 * @return  string  The completed filter clause.
	 */
	public function prepareFilter($field_name, $filter_values, $logical_operator = 'OR', $comparison_operator = '=') {
		$filter_values = (array) $filter_values;

		$filter_clause = '(';
		$w_count = count($filter_values);
		$i = 1;
		foreach($filter_values as $k => $v) {
			$filter_clause .= "{$field_name} {$comparison_operator} ". $this->prepareValue($v);
			if ($i<$w_count) { $filter_clause .= " $logical_operator "; }
			$i++;
		}
		$filter_clause .= ')';

		return $filter_clause;
	}// /method



	/**
	 * Prepare an SQL query by replacing any placeholders using the given binds.
	 *
	 * Placeholders should be of the form: ':name'
	 * The binds array should be of the form:  array ( name1 => value1, name2 => value2, ..)
	 * Binds are automatically escaped before being replacing the relevant placeholder.
	 *
	 * @param  string  $sql  The SQL statement to prepare.
	 * @param  array  $bind  (optional) An assoc-array of placeholder-value pairs, or null for no bindings.
	 *
	 * @return  string  The resulting SQL.
	 */
	public function prepareQuery($sql, $bind = null) {
		if (is_array($bind)) {
			foreach($bind as $placeholder => $value) {
				$placeholder = (string) $placeholder;
				if ($placeholder[0]!=':') { $placeholder = ':'.$placeholder; }

				$value = $this->prepareValue($value);
				$sql = str_replace($placeholder, $value, $sql);
			}
		}

		return $sql;
	}// /method



	/**
	 * Builds an SQL set of the form ('aaa','bbb','ccc') for use with IN operators.
	 *
	 * @param  mixed  $values  Value, or array of values, to include in the set.
	 *
	 * @return  string  The resulting SET construct.
	 */
	public function prepareSet($values) {
		if (is_array($values)) {
			if (empty($values)) {
				return '(null)';
			} else {
				$values = array_map(array(&$this, 'prepareValue'), $values);
				return '('. implode(',', $values) .')';
			}
		} else {
			return '('. $this->prepareValue($values) .')';
		}
	}// /method



	/**
	 * Prepare a value for putting into the database
	 *
	 * Escapes special characters, checks for NULL, and puts in surrounding quotes as necessary
	 *
	 * @param  mixed  $value  Value to prepare
	 *
	 * @return  string  Enquoted value, ready for insertion into a database (of the form 'value' or NULL)
	 */
	public function prepareValue($value) {
		// Performs a MySQL enquoting. Every value can be enclosed in quotes except NULL
		return (null === $value) ? 'NULL' : '\''. $this->escapeString($value) .'\'';
	}// /method



	/**
	 * Set the connection info to use
	 *
	 * @param  array  $config  An assoc-array of connection settings.
	 *
	 * @return  boolean  True in all cases.
	 */
	public function setConnection($config) {
		if (is_array($config)) {
			foreach($this->_config as $k => $v) {
				if (array_key_exists($k, $config)) {
					$this->_config[$k] = $config[$k];
				}
			}
		}
		return true;
	}// /->setConnection()



	/**
	 * Set debug mode
	 * When in debug mode, detailed error reports are echoed
	 */
	public function setDebug($on) {
		$this->_debug = (bool) $on;
	}// /method



	/**
	 * Set connection to be persistent
	 */
	public function setPersistent($on) {
		$this->_config['persistent'] = (bool) $on;
	}// /method



	/**
	 * Set whether to throw exceptions on errors, instead of calling die().
	 */
	public function setUseExceptions($on) {
		$this->_use_error_exceptions = (bool) $on;
	}// /method



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



	/**
	 * Execute the SQL and collect the result set
	 *
	 * @param  string  $sql  The sql statement to process and execute
	 * @param  boolean  $store_result  (optional) The result of the query should be stored for retrieval later. (default: true)
	 *
	 * @return  boolean  The query executed successfully
	 */
	protected function _processQuery($sql, $store_result = true) {
		$this->clear();
		$this->_sql = trim($sql);  // Save query

		// Run the query
		if (!$this->connect()) { $this->_throwError('No Connection'); }
		$this->_result_set = @mysql_query($sql, $this->_connection) or $this->_throwError('Querying database');

		// Process the results, if any
		if (!$this->_result_set) {
			$this->_result_set = null;
			return false;
		} else {
			return true;
		}
	} // /method



	/**
	 * Throw an error.
	 *
	 * @param  string  $err_msg  The error message to return.
	 *
	 * @return  boolean  False in all cases.
	 */
	protected function _throwError($err_msg) {
		if ($this->_debug) {
			if ($this->_use_error_exceptions) {
				if ($this->_connection) {
					throw new Exception("MySQL DB Error ({$this->_config['database']}@{$this->_config['host']}). $err_msg :: ". $this->getError());
				} else {
					throw new Exception("MySQL DB Error (no connection). $err_msg :: ". $this->getError());
				}
			} else {
				if ($this->_connection) {
					die("<hr />MySQL DB Error ({$this->_config['database']}@{$this->_config['host']})<hr />$err_msg :: ". $this->getError() .'<hr />'. $this->getSql() .'<hr />');
				} else {
					die("<hr />MySQL DB Error (no connection)<hr />$err_msg :: ". mysql_error() .'<hr />'. $this->getSql() .'<hr />');
				}
			}
		} else {
			if ($this->_use_error_exceptions) {
				throw new Exception('MySQL DB Error');
			} else {
				die('MySQL DB Error');
			}
		}
		return false;
	}// /method



}// /class
?>