<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

header('Content-Type: application/json');

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
    
    $user_id = $myuser->userdata['id'];
    
    // DataTables parameters
    $draw = intval($_POST['draw'] ?? 0);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search_value = $_POST['search']['value'] ?? '';
    
    // Custom filter parameters
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $subject_search = $_POST['subject_search'] ?? '';
    
    // Base query - only notifications user received
    $base_query = "FROM tnotification_recipients nr 
                   INNER JOIN tnotification_log nl ON nr.notification_log_id = nl.id 
                   LEFT JOIN tusers u ON nl.sent_by = u.id
                   WHERE nr.user_id = ? AND nr.status = 'sent' AND nl.deleted = 0";
    
    $params = [$user_id];
    $where_conditions = [];
    
    // Apply filters
    if (!empty($date_from)) {
        $where_conditions[] = "DATE(nl.sent_at) >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "DATE(nl.sent_at) <= ?";
        $params[] = $date_to;
    }
    
    if (!empty($subject_search)) {
        $where_conditions[] = "(nl.subject LIKE ? OR nl.message LIKE ?)";
        $params[] = '%' . $subject_search . '%';
        $params[] = '%' . $subject_search . '%';
    }
    
    // DataTables search
    if (!empty($search_value)) {
        $where_conditions[] = "(nl.subject LIKE ? OR nl.message LIKE ? OR u.name LIKE ?)";
        $params[] = '%' . $search_value . '%';
        $params[] = '%' . $search_value . '%';
        $params[] = '%' . $search_value . '%';
    }
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = ' AND ' . implode(' AND ', $where_conditions);
    }
    
    // Count total records
    $count_sql = "SELECT COUNT(*) as total $base_query $where_clause";
    $count_stmt = $dbo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get data
    $order_column = $_POST['order'][0]['column'] ?? 4; // Default to sent_at
    $order_dir = $_POST['order'][0]['dir'] ?? 'desc';
    
    $columns = ['nl.subject', 'u.name', 'nl.recipient_type', 'nl.attachments_count', 'nl.sent_at'];
    $order_by = isset($columns[$order_column]) ? $columns[$order_column] : 'nl.sent_at';
    
    $data_sql = "SELECT nl.id, nl.subject, nl.message, nl.sent_at, nl.attachments_count, 
                        nl.recipient_type, nl.recipient_count, nl.failed_count,
                        u.name as sender_name, u.email as sender_email,
                        nr.sent_at as delivered_at
                 $base_query $where_clause
                 ORDER BY $order_by $order_dir
                 LIMIT $length OFFSET $start";
    
    $data_stmt = $dbo->prepare($data_sql);
    $data_stmt->execute($params);
    $records = $data_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($records as $row) {
        // Create message preview
        $message_text = strip_tags($row['message']);
        $message_preview = strlen($message_text) > 100 ? 
            substr($message_text, 0, 100) . '...' : 
            $message_text;
        
        // Format recipient type for users
        $recipient_type_labels = [
            'all_clients' => 'All Clients',
            'all_team' => 'All Team',
            'all_both' => 'All Users',
            'specific' => 'Selected Recipients'
        ];
        
        $data[] = [
            'id' => $row['id'],
            'subject' => htmlspecialchars($row['subject']),
            'message_preview' => htmlspecialchars($message_preview),
            'sender_name' => htmlspecialchars($row['sender_name'] ?: 'System'),
            'recipient_type' => $row['recipient_type'],
            'recipient_type_label' => $recipient_type_labels[$row['recipient_type']] ?? ucfirst($row['recipient_type']),
            'attachments_count' => (int)$row['attachments_count'],
            'sent_at' => $row['sent_at'],
            'sent_at_formatted' => date('M j, Y g:i A', strtotime($row['sent_at'])),
            'delivered_at' => $row['delivered_at']
        ];
    }
    
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $total_records,
        'recordsFiltered' => $total_records,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?>
