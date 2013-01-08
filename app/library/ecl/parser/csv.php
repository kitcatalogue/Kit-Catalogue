<?php
/**
 * A class providing CSV file parsing.
 *
 * Can only handle CSV files where all data is enclosed in the appropriate chars (e.g. "abcd","efgh")
 *
 * @package  Ecl
 * @version  1.1.0
 */
class Ecl_Parser_Csv {

	// Private Properties

	private $_use_field_headers = false;

	private $_col_encloser  = '"';
	private $_col_separator  = ',';
	private $_row_separator  = "\n";



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Generate a csv string from an array of row-data.
	 *
	 * The data array must be two dimensional.
	 * e.g.  array ( 0 .. n => array ( value1, value2, .. )
	 *
	 * @param  mixed  $data  The array to process.
 	 *
	 * @return  string  The string representation. On fail, null.
	 */
	public function generate($data) {
		$string = '';

		if (is_array($data)) {

			$col_escape = $this->_col_encloser . $this->_col_encloser;

			foreach($data as $x => $row) {
				$cols = array();
				foreach($row as $y => $col) {
					if (strpos($col, $this->_col_encloser)!==false) {
						$col = str_replace($this->_col_encloser, $col_escape, $col);
					}
					$cols[$y] = $this->_col_encloser . $col . $this->_col_encloser;
				}
				$string .= implode($this->_col_separator, $cols);
				$string .= $this->_row_separator;
			}
		}

		return $string;
	}// /method



	/**
	 * Parse an csv file into an 2D array of row-data.
	 *
	 * When detecting row separators, the parser will look for single \r characters, which probably denote a Mac based upload.
	 * If a \r is found, then \r will be used as the row separator. Otherwise, the setting from ->setRowSeparator() will be used.
	 *
	 * @param  mixed  $csv_string  The csv file to parse.
	 * @param  boolean  $detect_line_end  (optional) Attempt to determine the line-end char automatically (Mac = \r, Windows = [\r]\n, etc)
	 *
	 * @return  mixed  The array representation. On fail, null.
	 */
	public function parse($string, $detect_row_separator = true) {
		$array = null;

		$string = trim($string);
		$string_len = strlen($string);

		$row_sep = $this->_row_separator;

		if ($detect_row_separator) {
			if (preg_match('(\r[^\n])', $string)) {
				$row_sep = "\r";
			}
		}

		$row_sep_len = strlen($row_sep);


		$row = 0;
		$col = 0;

		$value = '';

		$in_col = true;
		$enclosed = false;

		$i = 0;
		while ($i<$string_len) {

			$char = $string[$i];

			$next = ($i<$string_len-1) ? $string[$i + 1] : null ;

			if ($char==$this->_col_encloser) {
				if ($enclosed) {
					if ($next==$this->_col_encloser) {
						$value .= $char;
						$i++;
					} else {
						$enclosed = false;
						$in_col = false;

						// Add column
						$array[$row][$col] = $value;
						$value = '';
						$col++;
					}
				} else {
					$enclosed = true;
					$in_col = true;
					$value = '';
				}
			} else {
				if ($enclosed) {
					$value .= $char;
				} else {
					if ($char==$row_sep) {
						// Add column
						if ($in_col) {
							$array[$row][$col] = trim($value);
						}

						$value = '';
						$in_col = true;
						$enclosed = false;

						// New Row
						$row++;
						$col = 0;
					} else {
						if ($char==$this->_col_separator) {
							if ($in_col) {
								// Add column
								$array[$row][$col] = $value;
								$value = '';
								$col++;
							} else {
								$in_col = true;
							}
						} else {
							$value .= $char;
						}
					}
				}
			}

			$i++;
		}

		if (!empty($value)) {
			// Add column
			$array[$row][$col] = $value;
			$value = '';
		}


		// If we want to use field headers, apply them now
		if ($this->_use_field_headers) {
			$array = $this->_applyFieldHeaders($array);
		}

		return $array;
	}// /method



	/**
	 * Set which character is used for enclosing columns.
	 *
	 * Used by both the generate() and parse() methods.
	 *
	 * @param  char  $char  The character to use.  (default: '"')
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setColumnEncloser($char = null) {
		if (strlen($char)==1) {
			$this->_col_encloser = $char;
			return true;
		}
		return false;
	}// /method



	/**
	 * Set which character is used for separating columns.
	 *
	 * Used by both the generate() and parse() methods.
	 *
	 * @param  char  $char  The character to use.  (default: ',')
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setColumnSeparator($char = null) {
		if (strlen($char)==1) {
			$this->_col_separator = $char;
			return true;
		}
		return false;
	}// /method



	/**
	 * Set which character is used for separating rows.
	 *
	 * Used by both the generate() and parse() methods.
	 *
	 * @param  char  $char  The character to use.  (default: \n)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setRowSeparator($char = null) {
		if (strlen($char)==1) {
			$this->_row_separator = $char;
			return true;
		}
		return false;
	}// /method



	/**
	 * Flag whether to use the first row of CSV data for column headers.
	 *
	 * Causes the parse() method to output an assoc-array, instead of a numerically indexed 2D array.
	 * The first row of csv data will be used for headers, and will not appear as a row in the results.
	 *
	 * @param  boolean  $use_headers  Should the parse() use headers? (default: false)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setUseFieldHeaders($use_headers = false) {
		$this->_use_field_headers = ($use_headers == true);
		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	/**
	 * Restructure an array so it uses the first row's columns as assoc-keys for the rest of the array
	 *
	 * The source array must contain at least 2 rows.
	 * The row used for the field headers will not be present in the output array.
	 * If a data-row has more columns than there are field headers, then extra assoc-keys are produced.
	 * These extra keys will be named 'unknown-1', 'unknown-2', etc.
	 *
	 * @param  array  $array  The array to process.
	 *
	 * @return  mixed  The new array. On fail, null.
	 */
	protected function _applyFieldHeaders($array) {
		$new = null;

		// If there's less than 2 rows, fail now
		if ( (!is_array($array)) || (count($array)<1) ) {
			return null;
		}

		$fields = $array[0];
		unset($array[0]);

		$fields_count = count($fields);

		foreach($array as $i => $row) {

			$new_row = null;

			foreach($row as $j => $col) {

				if (array_key_exists($j, $fields)) {
					$field = $fields[$j];
				} else {
					$field = 'unknown-'. (($j - $fields_count) + 1);
				}

				$new_row[$field] = $col;
			}

			$new[] = $new_row;

		}

		return $new;
	}// /method



}// /class
?>