<?php
if (!session_id())
session_start();
if (!isset($_SESSION['username'])){exit();};
if (isset($_SERVER['HTTP_REFERER']))
{
	$theRefHost = parse_url(str_replace('www.','',$_SERVER['HTTP_REFERER']));
	$theHost = str_replace('www.','',$_SERVER['HTTP_HOST']);
	if($theHost != $theRefHost['host']){
	exit();
	}
}

if(!defined("_HQC_"))
define("_HQC_",true);
if(!defined("__HQC__"))
define("__HQC__",true);
include dirname(__FILE__)."/config/paths.inc.php";

extract($_REQUEST);

date_default_timezone_set(date_default_timezone_get());
date_default_timezone_get();

$username = $_SESSION['username'];
if(file_exists("$prog_path/hqc_users/$username.usr.php")){
ob_start();
include "$prog_path/hqc_users/$username.usr.php";
ob_clean();
}
$userGoBackDir = "$prog_www/$home_dir/".(isset($_SESSION['uid'])?"admin":"company");
?>