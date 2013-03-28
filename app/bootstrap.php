<?php
/**
 * Application Bootstrap
 */



// --------------------------------------------------------------------------------
// Initialise PHP


// PHP Error Settings
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');
// @debug : ini_set('memory_limit','10M');



// PHP Locale Settings
date_default_timezone_set('Europe/London');
setlocale(LC_ALL, 'en_UK.UTF8');



// PHP Session Settings
ini_set('session.gc_maxlifetime', 2400);   // 40 minute session timeout
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
@session_start();



// --------------------------------------------------------------------------------
// Application Constants


// Types of feedback boxes
define('KC__FEEDBACK_ERROR', 'feedback_error');
define('KC__FEEDBACK_INFO', 'feedback_info');
define('KC__FEEDBACK_QUESTION', 'feedback_question');
define('KC__FEEDBACK_SUCCESS', 'feedback_success');
define('KC__FEEDBACK_WARNING', 'feedback_warning');


// Different user permissions/auths
<<<<<<< HEAD
define('KC__AUTH_CANADMIN', 'can_admin');       // Sysadmin
define('KC__AUTH_CANOUADMIN', 'can_ouadmin');   // OU specific admin
define('KC__AUTH_CANEDIT', 'can_edit');         // Deprecated
=======
define('KC__AUTH_CANADMIN', 'can_admin');
define('KC__AUTH_CANEDIT', 'can_edit');
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd


define('KC__OBJECT_SYSTEM', 'system');


// Different item visibilities
define('KC__VISIBILITY_PUBLIC', 1);
define('KC__VISIBILITY_INTERNAL', 2);
define('KC__VISIBILITY_DRAFT', 3);


<<<<<<< HEAD
=======

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
// Different category code types
define('KC__VOCABULARY_CPV', 'cpv');


<<<<<<< HEAD
// Define user parameters
define('KC__USER_HASITEMS', 'user_has_items');


=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

// --------------------------------------------------------------------------------
// Setup LDAP basics if extension not installed
// This stops errors appearing in the config files, etc


if (!defined('LDAP_OPT_PROTOCOL_VERSION')) {
	define('LDAP_OPT_PROTOCOL_VERSION', 17);
	define('LDAP_OPT_REFERRALS', 8);
}



// --------------------------------------------------------------------------------
// Load Application Default Config


require(dirname(__FILE__). '/config.php');



// --------------------------------------------------------------------------------
// Initialise the ECL framework


require($config['app.include_root'] .'/library/ecl.php');



// --------------------------------------------------------------------------------
// Load Application Default Language


$lang = Ecl::factory('Ecl_Dictionary');
require(dirname(__FILE__). '/language.php');



// --------------------------------------------------------------------------------
// Load Local Config


$path = $config['app.local_root'].'/local_config.php';
if (file_exists($path)) { include($path); }



// --------------------------------------------------------------------------------
<<<<<<< HEAD
// Normalise Config


// These config settings have been deprecated over time.
// We normalise them here.

if ( (!isset($config['app.email.owner'])) && (isset($config['app.email'])) ) {
	 $config['app.email.owner'] = $config['app.email'];
}



// --------------------------------------------------------------------------------
=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
// Load Local Language File


$path = $config['app.local_root'].'/local_language.php';
if (file_exists($path)) { include($path); }



// Some of the language definitions MUST obey certain rules.

// Ensure the 'dept.route' is valid
<<<<<<< HEAD
=======
//Ecl::dump($lang['dept.route']);
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
if (preg_match('/[^a-zA-Z0-9]/', $lang['dept.route'])) {
	die('Error - The following setting is invalid: $lang[\'dept.route\'] = \''. $lang['dept.route'] .'\'');
}

<<<<<<< HEAD
// @fix : OU route check
=======

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

?>