<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

    $idapp = getPostParam('idapp');
    $idclient = getPostParam('idclient');
    $notes = getPostParam('notes');
    
    $sql = 'UPDATE tapplications as app 
            SET app.notes = :notes
            WHERE app.id = :idapp
            AND app.idclient = :idclient';
    
    $stmt = $dbo->prepare($sql);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':idapp', $idapp);
    $stmt->bindParam(':idclient', $idclient);
    // Check if the update was successful
    if ($stmt->execute()) {
        // Return success message or handle further processing
        echo json_encode(array('status' => 'success', 'message' => 'Notes updated successfully'));
    } else {
        // Return failure message or handle error
        echo json_encode(array('status' => 'error', 'message' => 'Failed to update notes'));
    }
?>