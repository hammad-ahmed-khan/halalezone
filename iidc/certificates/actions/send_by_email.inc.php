<?php
if(isset($_GET['crtSent'])&& isset($_GET['clid'])){
    include "../../config/paths.inc.php";
    $client = get_client($_GET['clid']);
    echo "<div style=\"padding:50px;\">Annual Certificate sent to $client[contact_name] <br/>$client[company_name]</div>";
    exit();
}

if (!defined("__HQC__"))
    define("__HQC__", true);

if (isset($future_action)) {

    $future = $future_action;
    $crtNr = $future['action_id'];
    $action_data = json_decode($future['action_data'], true);

    $certFilesDir = $hcp_path . "/client_data/certificates";

    //$certificate['url'] = $certFilesDir . "/" . $certificate['url'];
    if ($client = get_client($future['clid'])) {
        $client_data['company_name'] = $client['company_name'];
        $client_data['client_name'] = $client['contact_name'];
        $client_data['client_email'] = $action_data['email'];
        $client_data['company_address'] = $client['company_address'];
        $client_data['certificate_nr'] = $action_data['certificate_nr'];
        if ($email_message = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name = 'scheduled_annual_certificate'")) {
            if ($email_footer = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name = 'email_footer'")) {
                $email_message['email_body'] = str_replace("[email_footer]", $email_footer['email_body'], $email_message['email_body']);
            }
            foreach ($client_data as $key => $value) {
                $email_message['email_subject'] = str_replace("[" . $key . "]", $value, $email_message['email_subject']);
                $email_message['email_body'] = str_replace("[" . $key . "]", $value, $email_message['email_body']);
            }

            if (isset($action_data['bcc']) && $action_data['bcc'] != '') {
                $_POST['bcc_email'][] = $action_data['bcc'];
            }

            if (!isset($action_data['file']) and isset($certFile))
                $action_data['file'] = $certFile;

            include $prog_path . "/tools/mail/hqc_mail.inc.php";
            if (hqc_mail(
                $client_data['client_email'],
                $client_data['client_name'],
                $email_message['email_reply_address'],
                $email_message['email_sender_name'],
                $email_message['email_subject'],
                $email_message['email_body'],
                array('Annual Certificate' => $action_data['file']),
                true
            )) {
                $amdb->query("update acms_halal_certificates set status_sent_on='" . time() . "', stsus='sentByEmail' where  crtNr = '$future[action_id]'");
                header("location: /certificates/actions/send_by_email.inc.php?crtSent=$crtNr&clid=$clid");
            } else {
                echo "Email-failed";
            }
        }
    }
}
