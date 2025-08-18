<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
    $bannedwords = $_POST['bannedwords'];	
	
	$sql = "UPDATE tsettings SET value = :value WHERE name = 'bannedwords'";
	$stmt = $dbo->prepare($sql);
	$stmt->bindValue(':value', $bannedwords);
	if (!$stmt->execute()){
		echo json_encode(generateErrorResponse('Settings update failed.'));
		die();
	}

    echo json_encode(['message' => 'Settings update successful.']);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}	
?>