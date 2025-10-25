<?php
@session_start();
include_once "../../config/config.php";
include_once "../../classes/users.php";
include_once "../../includes/func.php";
include_once "../../notifications/notifyfuncs.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$myuser = cuser::singleton();
	$myuser->getUserData();
	
	$data = [];
		
	$error = "";
	$category = "offer";
	$idclient = $_POST["idclient"];
	$idapp = $_POST["idapp"];

	$sql = "SELECT * FROM tusers WHERE id=:idclient";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	$data['user'] = $stmt->fetch(PDO::FETCH_ASSOC);

	if (isset($_POST["Delete"])) {
		$id = $_POST["Delete"];
		$query = "UPDATE tdocs SET deleted =1 WHERE id=:id AND idapp=:idapp AND idclient=:idclient AND category=:category";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':id', $id, PDO::PARAM_STR);  
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);  
		$stmt->bindParam(':idclient', $data['user']["id"], PDO::PARAM_STR);  
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);   
		$stmt->execute();
		$data = array('success' => 1);
		die(json_encode($data));
	}

	$sql = "SELECT * FROM tapplications WHERE idclient=:idclient AND id=:idapp";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->bindValue(':idapp', $idapp);
	$stmt->execute();
	$data['app'] = $stmt->fetch(PDO::FETCH_ASSOC);
	
	$query = "SELECT *
	FROM toffers	
	WHERE idclient='".$idclient."' AND idapp='".$idapp."'";
	$stmt = $dbo->prepare($query);
	$stmt->execute();
	$data['offer'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$decode = file_get_contents( __DIR__ ."/../../config.json");
	$config=json_decode($decode, TRUE);
	$client = $data['user']["name"]. ' ('.$data['user']["id"].')';

	$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$client."/application/offer/";
	$absolutePath = $config['basePath'].$hostPath;

	$query = "INSERT INTO tdocs (idapp, idclient, iduser, category, hostpath, status) 
	VALUES (:idapp, :idclient, :iduser, :category, :hostpath, 1)";
	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
	$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);	
	$stmt->bindParam(':iduser', $myuser->userdata['id'], PDO::PARAM_STR);
	$stmt->bindParam(':category', $category, PDO::PARAM_STR);
	$stmt->bindParam(':hostpath', $hostPath, PDO::PARAM_STR);
	$stmt->execute();
	$iddoc = $dbo->lastInsertId();
	$data['id'] = $iddoc;
	if (!file_exists($absolutePath)) {
		mkdir( $absolutePath, 0777, true);
	}
	$attach = '../../files/docs/F0417_Offer Halal certification_EN.pdf';
	$ext = "pdf";
	$filename = str_replace(".".$ext, '_'.$iddoc.'.'.$ext, basename($attach));
	$dest_path = $absolutePath . $filename; 
	saveOfferPDF($data, $attach, $dest_path, false);
	
	$query = "UPDATE tdocs SET filename = :filename WHERE id=:id";
	$stmt = $dbo->prepare($query);
	$stmt->bindParam(':filename', $filename, PDO::PARAM_STR);   
	$stmt->bindParam(':id', $iddoc, PDO::PARAM_STR);   
	$stmt->execute();

	$state = "soffer";

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
			$ownerEmailAddress = "halal.ezone@gmail.com";
			$fromEmailAddress = "noreply@halal-e.zone";
	if ($newIndex > $currentIndex) { 				
	
	
			$offerToken = getToken();
			$sql = "UPDATE tusers SET offer_token=:offer_token WHERE isclient=1 AND id=:id";
				$stmt = $dbo->prepare($sql);
				$stmt->bindValue(':offer_token', $offerToken);
				$stmt->bindValue(':id', $idclient);		
				if(!$stmt->execute()) die(json_encode(generateErrorResponse("Unknown error!")));
				//$idclient = $dbo->lastInsertId();

			



			$body = [];
			$body['name'] = 'Halal e-Zone';
			$body['email'] =  $fromEmailAddress;
			$body['to'] = $data['user']['email'];
			
			//$body['attachhostpath'] = $dest_path;
			//$body['attach'] = $filename;
			
			// sending notification
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
			$body['body'] .= 'If you have any requests or inquiries regarding the offer, kindly reach out to us at <a href="mailto:info@hqc.at">info@hqc.at</a>';			
			$body['body'] .= "<br /><br />";
			$body['body'] .= 'Best regards';
			sendEmail($body);

					$query = "UPDATE tapplications SET state=:state WHERE idclient=:idclient AND id=:idapp";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':state', $state, PDO::PARAM_STR);
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);
		$stmt->execute();
	}
			
			//sendEmailWithAttach
			$body = [];
			$body['name'] = 'Halal e-Zone';
			$body['email'] =  $fromEmailAddress;
			$body['to'] = $data['user']['email'];
			
			$body['attachhostpath'] = $dest_path;
			$body['attach'] = $filename;

			$idcycle = $data['app']["idcycle"];
			$isFirstCycle = false;
			// get first cycle id
			$sql = "SELECT * FROM tcycles WHERE idclient=:idclient AND state = '1' ORDER BY id ASC LIMIT 1";
			$stmt = $dbo->prepare($sql);
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
			$stmt->bindValue(':idclient', $idclient);
			$stmt->execute();
			$firstCycle = $stmt->fetch(PDO::FETCH_ASSOC);
			$cycleName = $firstCycle["name"];
			if ($idcycle == $firstCycle["id"]) {
				$isFirstCycle = true;
			}


			// sending notification
			$body['subject'] = "Halal e-Zone - Offer Halal certification - ".$data["user"]["name"];
			$body['header'] = "Client ".getClientInfo($prod['idclient'])." added a new product:";
			$body['message'] = "<p>Dear Ms./ Mr. ".$data['user']["contact_person"]."!</p>";
			$body['message'] .= '<p>Thank you for your interest in our Halal certification which is accepted and recognised nearly worldwide. Please note that we have excellent experience in your category. This makes the certification process very efficient and clear.</p>
			<p>Please note that we offer a Halal Certificate with international Halal accreditation, including MUI Indonesia, Jakim Malaysia, SFAD KSA, ESMA UAE, HAC Sri Lanka, CICOT Thailand as well as in almost all Islamic countries in Asia and Africa and the Muslim communities in Europe. Our certificate gives you international recognition that is accepted almost everywhere in the world, whereas other bodies\' certifications are only recognised in certain countries.</p>
			<p>Please note that our team consists of professionals who work with SOPs for each step of the halal certification process. This saves you time and gives you a clear overview of the certification process. You can also benefit from our email and phone support at any time. You can send us your questions and we will answer them by email or make an appointment for a phone call to answer all your questions.</p>
			<p>After your internal review and acceptance of the offer, please send us the signed offer by e-mail.</p>
			<h3>What happens next:</h3>
			<p>After acceptance of the offer, you will receive your access data to our online platform. There you can download many useful documents such as: Halal certification guidelines, our checklist for your audit preparation, etc.</p>
			<h3>Your way to an internationally recognised Halal certificate:</h3>
			<p>The audit process is divided into two steps, which can be carried out simultaneously or consecutively.</p>
			<h3>1: On-Desk Audit:</h3>
			<p>This is a document audit. This process is 100% paperless and online. Once you have your login details to our portal, you can start uploading documents related to your raw material specifications, your quality manual, your work instructions, etc.</p>
			<p>OUR TIP! Please note that you can expand and add to your existing quality manual to cover halal requirements. This will save a lot of time and minimise the number of documents you need. The process is simply carried out as described. If you need help, please contact our support team.</p>
			<h3>2: On-site audit:</h3>
			<p>Please choose 3 proposed dates for the on-site audit in your eZone account. The main objective of the on-site audit is to review your QM manual and your production site. You will receive our audit checklist before the audit so that you are prepared and know exactly which questions will be asked and which documents are required. If you have any questions, please do not hesitate to contact our support team. Our team is always at your disposal if you need help or have any questions.</p>
			';

			if ($isFirstCycle) {
				$body['message'] .= '<p style="color:red; font-weight:bold;">Kindly ask you to upload the signed and stamped offer using the following link.</p>';

				$body['message'] .= '<p><a  style="color:red; font-weight:bold;" href="http://halal-e.zone/upload?code='.urlencode($offerToken).'">http://halal-e.zone/upload?code='.$offerToken.'</a></p>';
			}
			else {
				$body['message'] .= '<p style="color:red; font-weight:bold;">Kindly ask you to log into the Halal eZone portal and upload the signed and stamped offer.</p>';
			}

			$body['message'] .= '<p>If you have any requests or inquiries regarding the offer, kindly reach out to us at <a href="mailto:info@hqc.at">info@hqc.at</a></p>';			
			
			$body['message'] .= '	<p>Best regards and have a nice day.</p>';
			
			sendEmailWithAttach($body);

			insertActivityLog($idclient, $idapp, $myuser->userdata['id'], $myuser->userdata['name'], 'Offer sent to client');

		

 

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