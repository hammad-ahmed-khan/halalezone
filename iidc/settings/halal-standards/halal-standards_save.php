<?php
if (!isset($_REQUEST['act'])) {
    exit();
};
include "../../checkuser.inc.php";
include "../../config/paths.inc.php";

if ($_POST['act'] == 'update') {
    if ($_POST['type'] == 'standard')
        $amdb->update("hqc_halal_standards", $_POST, "stnid = '$_POST[stnid]'");
    else
        $amdb->update("hqc_normative_references", $_POST, "normid = '$_POST[normid]'");
} else {
    if ($_POST['type'] == 'standard')
        $amdb->insert("hqc_halal_standards", $_POST);
    else
        $amdb->insert("hqc_normative_references", $_POST);
}
$amdb->post_results('index.php', 'url');