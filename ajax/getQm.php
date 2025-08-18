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
    $prevYears = (isset($_GET['prevyears']) && $_GET['prevyears'] == '1')  ? '1' : '0';

	if(!is_numeric(getGetParam('idclient'))) $idclient = -1;
	else $idclient = getGetParam('idclient');

    if(!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');

    $searching = $_POST['_search'];

    //$filter='WHERE i.idclient='.$idclient. ' AND i.deleted='.$displaymode;
    $sql = "SELECT parent_id, qm_documents_preference FROM tusers WHERE id='".$idclient."'";
    $rows = $dbo->prepare($sql);
    $rows->execute();
    if ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
        $parent_id = $row["parent_id"];
        $qm_documents_preference = $row["qm_documents_preference"];
    }
    
    // If parent_id is NULL or 0, the client is a parent
    if ($parent_id == "" || $parent_id == 0) {
        // If the parent prefers all ingredients, fetch parent's and all children's ingredients
        if ($qm_documents_preference == 1) {
            $filter = "WHERE (i.idclient='".$idclient."' OR i.idclient IN (SELECT id FROM tusers WHERE parent_id='".$idclient."')) AND i.deleted=".$displaymode;
        } else {
            // Otherwise, fetch only the parent's own ingredients
            $filter = "WHERE i.idclient='".$idclient."' AND i.deleted=".$displaymode;
        }
    } else {
        // The client is a child, so check the parent's qm_documents_preference
        $sql_parent_preference = "SELECT qm_documents_preference FROM tusers WHERE id='".$parent_id."'";
        $parent_pref_result = $dbo->prepare($sql_parent_preference);
        $parent_pref_result->execute();
        $parent_pref_row = $parent_pref_result->fetch(PDO::FETCH_ASSOC);
    
        if ($parent_pref_row && $parent_pref_row["qm_documents_preference"] == 1) {
            // If the parent's preference is 1, fetch all siblings and parent's ingredients
            $filter = "WHERE ((i.idclient IN (SELECT id FROM tusers WHERE parent_id='".$parent_id."')) OR i.idclient='".$parent_id."') AND i.deleted=".$displaymode;
        } else {
            // Otherwise, fetch only the child's own ingredients
            $filter = "WHERE i.idclient='".$idclient."' AND i.deleted=".$displaymode;
        }
    }	    

	$sql = 'SELECT COUNT(id) AS count FROM tqm i '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    $sql = 'SELECT i.id, i.dt, i.policy, i.haccp, i.team, i.training, i.purchasing, i.cleaning, i.production,i.handling, '.
        'i.storage, i.traceability, i.audit, i.analysis, i.flowchart, i.qcertificate, '.
		'i.addoc, i.note, i.deleted from tqm i '.$filter.' '.($prevYears == '1' ? '' : ' AND ( CAST(dt AS UNSIGNED) = YEAR(CURRENT_DATE()) OR
        CAST(dt AS UNSIGNED) = YEAR(CURRENT_DATE()) - 1)'). ' GROUP BY i.id ORDER BY '
		.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder.' LIMIT '.$firstRowIndex.', '.$rowsPerPage;

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
        $response->rows[$i]['cell']=array($row['id'], $row['dt'],$row['policy'],$row['haccp'], $row['team'], $row['training'],$row['purchasing'],
			$row['cleaning'], $row['production'],$row['handling'], $row['storage'], $row['traceability'], $row['audit'],$row['analysis'],
            $row['addoc'],$row['flowchart'], $row['qcertificate'], $row['note'],$row['deleted']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>