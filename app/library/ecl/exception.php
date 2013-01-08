<?php
/**
 * Class for Ecl exceptions.
 *
 * @package  Ecl
 * @version 1.0.0
 */
class Ecl_Exception extends Exception {



	/**
	 * Constructor
	 */
	public function __construct($message = '', $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}// /->__construct()



	/**
	 * To string
	 */
	public function __toString() {
		$output = '';
		$output .= "Exception: ". get_class($this) ."\n";
		$output .= "Text: {$this->message}\n";
		$output .= "Code: {$this->code}\n";
		$output .= "File: {$this->file}\n";
		$output .= "Line: {$this->line}\n";
		$output .= "Trace:\n". $this->getTraceAsString() ."\n";

		$output = str_replace("\n", " <br />\n", $output);

		return $output;
	}// /__toString()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>