<?php
@session_start();
include_once "../../config/config.php";
include_once "../../classes/users.php";
include_once "../../includes/func.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$myuser = cuser::singleton();
	$myuser->getUserData();
		
	$error = "";
 
	$ext = pathinfo($_FILES['filedata']['name'], PATHINFO_EXTENSION);
	
	if (strtolower($ext) != "csv" || $_FILES["filedata"]["type"] != "text/csv") {
		$error = "Invalid file.";
	}
	
	if ($error == "") {
		
 			// Open the uploaded CSV file
			$file = fopen($_FILES['filedata']['tmp_name'], 'r');

			// Read the contents of the file into a string
			$csvContent = fread($file, filesize($_FILES['filedata']['tmp_name']));

			// Call the find_delimiter() function to detect the delimiter in the CSV
			$detectedDelimiter = find_delimiter($csvContent);
			
			fseek($file, 0);
			
			// Loop through each row in the CSV file
			$count = 0;
			$producer_name = "";
			$rmcode = "";
			$name = "";
			$cb = "";
			$halalexp = "";
			$rmposition = "";

			while (($row = fgetcsv($file, 0, $detectedDelimiter)) !== false) {
				// Output each column in the current row
				if ($count > 0) {
					if ($producer_name == "") {
						$producer_name = $row[0];
						$cb = $row[3];
						$halalexp = $row[4];
						if (($dateTime = DateTime::createFromFormat('d/m/Y', $halalexp)) !== FALSE) { 
							$halalexp = $dateTime->format('Y-m-d');
						}					
					}

					$rmposition = $row[5];
					$rmcode = $row[1];
					$name = $row[2];

					if ($producer_name != "") {
						// Check if product exists in database
						$sql = "SELECT id FROM tproducers WHERE name = :name";
						$stmt = $dbo->prepare($sql);
						$stmt->bindParam(':name', $producer_name, PDO::PARAM_STR);
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);

						if ($result) {
							// Product exists in database, return its ID
							$producer_id = $result['id'];
						} else {
							// Product does not exist in database, insert it and return inserted ID
							$sql = "INSERT INTO tproducers (name) VALUES (:name)";
							$stmt = $dbo->prepare($sql);
							$stmt->bindParam(':name', $producer_name, PDO::PARAM_STR);
							$stmt->execute();
							$producer_id = $dbo->lastInsertId();
						}
						
						$sql = "SELECT id FROM tingredients_pa WHERE rmcode = :rmcode";
						$stmt = $dbo->prepare($sql);
						$stmt->bindParam(':rmcode', $rmcode, PDO::PARAM_STR);
						$stmt->execute();
						$result = $stmt->fetch(PDO::FETCH_ASSOC);

						if ($result) {
							// Product exists in database, return its ID
							$id = $result['id'];
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
						else {
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
					}
				}				
				$count++;
			}
			fclose($file); // Close the CSV file
			/*
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
		*/
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

function find_delimiter($csv)
{
    $delimiters = array(',', '.', ';');
    $bestDelimiter = false;
    $count = 0;
    foreach ($delimiters as $delimiter)
        if (substr_count($csv, $delimiter) > $count) {
            $count = substr_count($csv, $delimiter);
            $bestDelimiter = $delimiter;
        }
    return $bestDelimiter;
}
?>