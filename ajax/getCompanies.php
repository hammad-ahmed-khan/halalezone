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
    $active = getPostParam('active');

		if(!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');

    $searching = $_POST['_search'];

	//	$filter='WHERE u.deleted='.$displaymode;

    if($searching) // есть inline поиск по столбцам
    {
        if($name!='') $filter.=' AND name LIKE "%'.$name.'%"';
        if($active!='') $filter.=' AND active = "'.$active.'"';
    }

	$sql = 'SELECT COUNT(id) AS count FROM tcompanies u '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    $sql = 'SELECT * from tcompanies u '
						
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
		if(!empty($row['pass'])) $pass='********';
		else $pass='';
/*        $response->rows[$i]['cell'] = array($row['id'],$row['name'], $row['email'],$row['prefix'],$row['login'],$pass, $row['ingrednumber'],
																						$row['prodnumber'], $row['isclient'], $row['application'],  $row['clients'],  $row['audit'],
																						$row['canadmin'], $row['deleted'], $row['blocked']);
  */      
  		 $response->rows[$i]['cell'] = array($row['id'],$row['name'], $row['active']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>
