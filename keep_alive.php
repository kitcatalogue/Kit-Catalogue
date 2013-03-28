<?php
<<<<<<< HEAD
/*
 * Keep-Alive refreshes every few minutes to keep the session alive during
 * periods of user inactivity.
 */


date_default_timezone_set('Europe/London');
setlocale(LC_ALL, 'en_UK.UTF8');

@session_start();

?><!doctype html>
=======
@session_start();
?>
<!doctype html>
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd
<html>
<head>
<title>Keep-Alive</title>
<meta http-equiv="refresh" content="240; url=<?php echo($_SERVER['PHP_SELF']); ?>">
<<<<<<< HEAD
<script type="text/javascript"><!--
=======
<script language="JavaScript" type="text/javascript"><!--
>>>>>>> 593f5496075bbdb70e356142caa3cdea7c0271dd

	function refresh() {
		window.location.reload(true);
	}

	function body_onload() {
		self.setTimeout('refresh()', 250000);
	}

// -->
</script>
</head>
<body onload="body_onload();">

<?php echo( date('H:i:s d-m-Y') ); ?>

</body>
</html>