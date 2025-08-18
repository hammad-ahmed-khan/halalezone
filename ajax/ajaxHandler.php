<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../notifications/notifyfuncs.php";
include_once "../includes/func.php";
include_once "../reports/reports.php";
 
define('LOCAL_FILE_DIR','files');
define('DRIVE_FILE_DIR','CRM');

/* login */
function cors() {
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }
}

function updateAppState($data) {
	global $statusOptions;
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$iduser = $myuser->userdata['id'];

	$idclient = $data["idclient"];
	$idapp = $data["idapp"];
	$state = $data["state"];
	$skip = $data["skip"];

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	$errors = "";
	$id = "";

	$decode = file_get_contents( __DIR__ ."/../config.json");
	$config=json_decode($decode, TRUE);

	if (is_numeric($idclient) && is_numeric($idapp) && $state != "") {
		
		if ($skip != 1) {

			if ($errors == "" && ($state == "soffer")) {
				$query = "SELECT id
				FROM tdocs WHERE category='offer' AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'The client hasn\'t received the offer form. To send it, just click the "Send" button under the "Offer" tab.';
				}
			}
			else if ($state == "app") {
				$query = "SELECT id
				FROM tdocs WHERE category='soffer' AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'Signed offer does not exist.';
				}
				if ($user["login"]=="" || $user["pass"]=="") {
					$errors .= 'The client login access data has not been sent to the client. Please click the "Send Client Login" button to send it.';
				}
			}
			/*
			else if ($state == "declarations") {
				$query = "SELECT id
				FROM tdocs WHERE category='popinv' AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'Proof of payment for invoice for certification fees does not exist.';
				}
			}
			*/
			else if ($state == "popinv") {
				$query = "SELECT id
				FROM tdocs WHERE category='invoice' AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'invoice for certification fees does not exist.';
				}
			}	
			
			if ($state == "audit") {
				$query = "SELECT id
				FROM tevents WHERE status=1 AND idclient=:idclient AND idapp=:idapp
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'Audit date must be approved.'."\n";
				}				
			}
			
		  if ($state == "dates") {
				$query = "SELECT id
				FROM tdocs WHERE category='app' AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'The client has not signed and submitted the application form.';
				}
				else {
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
					$body['body'] .= "Please go to Halal eZone portal/ application tab and select 3 audit date proposals for the on-site audit for your facility.<br /><br />";
					$body['body'] .= "Our team will confirm one of the proposed dates as soon as possible.<br /><br />";
					$body['body'] .= "Once date is confirmed you�ll receive your audit plan, and we stay at your disposal for any assistance or clarification.<br /><br />";
					$body['body'] .= "Kind Regards<br/>";
					$body['body'] .= "Your HQC Team";
 					sendEmail($body);
				}
			}			
			if ($state == "audit") {
				$query = "SELECT id
				FROM tdocs WHERE category='declarations' AND title='Client Questionnaire' AND idparent IS NOT NULL AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'Client Questionnaire form'."\n";
				}
				$query = "SELECT id
				FROM tdocs WHERE category='declarations' AND title='Pork Free Declaration' AND idparent IS NOT NULL AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'Pork Free Declaration form.'."\n";
				}
				$query = "SELECT id
				FROM tdocs WHERE category='declarations' AND title='Alcohol (Free) Declaration' AND idparent IS NOT NULL AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'Alcohol (Free) Declaration form.'."\n";
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
						$errors .= 'Animal Feedstuff Declaration form'."\n";
					}
				}
				if ($errors != "") {
					$errors = "The customer is required to complete and sign the following documents:\n\n". $errors;
				}
				else {
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
					$body['body'] .= "Please go to Halal eZone portal/ application tab and select 3 audit date proposals for the on-site audit for your facility.<br /><br />";
					$body['body'] .= "Our team will confirm one of the proposed dates as soon as possible.<br /><br />";
					$body['body'] .= "Once date is confirmed you�ll receive your audit plan, and we stay at your disposal for any assistance or clarification.<br /><br />";
					$body['body'] .= "Kind Regards<br/>";
					$body['body'] .= "Your HQC Team";
 					sendEmail($body);
					*/
 				}
			}
			else if ($state == "invoice") { // this is the next step. Current step is "dates"
				/*
				$query = "SELECT id
				FROM tevents WHERE status=1 AND idclient=:idclient AND idapp=:idapp
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'No date selected.';
				}
				*/
	//		}
	//		else if ($state == "checklist") { // this is the next step. Current step is "audit"
				if ($errors == "") {
					$body = [];
					$title = "Checklist";
					$category = "checklist";

					$userData = [];
					
					$query = "SELECT *
					FROM tapplications	
					WHERE id='".$idapp."' AND idclient='".$idclient."'";
					$stmt = $dbo->prepare($query);
					$stmt->execute();
					$userData['app'] = $stmt->fetch(PDO::FETCH_ASSOC);

					$query = "SELECT *
					FROM tusers	
					WHERE id='".$idclient."'";
					$stmt = $dbo->prepare($query);
					$stmt->execute();
					$userData['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

					$industry = $userData['user']["industry"];

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

					$attach = '../files/docs/'.$filetoattach;
					//$attach = $filetoattach;
					$ext = "pdf";
					$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/checklist/";
					$absolutePath = __DIR__ ."/../".$hostPath;
					if (!file_exists($absolutePath)) {
						mkdir($absolutePath, 0777, true);
					}

					$query = "DELETE FROM tdocs WHERE idapp=:idapp AND idclient=:idclient AND title=:title AND category=:category";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
					$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
					$stmt->bindParam(':title', $title, PDO::PARAM_STR);
					$stmt->bindParam(':category', $category, PDO::PARAM_STR);
					$stmt->execute();

					$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, category, hostpath, signature) 
										VALUES (:idapp, :idclient, :iduser, :title, :category, :hostpath, 0)";
					$stmt = $dbo->prepare($query);

					/*
					echo $idapp."\n";
					echo $idclient."\n";
					echo $iduser."\n";
					echo $title."\n";
					echo $category."\n";
					echo $hostPath."\n";
					*/

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

					$ownerEmailAddress = "halal.ezone@gmail.com";
					$fromEmailAddress = "noreply@halal-e.zone";

					//sendEmailWithAttach
					$body['name'] = 'Halal e-Zone';
					$body['email'] =  $fromEmailAddress;
					$body['to'] = $user['email'];

					$body['attachhostpath'] = $dest_path;
					$body['attach'] = $filename;

					// sending notification
					$body['subject'] = "Halal e-Zone - ".$title . ' - '.$user["name"];
					$body['header'] = "";
					$body['message'] = "Dear Ms./ Mr. ".$user["contact_person"]."!<br /><br />";
					$body['message'] .= "Attached is the Auditor Checklist for your reference. It is also available on the Halal eZone portal.<br/><br/>";
					$body['message'] .= "Kind Regards<br/>";
					$body['message'] .= "Your HQC Team";
					//sendEmailWithAttach($body);
					
					// get cycle name
					$sql = "SELECT * FROM tcycles WHERE idclient=:idclient AND state = '1' ORDER BY id ASC LIMIT 1";
					$stmt = $dbo->prepare($sql);
					$stmt->setFetchMode(PDO::FETCH_ASSOC);
					$stmt->bindValue(':idclient', $idclient);
					$stmt->execute();
					$firstCycle = $stmt->fetch(PDO::FETCH_ASSOC);
					$cycleName = $firstCycle["name"];
					
					if (file_exists($absolutePath.'/'. $filename)) {
						/*
						$uploadDir = DRIVE_FILE_DIR."/".$config['clientsfolder']."/".str_replace('/', '{slash}', getClientInfo($idclient))."/application/Audit Forms/2 Auditor Checklist";
						require_once('../fileupload/GoogleDriveFunctions.php');
						$client = gfGetClient();
						$service = new Google_Service_Drive($client);
						gfUploadFile($client, $service, $absolutePath, $filename,  mime_content_type($absolutePath ."/". $filename), $uploadDir); 
						*/
					}
				}
			}
			else if ($state == "report") {
				$query = "SELECT id
				FROM tdocs WHERE category='audit'  AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'You have not sent the audit plan to the client. Please click the "Send" button to deliver the audit plan to the client.';
				}
				/*
				$query = "SELECT id
				FROM tdocs WHERE category='checklist' AND idclient=:idclient AND idapp=:idapp
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'Checklist is required.';
				}
				*/
			}
			else if ($state == "pop") {
				$query = "SELECT id FROM tauditreport WHERE Type='Major' AND  (Type = 'Major' AND (Status = 0 OR Implemented = 0)) AND idclient=:idclient AND idapp=:idapp
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if ($stmt->fetchColumn()) {
					$errors .= 'All major findings must be accepted and implemented before the certificate can be issued.';
				}
				else {

					$query = "SELECT *
					FROM tapplications	
					WHERE id='".$idapp."' AND idclient='".$idclient."'";
					$stmt = $dbo->prepare($query);
					$stmt->execute();
					$data['app'] = $stmt->fetch(PDO::FETCH_ASSOC);
				
					$query = "SELECT *
					FROM tusers	
					WHERE id='".$idclient."'";
					$stmt = $dbo->prepare($query);
					$stmt->execute();
					$data['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
 				
					$audit_report_settings = $data['app']['audit_report_settings'];
					if ($audit_report_settings == "") $audit_report_settings = "[]";
				
					$decode = file_get_contents( __DIR__ ."/../config.json");
					$config=json_decode($decode, TRUE);
					$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $data['user']['name'])." (".$idclient.")/application/report/";
					$absolutePath = __DIR__ ."/../".$hostPath;
			
					if (!file_exists($absolutePath)) {
						mkdir($absolutePath, 0777, true);
					}
			
					$attach = '../files/docs/F0436 Audit Report (NCR).pdf';
					$ext = "pdf";
			
					$filename = basename($attach);
					$dest_path = $absolutePath . $filename;
			
					saveAuditReportPDF($data, $attach, $dest_path, $audit_plan_settings, false);

 					// get cycle name
					$sql = "SELECT * FROM tcycles WHERE idclient=:idclient AND state = '1' ORDER BY id ASC LIMIT 1";
					$stmt = $dbo->prepare($sql);
					$stmt->setFetchMode(PDO::FETCH_ASSOC);
					$stmt->bindValue(':idclient', $idclient);
					$stmt->execute();
					$firstCycle = $stmt->fetch(PDO::FETCH_ASSOC);
					$cycleName = $firstCycle["name"];
					
					if (file_exists($absolutePath.'/'. $filename)) {
						/*
						$uploadDir = DRIVE_FILE_DIR."/".$config['clientsfolder']."/".str_replace('/', '{slash}', getClientInfo($idclient))."/application/Audit Forms/3.1 NCR And Audit Report";
						require_once('../fileupload/GoogleDriveFunctions.php');
						$client = gfGetClient();
						$service = new Google_Service_Drive($client);
						gfUploadFile($client, $service, $absolutePath, $filename,  mime_content_type($absolutePath ."/". $filename), $uploadDir); 
						*/
					}

					// Save review and decision making pdfs
					$title = "Audit Review Report";
					$category = "review";

					$userData = [];
					$query = "SELECT *
								FROM tapplications	
								WHERE id='".$idapp."' AND idclient='".$idclient."'";
								$stmt = $dbo->prepare($query);
								$stmt->execute();
								$userData['app'] = $stmt->fetch(PDO::FETCH_ASSOC);

								$query = "SELECT *
								FROM tusers	
								WHERE id='".$idclient."'";
								$stmt = $dbo->prepare($query);
								$stmt->execute();
								$userData['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

					$filetoattach = "F0421 Audit Review Report.pdf";

					$attach = '../files/docs/'.$filetoattach;
					$ext = "pdf";
					$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/review/";
					$absolutePath = __DIR__ ."/../".$hostPath;
					mkdir($absolutePath, 0777, true);

					$query = "DELETE FROM tdocs WHERE idapp=:idapp AND idclient=:idclient AND title=:title AND category=:category";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
					$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
					$stmt->bindParam(':title', $title, PDO::PARAM_STR);
					$stmt->bindParam(':category', $category, PDO::PARAM_STR);
					$stmt->execute();

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

					saveAuditReviewReportPDF($userData, $attach, $dest_path);

					$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
					$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
					$stmt->execute();

					// Save review and decision making pdfs
					$title = "Decision Making Report";
					$category = "dm";

					$userData = [];
					$query = "SELECT *
								FROM tapplications	
								WHERE id='".$idapp."' AND idclient='".$idclient."'";
								$stmt = $dbo->prepare($query);
								$stmt->execute();
								$userData['app'] = $stmt->fetch(PDO::FETCH_ASSOC);

								$query = "SELECT *
								FROM tusers	
								WHERE id='".$idclient."'";
								$stmt = $dbo->prepare($query);
								$stmt->execute();
								$userData['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

					$filetoattach = "F0403 Decision Making Report.pdf";

					$attach = '../files/docs/'.$filetoattach;
					$ext = "pdf";
					$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$user['name']." (".$idclient.")/application/review/";
					$absolutePath = __DIR__ ."/../".$hostPath;
					mkdir($absolutePath, 0777, true);

					$query = "DELETE FROM tdocs WHERE idapp=:idapp AND idclient=:idclient AND title=:title AND category=:category";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
					$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
					$stmt->bindParam(':title', $title, PDO::PARAM_STR);
					$stmt->bindParam(':category', $category, PDO::PARAM_STR);
					$stmt->execute();

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

					saveDecisionMakingReportPDF($userData, $attach, $dest_path);

					$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
					$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
					$stmt->execute();

					// Send email to upload POP
					$sql = "SELECT * FROM tusers WHERE id=:idclient";
					$stmt = $dbo->prepare($sql);
					$stmt->setFetchMode(PDO::FETCH_ASSOC);
					$stmt->bindValue(':idclient', $idclient);
					$stmt->execute();
					$user = $stmt->fetch(PDO::FETCH_ASSOC);

					$ownerEmailAddress = "halal.ezone@gmail.com";
					$fromEmailAddress = "noreply@halal-e.zone";

					$body['name'] = 'Halal e-Zone';
					$body['email'] =  $fromEmailAddress;
					$body['to'] = $user["email"];

					// sending notification
					$body['subject'] = "Halal e-Zone - Upload Proof of Payment - ".$user["name"];
					$body['header'] = "";
					$body['body'] = "Dear ".$user["name"].",";
					$body['body'] .= "<br /><br />";
					$body['body'] .= "kindly upload your proof of payment on the Halal eZone portal so that we can proceed to issue your certificate.";
					$body['body'] .= "<br /><br />";
					$body['body'] .= "Kind Regards,";
					$body['body'] .= "<br/>";
					$body['body'] .= "Your HQC supporting Team";
					sendEmail($body);

					if (file_exists($absolutePath.'/'. $filename)) {
						/*
						$uploadDir = DRIVE_FILE_DIR."/".$config['clientsfolder']."/".str_replace('/', '{slash}', getClientInfo($idclient))."/application/Audit Forms/4 Decision Making Report";
						require_once('../fileupload/GoogleDriveFunctions.php');
						$client = gfGetClient();
						$service = new Google_Service_Drive($client);
						gfUploadFile($client, $service, $absolutePath, $filename,  mime_content_type($absolutePath ."/". $filename), $uploadDir); 
						*/
					}
				}
			}
			else if ($state == "certificate") {
				$query = "SELECT id
				FROM tdocs WHERE category='pop' AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'Proof of payment does not exist.';
				}
			}
			else if ($state == "additional_items") {
				$query = "SELECT id
				FROM tdocs WHERE category='certificate' AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= 'Certificate has not been uploaded.';
				}
				else {
					$query = "SELECT *
					FROM tapplications WHERE idclient=:idclient AND id=:idapp 
					AND IFNULL(CertificateNumber, '') <>'' 
					AND IFNULL(CertificateIssueDate, '') <>'' 
					AND IFNULL(CertificateExpiryDate, '') <>'' 
					LIMIT 0, 1";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
					$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
					$stmt->execute();
					if (!$stmt->fetchColumn()) {
						$errors .= 'The Certificate Number, Issue Date, and Expiry Date fields are required.';
					}
				}
			}
			else if ($state == "extension") {
				$query = "SELECT id
				FROM tdocs WHERE category='additional_items' AND idclient=:idclient AND idapp=:idapp AND deleted=0
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					//$errors .= 'Additional Items Application does not exist.';
				}
			}
		}

		if ($skip == '1') {

			if ($state == "audit") { 

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
				$decode = file_get_contents( __DIR__ ."/../config.json");
				$config=json_decode($decode, TRUE);
				$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/audit/";
				$absolutePath = __DIR__ ."/../".$hostPath;

				if (!file_exists($absolutePath)) {
					mkdir($absolutePath, 0777, true);
				}

				$attach = '../files/docs/F0401 Audit Plan Form 2021.pdf';
				$ext = "pdf";

				$query = "DELETE FROM tdocs WHERE idapp=:idapp AND idclient=:idclient AND title=:title AND category=:category";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':title', $title, PDO::PARAM_STR);
				$stmt->bindParam(':category', $category, PDO::PARAM_STR);
				$stmt->execute();

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

				$myuser = cuser::singleton();
				$myuser->getUserData();
				$iduser = $myuser->userdata['id'];

				$attach = '../files/docs/'.$filetoattach;
				//$attach = $filetoattach;
				$ext = "pdf";
				$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/checklist/";
				$absolutePath = __DIR__ ."/../".$hostPath;
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
			}
			if ($state == "pop") { 
				// Save review and decision making pdfs
				$title = "Audit Review Report";
				$category = "review";

				$userData = [];
				$query = "SELECT *
							FROM tapplications	
							WHERE id='".$idapp."' AND idclient='".$idclient."'";
							$stmt = $dbo->prepare($query);
							$stmt->execute();
							$userData['app'] = $stmt->fetch(PDO::FETCH_ASSOC);

							$query = "SELECT *
							FROM tusers	
							WHERE id='".$idclient."'";
							$stmt = $dbo->prepare($query);
							$stmt->execute();
							$userData['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

				$filetoattach = "F0421 Audit Review Report.pdf";

				$attach = '../files/docs/'.$filetoattach;
				$ext = "pdf";
				$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/review/";
				$absolutePath = __DIR__ ."/../".$hostPath;
				mkdir($absolutePath, 0777, true);

				$query = "DELETE FROM tdocs WHERE idapp=:idapp AND idclient=:idclient AND title=:title AND category=:category";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':title', $title, PDO::PARAM_STR);
				$stmt->bindParam(':category', $category, PDO::PARAM_STR);
				$stmt->execute();

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

				saveAuditReviewReportPDF($userData, $attach, $dest_path);

				$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
				$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
				$stmt->execute();

				// Save review and decision making pdfs
				$title = "Decision Making Report";
				$category = "dm";

				$userData = [];
				$query = "SELECT *
							FROM tapplications	
							WHERE id='".$idapp."' AND idclient='".$idclient."'";
							$stmt = $dbo->prepare($query);
							$stmt->execute();
							$userData['app'] = $stmt->fetch(PDO::FETCH_ASSOC);

							$query = "SELECT *
							FROM tusers	
							WHERE id='".$idclient."'";
							$stmt = $dbo->prepare($query);
							$stmt->execute();
							$userData['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

				$filetoattach = "F0403 Decision Making Report.pdf";

				$attach = '../files/docs/'.$filetoattach;
				$ext = "pdf";
				$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/review/";
				$absolutePath = __DIR__ ."/../".$hostPath;
				mkdir($absolutePath, 0777, true);

				$query = "DELETE FROM tdocs WHERE idapp=:idapp AND idclient=:idclient AND title=:title AND category=:category";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':title', $title, PDO::PARAM_STR);
				$stmt->bindParam(':category', $category, PDO::PARAM_STR);
				$stmt->execute();

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

				saveDecisionMakingReportPDF($userData, $attach, $dest_path);

				$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
				$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
				$stmt->execute();

			}
		}
		if ($state == "declarations" && $errors == "") {
			if ($errors == "") {
				//$errors = "Unknown error has";

				//////////////////////////////////////////////////////////////
				$body = [];
				$title = "Client Questionnaire";
				$category = "declarations";
				$industry = $user["industry"];

				$query = "SELECT id
				FROM tdocs WHERE category='declarations' AND title='".$title."' AND idparent IS NULL AND idclient=:idclient AND idapp=:idapp
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();

				if (!$stmt->fetchColumn()) {
					$filetoattach = "";
					if ($industry == "Meat Processing") {
						$filetoattach = "customer_questionnaire_meat_processing.pdf";
					}
					else if ($industry == "Slaughter Houses") {
						$filetoattach = "customer_questionnaire_slaughtering_plants.pdf";
					}
					else  {
						$filetoattach = "customer_questionnaire_manufacturing.pdf";
					}
					$attach = '../files/docs/'.$filetoattach;
					$ext = "pdf";
					$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/questionnaire/";
					$absolutePath = __DIR__ ."/../".$hostPath;
					if (!file_exists($absolutePath)) {
						mkdir($absolutePath, 0777, true);
					}

					$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, category, hostpath, signature) 
										VALUES (:idapp, :idclient, :iduser, :title, :category, :hostpath, 1)";
					$stmt = $dbo->prepare($query);

					$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
					$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
					$stmt->bindParam(':iduser', $iduser, PDO::PARAM_STR);
					$stmt->bindParam(':title', $title, PDO::PARAM_STR);
					$stmt->bindParam(':category', $category, PDO::PARAM_STR);
					$stmt->bindParam(':hostpath', $hostPath, PDO::PARAM_STR);
					/*
					echo $idapp . "<br/>";
					echo $idclient . "<br/>";
					echo $iduser . "<br/>";
					echo $title . "<br/>";
					echo $category . "<br/>";
					echo $hostPath . "<br/>";
					*/
					$stmt->execute();
					$iddoc = $dbo->lastInsertId();

					$filename = str_replace(".".$ext, '_'.$iddoc.'.'.$ext, basename($attach));
					$dest_path = $absolutePath . $filename;
					if (!file_exists($dest_path)) {
						saveQuestionnairePDF1($user, $attach, $dest_path, $industry);
					}
					$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
					$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
					$stmt->execute();

					$ownerEmailAddress = "halal.ezone@gmail.com";
					$fromEmailAddress = "noreply@halal-e.zone";

					//sendEmailWithAttach
					$body['name'] = 'Halal e-Zone';
					$body['email'] =  $fromEmailAddress;
					$body['to'] = $user['email'];

					$body['attachhostpath'] = $dest_path;
					$body['attach'] = $filename;

					// sending notification
					$body['subject'] = "Halal e-Zone - ".$title.' - '.$user["name"];
					$body['header'] = "";
					$body['message'] = "<p>Dear Ms./ Mr. ".$user["contact_person"]."!</p>";
					$body['message'] .= '<p>Please find attached to this email our Client Questionnaire. You are kindly requested to download, fill and upload the filled documents on your eZone account/Applications/client questionnaire-free form declarations.</p>
					<p>Feel free to contact us for any assistance or clarification.</p>
					<p>Regards</p>
					<p>Halal e-Zone</p>
					';

					if ($skip != "1") {
						sendEmailWithAttach($body);
					}
				}

				$body = [];
				$title = "Pork Free Declaration";
				$category = "declarations";

				$query = "SELECT id
				FROM tdocs WHERE category='declarations' AND title='".$title."' AND idparent IS NULL AND idclient=:idclient AND idapp=:idapp
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();

				if (!$stmt->fetchColumn()) {
					$filetoattach = "F0451 Pork-Free Declaration.pdf";
					$attach = '../files/docs/'.$filetoattach;
					$ext = "pdf";
					$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/declarations/";
					$absolutePath = __DIR__ ."/../".$hostPath;

					if (!file_exists($absolutePath)) {
						mkdir($absolutePath, 0777, true);
					}

					$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, category, hostpath, signature) 
										VALUES (:idapp, :idclient, :iduser, :title, :category, :hostpath, 1)";
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

					//copy($attach, $dest_path);
					if (!file_exists($dest_path)) {
						savePorkFreePDF($user, $attach, $dest_path, $industry);
					}

					$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
					$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
					$stmt->execute();

					$ownerEmailAddress = "halal.ezone@gmail.com";
					$fromEmailAddress = "noreply@halal-e.zone";

					//sendEmailWithAttach
					$body['name'] = 'Halal e-Zone';
					$body['email'] =  $fromEmailAddress;
					$body['to'] = $user['email'];

					$body['attachhostpath'] = $dest_path;
					$body['attach'] = $filename;

					// sending notification
					$body['subject'] = "Halal e-Zone - ".$title. ' - '.$user["name"];
					$body['header'] = "";
					$body['message'] = "<p>Dear Ms./ Mr. ".$user["contact_person"]."!</p>";
					$body['message'] .= '<p>Please find attached to this email our '.$title.'. You are kindly requested to download, fill and upload the filled documents on your eZone account/Applications/client questionnaire-free form declarations.</p>
					<p>Feel free to contact us for any assistance or clarification.</p>
					<p>Regards</p>
					<p>Halal e-Zone</p>
					';

					if ($skip != "1") {
						sendEmailWithAttach($body);
					}
				}

				$body = [];
				$title = "Alcohol (Free) Declaration";
				$category = "declarations";

				$query = "SELECT id
				FROM tdocs WHERE category='declarations' AND title='".$title."' AND idparent IS NULL AND idclient=:idclient AND idapp=:idapp
				LIMIT 0, 1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();

				if (!$stmt->fetchColumn()) {
					$filetoattach = "F0453 Alcohol Free Production Line Declaration.pdf";
					$attach = '../files/docs/'.$filetoattach;
					$ext = "pdf";
					$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/declarations/";
					$absolutePath = __DIR__ ."/../".$hostPath;

					if (!file_exists($absolutePath)) {
						mkdir($absolutePath, 0777, true);
					}

					$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, category, hostpath, signature) 
										VALUES (:idapp, :idclient, :iduser, :title, :category, :hostpath, 1)";
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

					//copy($attach, $dest_path);
					if (!file_exists($dest_path)) {
						saveAlcoholFreePDF($user, $attach, $dest_path, $industry);
					}

					$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
					$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
					$stmt->execute();

					$ownerEmailAddress = "halal.ezone@gmail.com";
					$fromEmailAddress = "noreply@halal-e.zone";

					//sendEmailWithAttach
					$body['name'] = 'Halal e-Zone';
					$body['email'] =  $fromEmailAddress;
					$body['to'] = $user['email'];

					$body['attachhostpath'] = $dest_path;
					$body['attach'] = $filename;

					// sending notification
					$body['subject'] = "Halal e-Zone - ".$title . ' - '.$user["name"];
					$body['header'] = "";
					$body['message'] = "<p>Dear Ms./ Mr. ".$user["contact_person"]."!</p>";
					$body['message'] .= '<p>Please find attached to this email our '.$title.'. You are kindly requested to download, fill and upload the filled documents on your eZone account/Applications/client questionnaire-free form declarations.</p>
					<p>Feel free to contact us for any assistance or clarification.</p>
					<p>Regards</p>
					<p>Halal e-Zone</p>
					';

					if ($skip != "1") {
						sendEmailWithAttach($body);
					}
				}

				if ($industry == "Slaughter Houses") {
					$body = [];
					$title = "Animal Feedstuff Declaration";
					$category = "declarations";

					$query = "SELECT id
					FROM tdocs WHERE category='declarations' AND title='".$title."' AND idparent IS NULL AND idclient=:idclient AND idapp=:idapp
					LIMIT 0, 1";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
					$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
					$stmt->execute();

					if (!$stmt->fetchColumn()) {

						$filetoattach = "F0460 Animal Feedstuff Declaration Form.pdf";
						$attach = '../files/docs/'.$filetoattach;
						$ext = "pdf";
						$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/declarations/";
						$absolutePath = __DIR__ ."/../".$hostPath;

						if (!file_exists($absolutePath)) {
							mkdir($absolutePath, 0777, true);
						}

						$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, category, hostpath, signature) 
											VALUES (:idapp, :idclient, :iduser, :title, :category, :hostpath, 1)";
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

						//copy($attach, $dest_path);
						if (!file_exists($dest_path)) {
							saveFeedStuffPDF($user, $attach, $dest_path, $industry);
						}

						$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
						$stmt = $dbo->prepare($query);
						$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
						$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
						$stmt->execute();

						$ownerEmailAddress = "halal.ezone@gmail.com";
						$fromEmailAddress = "noreply@halal-e.zone";

						//sendEmailWithAttach
						$body['name'] = 'Halal e-Zone';
						$body['email'] =  $fromEmailAddress;
						$body['to'] = $user['email'];

						$body['attachhostpath'] = $dest_path;
						$body['attach'] = $filename;

						// sending notification
						$body['subject'] = "Halal e-Zone - ".$title.' - '.$user["name"];
						$body['header'] = "";
						$body['message'] = "Dear Ms./ Mr. ".$user["contact_person"]."!<br /><br />";
						$body['message'] .= '<p>Please find attached to this email our '.$title.'. You are kindly requested to download, fill and upload the filled documents on your eZone account/Applications/client questionnaire-free form declarations.</p>
						<p>Feel free to contact us for any assistance or clarification.</p>
						<p>Regards</p>
						<p>Halal e-Zone</p>	
						';
						if ($skip != "1") {
							sendEmailWithAttach($body);
						}	
					}
				}
				////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			}
		}		
		if ($errors == "") {

			
			$newIndex = array_search($state, $statusOptions);

			$query = "SELECT *
			FROM tapplications	
			WHERE id='".$idapp."' AND idclient='".$idclient."'";
			$stmt = $dbo->prepare($query);
			$stmt->execute();
			$appData = $stmt->fetch(PDO::FETCH_ASSOC);
			$currentState = $appData["state"];
			$currentIndex = array_search($currentState, $statusOptions);

			if ($newIndex > $currentIndex) { 

				insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'],  "Step \"".getAppStateName($statusOptions[$newIndex - 1])."\" marked as complete");
	
				$query = "UPDATE tapplications SET state=:state WHERE idclient=:idclient AND id=:idapp";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':state', $state, PDO::PARAM_STR);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
			}
		}
	}
	echo json_encode(generateSuccessResponse(array("state" => $state, "errors" => $errors)));
}


function sendClientLogin($data) {

	global $country_list, $adminEmailAddress;

	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$iduser = $myuser->userdata['id'];
	//$myuser->sec_session_start();
	//$res = $myuser->login($data['email'], $data['password']);
	$errors = "";
	$idclient = $data["idclient"];
	$idapp = $data["idapp"];

	$query = "SELECT id
	FROM tdocs WHERE category='soffer' AND idclient=:idclient AND idapp=:idapp
	LIMIT 0, 1";
	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
	$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
	$stmt->execute();
	if (!$stmt->fetchColumn()) {
		//$errors .= '<li>No record found, signed offer has not been uploaded.</li>';
	}

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($user["login"]!="" && $user["pass"]!="") {
		//$errors .= '<li>Client Login already sent.</li>';
	}

	if ($errors == "") {

		$name = slugify($user["name"]);
		/*
		$num = strlen($name) ;
		$num = intval($num/2);
		$first_half = substr($name,0, $num);
		$second_half = substr($name, $num);
		$name = $first_half . ' '.$second_half;
		*/
		$username =  random_username($name);
		$password = getToken(10);
		$encrypted = hash('sha512', $password);
		$country_code = array_search($user["country"], $country_list);
		if ($country_code == "") {
			$country_code = $user["country"];
		}

		$sql = "SELECT * FROM tapplications WHERE idclient=:idclient AND id=:idapp";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->bindValue(':idapp', $idapp);
		$stmt->execute();
		$appData = $stmt->fetch(PDO::FETCH_ASSOC);

		//$sql = "UPDATE tusers SET login=:login, pass=:password, prefix='CID_".date('m')."/".date('y')."_".$country_code."_' WHERE id=:idclient";
		$sql = "UPDATE tusers SET login=:login, pass=:password, last_login_sent=NOW() WHERE id=:idclient";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':login', $username);
		$stmt->bindValue(':password', $encrypted);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->execute();

		$ownerEmailAddress = "halal.ezone@gmail.com";
		$fromEmailAddress = "noreply@halal-e.zone";

		$decode = file_get_contents( __DIR__ ."/../config.json");
		$config=json_decode($decode, TRUE);
		$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/";
		$absolutePath = __DIR__ ."/../".$hostPath;

		mkdir($absolutePath, 0777, true);

		$offerOffice = trim($appData["offerOffice"]);
		if ( $offerOffice == "" ) {
			$offerOffice = "AT";
		}

		if ($offerOffice == "HU") {
			$attach = '../files/docs/F0-01 new customer registration_HU.pdf';
		}
		else {
			$attach = '../files/docs/F0-01 new customer registration.pdf';
		}
		$ext = "pdf";
		$filename = str_replace(".".$ext, '_'.$idapp.'.'.$ext, basename($attach));
		$dest_path = $absolutePath . $filename;
		saveHQAccessDataPDF($user, $attach, $dest_path, $offerOffice);

		//sendEmailWithAttach
		$body = [];
		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = "mustafa@halaloffice.com";

		$body['attachhostpath'] = $dest_path;
		$body['attach'] = $filename;

		// sending notification
		$body['subject'] = "HQC_".$offerOffice." new customer registration - " . $user["name"];
		$body['header'] = "";
		$body['message'] = "Salam Mustafa!<br /><br />";
		$body['message'] .= "How are you doing?<br /><br />";
		$body['message'] .= "We kindly would like to inform you of having a new customer. The registration form is attached.<br /><br />";
		$body['message'] .= "Wsalam,<br />";
		$body['message'] .= "Mona";

		sendEmailWithAttach($body);

		$body['to'] = "office@hqc.at";
		sendEmailWithAttach($body);
		
		///////////////////////////////////////////////////////////////

		$data = ['name' => $user["name"],
				 'username' => $username,
				 'password' => $password,
				];

		$attach = '../files/docs/05access data Halal eZone_'.$offerOffice.'.pdf';
		$ext = "pdf";
		$filename = str_replace(".".$ext, '_'.$idapp.'.'.$ext, basename($attach));
		$dest_path = $absolutePath . $filename;
		saveAccessDataPDF($data, $attach, $dest_path, $offerOffice);

		//sendEmailWithAttach
		$body = [];
		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $user["email"];

		$body['attachhostpath'] = $dest_path;
		$body['attach'] = $filename;

		// sending notification
		$body['subject'] = "Halal e-Zone - Access Data - ".$user["name"];
		$body['header'] = "";
		$message = "<p>Dear ".$user["contact_person"].",</p>

<p>We are thrilled to welcome you to the HQC Halal Quality Control Community! We prioritize exceptional customer service and a smooth digital certification process to ensure a positive experience.</p>

<p><strong>Your Access to the Halal eZone Portal</strong></p>

<p>With this email, you'll receive your access data to the Halal eZone, our unique online portal designed for a quick, digital, and hassle-free halal certification process. The eZone is user-friendly but we'd like to provide some additional resources to help you get started.</p>

<p><strong>Finding Your Way Around the eZone</strong></p>

<p>In your eZone dashboard, you'll find the \"Files\" section at the bottom of your eZone account. Here, you'll find instructional videos in both German and English to help you master using the eZone platform. The videos are accessible through the provided Loom links. Click on the <strong>\"German Webinar\" Loom Link</strong> or <strong>\"English Webinar\" Loom Link</strong> to access the pre-recorded session.</p>

<p><img src=\"https://halal-e.zone/img/welcome_email.jpg\" /></p>

<p>The webinar platform includes a chapter list for your convenience, allowing you to jump directly to the section that interests you most. From our experience, these videos provide excellent guidance on utilizing the eZone's functionalities.</p>

<p><strong>Want to go directly to the recorded webinar link?</strong></p>

<p>Here the English version: <a href=\"https://www.loom.com/share/3def04eb6e604bab88978ead4ea740fb?sid=b109fd54-c11c-4308-bd5f-3b5dfebc7bec\">Link to the recorded webinar</a></p>

<p>Here the German version: <a href=\"https://www.loom.com/share/3def04eb6e604bab88978ead4ea740fb?sid=b109fd54-c11c-4308-bd5f-3b5dfebc7bec\">Link to the recorded webinar</a></p>";


$body['message'] = $message;

sendEmailWithAttach($body);

$body = [];
		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $adminEmailAddress;
		//$body['to'] = 'alrahmahsolutions@gmail.com';

		$body['attachhostpath'] = $dest_path;
		$body['attach'] = $filename;

		// sending notification
		$body['subject'] = "Halal e-Zone - Access Data - ".$user["name"];
		$body['header'] = "";

		$body['message'] = $message;
	 
		
sendEmailWithAttach($body);

		echo json_encode(generateSuccessResponse(array("id" => $idclient, "last_login_sent" => date('d/m/Y h:i a'), 'errorInfo' => $dbo->errorInfo())));
		exit;
	}

		echo json_encode(generateSuccessResponse(array("errors" => "<ul>$errors</ul>", 'errorInfo' => $dbo->errorInfo())));
}

	function deleteOffer($data){
		$dbo = &$GLOBALS['dbo'];
		$myuser = cuser::singleton();
		//$myuser->sec_session_start();
		//$res = $myuser->login($data['email'], $data['password']);
		$errors = "";
		$idclient = $data["idclient"];
		$idapp = $data["idapp"];
		$ID = $data["ID"];
		if ($ID != "") {
			$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
			$sql = "DELETE FROM toffers WHERE id =:ID AND idclient=:idclient AND idapp=:idapp";
			$stmt = $dbo->prepare($sql);
			$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
			$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
			$stmt->bindValue(':ID', $ID);
			if(!$stmt->execute()) die(json_encode(generateErrorResponse("Unknown error!")));
		}
		echo json_encode(generateSuccessResponse(array("id" => $ID, 'errorInfo' => $dbo->errorInfo())));
	}

	function getOfferData($data){
		$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$id = $data["id"];
	$sql = "
    SELECT 
        *
    FROM 
        toffers
    WHERE 
        id = :id";	
	$stmt = $dbo->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_STR);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	$offer = $stmt->fetch();
	echo json_encode(generateSuccessResponse(array("offer" => $offer, 'errorInfo' => $dbo->errorInfo())));
	}

	function deleteServiceList($data){
		$dbo = &$GLOBALS['dbo'];
		$myuser = cuser::singleton();
		//$myuser->sec_session_start();
		//$res = $myuser->login($data['email'], $data['password']);
		$errors = "";
		$ID = $data["ID"];
		if ($ID != "") {
			$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
			$sql = "DELETE FROM tservices WHERE id =:ID";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':ID', $ID);
			if(!$stmt->execute()) die(json_encode(generateErrorResponse("Unknown error!")));
		}
		echo json_encode(generateSuccessResponse(array("id" => $ID, 'errorInfo' => $dbo->errorInfo())));
	}

	function saveDeviation($data){

		$dbo = &$GLOBALS['dbo'];
		$myuser = cuser::singleton();
		$Deviation = trim($data["Deviation"]);

		$errors = "";
		$id = "";

		if ($Deviation == "" ) {
			$errors .= "<li>Deviation is required.</li>";
		}

		if ($errors == "") {
			$query = "INSERT INTO tdeviations (deviation) VALUES (:Deviation)";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':Deviation', $Deviation, PDO::PARAM_STR);
			$stmt->execute();
			$id = $dbo->lastInsertId();
		}
		echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $id)));
	}

	function sendAuditPlan($data) { 
	
		$dbo = &$GLOBALS['dbo'];
		$myuser = cuser::singleton();
		$myuser->getUserData();
		$iduser = $myuser->userdata['id'];
		$idclient = $data["idclient"];
		$idapp = $data["idapp"];

		$query = "SELECT *
		FROM tapplications	
		WHERE id='".$idapp."' AND idclient='".$idclient."'";
		$stmt = $dbo->prepare($query);
		$stmt->execute();
		$data['app'] = $stmt->fetch(PDO::FETCH_ASSOC);

		$query = "SELECT *
		FROM tusers	
		WHERE id='".$idclient."'";
		$stmt = $dbo->prepare($query);
		$stmt->execute();
		$data['user'] = $stmt->fetch(PDO::FETCH_ASSOC);
		$user = $data['user'];

		$audit_plan_settings = $data['app']['audit_plan_settings'];
		if ($audit_plan_settings == "") $audit_plan_settings = "[]";

		$errors = "";

		if ($data['app']['approved_date1'] == "") {
			$errors .= "Failed to create the audit plan. Please select an audit date first.";
		}

		if ($errors == "") {
			$title = "Audit Plan";
			$category = "audit";
			$decode = file_get_contents( __DIR__ ."/../config.json");
			$config=json_decode($decode, TRUE);
			$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/audit/";
			$absolutePath = __DIR__ ."/../".$hostPath;

			if (!file_exists($absolutePath)) {
				mkdir($absolutePath, 0777, true);
			}

			$attach = '../files/docs/F0401 Audit Plan Form 2021.pdf';
			$ext = "pdf";

			$query = "DELETE FROM tdocs WHERE idapp=:idapp AND idclient=:idclient AND title=:title AND category=:category";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
			$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
			$stmt->bindParam(':title', $title, PDO::PARAM_STR);
			$stmt->bindParam(':category', $category, PDO::PARAM_STR);
			$stmt->execute();

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

				$ownerEmailAddress = "halal.ezone@gmail.com";
			$fromEmailAddress = "noreply@halal-e.zone";

			//sendEmailWithAttach
			$body['name'] = 'Halal e-Zone';
			$body['email'] =  $fromEmailAddress;
			$body['to'] = $data["user"]["email"];

			$body['attachhostpath'] = $dest_path;
			$body['attach'] = $filename;

			// sending notification
			$body['subject'] = "Halal e-Zone - Audit Plan - ".$user["name"];
			$body['header'] = "";
			$body['message'] = "Dear ".$data["user"]["name"].",<br /><br />";
			$body['message'] .= "Your Audit Plan has been created. Please find attached.";
			$body['message'] .= "Kind Regards<br/>";
			$body['message'] .= "Your HQC supporting Team";

			sendEmailWithAttach($body);

			// get cycle name
			$sql = "SELECT * FROM tcycles WHERE idclient=:idclient AND state = '1' ORDER BY id ASC LIMIT 1";
			$stmt = $dbo->prepare($sql);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->bindValue(':idclient', $idclient);
			$stmt->execute();
			$firstCycle = $stmt->fetch(PDO::FETCH_ASSOC);
			$cycleName = $firstCycle["name"];
			
			if (file_exists($absolutePath.'/'. $filename)) {
				/*
				$uploadDir = DRIVE_FILE_DIR."/".$config['clientsfolder']."/".str_replace('/', '{slash}', getClientInfo($idclient))."/application/Audit Forms/1 Audit Plan";
				require_once('../fileupload/GoogleDriveFunctions.php');
				$client = gfGetClient();
				$service = new Google_Service_Drive($client);
				gfUploadFile($client, $service, $absolutePath, $filename,  mime_content_type($absolutePath ."/". $filename), $uploadDir); 
				*/
			}

			$sql = "SELECT * FROM tapplications WHERE idclient=:idclient AND id=:idapp";
			$stmt = $dbo->prepare($sql);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->bindValue(':idclient', $idclient);
			$stmt->bindValue(':idapp', $idapp);
			$stmt->execute();
			$appData = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($appData["state"] == "audit" || $appData["state"] == "checklist") {
				$state = "report";
				$query = "UPDATE tapplications SET state=:state WHERE idclient=:idclient AND id=:idapp";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':state', $state, PDO::PARAM_STR);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();

				insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], 'Audit plan sent to client');
			}
			echo json_encode(generateSuccessResponse(array('filename'=>$filename )));
		}
		else {
			echo json_encode(generateSuccessResponse(array("errors" => $errors)));
		}		
	}

	function saveOffer($data) { 

		$dbo = &$GLOBALS['dbo'];
		$myuser = cuser::singleton();
		$id = $data["id"];
		$idclient = $data["idclient"];
		$idapp = $data["idapp"];
		$Service = trim($data["Service"]);
		$NewService = trim($data["NewService"]);
		$Fee = trim($data["Fee"]);

		$errors = "";
 
		if ($Service == "" && $NewService == "") {
			$errors .= "<li>Service is required.</li>";
		}
		if ($Fee == "") {
			$errors .= "<li>Fee is required.</li>";
		}
		elseif (!is_numeric($Fee)) {
			$errors .= "<li>Fee must be numeric.</li>";
		}
		if ($errors == "") {
			if ($NewService != "") {
				if ($id == "") {
					$query = "INSERT INTO tservices (service) VALUES (:Service)";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':Service', $NewService, PDO::PARAM_STR);
					$stmt->execute();
				}
				$Service = $NewService;
			}
			$Service = str_replace("\n",'<br />', $Service);

			if (!empty($id)) {
				// Update query if $id is not empty
				$query = "UPDATE toffers SET idclient = :idclient, idapp = :idapp, Service = :Service, Fee = :Fee WHERE id = :id";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
			} else {
				// Insert query if $id is empty
				$query = "INSERT INTO toffers (idclient, idapp, Service, Fee) VALUES (:idclient, :idapp, :Service, :Fee)";
				$stmt = $dbo->prepare($query);
			}
			
			$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
			$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
			$stmt->bindParam(':Service', $Service, PDO::PARAM_STR);
			$stmt->bindParam(':Fee', $Fee, PDO::PARAM_STR);
			$stmt->execute();

			if (empty($id)) {
				$id = $dbo->lastInsertId();
			}
		}
		echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $id)));
}

function changeOfferOffice($data){

	$dbo = &$GLOBALS['dbo'];
 	$idclient = $data["idclient"];
	$idapp = $data["idapp"];
	$offerOffice = trim($data["offerOffice"]);

	$errors = "";
	$id = "";

	if ($errors == "") {
		$query = "UPDATE tapplications SET offerOffice=:offerOffice  WHERE idclient=:idclient AND id=:idapp";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':offerOffice', $offerOffice, PDO::PARAM_STR);
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
		$stmt->execute();

	}
	echo json_encode(generateSuccessResponse(array("errors" => $errors)));
}

function changeIngredientsLimit($data){

    $dbo = &$GLOBALS['dbo'];
    $idclient = $data["idclient"];
    $ingredientsLimit = trim($data["ingredientsLimit"]);

    $errors = "";
    $id = "";

	// Check if ingredientsLimit is not a number
	if (!is_numeric($ingredientsLimit)) {
		$errors = "Ingredients limit must be numeric.";
	}	

    if ($errors == "") {
        $query = "UPDATE tusers SET ingrednumber=:ingredientsLimit  WHERE id=:idclient";
        $stmt = $dbo->prepare($query);
        $stmt->bindParam(':ingredientsLimit', $ingredientsLimit, PDO::PARAM_STR);
        $stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
        $stmt->execute();
    }
	else {
    	echo json_encode(generateSuccessResponse(array("errors" => $errors)));
	}
}

function changeProductsLimit($data){

    $dbo = &$GLOBALS['dbo'];
    $idclient = $data["idclient"];
    $productsLimit = trim($data["productsLimit"]);

    $errors = "";
    $id = "";

	// Check if productsLimit is not a number
	if (!is_numeric($productsLimit)) {
		$errors = "Products limit must be numeric.";
	}

    if ($errors == "") {
        $query = "UPDATE tusers SET prodnumber=:productsLimit  WHERE id=:idclient";
        $stmt = $dbo->prepare($query);
        $stmt->bindParam(':productsLimit', $productsLimit, PDO::PARAM_STR);
        $stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
        $stmt->execute();
    }
	else {
    	echo json_encode(generateSuccessResponse(array("errors" => $errors)));
	}
}


function saveOfferService($data){

	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$Service = trim($data["Service"]);

	$errors = "";
	$id = "";

	if ($Service == "") {
		$errors .= "<li>Description is required.</li>";
	}

	if ($errors == "") {
		$query = "INSERT INTO tservices (service) VALUES (:Service)";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':Service', $Service, PDO::PARAM_STR);
		$stmt->execute();
		$id = $dbo->lastInsertId();
	}
	echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $id)));
}

function saveAuditReport($data){

	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$idclient = $data["idclient"];
	$idapp = $data["idapp"];
	$Type = trim($data["Type"]);
	$Deviation = trim($data["Deviation"]);
	$NewDeviation = trim($data["NewDeviation"]);
	$Reference = trim($data["Reference"]);

	$errors = "";
	$id = "";

	if ($Type == "") {
		$errors .= "<li>Type of Finding is required.</li>";
	}
	if ($Deviation == "" && $NewDeviation == "") {
		$errors .= "<li>NC/OBS Statement is required.</li>";
	}
	if ($Reference == "") {
		$errors .= "<li>Reference to Checklist is required.</li>";
	}
	if ($errors == "") {
		if ($NewDeviation != "") {
			$query = "INSERT INTO tdeviations (deviation) VALUES (:Deviation)";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':Deviation', $NewDeviation, PDO::PARAM_STR);
			$stmt->execute();
			$Deviation = $NewDeviation;
		}
		$query = "INSERT INTO tauditreport (idclient, idapp, Type, Deviation, Reference) VALUES (:idclient, :idapp, :Type, :Deviation, :Reference)";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
		$stmt->bindParam(':Type', $Type, PDO::PARAM_STR);
		$stmt->bindParam(':Deviation', $Deviation, PDO::PARAM_STR);
		$stmt->bindParam(':Reference', $Reference, PDO::PARAM_STR);
		$stmt->execute();
		$id = $dbo->lastInsertId();

		$sql = "SELECT * FROM tusers WHERE id=:idclient";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->execute();
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		$ownerEmailAddress = "halal.ezone@gmail.com";
		$fromEmailAddress = "noreply@halal-e.zone";

		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $user["email"];

		// sending notification
		$body['subject'] = "Halal e-Zone - Audit Report update - ".$user["name"];
		$body['header'] = "";
		$body['body'] = "Dear ".$user["name"].",";
		$body['body'] .= "<br /><br />";
		$body['body'] = "Following corrective actions were added to the audit report.";
		$body['body'] .= "<br /><br />";
		$body['body'] .= "Client: ".$user['name'];		
		$body['body'] .= "<br /><br />";
		$body['body'] .= "Type of Finding: ".$Type;
		$body['body'] .= "<br />";
		$body['body'] .= "NCR/OBS statement: ".$Deviation;
		$body['body'] .= "<br />";
		$body['body'] .= "Reference to Checklist: ".$Reference;
		$body['body'] .= "<br /><br />";
		$body['body'] .= "Please log in to your eZone account / application tab to input your root cause analysis and corrective action, as well as to download your audit report.";
		$body['body'] .= "<br /><br />";
		$body['body'] .= "Kind Regards<br/>";
		$body['body'] .= "Your HQC Team";

		insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], 'Corrective actions added to the audit report');			

	//	sendEmail($body);
	}
	echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $id)));
}

function sendAuditReport($data) {

	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$idclient = $data["idclient"];
	$idapp = $data["idapp"];

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	$query = "SELECT * FROM tauditreport WHERE idclient=:idclient AND idapp=:idapp";
	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
	$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
	$stmt->execute();

	if ($deviations = $stmt->fetchAll()) {
		$contents = "Dear ".$user["name"].",";
		$contents .= "<br /><br />";
		$contents .= "Following corrective actions were added to the audit report.";
		foreach ($deviations as $deviation) {
			$contents .= "<br /><br />";
			$contents .= "Type of Finding: ".$deviation["Type"];
			$contents .= "<br />";
			$contents .= "NCR/OBS statement: ".$deviation["Deviation"];
			$contents .= "<br />";
			$contents .= "Reference to Checklist: ".$deviation["Reference"];
		}
		$contents .= "<br /><br />";
		$contents .= "Please log in to your eZone account / application tab to input your root cause analysis and corrective action, as well as to download your audit report.";
		$contents .= "<br /><br />";
		$contents .= "Kind Regards,";
		$contents .= "<br/>";
		$contents .= "Your HQC supporting Team";


		$ownerEmailAddress = "halal.ezone@gmail.com";
		$fromEmailAddress = "noreply@halal-e.zone";

		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $user["email"];

		// sending notification
		$body['subject'] = "Halal e-Zone - Audit Report update - ".$user["name"];
		$body['header'] = "";
		$body['body'] = $contents;
	 	sendEmail($body);
	}

	$updateQuery = "UPDATE tapplications SET last_report_sent = NOW() WHERE idclient = :idclient AND id = :idapp";
	$updateStmt = $dbo->prepare($updateQuery);
	$updateStmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
	$updateStmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
	$updateStmt->execute();	

	$lastReportSent = date('d/m/Y h:i a');

	echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $idapp, "last_report_sent" => strtoupper($lastReportSent))));
}

function save_event($data){
	global $calendarId, $serviceAccountFileName, $defaultTimezone;
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	//$myuser->sec_session_start();
	//$res = $myuser->login($data['email'], $data['password']);
	$errors = "";
	$ID = $data["ID"];
	$idclient = $data["idclient"] ?? "-1";
	$idapp = $data["idapp"] ?? "-1";
	$dateError = false;
	$dateTime1 = "";
	$dateTime2 = "";
	$tempDate1 = "";
	$tempDate2 = "";

	if (trim($data['Title']) == "") {
		$errors .= "<li>Title is required.</li>";
	}

	if (trim($data['StartDate']) == "") {
		$errors .= "<li>Start Date is required.</li>";
		$dateError=true;
	}
	else if(($dateTime1 = DateTime::createFromFormat('d/m/Y', $data['StartDate'])) == FALSE) {
		$errors .= "<li>Start Date is invalid.";
		$dateError=true;
	}
	$tempDate1 = $dateTime1->format("Y-m-d");

	if (trim($data['EndDate']) == "") {
		$errors .= "<li>End Date is required.</li>";
		$dateError=true;
	}
	else if(($dateTime2 = DateTime::createFromFormat('d/m/Y', $data['EndDate'])) == FALSE) {
		$errors .= "<li>End Date is invalid.";
		$dateError=true;
	}
	$tempDate2 = $dateTime2->format("Y-m-d");

	if (!$dateError) {
		/*
		$query = "SELECT id FROM tevents WHERE :new_start <= end_date AND :new_end  >= start_date AND status =1 ".($ID != "" ?" AND id<>:ID" : "")." LIMIT 0,1";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':new_start', $tempDate1, PDO::PARAM_STR);
		$stmt->bindParam(':new_end', $tempDate2, PDO::PARAM_STR);
		if ($ID != "") {
			$stmt->bindParam(':ID', $ID, PDO::PARAM_STR);
		}
		$stmt->execute();
		if ($stmt->fetchColumn()) {
			$errors .= "<li>Date is not available.</li>";
		}
		*/
	}

	$datax = [];
	if ($errors != "") {
		$datax['errors'] = "<ul>".$errors."</ul>";
	}
	else {
		require __DIR__ . '/../vendor/autoload.php';
		$serviceAccountFilePath =  __DIR__ . '/../config/google/'.$serviceAccountFileName;
		$client = new Google_Client();
		$client->setAuthConfig($serviceAccountFilePath);
		$client->addScope(Google_Service_Calendar::CALENDAR);				
		// Authenticate with the service account
		if ($client->isAccessTokenExpired()) {
			$client->fetchAccessTokenWithAssertion();
		}
		// Create a new Calendar service
		$service = new Google_Service_Calendar($client);
		if ($ID == "") {
			$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
			$sql = "INSERT INTO tevents (idclient, idapp, title, start_date, end_date) VALUES (:idclient, :idapp, :title, :start_date, :end_date)";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':idclient', $idclient);
			$stmt->bindValue(':idapp', $idapp);
			$stmt->bindValue(':title', $data['Title']);
			$stmt->bindValue(':start_date', $tempDate1);
			$stmt->bindValue(':end_date', $tempDate2);
			if(!$stmt->execute()) die(json_encode(generateErrorResponse("Unknown error!")));
			$ID = $dbo->lastInsertId();

			$auditor_name = ""; 

			if ($idclient != '-1') {
				$json_id = json_encode((string) $idclient); // Convert to string and JSON encode
				$sql = "SELECT *
						FROM tusers 
						WHERE isclient = 2 
						AND deleted = 0  
						AND JSON_CONTAINS(clients_audit, :json_id, '$') LIMIT 0, 1";								
				$stmt = $dbo->prepare($sql);
				$stmt->bindParam(':json_id', $json_id);
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				// Check if a result was found
				if ($result) {
					$auditor_name = $result['name'] . ' - ';
				}		
			}

			$event = new Google_Service_Calendar_Event(array(
				'summary' => str_replace("Auditor_", "", str_replace("Auditor ", "", $auditor_name)) . $data['Title'],
 				'start' => array(
					'date' =>  $dateTime1->format('Y-m-d'), // Start time of the event (in RFC3339 format)
					'timeZone' => $defaultTimezone, // Timezone of the event
				),
				'end' => array(
					'date' =>  $dateTime1->format('Y-m-d'), // End time of the event (in RFC3339 format)
					'timeZone' => $defaultTimezone, // Timezone of the event
				),
				'colorId' => 1, // Set the color ID here

			));
			if ($event = $service->events->insert($calendarId, $event)) {
				$gcal_id = $event->id;
				// Update gcal_id in tevents table
				$updateSql = "UPDATE tevents SET gcal_id = :gcal_id WHERE id = :id";
				$updateStmt = $dbo->prepare($updateSql);
				$updateStmt->bindValue(':gcal_id', $gcal_id);
				$updateStmt->bindValue(':id', $ID);
				$updateStmt->execute();
			}
		}
		else {
			$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

			// Fetch the gcal_id from the same row in the database
			$sql = "SELECT gcal_id FROM tevents WHERE id = :ID";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':ID', $ID);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($gcal_id = $row['gcal_id']) {

				$auditor_name = "";

				if ($idclient != '-1') {
					$json_id = json_encode((string) $idclient); // Convert to string and JSON encode
					$sql = "SELECT *
							FROM tusers 
							WHERE isclient = 2 
							AND deleted = 0  
							AND JSON_CONTAINS(clients_audit, :json_id, '$') LIMIT 0, 1";									
					$stmt = $dbo->prepare($sql);
					$stmt->bindParam(':json_id', $json_id);
					$stmt->execute();
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					// Check if a result was found
					if ($result) {
						$auditor_name = $result['name'] . ' - ';
					}		
				}
			
			$sql = "UPDATE tevents SET title = :title, start_date=:start_date, end_date=:end_date WHERE id =:ID";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':title', str_replace($auditor_name, "", $data['Title']));
			$stmt->bindValue(':start_date', $tempDate1);
			$stmt->bindValue(':end_date', $tempDate2);
			$stmt->bindValue(':ID', $ID);
			if (!$stmt->execute()) die(json_encode(generateErrorResponse("Unknown error!")));

				$event = new Google_Service_Calendar_Event($eventData = array(
					'summary' => str_replace($auditor_name, "", $data['Title']),
					'start' => array(
						'date' => $dateTime1->format('Y-m-d'),
						'timeZone' => $defaultTimezone,
					),
					'end' => array(
						'date' => $dateTime1->format('Y-m-d'),
						'timeZone' => $defaultTimezone,
					),
					'colorId' => 1, // Set the color ID here
				));

				//print_r($eventData);
				$updatedEvent = $service->events->update($calendarId, $gcal_id, $event);
			}
		}
		echo json_encode(generateSuccessResponse(array("id" => $ID, 'errorInfo' => $dbo->errorInfo())));
		exit;
	}

	echo json_encode(generateSuccessResponse($datax));
}


function delete_event($data){
	global $calendarId, $serviceAccountFileName, $defaultTimezone;
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$myuser->getUserData();
	//$myuser->sec_session_start();
	//$res = $myuser->login($data['email'], $data['password']);
	$errors = "";
	$ID = $data["ID"];

	if ($ID != "") {
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

		$sql = "SELECT * FROM tevents WHERE id =:ID AND idclient>0 AND idapp>0";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':ID', $ID);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->execute();
		if ($event = $stmt->fetch()) {
			//$errors = "<li>Approved audit dates cannot be deleted.</li>";
			/*
			if ($event["status"] == "1") {
				$sql = "UPDATE tapplications SET approved_date1=NULL WHERE id=:id AND idclient=:idclient";
				$stmt = $dbo->prepare($sql);
				$stmt->bindValue(':id', $event["idapp"]);
				$stmt->bindValue(':idclient', $event["idclient"]);		
				$stmt->execute();					
				insertActivityLog($event["idclient"], $event["idapp"], $myuser->userdata['id'], $myuser->userdata['name'], "Audit date changed");
			}
			*/
		}
		if ($errors == "") {
			$sql = "SELECT gcal_id FROM tevents WHERE id = :ID";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':ID', $ID);
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($gcal_id = $row['gcal_id']) {
				require __DIR__ . '/../vendor/autoload.php';
				$serviceAccountFilePath =  __DIR__ . '/../config/google/'.$serviceAccountFileName;
				$client = new Google_Client();
				$client->setAuthConfig($serviceAccountFilePath);
				$client->addScope(Google_Service_Calendar::CALENDAR);				
				// Authenticate with the service account
				if ($client->isAccessTokenExpired()) {
					$client->fetchAccessTokenWithAssertion();
				}
				// Create a new Calendar service
				$service = new Google_Service_Calendar($client);
				// Delete the event from Google Calendar
				try {
					$service->events->delete($calendarId, $gcal_id);
				} 
				catch (Exception $e) {
					die(json_encode(generateErrorResponse("Error deleting event from Google Calendar: " . $e->getMessage())));
				}
			}

			$sql = "DELETE FROM tevents WHERE id =:ID";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':ID', $ID);
			if(!$stmt->execute()) die(json_encode(generateErrorResponse("Unknown error!")));

		}
		else {
			echo json_encode(generateSuccessResponse(array("errors" => "<ul>$errors</ul>", 'errorInfo' => $dbo->errorInfo())));
		}
	}
	echo json_encode(generateSuccessResponse(array("id" => $ID, 'errorInfo' => $dbo->errorInfo())));
}

function updateDeviationStatus($data) { 
	global $adminEmailAddress, $statusOptions;
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$errors = "";
	$id = $data["id"];
	$Status = $data["Status"];
	$sql = "UPDATE tauditreport SET Status=:Status WHERE id=:id";
	$stmt = $dbo->prepare($sql);
	$stmt->bindValue(':Status', $data['Status']);
	$stmt->bindValue(':id', $data['id']);

	if (!$stmt->execute()){
		echo json_encode(generateErrorResponse('Data saving failed'));
		die();
	}

	$sql = "SELECT *, date_format(Deadline, '%d/%m/%Y') as Deadline FROM tauditreport WHERE id = :id";
	$stmt = $dbo->prepare($sql);
	$stmt->bindParam(':id',  $data['id'], PDO::PARAM_STR);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	$deviation = $stmt->fetch();
	$idclient = $deviation["idclient"];
	$idapp = $deviation["idapp"];

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	$ownerEmailAddress = "halal.ezone@gmail.com";
	$fromEmailAddress = "noreply@halal-e.zone";

	$body['name'] = 'Halal e-Zone';
	$body['email'] =  $fromEmailAddress;
	$body['to'] = $user["email"];

	// sending notification
	$body['subject'] = "Halal e-Zone - Audit Report update - ".$user["name"];
	$body['header'] = "";
	$body['body'] = "Dear ".$user["name"].",";
	$body['body'] .= "<br /><br />";
	$body['body'] = "Following corrective action acceptance was ".($deviation['Status'] == 1 ? "Comfirmed" : "Un-Confirmed").".";
	$body['body'] .= "<br /><br />";
	$body['body'] .= "Type of Finding: ".$deviation['Type'];
	$body['body'] .= "<br />";
	$body['body'] .= "NCR/OBS statement: ".$deviation['Deviation'];
	$body['body'] .= "<br />";
	$body['body'] .= "Reference to Checklist: ".$deviation['Reference'];
	$body['body'] .= "<br /><br />";
	$body['body'] .= "Root Cause Analysis: ".$deviation['RootCause'];
	$body['body'] .= "<br />";
	$body['body'] .= "Proposed Corrective Action: ".$deviation['Measure'];
	$body['body'] .= "<br />";
	$body['body'] .= "Deadline: ".$deviation['Deadline'];
	$body['body'] .= "<br />";
	$body['body'] .= "Status: ".($deviation['Status'] == "1" ? "Confirmed" : "Un-Confirmed");
	$body['body'] .= "<br /><br />";
	$body['body'] .= "Kind Regards,";
	$body['body'] .= "<br/>";
	$body['body'] .= "Your HQC supporting Team";

	sendEmail($body);

	$email_type = 'Audit Report Notification';

 	// Check the last email sent time
	$query = "SELECT MAX(sent_at) FROM email_logs WHERE email_type = :emailType AND recipient_email = :adminEmailAddress";
	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':emailType', $email_type, PDO::PARAM_STR);
	$stmt->bindParam(':adminEmailAddress', $adminEmailAddress, PDO::PARAM_STR);	
	$stmt->execute();
	$lastSentTime = $stmt->fetchColumn();

	$all = 0;
	$timeLimit = 30 * 60; // 30 minutes in seconds
	$currentTime = time();

	$query = "SELECT id FROM tauditreport WHERE  (Type = 'Major' AND (Status = 0 OR Implemented = 0)) AND idclient=:idclient AND idapp=:idapp LIMIT 1";
	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
	$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
	$stmt->execute();

	if (!$stmt->fetchColumn()) {
		$all = 1;
		$state = "pop";

		
		$newIndex = array_search($state, $statusOptions);

		$query = "SELECT *
		FROM tapplications	
		WHERE id='".$idapp."' AND idclient='".$idclient."'";
		$stmt = $dbo->prepare($query);
		$stmt->execute();
		$appData = $stmt->fetch(PDO::FETCH_ASSOC);
		$currentState = $appData["state"];
		$currentIndex = array_search($currentState, $statusOptions);

		if ($newIndex > $currentIndex) { 
 			$query = "UPDATE tapplications SET state=:state WHERE idclient=:idclient AND id=:idapp";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':state', $state, PDO::PARAM_STR);
			$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
			$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
			$stmt->execute();				

		}		
		if (!$lastSentTime || ($currentTime - strtotime($lastSentTime) > $timeLimit)) {

			$ownerEmailAddress = "halal.ezone@gmail.com";
			$fromEmailAddress = "noreply@halal-e.zone";

			$body['name'] = 'Halal e-Zone';
			$body['email'] = $fromEmailAddress;
			$body['to'] = $adminEmailAddress;

			// Sending notification
			$body['subject'] = "Halal e-Zone - All Corrective Actions Confirmed - " . $user["name"];
			$body['header'] = "";
			$body['body'] = "Dear Admin,";
			$body['body'] .= "<br /><br />";
			$body['body'] .= "All corrective actions for the audit report of client \"" . $user["name"] . "\" have been confirmed by the auditor. Please review the audit report and mark the step as complete.";
			$body['body'] .= "<br /><br />";
			$body['body'] .= "<strong>Additionally, please issue the invoice for transfer and accommodation costs.</strong>";
			$body['body'] .= "<br /><br />";
			$body['body'] .= "Kind Regards,";
			$body['body'] .= "<br/>";
			$body['body'] .= "Your HQC Supporting Team";

			sendEmail($body);

			// Log the email sending time
			$query = "INSERT INTO email_logs (email_type, recipient_email, sent_at) VALUES (:emailType, :adminEmailAddress, NOW())";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':emailType', $email_type, PDO::PARAM_STR);
			$stmt->bindParam(':adminEmailAddress', $adminEmailAddress, PDO::PARAM_STR);
			$stmt->execute();
		}		
	}


	insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], 'Corrective actions '.($deviation['Status'] == 1 ? "Comfirmed" : "Un-Confirmed"));


	echo json_encode(generateSuccessResponse(array("all" => $all, 'message' => "Data was updated") ));
}

function updateImplementationStatus($data) { 
	global $adminEmailAddress, $statusOptions;
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$errors = "";
	$id = $data["id"];
	$Implemented = $data["Implemented"];
	$sql = "UPDATE tauditreport SET Implemented=:Implemented WHERE id=:id";
	$stmt = $dbo->prepare($sql);
	$stmt->bindValue(':Implemented', $data['Implemented']);
	$stmt->bindValue(':id', $data['id']);

	if (!$stmt->execute()){
		echo json_encode(generateErrorResponse('Data saving failed'));
		die();
	}

	$sql = "SELECT *, date_format(Deadline, '%d/%m/%Y') as Deadline FROM tauditreport WHERE id = :id";
	$stmt = $dbo->prepare($sql);
	$stmt->bindParam(':id',  $data['id'], PDO::PARAM_STR);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	$deviation = $stmt->fetch();
	$idclient = $deviation["idclient"];
	$idapp = $deviation["idapp"];

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	$ownerEmailAddress = "halal.ezone@gmail.com";
	$fromEmailAddress = "noreply@halal-e.zone";

	$body['name'] = 'Halal e-Zone';
	$body['email'] =  $fromEmailAddress;
	$body['to'] = $user["email"];

	// sending notification
	$body['subject'] = "Halal e-Zone - Audit Report update - ".$user["name"];
	$body['header'] = "";
	$body['body'] = "Dear ".$user["name"].",";
	$body['body'] .= "<br /><br />";
	$body['body'] = "Following corrective actions implementation was ".($deviation['Status'] == 1 ? "Comfirmed" : "Un-Confirmed").".";
	$body['body'] .= "<br /><br />";
	$body['body'] .= "Type of Finding: ".$deviation['Type'];
	$body['body'] .= "<br />";
	$body['body'] .= "NCR/OBS statement: ".$deviation['Deviation'];
	$body['body'] .= "<br />";
	$body['body'] .= "Reference to Checklist: ".$deviation['Reference'];
	$body['body'] .= "<br /><br />";
	$body['body'] .= "Root Cause Analysis: ".$deviation['RootCause'];
	$body['body'] .= "<br />";
	$body['body'] .= "Proposed Corrective Action: ".$deviation['Measure'];
	$body['body'] .= "<br />";
	$body['body'] .= "Deadline: ".$deviation['Deadline'];
	$body['body'] .= "<br />";
	$body['body'] .= "Status: ".($deviation['Status'] == "1" ? "Confirmed" : "Un-Confirmed");
	$body['body'] .= "<br /><br />";
	$body['body'] .= "Kind Regards,";
	$body['body'] .= "<br/>";
	$body['body'] .= "Your HQC supporting Team";

	sendEmail($body);

	$email_type = 'Audit Report Notification';

	// Check the last email sent time
   $query = "SELECT MAX(sent_at) FROM email_logs WHERE email_type = :emailType AND recipient_email = :adminEmailAddress";
   $stmt = $dbo->prepare($query);
   $stmt->bindParam(':emailType', $email_type, PDO::PARAM_STR);
   $stmt->bindParam(':adminEmailAddress', $adminEmailAddress, PDO::PARAM_STR);	
   $stmt->execute();
   $lastSentTime = $stmt->fetchColumn();

   $all = 0;
   $timeLimit = 30 * 60; // 30 minutes in seconds
   $currentTime = time();

   $query = "SELECT id FROM tauditreport WHERE  (Type = 'Major' AND (Status = 0 OR Implemented = 0)) AND idclient=:idclient AND idapp=:idapp LIMIT 1";
   $stmt = $dbo->prepare($query);
   $stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
   $stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
   $stmt->execute();

   if (!$stmt->fetchColumn()) {
	   $all = 1;
	   $state = "pop";
	   
	   $newIndex = array_search($state, $statusOptions);

	   $query = "SELECT *
	   FROM tapplications	
	   WHERE id='".$idapp."' AND idclient='".$idclient."'";
	   $stmt = $dbo->prepare($query);
	   $stmt->execute();
	   $appData = $stmt->fetch(PDO::FETCH_ASSOC);
	   $currentState = $appData["state"];
	   $currentIndex = array_search($currentState, $statusOptions);

	   if ($newIndex > $currentIndex) { 
			$query = "UPDATE tapplications SET state=:state WHERE idclient=:idclient AND id=:idapp";
		   $stmt = $dbo->prepare($query);
		   $stmt->bindParam(':state', $state, PDO::PARAM_STR);
		   $stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
		   $stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
		   $stmt->execute();				

	   }		
	   if (!$lastSentTime || ($currentTime - strtotime($lastSentTime) > $timeLimit)) {

		   $ownerEmailAddress = "halal.ezone@gmail.com";
		   $fromEmailAddress = "noreply@halal-e.zone";

		   $body['name'] = 'Halal e-Zone';
		   $body['email'] = $fromEmailAddress;
		   $body['to'] = $adminEmailAddress;

		   // Sending notification
		   $body['subject'] = "Halal e-Zone - All Corrective Actions Confirmed - " . $user["name"];
		   $body['header'] = "";
		   $body['body'] = "Dear Admin,";
		   $body['body'] .= "<br /><br />";
		   $body['body'] .= "All corrective actions for the audit report of client \"" . $user["name"] . "\" have been confirmed by the auditor. Please review the audit report and mark the step as complete.";
		   $body['body'] .= "<br /><br />";
		   $body['body'] .= "<strong>Additionally, please issue the invoice for transfer and accommodation costs.</strong>";
		   $body['body'] .= "<br /><br />";
		   $body['body'] .= "Kind Regards,";
		   $body['body'] .= "<br/>";
		   $body['body'] .= "Your HQC Supporting Team";

		   sendEmail($body);

		   // Log the email sending time
		   $query = "INSERT INTO email_logs (email_type, recipient_email, sent_at) VALUES (:emailType, :adminEmailAddress, NOW())";
		   $stmt = $dbo->prepare($query);
		   $stmt->bindParam(':emailType', $email_type, PDO::PARAM_STR);
		   $stmt->bindParam(':adminEmailAddress', $adminEmailAddress, PDO::PARAM_STR);
		   $stmt->execute();
	   }		
   }


   insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], 'Corrective actions '.($deviation['Status'] == 1 ? "Comfirmed" : "Un-Confirmed"));


   echo json_encode(generateSuccessResponse(array("all" => $all, 'message' => "Data was updated") ));
}


function deleteDeviation($data){
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$errors = "";
	$id = $data["id"];
	$idclient = $data["idclient"];
	$idapp = $data["idapp"];
	$id = $data["id"];
	$sql = "DELETE FROM tauditreport WHERE id=:id AND idapp=:idapp AND idclient=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->bindValue(':id', $data['id']);
	$stmt->bindValue(':idapp', $data['idapp']);
	$stmt->bindValue(':idclient', $data['idclient']);
	if (!$stmt->execute()){
		echo json_encode(generateErrorResponse('Data saving failed'));
		die();
	}

	insertActivityLog($data['idclient'], $data['idapp'], $myuser->userdata['id'], $myuser->userdata['name'], 'Corrective actions removed');

	echo json_encode(generateSuccessResponse('Data was updated'));
}

function deleteDeviationDoc($data){
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$errors = "";
	
	$id = $data['id'];
    $idclient = $data['idclient'];
    $idapp = $data['idapp'];
    $document = $data['document'];

    // Prepare and execute the query to fetch the Documents field
    $query = "SELECT Documents FROM tauditreport WHERE idclient=:idclient AND idapp=:idapp AND id=:id";
    $stmt = $dbo->prepare($query);
    $stmt->bindParam(':idclient', $idclient, PDO::PARAM_INT);
    $stmt->bindParam(':idapp', $idapp, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {

		// Decode the JSON string
        $documents = json_decode($result['Documents'], true);

        // Find the document and set delete to 1
        foreach ($documents as &$doc) {
            if ($doc['name'] == $document) {
                $doc['deleted'] = 1;
                break;
            }
        }

        // Re-encode the array back to a JSON string
        $updatedDocuments = json_encode($documents);

        // Update the Documents field in the database
        $updateQuery = "UPDATE tauditreport SET Documents=:documents WHERE idclient=:idclient AND idapp=:idapp AND id=:id";
        $updateStmt = $dbo->prepare($updateQuery);
        $updateStmt->bindParam(':documents', $updatedDocuments, PDO::PARAM_STR);
        $updateStmt->bindParam(':idclient', $idclient, PDO::PARAM_INT);
        $updateStmt->bindParam(':idapp', $idapp, PDO::PARAM_INT);
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $updateStmt->execute();

        // Return a success response
        
	}

	insertActivityLog($data['idclient'], $data['idapp'], $myuser->userdata['id'], $myuser->userdata['name'], 'Client deleted a document from Corrective Actions');

	echo json_encode(generateSuccessResponse('Document was deleted'));
}

function saveMeasureData($data) {

	global $adminEmailAddress, $supportEmailAddress;
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$errors = "";
	$id = $data["id"];
	$idclient = $data["idclient"]; 
	$idapp = $data["idapp"];
	$tempDate = "";

	if ($data['RootCause'] == "") {
		$errors .= "<li>Root Cause Analysis is required.</li>";
	}
	if ($data['Measure'] == "") {
		$errors .= "<li>Proposed Corrective Action is required.</li>";
	}
	if ($data['Deadline'] == "") {
		$errors .= "<li>Target Date is required.</li>";
	}
	else if (($dateTime = DateTime::createFromFormat('d/m/Y', $data['Deadline'])) === FALSE) {
		$errors .= "<li>Target Date is invalid.";
	}

	$tempDate = $dateTime->format("Y-m-d");

	if ($errors == "") {

		$sql = "UPDATE tauditreport SET RootCause=:RootCause, Measure=:Measure, Deadline=:Deadline, AdminNotified=0 WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':RootCause', $data['RootCause']);
		$stmt->bindValue(':Measure', $data['Measure']);
		$stmt->bindValue(':Deadline', $tempDate);
		$stmt->bindValue(':id', $data['id']);

		if (!$stmt->execute()) {
			echo json_encode(generateErrorResponse('Data saving failed'));
			die();
		}

		$sql = "SELECT * FROM tauditreport WHERE id = :id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindParam(':id',  $data['id'], PDO::PARAM_STR);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->execute();
		$deviation = $stmt->fetch();
		$idclient = $deviation['idclient'];

		$sql = "SELECT * FROM tusers WHERE id=:idclient";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->execute();
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		$ownerEmailAddress = "halal.ezone@gmail.com";
		$fromEmailAddress = "noreply@halal-e.zone";

		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress; 
		$body['to'] = $supportEmailAddress;

		// sending notification
		$body['subject'] = "Halal e-Zone - Audit Report update - ".$user["name"];
		$body['header'] = "";
		$body['body'] = "Dear Admin,";
		$body['body'] .= "<br /><br />";
		$body['body'] .= "<strong>".$user["name"]."</strong> has added the following corrective actions to the audit report.";
		$body['body'] .= "<br /><br />";
		$body['body'] .= "Type of Finding: ".$deviation['Type'];
		$body['body'] .= "<br />";
		$body['body'] .= "NCR/OBS statement: ".$deviation['Deviation'];
		$body['body'] .= "<br />";
		$body['body'] .= "Reference to Checklist: ".$deviation['Reference'];
		$body['body'] .= "<br /><br />";
		$body['body'] .= "Root Cause Analysis: ".$deviation['RootCause'];
		$body['body'] .= "<br />";
		$body['body'] .= "Proposed Corrective Action: ".$deviation['Measure'];
		$body['body'] .= "<br />";
		$body['body'] .= "Deadline: ".$deviation['Deadline'];
		$body['body'] .= "<br /><br />";
		$body['body'] .= "Regards,";
		$body['body'] .= "<br/>";
		$body['body'] .= "Halal e-Zone";
 
		sendEmail($body);

		$json_id = json_encode((string) $idclient); // Convert to string and JSON encode
		$sql = "SELECT *
				FROM tusers 
				WHERE isclient = 2 
				AND deleted = 0  
				AND JSON_CONTAINS(clients_audit, :json_id, '$')";

		$stmt = $dbo->prepare($sql);
		$stmt->bindParam(':json_id', $json_id);
		$stmt->execute();

		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($results as $row) {
			$body['to'] = $row["email"];
			$body['body'] = "Dear Auditor,";
			$body['body'] .= "<br /><br />";
			$body['body'] .= "<strong>".$user["name"]."</strong> has added the following corrective actions to the audit report.";
			$body['body'] .= "<br /><br />";
			$body['body'] .= "Type of Finding: ".$deviation['Type'];
			$body['body'] .= "<br />";
			$body['body'] .= "NCR/OBS statement: ".$deviation['Deviation'];
			$body['body'] .= "<br />";
			$body['body'] .= "Reference to Checklist: ".$deviation['Reference'];
			$body['body'] .= "<br /><br />";
			$body['body'] .= "Root Cause Analysis: ".$deviation['RootCause'];
			$body['body'] .= "<br />";
			$body['body'] .= "Proposed Corrective Action: ".$deviation['Measure'];
			$body['body'] .= "<br />";
			$body['body'] .= "Deadline: ".$deviation['Deadline'];
			$body['body'] .= "<br /><br />";
			$body['body'] .= "Regards,";
			$body['body'] .= "<br/>";
			$body['body'] .= "Halal e-Zone";
			sendEmail($body);		
		}
		
		insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], 'Corrective actions added to the audit report');
		
		echo json_encode(generateSuccessResponse('Data was updated'));
	}
	else {
		echo json_encode(generateSuccessResponse(array("errors" => "<ul>$errors</ul>", 'errorInfo' => $dbo->errorInfo())));
	}
}

function getDeviationData($data){
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$id = $data["id"];
	$sql = "
    SELECT 
        Type, 
        Deviation, 
        Reference, 
        RootCause, 
        Measure, 
        DATE_FORMAT(Deadline, '%d/%m/%Y') AS Deadline 
    FROM 
        tauditreport 
    WHERE 
        id = :id";	
	$stmt = $dbo->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_STR);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	$deviation = $stmt->fetch();
	echo json_encode(generateSuccessResponse(array("deviation" => $deviation, 'errorInfo' => $dbo->errorInfo())));
}

function getDeviations(){
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$sql = "SELECT * FROM tdeviations WHERE 1 = 1";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	$deviations = $stmt->fetchAll();
	echo json_encode(generateSuccessResponse(array("deviations" => $deviations, 'errorInfo' => $dbo->errorInfo())));
}

function getTaskDetails($data) {
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$id = $data["id"];
	$sql = "SELECT * FROM tdeviations WHERE id = :id";
	$stmt = $dbo->prepare($sql);
	$stmt->bindParam(':id', $id, PDO::PARAM_STR);
	$stmt->execute();
	$taskData = $stmt->fetch();
	echo json_encode(generateSuccessResponse(array("task" => $taskData, 'errorInfo' => $dbo->errorInfo())));
}
function getServices(){
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$sql = "SELECT * FROM tservices WHERE 1 = 1";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute();
	$services = $stmt->fetchAll();
	echo json_encode(generateSuccessResponse(array("services" => $services, 'errorInfo' => $dbo->errorInfo())));
}

function getAppData($data){
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$idclient = $data["idclient"];
	$idcycle = $data["idcycle"];
	$query = "SELECT *, DATE_FORMAT(last_login_sent, '%d/%m/%Y %h:%i %p') as last_login_sent FROM tusers WHERE id = :idclient";
	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
	$stmt->execute();
	$clientData = $stmt->fetch();
	$query = "SELECT *, date_format(last_report_sent, '%d/%m/%Y %h:%i %p') as last_report_sent, date_format(audit_date_1, '%d/%m/%Y') as audit_date_1, date_format(audit_date_2, '%d/%m/%Y') as audit_date_2, date_format(audit_date_3, '%d/%m/%Y') as audit_date_3, date_format(approved_date1, '%d/%m/%Y') as approved_date1f FROM tapplications WHERE idclient = :idclient AND idcycle = :idcycle";	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
	$stmt->bindParam(':idcycle', $idcycle, PDO::PARAM_STR);
	$stmt->execute();
	$appData = $stmt->fetch();
	if (!$appData) {
		/*
		$query = "INSERT INTO tapplications (idclient, idcycle) VALUES (:idclient, :idcycle)";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
		$stmt->bindParam(':idcycle', $idcycle, PDO::PARAM_STR);
		$stmt->execute();
		$idapp = $dbo->lastInsertId();
		$query = "SELECT *, date_format(approved_date1, '%d/%m/%Y') as approved_date1f FROM tapplications WHERE id = :idapp";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
		$stmt->execute();
		$appData = $stmt->fetch();
		*/
	}
	if($appData) {
		$myuser->getUserData();

		if ($myuser->userdata['isclient'] == '1' && $appData["state"] == "checklist") {
			$appData["state"] = "audit";
		}
		
		echo json_encode(generateSuccessResponse(array("appData" => $appData, "clientData" => $clientData, 'errorInfo' => $dbo->errorInfo())));
	}
	else echo json_encode(generateErrorResponse());
}

function getDisabledDates(){

	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();

	$query = "SELECT  start_date, end_date FROM tevents WHERE end_date >= CURDATE()";
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$rows = $stmt->fetchAll();
	$dates = [];
	foreach ($rows as $row) {
		$period = new DatePeriod(
			 new DateTime($row['start_date']),
			 new DateInterval('P1D'),
			 new DateTime($row['end_date'])
		);
		foreach ($period as $key => $value) {
			$dates[] = $value->format('d/m/Y');
		}
		$end_date = new DateTime($row['start_date']);
		$dates[] = $end_date->format('d/m/Y');
	}
	echo json_encode(generateSuccessResponse(['disabledDates'=>$dates]));
}

function saveAuditDates($data){
    global $adminEmailAddress, $calendarId, $serviceAccountFileName, $defaultTimezone, $supportEmailAddress;
    $dbo = &$GLOBALS['dbo'];
    $myuser = cuser::singleton();
    $myuser->getUserData(); 
    $idclient = intval($data["idclient"]);
    $idapp = intval($data["idapp"]);
    $AuditDate1 = $data["AuditDate1"];
    $AuditDate2 = $data["AuditDate2"];
    $AuditDate3 = $data["AuditDate3"];
    $preferredLanguage = isset($data["PreferredLanguage"]) ? trim($data["PreferredLanguage"]) : '';
    $englishAcceptable = isset($data["EnglishAcceptable"]) ? trim($data["EnglishAcceptable"]) : '';
    $tempDate1 = "";
    $tempDate2 = "";
    $tempDate3 = "";
    $SameDatesCheck = array();
    $errors = "";

    // Validate language preference fields
    if (empty($preferredLanguage)) {
        $errors .= "<li>Preferred audit language is required.</li>";
    } elseif (!in_array($preferredLanguage, ['ENGLISH', 'GERMAN', 'ITALIAN', 'FRENCH', 'HUNGARIAN'])) {
        $errors .= "<li>Invalid preferred audit language selected.</li>";
    }
    
    if (empty($englishAcceptable)) {
        $errors .= "<li>Please specify if an audit in English would be acceptable.</li>";
    } elseif (!in_array($englishAcceptable, ['Yes', 'No'])) {
        $errors .= "<li>Invalid value for English acceptable option.</li>";
    }

    $SameDatesCheck[$AuditDate1] = "1";
    $SameDatesCheck[$AuditDate2] = "1";
    $SameDatesCheck[$AuditDate3] = "1";

    $dateTime1 = "";
    $dateTime2 = "";
    $dateTime3 = "";

    if ($idclient <= 0 || $idapp <=0 ) {
        die(json_encode(generateErrorResponse("<ul><li>Internal server error!</li></ul>")));
    }

    // Error if date is already approved
    $query = "SELECT id FROM tevents WHERE idclient = :idclient AND idapp = :idapp AND status =1 LIMIT 0,1";
    $stmt = $dbo->prepare($query);
    $stmt->bindValue(':idclient', $idclient);
    $stmt->bindValue(':idapp', $idapp);
    $stmt->execute();
    if ($stmt->fetchColumn()) {
    //    $errors .= "<li>Audit date is already approved.</li>";
    }

    if ($errors == "") {
        $dates = [$AuditDate1, $AuditDate2, $AuditDate3];
        $validDates = [];
        $errors = "";
        
        foreach ($dates as $index => $date) {
            if ($date != "") {
                $dateTime = DateTime::createFromFormat('d/m/Y', $date);
                if ($dateTime === FALSE) {
                    $errors .= "<li>Date #" . ($index + 1) . " is invalid.</li>";
                } elseif ($dateTime->format('N') >= 6) {
                    $errors .= "<li>Date #" . ($index + 1) . " is on a weekend and not available.</li>";
                } else {
                    $tempDate = $dateTime->format('Y-m-d');
                    $validDates[$index] = $tempDate;                    
                }
            } else {
                $validDates[$index] = null;
            }
        }
        
        // Ensure at least one valid date is provided
        if (empty(array_filter($validDates))) {
            $errors .= "<li>At least one date is required.</li>";
        }
        
        // Ensure all provided dates are unique
        $filteredDates = array_filter($validDates);
        if (count(array_unique($filteredDates)) < count($filteredDates)) {
            $errors .= "<li>All the dates should be different from each other.</li>";
        }

        if ($errors == "") {
            $sql = "SELECT * FROM tusers WHERE id=:idclient";
            $stmt = $dbo->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->bindValue(':idclient', $idclient);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Update application with audit dates AND language preferences
            $query = "UPDATE tapplications SET 
                      audit_date_1 = :AuditDate1, 
                      audit_date_2 = :AuditDate2, 
                      audit_date_3 = :AuditDate3,
                      preferred_language = :PreferredLanguage,
                      english_acceptable = :EnglishAcceptable
                      WHERE idclient = :idclient AND id = :idapp";
            
            $stmt = $dbo->prepare($query);
            $stmt->bindValue(':AuditDate1', $validDates[0], $validDates[0] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':AuditDate2', $validDates[1], $validDates[1] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':AuditDate3', $validDates[2], $validDates[2] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':PreferredLanguage', $preferredLanguage, PDO::PARAM_STR);
            $stmt->bindValue(':EnglishAcceptable', $englishAcceptable, PDO::PARAM_STR);
            $stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
            $stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
            $stmt->execute();            
			
            // Google Calendar API
            require __DIR__ . '/../vendor/autoload.php';
            $serviceAccountFilePath =  __DIR__ . '/../config/google/'.$serviceAccountFileName;
            $client = new Google_Client();
            $client->setAuthConfig($serviceAccountFilePath);
            $client->addScope(Google_Service_Calendar::CALENDAR);                
            // Authenticate with the service account
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithAssertion();
            }
            // Create a new Calendar service
            $service = new Google_Service_Calendar($client);

            $sql = "SELECT gcal_id FROM tevents WHERE idclient = :idclient AND idapp = :idapp AND gcal_id IS NOT NULL AND gcal_id != ''";
            $stmt = $dbo->prepare($sql);
            $stmt->bindValue(':idclient', $idclient);
            $stmt->bindValue(':idapp', $idapp);
            $stmt->execute();
            $gcal_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($gcal_ids as $gcal_id) {
                if (!empty($gcal_id)) {
                    try {
                        $service->events->delete($calendarId, $gcal_id);
                    } catch (Exception $e) {
                        // Handle any errors or log them
                    }
                }
            }
            $sql = "DELETE FROM tevents WHERE idclient=:idclient AND idapp=:idapp";
            $stmt = $dbo->prepare($sql);
            $stmt->bindValue(':idclient', $idclient);
            $stmt->bindValue(':idapp', $idapp);
            if(!$stmt->execute()) die(json_encode(generateErrorResponse("Unknown error!")));

            foreach ($dates as $date) {
                if (!empty($date)) {
                    $dateTime = DateTime::createFromFormat('d/m/Y', $date);
                    if ($dateTime === false) {
                        die(json_encode(generateErrorResponse("Invalid date format!")));
                    }
                    $formattedDate = $dateTime->format('Y-m-d');
            
                    $title = $user['name'];
                
                    // Insert event into the database
                    $sql = "INSERT INTO tevents (idclient, idapp, title, start_date, end_date, status) 
                            VALUES (:idclient, :idapp, :title, :start_date, :end_date, 0)";
                    $stmt = $dbo->prepare($sql);
                    $stmt->bindValue(':idclient', $idclient);
                    $stmt->bindValue(':idapp', $idapp);
                    $stmt->bindValue(':title', $title);
                    $stmt->bindValue(':start_date', $formattedDate, $formattedDate === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                    $stmt->bindValue(':end_date', $formattedDate, $formattedDate === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                
                    if (!$stmt->execute()) {
                        die(json_encode(generateErrorResponse("Unknown error!")));
                    }
                
                    $ID = $dbo->lastInsertId();
                }
            }
                        
            $ownerEmailAddress = "halal.ezone@gmail.com";
            $fromEmailAddress = "noreply@halal-e.zone";

            // Prepare language information for emails
            $languageInfo = "<strong>Language Preferences:</strong><br>";
            $languageInfo .= "Preferred Language: " . $preferredLanguage . "<br>";
            $languageInfo .= "English Acceptable: " . $englishAcceptable . "<br><br>";

            // sending notification to admin
            $body = [];
            $body['name'] = 'Halal e-Zone';
            $body['email'] =  $fromEmailAddress;
            $body['to'] = $supportEmailAddress; 
            $body['subject'] = "Halal e-Zone - Audit Date Proposals - ".$user["name"];            
            $body['header'] = "";
            $body['body'] = "Dear Admin,";
            $body['body'] .= "<br /><br />";
            $body['body'] .= "<strong>".$user["name"]."</strong> has provided three proposed dates for the audit. These are as follows:";
            $body['body'] .= "<br /><br />";
            $body['body'] .= $AuditDate1;
            $body['body'] .= "<br />";
            $body['body'] .= $AuditDate2;
            $body['body'] .= "<br />";
            $body['body'] .= $AuditDate3;
            $body['body'] .= "<br /><br />";
            $body['body'] .= $languageInfo;
            $body['body'] .= "Regards,";
            $body['body'] .= "<br/>";
            $body['body'] .= "Halal e-Zone";

            sendEmail($body);
                    
            $json_id = json_encode((string) $idclient);
            $sql = "SELECT *
                    FROM tusers 
                    WHERE isclient = 2 
                    AND deleted = 0  
                    AND JSON_CONTAINS(clients_audit, :json_id, '$')";                    

            $stmt = $dbo->prepare($sql);
            $stmt->bindParam(':json_id', $json_id);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as $row) {
                $emails = explode(',', $row['email']);
                foreach ($emails as $email) {
                    $body = [];
                    $body['name'] = 'Halal e-Zone';
                    $body['email'] =  $fromEmailAddress;
                    $body['to'] = trim($email);
                    $body['subject'] = "Halal e-Zone - Audit Date Proposals - " . $user["name"];
                    $body['header'] = "";
                    $body['body'] = "Dear Auditor,";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "<strong>" . $user["name"] . "</strong> has provided three proposed dates for the audit. These are as follows:";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= $AuditDate1;
                    $body['body'] .= "<br />";
                    $body['body'] .= $AuditDate2;
                    $body['body'] .= "<br />";
                    $body['body'] .= $AuditDate3;
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= $languageInfo;
                    $body['body'] .= "Regards,";
                    $body['body'] .= "<br/>";
                    $body['body'] .= "Halal e-Zone";
                    sendEmail($body);
                }
            }

            insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], 'Proposed three audit dates and language preferences');            

            echo json_encode(generateSuccessResponse());
        }
    }
    if ($errors != "") {
        echo json_encode(generateErrorResponse("<ul>".$errors."</ul>"));
    }
}

function saveAuditPlanSettings() {

	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$idclient = intval($_POST["idclient"]);
	$idapp = intval($_POST["idapp"]);
	$settings = [];
	foreach ($_POST as $k=>$v) {
		if ($k != "idclient" && $k != "idapp" && $k != "rtype" && $k != "uid") {
			$settings[$k] = $v;
		}
	}
	$audit_plan_settings = json_encode($settings);

	if ($idclient <= 0 || $idapp <=0 ) {
		die(json_encode(generateErrorResponse("<ul><li>Internal server error!</li></ul>")));
	}

	$query = "UPDATE tapplications SET audit_plan_settings=:audit_plan_settings,
	countryOfCompany=:countryOfCompany,
	addresses=:addresses,
	companyId=:companyId,
	reference=:reference,
	LeadAuditor=:LeadAuditor,
	coAuditor=:coAuditor,
	IslamicAffairsExpert=:IslamicAffairsExpert,
	Veterinary=:Veterinary
	 WHERE idclient=:idclient AND id=:idapp";
	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':audit_plan_settings', $audit_plan_settings, PDO::PARAM_STR);
	$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
	$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
	$stmt->bindParam(':countryOfCompany', $_POST["countryOfCompany"], PDO::PARAM_STR);
	$stmt->bindParam(':addresses', $_POST["addresses"], PDO::PARAM_STR);
	$stmt->bindParam(':companyId', $_POST["companyId"], PDO::PARAM_STR);
	$stmt->bindParam(':reference', $_POST["reference"], PDO::PARAM_STR);
	$stmt->bindParam(':LeadAuditor', $_POST["LeadAuditor"], PDO::PARAM_STR);
	$stmt->bindParam(':coAuditor', $_POST["coAuditor"], PDO::PARAM_STR);
	$stmt->bindParam(':IslamicAffairsExpert', $_POST["IslamicAffairsExpert"], PDO::PARAM_STR);
	$stmt->bindParam(':Veterinary', $_POST["Veterinary"], PDO::PARAM_STR);
	$stmt->execute();

	if ($errors != "") {
		echo json_encode(generateErrorResponse("<ul>".$errors."</ul>"));
	}
	echo json_encode(generateSuccessResponse([]));
}

function saveCertificateData($data){

	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$idclient = $data["idclient"];
	$idapp = $data["idapp"];
	$certificateNumber = trim($data["certificateNumber"]);
	$certificateIssueDate = trim($data["certificateIssueDate"]);
	$certificateExpiryDate = trim($data["certificateExpiryDate"]);
	$errors = "";
	$dateTime1 = "";
	$dateTime2 = "";

	if ($idclient <= 0 || $idapp <=0 ) {
		$errors .= "<li>Internal server error!</li></ul>";
		die(json_encode(generateSuccessResponse(array("errors" => $errors))));
	}
	if ($certificateNumber == "") {
		$errors .= "<li>Please enter Certificate Number.</li>";
	}
	if ($certificateIssueDate == "") {
		$errors .= "<li>Please enter Certificate Issue Date.</li>";
	}
	else if(($dateTime1 = DateTime::createFromFormat('d/m/Y', $certificateIssueDate)) === FALSE) {
		$errors .= "<li>Issue Date is invalid.</li>";
	}
	if ($certificateExpiryDate == "") {
		$errors .= "<li>Please enter Certificate Expiry Date.</li>";
	}
	else if(($dateTime2 = DateTime::createFromFormat('d/m/Y', $certificateExpiryDate)) === FALSE) {
		$errors .= "<li>Expiry Date is invalid.</li>";
	}
	if ($dateTime1) {
		$certificateIssueDate = $dateTime1->format('Y-m-d');
	}
	if ($dateTime2) {
		$certificateExpiryDate = $dateTime2->format('Y-m-d');
	}
	if ($errors == "") {
		$query = "UPDATE tapplications SET CertificateNumber=:certificateNumber, CertificateIssueDate=:certificateIssueDate, CertificateExpiryDate=:certificateExpiryDate WHERE idclient=:idclient AND id=:idapp";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':certificateNumber', $certificateNumber, PDO::PARAM_STR);
		$stmt->bindParam(':certificateIssueDate', $certificateIssueDate, PDO::PARAM_STR);
		$stmt->bindParam(':certificateExpiryDate', $certificateExpiryDate, PDO::PARAM_STR);
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
		$stmt->execute();

		$expdate = $certificateExpiryDate;
		$certdate = strtotime($expdate);
		$datediff = get_month_diff($certdate);
		$status = 0;
		if ($datediff <= 0) $status = 4;
		elseif ($datediff <= 30) $status = 3;
		elseif ($datediff <= 60) $status = 2;
		elseif ($datediff <= 90) $status = 1;
		$sql = "UPDATE tcertificates SET expdate = :expdate, status = :status WHERE idclient = :idclient AND idapp = :idapp";
		$stmt = $dbo->prepare($sql);
		$stmt->bindParam(':expdate', $expdate);
		$stmt->bindParam(':status', $status);
		$stmt->bindParam(':idclient', $idclient);
		$stmt->bindParam(':idapp', $idapp);
		$stmt->execute();

		$stmt->execute();
	}

	if ($errors != "") {
		die(json_encode(generateSuccessResponse(array("errors" => $errors))));
	}
	echo json_encode(generateSuccessResponse([]));
}

function saveAuditReportSettings() {

	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$idclient = intval($_POST["idclient"]);
	$idapp = intval($_POST["idapp"]);
	$settings = [];
	foreach ($_POST as $k=>$v) {
		if ($k != "idclient" && $k != "idapp" && $k != "rtype" && $k != "uid") {
			$settings[$k] = $v;
		}
	}
	$audit_report_settings = json_encode($settings);

	if ($idclient <= 0 || $idapp <=0 ) {
		die(json_encode(generateErrorResponse("<ul><li>Internal server error!</li></ul>")));
	}

	$query = "UPDATE tapplications SET audit_report_settings=:audit_report_settings WHERE idclient=:idclient AND id=:idapp";
	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':audit_report_settings', $audit_report_settings, PDO::PARAM_STR);
	$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
	$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
	$stmt->execute();

	if ($errors != "") {
		echo json_encode(generateErrorResponse("<ul>".$errors."</ul>"));
	}
	echo json_encode(generateSuccessResponse([]));
}

function approveAuditDates($data){ 
	global $adminEmailAddress, $calendarId, $serviceAccountFileName, $defaultTimezone, $supportEmailAddress, $statusOptions;
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$myuser->getUserData();
	$iduser = $myuser->userdata['id'];
	$idclient = intval($data["idclient"]);
	$idapp = intval($data["idapp"]);
	$ApprovedDate1 = $data["ApprovedDate1"];
	$AuditDate1 = $data["AuditDate1"];
	$AuditDate2 = $data["AuditDate2"];
	$AuditDate3 = $data["AuditDate3"];
	//$ApprovedDate2 = $data["ApprovedDate2"] ?? "";
	//$ApprovedDate3 = $data["ApprovedDate3"] ?? "";
	$errors = "";
	$dateTime = "";

	if ($idclient <= 0 || $idapp <=0 ) {
		die(json_encode(generateErrorResponse("<ul><li>Internal server error!</li></ul>")));
	}
	
	$sql = "SELECT * FROM tapplications WHERE idclient=:idclient AND id=:idapp";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->bindValue(':idapp', $idapp);
	$stmt->execute();
	$appData = $stmt->fetch(PDO::FETCH_ASSOC);

	// Error if date is already approved
	$query = "SELECT id FROM tevents WHERE idclient = :idclient AND idapp = :idapp AND status =1 LIMIT 0,1";
	$stmt = $dbo->prepare($query);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->bindValue(':idapp', $idapp);
	$stmt->execute();
	$dateTime = "";
	$tempDate = "";
	if ($stmt->fetchColumn()) {
		//$errors .= "<li>Audit date is already approved.</li>";
	}
	if ($errors == "") {
	//	if ($ApprovedDate1 == "" && $ApprovedDate2 == "" && $ApprovedDate3 == "") {
		if ($ApprovedDate1 == "") {
			$errors .= "<li>Please select a date to approve.</li>";
		}
		else if(($dateTime = DateTime::createFromFormat('d/m/Y', $ApprovedDate1)) === FALSE) {
			$errors .= "<li>Date is invalid.</li>";
		}
		else {
			$tempDate = $dateTime->format("Y-m-d");
			if ($errors == "") {
				//$EndDate = $dateTime->modify('+1 day')->format('Y-m-d');
				/*
				$query = "SELECT id FROM tevents WHERE idclient<>:idclient AND idapp<>:idapp AND (:new_start <= end_date AND :new_end  >= start_date AND status =1) LIMIT 0,1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':new_start', $tempDate, PDO::PARAM_STR);
				$stmt->bindParam(':new_end', $tempDate, PDO::PARAM_STR);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if ($stmt->fetchColumn()) {
					$errors .= "<li>Date is not available.</li>";
				}
				*/
			}
		}
		/*
		if($ApprovedDate2 != "" && $dateTime = DateTime::createFromFormat('Y-m-d', $ApprovedDate2) == FALSE) {
			$errors .= "<li>Date #2 is invalid.</li>";
		}
		else if ($ApprovedDate2 != "") {
			if ($errors == "") {
				$query = "SELECT id FROM tapplications WHERE idclient=:idclient AND id=:idapp AND ((audit_date_1=:ApprovedDate1 OR audit_date_2=:ApprovedDate2 OR  audit_date_3=:ApprovedDate3)) LIMIT 0,1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':ApprovedDate1', $ApprovedDate2, PDO::PARAM_STR);
				$stmt->bindParam(':ApprovedDate2', $ApprovedDate2, PDO::PARAM_STR);
				$stmt->bindParam(':ApprovedDate3', $ApprovedDate2, PDO::PARAM_STR);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= "<li>Date #2 is not available.</li>";
				}
			}
		}
		if($ApprovedDate3 != "" && $dateTime = DateTime::createFromFormat('Y-m-d', $ApprovedDate3) == FALSE) {
			$errors .= "<li>Date #3 is invalid.</li>";
		}
		else if ($ApprovedDate3 != "") {
			if ($errors == "") {
				$query = "SELECT id FROM tapplications WHERE idclient=:idclient AND id=:idapp AND (audit_date_1=:ApprovedDate1 OR audit_date_2=:ApprovedDate2 OR  audit_date_3=:ApprovedDate3) LIMIT 0,1";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':ApprovedDate1', $ApprovedDate3, PDO::PARAM_STR);
				$stmt->bindParam(':ApprovedDate2', $ApprovedDate3, PDO::PARAM_STR);
				$stmt->bindParam(':ApprovedDate3', $ApprovedDate3, PDO::PARAM_STR);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
				if (!$stmt->fetchColumn()) {
					$errors .= "<li>Date #3 is not available.</li>";
				}
			}
		}
		*/

		if ($errors== "") {
			$sql = "SELECT * FROM tusers WHERE id=:idclient";
			$stmt = $dbo->prepare($sql);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->bindValue(':idclient', $idclient);
			$stmt->execute();
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			$query = "UPDATE tapplications SET ".($AuditDate1 == "" ? "" : "audit_date_1=:AuditDate1,")." ".($AuditDate2 == "" ? "" : "audit_date_2=:AuditDate2,")." ".($AuditDate3 == "" ? "" : "audit_date_3=:AuditDate3,")." approved_date1=:ApprovedDate1, approved_by=:approved_by WHERE idclient=:idclient AND id=:idapp";
			$stmt = $dbo->prepare($query);
			$approved_by = $myuser->userdata['name'];
			if ($AuditDate1 != "") {
				$dateTime = DateTime::createFromFormat('d/m/Y', $AuditDate1);
				$AuditDate1 = $dateTime->format("Y-m-d");
				$stmt->bindParam(':AuditDate1', $AuditDate1, PDO::PARAM_STR);
			}
			if ($AuditDate2 != "") {
				$dateTime = DateTime::createFromFormat('d/m/Y', $AuditDate2);
				$AuditDate2 = $dateTime->format("Y-m-d");
				$stmt->bindParam(':AuditDate2', $AuditDate2, PDO::PARAM_STR);
			}
			if ($AuditDate3 != "") {
				$dateTime = DateTime::createFromFormat('d/m/Y', $AuditDate3);
				$AuditDate3 = $dateTime->format("Y-m-d");
				$stmt->bindParam(':AuditDate3', $AuditDate3, PDO::PARAM_STR);
			}
			$stmt->bindParam(':ApprovedDate1', $tempDate, PDO::PARAM_STR);
			$stmt->bindParam(':approved_by', $approved_by, PDO::PARAM_STR);
			$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
			$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
			$stmt->execute();

			// Google Calendar API
			require __DIR__ . '/../vendor/autoload.php';
			$serviceAccountFilePath =  __DIR__ . '/../config/google/'.$serviceAccountFileName;
			$client = new Google_Client();
			$client->setAuthConfig($serviceAccountFilePath);
			$client->addScope(Google_Service_Calendar::CALENDAR);				
			// Authenticate with the service account
			if ($client->isAccessTokenExpired()) {
				$client->fetchAccessTokenWithAssertion();
			}
			// Create a new Calendar service
			$service = new Google_Service_Calendar($client);

			$sql = "SELECT gcal_id FROM tevents WHERE idclient = :idclient AND idapp = :idapp AND gcal_id IS NOT NULL AND gcal_id != ''";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':idclient', $idclient);
			$stmt->bindValue(':idapp', $idapp);
			$stmt->execute();
			$gcal_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

			foreach ($gcal_ids as $gcal_id) {
				if (!empty($gcal_id)) { // Check if gcal_id is not empty
					try {
						$service->events->delete($calendarId, $gcal_id);
					} catch (Exception $e) {
						// Handle any errors or log them
					}
				}
			}

			$sql = "DELETE FROM tevents WHERE idclient=:idclient AND idapp=:idapp";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':idclient', $idclient);
			$stmt->bindValue(':idapp', $idapp);
			if(!$stmt->execute()) die(json_encode(generateErrorResponse("Unknown error!")));

			$sql = "INSERT INTO tevents (idclient, idapp, title, start_date, end_date, status) VALUES (:idclient, :idapp, :title, :start_date, :end_date, 1)";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':idclient', $idclient);
			$stmt->bindValue(':idapp', $idapp);
			$stmt->bindValue(':title', "".$user['name']."");
			$stmt->bindValue(':start_date', $tempDate);
			$stmt->bindValue(':end_date', $tempDate);
			if(!$stmt->execute()) die(json_encode(generateErrorResponse("Unknown error!")));
			$ID = $dbo->lastInsertId();

			$auditor_name = "";

			if ($idclient != '-1') {
				$json_id = json_encode((string) $idclient); // Convert to string and JSON encode
				$sql = "SELECT *
						FROM tusers 
						WHERE isclient = 2 
						AND deleted = 0  
						AND JSON_CONTAINS(clients_audit, :json_id, '$') LIMIT 0, 1";				
				$stmt = $dbo->prepare($sql);
				$stmt->bindParam(':json_id', $json_id);
				$stmt->execute();
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				// Check if a result was found
				if ($result) {
					$auditor_name = $result['name'] . ' - ';
				}		
			}
			$dateTime=	DateTime::createFromFormat('d/m/Y', $ApprovedDate1);

			$event = new Google_Service_Calendar_Event(array(
				'summary' => str_replace("Auditor_", "", str_replace("Auditor ", "", $auditor_name)). $user['name'],
 				'start' => array(
					'date' =>  $dateTime->format('Y-m-d'), // Start time of the event (in RFC3339 format)
					'timeZone' => $defaultTimezone, // Timezone of the event
				),
				'end' => array(
					'date' =>  $dateTime->format('Y-m-d'), // End time of the event (in RFC3339 format)
					'timeZone' => $defaultTimezone, // Timezone of the event
				),
				'colorId' => 10, // Set the color ID here
			));

			if ($event = $service->events->insert($calendarId, $event)) {
				$gcal_id = $event->id;
				// Update gcal_id in tevents table
				$updateSql = "UPDATE tevents SET gcal_id = :gcal_id WHERE id = :id";
				$updateStmt = $dbo->prepare($updateSql);
				$updateStmt->bindValue(':gcal_id', $gcal_id);
				$updateStmt->bindValue(':id', $ID);
				$updateStmt->execute();
			}

			$ownerEmailAddress = "halal.ezone@gmail.com";
			$fromEmailAddress = "noreply@halal-e.zone";

			// Explode and loop through email addresses
			$emails = explode(',', $user["email"]);
			foreach ($emails as $email) {
				$body = [];
				$body['name'] = 'Halal e-Zone';
				$body['email'] =  $fromEmailAddress;
				$body['to'] = trim($email); // Trim to remove any extra whitespace

				// sending notification
				$body['subject'] = "Halal e-Zone - Audit Date Approval - ".$user["name"];
				$body['header'] = "";
				$body['body'] = "Dear ".$user["name"].",";
				$body['body'] .= "<br /><br />";
				$body['body'] .= "We are glad to confirm you the following date for your audit. Please save the date and contact us for any assistance or clarification.";
				$body['body'] .= "<br /><br />";
				$body['body'] .= "<strong>". $ApprovedDate1."</strong>";
				$body['body'] .= "<br /><br />";
				$body['message'] .= "Kind Regards<br/>";
				$body['body'] .= "Your HQC Team ";

				sendEmail($body);
			}


			if ($myuser->userdata['isclient'] == '2') { // Auditor

				$ownerEmailAddress = "halal.ezone@gmail.com";
				$fromEmailAddress = "noreply@halal-e.zone";
				$body = [];

				//sendEmailWithAttach
				$body['name'] = 'Halal e-Zone';
				$body['email'] =  $fromEmailAddress;
				$body['to'] = $supportEmailAddress;

				//$body['attachhostpath'] = $dest_path;
				//$body['attach'] = $filename;
			//	$ApprovedDate1 = $ApprovedDate1;
			//	$ApprovedDate1F = DateTime::createFromFormat('d/m/Y', $ApprovedDate1)->format('d/m/Y');

				// sending notification
				$body['subject'] = "Halal e-Zone - Audit Date Approval - ".$user["name"];
				$body['header'] = "";
				$body['body'] = "Dear Admin,";
				$body['body'] .= "<br /><br />";
				$body['body'] .= "Auditor <strong>".$myuser->userdata['name']."</strong> has approved the following date for the audit of client <strong>".$user["name"]."</strong>.";
				$body['body'] .= "<br /><br />";
				$body['body'] .= "<strong>". $ApprovedDate1."</strong>";
				$body['body'] .= "<br /><br />";
				$body['message'] .= "Kind Regards<br/>";
				$body['body'] .= "Your HQC Team ";

				sendEmail($body);
			}
			else {

				$ownerEmailAddress = "halal.ezone@gmail.com";
				$fromEmailAddress = "noreply@halal-e.zone";
				$body = [];

				//sendEmailWithAttach
				$body['name'] = 'Halal e-Zone';
				$body['email'] =  $fromEmailAddress;
				$body['to'] = $supportEmailAddress;

				//$body['attachhostpath'] = $dest_path;
				//$body['attach'] = $filename;
			//	$ApprovedDate1 = $ApprovedDate1;
			//	$ApprovedDate1F = DateTime::createFromFormat('d/m/Y', $ApprovedDate1)->format('d/m/Y');

				// sending notification
				$body['subject'] = "Halal e-Zone - Audit Date Approval - ".$user["name"];
				$body['header'] = "";
				$body['body'] = "Dear Admin,";
				$body['body'] .= "<br /><br />";
				$body['body'] .= "Following date was approved for the audit of client <strong>".$user["name"]."</strong>.";
				$body['body'] .= "<br /><br />";
				$body['body'] .= "<strong>". $ApprovedDate1."</strong>";
				$body['body'] .= "<br /><br />";
				$body['message'] .= "Kind Regards<br/>";
				$body['body'] .= "Your HQC Team ";

				sendEmail($body);
			}

			/////////////////////////////////////////////////////////////
			$decode = file_get_contents( __DIR__ ."/../config.json");
			$config=json_decode($decode, TRUE);
		
			$body = [];
			$title = "Checklist"; 
			$category = "checklist";

			$userData = [];
			$query = "SELECT *
FROM tapplications	
WHERE id='".$idapp."' AND idclient='".$idclient."'";
$stmt = $dbo->prepare($query);
$stmt->execute();
$userData['app'] = $stmt->fetch(PDO::FETCH_ASSOC);

$query = "SELECT *
FROM tusers	
WHERE id='".$idclient."'";
$stmt = $dbo->prepare($query);
$stmt->execute();
$userData['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

$industry = $userData['user']["industry"];

/*
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

			$myuser = cuser::singleton();
			$myuser->getUserData();
			$iduser = $myuser->userdata['id'];

			//$attach = '../files/docs/'.$filetoattach;
			$attach = $filetoattach;
			$ext = "pdf";
			$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $user['name'])." (".$idclient.")/application/checklist/";
			$absolutePath = __DIR__ ."/../".$hostPath;
			if (!file_exists($absolutePath)) {
				mkdir($absolutePath, 0777, true);
			}

			$query = "DELETE FROM tdocs WHERE idapp=:idapp AND idclient=:idclient AND title=:title AND category=:category";
$stmt = $dbo->prepare($query);
$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
$stmt->bindParam(':title', $title, PDO::PARAM_STR);
$stmt->bindParam(':category', $category, PDO::PARAM_STR);
$stmt->execute();

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

			$ownerEmailAddress = "halal.ezone@gmail.com";
			$fromEmailAddress = "noreply@halal-e.zone";

			//sendEmailWithAttach
			$body['name'] = 'Halal e-Zone';
			$body['email'] =  $fromEmailAddress;
			$body['to'] = $user['email'];

			$body['attachhostpath'] = $dest_path;
			$body['attach'] = $filename;

			// sending notification
			$body['subject'] = "Halal e-Zone - ".$title . ' - '.$user["name"];
			$body['header'] = "";
			$body['message'] = "Dear Ms./ Mr. ".$user["contact_person"]."!<br /><br />";
		$body['message'] .= "Attached is the Auditor Checklist for your reference. It is also available on the Halal eZone portal.<br/><br/>";
		$body['message'] .= "Kind Regards<br/>";
		$body['message'] .= "Your HQC Team";
			//sendEmailWithAttach($body);
			
			// get cycle name
			$sql = "SELECT * FROM tcycles WHERE idclient=:idclient AND state = '1' ORDER BY id ASC LIMIT 1";
			$stmt = $dbo->prepare($sql);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->bindValue(':idclient', $idclient);
			$stmt->execute();
			$firstCycle = $stmt->fetch(PDO::FETCH_ASSOC);
			$cycleName = $firstCycle["name"];
			
			if (file_exists($absolutePath.'/'. $filename)) {
				 
			}
			*/

			$state = "report";

			$newIndex = array_search($state, $statusOptions);

			$query = "SELECT *
			FROM tapplications	
			WHERE id='".$idapp."' AND idclient='".$idclient."'";
			$stmt = $dbo->prepare($query);
			$stmt->execute();
			$appData = $stmt->fetch(PDO::FETCH_ASSOC);
			$currentState = $appData["state"];
			$currentIndex = array_search($currentState, $statusOptions);

			if ($newIndex > $currentIndex) { 
			//if ($appData["state"] == "dates") {
				$query = "UPDATE tapplications SET state=:state WHERE idclient=:idclient AND id=:idapp";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':state', $state, PDO::PARAM_STR);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();				

			}
			insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], 'Approved audit date');			

			echo json_encode(generateSuccessResponse(['approved_date1' => $tempDate, 'approved_date1f' => $ApprovedDate1, 'approved_by' =>$approved_by]));
		}			
			/////////////////////////////////////////////////////////////

			
		 
	}
	if ($errors != "") {
		echo json_encode(generateErrorResponse("<ul>".$errors."</ul>"));
	}
}

function login($data){
	$myuser = cuser::singleton();
	$myuser->sec_session_start();
	$res = $myuser->login($data['email'], $data['password']);
	if($res === 0) echo json_encode(generateSuccessResponse());
	else echo json_encode(generateErrorResponse($res));
}

function register($data) {

	global $adminEmailAddress, $country_list;
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	//$myuser->sec_session_start();
	//$res = $myuser->login($data['email'], $data['password']);
	$errors = array();
	$recpatcha_secret = '6Ld8HiIhAAAAACAWLM1dPaqbmkJIMatPMRnO_SOG';

	if (trim($data['name']) == "") {
		$errors['name'] = "Company Name is required.";
	}
	if (trim($data['address']) == "") {
		$errors['address'] = "Address is required.";
	}
	if (trim($data['city']) == "") {
		$errors['city'] = "City is required.";
	}
	//if (trim($data['state']) == "") {
	//	$errors['state'] = "State is required.";
	//}
	if (trim($data['zip']) == "") {
		$errors['zip'] = "Zip Code is required.";
	}
	if (trim($data['country']) == "") {
		$errors['country'] = "Country is required.";
	}
	if (trim($data['industry']) == "") {
		$errors['industry'] = "Industry is required.";
	}
	if (trim($data['category']) == "") {
		$errors['category'] = "Product Category is required.";
	}
	if (trim($data['prodnumber']) == "") {
		$errors['prodnumber'] = "Number of products is required.";
	}
	else if (!is_numeric($data['prodnumber'])) {
		$errors['prodnumber'] = "Number of products must be numeric.";
	}
	if (trim($data['ingrednumber']) == "") {
		$errors['ingrednumber'] = "Number of raw materials is required.";
	}
	else if (!is_numeric($data['ingrednumber'])) {
		$errors['ingrednumber'] = "Number of raw materials must be numeric.";
	}
	if (trim($data['vat']) == "") {
		$errors['vat'] = "VAT Number is required.";
	}
	if (trim($data['email']) == "") {
		$errors['email'] = "Email Address is required.";
	}
	else if (!filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
	  $errors['email'] = "Invalid Email Address.";
	}
	else {
		$sql = "SELECT id FROM tusers WHERE email=:email LIMIT 0,1";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':email', $data['email']);
		$stmt->execute();
		if ($stmt->fetchColumn()) {
		  $errors['email'] = "Email Address already exists.";
		}
	}
	if (trim($data['contact_person']) == "") {
		$errors['contact_person'] = "Contact Person Name is required.";
	}
	if (trim($data['cemail']) == "" ) {
		$errors['cemail'] = "Confirm Email Address is required.";
	}
	if (trim($data['email']) != "" && trim($data['cemail']) != "" && (trim($data['email']) != trim($data['cemail']))) {
		$errors['email'] = "Email Address and Confirm Email Address mismatch.";
	}
	if(!isset($data['captcha']) || $data['captcha'] == "") {
		$errors['captcha'] = "Error verifying reCAPTCHA, Please try again.";
	}
	else {
		
        $ip = $_SERVER['REMOTE_ADDR'];
        // post request to server
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($recpatcha_secret) .  '&response=' . urlencode($data['captcha']);
        $response = file_get_contents($url);
        $responseKeys = json_decode($response,true);
        // should return JSON with success as true
        if (!$responseKeys["success"]) {
			$errors['captcha'] = 'Error verifying reCAPTCHA, Please try again.';
        }
	}	 
	$datax = [];
	if (count($errors) != 0) {
		$datax['errors'] = $errors;
	}
	else {
		$appToken = getToken();
		
		$country_code = array_search($data['country'], $country_list);
		$prefix = "CID_".date('m')."/".date('y')."_".$country_code."_";

		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "INSERT INTO tusers (
            name, login, email, address, city, zip, country, industry, category, 
            prodnumber, ingrednumber, vat, contact_person, phone, lang, 
            isclient, app_token, prefix, 
            pork_free_facility, dedicated_halal_lines, export_regions, 
            third_party_products, third_party_halal_certified
        ) VALUES (
            :name, :login, :email, :address, :city, :zip, :country, :industry, :category, 
            :prodnumber, :ingrednumber, :vat, :contact_person, :phone, :lang, 
            1, :app_token, :prefix, 
            :pork_free_facility, :dedicated_halal_lines, :export_regions, 
            :third_party_products, :third_party_halal_certified
        )";

		$stmt = $dbo->prepare($sql);

		$trimmed = strtolower(str_replace(" ", "", $data['name']));
		$login =  random_username($trimmed);

		$stmt->bindValue(':name', $data['name']);
		$stmt->bindValue(':login', $login);
		$stmt->bindValue(':email', $data['email']);
		$stmt->bindValue(':address', $data['address']);
		$stmt->bindValue(':city', $data['city']);
		$stmt->bindValue(':zip', $data['zip']);
		$stmt->bindValue(':country', $data['country']);
		$stmt->bindValue(':industry', $data['industry']);
		$stmt->bindValue(':category', $data['category']);
		$stmt->bindValue(':prodnumber', $data['prodnumber']);
		$stmt->bindValue(':ingrednumber', $data['ingrednumber']);
		$stmt->bindValue(':vat', $data['vat']);
		$stmt->bindValue(':contact_person', $data['contact_person']);
		$stmt->bindValue(':phone', $data['phone']);
		$stmt->bindValue(':lang', $data['lang']);
		$stmt->bindValue(':app_token', $appToken);
		$stmt->bindValue(':prefix', $prefix);
		$stmt->bindValue(':pork_free_facility', $data['pork_free_facility']);
		$stmt->bindValue(':dedicated_halal_lines', $data['dedicated_halal_lines']);
		$stmt->bindValue(':export_regions', $data['export_regions']);
		$stmt->bindValue(':third_party_products', $data['third_party_products']);
		$stmt->bindValue(':third_party_halal_certified', $data['third_party_halal_certified']);
		$stmt->execute();
		$idclient = $dbo->lastInsertId();

		$sql = "INSERT INTO tcycles (idclient, name, `state`) " .
		" VALUES (:idclient, :name, 1)";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->bindValue(':name', date('Y'));
		//$stmt->bindValue(':startDate', $startDt);
		//$stmt->bindValue(':endDate', $endDt);
		$stmt->execute();
		$idcycle = $dbo->lastInsertId();

		$query = "INSERT INTO tapplications (idclient, idcycle, prodnumber, ingrednumber, state) VALUES (:idclient, :idcycle, :prodnumber, :ingrednumber, 'offer')";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
		$stmt->bindParam(':idcycle', $idcycle, PDO::PARAM_STR);
		$stmt->bindValue(':prodnumber', $data['prodnumber']);
		$stmt->bindValue(':ingrednumber', $data['ingrednumber']);
		$stmt->execute();
		$idapp = $dbo->lastInsertId();

		insertActivityLog($idclient, $idapp, $idclient, $data['name'], 'New Registration');			
		
		$ownerEmailAddress = "halal.ezone@gmail.com";
		$fromEmailAddress = "noreply@halal-e.zone";

		$decode = file_get_contents( __DIR__ ."/../config.json");
		$config=json_decode($decode, TRUE);
		$attach = 'F0422 HQC Application Form.pdf';
		$ext = "pdf";
		 $hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $client)."/application/app/";
		 $absolutePath = __DIR__ ."/../".$hostPath;
	 
		mkdir($absolutePath, 0777, true);
		$filename = str_replace(".".$ext, '_'.$idapp.'.'.$ext, $attach);
		$dest_path = $absolutePath . $filename;
		$fields = array(
			'Company Name' => $data['name'],
			'Company Representative' => $data['contact_person'],
			'Date of Request' => date('d/m/Y'),
			'Official Company Names' => $data['name'],
			'Contact person Name Email Telephone number' => $data['contact_person'] . ', '. $data['email'] . ', '. $data['phone'],
			'Text1' => $data['address'],
			'Example Ltd SA SPA BV GMBH AS NVVATTaxBTW Number' => $data['vat']);		
		saveApplicationPDF1($fields, '../files/docs/'.$attach, $dest_path);

		//sendEmailWithAttach
		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $data['email'];

		$body['attachhostpath'] = $dest_path;
		$body['attach'] = $filename;

		// sending notification
		$body['subject'] = "Halal e-Zone - Registration Confirmation - ".$data['name'];
		$body['header'] = "";
		$body['message'] = 'Thank you for registering. We have attached the "Application Form" for you to download, fill out, and upload using the link provided below. If you have any questions or require any assistance, please do not hesitate to contact us.';
		$body['message'] .= "<br /><br />";
		$body['message'] .= '<a href="http://halal-e.zone/upload?code='.urlencode($appToken).'">http://halal-e.zone/upload?code='.$appToken.'</a><br/><br/>';
		$body['message'] .= "Kind Regards,";
		$body['message'] .= "<br/>";
		$body['message'] .= "Your HQC supporting Team";

		$Title = 'Application';
		$category = 'app';

		$adminId= 16;
		$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, hostpath, category, filename, status) 
		VALUES  (:idapp, :idclient, :iduser, :title, :hostpath, :category, :filename, 1)";
$stmt = $dbo->prepare($query);
$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
$stmt->bindParam(':iduser', $adminId, PDO::PARAM_STR);
$stmt->bindParam(':title', $Title, PDO::PARAM_STR);
$stmt->bindParam(':hostpath', $hostPath, PDO::PARAM_STR);
$stmt->bindParam(':category', $category, PDO::PARAM_STR);
$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
$stmt->execute();

		//sendEmailWithAttach($body);

		//sendEmailWithAttach
		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $adminEmailAddress;

		$body['attachhostpath'] = $dest_path;
		$body['attach'] = $filename;

		// sending notification
		$body['subject'] = "Halal e-Zone - Registration Notification - ".$data['name'];
		$body['header'] = "";
		$body['message'] = 'Dear Admin,<br/>
		A new client has registered on the Halal e-Zone platform. The registration details are as follows:<br/><br/>
			Name: '.$data['name'].'<br/>
			Email: '.$data['email'].'<br/>
			Address: '. $data['address'].'<br/>
			City: '. $data['city'].'<br/>
			Zip Code: '. $data['zip'].'<br/>
			Country: '. $data['country'].'<br/>
			Industry: '. $data['industry'].'<br/>
			Category: '. $data['category'].'<br/>
			Number of products to be certified(estimated): '. $data['prodnumber'].'<br/>
			Number of raw materials(estimated): '. $data['ingrednumber'].'<br/>
			Vat: '. $data['vat'].'<br/>
			Contact Person: '. $data['contact_person'].'<br/>
			Phone: '. $data['phone'].'<br/>
			Is your facility a pork-free facility? '.$data['pork_free_facility'].'<br/>
			Do you have dedicated lines for Halal production? '.$data['dedicated_halal_lines'].'<br/>
			What are your target export regions? '.$data['export_regions'].'<br/>
			Are the products to be Halal certified, produced by a third party? '.$data['third_party_products'].'<br/>
			Is this third party Halal certified? '.$data['third_party_halal_certified'].'<br/>		
			Date of Registration: '.date('d/m/Y');
		$body['message'] .= "<br /><br />";
		$body['message'] .= 'Best regards,<br />
		Halal e-Zone';

		sendEmailWithAttach($body);

		echo json_encode(generateSuccessResponse(array("id" => $idclient, 'errorInfo' => $dbo->errorInfo())));
		exit;
	}

	echo json_encode(generateSuccessResponse($datax));
}


function logout(){
	//$_SESSION = array();
	//$params = session_get_cookie_params();
	/*
	setcookie(session_name(),
		'', time() - 42000,
		$params["path"],
		$params["domain"],
		$params["secure"],
		$params["httponly"]);
	*/
	session_destroy();
	echo json_encode(generateSuccessResponse());
}

/*
 * 	Products
 *
 * */
function getClients(){
	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$myuser = cuser::singleton();
		$myuser->getUserData();
		//print_r($userdata);
		if ($myuser->userdata['isclient'] == '2') { // Auditor
			$ids = [-1];
			$clients_audit = $myuser->userdata['clients_audit'];
			if ($clients_audit != "") {
				$ids = json_decode($clients_audit);
			}
   			$sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND deleted = 0 AND id IN (".implode(",", $ids).") ORDER BY name";
		}
		else {
			$sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND deleted = 0 ORDER BY name";
		}
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		if(!$stmt->execute()) {
			//echo json_encode(generateErrorResponse("Getting clients list failed"));
			die();
		}
		return $stmt->fetchAll();
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function sendClientsData() {
	echo json_encode(generateSuccessResponse(array("clients"=>getClients())));
}

function getIngredientsForProduct($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$filter = " AND (idclient = '".$prod['idclient']."' OR idclient IN (SELECT id FROM tusers WHERE parent_id = '".$prod['idclient']."'))";
		$sql = 'SELECT id, IFNULL(CONCAT("RMC_", id, "/", rmcode, "/", name), "") as text '.
			'from tingredients where 1 '.$filter.' and deleted=0 order by name';
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		//$stmt->bindValue(':idclient', $prod['idclient']);
		if(!$stmt->execute()) {
			echo json_encode(generateErrorResponse("Getting ingredients list failed"));
			die();
		}
		return $stmt->fetchAll();
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function sendIngredientsForProductData($prod) {
	echo json_encode(generateSuccessResponse(array("ingredients"=>getIngredientsForProduct($prod))));
}

function sendNextProdIdData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "INSERT INTO tproducts (idclient) VALUES (:idclient)";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idclient', $prod['idclient']);
		if(!$stmt->execute()) die(json_encode(generateErrorResponse("Adding new product failed")));
		echo json_encode(generateSuccessResponse(array("id" => $dbo->lastInsertId())));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}


function saveProductData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "UPDATE tproducts SET item=:item, ean=:ean, spec=:spec, addoc=:addoc, label=:label WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':item', $prod['item']);
		$stmt->bindValue(':ean', $prod['ean']);
		$stmt->bindValue(':spec', $prod['spec']);
		$stmt->bindValue(':addoc', $prod['addoc']);
		$stmt->bindValue(':label', $prod['label']);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Product updating failed'));
			die();
		}

		/*
		$sql = "DELETE FROM tp2i WHERE idp=".$prod['id'];
		$stmt = $dbo->prepare($sql);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Product updating failed'));
			die();
		}
		if(isset($prod['ingredients']) && !empty($prod['ingredients']))
		foreach($prod['ingredients'] as $i){
			$sql = "INSERT INTO tp2i (idp, idi) VALUES (:id, :idi)";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':idi', $i);
			$stmt->bindValue(':id', $prod['id']);
			if (!$stmt->execute()){
				echo json_encode(generateErrorResponse('Product ingredients list updating failed'));
				die();
			}
		}
		*/

		// Get existing ingredients for the product
		$sql = "SELECT idi FROM tp2i WHERE idp = :idp";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':idp', $prod['id']);
		$stmt->execute();
		$existingIngredients = $stmt->fetchAll(PDO::FETCH_COLUMN);

		// Convert existing ingredients to an array for easier comparison
		if (!$existingIngredients) {
			$existingIngredients = [];
		}

		// Process new ingredients
		if (isset($prod['ingredients']) && !empty($prod['ingredients'])) {
			foreach ($prod['ingredients'] as $i) {
				if (!in_array($i, $existingIngredients)) {
					// Insert only if the ingredient does not already exist
					$sql = "INSERT INTO tp2i (idp, idi) VALUES (:idp, :idi)";
					$stmt = $dbo->prepare($sql);
					$stmt->bindValue(':idp', $prod['id']);
					$stmt->bindValue(':idi', $i);
					if (!$stmt->execute()) {
						echo json_encode(generateErrorResponse('Product ingredients list updating failed'));
						die();
					}
				}
			}
		}

		// Optionally, you could delete any old ingredients that are no longer in the new list
		$toDelete = array_diff($existingIngredients, $prod['ingredients']);
		if (!empty($toDelete)) {
			$sql = "DELETE FROM tp2i WHERE idp = :idp AND idi = :idi";
			$stmt = $dbo->prepare($sql);
			foreach ($toDelete as $idi) {
				$stmt->bindValue(':idp', $prod['id']);
				$stmt->bindValue(':idi', $idi);
				if (!$stmt->execute()) {
					echo json_encode(generateErrorResponse('Failed to delete old ingredients'));
					die();
				}
			}
		}

		updateProductStats($prod['idclient']);
		echo json_encode(generateSuccessResponse('Product was updated'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function addProductData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "UPDATE tproducts SET item=:item, ean=:ean, spec=:spec, addoc=:addoc, label=:label WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':item', $prod['item']);
		$stmt->bindValue(':ean', $prod['ean']);
		$stmt->bindValue(':spec', $prod['spec']);
		$stmt->bindValue(':addoc', $prod['addoc']);
		$stmt->bindValue(':label', $prod['label']);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('New product adding failed'));
			die();
		}
		if(isset($prod['ingredients']) && !empty($prod['ingredients']))
		foreach($prod['ingredients'] as $i){
			$sql = "INSERT INTO tp2i (idp, idi) VALUES (:id, :idi)";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':idi', $i);
			$stmt->bindValue(':id', $prod['id']);
			if (!$stmt->execute()){
				echo json_encode(generateErrorResponse('Product ingredients list updating failed'));
				die();
			}
		}
		// sending notification
		$body['subject'] = "New Product notification";
		$body['header'] = "Client ".getClientInfo($prod['idclient'])." added a new product:";
		$body['body'] = $prod['item']." (HCP_".$prod['id'].")";
		//sendEmail($body);
		echo json_encode(generateSuccessResponse('Product was updated'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function removeProductData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "DELETE FROM tproducts WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Product removing failed'));
			die();
		}
		echo json_encode(generateSuccessResponse('Product was removed'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function markDeletedProductData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "UPDATE tproducts SET deleted = 1, deleted_at=NOW() WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Product removing failed'));
			die();
		}
		$sql = "SELECT idclient FROM tproducts WHERE id = :id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
			updateProductStats($row['idclient']);
        }
		echo json_encode(generateSuccessResponse('Product was removed'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function additionalItemsApplicationData($data) {
	global $adminEmailAddress, $supportEmailAddress;
	$resultPDF = [];
	$resultXLS = [];
	try {
        $ids = implode(',', $data['ids']);
        $result = array();
        $dbo = &$GLOBALS['dbo'];
		//$deleted = 0;
		$data['conformed']=0;
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = 'SELECT p.id, CONCAT("HCP_", p.id) as hcpid, p.item as product, p.ean, MIN(IFNULL(r.conf, 0)) as conf from tproducts p '.
            'left join tp2i on (tp2i.idp=p.id) '.
            'left join tingredients i on (i.id=tp2i.idi) '.
            'left join (SELECT i.id, (IF(MIN(s.conf) is NULL, 1, MIN(s.conf))*i.conf) as conf, GREATEST(IF(MAX(s.status) is NULL, 0, MAX(s.status)), i.status) as status from tingredients i '.
            'left join ti2i on (ti2i.idi1=i.id) '.
            'left join tingredients s on (s.id=ti2i.idi2) '.
            'Group by i.id) r on (r.id=i.id) '.
            'WHERE p.id in ('.$ids.') AND p.deleted=:deleted GROUP BY p.id';
        $res = $dbo->prepare($sql);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $res->bindValue(':deleted', $data['displaymode']);
        //$res->bindValue(':deleted', $deleted);
        $res->execute();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $sql='SELECT (IF(MIN(s.conf) is NULL, 1 , MIN(s.conf))*i.conf) as conf from tproducts p '.
                'left join tp2i on (tp2i.idp=p.id) '.
                'left join tingredients i on (i.id=tp2i.idi) '.
                'left join ti2i on (ti2i.idi1=i.id) '.
                'left join tingredients s on (s.id=ti2i.idi2) WHERE p.id='.$row['id'];
            $stmt = $dbo->prepare($sql);
            $stmt->execute();
            $ingr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $str = "[";
            $allconf = 1;
            foreach($ingr as $st){
                if(empty($st['ingred'])) continue;
                $str .= $st['ingred'] . ",";
                $allconf *= $st['conf'];
            }
            $str = rtrim($str, ',')."]";
            if($str == "[]"){
                $allconf = 0;
            }
            // return only nonconformed if filter is set
            if($data['conformed'] == 1) {
                if($allconf == 0) {
                    $result[] = $row;
                }
            }else{
                $result[] = $row;
            }
        }
		$return  =[];
		$return["products"] = $result;

		//////////////////////////////////////////////////////////////////////////

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $data["idclient"]);
	$stmt->execute();
	$return['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

	$sql = "SELECT * FROM tapplications WHERE idclient=:idclient1 AND idcycle=:idcycle";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient1', $data["idclient"]);
	$stmt->bindValue(':idcycle', $data['idcycle']);
	$stmt->execute();
	$return['app'] = $stmt->fetch(PDO::FETCH_ASSOC);
	$decode = file_get_contents( __DIR__ ."/../config.json");
	$config=json_decode($decode, TRUE);
	$client = $return['user']["name"]. ' ('.$return['user']["id"].')';

	$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".str_replace('"', '', $client)."/application/additional_items/";
	$absolutePath = __DIR__ ."/../".$hostPath;

	mkdir( $absolutePath, 0777, true);
	$attach = '../../files/docs//Additional items application.pdf';

	//if ($data['format'] == 'pdf') {
	if (1) {

		$attach = '../../files/docs//Additional items application.xls';
		$ext = "xls";

		$myuser = cuser::singleton();
		$myuser->getUserData();
		$Title = 'Additional Items Application XLS';
		$category = 'additional_items';
		$resultXLS = getConfirmedProductsExcelReport($return["products"], 'additional_items');

		$filename = basename($resultXLS["path"]);
		$xlsPath = str_replace($filename, "", $result["url"]);

		$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, hostpath, category, filename, status) 
							VALUES  (:idapp, :idclient, :iduser, :title, :hostpath, :category, :filename, 1)";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idapp', $return['app']['id'], PDO::PARAM_STR);
		$stmt->bindParam(':idclient', $data["idclient"], PDO::PARAM_STR);
		$stmt->bindParam(':iduser', $myuser->userdata['id'], PDO::PARAM_STR);
		$stmt->bindParam(':title', $Title, PDO::PARAM_STR);
		$stmt->bindParam(':hostpath', $xlsPath, PDO::PARAM_STR);
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);
		$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
		$stmt->execute();
		$iddoc = $dbo->lastInsertId();

		///////////////////////////////////////////////////////////////////

		$attach = '../../files/docs//Additional items application.pdf';
		$ext = "pdf";
		//$filename = str_replace(".".$ext, '_'.time().'.'.$ext, basename($attach));
		//$dest_path = $absolutePath . $filename;

		/////////////////////////////////////////////////////////////////
		$myuser = cuser::singleton();
		$myuser->getUserData();
		$Title = 'Additional Items Application PDF';
		$category = 'additional_items';

		/*
		$query = "DELETE FROM tdocs WHERE idapp=:idapp AND idclient=:idclient AND title=:title AND category=:category";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idapp', $return['app']['id'], PDO::PARAM_STR);
		$stmt->bindParam(':idclient', $data["idclient"], PDO::PARAM_STR);
		$stmt->bindParam(':title', $Title, PDO::PARAM_STR);
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);
		$stmt->execute();
		*/

		$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, hostpath, category, status) 
							VALUES  (:idapp, :idclient, :iduser, :title, :hostpath, :category, 1)";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idapp', $return['app']['id'], PDO::PARAM_STR);
		$stmt->bindParam(':idclient', $data["idclient"], PDO::PARAM_STR);
		$stmt->bindParam(':iduser', $myuser->userdata['id'], PDO::PARAM_STR);
		$stmt->bindParam(':title', $Title, PDO::PARAM_STR);
		$stmt->bindParam(':hostpath', $hostPath, PDO::PARAM_STR);
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);
		$stmt->execute();
		$iddoc = $dbo->lastInsertId();

		$filename = str_replace(".".$ext, '_'.$iddoc.'.'.$ext, basename($attach));
		$dest_path = $absolutePath . $filename;

		saveAdditionalItemsApplicationPDF($return, $attach, $dest_path);

		$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
		$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);
		$stmt->execute();

		/*
		$ownerEmailAddress = "halal.ezone@gmail.com";
		$fromEmailAddress = "noreply@halal-e.zone";

		$body = [];
		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $adminEmailAddress;

		$body['attachhostpath'] = $dest_path;
		$body['attach'] = $filename;
	// sending notification
	$body['subject'] = "Halal eZone - Additional Items Application";
	//$body['header'] = "Client ".getClientInfo($prod['idclient'])." added a new product:";
	$body['message'] = "Dear Admin,
<br /><br />
Client ".getClientInfo($data["idclient"])." has uploaded Additional Items Application. Please find attached. 
<br /><br />
Best regards,<br />
Your HQC supporting Team";
sendEmailWithAttach($body);
*/
		/*
		$query = "DELETE FROM tdocs WHERE idapp=:idapp AND idclient=:idclient AND title=:title AND category=:category";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idapp', $return['app']['id'], PDO::PARAM_STR);
		$stmt->bindParam(':idclient', $data["idclient"], PDO::PARAM_STR);
		$stmt->bindParam(':title', $Title, PDO::PARAM_STR);
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);
		$stmt->execute();
		*/

		//$file1 = $dest_path;
		//$resultXLS = getConfirmedProductsExcelReport($return["products"], 'additional_items');
 
	}
	//else if ($data['format'] == 'xls') {
	if (1) {

		//echo json_encode(generateSuccessResponse(getConfirmedProductsExcelReport($return["products"], 'additional_items')));
		//die();
		/*
		$filename = str_replace(".pdf", '.csv', basename($attach));
		$dest_path = $absolutePath . $filename;
		$csvdata = [];

		$csvdata[] = ["Company name/ Firma", $return["user"]["name"]];
		$csvdata[] = ["Contact person/ Ansprechperson", $return["user"]["contact_person"]];
		$csvdata[] = ["Tel.", $return["user"]["phone"]];
		$csvdata[] = ["E-Mail", $return["user"]["email"]];
		$csvdata[] = ["HQC certificate N�/ HQC Zertifikat Nummer", ""];
		$csvdata[] = ["", ""];

		$csvdata[] = ["Item name/ Artikelbezeichnung",
		  "Item N�/ Artikelnummer",
		  "Halal e-Zone HCP N�"];

	  foreach ($return["products"] as $product) {
		if ($product["conf"] == '1') {
			$csvdata[] = [$product['product'],
					  $product["id"],
					  $product["hcpid"]];
	    }
	  }
	  // Open a file in write mode ('w')
		$fp = fopen($dest_path , 'w');

		// Loop through file pointer and a line
		foreach ($csvdata as $fields) {
			fputcsv($fp, $fields);
		}

		fclose($fp);
		*/
	}
	//////////////////////////////////////////////////////////////////

		
		$resultPDF['name'] = $filename;
		$resultPDF['path'] = $filename;
		$resultPDF['url'] =  $config['filesfolder']."/".$config['clientsfolder']."/".$client."/application/additional_items/".$resultPDF['path'];
		$resultPDF['path'] = $dest_path;

		///////////////////////////////////////////////////////////////////////////
		$zip = new ZipArchive();
		$zipFilename = 'additional_items_application.zip';
		$zipFilePath = $absolutePath . 'additional_items_application.zip';

		if(file_exists($zipFilePath)) {
			unlink ($zipFilePath);
		}

		if ($zip->open($zipFilePath, ZIPARCHIVE::CREATE) != TRUE) {
				die ("Could not open archive");
		}

		// Add the first file to the zip
		$sourceFile1 = $resultPDF['path']; // Replace with the actual path of your first file
		$zip->addFile($sourceFile1, $resultPDF['name']);

		// Add the second file to the zip
		$sourceFile2 = $resultXLS['path']; // Replace with the actual path of your second file
		$zip->addFile($sourceFile2, $resultXLS['name']);

		// Close the zip file
		$zip->close();

		$ownerEmailAddress = "halal.ezone@gmail.com";
		$fromEmailAddress = "noreply@halal-e.zone";

		//sendEmailWithAttach
		$body = [];
		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $adminEmailAddress; 

		$body['attachhostpath'] = $zipFilePath;
		$body['attach'] = $zipFilename;

		// sending notification
		$body['subject'] = "Halal e-Zone - Additional Items Application - ".$return['user']["name"];
		$body['header'] = "";
		$body['message'] = "Dear Admin,<br /><br />";
		$body['message'] .= "<strong>".$return['user']["name"]."</strong> has uploaded the additional items application. Please find attached.<br/>";
		$body['message'] .= "Regards,";
		$body['message'] .= "<br/>";
		$body['message'] .= "Halal e-Zone";

		//$body['message'] .= getClientInformation($return['user']);

		//if ($data['format'] == 'xls') {
		sendEmailWithAttach($body);
			
		$body['to'] = $supportEmailAddress; 
		sendEmailWithAttach($body);

		//}



        echo json_encode(generateSuccessResponse(['url' => $config['filesfolder']."/".$config['clientsfolder']."/".$client."/application/additional_items/additional_items_application.zip", 'name' => 'additional_items_application.zip']));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

// Function to generate client information
function getClientInformation($user) {
    $message = "<h4>Client Information:</h4>";
    $message .= "<strong>Name:</strong> " . $user["name"] . "<br/>";
    $message .= "<strong>Address:</strong> " . $user["address"] . "<br/>";
    $message .= "<strong>Phone:</strong> " . $user["phone"] . "<br/>";
    $message .= "<strong>Email:</strong> " . $user["email"] . "<br/>";
    return $message;
}

function productsExcelReportData($data){
    try{
        $ids = implode(',', $data['ids']);
        $result = array();
        $dbo = &$GLOBALS['dbo'];
		//$deleted = 0;
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = 'SELECT p.id, CONCAT("HCP_", p.id) as hcpid, p.item as product, p.ean, MIN(IFNULL(r.conf, 0)) as conf from tproducts p '.
            'left join tp2i on (tp2i.idp=p.id) '.
            'left join tingredients i on (i.id=tp2i.idi) '.
            'left join (SELECT i.id, (IF(MIN(s.conf) is NULL, 1, MIN(s.conf))*i.conf) as conf, GREATEST(IF(MAX(s.status) is NULL, 0, MAX(s.status)), i.status) as status from tingredients i '.
            'left join ti2i on (ti2i.idi1=i.id) '.
            'left join tingredients s on (s.id=ti2i.idi2) '.
            'Group by i.id) r on (r.id=i.id) '.
            'WHERE p.id in ('.$ids.') AND p.deleted=:deleted GROUP BY p.id';
        $res = $dbo->prepare($sql);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $res->bindValue(':deleted', $data['displaymode']);
        //$res->bindValue(':deleted', $deleted);
        $res->execute();
        while($row = $res->fetch(PDO::FETCH_ASSOC)) {
            $sql='SELECT (IF(MIN(s.conf) is NULL, 1 , MIN(s.conf))*i.conf) as conf from tproducts p '.
                'left join tp2i on (tp2i.idp=p.id) '.
                'left join tingredients i on (i.id=tp2i.idi) '.
                'left join ti2i on (ti2i.idi1=i.id) '.
                'left join tingredients s on (s.id=ti2i.idi2) WHERE p.id='.$row['id'];
            $stmt = $dbo->prepare($sql);
            $stmt->execute();
            $ingr = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $str = "[";
            $allconf = 1;
            foreach($ingr as $st){
                if(empty($st['ingred'])) continue;
                $str .= $st['ingred'] . ",";
                $allconf *= $st['conf'];
            }
            $str = rtrim($str, ',')."]";
            if($str == "[]"){
                $allconf = 0;
            }
            // return only nonconformed if filter is set
            if($data['conformed'] == 1) {
                if($allconf == 0) {
                    $result[] = $row;
                }
            }else{
                $result[] = $row;
            }
        }
/*

        $sql = 'SELECT CONCAT("HCP_", p.id) as hcpid, p.item as product, p.ean, MIN(r.conf) as conf from tproducts p '.
            'left join tp2i on (tp2i.idp=p.id) '.
            'left join tingredients i on (i.id=tp2i.idi) '.
            'left join (SELECT i.id, (IF(MIN(s.conf) is NULL, 1, MIN(s.conf))*i.conf) as conf, GREATEST(IF(MAX(s.status) is NULL, 0, MAX(s.status)), i.status) as status from tingredients i '.
            'left join ti2i on (ti2i.idi1=i.id) '.
            'left join tingredients s on (s.id=ti2i.idi2) '.
            'Group by i.id) r on (r.id=i.id) '.
            ' WHERE p.idclient=:idclient AND p.deleted=:deleted GROUP BY p.id having (conf=:conf)  ORDER BY p.item';
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':idclient', $data['idclient']);
        $stmt->bindValue(':deleted', $data['displaymode']);
        $stmt->bindValue(':conf', $data['conformed']);

        if(!$stmt->execute()) {
            echo json_encode(generateErrorResponse("Getting products list failed"));
            die();
        }*/

        echo json_encode(generateSuccessResponse(getProductsExcelReport($result)));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function confirmedProductsExcelReportData($data){ // ONLY CONFIRMED!
    try{
        $ids = count($data['ids']) > 0 ? implode(',', $data['ids']) : "";
        $result = array();
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = 'SELECT p.id, p.item as product, p.ean, MIN(i.conf) as c from tproducts p '.
               'left join tp2i on (tp2i.idp=p.id) '.
               'left join tingredients i on (i.id=tp2i.idi) '.
               'where '.($ids != "" ? 'p.id in ('.$ids.') AND ': '').' i.deleted=0 and p.deleted=0 and p.idclient=:idclient GROUP BY p.id having c=1';
        $res = $dbo->prepare($sql);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $res->bindValue(':idclient', $data['idclient']);
        $res->execute();
        echo json_encode(generateSuccessResponse(getConfirmedProductsExcelReport($res)));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function allProductsExcelReportData($data){ // ONLY CONFIRMED!
    try{
        $ids = count($data['ids']) > 0 ? implode(',', $data['ids']) : "";
        $result = array();
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = 'SELECT p.id, p.item as product, p.ean, MIN(i.conf) as c from tproducts p '.
               'left join tp2i on (tp2i.idp=p.id) '.
               'left join tingredients i on (i.id=tp2i.idi) '.
               'where '.($ids != "" ? 'p.id in ('.$ids.') AND ': '').' p.idclient=:idclient GROUP BY p.id';
        $res = $dbo->prepare($sql);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $res->bindValue(':idclient', $data['idclient']);
        $res->execute();
        echo json_encode(generateSuccessResponse(getConfirmedProductsExcelReport($res)));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function allClientsExcelReportData($data){ // ONLY CONFIRMED!
    try{
        $ids = count($data['ids']) > 0 ? implode(',', $data['ids']) : "";
        $result = array();
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = 'SELECT * FROM tusers u WHERE u.isclient=1 AND u.deleted=0 ORDER BY name';
        $res = $dbo->prepare($sql);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $res->bindValue(':idclient', $data['idclient']);
        $res->execute();
        echo json_encode(generateSuccessResponse(getAllClientsExcelReport($res)));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

/* Ingredients */

function getIngredientsForIngredient($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
        //$sql = 'SELECT id, CONCAT("RMC_", id, "/", rmcode, "/", name) as text from tingredients where  idclient=:idclient AND sub=1 AND deleted = 0 ORDER BY name';
        $sql = 'SELECT id, CONCAT("RMC_", id, "/", rmcode, "/", name) as text from tingredients where CONCAT("RMC_", id, "/", rmcode, "/", name) is NOT NULL AND idclient=:idclient AND deleted = 0 ORDER BY name';
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idclient', $prod['idclient']);
		if(!$stmt->execute()) {
			echo json_encode(generateErrorResponse("Getting ingredients list failed"));
			die();
		}
		return $stmt->fetchAll();
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function sendIngredientsForIngredientData($prod) {
	echo json_encode(generateSuccessResponse(array("ingredients"=>getIngredientsForIngredient($prod))));
}

function sendNextIngredIdData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "INSERT INTO tingredients (idclient) VALUES (:idclient)";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idclient', $prod['idclient']);
		if(!$stmt->execute()) die(json_encode(generateErrorResponse("Adding new ingredient failed")));
		echo json_encode(generateSuccessResponse(array("id" => $dbo->lastInsertId())));
	} catch (PDOException $e) {
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function savePAIngredientData($prod) {
	try {
		$myuser = cuser::singleton();
		$myuser->getUserData();

		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

		$ids = explode(",", $prod['ids']);

		foreach ($ids as $id) {
			$sql = "SELECT i.*, p.name AS producer_name 
			FROM tingredients_pa AS i 
			LEFT OUTER JOIN tproducers AS p ON i.producer_id = p.id WHERE i.id=".$id;
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':id', $id);
			$stmt->execute();
			$ingredient = $stmt->fetch();

			$sql = "INSERT INTO tingredients SET idclient=:idclient, id_paingred=:id_paingred, 
								rmcode=:rmcode, name=:name, producer=:producer, cb=:cb, halalexp=:halalexp, rmposition=:rmposition, halalcert=1, conf=1";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':idclient', $prod["idclient"]);
			$stmt->bindValue(':id_paingred', $ingredient['id']);
			$stmt->bindValue(':rmcode', $ingredient['rmcode']);
			$stmt->bindValue(':name', $ingredient['name']);
			$stmt->bindValue(':producer', $ingredient['producer_name']);
			$stmt->bindValue(':cb', $ingredient['cb']);
			$stmt->bindValue(':halalexp', $ingredient['halalexp']);
			$stmt->bindValue(':rmposition', $ingredient['rmposition']);
			$stmt->execute();
		}

		echo json_encode(generateSuccessResponse('Ingredient was updated'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function deletePAIngredientData($prod) {
	try {
		$myuser = cuser::singleton();
		$myuser->getUserData();

		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

		$ids = explode(",", $prod['ids']);

		foreach ($ids as $id) {
			$sql = "DELETE
			FROM tingredients_pa  WHERE id=".$id;
			$stmt = $dbo->prepare($sql);
			$stmt->execute();
		}

		echo json_encode(generateSuccessResponse('Ingredient was deleted'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function saveIngredientData($prod) {
	global $adminEmailAddress;
	try {
		$myuser = cuser::singleton();
		$myuser->getUserData();
		$status=0;
		if($prod['halalcert'] == 1) {
			if(isset($prod['halalexp']) && !empty($prod['halalexp'])){
			$date = date("Y-m-d", strtotime($prod['halalexp']));
			// count difference in dates to alarm expiry
			$now = time(); // or your date as well
			$certdate = strtotime($date);
			$datediff = floor(($certdate - $now) / (60 * 60 * 24));

			if ($datediff <= 7) $status = 3;
			elseif ($datediff <= 28) $status = 2;
			elseif ($datediff <= 56) $status = 1;
			}
		}

		$dbo = &$GLOBALS['dbo'];
		$sql = "SELECT * FROM tingredients WHERE id=".$prod['id'];
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		$stmt->execute();
		$ingredient = $stmt->fetch();

		$sql = "UPDATE tingredients SET sub=:sub, name=:name, supplier=:supplier, statement=:statement, halalcert=:halalcert, ".
					"cert=:cert, ".($prod['halalcert'] == 1 ? "cb=:cb, halalexp=:halalexp, rmposition=:rmposition,":"halalexp=:halalexp,")." rmcode=:rmcode, material=:material, spec=:spec, quest=:quest, ".
					" conf=:conf, note=:note, status=:status, addoc=:addoc, producer=:producer WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		//if ($myuser->userdata['isclient']) {
		//	$prod['conf'] = 0;
		//}
		$stmt->bindValue(':sub', $prod['sub']);
		$stmt->bindValue(':name', $prod['name']);
		$stmt->bindValue(':supplier', $prod['supplier']);
		$stmt->bindValue(':producer', $prod['producer']);
		$stmt->bindValue(':statement', $prod['state']);
		if($prod['halalcert'] == 1){
			$stmt->bindValue(':halalcert', 1);
			$stmt->bindValue(':cert', $prod['cert']);
			$stmt->bindValue(':cb', $prod['cb']);
			$stmt->bindValue(':halalexp', $date);
            $stmt->bindValue(':rmposition', $prod['rmposition']);
		}else{
			$stmt->bindValue(':halalcert', 0);
			$stmt->bindValue(':cert', $prod['cert']);
			//$stmt->bindValue(':cb', null, PDO::PARAM_INT);
			$stmt->bindValue(':halalexp', null, PDO::PARAM_INT);
           //$stmt->bindValue(':rmposition', null, PDO::PARAM_INT);
		}
		$stmt->bindValue(':spec', $prod['spec']);
		$stmt->bindValue(':rmcode', $prod['code']);
		if(!empty($prod['material']))
			$stmt->bindValue(':material', $prod['material']);
		else $stmt->bindValue(':material', null, PDO::PARAM_INT);
		$stmt->bindValue(':quest', $prod['quest']);
		$stmt->bindValue(':note', $prod['note']);
		$stmt->bindValue(':status', $status);
		$stmt->bindValue(':addoc', $prod['addoc']);

		if (!$myuser->userdata['isclient']) {
			if (isset($ingredient['cb']) && (strtolower($ingredient['cb']) != strtolower($prod['cb']))) {
				$prod['conf'] = 0;
			}
			$stmt->bindValue(':conf', $prod['conf']);
		}
		else {
			if (isset($ingredient['cb']) && (strtolower($ingredient['cb']) != strtolower($prod['cb']))) {
				$prod['conf'] = 0;
			}
			$stmt->bindValue(':conf', $prod['conf']);
		}
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			$dberror = $stmt->errorInfo();
			$ownerEmailAddress = "halal.ezone@gmail.com";
			$fromEmailAddress = "noreply@halal-e.zone";
			$body = [];
			$body['name'] = 'Halal e-Zone';
			$body['email'] =  $fromEmailAddress;
			$body['to'] = 'alrahmahsolutions@gmail.com';
			$body['header'] = "";
			// sending notification
			$body['subject'] = "Halal e-Zone - Ingredient updating failed";
			$body['header'] = "";
			$body['body'] .= print_r($dberror, true);
			$body['body'] .= print_r($ingredient, true);
			//sendEmail($body);
			echo json_encode(generateErrorResponse('Ingredient updating failed'));
			die();
    	}
		//$stmt->debugDumpParams();
		/////////////////////// INGREDIENT LOG /////////////////////////////////
		//if ($stmt->rowCount() > 0) {
		
		//}

		// if it is sub ingredient then check peer status
		$confirmed = 0;
		if (!$myuser->userdata['isclient'] || $myuser->userdata['isclient'] == '2') {
			if ($prod['sub']) {
				$sql = "SELECT idi1 FROM ti2i WHERE idi2=:id";
				$stmt = $dbo->prepare($sql);
				$stmt->bindValue(':id', $prod['id']);
				$stmt->execute();
				$parents = $stmt->fetchAll();
				foreach ($parents as $parent) {
					
					//$sql = 'SELECT IF( IF(s.conf is not NULL, (count(s.id)-SUM(s.conf)), 1), 0, 1) as conf  
					$sql = 'SELECT IF(COUNT(s.id) = 0, i.conf, IF(s.conf IS NOT NULL AND (COUNT(s.id) - SUM(s.conf)) = 0, 1, 0)) AS conf  
					from tingredients i '.
					'left join ti2i on (ti2i.idi1=i.id) '.
					'left join tingredients s on (s.id=ti2i.idi2) 
					WHERE i.id='.$parent["idi1"].'
					GROUP BY i.id';
					$stmt = $dbo->prepare($sql);
					$stmt->execute();
					$confirmed = $stmt->fetchColumn();
					$sql = "UPDATE tingredients SET conf = '".$confirmed."' WHERE id='".$parent["idi1"]."'";
					$stmt = $dbo->prepare($sql);
					$stmt->execute();
					if ($confirmed == 1) {
						$sql = "UPDATE tclientactions SET status = 1 WHERE itemid = :itemid";
						$stmt1 = $dbo->prepare($sql);
						$stmt1->bindValue(':itemid', $parent["idi1"]);
						$stmt1->execute();
						$sql = "UPDATE td2i SET status=2 WHERE idi=:id";
						$stmt = $dbo->prepare($sql);
						$stmt->bindValue(':id', $parent["idi1"]);
						$stmt->execute();
					}
				}
			}
			else {
				//$sql = 'SELECT IF(s.conf is NULL, '.$prod["conf"].', IF( IF(s.conf is not NULL, (count(s.id)-SUM(s.conf)), 1), 0, 1)) as conf 
				$sql = 'SELECT IF(COUNT(s.id) = 0, i.conf, IF(s.conf IS NOT NULL AND (COUNT(s.id) - SUM(s.conf)) = 0, 1, 0)) AS conf 
				from tingredients i '.
				'left join ti2i on (ti2i.idi1=i.id) '.
				'left join tingredients s on (s.id=ti2i.idi2) 
				WHERE i.id='.$prod["id"].'
					GROUP BY i.id';
					$stmt = $dbo->prepare($sql);
				$stmt->execute();
				$confirmed = $stmt->fetchColumn();
				$sql = "UPDATE tingredients SET conf = :conf WHERE id=:id";
				$stmt = $dbo->prepare($sql);
				$stmt->bindValue(':conf', $confirmed);
				$stmt->bindValue(':id', $prod["id"]);
				$stmt->execute();
				if ($confirmed == 1) {
					$sql = "UPDATE tclientactions SET status = 1 WHERE itemid = :itemid";
					$stmt1 = $dbo->prepare($sql);
					$stmt1->bindValue(':itemid', $prod["id"]);
					$stmt1->execute();
					$sql = "UPDATE td2i SET status=2 WHERE idi=:id";
					$stmt = $dbo->prepare($sql);
					$stmt->bindValue(':id', $prod["id"]);
					$stmt->execute();
				}
			}
		}

    	// remove all tasks (deviations) for the ingrediarent if it is conformed
		if (!$myuser->userdata['isclient'] || $myuser->userdata['isclient'] == '2') {

			if ($prod['conf'] == 1){
					$sql = "UPDATE tclientactions SET status = 1 WHERE itemid = :itemid";
					$stmt1 = $dbo->prepare($sql);
					$stmt1->bindValue(':itemid', $prod['id']);
					//$stmt1->bindValue(':itemcode', $prod['code']);
					$stmt1->execute();

				if(!confirmAllTaskForIngredient($prod)){
					echo json_encode(generateErrorResponse('Tasks updating failed'));
						die();
				}
			}

		}

		$sql = "DELETE FROM ti2i WHERE idi1=".$prod['id'];
		$res = $dbo->prepare($sql);
		if (!$res->execute()) die($sql);

		if(isset($prod['ingred']) && !empty($prod['ingred'])){
			foreach($prod['ingred'] as $i){
				$sql = "INSERT INTO ti2i (idi1, idi2) VALUES (:id, :idi)";
				$res = $dbo->prepare($sql);
				$res->bindValue(':id', $prod['id']);
				$res->bindValue(':idi', $i);
				if (!$res->execute()){
					echo json_encode(generateErrorResponse('Ingredient updating failed 2'));
					die();
				}
            }
		}

		$sql = 'SELECT d.deviation 
		FROM tdeviations d 
		INNER JOIN td2i ON td2i.idd = d.id AND td2i.idi = :idi 
		WHERE td2i.status < 2';

		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idi', $prod['id']);
		$stmt->execute();
		
		$deviations = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch only the deviation column
		
		// Concatenate all deviations into a single string (comma-separated)
		$tasks = implode("|", $deviations);
		 
		$sql = "INSERT INTO tingredients_log SET sub=:sub, name=:name, tasks=:tasks, supplier=:supplier, statement=:statement, halalcert=:halalcert, ".
					"cert=:cert, cb=:cb, halalexp=:halalexp, rmposition=:rmposition, ingredients=:ingredients, rmcode=:rmcode, material=:material, spec=:spec, quest=:quest, ".
					"conf=:conf, note=:note, status=:status, addoc=:addoc, producer=:producer, idingredient=:id, created_by=:created_by";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':sub', $prod['sub']);
		$stmt->bindValue(':name', $prod['name']);
		$stmt->bindValue(':tasks', $tasks);
		$stmt->bindValue(':supplier', $prod['supplier']);
		$stmt->bindValue(':producer', $prod['producer']);
		$stmt->bindValue(':statement', $prod['state']);
		if ($prod['halalcert'] == 1) {
			$stmt->bindValue(':halalcert', 1);
			$stmt->bindValue(':cert', $prod['cert']);
			$stmt->bindValue(':cb', $prod['cb']);
			$stmt->bindValue(':halalexp', $date);
			$stmt->bindValue(':rmposition', $prod['rmposition']);
		}
		else {
			$stmt->bindValue(':halalcert', 0);
			$stmt->bindValue(':cert', $prod['cert']);
			$stmt->bindValue(':cb', null, PDO::PARAM_INT);
			$stmt->bindValue(':halalexp', null, PDO::PARAM_INT);
			$stmt->bindValue(':rmposition', null, PDO::PARAM_INT);
		}
		$stmt->bindValue(':ingredients', $prod['ingredtext']);
		$stmt->bindValue(':spec', $prod['spec']);
		$stmt->bindValue(':rmcode', $prod['code']);
		if(!empty($prod['material']))
			$stmt->bindValue(':material', $prod['material']);
		else $stmt->bindValue(':material', null, PDO::PARAM_INT);
		$stmt->bindValue(':quest', $prod['quest']);
		$stmt->bindValue(':note', $prod['note']);
		$stmt->bindValue(':status', $status);
		$stmt->bindValue(':addoc', $prod['addoc']);

		if (!$myuser->userdata['isclient']) {
			if (isset($ingredient['cb']) && (strtolower($ingredient['cb']) != strtolower($prod['cb']))) {
				$prod['conf'] = 0;
			}
			$stmt->bindValue(':conf', $prod['conf']);
		}
		else {
			if (isset($ingredient['cb']) && (strtolower($ingredient['cb']) != strtolower($prod['cb']))) {
				$prod['conf'] = 0;
			}
			$stmt->bindValue(':conf', $prod['conf']);
		}
		$stmt->bindValue(':id', $prod['id']);
		$stmt->bindValue(':created_by', $myuser->userdata['name']);
		$stmt->execute();
		
		updateIngredientStats($prod['idclient']);
		echo json_encode(generateSuccessResponse('Ingredient was updated'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function addIngredientFiles($prod)
{
	$fields = ['statement', 'cert', 'spec', 'quest', 'addoc'];
	$values = array_intersect_key($prod, array_flip($fields));

	if (empty($values)) {
		// nothing was sent to update
		return;
	}

	$dbo = &$GLOBALS['dbo'];
	$sql = "SELECT * FROM tingredients WHERE id=:id";
	$stmt = $dbo->prepare($sql);
	$stmt->bindValue(':id', $prod['id']);
	$stmt->execute();
	$ingredient = $stmt->fetch();

	$updates = [];
	$clause  = [];

	if (isset($prod['cert'])) {
		$cbValue = $prod['cb'];
		$dateValue = $prod['date'];
		$rmpositionValue = $prod['rmposition'];

		// var_dump($ingredient['cert']);
		foreach ($values as $field => $value) {

			if (!empty($value)) {

				// Check if the field is initially empty
				if (empty($ingredient[$field])) {
					// If it is, paste the new value
					$updates[$field] = $value;
				} else {
                    // Otherwise, append the new value using a comma
					$jsonArrayString = '[' . $ingredient['cert'] . ']';

					// decode JSON
					$jsonArray = json_decode($jsonArrayString, true);

					if ($jsonArray === null) {
						echo "JSON error: " . json_last_error_msg();
						exit;
					}

					$delar = ""; // Initialize a string to hold the result
					$todayDate = date('d/m/Y');

					foreach ($jsonArray as $jsonObject) {
						// Check if deleted, deleted_at, deleted_by are given. If not, adding them
						if (!isset($jsonObject['deleted'])) {
							$jsonObject['deleted'] = 1;
						}
						if (!isset($jsonObject['deleted_at'])) {
							$jsonObject['deleted_at'] = $todayDate;
						}
						if (!isset($jsonObject['deleted_by'])) {
							$jsonObject['deleted_by'] = 'Admin';
						}

						// Convert the array back to json
						$jsonString = json_encode($jsonObject);

						// Add the converted json back to column value
						$delar .= ($delar === "" ? "" : ",") . $jsonString;
					}

					// After the complete cycle, $delar will have all the json objects joined by comma
					// var_dump($delar);

					$updates['cert'] = $delar . ',' . $value;
				}

				// Prepare the SQL query part
				$clause[$field] = $field . '=:' . $field;
			}
		}

		if (empty($updates)) {
			return;
		}

		$clause['cb'] = 'cb=:cb';
		$clause['halalexp'] = 'halalexp=:halalexp';
		$clause['rmposition'] = 'rmposition=:rmposition';

		$updates['cb'] = $cbValue;
		$updates['halalexp'] = date('Y-m-d', strtotime($dateValue));
		$updates['rmposition'] = $rmpositionValue;

		$strClause = implode(', ', $clause); // statement=:statement, cert=:cert ...
		$sql  = "UPDATE tingredients SET $strClause WHERE id=:id";
		//     var_dump($updates);
		// var_dump($sql);
		$stmt = $dbo->prepare($sql);

		$stmt->bindValue(':id', $prod['id']);

		foreach ($updates as $field => $value) {
			$stmt->bindValue(':' . $field, $value);
		}
	} else {
        // certificate wasn't provided among the files

		foreach ($values as $field => $value) {

			if (!empty($value)) {
				// insert or append to the existing field value
				$updates[$field] = empty($ingredient[$field])
					? $value
					: sprintf('%s,%s', $ingredient[$field], $value);
				$clause[$field] = sprintf('%s=:%s', $field, $field);
			}
		}

		if (empty($updates)) {
			return;
		}

		$strClause = implode(', ', $clause); // statement=:statement, cert=:cert ...
		$sql  = "UPDATE tingredients SET $strClause WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);

		foreach ($updates as $field => $value) {
			$stmt->bindValue(':' . $field, $value);
		}
	}

	if (!$stmt->execute()) {
		// var_dump($stmt->errorInfo());
		echo json_encode(generateErrorResponse('Failed to attach files'));
		die();
	}

	echo json_encode(generateSuccessResponse('Files were updated'));
}

function addActivityFiles($activityData) {
    $documentFields = [
        'invoice_inbound', 
		'travel_invoices', 
		'training_request_form', 
        'attendance_list', 
        'customer_feedback_form', 
        'attendance_certificates'
    ];
    
    $values = array_intersect_key($activityData, array_flip($documentFields));

    if (empty($values)) {
        // nothing was sent to update
        return;
    }

    $dbo = &$GLOBALS['dbo'];
    
    // Get current activity data
    $sql = "SELECT * FROM ttrainer_activities WHERE id=:id";
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':id', $activityData['id']);
    $stmt->execute();
    $activity = $stmt->fetch();

    $updates = [];
    $clause = [];

    foreach ($values as $field => $value) {

		if (!empty($value)) {
			// insert or append to the existing field value
			$updates[$field] = empty($activity[$field])
				? $value
				: sprintf('%s,%s', $activity[$field], $value);
			$clause[$field] = sprintf('%s=:%s', $field, $field);
		}
	}

	if (empty($updates)) {
		return;
	}	

    // Prepare the update query
    $strClause = implode(', ', $clause);
    $sql = "UPDATE ttrainer_activities SET $strClause WHERE id=:id";
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':id', $activityData['id']);

    foreach ($updates as $field => $value) {
        $stmt->bindValue(':' . $field, $value);
    }

    if (!$stmt->execute()) {
        echo json_encode(generateErrorResponse('Failed to attach files to activity'));
        die();
    }    

    echo json_encode(generateSuccessResponse('Activity documents were updated'));
}

function addProductFiles($prod)
{
    $fields = ['spec', 'addoc', 'label'];
    $values = array_intersect_key($prod, array_flip($fields));

    if (empty($values)) {
        echo json_encode(generateSuccessResponse('Nothing to update (0)'));

        return;
    }

    $dbo  = &$GLOBALS['dbo'];
    $sql  = "SELECT * FROM tproducts WHERE id=:id";
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':id', $prod['id']);
    $stmt->execute();
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(generateSuccessResponse('Nothing to update (1)'));

        return;
    }

    $updates = [];
    $clause  = [];

    foreach ($values as $field => $value) {
        if (!empty($value)) {
            // insert or append to the existing field value
            $updates[$field] = empty($product[$field])
                ? $value
                : sprintf('%s,%s', $product[$field], $value);
            $clause[$field]  = sprintf('%s=:%s', $field, $field);
        }
    }

    if (empty($updates)) {
        echo json_encode(generateSuccessResponse('Nothing to update (2)'));

        return;
    }

    $strClause = implode(', ', $clause); // statement=:statement, cert=:cert ...
    $sql       = "UPDATE tproducts SET $strClause WHERE id=:id";
    $stmt      = $dbo->prepare($sql);
    $stmt->bindValue(':id', $prod['id']);

    foreach ($updates as $field => $value) {
        $stmt->bindValue(':' . $field, $value);
    }

    if (!$stmt->execute()) {
   		// var_dump($stmt->errorInfo());
   		echo json_encode(generateErrorResponse('Failed to attach files'));
   		die();
   	}

   	echo json_encode(generateSuccessResponse('Files were updated'));
}


function removeCompanyData($prod){
	try {

		$dbo = &$GLOBALS['dbo'];

		$sql = "DELETE FROM tcompanies WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		$stmt->execute();

		$sql = "UPDATE tusers SET company_id = NULL WHERE company_id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Company removing failed'));
			die();
		}

		echo json_encode(generateSuccessResponse('Company was removed'));

	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function removeIngredientData($prod){
	try {

		$dbo = &$GLOBALS['dbo'];

		$sql = "DELETE FROM tp2i WHERE idi=:id)";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		$stmt->execute();

		$sql = "DELETE FROM ti2i WHERE idi2=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		$stmt->execute();

		$sql = "DELETE FROM tingredients WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Ingredient removing failed'));
			die();
		}

		echo json_encode(generateSuccessResponse('Ingredient was removed'));

	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function markDeletedIngredientData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];

		$ids = implode(',', $prod['ids']);

		$sql = "DELETE FROM tp2i WHERE idi IN (".$ids.")";
		$stmt = $dbo->prepare($sql);
		//$stmt->bindValue(':id', $prod['id']);
		$stmt->execute();

		$sql = "DELETE FROM ti2i WHERE idi1 IN (".$ids.")";
		$stmt = $dbo->prepare($sql);
		//$stmt->bindValue(':id', $prod['id']);
		$stmt->execute();

		$sql = "DELETE FROM ti2i WHERE idi2 IN (".$ids.")";
		$stmt = $dbo->prepare($sql);
		//$stmt->bindValue(':id', $prod['id']);
		$stmt->execute();

		$sql = "UPDATE tingredients SET deleted = 1 WHERE id IN (".$ids.")";
		$stmt = $dbo->prepare($sql);
		//$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Ingredient removing failed'));
			die();
		}

		$sql = "SELECT idclient FROM tingredients WHERE id IN (".$ids.")";
		$stmt = $dbo->prepare($sql);
		//$stmt->bindValue(':id', $prod['id']);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
			updateIngredientStats($row['idclient']);
        }

		echo json_encode(generateSuccessResponse('Ingredient was removed'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function markDeletedActivityData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];

		$ids = implode(',', $prod['ids']);

		$sql = "UPDATE ttrainer_activities SET deleted = 1 WHERE id IN (".$ids.")";
		$stmt = $dbo->prepare($sql);
		//$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Activity removing failed'));
			die();
		}

		echo json_encode(generateSuccessResponse('Activity was removed'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function changeConformityData($prod) {
	try {
		$dbo = &$GLOBALS['dbo'];
		$myuser = cuser::singleton();
		$myuser->getUserData();

		if ($myuser->userdata['isclient'] == "1") {
			echo json_encode(generateErrorResponse("Error: Only admin is allowed to perform this action."));
			die();
		}

		$sql = "UPDATE tingredients SET conf = conf XOR 1 WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()) {
			echo json_encode(generateErrorResponse('Ingredient updating failed'));
			die();
		}
		echo json_encode(generateSuccessResponse('Ingredient conformity updated'));

		///////////////////////////////////////////////////////
    	$sql = "SELECT * FROM tingredients WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		$stmt->execute();
		$prod = $stmt->fetch();
		$confirmed = 0;
		if ($prod['sub']) {
			$sql = "SELECT idi1 FROM ti2i WHERE idi2=:id";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':id', $prod['id']);
			$stmt->execute();
			$parents = $stmt->fetchAll();
			foreach ($parents as $parent) {
				$sql = 'SELECT IF( IF(s.conf is not NULL, (count(s.id)-SUM(s.conf)), 1), 0, 1) as conf  
				from tingredients i '.
				'left join ti2i on (ti2i.idi1=i.id) '.
				'left join tingredients s on (s.id=ti2i.idi2) 
				WHERE i.id='.$parent["idi1"].'
				GROUP BY i.id';
				$stmt = $dbo->prepare($sql);
				$stmt->execute();
				$confirmed = $stmt->fetchColumn();
				$sql = "UPDATE tingredients SET conf = '".$confirmed."' WHERE id='".$parent["idi1"]."'";
				$stmt = $dbo->prepare($sql);
				$stmt->execute();
				if ($confirmed == 1) {
					$sql = "UPDATE tclientactions SET status = 1 WHERE itemid = :itemid";
					$stmt1 = $dbo->prepare($sql);
					$stmt1->bindValue(':itemid', $parent["idi1"]);
					$stmt1->execute();
					$sql = "UPDATE td2i SET status=2 WHERE idi=:id";
					$stmt = $dbo->prepare($sql);
					$stmt->bindValue(':id', $parent["idi1"]);
					$stmt->execute();
				}
			}
		}
		else {
			$sql = 'SELECT IF(s.conf is NULL, '.$prod["conf"].', IF( IF(s.conf is not NULL, (count(s.id)-SUM(s.conf)), 1), 0, 1)) as conf  
			from tingredients i '.
			'left join ti2i on (ti2i.idi1=i.id) '.
			'left join tingredients s on (s.id=ti2i.idi2) 
			WHERE i.id='.$prod["id"].'
				GROUP BY i.id';
				$stmt = $dbo->prepare($sql);
			$stmt->execute();
			$confirmed = $stmt->fetchColumn();
			$sql = "UPDATE tingredients SET conf = :conf WHERE id=:id";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':conf', $confirmed);
			$stmt->bindValue(':id', $prod["id"]);
			$stmt->execute();
			if ($confirmed == 1) {
				$sql = "UPDATE tclientactions SET status = 1 WHERE itemid = :itemid";
				$stmt1 = $dbo->prepare($sql);
				$stmt1->bindValue(':itemid', $prod["id"]);
				$stmt1->execute();
				$sql = "UPDATE td2i SET status=2 WHERE idi=:id";
				$stmt = $dbo->prepare($sql);
				$stmt->bindValue(':id', $prod["id"]);
				$stmt->execute();
			}
		}
		///////////////////////////////////////////////////////

    	$sql = "SELECT conf FROM tingredients WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		$stmt->execute();
    	if ($stmt->fetch()['conf'] == 1) {

			// if conformed, reset all the tasks and Actions
			if (!confirmAllTaskForIngredient($prod)) {
				echo json_encode(generateErrorResponse('Tasks updating failed'));
				die();
			}
			if (!confirmAllActionsForIngredient($prod)) {
				echo json_encode(generateErrorResponse('Actions updating failed'));
				die();
			}
		}
		$sql = "INSERT INTO tingredients_log 
		(sub, name, supplier, statement, halalcert, cert, cb, halalexp, rmposition, rmcode, material, spec, quest, conf, note, status, addoc, producer, idingredient, created_by)		
		SELECT sub, name, supplier, statement, halalcert, cert, cb, halalexp, rmposition, rmcode, material, spec, quest, conf, note, status, addoc, producer, id, '".$myuser->userdata['name']."' FROM tingredients WHERE id = :id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod["id"]);
		$stmt->execute();

	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function restoreAdminData($data) {
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "UPDATE tusers SET deleted = 0 WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $data['id']);
		if (!$stmt->execute()) {
			echo json_encode(generateErrorResponse('Ingredient restoring failed'));
			die();
		}
		echo json_encode(generateSuccessResponse('Ingredient was restored'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function restoreIngredData($data) {
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "UPDATE tingredients SET deleted = 0 WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $data['id']);
		if (!$stmt->execute()) {
			echo json_encode(generateErrorResponse('Ingredient restoring failed'));
			die();
		}
		$sql = "SELECT idclient FROM tingredients WHERE id = :id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $data['id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
			updateIngredientStats($row['idclient']);
        }
		echo json_encode(generateSuccessResponse('Ingredient was restored'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function restoreProdData($data) {
	try {
		$dbo = &$GLOBALS['dbo'];
		$sql = "UPDATE tproducts SET deleted = 0 WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $data['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Product restoring failed'));
			die();
		}
		$sql = "SELECT idclient FROM tproducts WHERE id = :id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $data['id']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
			updateIngredientStats($row['idclient']);
        }
		echo json_encode(generateSuccessResponse('Product was restored'));
	} catch (PDOException $e) {
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

/*function assignTaskForIngredientData($prod){
  try{
    $dbo = &$GLOBALS['dbo'];
    $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    $idi = $prod['idi'];
    if (($prod['status']) == 0)
      $sql = "DELETE FROM td2i WHERE idd=:idd AND idi=:idi"; // remove the task
    else
      $sql = "INSERT INTO td2i (idd, idi) VALUES (:idd, :idi)"; // assign task;
    $stmt = $dbo->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->bindValue(':idd', $prod['idd']);
    $stmt->bindValue(':idi', $idi);
    if(!$stmt->execute()) die(json_encode(generateErrorResponse("Assigning new task failed")));
      echo json_encode(generateSuccessResponse(array("id" => $dbo->lastInsertId())));
  } catch (PDOException $e){
    echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
    die();
  }
}*/

function assignTaskForIngredientData($prod){
  try {
    $dbo = &$GLOBALS['dbo'];
    $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$myuser = cuser::singleton();
	$myuser->getUserData();   
	$idi = explode(',', $prod['idi']); // ????????? ?????? ?? ?????? ?? ???????
    $idd = $prod['idd'];
    $status = $prod['status'];
	$ingredId = "";	

    foreach ($idi as $singleId) {
	  $ingredId = $singleId;
      if ($status == 0) {
        $sql = "DELETE FROM td2i WHERE idd=:idd AND idi=:idi"; // ??????? ??????
      } else {
        $sql = "INSERT INTO td2i (idd, idi) VALUES (:idd, :idi)"; // ????????? ??????
      }

      $stmt = $dbo->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->bindValue(':idd', $idd);
      $stmt->bindValue(':idi', $singleId);

      if (!$stmt->execute()) {
        die(json_encode(generateErrorResponse("Assigning new task failed")));
      }
    }

	if ($ingredId != "") {
		
		 // Step 1: Fetch the row from tingredients
		$sql = "SELECT * FROM tingredients WHERE id = :id";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':id', $ingredId);
		$stmt->execute();
		$ingredient = $stmt->fetch();
 
		// Step 2: Fetch deviations
		$sql = 'SELECT d.deviation 
				FROM tdeviations d 
				INNER JOIN td2i ON td2i.idd = d.id AND td2i.idi = :idi 
				WHERE td2i.status < 2';

		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idi', $ingredId);
		$stmt->execute();

		$deviations = $stmt->fetchAll(PDO::FETCH_COLUMN);
		$tasks = implode("|", $deviations); // Concatenate deviations

		// Step 3: Insert the row into tingredients_log
		$sql = "INSERT INTO tingredients_log 
				(sub, name, tasks, supplier, statement, halalcert, cert, cb, halalexp, 
				rmposition, ingredients, rmcode, material, spec, quest, conf, note, 
				status, addoc, producer, idingredient, created_by)
				VALUES 
				(:sub, :name, :tasks, :supplier, :statement, :halalcert, :cert, :cb, :halalexp, 
				:rmposition, :ingredients, :rmcode, :material, :spec, :quest, :conf, :note, 
				:status, :addoc, :producer, :idingredient, :created_by)";

		$stmt = $dbo->prepare($sql);
		$stmt->execute([
			':sub' => $ingredient['sub'],
			':name' => $ingredient['name'],
			':tasks' => $tasks, // Newly generated
			':supplier' => $ingredient['supplier'],
			':statement' => $ingredient['statement'],
			':halalcert' => $ingredient['halalcert'],
			':cert' => $ingredient['cert'],
			':cb' => $ingredient['cb'],
			':halalexp' => $ingredient['halalexp'],
			':rmposition' => $ingredient['rmposition'],
			':ingredients' => $ingredient['ingredients'],
			':rmcode' => $ingredient['rmcode'],
			':material' => $ingredient['material'],
			':spec' => $ingredient['spec'],
			':quest' => $ingredient['quest'],
			':conf' => $ingredient['conf'],
			':note' => $ingredient['note'],
			':status' => $ingredient['status'],
			':addoc' => $ingredient['addoc'],
			':producer' => $ingredient['producer'],
			':idingredient' => $ingredient['id'], // Keeping reference to original ID
			':created_by' => $myuser->userdata['name'],
		]);

	}

    echo json_encode(generateSuccessResponse(array("id" => $dbo->lastInsertId())));
  } catch (PDOException $e) {
    echo json_encode(generateErrorResponse("Error: " . $e->getMessage()));
    die();
  }
}


function deleteTaskData($prod){
    try{
        $dbo = &$GLOBALS['dbo'];
		$idi = $prod['idi'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "DELETE FROM td2i WHERE idd=:idd AND idi=:idi"; // remove the task
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':idd', $prod['id']);
		$stmt->bindValue(':idi', $idi);
        $stmt->execute();

		$sql = "UPDATE tdeviations SET deleted=1 WHERE id=:id"; // remove the task
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $prod['id']);
        $stmt->execute();
        echo json_encode(generateSuccessResponse(array("id" => $prod['id'])));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function ingredientsCertificatesData($data){
    try{
		$ids = implode(',', $data['ids']);
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = 'select i.cert from tingredients i '.
            ' WHERE i.id in ('.$ids.') ORDER BY i.id';
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        //$stmt->bindValue(':idclient', $data['idclient']);
        //$stmt->bindValue(':deleted', $data['displaymode']);
        if(!$stmt->execute()) {
            echo json_encode(generateErrorResponse("Getting ingredients list failed"));
            die();
        }
		$zip = new ZipArchive();
		$certs = $stmt->fetchAll();
		$FilePath=random_string(21).".zip";
		if(file_exists($_SERVER['DOCUMENT_ROOT']."/temp/".$FilePath)) {
			unlink ($_SERVER['DOCUMENT_ROOT']."/temp/".$FilePath);
		}
		if ($zip->open($_SERVER['DOCUMENT_ROOT']."/temp/".$FilePath, ZIPARCHIVE::CREATE) != TRUE) {
				die ("Could not open archive");
		}
		foreach ($certs as $cert) {
			if ($json_arr = @json_decode("[".$cert['cert']."]")) {
				foreach ($json_arr as $json) {
					$filePath = $json->hostpath;
					$fileName = str_replace('files/clients/','',$json->hostUrl);
					$zip->addFile($filePath, $fileName);
				}
			}
		}
		$zip->close();
        echo json_encode(generateSuccessResponse(['url'=>'/temp/'.$FilePath, 'name' => $FilePath]));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function ingredientsSupplierQuestionsData($data){
    try{
		$ids = implode(',', $data['ids']);
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = 'select i.quest from tingredients i '.
            ' WHERE i.id in ('.$ids.') ORDER BY i.id';
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        //$stmt->bindValue(':idclient', $data['idclient']);
        //$stmt->bindValue(':deleted', $data['displaymode']);
        if(!$stmt->execute()) {
            echo json_encode(generateErrorResponse("Getting ingredients list failed"));
            die();
        }
		$zip = new ZipArchive();
		$quests = $stmt->fetchAll();
		$FilePath=random_string(21).".zip";
		if(file_exists($_SERVER['DOCUMENT_ROOT']."/temp/".$FilePath)) {
			unlink ($_SERVER['DOCUMENT_ROOT']."/temp/".$FilePath);
		}
		if ($zip->open($_SERVER['DOCUMENT_ROOT']."/temp/".$FilePath, ZIPARCHIVE::CREATE) != TRUE) {
				die ("Could not open archive");
		}
		foreach ($quests as $quest) {
			if ($json_arr = @json_decode("[".$quest['quest']."]")) {
				foreach ($json_arr as $json) {
					$filePath = $json->hostpath;
					$fileName = str_replace('files/clients/','',$json->hostUrl);
					$zip->addFile($filePath, $fileName);
				}
			}
		}
		$zip->close();
        echo json_encode(generateSuccessResponse(['url'=>'/temp/'.$FilePath, 'name' => $FilePath]));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function ingredientsExcelReportData($data){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = 'select DATE_FORMAT(di.created_at, \'%e %M %Y\') as time, '.
            'CONCAT("RMC_", i.id) as rmid, REPLACE(i.name, "<br/>", "\\n\\r") as name, '.
            'i.supplier, i.producer, GROUP_CONCAT(d.deviation) as deviation, GROUP_CONCAT(d.measure) as measure from tingredients i '.
            ' inner join td2i di on di.idi=i.id '.
            ' inner join tdeviations d on d.id=di.idd '.
            ' WHERE (i.idclient=:idclient1 OR i.idclient IN (SELECT id FROM tusers WHERE parent_id=:idclient2)) AND di.status < 2 GROUP BY i.id ORDER BY i.id';
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':idclient1', $data['idclient']);
		$stmt->bindValue(':idclient2', $data['idclient']);
        //$stmt->bindValue(':deleted', $data['displaymode']);
        if(!$stmt->execute()) {
            echo json_encode(generateErrorResponse("Getting ingredients list failed"));
            die();
        }
        echo json_encode(generateSuccessResponse(getIngredientsExcelReport($stmt->fetchAll())));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function ingredientsAllExcelReportData($data){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
			$sql = 'select DATE_FORMAT(di.created_at, \'%e %M %Y\') as time, '.
            'CONCAT("RMC_", i.id) as rmid, i.name, i.rmcode, i.halalcert, i.cb, i.conf, '.
            'i.supplier, i.producer, GROUP_CONCAT(d.deviation) as deviation, GROUP_CONCAT(d.measure) as measure from tingredients i '.
            ' left join td2i di on di.idi=i.id '.
            ' left join tdeviations d on d.id=di.idd '.
            ' WHERE (i.idclient=:idclient1 OR i.idclient IN (SELECT id FROM tusers WHERE parent_id=:idclient2)) AND i.deleted = 0 GROUP BY i.id ORDER BY i.id';
        $stmt = $dbo->prepare($sql); 
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':idclient1', $data['idclient']);
		$stmt->bindValue(':idclient2', $data['idclient']);
        //$stmt->bindValue(':deleted', $data['displaymode']);
        if(!$stmt->execute()) {
            echo json_encode(generateErrorResponse("Getting ingredients list failed"));
            die();
        }
        echo json_encode(generateSuccessResponse(getAllIngredientsExcelReport($stmt->fetchAll())));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function tasksAllExcelReportData($data){
    try{
        $dbo = &$GLOBALS['dbo'];
		$ids = implode(',', $data['ids']);
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = 'SELECT d.id, td2i.idi, if(td2i.idi is NULL, 0, 1) as flag, d.deviation, d.measure FROM tdeviations d '.
		' inner join td2i on td2i.idd=d.id AND td2i.status < 2 and td2i.idi IN ('.$ids.') ORDER BY td2i.idi, flag DESC';
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
       // $stmt->bindValue(':idi', $data['idingredient']);
        //$stmt->bindValue(':deleted', $data['displaymode']);
        if(!$stmt->execute()) {
            echo json_encode(generateErrorResponse("Getting tasks list failed"));
            die();
        }
		///////////////////

		$folder =  __DIR__."/../files/reports/";
		$result['name'] = 'tasks'.time().".csv";
		$result['path'] = 'tasks'.time().".csv";
		$result['url'] = "files/reports/".$result['path'];
		$result['path'] = $folder.$result['path'];

		$rows = [];
		while ($task = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$rows[] = $task;
		}
		/*
		// Open a file in write mode ('w')
		$fp = fopen($result['path'] , 'w');
		// Loop through file pointer and a line
		foreach ($csvdata as $fields) {
			fputcsv($fp, $fields);
		}
		fclose($fp);
		*/
		///////////////////

       // echo json_encode(generateSuccessResponse($result));
		echo json_encode(generateSuccessResponse(getTasksExcelReport($rows)));

    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

/** QM */
function tasksAllToolTipData($data){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = 'SELECT d.id, if(td2i.idi is NULL, 0, 1) as flag, d.deviation, d.measure FROM tdeviations d '.
		' inner join td2i on td2i.idd=d.id AND td2i.status < 2 and td2i.idi = :idi GROUP BY d.id ORDER BY flag DESC';
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':idi', $data['idingredient']);
        //$stmt->bindValue(':deleted', $data['displaymode']);
        if(!$stmt->execute()) {
            echo json_encode(generateErrorResponse("Getting tasks list failed"));
            die();
        }
		$data = '<table class="table table-bordered table-condensed" style="width:100%;">';
		$data .= '<thead><tr><th width="50%">Deviation</th><th width="50%">Measure</th></tr></thead><tbody>';
		while ($task = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$data .= '<tr><td>'.$task['deviation'].'</td><td>'.$task["measure"].'</td></tr>';
		}
		$data .= '</tbody></table>';
        echo json_encode(generateSuccessResponse($data));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function sendNextQMIdData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "INSERT INTO tqm (idclient) VALUES (:idclient)";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idclient', $prod['idclient']);
		if(!$stmt->execute()) die(json_encode(generateErrorResponse("Adding new QM failed")));
		echo json_encode(generateSuccessResponse(array("id" => $dbo->lastInsertId())));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function saveQMData($prod){
  try{
    $dbo = &$GLOBALS['dbo'];
    $sql = "UPDATE tqm SET dt=:dt, policy=:policy, haccp=:haccp, team=:team, purchasing=:purchasing, ".
      "cleaning=:cleaning, production=:production, flowchart=:flowchart, qcertificate=:qcertificate, ".
      "storage=:storage, audit=:audit, analysis=:analysis, addoc=:addoc, note=:note, training=:training, handling=:handling, traceability=:traceability WHERE id=:id";
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':dt', $prod['dt']);
    $stmt->bindValue(':policy', $prod['policy']);
    $stmt->bindValue(':haccp', $prod['haccp']);
    $stmt->bindValue(':team', $prod['team']);
    $stmt->bindValue(':purchasing', $prod['purchasing']);
    $stmt->bindValue(':cleaning', $prod['cleaning']);
    $stmt->bindValue(':production', $prod['production']);
    $stmt->bindValue(':storage', $prod['storage']);
    $stmt->bindValue(':audit', $prod['audit']);
    $stmt->bindValue(':analysis', $prod['analysis']);
    $stmt->bindValue(':addoc', $prod['addoc']);
    $stmt->bindValue(':note', $prod['note']);
    $stmt->bindValue(':id', $prod['id']);
    $stmt->bindValue(':training', $prod['training']);
    $stmt->bindValue(':handling', $prod['handling']);
    $stmt->bindValue(':traceability', $prod['traceability']);
    $stmt->bindValue(':flowchart', $prod['flowchart']);
    $stmt->bindValue(':qcertificate', $prod['qcertificate']);
    if (!$stmt->execute()){
      echo json_encode(generateErrorResponse('QM updating failed'));
      die();
    }
    echo json_encode(generateSuccessResponse('QM was updated'));
  } catch (PDOException $e){
    echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
    die();
  }
}

function saveQMDataOneCol($prod){
  try{
    $dbo = &$GLOBALS['dbo'];

    if ($prod['policy'] != false) {
      $colName = 'policy';
    }
    if ($prod['haccp'] != false) {
      $colName = 'haccp';
    }
    if ($prod['team'] != false) {
      $colName = 'team';
    }
    if ($prod['purchasing'] != false) {
      $colName = 'purchasing';
    }
    if ($prod['cleaning'] != false) {
      $colName = 'cleaning';
    }
    if ($prod['production'] != false) {
      $colName = 'production';
    }
    if ($prod['storage'] != false) {
      $colName = 'storage';
    }
    if ($prod['audit'] != false) {
      $colName = 'audit';
    }
    if ($prod['analysis'] != false) {
      $colName = 'analysis';
    }
    if ($prod['add'] != false) {
      $colName = 'addoc';
    }
    if ($prod['training'] != false) {
      $colName = 'training';
    }
    if ($prod['handling'] != false) {
      $colName = 'handling';
    }
    if ($prod['traceability'] != false) {
      $colName = 'traceability';
    }
    if ($prod['flowchart'] != false) {
      $colName = 'flowchart';
    }
    if ($prod['qcertificate'] != false) {
      $colName = 'qcertificate';
    }

    $currentValue = $dbo->query("SELECT $colName FROM tqm WHERE id = {$prod['id']}")->fetchColumn();

    if ($prod['policy'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['policy'] : $prod['policy'];
    }
    if ($prod['haccp'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['haccp'] : $prod['haccp'];
    }
    if ($prod['team'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['team'] : $prod['team'];
    }
    if ($prod['purchasing'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['purchasing'] : $prod['purchasing'];
    }
    if ($prod['cleaning'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['cleaning'] : $prod['cleaning'];
    }
    if ($prod['production'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['production'] : $prod['production'];
    }
    if ($prod['storage'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['storage'] : $prod['storage'];
    }
    if ($prod['audit'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['audit'] : $prod['audit'];
    }
    if ($prod['analysis'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['analysis'] : $prod['analysis'];
    }
    if ($prod['add'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['add'] : $prod['add'];
    }
    if ($prod['training'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['training'] : $prod['training'];
    }
    if ($prod['handling'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['handling'] : $prod['handling'];
    }
    if ($prod['traceability'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['traceability'] : $prod['traceability'];
    }
    if ($prod['flowchart'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['flowchart'] : $prod['flowchart'];
    }
    if ($prod['qcertificate'] != false) {
      $colVal = $currentValue ? $currentValue . ',' . $prod['qcertificate'] : $prod['qcertificate'];
    }

    $sql = "UPDATE tqm SET $colName=:colVal WHERE id=:id";
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':id', $prod['id']);
    $stmt->bindValue(':colVal', $colVal);

    if (!$stmt->execute()){
      echo json_encode(generateErrorResponse('QM updating failed'));
      die();
    }
    echo json_encode(generateSuccessResponse('QM was updated'));
  } catch (PDOException $e){
    echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
    die();
  }
}

function removeQMData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "DELETE FROM tqm WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('QM removing failed'));
			die();
		}
		echo json_encode(generateSuccessResponse('QM was removed'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function markDeletedQMData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "UPDATE tqm SET deleted = 1 WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('QM removing failed'));
			die();
		}
		echo json_encode(generateSuccessResponse('QM was removed'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

/** Audit */

function sendNextAuditIdData(){
	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "INSERT INTO taudit (auditnr) VALUES ('')";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		if(!$stmt->execute()) die(json_encode(generateErrorResponse("Adding new Audit failed")));
		echo json_encode(generateSuccessResponse(array("id" => $dbo->lastInsertId())));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function saveAuditData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "UPDATE taudit SET auditnr=:auditnr, auditorid=:auditorid, auditorname=:auditorname, auditeename=:auditeename, aorder=:order, ".
				"plan=:plan, report=:report, certificate=:certificate, gtc=:gtc WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':auditnr', $prod['auditnr']);
		$stmt->bindValue(':auditorid', $prod['auditorid']);
		$stmt->bindValue(':auditorname', $prod['auditorname']);
		$stmt->bindValue(':auditeename', $prod['auditeename']);
		$stmt->bindValue(':order', $prod['order']);
		$stmt->bindValue(':plan', $prod['plan']);
		$stmt->bindValue(':report', $prod['report']);
		$stmt->bindValue(':certificate', $prod['certificate']);
		$stmt->bindValue(':gtc', $prod['gtc']);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Audit record updating failed'));
			die();
		}
		echo json_encode(generateSuccessResponse('Audit record was updated'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function removeAuditData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "DELETE FROM taudit WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Audit record removing failed'));
			die();
		}
		echo json_encode(generateSuccessResponse('Audit record was removed'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}


/** Admin */

function sendNextAdminIdData() {
	try {
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "INSERT INTO tusers (name) VALUES ('')";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		if(!$stmt->execute()) die(json_encode(generateErrorResponse("Adding new client failed")));
		echo json_encode(generateSuccessResponse(array("id" => $dbo->lastInsertId())));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function saveAdminData($prod) { 

	try { 
		global $country_list; 
		
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$myuser = cuser::singleton();
		$myuser->getUserData();
		$prefix = $prod['prefix'];

		if ($prod['company_admin'] == '1' && $prod['company_id'] != "") {
			$sql = "UPDATE tusers SET company_admin=0 WHERE company_id=:company_id";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':company_id', $prod['company_id']);
			$stmt->execute();
		}

		if ($prod['company_id'] == "") {
			if ($company_id = $myuser->userdata['company_id']) {
				$prod['company_id'] = $company_id;
				$country_code = array_search($data['country'], $country_list);
				if ($prod['prefix'] == "") {
					$prod['prefix'] = "CID_".date('m')."/".date('y')."_".$country_code."_";
				}
				if ($prod['login'] == "") {
					$prod['login'] = $prod['email'];
				}
				if (!isset($prod['isclient']) || trim($prod['isclient']) == "" ) {
					$prod['isclient'] = "1";
				}
			}
		}

		// Before the update, get the current clients_audit value to compare
		$sql_current = "SELECT clients_audit FROM tusers WHERE id=:id";
		$stmt_current = $dbo->prepare($sql_current);
		$stmt_current->bindValue(':id', $prod['id']);
		$stmt_current->execute();
		$current_data = $stmt_current->fetch(PDO::FETCH_ASSOC);
		$current_clients_audit = $current_data['clients_audit'] ?? null;

		// Check if parent_id is empty and set it to NULL if so
		$parent_id = empty($prod['parent_id']) ? NULL : $prod['parent_id'];

		if (isset($prod['pass']) && !empty($prod['pass'])) {
			
			$sql = "UPDATE tusers SET company_id=:company_id, parent_id=:parent_id, name=:name, email=:email, prefix=:prefix, login=:login, pass=:pass, isclient=:isclient, " .
				"dashboard=:dashboard, application=:application, calendar=:calendar, products=:products, ingredients=:ingredients, documents=:documents, clients_audit=:clients_audit, sources_audit=:sources_audit, canadmin=:canadmin, prodnumber=:prodnumber, ingrednumber=:ingrednumber, " .
				"address=:address, city=:city, zip=:zip, country=:country, vat=:vat, industry=:industry, category=:category, contact_person=:contact_person, phone=:phone, company_admin=:company_admin, " .
				"pork_free_facility=:pork_free_facility, dedicated_halal_lines=:dedicated_halal_lines, export_regions=:export_regions, third_party_products=:third_party_products, third_party_halal_certified=:third_party_halal_certified " .
				"WHERE id=:id";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':pass', $prod['pass']);
		} else {
			$sql = "UPDATE tusers SET company_id=:company_id, parent_id=:parent_id, name=:name, email=:email, prefix=:prefix, login=:login, isclient=:isclient, " .
				"dashboard=:dashboard, application=:application, calendar=:calendar, products=:products, ingredients=:ingredients, documents=:documents, clients_audit=:clients_audit, sources_audit=:sources_audit, canadmin=:canadmin, prodnumber=:prodnumber, ingrednumber=:ingrednumber, " .
				"address=:address, city=:city, zip=:zip, country=:country, vat=:vat, industry=:industry, category=:category, contact_person=:contact_person, phone=:phone, company_admin=:company_admin, " .
				"pork_free_facility=:pork_free_facility, dedicated_halal_lines=:dedicated_halal_lines, export_regions=:export_regions, third_party_products=:third_party_products, third_party_halal_certified=:third_party_halal_certified " .
				"WHERE id=:id";
			$stmt = $dbo->prepare($sql);
		}

		$clients_audit = json_encode($prod['clients_audit']);
		$sources_audit = json_encode($prod['sources_audit']);
		$stmt->bindValue(':company_id', $prod['company_id']);
		$stmt->bindValue(':parent_id', $parent_id); // Use the checked value of parent_id (NULL if empty)
		$stmt->bindValue(':name', $prod['name']);
		$stmt->bindValue(':email', $prod['email']);
		$stmt->bindValue(':prefix', $prod['prefix']);
		$stmt->bindValue(':login', $prod['login']);
		$stmt->bindValue(':isclient', $prod['isclient']);
		$stmt->bindValue(':dashboard', $prod['dashboard']);
		$stmt->bindValue(':application', $prod['application']);
		$stmt->bindValue(':calendar', $prod['calendar']);
		$stmt->bindValue(':products', $prod['products']);
		$stmt->bindValue(':ingredients', $prod['ingredients']);
		$stmt->bindValue(':documents', $prod['documents']);
		$stmt->bindValue(':clients_audit', $clients_audit);
		$stmt->bindValue(':sources_audit', $sources_audit);
		$stmt->bindValue(':prodnumber', $prod['prodnumber']);
		$stmt->bindValue(':ingrednumber', $prod['ingrednumber']);
		$stmt->bindValue(':canadmin', $prod['canadmin']);
		$stmt->bindValue(':address', $prod['address']);
		$stmt->bindValue(':city', $prod['city']);
		$stmt->bindValue(':zip', $prod['zip']);
		$stmt->bindValue(':country', $prod['country']);
		$stmt->bindValue(':vat', $prod['vat']);
		$stmt->bindValue(':industry', $prod['industry']);
		$stmt->bindValue(':category', $prod['category']);
		$stmt->bindValue(':contact_person', $prod['contact_person']);
		$stmt->bindValue(':phone', $prod['phone']);
		$stmt->bindValue(':company_admin', $prod['company_admin']);
		$stmt->bindValue(':pork_free_facility', $prod['pork_free_facility']);
		$stmt->bindValue(':dedicated_halal_lines', $prod['dedicated_halal_lines']);
		$stmt->bindValue(':export_regions', $prod['export_regions']);
		$stmt->bindValue(':third_party_products', $prod['third_party_products']);
		$stmt->bindValue(':third_party_halal_certified', $prod['third_party_halal_certified']);
		$stmt->bindValue(':id', $prod['id']);		
		

		if ($prefix == "") {
			//sendEmailWithAttach
			$ownerEmailAddress = "halal.ezone@gmail.com";
			$fromEmailAddress = "noreply@halal-e.zone";			
			$body = [];
			$body['name'] = 'Halal e-Zone';
			$body['email'] =  $fromEmailAddress;
			$body['to'] = $adminEmailAddress;
			//$body['to'] = 'alrahmahsolutions@gmail.com';
	
			// sending notification
			$body['subject'] = "Halal e-Zone - New branch added by - ".$myuser->userdata['name'];
			$body['header'] = "";
			$body['body'] = 'Dear Admin,<br/>
			I am writing to inform you that a new branch has been added by '.$myuser->userdata['name'].' on our platform. The details are as follows:<br/><br/>
				Name: '.$prod['name'].'<br/>
				Email: '.$prod['email'].'<br/>
				Address: '. $prod['address'].'<br/>
				City: '. $prod['city'].'<br/>
				Zip Code: '. $prod['zip'].'<br/>
				Country: '. $prod['country'].'<br/>
				Industry: '. $prod['industry'].'<br/>
				Category: '. $prod['category'].'<br/>
				Number of products to be certified(estimated): '. $prod['prodnumber'].'<br/>
				Number of raw materials(estimated): '. $prod['ingrednumber'].'<br/>
				Vat: '. $prod['vat'].'<br/>
				Contact Person: '. $prod['contact_person'].'<br/>
				Phone: '. $prod['phone'].'<br/>
				Date of Registration: '.date('d/m/Y');
			$body['body'] .= "<br /><br />";
			$body['body'] .= 'Best regards,<br />
			Halal e-Zone';
			sendEmail($body);
		}

		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Client record updating failed'));
			die();
		}

		if (isset($prod['CertificateExpiryDate']) && !empty($prod['CertificateExpiryDate'])) {
			// Convert d/m/Y format to Y-m-d for MySQL
			$date = DateTime::createFromFormat('d/m/Y', $prod['CertificateExpiryDate']);
			
			if ($date) { // Ensure the date is valid
				$formattedDate = $date->format('Y-m-d'); // Convert to MySQL format
				
				$updateSQL = "UPDATE tapplications app
					LEFT JOIN tcycles cyc ON app.idcycle = cyc.id
					LEFT JOIN tusers u ON cyc.idclient = u.id
					SET app.CertificateExpiryDate = ?
					WHERE u.id = ? AND cyc.state = 1";
		
				$stmt = $dbo->prepare($updateSQL);
				$stmt->bindParam(1, $formattedDate, PDO::PARAM_STR);
				$stmt->bindParam(2, $prod['id'], PDO::PARAM_STR);
				
				$stmt->execute();				 
				
			}  
		}

		// Prepare the new clients_audit value
		$new_clients_audit = json_encode($prod['clients_audit']);
		
		// After the successful update, check if we need to send email
		if ($prod['isclient'] == '2' && $current_clients_audit != $new_clients_audit && !empty($prod['clients_audit'])) {
			// Get client names
			$ids = $prod['clients_audit'];
			if (!empty($ids)) {
				$id_list = implode(',', $ids);
				$sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND deleted = 0 AND id IN ($id_list) ORDER BY name";
				$stmt = $dbo->query($sql);
				$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
				
				// Prepare client list for email
				$client_list = '<ul>';
				foreach ($clients as $client) {
					$client_name =  $client["name"] . ' - ' . $client["prefix"] . $client["id"];
					$client_list .= "<li>$client_name</li>";
				}
				$client_list .= '</ul>';
				
				// Send email with client list
				$fromEmailAddress = "noreply@halal-e.zone";            
				$body = [
					'name' => 'Halal e-Zone',
					'email' => $fromEmailAddress,
					'to' => $prod['email'],
					'subject' => "Halal e-Zone - New Client Audit Assignments",
					'header' => "",
					'body' => 'Dear Auditor,<br/><br/>
					You have been assigned the following clients to audit:<br/><br/>
					'.$client_list.'<br/>
					Please log in to your account to view details and begin your audit process.<br/><br/>
					<strong>Halal e-Zone Team</strong>'
				];
				
				sendEmail($body);
			}
		}

        //addCyclesForClient(array("idclient" => $prod["id"]));
		echo json_encode(generateSuccessResponse('Client record was updated'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function saveFacilityData($prod) { 

	try { 
		global $country_list, $adminEmailAddress, $supportEmailAddress;
		
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$myuser = cuser::singleton();
		$myuser->getUserData();
		$prefix = $prod['prefix'];

		$parent_id = $myuser->userdata['id'];

		$country_code = array_search($data['country'], $country_list);
		
		$prod['prefix'] = "CID_".date('m')."/".date('y')."_".$country_code."_";
		
		$prod['isclient'] = "1";
		$prod['canadmin'] = "0";
		
		if (empty($prod['id'])) {
			// Insert new record if $prod['id'] is empty
			$sql = "INSERT INTO tusers (parent_id, name, email, prefix, isclient, canadmin, prodnumber, ingrednumber, 
					address, city, zip, country, vat, industry, category, contact_person, phone, 
					pork_free_facility, dedicated_halal_lines, export_regions, third_party_products, third_party_halal_certified) 
					VALUES (:parent_id, :name, :email, :prefix, :isclient, :canadmin, :prodnumber, :ingrednumber, 
					:address, :city, :zip, :country, :vat, :industry, :category, :contact_person, :phone, 
					:pork_free_facility, :dedicated_halal_lines, :export_regions, :third_party_products, :third_party_halal_certified)";
		} else {
			// Update existing record if $prod['id'] is not empty
			// Additional condition: parent_id must also match
			$sql = "UPDATE tusers SET name=:name, email=:email, prefix=:prefix, isclient=:isclient, 
					canadmin=:canadmin, prodnumber=:prodnumber, ingrednumber=:ingrednumber, 
					address=:address, city=:city, zip=:zip, country=:country, vat=:vat, industry=:industry, category=:category, 
					contact_person=:contact_person, phone=:phone, 
					pork_free_facility=:pork_free_facility, dedicated_halal_lines=:dedicated_halal_lines, 
					export_regions=:export_regions, third_party_products=:third_party_products, third_party_halal_certified=:third_party_halal_certified 
					WHERE id=:id AND parent_id=:parent_id";
		}
		
		$stmt = $dbo->prepare($sql);
		
		// Bind the parameters for both INSERT and UPDATE
		$stmt->bindValue(':parent_id', $parent_id);
		$stmt->bindValue(':name', $prod['name']);
		$stmt->bindValue(':email', $prod['email']);
		$stmt->bindValue(':prefix', $prod['prefix']);
		$stmt->bindValue(':isclient', $prod['isclient']);
		$stmt->bindValue(':prodnumber', $prod['prodnumber']);
		$stmt->bindValue(':ingrednumber', $prod['ingrednumber']);
		$stmt->bindValue(':canadmin', $prod['canadmin']);
		$stmt->bindValue(':address', $prod['address']);
		$stmt->bindValue(':city', $prod['city']);
		$stmt->bindValue(':zip', $prod['zip']);
		$stmt->bindValue(':country', $prod['country']);
		$stmt->bindValue(':vat', $prod['vat']);
		$stmt->bindValue(':industry', $prod['industry']);
		$stmt->bindValue(':category', $prod['category']);
		$stmt->bindValue(':contact_person', $prod['contact_person']);
		$stmt->bindValue(':phone', $prod['phone']);
		$stmt->bindValue(':pork_free_facility', $prod['pork_free_facility']);
		$stmt->bindValue(':dedicated_halal_lines', $prod['dedicated_halal_lines']);
		$stmt->bindValue(':export_regions', $prod['export_regions']);
		$stmt->bindValue(':third_party_products', $prod['third_party_products']);
		$stmt->bindValue(':third_party_halal_certified', $prod['third_party_halal_certified']);
		
		if (!empty($prod['id'])) {
			// Only bind id if updating
			$stmt->bindValue(':id', $prod['id']);
		}
		
		// Execute the query
		$stmt->execute();
		

		if (empty($prod['id']))  {
			//sendEmailWithAttach

			$idclient = $dbo->lastInsertId();

		$sql = "INSERT INTO tcycles (idclient, name, `state`) " .
		" VALUES (:idclient, :name, 1)";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->bindValue(':name', date('Y'));
		//$stmt->bindValue(':startDate', $startDt);
		//$stmt->bindValue(':endDate', $endDt);
		$stmt->execute();
		$idcycle = $dbo->lastInsertId();

		$query = "INSERT INTO tapplications (idclient, idcycle, prodnumber, ingrednumber, state) VALUES (:idclient, :idcycle, :prodnumber, :ingrednumber, 'offer')";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
		$stmt->bindParam(':idcycle', $idcycle, PDO::PARAM_STR);
		$stmt->bindValue(':prodnumber', $prod['prodnumber']);
		$stmt->bindValue(':ingrednumber', $prod['ingrednumber']);
		$stmt->execute();
		$idapp = $dbo->lastInsertId();

		insertActivityLog($idclient, $idapp, $idclient, $prod['name'], 'New Facility');		

			$ownerEmailAddress = "halal.ezone@gmail.com";
			$fromEmailAddress = "noreply@halal-e.zone";			
			$body = [];
			$body['name'] = 'Halal e-Zone';
			$body['email'] =  $fromEmailAddress;
			$body['to'] = $supportEmailAddress;
			//$body['to'] = 'alrahmahsolutions@gmail.com';
	
			// sending notification
			$body['subject'] = "Halal e-Zone - New facility added by - ".$myuser->userdata['name'];
			$body['header'] = ""; 
			$body['body'] = 'Dear Admin,<br/>
			A new Facility has been added by '.$myuser->userdata['name'].' on the Halal e-Zone platform. The details are as follows:<br/><br/>
			Name: '.$data['name'].'<br/>
			Email: '.$data['email'].'<br/>
			Address: '. $data['address'].'<br/>
			City: '. $data['city'].'<br/>
			Zip Code: '. $data['zip'].'<br/>
			Country: '. $data['country'].'<br/>
			Industry: '. $data['industry'].'<br/>
			Category: '. $data['category'].'<br/>
			Number of products to be certified(estimated): '. $data['prodnumber'].'<br/>
			Number of raw materials(estimated): '. $data['ingrednumber'].'<br/>
			Vat: '. $data['vat'].'<br/>
			Contact Person: '. $data['contact_person'].'<br/>
			Phone: '. $data['phone'].'<br/>
			Is your facility a pork-free facility? '.$data['pork_free_facility'].'<br/>
			Do you have dedicated lines for Halal production? '.$data['dedicated_halal_lines'].'<br/>
			What are your target export regions? '.$data['export_regions'].'<br/>
			Are the products to be Halal certified, produced by a third party? '.$data['third_party_products'].'<br/>
			Is this third party Halal certified? '.$data['third_party_halal_certified'].'<br/>		
			Date of Registration: '.date('d/m/Y');

			$body['body'] .= "<br /><br />";
			$body['body'] .= 'Best regards,<br />
			Halal e-Zone';
			sendEmail($body);
		}

		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Client record updating failed'));
			die();
		}

        //addCyclesForClient(array("idclient" => $prod["id"]));
		echo json_encode(generateSuccessResponse('Client record was updated'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function sendNextCompanyIdData() {
	try {
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "INSERT INTO tcompanies (name) VALUES ('')";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		if(!$stmt->execute()) die(json_encode(generateErrorResponse("Adding new company failed")));
		echo json_encode(generateSuccessResponse(array("id" => $dbo->lastInsertId())));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function saveCompanyData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "UPDATE tcompanies SET name=:name, active=:active WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':name', $prod['name']);
		$stmt->bindValue(':active', $prod['active']);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Company record updating failed'));
			die();
		}
        //addCyclesForClient(array("idclient" => $prod["id"]));
		echo json_encode(generateSuccessResponse('Company record was updated'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

/*
function getIngredientLog($id) {

	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

		$sql = 'SELECT i.id, i.name, i.rmcode, i.material, GROUP_CONCAT("{\"id\":\"",s.id,"\",\"name\":\"RMC_",s.id,"/", REPLACE(s.name,\'"\',\'\\\"\'),"\",", "\"conf\":\"",s.conf, "\", \"status\":\"",getHalalExpStatus(s.halalexp), "\"}") as ingred, '.
		'i.supplier,i.producer,  i.statement, i.halalcert, i.cert, i.cb, i.halalexp, i.rmposition, i.spec, i.quest, i.note, i.addoc, DATE_FORMAT(i.created_at, "%d/%m/%Y %H:%i") as created_at_formated, i.created_at, '.
		'IF( IF(s.conf is not NULL, (count(s.id)-SUM(s.conf)), 0) = 0 AND i.conf=1, 1, 0) as conf1, '.
		'GREATEST(MAX(getHalalExpStatus(s.halalexp)), getHalalExpStatus(i.halalexp)) as status, i.deleted, i.sub, '.
		'if((select count(id) as cc from td2i where td2i.status < 2 and idi=i.id) > 0, 1, 0) as tasks, '.
		'(select count(id) as cc from td2i where td2i.status < 2 and idi=i.id) as tasksnumber from tingredients i '.
		'left join ti2i on (ti2i.idi1=i.id) '.
		'left join tingredients s on (s.id=ti2i.idi2) '
	.$filter.' GROUP BY i.id '.($conf!='' ? ' HAVING conf1 = '.$conf: '').' ORDER BY '.strtolower(str_replace(' ', '', $sortingField)).' '.$sortingOrder.' LIMIT '.$firstRowIndex.', '.$rowsPerPage;

		$stmt = $dbo->prepare($query);
		$stmt->bindParam(1, $_POST['id'], PDO::PARAM_STR);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Client record not found'));
			die();
		}
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row['clients_audit'] != "") {
			$row['clients_audit'] = json_decode($row['clients_audit']);
		}
		if ($row['sources_audit'] != "") {
			$row['sources_audit'] = json_decode($row['sources_audit']);
		}
		echo json_encode(generateSuccessResponse($row));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}
*/

function getAdminData($id){
	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

		$query = "SELECT * FROM tusers AS t WHERE id = ?";

		$stmt = $dbo->prepare($query);
		$stmt->bindParam(1, $_POST['id'], PDO::PARAM_STR);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Client record not found'));
			die();
		}
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row['clients_audit'] != "") {
			$row['clients_audit'] = json_decode($row['clients_audit']);
		}
		if ($row['sources_audit'] != "") {
			$row['sources_audit'] = json_decode($row['sources_audit']);
		}

		// Fetch CertificateExpiryDate from tapplications
		$sql = "SELECT date_format(app.CertificateExpiryDate , '%d/%m/%Y') as CertificateExpiryDate  
		FROM tusers u
		LEFT JOIN tcycles cyc ON cyc.idclient = u.id AND cyc.state = 1
		LEFT JOIN tapplications app ON app.idcycle = cyc.id
		WHERE u.id = ?";

		$stmt = $dbo->prepare($sql);
		$stmt->bindParam(1, $_POST['id'], PDO::PARAM_STR);
		$stmt->execute();

		$certificateData = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($certificateData) {
			$row['CertificateExpiryDate'] = $certificateData['CertificateExpiryDate'];
		}

		echo json_encode(generateSuccessResponse($row));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function getFacilityData($id){ 
	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$myuser = cuser::singleton();
	   $myuser->getUserData();
	   $parent_id = $myuser->userdata['id'];
   
	   $query = "SELECT * FROM tusers WHERE id = ? AND parent_id = ?";
	   $stmt = $dbo->prepare($query);
	   $stmt->bindParam(1, $_POST['id'], PDO::PARAM_INT); 
	   $stmt->bindParam(2, $parent_id, PDO::PARAM_INT);  
			   
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Facility record not found'));
			die();
		}
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		 
		echo json_encode(generateSuccessResponse($row));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function getCompanyData($id){
	try{
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

		$query = "SELECT * FROM tcompanies AS t WHERE id = ?";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(1, $_POST['id'], PDO::PARAM_STR);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Client record not found'));
			die();
		}
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		echo json_encode(generateSuccessResponse($row));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function removeAdminData($prod){
	try{
		$dbo = &$GLOBALS['dbo'];
		$sql = "UPDATE tusers SET deleted=1 WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':id', $prod['id']);
		if (!$stmt->execute()){
			echo json_encode(generateErrorResponse('Client record deleting failed'));
			die();
		}
		echo json_encode(generateSuccessResponse('Client record was removed'));
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}

function changeIsClientData($data){
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tusers SET isclient = isclient XOR 1 WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $data['id']);
        if (!$stmt->execute()){
            echo json_encode(generateErrorResponse('Record updating failed'));
            die();
        }
        echo json_encode(generateSuccessResponse('Record conformity updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function changeApplicationData($data){
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tusers SET application = application XOR 1 WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $data['id']);
        if (!$stmt->execute()){
            echo json_encode(generateErrorResponse('Record updating failed'));
            die();
        }
        echo json_encode(generateSuccessResponse('Record conformity updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function changeClientsData($data){
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tusers SET clients = clients XOR 1 WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $data['id']);
        if (!$stmt->execute()){
            echo json_encode(generateErrorResponse('Record updating failed'));
            die();
        }
        echo json_encode(generateSuccessResponse('Record conformity updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function changeAuditData($data){
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tusers SET audit = audit XOR 1 WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $data['id']);
        if (!$stmt->execute()){
            echo json_encode(generateErrorResponse('Record updating failed'));
            die();
        }
        echo json_encode(generateSuccessResponse('Record conformity updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function changeCanAdminData($data){
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tusers SET canadmin = canadmin XOR 1 WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $data['id']);
        if (!$stmt->execute()){
            echo json_encode(generateErrorResponse('Record updating failed'));
            die();
        }
        echo json_encode(generateSuccessResponse('Record conformity updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function unblockUserData($data){
	try{
			$dbo = &$GLOBALS['dbo'];
			$sql = "DELETE from attempts WHERE iduser=:id";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':id', $data['id']);
			if (!$stmt->execute()){
					echo json_encode(generateErrorResponse('Unblocking user failed'));
					die();
			}
			echo json_encode(generateSuccessResponse('User account unblocked'));
	} catch (PDOException $e){
			echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
			die();
	}
}

function addCyclesForClient($prod){
	try{
			$dbo = &$GLOBALS['dbo'];
			$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
			$sql = "SELECT COUNT(id) as count FROM tcycles WHERE idclient=:idclient";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':idclient', $prod['idclient']);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			if(!$stmt->execute()) return false;
			if($stmt->fetch()['count'] == 0)
					return addCycles($prod['idclient']);
			return true;
	} catch (PDOException $e){
			echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
			die();
	}
}

/*  Applications */

function addSubCycles($cycle){
	try{
			$dbo = &$GLOBALS['dbo'];
			$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
			$sql = "INSERT INTO tapplications (idcycle, name, state) VALUES (:idcycle, :name, :state)";
			$stmt = $dbo->prepare($sql);
			$stmt->bindValue(':idcycle', $cycle['idcycle']);
			$stmt->bindValue(':name', "Initial application");
			$stmt->bindValue(':state', $cycle['state']);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			if(!$stmt->execute()) return false;
			$stmt->bindValue(':name', "Surveillance 1");
			$stmt->bindValue(':state', 0);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			if(!$stmt->execute()) return false;
			$stmt->bindValue(':name', "Surveillance 2");
			$stmt->bindValue(':state', 0);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			if(!$stmt->execute()) return false;
			return true;
	} catch (PDOException $e){
			echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
			die();
	}
}

function addCycles($idclient){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "INSERT INTO tcycles (idclient, name, state) VALUES (:idclient, :name, :state)";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':idclient', $idclient);
        $stmt->bindValue(':name', "Certification cycle 1");
        $stmt->bindValue(':state', 1);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if(!$stmt->execute()) return false;
        if(!addSubCycles(array("idcycle"=>$dbo->lastInsertId(), "state"=>1))) return false;
        $stmt->bindValue(':name', "Certification cycle 2");
        $stmt->bindValue(':state', 0);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if(!$stmt->execute()) return false;
        if(!addSubCycles(array("idcycle"=>$dbo->lastInsertId(), "state"=>0))) return false;
        $stmt->bindValue(':name', "Certification cycle 3");
        $stmt->bindValue(':state', 0);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if(!$stmt->execute()) return false;
        if(!addSubCycles(array("idcycle"=>$dbo->lastInsertId(), "state"=>0))) return false;
        $stmt->bindValue(':name', "Certification cycle 4");
        $stmt->bindValue(':state', 0);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if(!$stmt->execute()) return false;
        if(!addSubCycles(array("idcycle"=>$dbo->lastInsertId(), "state"=>0))) return false;
        $stmt->bindValue(':name', "Certification cycle 5");
        $stmt->bindValue(':state', 0);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if(!$stmt->execute()) return false;
        if(!addSubCycles(array("idcycle"=>$dbo->lastInsertId(), "state"=>0))) return false;
        $stmt->bindValue(':name', "Certification cycle 6");
        $stmt->bindValue(':state', 0);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if(!$stmt->execute()) return false;
        if(!addSubCycles(array("idcycle"=>$dbo->lastInsertId(), "state"=>0))) return false;
        $stmt->bindValue(':name', "Certification cycle 7");
        $stmt->bindValue(':state', 0);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if(!$stmt->execute()) return false;
        if(!addSubCycles(array("idcycle"=>$dbo->lastInsertId(), "state"=>0))) return false;
        $stmt->bindValue(':name', "Certification cycle 8");
        $stmt->bindValue(':state', 0);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if(!$stmt->execute()) return false;
        if(!addSubCycles(array("idcycle"=>$dbo->lastInsertId(), "state"=>0))) return false;
        $stmt->bindValue(':name', "Certification cycle 9");
        $stmt->bindValue(':state', 0);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if(!$stmt->execute()) return false;
        if(!addSubCycles(array("idcycle"=>$dbo->lastInsertId(), "state"=>0))) return false;
        $stmt->bindValue(':name', "Certification cycle 10");
        $stmt->bindValue(':state', 0);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        if(!$stmt->execute()) return false;
        return addSubCycles(array("idcycle"=>$dbo->lastInsertId(), "state"=>0));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function activateNextCycle($prod){
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tapplications SET state=1 WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $prod['id']+1);
        return $stmt->execute();
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function saveApplicationData($prod){
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tapplications SET app=:app, offer=:offer, soffer=:soffer, halaltraining=:halaltraining, ".
            "plan=:plan, checklist=:checklist, report=:report, action=:action, list=:list, payment=:payment, ".
            "cert=:cert, newapp=:newapp, newcert=:newcert, state=:state, auditorname=:auditorname, ".
            " startdt=:startdt, enddt=:enddt WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $prod['id']);
        $stmt->bindValue(':app', $prod['app']);
        if (isset($prod['auditorname']))
            $stmt->bindValue(':auditorname', $prod['auditorname']);
        else
            $stmt->bindValue(':auditorname', null, PDO::PARAM_STR);
        $stmt->bindValue(':offer', $prod['offer']);
        $stmt->bindValue(':soffer', $prod['soffer']);
        $stmt->bindValue(':plan', $prod['plan']);
        $stmt->bindValue(':checklist', $prod['checklist']);
        $stmt->bindValue(':report', $prod['report']);
        $stmt->bindValue(':action', $prod['action']);
        $stmt->bindValue(':list', $prod['list']);
        $stmt->bindValue(':payment', $prod['payment']);
        $stmt->bindValue(':cert', $prod['cert']);
        $stmt->bindValue(':newapp', $prod['newapp']);
        $stmt->bindValue(':newcert', $prod['newcert']);
        $stmt->bindValue(':halaltraining', $prod['halaltraining']);
        $stmt->bindValue(':state', $prod['state']);
        if (isset($prod['issuedate']) && !empty($prod['issuedate'])) {
            $dt = new DateTime( $prod['issuedate'] );
            $stmt->bindValue(':startdt', $dt->format('Y-m-d'));
            $dt->add(new DateInterval('P1Y'));
            $dt->sub(new DateInterval('P1D'));
            $stmt->bindValue(':enddt', $dt->format('Y-m-d'));
        } else {
            $stmt->bindValue(':startdt', null, PDO::PARAM_STR);
            $stmt->bindValue(':enddt', null, PDO::PARAM_STR);
        }
        if (!$stmt->execute()){
            echo json_encode(generateErrorResponse('Application updating failed'));
            die();
        }
        // if completed, set the next cycle active
        if($prod['state'] == 0) {
            activateNextCycle($prod);
        }
        echo json_encode(generateSuccessResponse('Application was updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function updateApplicationField($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE tapplications SET ".$app['name']."=:app WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        $stmt->bindValue(':app', $app['value']);
        return $stmt->execute();
    } catch (PDOException $e){
        return false;
    }
}

function invalidateApplicationFieldFiles($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        // get files if any
        $sql = 'select '.$app["name"]. ' from tapplications where id=:id';
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':id', $app['id']);
        if(!$stmt->execute()) {
            return false;
        }
        $res = json_decode( "[".$stmt->fetch()[ $app["name"] ]."]" );
        foreach($res as $key => &$value ) {
            $value->invalid = "1";
        }
        $res = json_encode($res);
        $res = str_replace( array("[", "]"), "", $res);
        $sql = "UPDATE tapplications SET ".$app['name']."=:app WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        $stmt->bindValue(':app', $res);
        return $stmt->execute();
    } catch (PDOException $e){
        return false;
    }
}

function updateCycleDate($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE tcycles SET startdt=:startdt WHERE id=(select idcycle from tapplications WHERE id=:id)";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        $stmt->bindValue(':startdt', date('Y-m-d'));
        return $stmt->execute();
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function updateSubCycleDate($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE tapplications SET startdt=:startdt, enddt=:enddt WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        $stmt->bindValue(':startdt', date('Y-m-d'));
        $stmt->bindValue(':enddt', (date('Y')+1).date('-m-d'));
        if(!$stmt->execute()) return false;
        $sql = "UPDATE tcycles SET enddt=:enddt WHERE id=(select idcycle from tapplications WHERE id=:id)";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        $stmt->bindValue(':enddt', (date('Y')+3).date('-m-d'));
        return $stmt->execute();
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function completeApplicationData($app){
    sendCompleteApplicationNotification($app);
    /*
    if($app['name'] == 'certstate'){
        if (!updateSubCycleDate($app)) {
            echo json_encode(generateErrorResponse('Cycle date updating failed'));
            die();
        }
    }
    */
    $app['value'] = 2;
    if (!updateApplicationField($app)) {
        echo json_encode(generateErrorResponse('Application completing failed'));
        die();
    }
    if (!empty($app['nextname'])) {
        $app['name'] = $app['nextname'];
        $app['value'] = 1;
        if (!updateApplicationField($app)) {
            echo json_encode(generateErrorResponse('Application updating failed'));
            die();
        }
    }
    echo json_encode(generateSuccessResponse('Application was updated'));
}

function confirmApplicationData($app){
    sendConfirmApplicationNotification($app);
    /*
    if($app['name'] == 'appstate'){
        if (!updateCycleDate($app)) {
            echo json_encode(generateErrorResponse('Cycle date updating failed'));
            die();
        }
    }
    */
    $app['value'] = 3;
    if(!updateApplicationField($app)){
        echo json_encode(generateErrorResponse('Application updating failed'));
        die();
    }
    $app['name'] = $app['nextname'];
    $app['value'] = 1;
    if(!updateApplicationField($app)) {
       echo json_encode(generateErrorResponse('Application updating failed'));
       die();
    }
    echo json_encode(generateSuccessResponse('Application was updated'));
}

function cancelApplicationData($app){
    //sendConfirmApplicationNotification($app);
    if ( $app["name"] == "report" )
        $res = cancelApplicationFromAuditReport($app);
    elseif ( $app["name"] == "checklist" )
            $res = cancelApplicationFromCheckList($app);
        elseif ( $app["name"] == "plan" )
                $res = cancelApplicationFromAuditPlan($app);
            else
                $res = cancelApplicationFromOffer($app);
    if ( $res )
        echo json_encode(generateSuccessResponse('Application was cancelled'));
    else
        echo json_encode(generateErrorResponse('Application cancelling failed'));
}

function cancelApplicationFromAuditReport($app) {
    // cancel all the step from this
    $app['name'] = 'report';
    $app['value'] = 1;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'reportstate';
    if ( !updateApplicationField($app) )
        return false;
    $app['name'] = 'action';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'actionstate';
    if ( !updateApplicationField($app) )
        return false;
    $app['name'] = 'list';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'liststate';
    if ( !updateApplicationField($app) )
        return false;
    $app['name'] = 'payment';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'paymentstate';
    if ( !updateApplicationField($app) )
        return false;
    return true;
}

function cancelApplicationFromCheckList($app) {
    $app['name'] = 'checklist';
    $app['value'] = 1;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'checkliststate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'report';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'reportstate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'action';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'actionstate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'list';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'liststate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'payment';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'paymentstate';
    if ( !updateApplicationField($app) )
        return false;
    return true;
}

function cancelApplicationFromAuditPlan($app) {
    $app['name'] = 'plan';
    $app['value'] = 1;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'planstate';
    if ( !updateApplicationField($app) )
        return false;

    $app['value'] = 0;
    $app['name'] = 'auditornamestate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'checklist';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'checkliststate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'report';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'reportstate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'action';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'actionstate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'list';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'liststate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'payment';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'paymentstate';
    if ( !updateApplicationField($app) )
        return false;
    return true;
}

function cancelApplicationFromOffer($app) {
    $app['name'] = 'offer';
    $app['value'] = 1;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'offerstate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'soffer';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'sofferstate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'plan';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'planstate';
    if ( !updateApplicationField($app) )
        return false;

    $app['value'] = 0;
    $app['name'] = 'auditornamestate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'checklist';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'checkliststate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'report';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'reportstate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'action';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'actionstate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'list';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'liststate';
    if ( !updateApplicationField($app) )
        return false;

    $app['name'] = 'payment';
    $app['value'] = 0;
    if ( !invalidateApplicationFieldFiles($app) )
        return false;
    $app['name'] = 'paymentstate';
    if ( !updateApplicationField($app) )
        return false;
    return true;
}

function getClientDataByApplication($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "select u.name, u.email FROM tapplications a ".
            " inner join tcycles c on a.idcycle = c.id ".
            " inner join tusers u on u.id = c.idclient ".
            " WHERE a.id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':id', $app['id']);
        if(!$stmt->execute()) return false;
        return $stmt->fetch();
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function sendCompleteApplicationNotification($app){
    $clientData = getClientDataByApplication($app);
    $body['subject'] = 'New document notification';
    switch($app['name']){
        case 'offerstate': $body['header'] = 'New Offer has been uploaded to '.$app['cycle']; break;
        case 'planstate': $body['header'] = 'New Audit plan has been uploaded to '.$app['cycle']; break;
        case 'checkliststate': $body['header'] = 'New Check List has been uploaded to '.$app['cycle']; break;
        case 'reportstate': $body['header'] = 'New Audit report has been uploaded to '.$app['cycle']; break;
        default : $body['header'] = 'New Certificate has been uploaded to '.$app['cycle']; break;
    }
    $body['body'] = '';
    return sendEmailCycleNotificationToClient($clientData, $body);
}

function sendConfirmApplicationNotification($app){
    $clientData = getClientDataByApplication($app);
    $body['subject'] = 'New document confirmation notification';
    if($app['isclient'] == 0){
        switch($app['name']){
            case 'sofferstate': $body['header'] = 'New Signed offer has been confirmed in '.$app['cycle']; break;
            case 'actionstate': $body['header'] = 'New Corrective actions plan offer has been confirmed in '.$app['cycle']; break;
            case 'liststate': $body['header'] = 'New List of products has been confirmed in '.$app['cycle']; break;
            default : $body['header'] = 'New Application has been confirmed in '.$app['cycle']; break;
        }
        $body['body'] = '';
        return sendEmailCycleNotificationToClient($clientData, $body);
    }else{
        switch($app['name']){
            case 'planstate': $body['header'] = 'New Audit plan has been confirmed by '.$clientData['name'].' in '.$app['cycle']; break;
            case 'reportstate': $body['header'] = 'New Audit report has been confirmed by '.$clientData['name'].' in '.$app['cycle']; break;
            default : $body['header'] = 'New Certificate has been confirmed by '.$clientData['name'].' in '.$app['cycle']; break;
        }
        $body['body'] = '';
        return sendEmail($body);
    }
}

function stopApplicationNotificationData($app){
    $app['name'] = 'notifystatus';
    $app['value'] = 3;
    if (!updateApplicationField($app)) {
       echo json_encode(generateErrorResponse('Application completing failed'));
       die();
    }
    echo json_encode(generateSuccessResponse('Application was updated'));
}

function skipApplicationData($app){
    $app['name'] = 'state';
    $app['value'] = 0;
    if (!updateApplicationField($app)) {
       echo json_encode(generateErrorResponse('Application completing failed'));
       die();
    }
    activateNextCycle($app);
    echo json_encode(generateSuccessResponse('Application was updated'));
}

/*  Dashboard Data */
function getClientStatistics($data){
    try{
        $dbo = &$GLOBALS['dbo']; 

       // all clients ingredients (excluding Cleaning agents and Packaging Material)
		$sql = "SELECT COUNT(id) AS count FROM tingredients 
				WHERE deleted = 0 
				AND idclient = :idclient 
				AND material NOT IN ('Cleaning agents', 'Packaging Material')";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idclient', $data['id']);
		if (!$stmt->execute()) return false;
		$result['ingredPublished'] = $stmt->fetch()['count'] * 1;

		// all client confirmed ingredients (excluding Cleaning agents and Packaging Material)
		$sql = "SELECT COUNT(id) AS count FROM tingredients 
				WHERE deleted = 0 
				AND idclient = :idclient 
				AND conf = 1 
				AND material NOT IN ('Cleaning agents', 'Packaging Material')";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':idclient', $data['id']);
		if (!$stmt->execute()) return false;
		$result['ingredConfirmed'] = $stmt->fetch()['count'] * 1;

        // all client allowed
        $sql = "select ingrednumber from tusers WHERE id=:idclient";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':idclient', $data['id']);
        if(!$stmt->execute()) return false;
        $result['ingredNumber'] = $stmt->fetch()['ingrednumber']*1;

        // all clients products
        $sql = "select count(id) count from tproducts WHERE deleted=0 AND idclient=:idclient";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':idclient', $data['id']);
        if(!$stmt->execute()) return false;
        $result['prodPublished'] = $stmt->fetch()['count']*1;

        // all client confirmed products
        $sql = "select count(pp.id) as count from ".
            " (SELECT p.id, IF(count(i.id)-SUM(IF(i.conf is NULL, 0, i.conf))=0 AND count(si.id)-SUM(IF(si.conf is NULL, 0, si.conf))=0, 1, 0) as conf from tproducts p ".
            " left join tp2i on (tp2i.idp=p.id) ".
            " left join tingredients i on (i.id=tp2i.idi) ".
            " left join ti2i on (ti2i.idi1=i.id) ".
            " left join tingredients si on (si.id=ti2i.idi2) ".
            " where p.deleted=0 AND p.idclient=:idclient group by p.id ) pp WHERE pp.conf=1";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':idclient', $data['id']);
        if(!$stmt->execute()) return false;
        $result['prodConfirmed'] = $stmt->fetch()['count']*1;

        // all client remained
        $sql = "select prodnumber from tusers WHERE id=:idclient";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(':idclient', $data['id']);
        if(!$stmt->execute()) return false;
        $result['prodNumber'] = $stmt->fetch()['prodnumber']*1;

        return $result;
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function sendDashboardData($data){
    $r = getClientStatistics($data);
    if ($r)
        $result['statistics'] = $r;
    else
        die(json_encode(generateErrorResponse("Getting client statistics failed")));

    echo json_encode(generateSuccessResponse($result));
}

function importClientsFromExcel($data) { 

	global $country_list;
	$myuser = cuser::singleton();
	$myuser->getUserData();
    $dbo = &$GLOBALS['dbo'];
	
    try {
        if (!isset($data['fileContent'])) {
			die(json_encode(generateErrorResponse("No file content provided")));
        }

        // Create temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'import_');
        file_put_contents($tempFile, base64_decode($data['fileContent']));
        
        // Load the Excel file using PhpSpreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempFile);
        $worksheet = $spreadsheet->getActiveSheet();
        
        $highestRow = $worksheet->getHighestRow();
        $processed = 0;
        $errors = [];
        
        // Start from row 2 to skip headers
        for ($row = 2; $row <= $highestRow; $row++) {
            try {
				$companyName = trim($worksheet->getCell('A'.$row)->getValue());
                
                // Skip empty rows
                if (empty($companyName)) {
                    continue;
                }
                
                // Get email value safely
                $emailCell = $worksheet->getCell('M'.$row);
                $emailValue = $emailCell->getValue();
                $email = is_null($emailValue) ? '' : trim($emailValue);

				//$trimmed = !empty($email) ? strtolower(preg_replace('/[^a-z0-9]/i', '', $email)) : strtolower(str_replace(" ", "", $companyName));
				$trimmed = strtolower(str_replace(" ", "", $companyName));
				$login =  random_username(substr($trimmed, 0, 25));

				$cell = $worksheet->getCell('N' . $row);
				$value = trim($cell->getValue());
				$certificateExpiryDate = null;

				if (!empty($value)) {
					if (is_numeric($value)) {
						try {
							$date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
							$certificateExpiryDate = convertToMySQLDate($date->format('d/m/Y')); // Or 'Y-m-d H:i:s' if you need time
						} catch (\Exception $e) {
							$certificateExpiryDate = null; // Or handle error
						}
					} else {
						// Try parsing as a plain string
						$certificateExpiryDate = convertToMySQLDate(trim($value));
					}
				}
                
                // Map all Excel values with proper null checks
                $clientData = [
                    'name' => $companyName,
                    'address' => trim($worksheet->getCell('B'.$row)->getValue()),
                    'city' => trim($worksheet->getCell('C'.$row)->getValue()),
                    'zip' => trim($worksheet->getCell('D'.$row)->getFormattedValue()),
                    'country' => trim($worksheet->getCell('E'.$row)->getValue()),
                    'industry' => trim($worksheet->getCell('F'.$row)->getValue()),
                    'category' => trim($worksheet->getCell('G'.$row)->getValue()),
                    'prodnumber' => (int)trim($worksheet->getCell('H'.$row)->getValue()),
                    'ingrednumber' => (int)trim($worksheet->getCell('I'.$row)->getValue()),
                    'vat' => trim($worksheet->getCell('J'.$row)->getFormattedValue()),
                    'contact_person' => trim($worksheet->getCell('K'.$row)->getValue()),
                    'phone' => trim($worksheet->getCell('L'.$row)->getFormattedValue()),
                    'email' => $email, // Use the safely retrieved email
                    'pork_free_facility' => strtolower(trim($worksheet->getCell('O'.$row)->getValue())) === 'yes' ? 'Yes' : 'No',
                    'dedicated_halal_lines' => trim($worksheet->getCell('P'.$row)->getValue()),
                    'export_regions' => trim($worksheet->getCell('Q'.$row)->getValue()),
                    'third_party_products' => strtolower(trim($worksheet->getCell('R'.$row)->getValue())) === 'yes' ? 'Yes' : 'No',
                    'third_party_halal_certified' => trim($worksheet->getCell('S'.$row)->getValue()),
                    
                    // Default values
                    'role' => 0,
                    'isclient' => 1,
                    'application' => 1,
                    'products' => 1,
                    'ingredients' => 1,
                    'ingredients_preference' => 1,
                    'login' => $login,
					'prefix' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                ];
				
                // Validate required fields
                if (empty($clientData['name'])) {
					die(json_encode(generateErrorResponse("Name is required")));
                }              

				//echo(json_encode(generateSuccessResponse($clientData)));
				//echo ",";
				//continue;

				  // Check if login already exists
				  $checkStmt = $dbo->prepare("SELECT id FROM tusers WHERE name = :name");
				  $checkStmt->execute([':name' => $companyName]);
				  
				  if ($checkStmt->fetch()) {
					  $skipped++;
					  continue; // Skip this record if login exists
				  }

				  $country_code = array_search($clientData['country'], $country_list);
				  $prefix = "CID_".date('m')."/".date('y')."_".$country_code."_";
				  $clientData["prefix"] = $prefix;
  
                // Prepare SQL statement
                $stmt = $dbo->prepare(" 
                    INSERT INTO tusers (
                        name, address, city, zip, country, industry, category,
                        prodnumber, ingrednumber, vat, contact_person, phone, email,
                        pork_free_facility, dedicated_halal_lines, export_regions,
                        third_party_products, third_party_halal_certified,
                        role, isclient, application, products, ingredients,
                        ingredients_preference, login, prefix, created_at
                    ) VALUES (
                        :name, :address, :city, :zip, :country, :industry, :category,
                        :prodnumber, :ingrednumber, :vat, :contact_person, :phone, :email,
                        :pork_free_facility, :dedicated_halal_lines, :export_regions,
                        :third_party_products, :third_party_halal_certified,
                        :role, :isclient, :application, :products, :ingredients,
                        :ingredients_preference, :login, :prefix, :created_at
                    )
                ");

                $stmt->execute($clientData);

				$idclient = $dbo->lastInsertId();
				
		$sql = "INSERT INTO tcycles (idclient, name, `state`) " .
		" VALUES (:idclient, :name, 1)";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->bindValue(':name', date('Y'));
		//$stmt->bindValue(':startDate', $startDt);
		//$stmt->bindValue(':endDate', $endDt);
		$stmt->execute();
		$idcycle = $dbo->lastInsertId();


		$query = "INSERT INTO tapplications (idclient, idcycle, prodnumber, ingrednumber, CertificateExpiryDate, state) VALUES (:idclient, :idcycle, :prodnumber, :ingrednumber, :CertificateExpiryDate, 'offer')";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
		$stmt->bindParam(':idcycle', $idcycle, PDO::PARAM_STR);
		$stmt->bindValue(':prodnumber', $clientData['prodnumber']);
		$stmt->bindValue(':ingrednumber',$clientData['ingrednumber']);
		$stmt->bindValue(':CertificateExpiryDate', $certificateExpiryDate);		
		$stmt->execute();
		$idapp = $dbo->lastInsertId();

		insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], ' Import Clients');		


                $processed++;
            } catch (Exception $e) {
                $errors[] = "Row $row: " . $e->getMessage();
            }
        }
        
        unlink($tempFile);
        
        if (!empty($errors)) {
            $errorMessage = 'Completed with errors: ' . $processed . ' processed. ' . implode('; ', $errors);
            die(json_encode(generateErrorResponse($errorMessage)));
        } else {
            $response = [
                'status' => 1,
                'data' => [
                    'processed' => $processed
                ]
            ];
            die(json_encode(generateSuccessResponse($response)));
        }
    } catch (Exception $e) {
        if (isset($tempFile) && file_exists($tempFile)) {
            unlink($tempFile);
        }
        die(json_encode(generateErrorResponse('Error: ' . $e->getMessage())));
    }
}

function addCertificateData($app){
    try{
        $status = 0;
        $certdate = strtotime($app['expdate']);
        $datediff = get_month_diff($certdate);

        if ($datediff <= 0) $status = 4;
        elseif ($datediff <= 30) $status = 3;
        elseif ($datediff <= 60) $status = 2;
        elseif ($datediff <= 90) $status = 1;

        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "INSERT INTO tcertificates (filename, url, hostpath, gdrivepath, expdate, status, idclient) ".
            " VALUES (:filename, :url, :hostpath, :gdrivepath, :expdate, :status, :idclient)";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':filename', $app['filename']);
        $stmt->bindValue(':idclient', $app['idclient']);
        $stmt->bindValue(':url', $app['url']);
        $stmt->bindValue(':hostpath', $app['hostpath']);
        $stmt->bindValue(':gdrivepath', $app['gdrivepath']);
        $stmt->bindValue(':expdate', date("Y-m-d", $certdate));
        $stmt->bindValue(':status', $status);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("Certificate inserting failed")));
        echo json_encode(generateSuccessResponse('Certificate was inserted'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function editCertificateData($app){
    try{
        $status = 0;
        $certdate = strtotime($app['expdate']);
        $datediff = get_month_diff($certdate);

        if ($datediff <= 0) $status = 4;
        elseif ($datediff <= 30) $status = 3;
        elseif ($datediff <= 60) $status = 2;
        elseif ($datediff <= 90) $status = 1;

        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE tcertificates SET expdate=:expdate, status=:status WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        $stmt->bindValue(':expdate', date("Y-m-d", $certdate));
        $stmt->bindValue(':status', $status);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("Date updating failed")));
        echo json_encode(generateSuccessResponse('Expiry date was updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function removeCertificateData($data){
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "DELETE FROM tcertificates WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $data['id']);
        if (!$stmt->execute()){
            echo json_encode(generateErrorResponse('Certificate removing failed'));
            die();
        }
        echo json_encode(generateSuccessResponse('Certificate was removed'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function addFileData($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "INSERT INTO tfiles (filename, url, hostpath, gdrivepath, idclient) ".
            " VALUES (:filename, :url, :hostpath, :gdrivepath, :idclient)";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':filename', $app['filename']);
        $stmt->bindValue(':idclient', 0);
        $stmt->bindValue(':url', "");
        $stmt->bindValue(':hostpath', "");
        $stmt->bindValue(':gdrivepath', $app['gdrivepath']);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("File info adding failed")));
        echo json_encode(generateSuccessResponse('File was inserted'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function editFileStatusData($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE tfiles SET status=:status WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        $stmt->bindValue(':status', $app['status']);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("Status updating failed")));
        echo json_encode(generateSuccessResponse('Share status was updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function sendEmailMessage($data){
    if(sendEmailWithAttach($data))
        echo json_encode(generateSuccessResponse());
    else
        echo json_encode(generateErrorResponse("Sending email failed"));
}

function createTask($data) {

	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
	$idauditor = trim($data["idauditor"]);
	$idclient = trim($data["idclient"]);
 	$issueDescription = trim($data["issueDescription"]);
	$issueType = trim($data["issueType"]);
	$attachments = trim($data["attachments"]);

	$myuser->getUserData();
	$user_id = $myuser->userdata['id'];
	$username = $myuser->userdata['name'];
	$email = $myuser->userdata['email'];

	$errors = "";
	$id = "";

	// Validate logged-in user
	if (empty($user_id)) {
		$errors .= "<li>User not logged in or session expired. Please refresh the page. If the problem persists, please log out and log in again.</li>";
	}

	if ($idauditor == "") {
		$errors .= "<li>Team member or auditor is required.</li>";
	}

	if ($idclient == "") {
		$errors .= "<li>Client is required.</li>";
	}

	if ($issueType == "") {
		$errors .= "<li>Category is required.</li>";
	}
	
	if ($issueDescription == "") {
		$errors .= "<li>Task Description is required.</li>";
	}

	if ($errors == "") {
		try {

			$query = "INSERT INTO ttasks 
          (user_id, username, email, issue_type, issue_description, current_url, attachments, last_updated_by, idauditor, idclient) 
          VALUES 
          (:user_id, :username, :email, :issueType, :issueDescription, :currentURL, :attachments, :last_updated_by, :idauditor, :idclient)";
$stmt = $dbo->prepare($query);

// Bind the new parameters for idauditor and idclient
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':issueType', $issueType, PDO::PARAM_STR);                                     
$stmt->bindParam(':issueDescription', $issueDescription, PDO::PARAM_STR);
$stmt->bindParam(':currentURL', $currentURL, PDO::PARAM_STR);
$stmt->bindParam(':attachments', $attachments, PDO::PARAM_STR);
$stmt->bindParam(':last_updated_by', $user_id, PDO::PARAM_STR);
$stmt->bindParam(':idauditor', $idauditor, PDO::PARAM_STR);  // Bind idauditor
$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);  // Bind idclient

$stmt->execute();


			$id = $dbo->lastInsertId();

			// Insert the new record into the treplies table
$replyQuery = "INSERT INTO treplies (task_id, user_id, username, email, message, attachments) VALUES (:task_id, :user_id, :username, :email, :message, :attachments)";
$replyStmt = $dbo->prepare($replyQuery);
$replyStmt->bindParam(':task_id', $id, PDO::PARAM_INT);
$replyStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$replyStmt->bindParam(':username', $username, PDO::PARAM_STR);
$replyStmt->bindParam(':email', $email, PDO::PARAM_STR);
$replyStmt->bindParam(':message', $issueDescription, PDO::PARAM_STR);
$replyStmt->bindParam(':attachments', $attachments, PDO::PARAM_STR);
$replyStmt->execute();
				
					$decode = file_get_contents( __DIR__ ."/../config.json");
					$config=json_decode($decode, TRUE);

					$hostPath = $config['filesfolder'] . "/";
					$absolutePath = __DIR__ ."/../".$hostPath;

					$ownerEmailAddress = "halal.ezone@gmail.com";
					$fromEmailAddress = "noreply@halal-e.zone";

					
					$auditorQuery = "SELECT email, name FROM tusers WHERE id = :idauditor";  // isclient = 2 for auditors
					$auditorStmt = $dbo->prepare($auditorQuery);
					
					// Bind the idauditor parameter
					$auditorStmt->bindParam(':idauditor', $idauditor, PDO::PARAM_STR);
					
					// Execute the query
					$auditorStmt->execute();
					
					// Fetch the result
					$auditorData = $auditorStmt->fetch(PDO::FETCH_ASSOC);
					
					// Get the auditor's email and name
					$auditorEmail = $auditorData['email'];
					$auditorName = $auditorData['name'];


					$clientQuery = "SELECT email, name FROM tusers WHERE id = :idclient";  // isclient = 1 for clients
$clientStmt = $dbo->prepare($clientQuery);

// Bind the idclient parameter
$clientStmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);

// Execute the query
$clientStmt->execute();

// Fetch the result
$clientData = $clientStmt->fetch(PDO::FETCH_ASSOC);

// Get the client's email and name
$clientEmail = $clientData['email'];
$clientName = $clientData['name'];

									
					
					//sendEmailWithAttach
					$body = [];
					$body['name'] = 'Halal e-Zone';
					$body['email'] =  $fromEmailAddress;
					$body['to'] = $auditorEmail;
					$attachment_filenames = explode(',', $attachments);

					// Check if there are multiple attachments
					if (count($attachment_filenames) > 1) {
						// If there are multiple attachments, set $body['attach'] to an array
						$body['attach'] = [];
				
						// Loop through each filename
						foreach ($attachment_filenames as $filename) {
							// Add each filename to the array with its hostpath
							$body['attach'][] = [
								'name' => trim($filename),
								'hostpath' => $absolutePath . trim($filename)
							];
						}
					} else {
						// If there is only one attachment, set $body['attach'] to the single filename with its hostpath
						$body['attachhostpath'] = $absolutePath . $attachments;
						$body['attach'] =  $attachments;
 					}

					// sending notification
					$body['subject'] = "Halal e-Zone - New Task Assigned to You - ".$auditorName;
$body['header'] = "";
$body['message'] = "<p>Dear ".$auditorName.",</p>";
$body['message'] .= "<p>A new task has been assigned to you on the Halal e-Zone portal:</p>
<p><strong>Reference number for this task:</strong> ".$id."</p>
<p><strong>Client:</strong> $clientName</p>
<p><strong>Task Category:</strong> $issueType</p>
<p><strong>Task Description:</strong> $issueDescription</p>
<p><strong>Assigned By:</strong> $username</p>
<p>Please log in to the Halal e-Zone portal to review and address the task. Once completed, kindly update the task status accordingly.</p>
<p>Regards,</p>
<p>Halal e-Zone</p>
";

sendEmailWithAttach($body);

					

		} catch (PDOException $e) {
			$errors .= "<li>Database error: " . $e->getMessage() . "</li>";
		}
	}

	echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $id)));
}

function createTicket($data) {

	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
 	$issueDescription = trim($data["issueDescription"]);
	$issueType = trim($data["issueType"]);
	$currentURL = trim($data["currentURL"]);
	$stepsToReproduce = trim($data["stepsToReproduce"]);
	$attachments = trim($data["attachments"]);

	$myuser->getUserData();
	$user_id = $myuser->userdata['id'];
	$username = $myuser->userdata['name'];
	$email = $myuser->userdata['email'];

	$errors = "";
	$id = "";

	// Validate logged-in user
	if (empty($user_id)) {
		$errors .= "<li>User not logged in or session expired. Please refresh the page. If the problem persists, please log out and log in again.</li>";
	}

	if ($issueType == "") {
		$errors .= "<li>Issue Type is required.</li>";
	}
	
	if ($issueDescription == "") {
		$errors .= "<li>Issue Description is required.</li>";
	}

	if ($errors == "") {
		try {

			$query = "INSERT INTO ttickets (user_id, username, email, issue_type, issue_description, current_url, attachments, last_updated_by) VALUES (:user_id, :username, :email, :issueType, :issueDescription, :currentURL, :attachments, :last_updated_by)";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->bindParam(':email', $email, PDO::PARAM_STR);
			$stmt->bindParam(':issueType', $issueType, PDO::PARAM_STR);									
			$stmt->bindParam(':issueDescription', $issueDescription, PDO::PARAM_STR);
			$stmt->bindParam(':currentURL', $currentURL, PDO::PARAM_STR);
			$stmt->bindParam(':attachments', $attachments, PDO::PARAM_STR);
			$stmt->bindParam(':last_updated_by', $user_id, PDO::PARAM_STR);

			$stmt->execute();

			$id = $dbo->lastInsertId();

			// Insert the new record into the treplies table
$replyQuery = "INSERT INTO treplies (ticket_id, user_id, username, email, message, attachments) VALUES (:ticket_id, :user_id, :username, :email, :message, :attachments)";
$replyStmt = $dbo->prepare($replyQuery);
$replyStmt->bindParam(':ticket_id', $id, PDO::PARAM_INT);
$replyStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$replyStmt->bindParam(':username', $username, PDO::PARAM_STR);
$replyStmt->bindParam(':email', $email, PDO::PARAM_STR);
$replyStmt->bindParam(':message', $issueDescription, PDO::PARAM_STR);
$replyStmt->bindParam(':attachments', $attachments, PDO::PARAM_STR);
$replyStmt->execute();
				
					$decode = file_get_contents( __DIR__ ."/../config.json");
					$config=json_decode($decode, TRUE);

					$hostPath = $config['filesfolder'] . "/";
					$absolutePath = __DIR__ ."/../".$hostPath;

					$ownerEmailAddress = "halal.ezone@gmail.com";
					$fromEmailAddress = "noreply@halal-e.zone";

					$body = [];
					$body['name'] = 'Halal e-Zone';
					$body['email'] =  $fromEmailAddress;
					$body['to'] = $email;
					 
					// sending notification
					$body['subject'] = "Halal e-Zone - New Ticket - Reference Number: ".$id;
					$body['header'] = "";
					$body['message'] = "<p>Dear ".$username.",</p>";
					$body['message'] .= "<p>Thank you for contacting us! Your inquiry is important to us.

					We will review your request and reply to you within 2 business days. Please do not send multiple emails, as the review time will be prolonged to an additional business day after every email. </p>
					<p>Reference number for this inquiry: ".$id."</p>
					<p><strong>Issue Type:</strong> $issueType</p>
					<p><strong>Issue Description:</strong> $issueDescription</p>
					<p><strong>Current URL:</strong> <a href='$currentURL'>$currentURL</a></p>
					<p>Please log in to the Halal e-Zone portal to track the issue status.</p>
					<p>Regards</p> 
					<p>Halal e-Zone</p>
					";
					sendEmailWithAttach($body);

					//sendEmailWithAttach
					$body = [];
					$body['name'] = 'Halal e-Zone';
					$body['email'] =  $fromEmailAddress;
					$body['to'] = "alrahmahsolutions@gmail.com";
					$attachment_filenames = explode(',', $attachments);

					// Check if there are multiple attachments
					if (count($attachment_filenames) > 1) {
						// If there are multiple attachments, set $body['attach'] to an array
						$body['attach'] = [];
				
						// Loop through each filename
						foreach ($attachment_filenames as $filename) {
							// Add each filename to the array with its hostpath
							$body['attach'][] = [
								'name' => trim($filename),
								'hostpath' => $absolutePath . trim($filename)
							];
						}
					} else {
						// If there is only one attachment, set $body['attach'] to the single filename with its hostpath
						$body['attachhostpath'] = $absolutePath . $attachments;
						$body['attach'] =  $attachments;
 					}

					// sending notification
					$body['subject'] = "Halal e-Zone - New Issue Reported - ".$username;
					$body['header'] = "";
					$body['message'] = "<p>Dear Admin,</p>";
					$body['message'] .= "<p>A new issue has been reported on the Halal e-Zone portal:</p>
					<p><strong>Reference number for this inquiry:</strong> ".$id."</p>
					<p><strong>Client:</strong> $username</p>
					<p><strong>Client Email:</strong> $email</p>
					<p><strong>Issue Type:</strong> $issueType</p>
					<p><strong>Issue Description:</strong> $issueDescription</p>
					<p><strong>Current URL:</strong> <a href='$currentURL'>$currentURL</a></p>
					<p>Please log in to the Halal e-Zone portal to update the client about the issue status.</p>
					<p>Regards</p>
					<p>Halal e-Zone</p>
					";
					sendEmailWithAttach($body);
					

		} catch (PDOException $e) {
			$errors .= "<li>Database error: " . $e->getMessage() . "</li>";
		}
	}

	echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $id)));
}

function postReply($data) {
	global $supportEmailAddress;

    $dbo = &$GLOBALS['dbo'];
    $myuser = cuser::singleton();
    
    $replyText = trim($data["message"]);
    $ticketId = trim($data["ticketId"]);
    $customerServiceId = trim($data["customerServiceId"]);
    $taskId = trim($data["taskId"]);
    $attachments = trim($data["attachments"]);

    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];
    $username = $myuser->userdata['name'];
    $email = $myuser->userdata['email'];

    $errors = "";
    $id = "";

		// Validate logged-in user
	if (empty($user_id)) {
		$errors .= "<li>User not logged in or session expired. Please refresh the page. If the problem persists, please log out and log in again.</li>";
	}

    if ($replyText == "") {
        $errors .= "<li>Reply text is required.</li>";
    }

    if ($ticketId == "" && $customerServiceId == "" && $taskId == "") {
        $errors .= "<li>Reference# is required.</li>";
    }

    if ($errors == "") {
        try {
            // Determine the table and field names
            if ($ticketId != "") {
                $tableName = 'ttickets';
                $replyField = 'ticket_id';
                $updateField = 'id';
				$descriptionField = "issue_description";
            } elseif ($customerServiceId != "") {
                $tableName = 'tcustomerservice';
                $replyField = 'customerservice_id';
                $updateField = 'id';
				$descriptionField = "request_description";
            } elseif ($taskId != "") {
                $tableName = 'ttasks';
                $replyField = 'task_id';
                $updateField = 'id';        
				$descriptionField = "issue_description";
            } else {
                throw new Exception('Reference ID is missing.');
            }

            $referenceId = $ticketId ?: ($customerServiceId ?: $taskId);

            // Insert reply
            $query = "INSERT INTO treplies ($replyField, user_id, username, email, message, attachments) VALUES (:id, :user_id, :username, :email, :replyText, :attachments)";
            $stmt = $dbo->prepare($query);
            $stmt->bindValue(':id', $referenceId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':replyText', $replyText, PDO::PARAM_STR);
            $stmt->bindValue(':attachments', $attachments, PDO::PARAM_STR);
            $stmt->execute();

            $id = $dbo->lastInsertId();

            // Update last updated info
            $update_query = "UPDATE $tableName SET last_updated_by = :user_id, last_updated_by_name = :username, last_updated_by_email = :email, viewed = 0 WHERE $updateField = :id";
            $update_stmt = $dbo->prepare($update_query);
            $update_stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
            $update_stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $update_stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $update_stmt->bindValue(':id', $referenceId, PDO::PARAM_INT);
            $update_stmt->execute();

            $config = json_decode(file_get_contents(__DIR__ . "/../config.json"), TRUE);
            $hostPath = $config['filesfolder'] . "/";
            $absolutePath = __DIR__ . "/../" . $hostPath;

            $ownerEmailAddress = "halal.ezone@gmail.com";
            $fromEmailAddress = "noreply@halal-e.zone";

            $ticket_query = "SELECT user_id, email, idauditor, idclient, $descriptionField FROM $tableName WHERE $updateField = :id";
            $ticket_stmt = $dbo->prepare($ticket_query);
            $ticket_stmt->bindValue(':id', $referenceId, PDO::PARAM_INT);
            $ticket_stmt->execute();
            $dataRet = $ticket_stmt->fetch(PDO::FETCH_ASSOC);
            $creator = $dataRet['user_id'];
            $idauditor = $dataRet['idauditor'];
			$idclient = $dataRet['idclient'];
			$issueDescription = $dataRet[$descriptionField];

            // Get creator info
            $creatorQuery = "SELECT email, name FROM tusers WHERE id = :user_id";
            $creatorStmt = $dbo->prepare($creatorQuery);
            $creatorStmt->bindParam(':user_id', $creator, PDO::PARAM_STR);
            $creatorStmt->execute();
            $creatorData = $creatorStmt->fetch(PDO::FETCH_ASSOC);
            $creatorEmail = $creatorData['email'];
            $creatorName = $creatorData['name'];

            // Get auditor info
            $auditorEmail = "";
            $auditorName = "";
            if ($idauditor) {
                $auditorQuery = "SELECT email, name FROM tusers WHERE id = :idauditor";
                $auditorStmt = $dbo->prepare($auditorQuery);
                $auditorStmt->bindParam(':idauditor', $idauditor, PDO::PARAM_STR);
                $auditorStmt->execute();
                $auditorData = $auditorStmt->fetch(PDO::FETCH_ASSOC);
                $auditorEmail = $auditorData['email'];
                $auditorName = $auditorData['name'];
            }

			$clientEmail = "";
            $clientName = "";
            if ($idauditor) {
                $clientQuery = "SELECT email, name FROM tusers WHERE id = :idclient";
                $clientStmt = $dbo->prepare($clientQuery);
                $clientStmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
                $clientStmt->execute();
                $clientData = $clientStmt->fetch(PDO::FETCH_ASSOC);
                $clientEmail = $clientData['email'];
                $clientName = $clientData['name'];
            }

            $to = [];

            if ($customerServiceId) {
                $json_id = json_encode([(string)$creator]);
                $sql = "SELECT * FROM tusers WHERE isclient = 2 AND deleted = 0 AND JSON_CONTAINS(clients_audit, :json_id, '$')";
                $stmt = $dbo->prepare($sql);
                $stmt->bindParam(':json_id', $json_id);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($results as $row) {
                    $to[] = ["name" => $row["name"], "email" => $row["email"]];
                }
            }

            if ($ticketId || $customerServiceId) {
                if ($customerServiceId) {
                    $to[] = ["name" => "Admin", "email" => $supportEmailAddress];
                } else {
                    $to[] = ["name" => "Admin", "email" => "alrahmahsolutions@gmail.com"];
                }
                $to[] = ["name" => $creatorName, "email" => $creatorEmail];
            } else { 
                $to[] = ["name" => $auditorName, "email" => $auditorEmail];
                $to[] = ["name" => $creatorName, "email" => $creatorEmail];
            }

            $type = $ticketId ? "Issue Tracker" : ($customerServiceId ? "Customer Support" : ($taskId ? "Task" : "Unknown"));

            $sentEmails = [];
            foreach ($to as $recipient) {
                if ($recipient['email'] != $email && !in_array($recipient['email'], $sentEmails)) {
                    $body = [];
                    $body['name'] = 'Halal e-Zone';
                    $body['email'] = $fromEmailAddress;
                    $body['to'] = $recipient['email'];
					$body['subject'] = "Halal e-Zone - Reply Posted by " . $username . " - " . $type . ": " . $referenceId;
                    $body['header'] = "";
                    $body['message'] = "<p>Dear " . htmlspecialchars($recipient['name']) . ",</p>";
                    $body['message'] .= "<p>A new reply has been posted by <strong>" . $username . "</strong> on the Halal e-Zone portal:</p>";
                    $body['message'] .= "<p><strong>Reference number:</strong> " . $referenceId . "</p>";
					if ($taskId) {
						$body['message'] .= "<p><strong>Client Name:</strong> " . $clientName . "</p>";
					}
					if ($issueDescription) {
 					   $body['message'] .= "<p><strong>Original Request:</strong> " . htmlspecialchars($issueDescription) . "</p>";
					}
                    $body['message'] .= "<p><strong>Reply Text:</strong> " . nl2br(htmlspecialchars($replyText)) . "</p>";
                    $body['message'] .= "<p>Regards</p><p>Halal e-Zone</p>";
                    sendEmailWithAttach($body);
                    $sentEmails[] = $recipient['email'];
                }
            }

        } catch (PDOException $e) {
            $errors .= "<li>Database error: " . $e->getMessage() . "</li>";
        }
    }

    echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $id)));
}

function closeTicket($data) {
    $dbo = &$GLOBALS['dbo'];
    $myuser = cuser::singleton();
    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];
    $username = $myuser->userdata['name'];
    $email = $myuser->userdata['email'];
    $ticketId = trim($data["id"]);
	try {
		
		// Update last_updated_id, last_updated_name, and last_updated_email in ttickets table
		$update_query = "UPDATE ttickets SET last_updated_by = :user_id, last_updated_by_name = :username, last_updated_by_email = :email, status = 0  WHERE id = :ticketId";
		$update_stmt = $dbo->prepare($update_query);
		$update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
		$update_stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$update_stmt->bindParam(':email', $email, PDO::PARAM_STR);
		$update_stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
		$update_stmt->execute();
 
	} catch (PDOException $e) {
		$errors .= "<li>Database error: " . $e->getMessage() . "</li>";
	}

    echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $ticketId)));
}

function closeTask($data) {
    $dbo = &$GLOBALS['dbo'];
    $myuser = cuser::singleton();
    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];
    $username = $myuser->userdata['name'];
    $email = $myuser->userdata['email'];
    $taskId = trim($data["id"]);
	try {
		
		// Update last_updated_id, last_updated_name, and last_updated_email in ttasks table
		$update_query = "UPDATE ttasks SET last_updated_by = :user_id, last_updated_by_name = :username, last_updated_by_email = :email, status = 0  WHERE id = :taskId";
		$update_stmt = $dbo->prepare($update_query);
		$update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
		$update_stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$update_stmt->bindParam(':email', $email, PDO::PARAM_STR);
		$update_stmt->bindParam(':taskId', $taskId, PDO::PARAM_INT);
		$update_stmt->execute();
 
	} catch (PDOException $e) {
		$errors .= "<li>Database error: " . $e->getMessage() . "</li>";
	}

    echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $taskId)));
}

function createCustomerService($data) {
	global $supportEmailAddress;
	$dbo = &$GLOBALS['dbo'];
	$myuser = cuser::singleton();
 	$requestDescription = trim($data["requestDescription"]);
	$requestType = trim($data["requestType"]);
	$currentURL = trim($data["currentURL"]);
	$stepsToReproduce = trim($data["stepsToReproduce"]);
	$attachments = trim($data["attachments"]);

	$myuser->getUserData();
	$user_id = $myuser->userdata['id'];
	$username = $myuser->userdata['name'];
	$email = $myuser->userdata['email'];

	if ($data["user_id"] != "") {
		$user_id = $data["user_id"];
	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $user_id);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

		$username = $user['name'];
		$email = $user['email'];
	}

	$errors = "";
	$id = "";

	// Validate logged-in user
	if (empty($user_id)) {
		$errors .= "<li>User not logged in or session expired. Please refresh the page. If the problem persists, please log out and log in again.</li>";
	}

	if ($requestType == "") {
		$errors .= "<li>Request Type is required.</li>";
	}
	
	if ($requestDescription == "") {
		$errors .= "<li>Request Description is required.</li>";
	}

	if ($errors == "") {
		try {

			$query = "INSERT INTO tcustomerservice (user_id, username, email, request_type, request_description, current_url, attachments, created_by, created_by_name, created_by_email) VALUES (:user_id, :username, :email, :requestType, :requestDescription, :currentURL, :attachments, :created_by, :created_by_name, :created_by_email)";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
			$stmt->bindParam(':username', $username, PDO::PARAM_STR);
			$stmt->bindParam(':email', $email, PDO::PARAM_STR);
			$stmt->bindParam(':requestType', $requestType, PDO::PARAM_STR);									
			$stmt->bindParam(':requestDescription', $requestDescription, PDO::PARAM_STR);
			$stmt->bindParam(':currentURL', $currentURL, PDO::PARAM_STR);
			$stmt->bindParam(':attachments', $attachments, PDO::PARAM_STR);
			$stmt->bindParam(':created_by', $myuser->userdata['id'], PDO::PARAM_STR);
			$stmt->bindParam(':created_by_name', $myuser->userdata['name'], PDO::PARAM_STR);
			$stmt->bindParam(':created_by_email', $myuser->userdata['email'], PDO::PARAM_STR);

			$stmt->execute();

			$id = $dbo->lastInsertId();
 
			// Insert the new record into the treplies table
$replyQuery = "INSERT INTO treplies (customerservice_id, user_id, username, email, message, attachments) VALUES (:customerservice_id, :user_id, :username, :email, :message, :attachments)";
$replyStmt = $dbo->prepare($replyQuery);
$replyStmt->bindParam(':customerservice_id', $id, PDO::PARAM_INT);
$replyStmt->bindParam(':user_id', $myuser->userdata['id'], PDO::PARAM_INT);
$replyStmt->bindParam(':username', $myuser->userdata['name'], PDO::PARAM_STR);
$replyStmt->bindParam(':email', $myuser->userdata['email'], PDO::PARAM_STR);
$replyStmt->bindParam(':message', $requestDescription, PDO::PARAM_STR);
$replyStmt->bindParam(':attachments', $attachments, PDO::PARAM_STR);
$replyStmt->execute();
				
					$decode = file_get_contents( __DIR__ ."/../config.json");
					$config=json_decode($decode, TRUE);

					$hostPath = $config['filesfolder'] . "/";
					$absolutePath = __DIR__ ."/../".$hostPath;

					$ownerEmailAddress = "halal.ezone@gmail.com";
					$fromEmailAddress = "noreply@halal-e.zone";

					if ($data["user_id"] != "") {
						$body = [];
						$body['name'] = 'Halal e-Zone';
						$body['email'] =  $fromEmailAddress;
						$body['to'] = $email;
						$attachment_filenames = explode(',', $attachments);

						// Check if there are multiple attachments
						if (count($attachment_filenames) > 1) {
							// If there are multiple attachments, set $body['attach'] to an array
							$body['attach'] = [];
					
							// Loop through each filename
							foreach ($attachment_filenames as $filename) {
								// Add each filename to the array with its hostpath
								$body['attach'][] = [
									'name' => trim($filename),
									'hostpath' => $absolutePath . trim($filename)
								];
							}
						} else {
							// If there is only one attachment, set $body['attach'] to the single filename with its hostpath
							$body['attachhostpath'] = $absolutePath . $attachments;
							$body['attach'] =  $attachments;
						}

						// sending notification
						$body['subject'] = "Halal e-Zone - New Request Submitted";
						$body['header'] = "";
						$body['message'] = "<p>Dear $username,</p>";
						$body['message'] .= "<p>A new request has been submitted on the Halal e-Zone portal:</p>
						<p><strong>Reference number:</strong> ".$id."</p>
						<p><strong>Request Type:</strong> $requestType</p>
						<p><strong>Request Description:</strong> $requestDescription</p>
						<p>You can log in to the Halal e-Zone portal to view and respond to this request.</p>
						<p>Regards</p>
						<p>Halal e-Zone</p>
						";
						sendEmailWithAttach($body);
						
						$body['to'] = "alrahmahsolutions@gmail.com";
						
						sendEmailWithAttach($body);
					}
					else {
						$body = [];
						$body['name'] = 'Halal e-Zone';
						$body['email'] =  $fromEmailAddress;
						$body['to'] = $email;
						
						// sending notification
						$body['subject'] = "Halal e-Zone - New Request - Reference Number: ".$id;
						$body['header'] = "";
						$body['message'] = "<p>Dear ".$username.",</p>";
						$body['message'] .= "<p>Thank you for contacting us!
											We will review your request and reply to you within 2 business days. </p>
						<p>Reference number: ".$id."</p>
						<p><strong>Request Type:</strong> $requestType</p>
						<p><strong>Request Description:</strong> $requestDescription</p>
						<p><strong>Current URL:</strong> <a href='$currentURL'>$currentURL</a></p>
						<p>Please log in to the Halal e-Zone portal to track the request status.</p>
						<p>Regards</p> 
						<p>Halal e-Zone</p>
						";
						sendEmailWithAttach($body);

						//sendEmailWithAttach
						$body = [];
						$body['name'] = 'Halal e-Zone';
						$body['email'] =  $fromEmailAddress;
						$body['to'] = $supportEmailAddress;
						$attachment_filenames = explode(',', $attachments);

						// Check if there are multiple attachments
						if (count($attachment_filenames) > 1) {
							// If there are multiple attachments, set $body['attach'] to an array
							$body['attach'] = [];
					
							// Loop through each filename
							foreach ($attachment_filenames as $filename) {
								// Add each filename to the array with its hostpath
								$body['attach'][] = [
									'name' => trim($filename),
									'hostpath' => $absolutePath . trim($filename)
								];
							}
						} else {
							// If there is only one attachment, set $body['attach'] to the single filename with its hostpath
							$body['attachhostpath'] = $absolutePath . $attachments;
							$body['attach'] =  $attachments;
						}

						// sending notification
						$body['subject'] = "Halal e-Zone - New Request Submitted - ".$username;
						$body['header'] = "";
						$body['message'] = "<p>Dear Admin,</p>";
						$body['message'] .= "<p>A new request has been submitted on the Halal e-Zone portal:</p>
						<p><strong>Reference number:</strong> ".$id."</p>
						<p><strong>Client:</strong> $username</p>
						<p><strong>Client Email:</strong> $email</p>
						<p><strong>Request Type:</strong> $requestType</p>
						<p><strong>Request Description:</strong> $requestDescription</p>
						<p><strong>Current URL:</strong> <a href='$currentURL'>$currentURL</a></p>
						<p>Please log in to the Halal e-Zone portal to update the client about the request status.</p>
						<p>Regards</p>
						<p>Halal e-Zone</p>
						";
						sendEmailWithAttach($body);
						
						$body['to'] = "alrahmahsolutions@gmail.com";
						
						sendEmailWithAttach($body);
					}

		} catch (PDOException $e) {
			$errors .= "<li>Database error: " . $e->getMessage() . "</li>";
		}
	}

	echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $id)));
}
 

function closeCustomerService($data) {
    $dbo = &$GLOBALS['dbo'];
    $myuser = cuser::singleton();
    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];
    $username = $myuser->userdata['name'];
    $email = $myuser->userdata['email'];
    $ticketId = trim($data["id"]);
	try {
		
		// Update last_updated_id, last_updated_name, and last_updated_email in tcustomerservice table
		$update_query = "UPDATE tcustomerservice SET last_updated_by = :user_id, last_updated_by_name = :username, last_updated_by_email = :email, status = 0  WHERE id = :ticketId";
		$update_stmt = $dbo->prepare($update_query);
		$update_stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
		$update_stmt->bindParam(':username', $username, PDO::PARAM_STR);
		$update_stmt->bindParam(':email', $email, PDO::PARAM_STR);
		$update_stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
		$update_stmt->execute();
 
	} catch (PDOException $e) {
		$errors .= "<li>Database error: " . $e->getMessage() . "</li>";
	}

    echo json_encode(generateSuccessResponse(array("errors" => $errors, "id" => $ticketId)));
}


function getNewTicketsCount() {
    $dbo = &$GLOBALS['dbo'];
    $myuser = cuser::singleton();
    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];

    if (!$myuser->userdata['canadmin']) {
        $filter = " AND t.user_id = " . $user_id;
    } else {
        $filter = "";
    }

    // Find tickets with replies from other users that have not been read by the current user
	/*
    $query = "
        SELECT COUNT(DISTINCT t.id) AS unviewed_count
        FROM ttickets AS t
        LEFT JOIN treplies AS r ON t.id = r.ticket_id
        LEFT JOIN ticket_reads AS tr ON t.id = tr.ticket_id AND r.id = tr.reply_id  
        WHERE t.status = 1
        AND r.user_id != :user_id
        AND tr.user_id IS NULL
        " . $filter;

    $stmt = $dbo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['unviewed_count' => $result['unviewed_count']]);
    } else {
        echo json_encode(['unviewed_count' => 0]);
    }
	*/
}


function completeTask($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE td2i SET status=1, completed_at=current_timestamp() WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("Status updating failed")));
        echo json_encode(generateSuccessResponse('Share status was updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function undoneTask($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE td2i SET status=0, completed_at=NULL WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("Status updating failed")));
        echo json_encode(generateSuccessResponse('Share status was updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function confirmTask($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE td2i SET status=2 WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("Status updating failed")));
        echo json_encode(generateSuccessResponse('Share status was updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function confirmAllTaskForIngredient($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE td2i SET status=2 WHERE idi=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        return $stmt->execute();
    } catch (PDOException $e){
        return false;
    }
}

function confirmAllActionsForIngredient($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE tclientactions SET status=1 WHERE itemid=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        return $stmt->execute();
    } catch (PDOException $e){
        return false;
    }
}

function addTaskData($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

        // add task
        $sql = "INSERT INTO tdeviations (deviation, measure) ".
            " VALUES (:deviation, :measure)";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':deviation', $app['deviation']);
        $stmt->bindValue(':measure', $app['measure']);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("New task adding failed")));

        // bind task to the ingredient
        if (isset($app['idingredient'])) {
            $lastInsertId = $dbo->lastInsertId();
            $sql = "INSERT INTO td2i (idd, idi) VALUES (:idd, :idi)";
            $stmt = $dbo->prepare($sql);
            $stmt->bindValue(':idd', $lastInsertId);
            $stmt->bindValue(':idi', $app['idingredient']);
            if(!$stmt->execute()) die(json_encode(generateErrorResponse("New task adding failed")));
        }

        echo json_encode(generateSuccessResponse('New task was inserted'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function updateTaskData($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE tdeviations SET deviation=:deviation, measure=:measure WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':deviation', $app['deviation']);
        $stmt->bindValue(':measure', $app['measure']);
		$stmt->bindValue(':id', $app['id']);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("Task updating failed")));
        echo json_encode(generateSuccessResponse('Task was updated.'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function confirmProcessStatus($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE tactivity_log SET status=1 WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("Status updating failed")));
        echo json_encode(generateSuccessResponse('Share status was updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function confirmClientAction($app){
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "UPDATE tclientactions SET status=1 WHERE id=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $app['id']);
        if(!$stmt->execute()) die(json_encode(generateErrorResponse("Status updating failed")));
        echo json_encode(generateSuccessResponse('Share status was updated'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function addClientAction($data){
    if ($data['itemtype']=='products'){
        die();
    }
    try{
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        $sql = "INSERT INTO tclientactions (idclient, itemtype, itemid, itemcode, itemname, action) ".
            " VALUES (:idclient, :itemtype, :itemid, :itemcode, :itemname, :action)";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':idclient', $data['idclient']);
        $stmt->bindValue(':itemtype', $data['itemtype']);
        $stmt->bindValue(':itemid', $data['itemid']);
        $stmt->bindValue(':itemcode', $data['itemcode']);
        $stmt->bindValue(':itemname', $data['itemname']);
        if (isset($data['documents'])) {
            $ds =  json_decode($data['documents']);
            foreach ($ds as $d) {
                $stmt->bindValue(':action', 'New document ('.$d->file.')');
                if(!$stmt->execute()) die(json_encode(generateErrorResponse("Action inserting failed")));
            }
        }
		else {
            $stmt->bindValue(':action', $data['action']);
            if (!$stmt->execute()) die(json_encode(generateErrorResponse("Action inserting failed")));
        }
        echo json_encode(generateSuccessResponse('Action was inserted'));
    } catch (PDOException $e) {
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function saveCertCycleData($data) {
    $errors = array();

    // Validate Certification Cycle Name
    if (empty($data['cycleName'])) {
        $errors[] = "Cycle Name is mandatory";
    }

    // Validate Start Date
	/*
    if (empty($data['startDate'])) {
        $errors[] = "Start Date is mandatory";
    } else {
        $dateTime = DateTime::createFromFormat('d/m/Y', $data['startDate']);
        if (!$dateTime) {
            $errors[] = "Invalid Start Date format";
        }
    }

    // Validate End Date
    if (empty($data['endDate'])) {
        $errors[] = "End Date is mandatory";
    } else {
        $dateTime = DateTime::createFromFormat('d/m/Y', $data['endDate']);
        if (!$dateTime) {
            $errors[] = "Invalid End Date format";
        }
    }
	*/

    // Check if there are any validation errors
    if (!empty($errors)) {
        die(json_encode(generateErrorResponse($errors)));
    }

    // Validation passed, continue with saving the data
    try {
        //$startDt = $dateTime->format("Y-m-d");
        //$endDt = $dateTime->format("Y-m-d");

        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//        $sql = "INSERT INTO tcycles (idclient, name, startdt, enddt, `state`) " .
//            " VALUES (:idclient, :name, :startDate, :endDate, 1)";
		$sql = "INSERT INTO tcycles (idclient, name, `state`) " .
		" VALUES (:idclient, :name, 1)";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':idclient', $data['idclient']);
        $stmt->bindValue(':name', $data['cycleName']);
       // $stmt->bindValue(':startDate', $startDt);
        //$stmt->bindValue(':endDate', $endDt);
        if (!$stmt->execute()) {
            die(json_encode(generateErrorResponse("Cycle creating failed")));
        }
        $idcycle = $dbo->lastInsertId();

        // Assuming $insertedId contains the inserted ID
        // Check if there is a row with the same idclient in tapplications
        $sql = "SELECT idclient FROM tapplications WHERE IFNULL(idcycle, '')='' AND idclient = :idclient";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':idclient', $data['idclient']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            // If a row exists, update the idcycle field
            $sql = "UPDATE tapplications SET idcycle = :idcycle WHERE idclient = :idclient";
            $stmt = $dbo->prepare($sql);
            $stmt->bindValue(':idcycle', $idcycle);
            $stmt->bindValue(':idclient', $data['idclient']);
            $stmt->execute();
        }
		else {
			// Select data from tusers table based on idclient
			$selectQuery = "SELECT prodnumber, ingrednumber FROM tusers WHERE id=:idclient";
			$stmtSelect = $dbo->prepare($selectQuery);
			$stmtSelect->bindParam(':idclient', $data['idclient'], PDO::PARAM_STR);
			$stmtSelect->execute();
			$userData = $stmtSelect->fetch(PDO::FETCH_ASSOC);
			if ($userData) {
				// Insert data into tapplications table
				$insertQuery = "INSERT INTO tapplications (idclient, idcycle, prodnumber, ingrednumber, state) VALUES (:idclient, :idcycle, :prodnumber, :ingrednumber, 'app')";
				$stmtInsert = $dbo->prepare($insertQuery);
				$stmtInsert->bindParam(':idclient', $data['idclient'], PDO::PARAM_STR);
				$stmtInsert->bindParam(':idcycle', $idcycle, PDO::PARAM_STR);
				$stmtInsert->bindParam(':prodnumber', $userData['prodnumber'], PDO::PARAM_STR);
				$stmtInsert->bindParam(':ingrednumber', $userData['ingrednumber'], PDO::PARAM_STR);
				$stmtInsert->execute();
				$idapp = $dbo->lastInsertId();
			}
		}

        echo json_encode(generateSuccessResponse('Cycle created.'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function startNewCertCycleData($data) {

	$errors = array();

	$idclient = $data["idclient"];
	$idapp = $data["idapp"];

    // Validate Certification Cycle Name
    if (empty($data['cycleName'])) {
        $errors[] = "Cycle Name is mandatory";
    }

	/*
    // Validate Start Date
    if (empty($data['startDate'])) {
        $errors[] = "Start Date is mandatory";
    } else {
        $dateTime = DateTime::createFromFormat('d/m/Y', $data['startDate']);
        if (!$dateTime) {
            $errors[] = "Invalid Start Date format";
        }
    }

    // Validate End Date
    if (empty($data['endDate'])) {
        $errors[] = "End Date is mandatory";
    } else {
        $dateTime = DateTime::createFromFormat('d/m/Y', $data['endDate']);
        if (!$dateTime) {
            $errors[] = "Invalid End Date format";
        }
    }
	*/

    // Check if there are any validation errors
    if (!empty($errors)) {
        die(json_encode(generateErrorResponse($errors)));
    }

    // Validation passed, continue with saving the data
    try {
       // $startDt = $dateTime->format("Y-m-d");
       // $endDt = $dateTime->format("Y-m-d");

        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "UPDATE tcycles SET `state`=0  WHERE idclient = :idclient";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':idclient', $idclient);
        if (!$stmt->execute()) {
            die(json_encode(generateErrorResponse("Cycle creating failed")));
        }

		//$sql = "INSERT INTO tcycles (idclient, name, startdt, enddt, `state`) VALUES (:idclient, :name, :startDate, :endDate, 1)";
		$sql = "INSERT INTO tcycles (idclient, name, `state`) " .
		" VALUES (:idclient, :name, 1)";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->bindValue(':name', $data['cycleName']);
		//$stmt->bindValue(':startDate', $startDt);
		//$stmt->bindValue(':endDate', $endDt);
		if (!$stmt->execute()) {
			die(json_encode(generateErrorResponse("Cycle creating failed")));
		}
		$insertedId = $dbo->lastInsertId();

		// Fetch the existing row data based on the id
		$sql = "SELECT * FROM tapplications WHERE idclient = :idclient AND id = :idapp";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->bindValue(':idapp', $idapp);
		$stmt->execute();
		$rowData = $stmt->fetch(PDO::FETCH_ASSOC);

		// Duplicate the row with the new values
		$sql = "INSERT INTO tapplications (idcycle, idclient, name, prodnumber, ingrednumber, state, notifystatus, audit_plan_settings, audit_report_settings, countryOfCompany, addresses, companyId, reference, LeadAuditor, coAuditor, IslamicAffairsExpert, Veterinary, AccompanyingAuditorsOrExperts, HalalQualityControlOfficeCountry, offerOffice)
				VALUES (:idcycle, :idclient, :name, :prodnumber, :ingrednumber, 'soffer', :notifystatus, :audit_plan_settings, :audit_report_settings, :countryOfCompany, :addresses, :companyId, :reference, :LeadAuditor, :coAuditor, :IslamicAffairsExpert, :Veterinary, :AccompanyingAuditorsOrExperts, :HalalQualityControlOfficeCountry, :offerOffice)";

		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':idcycle', $insertedId);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->bindValue(':name', $rowData['name']);
		$stmt->bindValue(':prodnumber', $rowData['prodnumber']);
		$stmt->bindValue(':ingrednumber', $rowData['ingrednumber']);
		$stmt->bindValue(':notifystatus', $rowData['notifystatus']);
		//$stmt->bindValue(':audit_date_1', $rowData['audit_date_1']);
		//$stmt->bindValue(':audit_date_2', $rowData['audit_date_2']);
		//$stmt->bindValue(':audit_date_3', $rowData['audit_date_3']);
		//$stmt->bindValue(':approved_date1', $rowData['approved_date1']);
		//$stmt->bindValue(':approved_date2', $rowData['approved_date2']);
		//$stmt->bindValue(':approved_date3', $rowData['approved_date3']);
		//$stmt->bindValue(':approved_by', $rowData['approved_by']);
		$stmt->bindValue(':audit_plan_settings', $rowData['audit_plan_settings']);
		$stmt->bindValue(':audit_report_settings', $rowData['audit_report_settings']);
		$stmt->bindValue(':countryOfCompany', $rowData['countryOfCompany']);
		$stmt->bindValue(':addresses', $rowData['addresses']);
		$stmt->bindValue(':companyId', $rowData['companyId']);
		$stmt->bindValue(':reference', $rowData['reference']);
		$stmt->bindValue(':LeadAuditor', $rowData['LeadAuditor']);
		$stmt->bindValue(':coAuditor', $rowData['coAuditor']);
		$stmt->bindValue(':IslamicAffairsExpert', $rowData['IslamicAffairsExpert']);
		$stmt->bindValue(':Veterinary', $rowData['Veterinary']);
		$stmt->bindValue(':AccompanyingAuditorsOrExperts', $rowData['AccompanyingAuditorsOrExperts']);
		$stmt->bindValue(':HalalQualityControlOfficeCountry', $rowData['HalalQualityControlOfficeCountry']);
		//$stmt->bindValue(':CertificateNumber', $rowData['CertificateNumber']);
		//$stmt->bindValue(':CertificateIssueDate', $rowData['CertificateIssueDate']);
		//$stmt->bindValue(':CertificateExpiryDate', $rowData['CertificateExpiryDate']);
		$stmt->bindValue(':offerOffice', $rowData['offerOffice']);
		$stmt->execute();
		$newAppId = $dbo->lastInsertId();

		////////////////////////////////
		// Retrieve the existing documents with the given idapp
		//$sql = "SELECT * FROM tdocs WHERE idclient = :idclient AND idapp = :idapp AND category IN ('app', 'offer', 'soffer', 'declarations')";
		$sql = "SELECT * FROM tdocs WHERE idclient = :idclient AND idapp = :idapp AND (idparent IS NULL OR idparent = 0) AND deleted=0";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->bindValue(':idapp', $idapp);
		$stmt->execute();
		$existingDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$excludedCategories = ['invoice', 'popinv', 'audit', 'checklist', 'review', 'decision', 'pop', 'certificate', 'additional_items', 'invoiceai', 'popai', 'extension'];

		// Duplicate the documents with the newAppId
		foreach ($existingDocs as $doc) {

			if (!in_array($doc['category'], $excludedCategories)) {
				$sql = "INSERT INTO tdocs (idapp, idclient, idparent, iduser, title, category, filename, url, hostpath, gdrivepath, status, deleted, oid, signature)
						VALUES (:newAppId, :idclient, :idparent, :iduser, :title, :category, :filename, :url, :hostpath, :gdrivepath, :status, :deleted, :oid, :signature)";
				$stmt = $dbo->prepare($sql);
				$stmt->bindValue(':newAppId', $newAppId);
				$stmt->bindValue(':idclient', $idclient);
				$stmt->bindValue(':idparent', $doc['idparent']);
				$stmt->bindValue(':iduser', $doc['iduser']);
				$stmt->bindValue(':title', $doc['title']);
				$stmt->bindValue(':category', $doc['category']);
				$stmt->bindValue(':filename', $doc['filename']);
				$stmt->bindValue(':url', $doc['url']);
				$stmt->bindValue(':hostpath', $doc['hostpath']);
				$stmt->bindValue(':gdrivepath', $doc['gdrivepath']);
				$stmt->bindValue(':status', $doc['status']);
				$stmt->bindValue(':deleted', $doc['deleted']);
				$stmt->bindValue(':oid', $doc['id']);
				$stmt->bindValue(':signature', $doc['signature']);
				$stmt->execute();
			}
		}

		$sql = "UPDATE tdocs d1, tdocs d2 set d1.idparent=d2.id WHERE d1.idparent=d2.oid
		AND d1.idclient='".$idclient."' AND d2.idclient='".$idclient."' AND d1.idapp='".$newAppId."' AND d2.idapp='".$newAppId."'";
		$stmt = $dbo->prepare($sql);
		$stmt->execute();

		///////////////////////////////////
		$sql = "SELECT * FROM toffers WHERE idclient = :idclient AND idapp = :idapp";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->bindValue(':idapp', $idapp);
		$stmt->execute();
		$existingOffers = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Duplicate the documents with the newAppId
		foreach ($existingOffers as $offer) {
			$query = "INSERT INTO toffers (idclient, idapp, Service, Fee) VALUES (:idclient, :idapp, :Service, :Fee)";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
			$stmt->bindParam(':idapp', $newAppId, PDO::PARAM_STR);
			$stmt->bindParam(':Service', $offer["Service"], PDO::PARAM_STR);
			$stmt->bindParam(':Fee', $offer["Fee"], PDO::PARAM_STR);
			$stmt->execute();
		}
		/*
		$sql = "SELECT * FROM tauditreport WHERE idclient = :idclient AND idapp = :idapp";
		$stmt = $dbo->prepare($sql);
		$stmt->bindValue(':idclient', $idclient);
		$stmt->bindValue(':idapp', $idapp);
		$stmt->execute();
		$existingAuditReport = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Duplicate the documents with the newAppId
		foreach ($existingAuditReport as $auditReport) {
			$query = "INSERT INTO tauditreport (idclient, idapp, Type, Deviation, Reference, RootCause, Measure, Deadline, Status) VALUES (:idclient, :idapp, :Type, :Deviation, :Reference, :RootCause, :Measure, :Deadline, :Status)";
			$stmt = $dbo->prepare($query);
			$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
			$stmt->bindParam(':idapp', $newAppId, PDO::PARAM_STR);
			$stmt->bindParam(':Type', $auditReport["Type"], PDO::PARAM_STR);
			$stmt->bindParam(':Deviation', $auditReport["Deviation"], PDO::PARAM_STR);
			$stmt->bindParam(':Reference', $auditReport["Reference"], PDO::PARAM_STR);
			$stmt->bindParam(':RootCause', $auditReport["RootCause"], PDO::PARAM_STR);
			$stmt->bindParam(':Measure', $auditReport["Measure"], PDO::PARAM_STR);
			$stmt->bindParam(':Deadline', $auditReport["Deadline"], PDO::PARAM_STR);
			$stmt->bindParam(':Status', $auditReport["Status"], PDO::PARAM_STR);
			$stmt->execute();
		}
			*/

        echo json_encode(generateSuccessResponse('Cycle created.'));
    } catch (PDOException $e){
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

function sendNextActivityIdData($data) {
    try {
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        
        // Insert a new empty activity record
       $sql = "INSERT INTO ttrainer_activities 
                (created_by, created_at) 
                VALUES 
                (:created_by, NOW())";
        
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        
        // Get current user
        $myuser = cuser::singleton();
        $myuser->getUserData();
        
        // Bind parameters
        $stmt->bindValue(':created_by', $myuser->userdata['id']);
        
        if (!$stmt->execute()) {
            die(json_encode(generateErrorResponse("Adding new activity failed")));
        }
        
        // Return the new activity ID
        echo json_encode(generateSuccessResponse(array(
            "id" => $dbo->lastInsertId()
        )));
        
    } catch (PDOException $e) {
        echo json_encode(generateErrorResponse("Error: " . $e->getMessage()));
        die();
    }
}

function saveActivityData($activity) {
    global $adminEmailAddress;
    try {
        $myuser = cuser::singleton();
        $myuser->getUserData();
        $dbo = &$GLOBALS['dbo'];

        // Get existing activity data for comparison
        $sql = "SELECT * FROM ttrainer_activities WHERE id = :id";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':id', $activity['id']);
        $stmt->execute();
        $existingActivity = $stmt->fetch();

        // Prepare the update query
        $sql = "UPDATE ttrainer_activities SET 
                idauditor = :idauditor,
                company_name = :company_name,
                date_of_service = :date_of_service,
                service_type = :service_type,
                auditor_type = :auditor_type,
                invoice_number_inbound = :invoice_number_inbound,
                invoice_date_inbound = :invoice_date_inbound,
                invoice_inbound = :invoice_inbound,
                travel_invoices = :travel_invoices,
				travel_expenses = :travel_expenses,
                paid_on = :paid_on,
                invoice_number_outbound = :invoice_number_outbound,
                paid = :paid,
                training_request_form = :training_request_form,
                attendance_list = :attendance_list,
                customer_feedback_form = :customer_feedback_form,
                attendance_certificates = :attendance_certificates,
                note = :note
                WHERE id = :id";

        $stmt = $dbo->prepare($sql);

        // Format dates for SQL (Y-m-d)
        $dateOfService = !empty($activity['date_of_service']) ? date("Y-m-d", strtotime($activity['date_of_service'])) : null;
        $invoiceDateInbound = !empty($activity['invoice_date_inbound']) ? date("Y-m-d", strtotime($activity['invoice_date_inbound'])) : null;
        $paidOn = !empty($activity['paid_on']) ? date("Y-m-d", strtotime($activity['paid_on'])) : null;

        // Bind parameters
        $stmt->bindValue(':idauditor', $activity['idauditor']);
        $stmt->bindValue(':company_name', $activity['company_name']);
        $stmt->bindValue(':date_of_service', $dateOfService);
        $stmt->bindValue(':service_type', $activity['service_type']);
        $stmt->bindValue(':auditor_type', $activity['auditor_type'] ?? 'External'); // Default to External if not set		
        $stmt->bindValue(':invoice_number_inbound', $activity['invoice_number_inbound']);
        $stmt->bindValue(':invoice_date_inbound', $invoiceDateInbound);
        $stmt->bindValue(':invoice_inbound', $activity['invoice_inbound'] ?? '');
		$stmt->bindValue(':travel_invoices', $activity['travel_invoices'] ?? '');
		$stmt->bindValue(':travel_expenses', $activity['travel_expenses'] ?? '');
        $stmt->bindValue(':paid_on', $paidOn);
        $stmt->bindValue(':invoice_number_outbound', $activity['invoice_number_outbound']);
        $stmt->bindValue(':paid', $activity['paid']);
        $stmt->bindValue(':training_request_form', $activity['training_request_form']);
        $stmt->bindValue(':attendance_list', $activity['attendance_list']);
        $stmt->bindValue(':customer_feedback_form', $activity['customer_feedback_form']);
        $stmt->bindValue(':attendance_certificates', $activity['attendance_certificates']);
        $stmt->bindValue(':note', $activity['note']);
        $stmt->bindValue(':id', $activity['id']);

        // Execute the update
        $stmt->execute();

        echo json_encode(generateSuccessResponse('Activity was updated'));
    } catch (PDOException $e) {
        echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
        die();
    }
}

/* Other functions */

function validateUserId($uid){
	return $uid == $GLOBALS['userId'] ? true : false;
}

function generateSuccessResponse($data = null){
	return array("status"=>"1","statusDescription"=>"SUCCESS","data"=>$data);
}

function generateErrorResponse($description=""){
	return array("status"=>"0","statusDescription"=>$description);
}


// ----------- Incoming data processing ------------

function processGetRequests() {
	//$requestData = array("uid"=>$_GET['uid']);
	/*
	if (!validateRequest($requestData)) {
		die();
	}
	*/
	switch ($_GET['rtype']) {
		case 'clients':
			sendClientsData();
			break;
		/** Products */
		case 'nextProdId':
			sendNextProdIdData($_GET['data']);
			break;
		case 'ingredientsForProduct':
			sendIngredientsForProductData($_GET['data']);
			break;
		/** Ingredients */
		case 'nextIngredId':
			sendNextIngredIdData($_GET['data']);
			break;
		case 'ingredientsForIngredient':
			sendIngredientsForIngredientData($_GET['data']);
			break;
		/** QM */
		case 'nextQMId':
			sendNextQMIdData($_GET['data']);
			break;
		/** Audit */
		case 'nextAuditId':
			sendNextAuditIdData();
			break;
		/** Admin */
		case 'nextAdminId':
			sendNextAdminIdData();
			break;
		case 'nextCompanyId':
			sendNextCompanyIdData();
			break;
			//  Dashboard
        case 'dashboardData':
            sendDashboardData($_GET['data']);
            break;
		/** Groups */
		case 'nextGroupId':
			sendNextGroupIdData();
			break;
		/** Activities */
		case 'nextActivityId':
			sendNextActivityIdData($_GET['data']);
			break;			
		case 'importClientsFromExcel':
			importClientsFromExcel();
			break;
			
	}
}

function processPostRequests() {
    $requestData = array("uid"=>$_POST['uid']);
	switch ($_POST['rtype']) {
		case 'updateAppState':
			updateAppState($_POST['data']);
			break;
		case 'sendClientLogin':
			sendClientLogin($_POST['data']);
			break;
		case 'deleteOffer':
			deleteOffer($_POST['data']);
			break;
		case 'deleteServiceList':
			deleteServiceList($_POST['data']);
			break;
		case 'changeOfferOffice':
			changeOfferOffice($_POST['data']);
			break;
		case 'changeIngredientsLimit':
			changeIngredientsLimit($_POST['data']);
			break;
		case 'changeProductsLimit':
			changeProductsLimit($_POST['data']);
			break;		
		case 'saveOffer':
			saveOffer($_POST['data']);
			break;
		case 'getOfferData':
			getOfferData($_POST['data']);
			break;
		case 'saveCertificateData':
			saveCertificateData($_POST['data']);
			break;
		case 'saveOfferService':
			saveOfferService($_POST['data']);
			break;
		case 'sendAuditPlan':
			sendAuditPlan($_POST['data']);
			break;
		case 'getServices':
			getServices();
			break;
		case 'delete_event':
			delete_event($_POST['data']);
			break;
		case 'save_event':
			save_event($_POST['data']);
			break;
		case 'saveDeviation':
			saveDeviation($_POST['data']);
			break;
		case 'saveAuditReport':
			saveAuditReport($_POST['data']);
			break;
		case 'sendAuditReport':
			sendAuditReport($_POST['data']);
			break;
		case 'getDeviations':
			getDeviations();
			break;
		case 'getDeviationData':
			getDeviationData($_POST['data']);
			break;
		case 'updateDeviationStatus':
			updateDeviationStatus($_POST['data']);
			break;
		case 'updateImplementationStatus':
			updateImplementationStatus($_POST['data']);
			break;
		case 'deleteDeviation':
			deleteDeviation($_POST['data']);
			break;
		case 'saveMeasureData':
			saveMeasureData($_POST['data']);
			break;
		case 'deleteDeviationDoc':
			deleteDeviationDoc($_POST['data']);
			break;	
		case 'getDisabledDates':
			getDisabledDates();
			break;
		case 'saveAuditDates':
			saveAuditDates($_POST['data']);
			break;
		case 'saveAuditPlanSettings':
			saveAuditPlanSettings();
			break;
		case 'saveAuditReportSettings':
			saveAuditReportSettings();
			break;
		case 'approveAuditDates':
			approveAuditDates($_POST['data']);
			break;
		case 'getAppData':
			getAppData($_POST['data']);
			break;
		/* Login */
		case 'login':
			login($_POST['data']);
			break;
		case 'register':
			register($_POST['data']);
			break;
		case 'logout':
			logout();
			break;
		/* Product */
		case 'saveProduct':
			saveProductData($_POST['data']);
			break;
		case 'addProduct':
			addProductData($_POST['data']);
			break;
		case 'removeProduct':
			removeProductData($_POST['data']);
			break;
		case 'markDeletedProduct':
			markDeletedProductData($_POST['data']);
			break;
		case 'sendAdditionalItemsApplicationRequest':
            additionalItemsApplicationData($_POST['data']);
			break;
		case 'sendProductsExcelReportRequest':
            productsExcelReportData($_POST['data']);
			break;
		case 'sendConfirmedProductsExcelReportRequest': // change?
			confirmedProductsExcelReportData($_POST['data']);
			break;
		case 'sendAllProductsExcelReportRequest': // change?
			allProductsExcelReportData($_POST['data']);
			break;
		case 'sendAllClientsExcelReportRequest': // change?
			allClientsExcelReportData($_POST['data']);
			break;	
		/* Ingredient */
		case 'deletePAIngredient':
			deletePAIngredientData($_POST['data']);
			break;
		case 'savePAIngredient':
			savePAIngredientData($_POST['data']);
			break;
		case 'saveIngredient':
			saveIngredientData($_POST['data']);
			break;
        case 'addIngredientFiles':
            addIngredientFiles($_POST);
            break;
        case 'addProductFiles':
            addProductFiles($_POST);
            break;
        case 'addActivityFiles':
            addActivityFiles($_POST);
            break;
		case 'removeIngredient':
			removeIngredientData($_POST['data']);
			break;
		case 'markDeletedIngredient':
			markDeletedIngredientData($_POST['data']);
			break;
		case 'markDeletedActivity':
			markDeletedActivityData($_POST['data']);
			break;
		case 'removeCompany':
			removeCompanyData($_POST['data']);
			break;
		case 'changeConformity':
			changeConformityData($_POST['data']);
			break;
		case 'assignTaskForIngredient':
			assignTaskForIngredientData($_POST['data']);
			break;
		case 'deleteTask':
			deleteTaskData($_POST['data']);
			break;
		case 'sendIngredientsSupplierQuestionsRequest':
			ingredientsSupplierQuestionsData($_POST['data']);
			break;
		case 'sendIngredientsCertificatesRequest':
			ingredientsCertificatesData($_POST['data']);
			break;
    	case 'sendIngredientsExcelReportRequest':
			ingredientsExcelReportData($_POST['data']);
		    break;
    	case 'sendAllIngredientsExcelReportRequest':
			ingredientsAllExcelReportData($_POST['data']);
	  		break;
		case 'sendAllTasksExcelReportRequest':
			tasksAllExcelReportData($_POST['data']);
			break;
		case 'sendAllTasksToolTipRequest':
			tasksAllToolTipData($_POST['data']);
			break;
		case 'restoreAdmin':
      		restoreAdminData($_POST['data']);
		break;
		case 'restoreIngred':
			restoreIngredData($_POST['data']);
	  break;

    	case 'restoreProd':
        	restoreProdData($_POST['data']);
  		break;
		/* QM */
    case 'saveQM':
      saveQMData($_POST['data']);
      break;
    case 'saveQMOneCol':
      saveQMDataOneCol($_POST['data']);
      break;
		case 'removeQM':
			removeQMData($_POST['data']);
			break;
		case 'markDeletedQM':
			markDeletedQMData($_POST['data']);
			break;
		/* Audit */
		case 'saveAudit':
			saveAuditData($_POST['data']);
			break;
		case 'removeAudit':
			removeAuditData($_POST['data']);
			break;
		/* Admin */
		case 'saveFacility':
			saveFacilityData($_POST['data']);
			break;
		case 'saveAdmin':
			saveAdminData($_POST['data']);
			break;
		case 'saveCompany':
			saveCompanyData($_POST['data']);
			break;
		case 'getAdmin':
			getAdminData($_POST['id']);
			break;
		case 'getFacility':
			getFacilityData($_POST['id']);
			break;	
		case 'getCompany':
			getCompanyData($_POST['id']);
			break;
		case 'removeAdmin':
			removeAdminData($_POST['data']);
			break;
        case 'changeIsClient':
            changeIsClientData($_POST['data']);
            break;
        case 'changeApplication':
            changeApplicationData($_POST['data']);
            break;
        case 'changeClients':
            changeClientsData($_POST['data']);
            break;
        case 'changeAudit':
            changeAuditData($_POST['data']);
            break;
        case 'changeCanAdmin':
            changeCanAdminData($_POST['data']);
            break;
		case 'unblockUser':
            unblockUserData($_POST['data']);
			break;
        /* Application */
        case 'saveApplication':
            saveApplicationData($_POST['data']);
            break;
        case 'completeApplication':
            completeApplicationData($_POST['data']);
            break;
        case 'confirmApplication':
            confirmApplicationData($_POST['data']);
            break;
        case 'cancelApplication':
            cancelApplicationData($_POST['data']);
            break;
        case 'stopApplicationNotification':
            stopApplicationNotificationData($_POST['data']);
            break;
        case 'skipApplication':
            skipApplicationData($_POST['data']);
            break;
        /* Dashboard */
        case 'addCertificate':
            addCertificateData($_POST['data']);
            break;
        case 'editCertificate':
            editCertificateData($_POST['data']);
            break;
        case 'removeCertificate':
            removeCertificateData($_POST['data']);
            break;
        case 'addFile':
            addFileData($_POST['data']);
            break;
        case 'editFileStatus':
            editFileStatusData($_POST['data']);
            break;
        case 'sendEmailMessage':
            sendEmailMessage($_POST['data']);
            break;
        case 'completeTask':
            completeTask($_POST['data']);
            break;
        case 'undoneTask':
            undoneTask($_POST['data']);
            break;
        case 'confirmTask':
            confirmTask($_POST['data']);
            break;
		case 'addTask':
			addTaskData($_POST['data']);
			break;
		case 'updateTask':
			updateTaskData($_POST['data']);
			break;
		case 'getTaskDetails':
			getTaskDetails($_POST['data']);
			break;
        case 'confirmClientAction':
            confirmClientAction($_POST['data']);
            break;
        case 'addClientAction':
            addClientAction($_POST['data']);
            break;
		case 'saveCertCycle':
			saveCertCycleData($_POST['data']);
			break;
		case 'startNewCertCycle':
			startNewCertCycleData($_POST['data']);
			break;
			// case 'bulkIngredients':
			// 	bulkIngredients($_POST['data']);
			// 	break;
		case 'createTask':
			createTask($_POST);
			break;	
		case 'createTicket':
			createTicket($_POST);
			break;
		case 'postReply':
			postReply($_POST['data']);
			break;
		case 'closeTicket':
			closeTicket($_POST['data']);
			break;		
		case 'closeTask':
			closeTask($_POST['data']);
			break;	
		case 'createCustomerService':
			createCustomerService($_POST);
			break;
		case 'closeCustomerService':
			closeCustomerService($_POST['data']);
			break;	
		case 'getNewTicketsCount':
			getNewTicketsCount();
			break;
		case 'importClientsFromExcel':
			importClientsFromExcel($_POST['data']);
			break;
		case 'saveActivity':
			saveActivityData($_POST['data']);
			break;

	}
}

$db = acsessDb :: singleton();
$dbo =  $db->connect();

cors();

switch ($_SERVER['REQUEST_METHOD']) {
	case "POST":
		processPostRequests();
		break;
	case "GET":
		processGetRequests();
}

function random_string($length) {
    $key = '';
    $keys = array_merge(range(0, 9), range('a', 'z'));

    for ($i = 0; $i < $length; $i++) {
        $key .= $keys[array_rand($keys)];
    }

    return $key;
}
