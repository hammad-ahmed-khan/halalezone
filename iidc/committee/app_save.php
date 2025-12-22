<?php
if (!isset($_POST['act']) or !isset($_POST['decid'])) {
    exit();
}
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($_POST['act'] == 'delete') {

    if ($dec = $amdb->get_row("SELECT * FROM hqc_committee_decision WHERE decid='$_POST[decid]'")) {
        $amdb->update("hqc_committee_decision", array('status' => 'deleted', 'dmr_reference' => ''), "decid='$_POST[decid]'");
    }
    echo ('success');
    exit();
}

if ($_POST['act'] == 'resetDMCProcess') {

    if ($dec = $amdb->get_row("SELECT * FROM hqc_committee_decision WHERE decid='$_POST[decid]'")) {
        $amdb->update("acms_halal_certificates", array('approved_by_dmc' => 'no'), "crtNr='$dec[crtNr]'");
        $amdb->update("hqc_committee_decision", array('status' => $_POST['status'], 'dmr_reference'=>''), "decid='$_POST[decid]'");
    }
    echo ('success');
    exit();
}

if (isset($_POST['saveMemo']) && $_POST['saveMemo'] == 'yes' && isset($_POST['decid']) && isset($_POST['internal_memo'])) {
    //read  the internal memo from the database and add the new memo to the array
    if ($memo = $amdb->get_row("SELECT internal_memo FROM hqc_committee_decision WHERE decid='$_POST[decid]'")) {
        if (trim($memo['internal_memo']) != '' and is_array(unserialize($memo['internal_memo'])))
            $memo = unserialize($memo['internal_memo']);
        else
            $memo = array();
    } else {
        $memo = array();
    }
    //add the new memo to the array
    $memo[$_SESSION['comemid']] = $_POST['internal_memo'];
    $amdb->update("hqc_committee_decision", array('internal_memo' => serialize($memo)), "decid='$_POST[decid]'");
    echo ('success');
    exit();
}

if ($_POST['act'] == 'save') {

    $decision['decision'] = serialize($_POST);
    if (isset($_POST['agree'])) {
        $decision['status'] = 'approved';
    } else {
        $decision['status'] = 'pending';
        if ($dec = $amdb->get_row("SELECT * FROM hqc_committee_decision WHERE decid='$_POST[decid]'")) {


            if (trim($dec['internal_memo']) != '' and is_array(unserialize($dec['internal_memo'])))
                $memo = unserialize($dec['internal_memo']);
            else
                $memo = array();

            if (trim($_POST['internal_memo']) == '') {
                if (isset($memo[$_SESSION['comemid']]))
                    unset($memo[$_SESSION['comemid']]);
            } else {
                $memo[$_SESSION['comemid']] = trim($_POST['internal_memo']);
            }

            if (count($memo) > 0)
                $decision['internal_memo'] = serialize($memo);
            else
                $decision['internal_memo'] = '';
        }
    }
    $decision['dmr_reference'] = $_POST['dmr_reference'];

    $amdb->update("hqc_committee_decision", $decision, "decid='$_POST[decid]'");
    if ($decision['status'] == 'approved') {
        $amdb->update("acms_halal_certificates", array('approved_by_dmc' => 'yes'), "crtNr='$dec[crtNr]'");
        if ($dec = $amdb->get_row("SELECT * FROM hqc_committee_decision WHERE decid='$_POST[decid]'")) {
            if ($newCer = $amdb->get_row("SELECT * FROM hqc_committee_decision JOIN `acms_halal_certificates` ON hqc_committee_decision.crtNr = acms_halal_certificates.crtNr WHERE hqc_committee_decision.comemids='$dec[comemids]' AND hqc_committee_decision.status = 'pending'")) {
                post_this_results('/committee/?inc=app&act=edit&clid=' . $newCer['clid'] . '&crtNr=' . $newCer['crtNr'] . '&decid=' . $newCer['decid'], 'url');
            }
        }
    }
      header('location: /committee/');
}
