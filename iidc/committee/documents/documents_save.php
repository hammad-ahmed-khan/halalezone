<?php
if (!isset($_POST['act'])) {
    exit();
}
include "../../check_user.inc.php";

$filesDir = $prog_path . "/data/DMC/documents";
if (!is_dir($filesDir)) {
    mkdir($filesDir, 0777, true);
};

if($_POST['act'] == 'delete_document'){
    if(unlink($filesDir . '/' . $_POST['file'])){
        echo 'success';
    }else{
        echo 'Error deleting file!';
    }
    exit();
}

function clean($string)
{
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\.\-]/', '', $string); // Removes special chars.
    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

if($_POST['act'] == 'upload_files'){
    $files = $_FILES['files'];
    $output = array();
    foreach ($files['name'] as $key => $name) {

        if ($files['error'][$key] === 0) {
            move_uploaded_file($files['tmp_name'][$key], $filesDir . '/' . clean($name));
        }
    }
    post_this_results('','reload');
    exit();
}