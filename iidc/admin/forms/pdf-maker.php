<?php
if (!session_id()) {session_start();}
include $_SESSION['hqc_path'].'/load.inc.php';

include dirname(__FILE__)."/forms.class.php";
$amform->view_form($_REQUEST['foid'],$_REQUEST,array(),'pdf');
?>