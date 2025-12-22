<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	
	 $myuser = cuser::singleton();
	$myuser->getUserData();

	$s = trim($_POST["s"]);
	$idclient = $_POST["idclient"] == "" ? -1 : $_POST["idclient"] ;
	$idapp =  $_POST["idapp"] == "" ? -1 : $_POST["idapp"];
	
	$query = "SELECT d.deviation AS Deviation, d.measure AS Measure
	FROM tdeviations AS d
	INNER JOIN td2a ON d.id = td2a.idd	
	WHERE td2a.ida='".$idapp."'";
	
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);  
	
	$data = array();
	foreach($docs as $d) {
		$data[] = $d;
	}
	
	$i = 0;
	foreach ($data as $key=>$val) {

		// add new button
		$data[$i]['Deadline'] = 'N/A';		
		$data[$i]['button'] = 'N/A';		
		$i++;
	}

	$datax = array(
	'recordsTotal' => $TotalCount,
	'recordsFiltered' => $TotalCount,	
	'data' => $data);
	echo json_encode($datax);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>