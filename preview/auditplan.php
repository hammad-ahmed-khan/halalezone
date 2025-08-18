<?php
	@session_start();
	include_once "../config/config.php";
	include_once "../classes/users.php";
	include_once "../includes/func.php";

	//error_reporting(E_ALL);
	//ini_set('display_errors', 1);

	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$idclient = $_POST["idclient"];
	$idapp = $_POST["idapp"];	

	$data = [];

	$query = "SELECT *
	FROM tapplications	
	WHERE id='".$idapp."' AND idclient='".$idclient."'";
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$data['app'] = $stmt->fetch(PDO::FETCH_ASSOC);

	$query = "SELECT *
	FROM tusers	
	WHERE id='".$idclient."'";
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$data['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

	$audit_plan_settings = $data['app']['audit_plan_settings'];
	if ($audit_plan_settings == "") $audit_plan_settings = "[]";
	
	saveAuditPlanPDF($data, 'files/docs/F0417_Offer Halal certification_EN.pdf', null, $audit_plan_settings, true);
?>