<?php
/**
 * Supplier class
 *
 * @version 1.0.0
 */
class Supplier {

	// Public Properties
	public $id = null;        // The internal ID (numeric)
	public $name = '';        // The name

	public $item_count_internal = 0;    // The number of internally-restricted items in this supplier
	public $item_count_public = 0;      // The number of public items in this supplier



	public function __get($supplier_name) {
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



	/**
	 * Get the item count appropriate for the given visibility
	 *
	 * An invalid $visibility will return 0.
	 * A $visibility of null will return a count of internal items.
	 *
	 * @param  integer  $visibility  The type of visibility items should possess.
	 *
	 * @return  integer
	 */
	public function getItemCount($visibility = null) {
		if (is_null($visibility)) {
			return $this->item_count_internal;
		} else {
			switch($visibility) {
				case KC__VISIBILITY_INTERNAL:
					return $this->item_count_internal;
					break;
				case KC__VISIBILITY_PUBLIC:
					return $this->item_count_public;
					break;
				default:
					return 0;
					break;
			}
		}
	}// /method



}// /class
?>
