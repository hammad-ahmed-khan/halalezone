<?php
if (!session_id()) {session_start();}
include $_SESSION['hqc_path'].'/load.inc.php';
include dirname(__FILE__)."/../forms.class.php";
if($data = $hqcdb->get_row("SELECT * FROM hqc_forms_test WHERE tstid='$_GET[tstid]'")){
    $data['date_of_application'] = date ("d F Y",strtotime(fix_date($data['inserted_on'])));
$data = json_decode($data['form_content'],true);
$amform->view_form($_REQUEST['foid'],$data,array(),'pdf');
}
?>