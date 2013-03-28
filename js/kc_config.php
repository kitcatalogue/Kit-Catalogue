<?php
include("../app/bootstrap.php");



if ( ((isset($_SERVER['HTTPS'])) && ('off' != $_SERVER['HTTPS'])) || ( 443 == $_SERVER['SERVER_PORT']) ) {
	$app_www = preg_replace('#^http:#', 'https:', $config['app.www']);
} else {
	$app_www = $config['app.www'];
}
?>
// Kit-Catalogue Javascript Configuration



var APP_WWW = "<?php echo($app_www); ?>";


$.scriptPath = APP_WWW + "/js/";