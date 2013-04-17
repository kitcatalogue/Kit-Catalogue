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

	protected $_editor_lookup = null;

	protected $_perm_lookup = array();



	/**
	 * Constructor
	 *
	 * @param  object  $user  An Ecl_User object.
	 * @param  object  $sysauth  An Ecl_Authorisation object.
	 * @param  object  $model  An Ecl_Router_Model object.
	 */
	public function __construct($user, $sysauth, $model) {
		$this->_user = $user;
		$this->_sysauth = $sysauth;
		$this->_model = $model;

		$authorisations = $this->_sysauth->findForAgent($this->_user->username);
		$this->_auth_lookup = Ecl_Helper_Array::extractGroupedValues($authorisations, 'auth', 'item');
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
								foreach($ou_ids as $ou_id_to_add) {
									if (!in_array($ou_id_to_add, $this->_auth_lookup[$auth])) {
										$this->_auth_lookup[$auth][] = $ou_id_to_add;
									}
								}
							}
						}
					}
				}
			}
		}
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
	 * Check if the current user has the requested ou and auth.
	 *
	 * Convenience method that converts an ou_id to a proper authorisation 'object'.
	 *
	 * @param  integer  $ou_id
	 * @param  string  $auth
	 *
	 * @return  boolean  The user has the auth on the requested object.
	 */
	public function checkOUAuth($ou_id, $auth) {
		return $this->checkObjectAuth("ou_{$ou_id}", $auth);
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
				'site.item.edit'    => false ,
				'item.editors.edit' => false ,
			);


			// If user has signed in, set the default viewing permissions
			if (!$this->_user->isAnonymous()) {

				if ($new_item) {
					// If user has ANY OU editing rights
					if ($this->checkAuth(KC__AUTH_CANOUADMIN)) {
						$perms['site.item.edit'] = true;
					}
				} else {
					// If user is the item custodian (has the same email address)
					if (!empty($this->_user->email)) {

						$user_email = strtolower($this->_user->email);

						if  (
							$this->_model->get('admin.item.edit.contact_1')
							&& ($user_email == strtolower($item->contact_1_email))
							) {
							$perms['site.item.edit'] = true;
						} elseif (
							$this->_model->get('admin.item.edit.contact_2')
							&& ($user_email == strtolower($item->contact_2_email))
							) {
							$perms['site.item.edit'] = true;
						}
					}
				}

			}


			// If user has editing rights for the item's OU

			$ou_perm = "ou_{$item->ou}";
			if ($this->checkOUAuth($item->ou, KC__AUTH_CANOUADMIN)) {
				$perms['site.item.edit'] = true;
			}


			// If item-editors are disabled, deny access
			if ($this->_model->get('admin.item.editors.enabled')) {
				$this->loadEditorLookup();

				if (in_array($item->id, $this->_editor_lookup)) {
					$perms['site.item.edit'] = true;
				}

				if (!$this->_model->get('admin.item.editors.adminonly')) {
					$perms['item.editors.edit'] = true;
				} elseif  ($this->checkOUAuth($item->ou_id, KC__AUTH_CANOUADMIN)) {
					$perms['item.editors.edit'] = false;
				}
			} else {
				$perms['item.editors.edit'] = false;
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



	public function loadEditorLookup() {
		if (!is_array($this->_editor_lookup)) {
			$item_editor_store = $this->_model->get('itemeditorstore');
			$editors = $item_editor_store->findForUsername($this->_user->username)->toArray();
			$this->_editor_lookup = Ecl_Helper_Array::extractColumn($editors, 'item_id', true);
		}
		return true;
	}



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>