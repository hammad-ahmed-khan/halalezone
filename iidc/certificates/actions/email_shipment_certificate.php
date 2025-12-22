<?php
if (!defined("_HQC_")) exit();

if (isset($_REQUEST['sub_act']) && $_REQUEST['sub_act'] == 'email' && isset($_REQUEST['to_email']) && isset($_REQUEST['nr']) && $_REQUEST['nr'] != '') {

    $email_data = array();
    $email_data['certificate_nr'] = $_REQUEST['certificate_nr'];
    if (!isset($_REQUEST['to_name'])) {
        $client = get_client(intval($_REQUEST['clid']));
        $_REQUEST['to_name'] = $client['company_name'];

        $client_name = trim($client['contact_title1']) != '' ? $client['contact_title1'] . '' : '';
        $client_name .= trim($client['contact_name1']) != '' ? ' ' . $client['contact_name1'] : '';
        $client_name .= trim($client['contact_surname1']) != '' ? ' ' . $client['contact_surname1'] : '';
        $email_data['client_name'] = trim($client_name) != '' ? $client_name : $client['company_name'];
    }

    if (!isset($_REQUEST['from_name'])) {
        $office = get_office_data($_SESSION['offid']);
        $_REQUEST['from_name'] = $office['company_name_english'];
        $email_data['contact_person'] = $office['contact_person'];
    }

    if (!isset($_REQUEST['subject'])) {
        if ($emailMessage = $row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='shipment-certificate'")) {
            $_REQUEST['subject'] = $emailMessage['email_subject'];
            $_REQUEST['message'] = $emailMessage['email_body'];
        }
    }

    foreach ($email_data as $key => $value) {
        $_REQUEST['subject'] = str_replace('[' . $key . ']', $value, $_REQUEST['subject']);
        $_REQUEST['message'] = str_replace('[' . $key . ']', $value, $_REQUEST['message']);
    }

    include $prog_path . "/tools/mail/hqc_mail.inc.php";

    if (hqc_mail(
        $_REQUEST['to_email'],
        $_REQUEST['to_name'],
        'info@iidc.eu',
        $_REQUEST['from_name'],
        $_REQUEST['subject'],
        $_REQUEST['message'],
        array('Certificate (' . $pdf_data['certificate_nr'] . ')' => str_replace('\\', '/', $certFile))
    )) {
        $sentByEmail = array();
        $sentByEmail['sent_on'] = date("Y-m-d H:i:s");
        if (isset($_SESSION['user_type'])) {
            $sentByEmail['sent_by_type'] = $_SESSION['user_type'];
            $sentByEmail['offid'] = $_SESSION['offid'];
            $sentByEmail['sent_by'] = $_SESSION['hqc_title'];
        }

        $sentByEmail['sent_to_email'] = $_REQUEST['to_email'];
        $sentByEmail['sent_to_name'] = $_REQUEST['to_name'];
        $amdb->update('certificates_' . $tp, array('sent_by_email' => json_encode($sentByEmail)), "nr = '$_REQUEST[nr]'");
        if (isset($_REQUEST['popup_act'])) {
            $amdb->post_results('top.closePopup();removeCertificateFromList(' . $_REQUEST['nr'] . ')', 'function');
            $amdb->post_results('Certificate sent by email to: ' . $_REQUEST['to_name']);
        } else {
            echo "<script>window.close();</script>";
        }
    } else {
        $amdb->post_results('top.closePopup()', 'function');
        $amdb->post_results('Error sending email. Please try again later.<br/>If the problem persists, please contact the system administrator.');
    }
}
