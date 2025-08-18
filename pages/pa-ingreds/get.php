<?php
include_once '../../config/config.php';
include_once '../../classes/users.php';
include_once '../../includes/func.php';

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
}	
catch (PDOException $e) {
	echo 'Database error: '.$e->getMessage();
}

$result = array();

$query = "SELECT id, producer_id, rmcode, name, cb, DATE_FORMAT(halalexp, '%d/%m/%Y') AS halalexp, rmposition FROM tingredients_pa AS t WHERE t.id = ?";
$stmt = $dbo->prepare($query);
$stmt->bindParam(1, $_POST['id'], PDO::PARAM_STR);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($row);
?>