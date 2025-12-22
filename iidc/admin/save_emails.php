<?php
include "../config/paths.inc.php";
if(!isset($_POST['clid']) or !isset($_POST['email1']) or !isset($_POST['act']))
    exit();
if($_POST['act']== 'save'){
	if($amdb->query("UPDATE companies SET email1='$_POST[email1]', email2='$_POST[email2]' WHERE clid=$_POST[clid]")){
		echo "saved";
		exit();
	}
	else{
		echo "Error saving emails";
		exit();
	}
	exit();
}