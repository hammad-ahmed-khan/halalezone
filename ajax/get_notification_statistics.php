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
    
    $stats = [];
    
    // Total notifications sent
    $sql = "SELECT COUNT(*) as total FROM tnotification_log WHERE deleted = 0";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $stats['total_notifications'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total emails sent
    $sql = "SELECT SUM(recipient_count) as total FROM tnotification_log WHERE deleted = 0";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['total_emails'] = $result['total'] ?: 0;
    
    // Failed emails
    $sql = "SELECT SUM(failed_count) as total FROM tnotification_log WHERE deleted = 0";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['failed_emails'] = $result['total'] ?: 0;
    
    // Success rate
    $stats['success_rate'] = $stats['total_emails'] > 0 ? 
        round((($stats['total_emails'] - $stats['failed_emails']) / $stats['total_emails']) * 100, 1) : 100;
    
    // Notifications this month
    $sql = "SELECT COUNT(*) as total FROM tnotification_log WHERE deleted = 0 AND MONTH(sent_at) = MONTH(NOW()) AND YEAR(sent_at) = YEAR(NOW())";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $stats['this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Notifications today
    $sql = "SELECT COUNT(*) as total FROM tnotification_log WHERE deleted = 0 AND DATE(sent_at) = CURDATE()";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Most active sender
    $sql = "SELECT u.name, COUNT(*) as count 
            FROM tnotification_log nl 
            LEFT JOIN tusers u ON nl.sent_by = u.id 
            GROUP BY nl.sent_by 
            ORDER BY count DESC 
            LIMIT 1";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['most_active_sender'] = $result ? $result['name'] . ' (' . $result['count'] . ')' : 'N/A';
    
    // Recipient type breakdown
    $sql = "SELECT recipient_type, COUNT(*) as count FROM tnotification_log GROUP BY recipient_type";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $recipient_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats['recipient_breakdown'] = [];
    foreach ($recipient_breakdown as $type) {
        $label = '';
        switch ($type['recipient_type']) {
            case 'all_clients':
                $label = 'All Clients';
                break;
            case 'all_team':
                $label = 'All Team';
                break;
            case 'all_both':
                $label = 'All Users';
                break;
            case 'specific':
                $label = 'Specific Recipients';
                break;
            default:
                $label = ucfirst($type['recipient_type']);
        }
        $stats['recipient_breakdown'][] = [
            'type' => $type['recipient_type'],
            'label' => $label,
            'count' => $type['count']
        ];
    }
    
    // Recent activity (last 7 days)
    $sql = "SELECT DATE(sent_at) as date, COUNT(*) as count 
            FROM tnotification_log 
            WHERE deleted = 0 AND sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
            GROUP BY DATE(sent_at) 
            ORDER BY date ASC";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats['recent_activity'] = [];
    foreach ($recent_activity as $day) {
        $stats['recent_activity'][] = [
            'date' => $day['date'],
            'date_formatted' => date('M j', strtotime($day['date'])),
            'count' => $day['count']
        ];
    }
    
    // Average recipients per notification
    $stats['avg_recipients'] = $stats['total_notifications'] > 0 ? 
        round($stats['total_emails'] / $stats['total_notifications'], 1) : 0;
    
    // Notifications with attachments
    $sql = "SELECT COUNT(*) as total FROM tnotification_log WHERE  deleted = 0 AND attachments_count > 0";
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $stats['with_attachments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo json_encode([
        'success' => true,
        'statistics' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
