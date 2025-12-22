<?php
include_once '../../config/config.php';
include_once '../../classes/users.php';
include_once '../../includes/func.php';

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$pageNumber = $_POST["start"];
	$pageSize = $_POST["length"];
	$clientid = trim($_POST["clientid"]);
	$sproducer_id = trim($_POST["sproducer_id"]);
	$srmcode = trim($_POST["srmcode"]);
	$sname = trim($_POST["sname"]);
	$shalalcert = trim($_POST["shalalcert"]);
	$srmposition = trim($_POST["srmposition"]);
	$criteria = "";

	if (strlen($sproducer_id) > 0) {
		$criteria .= " AND i.producer_id = '".$sproducer_id."'";
	}
	if (strlen($srmcode) > 1) {
		$criteria .= " AND i.rmcode LIKE '%".$srmcode."%'";
	}
	if (strlen($sname) > 1) {
		$criteria .= " AND i.name LIKE '%".$sname."%'";
	}
	if (strlen($shalalcert) > 1) {
		$criteria .= " AND i.cb LIKE '%".$shalalcert."%'";
	}
	if (strlen($srmposition) > 0) {
		$criteria .= " AND i.rmposition = '".$srmposition."'";
	}


	$query = "
        SELECT COUNT(*) as total_records FROM tingredients_pa $criteria";
        
		$stmt = $dbo->prepare($query);
		$stmt->execute();
		$recordsTotal = $stmt->fetchColumn(); 	

		if (!is_numeric($recordsTotal)) {
			$recordsTotal = 0;
		}

	$query = "SELECT i.*, p.name AS producer_name 
	FROM tingredients_pa AS i 
	LEFT OUTER JOIN tproducers AS p ON i.producer_id = p.id
	WHERE 1 = 1 
	$criteria
	ORDER BY p.name, i.rmcode
	LIMIT ".$pageNumber.", ".$pageSize;

	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$tanks = $stmt->fetchAll(PDO::FETCH_ASSOC);  

	$data = array();
	foreach($tanks as $d) {
		$data[] = $d;
	}

	$i = 0;
	foreach ($data as $key=>$val) {
		
			// add new button
		//if ($clientid != "" && is_numeric($clientid)) {
			$data[$i]['button'] = '<input type="checkbox" name="paingred_id[]" value="'.$data[$i]['id'].'" />';
		/*
		}
		else {
			$data[$i]['button'] = '<a href="#" id="'.$data[$i]['id'].'" class="btn btn-danger btndel-tank">
								<span class="glyphicon glyphicon-trash" title="Delete" aria-hidden="true"></span>
								</a> '. 
								'<a href="#" id="'.$data[$i]['id'].'" class="btn btn-primary btnedit-tank">
								<span class="glyphicon glyphicon-pencil" title="Edit" aria-hidden="true"></span>
								</a>';
		}
		*/
		$i++;
	}

	$datax = array('recordsTotal' =>$recordsTotal, 'recordsFiltered' =>$recordsTotal, 'data' => $data);
	echo json_encode($datax);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}	
?>