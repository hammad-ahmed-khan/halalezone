<?php
if (!session_id()) {
	session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';
include admin_path . "/forms/forms.class.php";

$amform->view_form($_REQUEST['foid'], $_GET, array(), 'pdf');
