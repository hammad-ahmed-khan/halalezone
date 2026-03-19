<?php
if (!session_id()) {
    session_start();
}

if (!isset($_POST['stage']))
    exit();

include $_SESSION['hqc_path'] . '/load.inc.php';

if(isset($_POST['act']) && $_POST['act'] == 'saveInitialMessage') {
    if (!$ionInitialMessages = json_decode(get_hqc_options('applicationInitialMessages'), true))
    $ionInitialMessages = array();
    $ionInitialMessages[$_POST['stage']] = $_POST['initialMessage'];
    set_hqc_options('applicationInitialMessages', json_encode($ionInitialMessages));
    echo 'success';
    exit();
}

if (!$stages = json_decode(get_hqc_options('applicationStages'),true))
    $stages = array();

if(isset($stages[$_POST['stage']])) {

    if (!isset($_POST['stages']))
        unset($stages[$_POST['stage']]);
    else
        $stages[$_POST['stage']] = $_POST['stages'];
} else {
    $stages = $stages + array($_POST['stage'] => $_POST['stages']);
}

set_hqc_options('applicationStages', json_encode($stages));
