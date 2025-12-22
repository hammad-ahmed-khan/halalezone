<?php
@session_start();
include_once "../../config/config.php";
include_once "../../classes/users.php";
include_once "../../includes/func.php";
include_once "../../notifications/notifyfuncs.php";
include_once "../../reports/reports.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$myuser = cuser::singleton();
	$myuser->getUserData();
		
	$error = "";
	$category = "app";
	$idclient = $_POST["idclient"];
	$idapp = $_POST["idapp"];
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
		
		$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$client."/application/offer/";			
		$options['upload_dir']= __DIR__ ."/../../".$hostPath;
		
		mkdir($options['upload_dir'], 0777, true);
		/*
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


		///////////////////////////////////////////////////////////////
	/*
					$body['subject'] = "Welcome to Halal-eZone";
					$body['header'] = "Welcome to Halal-eZone";
					
					$body['body'] = 'Thanks for your interest on our nearly worldwide accepted and recognized Halal certification.';
					$body['body'] .= "<br /><br />";			
					$body['body'] .= 'We confirm the reception of your application and will send you our offer and contact you as soon as possible.';
					$body['body'] .= "<br /><br />";			
					$body['body'] .= 'This email sums up your advantage with HQC Halal certification:';
					$body['body'] .= "<br /><br />";			
					$body['body'] .= '1- HQC offers a Halal certificate with international Halal accreditation including MUI Indonesia, Jakim Malaysia, SFAD KSA, ESMA UAE, HAC Sri Lanka, CICOT Thailand as well as in almost all Islamic countries in Asia and Africa and the Muslim Communities in Europe. Our certificate helps you in getting international recognition that is widely accepted nearly worldwide while the certification by other bodies is only accepted in limited countries.';
					$body['body'] .= "<br /><br />";			
					$body['body'] .= '2- Our team consists of professionals working with SOP’s for every Halal certification step and ready to assist you. This saves your time and give you a clear overview about the certification process. Feel free to profit from our email and phone support and contact us by email or phone to answer any questions you may have about Halal certification.';
					$body['body'] .= "<br />";			
					$body['body'] .= 'email: office@hqc.at';
					$body['body'] .= "<br />";			
					$body['body'] .= 'T: +43 677 62 33 22 44';
					$body['body'] .= "<br /><br />";			
					$body['body'] .= '3- IT solution and time factor
					HQC works with Halal e-Zone which is a tailored IT solution for Halal certification process. With this highly intelligent IT solution and our professional, fast phone and e-mail support, HQC  intends to significantly accelerate the certification process so that you can better use your time for your core tasks and answer your customer inquiries even faster.';
					$body['body'] .= "<br /><br />";			
					$body['body'] .= '4- Offer: HQC offers a very transparent and competitive cost structure depending on the number of products and ingredients you would like to certify. You\'ll receive our offer as soon as possible for your review and approval.';
					$body['body'] .= "<br /><br />";			
					$body['body'] .= 'We\'ll be very glad to welcome you soon as a part of our community and assist you on your Halal certification project.';
					$body['body'] .= "<br /><br />";			
					$body['body'] .= 'Best regards';
					sendEmail($body);
					*/
					insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], 'Signed application uploaded');

		if ($appData["state"] == "app") {

				$myuser = cuser::singleton();
				$myuser->getUserData();
				$iduser = $myuser->userdata['id'];
				//$errors = "Unknown error has";

				//////////////////////////////////////////////////////////////
				/*
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
				*/

				$state = "declarations";

				$statusOptions = ['offer', 'soffer', 'app', 'dates', 'invoice', 'popinv', 'declarations', 'audit', 'report', 'pop', 'certificate', 'additional_items', 'popai', 'extension'];
				$newIndex = array_search($state, $statusOptions);

				/*
				$query = "SELECT *
				FROM tapplications	
				WHERE id='".$idapp."' AND idclient='".$idclient."'";
				$stmt = $dbo->prepare($query);
				$stmt->execute();
				$appData = $stmt->fetch(PDO::FETCH_ASSOC);
				$currentState = $appData["state"];
				$currentIndex = array_search($currentState, $statusOptions);
				*/

				if ($appData["state"] == "app") {
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
					
					$query = "UPDATE tapplications SET state=:state WHERE idclient=:idclient AND id=:idapp";
					$stmt = $dbo->prepare($query);
					$stmt->bindParam(':state', $state, PDO::PARAM_STR);
					$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
					$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
					$stmt->execute();
					*/
				}
		}		
		///////////////////////////////////////////////////////////////
	}

	if ($error != "")  {
	
		http_response_code(403);
		echo $error;
	}	
}
catch (PDOException $e) {
		http_response_code(403);
    echo 'Database error: '.$e->getMessage();
}

?>