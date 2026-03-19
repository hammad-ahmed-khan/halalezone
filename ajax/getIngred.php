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
    $curPage = $_POST['page'] ;
    $rowsPerPage = $_POST['rows'];
    $sortingField = $_POST['sidx'];
    $sortingOrder = $_POST['sord'];

	$name = getPostParam('name');
	$rmcode = getPostParam('rmcode');
	$material = getPostParam('material');
	$supplier = getPostParam('supplier');
    $producer = getPostParam('producer');
	$halalcert = getPostParam('halalcert');
	$tasks = getPostParam('tasks');
	$cb = getPostParam('cb');
	$conf = getPostParam('conf');
	$sub = getPostParam('sub');
	$halalexp = getPostParam('date');
	$facilityId = getPostParam('facilityId');

	$sortingField = $sortingField == "rmid" ? 'id' : $sortingField;

    $id = preg_replace("/[^0-9]/", '', getPostParam('rmid'));

	if(!is_numeric(getGetParam('idclient'))) $idclient = -1;
	else $idclient = getGetParam('idclient');

    if(!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');

    $searching = $_POST['_search'];

    // If idclient = -2 (special value), do not return any records.
    if ($idclient === -2) {
        $response = new \stdClass();
        $response->page = 1;
        $response->total = 0;
        $response->records = 0;
        $response->rmcrecords = 0;

        $response->rows = [];
        echo json_encode($response);
        die();
    }

	//if ($myuser->userdata['isclient'] == '1') { // Client
 
		if ($idclient == -1) {
			$filter='WHERE i.deleted='.$displaymode;
		}
		else {
 			$sql = "SELECT parent_id, ingredients_preference FROM tusers WHERE id='".$idclient."'";
			$rows = $dbo->prepare($sql);
			$rows->execute();
			if ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
				$parent_id = $row["parent_id"];
				$ingredients_preference = $row["ingredients_preference"];
			}
			
			// If parent_id is NULL or 0, the client is a parent
			if ($parent_id == "" || $parent_id == 0) {
				// If the parent prefers all ingredients, fetch parent's and all children's ingredients
				if ($ingredients_preference == 1) {
					$filter = "WHERE (i.idclient='".$idclient."' OR i.idclient IN (SELECT id FROM tusers WHERE parent_id='".$idclient."')) AND i.deleted=".$displaymode;
				} else {
					// Otherwise, fetch only the parent's own ingredients
					$filter = "WHERE i.idclient='".($facilityId != "" ? $facilityId : $idclient)."' AND i.deleted=".$displaymode;
				}
			} else {
				// The client is a child, so check the parent's ingredients_preference
				$sql_parent_preference = "SELECT ingredients_preference FROM tusers WHERE id='".$parent_id."'";
				$parent_pref_result = $dbo->prepare($sql_parent_preference);
				$parent_pref_result->execute();
				$parent_pref_row = $parent_pref_result->fetch(PDO::FETCH_ASSOC);
			
				if ($parent_pref_row && $parent_pref_row["ingredients_preference"] == 1) {
					// If the parent's preference is 1, fetch all siblings and parent's ingredients
					$filter = "WHERE ((i.idclient IN (SELECT id FROM tusers WHERE parent_id='".$parent_id."')) OR i.idclient='".$parent_id."') AND i.deleted=".$displaymode;
				} else {
					// Otherwise, fetch only the child's own ingredients
					$filter = "WHERE i.id IN (SELECT ingredient_id FROM tingredient_facilities WHERE facility_id='".$idclient."') AND i.deleted=".$displaymode;
				}
			}										
		}
	//}

	

	//$filter='WHERE i.idclient='.$idclient.($displaymode == '1' ? ' AND i.deleted=0' :'');
	if ($myuser->userdata['isclient'] == '2') { // Auditor
		$clients_audit = json_decode($myuser->userdata['clients_audit']);
		$filter .=' AND i.idclient IN ('.implode(",", $clients_audit).')';
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
	$searching = $_POST['_search'];

	if($searching) // есть inline поиск по столбцам
	{
		if ( $name !='' ) {
			$filter.=' AND i.name LIKE "%'.$name.'%"';
		}
		else {
			$filter.=" AND IFNULL(i.name, '') <> ''";
		}
		if($rmcode!='') {
			$filter.=' AND i.rmcode LIKE "%'.$rmcode.'%"';
		}
		else {
			$filter.=" AND IFNULL(i.rmcode, '') <> ''";
		}
		if($material!='') $filter.=' AND i.material LIKE "%'.$material.'%"';
		if($supplier!='') $filter.=' AND i.supplier LIKE "%'.$supplier.'%"';
		if($producer!='') $filter.=' AND i.producer LIKE "%'.$producer.'%"';
		if($halalcert!='') $filter.=' AND i.halalcert = '.$halalcert;
		if($cb!='') $filter.=' AND i.cb LIKE "%'.$cb.'%"';
		//if($conf!='') $filter.=' AND i.conf = '.$conf;
		if($sub!='') $filter.=' AND i.sub LIKE "%'.$sub.'%"';
		if($id!='') $filter.=' AND i.id = "'.$id.'"';
		if($tasks!='') {
		   if ($tasks == 1) $filter.=' AND (select count(id) as cc from td2i where td2i.status < 2 and idi=i.id) > 0 ';
		   else $filter.=' AND (select count(id) as cc from td2i where td2i.status < 2 and idi=i.id) = 0 ';
        }
		if($halalexp!='')
		{
			$date = str_replace('/', '-', $halalexp);
			$filter.=' AND i.halalexp="'.date('Y-m-d', strtotime($date)).'"';
		}

		if($facilityId !='') $filter.=" AND i.id IN (SELECT ingredient_id FROM tingredient_facilities WHERE facility_id='".$facilityId."')";
	}

	$sql = 'SELECT COUNT(id) AS count, COUNT(DISTINCT(rmcode)) as rmccount FROM tingredients i '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $rowsPerPage   = intval($rowsPerPage) === 1000000 ? 0 : intval($rowsPerPage);
    $curPage       = $rowsPerPage ? $_POST['page'] : 1;
    $firstRowIndex = $rowsPerPage ? $curPage * $rowsPerPage - $rowsPerPage : 0;

    $postCondition = ($conf!='' ? ' HAVING conf1 = '.$conf .($conf != '1' ? ' OR `status` = 4' :  ' AND `status` <> 4'): '');
    $order         = strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder;
    $limit         = $rowsPerPage ? "LIMIT $firstRowIndex, $rowsPerPage" : '';

/*
    $sql = <<<EOL
SELECT i.id, i.idclient, i.name, i.rmcode, i.material,
GROUP_CONCAT(
    CASE WHEN s.id IS NOT NULL THEN
        JSON_OBJECT(
            "id", s.id,
            "name", CONCAT("RMC_", s.id, "/", s.name),
            "conf", s.conf,
            "status", getHalalExpStatus(s.halalexp)
        )
    END
) as ingred,
       i.supplier,i.producer,  i.statement, i.stmt_exp_date, i.halalcert, i.cert, i.cb, i.halalexp, i.rmposition, i.spec, i.quest, i.note, i.addoc,
       DATE_FORMAT(i.created_at, "%d/%m/%Y %H:%i") as created_at_formated, i.created_at,
       IF(COUNT(s.id) = 0, i.conf, IF(s.conf IS NOT NULL AND (COUNT(s.id) - SUM(s.conf)) = 0, 1, 0)) AS conf1,
	    GREATEST(MAX(getHalalExpStatus(s.halalexp)), getHalalExpStatus(i.halalexp)) as status,
	    i.deleted, i.sub, IFNULL(i.id_paingred, 0) AS id_paingred,

	    u.name as user_name,
	    COUNT(tasks.id) AS tasksnumber,
	    IF(COUNT(tasks.id) > 0, 1, 0) AS tasks

FROM tingredients i
LEFT JOIN ti2i on (ti2i.idi1=i.id)
LEFT JOIN tingredients s on (s.id=ti2i.idi2)
LEFT JOIN tusers u on (u.id=i.idclient)
LEFT JOIN td2i tasks ON (tasks.status < 2 and idi=i.id)

$filter

GROUP BY i.id

$postCondition

ORDER BY $order

$limit;
EOL;
*/

$sql = <<<EOL
SELECT 
    i.id, 
    i.idclient, 
    i.name, 
    i.rmcode, 
    i.material,
    
    GROUP_CONCAT(
        CASE WHEN s.id IS NOT NULL THEN
            JSON_OBJECT(
                "id", s.id,
                "name", CONCAT("RMC_", s.id, "/", s.name),
                "conf", s.conf,
                "status", 
                CASE
                    WHEN DATEDIFF(s.halalexp, CURDATE()) <= 0 THEN 4
                    WHEN DATEDIFF(s.halalexp, CURDATE()) <= 7 THEN 3
                    WHEN DATEDIFF(s.halalexp, CURDATE()) <= 28 THEN 2
                    WHEN DATEDIFF(s.halalexp, CURDATE()) <= 56 THEN 1
                    ELSE 0
                END
            )
        END
    ) AS ingred,
    
    i.supplier,
    i.producer,
    i.statement,
    i.stmt_exp_date,
    i.halalcert,
    i.cert,
    i.cb,
    i.halalexp,
    i.rmposition,
    i.spec,
    i.quest,
    i.note,
    i.addoc,
    DATE_FORMAT(i.created_at, "%d/%m/%Y %H:%i") AS created_at_formated,
    i.created_at,
    
    IF(
        COUNT(s.id) = 0, 
        i.conf, 
        IF(s.conf IS NOT NULL AND (COUNT(s.id) - SUM(s.conf)) = 0, 1, 0)
    ) AS conf1,

    GREATEST(
        COALESCE(MAX(
            CASE
                WHEN DATEDIFF(s.halalexp, CURDATE()) <= 0 THEN 4
                WHEN DATEDIFF(s.halalexp, CURDATE()) <= 7 THEN 3
                WHEN DATEDIFF(s.halalexp, CURDATE()) <= 28 THEN 2
                WHEN DATEDIFF(s.halalexp, CURDATE()) <= 56 THEN 1
                ELSE 0
            END
        ), 0),
        CASE
            WHEN DATEDIFF(i.halalexp, CURDATE()) <= 0 THEN 4
            WHEN DATEDIFF(i.halalexp, CURDATE()) <= 7 THEN 3
            WHEN DATEDIFF(i.halalexp, CURDATE()) <= 28 THEN 2
            WHEN DATEDIFF(i.halalexp, CURDATE()) <= 56 THEN 1
            ELSE 0
        END
    ) AS status,

    i.deleted, 
    i.sub, 
    IFNULL(i.id_paingred, 0) AS id_paingred,
    u.name AS user_name,
    
     COALESCE(task_data.tasksnumber, 0) AS tasksnumber,
    CASE WHEN task_data.tasksnumber > 0 THEN 1 ELSE 0 END AS tasks,
    COALESCE(task_data.html_table, '') AS task_html,

	COALESCE(
        GROUP_CONCAT(
            DISTINCT tf.facility_id
            ORDER BY facilities.name
            SEPARATOR ','
        ), 
        ''
    ) AS facility_ids,
    
     COALESCE(
        GROUP_CONCAT(
            DISTINCT CONCAT(facilities.name, ' (', facilities.prefix, facilities.id, ')')
            ORDER BY facilities.name
            SEPARATOR '<br/>'
        ), 
        ''
    ) AS assigned_facilities

FROM tingredients i
LEFT JOIN ti2i ON ti2i.idi1 = i.id
LEFT JOIN tingredients s ON s.id = ti2i.idi2
LEFT JOIN tusers u ON u.id = i.idclient
LEFT JOIN (
    -- INLINE TASKS TABLE
    SELECT 
        td2i.idi, 
        COUNT(*) as tasksnumber,
        CONCAT(
            '<table class="table table-bordered table-condensed" style="width:100%;">',
            '<thead><tr><th width="50%">Deviation</th><th width="50%">Measure</th></tr></thead>',
            '<tbody>',
            GROUP_CONCAT(
                CONCAT('<tr><td>', d.deviation, '</td><td>', d.measure, '</td></tr>') 
                ORDER BY d.id
                SEPARATOR ''
            ),
            '</tbody></table>'
        ) as html_table
    FROM td2i
    INNER JOIN tdeviations d ON d.id = td2i.idd
    WHERE td2i.status < 2
    GROUP BY td2i.idi
) task_data ON task_data.idi = i.id
LEFT JOIN tingredient_facilities tf ON tf.ingredient_id = i.id AND tf.status = 1
LEFT JOIN tusers facilities ON facilities.id = tf.facility_id

{$filter}

GROUP BY i.id

{$postCondition}

ORDER BY {$order}

{$limit};
EOL;

    $response = new \stdClass();
    $response->page = $curPage;
    $response->total = $rowsPerPage ? ceil($totalRows['count'] / $rowsPerPage) : 1;
    $response->records = $totalRows['count'];
    $response->rmcrecords = $totalRows['rmccount'];

    $i=0;
    $res = $dbo->prepare($sql);

	if(!$res->execute()) die($sql);

    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $response->rows[$i]['id']=$row['id'];
		if ($row['status'] == '4') {
			$row['conf1'] = 0;
		}

		$response->rows[$i]['cell']=array($row['id'], $row["assigned_facilities"], "RMC_".$row['id'],$row['rmcode'],$row['name'], $row['tasks'],$row['conf1'], $row['tasksnumber'],$row['sub'], $row['supplier'],
		    $row['producer'],$row['material'],$row['halalcert'],
			$row['cert'],$row['cb'], $row['halalexp'], $row['rmposition'], "[".$row['ingred']."]", $row['spec'], $row['quest'], $row['statement'] , $row['stmt_exp_date'],  $row['addoc'],
			$row['note'],$row['status'],  $row['created_at'],$row['deleted'], $row['id_paingred'], $row['facility_ids'], $row['task_html']);
        $i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>
