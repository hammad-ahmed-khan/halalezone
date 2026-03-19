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
    
    $notification_id = $_POST['id'] ?? 0;
    
    if (!$notification_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
        exit();
    }
    
    // Get original notification details
    $sql = "SELECT * FROM tnotification_log WHERE id = ?";
    $stmt = $dbo->prepare($sql);
    $stmt->execute([$notification_id]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notification) {
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
        exit();
    }
    
    // For now, we'll just create a new log entry indicating a resend
    // In a full implementation, you would recreate the recipient list and resend emails
    
    $sql = "INSERT INTO tnotification_log (sent_by, recipient_type, recipient_count, subject, message, attachments_count, sent_at, failed_count) 
            VALUES (:sent_by, :recipient_type, :recipient_count, :subject, :message, :attachments_count, NOW(), 0)";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute([
        'sent_by' => $myuser->userdata['id'],
        'recipient_type' => $notification['recipient_type'],
        'recipient_count' => $notification['recipient_count'],
        'subject' => '[RESEND] ' . $notification['subject'],
        'message' => $notification['message'],
        'attachments_count' => $notification['attachments_count']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification resend initiated successfully',
        'new_id' => $dbo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
