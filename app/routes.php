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



<<<<<<< HEAD
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

=======
// Support Routes

$router->addRoute('*', 'support/:itemname/:id/file/:filename', array(
	'controller'  => 'item' ,
	'action'      => 'downloadfile' ,
));


>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
$router->addRoute('*', 'support/help', array(
	'module'      => 'support' ,
	'controller'  => 'help' ,
	'action'      => 'index' ,
));

<<<<<<< HEAD
=======

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
$router->addRoute('*', 'support', array(
	'module'      => 'support' ,
	'controller'  => 'help' ,
	'action'      => 'index' ,
));



<<<<<<< HEAD
// Search


$router->addRoute('*', 'search/item/:itemname/:itemid' , array(
	'controller'  => 'search' ,
	'action'      => 'viewitem' ,
));

$router->addRoute('*', 'search/' , array(
	'controller'  => 'search' ,
	'action'      => 'results' ,
=======
// Search Routes

$router->addRoute('*', 'search/' , array(
	'controller'  => 'item' ,
	'action'      => 'search' ,
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
));



<<<<<<< HEAD
// My Profile

$router->addRoute('*', 'myprofile/items/:itemname/:itemid', array(
	'controller'  => 'myprofile' ,
	'action'      => 'viewitem' ,
=======
// Custom field Routes

$router->addRoute('*', 'custom/' , array(
	'controller'  => 'item' ,
	'action'      => 'custom' ,
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
));



// Tags

<<<<<<< HEAD
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
=======
$router->addRoute('*', 'tags/:tag', array(
	'controller'  => 'item' ,
	'action'      => 'tags' ,
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
));



<<<<<<< HEAD

// Resource / File
=======
// Resource / File Route
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

$router->addRoute('*', 'resource/:id', array(
	'controller'  => 'resource' ,
	'action'      => 'view' ,
));



<<<<<<< HEAD
// Item
=======
// Item Routes
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

$router->addRoute('*', 'item/:itemname/:itemid', array(
	'controller'  => 'item' ,
	'action'      => 'view' ,
));

<<<<<<< HEAD
=======

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
$router->addRoute('*', 'item/:itemname/:itemid/file/:filename', array(
	'controller'  => 'item' ,
	'action'      => 'downloadfile' ,
));

<<<<<<< HEAD
$router->addRoute('*', 'item/:itemname/:itemid/image/:image', array(
	'controller'  => 'item' ,
	'action'      => 'downloadimage' ,
));



// Department
=======


// Department Routes
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd


$router->addRoute('*', $model->lang['dept.route'] , array(
	'controller'  => 'department' ,
));

<<<<<<< HEAD
=======

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
$router->addRoute('*', $model->lang['dept.route'].'/:deptname/:deptid' , array(
	'controller'  => 'department' ,
	'action'      => 'view' ,
));

<<<<<<< HEAD
=======

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
$router->addRoute('*', $model->lang['dept.route'].'/:deptname/:deptid/category/:catname/:catid', array(
	'controller'  => 'department' ,
	'action'      => 'view' ,
));

<<<<<<< HEAD
=======

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
$router->addRoute('*', $model->lang['dept.route'].'/:deptname/:deptid/item/:itemname/:itemid', array(
	'controller'  => 'department' ,
	'action'      => 'viewitem' ,
));



<<<<<<< HEAD
// Custom Field


$router->addRoute('*', 'customfield/:fieldname/:fieldid/:fieldvalue/item/:itemname/:itemid', array(
	'controller'  => 'customfield' ,
	'action'      => 'viewitem' ,
));
=======
// Custom Field Routes

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

$router->addRoute('*', 'customfield/:fieldname/:fieldid/:fieldvalue', array(
	'controller'  => 'customfield' ,
	'action'      => 'viewvalue' ,
));

<<<<<<< HEAD
=======

>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
$router->addRoute('*', 'customfield/:fieldname/:fieldid', array(
	'controller'  => 'customfield' ,
	'action'      => 'listvalues' ,
));



<<<<<<< HEAD
// Staff Contact
=======
// Staff Contact Routes
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

$router->addRoute('*', 'contact/:contactid' , array(
	'controller'  => 'contact' ,
	'action'      => 'view' ,
));



<<<<<<< HEAD
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
=======
// Category Routes


$router->addRoute('*', 'category/:catname/:catid' , array(
	'controller'  => 'category' ,
	'action'      => 'view' ,
));


$router->addRoute('*', 'category/:catname/:catid/item/:itemname/:itemid', array(
	'controller'  => 'category' ,
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
	'action'      => 'viewitem' ,
));



<<<<<<< HEAD
// ID

$router->addRoute('*', 'id/:action/:id/:name', array (
	'controller' => 'id' ,
));
=======
// ID Route
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

$router->addRoute('*', 'id/:action/:id', array (
	'controller' => 'id' ,
));



<<<<<<< HEAD
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
=======
// Filter Routes


$router->addRoute('*', 'filter/' , array(
	'controller'  => 'filter' ,
	'action'      => 'view' ,
));


$router->addRoute('*', 'filter/JSON' , array(
	'controller'  => 'filter' ,
	'action'      => 'JSON' ,
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
));



<<<<<<< HEAD
// OU Admin homepage

$router->addRoute('*', 'ouadmin', array (
	'controller'  => 'home' ,
	'action'      => 'index' ,
	'module'      => 'ouadmin' ,
));

=======
// Calibration Routes


$router->addRoute('*', 'calibration/JSON' , array(
	'controller'  => 'calibration' ,
	'action'      => 'JSON' ,
));
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd


// Admin homepage

$router->addRoute('*', 'admin', array (
	'controller'  => 'home' ,
	'action'      => 'index' ,
	'module'      => 'admin' ,
));


<<<<<<< HEAD

// Homepage
=======
// Homepage Route
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

$router->addRoute('*', '/', array(
	'controller'  => 'home' ,
	'action'      => 'index' ,
));


