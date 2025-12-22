<?php
if (!isset($_POST['comemid']) or !isset($_POST['act'])) {
    exit();
}
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($_POST['act'] == 'update_evaluation') {
    $evaluation = serialize($_POST['evaluation']);
    if ($amdb->update("hqc_committee_members", array("evaluation" => $evaluation), "comemid='$_POST[comemid]'")) {
        post_this_results('index.php?inc=committee', 'url');
    }
    exit();
}

if ($_POST['act'] == 'remove_evaluation') {
    $evaluation = '';
    if ($amdb->update("hqc_committee_members", array("evaluation" => $evaluation), "comemid='$_POST[comemid]'")) {
        echo 'success';
    }
    else {
        echo "Error deleting evaluation!";
    }
    exit();
}
