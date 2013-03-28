<?php

require_once('./inc__install.php');




// Setup LDAP basics if extension not installed
// This stops errors appearing in the config files, etc
if (!defined('LDAP_OPT_PROTOCOL_VERSION')) {
	define('LDAP_OPT_PROTOCOL_VERSION', 17);
	define('LDAP_OPT_REFERRALS', 8);
}



$step = isset($_GET['step']) ? $_GET['step'] : 0 ;
$step = (int) $step;

$dir = isset($_GET['dir']) ? $_GET['dir'] : 'prev' ;
$dir = ('prev' == $dir) ? 'prev' : 'next' ;



$available_steps = array (
	'1_intro' ,
	'2_database' ,
	'3_finish' ,
);



if (isset($available_steps[$step])) {
	$page = $available_steps[$step];
} else {
	$step = 0;
	$page = $available_steps[0];
}



$url = "upgrader.php?step={$step}";



$no_next = false;



?><!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Kit-Catalogue : Upgrade</title>
	<link href="../css/style.css" media="all" rel="stylesheet" type="text/css" />
	<link href="../css/installer.css" media="all" rel="stylesheet" type="text/css" />
</head>
<body>



<div id="header">

	<div class="kclogo"><img src="../images/logo-kc.jpg" alt="Kit Catalogue" /></div>

</div>



<div id="main" class="grid_container">

	<div style="float: right; margin: 0.2em 0 0 0; background-color: #eee; border: 1px solid #999;">
		<div><a style="display: inline-block; padding: 1em;" href="<?php echo $url; ?>">Refresh</a></div>
	</div>

	<div style="margin: 0; padding: 0 0.5em;">
		<p><a href="index.php">&laquo; Back to the installation menu</a></p>
	</div>


	<h1>Upgrade Wizard</h1>


	<div style="margin: 2em;">
		<?php
		$filepath = realpath("./upgrade_steps/{$page}.php");
		if (!file_exists($filepath)) {
			?>
			<div class="feedback feedback_error">
				<p class="title">Unable to find wizard step '<?php out($page); ?>'.</p>
			</div>
			<?php
		} else {
			include($filepath);
		}
		?>
	</div>

	<div class="prevnext">
		<?php
		if ($step>0) {
			?>
			<div style="float: left;"><a href="./upgrader.php?step=<?php echo($step-1); ?>">&lt; Previous</a></div>
			<?php
		}
		if ( (!$no_next) && $step<count($available_steps)-1) {
			?>
			<div style="float: right;"><a href="./upgrader.php?step=<?php echo($step+1); ?>">Next &gt;</a></div>
			<?php
		}
		?>
	</div>

</div>

<div class="footer">&nbsp;</div>

</body>
</html>