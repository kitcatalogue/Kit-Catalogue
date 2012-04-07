<?php
/**
 * File Cache.
 *
 * A simple caching class that stores its data in files.
 *
 * @todo : Test this class
 *
 * @package  Ecl
 * @version  1.1.0
 */
class Ecl_Cache_File {

	// Public Properties

	// Private Properties
	private $_path = '';   // The folder to store cached files in

	private $_expiry_age = 86400;   // The default expiry age in seconds (86400 = 24hrs).

	private $_use_hashes = false;   // Use hashes as filenames



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
		if ($handle = opendir($this->_path)) {

			$expiry_age = time() - $this->_expiry_age;

			while (false !== ($file = readdir($handle))) {
		  		$file_path = $this->_path . DIRECTORY_SEPARATOR . $file;

		  		// Don't process folders or hidden files
				if ( ($file[0] != '.') && (is_dir($file_path)) ) {
					continue;
				}

				$modified_time = filemtime($filename);
				if ($modified_time) { $expired = $expiry > $modified_time; }
				if (filemtime($file) < $expiry_age) { unlink($file_path); }
			}// /while
			closedir($handle);
		}

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
	 * Read the cached file, if it hasn't expired.
	 *
	 * @param  string  $filename  The filename of the content to read.
	 * @param  mixed  $expiry  Expiry age in seconds. If null, use the age set by ->setExpiryAge(). (default: null)
	 *
	 * @return  mixed  The cached content requested, or null if expired. On fail, null.
	 */
	public function read($filename, $expiry_age = null) {

		$filename = $this->_getCacheFilename($filename);

		if (!file_exists($filename)) { return null; }

		$expired = true;
		$expiry = null;

		// If expiry age given, use it
		if (!is_null($expiry_age)) {
			$expiry = time() - $expiry_age;
		} else {
			// If no expiry time, caches are always valid
			if (empty($this->_expiry_age)) {
				$expired = false;
			} else {
				$expiry = time() - $this->_expiry_age;
			}
		}

		// If needed, check the expiry date
		if ($expiry) {
			$modified_time = filemtime($filename);
			if ($modified_time) {
				$expired = $expiry > $modified_time;
			}
		}

		if ($expired) {
			return null;
		} else {
			$fp = fopen($filename, 'r');
			flock($fp, LOCK_SH);
			$contents = stream_get_contents($fp);
			flock($fp, LOCK_UN);
			fclose($fp);
			return $contents;
		}
	}// /method



	/**
	 * Set the path of the folder to store the cache data in.
	 *
	 * @param  string  $path
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setPath($path) {
		$this->_path = $path;
		return true;
	}// /method



	/**
	 * Set whether to use hashes for filenames.
	 *
	 * @param  boolean  $use_hashes  (optional) Use hashes. (default: false).
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function getHash($use_hashes = false) {
		$this->_use_hashes = ($use_hashes==true);
		return true;
	}// /method



	/**
	 * Write data to the cache.
	 *
	 * @param  string  $filename  The filename to store the cached content.
	 * @param  string  $content   The content to store.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function write($filename, $content) {
		$filename = $this->_getCacheFilename($filename);
		return (file_put_contents($filename, $content, LOCK_EX)>0);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



	/**
	 * Generate the caching filename from the given client filename.
	 *
	 * If using file-hashes, filenames are converted to MD5 hashes.
	 * If not using hashes, a valid filename is returned with inappropriate characters removed.
	 * The cache path is automatically prepended to the filename.
	 *
	 * @param  string  $filename  The client's filename for the content.
	 *
	 * @return  string  The filename to save in the cache.
	 */
	protected function _getCacheFilename($filename) {
		if ($this->_use_hashes) {
			return $this->_path . $this->getHash($filename);
		} else {
			return $this->_path . $filename;
		}
	}// /method



}// /class
?>