<?php
/*
 * Setup the routes used by the MVC router.
 *
 * Routes will be processed in reverse order, so generic routing instructions are declared first in this list.
 */



// Generic Routes

$router->addRoute('*', ':controller');

$router->addRoute('*', ':controller/:action');

$router->addRoute('*', ':module/:controller/:action');

$router->addRoute('*', ':module/:controller/:action/:id');



// Support Routes

$router->addRoute('*', 'support/:itemname/:id/file/:filename', array(
	'controller'  => 'item' ,
	'action'      => 'downloadfile' ,
));


$router->addRoute('*', 'support/help', array(
	'module'      => 'support' ,
	'controller'  => 'help' ,
	'action'      => 'index' ,
));


$router->addRoute('*', 'support', array(
	'module'      => 'support' ,
	'controller'  => 'help' ,
	'action'      => 'index' ,
));



// Search Routes

$router->addRoute('*', 'search/' , array(
	'controller'  => 'item' ,
	'action'      => 'search' ,
));



// Custom field Routes

$router->addRoute('*', 'custom/' , array(
	'controller'  => 'item' ,
	'action'      => 'custom' ,
));



// Tags

$router->addRoute('*', 'tags/:tag', array(
	'controller'  => 'item' ,
	'action'      => 'tags' ,
));



// Resource / File Route

$router->addRoute('*', 'resource/:id', array(
	'controller'  => 'resource' ,
	'action'      => 'view' ,
));



// Item Routes

$router->addRoute('*', 'item/:itemname/:itemid', array(
	'controller'  => 'item' ,
	'action'      => 'view' ,
));


$router->addRoute('*', 'item/:itemname/:itemid/file/:filename', array(
	'controller'  => 'item' ,
	'action'      => 'downloadfile' ,
));



// Department Routes


$router->addRoute('*', $model->lang['dept.route'] , array(
	'controller'  => 'department' ,
));


$router->addRoute('*', $model->lang['dept.route'].'/:deptname/:deptid' , array(
	'controller'  => 'department' ,
	'action'      => 'view' ,
));


$router->addRoute('*', $model->lang['dept.route'].'/:deptname/:deptid/category/:catname/:catid', array(
	'controller'  => 'department' ,
	'action'      => 'view' ,
));


$router->addRoute('*', $model->lang['dept.route'].'/:deptname/:deptid/item/:itemname/:itemid', array(
	'controller'  => 'department' ,
	'action'      => 'viewitem' ,
));



// Custom Field Routes


$router->addRoute('*', 'customfield/:fieldname/:fieldid/:fieldvalue', array(
	'controller'  => 'customfield' ,
	'action'      => 'viewvalue' ,
));


$router->addRoute('*', 'customfield/:fieldname/:fieldid', array(
	'controller'  => 'customfield' ,
	'action'      => 'listvalues' ,
));



// Staff Contact Routes

$router->addRoute('*', 'contact/:contactid' , array(
	'controller'  => 'contact' ,
	'action'      => 'view' ,
));



// Category Routes


$router->addRoute('*', 'category/:catname/:catid' , array(
	'controller'  => 'category' ,
	'action'      => 'view' ,
));


$router->addRoute('*', 'category/:catname/:catid/item/:itemname/:itemid', array(
	'controller'  => 'category' ,
	'action'      => 'viewitem' ,
));



// ID Route

$router->addRoute('*', 'id/:action/:id', array (
	'controller' => 'id' ,
));



// Filter Routes


$router->addRoute('*', 'filter/' , array(
	'controller'  => 'filter' ,
	'action'      => 'view' ,
));


$router->addRoute('*', 'filter/JSON' , array(
	'controller'  => 'filter' ,
	'action'      => 'JSON' ,
));



// Calibration Routes


$router->addRoute('*', 'calibration/JSON' , array(
	'controller'  => 'calibration' ,
	'action'      => 'JSON' ,
));


// Admin homepage

$router->addRoute('*', 'admin', array (
	'controller'  => 'home' ,
	'action'      => 'index' ,
	'module'      => 'admin' ,
));


// Homepage Route

$router->addRoute('*', '/', array(
	'controller'  => 'home' ,
	'action'      => 'index' ,
));


