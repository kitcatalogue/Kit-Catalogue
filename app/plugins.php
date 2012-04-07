<?php
$plugins = Ecl::factory('Ecl_Hooks');


$plugin_path = Ecl_Helper_Filesystem::fixPath($config['app.local_root'].'/plugins');
$list = Ecl_Helper_Filesystem::getFolderContents($plugin_path, true, 'php');


if (!empty($list)) {
	foreach($list as $i => $file) {
		$path = $plugin_path .'/'. $file;
		if (file_exists($path)) {
			include($path);
		}
	}
}


if (isset($model)) {
	$model->setObject('plugins', $plugins);
}
?>