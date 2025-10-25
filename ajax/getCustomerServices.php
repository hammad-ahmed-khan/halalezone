<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();

    $myuser = cuser::singleton();
    $myuser->getUserData();
    $user_id = $myuser->userdata['id'];

    $curPage = $_POST["start"];
    $rowsPerPage = $_POST['length'];
    $idclient = getPostParam('idclient');
    $status = getPostParam('status');
    $origin = getPostParam('origin'); // New parameter: 'all', 'client', or 'internal'

    // Base filter
    $filter = "";
    
    if ($myuser->userdata['isclient'] == "1") {
        $filter = " WHERE t.status='".$status."' AND t.user_id = " . (int)$user_id;
    } else {
        if ($idclient != "") { 
            $filter = " WHERE t.status='".$status."' AND t.user_id = '".$idclient."'";
        } else { 
            $filter = " WHERE t.status='".$status."' ";
        }
    }
    
    // Add origin filter based on created_by user's isclient status
    if ($origin === 'client') {
        // Show only tickets created by clients (where created_by user has isclient=1)
        $filter .= " AND EXISTS (SELECT 1 FROM tusers u WHERE u.id = t.created_by AND u.isclient = 1)";
    } elseif ($origin === 'internal') {
        // Show only tickets created internally (where created_by user has isclient!=1 or created_by is null)
        $filter .= " AND (NOT EXISTS (SELECT 1 FROM tusers u WHERE u.id = t.created_by AND u.isclient = 1) OR t.created_by IS NULL)";
    }
    // If origin is 'all' or not set, no additional filtering is applied
    
    $sortColumnIndex = $_POST['order'][0]['column'];
    $sortDirection = $_POST['order'][0]['dir'];

    // Map DataTables column index to database column name
    $columns = [];

    if ($myuser->userdata['isclient'] != "1") {
        $columns[] = 't.username';
    }

    $columns[] = 't.id';
    $columns[] = 't.request_type';
    $columns[] = 't.request_description';
    $columns[] = 't.status';
    $columns[] = 't.created_at';

    // Get the corresponding column name from the columns array
    $sortBy = $columns[$sortColumnIndex];

    $sql = 'SELECT COUNT(id) AS count FROM tcustomerservice AS t '.$filter;
    $rows = $dbo->prepare($sql);
    $rows->execute();
    $totalRows = $rows->fetch(PDO::FETCH_ASSOC);

    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
    
    $sql = 'SELECT t.id, t.user_id, t.username, t.email, t.status, t.request_type, 
        CASE
            WHEN CHAR_LENGTH(t.request_description) <= 50 THEN t.request_description
            ELSE CONCAT(
                SUBSTRING(
                    t.request_description, 
                    1, 
                    LENGTH(SUBSTRING(t.request_description, 1, 50)) - LENGTH(SUBSTRING_INDEX(SUBSTRING(t.request_description, 1, 50), \' \', -1))
                ),
                \'...\'
            )
        END AS request_description,
        t.current_url, t.viewed, 
        date_format(t.created_at, \'%d/%m/%Y %h:%i %p\') AS date_created, 
        t.created_by, t.created_by_name, t.created_by_email, 
        date_format(t.updated_at, \'%d/%m/%Y %h:%i %p\') AS last_updated, 
        t.last_updated_by, t.last_updated_by_name, t.last_updated_by_email,
        (SELECT CASE WHEN u.isclient = 1 THEN 1 ELSE 0 END FROM tusers u WHERE u.id = t.created_by) AS is_client_created
        FROM tcustomerservice t ' . $filter . '  
        ORDER BY ' . $sortBy . ' ' . $sortDirection . '  
        LIMIT ' . $curPage . ', ' . $rowsPerPage;
    
    $data = [];
    $i = 0;
    $res = $dbo->prepare($sql);
    if (!$res->execute()) die($sql);
    
    while($row = $res->fetch(PDO::FETCH_ASSOC)) {
        // Mark as viewed if needed
         
  $row['id'] = '<a href="#" id="'. $row['id'].'" class="post-reply">'. $row['id'].'</a>';




            if ($row['created_by_name'] != "") {
                $row['date_created'] =  $row['date_created'] . ' by ' . $row['created_by_name'];
            }


            if ($row['last_updated_by_name'] == "") {
                $row['last_updated'] =  "";
            }
            else {
                $row['last_updated'] =  $row['last_updated'] . ' by ' . $row['last_updated_by_name'];
            }

        // Set is_client_created to 0 if null (for backwards compatibility)
        if ($row['is_client_created'] === null) {
            $row['is_client_created'] = '0';
        }

        $data[] = $row;
        $i++;
    }

    $jsonData = array(
        "draw" => intval($_POST['draw']),
        "recordsTotal" => intval($totalRows['count']),
        "recordsFiltered" => intval($totalRows['count']),
        "data" => $data
    );

    echo json_encode($jsonData);
    
} catch (Exception $e) {
    echo json_encode(array(
        "draw" => intval($_POST['draw']),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => array(),
        "error" => $e->getMessage()
    ));
}
?>