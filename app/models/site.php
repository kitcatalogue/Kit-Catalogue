<?php
/**
 * Site class
 *
 * @version 1.0.0
 */
class Site {

	// Public Properties
	public $id = null;        // The internal ID (numeric)
	public $name = '';        // The name



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