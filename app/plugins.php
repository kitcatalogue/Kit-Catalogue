<?php
$plugins = Ecl::factory('Ecl_Hooks');


$plugin_path = Ecl_Helper_Filesystem::fixPath($config['app.local_root'].'/plugins');
$list = Ecl_Helper_Filesystem::getFolderContents($plugin_path, true, 'php');


if (!empty($list)) {
	foreach($list as $i => $file) {
<<<<<<< HEAD
		// Ignore files that start with a dot '.'
		if ('.' != $file[0]) {
			$path = $plugin_path .'/'. $file;
			if (file_exists($path)) {
				include($path);
			}
=======
		$path = $plugin_path .'/'. $file;
		if (file_exists($path)) {
			include($path);
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
		}
	}
}


if (isset($model)) {
	$model->setObject('plugins', $plugins);
}
?>