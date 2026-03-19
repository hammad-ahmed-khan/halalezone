<?php
if (!session_id()) {
    session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';
//get a lis of active forms
if ($forms = $hqcdb->get_results("SELECT foid,form_name FROM hqc_forms WHERE status = 'active'")) {
    $data['success'] = true;
    $data['data']= $forms;
    echo json_encode($data);
}