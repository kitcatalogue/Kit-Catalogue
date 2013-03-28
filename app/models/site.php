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
<<<<<<< HEAD
	public $url = '';
=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd



	public function __get($name) {
		switch ($name) {
<<<<<<< HEAD
			case 'idslug':
				return "{$this->id}/". preg_replace('/[^a-z0-9]+/', '-', strtolower($this->name)) .'.html';
				break;
=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
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