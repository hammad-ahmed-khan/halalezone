<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	$myuser = cuser::singleton();
	$myuser->getUserData();

    $curPage = $_GET['page'];
    $rowsPerPage = $_GET['rows'];
    $sortingField = $_GET['sidx'];
    $sortingOrder = $_GET['sord'];

	$sortingField = $sortingField == 'rmid' ? 'id' : $sortingField;

	$id = preg_replace("/[^0-9]/", '', getPostParam('rmid'));

	if(!is_numeric(getGetParam('idproduct'))) die();
	else $idproduct = getGetParam('idproduct');

	//$filter='WHERE i.idproduct='.$idproduct;
	
	$filter='';

	if ($myuser->userdata['isclient'] == '2') { // Auditor
		$sources_audit = json_decode($myuser->userdata['sources_audit'], true); // force array

		if (is_array($sources_audit) && count($sources_audit) > 0) {
			$safe_sources = array_map('addslashes', $sources_audit);
			$conditions = [];

			foreach ($safe_sources as $source) {
				$conditions[] = "i.material = '" . $source . "'";
			}

			$filter .= ' AND (' . implode(' OR ', $conditions) . ')';
		} 
	}


	$sql = 'SELECT COUNT(id) AS count FROM tingredients i '.
			' left join tp2i on (i.id=tp2i.idi) '.
			' WHERE tp2i.idp = '.$idproduct . $filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    $sql = 'SELECT i.id, i.name, i.rmcode, i.material, 
	(SUBSTR(GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ","), 1, length(GROUP_CONCAT(s.name SEPARATOR ",")))) as ingred, '.
			'i.supplier, i.statement, i.halalcert, i.cert, i.cb, i.halalexp, i.spec, i.quest, i.note, i.addoc, 
			IF( IF(s.conf is not NULL, (count(s.id)-SUM(s.conf)), 0) = 0 AND i.conf = 1, 1, 0) as conf, 
			GREATEST(MAX(getHalalExpStatus(s.halalexp)), getHalalExpStatus(i.halalexp)) as status from tingredients i '.
			'left join ti2i on (ti2i.idi1=i.id) '.
			'left join tingredients s on (s.id=ti2i.idi2) '.
			' left join tp2i on (i.id=tp2i.idi) '.
			' WHERE tp2i.idp = '.$idproduct. $filter.
		' GROUP BY i.id ORDER BY i.'.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder.' LIMIT '.$firstRowIndex.', '.$rowsPerPage;

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
        $response->rows[$i]['cell']=array($row['id'], "RMC_".$row['id'],$row['rmcode'],$row['name'], $row['supplier'],$row['material'],$row['halalcert'], $row['cert'],$row['cb'],  $row['halalexp'],$row['conf'],$row['status'],$row['ingred'], $row['spec'], $row['quest'], $row['statement'],  $row['addoc'], $row['note']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>