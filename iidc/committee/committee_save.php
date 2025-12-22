<?php
include "../check_user.inc.php";
include "$prog_path/admin/committee/committee_save.php";
exit();
if (!isset($_REQUEST['act']) or !isset($_REQUEST['comemid'])) {
    exit();
}
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

//sign in as committee member
if ($_REQUEST['act'] == 'signin_as' && isset($_REQUEST['comemid'])) {
    if ($member = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE comemid = '$_REQUEST[comemid]' AND status = 'active'")) {
        $data = $_SESSION;
        session_destroy();
        session_start();
        $_SESSION['office_user'] = $data;
        $_SESSION['comemid'] = $member['comemid'];
        $_SESSION['username'] = $member['username'];
        $_SESSION['user_type'] = 'committee_member';
        $_SESSION['hqc_title'] = $member['member_name'];
        $_SESSION['super_admin'] = $member['super_admin'];
        $_SESSION['member_offices'] = $member['member_offices'];
        echo "success";
        exit();
    }
    echo "error";
    exit();
}

//sign in as committee member
if ($_REQUEST['act'] == 'switch_back' && isset($_REQUEST['comemid']) && isset($_SESSION['office_user'])) {
    if ($member = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE comemid = '$_REQUEST[comemid]' AND status = 'active'")) {
        $data = $_SESSION['office_user'];
        session_destroy();
        session_start();
        $_SESSION = $data;
        header("Location: $_SESSION[user_url]");
    }
    exit();
}
