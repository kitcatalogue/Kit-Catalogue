<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Upgrade wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }
?>



<h2>Finished</h2>

<p>The Kit-Catalogue system should now be fully upgraded.</p>



<h2>What Next?</h2>
<ul>
	<li>Visit <a class="hilight" href="<?php echo $config['app.www']; ?>">your catalogue homepage</a> and try using the system.</li>
	<li>If you want to check your Kit-Catalogue system and its settings, go through the steps in the <a class="hilight" href="installer.php">installation wizard</a>.</li>
	<li>If you need more information on how Kit-Catalogue operates, check out the <a class="hilight" href="http://kit-catalogue.lboro.ac.uk/project/software/docs/usermanual/">user manual</a>.</li>
</ul>



<?php include(INSTALLER_PATH . '/inc__installer_warning.php'); ?>


