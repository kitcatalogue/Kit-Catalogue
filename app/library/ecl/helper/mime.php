<?php
/**
 * Mime Type helper class
 *
 * @package  Ecl
 * @static
 * @version  1.0.0
 */
class Ecl_Helper_Mime {

	// Private Properties
	protected $_map = array (

		// Audio
		'mp3'   => 'audio/mpeg3' ,
		'ogg'   => 'audio/x-ogg' ,
		'wav'   => 'audio/wav' ,

		// Application Specific
		'eps'    => 'application/postscript' ,
		'doc'    => 'application/msword' ,
		'docx'   => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ,
		'dot'    => 'application/msword' ,
		'dotx'   => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template' ,
		'latex'  => 'application/x-latex' ,
		'mdb'    => 'application/x-msaccess' ,
		'pdf'    => 'application/pdf' ,
		'pps'    => 'application/vnd.ms-powerpoint' ,
		'ppt'    => 'application/vnd.ms-powerpoint' ,
		'pptx'   => 'application/vnd.openxmlformats-officedocument.presentationml.presentation' ,
		'ps'     => 'application/postscript' ,
		'rtf'    => 'application/rtf' ,
		'swf'    => 'application/x-shockwave-flash' ,
		'tex'    => 'application/x-tex' ,
		'xhtml'  => 'application/xhtml+xml' ,
		'xls'    => 'application/x-msexcel' ,
		'xlsx'   => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ,

		// Compressed
		'gz'    => 'application/x-gtar' ,
		'rar'   => 'application/x-rar-compressed' ,
		'tar'   => 'application/x-tar' ,
		'tgz'   => 'application/x-gtar' ,
		'zip'   => 'application/zip' ,

		// Image/Graphic
		'bmp'   => 'image/x-bmp' ,
		'gif'   => 'image/gif' ,
		'ico'   => 'image/vnd.microsoft.icon' ,
		'jpe'   => 'image/jpeg' ,
		'jpeg'  => 'image/jpeg' ,
		'jpg'   => 'image/jpeg' ,
		'png'   => 'image/png' ,
		'tif'   => 'image/tiff' ,
		'tiff'  => 'image/tiff' ,

		// Textual
		'csv'    => 'text/csv' ,
		'htm'    => 'text/html' ,
		'html'   => 'text/html' ,
		'ics'    => 'text/calendar' ,
		'.shtml' => 'text/html' ,
		'txt'    => 'text/plain' ,
		'xml'    => 'text/xml' ,

		// Video
		'asf'   => 'video/x-ms-asf' ,
		'asx'   => 'video/x-ms-asf' ,
		'avi'   => 'video/x-msvideo' ,
		'mng'   => 'video/x-mng' ,
		'mpe'   => 'video/mpeg' ,
		'mpeg'  => 'video/mpeg' ,
		'mpg'   => 'video/mpeg' ,
		'wma'   => 'video/x-ms-asf' ,
		'wmv'   => 'video/x-ms-asf' ,
	);



	/**
	 * Constructor
	 */
	private function __construct() {
	}// /method



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Get the likely mime-type of a file with the given file extension.
	 *
	 * The default mime-type is 'application/octet-stream' and represents any binary file. It is suitable for unknown file downloads, etc.
	 *
	 * @param  string  $file_ext  The file extension (with or without the dot prefix).
	 * @param  string  $default_type  The default MIME-type to use, if not known. (default: application/octet-stream)
	 *
	 * @return  string  The probable mime-type.  If not-known, returns $default_type.
	 */
	public static function getTypeForExtension($file_ext, $default_type = 'application/octet-stream') {
		// Remove any dot prefix
		if ($file_ext[0]=='.') {
			$file_ext = substr($file_ext, 1, strlen($file_ext));
		}

		return (array_key_exists($file_ext, self::$_map)) ? self::$_map[$file_ext] : $default_type ;
	}// /method



	/**
	 * Get the likely mime-type of a file with the given file extension.
	 *
	 * The default mime-type is 'application/octet-stream' and represents any binary file. It is suitable for unknown file downloads, etc.
	 *
	 * @param  string  $file_ext  The file extension (with or without the dot prefix).
	 * @param  string  $default_type  The default MIME-type to use, if not known. (default: application/octet-stream)
	 *
	 * @return  string  The probable file extension (without the dot).  If not-known, returns $default_ext.
	 */
	public static function getExtensionForType($mime_type, $default_ext = 'dat') {
		$file_ext = array_search($mime_type, self::$_map);
		return ($file_ext!==false) ? $file_ext : $default_ext ;
	}// /method



}// /class
?>