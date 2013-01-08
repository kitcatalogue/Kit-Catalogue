<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }
?>


<p>This wizard will take you through several steps to make sure your new Kit-Catalogue installation is working properly.</p>
<p>It's mainly a case of confirming that you have correctly entered the appropriate settings in /local/local_config.php, and that the system can communicate with the appropriate database and Active Directory/LDAP systems, etc.</p>


<p style="clear: both;">&nbsp;</p>


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

<br />
<div class="warn">
	<p class="title">Please note!</p>
	<p>For security reasons, once you have installed Kit-Catalogue and made sure
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