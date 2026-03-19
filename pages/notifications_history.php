<?php
@session_start();
include_once "config/config.php";
include_once "classes/users.php";
include_once "includes/func.php";

$db = acsessDb::singleton();
$dbo = $db->connect();
$myuser = cuser::singleton();
$myuser->getUserData();

// Check if user is logged in
if (empty($myuser->userdata['id'])) {
    header('Location: login.php');
    exit();
}

// Determine user role and configure page accordingly
$is_admin = ($myuser->userdata['isclient'] == '0');
$page_title = $is_admin ? 'Notification Management' : 'My Notifications';
$page_description = $is_admin ? 'Send, view and manage notifications' : 'Notifications and updates sent to you';
$icon = $is_admin ? 'fa-history' : 'fa-inbox';
$icon_color = $is_admin ? 'text-info' : 'text-primary';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php'); ?>
    <title><?php echo $page_title; ?> - Halal e-Zone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
    <style>
        .notification-row {
            cursor: pointer;
        }
        .notification-row:hover {
            background-color: #f5f5f5;
        }
        .status-badge {
            font-size: 11px;
            padding: 2px 6px;
        }
        .recipient-type-customers { color: #007bff; }
        .recipient-type-team { color: #28a745; }
        .recipient-type-both { color: #17a2b8; }
        .recipient-type-custom { color: #ffc107; }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stats-number {
            font-size: 2.5em;
            font-weight: bold;
        }
        .stats-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .deleted-notification {
            text-decoration: line-through;
            opacity: 0.7;
            background-color: #f9f2f4 !important;
        }
        .deleted-notification td {
            text-decoration: line-through;
            color: #999 !important;
        }
        .deleted-notification .label {
            text-decoration: none; /* Keep badges readable */
        }
        #deletedNotificationsToggle.active {
            background-color: #d9534f !important;
            border-color: #d43f3a !important;
            color: white !important;
        }
    </style>
</head>

<body>
    <?php include_once('pages/navigation.php'); ?>
    
    <div class="page-content">
      
        <!-- Breadcrumb Navigation -->
        <!--
        <div class="row">
            <div class="col-md-12">
                <ol class="breadcrumb" style="background-color: #f5f5f5; margin-bottom: 20px;">
                    <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                    <li class="active">
                        <i class="fa <?php echo $is_admin ? 'fa-bell' : 'fa-inbox'; ?>"></i> 
                        <?php echo $is_admin ? 'Notifications' : 'My Notifications'; ?>
                    </li>
                </ol>
            </div>
        </div>
        -->
        
        <div class="row">
            <div class="col-md-12">
                <div class="page-header" style="margin-top: 15px; padding-bottom: 5px; border-bottom: 1px solid #eee;">
                    <div class="row">
                        <div class="col-md-8">
                            <h2 id="pageTitle" style="margin-top: 0;">
                                <i class="fa <?php echo $icon . ' ' . $icon_color; ?>"></i> <?php echo $page_title; ?>
                                <br><small class="text-muted"><?php echo $page_description; ?></small>
                            </h2>
                        </div>
                        <div class="col-md-4 text-right" style="padding-top: 15px;">
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number" id="totalSent">-</div>
                            <div class="stats-label"><?php echo $is_admin ? 'Total Sent' : 'Total Received'; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number" id="thisMonth">-</div>
                            <div class="stats-label">This Month</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number" id="thisWeek">-</div>
                            <div class="stats-label"><?php echo $is_admin ? 'Today' : 'This Week'; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="stats-number" id="totalRecipients">-</div>
                            <div class="stats-label"><?php echo $is_admin ? 'Total Recipients' : 'With Attachments'; ?></div>
                        </div>
                    </div>
                </div>
                
                <?php if ($is_admin): ?>
                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="notificationsManagement" class="btn btn-success">
                                            <i class="fa fa-plus"></i> Send New Notification
                                        </a>
                                        <button type="button" class="btn btn-info" id="refreshBtn">
                                            <i class="fa fa-refresh"></i> Refresh
                                        </button>
                                    </div>
                                     
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- User refresh button -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <button type="button" class="btn btn-info" id="refreshBtn">
                                    <i class="fa fa-refresh"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Filters -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" href="#filtersPanel">
                                        <i class="fa fa-filter"></i> Filters
                                    </a>
                                </h4>
                            </div>
                            <div id="filtersPanel" class="panel-collapse collapse in">
                                <div class="panel-body">
                                    <form id="filtersForm">
                                        <div class="row">
                                            <?php if ($is_admin): ?>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Recipient Type:</label>
                                                    <select class="form-control" name="recipient_type">
                                                        <option value="">All Types</option>
                                                        <option value="all_clients">All Clients</option>
                                                        <option value="all_team">All Team</option>
                                                        <option value="all_both">All Users</option>
                                                        <option value="specific">Specific Recipients</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Date From:</label>
                                                    <input type="date" class="form-control" name="date_from">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Date To:</label>
                                                    <input type="date" class="form-control" name="date_to">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Search Subject:</label>
                                                    <input type="text" class="form-control" name="subject_search" placeholder="Search in subject">
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Date From:</label>
                                                    <input type="date" class="form-control" name="date_from">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Date To:</label>
                                                    <input type="date" class="form-control" name="date_to">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Search:</label>
                                                    <input type="text" class="form-control" name="subject_search" placeholder="Search in subject or message">
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-search"></i> Apply Filters
                                                </button>
                                                <button type="button" class="btn btn-default" id="clearFilters">
                                                    <i class="fa fa-times"></i> Clear Filters
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notifications Table -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title"><?php echo $is_admin ? 'Notification History' : 'My Notifications'; ?></h4>
                    </div>
                    <div class="panel-body">
                        <table id="notificationsTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <?php if ($is_admin): ?>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Recipient Type</th>
                                    <th>Recipients</th>
                                    <th>Attachments</th>
                                    <th>Sent Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                    <?php else: ?>
                                    <th>Subject & Message</th>
                                    <th>From</th>
                                    <th>Attachments</th>
                                    <th>Received</th>
                                    <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-envelope"></i> Notification Details</h4>
                </div>
                <div class="modal-body" id="notificationDetails">
                    <!-- Details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="resendBtn">
                        <i class="fa fa-repeat"></i> Resend
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
    
    <script>
    // Pass PHP variables to JavaScript
    const isAdmin = <?php echo json_encode($is_admin); ?>;
    const ajaxEndpoint = isAdmin ? 'ajax/get_notification_history.php' : 'ajax/get_user_notifications_table.php';
    const statsEndpoint = isAdmin ? 'ajax/get_notification_statistics.php' : 'ajax/get_user_notification_stats.php';
    const detailsEndpoint = isAdmin ? 'ajax/get_notification_details.php' : 'ajax/get_user_notification_details.php';
    </script>
    
    <script>
    $(document).ready(function() {
        let notificationsTable;
        let currentNotificationId = null;
        
        // Initialize DataTable
        notificationsTable = $('#notificationsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: ajaxEndpoint,
                type: 'POST',
                data: function(d) {
                    // Add filter values
                    if (isAdmin) {
                        d.recipient_type = $('select[name="recipient_type"]').val();
                        d.show_deleted = $('input[name="viewMode"]:checked').val() === 'deleted' ? 'only' : 'false';
                    }
                    d.date_from = $('input[name="date_from"]').val();
                    d.date_to = $('input[name="date_to"]').val();
                    d.subject_search = $('input[name="subject_search"]').val();
                }
            },
            columns: (function() {
                if (isAdmin) {
                    // Admin columns - full management view
                    return [
                        { data: 'id', width: '60px' },
                        { 
                            data: 'subject',
                            render: function(data, type, row) {
                                return data;
                            }
                        },
                        { 
                            data: 'recipient_type',
                            render: function(data, type, row) {
                                let icon, label, badgeClass;
                                switch(data) {
                                    case 'all_clients': 
                                        icon = 'fa-users'; 
                                        label = 'All Clients';
                                        badgeClass = 'recipient-type-customers';
                                        break;
                                    case 'all_team': 
                                        icon = 'fa-cogs'; 
                                        label = 'All Team';
                                        badgeClass = 'recipient-type-team';
                                        break;
                                    case 'all_both': 
                                        icon = 'fa-globe'; 
                                        label = 'All Users';
                                        badgeClass = 'recipient-type-both';
                                        break;
                                    case 'specific': 
                                        icon = 'fa-edit'; 
                                        label = 'Specific';
                                        badgeClass = 'recipient-type-custom';
                                        break;
                                    default:
                                        icon = 'fa-question';
                                        label = data.charAt(0).toUpperCase() + data.slice(1);
                                        badgeClass = 'recipient-type-custom';
                                }
                                return `<span class="${badgeClass}"><i class="fa ${icon}"></i> ${label}</span>`;
                            }
                        },
                        { 
                            data: 'recipient_count',
                            render: function(data, type, row) {
                                const totalRecipients = parseInt(row.recipient_count) || 0;
                                const failedCount = parseInt(row.failed_count) || 0;
                                const successCount = totalRecipients - failedCount;
                                
                                let failedBadge = '';
                                if (failedCount > 0) {
                                    failedBadge = ` <span class="label label-danger">${failedCount} failed</span>`;
                                }
                                return `<span class="label label-success">${successCount} sent</span>${failedBadge}`;
                            }
                        },
                        { 
                            data: 'attachments_count',
                            render: function(data, type, row) {
                                return data > 0 ? `<i class="fa fa-paperclip"></i> ${data}` : '<span class="text-muted">None</span>';
                            },
                            width: '80px'
                        },
                        { 
                            data: 'sent_at',
                            render: function(data, type, row) {
                                if (type === 'display' || type === 'type') {
                                    const date = new Date(data);
                                    return date.toLocaleDateString('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                }
                                return data;
                            },
                            width: '130px'
                        },
                        {
                            data: 'failed_count',
                            render: function(data, type, row) {
                                const failedCount = parseInt(data) || 0;
                                const recipientCount = parseInt(row.recipient_count) || 0;
                                
                                if (failedCount <= 0) {
                                    return '<span class="label label-success">Success</span>';
                                } else if (failedCount >= recipientCount) {
                                    return '<span class="label label-danger">Failed</span>';
                                } else {
                                    return '<span class="label label-warning">Partial</span>';
                                }
                            },
                            width: '80px'
                        },
                        { 
                            data: 'id',
                            render: function(data, type, row) {
                                let buttons = `<button class="btn btn-xs btn-info view-details" data-id="${data}">
                                    <i class="fa fa-eye"></i> Details
                                </button>`;
                                
                                // Check current view mode
                                const viewMode = $('input[name="viewMode"]:checked').val();
                                
                                if (viewMode === 'deleted') {
                                    buttons += ` <button class="btn btn-xs btn-success restore-notification" data-id="${data}">
                                        <i class="fa fa-undo"></i> Restore
                                    </button>`;
                                } else {
                                    buttons += ` <button class="btn btn-xs btn-danger delete-notification" data-id="${data}">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>`;
                                }
                                
                                return buttons;
                            },
                            orderable: false,
                            width: '120px'
                        }
                    ];
                } else {
                    // User columns - simplified inbox view (removed Recipient Type and Recipients columns)
                    return [
                        { 
                            data: 'subject',
                            render: function(data, type, row) {
                                // Show truncated message preview for users
                                const preview = row.message_preview || '';
                                return `<strong>${data}</strong><br><small class="text-muted">${preview}</small>`;
                            }
                        },
                        { 
                            data: 'sender_name',
                            render: function(data, type, row) {
                                return `<i class="fa fa-user"></i> ${data}`;
                            },
                            width: '150px'
                        },
                        { 
                            data: 'attachments_count',
                            render: function(data, type, row) {
                                return data > 0 ? `<i class="fa fa-paperclip text-primary"></i> ${data}` : '<span class="text-muted">None</span>';
                            },
                            width: '80px'
                        },
                        { 
                            data: 'sent_at',
                            render: function(data, type, row) {
                                if (type === 'display' || type === 'type') {
                                    const date = new Date(data);
                                    return date.toLocaleDateString('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    });
                                }
                                return data;
                            },
                            width: '130px'
                        },
                        { 
                            data: 'id',
                            render: function(data, type, row) {
                                return `<button class="btn btn-xs btn-info view-details" data-id="${data}">
                                    <i class="fa fa-eye"></i> View Details
                                </button>`;
                            },
                            orderable: false,
                            width: '100px'
                        }
                    ];
                }
            })(),
            order: [[0, 'desc']],
            pageLength: 25,
            language: {
                processing: '<i class="fa fa-spinner fa-spin"></i> Loading notifications...'
            },
            rowCallback: function(row, data) {
                // Add strikethrough class to deleted notifications
                if (data.deleted === 1) {
                    $(row).addClass('deleted-notification');
                }
            }
        });
        
        // Load statistics
        loadStatistics();
        
        // Event handlers
        $('#refreshBtn').click(function() {
            notificationsTable.ajax.reload();
            loadStatistics();
        });
        
        // Toggle between active and deleted notifications
        $('input[name="viewMode"]').change(function() {
            const viewMode = $(this).val();
            updateViewMode(viewMode);
            notificationsTable.ajax.reload();
        });
        
        // Handle toggle button styling
        $('#activeNotificationsToggle').click(function() {
            $(this).addClass('active').removeClass('btn-default').addClass('btn-primary');
            $('#deletedNotificationsToggle').removeClass('active').removeClass('btn-danger').addClass('btn-warning');
            $('input[name="viewMode"][value="active"]').prop('checked', true).trigger('change');
        });
        
        $('#deletedNotificationsToggle').click(function() {
            $(this).addClass('active').removeClass('btn-warning').addClass('btn-danger');
            $('#activeNotificationsToggle').removeClass('active').removeClass('btn-primary').addClass('btn-default');
            $('input[name="viewMode"][value="deleted"]').prop('checked', true).trigger('change');
        });
        
        $('#filtersForm').submit(function(e) {
            e.preventDefault();
            notificationsTable.ajax.reload();
        });
        
        $('#clearFilters').click(function() {
            $('#filtersForm')[0].reset();
            notificationsTable.ajax.reload();
        });
        
        // View notification details
        $('#notificationsTable').on('click', '.view-details', function() {
            const notificationId = $(this).data('id');
            currentNotificationId = notificationId;
            loadNotificationDetails(notificationId);
        });
        
        // Delete notification (admin only)
        $('#notificationsTable').on('click', '.delete-notification', function() {
            if (!isAdmin) {
                alert('You do not have permission to delete notifications.');
                return;
            }
            
            const notificationId = $(this).data('id');
            const row = notificationsTable.row($(this).closest('tr')).data();
            
            if (confirm(`Are you sure you want to delete the notification "${row.subject}"?\n\nThis action can be undone by restoring it later.`)) {
                deleteNotification(notificationId);
            }
        });
        
        // Restore notification (admin only)
        $('#notificationsTable').on('click', '.restore-notification', function() {
            if (!isAdmin) {
                alert('You do not have permission to restore notifications.');
                return;
            }
            
            const notificationId = $(this).data('id');
            const row = notificationsTable.row($(this).closest('tr')).data();
            
            if (confirm(`Are you sure you want to restore the notification "${row.subject}"?`)) {
                restoreNotification(notificationId);
            }
        });
        
        // Export functionality
        $('#exportBtn').click(function() {
            window.open('ajax/export_notification_history.php', '_blank');
        });
        
        // Resend notification
        $('#resendBtn').click(function() {
            if (currentNotificationId && confirm('Are you sure you want to resend this notification?')) {
                resendNotification(currentNotificationId);
            }
        });
        
        function loadStatistics() {
            $.ajax({
                url: statsEndpoint,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const stats = isAdmin ? response.statistics : response.stats;
                        if (isAdmin) {
                            $('#totalSent').text(stats.total_notifications || 0);
                            $('#thisMonth').text(stats.this_month || 0);
                            $('#thisWeek').text(stats.today || 0); // Using today since we don't have this_week
                            $('#totalRecipients').text(stats.total_emails || 0);
                        } else {
                            $('#totalSent').text(stats.total || 0);
                            $('#thisMonth').text(stats.this_month || 0);
                            $('#thisWeek').text(stats.this_week || 0);
                            $('#totalRecipients').text(stats.with_attachments || 0);
                        }
                    } else {
                        console.error('Error loading statistics:', response.message);
                        // Set default values
                        $('#totalSent').text('0');
                        $('#thisMonth').text('0');
                        $('#thisWeek').text('0');
                        $('#totalRecipients').text('0');
                    }
                },
                error: function() {
                    console.error('Error loading statistics');
                    // Set default values
                    $('#totalSent').text('0');
                    $('#thisMonth').text('0');
                    $('#thisWeek').text('0');
                    $('#totalRecipients').text('0');
                }
            });
        }
        
        function loadNotificationDetails(notificationId) {
            $.ajax({
                url: detailsEndpoint,
                type: 'POST',
                data: { id: notificationId },
                success: function(response) {
                    $('#notificationDetails').html(response);
                    $('#detailsModal').modal('show');
                    
                    // Initialize recipients table pagination after content loads
                    initializeRecipientsTable();
                },
                error: function() {
                    alert('Error loading notification details.');
                }
            });
        }
        
        function resendNotification(notificationId) {
            $.ajax({
                url: 'ajax/resend_notification.php',
                type: 'POST',
                data: { id: notificationId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Notification resent successfully!');
                        notificationsTable.ajax.reload();
                        $('#detailsModal').modal('hide');
                        loadStatistics();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error resending notification.');
                }
            });
        }
        
        // Initialize recipients table with server-side DataTables after AJAX content loads
        
        // Initialize recipients table with server-side DataTables after AJAX content loads
        function initializeRecipientsTable() {
            setTimeout(function() {
                if ($('#recipientsTable').length > 0) {
                    var notificationId = $('#recipientsTable').data('notification-id');
                    
                    console.log('Found recipients table, notification ID:', notificationId);
                    
                    if (!notificationId) {
                        console.error('No notification ID found for recipients table');
                        return;
                    }
                    
                    if (typeof $.fn.DataTable !== 'undefined' && !$.fn.DataTable.isDataTable('#recipientsTable')) {
                         var ajaxUrl = "ajax/get_recipients_datatable.php";
var recipientsDataTable = $('#recipientsTable').DataTable({
    paging: true,
    lengthChange: false,
    searching: false,
    ordering: false,
    info: true,
    responsive: true,
    autoWidth: false,
    pageLength: 15,
    pagingType: "full_numbers",
    processing: true,
    serverSide: true,
  
    ajax: {
      url: ajaxUrl,
      type: "POST",
      async: true,
        "data": function(d) {
            console.log('Sending to server:', d);
            d.notification_id = notificationId;
            d.recipient_filter = $('input[name="recipientFilter"]:checked').val() || 'all';
            d.search = $('#recipientSearch').val(); // Add search value

            return d;
        },
    },
    columns: [
    { data: "recipient" },
    { data: "email_display" }, 
    { data: "type" },
    { data: "status_display" },
    { data: "sent_date" },
    { data: "button", sClass: "text-center buttons" },
],

    columnDefs: [
      {
        targets: "no-sort",
        orderable: false,
      },
    ],
    initComplete: function () {

    },
  });

   
                        
                       $(document).off('keyup', '#recipientSearch');
$(document).off('click', '#clearSearch');

// Handle search input
$(document).on('keyup', '#recipientSearch', function() {
    recipientsDataTable.ajax.reload();
});

// Handle clear search button
$(document).on('click', '#clearSearch', function() {
    $('#recipientSearch').val('');
    recipientsDataTable.ajax.reload();
});

// Filter change handler (your existing code)
$(document).off('change', 'input[name="recipientFilter"]');
$(document).on('change', 'input[name="recipientFilter"]', function() {
    $('input[name="recipientFilter"]').closest('label').removeClass('active');
    $(this).closest('label').addClass('active');
    recipientsDataTable.ajax.reload();
});
                        $(document).off('click', '#exportRecipients');
                        $(document).on('click', '#exportRecipients', function() {
                            var filterValue = $('input[name="recipientFilter"]:checked').val();
                            var searchValue = recipientsDataTable.search();
                            var exportUrl = 'ajax/export_recipients.php?notification_id=' + notificationId;
                            if (filterValue && filterValue !== 'all') {
                                exportUrl += '&filter=' + encodeURIComponent(filterValue);
                            }
                            if (searchValue) {
                                exportUrl += '&search=' + encodeURIComponent(searchValue);
                            }
                            window.open(exportUrl, '_blank');
                        });
                        
                        console.log('Recipients server-side DataTable initialized for notification ' + notificationId);
                        
                    } else if (!$.fn.DataTable.isDataTable('#recipientsTable')) {
                        console.warn('DataTables not available');
                        $('#recipientsTable').before('<div class="alert alert-warning">DataTables not loaded.</div>');
                    }
                } else {
                    console.log('Recipients table not found in loaded content');
                }
            }, 200);
        }
        
        // Global function for error modal
        window.showErrorModal = function(errorMessage, email) {
            var modalHtml = '<div class="modal fade" id="errorModal" tabindex="-1" role="dialog">' +
                '<div class="modal-dialog" role="document"><div class="modal-content">' +
                '<div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button>' +
                '<h4 class="modal-title"><i class="fa fa-exclamation-triangle text-danger"></i> Delivery Error Details</h4></div>' +
                '<div class="modal-body"><p><strong>Recipient:</strong> ' + email + '</p>' +
                '<p><strong>Error Message:</strong></p><div class="alert alert-danger">' +
                '<i class="fa fa-times-circle"></i> ' + errorMessage + '</div>' +
                '<p class="text-muted"><small>This error occurred during the email delivery process.</small></p></div>' +
                '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>' +
                '</div></div></div>';
            $('#errorModal').remove();
            $('body').append(modalHtml);
            $('#errorModal').modal('show');
        };
        
        function deleteNotification(notificationId) {
            if (!isAdmin) {
                alert('You do not have permission to delete notifications.');
                return;
            }
            
            $.ajax({
                url: 'ajax/delete_notification.php',
                type: 'POST',
                data: { id: notificationId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Notification deleted successfully!');
                        notificationsTable.ajax.reload();
                        loadStatistics();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error deleting notification.');
                }
            });
        }
        
        function restoreNotification(notificationId) {
            if (!isAdmin) {
                alert('You do not have permission to restore notifications.');
                return;
            }
            
            $.ajax({
                url: 'ajax/restore_notification.php',
                type: 'POST',
                data: { id: notificationId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Notification restored successfully!');
                        notificationsTable.ajax.reload();
                        loadStatistics();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error restoring notification.');
                }
            });
        }
        
        function updateViewMode(mode) {
            const pageTitle = $('#pageTitle');
            const panelTitle = $('.panel-heading .panel-title');
            
            if (mode === 'deleted') {
                pageTitle.html('<i class="fa fa-trash text-danger"></i> Deleted Notifications<br><small class="text-muted">Manage deleted notifications - Super Admins can restore them</small>');
                panelTitle.text('Deleted Notification History');
                
                // Hide "Send New" button when viewing deleted notifications
                $('.col-md-4.text-right').hide();
            } else {
                pageTitle.html('<i class="fa fa-history text-info"></i> Notification History<br><small class="text-muted">View and manage sent notifications</small>');
                panelTitle.text('Notification History');
                
                // Show "Send New" button when viewing active notifications
                $('.col-md-4.text-right').show();
            }
        }
    });
    </script>
</body>
</html>
