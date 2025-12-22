<?php
include_once 'config/config.php';
include_once 'classes/users.php';
include_once 'pages/patterns.php';
include_once 'includes/func.php';
require_once( 'notifications/notifyfuncs.php');

error_reporting(E_ALL); 
ini_set('display_errors', 1);

$decode = file_get_contents(__DIR__ . "/config.json");
$config = json_decode($decode, TRUE);

$db = acsessDb::singleton();
$dbo = $db->connect();

$userId = 324;
$applicationId = 1517;

 // Send a reminder email to the admin if the invoice is not uploaded within one week after the signed offer is uploaded. 
 $sqlDocs = "SELECT * FROM tdocs WHERE idclient = :idclient AND idapp = :idapp AND category = 'invoice' LIMIT 1"; 
 $stmtDocs = $dbo->prepare($sqlDocs);
 $stmtDocs->bindParam(':idclient', $userId, PDO::PARAM_INT);
 $stmtDocs->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
 $stmtDocs->execute();            
 $docs = $stmtDocs->fetch(PDO::FETCH_ASSOC);

 if (!$docs) {
	 // Invoice not uploaded, check for signed offer (soffer)
	 $sqlSoffer = "SELECT created_at FROM tdocs WHERE idclient = :idclient AND idapp = :idapp AND category = 'soffer' LIMIT 1";
	 $stmtSoffer = $dbo->prepare($sqlSoffer);
	 $stmtSoffer->bindParam(':idclient', $userId, PDO::PARAM_INT);
	 $stmtSoffer->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
	 $stmtSoffer->execute();
	 $soffer = $stmtSoffer->fetch(PDO::FETCH_ASSOC);

	 if ($soffer) {
		/*
		echo $soffer['created_at'];
		echo ' == ';
		echo date('Y-m-d H:i:s', strtotime($soffer['created_at']));
		*/

		 $createdAt = strtotime($soffer['created_at']); // Convert datetime to timestamp
		 $oneWeekAgo = strtotime('-1 week');
		 

		 if ($createdAt <= $oneWeekAgo) {

		 
			 // Send reminder email
			 $ownerEmailAddress = "halal.ezone@gmail.com";
			 $fromEmailAddress = "noreply@halal-e.zone";

			 $body['name'] = 'Halal e-Zone';
			 $body['email'] = $fromEmailAddress;
			 //$body['to'] = $adminEmailAddress;

			 // Email content
			 $body['subject'] = "Reminder: Invoice Not Uploaded - " . $user["name"];
			 $body['header'] = "";
			 $body['body'] = "Dear Admin,";
			 $body['body'] .= "<br /><br />";
			 $body['body'] .= "A signed offer was uploaded for client \"" . $user["name"] . "\" more than a week ago, but the invoice has not been uploaded yet.";
			 $body['body'] .= "<br /><br />";
			 $body['body'] .= "Please ensure the invoice is uploaded as soon as possible.";
			 $body['body'] .= "<br /><br />";
			 $body['body'] .= "Kind Regards,";
			 $body['body'] .= "<br/>";
			 $body['body'] .= "Your HQC Supporting Team";

			 //sendEmail($body);
			 $body['to'] = 'alrahmahsolutions@gmail.com';
			 sendEmail($body);                    
		 }
	 }
 }
?>