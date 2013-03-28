<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }
?>


<h2>Introduction</h2>

<p>This wizard will take you through several steps to make sure your new Kit-Catalogue installation is working properly.</p>
<p>It's mainly a case of confirming that you have correctly entered the appropriate settings in /local/local_config.php, and that the system can communicate with the appropriate database and Active Directory/LDAP systems, etc.</p>


<h2>The different steps are:</h2>

<ol>
	<li>Introduction (this page).</li>
	<li>Check server setup.</li>
	<li>Check local configuration.</li>
	<li>Check application setup.</li>
	<li>Check database connectivity.</li>
	<li>Check authentication and LDAP (if required).</li>
	<li>Finish!</li>
</ol>



<?php include(INSTALLER_PATH . '/inc__installer_warning.php'); ?>


