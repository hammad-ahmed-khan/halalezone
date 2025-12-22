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
	$curPage = $_POST['page'];
	$rowsPerPage = $_POST['rows'];
	$sortingField = $_POST['sidx'];
	$sortingOrder = $_POST['sord'];

    //$name = getPostParam('name');
    //$code = getPostParam('itemcode');
	$client = getPostParam('client');
	
	$ids = "";
	$myuser = cuser::singleton();
	$myuser->getUserData();
	if ($myuser->userdata['isclient'] == '2') { // Auditor
		$ids = [-1];
		$clients_audit = $myuser->userdata['clients_audit'];
		if ($clients_audit != "") {
			$ids = json_decode($clients_audit);
		}
	}

    $all = getGetParam('all');
    if(!is_numeric(getGetParam('idclient'))) $idclient = -1;
    else $idclient = getGetParam('idclient');

	if ($all == 'true') {
        //get all clients' tasks
		if ($ids == "") {
        	$filter=' WHERE i.idclient is not NULL';
		}
		else {
			$filter=" WHERE i.idclient IN (".implode(",", $ids).")";
		}
	} else {
        $filter=' WHERE i.idclient='.$idclient;
	}
	
    if(!is_numeric(getGetParam('confirmed'))) $confirmed = 0;
    else $confirmed = getGetParam('confirmed');
    
    if ($confirmed)
        //get all confirmed tasks only
        $filter .=' AND i.Status = 1 ';
    else
        // get all but comfirmed 
        $filter .=' AND i.Status = 0 ';   

	$searching = $_POST['_search'];

    if($searching) // есть inline поиск по столбцам
    {
        /*
        if($name!='') $filter.=' AND i.itemname LIKE "%'.$name.'%"';
        if($code!='') $filter.=' AND i.itemcode LIKE "%'.$code.'%"';
				if($client!='') $filter.=' AND c.name LIKE "%'.$client.'%"';
        */
    }

	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = 'select count(i.id) as count from tauditreport i '
	 .' inner join tusers c on c.id=i.idclient '
	 .$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    $sql = 'select i.id, c.name as client, i.created_at, i.Type, i.Deviation, i.Reference, i.RootCause, i.Measure, i.Deadline, i.Status FROM tauditreport i '
					 .' inner join tusers c on c.id=i.idclient 
                     
                     '
           .$filter.' ORDER BY '.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder.' LIMIT '.$firstRowIndex.', '.$rowsPerPage;

    //сохраняем номер текущей страницы, общее количество страниц и общее количество записей
    $response = new \stdClass();
    $response->page = $curPage;
    $response->total = ceil($totalRows['count'] / $rowsPerPage);
    $response->records = $totalRows['count'];

    $i=0;
    $res = $dbo->prepare($sql);
    if(!$res->execute()) die($sql);
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $response->rows[$i]['id']=$row['id'];
        $response->rows[$i]['cell']=array($row['id'], $row['idclient'], $row['created_at'], $row['client'], $row['Type'],$row['Deviation'],$row['Reference'], $row['RootCause'], $row['Measure'], $row['Deadline'],
            $row['Status']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>