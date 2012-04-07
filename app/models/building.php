<?php
/**
 * Building class
 *
 * @version 1.0.0
 */
class Building {

	// Public Properties
	public $id = null;        // The internal ID (numeric)
	public $code = '';        // The building code (if applicable)
	public $name = '';        // The building's name
	public $site_id = null;   // The ID of the campus the building resides on



	public function __get($name) {
		if ('url_suffix' == $name) {
			$name = strtolower($this->name);
			$name = str_replace(array (',', '/'), '_', $name);
			return urlencode($name) ."/{$this->id}";
		}
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



}// /class
?>