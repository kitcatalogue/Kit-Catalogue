<?php



class Ecl_Mvc_Layout_Exception extends Ecl_Mvc_Exception {}



/**
 * A class for handling layouts.
 *
 * Use this class to define the generic portions of your web pages, that you want to wrap around
 * the content produced from the controller(s).
 * To define default HTTP headers to send, set them directly on the router's response object.
 *
 * @package  Ecl
 * @version  1.0.0
 */
class Ecl_Mvc_Layout extends Ecl_Mvc {

	// Public Properties


	// Private Properties
	protected $_encoding = 'utf-8';

	protected $_sections = array();   // Named body sections

	protected $_template_filepath = null;

	protected $_content = null;   // The content to be rendered in the layout



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Append the given section with content.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 *
	 * @return  boolean  The operation was succesful.
	 */
	public function appendSection($section, $content) {
		if (isset($this->_sections[$section])) {
			$this->_sections[$section] .= (string) $content;
		} else {
			$this->_sections[$section] = (string) $content;
		}
		return true;
	}// /method



	public function clear() {
		$this->_content = '';
		$this->_sections = array();
	}// /method



	/**
	 * Get/Set the layout content.
	 *
	 * @param  string  $content  (optional)
	 *
	 * @return  string  The current layout.
	 */
	public function content($content = null) {
		if (!is_null($content)) { $this->_content = $content; }
		return $this->_content;
	}// /method



	/**
	 * Escape the given string for output.
	 *
	 * @param  string  $string
	 * @param  string  $charset  (default: 'UTF-8')
	 *
	 * @return  string
	 */
	public function escape($string, $charset = 'UTF-8') {
		return htmlspecialchars($string, ENT_COMPAT | ENT_IGNORE, $charset, false);
	}// /method



	/**
	 * Get the content for the given section.
	 *
	 * @param  string  $section
	 *
	 * @return  string  The body or section content.
	 */
	public function getSection($section) {
		return (isset($this->_sections[$section])) ? $this->_sections[$section] : null ;
	}// /method



	/**
	 * Initialise the layout.
	 *
	 * @param  object  $router  The router that created this view object.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function init($router) {
		parent::init($router);
		$this->clear();
		return true;
	}// /method



	/**
	 * Echo out the escaped version given value.
	 *
	 * The value will be escaped for HTML.
	 *
	 * @param  mixed  $value
	 *
	 * return  string  The operation was successful.
	 */
	public function out($value) {
		echo $this->escape($value);
	}// /method



	/**
	 * Echo out the escaped value, if it is not empty.
	 *
	 * @param  mixed  $value
	 * @param  string  $format  (default: '%s')
	 *
 	 * return  boolean  The operation was successful.
	 */
	public function outf($value, $format = '%s') {
		if (!empty($value)) {
			printf($format, $this->escape($value));
		}
		return true;
	}



	/**
	 * Prepend the given body section.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 *
	 * @return  boolean  The operation was succesful.
	 */
	public function prependSection($section, $content) {
		if (isset($this->_sections[$section])) {
			$this->_sections[$section] = (string) $content . $this->_sections[$section];
		} else {
			$this->_sections[$section] = (string) $content;
		}
		return true;
	}// /method



	/**
	 * Render the layout using the given content.
	 *
	 * If no layout template is defined, this method will simply return the content unchanged.
	 *
	 * @param  string  $content  (optional)
	 *
	 * @return  string  The output of the layout.
	 */
	public function render($content = null) {
		if (empty($this->_template_filepath)) {
			return $content;
		} else {
			if (!is_null($content)) { $this->content($content); }
			ob_start();
			include($this->_template_filepath);
			return ob_get_clean();
		}
	}// /method



	/**
	 * Render any content in the given section.
	 *
	 * @param  string  $section
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function renderSection($section) {
		if (isset($this->_sections[$section])) {
			echo $this->_sections[$section];
			return true;
		} else {
			return false;
		}
	}// /method



	/**
	 * Render and echo out a view.
	 *
	 * @param  string  $view_name
	 * @param  string  $module_name  (optional)  If not given, the router's current module will be used.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function renderView($view_name, $module_name = null) {
		if (null === $module_name) { $module_name = $this->router()->getCurrentModule(); }

		$include_path = $this->router()->viewPath($view_name, $module_name);

		if (file_exists($include_path)) {
			include($include_path);
		} else {
			if (null === $module_name) {
				throw new Ecl_Mvc_Layout_Exception("Unknown view: '$view_name'.", 1);
			} else {
				throw new Ecl_Mvc_Layout_Exception("Unknown view: '$view_name' in module '$module_name'.", 1);
			}
		}
	}// /method



	/**
	 * Set the given body content.
	 *
	 * @param  string  $section
	 * @param  string  $content
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setSection($section, $content) {
		$this->_sections[$section] = (string) $content;
		return true;
	}// /method



	/**
	 * Set the layout template.
	 *
	 * @return  boolean  The operation was succesful.
	 */
	public function setTemplate($layout_template) {
		$include_path = $this->router()->layoutPath($layout_template);

		if (!file_exists($include_path)) {
			throw new Ecl_Mvc_Layout_Exception("Unknown layout: '$layout_template'.", 1);
		} else {
			$this->_template_filepath = $include_path;
		}
		return true;
	}// /method



	/**
	 * Set the layout template using a direct file-path.
	 *
	 * @return  boolean  The operation was succesful.
	 */
	public function setTemplateFile($filename) {
		if (!file_exists($filename)) {
			throw new Ecl_Mvc_Layout_Exception("Unknown layout file: '$filename'.", 1);
		} else {
			$this->_template_filepath = $filename;
		}
		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /class
?>