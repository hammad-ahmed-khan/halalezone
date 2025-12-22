<?php
session_start();
if (isset($_SESSION['username'])) {

	include dirname(__FILE__) . "/config/mysql_ftp.inc.php";
	include dirname(__FILE__) . "/config/connect.inc.php";
	include dirname(__FILE__) . "/config/paths.inc.php";

	if (isset($_SESSION['adminData']) and $_GET['act'] == 'backToAdmin') {
		$adminData = $_SESSION['adminData'];

		//remove all session variables
		session_unset();
		//destroy the session
		session_destroy();
		session_start();
		$_SESSION = $adminData;
		header("Location: $_SESSION[user_url]");
		exit();
	}
if(!isset($_GET['act']))
$_GET['act'] = 'logout';
	if (!isset($_SESSION['sys_admin']) and $_GET['act'] != 'backToAdmin') {

		$activity['action'] = 'logout';
		$activity['user_type'] = $_SESSION['user_type'];

		if (isset($_SESSION['uaid']))
			$activity['parent_uaid'] = $_SESSION['uaid'];

		$activity['ip'] = get_ip_address();

		if (isset($_SESSION['offuid'])) {
			$activity['uid'] = $_SESSION["offuid"];
			$activity['user_type'] = 'office_user';
		} elseif (isset($_SESSION["offid"]))
			$activity['uid'] = $_SESSION["offid"];
		elseif (isset($_SESSION["uid"]))
			$activity['uid'] = $_SESSION["uid"];
		elseif (isset($_SESSION["clid"]))
			$activity['uid'] = $_SESSION["clid"];

		if (isset($_SESSION['country']))
			$activity['country'] = $_SESSION['country'];

		if (isset($activity['uid'])) {
			$amdb->insert("users_activity", $activity);
		}
	}

	if (isset($_GET['act']) and $_GET['act'] == 'backToAdmin' and isset($_SESSION['logedAsClient']) and isset($_COOKIE['user'])) {
		$admin_user = json_decode($_COOKIE['user'], true);
		$_SESSION['user'] = $admin_user;
		if ($user = $amdb->get_row("select * from hqc_admin_users WHERE uid = '$_SESSION[uid]'")) {
			$_SESSION['user_type'] = 'admin';
			$_SESSION['username'] = $user['username'];
			unset($_SESSION['logedAsClient']);
			unset($_SESSION['clid']);
			$_SESSION['offid'] = 0;
			echo "<meta http-equiv=\"refresh\" content=\"0;url=/admin/?inc=clients\">";
		}
		exit();
	}


	$dir = "tem";
	if ($dh  = opendir($dir)) {
		while (false !== ($filename = readdir($dh))) {
			if (is_file("$dir/$filename") and $filename != "." and $filename != ".." and $filename != "index.html") {
				unlink("$dir/$filename");
			}
		}
		//close the directory
		closedir($dh);
	}
}

//remove all session variables
session_unset();
//destroy the session
session_destroy();
//remove all cookies not only PHPSESSID
foreach ($_COOKIE as $key => $value) {
	setcookie($key, "", time() - 3600, "/");
}

echo "<meta http-equiv=\"refresh\" target=top content=\"0;url=//$_SERVER[HTTP_HOST]\">";
