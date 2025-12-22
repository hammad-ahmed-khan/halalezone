<?php
if (!isset($_POST['expense']) or !is_array($_POST['expense']) or count($_POST['expense']) == 0 or !isset($_POST['act']) or $_POST['act'] != 'save_expense') {
    exit();
}
define("__HQC__", true);
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
$expenses = json_encode($_POST['expense']);

if (update_option('expense_type', $expenses)) {
    post_this_results('Default expenses saved.');
} else {
    post_this_results('Failed to save expenses!');
}
