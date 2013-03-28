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

<<<<<<< HEAD
	protected $_ou_adminable = array();

=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd


	/**
	 * Constructor
	 *
<<<<<<< HEAD
	 * @param  object  $user  An Ecl_User object.
	 * @param  object  $sysauth  An Ecl_Authorisation object.
=======
	 * @param  object  $database  An Ecl_Db data access object.
	 * @param  object  $user  An Ecl_User object.
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	 * @param  object  $model  An Ecl_Router_Model object.
	 */
	public function __construct($user, $sysauth, $model) {
		$this->_user = $user;
		$this->_sysauth = $sysauth;
		$this->_model = $model;

		$authorisations = $this->_sysauth->findForAgent($this->_user->username);
		$this->_auth_lookup = Ecl_Helper_Array::extractGroupedValues($authorisations, 'auth', 'item');
<<<<<<< HEAD
		if (is_null($this->_auth_lookup)) { $this->_auth_lookup = array(); }

		// If there are some OU auths, fetch the sub-ou IDs as they are also adminable.
		if ( (!empty($this->_auth_lookup)) && (array_key_exists(KC__AUTH_CANOUADMIN, $this->_auth_lookup)) ) {
			$ou_tree = $this->_model->get('ou_tree');
			if ($ou_tree) {
				$org_auths = $this->_auth_lookup;

				foreach($org_auths as $auth => $items) {
					foreach($items as $item) {
						if ('ou_' == substr($item, 0, 3)) {
							$ou_id = substr($item, 3);
							if (!in_array($ou_id, $this->_auth_lookup[$auth])) {
								$ou_ids = $ou_tree->findSubRefsForRef($ou_id);
								array_walk($ou_ids, function(&$v, $k) {
									$v = "ou_{$v}";
								});
								$this->_auth_lookup[$auth] += $ou_ids;
							}
						}
					}
				}
			}
		}
=======

		if (is_null($this->_auth_lookup)) { $this->_auth_lookup = array(); }
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
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
<<<<<<< HEAD
	 * Check if the current user has the requested ou and auth.
	 *
	 * Convenience method that converts an ou_id to a proper authorisation 'object'.
	 *
	 * @param  integer  $ou_id
=======
	 * Check if the current user has the requested department and auth.
	 *
	 * Convenience method that converts a department_id to a proper authorisation 'object'.
	 *
	 * @param  integer  $dept_id
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	 * @param  string  $auth
	 *
	 * @return  boolean  The user has the auth on the requested object.
	 */
<<<<<<< HEAD
	public function checkOUAuth($ou_id, $auth) {
		return $this->checkObjectAuth("ou_{$ou_id}", $auth);
=======
	public function checkDeptAuth($dept_id, $auth) {
		return $this->checkObjectAuth("dept_{$dept_id}", $auth);
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
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
				// Hide items that are 'draft' unless user is
				// the custodian
				if ($item->visibility != 3) {
<<<<<<< HEAD
					$perms['item.accesslevel.view']  = true;
					$perms['item.customfields.view'] = true;
					$perms['item.files.view']        = true;
					$perms['item.location.view']     = true;
				}

				if ($new_item) {
					// If user has ANY OU editing rights
					if ($this->checkAuth(KC__AUTH_CANOUADMIN)) {
=======
					$perms['item.accesslevel.view']   = true;
					$perms['item.customfields.view']  = true;
					$perms['item.files.view']         = true;
					$perms['item.location.view']      = true;
				}

				if ($new_item) {
					// If user has ANY department editing rights
					if ($this->checkAuth(KC__AUTH_CANEDIT)) {
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
						$perms['site.item.edit']  = true;
					}
				} else {
					// If user is the item custodian (has the same email address)
<<<<<<< HEAD
					if (
						(!empty($this->_user->email))
						&& (
							(strtolower($this->_user->email) == strtolower($item->contact_1_email))
							|| (strtolower($this->_user->email) == strtolower($item->contact_2_email))
							)
						) {
							$perms['site.item.edit']  = true;

							$perms['item.accesslevel.view']  = true;
							$perms['item.customfields.view'] = true;
							$perms['item.files.view']        = true;
							$perms['item.location.view']     = true;
					} else {
						// If user has editing rights for the item's OU
						if ($this->checkOUAuth($item->ou_id, KC__AUTH_CANOUADMIN)) {
							$perms['site.item.edit'] = true;
=======
					if ($this->_user->email == $item->contact_1_email
						|| $this->_user->email == $item->contact_2_email) {
							$perms['site.item.edit']  = true;

							$perms['item.accesslevel.view']   = true;
							$perms['item.customfields.view']  = true;
							$perms['item.files.view']         = true;
							$perms['item.location.view']      = true;
					} else {
						// If user has editing rights for the item's department
						// and the item isn't set to draft...
						if ($item->visibility!=3) {
							if ($this->checkDeptAuth($item->department, KC__AUTH_CANEDIT)) {
								$perms['site.item.edit'] = true;
							}
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
						}
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



<<<<<<< HEAD
	/**
	 * Find the ous the current user has the given auth for.
	 *
	 * @param  string  $auth
	 *
	 * @return  array  The department IDs
	 */
	public function findOUsForAuth($auth) {
		if (!isset($this->_auth_lookup[$auth])) { return array(); }

		$ou_ids = array();
		foreach($this->_auth_lookup[$auth] as $v) {
			if (0 === strpos($v, 'ou_')) {
				$ou_ids[] = (int) substr($v, 3);
			}
		}

		return $ou_ids;
	}



=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>