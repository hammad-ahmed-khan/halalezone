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
	$category = "certificate";
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
	
	$Title = 'Certificate';
	
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

		$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$client."/application/certificate/";			
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
		
		$body = [];
		$body['name'] = 'Halal e-Zone';
		$body['email'] =  $fromEmailAddress;
		$body['to'] = $user["email"];
		
		$body['attachhostpath'] = $dest_path;
		$body['attach'] = $filename;

		move_uploaded_file($source_path, $dest_path);

		// INSERT CERTIFICATE
        $dbo = &$GLOBALS['dbo'];
        $dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

		
		$table = 'tcertificates';
		$data = array(
			'filename' => $filename,
			'url' => $hostPath . '/' . $filename,
			'hostpath' => $dest_path,
			'idclient' => $idclient,
			'idapp' => $idapp
		);
		$stmt = $dbo->prepare("SELECT COUNT(*) FROM $table WHERE idclient = :idclient AND idapp = :idapp");
		$stmt->bindParam(':idclient', $data['idclient']);
		$stmt->bindParam(':idapp', $data['idapp']);
		$stmt->execute();
		$count = $stmt->fetchColumn();
		if ($count > 0) {
			// Update the record
			$stmt = $dbo->prepare("UPDATE $table SET filename = :filename, url = :url, hostpath = :hostpath WHERE idclient = :idclient AND idapp = :idapp");
			$stmt->bindParam(':filename', $data['filename']);
			$stmt->bindParam(':url', $data['url']);
			$stmt->bindParam(':hostpath', $data['hostpath']);
			$stmt->bindParam(':idclient', $data['idclient']);
			$stmt->bindParam(':idapp', $data['idapp']);
			$stmt->execute();
		} else {
			// Insert a new record
			$stmt = $dbo->prepare("INSERT INTO $table (filename, url, hostpath, idclient, idapp) VALUES (:filename, :url, :hostpath, :idclient, :idapp)");
			$stmt->bindParam(':filename', $data['filename']);
			$stmt->bindParam(':url', $data['url']);
			$stmt->bindParam(':hostpath', $data['hostpath']);
			$stmt->bindParam(':idclient', $data['idclient']);
			$stmt->bindParam(':idapp', $data['idapp']);
			$stmt->execute();
		}

		$query = "SELECT CertificateExpiryDate FROM tapplications WHERE idclient=:idclient AND id=:idapp";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);  
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);  
		$stmt->execute();
		
		if ($expdate = $stmt->fetchColumn()) {

			$certdate = strtotime($expdate);
			$datediff = get_month_diff($certdate);

			$status = 0;
			if ($datediff <= 0) $status = 4;
			elseif ($datediff <= 30) $status = 3;
			elseif ($datediff <= 60) $status = 2;
			elseif ($datediff <= 90) $status = 1;

			$stmt = $dbo->prepare("UPDATE $table SET expdate = :expdate, status = :status WHERE idclient = :idclient AND idapp = :idapp");
			$stmt->bindParam(':expdate', $expdate);
			$stmt->bindParam(':status', $status);
			$stmt->bindParam(':idclient', $data['idclient']);
			$stmt->bindParam(':idapp', $data['idapp']);
			$stmt->execute();
		}
 		// END INSERT CERTIFICATE

		// sending notification
		$body['subject'] = "Halal e-Zone - HQC Halal Certificate - " . $user["name"];
		//$body['header'] = "Client ".getClientInfo($prod['idclient'])." added a new product:";
		$body['message'] = "Dear ".$user["name"].",
<br /><br />
Your certificate is attached to this email for your convenience, or you can download it from the Dashboard/Certificates section.
<br /><br />
Best regards,<br />
Your HQC supporting Team";
sendEmailWithAttach($body);

	insertActivityLog($data['idclient'], $data['idapp'], $myuser->userdata['id'], $myuser->userdata['name'], 'Certificate uploaded');

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