<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('../pages/header.php');
    include_once ('../includes/func.php');?>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />    
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
<?php include_once('../pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">	  
                      
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
                       <option value="Sensor Installed no L100">Sensor Installed no L100</option>
                       <option value="Sensor Installed">Sensor Installed</option>
                       <option value="First Fill">First Fill</option>
                       <option value="Billed">Billed</option>
                       <option value="COT">COT</option>                       
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
<?php include_once('../pages/footer.php');?>
<script src="../js/jquery-2.1.4.min.js"></script>
<!-- <![endif]-->
<!--[if IE]>
<script src="js/jquery-1.11.3.min.js"></script>
<![endif]-->
<script src="../js/bootstrap.min.js"></script>
<!-- page specific plugin scripts -->
<script src="../js/bootstrap-datepicker.min.js"></script>
<script src="../js/jquery.jqGrid.min.js"></script>
<script src="../js/grid.locale-en.js"></script>
<!-- ace scripts -->
<script src="../js/ace-elements.min.js"></script>
<script src="../js/ace.min.js"></script>
<script src="../js/select2.full.min.js"></script>
<script src="../js/vendor/jquery.ui.widget.js"></script>
<script src="../js/jquery.iframe-transport.js"></script>
<script src="../js/jquery.fileupload.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script> 
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script> 
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js?ver=1285677791' id='blockui-js'></script> 