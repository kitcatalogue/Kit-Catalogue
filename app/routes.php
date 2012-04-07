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


$router->addRoute('*', 'department/:deptname/:deptid' , array(
	'controller'  => 'department' ,
	'action'      => 'view' ,
));


$router->addRoute('*', 'department/:deptname/:deptid/category/:catname/:catid', array(
	'controller'  => 'department' ,
	'action'      => 'view' ,
));


$router->addRoute('*', 'department/:deptname/:deptid/item/:itemname/:itemid', array(
	'controller'  => 'department' ,
	'action'      => 'viewitem' ,
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



// Homepage Route

$router->addRoute('*', '/', array(
	'controller'  => 'home' ,
	'action'      => 'index' ,
));


