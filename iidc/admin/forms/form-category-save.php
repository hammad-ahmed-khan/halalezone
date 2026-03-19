<?php
if (!session_id()) {
    session_start();
}

include $_SESSION['hqc_path'] . '/load.inc.php';

if(!is_super_admin())
exit();

if(!isset($_POST['cat']))
exit();

$categories = $_POST['cat'];

foreach($categories as $key=>$value){
    if(trim($value)=='')
    unset($categories[$key]);
}
if(set_hqc_options('form_categories',json_encode($categories))){
    post_results('reload');
}