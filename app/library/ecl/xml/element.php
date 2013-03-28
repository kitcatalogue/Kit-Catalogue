<?php
/**
 * XML Element class.
 *
 * @package Ecl
 * @version  1.0.0
 */
class Ecl_Xml_Element {

	// Public Vars
	public $data = null;
	public $tag = null;

	// Private Vars
	protected $_attributes = array();
	protected $_children = array();



	/**
	 * Constructor
	 */
	public function __construct($tag = null, $attributes = null, $data = null) {
		$this->tag = (!empty($tag)) ? $tag : null ;

		if ( (!empty($attributes)) && (is_array($attributes)) ) {
			foreach($attributes as $k => $v) {
				$this->setAttribute($k, $v);
			}
		}
		$this->data = (!empty($data)) ? $data : null ;
	}// /__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Clear the object
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function clear() {
		$this->data = null;
		$this->tag = null;

		$this->_attributes = array();
		$this->_children = array();
		return true;
	}// /method



	/**
	 * Escape all XML special characters.
	 *
	 * Converts special characters to the appropriate hex-entities.
	 *
	 * @param  string  $string  The string to escape.
	 *
	 * @return  string  The escaped string.
	 */
	public function escapeXml($string) {
		$string = str_replace('&', '&#x26;', $string);
		$string = str_replace('<', '&#x3C;', $string);
		$string = str_replace('>', '&#x3E;', $string);
		$string = str_replace('"', '&#x22;', $string);
		$string = str_replace('\'', '&#x27;', $string);
		return $string;
	}// /method



	/**
	 * Return the XML representation of this element and its children.
	 *
	 * @param  integer  $indent  (optional) The number of tab characters to indent this xml.
	 *
	 * @return  string  The XML representation. On fail, empty string.
	 */
	public function toXml($indent = 0) {
		$tabs = str_repeat("\t", $indent);
		$xml = "{$tabs}<{$this->tag}";

		// Get attributes
		if ($this->hasAttributes()) {
			foreach($this->_attributes as $k => $v) {
				$xml .= " $k=\"". $this->escapeXml($v) .'"';
			}
		}

		// If no data or children, close the empty tag
		if ( (empty($this->data)) && (!$this->_children) ) {
			$xml .= " />\n";
		} else {
			// This element is not empty

			$xml .= '>';

			// If there's data, ignore any children
			if (!empty($this->data)) {
<<<<<<< HEAD
				$temp = utf8_encode($this->data);
=======
				$temp = $this->data;   //utf8_encode($this->data);   Stopped encoding, as it kept producing 'Â' characters
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

				// Apply CDATA tags if needed
				if ( (strpos($temp,'<')!==false)
					|| (strpos($temp,'>')!==false)
					|| (strpos($temp,'&')!==false)
					|| (strpos($temp,'\'')!==false)
					|| (strpos($temp,'"')!==false)
					|| (strpos($temp,'\'')!==false) ) {
					$xml .= "<![CDATA[{$temp}]]>";
				} else {
					$xml .= $temp = $this->data;
				}
			} else {
				$xml .= "\n";
				// show the children, not the data
				foreach($this->_children as $i => $child) {
					$xml .= $child->toXml($indent + 1);
				}
				$xml .= $tabs;
			}

			$xml .= "</{$this->tag}>\n";
		}

		return $xml;
	}// /method



/* --------------------------------------------------------------------------------
 * Attribute methods
 */


	/**
	 * Delete an attribute.
	 *
	 * @param  string  $name  The attribute to delete.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function deleteAttribute($name) {
		if (array_key_exists($name, $this->_attributes)) {
			unset($this->_attributes[$name]);
		}
		return true;
	}// /method



	/**
	 * Get the value of the given attribute.
	 *
	 * @param  string  $name  The attribute to find.
	 * @param  mixed  $default  The default value to return if the attribute is not present. (default: null)
	 * @return  string  The attribute's value. On fail, $default.
	 */
	public function attribute($name, $default = null) {
		return (array_key_exists($name, $this->_attributes)) ? $this->_attributes[$name] : $default ;
	}// /method



	/**
	 * Get this element's attributes.
	 *
	 * @return  mixed  An assoc array of attributes. On fail, array().
	 */
	public function attributes() {
		return (!empty($this->_attributes)) ? $this->_attributes : array() ;
	}// /method



	/**
	 * Get any attributes with the given prefix.
	 *
	 * Useful for identifying things like XML Namespaces (xmlns).
	 *
	 * @param  string  $prefix  The prefix to search for.
	 *
	 * @return  mixed  An assoc array of attributes. On fail, array().
	 */
	public function attributesPrefixed($prefix) {
		if (empty($this->_attributes)) { return array(); }

		$matching_attrs = array();
		foreach($this->_attributes as $k => $v) {
			if (strpos($k, $prefix)===0) {
				$matching_attrs[$k] = $v;
			}
		}
		return $matching_attrs;
	}// /method



	/**
	 * Check if the element has attributes.
	 *
	 * @return  boolean  There are attributes.
	 */
	public function hasAttributes() {
		return (!empty($this->_attributes));
	}// /method



	/**
	 * Set an attribute.
	 *
	 * @param  string  $name  The attribute to set.
	 * @param  mixed  $value  The new value.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function setAttribute($name, $value) {
		$this->_attributes[$name] = $value;
	}// /method



/* --------------------------------------------------------------------------------
 * Child methods
 */



	/**
	 * Add a child element to this element.
	 *
	 * @param  object  $element  The XmlElement to add.
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addChild($element) {
		$this->_children[] = $element;
		return true;
	}// /method



	/**
	 * Add a 'complete' child tag to this element.
	 *
	 * @param  string  $tag  The element tag.
	 * @param  array  $attributes  (optional) An assoc-array of attributes. (default: null)
	 * @param  string  $data  (optional)  The tag content. (default: null)
	 *
	 * @return  boolean  The operation was successful.
	 */
	public function addChildTag($tag, $attributes = null, $data = null) {
		return $this->addChild( new Ecl_Xml_Element($tag, $attributes, $data) );
	}// /method



	/**
	 * Get the last child element of the requested type.
	 *
	 * An empty or missing $tag value, will return the last defined child element.
	 *
	 * @param  string  $tag  (optional) The tag to find. (default: null)
	 *
	 * @return  array  The last child Ecl_Xml_Element. On fail, null.
	 */
	public function lastChild($tag = null) {

		$count = count($this->_children);

		if ($count==0) { return null; }

		if (empty($tag)) {
			return $this->_children[$count-1];
		} else {
			$matching_tags = array();

			for($i=($count-1); $i>=0; $i--) {
				if ($this->_children[$i]->tag==$tag) {
					return $this->_children[$i];
				}
			}
		}
		return null;
	}// /method



	/**
	 * Get the requested child elements.
	 *
	 * An empty or missing $tag value, will search for all children.
	 *
	 * @param  string  $tag  (optional) The child elements to find. (default: null)
	 *
	 * @return  array  A single, or array of matching Ecl_Xml_Element. On fail, array().
	 */
	public function children($tag = null) {
		if (!$this->hasChildren()) { return array(); }

		if (empty($tag)) {
			return $this->_children();
		} else {
			$matching_tags = array();

			foreach($this->_children as $i => $child) {
				if ($child->tag==$tag) {
					$matching_tags[] = $child;
				}
			}
			return $matching_tags;
		}
	}// /method



	/**
	 * Check if the element has the given child tag.
	 *
	 * @param  string  $tag  The child tag to find.
	 *
	 * @return  boolean  There are child tags of that name.
	 */
	public function hasChild($tag) {
		if (!$this->hasChildren()) { return false; }

		foreach($this->_children as $i => $child) {
			if ($child->tag==$tag) {
				return true;
			}
		}

		return false;
	}// /method



	/**
	 * Check if the element has any children.
	 *
	 * @return  boolean  There are children.
	 */
	public function hasChildren() {
		return (!empty($this->_children));
	}// /method



}// /class
?>