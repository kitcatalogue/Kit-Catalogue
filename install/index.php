<?php

require_once('./inc__install.php');

?><!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Kit-Catalogue : Installation</title>
	<link href="../css/style.css" media="all" rel="stylesheet" type="text/css" />
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
</head>
<body>

<div id="header">

	<div class="kclogo"><img src="../images/system/kitcatalogue_logo.gif" alt="Kit Catalogue" /></div>

</div>



<div id="main" class="grid_container">

	<h1 style="padding-top: 1em;">Installation Options</h1>
	<hr style="clear: both;" />

	<p>Choose from the options below to continue your installation.</p>
	<p>Note: You can use the <a href="installer.php">Install</a> option for checking the configuration of your existing Kit-Catalogue software, as well as a brand new installation.</p>

	<div style="float: right; width: 40%; padding: 2em; background-color: #fff6dd; border: 1px solid #ccc; border-radius: 10px;">
		<h2 style="margin-top: 0.2em;">Upgrading an existing installation</h2>

		<p>This wizard will upgrade an existing Kit-Catalogue database and apply any required changes.</p>

		<div style="margin-top: 1em; text-align: center;"><a style="display: inline-block; padding: 1em; background-color: #fa5; border: 1px solid #ddd; font-weight: bold;" href="upgrader.php">Upgrade</a></div>
	</div>

	<div style="float: left; width: 40%; padding: 2em; background-color: #efe; border: 1px solid #ccc; border-radius: 10px;">
		<h2 style="margin-top: 0.2em;">Installing for the first time</h2>

		<p>This wizard will take you through several steps to make sure your Kit-Catalogue installation is working properly.</p>

		<div style="margin-top: 1em; text-align: center;"><a style="display: inline-block; padding: 1em; background-color: #9f9; border: 1px solid #ddd; font-weight: bold;" href="installer.php">Install</a></div>
	</div>

	<p style="clear: both;">&nbsp;<br /><br /></p>


	<div class="warn">
		<p class="title">Please note!</p>
		<p>For security reasons, once you have finished installing/upgrading Kit-Catalogue and made sure
			it is all working properly, you should edit your local config and disable the
			installer using this setting.</p>

		<p><em>$config['installer.enabled'] = false;</em></p>

		<p><strong>If you leave the installer available, malicious users can use it to
			damage/delete your catalogue!</strong></p>

		<p>For added security, you could delete the <em>/install/</em> folder to prevent
			unauthorised access, but if you later upgrade or reinstall Kit-Catalogue, you
			will have a new version of the <em>/install/</em>  folder created, so disabling it in
			your config is a must.</p>
	</div>

</div>
</body>
</html>