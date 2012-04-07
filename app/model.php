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


$model->load($config);



$model->setObject('request', Ecl::factory('Ecl_Request'));



$model->setFunction('db', function ($model) {
	$db = Ecl::factory('Ecl_Db_Mysql', array (
		'host'      => $model->get('db.host') ,
		'port'      => $model->get('db.port') ,
		'username'  => $model->get('db.username') ,
		'password'  => $model->get('db.password') ,
		'database'  => $model->get('db.database') ,
	));

	$db->setDebug( (bool) $model->get('app.debug', false));

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



$model->setFunction('itemstore', function ($model) {
	return new ItemStore($model);
});



$model->setFunction('user', function ($model) {
	$user = Ecl::factory('Ecl_User');

	if (false === $model->get('request')->session('_user_data', false)) {
		$model->get('userstore')->clearUserSession();
	} else {
		$user = $model->get('userstore')->newUserFromSession($model->get('request')->session('_user_data'));
	}

	$user->setParam('visibility', ($user->isAnonymous()) ? KC__VISIBILITY_PUBLIC : KC__VISIBILITY_INTERNAL);

	return $user;
});



?>