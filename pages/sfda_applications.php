<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">    <title>SFDA Applications - Halal Digital</title>
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
        #accreditation_other_text {
            margin-top: 10px;
        }
        .modal-lg {
          width:100%;
    max-width: 75%; /* Default is 800px in Bootstrap 5 */
}
          .app-sidebar {
            width: 100%;
            background-color: #ffffff; /* White background */
            color: #333333; /* Dark text */            
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

        /* DEFAULT (Inactive) */
        .sidebar-menu li a {
            color: #666;
        }

        /* COMPLETED (Circle checkmark) */
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

          /* ACTIVE STATE (When tab is clicked/selected) */
        .sidebar-menu li.active a {
            background-color: rgba(67, 160, 71, 0.2);
            border-left: 4px solid #43a047;
            color: #333;
            font-weight: bold;
        }
        

        /* CURRENT (Blue with arrow) */
        .sidebar-menu li.active a {
            background-color: rgba(76, 201, 240, 0.1); /* Light blue */
            border-left: 4px solid #4cc9f0;
            color: #333;
            font-weight: bold;
        }
        .sidebar-menu li.active a::before {
            content: "\f061"; /* FontAwesome arrow */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 10px;
            color: #4cc9f0; /* Blue */
        }

        /* LOCKED (Gray with padlock) */
        .sidebar-menu li.locked a {
            color: #999;
            cursor: not-allowed;
        }
        .sidebar-menu li.locked a::before {
            content: "\f023"; /* FontAwesome padlock */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 10px;
            color: #999;
        }

        /* Hover effects (excluding locked items) */
        .sidebar-menu li:not(.locked) a:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
        
        .sidebar-menu li i {
          display: none !important;
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
                        SFDA
                    </h2>
                    <div class="widget-box widget-border" style="margin: 15px 0;">
                        <div class="widget-body">
                            <div class="widget-main">

                                  <?php
                                
                                    $parent_id = $myuser->userdata['id'];
                                    $hasFacilities = false;

                                    if ($isAuditor) { // Auditor
                                        $ids = [-1];
                                        $clients_audit = $myuser->userdata['clients_audit'];
                                        if ($clients_audit != "") {
                                        $ids = json_decode($clients_audit);
                                        }
                                        $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND deleted = 0 AND id IN (".implode(",", $ids).") ORDER BY name";
                                    }
                                    else if ($isClient) {
                                        // Get facilities
                                        $sql = "SELECT id, name, prefix FROM tusers WHERE (id = '".$parent_id."' OR parent_id = '".$parent_id."') AND isclient = 1 AND deleted = 0 ORDER BY parent_id ASC, name";
                                    
                                    }
                                    else   { // Admin
                                        $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND IFNULL(parent_id,'0') = '0' AND deleted = 0  ORDER BY name";
                                    }  
                                    
                                    $clients = [];
                                    $stmt = $dbo->prepare($sql);
                                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                    if ($stmt->execute()) { 
                                        $clients = $stmt->fetchAll();
                                    }
                                    
                                    // Fetch all child clients and organize them in an array by parent_id
                                    $sql = "SELECT id, name, prefix, parent_id FROM tusers WHERE isclient=1 AND IFNULL(parent_id,'0') <> '0' AND deleted = 0 ORDER BY name";

                                    $childClients = [];
                                    $stmt = $dbo->prepare($sql);
                                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                    if ($stmt->execute()) { 
                                        $allChildren = $stmt->fetchAll();
                                        foreach ($allChildren as $child) {
                                            $childClients[$child['parent_id']][] = $child; // Group children under parent_id
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
                                        <input type="hidden" id="sfda-clientid" data-email="<?php echo $myuser->userdata['email']; ?>" value=<?php echo $_SESSION['halal']['id']; ?> data-clientname="<?php echo $myuser->userdata['name']," (",$myuser->userdata['prefix'],$myuser->userdata['id'],")"; ?>"/>
                                    <?php endif;?>

                                    <?php if (!$isClient || $hasFacilities): ?>
                                        <div class="form-inline">
                                            <div class="form-group">
                                                <label><?php if ($isClient): ?> Facilities <?php else: ?> Clients <?php endif; ?> &nbsp;&nbsp;</label>
                                                <select class="form-control clientslist" id="sfda-clientid">
                                                    <?php if (!$isClient): ?>
                                                        <option value="-1">Select <?php if ($isClient): ?> Facility <?php else: ?> Client <?php endif; ?></option>
                                                    <?php endif; ?>
                                                    <?php
                                                        foreach ($clients as $client) {
                                                            ?>
                                                            <option value="<?php echo $client["id"]; ?>" <?php if ($client["id"] == $_GET["idclient"] || $client["id"] == $myuser->userdata['id']):?>selected<?php endif; ?> data-clientname="<?php echo $client['name']," (",$client['prefix'],$client['id'],")"; ?>" ><?php echo $client["name"]; ?> - <?php echo $client["prefix"]; ?><?php echo $client["id"]; ?></option>
                                                            <?php
                                                            // Check if there are children for this parent and display them with indentation
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
                            <table id="sfdaGrid"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sfdaModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="sfdaModal-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="sfdaModal-label">Add SFDA Application</h4>
            </div>
            <div class="modal-body">
           <div class="row" >
              <div class="col-md-4">
                <div class="app-sidebar">
                  <ul class="sidebar-menu">
                    <li class="tab_step1 active"><a data-toggle="tab" href="#">First Application to SFDA <i class="fa"></i></a></li>
                    <li class="tab_step2 locked"><a data-toggle="tab" href="#">SFDA Shipment Certificate <i class="fa"></i></a></li>
                  </ul>  
                </div>  
            </div>
            <div class="col-md-8">
              <div id="sfda-tab1">
                <form id="sfda-form" class="form-horizontal">
                    <input type="hidden" id="sfda-id" value="" />
                    
                    
                    <!-- Company Name -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Company Name</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="company-name"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Address</label>
                        <div class='col-xs-12 col-md-8'>
                            <textarea class="form-control" id="address" rows="1"></textarea>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Commercial Registration Certificate -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Commercial Registration Certificate</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-commercial-reg-cert">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-commercial-reg-cert" type="file" name="files[]" foldertype="commercial_registration_certificate">
                            </span>
                            <ul id="ulcommercial_registration_certificate"></ul>
                        </div>
                    </div>
                    
                    <!-- Commercial Registration No -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Commercial Registration No.</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="commercial-registration-no"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- VAT Number -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">VAT Number</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="vat-number"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Accreditation Certificates -->
                  <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Accreditation Certificates</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="checkbox-group">
                                <div class="checkbox-inline-container">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="accreditation_certificates[]" value="ISO 14001"> ISO 14001
                                    </label>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="accreditation_certificates[]" value="ISO 22000"> ISO 22000
                                    </label>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="accreditation_certificates[]" value="ISO 22716"> ISO 22716
                                    </label>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="accreditation_certificates[]" value="GMP"> GMP
                                    </label>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="accreditation_certificates[]" value="Other" id="accreditation_other"> Other
                                    </label>
                                </div>
                                <div id="accreditation_other_text" style="display:none;">
                                    <textarea class="form-control" id="accreditation-certificates-other" 
                                              placeholder="Please specify other certifications..." rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Number of production lines -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Number of Production Lines</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="number-of-production-lines" min="0"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Number of critical points -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Number of Critical Points</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="number-of-critical-points" min="0"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Number of full time employees -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Number of Full Time Employees</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="number-of-full-time-employees" min="0"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Number of shifts -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Number of Shifts</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="number-of-shifts" min="0"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Number of shift employees -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Number of Shift Employees</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="number-of-shift-employees" min="0"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Production area space -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Production Area Space (m2)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="production-area-space-m2" min="0"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Additional branches -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Additional Branches</label>
                        <div class='col-xs-12 col-md-8'>
                            <textarea class="form-control" id="additional-branches" rows="3"></textarea>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Upload product information -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Upload Product Information
                         <!-- Template download link -->
                            <div class="form-help" style="margin-bottom: 10px;">
                                <i class="ace-icon fa fa-download text-primary"></i>
                                <a href="/files/template-product-info.xlsx" target="_blank" download="template-product-info.xlsx" class="text-primary">
                                    <strong>Download Product Info Template</strong>
                                </a>
                                <br>
                                <small class="text-muted" style="color:#c0392b; font-weight:600;">
⚠️ Please use this Excel template. <strong>All columns are mandatory</strong>, and <strong>some fields must be completed in Arabic</strong>.
</small>

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
                    
                    <!-- Validity of certificate period -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Validity of Certificate Period</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="validity-period">
                                <option value="">-- Select Period --</option>
                                <option value="1 year">1 year</option>
                                <option value="2 years">2 years</option>
                                <option value="3 years">3 years</option>
                            </select>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Invoice (Admin only) -->
                    <?php if ($isAdmin): ?>
                    <div class="row form-group admin-fields">
                        <label class="col-xs-12 col-md-4">Invoice</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-invoice">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-invoice" type="file" name="files[]" foldertype="invoice">
                            </span>
                            <ul id="ulinvoice"></ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Proof of payment -->
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
                    
                    <!-- SFDA Facility Certificate (Auditor only) -->
                    <?php if ($isAuditor): ?>
                    <div class="row form-group auditor-fields">
                        <label class="col-xs-12 col-md-4">SFDA Facility Certificate</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-sfda-facility">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-sfda-facility" type="file" name="files[]" foldertype="sfda_facility_certificate">
                            </span>
                            <ul id="ulsfda_facility_certificate"></ul>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-note">all fields are required</div>

                   <div class="text-right">

                 <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveSfdaApplication" onclick="TP1.onSave();">Save Application</button>
                <?php if ($isAdmin): ?>
                <button type="button" class="btn btn-success" id="btnCompleteSfdaApplication" onclick="markSfdaComplete();">Mark as Complete</button>
                <?php endif; ?>                
                </div>

                </form>
                
                </div>

                <div id="sfda-tab2" style="display:none;">
                  <form id="shipment-form" class="form-horizontal">
                    <input type="hidden" id="shipment-id" value="" />
                    
                    <!-- Company name -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Company name</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="company-name"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Contact person -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Contact person</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="contact-person"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- E-Mail -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">E-Mail</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="email" class="form-control" id="email"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- IIDC certificate No. -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">IIDC certificate No.</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="iidc-certificate-no"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Product name -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Product name</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="product-name"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Article number -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Article number (as given in excel file)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="article-number"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Halal Digital HCP N° -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Halal Digital HCP N°</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="halal-digital-hcp-no"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Commercial Registration No of Importeur -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Commercial Registration No of Importeur (10-digits) 700XXXXXXX</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="commercial-registration-no-importeur" placeholder="700XXXXXXX"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Shipping Method -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Shipping Method</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="shipping-method">
                                <option value="">-- Select Shipping Method --</option>
                                <option value="Ocean Freight">Ocean Freight</option>
                                <option value="Air Freight">Air Freight</option>
                                <option value="Land Freight">Land Freight</option>
                            </select>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Shipping Port -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Shipping Port</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="shipping-port"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Port of Entry -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Port of Entry</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="port-of-entry"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Quantity -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Quantity (bags, bottles, etc)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="quantity"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Total Actual Weight Brutto -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Total Actual Weight Brutto (with unit)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="total-actual-weight-brutto"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Production Date -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Production Date</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datepicker" id="production-date"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Expiry date -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Expiry date</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datepicker" id="expiry-date"/>
                            <span class="alert-string"></span>
                        </div>
                    </div>
                    
                    <!-- Additional documents -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Additional documents (Packing list, shipment documents, …)</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-additional-documents">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-additional-documents" type="file" name="files[]" foldertype="additional_documents">
                            </span>
                            <ul id="uladditional_documents"></ul>
                        </div>
                    </div>
                    
                    <!-- Invoice -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Invoice</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-invoice">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-invoice" type="file" name="files[]" foldertype="invoice2">
                            </span>
                            <ul id="ulinvoice2"></ul>
                        </div>
                    </div>
                    
                    <!-- Proof of payment -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Proof of payment</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-proof-payment">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-proof-payment" type="file" name="files[]" foldertype="proof_of_payment2">
                            </span>
                            <ul id="ulproof_of_payment2"></ul>
                        </div>
                    </div>
                    
                    <!-- SFDA Shipment Certificate -->
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">SFDA Shipment Certificate</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-sfda-shipment">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-sfda-shipment" type="file" name="files[]" foldertype="sfda_shipment_certificate">
                            </span>
                            <ul id="ulsfda_shipment_certificate"></ul>
                        </div>
                    </div>

                    <div class="form-note">all fields are required</div>

                    <div class="text-right">

                     <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                     <button type="button" class="btn btn-primary" id="btnSaveSfdaShipmentCertificate" onclick="TP2.onSave();">Save Application</button>
                <?php if ($isAdmin): ?>
                <button type="button" class="btn btn-success" id="btnCompleteSfdaShipmentCertificate" onclick="markSfdaComplete();">Mark as Complete</button>
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
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script>

<script type="text/javascript">
var TP1 = {

  isAdmin:<?php echo $isAdmin ? 'true' : 'false'; ?>,
  isAuditor:<?php echo $isAuditor ? 'true' : 'false'; ?>,

  onDocumentReady: function() {

    $("#logModal").on("shown.bs.modal", function() {
      var table = $("#table_log").DataTable();
      table.ajax.reload(null, false);
    });

    Common.setMainMenuItem("sfda");

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

    $("#sfda-clientid").on("change", function() {
      if (jqGridRequest) {
        jqGridRequest.abort();
      }
      const gridParams = {
        url: "ajax/getSfdaApplications.php?displaymode=" + TP1.gridMode + "&idclient=" + this.value,
        rowNum: isNaN(parseInt(this.value)) ? 20 : 1000000,
      };

      $(".ui-paging-pager").toggle(isNaN(parseInt(this.value)));

      $("#sfda-clientid").data(
        "clientname",
        $("#sfda-clientid option:selected").text()
      );

      jQuery("#sfdaGrid").jqGrid("setGridParam", gridParams);
      jQuery("#sfdaGrid").jqGrid().trigger("reloadGrid");
    });

    // Handle accreditation certificates checkboxes
    $("#accreditation_other").change(function() {
      if ($(this).is(':checked')) {
        $('#accreditation_other_text').slideDown();
      } else {
        $('#accreditation_other_text').slideUp();
        $('#accreditation-certificates-other').val('');
      }
    });

initFileUploader({
  fileUploadSelector: "#sfda-form .fileupload",
  dropzoneSelector: "#sfda-form .dropzone",
  progressSelector: "#sfda-form .progress",

  dataModifier: function(e, data) {
    
    data.formData = {
      folderType: $(e.target).attr("foldertype"),
      infoType: "sfda_application",
      client: $("#sfda-clientid option:selected").text(),
      idapplication: $("#sfda-form #sfda-id").val(),
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


initFileUploader({
  fileUploadSelector: "#shipment-form .fileupload",
  dropzoneSelector: "#shipment-form .dropzone",
  progressSelector: "#shipment-form .progress",

  dataModifier: function(e, data) {
    
    data.formData = {
      folderType: $(e.target).attr("foldertype"),
      infoType: "sfda_application",
      client: $("#sfda-clientid option:selected").text(),
      idapplication: $("#shipment-form #sfda-id").val(),
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
      $("#sfdaGrid").jqGrid({
        url: "ajax/getSfdaApplications.php?displaymode=" + TP1.gridMode + "&idclient=" + $("#sfda-clientid").val(),
        datatype: "json",
        mtype: "POST",
        width: $("#sfdaGrid").parent().width(),
        height: h,
        colModel: [
          { name: "id", label: "ID", width: 50, key: true, hidden: true },
          { name: "idclient", label: "Client ID", width: 50, hidden: true },
          { name: "client_name", label: "Client", width: 125, frozen: true <?php if ($isClient): ?>, hidden: true<?php endif; ?> },
          { name: "company_name", label: "Company Name", width: 125, frozen: true },
          { name: "address", label: "Address", width: 125, frozen: true },
          { 
              name: "commercial_registration_certificate", 
              index: "commercial_registration_certificate", 
              label: "Commercial Reg. Cert.", 
              width: 135,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { name: "commercial_registration_no", label: "Commercial Reg. No.", width: 125 },
          { name: "vat_number", label: "VAT Number", width: 100 },
          { name: "accreditation_certificates", label: "Accreditation Cert.", width: 130 },
          { name: "accreditation_certificates_other", label: "Other Cert.", width: 100 },
          { name: "number_of_production_lines", label: "Production Lines", width: 125, align: "center" },
          { name: "number_of_critical_points", label: "Critical Points", width: 125, align: "center" },
          { name: "number_of_full_time_employees", label: "Full Time Emp.", width: 125, align: "center" },
          { name: "number_of_shifts", label: "Shifts", width: 60, align: "center" },
          { name: "number_of_shift_employees", label: "Shift Emp.", width: 125, align: "center" },
          { name: "production_area_space_m2", label: "Area (m2)", width: 125, align: "center" },
          { name: "additional_branches_of_the_company", label: "Additional Branches", width: 120 },
          { 
              name: "upload_product_information",
              index: "upload_product_information", 
              label: "Product Info", 
              width: 135,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { name: "validity_of_certificate_period", label: "Validity Period", width: 100 },
          { 
              name: "invoice", 
              index: "invoice", 
              label: "Invoice", 
              width: 135,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false,
              hidden: !TP1.isAdmin
          },
          { 
              name: "proof_of_payment",
              index: "proof_of_payment", 
              label: "Proof of Payment", 
              width: 135,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
              name: "sfda_facility_certificate", 
              index: "sfda_facility_certificate", 
              label: "SFDA Facility Cert.", 
              width: 135,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false,
              hidden: !TP1.isAuditor
          },
          { 
            name: "status", 
            label: "Status", 
            width: 125,
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
        rowList: [20, 50, 100],
        pager: "#sfdaPager",
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
          TP1.editApplication();
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
            fileUploadSelector: "#gbox_sfdaGrid .fileupload",
            dropzoneSelector: "#gbox_sfdaGrid .dropzone",
            progressSelector: "#gbox_sfdaGrid .progress",
            dataModifier: function(e, data) {
              data.formData = {
                folderType: $(e.target).attr("foldertype"),
                infoType: "sfda_application",
                client: $("#sfda-clientid option:selected").text(),
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
              FD.append("rtype", "addSfdaApplicationFiles");
              
              const colName = {
                commercial_registration_certificate: "commercial_registration_certificate",
                upload_product_information: "upload_product_information",
                invoice: "invoice",
                proof_of_payment: "proof_of_payment",
                sfda_facility_certificate: "sfda_facility_certificate"
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
                  $("#sfdaGrid").jqGrid().trigger("reloadGrid");
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
          /////////////////////////////////////////////////
           var subgridTableId = subgrid_id + "_t";
          $("#" + subgrid_id).html(
            "<table id='" + subgridTableId + "' class='scroll'></table>"
          );
          $("#" + subgridTableId).jqGrid({
   datatype: "json",
        mtype: "POST",
            url: "ajax/getSfdaShipmentCertificates.php?displaymode=" + TP2.gridMode + "&idsfdaapp=" +row_id,
        colModel: [
          { name: "id", label: "ID", width: 50, key: true, hidden: true },
          { name: "idclient", label: "Client ID", width: 50, hidden: true },
          { name: "client_name", label: "Client", width: 155, frozen: true <?php if ($isClient): ?>, hidden: true<?php endif; ?> },
          { name: "company_name", label: "Company Name", width: 155, frozen: true },
          { name: "contact_person", label: "Contact Person", width: 120, frozen: true },
          { name: "email", label: "E-Mail", width: 150 },
          { name: "iidc_certificate_no", label: "IIDC Certificate No.", width: 130 },
          { name: "product_name", label: "Product Name", width: 150, frozen: true },
          { name: "article_number", label: "Article Number", width: 120 },
          { name: "halal_digital_hcp_no", label: "Halal Digital HCP N°", width: 130 },
          { name: "commercial_registration_no_importeur", label: "Commercial Reg. No.", width: 120 },
          { name: "shipping_method", label: "Shipping Method", width: 120 },
          { name: "shipping_port", label: "Shipping Port", width: 120 },
          { name: "port_of_entry", label: "Port of Entry", width: 120 },
          { name: "quantity", label: "Quantity", width: 100 },
          { name: "total_actual_weight_brutto", label: "Total Weight", width: 100 },
          { name: "production_date", label: "Production Date", width: 105, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" } },
          { name: "expiry_date", label: "Expiry Date", width: 105, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" } },
          { 
              name: "additional_documents",
              index: "additional_documents", 
              label: "Additional Documents", 
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
              name: "sfda_shipment_certificate",
              index: "sfda_shipment_certificate", 
              label: "SFDA Certificate", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { 
            name: "status", 
            label: "Status", 
            width: 125,
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
          TP1.editShipmentCertificate();
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
            fileUploadSelector: "#gbox_sfdaGrid_"+row_id+"_t .fileupload",
            dropzoneSelector: "#gbox_sfdaGrid_"+row_id+"_t .dropzone",
            progressSelector: "#gbox_sfdaGrid_"+row_id+"_t .progress",
            dataModifier: function(e, data) {
              data.formData = {
                folderType: $(e.target).attr("foldertype"),
                infoType: "sfda_application",
                client: $("#sfda-clientid option:selected").text(),
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
              FD.append("rtype", "addShipmentCertificateFiles");
              
              const colName = {
                additional_documents: "additional_documents",
                invoice: "invoice",
                proof_of_payment: "proof_of_payment",
                sfda_shipment_certificate: "sfda_shipment_certificate"
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

              //TP1.filesUploaded?.push({ file: data.result.files[0].name });
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


        }

      });
 
       $("#sfdaGrid").jqGrid("navGrid", "#sfdaPager", {
        cloneToTop: true,
        edit: true,
        add: true,
        del: true,
        search: false,
        refresh: true,
        view: false,
        addfunc: function () {
          TP1.newApplication();
        },
        editfunc: function () {
          TP1.editApplication();
        },
        delfunc: function () {
          TP1.deleteApplication();
        },
      });

      $("#sfdaGrid").jqGrid("filterToolbar", { 
        searchOperators: true,
        enableClear: false 
      });

 $("#sfdaGrid").navButtonAdd("#sfdaGrid_toppager", {
        caption: "",
        title: "Toggle displaying removed records mode",
        buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
        onClickButton: function () {
          TP1.onToggleRemovedRecordsMode(event);
        },
      });

      $("#sfdaGrid").jqGrid("setFrozenColumns");
      resolve("grid initialized");
    });
  },

  formatDoclink: function(cellvalue, options, rowObject) {
    if (!cellvalue) return "";
    try {
      var doc = JSON.parse(cellvalue);
      return '<a href="' + doc.hostpath + '" target="_blank" title="' + doc.name + '">' + 
             '<i class="ace-icon fa fa-file-pdf-o red"></i> ' + 
             (doc.name.length > 15 ? doc.name.substring(0,15)+"..." : doc.name) +
             '</a>' +
             '<span class="fileinput-button dropzone upload-area">' +
             '<input class="fileupload" type="file" name="files[]" foldertype="' + 
             options.colModel.name + '">' +
             '</span>';
    } catch(e) {
      return cellvalue;
    }
  },

  unformatDoclink: function(cellvalue, options, rowObject) {
    return cellvalue;
  },

clearForm: function() {
    TP1.clearAlerts();
    $(".datepicker").datepicker("update", "");
    $("#ulcommercial_registration_certificate").empty();
    $("#ulupload_product_information").empty();
    $("#ulinvoice").empty();
    $("#ulproof_of_payment").empty();
    $("#ulsfda_facility_certificate").empty();
    $("#sfda-form input").val("");
    $("#sfda-form textarea").val("");
    $("#sfda-form select").val("");
    $('input[name="accreditation_certificates[]"]').prop("checked", false);
    $("#accreditation_other_text").hide();
    $("#sfda-form .form-warning").hide();
},

clearAlerts: function() {
    $(".alert-string").text("");
},

fillForm: function(data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
        alert(response.statusDescription);
        return;
    }
    if (!response.data.application) {
        $("#sfda-form #sfda-id").val(response.data.id);
        $("#sfda-form #sfda-id").attr("data-id", response.data.id);
        $("#sfda-form #sfda-id").attr("data-new", 1);
    }
    $("#sfda-form").prop("submit", 0);
    TP1.filesUploaded = [];
    TP1.toggleFieldEditability();
    $("#sfdaModal").modal("show");
    
    toggleTabs(0);
},

getNextApplicationId: function(callback) {
    var app = {};
    $.get("ajax/ajaxHandler.php", {
        uid: 0,
        data: app,
        rtype: "nextSfdaApplicationId",
    }).done(callback);
},

newApplication: function() {
    if ($("#sfda-clientid").val() == "" || $("#sfda-clientid").val() == "-1") {
        alert("Please select a client");
        return;
    }
    TP1.clearForm();
    $("#sfdaModal-label").text("New SFDA Application");
    TP1.getNextApplicationId(TP1.fillForm);
},

editApplication: function() {
    if (jQuery("#sfdaGrid").jqGrid("getGridParam", "selrow") == null) {
        alert("Please select application");
        return;
    }
    
    var selectedId = jQuery("#sfdaGrid").jqGrid("getGridParam", "selrow");
    
    // Show loading indicator
    $.blockUI({
        message: '<h4><i class="ace-icon fa fa-spinner fa-spin"></i> Loading...</h4>',
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
    
    // Make AJAX call to load application data
    $.ajax({
        url: "ajax/getSfdaApplicationById.php",
        type: "GET",
        data: { id: selectedId },
        dataType: "json",
        success: function(response) {
            if (response.status == 0) {
                alert(response.message || "Error loading application data");
                return;
            }
            
            var app = response.data.app;
            
            // Clear and populate form
            TP1.clearForm();
            
            // Set client ID
            $("#sfda-clientid").val(app.idclient);
            
            // Set form title and ID
            $("#sfda-form-label").text("Edit SFDA Application");
            $("#sfda-form #sfda-id").val(app.id);
            $("#sfda-form #sfda-id").attr("data-id", app.id);
            $("#sfda-form #sfda-id").attr("data-new", 0);
            
            // Populate form fields
            $("#sfda-form #application-name").val(app.application_name || "");
            $("#sfda-form #company-name").val(app.company_name || "");
            $("#sfda-form #address").val(app.address || "");
            $("#sfda-form #commercial-registration-no").val(app.commercial_registration_no || "");
            $("#sfda-form #vat-number").val(app.vat_number || "");
            
            // Handle accreditation certificates checkboxes
            $('input[name="accreditation_certificates[]"]').prop('checked', false);
            $('#accreditation_other_text').hide();
            
            if (app.accreditation_certificates) {
                var certificates = app.accreditation_certificates.split(', ');
                certificates.forEach(function(cert) {
                    $('input[name="accreditation_certificates[]"][value="' + cert + '"]').prop('checked', true);
                });
                if (certificates.includes('Other')) {
                    $('#accreditation_other_text').show();
                }
            }
            
            $("#sfda-form #accreditation-certificates-other").val(app.accreditation_certificates_other || "");
            $("#sfda-form #number-of-production-lines").val(app.number_of_production_lines || "");
            $("#sfda-form #number-of-critical-points").val(app.number_of_critical_points || "");
            $("#sfda-form #number-of-full-time-employees").val(app.number_of_full_time_employees || "");
            $("#sfda-form #number-of-shifts").val(app.number_of_shifts || "");
            $("#sfda-form #number-of-shift-employees").val(app.number_of_shift_employees || "");
            $("#sfda-form #production-area-space-m2").val(app.production_area_space_m2 || "");
            $("#sfda-form #additional-branches").val(app.additional_branches_of_the_company || "");
            $("#sfda-form #validity-period").val(app.validity_of_certificate_period || "");
            
            // Load file lists for upload fields
            // Note: You may need to create a helper function if Utils.filesToList expects grid data
            // Alternative approach - directly populate file lists from JSON data
            filesToList("ulcommercial_registration_certificate", app.commercial_registration_certificate);
            filesToList("ulupload_product_information", app.upload_product_information);
            
            if (TP1.isAdmin) {
                filesToList("ulinvoice", app.invoice);
            }
            
            filesToList("ulproof_of_payment", app.proof_of_payment);

            if (TP1.isAuditor) {
                filesToList("ulsfda_facility_certificate", app.sfda_facility_certificate);
            }

            if (app.status == "completed") {
                toggleTabs();
            }

            //////////////////////////////////////////////////////////////////////////////////////////////////
            var shipment = response.data.shipment;
            
            if (shipment) {
                 

                // Clear and populate form
                TP2.clearForm();
                
                // Set client ID
                $("#shipment-clientid").val(shipment.idclient);
                
                // Set form title and ID
                $("#shipment-form-label").text("Edit Shipment Certificate");
                $("#shipment-form #shipment-id").val(shipment.id);
                $("#shipment-form #shipment-id").attr("data-id", shipment.id);
                $("#shipment-form #shipment-id").attr("data-new", 0);
                
                // Populate form fields
                $("#shipment-form #company-name").val(shipment.company_name || "");
                $("#shipment-form #contact-person").val(shipment.contact_person || "");
                $("#shipment-form #email").val(shipment.email || "");
                $("#shipment-form #iidc-certificate-no").val(shipment.iidc_certificate_no || "");
                $("#shipment-form #product-name").val(shipment.product_name || "");
                $("#shipment-form #article-number").val(shipment.article_number || "");
                $("#shipment-form #halal-digital-hcp-no").val(shipment.halal_digital_hcp_no || "");
                $("#shipment-form #commercial-registration-no-importeur").val(shipment.commercial_registration_no_importeur || "");
                $("#shipment-form #shipping-method").val(shipment.shipping_method || "");
                $("#shipment-form #shipping-port").val(shipment.shipping_port || "");
                $("#shipment-form #port-of-entry").val(shipment.port_of_entry || "");
                $("#shipment-form #quantity").val(shipment.quantity || "");
                $("#shipment-form #total-actual-weight-brutto").val(shipment.total_actual_weight_brutto || "");
                $("#shipment-form #production-date").val(shipment.production_date || "");
                $("#shipment-form #expiry-date").val(shipment.expiry_date || "");
                
                // Load file lists for upload fields
                filesToList("uladditional_documents", shipment.additional_documents);
                filesToList("ulinvoice2", shipment.invoice);
                filesToList("ulproof_of_payment2", shipment.proof_of_payment);
                filesToList("ulsfda_shipment_certificate", shipment.sfda_shipment_certificate);
                
                // Set form state and show modal
                $("#shipment-form").prop("submit", 1);
                TP2.filesUploaded = [];
            }
            
            // Set form state and show modal
            $("#sfda-form").prop("submit", 1);
            TP1.filesUploaded = [];
            $("#sfdaModal").modal("show");
        },
        error: function(xhr, status, error) {
            alert("Error loading application data: " + error);
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

deleteApplication: function() {
    if (jQuery("#sfdaGrid").jqGrid("getGridParam", "selrow") == null) {
        alert("Please select application");
        return;
    }
    if (confirm("Delete the application?")) {
        TP1.sendDeleteApplicationRequest();
    }
},

createDocFromInputData: function() {
    
    var doc = {};
    doc.id = $("#sfda-form #sfda-id").val();
    doc.idclient = $("#sfda-clientid").val();
    doc.company_name = $("#sfda-form #company-name").val().trim();
    doc.address = $("#sfda-form #address").val().trim();
    doc.commercial_registration_no = $("#sfda-form #commercial-registration-no").val().trim();
    doc.vat_number = $("#sfda-form #vat-number").val().trim();
    
    // Handle accreditation certificates
    var accredCerts = [];
    $('input[name="accreditation_certificates[]"]:checked').each(function() {
      accredCerts.push($(this).val());
    });
    doc.accreditation_certificates = accredCerts.join(', ');
    doc.accreditation_certificates_other = $("#sfda-form #accreditation-certificates-other").val().trim();
    
    doc.number_of_production_lines = $("#sfda-form #number-of-production-lines").val() || null;
    doc.number_of_critical_points = $("#sfda-form #number-of-critical-points").val() || null;
    doc.number_of_full_time_employees = $("#sfda-form #number-of-full-time-employees").val() || null;
    doc.number_of_shifts = $("#sfda-form #number-of-shifts").val() || null;
    doc.number_of_shift_employees = $("#sfda-form #number-of-shift-employees").val() || null;
    doc.production_area_space_m2 = $("#sfda-form #production-area-space-m2").val() || null;
    doc.additional_branches_of_the_company = $("#sfda-form #additional-branches").val().trim();
    doc.validity_of_certificate_period = $("#sfda-form #validity-period").val();
    
    doc.commercial_registration_certificate = Utils.filesToJSON("ulcommercial_registration_certificate");
    doc.upload_product_information = Utils.filesToJSON("ulupload_product_information");
    if (TP1.isAdmin) {
      doc.invoice = Utils.filesToJSON("ulinvoice");
    }
    doc.proof_of_payment = Utils.filesToJSON("ulproof_of_payment");
    if (TP1.isAuditor) {
      doc.sfda_facility_certificate = Utils.filesToJSON("ulsfda_facility_certificate");
    }
    
    return doc;
},

/*
// Replace TP1.validateForm (around line 1632)
*/
validateForm: function() {
    // Validate Commercial Registration No. format (not required on save)
    var commercialRegNo = $("#sfda-form #commercial-registration-no").val();
    var validation = validateCommercialRegNo(commercialRegNo, false);
    if (!validation.isValid) {
        Utils.notifyInput($("#sfda-form #commercial-registration-no"), validation.errorMessage);
        return false;
    }
    return true;
},

/*
// Replace TP1.validateFormForComplete (around line 1641)
*/
validateFormForComplete: function() {
    $("#sfda-form .form-warning").hide();
    
    var isValid = true;
    var errors = [];
    
    // Company Name
    if ($("#sfda-form #company-name").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #company-name"), "Company name is required");
        errors.push("Company name is required");
        isValid = false;
    }
    
    // Address
    if ($("#sfda-form #address").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #address"), "Address is required");
        errors.push("Address is required");
        isValid = false;
    }
    
    // Commercial Registration No - Required AND must match format
    var commercialRegNo = $("#sfda-form #commercial-registration-no").val();
    var regNoValidation = validateCommercialRegNo(commercialRegNo, true);
    if (!regNoValidation.isValid) {
        Utils.notifyInput($("#sfda-form #commercial-registration-no"), regNoValidation.errorMessage);
        errors.push(regNoValidation.errorMessage);
        isValid = false;
    }    
    
    // VAT Number
    if ($("#sfda-form #vat-number").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #vat-number"), "VAT Number is required");
        errors.push("VAT Number is required");
        isValid = false;
    }
    
    // Accreditation Certificates - Use Utils.notify (checkbox group)
    if ($('input[name="accreditation_certificates[]"]:checked').length === 0) {
        errors.push("At least one accreditation certificate is required");
        isValid = false;
    }
    
    // Number of Production Lines
    if ($("#sfda-form #number-of-production-lines").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #number-of-production-lines"), "Number of Production Lines is required");
        errors.push("Number of Production Lines is required");
        isValid = false;
    }
    
    // Number of Critical Points
    if ($("#sfda-form #number-of-critical-points").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #number-of-critical-points"), "Number of Critical Points is required");
        errors.push("Number of Critical Points is required");
        isValid = false;
    }
    
    // Number of Full Time Employees
    if ($("#sfda-form #number-of-full-time-employees").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #number-of-full-time-employees"), "Number of Full Time Employees is required");
        errors.push("Number of Full Time Employees is required");
        isValid = false;
    }
    
    // Number of Shifts
    if ($("#sfda-form #number-of-shifts").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #number-of-shifts"), "Number of Shifts is required");
        errors.push("Number of Shifts is required");
        isValid = false;
    }
    
    // Number of Shift Employees
    if ($("#sfda-form #number-of-shift-employees").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #number-of-shift-employees"), "Number of Shift Employees is required");
        errors.push("Number of Shift Employees is required");
        isValid = false;
    }
    
    // Production Area Space
    if ($("#sfda-form #production-area-space-m2").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #production-area-space-m2"), "Production Area Space (m2) is required");
        errors.push("Production Area Space (m2) is required");
        isValid = false;
    }
    
    // Additional Branches
    if ($("#sfda-form #additional-branches").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #additional-branches"), "Additional Branches is required");
        errors.push("Additional Branches is required");
        isValid = false;
    }
    
    // Validity Period
    if ($("#sfda-form #validity-period").val().trim() == "") {
        Utils.notifyInput($("#sfda-form #validity-period"), "Validity of Certificate Period is required");
        errors.push("Validity of Certificate Period is required");
        isValid = false;
    }
    
    // File upload validations - Use Utils.notify (file uploads)
    if ($("#ulcommercial_registration_certificate li:not(.deleted)").length === 0) {
        errors.push("Commercial Registration Certificate upload is required");
        isValid = false;
    }
    
    if ($("#ulupload_product_information li:not(.deleted)").length === 0) {
        errors.push("Product Information upload is required");
        isValid = false;
    }
    
    if ($("#ulproof_of_payment li:not(.deleted)").length === 0) {
        errors.push("Proof of Payment upload is required");
        isValid = false;
    }
    
    // Admin-only file validation
    if (TP1.isAdmin && $("#ulinvoice li:not(.deleted)").length === 0) {
        errors.push("Invoice upload is required");
        isValid = false;
    }
    
    // Auditor-only file validation
    if (TP1.isAuditor && $("#ulsfda_facility_certificate li:not(.deleted)").length === 0) {
        errors.push("SFDA Facility Certificate upload is required");
        isValid = false;
    }
    
    // Show all errors via Utils.notify
    if (!isValid) {
        Utils.notify("error", "Please complete all required fields:\n\n" + errors.join("\n"));
    }
    
    return isValid;
},

  sendModifyApplicationRequest: function(doc) {
    $.ajax({
        url: "ajax/ajaxHandler.php",
        type: "POST",
        data: {
            rtype: "saveSfdaApplication",
            uid: 0,
            data: doc
        },
        dataType: "json",
        beforeSend: function() {
            Utils.notify("info", "Saving application...");
            $.blockUI();
        },
        success: function(response) {
            if (response.status == 0) {
                Utils.notify("error", response.statusDescription);
                return;
            }
            Utils.notify("success", "Application saved successfully");

            var d = {};
            d.itemid = doc.id;
            d.idclient = doc.idclient;
            d.itemcode = $("#sfda-form #sfda-id").val();
            d.itemtype = "sfda_applications";
            d.itemname = doc.company_name + " - " + doc.application_name;
            d.action = ($("#sfda-form").prop("submit") == 0) ? "New SFDA application added" : "SFDA application updated";

            if (TP1.filesUploaded.length > 0) {
                d.action = "SFDA application documents updated";
                d.documents = JSON.stringify(TP1.filesUploaded);
            }

            $("#sfda-form").prop("submit", 1);
            //$("#sfdaModal").modal("hide");
            jQuery("#sfdaGrid").trigger("reloadGrid");
        },
        error: function(xhr, status, error) {
            Utils.notify("error", "Error saving application: " + error);
        },
        complete: function() {
            $.unblockUI();
        }
    });
},

sendRemoveApplicationRequest: function() {
    var doc = { id: $("#sfda-form #sfda-id").attr("data-id") };
    $.post("ajax/ajaxHandler.php", {
        rtype: "removeSfdaApplication",
        uid: 0,
        data: doc
    }).done(function(data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
        }
        jQuery("#sfdaGrid").trigger("reloadGrid");
        Utils.notify("success", "Application data was removed");
    });
},

sendDeleteApplicationRequest: function() {
    var doc = {};
    doc.ids = $("#sfdaGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
        rtype: "markDeletedSfdaApplication",
        uid: 0,
        data: doc
    }).done(function(data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
        }
        jQuery("#sfdaGrid").trigger("reloadGrid");
        Utils.notify("success", "Applications were deleted");
    });
},

onSave: function() {
    TP1.clearAlerts();
        
    if (!TP1.validateForm()) {
        return; // Stop if validation fails
    }

    var doc = TP1.createDocFromInputData();
    TP1.sendModifyApplicationRequest(doc);
},

  onExportGridToExcel: function() {
    var clientId = $("#sfda-clientid").val();
    window.open("ajax/exportSfdaApplications.php?idclient=" + clientId, "_blank");
  },

  onToggleRemovedRecordsMode: function (e) {
    if (TP1.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      TP1.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      TP1.gridMode = 1;
    }
    $("#sfda-clientid").trigger("change");
  },

  init: function() {
    $(document).ready(function() {
      TP1.onDocumentReady();

       $('.sidebar-menu li').click(function(e) {
        e.preventDefault();

        // Do not allow click on locked tabs
        if ($(this).hasClass('locked')) {
          return false;
        }

        // Remove active and current from all tabs
        $('.sidebar-menu li').removeClass('active');

        // Add active and current to clicked tab
        $(this).addClass('active');

        // Hide all tab contents
        $('#sfda-tab1, #sfda-tab2').hide();

        // Show corresponding tab content
        if ($(this).hasClass('tab_step1')) {
            $('#sfda-tab1').show();
        } else if ($(this).hasClass('tab_step2')) {
            $('#sfda-tab2').show();
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

  onDocumentReady: function() {

    $("#logModal").on("shown.bs.modal", function() {
      var table = $("#table_log").DataTable();
      table.ajax.reload(null, false);
    });

    Common.setMainMenuItem("sfda_shipment");

    TP2.gridMode = 0;

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

    $("#shipment-clientid").on("change", function() {
      if (jqGridRequest) {
        jqGridRequest.abort();
      }
      const gridParams = {
        url: "ajax/getSfdaShipmentCertificates.php?displaymode=0&idclient=" + this.value,
        rowNum: isNaN(parseInt(this.value)) ? 20 : 1000000,
      };

      $(".ui-paging-pager").toggle(isNaN(parseInt(this.value)));

      $("#shipment-clientid").data(
        "clientname",
        $("#shipment-clientid option:selected").text()
      );

      jQuery("#shipmentGrid").jqGrid("setGridParam", gridParams);
      jQuery("#shipmentGrid").jqGrid().trigger("reloadGrid");
    });

    // Save button handler
    $("#btnSaveShipmentCertificate").click(function() {
      TP2.onSave();
    });

    // Modal event handler for cleanup
    $("#shipment-form").on("hidden.bs.modal", function(e) {
      if ($(e.target).prop("submit") == 0) {
        TP2.sendRemoveShipmentRequest();
      } else {
        jQuery("#shipmentGrid").trigger("reloadGrid");
      }
    });

initFileUploader({
  fileUploadSelector: "#shipment-form .fileupload",
  dropzoneSelector: "#shipment-form .dropzone",
  progressSelector: "#shipment-form .progress",

  dataModifier: function(e, data) {
    data.formData = {
      folderType: $(e.target).attr("foldertype"),
      infoType: "sfda_application",
      client: $("#shipment-clientid option:selected").text(),
      idshipment: $("#shipment-form #shipment-id").val(),
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
    TP2.filesUploaded.push({ file: file.name });
  }
});


  },

clearForm: function() {
    TP2.clearAlerts();
    $(".datepicker").datepicker("update", "");
    $("#uladditional_documents").empty();
    $("#ulinvoice2").empty();
    $("#ulproof_of_payment2").empty();
    $("#ulsfda_shipment_certificate").empty();
    $("#shipment-form input").val("");
    $("#shipment-form textarea").val("");
    $("#shipment-form select").val("");
    $("#shipment-form .form-warning").hide();
},

clearAlerts: function() {
    $(".alert-string").text("");
},

fillForm: function(data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
        alert(response.statusDescription);
        return;
    }
    if (!response.data.shipment) {
        $("#shipment-form #shipment-id").val(response.data.id);
        $("#shipment-form #shipment-id").attr("data-id", response.data.id);
        $("#shipment-form #shipment-id").attr("data-new", 1);
    }
    $("#shipment-form").prop("submit", 0);
    TP2.filesUploaded = [];
    $("#shipmentModal").modal("show");
},

getNextShipmentId: function(callback) {
    var shipment = {};
    $.get("ajax/ajaxHandler.php", {
        uid: 0,
        data: shipment,
        rtype: "nextShipmentCertificateId",
    }).done(callback);
},

newShipmentCertificate: function() {
    if ($("#shipment-clientid").val() == "" || $("#shipment-clientid").val() == "-1") {
        alert("Please select a client");
        return;
    }
    TP2.clearForm();
    $("#shipment-form-label").text("New Shipment Certificate");
    TP2.getNextShipmentId(TP2.fillForm);
},

editShipmentCertificate: function() {
    if (jQuery("#shipmentGrid").jqGrid("getGridParam", "selrow") == null) {
        alert("Please select shipment certificate");
        return;
    }
    
    var selectedId = jQuery("#shipmentGrid").jqGrid("getGridParam", "selrow");
    
    // Show loading indicator
    $.blockUI({
        message: '<h4><i class="ace-icon fa fa-spinner fa-spin"></i> Loading shipment certificate data...</h4>',
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
    
    // Make AJAX call to load shipment certificate data
    $.ajax({
        url: "ajax/getShipmentCertificateById.php",
        type: "GET",
        data: { id: selectedId },
        dataType: "json",
        success: function(response) {
            if (response.status == 0) {
                alert(response.message || "Error loading shipment certificate data");
                return;
            }
            
            var shipment = response.data;
            
            // Clear and populate form
            TP2.clearForm();
            
            // Set client ID
            $("#shipment-clientid").val(shipment.idclient);
            
            // Set form title and ID
            $("#shipment-form-label").text("Edit Shipment Certificate");
            $("#shipment-form #shipment-id").val(shipment.id);
            $("#shipment-form #shipment-id").attr("data-id", shipment.id);
            $("#shipment-form #shipment-id").attr("data-new", 0);
            
            // Populate form fields
            $("#shipment-form #company-name").val(shipment.company_name || "");
            $("#shipment-form #contact-person").val(shipment.contact_person || "");
            $("#shipment-form #email").val(shipment.email || "");
            $("#shipment-form #iidc-certificate-no").val(shipment.iidc_certificate_no || "");
            $("#shipment-form #product-name").val(shipment.product_name || "");
            $("#shipment-form #article-number").val(shipment.article_number || "");
            $("#shipment-form #halal-digital-hcp-no").val(shipment.halal_digital_hcp_no || "");
            $("#shipment-form #commercial-registration-no-importeur").val(shipment.commercial_registration_no_importeur || "");
            $("#shipment-form #shipping-method").val(shipment.shipping_method || "");
            $("#shipment-form #shipping-port").val(shipment.shipping_port || "");
            $("#shipment-form #port-of-entry").val(shipment.port_of_entry || "");
            $("#shipment-form #quantity").val(shipment.quantity || "");
            $("#shipment-form #total-actual-weight-brutto").val(shipment.total_actual_weight_brutto || "");
            $("#shipment-form #production-date").val(shipment.production_date || "");
            $("#shipment-form #expiry-date").val(shipment.expiry_date || "");
            
            // Load file lists for upload fields
            filesToList("uladditional_documents", shipment.additional_documents);
            filesToList("ulinvoice2", shipment.invoice);
            filesToList("ulproof_of_payment2", shipment.proof_of_payment);
            filesToList("ulsfda_shipment_certificate", shipment.sfda_shipment_certificate);
            
            // Set form state and show modal
            $("#shipment-form").prop("submit", 1);
            TP2.filesUploaded = [];
            $("#shipmentModal").modal("show");
        },
        error: function(xhr, status, error) {
            alert("Error loading shipment certificate data: " + error);
        },
        complete: function() {
            $.unblockUI();
        }
    });
},

deleteShipmentCertificate: function() {
    if (jQuery("#shipmentGrid").jqGrid("getGridParam", "selrow") == null) {
        alert("Please select shipment certificate");
        return;
    }
    if (confirm("Delete the shipment certificate?")) {
        TP2.sendDeleteShipmentRequest();
    }
},

createDocFromInputData: function() {
    var doc = {};
    doc.id = $("#shipment-form #shipment-id").val();
    doc.idclient = $("#shipment-clientid").val();
    doc.company_name = $("#shipment-form #company-name").val().trim();
    doc.contact_person = $("#shipment-form #contact-person").val().trim();
    doc.email = $("#shipment-form #email").val().trim();
    doc.iidc_certificate_no = $("#shipment-form #iidc-certificate-no").val().trim();
    doc.product_name = $("#shipment-form #product-name").val().trim();
    doc.article_number = $("#shipment-form #article-number").val().trim();
    doc.halal_digital_hcp_no = $("#shipment-form #halal-digital-hcp-no").val().trim();
    doc.commercial_registration_no_importeur = $("#shipment-form #commercial-registration-no-importeur").val().trim();
    doc.shipping_method = $("#shipment-form #shipping-method").val().trim();
    doc.shipping_port = $("#shipment-form #shipping-port").val().trim();
    doc.port_of_entry = $("#shipment-form #port-of-entry").val().trim();
    doc.quantity = $("#shipment-form #quantity").val().trim();
    doc.total_actual_weight_brutto = $("#shipment-form #total-actual-weight-brutto").val().trim();
    doc.production_date = $("#shipment-form #production-date").val().trim();
    doc.expiry_date = $("#shipment-form #expiry-date").val().trim();
    doc.additional_documents = Utils.filesToJSON("uladditional_documents");
    doc.invoice = Utils.filesToJSON("ulinvoice2");
    doc.proof_of_payment = Utils.filesToJSON("ulproof_of_payment2");
    doc.sfda_shipment_certificate = Utils.filesToJSON("ulsfda_shipment_certificate");
    return doc;
},

/*
// Replace TP2.validateForm (around line 2166)
*/
validateForm: function() {
    // Validate Commercial Registration No. format (not required on save)
    var commercialRegNo = $("#shipment-form #commercial-registration-no-importeur").val();
    var validation = validateCommercialRegNo(commercialRegNo, false);
    if (!validation.isValid) {
        Utils.notifyInput($("#shipment-form #commercial-registration-no-importeur"), validation.errorMessage);
        return false;
    }
    return true;
},

/*
// Replace TP2.validateFormForComplete (around line 2173)
*/
validateFormForComplete: function() {
    $("#shipment-form .form-warning").hide();
    
    var isValid = true;
    var errors = [];
    
    // Company Name
    if ($("#shipment-form #company-name").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #company-name"), "Company name is required");
        errors.push("Company name is required");
        isValid = false;
    }
    
    // Contact Person
    if ($("#shipment-form #contact-person").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #contact-person"), "Contact person is required");
        errors.push("Contact person is required");
        isValid = false;
    }
    
    // Email
    if ($("#shipment-form #email").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #email"), "Email is required");
        errors.push("Email is required");
        isValid = false;
    }
    
    // IIDC Certificate No
    if ($("#shipment-form #iidc-certificate-no").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #iidc-certificate-no"), "IIDC certificate No. is required");
        errors.push("IIDC certificate No. is required");
        isValid = false;
    }
    
    // Product Name
    if ($("#shipment-form #product-name").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #product-name"), "Product name is required");
        errors.push("Product name is required");
        isValid = false;
    }
    
    // Article Number
    if ($("#shipment-form #article-number").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #article-number"), "Article number is required");
        errors.push("Article number is required");
        isValid = false;
    }
    
    // Halal Digital HCP No
    if ($("#shipment-form #halal-digital-hcp-no").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #halal-digital-hcp-no"), "Halal Digital HCP N° is required");
        errors.push("Halal Digital HCP N° is required");
        isValid = false;
    }

    // Commercial Registration No of Importeur - Required AND must match format
    var commercialRegNo = $("#shipment-form #commercial-registration-no-importeur").val();
    var regNoValidation = validateCommercialRegNo(commercialRegNo, true);
    if (!regNoValidation.isValid) {
        Utils.notifyInput($("#shipment-form #commercial-registration-no-importeur"), regNoValidation.errorMessage);
        errors.push(regNoValidation.errorMessage);
        isValid = false;
    }    
    
    // Shipping Port
    if ($("#shipment-form #shipping-port").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #shipping-port"), "Shipping Port is required");
        errors.push("Shipping Port is required");
        isValid = false;
    }
    
    // Port of Entry
    if ($("#shipment-form #port-of-entry").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #port-of-entry"), "Port of Entry is required");
        errors.push("Port of Entry is required");
        isValid = false;
    }
    
    // Quantity
    if ($("#shipment-form #quantity").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #quantity"), "Quantity is required");
        errors.push("Quantity is required");
        isValid = false;
    }
    
    // Total Actual Weight Brutto
    if ($("#shipment-form #total-actual-weight-brutto").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #total-actual-weight-brutto"), "Total Actual Weight Brutto is required");
        errors.push("Total Actual Weight Brutto is required");
        isValid = false;
    }
    
    // Production Date
    if ($("#shipment-form #production-date").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #production-date"), "Production Date is required");
        errors.push("Production Date is required");
        isValid = false;
    }
    
    // Expiry Date
    if ($("#shipment-form #expiry-date").val().trim() == "") {
        Utils.notifyInput($("#shipment-form #expiry-date"), "Expiry date is required");
        errors.push("Expiry date is required");
        isValid = false;
    }
    
    // File upload validations - Use Utils.notify (file uploads)
    if ($("#uladditional_documents li:not(.deleted)").length === 0) {
        errors.push("Additional documents are required");
        isValid = false;
    }
    
    if ($("#ulinvoice2 li:not(.deleted)").length === 0) {
        errors.push("Invoice upload is required");
        isValid = false;
    }
    
    if ($("#ulproof_of_payment2 li:not(.deleted)").length === 0) {
        errors.push("Proof of payment upload is required");
        isValid = false;
    }
    
    if ($("#ulsfda_shipment_certificate li:not(.deleted)").length === 0) {
        errors.push("SFDA Shipment Certificate upload is required");
        isValid = false;
    }
    
    // Show all errors via Utils.notify
    if (!isValid) {
        Utils.notify("error", "Please complete all required fields:\n\n" + errors.join("\n"));
    }
    
    return isValid;
},

/*
// Replace TP2.onSave (around line 2341)
*/
onSave: function() {
    TP2.clearAlerts();
    // Validate Commercial Registration No. must start with 7
    if (!TP2.validateForm()) {
        return;
    }
    var doc = TP2.createDocFromInputData();
    TP2.sendModifyShipmentRequest(doc);
},


  sendModifyShipmentRequest: function(doc) {
    $.ajax({
        url: "ajax/ajaxHandler.php",
        type: "POST",
        data: {
            rtype: "saveShipmentCertificate",
            uid: 0,
            data: doc
        },
        dataType: "json",
        beforeSend: function() {
            Utils.notify("info", "Saving shipment certificate...");
            $.blockUI();
        },
        success: function(response) {
            if (response.status == 0) {
                Utils.notify("error", response.statusDescription);
                return;
            }
            Utils.notify("success", "Shipment certificate saved successfully");

            var d = {};
            d.itemid = doc.id;
            d.idclient = doc.idclient;
            d.itemcode = $("#shipment-form #shipment-id").val();
            d.itemtype = "sfda_shipment_certificates";
            d.itemname = doc.company_name + " - " + doc.product_name;
            d.action = ($("#shipment-form").prop("submit") == 0) ? "New shipment certificate added" : "Shipment certificate updated";

            if (TP2.filesUploaded.length > 0) {
                d.action = "Shipment certificate documents updated";
                d.documents = JSON.stringify(TP2.filesUploaded);
            }

            $("#shipment-form").prop("submit", 1);
            //$("#shipmentModal").modal("hide");
            jQuery("#shipmentGrid").trigger("reloadGrid");
        },
        error: function(xhr, status, error) {
            Utils.notify("error", "Error saving shipment certificate: " + error);
        },
        complete: function() {
            $.unblockUI();
        }
    });
},

sendRemoveShipmentRequest: function() {
    var doc = { id: $("#shipment-form #shipment-id").attr("data-id") };
    $.post("ajax/ajaxHandler.php", {
        rtype: "removeShipmentCertificate",
        uid: 0,
        data: doc
    }).done(function(data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
        }
        jQuery("#shipmentGrid").trigger("reloadGrid");
        Utils.notify("success", "Shipment certificate data was removed");
    });
},

sendDeleteShipmentRequest: function() {
    var doc = {};
    doc.ids = $("#shipmentGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
        rtype: "markDeletedShipmentCertificate",
        uid: 0,
        data: doc
    }).done(function(data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
        }
        jQuery("#shipmentGrid").trigger("reloadGrid");
        Utils.notify("success", "Shipment certificates were deleted");
    });
},

 
  onExportGridToExcel: function() {
    var clientId = $("#shipment-clientid").val();
    window.open("ajax/exportShipmentCertificates.php?idclient=" + clientId, "_blank");
  },

  onToggleRemovedRecordsMode: function (e) {
    if (TP2.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      TP2.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      TP2.gridMode = 1;
    }
    $("#shipment-clientid").trigger("change");
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
          //if (a.glink) {
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
        // add li to the list of the appropriate ul - class from folderType
        $("#" + elementName).append(filename);
      });
    }
  }
function saveSfdaData() {
    if ($('#sfda-tab1').hasClass('active')) {
        TP1.onSave();
    } else if ($('#sfda-tab2').hasClass('active')) {
        TP2.onSave();
    }
}

/*
// Replace markSfdaComplete function (around line 2437)
*/
function markSfdaComplete() {
    var applicationId = $('#sfda-form #sfda-id').val();
    if (!applicationId) {
        Utils.notify("error", "Please select an application first");
        return;
    }
    
    // Validate form before marking as complete
    if ($('#sfda-tab1').is(':visible')) {
        if (!TP1.validateFormForComplete()) {
            return;
        }
    } else if ($('#sfda-tab2').is(':visible')) {
        if (!TP2.validateFormForComplete()) {
            return;
        }
    }
    
    // Confirm before proceeding
    if (!confirm('Are you sure you want to mark this application as complete? This action cannot be undone.')) {
        return;
    }
    
    completeSfdaApplication(applicationId);
}
/*
// Replace completeSfdaApplication function (around line 2466)
*/
function completeSfdaApplication(applicationId) {
    $.ajax({
        url: "ajax/ajaxHandler.php",
        type: "POST",
        data: {
            rtype: "completeSfdaApplication",
            data: { id: applicationId }
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
                Utils.notify("success", response.message || "Application completed successfully");
                jQuery("#sfdaGrid").trigger("reloadGrid");
            } else {
                var errorMsg = response.statusDescription || response.message || "Error completing application";
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

        $("#sfda-tab1").hide();
        $("#sfda-tab2").show();            
        
        $('#btnCompleteSfdaApplication').hide();
    }
    else {

        $('.tab_step1').addClass('active');
        $('.tab_step1').removeClass('completed');

        $('.tab_step2').addClass('locked');
        $('.tab_step2').removeClass('active');

        $("#sfda-tab1").show();
        $("#sfda-tab2").hide();            
        
        $('#btnCompleteSfdaApplication').show();
    }
}

/**
 * Validate Commercial Registration No. format
 * Must be 10 digits starting with 700 (format: 700XXXXXXX)
 */
function validateCommercialRegNo(value, isRequired) {
    var trimmedValue = (value || "").trim();
    
    if (trimmedValue === "") {
        if (isRequired) {
            return { isValid: false, errorMessage: "Commercial Registration No. is required" };
        }
        return { isValid: true, errorMessage: "" };
    }
    
    var regNoPattern = /^700\d{7}$/;
    if (!regNoPattern.test(trimmedValue)) {
        return { isValid: false, errorMessage: "Commercial Registration No. must be 10 digits starting with 700 (e.g., 700XXXXXXX)" };
    }
    
    return { isValid: true, errorMessage: "" };
}
</script>
</body>
</html>