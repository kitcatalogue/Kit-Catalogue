<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }


include(APP_PATH .'/bootstrap.php');
?>



<h2>Finished</h2>

<p>Kit-Catalogue should now be properly configured and setup to run.</p>

<p>You should now visit <a href="<?php echo $config['app.www']; ?>">your catalogue homepage</a> and try using the system.</p>

<p>If you need more information on how Kit-Catalogue operates, check out the <a href="http://kit-catalogue.lboro.ac.uk/project/software/docs/usermanual/">user manual</a>.</p>

<br />
<div class="warn">
	<p class="title">Remember to disable to the installer!</p>
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