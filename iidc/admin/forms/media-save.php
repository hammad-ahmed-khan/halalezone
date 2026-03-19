<?php
if (!session_id()) {
    session_start();
}
if (!isset($_REQUEST['act']))
    exit();

include $_SESSION['hqc_path'] . '/load.inc.php';

if ($_REQUEST['act'] == 'uploadFile' && isset($_FILES) && isset($_REQUEST['type'])) {
    if ($_REQUEST['type'] == 'imgs')
        $dir = data_path . '/offices/' . $_SESSION['offid'] . '/images/uploads';
    else
        $dir = data_path . '/offices/' . $_SESSION['offid'] . '/documents/uploads';

    if (!is_dir($dir))
        mkdir($dir, 0777, true);

    $files = _Files($_FILES);
    foreach ($files['files'] as $file) {
        if (file_exists($dir . '/' . $file['name']) && $_REQUEST['replace'] == 0) {
            echo $file['name'] . 'already uploaded.';
            exit();
        }
    }

    foreach ($files['files'] as $file) {
        move_uploaded_file($file['tmp_name'], $dir . '/' . $file['name']);
    }
    echo "success";
    exit();
}

if ($_REQUEST['act'] == 'deleteFile' && isset($_REQUEST['file'])) {
    if (unlink($_REQUEST['file']))
        echo 'deleted';
    else
        echo 'Error: Could not delete the file.';
    exit();
}
