<?php
session_start();
if (isset($_POST['adminAsClient']) and isset($_SESSION['username'])) {
    $fl = fopen('data/temp/'.$_SESSION['uid']."_as_client.php", "w");
    fwrite($fl, json_encode($_SESSION));
    fclose($fl);
    echo "success";
    exit();
}