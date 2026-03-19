<?php
if (!session_id()) {
    session_start();
}
include($_SESSION['hqc_path'] . '/load.inc.php');

if (!isset($_POST['act']))
    return;
if ($_POST['act'] == 'reorder') {

    foreach ($_POST['pos'] as $pos => $foid) {
        $hqcdb->update("hqc_forms", array("pos" => $pos), "foid='$foid'");
    }
    echo "success";
    exit();
}
