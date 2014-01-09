<?php


ini_set('display_errors', '1');
error_reporting(E_ALL);


// --------------------------------------------------------------------------------
// Begin bootstrap


include('./app/bootstrap.php');


?>


<a href="https://idp.lboro.ac.uk/simplesaml/module.php/core/loginuserpass.php?AuthState=_900b5f196716a37bff776a726999e1830aa247320d%3Ahttps%3A%2F%2Fidp.lboro.ac.uk%2Fsimplesaml%2Fsaml2%2Fidp%2FSSOService.php%3Fspentityid%3Dhttps%253A%252F%252Fkc-development.lboro.ac.uk">SSO test</a>


<?php
Ecl::dump($_SERVER);


