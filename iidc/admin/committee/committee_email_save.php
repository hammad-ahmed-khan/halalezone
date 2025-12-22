<?php
if (!isset($_POST['act'])) {
    exit();
}
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

$_POST['comemids'] = implode(',', $_POST['comemid']);

//create random code for each member
foreach ($_POST['comemid'] as $comemid) {
    //create random code with five digits
    $sms_codes[$comemid]['code'] = rand(10000, 99999);
}

$_POST['sms_codes'] = json_encode($sms_codes, JSON_UNESCAPED_UNICODE);

if($_POST['event_details']['location'] == 'Online' && isset($_POST['event_details']['zoom-link'])){
    $_POST['event_details']['zoom-link'] = 'https://us05web.zoom.us/j/86121130632?pwd=hpc5fhXWxGoFIQyjNXMB4hxSxQ6rkb.1';
    $_POST['email']['message'] = str_replace('[zoom-link]', '<a href="'.$_POST['event_details']['zoom-link'].'">click here to join online video meeting using zoom</a>', $_POST['email']['message']);
}

$_POST['event_details'] = json_encode($_POST['event_details']);
$_POST['email_message'] = serialize($_POST['email']);

if ($_POST['act'] == 'send_email') {
    if ($members = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE FIND_IN_SET(comemid,'$_POST[comemids]') AND status='active' ORDER BY member_name ASC")) {
        include $prog_path . "/tools/mail/hqc_mail.inc.php";
        foreach ($members as $member) {

            $email['to_email'] = $member['member_email'];
            $email['to_name'] = $member['member_name'];
            $email['subject'] = $_POST['email']['subject'];
            $email['message'] = $_POST['email']['message'];
            $email['from_email'] = $_POST['email']['from_email'];
            $email['from_name'] = $_POST['email']['from_name'];
            $email['reply_to'] = $_POST['email']['from_email'];
            foreach ($member as $key => $value) {
                $email['subject'] = str_replace('[' . $key . ']', $value, $email['subject']);
                $email['message'] = str_replace('[' . $key . ']', $value, $email['message']);
            }
            if (!hqc_mail($email['to_email'], $email['to_name'], $email['from_email'], $email['from_name'], $email['subject'], $email['message'])) {
                echo "error:Email sending failed!";
                exit();
            }
        }

        $amdb->insert('hqc_committee_decision', $_POST);
        $amdb->update("acms_halal_certificates", array('approved_by_dmc' => 'pending'), "crtNr='$_POST[crtNr]'");
        $amdb->post_results("meetingEmailResults('$_POST[crtNr]');top.closePopup();", 'function');
    }
}
