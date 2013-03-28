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



<<<<<<< HEAD
if (isset($config)) {
	$model->load($config);
}


if (isset($lang)) {
	$model->setObject('lang', $lang);
}



$model->setFunction('organisationalunitstore', function($model) {
	return new Organisationalunitstore($model, $model->get('db'));
});



$model->setFunction('ou_tree', function ($model) {
	$ou_tree = new Ecl_Tree_Manager($model->get('db'), 'ou_tree', array (
		'ordered' => true ,
	));

	$ou_tree->setLinkedTable('ou', 'ou_id');

	$ou_tree->setRowFunction( function($row) {
		$object = new StdClass();
		$object->id = $row['ou_id'];
		$object->name = $row['name'];
		$object->url = $row['url'];

		$object->item_count_internal = $row['item_count_internal'];
		$object->item_count_public = $row['item_count_public'];

		$object->tree_node_id = $row['tree_node_id'];
		$object->tree_level = $row['tree_level'];
		return $object;
	});

	return $ou_tree;
});
=======
$model->load($config);



$model->setObject('lang', $lang);
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd



$model->setObject('request', Ecl::factory('Ecl_Request'));



<<<<<<< HEAD
if (isset($router)) {
	$model->setObject('router', $router);
}



=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
$model->setFunction('db', function ($model) {
	$db = Ecl::factory('Ecl_Db_Mysql', array (
		'host'      => $model->get('db.host') ,
		'port'      => $model->get('db.port') ,
		'username'  => $model->get('db.username') ,
		'password'  => $model->get('db.password') ,
		'database'  => $model->get('db.database') ,
	));

	$db->setDebug( (bool) $model->get('app.debug', false));

<<<<<<< HEAD
	// We use SET NAMES too, in case our PHP version is one of the buggy ones
	$db->setCharset('utf8');
	$db->execute('SET NAMES utf8');

=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
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


<<<<<<< HEAD

=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
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
<<<<<<< HEAD
	return new Userstore($model, $model->get('app.user_session_var'));
=======
	return new Userstore($model->get('db'), $model->get('app.user_session_var'));
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
});



?>