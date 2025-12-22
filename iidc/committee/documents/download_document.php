<?php
if(!isset($_GET['file'])){
    exit();
}
include "../../check_user.inc.php";

//push the file to the browser for download
$file = $_GET['file'];
$filesDir = $prog_path . "/data/DMC/documents";
$file = $filesDir . '/' . $file;
if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length:' . filesize($file));
    readfile($file);
    exit;
}