<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect();
	$curPage = $_POST['page'];
	$rowsPerPage = 99999999;
	$sortingField = $_POST['sidx'];
	$sortingOrder = $_POST['sord'];

	$searching = $_POST['_search'];
    $filter1 = ' ';
    $filter2 = ' ';

  $ingredId = getPostParam('idingredient') ?: getGetParam('idingredient') ?: null;

  if ($ingredId) {
    $ingredArray = array_map('intval', explode(',', $ingredId));
    $ingredString = implode(',', $ingredArray);
    $filter1 .= ' and td2i.idi IN ('.$ingredString.')';
  }

  if (getPostParam('s') != "" && strlen(getPostParam('s')) >= 3) {
    $filter2 .= " WHERE (d.deviation LIKE '%".getPostParam('s')."%' OR d.measure LIKE '%".getPostParam('s')."%')";
  }

	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = 'SELECT COUNT(id) AS count FROM tdeviations ';
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    $sql = 'SELECT d.id, IF(td2i.idi IS NULL, 0, 1) AS flag, d.deviation, d.measure FROM tdeviations d '.
    'LEFT JOIN td2i ON td2i.idd=d.id AND td2i.status < 2'.
    $filter1.' '.$filter2.' GROUP BY d.id ORDER BY flag DESC, '.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder;


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
        $response->rows[$i]['cell']=array($row['id'], $row['flag'], $row['deviation'], $row['measure']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>
