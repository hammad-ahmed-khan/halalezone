<?php
$_SERVER['DOMAIN_NAME'] = str_replace('www.', '', strtolower($_SERVER['HTTP_HOST']));

if (substr_count($_SERVER['DOMAIN_NAME'], '.') < 2) {
	$http = ($_SERVER['REMOTE_ADDR'] == '::1' or $_SERVER['REMOTE_ADDR'] == '127.0.0.1') ? 'http' : 'https';
	header("location: $http://" . basename(dirname(__FILE__)) . "." . $_SERVER['DOMAIN_NAME']);
	exit();
}

session_start();
if (!isset($_SESSION["username"])) {
	session_destroy();
	session_start();
}
if (!file_exists("config/paths.inc.php")) {
	include_once "get_paths.inc.php";
	echo "<script>top.location.reload()</script>";
	exit();
}
$url = "home.php";

if (isset($_GET['do']) and trim($_GET['do']) == "register")
	$url = "company/?inc=register";

if (isset($_SESSION["username"])) {
	if (isset($_GET['tb']) and isset($_SESSION["tb_url" . $_GET['tb']]) and trim($_SESSION["tb_url" . $_GET['tb']]) != '')
		$url = $_SESSION["tb_url" . $_GET['tb']];
	elseif (isset($_SESSION["user_url"]) and trim($_SESSION["user_url"]) != "")
		$url = $_SESSION["user_url"];
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<link rel="icon" type="image/png" href="/images/small-logo.png">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>IIDC GmbH - Halal Certification</title>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script>
		var hcp_dir = "<?php echo trim(basename(dirname(__FILE__))); ?>";
		var hcp_url = '';

		function do_alert_message(msg) {
			document.getElementById("contents").contentWindow.alert_message(msg);
		}

		function closePopup() {
			document.getElementById("contents").contentWindow.closeDialog();
		}

		function alert_message(msg) {
			document.getElementById("contents").contentWindow.alert_message(msg);
		}

		function postResults(data) {
			document.getElementById("contents").contentWindow.postResults(data);
		}
	</script>
</head>
<frameset rows="0,*" framespacing="0" border="0" frameborder="0">
	<frame name="top" scrolling="no" noresize target="contents" src="top.html">
		<frame name="bottom" target="contents" id="contents" src="<?php echo $url ?>" scrolling="auto">
			<noframes>

				<body>

					<p>This page uses frames, but your browser doesn't support them.</p>

				</body>
			</noframes>
</frameset>

</html>