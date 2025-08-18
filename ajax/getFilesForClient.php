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

    if(!is_numeric(getGetParam('idclient'))) $idclient = -1;
    else $idclient = getGetParam('idclient');
	$filter='WHERE idclient='.$idclient;

	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = 'SELECT COUNT(id) AS count FROM tfiles '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    // first, take only clients files, but not shared.
    $sql = 'SELECT id, filename, url, hostpath, gdrivepath, created_at as uploaddate, status FROM tfiles '
        .$filter.' AND tfiles.status=0 ORDER BY '.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder;

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
        $response->rows[$i]['cell']=array($row['id'], $row['filename'], $row['url'], $row['hostpath'],
            $row['gdrivepath'], $row['uploaddate'], $row['status']);
        $i++;
    }
    // then, take all shared files
    $sql = 'SELECT id, filename, url, hostpath, gdrivepath, created_at as uploaddate, status FROM tfiles '
        .' WHERE tfiles.status=1 ORDER BY '.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder;

    $res = $dbo->prepare($sql);
    if(!$res->execute()) die($sql);
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $response->rows[$i]['id']=$row['id'];
        $response->rows[$i]['cell']=array($row['id'], $row['filename'], $row['url'], $row['hostpath'],
            $row['gdrivepath'], $row['uploaddate'], $row['status']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>
