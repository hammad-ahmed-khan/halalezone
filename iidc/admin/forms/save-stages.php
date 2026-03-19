<?php
if (!session_id()) {
    session_start();
}
if(!isset($_POST['stages']))
exit();

include $_SESSION['hqc_path'] . '/load.inc.php';

set_hqc_options('applicationStages',json_encode($_POST['stages']));