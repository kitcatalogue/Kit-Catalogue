<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }

?>



<h2>Upgrade Notes</h2>

<p>Please read the following notes carefully. There may be important configuration and setup changes that need your attention
before you continue to work with your catalogue.</p>



<dl>

	<dt>v2.0.0</dt>
	<dd>
		<p><strong>MySQL and MySQLi PHP Extensions</strong></p>
		<p>By default, Kit-Catalogue now uses the MySQLi extension for database access.  If you want to keep using the older
			and soon to be deprecated, Mysql extension, add <em>$config['db.use_mysqli'] = false;</em> to your local config.</p>



		<p><strong>Organisational Tree Structure</strong></p>

		<p>Kit-Catalogue now supports a hierarchical organisational tree structure, where you can model how your organisational units
			fit together. e.g. organisation &gt; faculty &gt; school &gt; department &gt; ...</p>

		<p>If you have upgraded from an earlier version of Kit-Catalogue, your existing Organisation and Department entries
			will have been converted to this new tree structure, but the new structure will be quite flat in nature.  Your catalogue administrators should take the time to check the tree structure, and make sure that it properly reflects
			the structure of your own organisation.

		<p>If you need time to properly re-organise your organisational tree, you can temporarily disable the new tree
			functionality while you work on it.  Your catalogue will revert to the old Department and Organisation
			settings, and will work in the "old way" for visitors, but the administration interface will continue to use the tree.

		<p>If you want to temporarily disable the organisational tree structure, add <em>$config['app.use_ou_tree'] = false;</em> to your local config.</p>

		<p>We are planning to completely remove the old Organisation and Department functionality in a future release, so do
			make time to complete the switch to the new organisational tree structure.</p>



		<p><strong>New Features</strong></p>
		<p>These are some of the other features you may want to consider activating in your local configuration.</p>
		<table class="grid">
		<tr>
			<th style="text-align: center;">Feature</th>
			<th style="text-align: center;">Config Setting</th>
		</tr>
		<tr>
			<td>"Facilities" top-menu item.</td>
			<td><em>menu.facility.enabled</em></td>
		</tr>
		<tr>
			<td>"Tags" top-menu item.</td>
			<td><em>menu.tag.enabled</em></td>
		</tr>
		<tr>
			<td>Item editors and custodian edit access controls.</td>
			<td><em>admin.item.___</em></td>
		</tr>
		<tr>
			<td>Set the data licence to use with your equipment data.</td>
			<td><em>data.___</em></td>
		</tr>
		<tr>
			<td>Automatically resize uploaded images.</td>
			<td><em>item.image.___</em></td>
		</tr>
		</table>
	</dd>

</dl>
