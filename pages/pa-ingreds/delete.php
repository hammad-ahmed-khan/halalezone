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

$id = $_POST["id"];

if ($_POST["id"] != "") { 

	$query = "DELETE FROM tingredients_pa WHERE id = :id";
	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':id', $id, PDO::PARAM_STR);   
	$stmt->execute();
}
?>