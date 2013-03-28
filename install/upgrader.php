<?php

require_once('./inc__install.php');



<<<<<<< HEAD

=======
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
// Setup LDAP basics if extension not installed
// This stops errors appearing in the config files, etc
if (!defined('LDAP_OPT_PROTOCOL_VERSION')) {
	define('LDAP_OPT_PROTOCOL_VERSION', 17);
	define('LDAP_OPT_REFERRALS', 8);
}



$step = isset($_GET['step']) ? $_GET['step'] : 0 ;
$step = (int) $step;

<<<<<<< HEAD
$dir = isset($_GET['dir']) ? $_GET['dir'] : 'prev' ;
$dir = ('prev' == $dir) ? 'prev' : 'next' ;



$available_steps = array (
	'1_intro' ,
	'2_database' ,
	'3_finish' ,
=======


$available_steps = array (
	'intro' ,
	'database' ,
	'finish' ,
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
);



if (isset($available_steps[$step])) {
	$page = $available_steps[$step];
} else {
	$step = 0;
<<<<<<< HEAD
	$page = $available_steps[0];
=======
	$page = 'intro';
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
}



$url = "upgrader.php?step={$step}";



$no_next = false;



?><!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Kit-Catalogue : Upgrade</title>
	<link href="../css/style.css" media="all" rel="stylesheet" type="text/css" />
<<<<<<< HEAD
	<link href="../css/installer.css" media="all" rel="stylesheet" type="text/css" />
=======
	<style type="text/css">

	body { padding: 1.5em 1.5em 2em 1.5em; background-color: #fff; font-size: 14px; }

	h2, h3, h4, h5, h6 { margin-left: -1em; color: #000; }
	h2 { margin-top: 2em; }

	.good { margin: 0.5em; padding: 0.4em; background-color: #cfc; border: 1px solid #090; border-radius: 10px; }
	.bad { margin: 0.5em; padding: 0.4em; background-color: #fcc; border: 1px solid #900; border-radius: 10px; }
	.warn { margin: 0.5em; padding: 0.4em; background-color: #ffc; border: 1px solid #990; border-radius: 10px; }

	.title { margin: 0; }

	.note { font-size: 0.875em; color: #666; }

	div.prevnext { margin: 1.5em 2em 0 2em; padding-top: 0.7em; border-top: 1px dotted #ccc; font-size: 1.125em; font-weight: bold; }


	table.valigntop td { vertical-align: top; }

	</style>
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
</head>
<body>



<div id="header">

<<<<<<< HEAD
	<div class="kclogo"><img src="../images/logo-kc.jpg" alt="Kit Catalogue" /></div>
=======
	<div class="kclogo"><img src="../images/system/kitcatalogue_logo.gif" alt="Kit Catalogue" /></div>
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

</div>



<div id="main" class="grid_container">

<<<<<<< HEAD
	<div style="float: right; margin: 0.2em 0 0 0; background-color: #eee; border: 1px solid #999;">
		<div><a style="display: inline-block; padding: 1em;" href="<?php echo $url; ?>">Refresh</a></div>
	</div>

	<div style="margin: 0; padding: 0 0.5em;">
		<p><a href="index.php">&laquo; Back to the installation menu</a></p>
	</div>


	<h1>Upgrade Wizard</h1>
=======
	<div style="margin: 0; padding: 0.3em 0.5em;">
		<p><a href="index.php">&laquo; Back to the installation menu</a></p>
	</div>


	<div style="float: right; margin: 0; background-color: #eee; border: 1px solid #999;">
		<div><a style="display: inline-block; padding: 1em;" href="<?php echo $url; ?>">Refresh</a></div>
	</div>


	<h1 style="padding-top: 1em;">Upgrade Wizard &nbsp; (<?php out($page); ?>)</h1>
	<hr style="clear: both;" />
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd


	<div style="margin: 2em;">
		<?php
<<<<<<< HEAD
		$filepath = realpath("./upgrade_steps/{$page}.php");
=======
		$filepath = realpath("./upgrade/steps/{$page}.php");
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
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

<<<<<<< HEAD
<div class="footer">&nbsp;</div>

</body>
</html>
=======
<?php
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
