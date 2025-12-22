<?php
if (!isset($_POST['uid']) or !isset($_POST['act'])) {
    exit();
}
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($_POST['act'] == 'update_evaluation') {
    $evaluation = serialize($_POST['evaluation']);
    if ($amdb->update("hqc_admin_users", array("evaluation" => $evaluation), "uid='$_POST[uid]'")) {
        post_this_results('index.php?inc=admin_users&type=auditor', 'url');
    }
    exit();
}

if ($_POST['act'] == 'remove_evaluation') {
    $evaluation = '';
    if ($amdb->update("hqc_admin_users", array("evaluation" => $evaluation), "uid='$_POST[uid]'")) {
        echo 'success';
    } else {
        echo "Error deleting evaluation!";
    }
    exit();
}
