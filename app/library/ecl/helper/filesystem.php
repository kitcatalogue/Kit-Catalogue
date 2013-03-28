<?php
/**
 * Filesystem helper class
 *
 * Mainly sensibly named wrappers for PHP's file handling functions.
 *
 * @package  Ecl
 * @static
<<<<<<< HEAD
 * @version  1.2.0
=======
 * @version  1.0.0
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
 */
class Ecl_Helper_Filesystem {



	/**
	 * Constructor
	 */
	private function __construct() {
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Clear the file status cache.
	 *
	 * PHP caches the results from stat(), file_exists() etc.  Calling this method clears the cache.
	 *
	 * @return  boolean  True in all cases.
	 */
	public static function clearStatusCache() {
		clearstatcache();
		return true;
	}// /method



	/**
	 * Close an open File-Stream pointer.
	 *
	 * @param  resource  $handle  The handle of the stream to close.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function closeFileStream($handle) {
		return fclose($handle);
	}// /method



	/**
	 * Copy a file from one location to another.
	 *
	 * If the target location does not exist, it will be created.
	 *
	 * @param  string  $source_path  The path of the file to be moved.
	 * @param  string  $target_path  The path to move the file to.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function copyFile($source_path, $target_path) {
		$target_dir = dirname($target_path);
		if (!is_dir($target_dir)) { self::createFolder($target_dir); }

		return copy($source_path, $target_path);
	}// /method



	/**
	 * Create the given file.
	 *
	 * If the file already exists, it returns false.
	 *
	 * @param  string  $path  The path of the file to create.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function createFile($path) {
		if (!file_exists($path)) {
			return touch($path);
		} else {
			return false;
		}
	}// /method



	/**
	 * Create a new folder.
	 *
	 * Will create the entire folder tree, if required.
	 *
	 * @param  string  $path  The path of the new folder.
	 * @param  int  $mode  The mode (permissions) to use when creating the folder. (When using Octal, numbers must start with 0).
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function createFolder($path, $mode = 0777) {
		return mkdir($path, $mode, true);
	}// /method



	/**
	 * Delete the given file.
	 *
	 * Does no error checking.
	 *
<<<<<<< HEAD
	 * @param  mixed  $path  The path of the file(s) to delete.
=======
	 * @param  string  $path  The path of the file to delete.
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function deleteFile($path) {
<<<<<<< HEAD
		if (is_array($path)) {
			foreach($path as $filepath) {
				@unlink($filepath);
			}
			return true;
		} else {
			return @unlink($path);
		}
=======
		return @unlink($path);
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	}// /method



	/**
	 * Delete the given folder.
	 *
	 * @param  string  $path  The path of the folder to delete.
	 * @param  boolean  $recursive  (optional) Perform a recursive delete, removing all sub-folders. (Default: false).
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function deleteFolder($path, $recursive = false) {

		if (!$recursive) { return rmdir($path); }

		$contents = self::getFolderContents($path, true);
		if (!$contents) {
			return rmdir($path);
		} else {
			$result = false;

			foreach($contents as $filename) {
				$new_path = $path . DIRECTORY_SEPARATOR . $filename;
				if (is_dir($new_path)) {
					$result = self::deleteFolder($new_path, true);
					if (!$result) { return false; }
				} else {
					$result = unlink($new_path);
					if (!$result) { return false; }
				}
			}
		}
		return $result;
	}// /method



	/**
	 * Does the given path exist.
	 *
	 * Can check for the existence of files or folders.
	 *
	 * @param  string  $path  The path to check.
	 *
	 * @return  boolean  The path exists.
	 */
	public static function exists($path) {
		return file_exists($path);
	}// /method



	/**
	 * Remove relative references ( . and .. ) from the given path.
	 *
	 * Trailing slashes are removed.
	 *
	 * @param  string  $virtual_path  The path to convert.
	 *
	 * @return  string  The canonical path.
	 */
	public static function fixPath($path) {

		if (empty($path)) { return ''; }

		// If the first char is '/' then remember we're using the root
		$root_char = ($path[0]==DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '' ;

		$parts = explode(DIRECTORY_SEPARATOR, $path);
		$parts_count = count($parts);

		$canonical_parts = array();

		// Loop through all the path-parts
		for ($i=0; $i<$parts_count; $i++) {
			// If the part is something other than blank or the curr-dir (.), check further
			if ( ($parts[$i]!=='') && ($parts[$i]!=='.') ) {
				// If the part is go-up (..), check if we can go up
				if ($parts[$i]==='..') {
					// if there's room to go up, do it. else, ignore it
					if ( ($i>0) && (count($canonical_parts)>0) ) {
						array_pop($canonical_parts);
					}
				} else {
					array_push($canonical_parts, $parts[$i]);
				}
			}
		}

		return $root_char . implode(DIRECTORY_SEPARATOR, $canonical_parts);
	}// /method



	/**
	 * Get the file extension portion of the given filename.
	 *
	 * @param  string  $filename  The filename to process.
	 *
	 * @return  string  The file extension.  On fail, null.
	 */
	public static function getFileExtension($filename) {
		$ext = substr(strrchr($filename, '.'), 1);
		return (empty($ext)) ? null : $ext ;
	}// /method



	/**
	 * Get the files within the given folder.
	 *
	 * @param  string  $path  The path of the folder to scan.
<<<<<<< HEAD
	 * @param  string  $pattern  (optional) A regex pattern to whitelist files against. (default: '')
	 *
	 * @return  array  An array of files.
	 */
	public static function getFiles($path, $pattern = '') {
		$all_files = self::getFolderContents($path, true);
		if (empty($all_files)) { return array(); }

		$files = array();
		if (empty($pattern)) {
			foreach($all_files as $i => $name) {
				if (!is_dir($path.'/'.$name)) {
					$files[] = $name;
				}
			}
		} else {
			foreach($all_files as $i => $name) {
				if ( (!is_dir($path.'/'.$name)) && (preg_match($pattern, $name)) ) {
					$files[] = $name;
				}
			}
		}
		return $files;
	}



	/**
	 * Get files older than the given age.
	 *
	 * @param  string  $path  The path of the folder to scan.
	 * @param  integer  $age  The age limit in seconds.
	 *
	 * @return  mixed  An array of filenames.
	 */
	public static function getFilesOlderThan($path, $age) {
		$files = array();

		$now = time();
=======
	 *
	 * @return  mixed  An array of filenames. On fail, null.
	 */
	public static function getFiles($path) {
		$files = null;
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

		$all_files = self::getFolderContents($path);
		if ($all_files) {
			foreach($all_files as $i => $name) {
<<<<<<< HEAD
				$full_path = $path.'/'.$name;
				if (!is_dir($full_path)) {
					if ( ($now - filemtime($full_path)) > $age) {
						$files[] = $name;
					}
=======
				if (!is_dir($path.'/'.$name)) {
					$files[] = $name;
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
				}
			}
		}
		return $files;
	}// /method



	/**
	 * Get the contents of the given folder.
	 *
	 * If you provide an extension whitelist, any non-matching files will be removed from the results.
	 *
	 * @param  string  $path  The path of the folder to scan.
	 * @param  boolean  $remove_dot_folders  (optional) Remove the '.' and '..' folders from the returned list. (Default: true).
	 * @param  mixed  $extension_whitelist  (optional) A whitelist extension, or array of extensions, to check. (default: null)
	 *
	 * @return  mixed  An array of files and sub-folders. On fail, null.
	 */
	public static function getFolderContents($path, $remove_dot_folders = true, $extension_whitelist = null) {
		$files = null;

		if (is_dir($path)) {
			$files = scandir($path);

			if ( ($files) && ($remove_dot_folders) ) {
				$files = array_values( array_diff($files, array ('.', '..')) );
			}

			if ( (!$files) || (empty($extension_whitelist)) ) {
				return $files;
			} else {
				$extension_whitelist = (array) $extension_whitelist;
				$extension_whitelist = array_map('strtolower', $extension_whitelist);

				$filtered_files = null;
				foreach($files as $i => $filename) {
					if (in_array(strtolower(Ecl_Helper_Filesystem::getFileExtension($filename)), $extension_whitelist)) {
						$filtered_files[] = $filename;
					}
				}

				return $filtered_files;
			}// /if(whitelisting)
		}
	}// /method




	/**
	 * Get the sub-folders within the given folder.
	 *
	 * @param  string  $path  The path of the folder to scan.
	 * @param  boolean  $remove_dot_folders  (optional) Remove the '.' and '..' folders from the returned list. (Default: false).
	 *
	 * @return  mixed  An array of sub-folders. On fail, null.
	 */
	public static function getFolders($path, $remove_dot_folders = false) {

		$folders = null;

		$all_files = self::getFolderContents($path, $remove_dot_folders);
		if ($all_files) {
			foreach($all_files as $i => $name) {
				if (is_dir($path.'/'.$name)) {
					$folders[] = $name;
				}
			}
		}
		return $folders;
	}// /method



	/**
	 * Get the mime-type of the given filename.
	 *
	 * Checks against a list of common file extensions and mime types.
	 *
	 * @param  string  $filename  The filename to process.
	 *
	 * @return  string  The file extension.  On fail, null.
	 */
	public static function getMimeType($filename, $default_type = 'application/octet-stream') {
		$ext = FileSystem::getFileExtension($filename);
		if (!$ext) { return $default_type; }

		Ecl::loadFunctions('mime');
		return mime_for_file_ext($ext, $default_type);

		/*
		 * // @info : this code requires PECL fileinfo library.  Included by default from PHP >5.3
		$finfo = new finfo(FILEINFO_MIME);
		if (!$finfo) { return $default_type; }

		return $finfo->file($filename);
		*/
	}// /method



	/**
	 * Check if the given path is below the given root path.
	 *
	 * Both paths must exist.
	 *
	 * @param  string  $path  The path to check.
	 * @param  string  $root  The root folder to check against.
	 *
	 * @return  boolean  The path was below the root.
	 */
	public static function isPathBelowRoot($path, $root) {
		$real_path = realpath($path);
		$real_root = realpath($root);

		// If either path is false, then fail
		if ( (!$real_path) || (!$real_root) ) { return false; }

		// If the root path appears at the start of the real path, then the path is valid
		return (strpos($real_path, $real_root)===0);
	}// /method



	/**
	 * Move a file from one location to another.
	 *
	 * @param  string  $source_path  The path of the file to be moved.
	 * @param  string  $target_path  The path to move the file to.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function moveFile($source_path, $target_path) {

		// @idea : Change this method so moving folders with contents works

		return rename($source_path, $target_path);
	}// /method



	/**
	 * Open a File-Stream pointer.
	 *
	 * Does not throw an E_WARNING if the file cannot be opened.
	 * For different $mode options, check the PHP help for fopen().
	 *
	 * @param  string  $path  The path of the file to open.
	 * @param  string  $mode  (optional) The read/write mode to use (Default: 'r' = Reading only.)
	 *
	 * @return  mixed  The handle of the file-steam.  On fail, false.
	 */
	public static function openFileStream($path, $mode = 'rw') {
		return @fopen($path, $mode);
	}// /method



	/**
	 * Read the contents of the given file.
	 *
	 * If $path is a URI, use urlencode() to encode any special characters.
	 *
	 * @param  string  $path  The path of the file to read from.
	 *
	 * @return  mixed  The contents of the file as a string. On fail, false.
	 */
	public static function readFileContents($path) {
		return file_get_contents($path);
	}// /method



	/**
	 * Read the contents of the given file into an array of lines.
	 *
	 * If $path is a URI, use urlencode() to encode any special characters.
	 * For a list of available $flags, see PHP help on file().
	 *
	 * @param  string  $path  The path of the file to read from.
	 * @param  integer  $flags  The flags to use. (Default: FILE_IGNORE_NEW_LINES = don't include newline chars in output).
	 *
	 * @return  mixed  An array of strings read from the file. On fail, false.
	 */
	public static function readFileContentsArray($path, $flags = FILE_IGNORE_NEW_LINES) {
		return file($path, $flags);
	}// /method



	/**
	 * Read bytes from a file-stream.
	 *
	 * @param  resource  $handle  The handle of the file-stream to read from.
	 * @param  integer  $length  The number of bytes to read.
	 *
	 * @return  string  The bytes read from the file.
	 */
	public static function readFileStream($handle, $length) {
		return fread($handle, $length);
	}// /method



	/**
	 * Read a line from a file.
	 *
	 * Reading ends when either, (length - 1) bytes have been read,
	 * OR a newline is encountered (included in the returned string),
	 * OR an EOF is encountered.
	 *
	 * @param  resource  $handle  The handle of the file-stream to read from.
	 * @param  integer  $length  (optional) The length of string to return (-1).  (Default: null = no length limit).
	 *
	 * @return  mixed  The string read from the file. On fail, false.
	 */
	public static function readLineFileStream($handle, $length = null) {
		return ($length) ? fgets($handle, $length) : fgets($handle);
	}// /method



	/**
	 * Rename the given file.
	 *
	 * @param  string  $source_path  The path of the file to rename.
	 * @param  string  $target_path  The path to rename the file to.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public static function renameFile($source_path, $target_path) {
		return rename($source_path, $target_path);
	}// /method



	/**
	 * Set the contents of the given file.
	 *
	 * @param  string  $path  The path of the file to write to.
	 * @param  mixed  $contents  The string, array of strings, or stream, to use as the new file contents.
	 *
	 * @return  mixed  The number of bytes written. On fail, false.
	 */
	public static function setFileContents($path, $contents) {
		return file_put_contents($path, $contents);
	}// /method



	/**
	 * Write to a file stream.
	 *
	 * @param  resource  $handle  The stream-handle to write to.
	 * @param  string  $string  The string of bytes to write.
	 * @param  mixed  $length  The length of string to write (writing stops when $length is reached, regardless of $string).
	 *
	 * @return  mixed  The number of bytes written. On fail, false.
	 */
	public static function writeFileStream($handle, $string, $length = null) {
		return fwrite($handle, $string, $length);
	}// /method



}// /class
?>