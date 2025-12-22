<?php
define("__HQC__", true);
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
if ($_SESSION['user_role'] != "super_admin") {
    exit;
}

if (!isset($_POST['act']) or !isset($_POST['office']) or !is_array($_POST['office'])) {
    exit;
}

if ($_POST['act'] == 'update') {
    foreach ($_POST['office'] as $offid => $data) {
        $amdb->update("hqc_invoice_nrs", $data, "offid = $offid");
    }
    $amdb->post_results('Invoice numbers updated successfully');
    exit();
}