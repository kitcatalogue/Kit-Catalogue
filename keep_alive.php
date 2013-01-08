<?php
/*
 * Keep-Alive refreshes every few minutes to keep the session alive during
 * periods of user inactivity.
 */

@session_start();

?><!doctype html>
<html>
<head>
<title>Keep-Alive</title>
<meta http-equiv="refresh" content="240; url=<?php echo($_SERVER['PHP_SELF']); ?>">
<script type="text/javascript"><!--

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