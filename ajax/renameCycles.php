<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // ??????? ?????? ??????????? ? ??

    $myuser = cuser::singleton();
    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];

    $idclient = $_POST['idclient'] ?? '';
    $cycles = $_POST['cycle'] ?? [];

    if (!$idclient || empty($cycles)) {
        echo "Invalid request!";
        exit;
    }

    $success = true;
    foreach ($cycles as $id => $name) {
        $name = trim($name);
        if (empty($name)) continue;

        $query = "UPDATE tcycles SET name = :name WHERE id = :id AND idclient = :idclient";
        $stmt = $dbo->prepare($query);
        if (!$stmt->execute([':name' => $name, ':id' => $id, ':idclient' => $idclient])) {
            $success = false;
        }
    }

    echo json_encode($data);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>