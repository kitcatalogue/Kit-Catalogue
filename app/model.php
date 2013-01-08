<?php
/*
 * Setup the model object
 *
 * The model object acts as a registry of domain objects, and also manages their dependencies.
 */



$model = Ecl::factory('Ecl_Mvc_Model');



// Add include paths
$model->addIncludePath($config['app.include_root'].'/models');



// The default factory function for instantiating basic model objects.
// Essentially, this factory handles the '????store' classes.
$model->setDefaultFactory(function ($name, $model) {
	$class = ucwords($name);
	if (class_exists($class, true)) {
		$result = new $class($model->get('db'));
		return $result;
	}
	return null;
});



// Normalise config
// Those config settings that have been deprecated over time are normalised here.
if ( (!isset($config['app.email.owner'])) && (!isset($config['app.email'])) ) {
	 $config['app.email.owner'] = $config['app.email'];
}

$model->load($config);



$model->setObject('lang', $lang);



$model->setObject('request', Ecl::factory('Ecl_Request'));



$model->setObject('router', $router);



$model->setFunction('db', function ($model) {
	$db = Ecl::factory('Ecl_Db_Mysql', array (
		'host'      => $model->get('db.host') ,
		'port'      => $model->get('db.port') ,
		'username'  => $model->get('db.username') ,
		'password'  => $model->get('db.password') ,
		'database'  => $model->get('db.database') ,
	));

	$db->setDebug( (bool) $model->get('app.debug', false));

	$db->execute('SET NAMES utf8');

	return $db;
});



$model->setFunction('security', function ($model) {
	return new Security($model->get('user'), $model->get('sysauth'), $model);
});



$model->setFunction('sysauth', function ($model) {
	$sys_auth = new Ecl_Authorisation();
	$sys_auth->setDatabase($model->get('db'));
	$sys_auth->setTable('system_authorisation');
	return $sys_auth;
});



$model->setFunction('customfieldstore', function ($model) {
	return new CustomfieldStore($model);
});



$model->setFunction('itemstore', function ($model) {
	return new ItemStore($model);
});


$model->setFunction('user', function ($model) {
	$user = Ecl::factory('Ecl_User');

	if ($model->get('userstore')->isUserSession()) {
		$user = $model->get('userstore')->newUserFromSession();
	} else {
		$model->get('userstore')->clearUserSession();
	}

	$user->setParam('visibility', ($user->isAnonymous()) ? KC__VISIBILITY_PUBLIC : KC__VISIBILITY_INTERNAL);

	return $user;
});



$model->setFunction('userstore', function ($model) {
	return new Userstore($model->get('db'), $model->get('app.user_session_var'));
});



?>