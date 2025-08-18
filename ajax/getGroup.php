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

	$item = getPostParam('item');

    if(!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');
	
	$searching = $_POST['_search'];
		
	$filter='WHERE p.deleted='.$displaymode;
		
	if ($searching) // есть inline поиск по столбцам
	{
		if($item!='') $filter.=' AND p.name LIKE "%'.$item.'%"';
	}

	$sql = 'SELECT COUNT(*) as count FROM tgroups s ';
  $rows = $dbo->prepare($sql);
	$rows->execute();
  $uniquerecords = $rows->fetch(PDO::FETCH_ASSOC);
 
	$sql = 'SELECT * FROM tgroups AS p '.
		''
		.$filter.' ORDER BY p.'.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder;

	$response = new \stdClass();
    $response->page = 1;
    $response->total = 1;
	$response->uniquerecords = $uniquerecords['count'];

    $i=0;
		$res = $dbo->prepare($sql);
	if(!$res->execute()) die($sql);

    while($row = $res->fetch(PDO::FETCH_ASSOC)) { 
		$response->rows[$i]['id'] = $row['id'];
		$response->rows[$i]['cell'] = array($row['id'], $row['name'], $row['deleted']);
		$i++;
    }

    $response->records = $i;

    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}	
?>