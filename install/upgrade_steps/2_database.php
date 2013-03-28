<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }


require_once(APP_PATH.'/model.php');
?>


<h2>Database Updates</h2>

<p>
	Kit-Catalogue runs on a MySQL database.  This section will check to ensure the database connection works correctly, and prompt you to install any database upgrades that are required.
</p>



<h2>Checking connection to database server</h2>

<p>Using the settings in your configuration file, we will check that Kit-Catalogue can connect to your database server.</p>
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



<h2>Checking for database updates</h2>
<p>By checking your database's current version against the available upates, we can see if you need an upgrade.</p>


<?php
// Check database version


$migrator = new Ecl_Db_Migrator($db, $db->getSchema(), array (
	'path'   => APP_PATH . '/migrations/',
	'params' => array (
		'model' => $model,
		),
));
$patches = $migrator->listLatestMigrations();

if (empty($patches)) {
	?>
	<div class="good">
		<p class="title">OK - It looks like your database is up-to-date.</p>
	</div>
	<?php
	return;
}



$count = count($patches);
$msg = (1 == $count) ? 'There is 1 patch to apply.' : "There are $count patches to apply" ;
?>
<div class="warn">
	<p class="title">It looks like your database needs upgrading.</p>
	<p><?php out($msg)?></p>
</div>



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
if (isset($_POST['submitupgradedb'])) {

	if (!isset($_POST['confirmupgradedb'])) {
		?>
		<div class="bad">
			<p class="title">Error - You must tick the box to confirm you want to apply the database updates.</p>
		</div>
		<?php
	} else {
		$msg = '';
		$result = false;
		try {
			$result = $migrator->toLatest();
		} catch (\Exception $e) {
			$msg = $e->getMessage();
		}

		if ($result) {
			?>
			<div class="good">
				<p class="title">OK - The database upgrade is complete.</p>
			</div>
			<?php
		} else {
			?>
			<div class="bad">
				<p class="title">Error - There was an error while upgrading the database.</p>
				<p>The error returned was: <?php out($msg); ?></p>
			</div>

			<?php
			$no_next = true;
		}

	}

}
?>
</div>
