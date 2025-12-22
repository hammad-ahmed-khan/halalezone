<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel='stylesheet' id='fileup-css'  href='https://cdn.rawgit.com/shabuninil/fileup/master/src/fileup.min.css?ver=6.0.1' type='text/css' media='all' />    
    <title>Pre-Approved Ingredients - Halal e-Zone</title>
    <style>
        .blockUI h1 {
    font-size: 18px;
    margin: 10px auto;
}
        td.changed {
            background:greenyellow;
        }
    </style>
</head>
<body>
<?php include_once('pages/navigation.php');

try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
	$query = "
        SELECT * FROM tproducers WHERE active=1 ORDER BY name";
        
		$stmt = $dbo->prepare($query);
		$stmt->execute();
	$producers = $stmt->fetchAll(PDO::FETCH_ASSOC);  
	
  ?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">	  
                   <div style="margin:20px 0 10px;">
                   <div class="row">
                   <div class="col-md-6"><h3 style="font-weight:bold;">Pre-Approved Ingredients</h3></div>
                      <div class="col-md-6 text-right">
                      <!--<button class="btn btn-primary btn-add">Add Ingredients</button>-->
                      <button class="btn btn-danger btn-delete-pa">Delete Ingredients</button>
                      <button class="btn btn-success btn-import">Import Ingredients</button>
                      </div>                    
                    </div>
                    <!--
                     <div class="row" style="margin-top:5px;">
                      <div class="col-md-4">
                        <select class="form-control" name="sproducer_id" id="sproducer_id">
                          <option value="">Select Producer</option>
                          <?php foreach ($producers as $producer): ?>
                            <option value="<?php echo $producer["id"]; ?>"><?php echo $producer["name"]; ?></option>  
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-4"><input type="text" class="form-control" name="srmcode" id="srmcode" placeholder="RM Code"/></div>
                      <div class="col-md-4"><input type="text" class="form-control" name="sname" id="sname" placeholder="Name"/></div>                    
                    </div>
                          -->                    
                   </div>
                   <div class="row" style="margin-bottom:15px;"> 
                      <div class="col-md-4">
                        <select class="form-control" name="sproducer_id" id="sproducer_id">
                          <option value="">Select Producer</option>
                          <?php foreach ($producers as $producer): ?>
                            <option value="<?php echo $producer["id"]; ?>"><?php echo $producer["name"]; ?></option>  
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-4"><input type="text" class="form-control" name="srmcode" id="srmcode" placeholder="RM Code"/></div>
                      <div class="col-md-4"><input type="text" class="form-control" name="sname" id="sname" placeholder="Name"/></div>                    
                    </div>   

                    <table id="table_tank" class="table table-hover table-striped table-bordered">
                      <thead>
                        <tr class="tableheader">
                        <th id="sproducer">Producer</th>
                        <th id="srmcode">RM Code</th>
                        <th id="srmname">RM Name</th>
                        <th id="shalalcert">Halal Certification Body</th>
                        <th id="sexpdate">Cert. Exp. Date</th>
                        <th id="srmposition">RM Position</th>
                        <th style="width:25px;" class="no-sort"><input type="checkbox" name="checkall" id="checkall" value="1" /></th>
                        </tr>
                      </thead>
                      <tbody>
                      </tbody>
                    </table>
	</div>
  </div>
</div>
<div id="modaltank" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Add Ingredient</h4>
        <h4 class="modal-title title-edit" style="display:none;">Edit Ingredient</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors" style="display:none;"></div>
        <form id="frmTank" data-toggle="validator">
          <input type="hidden" name="id" id="id" value="" />
          <div class="form-horizontal row"> 
             <div class="col-md-12">
              <div class="row form-group">
                <label class="col-sm-4 control-label">Select Producer <span>*</span></label>
                <div class="col-sm-8">
                  <select name="producer_id" id="producer_id" class="form-control">
                  <option value="">Select Producer</option>
                          <?php foreach ($producers as $producer): ?>
                            <option value="<?php echo $producer["id"]; ?>"><?php echo $producer["name"]; ?></option>  
                          <?php endforeach; ?>
                </select>
                </div>
              </div>
              <div class="row form-group">
                <label class="col-sm-4 control-label">RM Code <span>*</span></label>
                <div class="col-sm-8">
                <input type="text" class="form-control" name="rmcode" id="rmcode" value="" required />
                </div>
              </div>
               <div class="row form-group">
                <label class="col-sm-4 control-label">Name <span>*</span></label>
                <div class="col-sm-8">
                <input type="text" class="form-control" name="name" id="name" value="" required />
                </div>
              </div>
             <div class="row form-group">
                <label class="col-sm-4 control-label">Halal Certification Body <span>*</span></label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="cb" id="cb" vlue="" required />
                </div>
              </div>
              <div class="row form-group">
                <label class="col-sm-4 control-label"> Certificate Expiry Date	 <span>*</span></label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="halalexp" id="halalexp"  value="" required />
                </div>
              </div>
              <div class="row form-group">
                <label class="col-sm-4 control-label">RM Position <span>*</span></label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="rmposition" id="rmposition" value="" required />
                </div>
              </div>
              <div class="row form-group">
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary pull-right" id="btnsave-tank"><i class="fa fa-save"></i> Save</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>  
<div id="modalapp" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Import Ingredients</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors"></div>
        <form id="frmApplication" data-toggle="validator">
          <input type="hidden" name="ID" id="ID" value="" />
          <div class="form-horizontal row">
            <div class="col-md-12">
              <div class="row form-group text-left">
                <div style="padding:0 15px;">
                  <div id="app-pdf-dropzone" class="dropzone" style="font-size:18px; padding:50px 0;">
                    <div class="fileup-btn fileup-btn-small" style="background-color: #008000;"><i class="fas fa-file-csv"></i> Select CSV file
                      <input type="file" name="app-pdf" class="_3eHqh form-control" id="app-pdf" tabindex="-1" accept="text/csv"/>
                    </div>
                    <br>
                    or  Drop file here </div>
                  <div id="app-pdf-queue"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="row" style="display: none;">
            <div class="col-sm-12">
              <button type="button" class="btn btn-primary pull-right" id="btn-submit" tabindex="13"><i class="fa fa-save"></i> Submit</button>
            </div>
          </div>
        </form>
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
<script src="js/select2.full.min.js"></script>
<script src="js/vendor/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script> 
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script> 
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js?ver=1285677791' id='blockui-js'></script>
<script type='text/javascript' src='../js/fileup.js?ver=162459439' id='fileup-js'></script> 
<script src="pages/pa-ingreds/script.js"></script>
<script>
$(document).ready(function(){

  $('#checkall').click(function() {
            var checked = $(this).prop('checked');
            $('#table_tank').find('input:checkbox').prop('checked', checked);
        });

  $(document).on("click", ".btn-delete-pa", function () {
            if ($("#table_tank tbody input:checked").length == 0) {
                alert("Please select at least one ingredient.");
                return false;
            }
            if (!confirm('Are you sure you want to delete?')) {
              return;
            }
            var doc = {};
            doc.idclient = $("#ingred-clientid").val();
            doc.ids = $('#table_tank tbody input:checked').map(function() {return this.value;}).get().join(',');

            $.post("ajax/ajaxHandler.php", {
            rtype: "deletePAIngredient",
            uid: 0,
            data: doc,
            }).done(function (data) {
              var table = $("#table_tank").DataTable();
              table.ajax.reload(null, false);
            });
            return false;
        });
});
</script>
