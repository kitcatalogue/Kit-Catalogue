<?php



// To enable plugins, comment out the following line..
return;



$plugins->add('signin.authenticate', function($username, $password) use ($model) {
	/* NOTE : Echo statements from the authentication process are supressed.
	 * If you want to output some debug information during development, the
	 * easiest way is to die().
	 */

	//die('My custom plugin authenticator was called!');


	// Returning false means the user was not authenticated
	// If there are other authentication plugins defined, the next in line will be called
	return false;


	// If the username and password are valid we need to return an array of user info..
	$user_info = array (
		'id'        => '123456' ,     // Staff/Student ID number
		'username'  => 'freduser' ,   // The username they signed in with
		'forename'  => 'Fred' ,
		'surname'   => 'Bloggs' ,
		'email'     => 'f.blogs@example.com' ,
	);

	return $user_info;
});



?>