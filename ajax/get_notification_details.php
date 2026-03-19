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
    
    // Check if user has admin access
    if ($myuser->userdata['isclient'] == '1') {
        echo '<div class="alert alert-danger">Access denied</div>';
        exit();
    }
    
    $notification_id = $_POST['id'] ?? 0;
    
    if (!$notification_id) {
        echo '<div class="alert alert-danger">Invalid notification ID</div>';
        exit();
    }
    
    // Get notification details
    $sql = "SELECT nl.*, u.name as sender_name, u.email as sender_email, du.name as deleted_by_name
            FROM tnotification_log nl 
            LEFT JOIN tusers u ON nl.sent_by = u.id 
            LEFT JOIN tusers du ON nl.deleted_by = du.id
            WHERE nl.id = ?";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute([$notification_id]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$notification) {
        echo '<div class="alert alert-danger">Notification not found</div>';
        exit();
    }
    
    // Format recipient type
    $recipient_type_label = '';
    switch ($notification['recipient_type']) {
        case 'all_clients':
            $recipient_type_label = 'All Clients';
            break;
        case 'all_team':
            $recipient_type_label = 'All Team Members';
            break;
        case 'all_both':
            $recipient_type_label = 'All Clients & Team';
            break;
        case 'specific':
            $recipient_type_label = 'Specific Recipients';
            break;
        default:
            $recipient_type_label = ucfirst($notification['recipient_type']);
    }
    
    // Calculate success rate
    $success_rate = 0;
    if ($notification['recipient_count'] > 0) {
        $success_rate = round((($notification['recipient_count'] - $notification['failed_count']) / $notification['recipient_count']) * 100, 1);
    }
    
    ?>
    <div class="row">
        <div class="col-md-6">
            <h5><i class="fa fa-info-circle"></i> Notification Information</h5>
            <table class="table table-condensed">
                <tr>
                    <td><strong>ID:</strong></td>
                    <td>#<?php echo $notification['id']; ?></td>
                </tr>
                <tr>
                    <td><strong>Subject:</strong></td>
                    <td><?php echo htmlspecialchars($notification['subject']); ?></td>
                </tr>
                <tr>
                    <td><strong>Sent by:</strong></td>
                    <td><?php echo htmlspecialchars($notification['sender_name'] ?: 'Unknown'); ?> 
                        <?php if ($notification['sender_email']): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($notification['sender_email']); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Sent at:</strong></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($notification['sent_at'])); ?></td>
                </tr>
                <?php if ($notification['deleted']): ?>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><span class="label label-danger">DELETED</span></td>
                </tr>
                <tr>
                    <td><strong>Deleted by:</strong></td>
                    <td><?php echo htmlspecialchars($notification['deleted_by_name'] ?: 'Unknown'); ?>
                        <?php if ($notification['deleted_at']): ?>
                            <br><small class="text-muted">on <?php echo date('M j, Y g:i A', strtotime($notification['deleted_at'])); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Recipient Type:</strong></td>
                    <td><?php echo $recipient_type_label; ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5><i class="fa fa-bar-chart"></i> Delivery Statistics</h5>
            <table class="table table-condensed">
                <tr>
                    <td><strong>Total Recipients:</strong></td>
                    <td><span class="label label-info"><?php echo $notification['recipient_count']; ?></span></td>
                </tr>
                <tr>
                    <td><strong>Successfully Sent:</strong></td>
                    <td><span class="label label-success"><?php echo ($notification['recipient_count'] - $notification['failed_count']); ?></span></td>
                </tr>
                <tr>
                    <td><strong>Failed:</strong></td>
                    <td><span class="label label-danger"><?php echo $notification['failed_count']; ?></span></td>
                </tr>
                <tr>
                    <td><strong>Success Rate:</strong></td>
                    <td>
                        <div class="progress" style="margin-bottom: 0;">
                            <div class="progress-bar progress-bar-success" style="width: <?php echo $success_rate; ?>%">
                                <?php echo $success_rate; ?>%
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><strong>Attachments:</strong></td>
                    <td>
                        <?php if ($notification['attachments_count'] > 0): ?>
                            <i class="fa fa-paperclip"></i> <?php echo $notification['attachments_count']; ?> files
                        <?php else: ?>
                            <span class="text-muted">None</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <h5><i class="fa fa-envelope"></i> Message Content</h5>
            <div class="well" style="max-height: 300px; overflow-y: auto;">
                <?php echo $notification['message']; ?>
            </div>
        </div>
    </div>
    
    <?php if ($notification['attachments_count'] > 0): ?>
    <div class="row">
        <div class="col-md-12">
            <h5><i class="fa fa-paperclip text-primary"></i> Attachments (<?php echo $notification['attachments_count']; ?>)</h5>
            <?php
            // Get attachment details - WITH DEBUGGING
            $attachments_sql = "SELECT file_name, original_name, file_size, file_type, uploaded_at, file_path FROM tnotification_attachments WHERE notification_log_id = ? ORDER BY uploaded_at DESC";
            $attachments_stmt = $dbo->prepare($attachments_sql);
            $attachments_stmt->execute([$notification_id]);
            $attachments = $attachments_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // DEBUG: Show query info
            echo "<div class='alert alert-info'><small>";
            echo "<strong>DEBUG:</strong> Notification ID: " . $notification_id . "<br>";
            echo "<strong>Attachments Count in Log:</strong> " . $notification['attachments_count'] . "<br>";
            echo "<strong>Query Result Count:</strong> " . count($attachments) . "<br>";
            if (!empty($attachments)) {
                echo "<strong>Sample attachment data:</strong><br>";
                echo "<pre>" . print_r($attachments[0], true) . "</pre>";
            }
            echo "</small></div>";
            
            if (!empty($attachments)): ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-right" style="margin-bottom: 10px;">
                            <a href="ajax/download_notification_attachments.php?id=<?php echo $notification_id; ?>" 
                               class="btn btn-primary btn-sm" target="_blank">
                                <i class="fa fa-download"></i> Download All Attachments
                                <?php if (count($attachments) > 1): ?>
                                    (ZIP)
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-condensed table-striped">
                        <thead>
                            <tr>
                                <th><i class="fa fa-file"></i> File Name</th>
                                <th><i class="fa fa-hdd-o"></i> Size</th>
                                <th><i class="fa fa-file-text"></i> Type</th>
                                <th><i class="fa fa-calendar"></i> Uploaded</th>
                                <th><i class="fa fa-download"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attachments as $attachment): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $extension = strtolower(pathinfo($attachment['original_name'], PATHINFO_EXTENSION));
                                    $icon_class = 'fa-file-o';
                                    
                                    switch($extension) {
                                        case 'pdf': $icon_class = 'fa-file-pdf-o'; break;
                                        case 'doc': case 'docx': $icon_class = 'fa-file-word-o'; break;
                                        case 'xls': case 'xlsx': $icon_class = 'fa-file-excel-o'; break;
                                        case 'ppt': case 'pptx': $icon_class = 'fa-file-powerpoint-o'; break;
                                        case 'jpg': case 'jpeg': case 'png': case 'gif': $icon_class = 'fa-file-image-o'; break;
                                        case 'zip': case 'rar': case '7z': $icon_class = 'fa-file-archive-o'; break;
                                        case 'txt': $icon_class = 'fa-file-text-o'; break;
                                    }
                                    ?>
                                    <i class="fa <?php echo $icon_class; ?>"></i>
                                    <?php echo htmlspecialchars($attachment['original_name']); ?>
                                </td>
                                <td><?php echo formatBytes($attachment['file_size']); ?></td>
                                <td><?php echo htmlspecialchars($attachment['file_type'] ?: 'Unknown'); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($attachment['uploaded_at'])); ?></td>
                                <td>
                                    <a href="ajax/download_notification_attachments.php?id=<?php echo $notification_id; ?>&file=<?php echo urlencode($attachment['file_name']); ?>" 
                                       class="btn btn-xs btn-success" target="_blank" title="Download <?php echo htmlspecialchars($attachment['original_name']); ?>">
                                        <i class="fa fa-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Fallback for older notifications without detailed attachment data -->
                <div class="alert alert-warning">
                    <h6><i class="fa fa-info-circle"></i> Legacy Attachment Information</h6>
                    <p><strong>This notification has <?php echo $notification['attachments_count']; ?> attachment(s)</strong>, but detailed information is not available.</p>
                    <p><small>This is from an older notification sent before the enhanced attachment tracking system was implemented. The files were sent successfully but cannot be downloaded from this interface.</small></p>
                    
                    <?php if ($notification['attachments_count'] > 0): ?>
                    <div style="margin-top: 15px;">
                        <h6>What you can do:</h6>
                        <ul style="margin-bottom: 0;">
                            <li>Contact the sender (<?php echo htmlspecialchars($notification['sender_name']); ?>) to request the files again</li>
                            <li>Check your email inbox for the original notification with attachments</li>
                            <li>Future notifications will have full download capabilities</li>
                        </ul>
                    </div>
                    <?php endif; ?>
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
    
    <div class="row">
        <div class="col-md-12">
            <h5><i class="fa fa-users text-success"></i> Recipients List (<?php echo count($recipients_list ?? []); ?> total)</h5>
            <?php
            // Get recipient details with user information
            $recipients_sql = "SELECT nr.user_id, nr.email, nr.status, nr.error_message, nr.sent_at, u.name as user_name, u.isclient, u.prefix 
                             FROM tnotification_recipients nr 
                             LEFT JOIN tusers u ON nr.user_id = u.id 
                             WHERE nr.notification_log_id = ? 
                             ORDER BY nr.status DESC, u.isclient ASC, u.name ASC, nr.email ASC";
            $recipients_stmt = $dbo->prepare($recipients_sql);
            $recipients_stmt->execute([$notification_id]);
            $recipients_list = $recipients_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($recipients_list)): 
                // Group recipients by status and type for better organization
                $sent_recipients = array_filter($recipients_list, function($r) { return $r['status'] === 'sent'; });
                $failed_recipients = array_filter($recipients_list, function($r) { return $r['status'] === 'failed'; });
                
                // Further group by user type
                $clients_sent = array_filter($sent_recipients, function($r) { return $r['isclient'] == '1'; });
                $team_sent = array_filter($sent_recipients, function($r) { return $r['isclient'] == '0'; });
                $auditors_sent = array_filter($sent_recipients, function($r) { return $r['isclient'] == '2'; });
                
                $clients_failed = array_filter($failed_recipients, function($r) { return $r['isclient'] == '1'; });
                $team_failed = array_filter($failed_recipients, function($r) { return $r['isclient'] == '0'; });
                $auditors_failed = array_filter($failed_recipients, function($r) { return $r['isclient'] == '2'; });
            ?>
                
                <!-- Summary Cards -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="panel panel-success">
                            <div class="panel-body text-center">
                                <h4 class="text-success"><?php echo count($sent_recipients); ?></h4>
                                <small>Successfully Sent</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-danger">
                            <div class="panel-body text-center">
                                <h4 class="text-danger"><?php echo count($failed_recipients); ?></h4>
                                <small>Failed to Send</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-info">
                            <div class="panel-body text-center">
                                <h4 class="text-info"><?php echo count($clients_sent) + count($clients_failed); ?></h4>
                                <small>Total Clients</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel panel-warning">
                            <div class="panel-body text-center">
                                <h4 class="text-warning"><?php echo count($team_sent) + count($team_failed) + count($auditors_sent) + count($auditors_failed); ?></h4>
                                <small>Total Team</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter Options -->
                <div class="row" style="margin-bottom: 15px;">
    <div class="col-md-8">
        <div class="btn-group btn-group-sm" data-toggle="buttons">
            <label class="btn btn-default active">
                <input type="radio" name="recipientFilter" value="all" checked> 
                <i class="fa fa-list"></i> All Recipients
            </label>
            <label class="btn btn-success">
                <input type="radio" name="recipientFilter" value="sent"> 
                <i class="fa fa-check"></i> Delivered Only
            </label>
            <label class="btn btn-danger">
                <input type="radio" name="recipientFilter" value="failed"> 
                <i class="fa fa-times"></i> Failed Only
            </label>
            <label class="btn btn-info">
                <input type="radio" name="recipientFilter" value="clients"> 
                <i class="fa fa-users"></i> Clients Only
            </label>
            <label class="btn btn-warning">
                <input type="radio" name="recipientFilter" value="team"> 
                <i class="fa fa-cogs"></i> Team Only
            </label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="input-group input-group-sm">
            <span class="input-group-addon">
                <i class="fa fa-search"></i>
            </span>
            <input type="text" class="form-control" id="recipientSearch" placeholder="Search recipients, emails...">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" id="clearSearch">
                    <i class="fa fa-times"></i>
                </button>
            </span>
        </div>
    </div>
</div>
                
                
                <!-- Recipients Table -->
                <div class="table-responsive">
                    <table class="table table-condensed table-striped" id="recipientsTable" data-notification-id="<?php echo $notification_id; ?>">
                        <thead>
                            <tr>
                                <th style="width:215px;"><i class="fa fa-user"></i> Recipient</th>
                                <th><i class="fa fa-envelope"></i> Email</th>
                                <th><i class="fa fa-users"></i> Type</th>
                                <th><i class="fa fa-info-circle"></i> Status</th>
                                <th><i class="fa fa-clock-o"></i> Sent At</th>
                                <th><i class="fa fa-comment"></i> Notes</th>
                            </tr>
                        </thead>
                        <tbody> 
                        </tbody>
                    </table>
                </div>
                
                <!-- Enhanced Summary -->
                <div class="row" style="margin-top: 15px;">
                    <div class="col-md-12">
                        <div class="well well-sm">
                            <strong>Delivery Summary:</strong>
                            <?php 
                            $status_counts = array_count_values(array_column($recipients_list, 'status'));
                            $sent_count = $status_counts['sent'] ?? 0;
                            $failed_count = $status_counts['failed'] ?? 0;
                            $total_count = count($recipients_list);
                            $success_rate = $total_count > 0 ? round(($sent_count / $total_count) * 100, 1) : 0;
                            ?>
                            <span class="text-success"><i class="fa fa-check"></i> <?php echo $sent_count; ?> delivered</span>
                            <?php if ($failed_count > 0): ?>
                                • <span class="text-danger"><i class="fa fa-times"></i> <?php echo $failed_count; ?> failed</span>
                            <?php endif; ?>
                            • <strong><?php echo $success_rate; ?>% success rate</strong>
                            
                            <?php if (count($clients_sent) + count($clients_failed) > 0): ?>
                                <br><small class="text-muted">
                                    <i class="fa fa-users text-info"></i> Clients: <?php echo count($clients_sent); ?> delivered
                                    <?php if (count($clients_failed) > 0): ?>
                                        , <?php echo count($clients_failed); ?> failed
                                    <?php endif; ?>
                                    
                                    <?php if (count($team_sent) + count($team_failed) + count($auditors_sent) + count($auditors_failed) > 0): ?>
                                        • <i class="fa fa-cogs text-success"></i> Team: <?php echo count($team_sent) + count($auditors_sent); ?> delivered
                                        <?php if (count($team_failed) + count($auditors_failed) > 0): ?>
                                            , <?php echo count($team_failed) + count($auditors_failed); ?> failed
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    <strong>No recipient information available.</strong>
                    This might be from an older notification before detailed recipient tracking was implemented.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($notification['failed_count'] > 0): ?>
    <div class="row">
        <div class="col-md-12">
            <h5><i class="fa fa-exclamation-triangle text-warning"></i> Failed Email Details</h5>
            <?php
            // Get failed email details
            $failed_sql = "SELECT email, error_message, sent_at FROM tnotification_recipients WHERE notification_log_id = ? AND status = 'failed'";
            $failed_stmt = $dbo->prepare($failed_sql);
            $failed_stmt->execute([$notification_id]);
            $failed_emails = $failed_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($failed_emails)): ?>
                <div class="table-responsive">
                    <table class="table table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Error Message</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($failed_emails as $failed): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($failed['email']); ?></td>
                                <td><small class="text-danger"><?php echo htmlspecialchars($failed['error_message']); ?></small></td>
                                <td><small><?php echo date('M j, Y g:i A', strtotime($failed['sent_at'])); ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <small>No detailed error information available. This might be from an older notification before detailed logging was implemented.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
