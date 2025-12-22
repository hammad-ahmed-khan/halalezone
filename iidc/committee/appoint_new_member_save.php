<?php
if (!isset($_POST['act']) or !isset($_POST['decid']) or !isset($_POST['comemid'])) {
    exit();
}

include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($_POST['act'] == 'appoint_new_member') {
    if ($meeting = $amdb->get_row("SELECT * FROM hqc_committee_decision WHERE decid='$_REQUEST[decid]'")) {
        $comemids = array_diff(explode(',', $meeting['comemids']), array($_POST['old_comemid']));
        $comemids[] = $_POST['comemid'];
        $comemids = implode(',', $comemids);
        if($_POST['old_comemid'] == $_POST['hoc'])
        $hoc = $_POST['comemid'];
    else
        $hoc = $meeting['hoc'];
        $amdb->update("hqc_committee_decision", array('comemids' => $comemids,'hoc'=>$hoc), "decid='$_REQUEST[decid]'");
        post_this_results('/committee/', 'url');
    }
    exit();
}
