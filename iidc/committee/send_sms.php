<?php
if (!isset($_REQUEST['act']) or !isset($_REQUEST['comemid']) or !isset($_REQUEST['decid']))
    exit();

include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
//find a record with id in comma separated list of committee members

$sql = "SELECT * FROM `hqc_committee_decision` WHERE FIND_IN_SET('$_REQUEST[comemid]' ,comemids) AND decid = '$_REQUEST[decid]'";
if ($decision = $amdb->get_row($sql)) {
    $sms_codes = json_decode($decision['sms_codes'], true);
    $my_code = $sms_codes[$_REQUEST['comemid']];
} else {
    exit();
}

//sending sms message
if ($_REQUEST['act'] == 'send_sms') {
    $my_code['sent'] = 'yes';
    $my_code['sent_on'] = date('Y-m-d H:i:s');
    $sms_codes[$_REQUEST['comemid']] = $my_code;
    $sms_codes = json_encode($sms_codes, JSON_UNESCAPED_UNICODE);

    if ($member = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE comemid='$_REQUEST[comemid]' AND status='active'")) {
        $member_mobile_phone = $member['member_mobile_phone'];
        include "sms/send_sms.inc.php";
        if ($res = sendSMS($member_mobile_phone, 'Your code for committee meeting: ' . $my_code['code'])) {
           $amdb->update('hqc_committee_decision', array('sms_codes' => $sms_codes), "decid='$_REQUEST[decid]'");
            echo 'SMSSent';
        } else {
            echo 'SMS could not be sent!';
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
if ($_REQUEST['act'] == 'approve_sms' && isset($_REQUEST['comemid']) && isset($_REQUEST['code'])) {

    $sql = "SELECT * FROM `hqc_committee_decision` WHERE FIND_IN_SET('$_REQUEST[comemid]',comemids) AND JSON_VALID(sms_codes) = 1 AND sms_codes LIKE '%\"code\": $_REQUEST[code]%' AND status='pending'";

    if ($member = $amdb->get_row($sql)) {
        $sms_codes = json_decode($member['sms_codes'], true);
        $my_code = $sms_codes[$_REQUEST['comemid']];
        $my_code['approved'] = 'yes';
        $my_code['approved_on'] = date('Y-m-d H:i:s');
        $sms_codes[$_REQUEST['comemid']] = $my_code;
        $sms_codes = json_encode($sms_codes, JSON_UNESCAPED_UNICODE);
        $amdb->update('hqc_committee_decision', array('sms_codes' => $sms_codes), "decid='$member[decid]'");
        $_SESSION['sms_code'] = $_REQUEST['code'];
       echo get_user_signature($_REQUEST['comemid']).'?tm='.time();
       // echo 'SMSApproved';
    } else {
        echo 'ERROR';}
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
