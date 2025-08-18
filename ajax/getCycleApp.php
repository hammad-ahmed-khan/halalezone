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

	if(!is_numeric(getGetParam('idcycle'))) $idcycle = 0;
	else $idcycle = getGetParam('idcycle');

	
	$filter=' WHERE idcycle='.$idcycle;
	
	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = 'SELECT COUNT(id) AS count FROM tapplications '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

	$sql = 'SELECT id, name, startdt, enddt, state, app, appstate, offer, offerstate, soffer, sofferstate, plan, planstate, '.
            ' checklist, checkliststate, report, reportstate, action, actionstate, list, liststate, payment, paymentstate, cert, certstate, '.
            ' newapp, newappstate, newcert, newcertstate, notifystatus, auditorname, auditornamestate, '.
            ' halaltraining from tapplications '.
		    $filter.' ORDER BY id';

    //сохраняем номер текущей страницы, общее количество страниц и общее количество записей
    $response = new \stdClass();
    $response->page = 1;
    $response->total = 3;
    $response->records = 3;

    $i=0;
    $res = $dbo->prepare($sql);
    if(!$res->execute()) die('Error');
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $response->rows[$i]['id']=$row['id'];
        $response->rows[$i]['cell']=array($row['id'],$row['name'],$row['app'], $row['appstate'], $row['offer'], $row['offerstate'],
            $row['soffer'], $row['sofferstate'], $row['plan'], $row['planstate'], $row['auditorname'], $row['auditornamestate'],
            $row['checklist'], $row['checkliststate'],
            $row['report'], $row['reportstate'], $row['action'], $row['actionstate'], $row['list'], $row['liststate'],
            $row['payment'], $row['paymentstate'],$row['cert'], $row['certstate'], $row['newapp'], $row['newappstate'], $row['newcert'],
            $row['newcertstate'],
            $row['startdt'],$row['enddt'],$row['halaltraining'],0,$row['state'],$row['notifystatus']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}	
?>