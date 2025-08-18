<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <title>Administration - Halal e-Zone</title>
    <style>
    .rel {
		display:none;
	}
.chosen-container {
    min-width:100%;
}	
    </style>
</head>

<body>
<?php
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	$sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 ORDER BY name";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	if(!$stmt->execute()) {
		echo json_encode(generateErrorResponse("Getting clients list failed"));
		die();
	}
	$clients = $stmt->fetchAll();
?>
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">
                        <!-- PAGE CONTENT BEGINS -->
                        <table id="companyGrid"></table>
                        <div id="companyPager"></div>
                        <!-- PAGE CONTENT ENDS -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
</div><!-- /.main-container -->
<!-- Admin Modal -->
<div class="modal fade" id="companyModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="prodModal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="companyModal-label">Add Company</h4>
            </div>
            <div class="modal-body row">
                <form id="company-form" class="col-md-12 form-horizontal">
                    <input type="text" hidden id="companyid"/>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Company Name</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="name" maxlength="50"/>
                            <div class="alert-string"></div>
                        </div></div>
                   
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Active</label>
                        <div class='col-xs-12 col-md-8'>
                               <div class="col-sm-8">
                          <label class="radio-inline">
                              <input type="radio" name="active" id="active1" value="1" checked>Yes
                            </label>
                            <label class="radio-inline">
                              <input type="radio" name="active" id="active0" value="0">No
                            </label>
                            
                            <div class="alert-string"></div>
                        </div></div>
                    </div>
                  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="CP.onSave();" >Save changes</button>
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
<script src="js/sha512.js"></script>
<script src="js/chosen/chosen.jquery.min.js"></script>
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script>

<!-- Menu Toggle Script -->
<script>
    var userId = <?php echo $_SESSION['halal']['id'] ?>;
    Common.onDocumentReady();
    CP.onDocumentReady();
	var chosenLoaded = false;
	$(document).ready(function(e) {
        $('input[type=radio]').click(function(e) {
            var rel = $(this).attr('id');
			if (rel == 'isclient2' && !chosenLoaded) {
				//alert('tes');
				//$('.chosen-select').chosen('destroy').chosen();
				chosenLoaded = true;
			}			
			$('.rel').hide();
			$('div[rel*='+rel+']').show();
        });
    });
</script>

</body>
</html>