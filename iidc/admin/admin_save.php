<?php
define("__HQC__", true);
$date = date("d/m/Y");
$time = date("h:i:s");
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";
include "../config/defaults.inc.php";

if (isset($_POST))
	extract($_POST);

if (isset($_POST['act']) and $_POST['act'] == 'block_client' && isset($_POST['clid'])) {
	if ($row = $amdb->get_row("SELECT * FROM users where clid='$_POST[clid]'")) {
		$amdb->query("UPDATE users SET active='b' WHERE clid='$_POST[clid]'");
		echo "ok";
	} else {
		echo "error:Not able to block client";
	}
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'activate' && isset($_POST['clid']) && count($_POST['clid']) > 0) {

	include "../tools/mail/hqc_mail.inc.php";
	include "../config/msgs.inc.php";

	foreach ($_POST['clid'] as $clid) {
		$user = array('active' => 'y', 'approved' => 'y', 'status' => 'active', 'activate_code' => '');
		$amdb->update("users", $user, "clid='$clid'");
		$amdb->update("companies", $user, "clid='$clid'");
		if ($row = $amdb->get_row("SELECT * FROM companies where clid='$clid'")) {

			$name = "$row[contact_title1] $row[contact_name1] $row[contact_surname1]";
			$company_name = $row['company_name'];
			$email = $row['email1'];
			//send the message==============================================================
			$to_email = $email;
			$to_name = $company_name;
			$from_email = 'info@iidc.eu';
			$from_name = 'iidc.eu';
			$subject = 	'Your account by iidc.eu has been activated';
			$message = $msgs['activated'];
			$message = str_replace('[client_name]', $name, $message);
			hqc_mail($to_email, $to_name, $from_email, $from_name, $subject, $message);
		}
	}
	echo "<meta http-equiv=\"refresh\" content=\"0;url=index.php\">";
	exit();
}

if (isset($act) and $act == 'docNrs') {
	if (isset($doc_nrs) and isset($id) && !$amdb->get_row("SELECT * FROM doc_numbers where doc_nrs='$doc_nrs'")) {
		$result = MYSQL_QUERY("SELECT * FROM companies where clid='$id'");
		if (@MYSQL_NUM_ROWS($result) > 0) {
			$row = MYSQL_FETCH_ARRAY($result);
			$company_name = $row['company_name'];
			$amdb->query("insert into doc_numbers (clid,company_name,doc_nrs,sent_date) values ('$id','$company_name','$doc_nrs','$sent_date')");
		}
	}
	header("location: index.php");
	exit();
}

if (isset($act) and $act == 'aut') {
	MYSQL_QUERY("update hc_numbers set certificate_nr=certificate_nr+1");
	$result = MYSQL_QUERY("SELECT * FROM hc_numbers");
	if (@MYSQL_NUM_ROWS($result) > 0) {
		$row = MYSQL_FETCH_ARRAY($result);
		if ($tp == 'a')
			$t_number = 'HA00000';
		if ($tp == 'b')
			$t_number = 'HB00000';
		$certificate_nr = substr($t_number, 0, -strlen($row['certificate_nr']));
		$certificate_nr .= $row['certificate_nr'];
		MYSQL_QUERY("update certificates_$tp set hcd_process='Authorised: $date',signed_by='$sb', certificate_nr='$certificate_nr',issue_date='$issue' where nr='$nr'");
		echo "<meta http-equiv=\"refresh\" content=\"0;url=index.php\">";
		exit();
	}
}

//=print ok or failed===========================================================
if (isset($act) and $act == 'printok') {
	MYSQL_QUERY("UPDATE certificates_$tp set printed_on='$sent_date',doc_nr='$doc_nr',hcd_process='Sent on: $sent_date' where nr='$nr'");
	echo "<meta http-equiv=\"refresh\" content=\"0;url=index.php\">";
	exit();
}

//delete clients ================================================================
if (isset($act) and $act == 'delclient') {
	if (!isset($_POST['clid']) && count($_POST['clid']) == 0) {
		return;
		$clid = $_GET['clid'];
	}

	foreach ($_POST['clid'] as $clid) {
		$user = array('active' => 'n', 'status' => 'deleted');
		$amdb->update("users", $user, "clid='$clid'");
		$amdb->update("companies", $user, "clid='$clid'");
	}

	echo "<meta http-equiv=\"refresh\" content=\"0;url=index.php\">";
	exit();
}

//delete new client ================================================================
if (isset($_GET['act']) and $_GET['act'] == 'delNew' && isset($_GET['id'])) {
	$clid = $_GET['id'];
	$user = array('active' => 'n', 'status' => 'deleted');
	$amdb->update("users", $user, "clid='$clid'");
	$amdb->update("companies", $user, "clid='$clid'");
	echo "<meta http-equiv=\"refresh\" content=\"0;url=index.php\">";
	exit();
}

//delete client ================================================================
if (isset($_POST['act']) and $_POST['act'] == 'delete' and isset($_POST['id'])) {
	$clid = $_GET['id'];
	$user = array('active' => 'n', 'status' => 'deleted');
	$amdb->update("users", $user, "clid='$clid'");
	$amdb->update("companies", $user, "clid='$clid'");
	echo "success";
	exit();
}

//delete cert ================================================================
if (isset($act) and $act == 'deleteCertificate') {
	$amdb->update("certificates_$tp", array('status' => 'deleted'), "nr='$nr'");
	echo "success";
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'delcer' and isset($_POST['nr']) and isset($_POST['tp'])) {
	$amdb->update("certificates_$_POST[tp]", array('status' => 'deleted'), "nr='$_POST[nr]'");
	echo "success";
	exit();
}

if (isset($act) and $act == 'badCer' and isset($nr)) {
	mysql_query("UPDATE certificates_$tp set is_bad='$goodBad' where nr='$nr'");
	echo "success";
	exit();
}
