<?php
@session_start();
include_once "config/config.php";
include_once "classes/users.php";
include_once "includes/func.php";

$db = acsessDb::singleton();
$dbo = $db->connect();
$myuser = cuser::singleton();
$myuser->getUserData();


// Check if user has admin access
if ($myuser->userdata['isclient'] == '1') {
    header('HTTP/1.0 403 Access denied');
    include_once('pages/403.php');
    exit();
}

// Get clients and team members for dropdowns (similar to tasks.php)
$isClient = $myuser->userdata['isclient'] == "1" ? true : false;
$isAuditor = $myuser->userdata['isclient'] == '2' ? true : false;
$isAdmin = (!$isClient && !$isAuditor);

// Get team members and auditors
$sql = "SELECT id, name, email, prefix, isclient FROM tusers WHERE (isclient=0 || isclient=2) AND name <> '' AND deleted = 0 ORDER BY isclient, name";
$stmt = $dbo->prepare($sql);
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();
$auditors = $stmt->fetchAll();

// Get clients
if ($isAuditor) {
    $ids = [-1];
    $clients_audit = $myuser->userdata['clients_audit'];
    if ($clients_audit != "") {
        $ids = json_decode($clients_audit);
    }
    $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND name <> '' AND deleted = 0 AND id IN (".implode(",", $ids).") ORDER BY name";
} else {
    $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND name <> '' AND deleted = 0 ORDER BY name";
}

$clients = [];
$stmt = $dbo->prepare($sql);
$stmt->setFetchMode(PDO::FETCH_ASSOC);
if ($stmt->execute()) {
    $clients = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php'); ?>
    <title>Enhanced Email Composer - Halal e-Zone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.css" rel="stylesheet">
    <link href="css/enhanced_email_composer.css" rel="stylesheet">
    <style>
        .form-section {
            background-color: #f9f9f9;
            padding: 20px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .form-section h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
        }
       
        .chosen-container {
            width: 100% !important;
        }
        .chosen-container-multi .chosen-choices {
            border: 1px solid #ccc;
            border-radius: 4px;
            background: #fff;
            min-height: 38px;
        }
        .chosen-container-multi .chosen-choices li.search-field input[type="text"] {
            height: 32px;
        }
        .chosen-container-multi .chosen-choices li.search-choice {
            background: #337ab7;
            border: 1px solid #204d74;
            color: #fff;
            border-radius: 3px;
        }
        .chosen-container-multi .chosen-choices li.search-choice .search-choice-close {
            background-position: center;
        }
        .fileinput-button {
            position: relative;
            overflow: hidden;
            display: inline-block;
            cursor: pointer;
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            width: 100%;
            background-color: #f9f9f9;
            transition: all 0.3s ease;
        }
        .fileinput-button:hover {
            border-color: #3498db;
            background-color: #ecf0f1;
        }
        .fileinput-button input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            background: white;
            cursor: inherit;
            display: block;
        }
        .uploaded-file-name {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            margin: 5px 0;
            background-color: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 4px;
        }
        .uploaded-file-name span:first-child {
            flex-grow: 1;
            margin-right: 10px;
        }
        .loader {
            display: none;
            text-align: center;
            padding: 10px;
        }
        .email-preview {
            background-color: white;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .template-selector {
            margin-bottom: 20px;
        }
        .template-preview {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
            max-height: 200px;
            overflow-y: auto;
        }
        .editor-toolbar {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            padding: 10px;
        }
        .editor-mode-buttons {
            margin-bottom: 15px;
        }
        .editor-mode-buttons .btn {
            margin-right: 5px;
        }
        .code-editor {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            border-radius: 0 0 5px 5px;
        }
        .summernote-wrapper {
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .note-editor {
            border: none !important;
        }
        .variable-inserter {
            background-color: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .variable-inserter h5 {
            margin-bottom: 10px;
            color: #495057;
        }
        .variable-tag {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            margin: 2px;
            cursor: pointer;
            font-size: 12px;
        }
        .variable-tag:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
        }
        .responsive-preview {
            margin-top: 20px;
        }
        .device-preview {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px 0;
            overflow: hidden;
        }
        .device-preview .preview-header {
            background-color: #f8f9fa;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .device-preview iframe {
            width: 100%;
            border: none;
        }
        .desktop-preview iframe {
            height: 400px;
        }
        .mobile-preview iframe {
            height: 600px;
            max-width: 375px;
            margin: 0 auto;
            display: block;
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
                    <li><a href="notifications_history.php"><i class="fa fa-bell"></i> Notifications</a></li>
                    <li class="active"><i class="fa fa-plus"></i> Compose Email</li>
                </ol>
            </div>
        </div>
        -->
        
        <div class="row">
            <div class="col-md-12">
                <div class="page-header" style="margin-top: 15px; padding-bottom: 5px; border-bottom: 1px solid #eee;">
                    <div class="row">
                        <div class="col-md-8">
                            <h2 style="margin-top: 0;">
                                <i class="fa fa-envelope text-primary"></i> Enhanced Email Composer
                                <br><small class="text-muted">Create rich HTML emails with visual editor</small>
                            </h2>
                        </div>
                        <div class="col-md-4 text-right" style="padding-top: 15px;">
                            <a href="/notificationsHistory" class="btn btn-info">
                                <i class="fa fa-history"></i> View History
                            </a>
                            <!--
                            <button type="button" class="btn btn-success" id="saveTemplateBtn">
                                <i class="fa fa-save"></i> Save as Template
                            </button>
                            -->
                        </div>
                    </div>
                </div>
                
                <div class="panel-panel-default">
                    <div class="panel-body-">
                        <form id="emailComposerForm" enctype="multipart/form-data">
                            
                            <!-- Template Selection Section -->
                            <?php  /*             
                            <div class="form-section">
                                <h4><i class="fa fa-file-text"></i> Email Template</h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="emailTemplate">Choose Template:</label>
                                            <select class="form-control" id="emailTemplate" name="email_template">
                                                <option value="">Custom Email (Start from scratch)</option>
                                                <option value="basic">Basic Company Template</option>
                                                <option value="announcement">Announcement Template</option>
                                                <option value="newsletter">Newsletter Template</option>
                                                <?php foreach ($templates as $template): ?>
                                                    <option value="<?php echo $template['id']; ?>" 
                                                            data-subject="<?php echo htmlspecialchars($template['subject']); ?>"
                                                            data-content="<?php echo htmlspecialchars($template['content']); ?>">
                                                        <?php echo htmlspecialchars($template['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Template Actions:</label>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-default" id="loadTemplateBtn">
                                                    <i class="fa fa-download"></i> Load Template
                                                </button>
                                                <button type="button" class="btn btn-default" id="previewTemplateBtn">
                                                    <i class="fa fa-eye"></i> Preview Template
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="template-preview" id="templatePreview" style="display: none;">
                                    <!-- Template preview will be shown here -->
                                </div>
                            </div>
                             */ ?>

                            <!-- Recipients Section -->
                            <div class="form-section">
                                <h4><i class="fa fa-users"></i> Recipients</h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Send To:</strong></label>
                                            <div class="recipient-type">
                                                <label class="radio-inline">
                                                    <input type="radio" name="recipient_type" value="all_clients">
                                                    <i class="fa fa-users text-primary"></i> All Clients
                                                </label>
                                            </div>
                                            <div class="recipient-type">
                                                <label class="radio-inline">
                                                    <input type="radio" name="recipient_type" value="all_team">
                                                    <i class="fa fa-cogs text-success"></i> All Team Members
                                                </label>
                                            </div>
                                            <div class="recipient-type">
                                                <label class="radio-inline">
                                                    <input type="radio" name="recipient_type" value="all_both">
                                                    <i class="fa fa-globe text-info"></i> All Clients & Team
                                                </label>
                                            </div>
                                            <div class="recipient-type">
                                                <label class="radio-inline">
                                                    <input type="radio" name="recipient_type" value="specific">
                                                    <i class="fa fa-check-square text-warning"></i> Select Specific Recipients
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><strong>Recipient Count:</strong></label>
                                            <div class="well well-sm" id="recipientCount">
                                                <i class="fa fa-spinner fa-spin"></i> Loading recipient count...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Specific Recipients Selection -->
                                <div class="form-group" id="specificRecipientsDiv" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="selectedClients">Select Specific Clients:</label>
                                            <select class="form-control chosen-select" id="selectedClients" name="selected_clients[]" multiple="multiple" 
                                                    data-placeholder="Choose clients..." style="width: 100%;">
                                                <?php foreach ($clients as $client): ?>
                                                    <option value="<?php echo $client["id"]; ?>">
                                                        <?php echo htmlspecialchars($client["name"]); ?> - <?php echo htmlspecialchars($client["prefix"]); ?><?php echo $client["id"]; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="help-block">Search and select specific clients</small>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="selectedTeam">Select Specific Team Members:</label>
                                            <select class="form-control chosen-select" id="selectedTeam" name="selected_team[]" multiple="multiple" 
                                                    data-placeholder="Choose team members..." style="width: 100%;">
                                                <optgroup label="Team Members">
                                                    <?php foreach ($auditors as $auditor): ?>
                                                        <?php if ($auditor["isclient"] == 0): ?>
                                                            <option value="<?php echo $auditor["id"]; ?>">
                                                                <?php echo htmlspecialchars($auditor["name"]); ?> <?php if($auditor["id"] == $myuser->userdata['id']) echo "(You)"; ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                                <optgroup label="Auditors">
                                                    <?php foreach ($auditors as $auditor): ?>
                                                        <?php if ($auditor["isclient"] == 2): ?>
                                                            <option value="<?php echo $auditor["id"]; ?>">
                                                                <?php echo htmlspecialchars($auditor["name"]); ?> <?php if($auditor["id"] == $myuser->userdata['id']) echo "(You)"; ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            </select>
                                            <small class="help-block">Search and select specific team members</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Composition Section -->
                            <div class="form-section">
                                <h4><i class="fa fa-pencil"></i> Email Composition</h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="subject">Subject Line: <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="subject" name="subject" 
                                                   placeholder="Enter email subject" required maxlength="200">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="priority">Priority:</label>
                                            <select class="form-control" id="priority" name="priority">
                                                <option value="normal">Normal</option>
                                                <option value="high">High Priority</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Variables -->
                                <!--
                                <div class="variable-inserter">
                                    <h5><i class="fa fa-magic"></i> Insert Dynamic Variables</h5>
                                    <p class="help-block">Click to insert variables that will be replaced with actual data when sent:</p>
                                    <a href="#" class="variable-tag" data-variable="{{CLIENT_NAME}}">Client Name</a>
                                    <a href="#" class="variable-tag" data-variable="{{COMPANY_NAME}}">Company Name</a>
                                    <a href="#" class="variable-tag" data-variable="{{CLIENT_ID}}">Client ID</a>
                                    <a href="#" class="variable-tag" data-variable="{{DATE}}">Current Date</a>
                                    <a href="#" class="variable-tag" data-variable="{{SENDER_NAME}}">Your Name</a>
                                    <a href="#" class="variable-tag" data-variable="{{UNSUBSCRIBE_LINK}}">Unsubscribe Link</a>
                                </div>
                                -->

                                <!-- Editor Mode Selection -->
                                <div class="editor-mode-buttons">
                                    <label><strong>Editor Mode:</strong></label>
                                    <div class="btn-group" data-toggle="buttons">
                                        <label class="btn btn-default active" id="visualModeBtn">
                                            <input type="radio" name="editor_mode" value="visual" checked>
                                            <i class="fa fa-eye"></i> Visual Editor
                                        </label>
                                        <label class="btn btn-default" id="codeModeBtn">
                                            <input type="radio" name="editor_mode" value="code">
                                            <i class="fa fa-code"></i> HTML Code
                                        </label>
                                        <label class="btn btn-default" id="splitModeBtn">
                                            <input type="radio" name="editor_mode" value="split">
                                            <i class="fa fa-columns"></i> Split View
                                        </label>
                                    </div>
                                </div>

                                <!-- Visual Editor -->
                                <div class="form-group" id="visualEditorDiv">
                                    <label for="messageVisual">Email Content: <span class="text-danger">*</span></label>
                                    <div class="summernote-wrapper">
                                        <div id="messageVisual"></div>
                                    </div>
                                </div>

                                <!-- Code Editor -->
                                <div class="form-group" id="codeEditorDiv" style="display: none;">
                                    <label for="messageCode">HTML Code: <span class="text-danger">*</span></label>
                                    <textarea class="form-control code-editor" id="messageCode" name="message" rows="15" 
                                              placeholder="Enter your HTML email code"></textarea>
                                </div>

                                <!-- Split View -->
                                <div class="row" id="splitEditorDiv" style="display: none;">
                                    <div class="col-md-6">
                                        <label>HTML Code:</label>
                                        <textarea class="form-control code-editor" id="messageCodeSplit" rows="15" 
                                                  placeholder="Enter your HTML email code"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Live Preview:</label>
                                        <div id="livePreview" style="border: 1px solid #ddd; padding: 15px; height: 350px; overflow-y: auto; background-color: white;">
                                            <!-- Live preview will appear here -->
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" id="sendCopy" name="send_copy">
                                                Send copy to my email
                                            </label>
                                        </div>
                                    </div>
                                    <!--
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" id="trackOpens" name="track_opens">
                                                Track email opens
                                            </label>
                                        </div>
                                    </div>
                                                        -->
                                </div>
                            </div>

                            <!-- Attachments Section -->
                            <div class="form-section">
                                <h4><i class="fa fa-paperclip"></i> Attachments</h4>
                                
                                <div class="form-group">
                                    <label for="attachment">Attachment (Screenshots, Excel, PDF files, etc.)</label>
                                    <span class="fileinput-button" id="dropzone999">
                                        Drop files here or click to upload
                                        <input class="fileupload" id="fileupload999" type="file" 
                                               foldertype="addoc999" subfolder="notifications" infotype="notifications" 
                                               name="files[]" multiple="">
                                    </span>
                                    <span class="loader"><i class="fa fa-spinner fa-spin"></i> Uploading...</span>
                                    <ul id="uladdoc999" class="list-unstyled"></ul>
                                    <div class="alert-string"></div>
                                    <small class="help-block">
                                        Supported formats: PDF, PPT, PPTX, DOC, DOCX, XLS, XLSX, Images (JPG, PNG, GIF)<br>
                                        Maximum file size: 10MB per file
                                    </small>
                                </div>
                            </div>

                            <!-- Preview Section -->
                            <div class="form-section">
                                <h4><i class="fa fa-eye"></i> Email Preview</h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-info btn-block" id="previewDesktopBtn">
                                            <i class="fa fa-desktop"></i> Desktop Preview
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" class="btn btn-info btn-block" id="previewMobileBtn">
                                            <i class="fa fa-mobile"></i> Mobile Preview
                                        </button>
                                    </div>
                                </div>

                                <div class="responsive-preview" id="responsivePreview" style="display: none;">
                                    <div class="device-preview desktop-preview" id="desktopPreview" style="display: none;">
                                        <div class="preview-header">
                                            <i class="fa fa-desktop"></i> Desktop View
                                        </div>
                                        <iframe id="desktopPreviewFrame"></iframe>
                                    </div>
                                    
                                    <div class="device-preview mobile-preview" id="mobilePreview" style="display: none;">
                                        <div class="preview-header">
                                            <i class="fa fa-mobile"></i> Mobile View
                                        </div>
                                        <iframe id="mobilePreviewFrame"></iframe>
                                    </div>
                                </div>
                            </div>

                            <!-- Send Options -->
                            <div class="form-section">
                                <h4><i class="fa fa-send"></i> Send Options</h4>
                                <!--
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sendTime">Send Time:</label>
                                            <select class="form-control" id="sendTime" name="send_time">
                                                <option value="now">Send Now</option>
                                                <option value="scheduled">Schedule for Later</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group" id="scheduledTimeDiv" style="display: none;">
                                            <label for="scheduledDateTime">Scheduled Date & Time:</label>
                                            <input type="datetime-local" class="form-control" id="scheduledDateTime" name="scheduled_datetime">
                                        </div>
                                    </div>
                                </div>
                                                        -->

                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" id="confirmSend" name="confirm_send" required>
                                        I confirm that I want to send this email to the selected recipients
                                    </label>
                                </div>
                            </div>

                            <!-- Navigation and Action Buttons -->
                            <div class="form-section">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="btn-group" role="group">
                                            <a href="/notificationsHistory" class="btn btn-info btn-lg">
                                                <i class="fa fa-history"></i> View History
                                            </a>
                                            <!--
                                            <button type="button" class="btn btn-warning btn-lg" id="saveDraftBtn">
                                                <i class="fa fa-save"></i> Save Draft
                                            </button>
                                            -->
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div class="btn-group" role="group">
                                            <!--
                                            <button type="button" class="btn btn-info btn-lg" id="testEmailBtn">
                                                <i class="fa fa-flask"></i> Send Test Email
                                            </button>
                                            -->
                                            <button type="submit" class="btn btn-success btn-lg" id="sendBtn">
                                                <i class="fa fa-send"></i> Send Email
                                            </button>
                                            <button type="reset" class="btn btn-secondary btn-lg">
                                                <i class="fa fa-refresh"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Preview Modal -->
    <div class="modal fade" id="enhancedPreviewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-eye"></i> Enhanced Email Preview</h4>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#previewDesktop" aria-controls="previewDesktop" role="tab" data-toggle="tab">
                                <i class="fa fa-desktop"></i> Desktop
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#previewMobile" aria-controls="previewMobile" role="tab" data-toggle="tab">
                                <i class="fa fa-mobile"></i> Mobile
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#previewSource" aria-controls="previewSource" role="tab" data-toggle="tab">
                                <i class="fa fa-code"></i> HTML Source
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content" style="margin-top: 15px;">
                        <div role="tabpanel" class="tab-pane active" id="previewDesktop">
                            <iframe id="desktopFrame" style="width: 100%; height: 500px; border: 1px solid #ddd;"></iframe>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="previewMobile">
                            <div style="text-align: center;">
                                <iframe id="mobileFrame" style="width: 375px; height: 600px; border: 1px solid #ddd;"></iframe>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="previewSource">
                            <pre id="sourceCode" style="background-color: #f8f9fa; padding: 15px; border: 1px solid #ddd; max-height: 500px; overflow-y: auto;"></pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="$('#enhancedPreviewModal').modal('hide'); $('#emailComposerForm').submit();">
                        <i class="fa fa-send"></i> Looks Good, Send Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Template Modal -->
    <div class="modal fade" id="saveTemplateModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-save"></i> Save as Template</h4>
                </div>
                <div class="modal-body">
                    <form id="saveTemplateForm">
                        <div class="form-group">
                            <label for="templateName">Template Name:</label>
                            <input type="text" class="form-control" id="templateName" name="template_name" required>
                        </div>
                        <div class="form-group">
                            <label for="templateDescription">Description:</label>
                            <textarea class="form-control" id="templateDescription" name="template_description" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="EmailComposer.saveTemplate()">
                        <i class="fa fa-save"></i> Save Template
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Email Modal -->
    <div class="modal fade" id="testEmailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-flask"></i> Send Test Email</h4>
                </div>
                <div class="modal-body">
                    <form id="testEmailForm">
                        <div class="form-group">
                            <label for="testEmailAddress">Test Email Address:</label>
                            <input type="email" class="form-control" id="testEmailAddress" name="test_email" 
                                   placeholder="Enter email address for test" required>
                        </div>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> This will send a test version of your email to the specified address.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="EmailComposer.sendTestEmail()">
                        <i class="fa fa-flask"></i> Send Test
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Modal -->
    <div class="modal fade" id="progressModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <h4><i class="fa fa-spinner fa-spin"></i> <span id="progressAction">Sending Emails...</span></h4>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" 
                             style="width: 0%" id="progressBar">
                            <span class="sr-only">0% Complete</span>
                        </div>
                    </div>
                    <p id="progressText">Preparing to send...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><i class="fa fa-check-circle"></i> Email Sent Successfully</h4>
                </div>
                <div class="modal-body" id="successMessage">
                    <!-- Success details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.location.reload()">Compose Another</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.js"></script>
    <script src="js/jquery.fileupload.js"></script>
    <script src="js/enhanced_email_composer.js"></script>
</body>
</html>