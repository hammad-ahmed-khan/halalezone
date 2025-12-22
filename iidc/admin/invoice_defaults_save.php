<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

update_option('invoice_defaults',json_encode($_POST,JSON_UNESCAPED_UNICODE));
$amdb->post_results('<center style="color:red">Invoice defaults updated</center>','html');
