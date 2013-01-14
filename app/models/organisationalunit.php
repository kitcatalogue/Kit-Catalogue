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

	public $tree_left = null;
	public $tree_right = null;
	public $tree_level = null;



	public function __get($name) {
		switch ($name) {
			case 'idslug':
				return "{$this->id}/". preg_replace('/[^a-z0-9]+/', '-', strtolower($this->name)) .'.html';
				break;
			case 'url_suffix':
			case 'slug':
				return preg_replace('/[^a-z0-9]+/', '-', strtolower($this->name)) ."/{$this->id}";
				break;
		}
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



}// /class
?>