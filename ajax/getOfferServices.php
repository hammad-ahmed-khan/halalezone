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

	$query = "SELECT *
	FROM toffers	
	WHERE idclient='".$idclient."' AND idapp='".$idapp."'";
	
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
		$id = $data[$i]['id'];
		$Service = str_replace("<br/>", "\n", $data[$i]['service']);
		$Fee = $data[$i]['fee'];
		
		/*
		$data[$i]['Type'] = '<select id="Type_'.$id.'" name="Type_'.$id.'" class="selectpicker form-control hidden"  style="width:100%;" data-live-search="true" title="">
                      <option value="" '.($Type==''?' selected' :'').'>Select an Option</option>
                      <option value="1" '.($Type=='1'?' selected' :'').'>Major</option>
                      <option value="2" '.($Type=='2'?' selected' :'').'>Minor</option>
                      <option value="3" '.($Type=='3'?' selected' :'').'>OBS</option>
                    </select>';												
		$data[$i]['Deviation'] = '<textarea class="form-control" name="Deviation_'.$id.'" id="Deviation_'.$id.'">'.$Deviation.'</textarea>';				
		$data[$i]['Reference'] = '<input type="text" class="form-control" name="Reference_'.$id.'" id="Reference_'.$id.'" value="'.$Reference.'"/>';				
		*/
		//$data[$i]['Deadline'] = 'N/A';
				
	  $data[$i]['button'] .= '<a href="#" id="'.$data[$i]['id'].'" class="btn btn-success btnedit-service">
						   <span class="glyphicon glyphicon-edit" title="Edit Row" aria-hidden="true"></span>
						   </a> <a href="#" id="'.$data[$i]['id'].'" class="btn btn-danger btndel-service">
						   <span class="glyphicon glyphicon-trash" title="Delete Row" aria-hidden="true"></span>
						   </a>
						   ';

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