<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <title>QM Documents - Halal e-Zone</title>
</head>

<body>
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">
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
                            $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND IFNULL(parent_id,'0') = '0' AND deleted = 0  ORDER BY name";
                          }  
                          
                          $clients = [];
                          $stmt = $dbo->prepare($sql);
                          $stmt->setFetchMode(PDO::FETCH_ASSOC);
                          if ($stmt->execute()) { 
                            $clients = $stmt->fetchAll();
                          }
                          
                          
                          if ($isClient && count($clients) > 1) {
                            $hasFacilities = true;
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

                          if ( $myuser->userdata['qm_documents_preference'] == '1') {
                               $hasFacilities = false;
                          }
                        ?>
                        <?php if ($isClient && !$hasFacilities): ?>              
                            <input type="hidden" id="qm-clientid" data-email="<?php echo $myuser->userdata['email']; ?>" value=<?php echo $_SESSION['halal']['id']; ?> data-clientname="<?php echo $myuser->userdata['name']," (",$myuser->userdata['prefix'],$myuser->userdata['id'],")"; ?>"/>
                        <?php endif;?>

                        <?php if (!$isClient || $hasFacilities): ?>
                        <div class="form-inline">
                        <div class="form-group">
                            <label><?php if ($isClient): ?> Facilities <?php else: ?> Clients <?php endif; ?> &nbsp;&nbsp;
                            <select class="form-control clientslist" id="qm-clientid">
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

                    <div class="row gutters" >
                        <div class="col-md-12">
                        <div style="display:flex; align-items:center; justify-content:space-between; width:100%; padding:20px 20px 0">
                    
                    <span class="alert alert-warning">The page displays only QM documents uploaded in the <strong>current year</strong>. To view documents uploaded in previous years, please click the toggle button on the right.</span>

                    <label class="right"  style="margin-top: 10px; margin-bottom:10px;">
                        <input id="filter-prevyears" class="ace ace-switch ace-switch-4" type="checkbox">
                        <span class="lbl" style="font-weight:bold;">&nbsp;&nbsp;Show documents from previous years</span>
                    </label>                    
                            </div>
                            </div>
                </div>
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <table id="qmGrid"></table>
                        <div id="qmPager"></div>

                        <!-- PAGE CONTENT ENDS -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
</div><!-- /.main-container -->
<!-- QM Modal -->
<div class="modal fade" id="qmModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="prodModal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="qmModal-label">Add Documents</h4>
            </div>
            <div class="modal-body row">
                <from id="qm-form" class="col-md-12 form-horizontal">
                    <input type="text" hidden id="qmid"/>
                    <div class="row form-group">
                        <label class="col-xs-4"><b>Year</b></label>
                        <div class='col-xs-8'>
                            <input type="text" class="form-control datepicker" id="dt"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Halal policy&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your valid signed Halal policy"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone1">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload1" type="file" foldertype="policy" subfolder="Halal policy" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulpolicy"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Halal HACCP&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your Halal risk analysis Halal HACCP"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone2">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload2" type="file" foldertype="haccp" subfolder="Halal HACCP" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulhaccp"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Management team&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload the list of organigram with the name of your Halal team members"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone3">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload3" type="file" foldertype="team" subfolder="Management team" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulteam"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Training&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your Halal training schedule and list of participants"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone4">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload4" type="file" foldertype="training" subfolder="Training" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ultraining"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Purchasing of Halal ingredients&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your work instructions for purchasing of Halal ingredients"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone5">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload5" type="file" foldertype="purchasing" subfolder="Purchasing of Halal ingredients" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulpurchasing"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Cleaning plan for Halal&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your cleaning plan"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone6">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload6" type="file" foldertype="cleaning" subfolder="Cleaning plan for Halal" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulcleaning"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Production plan for Halal&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your production plan for Halal products"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone7">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload7" type="file" foldertype="production" subfolder="Production plan for Halal" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulproduction"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Handling of non-conforming products&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your instructions for handling of non-conformed products"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone8">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload8" type="file" foldertype="handling" subfolder="Handling of non-conforming" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulhandling"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Storage of Halal Products&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your instructions for storage of Halal products"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone9">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload9" type="file" foldertype="storage" subfolder="Storage of Halal products" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulstorage"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Traceability&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your quality documents regarding traceability"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone10">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload10" type="file" foldertype="traceability" subfolder="Traceability" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ultraceability"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Internal Audits&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your internal audit schedule and audit reports for Halal internal audits"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone11">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload11" type="file" foldertype="audit" subfolder="Internal audit" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulaudit"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Laboranalysis&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your laboranalysis if any available"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone12">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload12" type="file" foldertype="analysis" subfolder="Laboranalysis" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulanalysis"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Additional Documents</label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone13">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload13" type="file" foldertype="addoc" subfolder="Additional documents" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="uladdoc"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Flow Chart&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your flow chart"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
                            <span class="fileinput-button" id="dropzone14">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload14" type="file" foldertype="flowchart" subfolder="Flow Chart" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulflowchart"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Quality Certificate&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload your quality certificate if any available"</sup></label>
                        <div class='col-xs-8'><!-- The fileinput-button span is used to style the file input field as button -->
                            <span class="fileinput-button" id="dropzone15">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload15" type="file" foldertype="qcertificate" subfolder="Quality Certificate" infotype="qm" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="ulqcertificate"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-4">Note</label>
                        <div class='col-xs-8'>
                            <textarea class="form-control input" id="note" rows="3" maxlength="500"></textarea>
                            <div class="alert-string"></div>
                        </div></div>
                </from>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="QP.onSave();" >Save changes</button>
            </div>
        </div>
    </div>
</div>
<?php include_once('pages/footer.php');?>
<!-- page specific plugin scripts -->
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/jquery.jqGrid.min.js"></script>
<script src="js/grid.locale-en.js"></script>
<!-- ace scripts -->
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>
<script src="js/vendor/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script>

<!-- Menu Toggle Script -->
<script>
    var userId = <?php echo $_SESSION['halal']['id'] ?>;
    Common.onDocumentReady();
    QP.onDocumentReady();
</script>

</body>
</html>