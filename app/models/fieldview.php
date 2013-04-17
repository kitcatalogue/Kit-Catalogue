<?php
/**
 * FieldView Dictionary for managing public field view configuration.
 *
 * @version  1.0.0
 */
Class FieldView extends Ecl_Dictionary {

	// Public Properties
	var $_user = null;



	public function __construct($config) {
		parent::__construct($config);

		if (isset($config['user'])) {
			$this->_user = $config['user'];
		}
	}



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	public function show($key) {
		$setting = $this->get($key, 'hide');
		if ('*' == $setting) { return true; }
		if ( ('user' == $setting) && (!$this->_user->isAnonymous()) ) { return true; }
		return false;
	}



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /Class
?>