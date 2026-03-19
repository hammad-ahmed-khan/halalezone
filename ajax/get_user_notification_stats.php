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
    
    // Get total notifications received
    $total_sql = "SELECT COUNT(*) as total 
                  FROM tnotification_recipients nr 
                  INNER JOIN tnotification_log nl ON nr.notification_log_id = nl.id 
                  WHERE nr.user_id = ? AND nr.status = 'sent' AND nl.deleted = 0";
    $total_stmt = $dbo->prepare($total_sql);
    $total_stmt->execute([$user_id]);
    $total = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get this month's notifications
    $month_sql = "SELECT COUNT(*) as total 
                  FROM tnotification_recipients nr 
                  INNER JOIN tnotification_log nl ON nr.notification_log_id = nl.id 
                  WHERE nr.user_id = ? AND nr.status = 'sent' AND nl.deleted = 0 
                  AND MONTH(nl.sent_at) = MONTH(CURRENT_DATE()) 
                  AND YEAR(nl.sent_at) = YEAR(CURRENT_DATE())";
    $month_stmt = $dbo->prepare($month_sql);
    $month_stmt->execute([$user_id]);
    $this_month = $month_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get this week's notifications
    $week_sql = "SELECT COUNT(*) as total 
                 FROM tnotification_recipients nr 
                 INNER JOIN tnotification_log nl ON nr.notification_log_id = nl.id 
                 WHERE nr.user_id = ? AND nr.status = 'sent' AND nl.deleted = 0 
                 AND WEEK(nl.sent_at) = WEEK(CURRENT_DATE()) 
                 AND YEAR(nl.sent_at) = YEAR(CURRENT_DATE())";
    $week_stmt = $dbo->prepare($week_sql);
    $week_stmt->execute([$user_id]);
    $this_week = $week_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get notifications with attachments
    $attachments_sql = "SELECT COUNT(*) as total 
                       FROM tnotification_recipients nr 
                       INNER JOIN tnotification_log nl ON nr.notification_log_id = nl.id 
                       WHERE nr.user_id = ? AND nr.status = 'sent' AND nl.deleted = 0 
                       AND nl.attachments_count > 0";
    $attachments_stmt = $dbo->prepare($attachments_sql);
    $attachments_stmt->execute([$user_id]);
    $with_attachments = $attachments_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total' => (int)$total,
            'this_month' => (int)$this_month,
            'this_week' => (int)$this_week,
            'with_attachments' => (int)$with_attachments
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
