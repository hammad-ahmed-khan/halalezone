<?php
	@session_start();
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	/*
	include_once "../vendor/autoload.php";

	use mikehaertl\pdftk\Pdf;

	$pdf = new Pdf('Auditor-Checklist1.pdf');

	$result =  $pdf->fillForm([
        'Production' =>'Yes',
        'Text8'=>'Wizard'
    ])
    ->needAppearances()
    ->saveAs('doc_filled.pdf');
	 
	if ($result === false) {
		echo $pdf->getError();
	}
	*/
	@session_start();
	include_once "../config/config.php";
	include_once "../classes/users.php";
	include_once "../includes/func.php";

	/*
	require_once('../includes/tcpdf/config/tcpdf_config.php');
	require_once('../includes/tcpdf/tcpdf.php');
	require_once('../includes/tcpdf/tcpdi_parser.php');
	require_once('../includes/tcpdf/tcpdi.php');
	require_once('../includes/fpdm/fpdm.php');
	*/

	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$idclient = $_GET["idclient"];
	$idapp = $_GET["idapp"];	

	$data = [];

	$query = "SELECT *
	FROM tapplications	
	WHERE id='".$idapp."' AND idclient='".$idclient."'";
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$app = $stmt->fetch(PDO::FETCH_ASSOC);

	$query = "SELECT *
	FROM tusers	
	WHERE id='".$idclient."'";
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	$data = array_merge($app, $user);
 

	$decode = file_get_contents( __DIR__ ."/../config.json"); 
	$config=json_decode($decode, TRUE);
	$attach = 'initial-application-fillable.pdf';
	$ext = "pdf";
	$hostPath =  __DIR__ ."/../".$config['filesfolder']."/".$config['clientsfolder']."/".$data['name']." (".$idclient.")/application/";
	if (!is_dir($hostPath)) {
		mkdir($hostPath, 0777, true);
	}

	$filename = str_replace(".".$ext, '_'.$idapp.'.'.$ext, $attach);
	$dest_path = $hostPath . $filename; 

	saveApplicationPDF1($data, $attach, $dest_path);
?>