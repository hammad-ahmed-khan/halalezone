<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$pageNumber = $_POST["start"];
	$pageSize = $_POST["length"];
	$idingredient = getPostParam('idingredient');

	$sql = 'SELECT COUNT(id) AS count FROM tingredients_log i WHERE i.idingredient = '.$idingredient;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC)["count"];          

    $sql = 'SELECT i.created_by,  i.idingredient AS id,  i.rmcode, i.rmcode, i.name, i.tasks, i.conf, i.sub, i.supplier, i.producer, 
				   i.material, i.cert, i.halalcert, i.cb, i.halalexp, i.rmposition, i.ingredients, i.spec, i.quest, i.statement, i.note, i.addoc, i.note, 
				   status, DATE_FORMAT(i.created_at, "%d/%m/%Y %H:%i") as created_at_formated, i.created_at, i.deleted 
				   from tingredients_log i 
				   WHERE i.idingredient = '.$idingredient.'  ORDER BY id DESC LIMIT '.$pageNumber.' ,'.$pageSize;
	//echo $sql;
    //сохраняем номер текущей страницы, общее количество страниц и общее количество записей
	$stmt = $dbo->prepare($sql);
	$stmt->execute();
	$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);  
	
	$data = array();
	foreach($docs as $d) {
		$d['id'] = "RMC_".$d['id'];
		if ($d['cert'] != "") {
			$certs = json_decode("[".$d['cert']."]", true);
			$val = '';
			foreach ($certs as $cert) {
				if ($cert['deleted'] != 1) {
					$val .= $cert['name'].', ';
				}
			}
			$d['cert'] = trim($val,', ');
		}
		if ($d['spec'] != "") {
			$specs = json_decode("[".$d['spec']."]", true);
			$val = '';
			foreach ($specs as $spec) {
				if ($spec['deleted'] != 1) {
					$val .= $spec['name'].', ';
				}
			}
			$d['spec'] = trim($val,', ');
		}	
		if ($d['halalcert'] == 1)  {
			$d['halalcert'] = 'Yes';
		}
		else {
			$d['halalcert'] = 'No';
		}
		if ($d['sub'] == 1)  {
			$d['sub'] = 'Yes';
		}
		else {
			$d['sub'] = 'No';
		}		
		if ($d['conf'] == 1)  {
			$d['conf'] = 'Yes';
		}
		else {
			$d['conf'] = 'No';
		}
		if ($d['deleted'] == 1)  {
			$d['deleted'] = 'Yes';
		}
		else {
			$d['deleted'] = 'No';
		}

		$tasksArray = array_filter(array_map('trim', explode("|", $d['tasks']))); // Split and remove empty values

		if (!empty($tasksArray)) {
			$d['tasks'] = '<ul><li>' . implode('</li><li>', $tasksArray) . '</li></ul>'; // Convert to UL/LI
		} else {
			$d['tasks'] = ''; // Keep it empty if no valid tasks exist
		}
						
		$data[] = $d;
	}

	$datax = array(
		'recordsTotal' => $totalRows,
		'recordsFiltered' => $totalRows,	
		'data' => $data);
	
	echo json_encode($datax);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>