<?php
/**
 * Debug object for outputting debug information
 *
 * This class must be called statically.
 *
 * @package  Ecl
 * @static
 * @version  1.0.0
 */
Class Ecl_Debug {

	// Public properties

	// Private properties



	/**
	 * Constructor
	 *
	 * You cannot call this constructor.  Use ::getInstance() instead.
	 *
	 * @access  private
	 */
	protected function __construct() {
	}// /->__construct()



/* --------------------------------------------------------------------------------
 * Public Methods
 */



	/**
	 * Show the object or class description in output.
	 *
	 * @param  mixed   $obj  The object/class name to describe.
	 *
	 * @return  boolean  True in all cases.
	 */
	public static function describe($obj) {

		if (is_object($obj)) {
			$class_name = get_class($obj);
		} else {
			$class_name = $obj;
		}

		echo('<pre class="ecl-describe">');

		if (!class_exists($class_name)) {
			echo("Class not defined: $class_name");
		} else {
			echo("== Class : $class_name ==\n\n");

			$class = new ReflectionClass($class_name);

			// List Constants
			$constants = $class->getConstants();

			echo("Constants:\n\n");
			if (empty($constants)) {
				echo("  No constants defined.\n");
			} else {
				foreach($constants as $constant => $value) {
					echo("  \${$constant}\n");
				}
			}
			echo("\n");

			// List Properties
			$properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

			echo("Public Properties:\n\n");
			if (empty($properties)) {
				echo("  No properties defined.\n");
			} else {
				foreach($properties as $i => $property) {
					echo("  \${$property->name}\n");
				}
			}
			echo("\n");

			// List methods
			$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

			echo("Public Methods:\n\n");
			if (empty($methods)) {
				echo("  No methods defined.\n");
			} else {
				foreach($methods as $i => $method_info) {
					echo("  {$method_info->name}(");
					$method = $class->getMethod($method_info->name);
					$parameters = $method->getParameters();
					if ($parameters) {
						$done_param = false;
						foreach($parameters as $j => $reflected_param) {
							if ($done_param) { echo(','); }
							echo(" \${$reflected_param->name} ");
							$done_param = true;
						}
					}
					echo(")\n");
				}
			}
			echo("\n");
		}
		echo('</pre>');
	}// /method



	/**
     * Show information about a variable in output.
     *
     * @param  mixed   $var  The variable to output.
     * @param  string  $label  (optional) A label to output before the dump.
     * @param  boolean  $use_html_entities  (optional) Encodes any HTML entities before output. (default: true)
	 *
	 * @return  boolean  True in all cases.
     */
    public static function dump($var, $label = null, $use_html_entities = true) {
    	$label = (empty($label)) ? '' : "== $label ==\n\n";

        // Open a buffer and var_dump the variable into it
        ob_start();
        var_dump($var);
        $output = ob_get_clean();

		// Tidy the output so elements/properties/etc are on a single line each
		$output = preg_replace("#\]\=\>\n(\s+)#m", "] => ", $output);

        // Convert HTML Entities and output
		if ($use_html_entities) {
			echo('<pre class="ecl-dump">' . $label . htmlentities($output, ENT_QUOTES) . '</pre>');
		} else {
			echo('<pre class="ecl-dump">' . $label . $output . '</pre>');
		}
		return true;
    }// /method



	/**
     * Show backtrace information in output.
     *
     * @param  string  $label  (optional) A label to output before the dump.
     *
	 * @return  boolean  True in all cases.
	 */
	public static function trace($label = null) {
		ob_start();
        debug_print_backtrace();
        $backtrace = htmlentities(ob_get_contents());
        ob_end_clean();
		echo('<pre class="ecl-trace">'. $label .$backtrace .'</pre>');
		return true;
	}// /method



/* --------------------------------------------------------------------------------
 * Private Methods
 */



}// /Class
?>