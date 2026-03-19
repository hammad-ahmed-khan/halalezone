<?php
if (!session_id()) {
    session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';

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

    $hqcdb->query("UPDATE hqc_certificates_annual SET qr_status = '$qr_status' WHERE crtNr = '$_POST[crtNr]'");
    post_results('function', 'jQuery("#qr_' . $_POST['crtNr'] . '").css("color","' . $qr_color . '");closePopup()');
}
