<?php 
session_start();
if (!isset($_SESSION["username"])){exit();};

if (isset($_POST['act'])){
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";

if ($_POST['act']=="paid_by" and isset($_POST['crtNr'])){
	$result = mysql_query("update acms_halal_certificates set paid_by='FOC', invoice_nr='FOC' where crtNr='$_POST[crtNr]'");
	echo ($result==true)?"ok":"Error: could not save try later.";
}
}
?>