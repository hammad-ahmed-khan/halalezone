<?php
session_start();
define("_HQC_", true);
if (!isset($inc)) {
    $inc = basename(dirname(__FILE__));
}
include "../../home.php";