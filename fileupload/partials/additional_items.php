<?php
@session_start();
include_once "../../config/config.php";
include_once "../../classes/users.php";
include_once "../../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$myuser = cuser::singleton();
	$myuser->getUserData();
		
	$error = "";
	$category = "additional_items";
	$idclient = $_POST["idclient"];
	$idapp = $_POST["idapp"];
	$Comments = $_POST["Comments"];

	
	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$user = $stmt->fetch();

	if (isset($_POST["Delete"])) {
		$id = $_POST["Delete"];
		$query = "UPDATE tdocs SET deleted =1 WHERE id=:id AND idapp=:idapp AND idclient=:idclient AND category=:category";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':id', $id, PDO::PARAM_STR);  
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);  
		$stmt->bindParam(':idclient', $user["id"], PDO::PARAM_STR);  
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);   
		$stmt->execute();
		$data = array('success' => 1);
		die(json_encode($data));
	}
}
catch (PDOException $e) {
		http_response_code(403);
    echo 'Database error: '.$e->getMessage();
}

?>