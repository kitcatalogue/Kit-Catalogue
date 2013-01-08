<?php
if (!defined('KC_INSTALL_WIZARD')) { die('Install wizard steps cannot be called directly.<br /><a href="../">Run the install wizard</a>.'); }
?>


<p>This wizard will take you through any steps required to bring your Kit-Catalogue installation up-to-date.</p>
<p>Usually, this will mean applying patches to your Kit-Catalogue database to create any new tables or fields used in the updated version of the sotware.</p>


<p style="clear: both;">&nbsp;</p>


<h2>The different steps are:</h2>

<ol>
	<li>Introduction (this page).</li>
	<li>Update database.</li>
	<li>Finish!</li>
</ol>

<br />
<div class="warn">
	<p class="title">Please note!</p>
	<p>For security reasons, once you have installed/upgraded Kit-Catalogue and made sure
		it is all working properly, you should edit your local config and disable the
		installer using this setting.</p>

	<p><em>$config['installer.enabled'] = false;</em></p>

	<p><strong>If you leave the installer available, malicious users can use it to
		damage/delete your catalogue!</strong></p>

	<p>For added security, you could delete the <em>/install/</em> folder to prevent
		unauthorised access, but if you later upgrade or reinstall Kit-Catalogue, you
		will have a new version of the <em>/install/</em>  folder created, so disabling it in
		your config is a must.</p>
</div>