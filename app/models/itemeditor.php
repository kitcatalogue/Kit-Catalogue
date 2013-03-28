<?php
/**
 * Item Editor class
 *
 * @version 1.0.0
 */
class Itemeditor {

	// Public Properties
	public $id = null;
	public $item_id = null;
	public $username = '';
	public $email = '';



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	public function toString() {
		$string = '';

		if (!empty($this->username)) {
			$string .= "user: {$this->username}";
		}

		if (!empty($this->email)) {
			if (!empty($string)) {
				$string .= ', ';
			}
			$string .= "email: {$this->email}";
		}

		return (!empty($string)) ? $string : 'unknown editor' ;
	}



}// /class
?>