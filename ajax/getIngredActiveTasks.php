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

	$searching = $_POST['_search'];

    if(!is_numeric(getGetParam('idingredient'))) $filter = ' ';
    else $filter = ' and d.idi='.getGetParam('idingredient');

	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = 'SELECT COUNT(d.id) AS count FROM td2i d WHERE d.status < 2 '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    $sql = 'SELECT d.id, d.status, dd.deviation, dd.measure FROM td2i d '.
            ' left join tdeviations dd on dd.id=d.idd '.
            ' WHERE d.status < 2 '
            .$filter.' ORDER BY '.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder;

    //сохраняем номер текущей страницы, общее количество страниц и общее количество записей
    $response = new \stdClass();
    $response->page = 1;
    $response->total = 1;
    $response->records = $totalRows['count'];

    $i=0;
    $res = $dbo->prepare($sql);
    if(!$res->execute()) die($sql);
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $response->rows[$i]['id']=$row['id'];
        $response->rows[$i]['cell']=array($row['id'], $row['status'], $row['deviation'], $row['measure']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}	
?>