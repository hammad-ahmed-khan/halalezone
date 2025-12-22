<?php
session_start();
if (!isset($_SESSION["username"])) {
    exit();
};

include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";

echo get_remote_data('/invoices/settings/predefined_prices.inc.php', array('act' => 'get_prices'));
