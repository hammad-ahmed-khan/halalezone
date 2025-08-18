<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	$curPage = $_POST['page'];
	$rowsPerPage = $_POST['rows'];
	$sortingField = $_POST['sidx'];
	$sortingOrder = $_POST['sord'];

	if(!is_numeric(getGetParam('idclient'))) $idclient = 0;
	else $idclient = getGetParam('idclient');

    if (!$idclient) {
	    if(!is_numeric($_POST['idclient'])) $idclient = 0;
    	else $idclient = $_POST['idclient'];
    }

	$filter=' WHERE idclient='.$idclient;
	
	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = 'SELECT COUNT(id) AS count FROM tcycles '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;

    $sql = 'SELECT id, name, startdt, enddt, state from tcycles '.
		    $filter.' ORDER BY '.$sortingField.' '.$sortingOrder.' LIMIT '.$firstRowIndex.', '.$rowsPerPage;
 
    //сохраняем номер текущей страницы, общее количество страниц и общее количество записей
    $response = new \stdClass();
    $response->page = $curPage;
    $response->total = ceil($totalRows['count'] / $rowsPerPage);
    $response->records = $totalRows['count'];

    $i=0;
    $res = $dbo->prepare($sql);
    if(!$res->execute()) die('Error');
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $response->rows[$i]['id']=$row['id'];
        $response->rows[$i]['cell']=array($row['id'],$row['name'], $row['startdt'],$row['enddt'],$row['state']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}	
?>