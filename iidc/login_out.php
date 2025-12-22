<?php
session_start();
extract($_POST);
extract($_GET);
date_default_timezone_set(date_default_timezone_get());
//show errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//logout
if (isset($_GET['act']) and $_GET['act'] == "logOut") {
	if (isset($_SESSION["username"])) {
		session_destroy();
	}
	$dir = "tem";
	if (@$dh  = opendir($dir)) {
		while (false !== ($filename = readdir($dh))) {
			if (is_file("$dir/$filename") and $filename != "." and $filename != ".." and $filename != "index.html") {
				unlink("$dir/$filename");
			}
		}
		@closedir($dir);
	}
	echo "<meta http-equiv=\"refresh\" target=top content=\"0;url=//www.iidc.eu\">";
}

function cleanInput($input)
{
	$input = preg_replace("/\s+/", ' ', $input);
	$input = str_replace(array("'", '"', 'DROP TABLE', 'drop table', '1 = 1', '1=1', ';', '--'), '', $input);
	return trim($input);
}

//login
if (isset($act) and $act == "logIn") {

	if (isset($username) and isset($password) and trim($username) != '' and trim($password) != '') {

		$username = cleanInput($username);
		$password = cleanInput($password);
		include "config/mysql_ftp.inc.php";
		include "config/connect.inc.php";
		include "config/paths.inc.php";
		if (isset($asClient) and trim($asClient) == "y") {
			$adminData = $_SESSION;

			//remove all session variables
			session_unset();
			//destroy the session
			session_destroy();
			session_start();
			$_SESSION['adminData'] = $adminData;

			$user = $amdb->get_row("SELECT users.clid,users.email,company_name, CONCAT(companies.contact_title1,' ',companies.contact_name1,' ',companies.contact_surname1) as name,users.username,companies.country1,companies.offid,users.email
			FROM users JOIN companies ON companies.clid = users.clid   where users.username='$username'");
			if ($user) {
				$_SESSION['clid'] = $user['clid'];
				$_SESSION['username'] = $user['username'];
				$_SESSION['user_type'] = 'client';
				$_SESSION['user_country'] = $user['country1'];
				$_SESSION['hqc_title'] = $user['company_name'];
				$_SESSION['user_url'] = "$prog_www/company";
				$_SESSION['offid'] = $user['offid'];
				foreach (array('clid', 'email', 'name', 'company_name') as $value)
					$_SESSION['user'][$value] = $user[$value];
			}
			$_SESSION['logedAsClient'] = "y";
			$_SESSION["user_url"] = "$prog_www/company";
			header("Location: $prog_www/company");
			exit();
		}

		session_unset();
		//destroy the session
		session_destroy();
		session_start();
		$row = array();
		//check if co- is in username and at 0 position
		if (strpos($username, 'co-') === 0 and trim($password) != '') {
			if ($row = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE username='{$_POST['username']}' AND password='{$password}'")) {
				$_SESSION['comemid'] = $row['comemid'];
				$_SESSION['username'] = $row['username'];
				$_SESSION['user_type'] = 'committee_member';
				$_SESSION['user_country'] = $row['country'];
				$_SESSION['hqc_title'] = $row['member_name'];
				$_SESSION['super_admin'] = $row['super_admin'];
				$_SESSION['member_offices'] = $row['member_offices'];
				$_SESSION['user_url'] = "$prog_www/committee";
				echo "success_committee";
			} else {
				echo "Error log-in: Please check your username and password and try again";
			}
			exit();
		}

		foreach (
			array(
				"hqc_admin_users" => "uid,username_owner as name,email,permissions,clients_allowed,status,user_role",
				"offices" => "offid,office_name,contact_person as name,office_email as email,status",
				"offices_users" => "offid,offuid,name,email,status",
				"users JOIN companies ON users.clid=companies.clid" => "users.clid,users.email,company_name, CONCAT(companies.contact_title1,' ',companies.contact_name1,' ',companies.contact_surname1) as name,users.status",
				"client_users JOIN companies ON client_users.clid=companies.clid" => "client_users.clid,client_users.cluid,company_name,client_users.name,client_users.email,client_users.status",
			) as $table => $select
		) {

			if ($row = $amdb->get_row("SELECT $select FROM $table where  username = '$username' and password='$password'")) {
				$_SESSION['user'] = $row;
				if ($table == 'hqc_admin_users')
					setcookie("user", json_encode($_SESSION['user']), time() + 3600 * 24 * 30, "/");
				break;
			}
		}

		if (count($row) > 0) {

			if (isset($row['status'])) {
				if ($row['status'] == 'active')
					$row['active'] = 'y';
				else
					$row['active'] = 'n';
			}

			if ($row['active'] != 'y' and $username != 'admin') {
				include "config/msgs.inc.php";
				echo $msgs['na'];
				exit();
			} else {
				$_SESSION["username"]	= $_POST['username'];
				$_SESSION["user_role"]	= '';
				if (isset($row['offid']) && $row['offid'] != '0') {
					$_SESSION["offid"]	= $row['offid'];
					$_SESSION['user_type'] = 'hqc_office';
					if (isset($row['offuid'])) {
						$_SESSION['offuid'] = $row['offuid'];
						if (trim($row['permissions']) == '')
							$row['permissions'] = 'certificates,clients,products,invoices,profile';
						$_SESSION['permissions'] = explode(',', $row['permissions']);
					}
					if ($office = $amdb->get_row("SELECT office_name,office_country FROM offices WHERE offid='$row[offid]'")) {
						$_SESSION['hqc_title'] = $office['office_name'];
						$_SESSION['office_country'] = $office['office_country'];
						$_SESSION['user_country'] = $office['office_country'];
					}
				} elseif (isset($row['clid'])) {
					if ($office = $amdb->get_row("SELECT company_name,country1 FROM companies WHERE offid='$row[clid]'")) {
						$_SESSION['hqc_title'] = $office['company_name'];
						$_SESSION['user_country'] = $office['country1'];
					}
					$_SESSION["clid"]	= $row['clid'];
					$_SESSION['user_type'] = 'client';
					if (isset($_SESSION['user']['cluid'])) {
						$_SESSION['cluid'] = $_SESSION['user']['cluid'];
					}
					if (isset($_SESSION['user']['name'])) {
						$_SESSION['hqc_title'] = $_SESSION['user']['company_name'] . ' (' . $_SESSION['user']['name'] . ')';
					}
					if (isset($_SESSION["offid"]))
						unset($_SESSION["offid"]);
				} else {
					if ($office = $amdb->get_row("SELECT office_name,office_country FROM offices WHERE offid='0'")) {
						$_SESSION['hqc_title'] = $office['office_name'];
						$_SESSION['user_country'] = $office['office_country'];
					}
					$_SESSION["uid"]	= $row['uid'];
					$_SESSION['user_type'] = 'admin';
					$_SESSION['user_role'] = $row['user_role'];
					$_SESSION["offid"] = 0;
					if ($_SESSION['user_type'] == 'admin') {
						$_SESSION['hqc_title'] .= ' - ' . $_SESSION['user']['name'];
					}
				}
				if ($_SESSION["user_type"] != 'hqc_office') {
					$permissions = "<?php if (!defined(\"_HQC_\")){exit();};
		\$user_permissions = array($row[permissions]);
		\$user_clients = array($row[clients_allowed]);
		?>";
					$hqc_user_file = "hqc_users/" . $_POST["username"] . ".usr.php";
					if (file_exists($hqc_user_file))
						unlink($hqc_user_file);
					$fl = fopen($hqc_user_file, "w");
					fwrite($fl, $permissions);
					fclose($fl);
				}
				if (!is_local() && get_ip_address()) {
					if ($s = file_get_contents("http://ip-api.com/json/" . get_ip_address())) {
						if (is_array(json_decode($s, true))) {
							$activity['country'] = json_decode($s)->country;
							$activity['cc'] = json_decode($s)->countryCode;
							$activity['city'] = json_decode($s)->city;
						}
					}
					if (!isset($activity['country']))
						$activity['country'] = '';
					$activity['ip'] = get_ip_address();
					$activity['action'] = 'login';
					$activity['user_type'] = $_SESSION['user_type'];
					if (isset($_SESSION["offid"]))
						$activity['uid'] = $_SESSION["offid"];
					elseif (isset($_SESSION["uid"]))
						$activity['uid'] = $_SESSION["uid"];
					elseif (isset($_SESSION["clid"]))
						$activity['uid'] = $_SESSION["clid"];
					if (isset($activity['uid'])) {
						if ($uaid = $amdb->insert("users_activity", $activity))
							$_SESSION['uaid'] = $uaid;
					}
					$_SESSION['country'] = $activity['country'];
				}

				if (isset($_SESSION["offid"]) && $_SESSION["offid"] != '0') {
					$_SESSION['logedAsOffice'] = "y";
					$_SESSION["user_url"] = "$prog_www/offices/home";
					echo "success_office";
				} elseif (isset($_SESSION["uid"]) or $_SESSION['user_type'] == "admin") {
					if (isset($asClient) and trim($asClient) == "y")
						$_SESSION['logedAsClient'] = "y";
					if ($row['permissions'] == '"auditor"') {
						$_SESSION["user_url"] = "$prog_www/audit";
						echo "success_auditor";
					} else {
						$_SESSION["user_url"] = "$prog_www/admin";
						echo "success_admin";
					}
				} else {
					if (isset($asClient) and trim($asClient) == "y")
						$_SESSION['logedAsClient'] = "y";
					$_SESSION["user_url"] = "$prog_www/company";
					echo "success_client";
				}
				exit();
			}
		} else {
			echo "Error log-in: Please check your username and password and try again";
			exit();
		}
	}
}

if (isset($act) and $act == "sendPassword" and isset($lost_email) and trim($lost_email) != '') {
	define('__HQC__', true);
	include __DIR__ . "/functions.inc.php";
	include "config/mysql_ftp.inc.php";
	include "config/connect.inc.php";

	$result = array();
	$tables = array("hqc_admin_users", "users", "offices", "offices_users");
	foreach ($tables as $table) {
		if ($table == "offices")
			$email_input = "office_email";
		else
			$email_input = "email";
		$email = trim($lost_email);
		if ($res = $amdb->get_row("SELECT * FROM $table where $email_input='$email' or username='$email'")) {
			$res['email'] = $res[$email_input];
			$result[] = $res;
		}
	}

	if (count($result) == 0) {
		post_this_results("We didn't find your email | username, please try again.<br/>Try switching between email and  username.");
		exit();
	}

	if (count($result) > 1) {
		post_this_results("We found more than one account with this email address, please use your username instead of email.");
		exit();
	} else {
		$row = $result[0];
	}

	if (isset($row)) {
		if (!$theMessage = $amdb->get_row("SELECT * FROM `hqc_email_messages` WHERE `message_id` = 'lost-password'"))
			return;

		$theFooter = $amdb->get_row("SELECT * FROM `hqc_email_messages` WHERE `message_id` = 'email_footer'");
		$theMessage['email_message'] = str_replace("[email_footer]", $theFooter['email_message'], $theMessage['email_message']);

		if (isset($row['uid'])) {
			$row['user_name'] = $row['username_owner'];
		} elseif (isset($row['offid'])) {
			$row['user_name'] = $row['contact_person'];
		} elseif (isset($row['offuid'])) {
			$row['user_name'] = $row['name'];
		} elseif (isset($row['clid'])) {
			$company = $amdb->get_row("SELECT * FROM companies WHERE clid='{$row['clid']}'");
			$user_name = trim($company['contact_title1']) != '' ? $company['contact_title1'] . ' ' : '';
			$user_name .= trim($company['contact_name1']) != '' ? $company['contact_name1'] . ' ' : '';
			$user_name .= trim($company['contact_surname1']) != '' ? $company['contact_surname1'] . ' ' : '';
			$row['user_name'] = $user_name;
		}

		$row['my_company'] = 'Halal Quality Control BV';
		$row['my_address'] = '';

		foreach ($row as $key => $value) {
			$theMessage['email_message'] = str_replace("[$key]", $value, $theMessage['email_message']);
			$theMessage['email_subject'] = str_replace("[$key]", $value, $theMessage['email_subject']);
		}

		$email = array();
		$to_email = $row['email'];
		$to_name = isset($theMessage['company_name']) ? $theMessage['company_name'] : $row['user_name'];
		$from_name = $row['my_company'];
		$from_email = $theMessage['reply_address'];
		$subject = $theMessage['email_subject'];
		$message = $theMessage['email_message'];


		include __DIR__ . "/tools/mail/hqc_mail.inc.php";
		if (hqc_mail($to_email, $to_name, $from_email, $from_name, $subject, $message)) {
			post_this_results("Please check your email ($to_email) for username and password.");
			echo '<script>window.parent.switchLoginPass("doLogin");</script>';
		} else {
			post_this_results("We couldn't send you an email, please try again.<br/>Try switching between email and  username.");
		}
		exit();
	} else {
		post_this_results("We didn't find your email | username, please try again.<br/>Try switching between email and  username.");
		exit();
	}
}
