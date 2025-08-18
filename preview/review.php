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
	
	$query = "SELECT *
	FROM tauditreport	
	WHERE idclient='".$idclient."' AND idapp='".$idapp."'";
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$data['deviations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$audit_report_settings = $data['app']['audit_report_settings'];
	if ($audit_report_settings == "") $audit_report_settings = "[]";

	saveDecisionMakingReportPDF($data, 'files/docs/F0417_Offer Halal certification_EN.pdf', null, $audit_report_settings, true);


?>