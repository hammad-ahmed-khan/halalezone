<?php
if (!session_id()) {
    session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';

$img_dir = data_path . '/offices/' . $_SESSION['offid'] . '/images/uploads';
$error = '{"error":"uploading the images"}';

if (!is_dir($img_dir))
    mkdir($img_dir, 0777, true);
if (isset($_FILES['file']['tmp_name'])) {
    $img = $img_dir . '/' . $_FILES['file']['name'];
    $img_url = data_url . '/offices/' . $_SESSION['offid'] . '/images/uploads/'. $_FILES['file']['name'];
    move_uploaded_file($_FILES['file']['tmp_name'], $img);
    if(file_exists($img))
    echo '{"location":"'.$img_url.'"}';
    else
    echo $error;
    exit();
}
echo $error;