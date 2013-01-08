<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }
?>


<p>The installation wizard </p>

<div style="float: right; width: 40%; padding: 2em; border: 1px solid #ccc; border-radius: 10px;">
	<h2 style="margin-top: 0.2em;">Upgrading an existing installation</h2>

	<p>The upgrade wizard will apply any required database patches, and ensure your Kit-Catalogue upgrade is OK.</p>

	<div style="margin-top: 1em; text-align: center;"><a style="display: inline-block; padding: 1em; background-color: #fa5; border: 1px solid #ddd; font-weight: bold;" href="./upgrade/">Upgrade</a></div>
</div>

<div style="float: left; width: 40%; padding: 2em; border: 1px solid #ccc; border-radius: 10px;">
	<h2 style="margin-top: 0.2em;">Installing for the first time</h2>

	<p>This wizard will take you through several steps to make sure your Kit-Catalogue installation is working properly.</p>

	<div style="margin-top: 1em; text-align: center;"><a style="display: inline-block; padding: 1em; background-color: #9f9; border: 1px solid #ddd; font-weight: bold;" href="./?step=1">Install</a></div>
</div>

<p style="clear: both;">&nbsp;</p>


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


