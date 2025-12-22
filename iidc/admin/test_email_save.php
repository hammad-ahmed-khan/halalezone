<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

update_option('test_email_address',$_POST['test_email_address']);
$amdb->post_results('<center style="color:red">Test email address saved</center>','html');