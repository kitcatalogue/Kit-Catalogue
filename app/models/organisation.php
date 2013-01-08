<?php
/**
 * Organisation class
 *
 * @version 1.0.0
 */
class Organisation {

	// Public Properties
	public $id = null;        // The internal ID (numeric)
	public $name = '';        // The name



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