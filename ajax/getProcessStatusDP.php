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

    if ($sortingField == 'state') {
        $sortingField = "FIELD(a.state, 'offer', 'soffer', 'app', 'declarations', 'dates', 'audit', 'checklist', 'report', 'pop', 'certificate', 'additional_items', 'popai', 'extension')";
    }

    $name = getPostParam('name');
    $code = getPostParam('itemcode');
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
    $idclient = "";
    $all = getGetParam('all');
    if(!is_numeric(getGetParam('idclient'))) $idclient = -1;
    else $idclient = getGetParam('idclient');

	if ($all == 'true' && ($idclient == "" || $idclient == "-1")) {
        //get all clients' tasks
         	$filter=' AND i.idclient is not NULL';
		 
	} else {
        $filter=' AND i.idclient='.$idclient;
	}
	
    /*
    if(!is_numeric(getGetParam('confirmed'))) $confirmed = 0;
    else $confirmed = getGetParam('confirmed');
    
    if ($confirmed)
        //get all confirmed tasks only
        $filter .=' AND i.status = 1 ';
    else
        // get all but comfirmed 
        $filter .=' AND i.status = 0 ';   
    */

	$searching = $_POST['_search'];

    if($searching) // есть inline поиск по столбцам
    {
				if($client!='') $filter.=' AND c.name LIKE "%'.$client.'%"';
    }

	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = 'select count(*)  as count from tactivity_log i '
	 .' inner join tusers c on c.id=i.idclient and c.deleted=0
     '
	 .$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
   $sql = 'select i.id, i.username, c.id as idclient, c.name as client, i.activity_description AS action, i.status, i.created_at, a.state 
    FROM tactivity_log i INNER JOIN tapplications a ON i.idapp=a.id'
					 .' inner join tusers c on c.id=i.idclient and c.deleted=0
                     
                     WHERE i.deleted = 0 '
           .$filter.' GROUP BY i.idclient, i.activity_description, DATE(i.created_at) ORDER BY '.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder.' LIMIT '.$firstRowIndex.', '.$rowsPerPage;

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
        $response->rows[$i]['cell']=array($row['id'],  $row['created_at'], $row['client'],
            '"'.$row['action'].'" by '.$row['username'], getAppStateName($row['state']), $row['status']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>