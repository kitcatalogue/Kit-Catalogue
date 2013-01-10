<?php



ini_set('display_errors', '1');
error_reporting(E_ALL);



// --------------------------------------------------------------------------------
// Begin bootstrap


include('./app/bootstrap.php');



// --------------------------------------------------------------------------------
// Setup router object


$router = Ecl::factory('Ecl_Mvc_Router', array (
	'mvc_root'  => $config['app.include_root'] ,
));


$router->baseUri($config['app.www']);


$router->controllerDefault('error');
$router->actionDefault('index');



// --------------------------------------------------------------------------------
// Setup model


include($config['app.include_root'].'/model.php');


$router->model($model);



// --------------------------------------------------------------------------------
// Setup routing instructions



$secure_base_url = preg_replace('#^http:#', 'https:', $config['app.www']);


// if the current scheme is HTTPS, then continue to use it
if ($model->get('request')->isSecure()) {
	$router->baseUri($secure_base_url);
} else {
	// If using HTTPS, switch to it if user is authenticated
	if ($config['app.use_https']) {
		if (!$model->get('user')->isAnonymous()) {
			$router->baseUri($secure_base_url);
		}
	}
}

include($config['app.include_root'].'/routes.php');



// --------------------------------------------------------------------------------
// Setup layout


include($config['app.include_root'].'/classes/kc_layout.php');
$router->layout(new Kc_Layout());

if (empty($config['layout.template_file'])) {
	$router->layout()->setTemplate('kitcatalogue');
} else {
	$layout_template_file = $config['app.local_root'].DIRECTORY_SEPARATOR.'layouts'.DIRECTORY_SEPARATOR.$config['layout.template_file'];
	if (!file_exists($layout_template_file)) {
		die("The configuration file defined a non-existent template in 'layout.template_file'.");
	} else {
		$router->layout()->setTemplateFile($layout_template_file);
	}
}
$router->layout()->addBreadcrumb('Home', $router->makeAbsoluteUri('/'));


// --------------------------------------------------------------------------------
// Setup any plugins defined in the local plugins folder and add them to the model


include($config['app.include_root'].'/plugins.php');



// --------------------------------------------------------------------------------
// Route and dispatch current request


$router->dispatch();



?>