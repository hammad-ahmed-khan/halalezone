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
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }
    
    $user_id = $myuser->userdata['id'];
    
    // Get pagination and filter parameters
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = 10; // Notifications per page
    $offset = ($page - 1) * $limit;
    
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $subject_search = $_POST['subject_search'] ?? '';
    
    // Build WHERE clause
    $where_conditions = [
        'nr.user_id = ?',
        'nr.status = "sent"', // Only successfully sent notifications
        'nl.deleted = 0' // Not deleted notifications
    ];
    $params = [$user_id];
    
    // Date filters
    if (!empty($date_from)) {
        $where_conditions[] = 'DATE(nl.sent_at) >= ?';
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = 'DATE(nl.sent_at) <= ?';
        $params[] = $date_to;
    }
    
    // Search filter
    if (!empty($subject_search)) {
        $where_conditions[] = '(nl.subject LIKE ? OR nl.message LIKE ?)';
        $params[] = '%' . $subject_search . '%';
        $params[] = '%' . $subject_search . '%';
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total 
                  FROM tnotification_recipients nr 
                  INNER JOIN tnotification_log nl ON nr.notification_log_id = nl.id 
                  WHERE $where_clause";
    $count_stmt = $dbo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get notifications
    $sql = "SELECT nl.id, nl.subject, nl.message, nl.sent_at, nl.attachments_count, nl.recipient_type,
                   u.name as sender_name, u.email as sender_email,
                   nr.sent_at as delivered_at
            FROM tnotification_recipients nr 
            INNER JOIN tnotification_log nl ON nr.notification_log_id = nl.id 
            LEFT JOIN tusers u ON nl.sent_by = u.id
            WHERE $where_clause
            ORDER BY nl.sent_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format notifications
    $formatted_notifications = [];
    foreach ($notifications as $notification) {
        // Create message preview
        $message_text = strip_tags($notification['message']);
        $is_truncated = strlen($message_text) > 200;
        $message_preview = $is_truncated ? 
            substr($message_text, 0, 200) . '...' : 
            $message_text;
        
        // Format recipient type
        $recipient_type_labels = [
            'all_clients' => 'All Clients',
            'all_team' => 'All Team',
            'all_both' => 'All Users',
            'specific' => 'Selected Recipients'
        ];
        
        $formatted_notifications[] = [
            'id' => (int)$notification['id'],
            'subject' => htmlspecialchars($notification['subject']),
            'message_preview' => nl2br(htmlspecialchars($message_preview)),
            'is_truncated' => $is_truncated,
            'sent_at' => $notification['sent_at'],
            'sent_at_formatted' => date('M j, Y g:i A', strtotime($notification['sent_at'])),
            'attachments_count' => (int)$notification['attachments_count'],
            'recipient_type' => $notification['recipient_type'],
            'recipient_type_label' => $recipient_type_labels[$notification['recipient_type']] ?? ucfirst($notification['recipient_type']),
            'sender_name' => htmlspecialchars($notification['sender_name'] ?: 'System'),
            'sender_email' => htmlspecialchars($notification['sender_email'] ?: ''),
            'delivered_at' => $notification['delivered_at']
        ];
    }
    
    // Calculate if there are more pages
    $has_more = ($offset + $limit) < $total_records;
    
    echo json_encode([
        'success' => true,
        'notifications' => $formatted_notifications,
        'total_records' => $total_records,
        'current_page' => $page,
        'has_more' => $has_more
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
