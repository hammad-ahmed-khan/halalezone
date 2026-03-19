<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
    $myuser = cuser::singleton();
    $myuser->getUserData();
    
    // Check if user has admin access
    if ($myuser->userdata['isclient'] == '1') {
        echo '<div class="alert alert-danger">Access denied</div>';
        exit();
    }
    
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Format the message (convert line breaks to HTML)
    $formatted_message = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    
    // Allow basic HTML tags
    $allowed_tags = '<br><b><strong><i><em><u><a><p><div><span><h1><h2><h3><h4><h5><h6><ul><ol><li>';
    $formatted_message = strip_tags($formatted_message, $allowed_tags);
    
    // Generate preview HTML
    $preview_html = '
        <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; line-height: 1.6;">
            <div style="background-color: #f8f9fa; padding: 10px; border-left: 4px solid #007bff; margin-bottom: 20px;">
                <strong>Subject:</strong> ' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '
            </div>
            
            <div style="background-color: white; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <div style="margin-bottom: 20px;">
                    <img src="../img/logo_email.png" alt="Halal e-Zone" style="max-height: 50px;">
                </div>
                
                <div style="color: #333;">
                    ' . $formatted_message . '
                </div>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; font-size: 12px; color: #856404;">
                <i class="fa fa-info-circle"></i> This is a preview of how your email will appear to recipients.
            </div>
        </div>
    ';
    
    echo $preview_html;
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error generating preview: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
