<?php
include("../app/bootstrap.php");
<<<<<<< HEAD



if ( ((isset($_SERVER['HTTPS'])) && ('off' != $_SERVER['HTTPS'])) || ( 443 == $_SERVER['SERVER_PORT']) ) {
	$app_www = preg_replace('#^http:#', 'https:', $config['app.www']);
} else {
	$app_www = $config['app.www'];
}
=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
?>
// Kit-Catalogue Javascript Configuration



<<<<<<< HEAD
var APP_WWW = "<?php echo($app_www); ?>";
=======
var APP_WWW = "<?php echo($config['app.www']); ?>";
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd


$.scriptPath = APP_WWW + "/js/";