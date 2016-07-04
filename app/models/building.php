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

	public $url = '';



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



/* --------------------------------------------------------------------------------
 * Public Methods
 */



}// /class
?>
