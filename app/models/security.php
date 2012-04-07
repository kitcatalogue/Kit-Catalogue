<?php
/**
 * Security class
 *
 * @version 1.0.0
 */
class Security {

	// Private Properties
	protected $_user = null;
	protected $_sysauth = null;
	protected $_model = null;

	protected $_auth_lookup = array();

	protected $_perm_lookup = array();



	/**
	 * Constructor
	 *
	 * @param  object  $database  An Ecl_Db data access object.
	 * @param  object  $user  An Ecl_User object.
	 * @param  object  $model  An Ecl_Router_Model object.
	 */
	public function __construct($user, $sysauth, $model) {
		$this->_user = $user;
		$this->_sysauth = $sysauth;
		$this->_model = $model;

		$authorisations = $this->_sysauth->findForAgent($this->_user->username);
		$this->_auth_lookup = Ecl_Helper_Array::extractGroupedValues($authorisations, 'auth', 'item');

		if (is_null($this->_auth_lookup)) { $this->_auth_lookup = array(); }
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Check if the current user has the requested auth on any object.
	 *
	 * @param  mixed  $auths  An auth, or array of auths, to look for.
	 *
	 * @return  boolean  The user has the requested auth.
	 */
	public function checkAuth($auths) {
		$auths = (array) $auths;

		foreach($auths as $k) {
			if (isset($this->_auth_lookup[$k])) { return true; }
		}
		return false;
	}// /method



	/**
	 * Check if the current user has the requested object and auth.
	 *
	 * @param  string  $object
	 * @param  string  $auth
	 *
	 * @return  boolean  The user has the auth on the requested object.
	 */
	public function checkObjectAuth($object, $auth) {
		return ( (isset($this->_auth_lookup[$auth])) && (in_array($object, $this->_auth_lookup[$auth])) );
	}// /method



	/**
	 * Check if the current user has the requested department and auth.
	 *
	 * Convenience method that converts a department_id to a proper authorisation 'object'.
	 *
	 * @param  integer  $dept_id
	 * @param  string  $auth
	 *
	 * @return  boolean  The user has the auth on the requested object.
	 */
	public function checkDeptAuth($dept_id, $auth) {
		return $this->checkObjectAuth("dept_{$dept_id}", $auth);
	}/// method



	/**
	 * Check if the current user has the requested item permission.
	 *
	 * @param  object  $item
	 * @param  string  $permission
	 *
	 * @return  boolean  The current user has the requested item permission.
	 */
	public function checkItemPermission($item, $permission) {
		if (!isset($this->_perm_lookup[$item->id])) {

			// System Admin can do everything, so check no further
			if ($this->checkAuth(KC__AUTH_CANADMIN)) {
				return true;
			}

			$new_item = (empty($item->id));

			/*
			 *  Technically, we could just default to an empty array here,
			 *  and only add the permissions we care about (as 'true').
			 *
			 *  However, to make things explicit for any community coders,
			 *  we instead set all the permissions we're using to false,
			 *  then change the active ones to true as we go.
			 */

			// Set the default permissions
			$perms = array(
				'site.item.edit'  => false ,

				'item.accesslevel.view'   => false ,
				'item.customfields.view'  => false ,
				'item.files.view'         => false ,
				'item.location.view'      => false ,
			);


			// If user has signed in, set the default viewing permissions
			if (!$this->_user->isAnonymous()) {
				$perms['item.accesslevel.view']   = true;
				$perms['item.customfields.view']  = true;
				$perms['item.files.view']         = true;
				$perms['item.location.view']      = true;

				// If user is the item custodian (has the same email address)
				if ($this->_user->email == $item->contact_email) {
					$perms['site.item.edit']  = true;
				}

				if ($new_item) {
					// If user has ANY department editing rights
					if ($this->checkAuth(KC__AUTH_CANEDIT)) {
						$perms['site.item.edit']  = true;
					}
				} else {
					// If user has editing rights for the item's department
					if ($this->checkDeptAuth($item->department, KC__AUTH_CANEDIT)) {
						$perms['site.item.edit']  = true;
					}
				}

			}

			// Set the item final permissions
			$this->_perm_lookup[$item->id] = $perms;
		}

		if (!isset($this->_perm_lookup[$item->id][$permission])) {
			return false;
		} else {
			return $this->_perm_lookup[$item->id][$permission];
		}
	}



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>