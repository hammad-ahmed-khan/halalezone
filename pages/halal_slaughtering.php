<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <title>Halal Slaughtering - Halal Digital</title>
    <style>
        .blockUI h1 {
            font-size: 18px;
            margin: 10px auto;
        }
        td.changed {
            background:greenyellow;
        }
        tr.highlighted-conformed .fa-flag {
            display: none !important;
        }
        .mandatory-field::after {
            content: " *";
            color: red;
            font-weight: bold;
        }
        .mandatory-field {
            font-weight: bold;
        }
        .form-note {
            font-size: 11px;
            color: #666;
            font-style: italic;
            margin-top: 15px;
        }
        .checkbox-group {
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }
        .checkbox-group .checkbox {
            margin-bottom: 8px;
            margin-top: 0;
        }
        .checkbox-group .checkbox label {
            font-weight: normal;
            color: #333;
            padding-left: 20px;
        }
        .checkbox-group .checkbox input[type="checkbox"] {
            margin-left: -20px;
        }
        .modal-lg {
          width:100%;
          max-width: 75%;
        }
        .app-sidebar {
            width: 100%;
            background-color: #ffffff;
            color: #333333;            
            padding: 0px 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }
        .sidebar-header {
            padding: 0 20px 15px;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 10px;
        }
        .sidebar-header h2 {
            margin: 0;
            font-size: 1.3rem;
            color: #333;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar-menu li {
            padding: 0;
        }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: inherit;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        .sidebar-menu li a {
            color: #666;
        }
        .sidebar-menu li.completed a {
            color: #333;
            background-color: rgba(76, 175, 80, 0.1);
            position: relative;
        }
        .sidebar-menu li.completed a::before {
            content: "";
            display: inline-block;
            width: 18px;
            height: 18px;
            margin-right: 10px;
            background-color: #4CAF50;
            border-radius: 50%;
            position: relative;
        }
        .sidebar-menu li.completed a::after {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 10px;
            color: white;
            position: absolute;
            left: 24px;
            top: 51%;
            transform: translateY(-50%);
        }
        .sidebar-menu li.active a {
            background-color: rgba(76, 201, 240, 0.1);
            border-left: 4px solid #4cc9f0;
            color: #333;
            font-weight: bold;
        }
        .sidebar-menu li.active a::before {
            content: "\f061";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 10px;
            color: #4cc9f0;
        }
        .sidebar-menu li.locked a {
            color: #999;
            cursor: not-allowed;
        }
        .sidebar-menu li.locked a::before {
            content: "\f023";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 10px;
            color: #999;
        }
        .sidebar-menu li:not(.locked) a:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
        .sidebar-menu li i {
          display: none !important;
        }
        .section-divider {
            display: none !important;
            border-top: 2px solid #e0e0e0;
            margin: 20px 0;
            padding-top: 15px;
        }
        .section-title {
            display: none !important;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<?php include_once('pages/navigation.php');
try {
    $db = acsessDb :: singleton();
    $dbo =  $db->connect();
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}

$myuser = cuser::singleton();
$myuser->getUserData();
$isAdmin = $myuser->userdata['isclient'] != "1" && $myuser->userdata['isclient'] != "2" ?  true : false;
$isAuditor = $myuser->userdata['isclient'] == "2"  ?  true : false;
$isClient = $myuser->userdata['isclient'] == "1" ?  true : false;
?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12"> 
                        
                        <h2 id="pageTitle" style="margin-top: 15px;">
                                <i class="fa fa-sticky-note text-info"></i>                            
                                Halal Batch Certificates
                        </h2>
                    <div class="widget-box widget-border" style="margin: 15px 0;">
                        <div class="widget-body">
                            <div class="widget-main">
                                  <?php
                                    $parent_id = $myuser->userdata['id'];
                                    $hasFacilities = false;

                                    if ($isAuditor) {
                                        $ids = [-1];
                                        $clients_audit = $myuser->userdata['clients_audit'];
                                        if ($clients_audit != "") {
                                        $ids = json_decode($clients_audit);
                                        }
                                        $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND deleted = 0 AND id IN (".implode(",", $ids).") ORDER BY name";
                                    }
                                    else if ($isClient) {
                                        $sql = "SELECT id, name, prefix FROM tusers WHERE (id = '".$parent_id."' OR parent_id = '".$parent_id."') AND isclient = 1 AND deleted = 0 ORDER BY parent_id ASC, name";
                                    
                                    }
                                    else {
                                        $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND IFNULL(parent_id,'0') = '0' AND deleted = 0  ORDER BY name";
                                    }  
                                    
                                    $clients = [];
                                    $stmt = $dbo->prepare($sql);
                                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                    if ($stmt->execute()) { 
                                        $clients = $stmt->fetchAll();
                                    }
                                    
                                    $sql = "SELECT id, name, prefix, parent_id FROM tusers WHERE isclient=1 AND IFNULL(parent_id,'0') <> '0' AND deleted = 0 ORDER BY name";

                                    $childClients = [];
                                    $stmt = $dbo->prepare($sql);
                                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                    if ($stmt->execute()) { 
                                        $allChildren = $stmt->fetchAll();
                                        foreach ($allChildren as $child) {
                                            $childClients[$child['parent_id']][] = $child;
                                        }
                                    }

                                    if ($isClient && count($clients) > 1) {
                                        $hasFacilities = true;
                                    }

                                    if ( $myuser->userdata['products_preference'] == '1') {
                                        $hasFacilities = false;
                                    }
                                    ?>
                                    <input type="hidden" id="filter-hcpid" <?php echo 'value="'.(isset($_GET['id']) ? $_GET['id'] : '').'"'; ?> />
                                    <input type="hidden" id="filter-idclient" <?php echo 'value="'.(isset($_GET['idclient']) ? $_GET['idclient'] : '').'"'; ?> />
                                    <?php if ($isClient && !$hasFacilities): ?>              
                                        <input type="hidden" id="halal-clientid" data-email="<?php echo $myuser->userdata['email']; ?>" value=<?php echo $_SESSION['halal']['id']; ?> data-clientname="<?php echo $myuser->userdata['name']," (",$myuser->userdata['prefix'],$myuser->userdata['id'],")"; ?>"/>
                                    <?php endif;?>

                                    <?php if (!$isClient || $hasFacilities): ?>
                                        <div class="form-inline">
                                            <div class="form-group">
                                                <label><?php if ($isClient): ?> Facilities <?php else: ?> Clients <?php endif; ?> &nbsp;&nbsp;</label>
                                                <select class="form-control clientslist" id="halal-clientid">
                                                    <?php if (!$isClient): ?>
                                                        <option value="-1">Select <?php if ($isClient): ?> Facility <?php else: ?> Client <?php endif; ?></option>
                                                    <?php endif; ?>
                                                    <?php
                                                        foreach ($clients as $client) {
                                                            ?>
                                                            <option value="<?php echo $client["id"]; ?>" <?php if ($client["id"] == $_GET["idclient"] || $client["id"] == $myuser->userdata['id']):?>selected<?php endif; ?> data-clientname="<?php echo $client['name']," (",$client['prefix'],$client['id'],")"; ?>" ><?php echo $client["name"]; ?> - <?php echo $client["prefix"]; ?><?php echo $client["id"]; ?></option>
                                                            <?php
                                                            if (isset($childClients[$client['id']])) {
                                                                foreach ($childClients[$client['id']] as $child) {
                                                                    ?>
                                                                    <option value="<?php echo $child["id"]; ?>" <?php if ($child["id"] == $_GET["idclient"] || $child["id"] == $myuser->userdata['id']):?>selected<?php endif; ?> 
                                                                            data-clientname="<?php echo $child['name'], " (", $child['prefix'], $child['id'], ")"; ?>" style="padding-left: 40px;">
                                                                        <?php echo "&nbsp;&nbsp;└── "; ?><?php echo $child["name"]; ?> - <?php echo $child["prefix"]; ?><?php echo $child["id"]; ?>
                                                                    </option>
                                                                    <?php
                                                                }
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                          </div>
                                        <?php endif;?> 
                                </div>
                            </div>
                        </div>
                        <div class="clearfix">
                            <div class="pull-right tableTools-container"></div>
                        </div>
                    
                        <div>
                            <table id="halalGrid"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Halal Slaughtering Modal -->
<div class="modal fade" id="halalModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="halalModal-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="halalModal-label">Add Halal Slaughtering Record</h4>
            </div>
            <div class="modal-body">
           <div class="row" >
              <div class="col-md-3">
                <div class="app-sidebar">
                  <ul class="sidebar-menu">
                    <li class="tab_step1 active"><a data-toggle="tab" href="#">Halal Slaughtering <i class="fa"></i></a></li>
                    <li class="tab_step2 locked"><a data-toggle="tab" href="#">Halal Batch Certificate <i class="fa"></i></a></li>
                  </ul>  
                </div>  
            </div>
            <div class="col-md-9">
              <!-- Tab 1: Halal Slaughtering Form -->
              <div id="halal-tab1">
                <form id="halal-form" class="form-horizontal">
                    <input type="hidden" id="halal-id" value="" />
                    
                    <!-- Client / Company Name -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Client / Company Name</label>
                        <div class='col-xs-12 col-md-8'>
                            <span id="company-name-display" class="form-control-static" style="font-weight: bold; color: #333;"></span>
                            <input type="hidden" id="company-name"/>
                        </div>
                    </div>
                    
                    <!-- Contact Person 1 -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Contact Person 1</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="contact-person-1"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Contact Person 2 -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Contact Person 2</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="contact-person-2"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Start Date & Time -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Start Date</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datetimepicker" id="slaughter-start-datetime"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- End Date & Time -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">End Date</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datetimepicker" id="slaughter-end-datetime"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Type of Animal -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Type of Animal</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="type-of-animal">
                                <option value="">-- Select Animal Type --</option>
                                <option value="chicken">Chicken</option>
                                <option value="duck">Duck</option>
                                <option value="turkey">Turkey</option>
                                <option value="cattle">Cattle</option>
                                <option value="sheep">Sheep</option>
                                <option value="goat">Goat</option>
                                <option value="lamb">Lamb</option>
                                <option value="other">Other</option>
                            </select>
                            <span class="alert-string"></span>
                        </div>
                    </div>

                    <div class="section-divider">
                        <div class="section-title">Butchers</div>
                    </div>
                    
                    <!-- Butcher 1 -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Butcher 1</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="butcher-1"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Butcher 2 -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Butcher 2</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="butcher-2"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Butcher 3 -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Butcher 3</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="butcher-3"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>

                    <div class="section-divider">
                        <div class="section-title">Supervisors</div>
                    </div>
                    
                    <!-- Supervisor 1 -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Supervisor 1</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="supervisor-1"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Supervisor 2 -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Supervisor 2</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="supervisor-2"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Supervisor 3 -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Supervisor 3</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="supervisor-3"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>

                    <div class="section-divider">
                        <div class="section-title">Slaughtering Details</div>
                    </div>
                    
                    <!-- Halal Slaughtering Documents -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Halal Slaughtering Documents
                            <div class="form-help">
                                <small class="text-muted">Halal Slaughtering Plan, Participant list, Slaughtering Confirmation, Halal Slaughtering Checklist</small>
                            </div>
                        </label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-halal-docs">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-halal-docs" type="file" name="files[]" foldertype="halal_slaughtering_documents">
                            </span>
                            <ul id="ulhalal_slaughtering_documents"></ul>
                        </div>
                    </div>
                    
                    <!-- Method of Stunning -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Method of Stunning</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="method-of-stunning">
                                <option value="">-- Select Method --</option>
                                <option value="Electrical">Electrical</option>
                                <option value="Pneumatic Percussive">Pneumatic Percussive</option>
                                <option value="None">None (No Stunning)</option>
                            </select>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Upload Halal Slaughtering Data (XLS) -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Halal Slaughtering Data
                            <div class="form-help" style="margin-bottom: 10px;">
                                <i class="ace-icon fa fa-download text-primary"></i>
                                <a href="/files/Halal_slaughtering_Template_xls.xlsx" target="_blank" download="Halal_slaughtering_Template_xls.xlsx" class="text-primary">
                                    <strong>Download Template</strong>
                                </a>
                                <br>
                                <small class="text-muted">Farmer Name/ID, Result, Reason for Non-Halal, Follow-up Action, Remarks</small>
                            </div>
                        </label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-halal-slaughtering-data">Drop XLS/XLSX file here or click to upload
                                <input class="fileupload" id="fileupload-halal-slaughtering-data" type="file" name="files[]" foldertype="halal_slaughtering_data" accept=".xls,.xlsx">
                            </span>
                            <ul id="ulhalal_slaughtering_data"></ul>
                        </div>
                    </div>

                    <div class="section-divider">
                        <div class="section-title">Document Uploads</div>
                    </div>
                    
                    <!-- Upload Live Animals Documents -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Upload Live Animals Documents</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-live-animals">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-live-animals" type="file" name="files[]" foldertype="upload_live_animals_documents">
                            </span>
                            <ul id="ulupload_live_animals_documents"></ul>
                        </div>
                    </div>
                    
                    <!-- Upload Pictures After Cleaning -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Upload Pictures After Cleaning</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-pictures-cleaning">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-pictures-cleaning" type="file" name="files[]" foldertype="upload_pictures_after_cleaning">
                            </span>
                            <ul id="ulupload_pictures_after_cleaning"></ul>
                        </div>
                    </div>
                    
                    <!-- Upload Halal Slaughtering Video -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Upload Halal Slaughtering Video</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-video">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-video" type="file" name="files[]" foldertype="upload_halal_slaughtering_video">
                            </span>
                            <ul id="ulupload_halal_slaughtering_video"></ul>
                        </div>
                    </div>
                    
                    <!-- Upload Additional Pictures -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Upload Additional Pictures
                            <div class="form-help">
                                <small class="text-muted">Stunning, producing, finished products, etc.</small>
                            </div>
                        </label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-additional-pictures">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-additional-pictures" type="file" name="files[]" foldertype="upload_additional_pictures">
                            </span>
                            <ul id="ulupload_additional_pictures"></ul>
                        </div>
                    </div>
                    
                    <!-- Upload Halal Stock -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Upload Halal Stock
                            <div class="form-help" style="margin-bottom: 10px;">
                                <i class="ace-icon fa fa-download text-primary"></i>
                                <a href="/files/template-halal-stock.xlsx" target="_blank" download="template-halal-stock.xlsx" class="text-primary">
                                    <strong>Download Halal Stock Template</strong>
                                </a>
                            </div>
                        </label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-halal-stock">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-halal-stock" type="file" name="files[]" foldertype="upload_halal_stock">
                            </span>
                            <ul id="ulupload_halal_stock"></ul>
                        </div>
                    </div>

                    <div class="section-divider">
                        <div class="section-title">Payment Information</div>
                    </div>
                    
                    <!-- Travel Expenses Invoice (Admin only) -->
                    <?php if ($isAdmin): ?>
                    <div class="row form-group admin-fields">
                        <label class="col-xs-12 col-md-4">Travel expenses invoice</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-invoice">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-invoice" type="file" name="files[]" foldertype="invoice_travel_expenses">
                            </span>
                            <ul id="ulinvoice_travel_expenses"></ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Proof of Payment -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Proof of Payment</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-proof-payment">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-proof-payment" type="file" name="files[]" foldertype="proof_of_payment">
                            </span>
                            <ul id="ulproof_of_payment"></ul>
                        </div>
                    </div>

                    <div class="form-note">All fields marked with * are required</div>

                   <div class="text-right">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                     <button type="button" class="btn btn-primary" id="btnSaveHalalSlaughtering" onclick="TP1.onSave();">Save Record</button>
                     <?php if ($isAdmin): ?>
                     <button type="button" class="btn btn-success" id="btnCompleteHalalSlaughtering" onclick="markHalalComplete();">Mark as Complete</button>
                     <?php endif; ?>                
                   </div>

                </form>
                
              </div>

              <!-- Tab 2: Halal Batch Certificate Form -->
              <div id="halal-tab2" style="display:none;">
                <form id="batch-form" class="form-horizontal">
                    <input type="hidden" id="batch-id" value="" />
                    
                    <!-- Client / Company Name -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Client / Company Name</label>
                        <div class='col-xs-12 col-md-8'>
                            <span id="batch-company-name-display" class="form-control-static" style="font-weight: bold; color: #333;"></span>
                            <input type="hidden" id="batch-company-name"/>
                        </div>
                    </div>
                    
                    <!-- Date -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Date</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datepicker" id="batch-date"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Country of Origin -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Country of Origin</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="country-of-origin"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Quality -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Quality</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="quality"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Net Weight (kg) -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Net Weight (kg)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="number" step="0.001" class="form-control" id="net-weight-kg"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Gross Weight (kg) -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Gross Weight (kg)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="number" step="0.001" class="form-control" id="gross-weight-kg"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Transport By -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Transport By</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="transport-by">
                                <option value="">-- Select Transport --</option>
                                <option value="Truck">Truck</option>
                                <option value="Shipping">Shipping</option>
                                <option value="Air">Air</option>
                            </select>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- AWB/Voyage/Flight No -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">AWB / Voyage / Flight No.</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="awb-voyage-flight-no"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Loading Port -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Loading Port</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="loading-port"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Destination -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Destination</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="destination"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>

                    <div class="section-divider">
                        <div class="section-title">Exporter Information</div>
                    </div>
                    
                    <!-- Exporter Name -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Exporter Name</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="exporter-name"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Exporter Address -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Exporter Address</label>
                        <div class='col-xs-12 col-md-8'>
                            <textarea class="form-control" id="exporter-address" rows="2"></textarea>
                            <span class="alert-string"></span>
                        </div>
                    </div>

                    <div class="section-divider">
                        <div class="section-title">Importer Information</div>
                    </div>
                    
                    <!-- Importer Name -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Importer Name</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="importer-name"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Importer Address -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Importer Address</label>
                        <div class='col-xs-12 col-md-8'>
                            <textarea class="form-control" id="importer-address" rows="2"></textarea>
                            <span class="alert-string"></span>
                        </div>
                    </div>

                    <div class="section-divider">
                        <div class="section-title">Product Information</div>
                    </div>
                    
                    <!-- Upload Product Information -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Upload Product Information
                            <div class="form-help" style="margin-bottom: 10px;">
                                <i class="ace-icon fa fa-download text-primary"></i>
                                <a href="/files/template-product-information.xlsx" target="_blank" download="template-product-information.xlsx" class="text-primary">
                                    <strong>Download Product Info Template</strong>
                                </a>
                                <br>
                                <small class="text-muted">Slaughter Date, Production Date, Expiry Date, Health Cert No., Slaughter House, Producing Company</small>
                            </div>
                        </label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-product-info">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-product-info" type="file" name="files[]" foldertype="upload_product_information">
                            </span>
                            <ul id="ulupload_product_information"></ul>
                        </div>
                    </div>

                    <div class="section-divider">
                        <div class="section-title">Document Uploads</div>
                    </div>
                    
                    <!-- Upload Consignment Details -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Upload Consignment Details
                            <div class="form-help" style="margin-bottom: 10px;">
                                <i class="ace-icon fa fa-download text-primary"></i>
                                <a href="/files/template-consignment-details.xlsx" target="_blank" download="template-consignment-details.xlsx" class="text-primary">
                                    <strong>Download Consignment Template</strong>
                                </a>
                                <br>
                                <small class="text-muted">Name, Br weight, Ne weight, additional info</small>
                            </div>
                        </label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-consignment">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-consignment" type="file" name="files[]" foldertype="upload_consignment_details">
                            </span>
                            <ul id="ulupload_consignment_details"></ul>
                        </div>
                    </div>
                    
                    <!-- Invoice -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Invoice</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-batch-invoice">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-batch-invoice" type="file" name="files[]" foldertype="batch_invoice">
                            </span>
                            <ul id="ulbatch_invoice"></ul>
                        </div>
                    </div>
                    
                    <!-- Proof of Payment -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Proof of Payment</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-batch-proof-payment">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-batch-proof-payment" type="file" name="files[]" foldertype="batch_proof_of_payment">
                            </span>
                            <ul id="ulbatch_proof_of_payment"></ul>
                        </div>
                    </div>
                    
                    <!-- Halal Batch Certificate -->
                    <?php if ($isAuditor || $isAdmin): ?>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Halal Batch Certificate</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-halal-batch-cert">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-halal-batch-cert" type="file" name="files[]" foldertype="halal_batch_certificate">
                            </span>
                            <ul id="ulhalal_batch_certificate"></ul>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-note">All fields marked with * are required</div>

                    <div class="text-right">
                     <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                     <button type="button" class="btn btn-primary" id="btnSaveBatchCertificate" onclick="TP2.onSave();">Save Record</button>
                     <?php if ($isAdmin): ?>
                     <button type="button" class="btn btn-success" id="btnCompleteBatchCertificate" onclick="markBatchComplete();">Mark as Complete</button>
                     <?php endif; ?>                
                    </div>

                </form>
              </div>

            </div>
            </div>
                                   

            </div>
            <div class="modal-footer">
               
            </div>
        </div>
    </div>
</div>

<?php include_once('pages/footer.php');?>
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="js/jquery.jqGrid.min.js"></script>
<script src="js/grid.locale-en.js"></script>
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>
<script src="js/select2.full.min.js"></script>
<script src="js/vendor/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js?ver=1285677791' id='blockui-js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script>

<script type="text/javascript">

var TP1 = {

  isAdmin:<?php echo $isAdmin ? 'true' : 'false'; ?>,
  isAuditor:<?php echo $isAuditor ? 'true' : 'false'; ?>,
  filesUploaded: [],

  onDocumentReady: function() {

    $("#logModal").on("shown.bs.modal", function() {
      var table = $("#table_log").DataTable();
      table.ajax.reload(null, false);
    });

    Common.setMainMenuItem("halal_slaughtering");

    TP1.gridMode = 0;

    $('[data-toggle="tooltip"]').tooltip();

    $("input").focus(function() {
      TP1.clearAlerts();
    });

    $("select").change(function() {
      TP1.clearAlerts();
    });
 
     $(".datepicker").datepicker({
      autoUpdateInput: false,
      autoclose: true,
      format: "dd M yyyy",
      orientation: "bottom",
    });

    $(".datepicker").on("changeDate", function(e) {
      TP1.clearAlerts();
    });

    // Initialize datetime pickers for start and end dates
    $(".datetimepicker").datetimepicker({
      format: "DD MMM YYYY HH:mm",
      sideBySide: true,
      showTodayButton: true,
      showClear: true,
      icons: {
        time: "fa fa-clock-o",
        date: "fa fa-calendar",
        up: "fa fa-chevron-up",
        down: "fa fa-chevron-down",
        previous: "fa fa-chevron-left",
        next: "fa fa-chevron-right",
        today: "fa fa-crosshairs",
        clear: "fa fa-trash"
      }
    });

    $(".datetimepicker").on("dp.change", function(e) {
      TP1.clearAlerts();
    });

    $("#halal-clientid").on("change", function() {
      if (jqGridRequest) {
        jqGridRequest.abort();
      }
      const gridParams = {
        url: "ajax/getHalalSlaughtering.php?displaymode=" + TP1.gridMode + "&idclient=" + this.value,
        rowNum: isNaN(parseInt(this.value)) ? 20 : 1000000,
      };

      $(".ui-paging-pager").toggle(isNaN(parseInt(this.value)));

      $("#halal-clientid").data(
        "clientname",
        $("#halal-clientid option:selected").text()
      );

      jQuery("#halalGrid").jqGrid("setGridParam", gridParams);
      jQuery("#halalGrid").jqGrid().trigger("reloadGrid");
    });

initFileUploader({
  fileUploadSelector: "#halal-form .fileupload",
  dropzoneSelector: "#halal-form .dropzone",
  progressSelector: "#halal-form .progress",

  dataModifier: function(e, data) {
    
    data.formData = {
      folderType: $(e.target).attr("foldertype"),
      infoType: "halal_slaughtering",
      client: $("#halal-clientid option:selected").text(),
      idapplication: $("#halal-form #halal-id").val(),
    };
  },

    fileValidator: function (e, data) {
    const uploadFile = data.files[0];

    if (!/\.(pdf|doc|docx|xls|xlsx|png|jpe?g|mp4|mov|avi|wmv)$/i.test(uploadFile.name)) {
        return "You can upload files in PDF, Word, Excel, image formats (PNG, JPG), or video formats (MP4, MOV, AVI)";
    }

    return true;
    },

  afterSuccess: function(e, file) {
    TP1.filesUploaded.push({ file: file.name });
  }
});


initFileUploader({
  fileUploadSelector: "#batch-form .fileupload",
  dropzoneSelector: "#batch-form .dropzone",
  progressSelector: "#batch-form .progress",

  dataModifier: function(e, data) {
    
    data.formData = {
      folderType: $(e.target).attr("foldertype"),
      infoType: "halal_batch_certificate",
      client: $("#halal-clientid option:selected").text(),
      idapplication: $("#batch-form #batch-id").val(),
    };
  },

    fileValidator: function (e, data) {
    const uploadFile = data.files[0];

    if (!/\.(pdf|doc|docx|xls|xlsx|png|jpe?g)$/i.test(uploadFile.name)) {
        return "You can upload files in PDF, Word, Excel, or image formats (PNG, JPG)";
    }

    return true;
    },

  afterSuccess: function(e, file) {
    TP1.filesUploaded.push({ file: file.name });
  }
});

    TP1.initGrid();
  },

   initGrid: function() {
    var h = (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight) - 350;

    new Promise(function(resolve) {
      $("#halalGrid").jqGrid({
        url: "ajax/getHalalSlaughtering.php?displaymode=" + TP1.gridMode + "&idclient=" + $("#halal-clientid").val(),
        datatype: "json",
        mtype: "POST",
        width: $("#halalGrid").parent().width(),
        height: h,
        colModel: [
          { name: "id", label: "ID", width: 50, key: true, hidden: true },
          { name: "idclient", label: "Client ID", width: 50, hidden: true },
          { name: "client_name", label: "Client", width: 125, frozen: true <?php if ($isClient): ?>, hidden: true<?php endif; ?> },
          { name: "contact_person_1", label: "Contact Person 1", width: 130 },
          { name: "contact_person_2", label: "Contact Person 2", width: 130, hidden: true },
          { name: "start_datetime", label: "Start Date", width: 130, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y H:i" } },
          { name: "end_datetime", label: "End Date", width: 130, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y H:i" } },
          { name: "type_of_animal", label: "Animal Type", width: 100 },
          { name: "butcher_1", label: "Butcher 1", width: 120 },
          { name: "butcher_2", label: "Butcher 2", width: 120, hidden: true },
          { name: "butcher_3", label: "Butcher 3", width: 120, hidden: true },
          { name: "supervisor_1", label: "Supervisor 1", width: 120 },
          { name: "supervisor_2", label: "Supervisor 2", width: 120, hidden: true },
          { name: "supervisor_3", label: "Supervisor 3", width: 120, hidden: true },
          { 
              name: "halal_slaughtering_documents", 
              index: "halal_slaughtering_documents", 
              label: "Slaughtering Docs", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { name: "method_of_stunning", label: "Stunning Method", width: 130 },
          { 
              name: "halal_slaughtering_data",
              index: "halal_slaughtering_data", 
              label: "Halal Slaughtering Data", 
              width: 180,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "upload_live_animals_documents",
              index: "upload_live_animals_documents", 
              label: "Live Animals Docs", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "upload_pictures_after_cleaning",
              index: "upload_pictures_after_cleaning", 
              label: "Cleaning Pictures", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "upload_halal_slaughtering_video",
              index: "upload_halal_slaughtering_video", 
              label: "Video", 
              width: 100,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "upload_additional_pictures",
              index: "upload_additional_pictures", 
              label: "Additional Pictures", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "upload_halal_stock",
              index: "upload_halal_stock", 
              label: "Halal Stock", 
              width: 100,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "invoice_travel_expenses", 
              index: "invoice_travel_expenses", 
              label: "Invoice", 
              width: 100,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false,
              hidden: !TP1.isAdmin
          },
          { 
              name: "proof_of_payment",
              index: "proof_of_payment", 
              label: "Proof of Payment", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
            name: "status", 
            label: "Status", 
            width: 100,
            align: "center",
            formatter: function(cellvalue) {
              const statusColors = {
                'draft': 'label-warning',
                'submitted': 'label-info', 
                'approved': 'label-success',
                'rejected': 'label-danger',
                'completed': 'label-primary'
              };
              const colorClass = statusColors[cellvalue] || 'label-default';
              return '<span class="label ' + colorClass + '">' + (cellvalue || 'Draft') + '</span>';
            }
          },
          { name: "deleted", index: "deleted", editable: false, hidden: true },
        ],
        rowNum: 20,
        rowList: [20, 50, 100],
        pager: "#halalPager",
        sortname: "created_at",
        viewrecords: true,
        sortorder: "desc",
        shrinkToFit: false,
        toppager: true,
        hoverrows: false,
        gridview: true,
        multiselect: true,
        subGrid: true,
        ondblClickRow: function(rowid) {
          TP1.editSlaughtering();
        },
        loadComplete: function() {
          Common.updatePagerIcons(this);
          document.querySelectorAll(".upload-area").forEach((area) => {
            area.addEventListener("dragover", handleDragOver);
            area.addEventListener("dragleave", handleDragLeave);
            area.addEventListener("drop", handleDrop);
          });
        },
        gridComplete: function() {
          initFileUploader({
            fileUploadSelector: "#gbox_halalGrid .fileupload",
            dropzoneSelector: "#gbox_halalGrid .dropzone",
            progressSelector: "#gbox_halalGrid .progress",
            dataModifier: function(e, data) {
              data.formData = {
                folderType: $(e.target).attr("foldertype"),
                infoType: "halal_slaughtering",
                client: $("#halal-clientid option:selected").text(),
                idapplication: $(e.target).closest("tr").attr("id"),
              };
            },

            fileValidator: function (e, data) {
                const uploadFile = data.files[0];

                if (!/\.(pdf|doc|docx|xls|xlsx|png|jpe?g|mp4|mov|avi)$/i.test(uploadFile.name)) {
                    return "You can upload files in PDF, Word, Excel, image formats (PNG, JPG), or video formats";
                }           

                return true;
            },

            onSuccess: function(e, data) {
              $(e.target).parent().siblings(".progress").hide();
              
              if (!data.result.files.length) return;
              
              const fileData = {
                name: data.result.files[0].name,
                glink: data.result.files[0].googleDriveUrl,
                hostpath: data.result.files[0].url,
                hostUrl: data.result.files[0].hostUrl,
              };

              const FD = new FormData();
              FD.append("id", $(e.target).closest("tr").attr("id"));
              FD.append("rtype", "addHalalSlaughteringFiles");
              
              const colName = {
                halal_slaughtering_data: "halal_slaughtering_data",
                halal_slaughtering_documents: "halal_slaughtering_documents",
                upload_live_animals_documents: "upload_live_animals_documents",
                upload_pictures_after_cleaning: "upload_pictures_after_cleaning",
                upload_halal_slaughtering_video: "upload_halal_slaughtering_video",
                upload_additional_pictures: "upload_additional_pictures",
                upload_halal_stock: "upload_halal_stock",
                invoice_travel_expenses: "invoice_travel_expenses",
                proof_of_payment: "proof_of_payment"
              }[data.result.files[0].folderType];

              FD.append(colName, JSON.stringify(fileData));

              fetch("/ajax/ajaxHandler.php", {
                method: "POST",
                credentials: "include",
                body: FD,
              }).then(r => r.json())
                .then(j => {
                  if (j.status != "1") {
                    alert("There was an error attaching the files.");
                    return;
                  }
                  $("#halalGrid").jqGrid().trigger("reloadGrid");
                });

              TP1.filesUploaded?.push({ file: data.result.files[0].name });
            }
          });
        },
        rowattr: function (rd) {
           var rowclass = "";
          if (rd.deleted === "1") rowclass += "deleted ";
         
          rowclass = { class: rowclass };
          return rowclass;
        },    
        
         subGridOptions: {
          plusicon: "ace-icon fa fa-plus center bigger-110 blue",
          minusicon: "ace-icon fa fa-minus center bigger-110 blue",
          openicon: "ace-icon fa fa-chevron-right center orange",
        },
        subGridRowExpanded: function (subgrid_id, row_id) {
           var subgridTableId = subgrid_id + "_t";
          $("#" + subgrid_id).html(
            "<table id='" + subgridTableId + "' class='scroll'></table>"
          );
          $("#" + subgridTableId).jqGrid({
   datatype: "json",
        mtype: "POST",
            url: "ajax/getHalalBatchCertificates.php?displaymode=" + TP2.gridMode + "&idhalal_slaughtering=" + row_id,
        colModel: [
          { name: "id", label: "ID", width: 50, key: true, hidden: true },
          { name: "idhalal_slaughtering", label: "Parent ID", width: 50, hidden: true },
          { name: "idclient", label: "Client ID", width: 50, hidden: true },
          { name: "client_name", label: "Client", width: 130, frozen: true <?php if ($isClient): ?>, hidden: true<?php endif; ?> },
          { name: "date", label: "Date", width: 100, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" } },
          { name: "country_of_origin", label: "Country of Origin", width: 120 },
          { name: "quality", label: "Quality", width: 100 },
          { name: "net_weight_kg", label: "Net Weight (kg)", width: 110 },
          { name: "gross_weight_kg", label: "Gross Weight (kg)", width: 110 },
          { name: "transport_by", label: "Transport", width: 90 },
          { name: "awb_voyage_flight_no", label: "AWB/Voyage/Flight", width: 130 },
          { name: "loading_port", label: "Loading Port", width: 120 },
          { name: "destination", label: "Destination", width: 120 },
          { name: "exporter_name", label: "Exporter", width: 130 },
          { name: "exporter_address", label: "Exporter Address", width: 150, hidden: true },
          { name: "importer_name", label: "Importer", width: 130 },
          { name: "importer_address", label: "Importer Address", width: 150, hidden: true },
          { 
              name: "upload_product_information",
              index: "upload_product_information", 
              label: "Product Info", 
              width: 100,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "upload_consignment_details",
              index: "upload_consignment_details", 
              label: "Consignment Details", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "invoice",
              index: "invoice", 
              label: "Invoice", 
              width: 100,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "proof_of_payment",
              index: "proof_of_payment", 
              label: "Proof of Payment", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "halal_batch_certificate",
              index: "halal_batch_certificate", 
              label: "Batch Certificate", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
            name: "status", 
            label: "Status", 
            width: 100,
            align: "center",
            formatter: function(cellvalue) {
              const statusColors = {
                'draft': 'label-warning',
                'submitted': 'label-info', 
                'approved': 'label-success',
                'rejected': 'label-danger'
              };
              const colorClass = statusColors[cellvalue] || 'label-default';
              return '<span class="label ' + colorClass + '">' + (cellvalue || 'Draft') + '</span>';
            }
          },
          { name: "deleted", index: "deleted", editable: false, hidden: true },
        ],
         rowNum: 20,
            altRows: true,
            shrinkToFit: true,
        ondblClickRow: function(rowid) {
          TP1.editBatchCertificate();
        },
        loadComplete: function() {
          Common.updatePagerIcons(this);
          document.querySelectorAll(".upload-area").forEach((area) => {
            area.addEventListener("dragover", handleDragOver);
            area.addEventListener("dragleave", handleDragLeave);
            area.addEventListener("drop", handleDrop);
          });
        },
        gridComplete: function() {
          initFileUploader({
            fileUploadSelector: "#gbox_halalGrid_"+row_id+"_t .fileupload",
            dropzoneSelector: "#gbox_halalGrid_"+row_id+"_t .dropzone",
            progressSelector: "#gbox_halalGrid_"+row_id+"_t .progress",
            dataModifier: function(e, data) {
              data.formData = {
                folderType: $(e.target).attr("foldertype"),
                infoType: "halal_batch_certificate",
                client: $("#halal-clientid option:selected").text(),
                idapplication: $(e.target).closest("tr").attr("id"),
              };
            },

            fileValidator: function (e, data) {
                const uploadFile = data.files[0];

                if (!/\.(pdf|doc|docx|xls|xlsx|png|jpe?g)$/i.test(uploadFile.name)) {
                    return "You can upload files in PDF, Word, Excel, or image formats (PNG, JPG)";
                }           

                return true;
            },

            onSuccess: function(e, data) {
              $(e.target).parent().siblings(".progress").hide();
              
              if (!data.result.files.length) return;
              
              const fileData = {
                name: data.result.files[0].name,
                glink: data.result.files[0].googleDriveUrl,
                hostpath: data.result.files[0].url,
                hostUrl: data.result.files[0].hostUrl,
              };

              const FD = new FormData();
              FD.append("id", $(e.target).closest("tr").attr("id"));
              FD.append("rtype", "addBatchCertificateFiles");
              
              const colName = {
                upload_product_information: "upload_product_information",
                upload_consignment_details: "upload_consignment_details",
                batch_invoice: "invoice",
                batch_proof_of_payment: "proof_of_payment",
                halal_batch_certificate: "halal_batch_certificate"
              }[data.result.files[0].folderType];

              FD.append(colName, JSON.stringify(fileData));

              fetch("/ajax/ajaxHandler.php", {
                method: "POST",
                credentials: "include",
                body: FD,
              }).then(r => r.json())
                .then(j => {
                  if (j.status != "1") {
                    alert("There was an error attaching the files.");
                    return;
                  }
                 $("#" + subgridTableId).jqGrid().trigger("reloadGrid");
                });
            }
          });
        },
        rowattr: function (rd) {
           var rowclass = "";
          if (rd.deleted === "1") rowclass += "deleted ";
         
          rowclass = { class: rowclass };
          return rowclass;
        },    
          });
          /////////////////////////////////////////////////
        },
        subGridRowColapsed: function (subgrid_id, row_id) {},
      });

      // Navigation buttons
      $("#halalGrid").jqGrid("navGrid", "#halalPager", {
        cloneToTop: true,
        edit: true,
        add: true,
        del: true,
        search: false,
        refresh: true,
        view: false,
        addfunc: function () {
          TP1.newSlaughtering();
        },
        editfunc: function () {
          TP1.editSlaughtering();
        },
        delfunc: function () {
          TP1.deleteSlaughtering();
        },
      });

      // Filter toolbar
      $("#halalGrid").jqGrid("filterToolbar", { 
        searchOperators: true,
        enableClear: false 
      });

      // Toggle deleted records button
      $("#halalGrid").navButtonAdd("#halalGrid_toppager", {
        caption: "",
        title: "Toggle displaying removed records mode",
        buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
        onClickButton: function () {
          TP1.onToggleRemovedRecordsMode(event);
        },
      });

      // Set frozen columns
      $("#halalGrid").jqGrid("setFrozenColumns");
      
      resolve("grid initialized");
    });
  },

clearForm: function() {
    TP1.clearAlerts();
    $(".datepicker").datepicker("update", "");
    $(".datetimepicker").val("");
    $("#ulhalal_slaughtering_documents").empty();
    $("#ulhalal_slaughtering_data").empty();
    $("#ulupload_live_animals_documents").empty();
    $("#ulupload_pictures_after_cleaning").empty();
    $("#ulupload_halal_slaughtering_video").empty();
    $("#ulupload_additional_pictures").empty();
    $("#ulupload_halal_stock").empty();
    $("#ulinvoice_travel_expenses").empty();
    $("#ulproof_of_payment").empty();
    $("#halal-form input").val("");
    $("#halal-form textarea").val("");
    $("#halal-form select").val("");
    $("#halal-form #company-name-display").text("");
    $("#halal-form .form-warning").hide();
},

clearAlerts: function() {
    $(".alert-string").text("");
},

newSlaughtering: function() {
    if ($("#halal-clientid").val() == "" || $("#halal-clientid").val() == "-1") {
        alert("Please select a client");
        return;
    }
    TP1.clearForm();
    toggleTabs(0);
    $("#halalModal-label").text("New Halal Slaughtering Record");
    TP1.getNextId(TP1.fillForm);
},

getNextId: function(callback) {
    var doc = {};
    $.get("ajax/ajaxHandler.php", {
        uid: 0,
        data: doc,
        rtype: "nextHalalSlaughteringId",
    }).done(callback);
},

fillForm: function(data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
        alert(response.statusDescription);
        return;
    }
    if (!response.data.slaughtering) {
        $("#halal-form #halal-id").val(response.data.id);
        $("#halal-form #halal-id").attr("data-id", response.data.id);
        $("#halal-form #halal-id").attr("data-new", 1);
    }
    
    // Set company name from selected client
    var clientName = $("#halal-clientid option:selected").data("clientname") || $("#halal-clientid option:selected").text();
    $("#halal-form #company-name").val(clientName);
    $("#halal-form #company-name-display").text(clientName);
    
    $("#halal-form").prop("submit", 0);
    TP1.filesUploaded = [];
    $("#halalModal").modal("show");
},

editSlaughtering: function() {
    if (jQuery("#halalGrid").jqGrid("getGridParam", "selrow") == null) {
        alert("Please select a record");
        return;
    }
    
    var selectedId = jQuery("#halalGrid").jqGrid("getGridParam", "selrow");
    
    $.blockUI({
        message: '<h4><i class="ace-icon fa fa-spinner fa-spin"></i> Loading halal slaughtering data...</h4>',
        css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            opacity: .5,
            color: '#fff'
        }
    });
    
    $.ajax({
        url: "ajax/getHalalSlaughteringById.php",
        type: "GET",
        data: { id: selectedId },
        dataType: "json",
        success: function(response) {
            if (response.status == 0) {
                alert(response.message || "Error loading data");
                return;
            }
            
            var slaughtering = response.data.slaughtering;
            
            TP1.clearForm();
            
            $("#halal-clientid").val(slaughtering.idclient);
            
            $("#halalModal-label").text("Edit Halal Slaughtering Record");
            $("#halal-form #halal-id").val(slaughtering.id);
            $("#halal-form #halal-id").attr("data-id", slaughtering.id);
            $("#halal-form #halal-id").attr("data-new", 0);
            
            // Populate form fields
            var clientName = $("#halal-clientid option:selected").data("clientname") || slaughtering.company_name || "";
            $("#halal-form #company-name").val(clientName);
            $("#halal-form #company-name-display").text(clientName);
            $("#halal-form #contact-person-1").val(slaughtering.contact_person_1 || "");
            $("#halal-form #contact-person-2").val(slaughtering.contact_person_2 || "");
            $("#halal-form #slaughter-start-datetime").val(slaughtering.start_datetime || "");
            $("#halal-form #slaughter-end-datetime").val(slaughtering.end_datetime || "");
            $("#halal-form #type-of-animal").val(slaughtering.type_of_animal || "");
            $("#halal-form #butcher-1").val(slaughtering.butcher_1 || "");
            $("#halal-form #butcher-2").val(slaughtering.butcher_2 || "");
            $("#halal-form #butcher-3").val(slaughtering.butcher_3 || "");
            $("#halal-form #supervisor-1").val(slaughtering.supervisor_1 || "");
            $("#halal-form #supervisor-2").val(slaughtering.supervisor_2 || "");
            $("#halal-form #supervisor-3").val(slaughtering.supervisor_3 || "");
            $("#halal-form #method-of-stunning").val(slaughtering.method_of_stunning || "");
            
            // Load file lists
            filesToList("ulhalal_slaughtering_documents", slaughtering.halal_slaughtering_documents);
            filesToList("ulhalal_slaughtering_data", slaughtering.halal_slaughtering_data);
            filesToList("ulupload_live_animals_documents", slaughtering.upload_live_animals_documents);
            filesToList("ulupload_pictures_after_cleaning", slaughtering.upload_pictures_after_cleaning);
            filesToList("ulupload_halal_slaughtering_video", slaughtering.upload_halal_slaughtering_video);
            filesToList("ulupload_additional_pictures", slaughtering.upload_additional_pictures);
            filesToList("ulupload_halal_stock", slaughtering.upload_halal_stock);
            
            if (TP1.isAdmin) {
                filesToList("ulinvoice_travel_expenses", slaughtering.invoice_travel_expenses);
            }
            
            filesToList("ulproof_of_payment", slaughtering.proof_of_payment);

            if (slaughtering.status == "completed") {
                toggleTabs();
            }

            // Load batch certificate data if exists
            var batchCert = response.data.batch_certificate;
            
            if (batchCert) {
                TP2.clearForm();
                
                $("#batch-form #batch-id").val(batchCert.id);
                $("#batch-form #batch-id").attr("data-id", batchCert.id);
                $("#batch-form #batch-id").attr("data-new", 0);
                
                // Populate batch form fields
                var clientName = $("#halal-clientid option:selected").data("clientname") || batchCert.company_name || "";
                $("#batch-form #batch-company-name").val(clientName);
                $("#batch-form #batch-company-name-display").text(clientName);
                $("#batch-form #batch-date").val(batchCert.date || "");
                $("#batch-form #country-of-origin").val(batchCert.country_of_origin || "");
                $("#batch-form #quality").val(batchCert.quality || "");
                $("#batch-form #net-weight-kg").val(batchCert.net_weight_kg || "");
                $("#batch-form #gross-weight-kg").val(batchCert.gross_weight_kg || "");
                $("#batch-form #transport-by").val(batchCert.transport_by || "");
                $("#batch-form #awb-voyage-flight-no").val(batchCert.awb_voyage_flight_no || "");
                $("#batch-form #loading-port").val(batchCert.loading_port || "");
                $("#batch-form #destination").val(batchCert.destination || "");
                $("#batch-form #exporter-name").val(batchCert.exporter_name || "");
                $("#batch-form #exporter-address").val(batchCert.exporter_address || "");
                $("#batch-form #importer-name").val(batchCert.importer_name || "");
                $("#batch-form #importer-address").val(batchCert.importer_address || "");
                
                // Load file lists
                filesToList("ulupload_product_information", batchCert.upload_product_information);
                filesToList("ulupload_consignment_details", batchCert.upload_consignment_details);
                filesToList("ulbatch_invoice", batchCert.invoice);
                filesToList("ulbatch_proof_of_payment", batchCert.proof_of_payment);
                filesToList("ulhalal_batch_certificate", batchCert.halal_batch_certificate);
                
                $("#batch-form").prop("submit", 1);
                TP2.filesUploaded = [];
            }
            
            $("#halal-form").prop("submit", 1);
            TP1.filesUploaded = [];
            $("#halalModal").modal("show");
        },
        error: function(xhr, status, error) {
            alert("Error loading data: " + error);
        },
        complete: function() {
            $.unblockUI();
        }
    });
},

toggleFieldEditability: function() {
    if (!TP1.isAdmin) {
        $(".admin-fields").hide();
    } else {
        $(".admin-fields").show();
    }
    
    if (!TP1.isAuditor) {
        $(".auditor-fields").hide();
    } else {
        $(".auditor-fields").show();
    }
},

deleteSlaughtering: function() {
    if (jQuery("#halalGrid").jqGrid("getGridParam", "selrow") == null) {
        alert("Please select a record");
        return;
    }
    if (confirm("Delete the record?")) {
        TP1.sendDeleteRequest();
    }
},

createDocFromInputData: function() {
    
    var doc = {};
    doc.id = $("#halal-form #halal-id").val();
    doc.idclient = $("#halal-clientid").val();
    doc.company_name = $("#halal-form #company-name").val().trim();
    doc.contact_person_1 = $("#halal-form #contact-person-1").val().trim();
    doc.contact_person_2 = $("#halal-form #contact-person-2").val().trim();
    doc.start_datetime = $("#halal-form #slaughter-start-datetime").val().trim();
    doc.end_datetime = $("#halal-form #slaughter-end-datetime").val().trim();
    doc.type_of_animal = $("#halal-form #type-of-animal").val();
    doc.butcher_1 = $("#halal-form #butcher-1").val().trim();
    doc.butcher_2 = $("#halal-form #butcher-2").val().trim();
    doc.butcher_3 = $("#halal-form #butcher-3").val().trim();
    doc.supervisor_1 = $("#halal-form #supervisor-1").val().trim();
    doc.supervisor_2 = $("#halal-form #supervisor-2").val().trim();
    doc.supervisor_3 = $("#halal-form #supervisor-3").val().trim();
    doc.method_of_stunning = $("#halal-form #method-of-stunning").val();

    doc.halal_slaughtering_documents = Utils.filesToJSON("ulhalal_slaughtering_documents");
    doc.halal_slaughtering_data = Utils.filesToJSON("ulhalal_slaughtering_data");
    doc.upload_live_animals_documents = Utils.filesToJSON("ulupload_live_animals_documents");
    doc.upload_pictures_after_cleaning = Utils.filesToJSON("ulupload_pictures_after_cleaning");
    doc.upload_halal_slaughtering_video = Utils.filesToJSON("ulupload_halal_slaughtering_video");
    doc.upload_additional_pictures = Utils.filesToJSON("ulupload_additional_pictures");
    doc.upload_halal_stock = Utils.filesToJSON("ulupload_halal_stock");
    
    if (TP1.isAdmin) {
      doc.invoice_travel_expenses = Utils.filesToJSON("ulinvoice_travel_expenses");
    }
    doc.proof_of_payment = Utils.filesToJSON("ulproof_of_payment");
    
    return doc;
},

validateForm: function() {
    return true;
},

validateFormForComplete: function() {
    $("#halal-form .form-warning").hide();
    
    var isValid = true;
    var errors = [];
    
    // Company Name
    if ($("#halal-form #company-name").val().trim() == "") {
        Utils.notifyInput($("#halal-form #company-name"), "Company name is required");
        errors.push("Company name is required");
        isValid = false;
    }
    
    // Start Date
    if ($("#halal-form #slaughter-start-datetime").val().trim() == "") {
        Utils.notifyInput($("#halal-form #slaughter-start-datetime"), "Start date is required");
        errors.push("Start date is required");
        isValid = false;
    }
    
    // Type of Animal
    if ($("#halal-form #type-of-animal").val().trim() == "") {
        Utils.notifyInput($("#halal-form #type-of-animal"), "Type of animal is required");
        errors.push("Type of animal is required");
        isValid = false;
    }
    
    // At least one Butcher
    if ($("#halal-form #butcher-1").val().trim() == "") {
        Utils.notifyInput($("#halal-form #butcher-1"), "At least one butcher is required");
        errors.push("At least one butcher is required");
        isValid = false;
    }
    
    // At least one Supervisor
    if ($("#halal-form #supervisor-1").val().trim() == "") {
        Utils.notifyInput($("#halal-form #supervisor-1"), "At least one supervisor is required");
        errors.push("At least one supervisor is required");
        isValid = false;
    }
    
    // Method of Stunning
    if ($("#halal-form #method-of-stunning").val().trim() == "") {
        Utils.notifyInput($("#halal-form #method-of-stunning"), "Method of stunning is required");
        errors.push("Method of stunning is required");
        isValid = false;
    }
    
// File upload validations
    if ($("#ulhalal_slaughtering_documents li:not(.deleted)").length === 0) {
        errors.push("Halal slaughtering documents are required");
        isValid = false;
    }
    
    if ($("#ulhalal_slaughtering_data li:not(.deleted)").length === 0) {
        errors.push("Halal slaughtering data file (XLS) is required");
        isValid = false;
    }
    
    if ($("#ulupload_live_animals_documents li:not(.deleted)").length === 0) {
        errors.push("Live animals documents are required");
        isValid = false;
    }
    
    if ($("#ulupload_pictures_after_cleaning li:not(.deleted)").length === 0) {
        errors.push("Pictures after cleaning are required");
        isValid = false;
    }
    
    if ($("#ulupload_halal_slaughtering_video li:not(.deleted)").length === 0) {
        errors.push("Halal slaughtering video is required");
        isValid = false;
    }
    
    if ($("#ulupload_additional_pictures li:not(.deleted)").length === 0) {
        errors.push("Additional pictures are required");
        isValid = false;
    }
    
    if ($("#ulupload_halal_stock li:not(.deleted)").length === 0) {
        errors.push("Halal stock document is required");
        isValid = false;
    }
    
    if ($("#ulproof_of_payment li:not(.deleted)").length === 0) {
        errors.push("Proof of payment is required");
        isValid = false;
    }
    
    // Admin-only file validation
    if (TP1.isAdmin && $("#ulinvoice_travel_expenses li:not(.deleted)").length === 0) {
        errors.push("Invoice for travel expenses is required");
        isValid = false;
    }

    // Show all errors
    if (!isValid) {
        Utils.notify("error", "Please complete all required fields:\n\n" + errors.join("\n"));
    }
    
    return isValid;
},

  sendModifyRequest: function(doc) {
    $.ajax({
        url: "ajax/ajaxHandler.php",
        type: "POST",
        data: {
            rtype: "saveHalalSlaughtering",
            uid: 0,
            data: doc
        },
        dataType: "json",
        beforeSend: function() {
            Utils.notify("info", "Saving record...");
            $.blockUI();
        },
        success: function(response) {
            if (response.status == 0) {
                Utils.notify("error", response.statusDescription);
                return;
            }
            Utils.notify("success", "Record saved successfully");

            var d = {};
            d.itemid = doc.id;
            d.idclient = doc.idclient;
            d.itemcode = $("#halal-form #halal-id").val();
            d.itemtype = "halal_slaughtering";
            d.itemname = doc.company_name + " - " + doc.type_of_animal;
            d.action = ($("#halal-form").prop("submit") == 0) ? "New halal slaughtering record added" : "Halal slaughtering record updated";

            if (TP1.filesUploaded.length > 0) {
                d.action = "Halal slaughtering documents updated";
                d.documents = JSON.stringify(TP1.filesUploaded);
            }

            $("#halal-form").prop("submit", 1);
            jQuery("#halalGrid").trigger("reloadGrid");
        },
        error: function(xhr, status, error) {
            Utils.notify("error", "Error saving record: " + error);
        },
        complete: function() {
            $.unblockUI();
        }
    });
},

sendRemoveRequest: function() {
    var doc = { id: $("#halal-form #halal-id").attr("data-id") };
    $.post("ajax/ajaxHandler.php", {
        rtype: "removeHalalSlaughtering",
        uid: 0,
        data: doc
    }).done(function(data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
        }
        jQuery("#halalGrid").trigger("reloadGrid");
        Utils.notify("success", "Record was removed");
    });
},

sendDeleteRequest: function() {
    var doc = {};
    doc.ids = $("#halalGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
        rtype: "markDeletedHalalSlaughtering",
        uid: 0,
        data: doc
    }).done(function(data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
        }
        jQuery("#halalGrid").trigger("reloadGrid");
        Utils.notify("success", "Records were deleted");
    });
},

onSave: function() {
    TP1.clearAlerts();
        
    if (!TP1.validateForm()) {
        return;
    }

    var doc = TP1.createDocFromInputData();
    TP1.sendModifyRequest(doc);
},

  onExportGridToExcel: function() {
    var clientId = $("#halal-clientid").val();
    window.open("ajax/exportHalalSlaughtering.php?idclient=" + clientId, "_blank");
  },

  onToggleRemovedRecordsMode: function (e) {
    if (TP1.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      TP1.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      TP1.gridMode = 1;
    }
    $("#halal-clientid").trigger("change");
  },

  init: function() {
    $(document).ready(function() {
      TP1.onDocumentReady();

       $('.sidebar-menu li').click(function(e) {
        e.preventDefault();

        if ($(this).hasClass('locked')) {
          return false;
        }

        $('.sidebar-menu li').removeClass('active');

        $(this).addClass('active');

        $('#halal-tab1, #halal-tab2').hide();

        if ($(this).hasClass('tab_step1')) {
            $('#halal-tab1').show();
        } else if ($(this).hasClass('tab_step2')) {
            $('#halal-tab2').show();
        }
    });


    });
  }
};

TP1.init();

var TP2 = {

  isAdmin:<?php echo $isAdmin ? 'true' : 'false'; ?>,
  isAuditor:<?php echo $isAuditor ? 'true' : 'false'; ?>,
  filesUploaded: [],
  gridMode: 0,

  onDocumentReady: function() {

    $('[data-toggle="tooltip"]').tooltip();

    $("input").focus(function() {
      TP2.clearAlerts();
    });

    $("select").change(function() {
      TP2.clearAlerts();
    });
 
     $(".datepicker").datepicker({
      autoUpdateInput: false,
      autoclose: true,
      format: "dd M yyyy",
      orientation: "bottom",
    });

    $(".datepicker").on("changeDate", function(e) {
      TP2.clearAlerts();
    });

    // Save button handler
    $("#btnSaveBatchCertificate").click(function() {
      TP2.onSave();
    });

    // Modal event handler for cleanup
    $("#batch-form").on("hidden.bs.modal", function(e) {
      if ($(e.target).prop("submit") == 0) {
        TP2.sendRemoveRequest();
      } else {
        jQuery("#halalGrid").trigger("reloadGrid");
      }
    });

  },

clearForm: function() {
    TP2.clearAlerts();
    $(".datepicker").datepicker("update", "");
    $("#ulupload_product_information").empty();
    $("#ulupload_consignment_details").empty();
    $("#ulbatch_invoice").empty();
    $("#ulbatch_proof_of_payment").empty();
    $("#ulhalal_batch_certificate").empty();
    $("#batch-form input").val("");
    $("#batch-form textarea").val("");
    $("#batch-form select").val("");
    $("#batch-form #batch-company-name-display").text("");
    $("#batch-form .form-warning").hide();
},

clearAlerts: function() {
    $(".alert-string").text("");
},

createDocFromInputData: function() {
    var doc = {};
    doc.id = $("#batch-form #batch-id").val();
    doc.idhalal_slaughtering = $("#halal-form #halal-id").val();
    doc.idclient = $("#halal-clientid").val();
    doc.company_name = $("#batch-form #batch-company-name").val().trim();
    doc.date = $("#batch-form #batch-date").val().trim();
    doc.country_of_origin = $("#batch-form #country-of-origin").val().trim();
    doc.quality = $("#batch-form #quality").val().trim();
    doc.net_weight_kg = $("#batch-form #net-weight-kg").val().trim();
    doc.gross_weight_kg = $("#batch-form #gross-weight-kg").val().trim();
    doc.transport_by = $("#batch-form #transport-by").val();
    doc.awb_voyage_flight_no = $("#batch-form #awb-voyage-flight-no").val().trim();
    doc.loading_port = $("#batch-form #loading-port").val().trim();
    doc.destination = $("#batch-form #destination").val().trim();
    doc.exporter_name = $("#batch-form #exporter-name").val().trim();
    doc.exporter_address = $("#batch-form #exporter-address").val().trim();
    doc.importer_name = $("#batch-form #importer-name").val().trim();
    doc.importer_address = $("#batch-form #importer-address").val().trim();
    doc.upload_product_information = Utils.filesToJSON("ulupload_product_information");
    doc.upload_consignment_details = Utils.filesToJSON("ulupload_consignment_details");
    doc.invoice = Utils.filesToJSON("ulbatch_invoice");
    doc.proof_of_payment = Utils.filesToJSON("ulbatch_proof_of_payment");
    doc.halal_batch_certificate = Utils.filesToJSON("ulhalal_batch_certificate");
    return doc;
},

validateForm: function() {
    return true;
},

validateFormForComplete: function() {
    $("#batch-form .form-warning").hide();
    
    var isValid = true;
    var errors = [];
    
    // Company Name
    if ($("#batch-form #batch-company-name").val().trim() == "") {
        Utils.notifyInput($("#batch-form #batch-company-name"), "Company name is required");
        errors.push("Company name is required");
        isValid = false;
    }
    
    // Country of Origin
    if ($("#batch-form #country-of-origin").val().trim() == "") {
        Utils.notifyInput($("#batch-form #country-of-origin"), "Country of origin is required");
        errors.push("Country of origin is required");
        isValid = false;
    }
    
    // Weights
    if ($("#batch-form #net-weight-kg").val().trim() == "") {
        Utils.notifyInput($("#batch-form #net-weight-kg"), "Net weight is required");
        errors.push("Net weight is required");
        isValid = false;
    }
    
    if ($("#batch-form #gross-weight-kg").val().trim() == "") {
        Utils.notifyInput($("#batch-form #gross-weight-kg"), "Gross weight is required");
        errors.push("Gross weight is required");
        isValid = false;
    }
    
    // Transport
    if ($("#batch-form #transport-by").val().trim() == "") {
        Utils.notifyInput($("#batch-form #transport-by"), "Transport method is required");
        errors.push("Transport method is required");
        isValid = false;
    }
    
    // File upload validations
    // File upload validations
if ($("#ulupload_product_information li:not(.deleted)").length === 0) {
    errors.push("Product information is required");
    isValid = false;
}

if ($("#ulupload_consignment_details li:not(.deleted)").length === 0) {
    errors.push("Consignment details are required");
    isValid = false;
}

if ($("#ulbatch_invoice li:not(.deleted)").length === 0) {
    errors.push("Invoice is required");
    isValid = false;
}

if ($("#ulbatch_proof_of_payment li:not(.deleted)").length === 0) {
    errors.push("Proof of payment is required");
    isValid = false;
}

if ($("#ulhalal_batch_certificate li:not(.deleted)").length === 0) {
    errors.push("Halal batch certificate is required");
    isValid = false;
}
    
    // Show all errors
    if (!isValid) {
        Utils.notify("error", "Please complete all required fields:\n\n" + errors.join("\n"));
    }
    
    return isValid;
},

onSave: function() {
    TP2.clearAlerts();
    if (!TP2.validateForm()) {
        return;
    }
    var doc = TP2.createDocFromInputData();
    TP2.sendModifyRequest(doc);
},


  sendModifyRequest: function(doc) {
    $.ajax({
        url: "ajax/ajaxHandler.php",
        type: "POST",
        data: {
            rtype: "saveBatchCertificate",
            uid: 0,
            data: doc
        },
        dataType: "json",
        beforeSend: function() {
            Utils.notify("info", "Saving batch certificate...");
            $.blockUI();
        },
        success: function(response) {
            if (response.status == 0) {
                Utils.notify("error", response.statusDescription);
                return;
            }
            Utils.notify("success", "Batch certificate saved successfully");

            var d = {};
            d.itemid = doc.id;
            d.idclient = doc.idclient;
            d.itemcode = $("#batch-form #batch-id").val();
            d.itemtype = "halal_batch_certificates";
            d.itemname = doc.company_name + " - " + doc.country_of_origin;
            d.action = ($("#batch-form").prop("submit") == 0) ? "New batch certificate added" : "Batch certificate updated";

            if (TP2.filesUploaded.length > 0) {
                d.action = "Batch certificate documents updated";
                d.documents = JSON.stringify(TP2.filesUploaded);
            }

            $("#batch-form").prop("submit", 1);
            jQuery("#halalGrid").trigger("reloadGrid");
        },
        error: function(xhr, status, error) {
            Utils.notify("error", "Error saving batch certificate: " + error);
        },
        complete: function() {
            $.unblockUI();
        }
    });
},

sendRemoveRequest: function() {
    var doc = { id: $("#batch-form #batch-id").attr("data-id") };
    $.post("ajax/ajaxHandler.php", {
        rtype: "removeBatchCertificate",
        uid: 0,
        data: doc
    }).done(function(data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
        }
        jQuery("#halalGrid").trigger("reloadGrid");
        Utils.notify("success", "Batch certificate was removed");
    });
},

sendDeleteRequest: function() {
    var doc = {};
    doc.ids = $("#halalGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
        rtype: "markDeletedBatchCertificate",
        uid: 0,
        data: doc
    }).done(function(data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
        }
        jQuery("#halalGrid").trigger("reloadGrid");
        Utils.notify("success", "Batch certificates were deleted");
    });
},

 
  onExportGridToExcel: function() {
    var clientId = $("#halal-clientid").val();
    window.open("ajax/exportBatchCertificates.php?idclient=" + clientId, "_blank");
  },

  onToggleRemovedRecordsMode: function (e) {
    if (TP2.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      TP2.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      TP2.gridMode = 1;
    }
    $("#halal-clientid").trigger("change");
  },

  init: function() {
    $(document).ready(function() {
      TP2.onDocumentReady();
    });
  }
};

TP2.init();


  filesToList = function (elementName, value) {
    $("#" + elementName).empty();
    if (value && value.length > 0) {
      var arr = JSON.parse("[" + value + "]");
      var filename, start, end;
      arr.forEach(function (a) {
        console.log(a);
        if (a.invalid === "undefined") a.invalid = 0;
        if (a.name.length > 40) ell = a.name.substr(0, 35) + "...";
        else ell = a.name;

        var cl = "uploaded-file-name " + (a.deleted ? "deleted" : "");
        if (a.invalid == 1) cl += " invalid-file";
        filename = $(
          '<li class="' +
            cl +
            '" originalname="' +
            encodeURI(JSON.stringify(a)) +
            '"></li>'
        );
        filename.append($("<span>", { text: ell }));

        if (a.deleted && a.deleted_at) {
          filename.append(
            ' <strong class="text-danger" style="float:right">(' +
              a.deleted_at +
              " by " +
              a.deleted_by +
              ")</strong> "
          );
        } else {
          if (1) {
            start = a.glink ? a.glink.indexOf("file/d/") + 7 : 0;
            end = a.glink ? a.glink.indexOf("/view") : 0;
            filename.append(
              $(
                '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                  "fileid=" +
                  (a.glink ? a.glink.substring(start, end) : "") +
                  " hostpath=" +
                  encodeURI(a.hostpath) +
                  ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
              ).bind("click", function (e) {
                delDocClick(e);
              })
            );
          }
        }
        $("#" + elementName).append(filename);
      });
    }
  }

function saveHalalData() {
    if ($('#halal-tab1').hasClass('active')) {
        TP1.onSave();
    } else if ($('#halal-tab2').hasClass('active')) {
        TP2.onSave();
    }
}

function markHalalComplete() {
    var halalId = $('#halal-form #halal-id').val();
    if (!halalId) {
        Utils.notify("error", "Please select a record first");
        return;
    }
    
    // Validate form before marking as complete
    if ($('#halal-tab1').is(':visible')) {
        if (!TP1.validateFormForComplete()) {
            return;
        }
    } else if ($('#halal-tab2').is(':visible')) {
        if (!TP2.validateFormForComplete()) {
            return;
        }
    }
    
    // Confirm before proceeding
    if (!confirm('Are you sure you want to mark this record as complete? This action cannot be undone.')) {
        return;
    }
    
    completeHalalSlaughtering(halalId);
}

function completeHalalSlaughtering(halalId) {
    $.ajax({
        url: "ajax/ajaxHandler.php",
        type: "POST",
        data: {
            rtype: "completeHalalSlaughtering",
            data: { id: halalId }
        },
        dataType: "json",
        beforeSend: function() {
            $.blockUI({
                message: '<h4><i class="ace-icon fa fa-spinner fa-spin"></i> Processing...</h4>'
            });
        },
        success: function(response) {
            if (response.status == 1) {
                toggleTabs();
                Utils.notify("success", response.message || "Record completed successfully");
                jQuery("#halalGrid").trigger("reloadGrid");
            } else {
                var errorMsg = response.statusDescription || response.message || "Error completing record";
                if (response.errors && response.errors.length > 0) {
                    errorMsg += "\n\nMissing fields:\n" + response.errors.join("\n");
                }
                Utils.notify("error", errorMsg);
            }
        },
        error: function(xhr, status, error) {
            Utils.notify("error", "Error: " + error);
        },
        complete: function() {
            $.unblockUI();
        }
    });
}

function toggleTabs(toggle = 1) {
    if (toggle == 1) {
        $('.tab_step1').removeClass('active');
        $('.tab_step1').addClass('completed');

        $('.tab_step2').removeClass('locked');
        $('.tab_step2').addClass('active');

        $("#halal-tab1").hide();
        $("#halal-tab2").show();
        
        // Set company name from selected client for batch certificate
        var clientName = $("#halal-clientid option:selected").data("clientname") || $("#halal-clientid option:selected").text();
        if (!$("#batch-form #batch-company-name").val()) {
            $("#batch-form #batch-company-name").val(clientName);
            $("#batch-form #batch-company-name-display").text(clientName);
        }
        
        $('#btnCompleteHalalSlaughtering').hide();
    }
    else {

        $('.tab_step1').addClass('active');
        $('.tab_step1').removeClass('completed');

        $('.tab_step2').addClass('locked');
        $('.tab_step2').removeClass('active');

        $("#halal-tab1").show();
        $("#halal-tab2").hide();            
        
        $('#btnCompleteHalalSlaughtering').show();
    }
}

function markBatchComplete() {
    var batchId = $('#batch-form #batch-id').val();
    var halalId = $('#halal-form #halal-id').val();
    
    if (!batchId && !halalId) {
        Utils.notify("error", "Please select a record first");
        return;
    }
     
    // Confirm before proceeding
    if (!confirm('Are you sure you want to mark this batch certificate as complete? This action cannot be undone.')) {
        return;
    }
    
    completeBatchCertificate(halalId);
}

function completeBatchCertificate(halalId) {
    $.ajax({
        url: "ajax/ajaxHandler.php",
        type: "POST",
        data: {
            rtype: "completeBatchCertificate",
            data: { id: halalId }
        },
        dataType: "json",
        beforeSend: function() {
            $.blockUI({
                message: '<h4><i class="ace-icon fa fa-spinner fa-spin"></i> Processing...</h4>'
            });
        },
        success: function(response) {
            if (response.status == 1) {
                toastr.success(response.message || "Batch certificate completed successfully", "Success");
                jQuery("#halalGrid").trigger("reloadGrid");
                $("#halalModal").modal("hide");
            } else {
                var errorMsg = response.statusDescription || response.message || "Error completing batch certificate";
                if (response.errors && response.errors.length > 0) {
                    errorMsg += "\n\nMissing fields:\n" + response.errors.join("\n");
                }
                toastr.error(errorMsg, "Error");
            }
        },
        error: function(xhr, status, error) {
            toastr.error("Error: " + error, "Error");
        },
        complete: function() {
            $.unblockUI();
        }
    });
}
</script>
</body>
</html>+