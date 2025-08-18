<?php
@session_start();
include_once "../../config/config.php";
include_once "../../classes/users.php";
include_once "../../includes/func.php";
include_once "../../notifications/notifyfuncs.php";

//error_reporting(E_ALL); 
//ini_set('display_errors', 1);

//try {

	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$myuser = cuser::singleton();
	$myuser->getUserData();
		
	$error = "";
	$category = "soffer";
	$idapp = $_POST["idapp"];
	$idclient = $_POST["idclient"];
	$Comments = $_POST["Comments"];

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$user = $stmt->fetch();

	$sql = "SELECT * FROM tapplications WHERE idclient=:idclient AND id=:idapp";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->bindValue(':idapp', $idapp);
	$stmt->execute();
	$appData = $stmt->fetch(PDO::FETCH_ASSOC);	

	if (isset($_POST["Delete"])) { 
		$id = $_POST["Delete"];
		$query = "UPDATE tdocs SET deleted =1 WHERE id=:id AND idapp=:idapp AND idclient=:idclient AND category=:category";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':id', $id, PDO::PARAM_STR);  
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);  
		$stmt->bindParam(':idclient', $user["id"], PDO::PARAM_STR);  
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);   
		$stmt->execute();
		$data = array('success' => 1);
		die(json_encode($data));
	}
	
	$decode = file_get_contents( __DIR__ ."/../../config.json");
	$config=json_decode($decode, TRUE);
	$client = $user["name"]. ' ('.$user["id"].')';
	$ext = pathinfo($_FILES['filedata']['name'], PATHINFO_EXTENSION);
	
	if (strtolower($ext) != "pdf" || $_FILES["filedata"]["type"] != "application/pdf") {
		$error = "Invalid file.";
	}
	
	if ($error == "") {

		$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$client."/application/soffer/";			
		$options['upload_dir']= $config["basePath"].$hostPath;
		if (!file_exists($options['upload_dir'])) {
			mkdir($options['upload_dir'], 0777, true);
		}

		/*
		mkdir($options['upload_dir'], 0777, true);
		$query = "UPDATE tdocs SET deleted =1 WHERE idapp=:idapp AND idclient=:idclient AND category=:category";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);  
		$stmt->bindParam(':idclient', $user["id"], PDO::PARAM_STR);  
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);   
		$stmt->execute();
		*/

		$query = "INSERT INTO tdocs (idapp, idclient, iduser, category, hostpath, status) 
		VALUES (:idapp, :idclient, :iduser, :category, :hostpath, 1)";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);  
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);  
		$stmt->bindParam(':iduser', $myuser->userdata['id'], PDO::PARAM_STR);  
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);   
		$stmt->bindParam(':hostpath', $hostPath, PDO::PARAM_STR);   
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
		
		move_uploaded_file($source_path, $dest_path);

		$ownerEmailAddress = "halal.ezone@gmail.com";
		$fromEmailAddress = "noreply@halal-e.zone";
		
		$body = [];
		//sendEmailWithAttach
		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $adminEmailAddress; // Will be changed to admin email
		
		$body['attachhostpath'] = $dest_path;
		$body['attach'] = $filename;
		
		// sending notification
		$body['subject'] = "Halal e-Zone - Signed Offer  Form - " . $user["name"];
		$body['header'] = "";
		$body['message'] .= "Dear Admin,<br /><br />";			
		$body['message'] .= "<strong>".$user["name"]."</strong> has  uploaded the signed Offer form.";
		$body['message'] .= "<br /><br />";
		$body['message'] .= "Please find attached.";
		$body['message'] .= "<br /><br />";			
		$body['message'] .= "Kind Regards";
		
		sendEmailWithAttach($body);

		if ($appData["state"] == "soffer") {

			$ownerEmailAddress = "halal.ezone@gmail.com";
			$fromEmailAddress = "noreply@halal-e.zone";

			$decode = file_get_contents( __DIR__ ."/../../config.json");
			$config=json_decode($decode, TRUE);
			
			$attach = 'F0422 HQC Application Form.pdf';
			$ext = "pdf";
			//$hostPath =  $config["basePath"].$config['filesfolder']."/".$config['clientsfolder']."/".$data['name']." (".$idclient.")/application/";
			$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$user['name']." (".$idclient.")/application/application/";			
 			$absolutePath = $config["basePath"]. $hostPath;
			if (!file_exists($absolutePath)) {
				mkdir($absolutePath, 0777, true);
			}

 			$filename = str_replace(".".$ext, '_'.$idapp.'.'.$ext, $attach);
			$dest_path = $absolutePath . $filename;
			
			$fields = array(
				'Company Name' => $user['name'],
				'Company Representative' => $user['contact_person'],
				'Date of Request' => date('d/m/Y'),
				'Official Company Names' => $user['name'],
				'Contact person Name Email Telephone number' => $user['contact_person'] . ', '. $user['email'] . ', '. $user['phone'],
				'Text1' => $user['address'],
				'Example Ltd SA SPA BV GMBH AS NVVATTaxBTW Number' => $user['vat']);		
			
			//print_r($fields);
			saveApplicationPDF1($fields, $config["basePath"].'files/docs/'.$attach, $dest_path);

			//sendEmailWithAttach
			$body['name'] = 'Halal e-Zone';
			$body['email'] =  $fromEmailAddress;
			$body['to'] = $user['email'];

			$body['attachhostpath'] = $dest_path;
			$body['attach'] = $filename;

			$appToken = $user['app_token'];

			// sending notification
			$body['subject'] = "Halal e-Zone - Application From - ".$user['name'];
			$body['header'] = "";
			$body['message'] = 'Thank you for uploading the signed offer form. We have attached the "Application Form" for you to download, fill out, and upload using the link provided below. If you have any questions or require any assistance, please do not hesitate to contact us.';
			$body['message'] .= "<br /><br />";
			$body['message'] .= '<a href="http://halal-e.zone/upload?code='.urlencode($appToken).'">http://halal-e.zone/upload?code='.$appToken.'</a><br/><br/>';
			$body['message'] .= "Kind Regards,";
			$body['message'] .= "<br/>";
			$body['message'] .= "Your HQC supporting Team";

			sendEmailWithAttach($body);

			insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'],  'Signed offer uploaded');
			
			if (empty($user["last_login_sent"])) {
				sendClientLogin($_POST);
			}	

			
			$state = "declarations";
			/*
			$statusOptions = ['offer', 'soffer', 'app', 'declarations', 'dates', 'audit', 'report', 'pop', 'certificate', 'additional_items', 'popai', 'extension'];
			$newIndex = array_search($state, $statusOptions);

			$query = "SELECT *
			FROM tapplications	
			WHERE id='".$idapp."' AND idclient='".$idclient."'";
			$stmt = $dbo->prepare($query);
			$stmt->execute();
			$appData = $stmt->fetch(PDO::FETCH_ASSOC);
			$currentState = $appData["state"];
			$currentIndex = array_search($currentState, $statusOptions);
			*/

			if ($appData["state"] == "soffer") { 	

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
				$body['body'] .= "Please go to Halal e-Zone portal/ application tab and select 3 audit date proposals for the on-site audit for your facility.<br /><br />";
				$body['body'] .= "Our team will confirm one of the proposed dates as soon as possible.<br /><br />";
				$body['body'] .= "Once date is confirmed you'll receive your audit plan, and we stay at your disposal for any assistance or clarification.<br /><br />";
				$body['body'] .= "Kind Regards<br/>";
				$body['body'] .= "Your HQC Team";
				 sendEmail($body);


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

				 $myuser = cuser::singleton();
				 $myuser->getUserData();
				 $iduser = $myuser->userdata['id'];
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
					 $filetoattach = "Questionnaire_Customer_EN.pdf";
					 
					 if ($industry == "Meat Processing") {
						 $filetoattach = "customer_questionnaire_meat_processing.pdf";
					 }
					 else if ($industry == "Slaughter Houses") {
						 $filetoattach = "customer_questionnaire_slaughtering_plants.pdf";
					 }
					 else  {
						 $filetoattach = "customer_questionnaire_manufacturing.pdf";
					 }
					
					 $ext = "pdf";
					 $hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".getClientInfo($idclient)."/application/questionnaire/";
					 $absolutePath = $config['basePath'].$hostPath;
					 if (!file_exists($absolutePath)) {
						 mkdir($absolutePath, 0777, true);
					 }
					 $attach = $config['basePath'].'files/docs/'.$filetoattach;
 
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
					 $body['message'] .= '<p>Please find attached to this email our Client Questionnaire. You are kindly requested to download, fill and upload the filled documents on your Halal e-Zone account/Applications/client questionnaire-free form declarations.</p>
					 <p>Feel free to contact us for any assistance or clarification.</p>
					 <p>Regards</p>
					 <p>Halal e-Zone</p>
					 ';
 
					sendEmailWithAttach($body);
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
					 $filetoattach = "Pork-Free-Declaration.pdf";
					 $attach = $config['basePath'].'files/docs/'.$filetoattach;
					 $ext = "pdf";
					 $hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".getClientInfo($idclient)."/application/declarations/";
					 $absolutePath = $config['basePath'].$hostPath;
 
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
					 $body['message'] .= '<p>Please find attached to this email our '.$title.'. You are kindly requested to download, fill and upload the filled documents on your Halal e-Zone account/Applications/client questionnaire-free form declarations.</p>
					 <p>Feel free to contact us for any assistance or clarification.</p>
					 <p>Regards</p>
					 <p>Halal e-Zone</p>
					 ';
 
					sendEmailWithAttach($body);
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
					 $filetoattach = "Alcohol-Free-Declaration.pdf";
					 $attach = $config['basePath'].'files/docs/'.$filetoattach;
					 $ext = "pdf";
					 $hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".getClientInfo($idclient)."/application/declarations/";
					 $absolutePath = $config['basePath'].$hostPath;
 
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
					 $body['message'] .= '<p>Please find attached to this email our '.$title.'. You are kindly requested to download, fill and upload the filled documents on your Halal e-Zone account/Applications/client questionnaire-free form declarations.</p>
					 <p>Feel free to contact us for any assistance or clarification.</p>
					 <p>Regards</p>
					 <p>Halal e-Zone</p>
					 ';
 
 					sendEmailWithAttach($body);
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
						 $attach = $config['basePath'].'files/docs/'.$filetoattach;
						 $ext = "pdf";
						 $hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".getClientInfo($idclient)."/application/declarations/";
						 $absolutePath = $config['basePath'].$hostPath;
 
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
						 $body['message'] .= '<p>Please find attached to this email our '.$title.'. You are kindly requested to download, fill and upload the filled documents on your Halal e-Zone account/Applications/client questionnaire-free form declarations.</p>
						 <p>Feel free to contact us for any assistance or clarification.</p>
						 <p>Regards</p>
						 <p>Halal e-Zone</p>	
						 ';
						 
						sendEmailWithAttach($body);
					 }
				 }
	  
				$query = "UPDATE tapplications SET state=:state WHERE idclient=:idclient AND id=:idapp";
				$stmt = $dbo->prepare($query);
				$stmt->bindParam(':state', $state, PDO::PARAM_STR);
				$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
				$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
				$stmt->execute();
			}
		}
	}

	if ($error != "")  {
	
		http_response_code(403);
		echo $error;
	}	
//}
//catch (PDOException $e) {
//		http_response_code(403);
//    echo 'Database error: '.$e->getMessage();
//}

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
		$errors .= '<li>No record found, signed offer has not been uploaded.</li>';
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

		$decode = file_get_contents( __DIR__ ."/../../config.json");
		$config=json_decode($decode, TRUE);
		$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$user['name']." (".$idclient.")/application/";
		$absolutePath = $config["basePath"].$hostPath;

		mkdir($absolutePath, 0777, true);

		$offerOffice = trim($appData["offerOffice"]);
		if ( $offerOffice == "" ) {
			$offerOffice = "AT";
		}

		if ($offerOffice == "HU") {
			$attach = $config["basePath"].'files/docs/F0-01 new customer registration_HU.pdf';
		}
		else {
			$attach = $config["basePath"].'files/docs/F0-01 new customer registration.pdf';
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
		$body['subject'] = "HQC_".$offerOffice." new customer registration";
		$body['header'] = "";
		$body['message'] = "Salam Mustafa!<br /><br />";
		$body['message'] .= "How are you doing?<br /><br />";
		$body['message'] .= "We kindly would like to inform you of having a new customer. The registration form is attached.<br /><br />";
		$body['message'] .= "Wsalam,<br />";
		$body['message'] .= "Mona";

		sendEmailWithAttach($body);
		///////////////////////////////////////////////////////////////

		$data = ['name' => $user["name"],
				 'username' => $username,
				 'password' => $password,
				];

		$attach = $config["basePath"].'files/docs/05access data Halal eZone_'.$offerOffice.'.pdf';
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

//		echo json_encode(generateSuccessResponse(array("id" => $idclient, "last_login_sent" => date('d/m/Y h:i a'), 'errorInfo' => $dbo->errorInfo())));
//		exit;
	}

//		echo json_encode(generateSuccessResponse(array("errors" => "<ul>$errors</ul>", 'errorInfo' => $dbo->errorInfo())));
}

?>