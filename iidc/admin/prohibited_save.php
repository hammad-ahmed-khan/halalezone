<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

//implode lines into a string separated by comma
$_POST['prohibited_words'] = explode("\r\n", $_POST['prohibited_words']);
//remove empty lines and trim spaces and duplicate lines
$_POST['prohibited_words'] = array_unique(array_filter(array_map('trim', $_POST['prohibited_words'])));
//implode lines into a string separated by comma
$_POST['prohibited_words'] = implode(",", $_POST['prohibited_words']);


update_option('prohibited_words', $_POST['prohibited_words']);
$amdb->post_results('saved');
