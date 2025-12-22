<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if(update_option('protected_pdf',json_encode($_POST['protected_pdf'])))
$amdb->post_results('','saved');