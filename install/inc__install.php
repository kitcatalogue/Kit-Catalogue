<?php



define('KC_INSTALL_WIZARD', true);



define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));
define('APP_PATH', ROOT_PATH . '/app');
define('LOCAL_PATH', ROOT_PATH . '/local');
define('WRITABLE_PATH', ROOT_PATH . '/writable');



define('INSTALLER_PATH', realpath(__DIR__));



// Setup LDAP basics if extension not installed (This stops errors appearing)
if (!defined('LDAP_OPT_PROTOCOL_VERSION')) {
	define('LDAP_OPT_PROTOCOL_VERSION', 17);
	define('LDAP_OPT_REFERRALS', 8);
}



// Check for installation lock
$path = LOCAL_PATH.'/local_config.php';
if (file_exists($path)) {
	include_once($path);
	if (isset($config) && (array_key_exists('install.enabled', $config)) && (false == $config['install.enabled'])) {
		die("<pre>
			Installation wizard disabled.

			The setting <em>\$config['install.enabled'] = false;</em> in <em>local/local_config.php</em>
			To enable the installer, change the configuration to  <em>\$config['install.enabled'] = true;</em>
			</pre>");
	}
}



require('../app/bootstrap.php');



function out($string) {
	echo htmlentities($string, ENT_QUOTES, 'UTF-8');
}


