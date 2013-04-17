<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }


?>


<h2>Apache setup</h2>

<p>This section will run some basic checks on the Apache installation, to see if it's working properly.</p>


<h2>IIS setup</h2>
<p>We have not yet produced detailed instructions for setting up Kit-Catalogue on Windows IIS.


<h2>Check Apache mod_rewrite</h2>

<p>Kit-Catalogue requires that Apache's mod_rewrite be enabled so it can process all
incoming requests correctly, and provide 'friendly' URLs.</p>

<?php
ob_start();
phpinfo(INFO_MODULES);
$modules_text = ob_get_clean();

if (false !== strpos($modules_text, 'mod_rewrite')) {
	?>
	<div class="good">
		<p class="title">OK - Apache's mod_rewrite appears to be enabled.</p>
	</div>
	<?php
} else {
	?>
	<div class="bad">
		<p class="title">Error - mod_rewrite appears to be disabled.</p>
	</div>
	<?php
}
?>

<p>If you need to enable mod_rewrite,  edit your system's <em>httpd.conf</em> file and make the following changes:</p>
<ul>
	<li>Make sure the AllowOverride option is set to a minimum of:
		<br /><em>AllowOverride FileInfo Options</em>
		<br />
		<br />Using <em>AllowOverride All</em> should definitely work, but may enable settings you do not wish to use.</li>
	<li>Ensure that mod_rewrite is enabled by uncommenting this line:
		<br /><em>LoadModule rewrite_module modules/mod_rewrite.so</em></li>
</ul>


