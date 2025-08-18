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
  $myuser = cuser::singleton();
  $myuser->getUserData();

  $products_preference = $myuser->userdata['products_preference'];
  $ingredients_preference = $myuser->userdata['ingredients_preference'];
  $qm_documents_preference = $myuser->userdata['qm_documents_preference'];
	
?>
<div class="main-container ace-save-state" id="main-container">
  <div class="main-content">
    <div class="main-content-inner">
      <div class="page-content">
        <div class="row no-gutters">
          <div class="col-md-12">
          <h1>Facility Data Sharing Preferences</h1>
              <div class="col-md-6 col-md-offset-3">
                <form id="settings-form" class="col-md-12 form-horizontal">

                 <p style="margin-top:25px;">Select whether to share or separate data across your facilities.</p>

                  <!-- Products Preference -->
                  <div class="form-group">
    <label for="products_preference" class="col-sm-4 control-label">Products:</label>
    <div class="col-sm-8">
        <div class="radio">
            <label>
                <input type="radio" name="products_preference" value="1" <?php echo ($products_preference == '1') ? 'checked' : ''; ?>>
                Share Across Facilities
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="products_preference" value="0" <?php echo ($products_preference == '0') ? 'checked' : ''; ?>>
                Separate for Each Facility
            </label>
        </div>
    </div>
</div>

<!-- Ingredients Preference -->
<div class="form-group">
    <label for="ingredients_preference" class="col-sm-4 control-label">Ingredients:</label>
    <div class="col-sm-8">
        <div class="radio">
            <label>
                <input type="radio" name="ingredients_preference" value="1" <?php echo ($ingredients_preference == '1') ? 'checked' : ''; ?>>
                Share Across Facilities
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="ingredients_preference" value="0" <?php echo ($ingredients_preference == '0') ? 'checked' : ''; ?>>
                Separate for Each Facility
            </label>
        </div>
    </div>
</div>

<!-- QM Documents Preference -->
<div class="form-group">
    <label for="qm_documents_preference" class="col-sm-4 control-label">QM Documents:</label>
    <div class="col-sm-8">
        <div class="radio">
            <label>
                <input type="radio" name="qm_documents_preference" value="1" <?php echo ($qm_documents_preference == '1') ? 'checked' : ''; ?>>
                Share Across Facilities
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio" name="qm_documents_preference" value="0" <?php echo ($qm_documents_preference == '0') ? 'checked' : ''; ?>>
                Separate for Each Facility
            </label>
        </div>
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
			url: "ajax/savePreferences.php",
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