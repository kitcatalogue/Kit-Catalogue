<?php
/**
 * Array helper class
 *
 * @package  Ecl
 * @static
 * @version  1.1.0
 */
class Ecl_Helper_Array {



	/**
	 * Constructor
	 */
	private function __construct() {
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Convert an array to an assoc-array, where the key is also the value.
	 *
	 * Duplicate values will be lost.
	 * e.g.
	 * Using a simple, 0-based array:
	 * input  : array ( 0 => blue, 1 => green, 2 => red )
	 * output : array ( 'blue' => 'blue', 'green' => 'green', 'red' => 'red' )
	 *
	 * @param  array  $array  The array to re-structure.
	 *
	 * @return  array  The resulting array.
	 */
	public static function duplicateValueAsKey($array) {
		$new_array = array();

		if (is_array($array)) {
			foreach($array as $k => $v) {
				$new_array[$v] = $v;
			}
		}
		return $new_array;
	}// /method



	/**
	 * Extracts two columns of values from a 2D array, and returns them as an assoc-array.
	 *
	 * Also works with arrays of objects, if $row_objects is true.
	 *
	 * WARNING : Does not check if the columns exist!
	 *
	 * @param  array  $array  Array to process.
	 * @param  mixed  $key_column  Index to use as the key in the new assoc-array.
	 * @param  mixed  $value_column  Index to use as the value in the new assoc-array.
	 * @param  boolean  $row_objects  (optional) Handle each each array-row as an object. (default: false)
	 *
	 * @return  array  Assoc-array of key-values pairs.
	 */
	public static function extractAssoc($array, $key_column, $value_column, $row_objects = false) {
		if (!is_array($array)) { return array(); }

		$extracted_array = array();

		if ($row_objects) {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row->$key_column] = $array_row->$value_column;
			}
		} else {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row[$key_column]] = $array_row[$value_column];
			}
		}
		return $extracted_array;
	}// /method



	/**
	 * Re-index an array so it is keyed using the given index.
	 *
	 * WARNING : Does not check if the column exists!
	 *
	 * e.g.
	 * output : array ( key => row_1, key => row_2, ... )
	 *
	 * @param  array  $array  Array to convert.
	 * @param  mixed  $key_column  Index/Key to use as the key in the new associative array.
	 * @param  boolean  $row_objects  (optional) Handle each each array-row as an object. (default: false)
	 *
	 * @return  array  An associated array of rows. On fail, null.
	 */
	public static function extractAssocRows($array, $key_column, $row_objects = false) {
		if ( (!is_array($array)) ) { return array(); }

		$assoc_array = array();

		if ($row_objects) {
			foreach($array as $i => $array_row) {
				$assoc_array[$array_row->$key_index] = $array_row;
			}
		} else {
			foreach($array as $i => $array_row) {
				$assoc_array["{$array_row[$key_index]}"] = $array_row;
			}
		}
		return $assoc_array;
	}// /method



	/**
	 * Extracts a column of values from a 2D array, and returns them as a 1D array.
	 *
	 * Any rows that do not contain the given key will be ignored.
	 *
	 * Also works with arrays of objects, if $row_objects is true.
	 *
	 * WARNING : Does not check if the column exists!
	 *
	 * @param  array  $array  Array to process.
	 * @param  mixed  $column  Array index/key to extract values from.
	 * @param  boolean  $row_objects  (optional) Handle each each array-row as an object. (default: false)
	 *
	 * @return  array  Array of values for given columns.
	 */
	public static function extractColumn($array, $column, $row_objects = false) {
		if (!is_array($array)) { return array(); }

		$extracted_column = array();

		if ($row_objects) {
			foreach($array as $i => $array_row) {
				$extracted_column[] = $array_row->$column;
			}
		} else {
			foreach($array as $i => $array_row) {
				$extracted_column[] = $array_row["$column"];
			}
		}
		return $extracted_column;
	}// /method



	/**
	 * Extracts three columns of from a 2D array, and returns them as an assoc-array of grouped rows.
	 *
	 * Also works with arrays of objects, if $row_objects is true.
	 *
	 * WARNING : Does not check if the columns exist!
	 *
	 * e.g.
	 * extractDoubleGroupedValues($people, 'dept', 'type')
	 *
	 * Might give:
	 *
	 * array = (
	 *   'el'  => array (
	 *   	'staff'    => array ( row_1, row_3 , ... ) ,
	 *   	'student'  => array ( row_2, row_5, ... ) ,
	 *   ) ,
	 *   'mc'  => array (
	 *   	'staff'    => array ( row_4, row_8, ... ) ,
	 *   	'student'  => array ( row_6, row_7, ... ) ,
	 *   ) ,
	 *   ...
	 * )
	 *
	 * @param  array  $array  Array to process.
	 * @param  mixed  $group_column  Index to use as the key in the new assoc-array.
	 * @param  mixed  $key_column  Index to use as the key in the sub-assoc-array.
	 * @param  boolean  $row_objects  (optional) Handle each each array-row as an object. (default: false)
	 *
	 * @return  array  Assoc-array of assoc-arrays ( group => array ( key => array ( values) ) ).
	 */
	public static function extractDoubleGroupedRows($array, $group_column, $key_column, $row_objects = false) {
		if (!is_array($array)) { return array(); }

		$extracted_array = array();

		if ($row_objects) {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row->$group_column][$array_row->$key_column][] = $array_row;
			}
		}  else {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row[$group_column]][$array_row[$key_column]][] = $array_row;
			}
		}
		return $extracted_array;
	}// /method



	/**
	 * Extracts three columns of from a 2D array, and returns them as an assoc-array of grouped key-value pairs.
	 *
	 * Also works with arrays of objects, if $row_objects is true.
	 *
	 * WARNING : Does not check if the columns exist!
	 *
	 * e.g.
	 * extractDoubleGroupedValues($people, 'dept', 'type', 'forename')
	 *
	 * Might give:
	 *
	 * array = (
	 *   'el'  => array (
	 *   	'staff'    => array ( 'Alice', 'Bob' , ... ) ,
	 *   	'student'  => array ( 'Elaine', 'Fred', ... ) ,
	 *   ) ,
	 *   'mc'  => array (
	 *   	'staff'    => array ( 'Michelle', 'Neil', ... ) ,
	 *   	'student'  => array ( 'Olivia', 'Paul', ... ) ,
	 *   ) ,
	 *   ...
	 * )
	 *
	 * @param  array  $array  Array to process.
	 * @param  mixed  $group_column  Index to use as the key in the new assoc-array.
	 * @param  mixed  $key_column  Index to use as the key in the sub-assoc-array.
	 * @param  mixed  $values_column  Index to use when fetching values for the sub-assoc-array.
	 * @param  boolean  $row_objects  (optional) Handle each each array-row as an object. (default: false)
	 *
	 * @return  array  Assoc-array of assoc-arrays ( group => array ( key => array ( values) ) ). On fail, null.
	 */
	public static function extractDoubleGroupedValues($array, $group_column, $key_column, $values_column, $row_objects = false) {
		if (!is_array($array)) { return array(); }

		$extracted_array = array();

		if ($row_objects) {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row->$group_column][$array_row->$key_column][] = $array_row->$values_column;
			}
		}  else {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row[$group_column]][$array_row[$key_column]][] = $array_row[$values_column];
			}
		}
		return $extracted_array;
	}// /method



	/**
	 * Extracts three columns of from a 2D array, and returns them as an assoc-array of key-values pairs.
	 *
	 * Also works with arrays of objects, if $row_objects is true.
	 *
	 * WARNING : Does not check if the columns exist!
	 *
	 * e.g.
	 * extractGroupedAssoc($people, 'dept', 'id', 'forename')
	 *
	 * Might give:
	 *
	 * array = (
	 *   'el'  => array (
	 *   	'00001'  => 'Alice' ,
	 *   	'00002'  => 'Bob' ,
	 *   ) ,
	 *   'mc'  => array (
	 *   	'00013'  => 'Michelle' ,
	 *   	'00014'  => 'Neil' ,
	 *   ) ,
	 *   ...
	 * )
	 *
	 * @param  array  $array  Array to process.
	 * @param  mixed  $group_column  Index to use as the first group-key in the new assoc-array.
	 * @param  mixed  $key_column  Index to use as the second group-key in the sub-assoc-array.
	 * @param  mixed  $value_column  Index to use as the value in the sub-assoc-array.
	 * @param  boolean  $row_objects  (optional) Handle each each array-row as an object. (default: false)
	 *
	 * @return  array()  Assoc-array of assoc-arrays ( key => value ).
	 */
	public static function extractGroupedAssoc($array, $group_column, $key_column, $value_column, $row_objects = false) {
		if (!is_array($array)) { return array(); }

		$extracted_array = array();

		if ($row_objects) {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row->$group_column][$array_row->$key_column] = $array_row->$value_column;
			}
		}  else {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row[$group_column]][$array_row[$key_column]] = $array_row[$value_column];
			}
		}
		return $extracted_array;
	}// /method



	/**
	 * Get an associative array, keyed using the given index, but where each key points to an array of matching row-values.
	 *
	 * Also works with arrays of objects, if $row_objects is true.
	 *
	 * WARNING : Does not check if the column exists!
	 *
	 * e.g.
	 * extractGroupedRows($people, 'type')
	 *
	 * Might give:
	 *
	 * array = (
	 *   'staff'    => array ( row_1 , row_2, ... ) ,
	 *   'student'  => array ( row_3 , row_4, row_5, ... ) ,
	 * )
	 *
	 * @param  array  $array	Array to convert.
	 * @param  mixed  $key_column  Index/Key to use as the key in the new associative array.
	 * @param  boolean  $row_objects  (optional) Handle each each array-row as an object. (default: false)
	 *
	 * @return  array  An associated array of the form : array ( key => array( rows) , key => array( rows) , ... )
	 */
	public static function extractGroupedRows($array, $key_column, $row_objects = false) {
		if (!is_array($array)) { return array(); }

		$extracted_array = array();

		if ($row_objects) {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row->$key_column][] = $array_row;
			}
		} else {
			foreach($array as $i => $array_row) {
				$extracted_array["{$array_row[$key_column]}"][] = $array_row;
			}
		}// /foreach

		return $extracted_array;
	}// /method



	/**
	 * Extracts two columns of values from a 2D array, and returns them as an assoc-array of multiple values.
	 *
	 * Also works with arrays of objects, if $row_objects is true.
	 *
	 * WARNING : Does not check if the columns exist!
	 *
	 * e.g.
	 * extractGroupedValues($people, 'type', 'forename')
	 *
	 * Might give:
	 *
	 * array = (
	 *   'staff'    => array ( 'Alice', 'Bob' , ... ) ,
	 *   'student'  => array ( 'Elaine', 'Fred', ... ) ,
	 * )
	 *
	 * @param  array  $array  Array to process.
	 * @param  mixed  $key_column  Index to use as the key in the new assoc-array.
	 * @param  mixed  $value_column  Index to use as the value in the new assoc-array.
	 * @param  boolean  $row_objects  (optional) Handle each each array-row as an object. (default: false)
	 *
	 * @return  mixed  Assoc-array of key => array of values. On fail, null.
	 */
	public static function extractGroupedValues($array, $key_column, $value_column, $row_objects = false) {
		if (!is_array($array)) { return array(); }

		$extracted_array = array();

		if ($row_objects) {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row->$key_column][] = $array_row->$value_column;
			}
		}  else {
			foreach($array as $i => $array_row) {
				$extracted_array[$array_row[$key_column]][] = $array_row[$value_column];
			}
		}
		return $extracted_array;
	}// /method



	/**
	 * Extract all array keys and force each key to be of type 'string'.
	 *
	 * @param  array  $array
	 *
	 * @return  array  The array keys.
	 */
	public static function extractStringKeys($array) {
		if (!is_array($array)) { return array(); }

		$keys = array_keys($array);

		return array_map('strval', $keys);
	}// /method



	/**
	 * Extract name-value pairs from an assoc-array, where the keys start with the given prefix.
	 *
	 * e.g.
	 * if your form contains a series of checkboxes of the form:
	 * <input type="checkbox" name="student_0001" value="1" />
	 * <input type="checkbox" name="student_0002" value="1" />
	 * ...
	 * You can use:  array_extract_prefixed($_POST, 'student_', true)
	 * To get an assoc-array :  array ( '0001' => 1 , '0002' => 1 , ... )
	 *
	 * If stripping the prefix leaves an empty key, that key will be ignored.
	 *
	 * @param  array  $array  The array to process.
	 * @param  string  $key_prefix  The prefix to check for when reading keys.
	 * @param  boolean  $strip_prefix  (optional) Remove the prefix from the key when returning the results? (default: false).
	 *
	 * @return  array  Assoc-array of results.
	 */
	public static function filterKeysPrefixed($array, $key_prefix, $strip_prefix = false) {
		if (!is_array($array)) { return array(); }

		$extracted_assoc = array();

		if ($strip_prefix) {
			foreach($array as $k => $v) {
				if (strpos($k, $key_prefix)===0) {
					$key = ($strip_prefix) ? substr($k, strlen($key_prefix), strlen($k)) : $k ;
					if (!empty($key)) {
						$extracted_assoc[$key] = $v;
					}
				}
			}
		} else {
			foreach($array as $k => $v) {
				if (strpos($k, $key_prefix)===0) {
					$extracted_assoc[$k] = $v;
				}
			}
		}

		return $extracted_assoc;
	}// /method



	/**
	 * Extract array items where the keys match the given regular expression.
	 *
	 * @param  array  $array  The array to process.
	 * @param  string  $pattern  The pattern to match against.
	 *
	 * @return  array  Assoc-array of results.
	 */
	public static function filterKeysRegex($array, $pattern) {
		if (!is_array($array)) { return array(); }

		$extracted_assoc = null;

		foreach($array as $k => $v) {
			if (preg_match($pattern, $v)) {
				$extracted_assoc[$key] = $v;
			}
		}

		return $extracted_assoc;
	}// /method



	/**
	 * Extract array items where the values match the given regular expression.
	 *
	 * @param  array  $array  The array to process.
	 * @param  string  $pattern  The pattern to match against.
	 * @param  boolean  $preserve_keys  (optional) Preserve the assoc keys in the resulting array. (default: false)
	 *
	 * @return  array  Assoc-array of results.
	 */
	public static function filterValuesRegex($array, $pattern, $preserve_keys = false) {
		if (!is_array($array)) { return array(); }

		$extracted_assoc = null;

		if ($preserve_keys) {
			foreach($array as $k => $v) {
				if (preg_match($pattern, $v)) {
					$extracted_assoc[$key] = $v;
				}
			}
		} else {
			foreach($array as $k => $v) {
				if (preg_match($pattern, $v)) {
					$extracted_assoc[] = $v;
				}
			}
		}

		return $extracted_assoc;
	}// /method



	/**
	 * Find the given value in a multi-dimensional array, and return the keys needed to locate that value.
	 *
	 * @param  mixed  $needle  The value to search for.
	 * @param  array  $array  The array to search.
	 * @param  mixed  $path  (output) The path found, as an array of keys. If not found, null.
	 * @param  boolean  $strict  (optional) Use strict (===) matching. (default: false).
	 *
	 * @return  boolean  The value was found during the search.
	 */
	public static function findPath($needle, $array, &$path, $strict = false) {
		if (!is_array($array)) { return false; }

		$found = false;
		$path = (empty($path)) ? array() : $path ;

		foreach($array as $key => $value) {
			array_push($path, $key);

			$found = ($strict===true) ? ($value===$needle) : ($value==$needle) ;

			if ( (!$found) && (is_array($value)) ) {
				$found = self::findPath($needle, $value, $path, $strict);
			}

			if ($found) {
				return true;
			} else {
				array_pop($path);
			}
		}

		$path = (empty($path)) ? null : $path ;

		return $found;
	}// /method



	/**
	 * Return all the differences between both arrays.
	 *
	 * This differs from array_diff() which would only return those elements in array2 missing from array1.
	 * This function returns the differences between both arrays.
	 *
	 * When there is a difference in element value, the first array is assumed to be correct.
	 *
	 * As with array_merge(), if the input arrays have the same string keys, then the later value for
	 * that key will overwrite the previous one. If, however, the arrays contain numeric keys,
	 * the later value will not overwrite the original value, but will be appended.
	 *
	 * @param  array  $array1  First array to compare.
	 * @param  array  $array2  Second array to compare.
	 *
	 * @return  array  Array of every difference between the two arrays.
	 */
	public static function getDifferences($array1, $array2) {
		$array1 = (array) $array1;
		$array2 = (array) $array2;
		return array_merge( array_diff($array2, $array1), array_diff($array1, $array2) );
	}// /method



	/**
	 * Get a value from an array using the given 'path' of array-keys
	 *
	 * @param  path  One or more array-keys to use.
	 * @param  array  The array to traverse.
	 * @param  mixed  (optional) The default value. (default: null)
	 *
	 * @return  mixed  The value of the element at the given path. On fail, the default.
	 */
	public static function getPathValue($path, $array, $default = null) {
		if (!is_array($array)) { return $default; }

		foreach($path as $i => $key) {
			if (array_key_exists($key, $array)) {
				$array = $array[$key];
			} else {
				return $default;
			}
		}

		// All the path-keys must have been present, $array will now be the correct value
		return $array;
	}// /->method



	/**
	 * Implode an associative array.
	 *
	 * First it implodes the key-value pairs =>  key + $inner_glue + value.
	 * Then it implodes the array =>  key+$inner_glue+value + $outer_glue + key+$inner_glue+value ...
	 * Useful for building query strings, and similar constructs.
	 *
	 * @param  string  $inner_glue  String to use for concatenating each key to each value.
	 * @param  string  $outer_glue  String to use for concatenating each key-value pair to the next pair.
	 * @param  array  $pieces  Array to implode
	 *
	 * @return  string  The imploded string.
	 */
	public static function implodeAssoc($inner_glue, $outer_glue, $pieces) {
		$pieces = (array) $pieces;

		$output = array();
		foreach($pieces as $key => $item ) {
			$output[] = $key . $inner_glue . $item;
		}
		return implode($outer_glue, $output);
	}// /method



	/**
	 * Insert the given values into an array, shifting existing values to the right until the first 'space' (null).
	 *
	 * If the index doesn't exist, the value is simply appended.
	 * If the index is currently unoccupied (null), the value is simply written there.
	 * If the index is currently occupied, then the smart-insert is used.
	 *
	 * e.g.
	 * An array (0-based) = [A][B][C][ ][D][ ]    ( where [ ] = null)
	 * Normally, inserting X at index 1 = [A][X][B][C][ ][D][ ]
	 * D has been shifted to the right, but could've remained in its original position if we utilized
	 * the empty position between C & D.  This empty-cell usage is done automatically in insertSmart(),
	 * so the actual resulting array = [A][X][B][C][D][ ]
	 *
	 * @param  array  $array  The array to change.
	 * @param  integer  $index  The index to insert into.
	 * @param  mixed  $value  The value to insert. (only one value can be inserted).
	 *
	 * @return  array  The new array.
	 */
	public static function insertSmart($array, $index, $value) {
		// If the index doesn't exist, append to the array
		if (!array_key_exists($index, $array)) {
			$array[] = $value;
		} else {
			// If index requested is null, just overwrite it
			if (is_null($array[$index])) {
				$array[$index] = $value;
			} else {
				// Insert into the array (everything is shuffled down 1)
				array_splice($array, $index, 0, array($value));

				$count = count($array);

				// Find first null *after* the inserted position
				$first_null = null;
				$i = $index;
				while ( ($i<$count) && (is_null($first_null)) ) {
					if (is_null($array[$i])) {
						$first_null = $i;
					}
					$i++;
				}

				if (!is_null($first_null)) {
					unset($array[$first_null]);
					$array = array_values($array);
				}
			}
		}
		return $array;
	}// /method



	/**
	 * Is the given variable an associative array.
	 *
	 * @param  array  $array  The array to test.
	 *
	 * @return  boolean  The given array is associative.
	 */
	public static function isAssoc($array) {
		return (is_array($array)) && (array_keys($array)!==range(0, count($array)-1));
	}// /method



	/**
	 * Merge rows where only partial data is present.
	 *
	 * Useful if you've imported a spreadsheet, where a record occupies more than one row.
	 * By default, rows are judged to be partial if the given column, after trimming, has non-zero length.
	 * Partial row columns are merged if, after trimming, they have non-zero length.
	 * Columns can only be merged to previous good rows.  If no previous good row exists, the row will be kept as-is.
	 *
	 * Override the partial-row check function using the $row_function parameter.
	 * Override the merge-column check function using the $column_function parameter.
	 *
	 * Check functions should be of the form:
	 *   function ($column_value) {} : boolean
	 * And return true if a column value is 'good'.
	 *
	 * e.g.
	 * Make | Model | Feature
	 * Ford | Focus | Airbags
	 *      |       | CD Player
	 *      |       | Sunroof
	 *
	 * ::mergePartialRows($array, 'make', ' & ')
	 *
	 * Becomes
	 *
	 * Make | Model | Feature
	 * Ford | Focus | Airbags & CD Player & Sunroof
	 *
	 * @param  array  $array
	 * @param  mixed  $column  The column index to check.
	 * @param  string  $glue  The glue to use when combining values.
	 * @param  callback  $check_function  (optional)  (default: null)
	 *
	 * @return  array  The merged array.
	 */
	public static function mergePartialRows($array, $column, $glue = '', $row_function = null, $column_function = null) {
		if ( (empty($array)) || (!is_array($array)) ) { return array(); }

		$trimmed_nolength = function($column_value) {
			return (strlen(trim($column_value))>0);
		};

		if (!is_callable($row_function)) { $row_function = $trimmed_nolength; }
		if (!is_callable($column_function)) { $column_function = $trimmed_nolength; }


		$new_array = array();
		$last_good_row = null;

		foreach($array as $i => $row) {
			if ( (is_null($last_good_row)) || ($row_function($row[$column])) ) {
				$new_array[$i] = $row;
				$last_good_row = $i;
			} else {
				// Process partial row
				foreach($row as $col => $value) {
					if ($column_function($value)) {
						$new_array[$last_good_row][$col] .= $glue . trim($value);
					}
				}
			}

		}

		return $new_array;
	}// /method



	public static function removeEmptyRows($array) {
		if ( (empty($array)) || (!is_array($array)) ) { return array(); }

		return array_filter($array, function($row) {
			foreach($row as $k => $v) {
				if (strlen(trim($v))>0) { return true; }
			}
			return false;
		});
	}// /method



	/**
	 * Remove any elements with the given value from the array.
	 *
	 * Unlike unset(), normal arrays will have their indexes reset.
	 * Assoc-arrays have their keys preserved.
	 *
	 * @param  array  $array  The array to check.
	 * @param  mixed  $value  The value, or array of values, to remove.
	 *
	 * @return  array  The resulting array.
	 */
	public static function removeValues($array, $value) {
		return array_merge(array_diff($array, (array) $value));
	}// /method



	/**
	 * Search the 2d array for matching values in the given column.
	 *
	 * If $return_index is null, on match, the entire row is returned.
	 * If a $return_index is givenReturns either the whole row or just the corresponding column.
	 *
	 * @param  string  $needle  The text to search for.
	 * @param  array  $array  The array to search.
	 * @param  mixed  $search_column  Index/Key to search in each row.
	 * @param  mixed  $return_column  (optional)  The Index/Key value to return when a match is found.  If null, the entire row is returned. (default: null)
	 *
	 * @return  mixed  The value of contained in the given $return_index in the matching row. If no $return_index, returns the entire matching row.
	 */
	public static function search($needle, $array, $search_column, $return_column = null) {
		if (!is_array($array)) { return null; }

		$arr_count = count($array);
		if ($arr_count>0) {
			for ($i=0; $i<$arr_count; ++$i) {
				if ($array[$i][$search_column]==$needle) {
					if (is_null($return_column)) {
						return (array) $array[$i];
					} else {
						return $array[$i][$return_column];
					}
					break;
				}
			}
		}
	}// /method



	/**
	 * Sort an array of objects using the given property.
	 *
	 * Any objects that do not have $property will be sorted as if $obj->property is null.
	 *
	 * @param  array  $arr  The array of objects to sort.
	 * @param  string  $property  The property to sort on.
	 * @param  boolean  $ascending  (optional) Sort in ascending order. (default: true)
	 *
	 * @return  array  The sorted array of objects.
	 */
	public static function sortObjects($array, $property, $ascending = true) {

		if ( (empty($array)) || (!is_array($array)) ) { return array(); }

		// Create an array containing just the values we're sorting on
		$sort_values = array();
		foreach($array as $i => $item) {
			//echo("$i == {$item->$property}<br />");
			$sort_values[$i] = (property_exists($item, $property)) ? $item->$property : null ;
		}

		// Sort the values
		if ($ascending) {
			asort($sort_values);
		} else {
			arsort($sort_values);
		}
		reset($sort_values);

		// Create a new array using the sorted values
		while (list ($arr_key, $arr_val) = each ($sort_values)) {
			$sorted_arr[] = $array[$arr_key];
		}
		return $sorted_arr;
	}// /method



	/**
	 * Sort a 2-Dimensional array on values in the given key.
	 *
	 * Any array elements that do not contain $key will be sorted as if $key is null.
	 *
	 * @param  array  $arr  The array to sort.
	 * @param  mixed  $key  Index/Key to sort on.
	 *
	 * @return  mixed  The sorted array. On fail, null.
	 */
	public static function sortOnKey($array, $key_column = 0) {

		if ( (!is_array($array)) || (empty($array)) ) { return array(); }

		// Create an array containing just the values we're sorting on
		for ($i=0; $i<sizeof($array); $i++) {
			$sort_values[$i] = (array_key_exists($key, $array[$i])) ? $array[$i][$key] : null ;
		}

		// Sort the values
		asort($sort_values);
		reset($sort_values);

		$sorted_arr = null;
		// Create a new array using the sorted values
		while (list ($arr_key, $arr_val) = each ($sort_values)) {
			$sorted_arr[] = $array[$arr_key];
		}
		return $sorted_arr;
	}// /method



	/**
	 * Performs a stable sort on the given array's values, using the callback function provided.
	 *
	 * Uses the Merge Sort algorithm.
	 * Preserves keys.
	 *
	 * @param  array  $array
	 * @param  callback  $callback
	 *
	 * @return  array  The sorted array.
	 */
	public static function sortStable($array, $callback) {
		if (count($array) < 2) { return $array; }

		$half = count($array) / 2;
		$array1 = array_slice($array, 0, $half, true);
		$array2 = array_slice($array, $half, true);

		// Sort the two halves
		self::sortStable($array1, $callback);
		self::sortStable($array2, $callback);

	    // If the arrays are now sorted, we're done
	    if ($callback(end($array1), reset($array2)) < 1) {
	        return array_merge($array1, $array2);
	    }

	    // Merge the two halves back together
	    $array = array();

	    $array1_i = 0;
	    $array2_i = 0;

	    while ($array1_i < count($array1) && $array2_i < count($array2)) {
	        if ($callback($array1[$array1_i], $array2[$array2_i]) < 1) {
	            $array[] = $array1[$array1_i++];
	        } else {
	            $array[] = $array2[$array2_i++];
	        }
	    }

	    // Merge any remaining items
	    while ($array1_i < count($array1)) $array[] = $array1[$array1_i++];
	    while ($array2_i < count($array2)) $array[] = $array2[$array2_i++];
	    return $array;   // Should this be return; ?
	}// /method



	/**
	 * Split an array into the given number of pieces.
	 *
	 * If there are not enough items for the requested number of pieces, then the number of pieces will equal the item-count.
	 * Returns an array of array-pieces, format: array ( 0 => array ( item1, item2, ..), 1 => array (item3, item4, ..), .. )
	 *
	 * @param  array  $array  The array to split.
	 * @param  integer  $pieces  The number of pieces required.
	 *
	 * @return  array  An array of array-pieces.
	 */
	public static function split($array, $pieces) {

		if (!is_array($array)) { return array(); }

		$count = count($array);

		// If we only need one piece, just output the array in a single piece.
		if ($pieces<2) {
			$split_array[0] = $array;
			return $split_array;
		}

		$normal_piece_length = floor($count / $pieces);   // Every piece has at least this many items in it
		$remainder = $count % $pieces;   // The number of 'extra' items that need allocating

		$split_array = array();

		$piece = 0;
		$piece_count = 0;
		$added_remainder = false;

		foreach($array as $k => $v) {
			$piece_count++;
			$split_array[$piece][$k] = $v;

			if ($piece_count>=$normal_piece_length) {
				if ($remainder==0) {
					$piece++;
					$piece_count = 0;
				} else {
					if ($added_remainder) {
						$added_remainder = false;
						$piece++;
						$piece_count = 0;
					} else {
						$remainder--;
						$added_remainder = true;
					}
				}
			}
		}

		return $split_array;
	}// /method



	/**
	 * Swap two elements in an array.
	 *
	 * This function does no error checking on the input array, or the keys.
	 *
	 * @param  array  $arr  (in/out) The array to swap elements in.
	 * @param  mixed  $key1  The Index/Key identifying the first element to swap.
	 * @param  mixed  $key2  The Index/Key identifying the second element to swap.
	 */
	public static function swap(&$array, $key1, $key2) {
		$temp = $array[$key1];
		$array[$key1] = $array[$key2];
		$array[$key2] = $temp;
	}// /swap()



}// /class
?>