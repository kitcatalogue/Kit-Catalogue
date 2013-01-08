<?php
/**
 * Building class
 *
 * @version 1.0.0
 */
class Building {

	// Public Properties
	public $id = null;        // The internal ID (numeric)
	public $code = '';
	public $name = '';
	public $site_id = null;
	public $latitude = null;
	public $longitude = null;

	public function __get($name) {
		switch ($name) {
			case 'idslug':
				return "{$this->id}/". preg_replace('/[^a-z0-9]+/', '-', strtolower($this->name)) .'.html';
				break;
			case 'name':
				return (empty($this->code)) ? $name : "{$name} ({$code})" ;
				break;
			case 'url_suffix':
			case 'slug':
				return preg_replace('/[^a-z0-9]+/', '-', strtolower($this->name)) ."/{$this->id}";
				break;
		}
	} // /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



}// /class
?>