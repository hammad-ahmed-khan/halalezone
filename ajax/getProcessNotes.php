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

    $sql = 'SELECT u.name, IFNULL(app.notes, "") as notes
    
    from tusers u 
    left join tcycles cyc ON cyc.idclient = u.id AND cyc.state=1
    left join tapplications app ON app.idcycle = cyc.id
    WHERE app.id = "'.$idapp.'" AND u.id="'.$idclient.'" ';

    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row);

?>
