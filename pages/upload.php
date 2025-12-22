<?php
include_once('config/config.php');
include_once('classes/users.php');
try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	
	$code = $_GET["code"];
	$isAppToken = false;
	$isOfferToken = false;

	$sql = "SELECT * FROM tusers WHERE app_token=:code";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':code', $code);
	$stmt->execute();
	$user = $stmt->fetch();
	
	if ($user) {
		$isAppToken = true;
	}
	else {
		$sql = 'SELECT * FROM tusers WHERE offer_token=:code';
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':code', $code);
		$stmt->execute();
		$user = $stmt->fetch();
	
		if ($user) {
			$isOfferToken = true;
		}
		else {
			die("Un-Authorized Access!!!");
		}
	}	
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}	

?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('pages/header.php');?>
<link rel='stylesheet' id='fileup-css'  href='css/fileup.css?ver=6.0.1' type='text/css' media='all' />
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<style>
#pdf-queue {
	/*
	position:absolute;
	bottom:15px;
	left:0px;
	*/
	width:100%;
	background-color:transparent;
}
.fileup-file {
	margin-top:10px !important;
	padding-top:0px !important;
}
#pdf-queue h3 {
	color:#fefefe;
	margin:10px auto !important;
}
.blockUI.blockMsg.blockPage {
	border: none !important; 
	padding: 15px !important;
	background-color: #000 !important; 
	-webkit-border-radius: 10px;
	-moz-border-radius': 10px; 
	opacity: .5; 
	color: #fff;
	text-align:center;
}
#fileup-pdf-0 { display: none;}
.fileup-file {
    background-color: transparent !important;
    border: none !important;
}
.fileup-preview,
.fileup-description,
.fileup-controls,
.fileup-result {
	display:none !important;
}
.fileup-file.fileup-doc .fileup-container {
    margin-left: 0px !important;
}
.fileup-progress-bar {
    height: 10px !important;
}
</style>
</head>
<body>
<input type="hidden" id="code" name="code" value="<?php echo $code;?>" />
<div class="main-container ace-save-state" id="main-container">
  <div class="main-content">
    <div class="main-content-inner">
      <div class="page-content">
        <div id="logo"></div>
        <div class="row">
          <div class="col-md-10 col-md-offset-1">
            <div class="clearfix">
              <div class="box">
                <div class="content-wrap">

                  <h3 style="text-align:center;margin-bottom:25px;font-size:34px;"><strong>Upload  Signed <?php echo $isAppToken ? 'Application' : 'Offer'; ?> Form</strong></h3>
                  <div id="mainForm">
                  <form id="admin-form" class="col-md-12 form-horizontal">
                    <div class="row form-group">
					<div id="errors" class="alert alert-danger hidden" style="margin-top:25px;font-size:20px; text-align:center;">
                       
					   </div>
	 
					   <div id="success" class="alert alert-success success hidden" style="margin-top:25px;font-size:20px; text-align:center;"><i class="fa fa-check-circle"></i> Thank you for sending us the signed <?php echo $isAppToken ? 'application' : 'offer'; ?> form. We will review your <?php echo $isAppToken ? 'application' : 'offer'; ?> and aim to respond within two business days. If you have any further questions or concerns, please feel free to contact us. </div>
 
						 <ul>
						   <li> Please use this form to send us your signed <?php echo $isAppToken ? 'application' : 'offer'; ?> form. </li>
						   <li> We kindly remind you to ensure that all sections of the form are completed accurately and fully, as any incomplete or inaccurate information may cause delays in processing your application. </li>
						 </ul>

                      <div id="pdf-dropzone" class="dropzone">

					  <i class="fa fa-upload" aria-hidden="true"></i>
                  <br/><br/>
                    Drag & Drop to Upload File 
                    <br/><br/>
                    OR
                    <br/>
                         
                        <div class="fileup-btn" style="min-width: 200px; height:65px !important; min-height:50px;"> Select PDF file
                          <input type="file" name="pdf" class="_3eHqh form-control" id="pdf" tabindex="-1" accept="application/pdf" style="font-size:13px !important;"/ >
                        </div>
                         
<div style="display:none;"><div id="pdf-queue"><h3>Uploading...</h3></div></div>
</div>
                      
                      </div>
                    </form>
                   </div>
                  <div class="text-center"> </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <?php if(file_exists( __DIR__ ."/../terms.txt")){$terms = file_get_contents( __DIR__ ."/../terms.txt"); echo $terms;} else echo "No Terms and Conditions file found!";?>
      </div>
      <div id="s_btn" class="modal-footer">
        <button class="btn" id="close_modal" data-dismiss="modal" aria-hidden="true">Close</button>
      </div>
    </div>
  </div>
</div>
<?php include_once('pages/footer.php');?>
</body>

<!-- page specific plugin scripts -->
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/jquery.jqGrid.min.js"></script>
<script src="js/grid.locale-en.js"></script>

<!-- ace scripts -->
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>
<script src="js/sha512.js"></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js?ver=1285677791' id='blockui-js'></script>
<script type='text/javascript' src='../js/fileup.js?ver=162459139' id='fileup-js'></script>
<script>
$(document).ready(function(e) {
	$.fileup({
		url: '/fileupload/upload.php',
		inputID: 'pdf',
		queueID: 'pdf-queue',
        dropzoneID: 'pdf-dropzone',
		extraFields: {code: $("#code").val()},		
		filesLimit: 1,
		sizeLimit: 1000000 * 25,
		autostart: 1,
		background: true,
		onSelect: function(file) {
		$("#errors").addClass("hidden")
		jQuery.blockUI({ css: { 
						
						
						},message: $('#pdf-queue') }); 
		},
		onRemove: function(total, file_number, file) {
			//$("#agent_photo_filename").val("");
		},
		onSuccess: function(response, file_number, file) {
			//$("#agent_photo_filename").val(response);
			$(".success").removeClass("hidden");
			//$("#mainForm").addClass("hidden");
			//$("#admin-form").hide();
			jQuery.unblockUI();
		},
		onError: function(event, file, file_number, response) {
			$("#success").addClass("hidden")
			$("#errors").removeClass("hidden").html('<i class="fa fa-info-circle"></i> '+response)
			jQuery.unblockUI();
		},		
	}).dragEnter(function(event) {
                $(event.target).addClass('over');
            })
            .dragLeave(function(event) {
                $(event.target).removeClass('over');
            })
            .dragEnd(function(event) {
                $(event.target).removeClass('over');
            });;
});
</script>
</html>