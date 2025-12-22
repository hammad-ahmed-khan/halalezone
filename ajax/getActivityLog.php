<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

    $myuser = cuser::singleton();
    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];

    $curPage = $_POST["start"];
    $rowsPerPage = $_POST['length'];
    
    /*
    $sortingField = $_POST['sidx'];
    $sortingOrder = $_POST['sord'];
    */
    
    $idapp = getPostParam('idapp');

    $sql = 'SELECT username, activity_description, created_at FROM tactivity_log WHERE deleted=0 AND idapp=:idapp ORDER BY created_at ASC';
    
    $data = [];
    $i = 0;
    $res = $dbo->prepare($sql);
    $res->bindParam(':idapp', $idapp, PDO::PARAM_INT);
    if (!$res->execute()) {
        die($sql);
    }

    $html = '<table id="activityLogTable" class="table table-striped table-bordered">';
    $html .= '<thead><tr><th>User</th><th>Activity</th><th>Date</th></tr></thead>';
    $html .= '<tbody>';
    
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['username']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['activity_description']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['created_at']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    
    echo $html;
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>