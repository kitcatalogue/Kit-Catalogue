<?php



class Ecl_Uploader_Exception extends Ecl_Exception {}



/**
 * File Uploader class.
 *
 * @package Ecl
 * @version  6.3.0
 */
class Ecl_Uploader {

	/*
	A simple usage example:

	<form action="upload.php" enctype="multipart/form-data" method="post" name="upload_form">


	$uploader = new Uploader('/my/upload/path');
	$uploader->setMaxFileSize(null);   // unlimited file uploads (php.ini allowing). 2Mb = 2097152
	$uploader->setFlags(UPLOAD__ALLOW_AUTORENAME | UPLOAD__ALLOW_CREATEPATH);

	if ($uploader->isUpload()) {
		$files = $uploader->getUploadedFiles();
	}
	*/

	// Class Constants
	const ALLOW_OVERWRITE = 1;         // Allow file overwrites
	const ALLOW_AUTORENAME = 2;        // Allow file names to be rewritten if name already exists (suffixed with _<num>)
	const ALLOW_CREATEPATH = 4;        // Allow the upload path to be created if it does not exist
	const RESTRICT_IMAGESONLY = 256;   // Only allow image uploads

	// Private Properties
	protected $_config = array (
		'chmod'      => null ,
		'flags'      => 0 ,
		'filenames'  => array() ,
		'path'       => null ,
	);

	protected $_upload_processed = false;

	protected $_is_error = false;   // Error flag
	protected $_messages = array(); // Array of error messages generated during upload

	protected $_uploaded_files_by_input = null;   // The files successfully uploaded array ( 'filename'  => org-name , 'path'  => uploaded-name )

	protected $_whitelist_extensions = null;   // Array of valid file extensions (format: 'xyz')
	protected $_whitelist_mime_types = null;   // Array of valid mime types (format: 'abcd/xyz')

	protected $_restrict_file_size = null;     // Maximum size in bytes of the uploaded files.



	/**
	 * Constructor
	 */
	public function __construct($config) {
		if (array_key_exists('chmod', $config)) { $this->_config['chmod'] = $config['chmod']; }
		if (array_key_exists('path', $config)) { $this->setUploadPath($config['path']); }
		if (array_key_exists('flags', $config)) { $this->setFlags($config['flags']); }
		if (array_key_exists('filenames', $config)) { $this->setFilenames($config['filenames']); }
	} // /__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Get an array of any errors that occurred.
	 *
	 * @return  mixed  If errors, returns an array of strings describing those errors. If no errors, returns null.
	 */
	public function getMessages() {
		return ($this->isMessage()) ? $this->_messages : null ;
	}// /method



	/**
	 * Get the PHP ini upload_max_size in bytes.
	 *
	 * @return  integer
	 */
	public function getPhpUploadMaxSize() {
		// Get max upload size
		$ini_maxsize = ini_get('upload_max_filesize');

		$maxsize_val = (int) $ini_maxsize;
		$maxsize_unit = strtolower(substr($ini_maxsize,strlen($maxsize_val),1));

		switch ($maxsize_unit) {
			case 'k':
			case 'kb':
				$max_bytes = $maxsize_val * 1024;   // Kilobytes
				break;
			case 'm':
			case 'mb':
				$max_bytes = $maxsize_val * 1048576;   // Megabytes
				break;
			case 'g':
			case 'gb':
				$max_bytes = $maxsize_val * 1073741824;   // Gigabytes
				break;
			default:
				$max_bytes = $maxsize_val;
				break;
		}

		return $max_bytes;
	}// /method



	/**
	 * Get the path to the uploaded file using that was uploaded through the given input-file element.
	 *
	 * @param  string  $input_name  The name of the HTML input element.
	 *
	 * @return  mixed  The path of the uploaded file requested.  On fail, null.
	 */
	public function getUploadedFile($input_name) {
		return ( (!empty($this->_uploaded_files_by_input)) && (array_key_exists($input_name, $this->_uploaded_files_by_input)) ) ? $this->_uploaded_files_by_input[$input_name]['path'] : null ;
	}// /method



	/**
	 * Get a list of the files successfully uploaded, and their upload location.
	 *
	 * Returns an array of the form:
	 * array (
	 *     'form-input-name'  => array (
	 *         'filename'  =>  <the name of the file as uploaded> ,   // This is the filename before any rewriting
	 *         'path'      =>  <path to the uploaded file> ,          // The path to the actually uploaded file (including rewritten name, etc)
	 *     ) ,
	 *     ...
	 * )
	 *
	 * @return  mixed  If there are files, returns an assoc-array, array ( original-filename => uploaded-file-path ). If no files, returns null.
	 */
	public function getUploadedFiles() {
		return (is_array($this->_uploaded_files_by_input)) ? $this->_uploaded_files_by_input : null ;
	}// /method



	/**
	 * Check if the given flags are set.
	 *
	 * @param  integer  $flags  The flags to check.
	 *
	 * @return  boolean  All of the given flags are set.
	 */
	public function hasFlag($flags) {
		$flags = (int) $flags;
		return ($this->_config['flags'] & $flags) == $flags;
	}// /method



	/**
	 * Check if any files are being uploaded in the current request.
	 *
	 * This method does not confirm that the uploader will accept the files provided.
	 *
	 * @return  boolean  Files are being uploaded.
	 */
	public function hasFilesPosted() {
		if (!is_array($_FILES)) { return false; }

		foreach($_FILES as $file_input => $file_info) {
			if ($file_info['size']>0) {
				return true;
			}
		}

		return false;
	}// /method



	/**
	 * Check if an error message occurred during upload
	 *
	 * @return  boolean  There are messages.
	 */
	public function isMessage() {
		return (!empty($this->_messages));
	}// /method



	/**
	 * Check if at least one file has been uploaded.
	 *
	 * @return  boolean  At least one file has been successfully uploaded.
	 */
	public function isUpload() {
		// If we haven't processed the upload yet, do so
		if (!$this->_upload_processed) { $this->upload(); }
		return (!empty($this->_uploaded_files_by_input));
	}// /method



	/**
	 * Set the file extension whitelist.
	 *
	 * Uploaded files with extensions not in the list will be rejected.
	 *
	 * @param  array  $whitelist  The array of permissible file extensions.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setExtensionWhitelist($whitelist) {
		$this->_whitelist_extensions = $whitelist;
		return true;
	}// /method



	/**
	 * Set the control flags.
	 *
	 * For flag details, check ALLOW_???? and RESTRICT_???? for details.
	 *
	 * @param  integer  $flags  The control flag(s) to use.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setFlags($flags) {
		$this->_config['flags'] = (int) $flags;
		return true;
	}// /method



	/**
	 * Set the maximum permissible file size to upload.
	 *
	 * Uploaded files larger than the maximum size will be rejected.
	 * PHP ini settings, and the HTML form itself, can also affect the maximum file size allowed.
	 *
	 * @param  mixed  $max_size  The maximum file size in bytes.  Setting to Null means no limit.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setMaxFileSize($max_size) {
		$this->_restrict_file_size = $max_size;
		return true;
	}// /method



	/**
	 * Set the MIME type whitelist.
	 *
	 * Uploaded files with MIME types not in the list will be rejected.
	 *
	 * @param  array  $whitelist  The array of permissible file MIME types.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setMimeTypeWhitelist($whitelist) {
		$this->_whitelist_mime_types = $whitelist;
		return true;
	}// /method



	/**
	 * Set filenames to override.
	 *
	 * Renames files uploaded from specific input-tags.
	 * e.g.  To rename the file uploaded through <input type="file" name="profile_image" /> to "myprofile.jpg" use..
	 * ->setFilenames( array ( 'profile_image' => 'myprofile.jpg' ) )
	 * As subsequent uploads may reuse the same filename, e.g. 'profile.jpg', you may also want to set the ::ALLOW_OVERWRITE flag.
	 *
	 * @param  array  $filenames  Assoc-array of input-tags to filenames to override.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setFilenames($filenames) {
		foreach( (array) $filenames as $input => $filename) {
			$this->_config['filenames'][$input] = $filename;
		}
		return true;
	}// /method



	/**
	 * Set the destination path for uploaded files.
	 *
	 * The existence of the upload path is not checked.
	 * Use the UPLOAD__ALLOW_CREATEPATH flag to create paths on the fly.
	 *
	 * @param  string  $path  The path to upload to.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setUploadPath($path) {
		if (empty($path)) { throw new Ecl_Uploader_Exception('No upload path given'); }
		$this->_config['path'] = $path;
		return true;
	}// /method



	/**
	 * Upload the given files
	 *
	 * In the HTML form, the upload control MUST be named as an array.
	 * e.g.  <input type="file" name="files_to_upload[]" ... >
	 *
	 * If this method returns false, use ->getMessages() to find out why.
	 *
	 * Files may be renamed on upload if the file already exists, overwrite is off, and auto-rename is on.
	 *
	 * @return  boolean  The upload was completely successful.
	 */
	public function upload() {

		$this->_messages = array();
		$this->_uploaded_files_by_input = null;
		$this->_upload_processed = true;

		// check if any files have been supplied
		if (!$this->hasFilesPosted()) {
			$this->_addMessage('No file data supplied.');
			return false;
		}

		$files = $_FILES;   // Get the file data

		// Check upload path exists
		if (empty($this->_config['path'])) {
			$this->_addMessage('No upload path set.');
			return false;
		} else {
			if (!is_dir($this->_config['path'])) {
				if (!$this->hasFlag(self::ALLOW_CREATEPATH)) {
					$this->_addMessage('The upload folder requested does not exist.');
					return false;
				} else {
					if (!@mkdir($this->_config['path'], 0777, true)) {
						$this->_addMessage('Unable to create the requested upload folder.');
						return false;
					}
				}
			}
		}


		// Check upload path is writeable
		if (!is_writeable($this->_config['path'])) {
			$this->_addMessage('PHP does not have write permissions for the requested upload folder.');
			return false;
		}


		// Process each file to upload
		foreach ($files as $input_name => $file) {

			$raw_filename = $file['name'];

			// If there's a filename, there should be a file
			if (!empty($raw_filename)) {

				// Get the raw_file's basename and extension
				$pathinfo = pathinfo($raw_filename);
				$raw_filename_noext = $pathinfo['filename'];
				$raw_file_ext = $pathinfo['extension'];


				// If there's no file-size, then the file wasn't properly uploaded
				if (empty($file['size'])) {
					$this->_addMessage("Error uploading '$raw_filename'. The file was empty or not uploaded correctly (0 bytes in size).");
				} else {

					// Check if we're overriding files from specific input tags
					if (!empty($this->_config['filenames'])) {
						if (array_key_exists($input_name, $this->_config['filenames'])) {
							$filename = $this->_config['filenames'][$input_name];
						} else {
							$filename = strtolower($raw_filename);
						}
					} else {
						$filename = strtolower($raw_filename);
					}

					// Sanitize the target filename
					$filename = preg_replace('#^[.]*#', '', $filename);   // Remove any leading dots, '.'
					$filename = preg_replace('#[.]*$#', '', $filename);   // Remove any trailing dots, '.'
					$filename = preg_replace('#[^.0-9a-zA-Z()_-]#', '', $filename);   // Sanitize the filename

					$filename = str_replace(' ', '_', $filename);
					$filename = str_replace('%20', '_', $filename);


					// Get the file's basename and extension
					$pathinfo = pathinfo($filename);
					$filename_noext = $pathinfo['filename'];
					$file_ext = $pathinfo['extension'];

					// Set the path the uploaded file will be found at (when we finally move it)
					$new_file_path = "{$this->_config['path']}/{$filename}";

					$curr_file_error = false;   // Flag to show if there's an error with this particular file


					// Check file-size restriction (if applicable)
					if ( ($this->_restrict_file_size) && ($file['size']>$this->_restrict_file_size) ) {
						$curr_file_error = true;
						$this->_addMessage("Error uploading '$raw_filename'. The file exceeded the maximum upload size: {$this->_restrict_file_size} bytes.");
					}


					// Check file-extension restriction (if applicable)
			 		if ( ($this->_whitelist_extensions) && (!in_array($file_ext, $this->_whitelist_extensions)) ) {
						$curr_file_error = true;
						$this->_addMessage("Error uploading '$raw_filename' : You can only upload files with the extension(s): ". implode(', ',$this->_whitelist_extensions));
					}


					//Check mime-type restriction (if applicable)
					if ( ($this->_whitelist_mime_types) && (!in_array($file['type'],$this->_whitelist_mime_types)) ) {
						$curr_file_error = true;
						$this->_addMessage("Error uploading '$raw_filename' of type '{$file['type']}' : You can only upload files with the mime type(s): ". implode(', ',$this->_whitelist_mime_types));
					}


					// Check image restriction (if applicable)
					if ($this->hasFlag(self::RESTRICT_IMAGESONLY)) {
						$imginfo = getimagesize($file['tmp_name'] );
	    				if (!$imginfo[2]>0) {
							$curr_file_error = true;
							$this->_addMessage("Error uploading '$raw_filename' : The uploaded file must be an image.");
	    				}
					}


					// Check overwrite and auto-renaming restrictions (if applicable)
					if ( (!$this->hasFlag(self::ALLOW_OVERWRITE)) && (file_exists($new_file_path)) ) {
						if ($this->hasFlag(self::ALLOW_AUTORENAME)) {
							// Add a auto-increment suffix to the file. Find the first available number
							$suffix = 2;
							do {
								$suffix_str = str_pad($suffix, 2, '0', STR_PAD_LEFT);
								$new_filename = "{$filename_noext}_{$suffix_str}.{$file_ext}";   // <filename>(<suffix>).<extension>
								++$suffix;
							} while (file_exists("{$this->_config['path']}/{$new_filename}"));

							// New numbered-name determined, let's make sure the file will be uploaded using the new name
							$filename = $new_filename;
							$new_file_path = "{$this->_config['path']}/{$filename}";
						} else {
							$curr_file_error = true;
							$this->_addMessage("Error uploading '$raw_filename' : A file with that name already exists.");
						}
					}

					// If we passed all the checks so far, try moving the temporary file...
					if (!$curr_file_error) {

						// Check if the file is really an uploaded file (negate header-forgery attack)
						if (!is_uploaded_file($file['tmp_name'])) {
							$this->_addMessage("Error uploading '$raw_filename'. The initial file upload to the server was interrupted or failed.");
						} else {
							if ( (!move_uploaded_file($file['tmp_name'], $new_file_path)) ) {
								$this->_addMessage("Error uploading '$raw_filename' : Could not move the file to the upload folder.");
							} else {
								$this->_uploaded_files_by_input[$input_name]['filename'] = $raw_filename;
								$this->_uploaded_files_by_input[$input_name]['path'] = $new_file_path;
							}

							// Check and apply chmod (if applicable)
							if (!empty($this->_config['chmod'])) {
								if (!chmod("{$this->_config['path']}/{$file['name']}", $this->_chmod)) {
									$this->_addMessage("Error uploading '$raw_filename' : The file has been uploaded but the system could not apply the given chmod() setting.");
								}
							}
						}// /if(is_uploaded_file)
					}
				}// /if(file-size)

				// Delete the original uploaded file from the temp folder.
				// If uploaded successful, this should do nothing. If upload failed, it removes the file.
				@unlink($file['tmp_name']);

			}// /if(not empty filename)
		}// /foreach

		return (!$this->isMessage());
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */




	protected function _addMessage($text) {
		$this->_messages[] = $text;
	}// /method


}// /class
?>