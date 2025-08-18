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
//	$myuser->sec_session_start();
	$myuser->getUserData();
	$pageNumber = $_POST["start"];
	$pageSize = $_POST["length"];
	$s = trim($_POST["s"]);
	$idparent = $_POST["idparent"] ;
	$idclient = $_POST["idclient"] == "" ? -1 : $_POST["idclient"] ;
	$idapp =  $_POST["idapp"] == "" ? -1 : $_POST["idapp"];
	$category = trim($_POST["category"]);
	$actions = trim($_POST["actions"]);
	$deleted = $_POST["deleted"] ?? '0';		
	$TotalCount = 0;
	$criteria = " AND d.deleted=".$deleted;

	if ($s != "") {
		$criteria .= " AND d.filename LIKE '%".$s."%'";
	}
	if ($idparent != "") {
		$criteria .= " AND d.idparent = '".$idparent."'";
	}
	else {
		$criteria .= " AND d.idparent IS NULL ";
	}
	if ($idclient != "") {
		$criteria .= " AND d.idclient = '".$idclient."'";
	}
	if ($idapp != "") {
		$criteria .= " AND d.idapp = '".$idapp."'";
	}
	if ($category != "") {
		$criteria .= " AND d.category = '".$category."'";
	}

	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = "SELECT COUNT(id) AS count FROM tdocs AS d WHERE 1=1 $criteria";
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $TotalCount = $rows->fetchColumn();
	
	$query = "SELECT d.id, d.title as Title, d.filename AS FileName, d.hostpath, date_format(d.created_at, '%d/%m/%Y %h:%i %p') AS Uploaded, d.deleted, d.comments AS Comments, d.signature AS Signature, if (u.isclient=0, 'Admin', u.name) AS UserName, COUNT(c.id) as hasChildren
	FROM tdocs AS d
	LEFT OUTER JOIN tusers AS u ON d.iduser = u.id		
	LEFT OUTER JOIN tdocs AS c ON (d.id = c.idparent AND c.deleted = 0)		

	WHERE 1=1
	$criteria
	Group by d.id
	ORDER BY d.id ASC ".($idparent != "" ? "" :"
	LIMIT ".$pageNumber." ,".$pageSize);
	
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);  
	
	$data = array();
	foreach($docs as $d) {
		$data[] = $d;
	}
	
	$i = 0;
	foreach ($data as $key=>$val) {

		$data[$i]['hostpath'] = str_replace('"', '', $data[$i]['hostpath']);

		// add new button
		//$data[$i]['UserName'] = $data[$i]['UserName'].'<br />'.$data[$i]['Uploaded'];
		$data[$i]['button'] = '';
		if (!$deleted) {
			//if($myuser->userdata['isclient'] && $data[$i]['Signature'] == '1') {
			if($data[$i]['Signature'] == '1') {
				$data[$i]['button'] .= '<a href="#" id="'.$data[$i]['id'].'" data-title="'.$data[$i]['Title'].'" class="btn btn-success btn-sign">
							   <span class="fa fa-signature" title="Upload Signed Document" aria-hidden="true"></span></a> ';
				}

		  $data[$i]['button'] .= '<a href="'.$data[$i]['hostpath'].$data[$i]['FileName'].'" id="'.$data[$i]['id'].'" target="_blank" class="btn btn-primary">
							   <span class="glyphicon glyphicon-download-alt" title="Download Document" aria-hidden="true"></span>
							   </a> '.($myuser->userdata['isclient'] != 1 ? 
							  '<a href="#" id="'.$data[$i]['id'].'" data-category="'.$category.'" class="btn btn-danger btndel-doc">
							   <span class="glyphicon glyphicon-trash" title="Delete Document" aria-hidden="true"></span>
							   </a>':'');
		
		}

		$icon = "";
		if (strpos(strtolower($data[$i]['FileName']), ".xls") !== FALSE) {
			$icon = "xls.png";
		}
		else {
			$icon = "pdf.png";
		}
		
		//$data[$i]['Signature'] = $data[$i]['Signature'] == '1' ? 'Yes' : 'No' ;
		
		$data[$i]['FileName'] = '<a href="'.$data[$i]['hostpath'].$data[$i]['FileName'].'" target="_blank"> <img src="/img/'.$icon.'" style="max-width:35px;" />'.$data[$i]['FileName'].'</a>';
		
		$i++;
	}

	$datax = array(
	'recordsTotal' => $TotalCount,
	'recordsFiltered' => $TotalCount,	
	'data' => $data);
	echo json_encode($datax);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>