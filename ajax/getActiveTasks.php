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

    $name = getPostParam('name');
    $client = getPostParam('client');

    $sortingField = $sortingField == "rmid" ? 'id' : $sortingField;

    $id = preg_replace("/[^0-9]/", '', getPostParam('rmid'));

    $all = getGetParam('all');
    if(!is_numeric(getGetParam('confirmed'))) $confirmed = 0;
    else $confirmed = getGetParam('confirmed');

    if(!is_numeric(getGetParam('idclient'))) $idclient = -1;
    else $idclient = getGetParam('idclient');

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

    if ($all == 'true') {
        //get all clients' tasks
		if ($ids == "") {
        	$filter=' WHERE i.deleted = 0 AND i.idclient is not NULL';
		}
		else {
			$filter=" WHERE i.deleted = 0 AND i.idclient IN (".implode(",", $ids).")";
		}
    }
    else {
        $filter=' WHERE i.deleted = 0 AND i.idclient='.$idclient;
    }

    if ($confirmed)
        //get all confirmed tasks only
        $filter .=' AND di.status = 2 ';
    else
        // get all but comfirmed 
        $filter .=' AND di.status < 2 ';       

	$searching = $_POST['_search'];

    if($searching) // есть inline поиск по столбцам
    {
        if($name!='') $filter.=' AND i.name LIKE "%'.$name.'%"';
        if($client!='') $filter.=' AND c.name LIKE "%'.$client.'%"';
        if($id!='') $filter.=' AND i.id LIKE "%'.$id.'%"';
    }

	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = 'select count(i.id) as count from tingredients i '.
        ' inner join td2i di on di.idi=i.id '.
        ' inner join tdeviations d on d.id=di.idd '.
        ' inner join tusers c on c.id=i.idclient '.
        $filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    $sql = 'select di.id, i.id as idingredient, i.idclient, i.name, i.rmcode, c.name as client, i.supplier, i.producer, di.created_at, di.completed_at, d.deviation, d.measure, di.status from tingredients i '.
            ' inner join td2i di on di.idi=i.id '.
            ' inner join tdeviations d on d.id=di.idd '.
            ' inner join tusers c on c.id=i.idclient '
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
        $response->rows[$i]['cell']=array($row['id'],$row['idingredient'],$row['idclient'],$row['created_at'],$row['client'],
                                    "RMC_".$row['idingredient'], $row['rmcode'], $row['name'],$row['supplier'],$row['producer'],
                                    $row['deviation'], $row['measure'], $row['status'], $row['completed_at']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>