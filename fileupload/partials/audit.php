<?php
@session_start();
include_once "../../config/config.php";
include_once "../../classes/users.php";
include_once "../../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$myuser = cuser::singleton();
	$myuser->getUserData();
		
	$error = "";
	$category = "audit";
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
	
	$Title = $_POST["Title"];
	$Signature = $_POST["Signature"];
	$Comments = $_POST["Comments"];

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

		$hostPath = $config['filesfolder']."/".$config['clientsfolder']."/".$client."/application/audit/";			
		$options['upload_dir']= __DIR__ ."/../../".$hostPath;
		if (!file_exists($options['upload_dir'])) {
			mkdir($options['upload_dir'], 0777, true);
		}

		$query = "INSERT INTO tdocs (idapp, idclient, iduser, title, hostpath, comments, category, signature, status) 
							VALUES  (:idapp, :idclient, :iduser, :title, :hostpath, :comments, :category, :signature, 1)";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':idapp', $idapp, PDO::PARAM_STR);  
		$stmt->bindParam(':idclient', $idclient, PDO::PARAM_STR);  
		$stmt->bindParam(':iduser', $myuser->userdata['id'], PDO::PARAM_STR);  
		$stmt->bindParam(':title', $Title, PDO::PARAM_STR);   
		$stmt->bindParam(':hostpath', $hostPath, PDO::PARAM_STR);   
		$stmt->bindParam(':comments', $Comments, PDO::PARAM_STR);   
		$stmt->bindParam(':category', $category, PDO::PARAM_STR);   
		$stmt->bindParam(':signature', $Signature, PDO::PARAM_STR);   
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