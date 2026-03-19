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
    
    // Check if user has super admin access (assuming super admin has isclient = 0 and additional permission)
    if ($myuser->userdata['isclient'] == '1') {
        echo json_encode(['success' => false, 'message' => 'Access denied. Only Super Admins can delete notifications.']);
        exit();
    }
    
    // Additional check for super admin - you can modify this based on your user permission system
    // For now, checking if user has admin access and isn't a client
    $is_super_admin = ($myuser->userdata['isclient'] == '0'); // Modify this condition as needed
    
    if (!$is_super_admin) {
        echo json_encode(['success' => false, 'message' => 'Access denied. Only Super Admins can delete notifications.']);
        exit();
    }
    
    $notification_id = $_POST['id'] ?? 0;
    
    if (!$notification_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
        exit();
    }
    
    // Check if notification exists and is not already deleted
    $check_sql = "SELECT id, subject, deleted FROM tnotification_log WHERE id = ?";
    $check_stmt = $dbo->prepare($check_sql);
    $check_stmt->execute([$notification_id]);
    $notification = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notification) {
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
        exit();
    }
    
    if ($notification['deleted'] == 1) {
        echo json_encode(['success' => false, 'message' => 'Notification is already deleted']);
        exit();
    }
    
    // Soft delete the notification
    $delete_sql = "UPDATE tnotification_log SET deleted = 1, deleted_by = ?, deleted_at = NOW() WHERE id = ?";
    $delete_stmt = $dbo->prepare($delete_sql);
    $delete_stmt->execute([$myuser->userdata['id'], $notification_id]);
    
    if ($delete_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Notification deleted successfully',
            'notification_id' => $notification_id,
            'notification_subject' => $notification['subject']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
