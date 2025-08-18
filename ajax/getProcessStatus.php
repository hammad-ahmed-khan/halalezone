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
  
    $curPage = $_POST["start"];
    $rowsPerPage = $_POST['length'];

    /*
    $sortingField = $_POST['sidx'];
    $sortingOrder = $_POST['sord'];
    */

    $name = getPostParam('name');
    $email = getPostParam('email');
    $prefix = getPostParam('prefix');
    $login = getPostParam('login');
    $isclient = getPostParam('isclient');
    $idclient = getPostParam('idclient');
    $audit = getPostParam('audit');
    $admin = getPostParam('canadmin');

    $contact_person = getPostParam('contact_person');
    $vat = getPostParam('vat');
    $industry = getPostParam('industry');
    $category = getPostParam('category');
    $state = getPostParam('state');
    $cert_from = getPostParam('cert_from');
    $cert_to = getPostParam('cert_to');
    $last_activity = $_POST['last_activity'] ?? ''; // Assuming the filter value is passed as a POST parameter
    $idauditor = getPostParam('idauditor');    
    $need_attention = $_POST['need_attention'] ?? ''; // Assuming the filter value is passed as a POST parameter

	if(!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');

    //$filter = 'WHERE u.isclient=1 AND IFNULL(last_activity_desc, \'\') <> \'New Registration\' AND IFNULL(u.name, \'\') <> \'\' AND u.deleted='.$displaymode;
    $filter = 'WHERE u.isclient=1 AND IFNULL(u.name, \'\') <> \'\' AND u.deleted='.$displaymode;

    if ($idclient!='') $filter.=" AND u.id = '".$idclient."'";
    if ($industry!='') $filter.=" AND u.industry LIKE '%".$industry."%'";
    if ($category!='') $filter.=" AND u.category LIKE '%".$category."%'";
    if ($state!='') $filter.=" AND app.state = '".$state."'";
    if ($cert_from!='') $filter.=" AND app.CertificateExpiryDate >= '".$cert_from."'";
    if ($cert_to!='') $filter.=" AND app.CertificateExpiryDate <= '".$cert_to."'";
    if ($idauditor != '')  $filter .= " AND a.id = '".$idauditor."'";

    if ($last_activity != '') {
        switch ($last_activity) {
            case 'today':
                $filter .= " AND DATE(app.last_activity_date) = CURDATE()";
                break;
            case 'yesterday':
                $filter .= " AND DATE(app.last_activity_date) = CURDATE() - INTERVAL 1 DAY";
                break;
            case 'last7':
                $filter .= " AND app.last_activity_date >= CURDATE() - INTERVAL 7 DAY";
                break;
            case 'last30':
                $filter .= " AND app.last_activity_date >= CURDATE() - INTERVAL 30 DAY";
                break;
            case 'last2months':
                $filter .= " AND app.last_activity_date >= CURDATE() - INTERVAL 2 MONTH";
                break;
            case 'last6months':
                $filter .= " AND app.last_activity_date >= CURDATE() - INTERVAL 6 MONTH";
                break;
            case 'dateRange':
                $start_date = $_POST['start_date'] ?? '';
                $end_date = $_POST['end_date'] ?? '';
                if ($start_date != '' && $end_date != '') {
                    $filter .= " AND app.last_activity_date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
                }
                break;
        }
    }

     if ($need_attention != '') {
        switch ($need_attention) {
            case 'audit_date_approval_needed':
                $filter .= " AND (app.approved_date1 IS NULL AND (app.audit_date_1 IS NOT NULL OR app.audit_date_2 IS NOT NULL OR app.audit_date_3 IS NOT NULL) AND app.last_activity_date >= CURDATE() - INTERVAL 7 DAY)";
                break;
            case 'audit_plan_not_sent':
                $filter .= " AND (app.approved_date1 IS NOT NULL AND app.id NOT IN(SELECT idapp FROM tdocs WHERE category='audit') AND app.state IN ('dates', 'audit', 'checklist') AND (CURDATE() >= app.approved_date1 - INTERVAL 10 DAY))";
                break;
            case 'signed_offer_awaiting_response':
                $filter .= " AND (app.last_activity_desc = 'Signed offer uploaded' AND app.last_activity_date >= CURDATE() - INTERVAL 7 DAY)";
                break;
            case 'certificate_expiring':
                $filter .= " AND app.CertificateExpiryDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH)";
                break;
            case 'awaiting_signed_offer_upload':
                $filter .= " AND (app.state IN ('soffer') AND app.id NOT IN(SELECT idapp FROM tdocs WHERE category='soffer'))";
                break;
            case 'new_registration':
                $filter .= " AND (IFNULL(app.last_activity_desc, '') = 'New Registration' AND app.id NOT IN(SELECT idapp FROM tdocs WHERE category='offer')) ";
                break;                
            case 'client_last_activity_awaiting_response':
                $filter .= " AND (app.last_activity_by = app.idclient AND app.last_activity_date >= CURDATE() - INTERVAL 7 DAY)";
                break;
        }
    }    

    $sortColumnIndex = $_POST['order'][0]['column'];
$sortDirection = $_POST['order'][0]['dir'];

// Map DataTables column index to database column name
$columns = array(
    'u.name',
    'app.last_activity_date',
    'u.industry',
    'u.category',
    'app.state',
    'u.prodconfirmed',
    'u.ingredconfirmed',
    'app.CertificateExpiryDate',
    'auditors'
);

// Get the corresponding column name from the columns array
$sortBy = $columns[$sortColumnIndex];

	$sql = 'SELECT COUNT(u.id) AS count FROM tusers u left join tcycles cyc ON cyc.idclient = u.id AND cyc.state=1
    left join tapplications app ON app.idcycle = cyc.id
    left join tusers a ON (JSON_CONTAINS(a.clients_audit, JSON_QUOTE(CAST(u.id AS CHAR))) AND a.isclient = 2)
      '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
   $sql = 'SELECT u.id, u.name, u.login, u.pass,u.email, u.prefix, u.isclient, u.application, u.clients, u.audit, u.canadmin, u.prodnumber, 
    
    u.phone, u.address, u.city, u.country, u.contact_person, u.vat, u.industry, u.category, u.ingrednumber, u.prodpublished, u.prodconfirmed, u.ingredpublished, u.ingredconfirmed, u.deleted, 
    app.CertificateExpiryDate,  app.state as process_status, app.notes as notes, app.id as appId, app.last_activity_desc, app.last_activity_by, app.last_activity_date,
 
    (app.approved_date1 IS NULL  AND (app.audit_date_1 IS NOT NULL OR app.audit_date_2 IS NOT NULL OR app.audit_date_3 IS NOT NULL) AND app.last_activity_date >= CURDATE() - INTERVAL 7 DAY) as audit_date_approval_needed,

    (app.approved_date1 IS NOT NULL AND app.id NOT IN(SELECT idapp FROM tdocs WHERE category=\'audit\') AND app.state IN (\'dates\', \'audit\', \'checklist\') AND (CURDATE() >= app.approved_date1 - INTERVAL 10 DAY)) AS audit_plan_not_sent,

    (app.last_activity_desc = \'Signed offer uploaded\' AND app.last_activity_date >= CURDATE() - INTERVAL 7 DAY) AS signed_offer_awaiting_response,

    (app.state IN (\'soffer\') AND app.id NOT IN(SELECT idapp FROM tdocs WHERE category=\'soffer\')) AS awaiting_signed_offer_upload,

    (IFNULL(last_activity_desc, \'\') = \'New Registration\') AS new_registration,

    (app.CertificateExpiryDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 MONTH)) AS certificate_expiring, GROUP_CONCAT(DISTINCT a.id) AS auditorids, GROUP_CONCAT(DISTINCT a.name) AS auditors

    
    from tusers u 
    left join tcycles cyc ON cyc.idclient = u.id AND cyc.state=1
    left join tapplications app ON app.idcycle = cyc.id 
    left join tusers a ON (JSON_CONTAINS(a.clients_audit, JSON_QUOTE(CAST(u.id AS CHAR))) AND a.isclient = 2)
                        
                        '

						.$filter.' GROUP BY u.id ORDER BY '.$sortBy.' '.$sortDirection.'  LIMIT '.$curPage.', '.$rowsPerPage;
 

    $totalCount = $totalRows['count'];
    $data = [];
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
   $expiryDate = $row['CertificateExpiryDate'];

  // Check if expiry date is null or blank
  if (empty($expiryDate)) {
      $days = "-";
  } else {
      // Calculate the remaining days
      $currentDate = date('Y-m-d');
      $remainingDays = floor((strtotime($expiryDate) - strtotime($currentDate)) / (60 * 60 * 24));

      // Display the result
      if ($remainingDays < 0) {
        $days= "Expired";
      } else {
        $days= "$remainingDays";
      }
  }

   $isclient = ($row['isclient'] == '1' ? true : false);

   if ($row["last_activity_date"] != "") {
    $row["last_activity_date"] = strtoupper(date('m/d/Y h:i a', strtotime($row["last_activity_date"])));
   }

   if ($row["last_activity_by"] != "") {
    $row["last_activity_date"] .= ' by '.$row["last_activity_by"].'<br/><b>'.$row["last_activity_desc"].'</b>';
}

if ($row["last_activity_date"] != "") {
    $row["last_activity_date"] .= '<br/><br/><a href="#" class="activity-log" data-idapp="'. $row['appId'].'">Full Activity Log</a>';
}

    $address = "";
    $row["user_data"] = '<span class="bold">' . $row['name'] . '</span>';
    if ($row["contact_person"] != "") {
        $row["user_data"] .= '<br>';
        $row["user_data"] .= '<span class="small"><span class="glyphicon glyphicon-user"></span> ' . $row["contact_person"] . '</span>';
    }    
    if (!empty($row["address"])) {
        $address .= '<span class="small"><span class="glyphicon glyphicon-map-marker"></span>' . $row["address"] . '</span>';
    }
    if (!empty($row["address"]) && !empty($row["city"])) {
        $address .= ', ';
    }
    if (!empty($row["city"])) {
        $address .= $row["city"];
    }
    if (!empty($row["city"]) && !empty($row["country"])) {
        $address .= ', ';
    }
    if (!empty($row["country"])) {
        $address .= $row["country"];
    }
    if (!empty($address)) {
        $row["user_data"] .= '<br>' . $address ;
    }
    $emails = explode(',', $row["email"]);
    $email = trim($emails[0]);
    $row["user_data"] .= '<br>';
    $row["user_data"] .= '<span class="small"><span class="glyphicon glyphicon-envelope"></span> <a href="mailto:' . $email . '">' . $email . '</a></span>';
    if ($row["phone"] != "") {
        $row["user_data"] .= '<br>';
        $row["user_data"] .= '<span class="small"><span class="glyphicon glyphicon-earphone"></span> ' . $row["phone"] . '</span>';
    }
    if ($row["vat"] != "") {
        $row["user_data"] .= '<br>';
        $row["user_data"] .= '<span class="small">VAT: ' . $row["vat"] . '</span>';
    }

    $row["user_data"] .= '<br/><a href="#" class="edit-client" id="'.$row['id'].'"><i class="fa fa-pencil"></i> Edit</a>';

    // Count pending customer service requests
    $query = "SELECT COUNT(*) FROM tcustomerservice WHERE status = 1 AND user_id = :idclient";
    $stmt = $dbo->prepare($query);
    $stmt->bindParam(':idclient', $row["id"], PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    // Build the badge HTML if count > 0
    $badge = '';
    if ($count > 0) {
        $badge = ' <span class="badge badge-red">'.$count.'</span>';
    }

    // Append the Customer Support link with optional badge
    $row["user_data"] .= '<br/><a href="#" class="client-tickets" id="'.$row['id'].'" data-name="'.getClientInfo($row['id']).'"><i class="fa fa-envelope"></i> Customer Support' . $badge . '</a>';

     // Count pending customer service requests
    $query = "SELECT COUNT(*) FROM ttasks WHERE status = 1 AND idclient = :idclient";
    $stmt = $dbo->prepare($query);
    $stmt->bindParam(':idclient', $row["id"], PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    // Build the badge HTML if count > 0
    $badge = '';
    if ($count > 0) {
        $badge = ' <span class="badge badge-red">'.$count.'</span>';
    }


    $row["user_data"] .= '<br/><a href="#" class="client-tasks" id="'.$row['id'].'" data-name="'.getClientInfo($row['id']).'" data-auditors="'.$row['auditorids'].'"><i class="fa fa-tasks"></i> Assign Tasks' . $badge . '</a>';

    $row["notes"] = $row['appId'] == "" ? "": nl2br($row["notes"]).' <a href="#" class="glyphicon glyphicon-pencil edit-notes" data-idapp="'. $row['appId'].'" data-idclient="'. $row['id'].'"></a>';
    
    $row["products"]  = '<div class="infobox-container">
    <div class="infobox infobox-green">
        <div class="infobox-data">
            <span class="infobox-data-number">' . $row['prodnumber'] . '</span>
            <div class="infobox-content">Allo</div>
        </div>
        <div class="infobox-data">
            <span class="infobox-data-number">' . $row['prodpublished'] . '</span>
            <div class="infobox-content">Pub</div>
        </div>
    </div>
    <div class="infobox infobox-green">
        <div class="infobox-data">
            <span class="infobox-data-number">' . $row['prodconfirmed'] . '</span>
            <div class="infobox-content">Cnf</div>
        </div>
    </div>
    <div class="infobox infobox-green">
        <div class="infobox-data">
            <span class="infobox-data-number">' . max(0, $row['prodnumber'] - $row['prodconfirmed']) . '</span>
            <div class="infobox-content">Rem</div>
        </div>
    </div>
    <div class="infobox infobox-red">
        <div class="infobox-data">
            <span class="infobox-data-number">' . max(0, $row['prodconfirmed'] - $row['prodnumber']) . '</span>
            <div class="infobox-content">Exc</div>
        </div>
    </div>
</div>';

$row["ingredients"]  = '<div class="infobox-container">
    <div class="infobox infobox-green2">
        <div class="infobox-data">
            <span class="infobox-data-number">' . $row['ingrednumber'] . '</span>
            <div class="infobox-content">Allo</div>
        </div>
        <div class="infobox-data">
            <span class="infobox-data-number">' . $row['ingredpublished'] . '</span>
            <div class="infobox-content">Pub</div>
        </div>
    </div>
    <div class="infobox infobox-green2">
        <div class="infobox-data">
            <span class="infobox-data-number">' . $row['ingredconfirmed'] . '</span>
            <div class="infobox-content">Cnf</div>
        </div>
    </div>
    <div class="infobox infobox-green2">
        <div class="infobox-data">
            <span class="infobox-data-number">' . max(0, $row['ingreddnumber'] - $row['ingredconfirmed']) . '</span>
            <div class="infobox-content">Rem</div>
        </div>
    </div>
    <div class="infobox infobox-red">
        <div class="infobox-data">
            <span class="infobox-data-number">' . max(0, $row['ingredconfirmed'] - $row['prodnumber']) . '</span>
            <div class="infobox-content">Exc</div>
        </div>
    </div>
</div>';

$process_status = getAppStateName($row["process_status"]);
if ($row["process_status"] == 'report') {
    $query = "SELECT id FROM tauditreport WHERE Type='Major' AND Status=0 AND idclient=:idclient AND idapp=:idapp
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $row["id"], PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $row["appId"], PDO::PARAM_STR);
				$stmt->execute();
				if ($stmt->fetchColumn()) {
					 $process_status .= ' <span class="glyphicon glyphicon-info-sign" style="color: red;" title="Please correct all major deviations"></span>';
				}
}

$row["process_status"] = $process_status;

$row["days"] = $days;
/*

$auditors = [];
    $idclient_json = json_encode((string)$row['id']); // Ensures it's passed as JSON string, e.g. "1"

$query = "SELECT name FROM tusers 
          WHERE isclient = 2 AND JSON_CONTAINS(clients_audit, :idclient_json)";
$stmt = $dbo->prepare($query);
$stmt->bindParam(':idclient_json', $idclient_json, PDO::PARAM_STR);
$stmt->execute();

$names = $stmt->fetchAll(PDO::FETCH_COLUMN);
if ($names) {
    foreach ($names as $name) {
        $auditors[] = $name;
    }
}

$row["auditors"] = implode(", ", $auditors);
*/

/*

Bouvard Italia S.p.A.  EZx20W&qIS
    $row["status"] .= '<div class="flex-item handshake"><i class="fa fa-handshake-o fa-fw"></i><br>' . getAppStateName($row["process_status"]) . '</div>';
    $row["status"] .= '<div class="flex-item wrench"><i class="fa fa-wrench fa-fw"></i><br>' . $row['prodnumber'].'/'.$prodConfirmed . '</div>';
    $row["status"] .= '<div class="flex-item flask"><i class="fa fa-flask fa-fw"></i><br>' . $row['ingrednumber'].'/'.$ingredConfirmed . '</div>';
    $row["status"] .= '<div class="flex-item certificate"><i class="fa fa-certificate fa-fw"></i><br>' . $days . '</div>';
    $row["status"] .= '</div>';
    */
    $data[] = $row;
}

$responseData = [
    'data' => $data, 
    'recordsTotal' => $totalCount,
    'recordsFiltered' => $totalCount,
];

echo json_encode($responseData);

}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>
