<?php



class Ecl_Form_Exception extends Ecl_Exception {}



/**
 * Class for handling HTML form creation and validation.
 *
 * @package  Ecl
 * @version  1.0.0
 */
Class Ecl_Form {

	// Public properties

	// Private properties

	/**
	 * Configuration info
	 */
	protected $_config = array(
        'action'     => '' ,
		'method'     => 'post' ,
		'name'       => null ,
        'charset'    => 'UTF-8' ,
        'enctype'    => null ,
    );

	protected $_attrs = array();   // HTML attributes

	protected $_elements = array();   // Array of element objects
	protected $_order = array();   // Array of element names, and their display order



	/**
	 * Constructor
	 */
	public function __construct($config = null) {
		$this->_config = array_merge($this->_config, (array) $config);
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Add the given element to the form.
	 *
	 * This method can be called in two ways.
	 *
	 * (1) ->addElement($obj)
	 * Where $obj is an Ecl_Form_Element object.
	 * If used, the $name and $options parameters will still override any settings in the element object.
	 *
	 * (2) ->addElement('text', 'myfield', $options)
	 * Where 'text' is the element type, and 'myfield' is the name and ID of the form element.
	 * Additional configuration, validation and filtering options can be included using the $options array.
	 *
	 * @see Ecl_Form_Element
	 *
	 * @param  mixed  $element  The object, or element type, to add.
	 * @param  string  $name  (optional) The name of the element.
	 * @param  array  $options  (optional) The name of the element.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addElement($element, $name, $options) {
		if (!is_object($element)) {
			$element = $this->newElement($element);
		}

		$element->setName($name);
		$element->setOptions($options);


		// If it's a file element, change this form's enctype to accept file uploads
		if ($element instanceof Ecl_Form_Element_File) {
			$this->_config['enctype'] = 'multipart/form-data';
		}

		$this->_elements[$name] = $element;
		$this->_order[$name] = $element->getOrder();

		return true;
	}// /method



	public function getElement($name) {
		return (array_key_exists($name, $this->_elements)) ? $this->_elements[$name] : null;
	}// /method



	public function getAttributes() {
		return $this->_attrs;
	}// /method



	/**
	 * Check if the given element has the given value.
	 *
	 * If $value is null, the method checks to see if any non-empty value is present.
	 *
	 * @param  string  $name
	 * @param  mixed  $value  (optional)  (default: null)
	 *
	 * @return  boolean  The element has the value requested.  On fail, false.
	 */
	public function elementHasValue($name, $value = null) {
		$element = $this->getElement($name);

		if (empty($element)) { return false; }

		return (is_null($value)) ? (!Ecl::isEmpty($element->getValue)) : ($value==$element->getValue());
	}// /method



	public function isValid($values) {

		$this->setValues($values);

		$valid = true;

		foreach($this->_elements as $k => $element) {
			if (!$element->isValid()) {
				$valid = false;
			}
		}

		return $valid;
	}// /method



	/**
	 * Create a new element instance, of the given type.
	 *
	 * @param  string  $type  The type of element to instantiate.
	 *
	 * @return  object  The new element object.  On fail, null.
	 */
	public function newElement($type) {
		$class = 'Ecl_Form_Element_'. ucfirst($type);
		if (class_exists($class)) {
			return Ecl::factory($class);
		}
		throw Ecl_Form_Exception_InvalidElement("Unknown element type: '$type'.");
		return null;
	}// /method



	/**
	 * Remove the given element.
	 *
	 * @param  string  $name  The name of the element.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function removeElement($name) {
		unset($this->_elements[$name]);
		unset($this->_ordering[$name]);
		return true;
	}// /method



	public function render() {
		$attrs = $this->getAttributes();
		$attrs['action'] = $this->_config['action'];
		$attrs['accept-charset'] = $this->_config['charset'];
		$attrs['enctype'] = $this->_config['enctype'];
		$attrs['method'] = $this->_config['method'];
		$attrs['name'] = $this->_config['name'];

		$content = '';

		$content .= "\n";
		$content .= Ecl_Helper_Html::getTag('form', null, $attrs);
		$content .= "\n<dl>\n";
		if (!empty($this->_elements)) {

			Ecl_Helper_Array::sortStable($this->_order, function($a, $b) {
				if ($a==$b) { return 0; }
				return ($a > $b) ? 1 : -1 ;
			});

			foreach($this->_order as $k => $rank) {
				$content .= $this->_elements[$k]->render();
			}
		}
		$content .= "</dl>\n";
		$content .= "</form>\n";
		return $content;
	}// /method



	public function setAttribute($key, $value) {
		$this->_attrs[$k] = $value;
		return true;
	}// /method



	public function setValues($values) {
		if (is_array($values)) {
			foreach($values as $k => $v) {
				if (array_key_exists($k, $this->_elements)) {
					$this->_elements[$k]->setValue($v);
				}
			}
			return true;
		}
		return false;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /Class
?>