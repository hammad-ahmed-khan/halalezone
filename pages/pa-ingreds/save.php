<?php
include_once '../../config/config.php';
include_once '../../classes/users.php';
include_once '../../includes/func.php';

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
}	
catch (PDOException $e) {
	echo 'Database error: '.$e->getMessage();
}
//error_reporting(E_ALL);
//ini_set('display_errors', true);

$id = $_POST["id"];
$producer_id = $_POST["producer_id"];
$rmcode = $_POST["rmcode"];
$name = $_POST["name"];
$cb = $_POST["cb"];
$halalexp = $_POST["halalexp"];
$rmposition = $_POST["rmposition"];
$errors = "";

if (trim($producer_id) == "") { 
	$errors .= "<li>Producer is required.</li>";
}
if (trim($rmcode) == "") { 
	$errors .= "<li>RM Code is required.</li>";
}
if (trim($name) == "") { 
	$errors .= "<li>Name is required.</li>";
}
if (trim($cb) == "") { 
	$errors .= "<li>Halal Certification Body is required.</li>";
}

if (trim($halalexp) == "") { 
	$errors .= "<li>Certificate Expiry Date is required.</li>";
}
else if (($dateTime = DateTime::createFromFormat('d/m/Y', $halalexp)) == FALSE) { 
	$errors .= "<li>Invalid Certificate Expiry Date .</li>";
}
$halalexp = date_format($dateTime, 'Y-m-d');

if (trim($rmposition) == "") { 
	$errors .= "<li>RM Position is required.</li>";
}

if ($errors == "") { 
	if ($id == "") { // New Tank
		$query = "INSERT INTO tingredients_pa (producer_id, rmcode, name, cb, halalexp, rmposition) 
		VALUES (:producer_id, :rmcode, :name, :cb, :halalexp, :rmposition)";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':producer_id', $producer_id, PDO::PARAM_STR);  
		$stmt->bindParam(':rmcode', $rmcode, PDO::PARAM_STR);  
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);   
		$stmt->bindParam(':cb', $cb, PDO::PARAM_STR);   
		$stmt->bindParam(':halalexp', $halalexp, PDO::PARAM_STR);  
		$stmt->bindParam(':rmposition', $rmposition, PDO::PARAM_STR);  
		$stmt->execute();
	}
	else { // Existing Tank
		$query = "UPDATE tingredients_pa SET producer_id = :producer_id, rmcode = :rmcode, name = :name, cb = :cb, 
		halalexp = :halalexp,
		rmposition = :rmposition 
		
		 WHERE id = :id";
		$stmt = $dbo->prepare($query);
		$stmt->bindParam(':producer_id', $producer_id, PDO::PARAM_STR);  
		$stmt->bindParam(':rmcode', $rmcode, PDO::PARAM_STR);  
		$stmt->bindParam(':name', $name, PDO::PARAM_STR);   
		$stmt->bindParam(':cb', $cb, PDO::PARAM_STR);     
		$stmt->bindParam(':halalexp', $halalexp, PDO::PARAM_STR);  
		$stmt->bindParam(':rmposition', $rmposition, PDO::PARAM_STR);  
		$stmt->bindParam(':id', $id, PDO::PARAM_STR);   
		$stmt->execute();
	}
}

if ($errors != "") {
	die("<ul>$errors</ul>");
}
?>