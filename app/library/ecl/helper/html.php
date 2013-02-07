<?php
/**
 * HTML helper class.
 *
 * Contains helper functions for drawing all manner of HTML structures, especially form components.
 * $value parameters are generally escaped prior to displaying in most of these methods.
 *
 * @package  Ecl
 * @static
 * @version  6.4.0
 */
class Ecl_Helper_Html {


	protected static $_unique_ids = array();


	/**
	 * Constructor
	 */
	private function __construct($config = null) {
	}// /->__construct()



	/* --------------------------------------------------------------------------------
	 * Public Methods
	 */



	/**
	 * Convert an array of attributes to an HTML string.
	 *
	 * If $attr is not an array, or is empty, it will be returned as is.
	 *
	 * @param  array  $attr  The attributes array
	 * @param  boolean  $escape  (optional) Should attribute values be escaped?  (default: true)
	 *
	 * @return  string  The attributes in HTML form.
	 */
	public static function convertAttrToHtml($attr) {
		if (empty($attr)) { return ''; }

		if (!is_array($attr)) { return $attr; }

		$html = '';
		foreach((array) $attr as $k => $v) {
			$html .= sprintf('%1$s="%2$s" ', self::escape($k), self::escape($v));
		}
		return trim($html);
	}// /method



	/**
	 * @param  string  $string
	 *
	 * @return  string
	 */
	public static function escape($string, $charset = 'UTF-8') {
		return htmlspecialchars($string, ENT_COMPAT | ENT_IGNORE, $charset, false);
	}// /method



	/**
	 * Draw a <form> tag.
	 *
	 * Remember you need to close the form tag yourself.
	 *
	 * @param  string  $action
	 * @param  string  $name  The name, and ID, of the form.
	 * @param  string  $method  (optional) The HTTP method to use. (default: 'post')
	 * @param  string  $accept_charset  (optional) The charset to accept.  (default: 'UTF-8')
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function form($name, $action, $method = 'post', $accept_charset = 'UTF-8', $attr = null) {
		$name = self::escape($name);
		$action = self::escape($action);
		$method = self::escape($method);
		$accept_charset = self::escape($accept_charset);
		$attr = self::convertAttrToHtml($attr);
		printf('<form action="%2$s" method="%3$s" name="%1$s" id="%1$s" accept-charset="%4$s" %5$s>', $name, $action, $method, $accept_charset, $attr);
		return true;
	}// /method



	/**
	 * Draw a <button> tag.
	 *
	 * @param  string  $name  The name, and ID, of the button.
	 * @param  string  $value
	 * @param  string  $content  (optional) The HTML content for the button.  (default: $value)
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formButton($name, $value, $content, $attr = null) {
		$value = self::escape($value);
		$attr = self::convertAttrToHtml($attr);
		if (empty($content)) { $content = $value; }
		printf('<button name="%1$s" id="%1$s" value="%2$s" %4$s>%3$s</button>', $name, $value, $content, $attr);
		return true;
	}// /method



	/**
	 * Draw an <input type="button"> tag.
	 *
	 * @param  string  $name  The name, and ID, of the button.
	 * @param  string  $value
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formInputButton($name, $value, $attr = null) {
		$value = self::escape($value);
		$attr = self::convertAttrToHtml($attr);
		printf('<input type="button" name="%1$s" id="%1$s" value="%2$s" %3$s />', $name, $value, $attr);
		return true;
	}// /method



	/**
	 * Draw an <input type="checkbox"> tag.
	 *
	 * @param  string  $name
	 * @param  string  $id
	 * @param  string  $value
	 * @param  boolean  $selected  (optional) Should this checkbox be checked.
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formCheckbox($name, $id, $value, $selected = false, $attr = null) {
		$value = self::escape($value);
		$selected = ($selected) ? 'checked="checked"' : '' ;
		$attr = self::convertAttrToHtml($attr);

		printf('<input type="checkbox" name="%1$s" id="%2$s" value="%3$s" %4$s %5$s />', $name, $id, $value, $selected, $attr);
		return true;
	}// /method



	/**
	 * Draw a named set of <input type="checkbox"> tags in a table.
	 *
	 * In the HTML output, checkbox names are automatically suffixed with [] so PHP will treat them as arrays in $_POST.
	 * The resulting table will have a class of "checkbox_grid".
	 *
	 * @param  string  $name
	 * @param  array  $array  The array to display.  An assoc-array of key-value pairs, where key = checkbox value and value = checkbox label.
	 * @param  integer  $columns  (optional) Number of columns to use in the grid. (default: 1)
	 * @param  array  $selected  (optional) An array of values showing which checkbox-tags should be selected. (default: null).
	 * @param  array  $attr  (optional) Other HTML attributes to use on every checkbox.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formCheckboxGrid($name, $array, $columns = 1, $selected = null, $attr = null) {

		if ( (empty($array)) || (!is_array($array)) ) { return false; }

		Ecl::load('Ecl_Helper_Array');

		$columns = (int) $columns;
		$selected = (array) $selected;
		$attr = self::convertAttrToHtml($attr);

		$count = count($array);

		if ($columns<1) { $columns = 1; }
		if ($columns>$count) { $columns = $count; }

		if (Ecl_Helper_Array::isAssoc($array)) {
			$keys_in_cols = Ecl_Helper_Array::split(array_keys($array), $columns);
			$values_in_cols = Ecl_Helper_Array::split(array_values($array), $columns);

			$keys_in_cols = array_map('array_values', $keys_in_cols);
			$values_in_cols = array_map('array_values', $values_in_cols);
		} else {
			$keys_in_cols = Ecl_Helper_Array::split(array_values($array), $columns);
			$keys_in_cols = array_map('array_values', $keys_in_cols);
			$values_in_cols = $keys_in_cols;
		}

		$max_rows = count($keys_in_cols[0]);

		echo("<table class=\"checkbox_grid\">\n");

		$id_index = 1;

		// For every row
		for($row=0; $row<$max_rows; ++$row) {
			echo('<tr>');

			for($col=0; $col<$columns; ++$col) {
				if (!array_key_exists($row, $keys_in_cols[$col])) {
					echo("\n\t<td>&nbsp;</td><td>&nbsp;</td>");
				} else {
					$id = "{$name}_{$id_index}";
					$is_selected = (in_array($keys_in_cols[$col][$row], $selected));

					echo("\n\t<td>");
					self::formCheckbox("{$name}[]", $id, $keys_in_cols[$col][$row], $is_selected, $attr);
					echo("</td><th><label for=\"$id\">{$values_in_cols[$col][$row]}</label></th>");
					$id_index++;
				}
			}// /for(columns)

			echo("\n</tr>\n");
		}// /for(rows)

		echo("</table>\n");

		return true;
	}// /method



	/**
	 * Draw arbritary checkboxes in a table.
	 *
	 * The checkboxes array should be of the form:
	 * array (
	 *   array (
	 *   	'name'      => 'readterms' ,
	 *      'id'        => 'readterms_cbox' ,
	 *      'label'     => 'Yes, I have read and understood the terms and conditions.' ,
	 *      'value'     => '1' ,
	 *      'selected'  => false ,
	 *   ) ,
	 *   ...
	 * );
	 *
	 * @param  array  $checkboxes
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formCheckboxTable($checkboxes, $attr = null) {
		if (empty($checkboxes)) { return true; }

		echo("<table class=\"checkbox_grid\">\n");
		foreach($checkboxes as $cbox) {
			?>
			<tr>
				<td><?php self::formCheckbox($cbox['name'], $cbox['id'], $cbox['value'], $cbox['selected']); ?></td>
				<th><?php self::formLabel($cbox['id'], $cbox['label']); ?></th>
			</tr>
			<?php
		}
		echo('</table>');

		return true;
	}// /method



	/**
	 * Draw a form-close tag.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formClose() {
		echo('</form>');
		return true;
	}// /method



	/**
	 * Draw an <input type="file"> tag.
	 *
	 * @param  string  $name  The name, and ID, of the tag.
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formFile($name, $attr = null) {
		$attr = self::convertAttrToHtml($attr);
		printf('<input type="file" name="%1$s" id="%1$s" %2$s />', $name, $attr);
		return true;
	}// /method



	/**
	 * Draw an <input type="hidden"> tag.
	 *
	 * @param  string  $name  The name, and ID, of the tag.
	 * @param  string  $value
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formHidden($name, $value, $attr = null) {
		$value = self::escape($value);
		$attr = self::convertAttrToHtml($attr);
		printf("\n".'<input type="hidden" name="%1$s" id="%1$s" value="%2$s" %3$s />', $name, $value, $attr);
		return true;
	}// /method



	/**
	 * Draw an <input type="text"> tag.
	 *
	 * You need to escape the value yourself.
	 *
	 * @param  string  $name  The name, and ID, of the tag.
	 * @param  int  $size  The size of the box.
	 * @param  int  $maxlength  The maximum length for the input box.
	 * @param  string  $value  (optional)  (default: null)
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formInput($name, $size, $maxlength, $value = null, $attr = null) {
		$value = self::escape($value);
		$attr = self::convertAttrToHtml($attr);
		printf('<input type="text" name="%1$s" id="%1$s" size="%2$s" maxlength="%3$s" value="%4$s" %5$s />', $name, $size, $maxlength, $value, $attr);
		return true;
	}// /method



	/**
	 * Draw a <label> tag.
	 *
	 * @param  string  $for  The HTML ID of the control the label is for.
	 * @param  string  $title
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formLabel($for, $title, $attr = null) {
		$title = self::escape($title);
		$attr = self::convertAttrToHtml($attr);
		printf('<label for="%1$s" %3$s>%2$s</label>', $for, $title, $attr);
		return true;
	}// /method



	/**
	 * Draw a series of <option> tags.
	 *
	 * The $array parameter is treated as an assoc array in all cases.
	 * i.e.  <option value="array-key"> array-value </option>
	 *
	 * @param  array  $array  The array of options.
	 * @param  string  $selected  (optional) The option which should be selected. (default: null).
	 * @param  array  $attr  (optional) Other HTML attributes to use for every option.  (default: null)
	 *
	 * @return  boolean  The was operation successful.
	 */
	public static function formOptions($options, $selected = null) {

		foreach($options as $k => $v) {
			printf('<option value="%1$s" %3$s> %2$s </option>', $k, $v, ( ($k==$selected) ? ' selected="selected"' : '' ));
		}

		return true;
	}// /method



	/**
	 * Draw a series of <option> tags grouped by <optgroup> tags.
	 *
	 * The $array parameter is treated as an assoc array in all cases.
	 * i.e.  <option value="array-key"> array-value </option>
	 * For Option-Groups, the array format is:
	 * array (
	 *   array-key => array_value ,     // This would be a normal option, not in a group
	 *   'group-name1' => array ( array-key => array-value , .. ) ,
	 *   'group-name2' => array ( array-key => array-value , ..) ,
	 * );
	 *
	 * @param  array  $array  The array to display.
	 * @param  string  $selected  (optional) The option which should be selected. (default: null).
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formOptionGroups($array, $selected = null) {

		foreach($array as $key => $value) {
			if (!is_array($value)) {
				self::formOptions( array($key => $value) , $selected);
			} else {
				echo("<optgroup label=\"$key\">");
				self::formOptions($value, $selected);
				echo('</optgroup>');
			}
		}
		return true;
	}// /method



	/**
	 * Draws a series of <option> tags representing days of the month.
	 *
	 * @param  datetime  $selected  (optional) The selected datetime, the day will be extracted automatically and selected. (default: null).
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formOptionsDay($selected = null) {

		if (!is_null($selected)) { $selected = date('j', $selected); }

		$days = range(1, 31);
		$days = Ecl_Helper_Array::duplicateValueAsKey($days);

		return self::formOptions($days, $selected);
	}// /method



	/**
	 * Draws a series of <option> tags representing months of the year.
	 *
	 * @param  datetime  $selected  (optional) The selected datetime, the month will be extracted automatically and selected. (default: null).
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formOptionsMonth($selected = null) {
		if (!is_null($selected)) { $selected = date('n', $selected); }

		$months = array( 1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		return self::formOptions($months, $selected);
	}// /method



	/**
	 * Draw a series of <option> tags representing a range of values
	 *
	 * You can use positive or negative increments.
	 * Use suffix to output x% for example.
	 *
	 * @param  integer  $start  The start of the range.
	 * @param  integer  $end  The end of the range.
	 * @param  mixed  $increment  The increment for each option value.
	 * @param  string  $suffix  (optional) The suffix for each option's title.
	 * @param  string  $selected  (optional) The option which should be selected. (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formOptionsRange($start, $end, $increment, $suffix = null, $selected = null) {
		if ( (!is_numeric($increment)) || ($increment==0) ) { return false; }

		$curr = $start;

		// If counting up
		if (($start<$end) && ($increment>0)) {
			while ($curr<=$end) {
				echo("<option value=\"{$curr}\"". ( ($curr==$selected) ? ' selected="selected"' : '' ) ."> {$curr}{$suffix} </option>");
				$curr += $increment;
			}
		} elseif (($start>$end) && ($increment<0)) {
			while ($curr>$end) {
				echo("<option value=\"{$curr}\"". ( ($curr==$selected) ? ' selected="selected"' : '' ) ."> {$curr}{$suffix} </option>");
				$curr += $increment;
			}
		} else {
			return false;
		}

		return true;
	}// /method



	/**
	 * Draws a series of <option> tags representing times of day
	 *
	 * @param  integer  $increment  (optional) The increment in minutes. Must be one of the following : 5, 10, 15, 20, 30, 60. (default: 15)
	 * @param  datetime  $selected  (optional) The selected datetime, the time will be extracted automatically and the appropriate time-option selected. (default: null).
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formOptionsTime($increment = 15, $selected = null) {

		if (is_null($selected)) {
			$sel_time = null;
			$sel_time_h = null;
			$sel_time_m = null;
		} else {
			$sel_time = date('H:i', $selected);

			// Get selected time
			$time_parts = explode(':', $sel_time);
			$sel_time_h = (int) $time_parts[0];

			$sel_time_m = (int) $time_parts[1];
			$sel_time_m = (int) floor($sel_time_m / $increment);
			$sel_time_m = $sel_time_m * $increment;
		}

		$increment = (int) $increment;

		$valid_increments = array (
		5  => 12 ,
		10  => 6 ,
		15  => 4 ,
		20  => 3 ,
		30  => 2 ,
		60  => 1 ,
		);

		if (!array_key_exists($increment, $valid_increments)) {
			$increment = 15;
		}

		$increments_per_hour = $valid_increments[$increment];

		for ($hours=0; $hours<=23; $hours++) {
			for ($j=0; $j<$increments_per_hour; $j++) {
				$mins = $j * $increment;
				$selected = ( ($hours === $sel_time_h) && ($mins === $sel_time_m) ) ? 'selected="selected"' : '' ;
				printf('<option value="%1$02d:%2$02d" '. $selected .'> %1$02d:%2$02d </option>', $hours, $mins);
			}
		}

		return true;
	}// /method



	/**
	 * Draws a series of <option> tags representing years.
	 *
	 * @param  integer  $start  (optional) The year to start the options from.  If the $selected datetime predates $start, then $start will be changed accordingly. (Default: this year).
	 * @param  integer  $end  (optional) The year to end the options on. (default: this year).
	 * @param  datetime  $selected  (optional) The selected datetime, the month will be extracted automatically and the appropriate month selected. (default: null).
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formOptionsYear($start = null, $end = null, $selected = null) {
		if (is_null($start)) { $start = date('Y', time()); }
		if (is_null($end)) { $end = date('Y', time()); }

		if (!is_null($selected)) {
			$selected = date('Y', $selected);
			if ($selected<$start) { $start = $selected; }
		}

		$years = array();
		for($year=$start; $year<=$end; $year++) {
			$years[$year] = $year;
		}
		return self::formOptions($years, $selected);
	}// /method



	/**
	 * Draw an <input type="password"> tag.
	 *
	 * You need to escape the value yourself.
	 *
	 * @param  string  $name  The name, and ID, of the tag.
	 * @param  int  $size  The size of the box.
	 * @param  int  $maxlength  The maximum length for the input box.
	 * @param  string  $value  (optional)  (default: null)
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formPassword($name, $size, $maxlength, $value = null, $attr = null) {
		$value = self::escape($value);
		$attr = self::convertAttrToHtml($attr);
		printf('<input type="password" name="%1$s" id="%1$s" size="%2$s" maxlength="%3$s" value="%4$s" %5$s />', $name, $size, $maxlength, $value, $attr);
		return true;
	}// /method



	/**
	 * Draw an <input type="radio"> tag.
	 *
	 * @param  string  $name  The name to use.
	 * @param  string  $id  The id to use.
	 * @param  string  $value  The value to use.
	 * @param  boolean  $selected  (optional) Should this radio button be checked.
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formRadio($name, $id, $value, $selected = false, $attr = null) {
		$value = self::escape($value);
		$selected = ($selected) ? 'checked="checked"' : '' ;
		$attr = self::convertAttrToHtml($attr);

		printf('<input type="radio" name="%1$s" id="%2$s" value="%3$s" %4$s %5$s />', $name, $id, $value, $selected, $attr);
		return true;
	}// /method



	/**
	 * Draw a table of <input type="radio"> tags.
	 *
	 * In the HTML output, radio names are automatically suffixed with [] so PHP will treat them as arrays in $_POST.
	 * The resulting table will have a class of "radio_grid".
	 *
	 * @param  string  $name  The name to give the HTML input tags (e.g.  "interests" ).
	 * @param  array  $array  The array to display.  An assoc-array of key-value pairs, the key will be the radio's value-attribute, and value the radio's label text.
	 * @param  integer  $columns  (optional) Number of columns to use in the grid. (default: 1)
	 * @param  string  $selected  (optional) A string matching the radio-tag value that should be selected. (default: null).
	 * @param  array  $attr  (optional) Other HTML attributes to use on every radio button.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formRadioGrid($name, $array, $columns = 1, $selected = null, $attr = null) {
		$attr = self::convertAttrToHtml($attr);

		if ( (empty($array)) || (!is_array($array)) ) { return false; }

		Ecl::load('Ecl_Helper_Array');

		$selected = (string) $selected;

		$columns = (int) $columns;

		$count = count($array);

		if ($columns<1) { $columns = 1; }
		if ($columns>$count) { $columns = $count; }

		if (Ecl_Helper_Array::isAssoc($array)) {
			$keys_in_cols = Ecl_Helper_Array::split(array_keys($array), $columns);
			$values_in_cols = Ecl_Helper_Array::split(array_values($array), $columns);

			$keys_in_cols = array_map('array_values', $keys_in_cols);
			$values_in_cols = array_map('array_values', $values_in_cols);
		} else {
			$keys_in_cols = Ecl_Helper_Array::split(array_values($array), $columns);
			$keys_in_cols = array_map('array_values', $keys_in_cols);
			$values_in_cols = $keys_in_cols;
		}

		$max_rows = count($keys_in_cols[0]);

		echo("\n<table class=\"radio_grid\">\n");

		$id_index = 1;

		// For every row
		for($row=0; $row<$max_rows; ++$row) {
			echo('<tr>');

			for($col=0; $col<$columns; ++$col) {
				if (!array_key_exists($row, $keys_in_cols[$col])) {
					echo("\n\t<td>&nbsp;</td><td>&nbsp;</td>");
				} else {
					$id = "{$name}_{$id_index}";
					$is_selected = ($keys_in_cols[$col][$row]==$selected);

					echo("\n\t<td>");
					self::formRadio("{$name}", $id, $keys_in_cols[$col][$row], $is_selected, $attr);
					echo("</td><th><label for=\"$id\">{$values_in_cols[$col][$row]}</label></th>");
					$id_index++;
				}
			}// /for(columns)

			echo("\n</tr>\n");
		}// /for(rows)

		echo("</table>\n");

		return true;
	}// /method



	/**
	 * Draw a select box using the given parameters.
	 *
	 * @param  string  $name
	 * @param  array  $options  The options to use.  Array keys will be used as the option tag's value attributes, and the values used as the text.
	 * @param  string  $selected  (optional) The option which should be selected. (default: null).
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formSelect($name, $options, $selected = null, $attr = null) {
		$attr = self::convertAttrToHtml($attr);
		printf('<select name="%1$s" id="%1$s" %2$s>', $name, $attr);
		self::formOptions($options, $selected);
		echo("</select>\n");
		return true;
	}// /method



	/**
	 * Draw a select box with grouped-options using the given parameters.
	 *
	 * @param  string  $name
	 * @param  array  $options  The options to use.  Array keys will be used as the option tag's value attributes, and the values used as the text.  See ->drawOptionGroups() for more information on option-groups.
	 * @param  mixed  $selected  (optional) The option which should be selected. (default: null).
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formSelectGrouped($name, $options, $selected = null, $attr = null) {
		$attr = self::convertAttrToHtml($attr);
		printf('<select name="%1$s" id="%1$s" %2$s>', $name, $attr);
		self::formOptionGroups($options, $selected);
		echo("</select>\n");
		return true;
	}// /method



	/**
	 * Draw a full set of Day-Month-Year-Time select boxes.
	 *
	 * Each select tag will be suffixed with the name of the date-element being used, e.g.
	 * open_date_day, open_date_month, open_date_year, open_date_time.
	 *
	 * @param  string  $name_stub  The name each HTML select tag will begin with (e.g.  "open_date" ).
	 * @param  integer  $start_year  (optional) The year to start the options from. Can be overridden by the $selected date. (Default: this year).
	 * @param  integer  $end_year  (optional) The year to end the options on. (Default: this year).
	 * @param  integer  $time_increment  (optional) The time increment in minutes. See ->drawOptionsTime() for details. (Default: 15).
	 * @param  datetime  $selected  (optional) The selected datetime. The day, month, year and (nearest) time will be selected automatically. (Default: null).
	 * @param  array  $attr  (optional) Other HTML attributes to use on every select box.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formSelectsDmyt($name_stub, $start_year = null, $end_year = null, $time_increment = 15, $selected = null, $attr = null) {
		$attr = self::convertAttrToHtml($attr);
		?>
	<table class="dmyt_grid">
	<tr>
		<td><select name="<?php echo($name_stub); ?>_day"
			id="<?php echo($name_stub); ?>_day" <?php echo($attr); ?>>
			<?php self::formOptionsDay($selected); ?>
		</select></td>
		<td><select name="<?php echo($name_stub); ?>_month"
			id="<?php echo($name_stub); ?>_month" <?php echo($attr); ?>>
			<?php self::formOptionsMonth($selected); ?>
		</select></td>
		<td><select name="<?php echo($name_stub); ?>_year"
			id="<?php echo($name_stub); ?>_year" <?php echo($attr); ?>>
			<?php self::formOptionsYear($start_year, $end_year, $selected); ?>
		</select></td>
		<th>at</th>
		<td><select name="<?php echo($name_stub); ?>_time"
			id="<?php echo($name_stub); ?>_time" <?php echo($attr); ?>>
			<?php self::formOptionsTime($time_increment, $selected); ?>
		</select></td>
	</tr>
	</table>
		<?php
		return true;
	}// /method



	/**
	 * Draw a full set of Day-Month-Year select boxes.
	 *
	 * Each select tag will be suffixed with the name of the date-element being used, e.g.
	 * open_date_day, open_date_month, open_date_year.
	 *
	 * @param  string  $name_stub  The name each HTML select tag will begin with (e.g.  "open_date" ).
	 * @param  integer  $start_year  (optional) The year to start the options from. Can be overridden by the $selected date. (Default: this year).
	 * @param  integer  $end_year  (optional) The year to end the options on. (Default: this year).
	 * @param  date  $selected  (optional) The selected datetime. The day, month and year (and time) will be selected automatically. (Default: null).
	 * @param  array  $attr  (optional) Other HTML attributes to use on every select box.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formSelectsDmy($name_stub, $start_year = null, $end_year = null, $selected = null, $attr = null) {
		$attr = self::convertAttrToHtml($attr);
		?>
	<table class="dmyt_grid">
	<tr>
		<td><select name="<?php echo($name_stub); ?>_day"
			id="<?php echo($name_stub); ?>_day" <?php echo($attr); ?>>
			<?php self::formOptionsDay($selected); ?>
		</select></td>
		<td><select name="<?php echo($name_stub); ?>_month"
			id="<?php echo($name_stub); ?>_month" <?php echo($attr); ?>>
			<?php self::formOptionsMonth($selected); ?>
		</select></td>
		<td><select name="<?php echo($name_stub); ?>_year"
			id="<?php echo($name_stub); ?>_year" <?php echo($attr); ?>>
			<?php self::formOptionsYear($start_year, $end_year, $selected); ?>
		</select></td>
	</tr>
	</table>
		<?php
		return true;
	}// /method



	/**
	 * Draw a full set of Day-Month-Year select boxes, but allow null-entries (no day/month/year info).
	 *
	 * Each select tag's name and ID  will be suffixed with the date-element being used, e.g.
	 * open_date_day, open_date_month, open_date_year, open_date_time.
	 *
	 * @param  string  $name_stub  The text each select tag's name and ID will begin with (e.g.  "open_date" )
	 * @param  integer  $start_year  (optional) The year to start the options from. Can be overridden by the $selected date. (Default: this year).
	 * @param  integer  $end_year  (optional) The year to end the options on. (Default: this year).
	 * @param  datetime  $selected  (optional) The selected datetime. The day, month, year and (nearest) time will be selected automatically. (Default: null).
	 * @param  array  $attr  (optional) Other HTML attributes to use on every select box.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formSelectsDmyNullable($name_stub, $start_year = null, $end_year = null, $selected = null, $attr = null) {
		if (empty($selected)) { $selected = null; }

		$attr = self::convertAttrToHtml($attr);
		?>
	<table class="dmyt_grid">
	<tr>
		<td><select name="<?php echo($name_stub); ?>_day"
			id="<?php echo($name_stub); ?>_day" <?php echo($attr); ?>>
			<option value=""></option>
			<?php self::formOptionsDay($selected); ?>
		</select></td>
		<td><select name="<?php echo($name_stub); ?>_month"
			id="<?php echo($name_stub); ?>_month" <?php echo($attr); ?>>
			<option value=""></option>
			<?php self::formOptionsMonth($selected); ?>
		</select></td>
		<td><select name="<?php echo($name_stub); ?>_year"
			id="<?php echo($name_stub); ?>_year" <?php echo($attr); ?>>
			<option value=""></option>
			<?php self::formOptionsYear($start_year, $end_year, $selected); ?>
		</select></td>
	</tr>
	</table>
		<?php
		return true;
	}// /method



	/**
	 * Draw a full set of Day-Month-Year-Time select boxes, but allow null-entries (no day/month/year/time info).
	 *
	 * Each select tag's name and ID  will be suffixed with the date-element being used, e.g.
	 * open_date_day, open_date_month, open_date_year, open_date_time.
	 *
	 * @param  string  $name_stub  The text each select tag's name and ID will begin with (e.g.  "open_date" )
	 * @param  integer  $start_year  (optional) The year to start the options from. Can be overridden by the $selected date. (Default: this year).
	 * @param  integer  $end_year  (optional) The year to end the options on. (Default: this year).
	 * @param  integer  $time_increment  (optional) The time increment in minutes. See ->drawOptionsTime() for details. (Default: 15).
	 * @param  datetime  $selected  (optional) The selected datetime. The day, month, year and (nearest) time will be selected automatically. (Default: null).
	 * @param  array  $attr  (optional) Other HTML attributes to use on every select box.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formSelectsDmytNullable($name_stub, $start_year = null, $end_year = null, $time_increment = 15, $selected = null, $attr = null) {
		if (empty($selected)) { $selected = null; }

		$attr = self::convertAttrToHtml($attr);
		?>
	<table class="dmyt_grid">
	<tr>
		<td><select name="<?php echo($name_stub); ?>_day"
			id="<?php echo($name_stub); ?>_day" <?php echo($attr); ?>>
			<option value=""></option>
			<?php self::formOptionsDay($selected); ?>
		</select></td>
		<td><select name="<?php echo($name_stub); ?>_month"
			id="<?php echo($name_stub); ?>_month" <?php echo($attr); ?>>
			<option value=""></option>
			<?php self::formOptionsMonth($selected); ?>
		</select></td>
		<td><select name="<?php echo($name_stub); ?>_year"
			id="<?php echo($name_stub); ?>_year" <?php echo($attr); ?>>
			<option value=""></option>
			<?php self::formOptionsYear($start_year, $end_year, $selected); ?>
		</select></td>
		<th style="padding: 0.3em 0.5em 0.2em 0.5em;">at</th>
		<td><select name="<?php echo($name_stub); ?>_time"
			id="<?php echo($name_stub); ?>_time" <?php echo($attr); ?>>
			<option value=""></option>
			<?php self::formOptionsTime($time_increment, $selected); ?>
		</select></td>
	</tr>
	</table>
		<?php
		return true;
	}// /method



	/**
	 * Draw an <input type="reset"> tag.
	 *
	 * @param  string  $name  The name, and ID, of the button to draw.
	 * @param  string  $value
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formReset($name, $value, $attr = null) {
		$value = self::escape($value);
		$attr = self::convertAttrToHtml($attr);
		printf('<input type="reset" name="%1$s" id="%1$s" value="%2$s" %3$s />', $name, $value, $attr);
		return true;
	}// /method



	/**
	 * Draw an <input type="submit"> tag.
	 *
	 * @param  string  $name  The name, and ID, of the button to draw.
	 * @param  string  $value
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formSubmit($name, $value, $attr = null) {
		$value = self::escape($value);
		$attr = self::convertAttrToHtml($attr);
		printf('<input type="submit" name="%1$s" id="%1$s" value="%2$s" %3$s />', $name, $value, $attr);
		return true;
	}// /method



	public static function formSubmitUnique($name, $value, $attr = null) {
		$html_id = self::_getUniqueHtmlId($name);
		$value = self::escape($value);
		$attr = self::convertAttrToHtml($attr);
		printf('<input type="submit" name="%1$s" id="%2$s" value="%3$s" %4$s />', $name, $html_id, $value, $attr);
		return true;
	}// /method



	/**
	 * Draw a <textarea> tag.
	 *
	 * The content is automatically escaped.
	 *
	 * @param  string  $name  The name, and ID, of the input box to draw.
	 * @param  int  $cols  The number of columns to show.
	 * @param  int  $rows  The number of rows to show.
	 * @param  string  $content  (optional) The contents of the textarea. (default: null)
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function formTextarea($name, $cols, $rows, $content = null, $attr = null) {
		$attr = self::convertAttrToHtml($attr);
		printf('<textarea name="%1$s" id="%1$s" cols="%2$s" rows="%3$s" %5$s>%4$s</textarea>', $name, $cols, $rows, self::escape($content), $attr);
		return true;
	}// /method



	/**
	 * Get the string representation of a generic HTML tag.
	 *
	 * Any name, id, or other attributes must be defined using the $attr parameter.
	 * $close_tag must be true for the $self_close parameter to have any effect.
	 * If $close_tag AND $self_close are true, any $content specified will be ignored.
	 *
	 * $content is automatically escaped for HTML.
	 *
	 * @param  string  $tag  The name of the tag.
	 * @param  string  $content  (optional) The content of the tag.
	 * @param  mixed  $attr  (optional) HTML attribute string, or assoc-array of attributes to use.  (default: null)
	 * @param  boolean  $close_tag  (optional) The tag should be rendered with a closing tag </xxx>.  (default: false)
	 * @param  boolean  $self_close  (optional) If $close_tag is true, then the tag should be self-closed with <xxx />.  (default: false)
	 *
	 * @return  string  The operation was successful.
	 */
	public static function getTag($tag, $content = null, $attr = null, $close_tag = false, $self_close = false) {
		$tag = self::escape($tag);
		$content = self::escape($content);

		// If attributes is an array, convert it to a string.
		if (is_array($attr)) { $attr = self::convertAttrToHtml($attr); }

		if (!$close_tag) {
			return sprintf('<%1$s %3$s>%2$s', $tag, $content, $attr);
		} else {
			if ($self_close) {
				return sprintf('<%1$s %2$s />', $tag, $attr);
			} else {
				return sprintf('<%1$s %3$s>%2$s</%1$s>', $tag, $content, $attr, 'X');
			}
		}
	}// /method



	/**
	 * Draw an <a> tag.
	 *
	 * @param  string  $url
	 * @param  string  $content  (optional) The HTML content for the link.  (default: $url)
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function link($url, $content = null, $attr = null) {
		$value = self::escape($value);
		$attr = self::convertAttrToHtml($attr);
		if (empty($content)) { $content = $url; }
		printf('<a href="%1$s" %3$s>%2$s</a>', $url, $content, $attr);
		return true;
	}// /method



	/**
	 * Draw a <ul> tag and items.
	 *
	 * @param  array  $array  The array to display.
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The was operation successful.
	 */
	public static function listBulleted($array, $attr = null) {
		if  ( (!is_array($array)) || (count($array)==0) ){ return false; }

		$attr = self::convertAttrToHtml($attr);

		echo("<ul {$attr}>");
		foreach($array as $i => $item) {
			$item = self::escape($item);
			echo("<li>$item</li>");
		}
		echo('</ul>');

		return true;
	}// /method



	/**
	 * Draw a <dl> tag and items.
	 *
	 * @param  array  $array  The assoc-array of title-definition pairs to display.
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The was operation successful.
	 */
	public static function listDefinition($array, $attr = null) {
		if  ( (!is_array($array)) || (count($array)==0) ){ return false; }

		$attr = self::convertAttrToHtml($attr);

		if ( ($list_id) || ($list_class) ) {
			$html_list_id = " id=\"$list_id\"";
			$html_list_class = " class=\"$list_class\"";
			echo("<dl {$attr}>");
		} else {
			echo('<dl>');
		}
		foreach($array as $title => $definition) {
			$title = self::escape($title);
			$definition = self::escape($definition);
			echo("<dt>$title</dt>");
			echo("<dd>$definition</dd>");
		}
		echo('</dl>');

		return true;
	}// /method



	/**
	 * Draw an <ol> tag and items.
	 *
	 * @param  array  $array  The array to display.
	 * @param  array  $attr  (optional) Other HTML attributes to use.  (default: null)
	 *
	 * @return  boolean  The was operation successful.
	 */
	public static function listOrdered($array, $attr = null) {
		if  ( (!is_array($array)) || (count($array)==0) ){ return false; }

		$attr = self::convertAttrToHtml($attr);

		echo("<ol {$attr}>");
		foreach($array as $i => $item) {
			$item = self::escape($item);
			echo("<li>$item</li>");
		}
		echo('</ol>');

		return true;
	}// /method



	/**
	 * Echo out an HTML string, encoding entities as required.
	 *
	 * @param  string  $string
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function out($string, $charset = 'UTF-8') {
		echo(self::escape($string, $charset));
	}// /method



	/**
	 * Draw a table using the given array.
	 *
	 * It is assumed that each rows has the same number of columns.
	 *
	 * @param  array  $array
	 * @param  mixed  $attrs
	 */
	public static function tabulate($array, $attr = '') {
		if ( (!is_array($array)) || (empty($array)) ) { return false; }

		$attr = self::convertAttrToHtml($attr);

		printf('<table %1$s>', $attr);
		foreach($array as $y => $row) {
			echo("\n<tr>\n\t");
			foreach($row as $x => $value) {
				$value = self::escape($value);
				echo("<td>$value</td>");
			}
			echo("\n</tr>");
		}
		echo("\n</table>\n");
	}// /method



	/**
	 * Draw a generic HTML tag.
	 *
	 * @See getTag() for more information.
	 *
	 * @param  string  $tag  The name of the tag.
	 * @param  string  $content  (optional) The content of the tag.
	 * @param  mixed  $attr  (optional) HTML attribute string, or assoc-array of attributes to use.  (default: null)
	 * @param  boolean  $close_tag  (optional) The tag should be rendered with a closing tag </xxx>.  (default: false)
	 * @param  boolean  $self_close  (optional) If $close_tag is true, then the tag should be self-closed with <xxx />.  (default: false)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function tag($tag, $content = null, $attr = null, $close_tag = false, $self_close = false) {
		echo self::getTag($tag, $content, $attr, $close_tag, $self_close);
		return true;
	}// /method



	/* --------------------------------------------------------------------------------
	 * Private Methods
	 */



	protected static function _getUniqueHtmlId($name) {
		if (!array_key_exists($name, self::$_unique_ids)) {
			self::$_unique_ids[$name] = 0;
		} else {
			self::$_unique_ids[$name]++;
		}
		return $name . self::$_unique_ids[$name];
	}



}// /class
?>