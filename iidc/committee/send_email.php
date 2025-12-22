<?php
include "../check_user.inc.php";
if (!isset($_SESSION['username']) or !isset($_REQUEST['act']))
    exit();

include "$prog_path/config/connect.inc.php";

if (!isset($_POST['act']) or $_POST['act'] != 'sendEmail') {
    exit();
}

$_POST['comemids'] = implode(',', $_POST['comemid']);
$_POST['email_message'] = serialize($_POST['email']);

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
    post_this_results('top.closePopup();', 'function');
}
