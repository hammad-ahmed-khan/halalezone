<?php
if (!isset($_REQUEST['act']) or !isset($_REQUEST['comemid']) or !isset($_REQUEST['decid']))
    exit();

include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";


if ($_REQUEST['act'] == 'sendVerificationCode' && isset($_REQUEST['comemid'])) {
    if ($member = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE comemid = '$_REQUEST[comemid]'")) {
        if ($message = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='DMC-Verification'")) {

            include $prog_path . "/tools/mail/hqc_mail.inc.php";
            $member['code'] = $_SESSION['sms_code'];
            $email['to_email'] = $member['member_email'];
            $email['to_name'] = $member['member_name'];
            $email['subject'] = $message['email_subject'];
            $email['message'] = $message['email_body'];
            $email['from_email'] = $message['email_reply_address'];
            $email['from_name'] = $message['email_sender_name'];
            $email['reply_to'] = $message['email_reply_address'];

            foreach ($member as $key => $value) {
                $email['subject'] = str_replace('[' . $key . ']', $value, $email['subject']);
                $email['message'] = str_replace('[' . $key . ']', $value, $email['message']);
            }
            if (!hqc_mail($email['to_email'], $email['to_name'], $email['from_email'], $email['from_name'], $email['subject'], $email['message'])) {
                echo "error:Email sending failed!";
                exit();
            } else {
                echo "EmailSent";
            }
        }
    }
    exit();
}

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

//search in json field for a string and compare the code
if ($_REQUEST['act'] == 'verify_code' && isset($_REQUEST['comemid']) && isset($_REQUEST['code'])) {
    if (!isset($_SESSION['sms_code'])) {
        $_SESSION['sms_codes'] = get_sms_codes();
    }

    if ($_SESSION['sms_code'] == $_REQUEST['code'] or $_REQUEST['byAdmin'] == true) {
        $member = $amdb->get_row("SELECT * FROM `hqc_committee_decision` WHERE decid = $_REQUEST[decid]");
        $sms_codes = json_decode($member['sms_codes'], true);
        $my_code['code'] = $_REQUEST['code'];
        $my_code['approved'] = 'yes';
        $my_code['approved_on'] = date('Y-m-d H:i:s');
        $sms_codes[$_REQUEST['comemid']] = $my_code;
        $sms_codes = json_encode($sms_codes, JSON_UNESCAPED_UNICODE);
        $amdb->update('hqc_committee_decision', array('sms_codes' => $sms_codes), "decid='$_REQUEST[decid]'");
         echo get_user_signature($_REQUEST['comemid']) . '?tm=' . time();
        // echo 'SMSApproved';
    } else {
        echo 'ERROR';
    }
    exit();
}

if ($_REQUEST['act'] == 'approve_report' && isset($_REQUEST['comemid']) && isset($_REQUEST['decid'])) {

    $sql = "SELECT * FROM `hqc_committee_decision` WHERE FIND_IN_SET('$_REQUEST[comemid]',comemids) AND decid = '$_REQUEST[decid]' AND status='pending'";

    if ($member = $amdb->get_row($sql)) {
        $sms_codes = json_decode($member['sms_codes'], true);
        $my_code = $sms_codes[$_REQUEST['comemid']];
        $my_code['approved'] = 'yes';
        $my_code['approved_on'] = date('Y-m-d H:i:s');
        $sms_codes[$_REQUEST['comemid']] = $my_code;
        $sms_codes = json_encode($sms_codes, JSON_UNESCAPED_UNICODE);
        $amdb->update('hqc_committee_decision', array('sms_codes' => $sms_codes), "decid='$_REQUEST[decid]'");
        echo get_user_signature($_REQUEST['comemid']) . '?tm=' . time();
        // echo 'SMSApproved';
    } else {
        echo 'ERROR';
    }
    exit();
}
