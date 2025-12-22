<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Create database connection object
    $myuser = cuser::singleton();
    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];
    $username = $myuser->userdata['name'];
    $email = $myuser->userdata['email'];

    // Determine user type
    $isAdmin = $myuser->userdata['isclient'] == "0";
    $isAuditor = $myuser->userdata['isclient'] == "2"; 
    $isClient = $myuser->userdata['isclient'] == "1";

    $curPage = $_POST["start"];
    $rowsPerPage = $_POST['length'];

    $filter = "";
    $status = getPostParam('status');
    $tidclient = getPostParam('idclient');
    $tidauditor = getPostParam('idauditor');
    $mytasks = getPostParam('mytasks');
   
	if (!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');

    // Build filter based on user type and parameters
    if ($isClient) {
        // For clients - only show tasks assigned to them (they cannot create tasks)
        $filter .= " AND t.task_type='client' AND t.idclient = '".$user_id."'";
    } elseif ($isAuditor) {
        // For auditors - existing logic
        $idauditor = $user_id;
        
        if ($tidclient != "") { 
            $filter .= " AND t.idclient = '".$tidclient."'";
        } else {
            if ($mytasks == "1") {
                $filter .= " AND t.idauditor = '".$idauditor."'";
            } else {
                $filter .= " AND t.user_id = '".$idauditor."'";
                if ($tidauditor != "" && $tidauditor != $idauditor) { 
                    $filter .= " AND t.idauditor = '".$tidauditor."'";
                }
            }
        }
    } elseif ($isAdmin) {
        // For admins - existing logic with additional filtering
        $idauditor = $user_id;
        
        if ($tidclient != "") { 
            $filter .= " AND t.idclient = '".$tidclient."'";
        } else {
            if ($mytasks == "1") {
                $filter .= " AND t.idauditor = '".$idauditor."'";
            } else {
                $filter .= " AND t.user_id = '".$idauditor."'";
                if ($tidauditor != "" && $tidauditor != $idauditor) { 
                    $filter .= " AND t.idauditor = '".$tidauditor."'";
                }
            }
        }
    }

    $filter .=" AND t.status='".$status."' ";
    
    $sortColumnIndex = $_POST['order'][0]['column'];
    $sortDirection = $_POST['order'][0]['dir'];

    // Map DataTables column index to database column name
    $columns = array(
        't.id',
        't.task_type',
        'auditor.name', 
        'client.name',
        't.issue_type',
        't.issue_description',
        't.status',
        't.username',
        't.created_at',
        't.updated_at'
    );

    // Get the corresponding column name from the columns array
    $sortBy = $columns[$sortColumnIndex];

	$sql = 'SELECT COUNT(id) AS count FROM ttasks AS t '.$filter;
	$rows = $dbo->prepare($sql);
	$rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    
    $sql = 'SELECT t.id, t.user_id, t.username, t.task_type, t.idauditor, t.idclient, t.email, t.status, t.issue_type, 
        
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
        
        , auditor.name AS auditorname, 
    client.name AS clientname,
    client.prefix AS clientprefix
FROM 
    ttasks t
LEFT JOIN 
    tusers auditor ON t.idauditor = auditor.id AND (auditor.isclient = 0 OR auditor.isclient = 2)
LEFT JOIN 
    tusers client ON t.idclient = client.id AND client.isclient = 1
        
       WHERE 1 = 1 ' . $filter . '  ORDER BY ' . $sortBy . ' ' . $sortDirection . '  LIMIT ' . $curPage . ', ' . $rowsPerPage;

        $totalCount = (int)$totalRows['count'];
        $data = [];
        $i=0;
        $res = $dbo->prepare($sql); 
        if (!$res->execute()) die($sql);
            while($row = $res->fetch(PDO::FETCH_ASSOC)) {

            // Check if this task has been viewed (placeholder for future implementation)
            $taskId = $row['id'];
            $viewed = '1'; // Default to viewed for now
            
            // Format client name with prefix and ID
            if ($row['clientname']) {
                $row['clientname'] = $row["clientname"] . ' - ' .  $row["clientprefix"] . $row["idclient"];
            } else {
                $row['clientname'] = 'N/A';
            }

            // Format task type


            
            $row['task_type'] = ucfirst($row["task_type"]);

            if ($row['task_type'] == 'Auditor') {
                $row['task_type'] = 'Auditor/Team Member';
            }

            $row['viewed'] = $viewed;

            // Make task ID clickable for viewing details
            $row['id'] = '<a href="#" id="'. $row['id'].'" class="post-reply">'. $row['id'].'</a>';

            // Format last updated information
            if ($row['last_updated_by_name'] == "") {
                $row['last_updated'] =  "";
            } else {
                $row['last_updated'] =  $row['last_updated'] . ' by ' . $row['last_updated_by_name'];
            }
            
            // Format status badge
            if ($row['status'] == '1') {
                $row['status'] = '<span class="badge badge-success">Open</span>';
            } else {
                $row['status'] = '<span class="badge badge-danger">Closed</span>';
            }
            
            // Handle missing auditor name
            if (!$row['auditorname']) {
                $row['auditorname'] = 'Unassigned';
            }
            
            $data[] = $row;
        }

        $responseData = [
            'data' => $data, 
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $totalCount,
        ];

        echo json_encode($responseData);

} catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
?>