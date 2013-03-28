<?php
/**
 * Class representing a simple Image resource.
 *
 * @package  Ecl
 * @version 1.0.0
 */
class Ecl_Image {

	// Private Properties
	protected $_image = null;   // The image resource

<<<<<<< HEAD
	protected $_filename = null;

=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	protected $_width = null;
	protected $_height = null;



	/**
	 * Constructor
	 *
<<<<<<< HEAD
	 * @see createFromFile()
	 * @see createFromImage()
	 * @see createFromString()
	 */
	protected function __construct($resource) {
		if (!is_resource($resource)) { return false; }

		$this->_image = $resource;
		$this->_width = imagesx($this->_image);
		$this->_height = imagesy($this->_image);
=======
	 */
	public function __construct() {
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Clear all data from this image.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function clear() {

		if ($this->_image) { imagedestroy($this->_image); }
		$this->_image = null;

		$this->_width = null;
		$this->_height = null;

		return true;
	}// /method



	/**
	 * Crop the image to the given dimensions.
	 *
	 * @param  integer  $x  The top co-ordinate to crop from.
	 * @param  integer  $y  The left co-ordinate to crop from.
	 * @param  integer  $width  The width to copy.
	 * @param  integer  $height  The height to copy.
	 *
	 * @return  boolean  The operation was sucessful.
	 */
	public function crop($x, $y, $width, $height) {

		if (!$this->_image) { return false; }

		$source = $this->_image;

		// Create an empty image of the new size
		$target = imagecreatetruecolor($width, $height);

		// Copy the image
		imagecopy($target, $source, 0, 0, $x, $y, $width, $height);

		imagedestroy($source);

		$this->_image = $target;

		$this->_width = $width;
		$this->_height = $height;

		return true;
	}// /method



	/**
<<<<<<< HEAD
	 * Create an image instance from the given file.
	 *
	 * @param  string  $filename  The file to load.
	 *
	 * @return  object  The image object. On fail, null.
	 */
	public static function createFromFile($filename) {
=======
	 * Load this image from the given file.
	 *
	 * @param  string  $filename  The file to load.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function fromFile($filename) {

		$this->clear();
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

		if (!file_exists($filename)) { return false; }

		$info = @getimagesize($filename);
<<<<<<< HEAD
		if (!$info) { return null; }
=======
		if (!$info) { return false; }
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

		$image_functions = array (
			IMAGETYPE_GIF   => 'imagecreatefromgif' ,
			IMAGETYPE_JPEG  => 'imagecreatefromjpeg' ,
			IMAGETYPE_PNG   => 'imagecreatefrompng' ,
			IMAGETYPE_WBMP  => 'imagecreatefromwbmp' ,
			IMAGETYPE_XBM   => 'imagecreatefromwxbm' ,
		);

<<<<<<< HEAD
		if (!array_key_exists($info[2], $image_functions)) { return null; }

		if (!function_exists($image_functions[$info[2]])) { return null; }

		// Get the image resource
		$img_resource = $image_functions[$info[2]]($filename);
		if (!$img_resource) { return null; }

		$img = self::createFromImage($img_resource);
		$img->setFilename($filename);

		return $img;
=======
		if (!array_key_exists($info[2], $image_functions)) { return false; }

		if (!function_exists($image_functions[$info[2]])) { return false; }

		// Get the image resource
		$this->_image = $image_functions[$info[2]]($filename);

		$this->_width = $info[0];
		$this->_height = $info[1];

		return true;
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	}// /method



	/**
	 * Load this image from an existing PHP image resource
	 *
	 * @param  resource  $img  The image to load.
	 *
<<<<<<< HEAD
	 * @return  object  The image object. On fail, null.
	 */
	public static function createFromImage($resource) {
		if (!is_resource($resource)) { return null; }
		return new static($resource);
=======
	 * @return  boolean  The operation was successful
	 */
	public function fromImage($img) {
		if (!is_resource($img)) { return false; }

		// Get the image resource
		$this->_image = $img;
		$this->_width = imagesx($this->_image);
		$this->_height = imagesy($this->_image);

		return true;
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	}// /method



	/**
<<<<<<< HEAD
	 * Create an image instance from a string representation.
	 *
	 * @param  string  $string  The image as a raw string.
	 *
	 * @return  object  The image object. On fail, null.
	 */
	public static function createFromString($string) {

		$img_resource = imagecreatefromstring($string);
		if (!$img) { return null; }

		return self::createFromResource($img_resourcE);
=======
	 * Load this image from the string representation.
	 *
	 * @param  string  $string  The image as a raw string.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function fromString($string) {

		$this->clear();

		$img = imagecreatefromstring($string);

		if (!$img) { return false; }

		// Get the image resource
		$this->_image = $img;

		$this->_width = imagesx($this->_image);
		$this->_height = imagesy($this->_image);

		return true;
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	}// /method



	/**
	 * Get the height of this image.
	 *
	 * @return  mixed  The image height. On fail, null.
	 */
	public function getHeight() {
		return $this->_height;
	}// /method



	/**
	 * Get the width of this image.
	 *
	 * @return  mixed  The image width. On fail, null.
	 */
	public function getWidth() {
		return $this->_width;
	}// /method



	/**
	 * Output the image to the browser in gif format.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function outputGif() {
		if (!$this->_image) { return false; }

		imagepng($this->_image, null);
	}// /method



	/**
	 * Output this image to the browser in jpeg format.
	 *
	 * @param  integer  $quality  The image quality to use.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function outputJpeg($quality = 100) {
		if (!$this->_image) { return false; }
		if ( ($quality<0) || ($quality>100) ) { $quality = 100; }

		imagejpeg($this->_image, null, $quality);
	}// /method



	/**
	 * Output the image to the browser in png format.
	 *
	 * @param  integer  $quality  The image quality to use.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function outputPng($quality = 100) {
		if (!$this->_image) { return false; }
		if ( ($quality<0) || ($quality>100) ) { $quality = 100; }

		imagepng($this->_image, null, $quality);
	}// /method



	/**
	 * Resize the image to the given size.
	 *
	 * To maintain the aspect ratio, resize on just one dimension, and use null for the other.
	 * At least one of $width or $height must be given.
	 *
	 * @param  integer  $width  The width required. (If null, proportional resize on height)
	 * @param  integer  $height  The height required. (If null, proportional resize on width)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function resize($width, $height) {

		if (!$this->_image) { return false; }

		if (is_null($width) && (is_null($height)) ) { return false; }

		$old_width = $this->_width;
		$old_height = $this->_height;

		$width = (int) $width;
		$height = (int) $height;

		// Calculate new image size
		if ( ($width) && ($height) ) {
			$new_width = $width;
			$new_height = $height;
		} else {
			// Calculate the proportional resizing
			if ( ($width) && (is_null($height)) ) {
				$new_width = $width;
				$new_height = ($old_height/$old_width) * $new_width;
			} else {
				$new_height = $height;
				$new_width = ($old_width/$old_height) * $new_height;
			}
		}


		if ( ($new_width>0) && ($new_height>0) ) {

			$source = $this->_image;

			// Create an empty image of the new size
			$target = imagecreatetruecolor($new_width, $new_height);

			// Resize the image
			imagecopyresampled($target, $source, 0, 0, 0, 0, $new_width, $new_height, $old_width, $old_height);

<<<<<<< HEAD
			imagedestroy($source);
=======
			imagedestory($source);
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

			$this->_image = $target;

			$this->_width = $new_width;
			$this->_height = $new_height;
		}

		return true;
	}// /method



	/**
	 * Resize the image to the required size, but limit the dimensions to the given maximum width/height.
	 *
	 * If the image's dimensions are smaller than the max, then no resize occurs but the method still returns true.
	 * If one or both dimensions are too large, the image is resized to the appropriate maximum dimension while maintaining the aspect ratio.
	 *
	 * @param  mixed  $max_width  (optional) The maximum width wanted (if null, no restriction).
	 * @param  mixed  $max_height  (optional) The maximum height wanted (if null, no restriction).
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function resizeWithinLimits($max_width = null, $max_height = null) {

		if (!$this->_image) { return false; }

		$old_width = $this->getWidth();
		$old_height = $this->getHeight();

		$width = $old_width;
		$height = $old_height;


		// If not restricting dimensions, or if the dimensions are fine, change nothing
		if ( ( (is_null($max_width)) && (is_null($max_height)) ) || ( ($old_width<$max_width) && ($old_height<$max_height) ) ) {
				return true;
		}


		// Check widths & height..
		if ( (!is_null($max_width)) && (!is_null($max_height)) ) {
			// If both dimensions are too big, find the worst offender
			if ( ($width<$max_width) && ($height<$max_height) ) {
				if ( ($width-$max_width)>($height-$max_height) ) {
					$width = $max_width;
					$height = floor( ($old_height/$old_width) * $width );
				} else {
					$height = $max_height;
					$width = floor( ($old_width/$old_height) * $height );
				}
			}
		}


		// Check widths..
		if (!is_null($max_width)) {
			// If width > max, then recalculate
			if ( (!is_null($max_width)) && ($width>$max_width) ) {
				$height = floor( ($height/$width) * $max_width );
				$width = $max_width;
			}
		}

		// Check heights..
		if (!is_null($max_height)) {
			// If height > max, then recalculate
			if ( (!is_null($max_width)) && ($height>$max_height) ) {
				$width = floor( ($width/$height) * $max_height );
				$height = $max_height;
			}
		}

		return $this->resize($width, $height);
	}// /method



<<<<<<< HEAD
	public function setFilename($filename) {
		$this->_filename = $filename;
		return true;
	}



=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	/**
	 * Returns the Data URL equivalent for the current image contents in jpeg format.
	 *
	 * @return  mixed  The data url requested. On fail, null.
	 */
	public function toDataUrl() {
		if (!$this->_image) { return null; }

		$data = null;

		ob_start();
		imagejpeg($this->_image, '', 100);
		$data = ob_get_contents();
		ob_end_clean();

		if($data) {
			$data = 'data:image/jpeg;base64,' . base64_encode($data);
		}
		return $data;
	}// /method



	/**
<<<<<<< HEAD
	 * Save the image back to its original filename.
	 *
	 * If the image was not created using ->fromFile() then this method will fail.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function save() {
		if (empty($this->_filename)) { return false; }

		$extension = strtolower(Ecl_Helper_Filesystem::getFileExtension($this->_filename));
		switch($extension) {
			case 'gif':
				$this->saveGif($this->_filename);
				break;
			case 'jpg':
			case 'jpeg':
				$this->saveJpeg($this->_filename);
				break;
			case 'png':
				$this->savePng($this->_filename);
				break;
			default:
				return false;
				break;
		}

		return true;
	}



	/**
=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	 * Save this image to a file in gif format.
	 *
	 * @param  string  $filename  The file to save to.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function saveGif($filename) {
		if (!$this->_image) { return false; }

		imagepng($this->_image, $filename);
	}// /method



	/**
	 * Save this image to a file in jpeg format.
	 *
	 * @param  string  $filename  The file to save to.
	 * @param  integer  $quality  The image quality to use.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function saveJpeg($filename, $quality = 100) {
		if (!$this->_image) { return false; }
		if ( ($quality<0) || ($quality>100) ) { $quality = 100; }

		imagejpeg($this->_image, $filename, $quality);
	}// /method



	/**
	 * Save this image to a file in png format.
	 *
	 * @param  string  $filename  The file to save to.
	 * @param  integer  $quality  The image quality to use.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function savePng($filename, $quality = 100) {
		if (!$this->_image) { return false; }
		if ( ($quality<0) || ($quality>100) ) { $quality = 100; }

		imagepng($this->_image, $filename, $quality);
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>