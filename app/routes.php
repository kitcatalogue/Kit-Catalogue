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



// API

$router->addroute('*', 'api', array (
	'controller'  => 'api' ,
	'action'      => 'index' ,
));


$router->addroute('*', 'api/public', array (
	'controller'  => 'apipublic' ,
));

$router->addroute('*', 'api/public/:action', array (
	'controller'  => 'apipublic' ,
));


$router->addroute('*', 'api/private', array (
	'controller'  => 'apiprivate' ,
));


$router->addroute('*', 'api/private/:action', array (
	'controller'  => 'apiprivate' ,
));



// Support

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



// Search


$router->addRoute('*', 'search/item/:itemname/:itemid' , array(
	'controller'  => 'search' ,
	'action'      => 'viewitem' ,
));

$router->addRoute('*', 'search/' , array(
	'controller'  => 'search' ,
	'action'      => 'results' ,
));



// My Profile

$router->addRoute('*', 'myprofile/items/:itemname/:itemid', array(
	'controller'  => 'myprofile' ,
	'action'      => 'viewitem' ,
));



// Tags

$router->addRoute('*', 'tag/:tag', array(
	'controller'  => 'tag' ,
	'action'      => 'view' ,
));

$router->addRoute('*', 'tag/:tag/item/:itemname/:itemid', array(
	'controller'  => 'tag' ,
	'action'      => 'viewitem' ,
));



// Facilities

$router->addRoute('*', 'facility/:item/:itemname/:itemid', array(
	'controller'  => 'facility' ,
	'action'      => 'viewitem' ,
));




// Resource / File

$router->addRoute('*', 'resource/:id', array(
	'controller'  => 'resource' ,
	'action'      => 'view' ,
));



// Item

$router->addRoute('*', 'item/:itemname/:itemid', array(
	'controller'  => 'item' ,
	'action'      => 'view' ,
));

$router->addRoute('*', 'item/:itemname/:itemid/file/:filename', array(
	'controller'  => 'item' ,
	'action'      => 'downloadfile' ,
));

$router->addRoute('*', 'item/:itemname/:itemid/image/:image', array(
	'controller'  => 'item' ,
	'action'      => 'downloadimage' ,
));



// Department


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



// Custom Field


$router->addRoute('*', 'customfield/:fieldname/:fieldid/:fieldvalue/item/:itemname/:itemid', array(
	'controller'  => 'customfield' ,
	'action'      => 'viewitem' ,
));

$router->addRoute('*', 'customfield/:fieldname/:fieldid/:fieldvalue', array(
	'controller'  => 'customfield' ,
	'action'      => 'viewvalue' ,
));

$router->addRoute('*', 'customfield/:fieldname/:fieldid', array(
	'controller'  => 'customfield' ,
	'action'      => 'listvalues' ,
));



// Staff Contact

$router->addRoute('*', 'contact/:contactid' , array(
	'controller'  => 'contact' ,
	'action'      => 'view' ,
));



// Category

$router->addRoute('*', $model->lang['cat.route'], array(
	'controller'  => 'category' ,
));

$router->addRoute('*', $model->lang['cat.route'].'/:catname/:catid' , array(
	'controller'  => 'category' ,
	'action'      => 'view' ,
));

$router->addRoute('*', $model->lang['cat.route'].'/:catname/:catid/item/:itemname/:itemid', array(
	'controller'  => 'category' ,
	'action'      => 'viewitem' ,
));



// Browse - List results

$router->addRoute('*', 'browse/', array(
	'controller'  => 'browse' ,
	'action'      => 'view' ,
));

$router->addRoute('*', 'browse/:param1', array(
	'controller'  => 'browse' ,
	'action'      => 'view' ,
));

$router->addRoute('*', 'browse/:param1/:param2', array(
	'controller'  => 'browse' ,
	'action'      => 'view' ,
));

$router->addRoute('*', 'browse/:param1/:param2/:param3', array(
	'controller'  => 'browse' ,
	'action'      => 'view' ,
));

$router->addRoute('*', 'browse/:param1/:param2/:param3/:param4', array(
	'controller'  => 'browse' ,
	'action'      => 'view' ,
));



// Browse - Item views

$router->addRoute('*', 'browse/item/:itemname/:itemid', array(
	'controller'  => 'browse' ,
	'action'      => 'viewitem' ,
));

$router->addRoute('*', 'browse/:param1/item/:itemname/:itemid', array(
	'controller'  => 'browse' ,
	'action'      => 'viewitem' ,
));

$router->addRoute('*', 'browse/:param1/:param2/item/:itemname/:itemid', array(
	'controller'  => 'browse' ,
	'action'      => 'viewitem' ,
));

$router->addRoute('*', 'browse/:param1/:param2/:param3/item/:itemname/:itemid', array(
	'controller'  => 'browse' ,
	'action'      => 'viewitem' ,
));

$router->addRoute('*', 'browse/:param1/:param2/:param3/:param4/item/:itemname/:itemid', array(
	'controller'  => 'browse' ,
	'action'      => 'viewitem' ,
));



// ID

$router->addRoute('*', 'id/:action/:id/:name', array (
	'controller' => 'id' ,
));

$router->addRoute('*', 'id/:action/:id', array (
	'controller' => 'id' ,
));



// Manufacturer A-Z

$router->addRoute('*', 'a-z/' , array(
	'controller'  => 'atoz' ,
));

$router->addRoute('*', 'a-z/item/:itemname/:itemid' , array(
	'controller'  => 'atoz' ,
	'action'      => 'viewitem' ,
));



// Enquiry
$router->addRoute('*', 'enquiry/:itemid', array (
	'controller'  => 'enquiry' ,
	'action'      => 'index' ,
));



// OU Admin homepage

$router->addRoute('*', 'ouadmin', array (
	'controller'  => 'home' ,
	'action'      => 'index' ,
	'module'      => 'ouadmin' ,
));



// Admin homepage

$router->addRoute('*', 'admin', array (
	'controller'  => 'home' ,
	'action'      => 'index' ,
	'module'      => 'admin' ,
));



// Homepage

$router->addRoute('*', '/', array(
	'controller'  => 'home' ,
	'action'      => 'index' ,
));


