<?php
//show php errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_POST['act'])) {
    exit();
}

include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

$_POST['comemids'] = implode(',', $_POST['comemids']);

//create random code for each member
// foreach ($_POST['comemid'] as $comemid) {
//     //create random code with five digits
//     $sms_codes[$comemid] = array('code' => '');
// }

// $_POST['sms_codes'] = json_encode($sms_codes, JSON_UNESCAPED_UNICODE);

if ($_POST['event_details']['location'] == 'Online') {

    $zoom_topic = 'DCM Meeting';

    $meetingData = array(
        'topic' => $zoom_topic,
        'date' => $_POST['event_details']['date'],
        'time' => $_POST['event_details']['time']
    );
    if (isset($_POST['decid']) && isset($_POST['useOldZoomLink']) && isset($_POST['oldZoomLink']) && trim($_POST['oldZoomLink']) != '') {
        $joinLink = $_POST['oldZoomLink'];
    } else {
        if (!is_local()) {
            include "zoom.inc.php";
            $joinLink =  make_meeting_link($meetingData);
        } else {
            $joinLink = 'Error';
        }
    }
    if ($joinLink != 'Error')
        $_POST['event_details']['zoom-link'] = $joinLink;
    else
        $_POST['event_details']['zoom-link'] = '';

    $_POST['email']['message'] = str_replace('[zoom-link]', '<a href="' . $_POST['event_details']['zoom-link'] . '">click here to join online video meeting using zoom</a>', $_POST['email']['message']);
}

$_POST['meeting_date'] = $_POST['event_details']['date'] . ' ' . $_POST['event_details']['time'] . ':00';
$_POST['event_details']['request_by'] = $_SESSION['hqc_title'];
$_POST['event_details'] = json_encode($_POST['event_details']);
$_POST['email_message'] = serialize($_POST['email']);
$_POST['branch'] = json_encode($_POST['branch']);

// if (!$hoc = $amdb->get_row("SELECT member_name FROM hqc_committee_members WHERE comemid = '$_POST[hoc]'"))
//     return;

if ($members = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE FIND_IN_SET(comemid,'$_POST[comemids]') AND status='active' ORDER BY member_name ASC")) {

    include $prog_path . "/tools/mail/hqc_mail.inc.php";

    foreach ($members as $member) {
        //   $member['HOC'] = $hoc['member_name'];
        $email['to_email'] = $member['member_email'];
        $email['to_name'] = $member['member_name'];
        $email['subject'] = $_POST['email']['subject'];
        $email['message'] = $_POST['email']['message'];
        $email['from_email'] = $_POST['email']['from_email'];
        $email['from_name'] = $_POST['email']['from_name'];
        $email['reply_to'] = $_POST['email']['from_email'];
        if (isset($_POST['sendTestEmail']) && trim($_POST['testEmailTo']) != '')
            $email['to_email'] = $_POST['testEmailTo'];
        // $email['to_email'] = 'najati@ayoub.nl';
        foreach ($member as $key => $value) {
            $email['subject'] = str_replace('[' . $key . ']', $value, $email['subject']);
            $email['message'] = str_replace('[' . $key . ']', $value, $email['message']);
        }

        if (!isset($_POST['noEmail'])) {
            if (!hqc_mail($email['to_email'], $email['to_name'], $email['from_email'], $email['from_name'], $email['subject'], $email['message'])) {
                echo "error:Email sending failed!";
                exit();
            }
        }

        if (isset($_POST['sendTestEmail']) && trim($_POST['testEmailTo']) != '') {
            post_this_results('Test email is sent', 'alert');
            exit();
        }
    }

    if ($_POST['act'] == 'send_email') {
        // $crtNrs = explode(',', $_POST['crtNr']);
        foreach ($_POST['clids'] as $clid) {
            $_POST['clid'] = $clid;
            $_POST['decid'] = $amdb->insert('hqc_committee_decision', $_POST);
        }
    } elseif (isset($_POST['decid'])) {
        $amdb->update('hqc_committee_decision', $_POST, "decid='$_POST[decid]'");
    };
    if (isset($_POST['clid']) && isset($_POST['decid']) && isset($_POST['ref'])) {
        echo '<script>parent.location.href = "/committee/dmc/?inc=dmc&ref=list&ifr=1&noResize=1&decid=' . $_POST['decid'] . '";</script>';
    } else {
        post_this_results('/committee/', 'url');
    }
}
