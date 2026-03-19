<?php
if (!isset($_POST['act'])) {
    exit();
}
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

//move uploaded signature file to the right folder
function upload_signature($comemid)
{
    global $prog_path, $amdb;
    $exTypes = array("jpg", "jpeg", "png", "svg");
    if ($_FILES['signature']['name'] and trim($_FILES['signature']['name']) != '' && isset($_POST['comemid'])) {
        $filesDir = $prog_path . "/data/DMC/signatures";
        if (!is_dir($filesDir))
            mkdir($filesDir, 0777, true);
        delete_signature($comemid);
        $name = $_FILES['signature']['name'];
        $uploads = $_FILES['signature']['tmp_name'];
        if (in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), $exTypes)) {
            move_uploaded_file($uploads, $filesDir . '/' . $comemid . '_signature' . '.' . pathinfo($name, PATHINFO_EXTENSION));
        }
    }
}

function delete_signature($comemid){
    global $prog_path;
    $image_file = $prog_path . '/data/DMC/signatures/' . $comemid . '_signature';
    $image_exts = array('.jpg', '.jpeg', '.png', '.svg');
    foreach ($image_exts as $ext) {
        if (file_exists($image_file . $ext))
            unlink($image_file . $ext);
    }
}
//delete signature file
if ($_POST['act'] == 'delete_signature' && isset($_POST['comemid'])) {
    delete_signature($_POST['comemid']);
    echo "success";
    exit();
}

if ($_POST['member_function'] == 'other' && trim($_POST['member_function_other']) != '')
    $_POST['member_function'] = $_POST['member_function_other'];

if($_POST['comDir']=='admin'){
if (isset($_POST['member_office']) && is_array($_POST['member_office'])) {
    $_POST['member_offices'] = implode(',', $_POST['member_office']);
} else {
    $_POST['member_offices'] = '';
}
}

//add new committee member
if ($_POST['act'] == 'add_committee_member') {
    $comemid = $amdb->insert('hqc_committee_members', $_POST);
    upload_signature($comemid);
    echo "url:/iidc/admin/committee/";
    exit();
}

if (isset($_POST['username']) && !strstr($_POST['username'], 'co-'))
    $_POST['username'] = 'co-' . $_POST['username'];

//update committee member
if ($_POST['act'] == 'update_committee_member' && isset($_POST['comemid'])) {

    $amdb->update('hqc_committee_members', $_POST, "comemid = $_POST[comemid]");
    upload_signature($_POST['comemid']);
    if (isset($_POST['comDir']) && $_POST['comDir'] == 'committee')
        echo "url:/iidc/committee/?inc=account";
    else
        echo "url:/iidc/admin/committee/";
    exit();
}

//delete committee member
if ($_POST['act'] == 'delete_committee_member' && isset($_POST['comemid'])) {
    $amdb->update('hqc_committee_members', array('status' => 'deleted'), "comemid = $_POST[comemid]");
    echo 'success';
    exit();
}

//change committee member status
if ($_POST['act'] == 'change_committee_member_status' && isset($_POST['comemid'])) {
    $amdb->update('hqc_committee_members', array('status' => $_POST['status']), "comemid = $_POST[comemid]");
    echo 'success';
    exit();
}

//change committee member super admin status
if ($_POST['act'] == 'set_super_admin' && isset($_POST['comemid'])) {
    $amdb->update('hqc_committee_members', array('super_admin' => $_POST['admin']), "comemid = $_POST[comemid]");
    echo 'success';
    exit();
}