<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', '1');

date_default_timezone_set('Europe/London');
setlocale(LC_ALL, 'en_UK.UTF8');


define('KC_INSTALL_WIZARD', true);



// Define some constants
define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));
define('APP_PATH', ROOT_PATH . '/app');
define('LOCAL_PATH', ROOT_PATH . '/local');
define('WRITABLE_PATH', ROOT_PATH . '/writable');



// Check for installation lock
$path = LOCAL_PATH.'/local_config.php';
if (file_exists($path)) {
	include_once($path);
	if (isset($config) && (array_key_exists('install.enabled', $config)) && (false == $config['install.enabled'])) {
		die("<pre>

			Installation wizard disabled.

			The setting <em>\$config['install.enabled'] = false;</em> in <em>local/local_config.php</em>
			To enable the installer, change the configuration to  \$config['install.enabled'] = true;
			</pre>");
	}
}



function out($string) {
	echo htmlentities($string, ENT_QUOTES, 'UTF-8');
}


