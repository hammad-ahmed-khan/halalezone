<?php
if (!session_id()) {
    session_start();
}
include $_SESSION['hqc_path'] . '/load.inc.php';
$img_dir = data_path . '/offices/' . $_SESSION['offid'] . '/images/uploads';

if (!is_dir($img_dir))
    mkdir($img_dir, 0777, true);

if ($img_files = get_dir_contents($img_dir, 'file') and count($img_files) > 0) {
    $images = array();
    foreach($img_files as $img)
    $images[] = '{"title":"'. str_replace(array('-', '_'), ' ', $img['file_title']).'","value":"'.$img['url'].'"}';
    echo '['.implode(',',$images).']';
}
