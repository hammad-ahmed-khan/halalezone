<?php
@session_start();
include_once "../../config/config.php";
include_once "../../classes/users.php";
include_once "../../includes/func.php";
include_once "../../notifications/notifyfuncs.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$myuser = cuser::singleton();
	$myuser->getUserData();
		
	$error = "";
	$category = "popinv";
	$idapp = $_POST["idapp"];
	$idclient = $_POST["idclient"];

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$user = $stmt->fetch();
	
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
	
	$Title = 'Proof of Payment';
	
	$decode = file_get_contents( __DIR__ ."/../../config.json");
	$config=json_decode($decode, TRUE);
	$client = $user["name"]. ' ('.$user["id"].')';
	$ext = pathinfo($_FILES['filedata']['name'], PATHINFO_EXTENSION);
	$errors = '';
	
	if (trim($Title) == "") {
		$errors .= "<li>Title is required.</li>";
	}
	if (strtolower($ext) != "pdf" || $_FILES["filedata"]["type"] != "application/pdf") {
		$errors .= "<li>Invalid file.</li>";
	}
	
	if ($errors == "") { 

		$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$client."/application/popinv/";			
		$options['upload_dir']= __DIR__ ."/../../".$hostPath;
		
		mkdir($options['upload_dir'], 0777, true);

		$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, hostpath, category, status) 
							VALUES  (:idapp, :idclient, :iduser, :title, :hostpath, :category, 1)";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);  
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);  
		$stmt->bindParam(':iduser', $myuser->userdata['id'], PDO::PARAM_STR);  
		$stmt->bindParam(':title', $Title, PDO::PARAM_STR);   
		$stmt->bindParam(':hostpath', $hostPath, PDO::PARAM_STR);   
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);   
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

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$ownerEmailAddress = "halal.ezone@gmail.com";
					$fromEmailAddress = "noreply@halal-e.zone";
					
					//sendEmailWithAttach
					$body['name'] = 'Halal e-Zone';
					$body['email'] =  $fromEmailAddress;
					$body['to'] = $adminEmailAddress;
					
					$body['attachhostpath'] = $dest_path;
					$body['attach'] = $filename;
					
					// sending notification
					$body['subject'] = "Halal e-Zone - ".$Title . " - " . $user["name"];
					$body['header'] = "";
					$body['message'] = "<p>Dear Admin!</p>";
					$body['message'] .= "<p><strong>".$user["name"]."</strong> has uploaded ".$Title.". Please find attached to this email.</p>
					<p>Regards,<br/>Halal e-Zone</p>
					";					
					sendEmailWithAttach($body);

					insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], 'Proof of payment uploaded');
 /*
					$state = "declarations";

				$statusOptions = ['offer', 'soffer', 'app', 'dates', 'invoice', 'popinv', 'declarations', 'audit', 'report', 'pop', 'certificate', 'additional_items', 'popai', 'extension'];
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
						$attach = '../../files/docs/'.$filetoattach;
						$ext = "pdf";
						$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$user['name']." (".$idclient.")/application/questionnaire/";
						$absolutePath = __DIR__ ."/../../".$hostPath;
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
						$filetoattach = "F0451 Pork-Free Declaration.pdf";
						$attach = '../files/docs/'.$filetoattach;
						$ext = "pdf";
						$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$user['name']." (".$idclient.")/application/declarations/";
						$absolutePath = __DIR__ ."/../../".$hostPath;
	
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
						$filetoattach = "F0453 Alcohol Free Production Line Declaration.pdf";
						$attach = '../files/docs/'.$filetoattach;
						$ext = "pdf";
						$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$user['name']." (".$idclient.")/application/declarations/";
						$absolutePath = __DIR__ ."/../../".$hostPath;
	
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
							$attach = '../files/docs/'.$filetoattach;
							$ext = "pdf";
							$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$user['name']." (".$idclient.")/application/declarations/";
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
			*/
	}

	if ($errors != "")  {
	
		http_response_code(403);
		echo "<ul>$errors</ul>";
	}	
}
catch (PDOException $e) {
		http_response_code(403);
    echo 'Database error: '.$e->getMessage();
}

?>