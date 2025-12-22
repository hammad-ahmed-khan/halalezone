<?php
session_start();
define("_HQC_", true);

if (!isset($_GET['inc'])) {
    $inc = basename(dirname(__FILE__));
} else {
    $inc = $_GET['inc'];
};
include "../../home.php";
