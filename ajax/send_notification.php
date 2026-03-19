<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";
include_once "../notifications/notifyfuncs.php";

header('Content-Type: application/json');

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
    $myuser = cuser::singleton();
    $myuser->getUserData();
    
    // Check if user has admin access
    if (!$myuser->userdata['canadmin']) {
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
    $send_copy = $_POST['send_copy'] === '1';
    $attachments = json_decode($_POST['attachments'] ?? '[]', true);
    
    // Validate required fields
    if (empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
        exit();
    }
    
    // Get recipient emails based on type - NOW WITH USER IDS
    $recipients = []; // Array of email addresses
    $recipient_users = []; // Array to map email -> user data
    
    if ($recipient_type === 'specific') {
        // Add selected clients
        if (!empty($selected_clients) && is_array($selected_clients)) {
            $client_placeholders = str_repeat('?,', count($selected_clients) - 1) . '?';
            $sql = "SELECT id, email, name FROM tusers WHERE id IN ($client_placeholders) AND isclient = 1 AND deleted = 0 AND email IS NOT NULL AND email != ''";
            $stmt = $dbo->prepare($sql);
            $stmt->execute($selected_clients);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as $row) {
                $recipients[] = $row['email'];
                $recipient_users[$row['email']] = $row; // Store user data
            }
        }
        
        // Add selected team members
        if (!empty($selected_team) && is_array($selected_team)) {
            $team_placeholders = str_repeat('?,', count($selected_team) - 1) . '?';
            $sql = "SELECT id, email, name FROM tusers WHERE id IN ($team_placeholders) AND (isclient = 0 OR isclient = 2) AND deleted = 0 AND email IS NOT NULL AND email != ''";
            $stmt = $dbo->prepare($sql);
            $stmt->execute($selected_team);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as $row) {
                $recipients[] = $row['email'];
                $recipient_users[$row['email']] = $row; // Store user data
            }
        }
    } else {
        // Get emails from database based on type
        $sql = '';
        switch ($recipient_type) {
            case 'all_clients':
                $sql = "SELECT DISTINCT id, email, name FROM tusers WHERE isclient = 1 AND deleted = 0 AND email IS NOT NULL AND email != ''";
                break;
            case 'all_team':
                $sql = "SELECT DISTINCT id, email, name FROM tusers WHERE (isclient = 0 OR isclient = 2) AND deleted = 0 AND email IS NOT NULL AND email != ''";
                break;
            case 'all_both':
                $sql = "SELECT DISTINCT id, email, name FROM tusers WHERE deleted = 0 AND email IS NOT NULL AND email != ''";
                break;
        }
        
        if (!empty($sql)) {
            $stmt = $dbo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as $row) {
                $recipients[] = $row['email'];
                $recipient_users[$row['email']] = $row; // Store user data
            }
        }
    }
    
    // Remove duplicates
    $recipients = array_unique($recipients);
    
    if (empty($recipients)) {
        echo json_encode(['success' => false, 'message' => 'No valid recipients found']);
        exit();
    }
    
    // Format message
    $formatted_message = $message;
    
    // Send emails immediately
    $sent_count = 0;
    $failed_emails = [];
    
    $from_email = "noreply@halal-digital.net";
    $from_name = "Halal Digital";
    
    foreach ($recipients as $recipient_email) {
        try {
            $body = [
                'name' => $from_name,
                'header' => $subject,
                'email' => $from_email,
                'to' => $recipient_email,
                'subject' => $subject,
                'message' => $formatted_message,
                'body' => $formatted_message
            ];
            
            // Add attachments if any
            if (!empty($attachments)) {
                if (count($attachments) === 1) {
                    // Single attachment
                    $body['attachhostpath'] = $attachments[0]['hostpath'];
                    $body['attach'] = $attachments[0]['name'];
                } else {
                    // Multiple attachments
                    $attach_array = [];
                    foreach ($attachments as $attachment) {
                        $attach_array[] = [
                            'hostpath' => $attachment['hostpath'],
                            'name' => $attachment['name']
                        ];
                    }
                    $body['attach'] = $attach_array;
                }
                
                $result = sendEmailWithAttach($body);
            } else {
                $result = sendEmail($body);
            }
            
            if ($result !== false) {
                $sent_count++;
            } else {
                $failed_emails[] = $recipient_email . ' (Email function returned false)';
            }
        } catch (Exception $e) {
            $failed_emails[] = $recipient_email . ' (Error: ' . $e->getMessage() . ')';
        }
    }
    
    // Send copy to sender if requested
    if ($send_copy && !empty($myuser->userdata['email'])) {
        try {
            $copy_body = [
                'name' => $from_name,
                'header' => '[COPY] ' . $subject,
                'email' => $from_email,
                'to' => $myuser->userdata['email'],
                'subject' => '[COPY] ' . $subject,
                'message' => $formatted_message,
                'body' => $formatted_message
            ];
            
            if (!empty($attachments)) {
                if (count($attachments) === 1) {
                    $copy_body['attachhostpath'] = $attachments[0]['hostpath'];
                    $copy_body['attach'] = $attachments[0]['name'];
                } else {
                    $attach_array = [];
                    foreach ($attachments as $attachment) {
                        $attach_array[] = [
                            'hostpath' => $attachment['hostpath'],
                            'name' => $attachment['name']
                        ];
                    }
                    $copy_body['attach'] = $attach_array;
                }
                sendEmailWithAttach($copy_body);
            } else {
                sendEmail($copy_body);
            }
        } catch (Exception $e) {
            // Copy email failure shouldn't affect the main response
        }
    }
    
    // Log the notification
    try {
        $sql = "INSERT INTO tnotification_log (sent_by, recipient_type, recipient_count, subject, message, attachments_count, sent_at, failed_count) 
                VALUES (:sent_by, :recipient_type, :recipient_count, :subject, :message, :attachments_count, NOW(), :failed_count)";
        $stmt = $dbo->prepare($sql);
        $stmt->bindValue(':sent_by', $myuser->userdata['id']);
        $stmt->bindValue(':recipient_type', $recipient_type);
        $stmt->bindValue(':recipient_count', $sent_count + count($failed_emails)); // Total attempted
        $stmt->bindValue(':subject', $subject);
        $stmt->bindValue(':message', $message);
        $stmt->bindValue(':attachments_count', count($attachments));
        $stmt->bindValue(':failed_count', count($failed_emails));
        $stmt->execute();
        
        $notification_log_id = $dbo->lastInsertId();
        
        // Save attachment information if any
        if (!empty($attachments)) {
            $attachment_sql = "INSERT INTO tnotification_attachments (notification_log_id, file_name, original_name, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?, ?)";
            $attachment_stmt = $dbo->prepare($attachment_sql);
            
            foreach ($attachments as $attachment) {
                $file_size = isset($attachment['size']) ? $attachment['size'] : (file_exists($attachment['hostpath']) ? filesize($attachment['hostpath']) : 0);
                $file_type = isset($attachment['type']) ? $attachment['type'] : mime_content_type($attachment['hostpath']);
                
                $attachment_stmt->execute([
                    $notification_log_id,
                    $attachment['name'],
                    $attachment['name'], // original_name same as name for now
                    $attachment['hostpath'],
                    $file_size,
                    $file_type
                ]);
            }
        }
        
        // Log individual failed emails with USER IDs
        if (!empty($failed_emails)) {
            $failed_sql = "INSERT INTO tnotification_recipients (notification_log_id, user_id, email, status, error_message, sent_at) VALUES (?, ?, ?, 'failed', ?, NOW())";
            $failed_stmt = $dbo->prepare($failed_sql);
            
            foreach ($failed_emails as $failed_email) {
                // Extract email and error message
                if (strpos($failed_email, ' (') !== false) {
                    $parts = explode(' (', $failed_email, 2);
                    $email = $parts[0];
                    $error = rtrim($parts[1], ')');
                } else {
                    $email = $failed_email;
                    $error = 'Unknown error';
                }
                
                // Get user ID for this email
                $user_id = isset($recipient_users[$email]) ? $recipient_users[$email]['id'] : null;
                
                $failed_stmt->execute([$notification_log_id, $user_id, $email, $error]);
            }
        }
        
        // Log successful recipients with USER IDs
        $success_sql = "INSERT INTO tnotification_recipients (notification_log_id, user_id, email, status, sent_at) VALUES (?, ?, ?, 'sent', NOW())";
        $success_stmt = $dbo->prepare($success_sql);
        
        foreach ($recipients as $recipient_email) {
            // Skip failed emails
            if (!in_array($recipient_email, array_map(function($failed) {
                return strpos($failed, ' (') !== false ? explode(' (', $failed, 2)[0] : $failed;
            }, $failed_emails))) {
                $user_id = isset($recipient_users[$recipient_email]) ? $recipient_users[$recipient_email]['id'] : null;
                $success_stmt->execute([$notification_log_id, $user_id, $recipient_email]);
            }
        }
        
    } catch (Exception $e) {
        // Log failure shouldn't affect the main response
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification sent successfully',
        'recipient_count' => $sent_count,
        'subject' => $subject,
        'attachments' => count($attachments),
        'failed_emails' => $failed_emails
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
