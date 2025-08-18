<?php
@session_start();
include_once "../../config/config.php";
include_once "../../classes/users.php";
include_once "../../includes/func.php";
include_once "../../notifications/notifyfuncs.php";
require('../GoogleDriveFunctions.php');

define('LOCAL_FILE_DIR','files');
define('DRIVE_FILE_DIR','CRM');

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$myuser = cuser::singleton();
	$myuser->getUserData();
		
	$error = "";
	$category = $_POST["category"];
	$ID = $_POST["ID"];
	$idapp = $_POST["idapp"];
	$idclient = $_POST["idclient"];
	$Title = $_POST["Title"];
	$Comments = $_POST["Comments"];

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$user = $stmt->fetch();

	$decode = file_get_contents( __DIR__ ."/../../config.json");
	$config=json_decode($decode, TRUE);
	$client = $user["name"]. ' ('.$user["id"].')';
	$ext = pathinfo($_FILES['filedata']['name'], PATHINFO_EXTENSION);
	$errors = '';
	
	if (trim($ID) == "") {
		$errors .= "<li>ID is required.</li>";
	}
	if (trim($Title) == "") {
		$errors .= "<li>Title is required.</li>";
	}
	if (strtolower($ext) != "pdf" || $_FILES["filedata"]["type"] != "application/pdf") {
		$errors .= "<li>Invalid file.</li>";
	}
	
	if ($errors == "") {

		$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$client."/application/".$category."/signed/";			
		$options['upload_dir']= __DIR__ ."/../../".$hostPath;
		
        if (!file_exists($options['upload_dir'])) {
		    mkdir($options['upload_dir'], 0777, true);
        }

		$query = "INSERT INTO tdocs (idapp, idclient, idparent, iduser, title, hostpath, comments, category, status) 
							VALUES  (:idapp, :idclient, :idparent, :iduser, :title, :hostpath, :comments, :category, 1)";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);  
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);  
		$stmt->bindParam(':iduser', $myuser->userdata['id'], PDO::PARAM_STR);  
		$stmt->bindParam(':idparent', $ID, PDO::PARAM_STR);  
		$stmt->bindParam(':title', $Title, PDO::PARAM_STR);   
		$stmt->bindParam(':hostpath', $hostPath, PDO::PARAM_STR);   
		$stmt->bindParam(':comments', $Comments, PDO::PARAM_STR);   
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);   
		//$stmt->bindParam(':signature', $Signature, PDO::PARAM_STR);   
		$stmt->execute();
		$docId = $dbo->lastInsertId();
		
		$filename = str_replace(".".$ext, '_'.$idapp.'.'.$ext, $_FILES['filedata']['name']);
		$source_path = $_FILES['filedata']['tmp_name'];
		$dest_path = $options['upload_dir'] . $filename; 

		$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);   
		$stmt->bindParam(':id', $docId, PDO::PARAM_STR);   
		$stmt->execute();

		$ownerEmailAddress = "halal.ezone@gmail.com";
		$fromEmailAddress = "noreply@halal-e.zone";
		
		$body = [];
		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $supportEmailAddress;
		
		$body['attachhostpath'] = $dest_path;
		$body['attach'] = $filename;

		move_uploaded_file($source_path, $dest_path);
		
		// sending notification
		$body['subject'] = "Client ".$user["name"]." added a new document";
		//$body['header'] = "Client ".getClientInfo($prod['idclient'])." added a new product:";
		$body['message'] = "Dear Admin,
<br /><br />
Client <strong>".$user["name"]."</strong> added a new document <strong>".$Title."</strong> to the Halal eZone portal. 
<br /><br />
Please find attached.
<br /><br />
Best regards,<br />
Your HQC supporting Team";
sendEmailWithAttach($body);


$body = [];
$body['name'] = 'Halal e-Zone';
$body['email'] =  $fromEmailAddress;
$body['to'] = "alrahmahsolutions@gmail.com";

$body['attachhostpath'] = $dest_path;
$body['attach'] = $filename;

move_uploaded_file($source_path, $dest_path);

// sending notification
$body['subject'] = "Client ".$user["name"]." added a new document";
//$body['header'] = "Client ".getClientInfo($prod['idclient'])." added a new product:";
$body['message'] = "Dear Admin,
<br /><br />
Client <strong>".$user["name"]."</strong> added a new document <strong>".$Title."</strong> to the Halal eZone portal. 
<br /><br />
Please find attached.
<br /><br />
Best regards,<br />
Your HQC supporting Team";
sendEmailWithAttach($body);


$json_id = json_encode([$idclient]);
$sql = "SELECT *
        FROM tusers 
        WHERE isclient = 2 
        AND deleted = 0  
        AND JSON_CONTAINS(clients_audit, :json_id) > 0";

    $stmt = $dbo->prepare($sql);
    $stmt->bindParam(':json_id', $json_id);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $body = [];
        $body['name'] = 'Halal e-Zone';
        $body['email'] =  $fromEmailAddress;
        $body['to'] = $row["email"];
        
            $body['attachhostpath'] = $dest_path;
            $body['attach'] = $filename;

        // sending notification
        $body['subject'] = "Client ".$user["name"]." added a new document";
        //$body['header'] = "Client ".getClientInfo($prod['idclient'])." added a new product:";
        $body['message'] = "Dear Auditor,
    <br /><br />
    Client <strong>".$user["name"]."</strong> added a new document <strong>".$Title."</strong> to the Halal eZone portal. 
    <br /><br />
    Please find attached.
    <br /><br />
    Best regards,<br />
    Your HQC supporting Team";
    sendEmailWithAttach($body);
    }
    $uploadDir = "";

            // get cycle name
            $sql = "SELECT * FROM tcycles WHERE idclient=:idclient AND state = '1' ORDER BY id ASC LIMIT 1";
            $stmt = $dbo->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->bindValue(':idclient', $idclient);
            $stmt->execute();
            $firstCycle = $stmt->fetch(PDO::FETCH_ASSOC);
            $cycleName = $firstCycle["name"];

    if ($Title == "Alcohol (Free) Declaration") {
        $uploadDir = DRIVE_FILE_DIR."/".$config['clientsfolder']."/".str_replace('/', '{slash}', getClientInfo($idclient))."/application/Customer Forms/3.1 Halal Declaration Form";
    }
    elseif ($Title == "Pork Free Declaration") {
        $uploadDir = DRIVE_FILE_DIR."/".$config['clientsfolder']."/".str_replace('/', '{slash}', getClientInfo($idclient))."/application/Customer Forms/3.2 Pork Free Facility Declaration";
    }

    if ($uploadDir != "") { 
		/*	
        $client = gfGetClient();
        $service = new Google_Service_Drive($client);
        $fileInfo = gfUploadFile($client, $service, $options['upload_dir'], $filename, 'application/pdf', $uploadDir);        
		*/
    }

	/* CHECK IF ALL SIGNED */
	$all_errors = "";
	$query = "SELECT id
				FROM tdocs WHERE category='declarations' AND title='Client Questionnaire' AND idparent IS NOT NULL AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$all_errors .= 'Client Questionnaire form'."\n";
				}

				$query = "SELECT id
				FROM tdocs WHERE category='declarations' AND title='Pork Free Declaration' AND idparent IS NOT NULL AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$all_errors .= 'Pork Free Declaration form.'."\n";
				}

				$query = "SELECT id
				FROM tdocs WHERE category='declarations' AND title='Alcohol (Free) Declaration' AND idparent IS NOT NULL AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$all_errors .= 'Alcohol (Free) Declaration form.'."\n";
				}

				if ($user["industry"] == "Slaughter Houses") {
					$query = "SELECT id
					FROM tdocs WHERE category='declarations' AND title='Animal Feedstuff Declaration' AND idparent IS NOT NULL AND idclient=:idclient AND idapp=:idapp AND deleted=0
					LIMIT 0, 1";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
					$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
					$stmt->execute();
					if (!$stmt->fetchColumn()) {
						$all_errors .= 'Animal Feedstuff Declaration form'."\n";
					}
				}

				if ($all_errors == "") {
//					$all_errors = "The customer is required to complete and sign the following documents:\n\n". $all_errors;
				//}
				//else {

					$sql = "SELECT * FROM tapplications WHERE idclient=:idclient AND id=:idapp";
					 $stmt = $dbo->prepare($sql);
					 $stmt->setFetchMode(PDO::FETCH_ASSOC);
					 $stmt->bindValue(':idclient', $idclient);
					 $stmt->bindValue(':idapp', $idapp);
					 $stmt->execute();
					 $appData = $stmt->fetch(PDO::FETCH_ASSOC);

					if ($appData["state"] == "declarations") {
						
						insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], $Title . ' uploaded');
						
						/*
						$ownerEmailAddress = "halal.ezone@gmail.com";
						$fromEmailAddress = "noreply@halal-e.zone";
						$body = [];
						$body['name'] = 'Halal e-Zone';
						$body['email'] =  $fromEmailAddress;
						$body['to'] = $user["email"];
						$body['header'] = "";
						// sending notification
						$body['subject'] = "Halal e-Zone - Audit Date Proposals - ".$user["name"];
						$body['header'] = "";
						$body['body'] = "Dear ".$user["name"].",<br /><br />";
						$body['body'] .= "Please go to Halal eZone portal/application tab and select 3 audit date proposals for the on-site audit for your facility.<br /><br />";
						$body['body'] .= "Our team will confirm one of the proposed dates as soon as possible.<br /><br />";
						$body['body'] .= "Once date is confirmed you'll receive your audit plan, and we stay at your disposal for any assistance or clarification.<br /><br />";
						$body['body'] .= "Kind Regards<br/>";
						$body['body'] .= "Your HQC Team";
						sendEmail($body);					
						*/
						$myuser = cuser::singleton();
						$myuser->getUserData();
						$iduser = $myuser->userdata['id'];
						
						$decode = file_get_contents( __DIR__ ."/../config.json");
						$config=json_decode($decode, TRUE);
					
						$body = [];
						$title = "Checklist"; 
						$category = "checklist";
			
						 
			$query = "SELECT *
			FROM tusers	
			WHERE id='".$idclient."'";
			$stmt = $dbo->prepare($query);
			$stmt->execute();
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			
			 
			$data = [];
			$data["user"] = $user;

			$query = "SELECT * 
			FROM tapplications 
			WHERE id='".$idapp."' AND idclient='".$idclient."'";
			$stmt = $dbo->prepare($query);
			$stmt->execute();
			$data["app"] = $stmt->fetch(PDO::FETCH_ASSOC);

			$audit_plan_settings = $data["app"]["audit_plan_settings"];
			if ($audit_plan_settings == "") $audit_plan_settings = "[]";

			$errors = "";

			$title = "Audit Plan";
			$category = "audit";
			$decode = file_get_contents( __DIR__ ."/../../config.json");
			$config=json_decode($decode, TRUE);			
			$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/audit/";
			$absolutePath = __DIR__ ."/../../".$hostPath;

			if (!file_exists($absolutePath)) {
				mkdir($absolutePath, 0777, true);
			}

			$attach = '../../files/docs/F0401 Audit Plan Form 2021.pdf';
 			$ext = "pdf";

 
			$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, category, hostpath, signature) 
									VALUES (:idapp, :idclient, :iduser, :title, :category, :hostpath, 0)";
			$stmt = $dbo->prepare($query);

			$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
			$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
			$stmt->bindParam(':iduser', $iduser, PDO::PARAM_STR);
			$stmt->bindParam(':title', $title, PDO::PARAM_STR);
			$stmt->bindParam(':category', $category, PDO::PARAM_STR);
			$stmt->bindParam(':hostpath', $hostPath, PDO::PARAM_STR);
			$stmt->execute();
			$iddoc = $dbo->lastInsertId();

			$filename = str_replace(".".$ext, '_'.$iddoc.'.'.$ext, basename($attach));
			$dest_path = $absolutePath . $filename;

			saveAuditPlanPDF($data, $attach, $dest_path, $audit_plan_settings, false);

			$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
			$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
			$stmt->execute();


			$title = "Checklist"; 
			$category = "checklist";

			$industry = $data['user']["industry"];

			$filetoattach = "";
			if ($industry == "Meat Processing") {
				$filetoattach = "auditor_checklist_meat_processing.pdf";
			}
			else if ($industry == "Slaughter Houses") {
				$filetoattach = "auditor_checklist_slaughtering_plants.pdf";
			}
			else  {
				$filetoattach = "auditor_checklist_manufacturing.pdf";
			}

	

			$attach = '../../files/docs/'.$filetoattach;
			//$attach = $filetoattach;
			$ext = "pdf";
			if (!file_exists($absolutePath)) {
				mkdir($absolutePath, 0777, true);
			}

			$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, category, hostpath, signature) 
			VALUES (:idapp, :idclient, :iduser, :title, :category, :hostpath, 0)";
			$stmt = $dbo->prepare($query);

			$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
			$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
			$stmt->bindParam(':iduser', $iduser, PDO::PARAM_STR);
			$stmt->bindParam(':title', $title, PDO::PARAM_STR);
			$stmt->bindParam(':category', $category, PDO::PARAM_STR);
			$stmt->bindParam(':hostpath', $hostPath, PDO::PARAM_STR);

			$stmt->execute();
			$iddoc = $dbo->lastInsertId();

			$filename = str_replace(".".$ext, '_'.$iddoc.'.'.$ext, basename($attach));
			$dest_path = $absolutePath . $filename;

			saveChecklistPDF1($userData, $attach, $dest_path, $industry);

			$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
			$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
			$stmt->execute();
/*
						if ($data["app"]["state"] == "declarations") {
							
							$state = "audit";
							$query = "UPDATE tapplications SET state=:state WHERE idclient=:idclient AND id=:idapp";
							$stmt = $dbo->prepare($query);
							$stmt->bindParam(':state', $state, PDO::PARAM_STR);
							$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
							$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
							$stmt->execute();
							
						}	
							*/				
					}
 				}
				else {
					echo $all_errors;
				}

	if ($errors != "")  {
	
		http_response_code(403);
		echo "<ul>$errors</ul>";
	}	

}
}
catch (PDOException $e) {
		http_response_code(403);
    echo 'Database error: '.$e->getMessage();
}

?>