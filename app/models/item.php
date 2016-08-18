<?php
/**
 * Item class
 *
 * @version 1.0.0
 */
class Item {

	const CALIB_YES    = 'yes';
	const CALIB_NO     = 'no';
	const CALIB_AUTO   = 'auto';
	const CALIB_NOTAPP = '';

	const DISPOSED_NO    = '';
	const DISPOSED_SOLD  = 'sold';
	const DISPOSED_SCRAP = 'scrap';

	// Public Properties
	public $id = null;   // The internal ID (numeric)

	public $is_parent = false;   // Can act as a parent facility to other items

    public $title = '';
	public $manufacturer = '';
	public $model = '';

	public $short_description = '';
	public $full_description = '';
	public $specification = '';

	public $upgrades = '';
	public $future_upgrades = '';

	public $acronym = '';
	public $keywords = '';

	public $technique = '';

	public $availability = '';
	public $restrictions = '';

	public $usergroup = '';
	public $access = '';   // Access ID
	public $portability = '';

	public $department = null;   // @todo : Deprecated - remove
	public $organisation = null; // @todo : Deprecated - remove

	public $ou = null;

	public $site = '';       // Site ID
	public $building = '';   // Building ID
	public $room = '';

	public $contact_1_name = null;
	public $contact_1_email = null;
	public $contact_2_name = null;
	public $contact_2_email = null;

	public $visibility = 0;

	public $image = '';
	public $embedded_content = '';

	public $manufacturer_website = '';
	public $copyright_notice = '';

	public $date_added = null;
	public $date_updated = null;
	public $last_updated_username = null;
	public $last_updated_email = null;

	public $training_required = null;
	public $training_provided = null;

	public $quantity = 1;
	public $quantity_detail = '';

	public $PAT = null;

	public $calibrated = self::CALIB_NOTAPP;
	public $last_calibration_date = null;
	public $next_calibration_date = null;

	public $asset_no = '';     // The item's asset number (if applicable)
	public $finance_id = '';   // e.g. finance system ID / purchase order ID
	public $serial_no = '';
	public $year_of_manufacture = null;
	public $supplier = '';     // Who supplied the item (may not be manufacturer)
	public $date_of_purchase = null;

	public $cost = '';
	public $replacement_cost = '';
	public $end_of_life = null;
	public $maintenance = '';

	public $is_disposed_of = false;
	public $date_disposed_of = null;

	public $comments = '';

	public $archived = false;



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /->__construct()



	public function __get($name) {
		switch ($name) {
			case 'idslug':
				return "{$this->id}/". preg_replace('/[^a-z0-9]+/', '-', strtolower($this->name)) .'.html';
				break;
			case 'imageslug':
					return "{$this->id}/image/{$this->image}";
					break;
			case 'last_update':
				return (empty($this->date_updated)) ? $this->date_added : $this->date_updated ;
				break;
			case 'last_updated_by':
				return (!empty($this->last_updated_email)) ? $this->last_updated_email : $this->last_updated_username ;
				break;
			case 'name':
				if (!empty($this->title)) {
					return $this->title;
				} elseif (empty($this->manufacturer)) {
					return "un-named item (#{$this->id})";
				} else {
					if (empty($this->model)) {
						return $this->manufacturer;
					} else {
						return "{$this->manufacturer} {$this->model}";
					}
				}
				break;
			case 'url_suffix':
			case 'slug':
				return preg_replace('/[^a-z0-9]+/', '-', strtolower($this->name)) ."/{$this->id}";
		}

	}// /method



	public function __isset($name) {
		$x = $this->$name;
		return isset($x);
	}



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
	}// /method



	public function isParent() {
		return ($this->is_parent);
	}



	public function validate(&$errors = null) {
		$errors = null;

		if ( (empty($this->title)) && (empty($this->manufacturer)) ) {
			$errors['title'] = 'Title is empty. Either Title or Manufacturer must be supplied.';
			$errors['manufacturer'] = 'Manufacturer is empty. Either Title or Manufacturer must be supplied.';
		}

		if (empty($this->ou)) { $errors['ou'] = 'Organisational Unit is empty.'; }

		if (empty($this->contact_1_email)) { $errors['contact_1_email'] = 'Contact 1 Email is empty.'; }
		//elseif (false === filter_var($this->contact_1_email, FILTER_VALIDATE_EMAIL)) { $errors['contact_1_email'] = 'Contact 1 Email is an invalid email address.'; }

		if (!empty($errors)) { return false; }

		return true;
	}// /method



}// /class
?>
