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
        echo '<div class="alert alert-danger">Please log in to view notification details.</div>';
        exit();
    }
    
    $notification_id = $_POST['id'] ?? 0;
    $user_id = $myuser->userdata['id'];
    
    if (!$notification_id) {
        echo '<div class="alert alert-danger">Invalid notification ID</div>';
        exit();
    }
    
    // Get notification details - only if user received it
    $sql = "SELECT nl.*, u.name as sender_name, u.email as sender_email, nr.sent_at as delivered_at
            FROM tnotification_log nl 
            INNER JOIN tnotification_recipients nr ON nl.id = nr.notification_log_id
            LEFT JOIN tusers u ON nl.sent_by = u.id 
            WHERE nl.id = ? AND nr.user_id = ? AND nr.status = 'sent' AND nl.deleted = 0";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute([$notification_id, $user_id]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notification) {
        echo '<div class="alert alert-danger">Notification not found or you do not have access to view it.</div>';
        exit();
    }
    
    // Update modal title
    echo "<script>$('#modalSubject').text('" . htmlspecialchars($notification['subject']) . "');</script>";
    
    // Format recipient type
    $recipient_type_labels = [
        'all_clients' => 'All Clients',
        'all_team' => 'All Team Members', 
        'all_both' => 'All Clients & Team',
        'specific' => 'Selected Recipients'
    ];
    $recipient_type_label = $recipient_type_labels[$notification['recipient_type']] ?? ucfirst($notification['recipient_type']);
    
    ?>
    <div class="row">
        <div class="col-md-6">
            <h5><i class="fa fa-info-circle"></i> Notification Information</h5>
            <table class="table table-condensed">
                <tr>
                    <td><strong>Subject:</strong></td>
                    <td><?php echo htmlspecialchars($notification['subject']); ?></td>
                </tr>
                <tr>
                    <td><strong>Delivered:</strong></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($notification['delivered_at'])); ?></td>
                </tr>
                
            </table>
        </div>
        <div class="col-md-6">
            <h5> </h5>
            <table class="table table-condensed">
               <tr>
                    <td><strong>From:</strong></td>
                    <td>
                        <?php echo htmlspecialchars($notification['sender_name'] ?: 'System'); ?> 
                        <?php if ($notification['sender_email']): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($notification['sender_email']); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Sent:</strong></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($notification['sent_at'])); ?></td>
                </tr>                
            </table>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <h5><i class="fa fa-envelope"></i> Message Content</h5>
            <div class="well" style="max-height: 400px; overflow-y: auto;">
                <?php echo $notification['message']; ?>
            </div>
        </div>
    </div>
    
    <?php if ($notification['attachments_count'] > 0): ?>
    <div class="row">
        <div class="col-md-12">
            <h5><i class="fa fa-paperclip text-primary"></i> Attachments (<?php echo $notification['attachments_count']; ?>)</h5>
            <?php
            // Get attachment details
            $attachments_sql = "SELECT file_name, original_name, file_size, file_type, uploaded_at FROM tnotification_attachments WHERE notification_log_id = ? ORDER BY uploaded_at DESC";
            $attachments_stmt = $dbo->prepare($attachments_sql);
            $attachments_stmt->execute([$notification_id]);
            $attachments = $attachments_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($attachments)): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-right" style="margin-bottom: 15px;">
                            <a href="ajax/download_notification_attachments.php?id=<?php echo $notification_id; ?>" 
                               class="btn btn-primary" target="_blank">
                                <i class="fa fa-download"></i> Download All Attachments
                                <?php if (count($attachments) > 1): ?>
                                    (ZIP)
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                
                <div class="row">
                    <?php foreach ($attachments as $index => $attachment): ?>
                    <div class="col-md-6" style="margin-bottom: 15px;">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="media">
                                    <div class="media-left">
                                        <?php 
                                        $extension = strtolower(pathinfo($attachment['original_name'], PATHINFO_EXTENSION));
                                        $icon_class = 'fa-file-o';
                                        $icon_color = '#6c757d';
                                        
                                        switch($extension) {
                                            case 'pdf': $icon_class = 'fa-file-pdf-o'; $icon_color = '#dc3545'; break;
                                            case 'doc': case 'docx': $icon_class = 'fa-file-word-o'; $icon_color = '#007bff'; break;
                                            case 'xls': case 'xlsx': $icon_class = 'fa-file-excel-o'; $icon_color = '#28a745'; break;
                                            case 'ppt': case 'pptx': $icon_class = 'fa-file-powerpoint-o'; $icon_color = '#fd7e14'; break;
                                            case 'jpg': case 'jpeg': case 'png': case 'gif': $icon_class = 'fa-file-image-o'; $icon_color = '#20c997'; break;
                                            case 'zip': case 'rar': case '7z': $icon_class = 'fa-file-archive-o'; $icon_color = '#6f42c1'; break;
                                            case 'txt': $icon_class = 'fa-file-text-o'; $icon_color = '#6c757d'; break;
                                        }
                                        ?>
                                        <i class="fa <?php echo $icon_class; ?> fa-3x" style="color: <?php echo $icon_color; ?>;"></i>
                                    </div>
                                    <div class="media-body">
                                        <h6 class="media-heading" style="margin-top: 0;">
                                            <?php echo htmlspecialchars($attachment['original_name']); ?>
                                        </h6>
                                        <p style="margin-bottom: 10px;">
                                            <small class="text-muted">
                                                Size: <?php echo ($attachment['file_size']); ?><br>
                                                Type: <?php echo htmlspecialchars($attachment['file_type'] ?: 'Unknown'); ?>
                                            </small>
                                        </p>
                                        <a href="ajax/download_notification_attachments.php?id=<?php echo $notification_id; ?>&file=<?php echo urlencode($attachment['file_name']); ?>" 
                                           class="btn btn-sm btn-success" target="_blank">
                                            <i class="fa fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (($index + 1) % 2 == 0): ?>
                        </div><div class="row">
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> 
                    <strong>Attachments were included</strong> but detailed information is not available. 
                    This might be from an older notification before enhanced attachment tracking was implemented.
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php 
    // Helper function to format file sizes
    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    endif; ?>
    
    <?php
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
