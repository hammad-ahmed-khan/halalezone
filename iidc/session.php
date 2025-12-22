<?php
session_start();
if(!isset($_REQUEST['act']))
return;

 if($_REQUEST['act']=='set' and isset($_REQUEST['url']) and isset($_REQUEST['key'])){
	 foreach($_SESSION as $k=>$v){
		 if(strstr($k,'goBack_url') && strstr($v,$_REQUEST['url'])){
		 	unset($_SESSION[$k]);
		 }
	 }
	 
 	$_SESSION[$_REQUEST['key']] = $_REQUEST['url'];
	echo $_REQUEST['key'];
 } elseif(isset($_REQUEST['key'])){
 	unset($_SESSION[$_REQUEST['key']]);
 }
?>