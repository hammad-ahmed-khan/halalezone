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
    
    // Check if user has super admin access
    if ($myuser->userdata['isclient'] == '1') {
        echo json_encode(['success' => false, 'message' => 'Access denied. Only Super Admins can restore notifications.']);
        exit();
    }
    
    $is_super_admin = ($myuser->userdata['isclient'] == '0'); // Modify this condition as needed
    
    if (!$is_super_admin) {
        echo json_encode(['success' => false, 'message' => 'Access denied. Only Super Admins can restore notifications.']);
        exit();
    }
    
    $notification_id = $_POST['id'] ?? 0;
    
    if (!$notification_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
        exit();
    }
    
    // Check if notification exists and is deleted
    $check_sql = "SELECT id, subject, deleted FROM tnotification_log WHERE id = ?";
    $check_stmt = $dbo->prepare($check_sql);
    $check_stmt->execute([$notification_id]);
    $notification = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notification) {
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
        exit();
    }
    
    if ($notification['deleted'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Notification is not deleted']);
        exit();
    }
    
    // Restore the notification
    $restore_sql = "UPDATE tnotification_log SET deleted = 0, deleted_by = NULL, deleted_at = NULL WHERE id = ?";
    $restore_stmt = $dbo->prepare($restore_sql);
    $restore_stmt->execute([$notification_id]);
    
    if ($restore_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification restored successfully',
            'notification_id' => $notification_id,
            'notification_subject' => $notification['subject']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to restore notification']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
