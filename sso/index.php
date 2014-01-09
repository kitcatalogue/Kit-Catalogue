<?php
/*
 * Authenticate a Shibboleth SSO user
 */


include('../app/bootstrap.php');
include('../app/model.php');


$router = Ecl::factory('Ecl_Mvc_Router', array (
	'mvc_root'  => $config['app.include_root'] ,
));

$router->baseUri($model->get('app.www'));


if ($model->get('signin.use_shibboleth')) {
	$user_info = $model->get('authenticator')->authenticateShibboleth();

	// @debug : Ecl::dump($user_info);

	if ($user_info) {
		$model->get('authenticator')->loginUser($user_info);
	}
}


header('Location: '. $router->makeAbsoluteUri('/', $model->get('app.use_https')) );
exit();