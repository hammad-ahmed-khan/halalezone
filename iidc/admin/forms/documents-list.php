<?php
if (!session_id()) {
    session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';
$docs_dir = data_path . '/offices/' . $_SESSION['offid'] . '/documents/uploads';

if (!is_dir($docs_dir))
    mkdir($docs_dir, 0777, true);

if ($docs_files = get_dir_contents($docs_dir, 'file') and count($docs_files) > 0) {
    $documents = array();
    foreach ($docs_files as $file)
        $documents[] = '{"title":"' . str_replace(array('-','_'),' ',$file['file_title']) . '","value":"' . $file['url'] . '"}';
    echo '[' . implode(',', $documents) . ']';
}
