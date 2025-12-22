<?php
include "../../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if (isset($_POST['crtNr']) and isset($_POST['qr_status']) and is_array($_POST['qr_status'])) {
    $qr_color = 'green';
    $qr_status = $_POST['qr_status'];

    if ($qr_status['status'] != 'active') {
        if ($qr_status['status'] == 'paused')
            $qr_color = 'orange';
        else
            $qr_color = '#800';
    }

    if ($qr_status['status'] == 'active' or $qr_status['status'] == 'expired')
        $qr_status = '';
    else
        $qr_status = json_encode($_POST['qr_status'], JSON_UNESCAPED_UNICODE);

    if (isset($_REQUEST['tp'])) {
        $table = "certificates_" . $_REQUEST['tp'];
        $amdb->query("UPDATE $table SET qr_status='$qr_status' WHERE nr='$_POST[crtNr]'");
    } else {
        $amdb->query("UPDATE acms_halal_certificates SET qr_status='$qr_status' WHERE crtNr='$_POST[crtNr]'");
    }
    $amdb->post_results('jQuery("#qr_' . $_POST['crtNr'] . '").css("color","' . $qr_color . '")', 'function');
}

$amdb->close_popup();
