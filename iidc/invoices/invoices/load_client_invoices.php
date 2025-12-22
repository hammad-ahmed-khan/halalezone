<?php
define("__HQC__", true);
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
if ($invoices = $amdb->get_results("SELECT total,YEAR(inserted_on) AS year,invoice_type FROM `invoices` WHERE clid='$_GET[clid]' ORDER BY inserted_on DESC")) {
echo "<pre>";
    print_r($invoices);
}
