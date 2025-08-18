<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // ??????? ?????? ??????????? ? ??
    $myuser = cuser::singleton();
    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];
    $username = $myuser->userdata['name'];
    $email = $myuser->userdata['email'];

    $curPage = $_POST["start"];
    $rowsPerPage = $_POST['length'];

    /*
    $sortingField = $_POST['sidx'];
    $sortingOrder = $_POST['sord'];
    */
    
    $idclient = getPostParam('idclient');
    $status = getPostParam('status');

    if ($status == "") {
        $status = "1";
    }

    if ($myuser->userdata['isclient'] == "2") { // Auditor can only see issues report by him/her
        $idclient = $user_id;
    }
   
	if (!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');

    if ($idclient != "") { 
	    $filter=" WHERE t.status='".$status."' AND t.user_id='".$idclient."'";
    }
    else { 
        $filter=" WHERE t.status='".$status."' ";
    }

    //if ($idclient!='') $filter.=" AND t.user_id = '".$idclient."'";
   
    $sortColumnIndex = $_POST['order'][0]['column'];
    $sortDirection = $_POST['order'][0]['dir'];

// Map DataTables column index to database column name
$columns = [];

if ($myuser->userdata['isclient'] != "1") {
    $columns[] = 't.username';
}

$columns[] = 't.id';
$columns[] = 't.issue_type';
$columns[] = 't.issue_description';
$columns[] = 't.status';
$columns[] = 't.created_at';

// Get the corresponding column name from the columns array
$sortBy = $columns[$sortColumnIndex];

 	$sql = 'SELECT COUNT(id) AS count FROM ttickets AS t '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    
        $sql = 'SELECT t.id, t.user_id, t.username, t.email, t.status, t.issue_type, 
        
                 CASE
        WHEN CHAR_LENGTH(t.issue_description) <= 50 THEN t.issue_description
        ELSE CONCAT(
            SUBSTRING(
                t.issue_description, 
                1, 
                LENGTH(SUBSTRING(t.issue_description, 1, 50)) - LENGTH(SUBSTRING_INDEX(SUBSTRING(t.issue_description, 1, 50), \' \', -1))
            ),
            \'...\'
        )
    END AS issue_description
        
        , t.current_url, t.viewed, date_format(t.created_at, \'%d/%m/%Y %h:%i %p\') AS date_created, date_format(t.updated_at, \'%d/%m/%Y %h:%i %p\') AS last_updated, last_updated_by, last_updated_by_name, last_updated_by_email
        from ttickets t ' . $filter . '  ORDER BY ' . $sortBy . ' ' . $sortDirection . '  LIMIT ' . $curPage . ', ' . $rowsPerPage;

        $totalCount = $totalRows['count'];
        $data = [];
        $i=0;
        $res = $dbo->prepare($sql); 
        if (!$res->execute()) die($sql);
            while($row = $res->fetch(PDO::FETCH_ASSOC)) {

            // Check if this ticket has been read by the current admin
            $ticketId = $row['id'];
            /*
            // Check if there are any replies to this ticket that are not posted by the current user
            $replySql = 'SELECT r.id FROM treplies r WHERE r.ticket_id = :ticket_id AND r.user_id != :user_id';
            $replyStmt = $dbo->prepare($replySql);
            $replyStmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);
            $replyStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $replyStmt->execute();
            $replies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);

            $viewed = '1'; // Default to viewed

            if ($replies) {
                foreach ($replies as $reply) {
                    $replyId = $reply['id'];

                    // Check if this reply has not been read by the current user
                    $readSql = 'SELECT 1 FROM ticket_reads WHERE user_id = :user_id AND ticket_id = :ticket_id AND reply_id = :reply_id LIMIT 1';
                    $readStmt = $dbo->prepare($readSql);
                    $readStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $readStmt->bindParam(':ticket_id', $ticketId, PDO::PARAM_INT);
                    $readStmt->bindParam(':reply_id', $replyId, PDO::PARAM_INT);
                    $readStmt->execute();

                    if (!$readStmt->fetch()) {
                        $viewed = '0';
                        break;
                    }
                }
            }
            */

            $row['viewed'] = $viewed;

            $row['id'] = '<a href="#" id="'. $row['id'].'" class="post-reply">'. $row['id'].'</a>';

            if ($row['last_updated_by_name'] == "") {
                $row['last_updated'] =  "";
            }
            else {
                $row['last_updated'] =  $row['last_updated'] . ' by ' . $row['last_updated_by_name'];
            }
            
            if ($row['status'] == '1') {
                $row['status'] = '<span class="badge badge-success">Open</span>';
            } else {
                $row['status'] = '<span class="badge badge-danger">Closed</span>';
            }
            
            $data[] = $row;
        }

        $responseData = [
            'data' => $data, 
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $totalCount,
        ];

        echo json_encode($responseData);

}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>