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
    
    // Check if user has admin access
    if ($myuser->userdata['isclient'] == '1') {
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }
    
    // DataTables parameters
    $draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
    $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
    $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
    
    // Get filter parameters
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $recipient_type = $_POST['recipient_type'] ?? '';
    $subject_search = $_POST['subject_search'] ?? '';
    $show_deleted = $_POST['show_deleted'] ?? 'false'; // New parameter
    
    // Build WHERE clause
    $where_conditions = ['1=1']; // Base condition
    $params = [];
    
    // Filter deleted notifications by default
    if ($show_deleted === 'only') {
        $where_conditions[] = 'nl.deleted = 1';
    } elseif ($show_deleted === 'all') {
        // Show both deleted and active
    } else {
        // Default: show only active notifications
        $where_conditions[] = 'nl.deleted = 0';
    }
    
    // Date filters
    if (!empty($date_from)) {
        $where_conditions[] = 'DATE(nl.sent_at) >= ?';
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = 'DATE(nl.sent_at) <= ?';
        $params[] = $date_to;
    }
    
    // Recipient type filter
    if (!empty($recipient_type)) {
        $where_conditions[] = 'nl.recipient_type = ?';
        $params[] = $recipient_type;
    }
    
    // Search filter
    if (!empty($subject_search)) {
        $where_conditions[] = '(nl.subject LIKE ? OR nl.message LIKE ?)';
        $params[] = '%' . $subject_search . '%';
        $params[] = '%' . $subject_search . '%';
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count (without filters)
    $total_sql = "SELECT COUNT(*) as total FROM tnotification_log";
    $total_stmt = $dbo->prepare($total_sql);
    $total_stmt->execute();
    $total_records = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get filtered count
    $filtered_sql = "SELECT COUNT(*) as total FROM tnotification_log nl LEFT JOIN tusers u ON nl.sent_by = u.id WHERE $where_clause";
    $filtered_stmt = $dbo->prepare($filtered_sql);
    $filtered_stmt->execute($params);
    $filtered_records = $filtered_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get notifications with sender info and deleted info
    $sql = "SELECT nl.*, u.name as sender_name, du.name as deleted_by_name
            FROM tnotification_log nl 
            LEFT JOIN tusers u ON nl.sent_by = u.id 
            LEFT JOIN tusers du ON nl.deleted_by = du.id
            WHERE $where_clause
            ORDER BY nl.sent_at DESC 
            LIMIT $length OFFSET $start";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formatted_notifications = [];
    foreach ($notifications as $notification) {
        $formatted_notifications[] = [
            'id' => (int)$notification['id'],
            'subject' => htmlspecialchars($notification['subject']),
            'sender_name' => htmlspecialchars($notification['sender_name'] ?: 'Unknown'),
            'recipient_type' => $notification['recipient_type'],
            'recipient_count' => (int)$notification['recipient_count'],
            'attachments_count' => (int)$notification['attachments_count'],
            'failed_count' => (int)$notification['failed_count'],
            'sent_at' => $notification['sent_at'],
            'sent_at_formatted' => date('M j, Y g:i A', strtotime($notification['sent_at'])),
            'message_preview' => strlen($notification['message']) > 100 ? 
                                substr(strip_tags($notification['message']), 0, 100) . '...' : 
                                strip_tags($notification['message']),
            'deleted' => (int)$notification['deleted'],
            'deleted_by' => $notification['deleted_by'],
            'deleted_by_name' => htmlspecialchars($notification['deleted_by_name'] ?: ''),
            'deleted_at' => $notification['deleted_at']
        ];
    }
    
    // Return DataTables format
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $total_records,
        'recordsFiltered' => $filtered_records,
        'data' => $formatted_notifications
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Error: ' . $e->getMessage()
    ]);
}
?>
