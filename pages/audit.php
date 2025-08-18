<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <title>Audit - Halal e-Zone</title>
</head>
<?php
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД

	$sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 ORDER BY name";
	$res = $dbo->prepare($sql);
	if(!$res->execute()) die($sql);
	$clients = $res->fetchALL(PDO::FETCH_ASSOC);
?>

<body>
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <table id="auditGrid"></table>
                        <div id="auditPager"></div>
                        <!-- PAGE CONTENT ENDS -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
</div><!-- /.main-container -->
<!-- Audit Modal -->
<div class="modal fade" id="auditModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="prodModal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="auditModal-label">Add Documents</h4>
            </div>
            <div class="modal-body row">
            	
                  <ul class="nav nav-tabs nav-justified-">
                    <li class="active"><a class="tab1" data-toggle="tab" href="#tab1">Auditor Info</a></li>
                    <li><a class="tab2" data-toggle="tab" href="#tab2">Account Info</a></li>
                  </ul>
                  <div class="tab-content clearfix">
                    <div id="tab1" class="tab-pane fade in active">

                        <form id="audit-form" class="col-md-12 form-horizontal">
                    <input type="text" hidden id="auditid"/>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Audit Nr</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="auditnr" maxlength="20"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Auditor ID</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="auditorid" maxlength="20"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Auditor Name</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="auditorname" maxlength="100"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Auditee Name</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="auditeename" maxlength="100"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Order</label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
                        <span class="fileinput-button" id="dropzone1">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload1"  type="file" foldertype="order" subfolder="Order" infotype="audit" name="files[]" multiple>
               			 </span><span class="loader"></span>
                            <ul id="ulorder"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Plan</label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone2">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload2"  type="file" foldertype="plan" subfolder="Plan" infotype="audit" name="files[]" multiple>
               			 </span><span class="loader"></span>
                            <ul id="ulplan"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Report</label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone3">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload3"  type="file" foldertype="report" subfolder="Report" infotype="audit" name="files[]" multiple>
               			 </span><span class="loader"></span>
                            <ul id="ulreport"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Certificate</label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone4">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload4"  type="file" foldertype="certificate" subfolder="Certificate" infotype="audit" name="files[]" multiple>
               			 </span><span class="loader"></span>
                            <ul id="ulcertificate"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">GTC</label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
        		        <span class="fileinput-button" id="dropzone5">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload5"  type="file" foldertype="gtc" subfolder="GTC" infotype="audit" name="files[]" multiple>
               			 </span><span class="loader"></span>
                            <ul id="ulgtc"></ul>
                            <div class="alert-string"></div>
                        </div></div>
</form>                       </div>
                    <div id="tab2" class="tab-pane fade in">

                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Login</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="login" maxlength="20"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Password</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="pass" maxlength="10"/>
                            <div class="alert-string"></div>
                        </div></div>
						<hr/>
                        <h5>Permissions</h5><br/>
                        
                        <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Source of Raw Material</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control chosen-select" multiple>
                                <option role="option" value="">Not specified</option>
                                <option role="option" value="Animal">Animal</option>
                                <option role="option" value="Plant">Plant</option>
                                <option role="option" value="Synthetic">Synthetic</option>
                                <option role="option" value="Mineral">Mineral</option>
                                <option role="option" value="Cleaning agents">Cleaning agents</option>
                                <option role="option" value="Other agents">Other agents</option>
                            </select>
                            <div class="alert-string"></div>
                        </div></div>
                                            <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Clients</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control chosen-select" multiple>
                            	<?php foreach ($clients as $client): ?>
                                	<option value="<?php echo $client["id"]; ?>"><?php echo $client["name"];?> - <?php echo $client["id"]; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="alert-string"></div>
                        </div></div>


                    </div>

                        </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="AP.onSave();" >Save changes</button>
            </div>
        </div>
    </div>
</div>
<?php include_once('pages/footer.php');?>
<script src="js/jquery-2.1.4.min.js"></script>

<!-- <![endif]-->

<!--[if IE]>
<script src="js/jquery-1.11.3.min.js"></script>
<![endif]-->
<script src="js/bootstrap.min.js"></script>
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
<script src="js/chosen/chosen.jquery.min.js"></script>
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script>

<!-- Menu Toggle Script -->
<script>
    var userId = <?php echo $_SESSION['halal']['id'] ?>;
    Common.onDocumentReady();
    AP.onDocumentReady();
</script>

</body>
</html>