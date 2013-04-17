<?php
/**
 * Organisational Unit (OU) class
 *
 * @version 1.0.0
 */
class Organisationalunit {

	// Public Properties
	public $id = null;        // The internal ID (numeric)
	public $name = '';        // The name
	public $url = '';

	public $item_count_internal = 0;
	public $item_count_public = 0;

	public $tree_level = null;



	public function __get($name) {
		if ('idslug' == $name) {
			return "{$this->id}/". preg_replace('/[^a-z0-9]+/', '-', strtolower($this->name)) .'.html';
		}
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



}// /class
?>