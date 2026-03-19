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
    
    // Get form data
    $recipient_type = $_POST['recipient_type'] ?? '';
    $selected_clients = $_POST['selected_clients'] ?? [];
    $selected_team = $_POST['selected_team'] ?? [];
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $priority = $_POST['priority'] ?? 'normal';
    $send_copy = isset($_POST['send_copy']) ? ($_POST['send_copy'] ? 1 : 0) : 0;
    
    // Create draft data
    $specific_recipients = [
        'recipient_type' => $recipient_type,
        'selected_clients' => $selected_clients,
        'selected_team' => $selected_team
    ];
    
    // Save to database
    $sql = "INSERT INTO tnotification_drafts (created_by, draft_name, recipient_type, specific_recipients, subject, message, priority, send_copy, created_at, updated_at) 
            VALUES (:created_by, :draft_name, :recipient_type, :specific_recipients, :subject, :message, :priority, :send_copy, NOW(), NOW())
            ON DUPLICATE KEY UPDATE 
            recipient_type = VALUES(recipient_type),
            specific_recipients = VALUES(specific_recipients),
            subject = VALUES(subject),
            message = VALUES(message),
            priority = VALUES(priority),
            send_copy = VALUES(send_copy),
            updated_at = NOW()";
    
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':created_by', $myuser->userdata['id']);
    $stmt->bindValue(':draft_name', 'Auto-saved Draft ' . date('Y-m-d H:i:s'));
    $stmt->bindValue(':recipient_type', $recipient_type);
    $stmt->bindValue(':specific_recipients', json_encode($specific_recipients));
    $stmt->bindValue(':subject', $subject);
    $stmt->bindValue(':message', $message);
    $stmt->bindValue(':priority', $priority);
    $stmt->bindValue(':send_copy', $send_copy);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Draft saved successfully',
            'draft_id' => $dbo->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save draft'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
