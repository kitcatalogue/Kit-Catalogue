<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }
?>


<h2>Introduction</h2>

<p>This wizard will take you through the process of brining your Kit-Catalogue installation up-to-date.</p>

<p>Usually, this means applying any required patches to the database, and performing some clean up operations.
	You may also be reminded of any important details from the release notes.</p>

<p>Before starting, please ensure sure you have unzipped all the code files from the latest release and overwritten your existing catalogue software with them.</p>



<?php include(INSTALLER_PATH . '/inc__installer_warning.php'); ?>


