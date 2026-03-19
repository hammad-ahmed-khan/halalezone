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
    
    $recipient_type = $_POST['recipient_type'] ?? '';
    $count = 0;
    
    switch ($recipient_type) {
        case 'all_clients':
            // Get all active clients
            $sql = "SELECT COUNT(*) as count FROM tusers WHERE isclient = 1 AND deleted = 0 AND email IS NOT NULL AND email != ''";
            break;
            
        case 'all_team':
            // Get all active team members and auditors
            $sql = "SELECT COUNT(*) as count FROM tusers WHERE (isclient = 0 OR isclient = 2) AND deleted = 0 AND email IS NOT NULL AND email != ''";
            break;
            
        case 'all_both':
            // Get all active users with emails
            $sql = "SELECT COUNT(*) as count FROM tusers WHERE deleted = 0 AND email IS NOT NULL AND email != ''";
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid recipient type']);
            exit();
    }
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    
    echo json_encode([
        'success' => true,
        'count' => $count,
        'recipient_type' => $recipient_type
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
