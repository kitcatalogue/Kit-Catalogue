<?php
/**
 * User Store class
 *
 * @version 1.0.0
 */
class Userstore {

	// Private Properties
	protected $_model = null;

	protected $_db = null;
	protected $_user_session_var = '_user_data';



	/**
	 * Constructor
	 *
	 * @param  object  $model  The model instance.
	 * @param  string  $user_session_var  (optional) The session key to use when storing user data.
	 */
	public function __construct($model, $user_session_var = '_user_data') {
		$this->_model = $model;
		$this->_db = $this->_model->get('db');
		if (!empty($user_session_var)) { $this->_user_session_var = $user_session_var; }
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	public function authenticateDb($username, $password) {
		$binds = array (
			'username'  => $username ,
		);
		$this->_db->query('
			SELECT *
			FROM user
			WHERE username=:username
		', $binds);
		if (!$this->_db->hasResult()) { return null; }

		$row = $this->_db->getRow();

		if (!is_array($row)) { return null; }

		if (md5("{$row['salt']}:{$password}") != $row['password']) { return null; }

		$user_info = array (
			'id'        => $row['user_id'] ,
			'username'  => $row['username'] ,
			'forename'  => $row['forename'] ,
			'surname'   => $row['surname'] ,
			'email'     => $row['email'] ,
		);

		return $user_info;
	}// /method



	/**
	 * Clear the current user session.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function clearUserSession() {
		unset($_SESSION[$this->_user_session_var]);
		return true;
	}// method



	/**
	 * Convert a domain object to a database row
	 *
	 * @param  object  $object
	 *
	 * @return  array
	 */
	public function convertObjectToRow($object) {
		return array (
			'username'   => $object->username ,
			'forename'   => $object->forename ,
			'surname'    => $object->surname ,
			'email'      => $object->email ,
		);
	}// /method



	/**
	 * Convert a database row to a domain object
	 *
	 * @param  array  $row
	 *
	 * @return  object
	 */
	public function convertRowToObject($row) {
		$object = $this->newUser();
		$object->username = $row['username'];
		$object->forename = $row['forename'];
		$object->surname = $row['surname'];
		$object->email = $row['email'];

		return $object;
	}// /method



	/**
	 * Delete a user.
	 *
	 * @param  string  $username  The user to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function delete($username) {
		$sql__username = $this->_db->prepareValue($username);
		$affected_count = $this->_db->delete('user', "username=$sql__username");
		$this->_model->get('sysauth')->deleteForAgent($username);

		return ($affected_count>0);
	}// /method



	/**
	 * Find all users.
	 *
	 * @return  mixed  An array of objects.  On fail, null.
	 */
	public function findAll() {
		return $this->_db->newRecordset("
				SELECT *
				FROM user
				ORDER BY surname, forename ASC
			", null, array($this, 'convertRowToObject') );
	}// /method
    /**
     * Find users that contain the sring.
     *
     * @return mixed An array of objects. On fail, null.
     */
    public function findPartialMatch($query){
        $binds = array (
                'query' => $query,
            );
        $this->_db->query("
                SELECT *
                FROM user 
                WHERE surname LIKE :query
                ", $binds);
    return $this->_db->getObject(array($this, 'convertRowToObject') );
    }// /method 



	/**
	 * Find a user by their username.
	 *
	 * @param  string  $username
	 *
	 * @return  mixed  The object requested.  On fail, null.
	 */
	public function findForUsername($username) {

		$binds = array (
				'username'  => $username,
		);

		$this->_db->query("
				SELECT *
				FROM user
				WHERE username=:username
			", $binds);

		return $this->_db->getObject(array($this, 'convertRowToObject') );
	}// /method



	/**
	 * Insert a new user.
	 *
	 * @param  object  $object  The User to create.
	 *
	 * @return  mixed  The new id created.  On fail, null.
	 */
	public function insert($object) {
		$binds = $this->convertObjectToRow($object);

		unset($binds['user_id']);   // Don't insert the id, we want a new one
		$new_id = $this->_db->insert('user', $binds);
		return ($new_id>0) ? $new_id : null ;
	}// /method



	public function isUserSession() {
		return (isset($_SESSION[$this->_user_session_var]));
	}



	/**
	 * Get a new instance of a User object.
	 *
	 * @return  object  A User object.
	 */
	public function newUser() {
		return new Ecl_User();
	}// /method



	/**
	 * Get a User instance loaded with data from the current session
	 *
	 * @return  object  The current user. On fail, an empty user instance.
	 */
	public function newUserFromSession() {

		if (isset($_SESSION[$this->_user_session_var])) {
			$user = $this->newUser();
			$user->fromAssoc($_SESSION[$this->_user_session_var]);
			return $user;
		}
		return $this->newUser();
	}// /method



	/**
	 * Set the given user's password.
	 *
	 * @param  string  $username
	 * @param  string  $password
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setPassword($username, $password) {
		$sql__username = $this->_db->prepareValue($username);

		$salt = Ecl_Helper_String::random(10);
		$password_hash = md5("{$salt}:{$password}");

		$binds = array (
			'salt'      => $salt ,
			'password'  => $password_hash ,
		);

		$affected_count = $this->_db->update('user', $binds, "username={$sql__username}");
		return ($affected_count>0);
	}// /method



	/**
	 * Set the user details that should be used for the user session from now on.
	 *
	 * User data MUST be of the following form.  Any missing fields will result in a session storage failure.
	 * array (
	 *   'id' ,         // The user's unique ID (e.g. Staff ID number)
	 *   'forename' ,
	 *   'surname' ,
	 *   'email' ,
	 *   'username' ,   // The user's username (what they used to signin)
	 *   'params' ,     // Array of additional parameters
	 * )
	 *
	 * @param  array  $assoc  An assoc-array of user information
	 *
	 * @return  boolean  The operation was successfull.
	 */
	public function setUserSession($assoc) {
		$valid_fields = array ('id', 'forename', 'surname', 'email', 'username', 'params');

		$user = Ecl::factory('Ecl_User');
		$user->generateSessionKey();

		$user_data = array();
		foreach($valid_fields as $i => $field) {
			if (!array_key_exists($field, $assoc)) {
				return false;
			} elseif ('params' == $field) {
				foreach($assoc[$field] as $k => $v) {
					$user->setParam($k, $v);
				}
			} else {
				$user->$field = $assoc[$field];
			}
		}

		// Save the session data
		$_SESSION[$this->_user_session_var] = $user->toAssoc();

		return true;
	}// /method



	/**
	 * Update an existing User.
	 *
	 * @param  object  $object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function update($object) {
		$binds = $this->convertObjectToRow($object);

		$sql__username = $this->_db->prepareValue($object->username);

		$affected_count = $this->_db->update('user', $binds, "username=$sql__username");

		return ($affected_count>0);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>
