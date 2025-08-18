<div class="row">
  <div class="col-sm-6">
    <h3>Audit Dates</h3>
  </div>
  <div class="col-sm-6">
  <?php if($myuser->userdata['isclient'] != "1"):?> 
    <a href="#" class="btn btn-success pull-right btn-complete" data-state="invoice" style="width:150px; margin-left:10px;"><span class="fa fa-check"></span> Mark Complete</a>
    <?php if($myuser->userdata['isclient'] != "2"):?> 
    <a href="" class="btn btn-warning pull-right btn-skip" data-state="invoice" style="margin-left: 10px;"><span class="fa fa-fast-forward"></span> Skip Step</a>    
    <?php endif; ?> 
   <?php endif; ?> 
   <!--<a href="" class="fileup-btn fileup-btn-small pull-right" id="btn-upload"><span class="glyphicon glyphicon-upload"></span> Upload </a>--> </div>
</div>
<hr style="margin-top:0;"/>
<div class="alert alert-danger" id="errors" style="display:none;"></div>
<div class="container-fluid-">
  <?php if ($myuser->userdata['isclient'] == "1"): ?>
  <p><strong>Please select 3 dates in order of preference.</strong></p>
  <?php else: ?>
  <p><strong>Please approve any one of the following dates selected by the client.</strong></p>
  <?php endif; ?>
  
  <!-- Added Language Preference Section -->
  <div class="row" style="margin-bottom: 20px;">
    <div class="col-sm-6">
      <h3>Preferred Audit Language*</h3>
      <select class="form-control" name="PreferredLanguage" id="PreferredLanguage" required>
        <option value="">-- Select Language --</option>
        <option value="ENGLISH">ENGLISH</option>
        <option value="GERMAN">GERMAN</option>
        <option value="ITALIAN">ITALIAN</option>
        <option value="FRENCH">FRENCH</option>
        <option value="HUNGARIAN">HUNGARIAN</option>
      </select>
    </div>
    <div class="col-sm-6">
      <h3>Would an audit in English be acceptable for you?*</h3>
      <div style="margin-top: 8px;">
        <label class="radio-inline">
          <input type="radio" name="EnglishAcceptable" id="EnglishAcceptableYes" value="Yes" required> Yes
        </label>
        <label class="radio-inline">
          <input type="radio" name="EnglishAcceptable" id="EnglishAcceptableNo" value="No"> No
        </label>
      </div>
    </div>
  </div>
  
  <div class="row">
    <div class="col-sm-4">
      <h3>1st Preferred Date*</h3>
      <div class="input-group date" id="dtpickerdemo"> 
      <?php if($myuser->userdata['isclient'] != "1"):?> 
        <span class="selectpicker form-control input-group-addon" style="width:10%;padding:10px;">
        <input type="radio" name="ApprovedDate1" id="ApprovedDate1" value="">
        </span>
        <?php endif; ?>
        <input type="text" <?php if($myuser->userdata['isclient'] != "1"):?> style="width:90%" disabledd<?php endif;?> class="form-control" name="AuditDate1" id="AuditDate1" value="" required  tabindex="4" />
        <span class="input-group-addon" style=" margin:0px auto;"> <span class="glyphicon glyphicon-calendar"></span> </span> </div>
    </div>
    <div class="col-sm-4">
      <h3>2nd Preferred Date*</h3>
      <div class="input-group date" id="dtpickerdemo"> 
      <?php if($myuser->userdata['isclient'] != "1"):?> 
        <span class="selectpicker form-control input-group-addon" style="width:10%;padding:10px;">
        <input type="radio" name="ApprovedDate1" id="ApprovedDate2" value="">
        </span>
        <?php endif; ?>
        <input type="text" <?php if($myuser->userdata['isclient'] != "1"):?> style="width:90%" disabledd<?php endif;?> class="form-control" name="AuditDate2" id="AuditDate2" value="" required  tabindex="4" />
        <span class="input-group-addon"> <span class="glyphicon glyphicon-calendar"></span> </span> </div>
    </div>
    <div class="col-sm-4">
      <h3>3rd Preferred Date*</h3>
      <div class="input-group date" id="dtpickerdemo"> 
      <?php if($myuser->userdata['isclient'] != "1"):?> 
        <span class="selectpicker form-control input-group-addon" style="width:10%;padding:10px;">
        <input type="radio" name="ApprovedDate1" id="ApprovedDate3" value="">
        </span>
        <?php endif; ?>
        <input type="text" <?php if($myuser->userdata['isclient'] != "1"):?> style="width:90%" disabledd<?php endif;?> class="form-control" name="AuditDate3" id="AuditDate3" value="" required  tabindex="4" />
        <span class="input-group-addon"> <span class="glyphicon glyphicon-calendar"></span> </span></div>
    </div>
  </div>
  <div class="SelectedDate" style="margin-top:20px;"></div>
  <div class="row">
    <div class="col-sm-12">
      <div style="text-align:right;"> 
        <?php if($myuser->userdata['isclient'] != "1"):?> 
          <button class="btn btn-info"  
             style="margin:0px; text-align:center; margin-top:16px; width:150px;" id="btn-approve"><span class="fa fa-check"></span> Approve Date </button> 
             <?php endif; ?>
          <button class="btn btn-primary"  
             style="margin:0px; text-align:center; margin-top:16px; width:150px;" id="btn-submit"><span class="fa fa-save"></span> Save Dates </button> 
      </div>
    </div>
  </div>
</div>