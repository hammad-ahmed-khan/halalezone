<?php
include "./check_user.inc.php";
include "./config/paths.inc.php";
if (!isset($_REQUEST['inc']))
return;

$inc = $_REQUEST['inc'];
unset($_REQUEST['inc']);
unset($_GET['inc']);
if (!file_exists($inc))
$inc = $_SERVER['DOCUMENT_ROOT'] . $inc;
if (file_exists($inc))
include ($inc);
else
echo "File not found";
?>