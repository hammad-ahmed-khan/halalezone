<?php
if (!defined("_HQC_")) {
     exit();
};

$today = date('Y-m-d');
function create_session()
{
    global $amdb, $today;
    //get member ids from the table hqc_committee_members
    $members = $amdb->get_results("SELECT comemid FROM `hqc_committee_members` WHERE `status` = 'active'");
    //create random code for each member
    $sms_codes = array();
    foreach ($members as $member) {
        //create random code with five digits
        $sms_codes[$member['comemid']] = array('code' => rand(10000, 99999));
    }

    $sms_codes = json_encode($sms_codes, JSON_UNESCAPED_UNICODE);
    //create a session for today
    $amdb->query("INSERT INTO `hqc_committee_sessions` (`session_day`, `sms_codes`) VALUES ('$today', '$sms_codes')");
    return array('sms_codes' => $sms_codes);
}

function get_sms_codes()
{
    global $amdb, $today;
    //check if there is a session registered for today in the table hqc_committee_sessions
    if (!$today_session = $amdb->get_row("SELECT * FROM `hqc_committee_sessions` WHERE `session_day` = '$today'")) {
        $today_session = create_session();
    }
    $sms_codes = json_decode($today_session['sms_codes'], true);
    $_SESSION['sms_code'] = $sms_codes[$_SESSION['comemid']]['code'];
    return $sms_codes;
}