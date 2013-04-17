<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }

?>



<h2>Finished</h2>

<p>Kit-Catalogue should now be installed and ready to run.</p>



<h2>What Next?</h2>
<ul>
	<li>Visit <a class="hilight" href="<?php echo $config['app.www']; ?>">your catalogue homepage</a> and try using the system.</li>
	<li>If you want to double-check your Kit-Catalogue system and its settings, you can go through the <a class="hilight" href="installer.php">installation wizard</a> again.</li>
	<li>If you need more information on how Kit-Catalogue operates, check out the <a class="hilight" href="http://kit-catalogue.lboro.ac.uk/project/software/docs/usermanual/">user manual</a>.</li>
</ul>



<?php include(INSTALLER_PATH . '/inc__installer_warning.php'); ?>


