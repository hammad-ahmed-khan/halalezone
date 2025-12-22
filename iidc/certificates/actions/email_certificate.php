<?php
include "../../check_user.inc.php";
include "../../config/paths.inc.php";
if (isset($_POST['act']) && $_POST['act'] == 'resendCertificate' && isset($_POST['email']) && is_array($_POST['email']) &&  isset($_POST['crtNr']) && $_POST['crtNr'] != '') {
    $email_data = $_POST['email'];

    if (isset($_POST['bcc']) && trim($_POST['bcc']) != '') {
        $_POST['bcc_email'][] = trim($_POST['bcc']);
    }
    include $prog_path . "/tools/mail/hqc_mail.inc.php";
    if (hqc_mail(
        $email_data['to_email'],
        $email_data['to_name'],
        $email_data['from_email'],
        $email_data['from_name'],
        $email_data['subject'],
        $email_data['message'],
        array('Annual Certificate' => $email_data['certificateFile']),
        true
    )) {
        $amdb->query("update acms_halal_certificates set status_sent_on='" . time() . "' where  crtNr = '$_POST[crtNr]'");
        $amdb->post_results('top.closePopup()', 'function');
        $amdb->post_results('Certificate sent by email to: ' . $email_data['to_name']);
    } else {
        $amdb->post_results('top.closePopup()', 'function');
        $amdb->post_results('Error sending email. Please try again later.<br/>If the problem persists, please contact the system administrator.');
    }
}
