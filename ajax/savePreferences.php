<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // ??????? ?????? ??????????? ? ??
 
    $id = $_SESSION['halal']['id']; // Get the user's parent ID (the main account)

    $products_preference = $_POST['products_preference'];
    $ingredients_preference = $_POST['ingredients_preference']; 
    $qm_documents_preference = $_POST['qm_documents_preference'];

     // Save the preferences in the database
    $sql = "UPDATE tusers SET 
            products_preference = :products_preference, 
            ingredients_preference = :ingredients_preference, 
            qm_documents_preference = :qm_documents_preference
            WHERE id = :id";
    
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':products_preference', $products_preference);
    $stmt->bindValue(':ingredients_preference', $ingredients_preference);
    $stmt->bindValue(':qm_documents_preference', $qm_documents_preference);
    $stmt->bindValue(':id', $id);
    $stmt->execute();

    echo json_encode(['message' => 'Settings update successful.']);
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}	
?>