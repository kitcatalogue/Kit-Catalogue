<?php
/**
 * Array Recordset Class
 *
 * Mimics the operation of the Database Recordset Iterator, but uses a given array of data instead.
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Db_Arrayrecordset extends Ecl_Db_Recordset {

	// Public properties


	// Private properties
	protected $_array = null;   // The array of data

	protected $_db = null;
	protected $_executed = true;   // We already have the data


	/**
	 * Constructor
	 *
	 * @param  array  $array  The array of data rows to use.
	 * @param  callback  $row_function  (optional) The row callback function to use to convert rows. (default: null)
	 *
	 * @return  object  A new instance of this class.
	 */
	public function __construct($array, $row_function = null) {
		$this->_array = (array) $array;
		$this->_row_function = $row_function;
	}// /method



	public function __destruct() {
	}// /method



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	/* Interface Methods */



	public function count(): int
	{
		return count($this->_array);
	}// /method



	public function current(): mixed
	{
		if ($this->_row_function) {
			$func = $this->_row_function;
			return call_user_func($func, $this->_row);
		} else {
			return $this->_row;
		}
	}// /method



	public function key(): mixed
	{
		return key($this->_array);
	}// /method



	public function next(): void
	{
		$this->_row = next($this->_array);
	}// /method



	public function offsetGet(mixed $offset): mixed
	{
		if ($this->offsetExists($offset)) { return null; }

		$this->_row = $this->_array[$offset];

		if ($this->_row_function) {
			$func = $this->_row_function;
			return call_user_func($func, $this->_row);
		} else {
			return $this->_row;
		}
	}// /method



	public function rewind(): void
	{
		reset($this->_array);
		$this->_row = current($this->_array);
	}// /method



	/* Data Access Methods */



	public function columnNames() {
		if (null !== $this->_column_names) { return $this->_column_names; }

		$this->_column_names = array();

		$row = $this->offsetGet(0);

		if (isset($row)) {
			$this->_column_names = array_keys( (array) $row);
		}

		return $this->_column_names;
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
		return $this->_array;
	}// /method



	public function valid(): bool
	{
		return (key($this->_array) !== null);
	}// /method



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



	protected function _execute() {
	}// /method



}// /class
?>
