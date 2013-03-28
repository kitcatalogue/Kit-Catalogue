<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }
?>



<h2>Checking Authentication Setup</h2>

<p>Kit-Catalogue has two main ways of authentication users of your catalogue, Active Directory/LDAP authentication and Database authentication.</p>




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



<h2>Check application model</h2>

<p>Here we initialise all the classes that make up the "domain model", such as category and item stores, security modules, etc.</p>
<?php
$path = APP_PATH .'/model.php';
if (file_exists($path)) {
	try {
		include_once($path);
		?>
		<div class="good">
			<p class="title">OK - Model loaded successfully.</p>
		</div>
		<?php
	} catch (\Exception $e) {
		?>
		<div class="bad">
			<p class="title">Error - There was an error while loading the model.</p>
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
		<p class="title">Error - Unable to find model: <em><?php out($path); ?></em>.</p>
	</div>
	<p>Without the model, Kit-Catalogue will not be able to run at all.</p>
	<?php
	$no_next = true;
	return;
}
?>



<h2>Check Active Directory/LDAP Authentication</h2>

<p>The LDAP authentication will authenticate your visitor's username and password against your local Active Directory (AD).</p>
<p>Using LDAP is probably the easiest way to manage authentication, as you will not need to add a record for every user of the
	system to Kit-Catalogue.  Instead, if a user authenticates against the AD, their details will be read automatically.</p>

<p>You will still need to add user records for anyone who will require admin privileges for the catalogue.</p>

<?php
if ($config['signin.use_ldap']) {

	if (function_exists('ldap_connect')) {
		?>
		<div class="good">
			<p class="title">OK - The LDAP PHP extension appears to be installed.</p>
		</div>
		<?php
	} else {
		?>
		<div class="bad">
			<p class="title">Error - The LDAP PHP extension is not installed.</p>
			<p>No ldap_connect() function could be found, which probably means the LDAP extension is not installed.</p>
			<p>See: <a href="http://www.php.net/manual/en/ldap.setup.php">http://www.php.net/manual/en/ldap.setup.php</a> for more information on installation.</p>
		</div>
		<?php
	}
	?>
	<div class="good">
		<p class="title">OK - LDAP authentication enabled.</p>
	</div>

	<table class="grid valigntop">
	<tr>
		<th>option</th>
		<th>currently set to</th>
	</tr>
	<tr>
		<td class="name">ldap.host</td>
		<td><?php out($config['ldap.host']); ?></td>
	</tr>
	<tr>
		<td class="name">ldap.port</td>
		<td><?php out($config['ldap.port']); ?></td>
	</tr>
	<tr>
		<td class="name">ldap.dn</td>
		<td><?php out($config['ldap.dn']); ?></td>
	</tr>
	<tr>
		<td class="name">ldap.username_suffix</td>
		<td><?php out($config['ldap.username_suffix']); ?></td>
	</tr>
	</table>
	<?php
} else {
	?>
	<div class="warn">
		<p class="title">LDAP authentication is disabled.</p>
	</div>
	<?php
}
?>



<h2>Check Database Authentication</h2>

<p>The Database authentication system allows you to add your own users to the database,	and assign your own usernames
	and passwords.  The database authentication also enables the built in <em>admin</em> user account (see below for more info).</p>
<?php
if ($config['signin.use_ldap']) {
	?>
	<p>If you have enabled LDAP authentication, and assigned other members of staff as administrators, you should probably disable Database authentication.</p>
	<?php
}

$use_db_auth = false;

if ($config['signin.use_database']) {
	$use_db_auth = true;
	?>
	<div class="good">
		<p class="title">OK - Database authentication enabled.</p>
	</div>
	<?php
} else {
	?>
	<div class="warn">
		<p class="title">Database authentication is disabled.</p>
	</div>
	<?php
}
?>



<h2>Check authentication plugins</h2>

<p>Using the local configuration plugin functionality, you can add customised authentication procedures to the signin process.</p>
<?php
$path = APP_PATH .'/plugins.php';
if (file_exists($path)) {
	try {
		include_once($path);
		?>
		<div class="good">
			<p class="title">OK - Plugin system loaded successfully.</p>
		</div>
		<?php
	} catch (\Exception $e) {
		?>
		<div class="bad">
			<p class="title">Error - There was an error while loading the plugins.</p>
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
		<p class="title">Error - Unable to find plugin system: <em><?php out($path); ?></em>.</p>
	</div>
	<p>Without the plugin system, Kit-Catalogue will not be able to run properly.</p>
	<?php
	$no_next = true;
	return;
}


if (!$model->get('plugins')->exists('signin.authenticate')) {
	?>
	<div class="warn">
		<p class="title">No authentication plugins found.  You are only using the built-in methods.</p>
	</div>
	<?php
} else {
	?>
	<div class="good">
		<p class="title">OK - You have added a customised authentication plugin.</p>
	</div>
	<?php
}
?>



<h2>Check admin account</h2>

<p>When first installed, Kit-Catalogue comes with a preset <em>admin</em> account you can use to setup the system, and begin adding
your proper user accounts. The default credientials for the <em>admin</em> account are:</p>

<p>Username : <em>admin</em></p>
<p>Password : <em>admin</em></p>

<?php
$model->get('db')->query("
	SELECT username
	FROM user
	WHERE username='admin'
");

$is_admin_account = ($model->get('db')->hasResult());

if (!$is_admin_account) {
	?>
	<div class="warn">
		<p class="title">The <em>admin</em> account has been deleted.</p>
	</div>
	<?php
} else {

	// Handle change admin password post
	$change_admin_password = null;
	if (isset($_POST['submitpassword'])) {
		$change_admin_password = 'bad';

		$curr_pass = (isset($_POST['current_password'])) ? $_POST['current_password'] : '' ;
		$new_pass = (isset($_POST['new_password'])) ? $_POST['new_password'] : '' ;
		$confirm_pass = (isset($_POST['confirm_password'])) ? $_POST['confirm_password'] : '' ;

		$errors = array();

		if (!$model->get('userstore')->authenticateDb('admin', $curr_pass)) { $errors[] = 'The current password was incorrect.'; }
		if (empty($new_pass)) { $errors[] = 'You cannot have an empty password.'; }
		if ($new_pass != $confirm_pass) { $errors[] = 'Your new password did not match the confirmation password.'; }

		if (empty($errors)) {
			$model->get('userstore')->setPassword('admin', $new_pass);
			$change_admin_password = 'good';
		}
	}

	// Check if admin password is default
	if ($model->get('userstore')->authenticateDb('admin', 'admin')) {
		?>
		<div class="bad">
			<p class="title">Security Warning - You are still using the default admin password!</p>
		</div>

		<p>For security reasons, you should change the <em>admin</em> account's password as soon as possible.  You can also delete the user account through the
		Kit-Catalogue administration interface, but you can only do this if you sign in under a different username.</p>
		<?php
		if (!$use_db_auth) {
			?>
			<p>Database authentication is disabled, so the built in <em>admin</em> account's password will be disabled too,
			but we still advise changing the default password in case this changes in the future and your installation is left vulnerable!</p>
			<?php
		}
	} else {
		?>
		<div class="good">
			<p class="title">Good - You have changed the default password for the admin account.</p>
		</div>
		<?php
		if (!$use_db_auth) {
			?>
			<p>In addition, Database authentication is disabled, so the built in <em>admin</em> account's password will be disabled too.</p>
			<?php
		}

	}
}
?>



<?php
if ($is_admin_account) {
	?>
	<h2>Change admin password</h2>

	<p>To change the <em>admin</em> account password, complete the form below.</p>

	<?php
	if (!empty($change_admin_password)) {
		if ('good' == $change_admin_password) {
			?>
			<div class="good">
				<p class="title">Saved - The <em>admin</em> account password has been changed.</p>
			</div>
			<?php
		} else {
			?>
			<div class="bad">
				<p class="title">Error - Unable to change admin password.</p>
				<?php
				foreach($errors as $error) {
					echo "<p>{$error}</p>";
				}
				?>
			</div>
			<?php
		}
	}
	?>

	<?php Ecl_Helper_Html::form('changepassform', $url.'#changepassform', 'post'); ?>
	<dl id="changepassform">

		<dt><?php Ecl_Helper_Html::formLabel('current_password', 'Current Password'); ?></dt>
		<dd><?php Ecl_Helper_Html::formInput('current_password', 30, 250, ''); ?></dd>

		<dt><?php Ecl_Helper_Html::formLabel('new_password', 'New Password'); ?></dt>
		<dd><?php Ecl_Helper_Html::formPassword('new_password', 30, 250, ''); ?></dd>

		<dt><?php Ecl_Helper_Html::formLabel('confirm_password', 'Confirm New Password'); ?></dt>
		<dd><?php Ecl_Helper_Html::formPassword('confirm_password', 30, 250, ''); ?></dd>

	</dl>
	<div style="margin-left: 2em;">
		<?php Ecl_Helper_Html::formSubmit('submitpassword', 'Change Password'); ?>
	</div>
	<?php Ecl_Helper_Html::formClose(); ?>

	<?php
}
?>


