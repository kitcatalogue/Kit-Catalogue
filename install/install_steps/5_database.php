<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }
?>


<p>
	Kit-Catalogue runs on a MySQL database.  This section will test whether the connections settings in your local
	configuration file are correct and that Kit-Catalogue can connect to the database.
</p>

<p>
	Once the database connectivity has been confirmed, you can also run the database install script that will create all the
	tables required for the catalogue's operation.
</p>



<h2>Check application bootstrap</h2>

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



<h2>Checking database settings</h2>

<p>These are the currently defined database settings.
	<br />You should ensure you are keeping these settings in your local configuration file.</p>

<table class="grid valigntop">
<tr>
	<th>option</th>
	<th>currently set to</th>
</tr>
<tr>
	<td class="name">db.use_mysqli</td>
	<td><?php out(($config['db.use_mysqli'] ? 'true' : 'false' )); ?></td>
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


<h2>Checking PHP database extensions</h2>

<p>Kit-Catalogue now uses PHP's MySQLi extension by default, though you can change to the older MySQL extension if you wish.
	The following table shows which extensions are available on your server. You can control which extension your catalogue
	will use by changing the <em>db.use_mysqli</em> configuration setting.</p>

<table class="grid valigntop">
<tr>
	<th>extension</th>
	<th>available</th>
</tr>
<tr>
	<td class="name">Mysqli (default)</td>
	<td><?php
		$is_mysqli = (function_exists('mysqli_connect'));
		echo ($is_mysqli ? 'Yes' : 'No');
		?>
	</td>
</tr>
<tr>
	<td class="name">Mysql (deprecated)</td>
	<td><?php
		$is_mysql = (function_exists('mysql_connect'));
		echo ($is_mysql ? 'Yes' : 'No');
		?>
	</td>
</tr>
</table>

<br />

<?php
if ($config['db.use_mysqli']) {
	if ($is_mysqli) {
		?>
		<div class="good">
			<p class="title">You have opted to use the MySQLi extension.</p>
			<p>This is the recommended PHP extension to use for database access.</p>
		</div>
		<?php
	} else {
		?>
		<div class="bad">
			<p class="title">Warning - You have opted to use the MySQLi extension, but it does not appear to be available.</p>
		</div>
		<?php
	}
} else {
	if ($is_mysql) {
		?>
		<div class="warn">
			<p class="title">You have opted to use the old MySQL extension.</p>
			<p>This extension should work fine, but it is being deprecated and removed from PHP in a future upgrade.</p>
			<p>For more information, visit: <a href="http://www.php.net/manual/en/mysqlinfo.api.choosing.php">http://www.php.net/manual/en/mysqlinfo.api.choosing.php</a></p>
			<p>If possible, we recommend you switch to using MySQLi.</p>
		</div>
		<?php
	} else {
		?>
		<div class="bad">
			<p class="title">Warning - You have opted to use the old MySQL extension, but it does not appear to be available.</p>
			<p>This extension is being deprecated and removed from PHP in a future upgrade.</p>
			<p>For more information, visit: <a href="http://www.php.net/manual/en/mysqlinfo.api.choosing.php">http://www.php.net/manual/en/mysqlinfo.api.choosing.php</a></p>
			<p>If possible, we recommend you switch to using MySQLi.</p>
		</div>
		<?php
	}
}
?>

<h2>Checking connection to database server</h2>

<p>Using the configuration settings above, we will check that Kit-Catalogue can connect to your database server.</p>
<?php

$db_class = ($config['db.use_mysqli']) ? 'Ecl_Db_Mysql' : 'Ecl_Db_Legacy_Mysql' ;

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
	</div>
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




<h2>Check database installation</h2>
<p>This is a quick check to see if it looks like the Kit-Catalogue database tables have already been setup.</p>



<div id="installdb">
<?php
$installed_ok = false;
if ( (isset($_POST['submitinstalldb'])) && (isset($_POST['confirminstalldb'])) ){

	$path = "./install_steps/install_db.sql";
	if (!file_exists($path)) {
		?>
		<div class="bad">
			<p class="title">Error - Unable to find database table install script: <em><?php out($path); ?></em>.</p>
		</div>
		<?php
	} else {
		$res = false;
		try {
			$res = $db->executeSqlDump(file_get_contents($path));
		} catch (\Exception $e) {
			$msg = $e->getMessage();
		}

		if ($res) {
			$installed_ok = true;
			?>
			<div class="good">
				<p class="title">OK - The database table installation is complete.</p>
			</div>
			<?php
		} else {
			?>
			<div class="bad">
				<p class="title">Error - There was an error while installing the database.</p>
			</div>
			<p>The error returned was: <?php out($e->getMessage()); ?></p>
			<?php
			$no_next = true;
			return;
		}

	}

}
?>
</div>


<?php
$db->query('SHOW TABLES');
$tables = (array) $db->getColumn();

if ( (count($tables)>0) && (in_array('category', $tables)) && (in_array('item', $tables)) && (in_array('system_authorisation', $tables)) ) {
	$command = 'reinstall';
	if (!$installed_ok) {
		?>
		<div class="good">
			<p class="title">OK - It looks like the database tables have already been installed.</p>
		</div>
		<?php
	}
} else {
	$command = 'install';
	$no_next = true;
	?>
	<div class="bad">
		<p class="title">It looks like you need to install the Kit-Catalogue database tables.</p>
	</div>
	<?php
}
?>

<h2><?php out(ucwords($command)); ?> database tables</h2>
<p>If you wish <?php out($command); ?> the database tables press the button below.</p>

<form action="<?php echo $url; ?>#installdb" method="post">
<div style="margin-top: 1em; text-align: center;">
	<p><strong>WARNING! The database installation procedure will delete any<br />
		existing Kit-Catalogue tables, and the data they contain!</strong></p>
	<table style="margin: 0 auto;">
	<tr>
		<td><input type="checkbox" name="confirminstalldb" id="confirminstalldb" value="1" /></td>
		<td><label for="confirminstalldb">Tick to confirm you want to replace any existing installation.</label></td>
	</tr>
	</table>
	<input type="submit" name="submitinstalldb" value="<?php out(ucwords($command)); ?> Database Tables" />
</div>
</form>
