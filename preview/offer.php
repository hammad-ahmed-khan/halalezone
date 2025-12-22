<?php
	
	@session_start();
	include_once "../config/config.php";
	include_once "../classes/users.php";
	include_once "../includes/func.php";
	
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	
	$idclient = $_POST["idclient"];
	$idapp = $_POST["idapp"];	
	
	$data = [];
	
	$query = "SELECT *
	FROM tusers	
	WHERE id='".$idclient."'";
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$data['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$query = "SELECT *
	FROM toffers	
	WHERE idclient='".$idclient."' AND idapp='".$idapp."'";
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$data['offer'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$sql = "SELECT * FROM tapplications WHERE idclient=:idclient AND id=:idapp";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->bindValue(':idapp', $idapp);
	$stmt->execute();
	$data['app'] = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$data["id"] = "{OfferID}";
	
	saveOfferPDF($data, 'files/docs/F0417_Offer Halal certification_EN.pdf', null, true);
?>