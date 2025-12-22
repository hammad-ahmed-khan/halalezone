<!DOCTYPE html>
<html lang="en">
<head>
<?php include_once('pages/header.php');
 include_once ('includes/func.php');?>
<title>Administration - Halal e-Zone</title>
</head>

<body>
<?php include_once('pages/navigation.php');?>
<?php
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
	$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	$sql = 'SELECT name, value '.
		'from tsettings where 1 = 1 order by name';
	$stmt = $dbo->prepare($sql);
	$stmt->execute();
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$rows = $stmt->fetchAll();

	$settings = array();
	foreach ($rows as $row) {
		$settings[$row['name']] = $row['value']; 
	}
?>
<div class="main-container ace-save-state" id="main-container">
  <div class="main-content">
    <div class="main-content-inner">
      <div class="page-content">
        <div class="row no-gutters">
          <div class="col-md-12">
              <h1>Settings</h1>
              <div class="col-md-6 col-md-offset-3">
                <form id="settings-form" class="col-md-12 form-horizontal">
                  <div class="row form-group">
                    <label class="col-xs-12 col-md-4"><strong>Forbidden Words</strong>&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Enter forbidden words separated by comma. Raw materials and products containing forbidden words will not be possible when entering the name."></sup></label>
                    <div class='col-xs-12 col-md-8'>
                      <textarea type="text" class="form-control" name="bannedwords" id="bannedwords" style="height:200px;"><?php echo $settings['bannedwords']; ?></textarea>
                      <div class="alert-string"></div>
                    </div>
                  </div>
                  <div class="text-right">
                    <button type="button" class="btn btn-primary" id="btn-save" >Save changes</button>
                  </div>
                </form>
             </div>
          </div>
        </div>
        <!-- PAGE CONTENT ENDS --> 
      </div>
      <!-- /.col --> 
    </div>
    <!-- /.row --> 
  </div>
  <!-- /.page-content --> 
</div>
</div>
<!-- /.main-content -->
</div>
<!-- /.main-container --> 
<!-- Admin Modal -->

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
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script> 

<!-- Menu Toggle Script --> 
<script>
    var userId = <?php echo $_SESSION['halal']['id'] ?>;
  	$(document).ready(function(e) {
		$('[data-toggle="tooltip"]').tooltip();
		$("#btn-save").click(function () {
		  $.ajax({
			type: "POST",
			url: "ajax/saveSettings.php",
			cache: false,
			data: $("#settings-form").serialize(),
			success: function (data) {
			  var response = JSON.parse(data);
			  alert(response.message);
			},
		  });
       });
	});
</script>
</body>
</html>