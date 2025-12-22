<?php $page_title = "Tanks"; ?>
<?php require "../config.php"; ?>
<?php require "../init.php"; ?>
<?php
$query = "SELECT * FROM Tank as t ORDER BY t.MPRN";
$stmt = $pdo->prepare($query);
$stmt->execute();
$tanks = $stmt->fetchAll(PDO::FETCH_ASSOC);  
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include '../includes/head.php'; ?>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" />
<style>
#bmrow, #btrow { display:none; }
</style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="clearfix">
	<div class="pull-left">
	  <h3 style="margin:0px;">Tanks</h3>
	</div>
	<div class="pull-right">
	  <button type="button" class="btn btn-default" id="btnadd-tank" name="btnadd-tank"><i class="fa fa-plus"></i> Add Tank</button>
	</div>
</div>

<hr style="margin:10px 0 20px;"/>

  <div class="well">
	<!--<h4 style="margin-top: 0">Filter</h4>-->
	<div class="row">
	<div class="col-md-6">
<div class="input-group my-group"> 
    <form method="get">
    <select id="in" class="selectpicker form-control input-group-addon"  style="width:150px;" data-live-search="true" title="">
        <option value="">Search In</option>
        <option value="FuelID">Fuel</option>
        <option value="TankID">Tank ID</option>
        <option value="CRN">CRN</option>
        <option value="MPRN">MPRN</option>
        <option value="Type">Type</option>
        <option value="Status">Status</option>
        <option value="AnomaliesStatus">Anomalies Status</option>
    </select> 
    <input type="text" class="form-control" name="s" id="s"  style="width:300px;" placeholder="Search..."/>
    <select class="form-control" name="s" id="f" style="width:300px;display:none;" disabled>
       <option value=""></option>
       <option value="1">Kero</option>
       <option value="2">GasOil</option>
    </select>
    <select class="form-control" name="s" id="ss" style="width:300px;display:none;" disabled>
       <option value=""></option>
       <option value="Sensor Installed">Sensor Installed</option>
       <option value="First Fill">First Fill</option>
       <option value="Billed">Billed</option>
       <option value="Cancelled">Cancelled</option>
       <option value="Sensor Removed">Sensor Removed</option>
    </select>
    <select class="form-control" name="s" id="st" style="width:300px;display:none;" disabled>
       <option value=""></option>
       <option value="Customer">Customer</option>
       <option value="Staff">Staff</option>
    </select>
    <select class="form-control" name="s" id="sa" style="width:300px;display:none;" disabled>
       <option value=""></option>
       <option value="Good">Good</option>
       <option value="Under Review">Under Review</option>
    </select>
    <span class="input-group-btn pull-left">
        <button class="btn btn-primary my-group-button btn-filter" type="submit"><span class="glyphicon glyphicon-search"></span> </button>
    </span>
    </form>
   </div>
  </div>
  <div class="col-md-6">
 </div>
 </div>
</div>
<div class="panels panel-defaults" > 
  <div class="panel-bosdy" style="paddings:5px;">
	<div class="table-responsive">
	  <table id="table_tank" class="table table-hover table-striped table-bordered">
		<thead>
		  <tr class="tableheader">
			<th>Fuel</th>
			<th>Tank ID</th>
			<th>CRN</th>
			<th>MPRN</th>
			<th>Type</th>
			<th>Installed</th>
			<th>Comms</th>
			<th>Status</th>
			<th>Fill Restriction Status</th>
			<th>Number of <br/>Fills</th>
			<th>Number of <br/>Factors</th>
			<th>Number of <br/>Bad Readings</th>
         		<th>AnomaliesStatus</th>
			<th>Billing Mode</th>
			<th>Billing Target</th>
			<th  style="width:95px;" class="no-sort"></th>
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
        <h4 class="modal-title title-add">Add Tank</h4>
        <h4 class="modal-title title-edit" style="display:none;">Edit Tank</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors"></div>
        <form id="frmTank" data-toggle="validator">
          <input type="hidden" name="ID" id="ID" value="" />
          <input type="hidden" id="billing_mode_ui_add_visibility" value="<?php echo ($billing_mode['ui']['add']['visibility'] ? 1 : 0); ?>" />
          <input type="hidden" id="billing_mode_ui_edit_visibility" value="<?php echo ($billing_mode['ui']['edit']['visibility'] ? 1 : 0); ?>" />
          <input type="hidden" id="billing_target_ui_add_visibility" value="<?php echo ($billing_target['ui']['add']['visibility'] ? 1 : 0); ?>" />
          <input type="hidden" id="billing_target_ui_edit_visibility" value="<?php echo ($billing_target['ui']['edit']['visibility'] ? 1 : 0); ?>" />
          <input type="hidden" id="billing_mode_ui_default" value="<?php echo $billing_mode['ui']['default']; ?>" />
          <input type="hidden" id="billing_target_ui_default" value="<?php echo $billing_target['ui']['default']; ?>" />
          <input type="hidden" id="tank_status_ui_default" value="<?php echo $tank_status ['ui'] ['default']; ?>" />
          <div class="form-horizontal row"> 
             <div class="col-md-12">
              <div class="row form-group">
                <label class="col-sm-3 control-label">Choose Fuel <span>*</span></label>
                <div class="col-sm-9">
                  <select name="FuelID" id="FuelID" class="form-control">
                  <option value="1">Kero</option>
                  <option value="2">GasOil</option>
                  </select>
                </div>
              </div>
              <div class="row form-group">
                <label class="col-sm-3 control-label">Install Date <span>*</span></label>
                <div class="col-sm-9">
                  <div class="input-group date" id="dtpickerdemo">
                     <input type="text" class="form-control" name="InstallDate" id="InstallDate" value="" required />
                     <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                     </span>
                   </div>
                </div>
              </div>
               <div class="row form-group">
                <label class="col-sm-3 control-label">Sensor Comms <span>*</span></label>
                <div class="col-sm-9">
                    <select class="form-control" name="SensorComms" id="SensorComms" required>
                       <option value=""></option>
                       <option value="2G">2G</option>
                       <option value="NBiOT">NBiOT</option>
                       <option value="Sigfox">Sigfox</option>
                       <option value="2G-NBiOT">2G-NBiOT</option>
                    </select>
                </div>
              </div>
             <div class="row form-group">
                <label class="col-sm-3 control-label">Tank ID <span>*</span></label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="TankID" id="TankID" maxlength="10" value="" required />
                </div>
              </div>
              <div class="row form-group">
                <label class="col-sm-3 control-label">CRN <span>*</span></label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="CRN" id="CRN" maxlength="10" value="" required />
                </div>
              </div>
              <div class="row form-group">
                <label class="col-sm-3 control-label">MPRN <span>*</span></label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="MPRN" id="MPRN" maxlength="11" value="" required />
                </div>
              </div>
              <div class="row form-group">
                <label class="col-sm-3 control-label">Status <span>*</span></label>
                <div class="col-sm-9">
                    <select class="form-control" name="Status" id="Status" required>
                       <option value=""></option>
                       <option value="Sensor Installed">Sensor Installed</option>
                       <option value="First Fill">First Fill</option>
                       <option value="Billed">Billed</option>
                       <option value="Cancelled">Cancelled</option>
                       <option value="Sensor Removed">Sensor Removed</option>
                    </select>
                </div>
              </div>
              <div class="row form-group">
                <label class="col-sm-3 control-label">Type</label>
                <div class="col-sm-9">
                  <label class="radio-inline">
                      <input type="radio" name="Type" id="Type1" value="Customer" checked>Customer
                    </label>
                    <label class="radio-inline">
                      <input type="radio" name="Type" id="Type2" value="Staff">Staff
                    </label>
                </div>
              </div>
              <div class="row form-group canadd canedit" id="bmrow">
                <label class="col-sm-3 control-label">Billing Mode</label>
                <div class="col-sm-9">
                  <?php foreach ($billing_modes as $i=>$v): ?>
                  <label class="radio-inline">
                      <input type="radio" name="BillingMode" id="BillingMode<?php echo $i; ?>" value="<?php echo $v; ?>"><?php echo $v; ?>
                    </label>
                   <?php endforeach; ?>
                </div>
              </div>
              <div class="row form-group canadd canedit" id="btrow">
                <label class="col-sm-3 control-label">Billing Target</label>
                <div class="col-sm-9">
                  <?php foreach ($billing_targets as $i=>$v): ?>
                  <label class="radio-inline">
                      <input type="radio" name="BillingTarget" id="BillingTarget<?php echo $i; ?>" value="<?php echo $v; ?>"><?php echo $v; ?>
                    </label>
                   <?php endforeach; ?>
                </div>
              </div>
              <div class="row form-group">
                <label class="col-sm-3 control-label">Fill Restriction</label>
                <div class="col-sm-9">
                  <label class="radio-inline">
                      <input type="radio" name="FillRestriction" id="FillRestriction1" value="No Restriction"> No Restriction
                    </label>
                  <label class="radio-inline">
                      <input type="radio" name="FillRestriction" id="FillRestriction2" value="Do Not Fill"> Do Not Fill
                    </label>
                </div>
              </div>
              <div class="row form-group">
                <label class="col-sm-3 control-label">Anomalies Status</label>
                <div class="col-sm-9">
                  <label class="radio-inline">
                      <input type="radio" name="AnomaliesStatus" id="AnomaliesStatus1" value="Good"> Good
                    </label>
                  <label class="radio-inline">
                      <input type="radio" name="AnomaliesStatus" id="AnomaliesStatus2" value="Under Review"> Under Review
                    </label>
                </div>
              </div>
              <!--               
                  <div class="row form-group">
                    <label class="col-sm-3 control-label">Calories</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="name" id="name" required />
                    </div>
                  </div> 
                  -->
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
<?php include '../includes/footer.php'; ?>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script> 
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="script.js"></script>
</body>
</html>