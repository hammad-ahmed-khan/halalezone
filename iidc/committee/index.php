<?php
session_start();
define("_HQC_", true);
if (!isset($inc)) {
    $inc = substr(basename(__DIR__), 0, -3);
}
include "../home.php";
