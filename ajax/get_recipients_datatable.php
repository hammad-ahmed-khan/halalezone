<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
    $myuser = cuser::singleton();
    $myuser->getUserData();
    
    // Check if user is logged in
    if (empty($myuser->userdata['id'])) {
        echo json_encode(['error' => 'Not logged in']);
        exit();
    }
    
    $notification_id = $_POST['notification_id'] ?? 0;
    if (!$notification_id) {
        echo json_encode(['error' => 'Invalid notification ID']);
        exit();
    }
    
    // Pagination parameters
    $pageNumber = $_POST["start"] ?? 0;
    $pageSize = $_POST["length"] ?? 15;
    $search_value = $_POST['search'] ?? '';
    $order_column = $_POST['order'][0]['column'] ?? 3; // Default to status
    $order_dir = $_POST['order'][0]['dir'] ?? 'desc';
    
    // Custom filter parameters
    $recipient_filter = $_POST['recipient_filter'] ?? 'all';
    
    // Build criteria
    $criteria = " AND nr.notification_log_id = " . intval($notification_id);
    
    // Apply custom filters
    if ($recipient_filter !== 'all') {
        switch ($recipient_filter) {
            case 'sent':
                $criteria .= " AND nr.status = 'sent'";
                break;
            case 'failed':
                $criteria .= " AND nr.status = 'failed'";
                break;
            case 'clients':
                $criteria .= " AND u.isclient = '1'";
                break;
            case 'team':
                $criteria .= " AND (u.isclient = '0' OR u.isclient = '2')";
                break;
        }
    }
    
    // Apply search
    if (!empty($search_value)) {
        $search_escaped = $dbo->quote('%' . $search_value . '%');
        $criteria .= " AND (u.name LIKE $search_escaped OR nr.email LIKE $search_escaped OR nr.error_message LIKE $search_escaped)";
    }
    
    // Column mapping for ordering
    $columns = [
        0 => 'u.name',          // Recipient name
        1 => 'nr.email',        // Email
        2 => 'u.isclient',      // Type
        3 => 'nr.status',       // Status
        4 => 'nr.sent_at',      // Sent At
        5 => 'nr.error_message' // Notes/Error
    ];
    
    $order_by = isset($columns[$order_column]) ? $columns[$order_column] : 'nr.status';
    
    $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    // Get total count
    $sql = "SELECT COUNT(nr.id) AS count FROM tnotification_recipients AS nr 
            LEFT JOIN tusers u ON nr.user_id = u.id 
            WHERE 1=1 $criteria";
    $rows = $dbo->prepare($sql);
    $rows->execute();
    $TotalCount = $rows->fetchColumn();
    
    // Get data
    $query = "SELECT nr.*, u.name as user_name, u.isclient, u.prefix,
              DATE_FORMAT(nr.sent_at, '%d/%m/%Y %h:%i %p') AS formatted_sent_at
              FROM tnotification_recipients AS nr 
              LEFT JOIN tusers u ON nr.user_id = u.id 
              WHERE 1=1 $criteria
              ORDER BY $order_by $order_dir
              LIMIT $pageNumber, $pageSize";
    
    $stmt = $dbo->prepare($query);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $data = array();
    foreach($records as $d) {
        $data[] = $d;
    }
    
    $i = 0;
    foreach ($data as $key => $val) {
        
        // Recipient name with ID
        if ($data[$i]['user_name']) {
            $data[$i]['recipient'] = '<strong>' . htmlspecialchars($data[$i]['user_name']) . '</strong>';
           
        } else {
            $data[$i]['recipient'] = '<i class="fa fa-user-times text-muted"></i> <span class="text-muted">Unknown User</span>';
        }
        
        // Email with mailto link
        $data[$i]['email_display'] = '<a href="mailto:' . htmlspecialchars($data[$i]['email']) . '" class="text-info">';
        $data[$i]['email_display'] .= '<i class="fa fa-envelope-o"></i> ' . htmlspecialchars($data[$i]['email']) . '</a>';
        
        // Type badge
        if (isset($data[$i]['isclient'])) {
            switch ($data[$i]['isclient']) {
                case '1':
                    $data[$i]['type'] = '<span class="label label-info"><i class="fa fa-user"></i> Client</span>';
                    break;
                case '0':
                    $data[$i]['type'] = '<span class="label label-success"><i class="fa fa-cogs"></i> Team</span>';
                    break;
                case '2':
                    $data[$i]['type'] = '<span class="label label-warning"><i class="fa fa-search"></i> Auditor</span>';
                    break;
                default:
                    $data[$i]['type'] = '<span class="label label-default">Other</span>';
            }
        } else {
            $data[$i]['type'] = '<span class="label label-default">Unknown</span>';
        }
        
        // Status badge
        if ($data[$i]['status'] === 'sent') {
            $data[$i]['status_display'] = '<span class="label label-success"><i class="fa fa-check"></i> Delivered</span>';
        } elseif ($data[$i]['status'] === 'failed') {
            $data[$i]['status_display'] = '<span class="label label-danger"><i class="fa fa-times"></i> Failed</span>';
        } else {
            $data[$i]['status_display'] = '<span class="label label-warning">' . ucfirst($data[$i]['status']) . '</span>';
        }
        
        // Sent date
        $data[$i]['sent_date'] = '<small class="text-muted">' . $data[$i]['formatted_sent_at'] . '</small>';
        
        // Notes/Error button
        if ($data[$i]['error_message']) {
            $error_escaped = htmlspecialchars(str_replace('"', '\\"', $data[$i]['error_message']));
            $email_escaped = htmlspecialchars($data[$i]['email']);
            $data[$i]['button'] = '<button class="btn btn-xs btn-danger" ';
            $data[$i]['button'] .= 'onclick="showErrorModal(\'' . $error_escaped . '\', \'' . $email_escaped . '\')">';
            $data[$i]['button'] .= '<i class="fa fa-exclamation-triangle"></i> Error</button>';
        } else {
            $data[$i]['button'] = '<small class="text-muted">-</small>';
        }
        
        $i++;
    }

    $datax = array(
        'recordsTotal' => $TotalCount,
        'recordsFiltered' => $TotalCount,	
        'data' => $data
    );
    
    echo json_encode($datax);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?>