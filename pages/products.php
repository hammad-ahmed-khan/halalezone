<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php'); ?>
    <title>Products - Halal e-Zone</title>
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
                    <div class="col-xs-6">
                        <?php 
                         $db = acsessDb :: singleton();
                         $dbo =  $db->connect(); // Создаем объект подключения к БД
                         
                         $myuser = cuser::singleton();
                         $myuser->getUserData();
                      
                         $parent_id = $myuser->userdata['id'];
                         $isClient = $myuser->userdata['isclient'] == "1" ? true : false;
                         $isAuditor = $myuser->userdata['isclient'] == '2' ? true : false;
                         $isAdmin = !$isclient && !isAuditor;
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
                            $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND IFNULL(parent_id, '0') = '0' AND deleted = 0  ORDER BY name";
                           }  
                           
                           $clients = [];
                           $stmt = $dbo->prepare($sql);
                           $stmt->setFetchMode(PDO::FETCH_ASSOC);
                           if ($stmt->execute()) { 
                             $clients = $stmt->fetchAll();
                           }
                           
                            // Fetch all child clients and organize them in an array by parent_id
                            $sql = "SELECT id, name, prefix, parent_id FROM tusers WHERE isclient = 1 AND IFNULL(parent_id,'0') <> '0' AND deleted = 0 ORDER BY name";

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
                            <label><?php if ($isClient): ?> Facilities <?php else: ?> Clients <?php endif; ?> &nbsp;&nbsp;
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
                            </label>
                        </div>
                        </div>
                        <?php endif;?> 
                    </div>
                    <div class="col-xs-6">
                        <label class="right">
                            <input id="filter-conformed" class="ace ace-switch ace-switch-4" type="checkbox">
                            <span class="lbl">&nbsp;&nbsp;Show only non-conformed products</span>
                        </label>
                    </div>
                    <div class="col-xs-12">
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
    <div class="modal-dialog" role="document">
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
                        <label class="col-xs-12 col-md-4">Ingredients&nbsp;
                            <sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please click here to select all ingredients used from the dropdown list"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control selectpicker" id="ingredients" multiple
                                    title="Choose ingredients"></select>

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
            <div  id="ingred_drop"></div>
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
<script src="js/vendor/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>&_nounce=<?php echo rand(); ?>"></script>

<!-- Menu Toggle Script -->
<script>
    var userId = <?php echo $_SESSION['halal']['id'] ?>;
    Common.onDocumentReady();
    PP.onDocumentReady();
</script>

</body>
</html>
