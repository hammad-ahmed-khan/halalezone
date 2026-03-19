<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php'); ?>
    <title>Products - Halal Digital</title>
    <style>
        /* Modern Professional Group Tabs */
        .group-tabs {
            margin-bottom: 25px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 0;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
        }

        .group-tabs .nav-tabs {
            border: none;
            margin: 0;
            padding: 8px;
            background: transparent;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .group-tabs .nav-tabs > li {
            margin: 0;
            flex: 0 0 auto;
        }

        .group-tabs .nav-tabs > li > a {
            font-size: 14px;
            font-weight: 600;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            color: #64748b;
            background: transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            text-decoration: none;
            white-space: nowrap;
            min-width: 100px;
            text-align: center;
            letter-spacing: 0.025em;
        }

        .group-tabs .nav-tabs > li > a:hover {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .group-tabs .nav-tabs > li.active > a,
        .group-tabs .nav-tabs > li.active > a:hover,
        .group-tabs .nav-tabs > li.active > a:focus {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            font-weight: 700;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
            border: none;
        }

        .group-tabs .nav-tabs > li.active > a::before {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #3b82f6;
        }

        /* Bulk Actions Bar Styling */
        .bulk-actions-bar {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            display: none;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .bulk-actions-bar .selected-count {
            font-weight: 600;
            color: #475569;
            margin-right: 15px;
        }

        .bulk-actions-bar .btn {
            margin-right: 8px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .group-tabs .nav-tabs {
                padding: 6px;
                gap: 3px;
            }
            
            .group-tabs .nav-tabs > li > a {
                font-size: 13px;
                padding: 10px 16px;
                min-width: 80px;
            }
        }

        /* Loading state for tabs */
        .group-tabs.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .group-tabs.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid #e2e8f0;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Enhanced focus states for accessibility */
        .group-tabs .nav-tabs > li > a:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Badge/count styling (if needed for showing product counts) */
        .group-tabs .nav-tabs > li > a .badge {
            background: rgba(255, 255, 255, 0.2);
            color: inherit;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 6px;
        }

        .group-tabs .nav-tabs > li.active > a .badge {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Smooth entry animation */
        .group-tabs {
            animation: slideInDown 0.3s ease-out;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom scrollbar for tabs on mobile */
        .group-tabs .nav-tabs {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .group-tabs .nav-tabs::-webkit-scrollbar {
            height: 4px;
        }

        .group-tabs .nav-tabs::-webkit-scrollbar-track {
            background: transparent;
        }

        .group-tabs .nav-tabs::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        #prodModal .form-group {
            margin-bottom:10px;
        }

        /* Bootstrap Dual Listbox overrides for modal */
        #prodModal .bootstrap-duallistbox-container {
            margin-bottom: 0;
        }
        #prodModal .bootstrap-duallistbox-container .box1,
        #prodModal .bootstrap-duallistbox-container .box2 {
            width: 50%;
        }
        #prodModal .bootstrap-duallistbox-container select.form-control {
            height: 250px !important;
            font-size: 12px;
        }
        #prodModal .bootstrap-duallistbox-container .filter-container {
            margin-bottom: 5px;
        }
        #prodModal .bootstrap-duallistbox-container textarea.filter {
            font-size: 12px;
            min-height: 60px;
            line-height: 1.4;
        }
        #prodModal .bootstrap-duallistbox-container .filter-highlights {
            font-size: 12px;
            line-height: 1.4;
        }
        #prodModal .bootstrap-duallistbox-container .btn-group .btn {
            font-size: 12px;
            padding: 4px 8px;
        }
        #prodModal .bootstrap-duallistbox-container label {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        #prodModal .bootstrap-duallistbox-container .info {
            font-size: 11px;
        }
    </style>
    <link rel="stylesheet" href="css/bootstrap-duallistbox.min.css">
</head>
<body>
<?php
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
?>
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">
                        <div class="widget-box widget-border" style="margin-bottom:15px;">
                            <div class="widget-body">
                                <div class="widget-main">
                                    <?php 
                                    $db = acsessDb :: singleton();
                                    $dbo =  $db->connect(); // Создаем объект подключения к БД
                                    
                                    $myuser = cuser::singleton();
                                    $myuser->getUserData();
                                
                                    $parent_id = $myuser->userdata['id'];
                                    $isClient = $myuser->userdata['isclient'] == "1" ? true : false;
                                    $isAuditor = $myuser->userdata['isclient'] == '2' ? true : false;
                                    $isOdAuditor = $isAuditor && $myuser->userdata['is_od_auditor'] == '1';
                                    $isAdmin = !$isClient && !$isAuditor;
                                    $hasFacilities = false;

                                    if ($isAuditor) { // Auditor
                                        if ($isOdAuditor) {
                                            // OD Auditor sees all clients like an admin
                                            $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND IFNULL(parent_id,'0') = '0' AND deleted = 0 ORDER BY name";
                                        } else {
                                            // Regular Auditor sees only assigned clients
                                            $ids = [-1];
                                            $clients_audit = $myuser->userdata['clients_audit'];
                                            if ($clients_audit != "") {
                                                $ids = json_decode($clients_audit);
                                            }
                                            $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND deleted = 0 AND id IN (".implode(",", $ids).") ORDER BY name";
                                        }
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
                                        <input type="hidden" id="prod-clientid" data-email="<?php echo $myuser->userdata['email']; ?>" value=<?php echo $_SESSION['halal']['id']; ?> data-clientname="<?php echo $myuser->userdata['name']," (",$myuser->userdata['prefix'],$myuser->userdata['id'],")"; ?>"/>
                                    <?php endif;?>

                                    <?php if (!$isClient || $hasFacilities): ?>
                                        <div class="form-inline">
                                            <div class="form-group">
                                                <label><?php if ($isClient): ?> Facilities <?php else: ?> Clients <?php endif; ?> &nbsp;&nbsp;</label>
                                                <select class="form-control clientslist" id="prod-clientid">
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
                                            <label class="right" style="margin:10px 0;" >
                                                <input id="filter-conformed" class="ace ace-switch ace-switch-4" type="checkbox">
                                                <span class="lbl">&nbsp;&nbsp;Show only non-conformed products</span>
                                            </label>
                                        </div>
                                    <?php endif;?> 
                                </div>
                            </div>    
                        </div>
                    </div>   
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <!-- Group Tabs -->
                        <div class="group-tabs" style="display: none;">
                            <ul class="nav nav-tabs" id="groupTabs">
                                <!-- Tabs will be populated dynamically -->
                            </ul>
                        </div>
                        
                        <!-- Bulk Actions Bar -->
                        <div class="bulk-actions-bar" id="bulkActionsBar">
                            <span class="selected-count" id="selectedCount">0 products selected</span>
                            <button type="button" class="btn btn-primary btn-sm" onclick="openBulkAssignModal();">
                                <i class="ace-icon fa fa-tags"></i> Bulk Assign Groups
                            </button>
                            <button type="button" class="btn btn-default btn-sm" onclick="clearSelection();">
                                Clear Selection
                            </button>
                        </div>
                        
                        <!-- PAGE CONTENT BEGINS -->
                        <table id="prodGrid"></table>
                        <div id="prodPager"></div>
                        <ul class="legend">
                            <li><span class="highlighted-conformed">Confirmed</span> </li>
                            <li><span class="highlighted-nonconformed">Non-Confirmed</span></li>
                            <li><span class="highlighted-expired">Expired</span></li>
                            <li><span class="highlighted-week">Expire in 1 Week</span></li>
                            <li><span class="highlighted-4week">Expire in 4 Weeks</span></li>
                            <li><span class="highlighted-8week">Expire in 8 Weeks</span></li>
                        </ul>
                        <!-- PAGE CONTENT ENDS -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
</div>
	<?php include_once('pages/footer.php');?>
</div><!-- /.main-container -->

<div class="modal" id="additionalItemsCycleModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="display:inline;">Select Certification Cycle</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="float:right;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <select id="additionalItemsCycleId" class="form-control">
          <option value="">-- Select Certification Cycle --</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button id="confirmYearSelection" type="button" class="btn btn-primary">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Products Modal -->
<div class="modal fade" id="prodModal" tabindex="-1" role="dialog"  data-backdrop="static" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="prodModal-label">Add Product</h4>
            </div>
            <div class="modal-body row">
                <from id="prod-form" class="col-md-12 form-horizontal">

                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">HCP ID</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" readonly id="hcpid"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Product Groups</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="productGroups" multiple>
                                <option value="">-- No Groups Selected --</option>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple groups</small>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4"><b>Item</b>&nbsp;
                            <sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please write the product name"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="item" maxlength="100"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4"><b>Item Nr/EAN Code</b>&nbsp;
                            <sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please write your internal item number or EAN code if available"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="ean" maxlength="50"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-3">Ingredients&nbsp;
                            <sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please click here to select all ingredients used from the dropdown list"></sup></label>
                        <div class='col-xs-12 col-md-9'>
                            <select class="form-control" id="ingredients" multiple
                                    title="Choose ingredients"></select>
                            <div class="help-block" style="margin-top: 6px; font-size: 11px; color: #888; line-height: 1.5;">
                                <i class="fa fa-lightbulb-o"></i>&nbsp;
                                <strong>Tip:</strong> Paste multiple ingredient names (one per line) into the search box to find and select them in bulk.
                                Matched items are auto-highlighted &mdash; click <strong>&rarr;</strong> to move them.
                                <span style="color: #d9534f;">Red lines</span> indicate items not found.
                            </div>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Specification&nbsp;
                            <sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload the product specification"></sup></label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button p-dropzone" id="dropzone1">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload1" foldertype="spec" infotype="product" type="file" name="files[]" multiple>
							</span><span class="loader"></span>
                            <ul id="ulspec"></ul>
                            <div class="alert-string"></div>
                        </div></div>

                        <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Additional Documents&nbsp;
                            <sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload any additional document like analysis"></sup></label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button p-dropzone" id="dropzone2">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload2" foldertype="add" infotype="product" type="file" name="files[]" multiple>
               			 </span><span class="loader"></span>
                            <ul id="uladd"></ul>
                            <div class="alert-string"></div>
                        </div></div>

                        <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Label&nbsp;
                            <sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload any label drafts"></sup></label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button p-dropzone" id="dropzone3">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload3" foldertype="label" infotype="product" type="file" name="files[]" multiple>
               			 </span><span class="loader"></span>
                            <ul id="ullabel"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                </from>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="PP.onSave();" >Save changes</button>
            </div>
            <div  id="ingred_drop"></div>
        </div>
    </div>
</div>

<!-- Bulk Assign Groups Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="bulkAssignModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="bulkAssignModal-label">Bulk Assign Groups</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><strong>Selected Products:</strong></label>
                    <div id="selectedProductsList" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
                        <!-- Selected products will be listed here -->
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="bulkGroups"><strong>Assign to Groups:</strong></label>
                    <select class="form-control" id="bulkGroups" multiple size="6">
                        <option value="">-- Loading Groups --</option>
                    </select>
                    <small class="text-muted">Hold Ctrl/Cmd to select multiple groups. This will REPLACE existing group assignments for selected products.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="performBulkAssign();" id="bulkAssignBtn">
                    <i class="ace-icon fa fa-tags"></i> Assign Groups
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" data-backdrop="static"  aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="infoModal-label">Information</h4>
            </div>
            <div class="modal-body row">
              <div class="col-xs-12"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>&nbsp;Creating Excel report...</div>
            </div>
        </div>
    </div>
</div>

<!-- page specific plugin scripts -->
<script src="js/bootstrap-datepicker.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/free-jqgrid/4.15.5/jquery.jqgrid.min.js"></script>

<script src="js/grid.locale-en.js"></script>

<!-- ace scripts -->
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>
<script src="js/select2.full.min.js"></script>
<script src="js/jquery.bootstrap-duallistbox.js"></script>
<script src="js/vendor/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>&_nounce=<?php echo rand(); ?>"></script>

<!-- Menu Toggle Script -->
<script>
    var userId = <?php echo $_SESSION['halal']['id'] ?>;
    Common.onDocumentReady();
    PP.onDocumentReady();

    // Initialize Bootstrap Dual Listbox for ingredients
    var ingredientsDualList = $('#ingredients').bootstrapDualListbox({
        nonSelectedListLabel: 'Available Ingredients',
        selectedListLabel: 'Selected Ingredients',
        preserveSelectionOnMove: 'moved',
        moveOnSelect: false,
        filterPlaceHolder: 'Search or paste ingredients (one per line)...',
        filterTextClear: 'Show all',
        selectorMinimalHeight: 250,
        showFilterInputs: true,
        infoText: 'Showing all {0}',
        infoTextFiltered: '<span class="label label-warning">Filtered</span> {0} from {1}',
        infoTextEmpty: 'Empty list'
    });

    // Helper to refresh the dual listbox after options are loaded via AJAX
    window.refreshIngredientsDualList = function() {
        $('#ingredients').bootstrapDualListbox('refresh');
    };
</script>

<!-- Groups Management Modal -->
<div class="modal fade" id="groupsModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="groupsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="groupsModal-label">Manage Product Groups</h4>
            </div>
            <div class="modal-body">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-xs-12">
                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddGroupModal();">
                            <i class="ace-icon fa fa-plus"></i> Add Group
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div id="groupsList">
                            <div class="text-center">
                                <i class="ace-icon fa fa-spinner fa-spin"></i> Loading groups...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Group Modal -->
<div class="modal fade" id="addGroupModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="addGroupModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="addGroupModal-label">Add Product Group</h4>
            </div>
            <div class="modal-body">
                <form id="groupForm" class="form-horizontal">
                    <input type="hidden" id="groupId"/>
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Group Name <span style="color: red;">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="groupName" required maxlength="100"/>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="groupDescription" rows="3" maxlength="500"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveGroup();">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
// Groups Management Functions
var currentGroupFilter = 'all';
var selectedProducts = [];

function openGroupsModal() {
    $("#groupsModal").modal("show");
    loadGroupsList();
}

function openAddGroupModal() {
    $("#addGroupModal-label").text("Add Product Group");
    $("#groupForm")[0].reset();
    $("#groupId").val("");
    $("#addGroupModal").modal("show");
}

function editGroup(groupId, groupName, groupDescription) {
    $("#addGroupModal-label").text("Edit Product Group");
    $("#groupId").val(groupId);
    $("#groupName").val(groupName);
    $("#groupDescription").val(groupDescription);
    $("#addGroupModal").modal("show");
}

// Bulk Assignment Functions
function updateSelectedProducts() {
    selectedProducts = jQuery("#prodGrid").jqGrid('getGridParam','selarrrow');
    updateBulkActionsBar();
}

function updateBulkActionsBar() {
    var count = selectedProducts.length;
    $("#selectedCount").text(count + " product" + (count !== 1 ? "s" : "") + " selected");
    
    if (count > 0) {
        $("#bulkActionsBar").show();
    } else {
        $("#bulkActionsBar").hide();
    }
}

function clearSelection() {
    jQuery("#prodGrid").jqGrid('resetSelection');
    selectedProducts = [];
    updateBulkActionsBar();
}

function openBulkAssignModal() {
    selectedProducts = jQuery("#prodGrid").jqGrid('getGridParam','selarrrow');

    if (selectedProducts.length === 0) {
        alert("Please select products first");
        return;
    }
    
    // Populate selected products list
    var productsList = '';
    selectedProducts.forEach(function(productId) {
        var productName = jQuery("#prodGrid").jqGrid('getCell', productId, 'Item');
        var hcpId = jQuery("#prodGrid").jqGrid('getCell', productId, 'hcpid');
        productsList += '<div><strong>' + htmlEscape(hcpId) + '</strong>: ' + htmlEscape(productName) + '</div>';
    });
    $("#selectedProductsList").html(productsList);
    
    // Load available groups
    loadBulkGroups();
    
    $("#bulkAssignModal").modal("show");
}

function loadBulkGroups() {
    var clientId = $("#prod-clientid").val();
    if (!clientId || clientId == "-1") {
        $("#bulkGroups").html('<option value="">-- No Client Selected --</option>');
        return;
    }
    
    $.post("ajax/ajax_groups.php", {
        action: 'getGroups',
        idclient: clientId
    }, function(response) {
        var html = '';
        
        if (response.success) {
            response.data.forEach(function(group) {
                html += '<option value="' + group.id + '">' + htmlEscape(group.name) + '</option>';
            });
        }
        
        if (html === '') {
            html = '<option value="">-- No Groups Available --</option>';
        }
        
        $("#bulkGroups").html(html);
    }, 'json').fail(function() {
        $("#bulkGroups").html('<option value="">-- Error Loading Groups --</option>');
    });
}

function performBulkAssign() {
    var selectedGroups = $("#bulkGroups").val() || [];
    var clientId = $("#prod-clientid").val();
    
    if (selectedProducts.length === 0) {
        alert("No products selected");
        return;
    }
    
    // Create confirmation message
    var groupNames = [];
    $("#bulkGroups option:selected").each(function() {
        groupNames.push($(this).text());
    });
    
    var confirmMessage = "Assign " + selectedProducts.length + " product" + (selectedProducts.length !== 1 ? "s" : "") + " to ";
    if (selectedGroups.length === 0) {
        confirmMessage += "NO GROUPS (remove from all groups)?";
    } else {
        confirmMessage += "the following group" + (selectedGroups.length !== 1 ? "s" : "") + ":\n\n";
        confirmMessage += "• " + groupNames.join("\n• ") + "\n\n";
        confirmMessage += "This will REPLACE existing group assignments.";
    }
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Disable the button and show loading
    $("#bulkAssignBtn").prop('disabled', true).html('<i class="ace-icon fa fa-spinner fa-spin"></i> Assigning...');
    
    // Perform bulk assignment
    var requests = selectedProducts.map(function(productId) {
        return $.post("ajax/ajax_groups.php", {
            action: 'saveProductGroups',
            product_id: productId,
            group_ids: selectedGroups,
            idclient: clientId
        });
    });
    
    Promise.all(requests).then(function(responses) {
        // Check if all requests succeeded
        var allSuccess = responses.every(function(data) {
            var response = typeof data === 'string' ? JSON.parse(data) : data;
            return response.success;
        });
        
        if (allSuccess) {
            alert("Successfully updated group assignments for " + selectedProducts.length + " product" + (selectedProducts.length !== 1 ? "s" : ""));
            $("#bulkAssignModal").modal("hide");
            clearSelection();
            jQuery("#prodGrid").jqGrid().trigger("reloadGrid");
        } else {
            alert("Some products failed to update. Please try again.");
        }
        
        $("#bulkAssignBtn").prop('disabled', false).html('<i class="ace-icon fa fa-tags"></i> Assign Groups');
        
    }).catch(function() {
        alert("Error updating product groups. Please try again.");
        $("#bulkAssignBtn").prop('disabled', false).html('<i class="ace-icon fa fa-tags"></i> Assign Groups');
    });
}

function loadGroupsList() {
    var clientId = $("#prod-clientid").val();
    if (!clientId || clientId == "-1") {
        $("#groupsList").html('<div class="alert alert-warning">Please select a client first</div>');
        return;
    }
    
    $.post("ajax/ajax_groups.php", {
        action: 'getGroups',
        idclient: clientId
    }, function(response) {
        if (response.success) {
            displayGroups(response.data);
            updateGroupTabs(response.data); // Add this line to update tabs
        } else {
            $("#groupsList").html('<div class="alert alert-danger">Error loading groups: ' + response.message + '</div>');
        }
    }, 'json').fail(function() {
        $("#groupsList").html('<div class="alert alert-danger">Failed to load groups</div>');
    });
}

function updateGroupTabs(groups) {
    var html = '';
    
    // Only show "All Products" tab if there are actual groups
    if (groups.length > 0) {
        html = '<li class="active"><a href="javascript:void(0)" data-group-id="all" onclick="filterByGroup(\'all\'); return false;">All Products</a></li>';
        
        groups.forEach(function(group) {
            html += '<li><a href="javascript:void(0)" data-group-id="' + group.id + '" onclick="filterByGroup(' + group.id + '); return false;">' + 
                    htmlEscape(group.name) + '</a></li>';
        });
    }
    // If no groups, show nothing (tabs will be hidden)
    
    $("#groupTabs").html(html);
    
    // Hide/show the entire tabs container based on whether there are groups
    if (groups.length > 0) {
        $(".group-tabs").show();
    } else {
        $(".group-tabs").hide();
    }
}

function filterByGroup(groupId) {
    // Prevent any default action
    if (typeof event !== 'undefined') {
        event.preventDefault();
    }
    
    currentGroupFilter = groupId;
    
    // Update active tab using data attributes for reliability
    $("#groupTabs li").removeClass("active");
    $("#groupTabs a[data-group-id='" + groupId + "']").parent().addClass("active");
    
    // Clear selection when filtering
    clearSelection();
    
    // Check if prodGrid exists and reload it with group filter
    if ($("#prodGrid").length > 0) {
        try {
            // Method 1: Try to reload existing grid with new postData
            $("#prodGrid").jqGrid('setGridParam', {
                postData: { group_filter: groupId }
            }).trigger('reloadGrid');
            console.log('Grid reloaded with group filter:', groupId);
        } catch (e) {
            console.log('Grid reload method 1 failed, trying alternative...');
            
            // Method 2: Try to reinitialize if PP object exists
            if (typeof PP !== 'undefined' && typeof PP.initGrid === 'function') {
                PP.currentGroupFilter = groupId;
                PP.initGrid();
                console.log('PP.initGrid called with group filter:', groupId);
            } else {
                console.log('PP object not found, group filter set to:', groupId);
            }
        }
    } else {
        console.log('prodGrid not found');
    }
    
    return false; // Extra safety to prevent navigation
}

function displayGroups(groups) {
    if (groups.length === 0) {
        $("#groupsList").html('<div class="alert alert-info">No groups found. Click "Add Group" to create your first group.</div>');
        return;
    }
    
    var html = '<div class="table-responsive"><table class="table table-striped table-bordered table-hover">';
    html += '<thead><tr><th>Group Name</th><th>Description</th><th style="width: 100px;">Actions</th></tr></thead>';
    html += '<tbody>';
    
    groups.forEach(function(group) {
        html += '<tr>';
        html += '<td><strong>' + htmlEscape(group.name) + '</strong></td>';
        html += '<td>' + htmlEscape(group.description || '') + '</td>';
        html += '<td width="125">';
        html += '<button class="btn btn-xs btn-info" onclick="editGroup(' + group.id + ', \'' + 
                htmlEscape(group.name).replace(/'/g, "\\'") + '\', \'' + 
                htmlEscape(group.description || '').replace(/'/g, "\\'") + '\')" title="Edit">';
        html += '<i class="ace-icon fa fa-edit"></i></button> ';
        html += '<button class="btn btn-xs btn-danger" onclick="deleteGroup(' + group.id + ', \'' + 
                htmlEscape(group.name).replace(/'/g, "\\'") + '\')" title="Delete">';
        html += '<i class="ace-icon fa fa-trash"></i></button>';
        html += '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table></div>';
    $("#groupsList").html(html);
}

function saveGroup() {
    var name = $("#groupName").val().trim();
    var description = $("#groupDescription").val().trim();
    var groupId = $("#groupId").val();
    var clientId = $("#prod-clientid").val();
    
    if (!name) {
        alert('Group name is required');
        $("#groupName").focus();
        return;
    }
    
    if (!clientId || clientId == "-1") {
        alert('Please select a client first');
        return;
    }
    
    var data = {
        action: 'saveGroup',
        id: groupId,
        name: name,
        description: description,
        idclient: clientId
    };
    
    $.post("ajax/ajax_groups.php", data, function(response) {
        if (response.success) {
            $("#addGroupModal").modal("hide");
            loadGroupsList(); // This will refresh both list and tabs
            loadGroupTabs(); // Refresh tabs to show/hide as needed
            alert(response.message);
        } else {
            alert('Error: ' + response.message);
        }
    }, 'json').fail(function() {
        alert('Failed to save group');
    });
}

function deleteGroup(groupId, groupName) {
    if (confirm("Are you sure you want to delete the group '" + groupName + "'?\n\nProducts in this group will be moved to 'No Group'.")) {
        var clientId = $("#prod-clientid").val();
        
        $.post("ajax/ajax_groups.php", {
            action: 'deleteGroup',
            id: groupId,
            idclient: clientId
        }, function(response) {
            if (response.success) {
                loadGroupsList(); // This will refresh both list and tabs
                loadGroupTabs(); // Refresh tabs - may hide them if no groups left
                alert(response.message);
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json').fail(function() {
            alert('Failed to delete group');
        });
    }
}

// Load tabs when client changes
$(document).ready(function() {
    $("#prod-clientid").change(function() {
        var clientId = $(this).val();
        if (clientId && clientId != "-1") {
            loadGroupTabs();
        } else {
            // Hide tabs when no client is selected
            $(".group-tabs").hide();
            $("#groupTabs").html('');
        }
        // Clear selection when client changes
        clearSelection();
    });
    
    // Load tabs on page load if client is already selected
    var initialClientId = $("#prod-clientid").val();
    if (initialClientId && initialClientId != "-1") {
        loadGroupTabs();
    }
    
    // Auto-populate group dropdown when product modal opens (for new products only)
    $('#prodModal').on('show.bs.modal', function () {
        // Only populate if it's a new product (no existing groups to set)
        if ($("#prod-form #hcpid").attr("data-new") == "1") {
            populateProductGroupDropdown([]);
        }
    });
    
    // Monitor grid selection changes for bulk actions
    // This will be called by your PP.js when the grid is initialized
    // You need to add multiselect: true to your grid configuration
});

function loadGroupTabs() {
    var clientId = $("#prod-clientid").val();
    if (!clientId || clientId == "-1") return;
    
    $.post("ajax/ajax_groups.php", {
        action: 'getGroups',
        idclient: clientId
    }, function(response) {
        if (response.success) {
            updateGroupTabs(response.data);
        }
    }, 'json');
}

// Product modal functions
function populateProductGroupDropdown(selectedGroupIds) {
    var clientId = $("#prod-clientid").val();
    if (!clientId || clientId == "-1") {
        $("#productGroups").html('<option value="">-- No Groups Available --</option>');
        return;
    }
    
    $.post("ajax/ajax_groups.php", {
        action: 'getGroups',
        idclient: clientId
    }, function(response) {
        var html = '';
        
        if (response.success) {
            response.data.forEach(function(group) {
                html += '<option value="' + group.id + '">' + htmlEscape(group.name) + '</option>';
            });
        }
        
        if (html === '') {
            html = '<option value="">-- No Groups Available --</option>';
        }
        
        $("#productGroups").html(html);
        
        // NEW: Set the selected values AFTER options are populated
        if (selectedGroupIds && selectedGroupIds.length > 0) {
            $("#productGroups").val(selectedGroupIds);
        }
    }, 'json');
}

function htmlEscape(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}
</script>

</body>
</html>