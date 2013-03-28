<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }
?>


<h2>Local Configuration</h2>

<p>
	Kit-Catalogue comes with a default set of configuration options and layout styles.
	Being an open source product, we fully expect you to tailor the system to your own particular needs, and you are
	free to change the code as you see fit.
	However, such changes can complicate things for IT staff when it comes time to upgrade or reinstall the system.
</p>

<p>
	To try and smooth this process, we've partitioned a lot of the Kit-Catalogue configuration and styling options
	in to a special folder called "<em>local</em>".  The idea is simple... You make all your configuration, CSS and layout
	changes in the <em>local</em> folder, and when it comes time to update to a new version, we don't overwrite the the
	local information, so all your changes remain intact.
</p>

<p>
	As far as configuration goes, all changes made in <em>local/local_config.php</em> will override those in the default
	<em>app/config.php</em>.  To ensure your updates are not accidentally overwritten during an upgrade, <strong>only make changes in the <em>local</em> folder!</strong>
</p>



<h2>Checking for a <em>local</em> folder</h2>

<?php
if (!file_exists(LOCAL_PATH)) {
	?>
	<div class="bad">
		<p class="title">Oh! It looks like you don't have a <em>local</em> folder.</p>
		<p>Looking for: <?php out(LOCAL_PATH); ?></p>
	</div>

	<p>If this is the first time you've installed Kit-Catalogue, then you'll need to enable all the local functionality
	by simply renaming the <em>new_local</em> folder to <em>local</em>.</p>

	<p>Once you've enabled the <em>local</em> folder, <a href="<?php echo $url; ?>">refresh this page</a> to continue with the wizard.</p>
	<?php
	$no_next = true;
	return;
} else {
	?>
	<div class="good">
		<p class="title">Good. It looks like you have a <em>local</em> folder.</p>
	</div>
	<p>Now let's check which features you have available within your folder.</p>
	<?php
}
?>



<h2>Checking for a local configuration file :  <em>local/local_config.php</em></h2>

<?php
$path = LOCAL_PATH . '/local_config.php';
if (!file_exists($path)) {
	?>
	<div class="bad">
		<p class="title">Oh! It looks like don't have a local config file.</p>
	</div>
	<p>
		It's not essential that you have a <em>local_config.php</em> file, but we'd strongly recommend it.
		If nothing else, it will allow you to properly set the application title and contact information for your Kit-Catalogue installation,
		as well as your database and other settings.
	</p>
	<?php
} else {
	?>
	<div class="good">
		<p class="title">Good. It looks like you have a local configuration file.</p>
	</div>
	<p>
		Here's what some of the main local configuration options are set to.
		<br />If you get a PHP error here, then there is probably a syntax error in the config file.
	</p>
	<?php
	include($path);
	?>

	<?php
	?>
	<table class="grid valigntop">
	<tr>
		<th>option</th>
		<th>currently set to</th>
	</tr>
	<tr>
		<td class="name">org.title</td>
		<td><?php out($config['org.title']); ?></td>
	</tr>
	<tr>
		<td class="name">org.www</td>
		<td><?php out($config['org.www']); ?></td>
	</tr>
	<tr>
		<td class="name">app.title</td>
		<td><?php out($config['app.title']); ?></td>
	</tr>
	<tr>
		<td class="name">app.www</td>
		<td>
			<?php
			if (empty($config['app.www'])) {
				$pos = strpos($_SERVER['SCRIPT_NAME'], '/install/');
				if ($pos > 0) {
					$path = substr($_SERVER['SCRIPT_NAME'], 0, $pos);
					if ( (!empty($path)) && ('/' != $path[0]) ) {
						$path = '/'.$path;
					}
				}
				$app_www_1 = $_SERVER['HTTP_HOST'] . $path;
				$app_www_2 = $_SERVER['HTTP_HOST'];
				?>
				<div class="bad">
					<p class="title">Your <em>app.www</em> setting is empty.</p>
					<p>This should be set to the URL from which the catalogue is served, e.g.</p>
					<p>http://<?php echo($app_www_1); ?></p>
					<p>or</p>
					<p>http://<?php echo($app_www_2); ?></p>
				</div>
				<?php
				$no_next = true;
			} else {
				out($config['app.www']);
				?>
				<p class="note">This is where your catalogue site will be served from.</p>
				<?php
			}
			?>
		</td>
	</tr>
	<tr>
		<td class="name">app.email</td>
		<td>
			<?php out($config['app.email']); ?>
			<p class="note">The person users should contact if they have questions/problems.</p>
		</td>
	</tr>
	<tr>
		<td class="name">app.allow_anonymous</td>
		<td>
			<?php out( ($config['app.allow_anonymous'] ? 'True (yes)' : 'False (No)' )); ?>
			<p class="note">Should anonymous (non-logged in users) be able to access the catalogue?.</p>
		</td>
	</tr>
	<tr>
		<td class="name">layout.use_local_css</td>
		<td>
			<?php out( ($config['layout.use_local_css'] ? 'True (yes)' : 'False (No)' )); ?>
			<p class="note">Kit-Catalogue will use your <em>local.css</em> file for styling.
			<br />See below for information on local CSS files.</p>
		</td>
	</tr>
	<tr>
		<td class="name">layout.template_file</td>
		<td>
			<?php
			if (empty($config['layout.template_file'])) {
				out('<none>');
			} else {
				out($config['layout.template_file']);
			}
			?>
			<p class="note">See below for information on local template files.</p>
		</td>
	</tr>
	</table>


	<p>We'll check more of your config settings as the installation wizard progresses.</p>
	<?php
}
?>



<h2>Checking for a local CSS file :  <em>local/css/local.css</em></h2>
<p>Using the local CSS file, you can override the Kit-Catalogue default styles, and tweak the look-and-feel of the site to your needs.</p>
<?php
$path = LOCAL_PATH . '/css/local.css';
if (!file_exists($path)) {
	?>
	<div class="warn">
		<p class="title">It looks like you don't have a local CSS file.</p>
	</div>
	<p>
		It's not essential that you have one, but if you need to tweek the look, style and colour of the Kit-Catalogue system
		to your	institution's own requirements, then you'll probably need to create one.
	</p>
	<?php
} else {
	?>
	<div class="good">
		<p class="title">OK - You already have a local CSS file.</p>
	</div>
	<?php
	if ( (isset($config['layout.use_local_css'])) && ($config['layout.use_local_css']) ) {
		?>
		<div class="good">
			<p class="title">OK - You have enabled the local CSS in your configuration.</p>
		</div>
		<?php
	} else {
		?>
		<div class="warn">
			<p class="title">Warning - The local CSS file is not enabled in your configuration.</p>
		</div>
		<p>Although you have a local CSS, you have not told Kit-Catalogue to use it.
		<p>To enable it the CSS file, add:  <em>$config['layout.use_local_css'] = true</em> to your local configuration file.</p>
		<?php
	}
}
?>



<h2>Checking for a local layout template :  <em>local/layouts/&lt;layout-file&gt;</em></h2>
<p>
	A layout template provides greater styling control as it defines the headers, footers, menus and other HTML output that wraps each page.
	If you need to completely alter Kit-Catalogue to match your corporate style, then a different layout template is what you need.
</p>

<?php
if ( (isset($config['layout.template_file'])) && (!empty($config['layout.template_file'])) ) {
	?>
	<div class="good">
		<p class="title">OK - You have defined a template in your configuration.</p>
	</div>
	<?php
	$path = ROOT_PATH . $config['layout.template_file'];
	if (!file_exists($path)) {
		?>
		<div class="bad">
			<p class="title">Error - It looks like the template file doesn't exist.</p>
		</div>
		<p>Your configuration contains the setting: <em>$config['layout.template_file'] = '<?php out($config['layout.template_file']); ?>'</em>
		<p>We could not find the file at: <em><?php out($path); ?></em></p>
		<p>Kit-Catalogue will not run if it cannot find your template file.
			<br />Check the path, or disable the template by using: <em>$config['layout.template_file'] = null</em> in your configuration file.
		<?php
	} else {
		?>
		<div class="good">
			<p class="title">OK - It looks like you have a local layout template.</p>
		</div>
		<?php
	}
} else {
	?>
	<div class="warn">
		<p class="title">You are not using a local layout template.</p>
	</div>
	<p>There's no problem with this, you will simply use the Kit-Catalogue default styles instead.
	<p>If you want to use your own template, create a new layout in <em>local/layouts</em>.  To get your started, you should copy the default Kit-Catalogue
		layout template from: <em>app/layouts/kitcatalogue.phtml</em>.</p>
	<p>To enable your new layout, add <em>$config['layout.template_file'] = '/local/layouts/&lt;filename&gt;'</em> to your local configuration.</p>
	<p>You can read a little more on templates in <em>docs/local_settings.txt</em>.</p>
	<?php
}


