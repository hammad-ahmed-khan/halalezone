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

	$auditnr = getPostParam('auditnr');
	$auditorid = getPostParam('auditorid');
	$auditorname = getPostParam('auditorname');
	$auditeename = getPostParam('auditeename');
	$order = getPostParam('order');
	$report = getPostParam('report');
	$plan = getPostParam('plan');
	$certificate = getPostParam('certificate');
	$gtc = getPostParam('gtc');

	$searching = $_POST['_search'];
	
	if($searching) // есть inline поиск по столбцам
	{
		$filter='WHERE id is NOT NULL';
		if($auditnr!='') $filter.=' AND auditnr LIKE "%'.$auditnr.'%"';
		if($auditorid!='') $filter.=' AND auditorid LIKE "%'.$auditorid.'%"';
		if($auditorname!='') $filter.=' AND auditorname LIKE "%'.$auditorname.'%"';
		if($auditeename!='') $filter.=' AND auditeename LIKE "%'.$auditeename.'%"';
		if($order!='') $filter.=' AND aorder LIKE "%'.$order.'%"';
		if($plan!='') $filter.=' AND plan LIKE "%'.$plan.'%"';
		if($report!='') $filter.=' AND report LIKE "%'.$report.'%"';
		if($gtc!='') $filter.=' AND gtc LIKE "%'.$gtc.'%"';
		if($certificate!='') $filter.=' AND certificate LIKE "%'.$certificate.'%"';
	}
	
	$sql = 'SELECT COUNT(id) AS count FROM taudit '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);	

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    $sql = 'SELECT `id`, `auditnr`, `auditorid`, `auditorname`, `auditeename`, `aorder`, `plan`, `report`, `certificate`, '.
		'`gtc` from taudit '.$filter.' ORDER BY '.strtolower(str_replace(' ', '', $sortingField)).' '.
		$sortingOrder.' LIMIT '.$firstRowIndex.', '.$rowsPerPage;
	
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
        $response->rows[$i]['cell']=array($row['id'],$row['auditnr'], $row['auditorid'], $row['auditorname'], $row['auditeename'], $row['aorder'], $row['plan'],$row['report'],$row['certificate'],$row['gtc']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}	
?>