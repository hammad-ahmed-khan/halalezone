<?php
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../notifications/notifyfuncs.php";
include_once "../includes/func.php";
include_once "../reports/reports.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	
	 $myuser = cuser::singleton();
	$myuser->getUserData();
	$pageNumber = $_POST["start"];
	$pageSize = $_POST["length"];
	$s = trim($_POST["s"]);
	$idparent = $_POST["idparent"] ;
	$idclient = $_POST["idclient"] == "" ? -1 : $_POST["idclient"] ;
	$idapp =  $_POST["idapp"] == "" ? -1 : $_POST["idapp"];
	$category = trim($_POST["category"]);
	$actions = trim($_POST["actions"]);
	$deleted = $_POST["deleted"] ?? '0';		
	$TotalCount = 0;
	$criteria = "";
print_r($myuser);

	if ($myuser->userdata['isclient'] == '1') { 
		$criteria =  " AND e.idclient = '".$myuser->userdata['id']."'";
	}
	
	echo $query = "SELECT e.id, e.idapp, e.idclient, e.title, e.start_date, e.end_date, e.status FROM tevents AS e
	WHERE 1=1
	$criteria
	ORDER BY e.start_date ASC";
	
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$events = $stmt->fetchAll(PDO::FETCH_ASSOC);  
	
	$data = array();
	foreach($events as $e) {

		if ($e["idclient"] == '-1' || $e["idapp"] == '-1') {
			$color = '#09F';
		}
		else if ($e["status"] == '1') {
			$color = '#0C0';
		}
		else {
			$color = '#F60';
		}

		 $data[] = array(
		  'id'   => $e["id"],
		  'idclient'   => $e["idclient"],
		  'idapp'   => $e["idapp"],
		  'title'   => $e["title"],
		  'start'   => $e["start_date"],
		  'end'   => $e["end_date"],
		  'status'   => $e["status"],
		  'color'   => $color
		 );	
	}
	echo json_encode($data);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>