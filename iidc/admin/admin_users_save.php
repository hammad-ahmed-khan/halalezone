<?php
/*--File name (hqc_admin_users_save.php)--*/
require("../config/paths.inc.php");
include_once("$prog_path/checkuser.inc.php");
include "$prog_path/config/mysql_ftp.inc.php";
include "$prog_path/config/connect.inc.php";
if (isset($_POST['permissions']) and count($_POST['permissions'])>0)
$_POST['permissions'] = '"'.implode('","',array_keys($_POST['permissions'])).'"';
else
$_POST['permissions']="";

if (!isset($_POST['clients_allowed']))
$_POST['clients_allowed'] = '';
/*--Insert a record--*/
if (isset($_POST['act']) and $_POST['act']=="add"){
$_POST['uid'] = $amdb->insert("hqc_admin_users",$_POST);
}
/*--End insert a record--*/
/*--Update a record--*/
if (isset($_POST['act']) and $_POST['act']=="edit"){
	$_POST['permissions'] = $_POST['permissions'];
$amdb->update("hqc_admin_users",$_POST," uid = '$_POST[uid]'");
		$permissions = "<?php
		if (!defined(\"_HQC_\")){exit();}
		\$user_permissions = array($_POST[permissions]);
		\$user_clients = array($_POST[clients_allowed]);
		?>";
		$hqc_user_file = "$prog_path/hqc_users/".$_POST['username'].".usr.php";
		if (file_exists($hqc_user_file))
		unlink($hqc_user_file);
		$fl = fopen($hqc_user_file,"w");
		fwrite($fl,$permissions);
		fclose($fl);
}
/*--End update a record--*/
////TODO: add switching possibilities to new system
if (isset($_POST['account'])) {
	$joined['id_type'] = 'uid';
	$joined['uid'] = $_POST['uid'];
	$joined['accounts'] = implode(',', $_POST['account']);
	if(!$amdb->update("hqc_joined_accounts",$joined,"uid = '$_POST[uid]'  AND id_type = 'uid'"))
	$amdb->insert('hqc_joined_accounts',$joined);
} else {
	$amdb->query("DELETE FROM hqc_joined_accounts WHERE uid = '$_POST[uid]'  AND id_type = 'uid'");
}

/*--Delete a record--*/
if (isset($_POST['act']) and $_POST['act']=="delete"){
MYSQL_QUERY("DELETE from hqc_admin_users where  uid = $_POST[uid]");
}
/*--End delete a record--*/
/*--Activate a record--*/
if (isset($_POST['act']) and $_POST['act']=="activate"){
MYSQL_QUERY("UPDATE hqc_admin_users SET active='$active' where  uid = $_POST[uid]");
}
/*--End Activate a record--*/
$usrs = $amdb->get_results("select * from hqc_admin_users");
if (isset($usrs) and count($usrs) > 0){
	$usr = array();
	foreach($usrs as $row){
		$usr[$row['uid']] = array('username'=>$row['username'],
			'user_name'=>$row['username_owner'],
			'active'=>$row['active']);
	}
	$admin_users = "<?php\n\$admin_users='".str_replace("'","\'",json_encode($usr))."'\n?>";
	$usrFl = "$prog_path/hqc_users/admin_users.inc.php";
	$fl = fopen($usrFl,"w");
	fwrite($fl,$admin_users);
	fclose($fl);
}
if (isset($_REQUEST['goback']) && $_REQUEST['goback']=='auditors')
echo"<meta http-equiv='refresh' content='0;url=index.php?inc=admin_users&type=auditor'>";
else
echo"<meta http-equiv='refresh' content='0;url=index.php?inc=admin_users'>";
?>