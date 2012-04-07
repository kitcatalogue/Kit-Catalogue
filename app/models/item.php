<?php
/**
 * Item class
 *
 * @version 1.0.0
 */
class Item {

	// Public Properties
	public $id = null;        // The internal ID (numeric)

	public $manufacturer = '';
	public $model = '';
	public $short_description = '';
	public $full_description = '';
	public $specification = '';
	public $acronym = '';
	public $keywords = '';

	public $technique = null;

	public $department = '';
	public $availability = '';
	public $usergroup = '';
	public $access = '';

	public $visibility = 0;

	public $site = '';
	public $building = '';
	public $room = '';

	public $contact_email = null;

	public $image = '';
	public $manufacturer_website = '';

	public $copyright_notice = '';



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /->__construct()



	public function __get($name) {
		if ('url_suffix' == $name) {
			$name = strtolower(trim("{$this->manufacturer} {$this->model}"));
			$name = str_replace(array (',', '/'), '_', $name);
			return urlencode($name) ."/{$this->id}";
		}
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Get the relative path to the item's files.
	 *
	 * An item that has not been created yet (i.e. not saved) will return null.
	 *
	 * @return mixed  The path.  On fail, null.
	 */
	public function getFilePath() {
		if ($this->id) {
			$id = str_pad($this->id, 12, '0', STR_PAD_LEFT);
			$chunk1 = substr($id, 0, 4);
			$chunk2 = substr($id, 0, 8);

			return "/{$chunk1}/{$chunk2}/{$id}";
		} else {
			return null;
		}
	}// /->getFilePath()



}// /class
?>