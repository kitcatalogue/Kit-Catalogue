<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }
?>


<p>
	Kit-Catalogue runs on a MySQL database.  This section will check to ensure the database connection works correctly, and prompt you to install any database upgrades that are required.
</p>



<?php
$path = APP_PATH .'/bootstrap.php';
if (file_exists($path)) {
	try {
		include_once($path);
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
	<p>You should use the <a href="installer.php">installation wizard</a> to test that the paths defined in your configuration are correct.</p>
	<?php
	$no_next = true;
	return;
}
?>



<h2>Checking connection to database server</h2>

<p>Using the configuration settings in your configuration, we will check that Kit-Catalogue can connect to your database server.</p>
<?php
$db = Ecl::factory('Ecl_Db_Mysql', array (
	'host'      => $config['db.host'] ,
	'port'      => $config['db.port'] ,
	'username'  => $config['db.username'] ,
	'password'  => $config['db.password'] ,
	'database'  => $config['db.database'] ,
));

$db->setDebug(true);
$db->setUseExceptions(true);

$connected = false;
$msg = 'No error message returned.';

try {
	$connected = $db->connect();
} catch (\Exception $e) {
	$msg = $e->getMessage();
}

if (!$connected) {
	?>
	<div class="bad">
		<p class="title">Error - Failed to connect to the database server.</p>
		<p><?php out($msg); ?></p>
		<p>You should use the <a href="installer.php">installation wizard</a> to test that your database connection is setup correctly.</p>
	</div>

	<p>These are the currently defined database settings.</p>

	<table class="grid valigntop">
	<tr>
		<th>option</th>
		<th>currently set to</th>
	</tr>
	<tr>
		<td class="name">db.host</td>
		<td><?php out($config['db.host']); ?></td>
	</tr>
	<tr>
		<td class="name">db.port</td>
		<td><?php out($config['db.port']); ?></td>
	</tr>
	<tr>
		<td class="name">db.username</td>
		<td><?php out($config['db.username']); ?></td>
	</tr>
	<tr>
		<td class="name">db.password</td>
		<td><?php out(str_pad('', strlen($config['db.password']), '*')); ?></td>
	</tr>
	<tr>
		<td class="name">db.database</td>
		<td>
			<?php out($config['db.database']); ?>
			<p class="note">The database/schema to use.</p>
		</td>
	</tr>
	</table>

	<?php
	$no_next = true;
	return;
} else {
	?>
	<div class="good">
		<p class="title">OK - Database connection confirmed.</p>
	</div>
	<?php
}
?>



<h2>Check database updates</h2>
<p>By checking your database's current version against the available upates, we can see if you need an upgrade.</p>


<?php
// Check database version

$sysinfo = array();

$db->query("SHOW TABLES LIKE 'system_info'");
$tables = (array) $db->getColumn();

if (!empty($tables)) {
	try {
		$db->query("SELECT name, value FROM system_info");
		$sysinfo = $db->getResultAssoc('name', 'value');
	} catch (\Exception $e) {
		?>
		<div class="bad">
			<p class="title">Error - There was an error while reading the <em>system_info</em> database table.</p>
			<p>The upgrader will assume the database version is that of the original beta release, v0.9.0.</p>
		</div>
		<p>The error returned was: <?php out($e->getMessage()); ?></p>
		<?php
	}
}


// If no sysinfo found, use the default
if (empty($sysinfo)) {
	$sysinfo = array (
		'database_updated'  => '2011-12-09 00:00:00' ,
		'database_version'  => '0.9.0' ,
	);
}


// If the sysinfo is missing the database version for some reason, use the default
if (!isset($sysinfo['database_version'])) {
	?>
	<div class="warn">
		<p class="title">Warning - It looks like the <em>database version</em> information does not exist in the <em>system_info</em> table.</p>
		<p>The upgrader will assume the database version is that of the original beta release, v0.9.0.</p>
	</div>
	<?php
	$sysinfo['database_version'] = '0.9.0';
	$no_next = true;
}



// Check for patch files

$patch_files = array();

$current_file_template = "patch_db_{$sysinfo['database_version']}.sql";

$path = './upgrade/database_patches';

// Check for patch files greater than our current version
$files = Ecl_Helper_Filesystem::getFiles($path);
if ($files) {
	foreach($files as $filename) {
		if (preg_match('%^patch_db_[0-9\.]*\.sql$%', $filename)) {
			if ($filename > $current_file_template) {
				$patch_files[] = $filename;
			}
		}
	}
}


if (empty($patch_files)) {
	?>
	<div class="good">
		<p class="title">OK - It looks like your database is up-to-date.</p>
		<p>Your database is <em>version <?php out($sysinfo['database_version']); ?></em>.</p>
	</div>
	<?php
	if ($config['app.version']>$sysinfo['database_version']) {
		?>
		<p>Your Kit-Catalogue software is <em>version <?php out($config['app.version']); ?></em>, which is newer than your databaes version, but that's nothing to worry about.  It simply means that the database structure has not changed in our latest software updates.</p>
		<?php
	}
	return;
} else {
	$count = count($patch_files);
	$msg = (1 == $count) ? 'There is 1 patch to apply.' : "There are $count patches to apply" ;
	?>
	<div class="warn">
		<p class="title">OK - It looks like your database is <em>version <?php out($sysinfo['database_version']); ?></em> and needs upgrading.</p>
		<p><?php out($msg)?></p>
	</div>
	<?php
}
?>



<h2>Upgrade database</h2>
<p>Press the button below to upgrade your database with the necessary patches.</p>

<form action="<?php echo $url; ?>#upgradedb" method="post">
<div style="margin-top: 1em; text-align: center;">
	<p><strong>We recommend you take a back up of your data before upgrading!</strong></p>
	<table style="margin: 0 auto;">
	<tr>
		<td><input type="checkbox" name="confirmupgradedb" id="confirmupgradedb" value="1" /></td>
		<td><label for="confirmupgradedb">Tick to confirm you want to upgrade your database.</label></td>
	</tr>
	</table>
	<input type="submit" name="submitupgradedb" value="Upgrade Database" />
</div>
</form>



<div id="upgradedb">
<?php
if ( (isset($_POST['submitupgradedb'])) && (isset($_POST['confirmupgradedb'])) ){

	if (!file_exists($path)) {
		?>
		<div class="bad">
			<p class="title">Error - Unable to find database table install script: <em><?php out($path); ?></em>.</p>
		</div>
		<?php
	} else {
		$res = false;
		$msg = '';
		try {
			foreach($patch_files as $filename) {
				$res = $db->executeSqlDump(file_get_contents("{$path}/{$filename}"), false);
			}
		} catch (\Exception $e) {
			$msg = $e->getMessage();
		}

		if ($res) {
			?>
			<div class="good">
				<p class="title">OK - The database upgrade is complete.</p>
			</div>
			<?php
		} else {
			?>
			<div class="bad">
				<p class="title">Error - There was an error while installing the database.</p>
			</div>
			<p>The error returned was: <?php out($msg); ?></p>
			<?php
			$no_next = true;
			return;
		}

	}

}
?>
</div>
