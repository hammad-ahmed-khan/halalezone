<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
    $myuser = cuser::singleton();
    $myuser->getUserData();
    
    // Check if user is logged in
    if (empty($myuser->userdata['id'])) {
        header('HTTP/1.0 401 Unauthorized');
        echo 'Please log in to access attachments';
        exit();
    }
    
    $notification_id = $_GET['id'] ?? 0;
    $file_name = $_GET['file'] ?? '';
    
    if (!$notification_id) {
        header('HTTP/1.0 400 Bad Request');
        echo 'Invalid notification ID';
        exit();
    }
    
    // Check if user can access this notification (basic security)
    $check_sql = "SELECT id FROM tnotification_log WHERE id = ? AND deleted = 0";
    $check_stmt = $dbo->prepare($check_sql);
    $check_stmt->execute([$notification_id]);
    if (!$check_stmt->fetch()) {
        header('HTTP/1.0 404 Not Found');
        echo 'Notification not found';
        exit();
    }
    
    // Get attachments
    $sql = "SELECT file_name, file_path, original_name 
            FROM tnotification_attachments na 
            WHERE na.notification_log_id = ?";
    
    // If specific file requested, filter by file name
    if (!empty($file_name)) {
        $sql .= " AND na.file_name = ?";
        $stmt = $dbo->prepare($sql);
        $stmt->execute([$notification_id, $file_name]);
    } else {
        $stmt = $dbo->prepare($sql);
        $stmt->execute([$notification_id]);
    }
    
    $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($attachments)) {
        header('HTTP/1.0 404 Not Found');
        echo 'No attachments found';
        exit();
    }
    
    // If only one attachment, download directly
    if (count($attachments) === 1) {
        $attachment = $attachments[0];
        $file_path = $attachment['file_path'];
        
        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $attachment['original_name'] . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit();
        } else {
            header('HTTP/1.0 404 Not Found');
            echo 'File not found';
            exit();
        }
    }
    
    // Multiple attachments - create a ZIP file
    $zip = new ZipArchive();
    $zip_filename = 'notification_' . $notification_id . '_attachments_' . date('Y-m-d_H-i-s') . '.zip';
    $zip_path = sys_get_temp_dir() . '/' . $zip_filename;
    
    if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
        header('HTTP/1.0 500 Internal Server Error');
        echo 'Cannot create ZIP file';
        exit();
    }
    
    foreach ($attachments as $attachment) {
        if (file_exists($attachment['file_path'])) {
            $zip->addFile($attachment['file_path'], $attachment['original_name']);
        }
    }
    
    $zip->close();
    
    if (file_exists($zip_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zip_path));
        readfile($zip_path);
        
        // Clean up temporary file
        unlink($zip_path);
        exit();
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        echo 'Failed to create ZIP file';
        exit();
    }
    
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Error: ' . $e->getMessage();
}
?>
