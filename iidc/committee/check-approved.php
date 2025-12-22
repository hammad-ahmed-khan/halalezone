<?php
if (!isset($_REQUEST['act']) or !isset($_REQUEST['decid'])) {
    exit();
}

include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

function get_user_signature($comemid)
{
    global $prog_path;

    $image_file = '/data/DMC/signatures/' . $comemid . '_signature';

    $image_exts = array('.jpg', '.jpeg', '.png', '.svg');
    foreach ($image_exts as $ext) {

        if (file_exists($prog_path . $image_file . $ext)) {
            return $image_file . $ext;
        }
    }
    return '';
}

if ($decision = $amdb->get_row("SELECT sms_codes FROM  hqc_committee_decision WHERE decid='$_REQUEST[decid]'")) {
    $sms_codes = json_decode($decision['sms_codes'], true);
    $signatures = array();
    foreach ($sms_codes as $codeKey => $codeValue) {
        if (isset($codeValue['approved'])) {
            $signatures[$codeKey] = get_user_signature($codeKey);
        }
    };
    echo json_encode($signatures);
}
