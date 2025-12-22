<?php
//show php errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo $_SERVER['DOCUMENT_ROOT'] . "\n";
echo file_get_contents(dirname($_SERVER['DOCUMENT_ROOT']). '/halaloff/index.php');