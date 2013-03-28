<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }


function path_check($path) {
	if (file_exists($path)) {
		?>
		<div class="good">
			<p>Path&nbsp;OK.</p>
		</div>
		<?php
	} else {
		?>
		<div class="bad">
			<p>Not&nbsp;found.</p>
		</div>
		<?php
	}
}// /function
?>



<h2>Application setup</h2>

<p>This section will run some basic checks on the application installation, to check that Kit-Catalogue can find
	the components it requires to function.</p>

<p>If you have simply downloaded, unzipped and copied the files into your website as-is, then these tests
	will probably all pass easily.  However, if you have moved parts of the catalogue system around in order to
	increase security, or fit in with your preferred folder structure, it's important to check Kit-Catalogue is
	still configured properly and can find them.</p>



<h2>Check application bootstrap</h2>

<p>Kit-Catalogue's bootstrap (<em>app/bootstrap.php</em>) sets up the basic application settings, and loads both the
	default and local configuration files. This test will try and load the bootstrap file and run it.</p>

<?php
$path = APP_PATH .'/bootstrap.php';
if (file_exists($path)) {
	try {
		include_once($path);
		?>
		<div class="good">
			<p class="title">OK - Bootstrap loaded successfully.</p>
		</div>
		<?php
	} catch (\Exception $e) {
		?>
		<div class="bad">
			<p class="title">Error - There was an error while loading the bootstrap.</p>
			<p>The file was found OK: <em><?php out($path); ?></em></p>
		</div>
		<p>The error returned was: <?php out($e->getMessage()); ?></p>
		<?php
		$no_next = true;
		return;
	}
} else {
	?>
	<div class="bad">
		<p class="title">Error - Unable to find bootstrap: <em><?php out($path); ?></em>.</p>
	</div>
	<p>Without the bootstrap file, Kit-Catalogue will not be able to run at all.</p>
	<?php
	$no_next = true;
	return;
}
?>



<h2>Check configured application paths</h2>

<p>The following paths are critical to Kit-Catalogue's operation.  By default, many of these are defined automatically in
	<em>app/config.php</em>, and derive from the location defined in <em>$config['app.root']</em>.</p>
<p>In your local configuration, you can override <em>$config['app.root']</em> to point to your actual install folder, or override individual paths using the
	appropriate config option.</p>

<p><strong>If one or more paths does not exist you should create them, or change your local configuration option to match the actual location.</strong></p>

<table class="grid valigntop">
<tr>
	<th>option</th>
	<th>currently set to</th>
	<th>path found</th>
</tr>
<tr>
	<td class="name">app.root</td>
	<td>
		<?php out($config['app.root']); ?>
		<p class="note">The application root from which Kit-Catalogue serves web pages.</p>
	</td>
	<td>
		<?php
		path_check($config['app.root']);
		?>
	</td>
</tr>
<tr>
	<td class="name">app.include_root</td>
	<td>
		<?php out($config['app.include_root']); ?>
		<p class="note">The location of the MVC components. Default value: <em>&lt;app.root&gt;/app</em></p>
	</td>
	<td>
		<?php
		path_check($config['app.include_root']);
		?>
	</td>
</tr>
<tr>
	<td class="name">app.local_root</td>
	<td>
		<?php out($config['app.local_root']); ?>
		<p class="note">The location of the local configuration folder. Default value: <em>&lt;app.root&gt;/local</em></p>
	</td>
	<td>
		<?php
		path_check($config['app.local_root']);
		?>
	</td>
</tr>
<tr>
	<td class="name">app.writable_root</td>
	<td>
		<?php out($config['app.writable_root']); ?>
		<p class="note">A location to which PHP has been given write access, and where cache files, uploads and temporary processing files will be kept. Default value: <em>&lt;app.root&gt;/writable</em></p>
		<p class="note">We will check if PHP has write permissions for this folder below.</p>
	</td>
	<td>
		<?php
		path_check($config['app.writable_root']);
		?>
	</td>
</tr>
<tr>
	<td class="name">app.upload_root</td>
	<td>
		<?php out($config['app.upload_root']); ?>
		<p class="note">The location under which the system will store uploaded images and files for items in the catalogue. Default value: <em>&lt;app.writable_root&gt;/uploads</em></p>
		<p class="note">This folder will be created automatically when required.</p>
	</td>
	<td class="center">N/A</td>
</tr>
</table>



<h2>Check writable folder</h2>

<p>Kit-Catalogue requires PHP to have write permissions to the <em>writable</em> folder, so it can store images and files associated with items of equipment, bulk upload and import item info from CSV files, and as a location to store cached and process-related data.</p>
<p>Above, we did a quick check to see if the <em>app.writable_root</em> folder exists.  Now we'll check the access permissions are set correct for the folder itself.

<?php
if (!file_exists($config['app.writable_root'])) {
	?>
	<div class="bad">
		<p class="title">Oh! It looks like you don't have a <em>writable</em> folder.</p>
	</div>
	<p>Once you've created the <em>writable</em> folder, <a href="<?php echo $url; ?>">refresh this page</a> to continue with the wizard.</p>
	<?php
	$no_next = true;
	return;
} else {
	?>
	<div class="good">
		<p class="title">OK - It looks like you already have a <em>writable</em> folder.</p>
		<p>The path is : <?php echo $config['app.writable_root']; ?></p>
	</div>
	<p>Now let's check if PHP has write permissions for that folder.</p>
	<?php
	clearstatcache();
	if (!is_writable($config['app.writable_root'])) {
		?>
		<div class="bad">
			<p class="title">Error - It looks like PHP does not have write permissions.</p>
		</div>
		<p>To enable write permissions on a *nix server, use something like this on the command line:
			<br /><em>chmod o+w path/to/site/writable</em></p>
		<p>Note, depending on how your site is setup, you may have to use <em>u+w</em> or <em>g+w</em> instead.</p>
		<?php
		$no_next = true;
		return;
	} else {
		?>
		<div class="good">
			<p class="title">OK - It looks like PHP has write permissions for the folder.</p>
		</div>
		<?php
	}
}
?>



