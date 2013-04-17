<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }

?>



<h2>Release Notes</h2>

<p>Please read the following release notes carefully. There may be important configuration and setup changes that need your attention
before you continue to work with your catalogue.</p>



<dl>

	<dt>v2.0.0</dt>
	<dd>
		<p>This version of Kit-Catalogue does not have any specific requirements for new installations.</p>

		<p>If you have used Kit-Catalogue before, these are some of the new features in this version that
			you might want to activate in your configuration.</p>
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
