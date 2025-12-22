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
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$curPage = $_POST['page'];
	$rowsPerPage = $_POST['rows'];
	$sortingField = $_POST['sidx'];
	$sortingOrder = $_POST['sord'];

	$item = getPostParam('item');
	$ean = getPostParam('ean');
	$spec = getPostParam('spec');
	$addoc = getPostParam('addocs');
	$label = getPostParam('label');
    $ingred = getPostParam('ingred');

	$sortingField = $sortingField == "hcpid" ? 'id' : $sortingField;

	$id = preg_replace("/[^0-9]/", '', getPostParam('hcpid'));

	if(!is_numeric(getGetParam('idclient'))) $idclient = -1;
	else $idclient = getGetParam('idclient');

	if(!is_numeric(getGetParam('conformed'))) $conformed = 0;
	else $conformed = getGetParam('conformed');

    if(!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');
	
	$searching = $_POST['_search'];

	$sql = "SELECT parent_id, products_preference FROM tusers WHERE id='".$idclient."'";
	$rows = $dbo->prepare($sql);
	$rows->execute();
	if ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
		$parent_id = $row["parent_id"];
		$products_preference = $row["products_preference"];
	}
	
	// If parent_id is NULL or 0, the client is a parent
	if ($parent_id == "" || $parent_id == 0) {
		// If the parent prefers all ingredients, fetch parent's and all children's ingredients
		if ($products_preference == 1) {
			$filter = "WHERE (p.idclient='".$idclient."' OR p.idclient IN (SELECT id FROM tusers WHERE parent_id='".$idclient."')) AND p.deleted=".$displaymode;
		} else {
			// Otherwise, fetch only the parent's own ingredients
			$filter = "WHERE p.idclient='".$idclient."' AND p.deleted=".$displaymode;
		}
	} else {
		// The client is a child, so check the parent's products_preference
		$sql_parent_preference = "SELECT products_preference FROM tusers WHERE id='".$parent_id."'";
		$parent_pref_result = $dbo->prepare($sql_parent_preference);
		$parent_pref_result->execute();
		$parent_pref_row = $parent_pref_result->fetch(PDO::FETCH_ASSOC);
	
		if ($parent_pref_row && $parent_pref_row["products_preference"] == 1) {
			// If the parent's preference is 1, fetch all siblings and parent's ingredients
			$filter = "WHERE ((p.idclient IN (SELECT id FROM tusers WHERE parent_id='".$parent_id."')) OR p.idclient='".$parent_id."') AND p.deleted=".$displaymode;
		} else {
			// Otherwise, fetch only the child's own ingredients
			$filter = "WHERE p.idclient='".$idclient."' AND p.deleted=".$displaymode;
		}
	}		
	
	if ($myuser->userdata['isclient'] == '2') { // Auditor
			
		if ($sources_audit = json_decode($myuser->userdata['sources_audit'])) {
			$filter .=' AND (';
			$i = 0;
			foreach ($sources_audit as $source) {
				$filter .= "i.material = '".$source."'" . ($i < count($sources_audit)-1 ? " OR " : "");
				$i++;
			}
			$filter .= ")";
		}
	}
	
	//$filter='WHERE p.idclient='.$idclient.($displaymode == '1' ? ' AND p.deleted=0' :'');
	
	if($searching) // есть inline поиск по столбцам
	{
        if($id!='') $filter.=' AND p.id LIKE "%'.$id.'%"';
		if($item!='') $filter.=' AND p.item LIKE "%'.$item.'%"';
		if($ean!='') $filter.=' AND p.ean LIKE "%'.$ean.'%"';
	}

	$sql = 'SELECT COUNT(*) as count FROM (SELECT DISTINCT GROUP_CONCAT(i.rmcode) as rmc FROM tproducts p '.
		     ' left join tp2i on tp2i.idp=p.id '.
			   ' left join tingredients  i on tp2i.idi=i.id '. $filter.
				 ' group by p.id) s ';
  $rows = $dbo->prepare($sql);
	$rows->execute();
  $uniquerecords = $rows->fetch(PDO::FETCH_ASSOC);
 
	$sql = 'SELECT p.id, p.idclient, p.item, p.ean, p.spec, p.addoc, p.label, '.
			'MIN(r.conf), r.status, p.deleted, DATE_FORMAT(p.created_at, "%d/%m/%Y %H:%i") as created_at_formated, 
			p.created_at, p.deleted_at from tproducts p '.
		'left join tp2i on (tp2i.idp=p.id) '.
		'left join tingredients i on (i.id=tp2i.idi) '.
		'left join (SELECT i.id, IF(COUNT(s.id) = 0, i.conf, IF(s.conf IS NOT NULL AND (COUNT(s.id) - SUM(s.conf)) = 0, 1, 0)) AS conf, 
		GREATEST(MAX(getHalalExpStatus(s.halalexp)), getHalalExpStatus(i.halalexp)) as status from tingredients i '.
		'left join ti2i on (ti2i.idi1=i.id) '.
		'left join tingredients s on (s.id=ti2i.idi2) '.
		'Group by i.id) r on (r.id=i.id) '
		.$filter.' GROUP BY p.id ORDER BY p.'.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder;

	$response = new \stdClass();
    $response->page = 1;
    $response->total = 1;
		$response->uniquerecords = $uniquerecords['count'];

    $i=0;
		$res = $dbo->prepare($sql);
	if(!$res->execute()) die($sql);

    if($ingred != '')
        $filter = ' WHERE nnn LIKE "%'.$ingred.'%"';
    else
        $filter = '';
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
		$sql='select * from ('.
            'SELECT CONCAT("{\"id\":\"",i.id,"\",\"name\":\"RMC_",i.id,"/", REPLACE(i.name,\'"\',\'\\\"\'),"\",\"conf\":\"",IF(COUNT(s.id) = 0, i.conf, IF(s.conf IS NOT NULL AND (COUNT(s.id) - SUM(s.conf)) = 0, 1, 0)),"\", \"status\":\"",GREATEST(MAX(getHalalExpStatus(s.halalexp)), getHalalExpStatus(i.halalexp)), "\"}") as ingred, '.
            ' CONCAT("RMC_",i.id,"/", i.name) as nnn,'.
            ' IF(COUNT(s.id) = 0, i.conf, IF(s.conf IS NOT NULL AND (COUNT(s.id) - SUM(s.conf)) = 0, 1, 0)) AS conf, 
			GREATEST(MAX(getHalalExpStatus(s.halalexp)), getHalalExpStatus(i.halalexp)) as status		  
			from tproducts p '.
				'left join tp2i on (tp2i.idp=p.id) '.
				'left join tingredients i on (i.id=tp2i.idi) '.
				'left join ti2i on (ti2i.idi1=i.id) '.
				'left join tingredients s on (s.id=ti2i.idi2) WHERE p.id='.$row['id'].' AND i.deleted=0 GROUP BY i.id ORDER BY i.id) qq '.$filter;
		$stmt = $dbo->prepare($sql);
		$stmt->execute();
		$ingr = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$str = "[";
		$allconf = 1;
		foreach($ingr as $st){
            if(empty($st['ingred'])) continue;
			if ($st['status'] == 4) {$st['conf'] = 0;}
            $str .= $st['ingred'] . ",";
            $allconf *= $st['conf'];
		}
		$str = rtrim($str, ',')."]";
		if($str == "[]"){
            $allconf = 0;
            if($filter != '') continue;
        }
		// return only nonconformed if filter is set
		if($conformed == 1) {
			if($allconf == 0) {
				$response->rows[$i]['id'] = $row['id'];
				$response->rows[$i]['cell'] = array($row['id'], "HCP_" . $row['id'], $row['item'], $row['ean'], $str, $row['spec'], $row['addoc'], $row['label'],
																						$allconf, $row['status'], $row['deleted'], $row['created_at'], $row['idclient'],$row['deleted'], $row['deleted_at']);
				$i++;
			}
		}else{
			$response->rows[$i]['id'] = $row['id'];
			$response->rows[$i]['cell'] = array($row['id'], "HCP_" . $row['id'], $row['item'], $row['ean'], $str, $row['spec'], $row['addoc'], $row['label'],
																					$allconf, $row['status'], $row['deleted'], $row['created_at'], $row['idclient'],$row['deleted'], $row['deleted_at']);
			$i++;
		}
    }

    $response->records = $i;

    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}	
?>