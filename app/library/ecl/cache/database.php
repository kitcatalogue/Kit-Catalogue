<?php
/**
 * Database Cache.
 *
 * A simple caching class that stores its data in a database table.
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Cache_Database {

	// Public Properties

	// Private Properties
	protected $_db = null;   // Reference to database object.
	protected $_table = null;   // The database table to use.

	protected $_expiry_age = 86400;   // The default expiry age in seconds (86400 = 24hrs).



	/**
	 * Constructor
	 */
	public function __construct() {
	}// /__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Clean up any old cache entries.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function cleanUp() {

		$sql__expiry = $this->_db->formatDate(time());

		$this->_db->execute("
			DELETE FROM `{$this->_table}`
			WHERE expiry<='$sql__expiry'
		");

		return true;
	}// /method



	/**
	 * Produce a hash for the given value.
	 *
	 * @param  string  $value  The value to hash.
	 *
	 * @return  string  The hash produced.
	 */
	public function getHash($value) {
		return md5($value);
	}// /method



	/**
	 * Install the into the provided database and table.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function install() {
		$sql = "
			CREATE TABLE IF NOT EXISTS `{$this->_table}` (
				`name` varchar(60) NOT NULL default '',
				`expiry` datetime NOT NULL,
				`content` mediumtext default null,
				PRIMARY KEY (`name`)
			)
		";
		$this->_db->execute($sql);

		return true;
	}// /method



	/**
	 * Read the cached data, if it hasn't expired.
	 *
	 * @param  string  $name  The name of the content to read.
	 * @param  mixed  $expiry  (optional) Expiry age in seconds. If null, use the age set by ->setExpiryAge(). (default: null)
	 *
	 * @return  mixed  The cached content requested, or null if expired. On fail, null.
	 */
	public function read($name, $expiry_age = null) {
		$expired = true;

		$expiry = null;

		// If expiry age given, use it
		if (!is_null($expiry_age)) {
			$expiry = time() - $expiry_age;
		} else {
			// If no expiry time, caches are always valid
			if (empty($this->_expiry_age)) {
				$expiry = 0;
			} else {
				$expiry = time() - $this->_expiry_age;
			}
		}


		$binds = array(
			'name'  => $name ,
			'expiry'  => $this->_db->formatDate($expiry) ,
		);

		$this->_db->query("
			SELECT content
			FROM `{$this->_table}`
			WHERE name=:name AND expiry>:expiry
		", $binds);

		return $this->_db->getValue();
	}// /method



	/**
	 * Set the DB database object to be used.
	 *
	 * @param  object $db  The Database object to use
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setDatabase(&$db) {
		$this->_db = $db;
		return true;
	}// /method



	/**
	 * Set the age in seconds the cache entries remain live.
	 *
	 * @param  integer  $age  The age in seconds.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setExpiryAge($age) {
		$this->_expiry_age = $age;
		return true;
	}// /method



	/**
	 * Set the table to use as a cache.
	 *
	 * Use the install() method to create the table.
	 *
	 * @param  string  $table_prefix  The prefix to put on the cache table.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setTable($table) {
		$this->_table = $table;
		return true;
	}// /method



	/**
	 * Write data to the cache.
	 *
	 * The $name should be unique to the particular data contained within, e.g. a hash.
	 * If two sets of data use the same name, there will be collisions.
	 *
	 * @param  string  $name  The name of the cache entry.
	 * @param  string  $content   The content to store.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function write($name, $content) {
		$binds = array (
			'name'     => $name ,
			'content'  => $content ,
			'expiry'   => $this->_db->formatDate(time() + $this->_expiry_age) ,
		);

		$this->_db->replace($this->_table, $binds);
		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>