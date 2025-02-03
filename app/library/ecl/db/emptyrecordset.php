<?php
/**
 * Empty Recordset Class
 *
 * Mimics a Recordset, but contains no results.
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Db_Emptyrecordset extends Ecl_Db_Recordset {

	// Public properties


	// Private properties



	/**
	 * Constructor
	 */
	public function __construct($db, $sql, $row_function = null) {
	}// /method



	public function __destruct() {
	}// /method



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	/* Interface Methods */



	public function count(): int
	{
		return 0;
	}// /method



	public function current(): mixed
	{
		return null;
	}// /method



	public function key(): mixed
	{
		return null;
	}// /method



	public function next(): void
	{
	}// /method



	public function offsetSet(mixed $offset, mixed $value): void
	{
		// Fake implementation to satisfy ArrayAccess Interface
		// Setting recordset entries is a no-no
	}// /method



	public function offsetExists(mixed $offset): bool
	{
		return false;
	}// /method



	public function offsetUnset(mixed $offset): void
	{
		// Fake implementation to satisy ArrayAccess Interface
		// Unsetting recordset entries is a no-no
	}// /method



	public function offsetGet(mixed $offset): mixed
	{
		return null;
	}// /method



	public function rewind(): void
	{
	}// /method



	public function valid(): bool
	{
		return false;
	}// /method



	/* Data Access Methods */



	public function columnNames() {
		return array();
	}// /method



	/**
	 * Return the current row as returned by the database.
	 *
	 * @return  mixed  Assoc-array of row data. On fail, false.
	 */
	public function currentRow() {
		return null;
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
		return array();
	}// /method



	/**
	 * Get an assoc-array representation from the result.
	 */
	public function toAssoc($key_column, $value_column = null) {
		return array();
	}// /method



	/**
	 * Get an array of values for one column in the result.
	 */
	public function toColumn($column) {
		return array();
	}// /method



	/**
	 * Get a custom array of results.
	 */
	public function toCustomArray($callable) {
		return array();
	}// /method



	/**
	 * Get an grouped assoc-array representation from the result, using the given key.
	 */
	public function toGroupedAssoc($key_column, $value_column = null) {
		return array();
	}// /method



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



	protected function _execute() {
	}// /method



}// /class
?>
