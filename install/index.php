<?php

require_once('./inc__install.php');

?><!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Kit-Catalogue : Installation</title>
	<link href="../css/style.css" media="all" rel="stylesheet" type="text/css" />
	<link href="../css/installer.css" media="all" rel="stylesheet" type="text/css" />
</head>
<body>

<div id="header">

	<div class="kclogo"><img src="../images/logo-kc.jpg" alt="Kit Catalogue" /></div>

</div>



<div id="main" class="grid_container">

	<h1 style="padding-top: 1em;">Installation Options</h1>

	<p>Choose from the options below to continue your installation.</p>
	<p>Note: You can use the <a href="installer.php">Install</a> option for checking the configuration of your existing Kit-Catalogue software, as well as a brand new installation.</p>

	<div class="cf">
		<div style="float: right; width: 40%; padding: 2em; background-color: #fff6dd; border: 1px solid #ccc; border-radius: 10px;">
			<h2 style="margin-top: 0.2em; text-align: center;">Upgrading an existing installation</h2>

			<p>This wizard will upgrade an existing Kit-Catalogue database and apply any required changes.</p>

			<div style="margin-top: 1em; text-align: center;"><a style="display: inline-block; padding: 1em; background-color: #fa5; border: 1px solid #ddd; font-weight: bold;" href="upgrader.php">Upgrade</a></div>
		</div>

		<div style="float: left; width: 40%; padding: 2em; background-color: #efe; border: 1px solid #ccc; border-radius: 10px;">
			<h2 style="margin-top: 0.2em; text-align: center;">Installing for the first time</h2>

			<p>This wizard will take you through several steps to make sure your Kit-Catalogue installation is working properly.</p>

			<div style="margin-top: 1em; text-align: center;"><a style="display: inline-block; padding: 1em; background-color: #9f9; border: 1px solid #ddd; font-weight: bold;" href="installer.php">Install</a></div>
		</div>
	</div>

	<?php include(INSTALLER_PATH . '/inc__installer_warning.php'); ?>

</div>
</body>
</html>