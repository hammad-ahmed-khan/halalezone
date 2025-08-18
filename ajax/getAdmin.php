<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // ??????? ?????? ??????????? ? ??

		$myuser = cuser::singleton();
		$myuser->getUserData();
    
  $isClient = $myuser->userdata['isclient'] == "1" ? true : false;
  $isAuditor = $myuser->userdata['isclient'] == '2' ? true : false;
  $isSuperAdmin = $myuser->userdata['superadmin'] == "1" ? true : false;
  $isAdmin = (!$isClient && !$isAuditor && !$isSuperAdmin);

    $curPage = $_POST['page'];
    $rowsPerPage = $_POST['rows'];
    $sortingField = $_POST['sidx'];
    $sortingOrder = $_POST['sord'];

    $name = getPostParam('name');
    $type = getPostParam('type');
    $company = getPostParam('company');
    $email = getPostParam('email');
    $prefix = getPostParam('prefix');
    $login = getPostParam('login');
    $isclient = getPostParam('isclient');
    $client = getPostParam('client');
    $audit = getPostParam('audit');
    $admin = getPostParam('canadmin');

    $contact_person = getPostParam('contact_person');
    $vat = getPostParam('vat');
    $industry = getPostParam('industry');
    $category = getPostParam('category');

		if(!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');

    $searching = $_POST['_search'];

		$filter='WHERE IFNULL(u.name, \'\') <> \'\' AND u.deleted='.$displaymode;

    if($searching) // ???? inline ????? ?? ????????
    {
        if($name!='') $filter.=' AND u.name LIKE "%'.$name.'%"';

        if($type == '1')  {
          $filter.=' AND u.parent_id IS NULL';
        }
        elseif($type == '2')  {
          $filter.=' AND u.parent_id IS NOT NULL';
        }

        if($company!='') $filter.=' AND u.parent_id IN (SELECT id FROM tusers WHERE name LIKE "%'.$company.'%")';
        if($email!='') $filter.=' AND u.email LIKE "%'.$email.'%"';
        if($prefix!='') $filter.=' AND u.prefix LIKE "%'.$prefix.'%"';
        if($login!='') $filter.=' AND u.login LIKE "%'.$login.'%"';
        if($isclient!='') $filter.=' AND u.isclient = "'.$isclient.'"';
        if($client!='') $filter.=' AND u.client LIKE "%'.$client.'%"';
        if($audit!='') $filter.=' AND u.audit LIKE "%'.$audit.'%"';
        if($admin!='') $filter.=' AND u.canadmin LIKE "%'.$admin.'%"';

        if($contact_person!='') $filter.=' AND u.contact_person LIKE "%'.$contact_person.'%"';
        if($vat!='') $filter.=' AND u.vat LIKE "%'.$vat.'%"';
        if($industry!='') $filter.=' AND u.industry = "'.$industry.'"';
        if($category!='') $filter.=' AND u.category LIKE "%'.$category.'%"';
    }

    if ($isAdmin) {
      $filter.=' AND u.isclient = 2';
    }

	$sql = 'SELECT COUNT(id) AS count FROM tusers u '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    /*
    $sql = 'SELECT u.id, u.parent_id, u.name, u.login, u.pass,u.email, u.prefix, u.isclient, u.application, u.clients, u.audit, u.canadmin, u.prodnumber, 
    
    u.contact_person, u.vat, u.industry, u.category, u.ingrednumber, u.deleted, IF(count(a.id) >= 3, 1, 0) as blocked, c.name as company, u.company_admin,
    app.CertificateExpiryDate,  app.state as process_status
    
     from tusers u 
    left join tcycles cyc ON cyc.idclient = u.id AND cyc.state=1
    left join tapplications app ON app.idcycle = cyc.id 
    
    

                        left join tusers c on u.parent_id = c.id
                        
                        left join attempts a on u.id=a.iduser AND a.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)'

						.$filter.' GROUP BY u.id ORDER BY '.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder.' LIMIT '.$firstRowIndex.', '.$rowsPerPage;
            */

            $sql = 'SELECT 
    u.id, 
    u.parent_id, 
    CASE 
        WHEN u.parent_id IS NOT NULL THEN CONCAT(\'   +-- \', u.name)  -- Indent child names
        ELSE u.name 
    END AS name , 
    u.login, 
    u.pass, 
    u.email, 
    u.prefix, 
    u.isclient, 
    u.application, 
    u.clients, 
    u.audit, 
    u.canadmin, 
    u.prodnumber, 
    u.contact_person, 
    u.vat, 
    u.industry, 
    u.category, 
    u.ingrednumber, 
    u.deleted, 
    MAX(IF(a.id IS NOT NULL, 1, 0)) AS blocked,  -- Avoids duplicate values
    c.name AS company, 
     MAX(app.CertificateExpiryDate) AS CertificateExpiryDate, -- Avoid duplicates
    MAX(app.state) AS process_status -- Avoid duplicates
FROM tusers u
LEFT JOIN tcycles cyc ON cyc.idclient = u.id AND cyc.state = 1
LEFT JOIN tapplications app ON app.idcycle = cyc.id
LEFT JOIN tusers c ON u.parent_id = c.id  -- Joining to get parent name
LEFT JOIN attempts a ON u.id = a.iduser AND a.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
'.$filter.'
GROUP BY u.id  -- Ensures only one row per user
ORDER BY 
    COALESCE(c.name, u.name),  -- Sort by parent name (or self if no parent)
    u.parent_id IS NOT NULL,   -- Ensure children come under their parent
    u.name
 LIMIT '.$firstRowIndex.', '.$rowsPerPage;

    //????????? ????? ??????? ????????, ????? ?????????? ??????? ? ????? ?????????? ???????
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
   $expiryDate = $row['CertificateExpiryDate'];

  // Check if expiry date is null or blank
  if (empty($expiryDate)) {
      $days = "No expiry date set";
  } else {
      // Calculate the remaining days
      $currentDate = date('Y-m-d');
      $remainingDays = floor((strtotime($expiryDate) - strtotime($currentDate)) / (60 * 60 * 24));

      // Display the result
      if ($remainingDays < 0) {
        $days= "Expired";
      } elseif ($remainingDays == 0) {
        $days= "Expires today";
      } else {
        $days= "$remainingDays days remaining";
      }
  }

  // all client confirmed products
  $prodConfirmed = 0;
  $sql = "select count(pp.id) as count from ".
  " (SELECT p.id, IF(count(i.id)-SUM(IF(i.conf is NULL, 0, i.conf))=0 AND count(si.id)-SUM(IF(si.conf is NULL, 0, si.conf))=0, 1, 0) as conf from tproducts p ".
  " left join tp2i on (tp2i.idp=p.id) ".
  " left join tingredients i on (i.id=tp2i.idi) ".
  " left join ti2i on (ti2i.idi1=i.id) ".
  " left join tingredients si on (si.id=ti2i.idi2) ".
  " where p.idclient=:idclient group by p.id ) pp WHERE pp.conf=1";
$stmt = $dbo->prepare($sql);
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->bindValue(':idclient', $row['id']);
if($stmt->execute()) {
    $prodConfirmed = $stmt->fetch()['count']*1;
}

$ingredConfirmed=0;
  $sql = "select count(id) count from tingredients WHERE idclient=:idclient and conf=1";
  $stmt = $dbo->prepare($sql);
  $stmt->setFetchMode(PDO::FETCH_ASSOC);
  $stmt->bindValue(':idclient', $row['id']);
  if ($stmt->execute()) {
    $ingredConfirmed = $stmt->fetch()['count']*1;
  }

  $isclient = ($row['isclient'] == '1' ? true : false);

  if (trim($row['parent_id']) == "") {
    $row['type'] = "Company";
} else {
    $row['type'] = "Facility";
}
 
/*
  $response->rows[$i]['cell'] = array($row['id'],$row['name'],$row['company'], $row['email'],$isclient ? $row['prefix']:"-",$row['login']
  ,$isclient?$row['contact_person']:"-",$isclient?$row['vat']:'-',$isclient?$row['industry']:'-',$isclient?$row['category']:'-'
  ,$isclient?getAppStateName($row['process_status']):"-",$isclient? $days:"-", 
  $isclient?$row['prodnumber'].'/'.$prodConfirmed:"-", $isclient?$row['ingrednumber'].'/'.$ingredConfirmed:"-", $row['isclient'], $row['deleted'], $row['blocked']);
  */

  //$response->rows[$i]['cell'] = array($row['id'],$row['name'],$row['company'] .($row['company_admin'] == '1' ? ' <span style="color:blue;">(Company Admin)</span>' : ''), $row['email'],$isclient ? $row['prefix']:"-",$row['login']
  $response->rows[$i]['cell'] = array($row['id'],$row['name'],$row['type'],  $row['email'],$isclient ? $row['prefix']:"-",$row['login']
  
  , $row['isclient'], $row['blocked'], $row['deleted']);
$i++;
    }
    echo json_encode($response);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>
