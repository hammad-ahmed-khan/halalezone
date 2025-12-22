<style>
  #last_report_sent {
    color:red;
    font-size:12px;
    text-align:center;
  }
.modal-footer--sticky {
  position: sticky;
  bottom: 0;
  background-color: inherit; /* [1] */
  z-index: 1055; /* [2] */
}	
#report .cvitem,
#report .cvitem a {
  font-size:12px !important;

}
</style>
<?php
$html = '
<div id="page1" class="page active">
<h2 style="text-align:center;">
Halal Quality Control<br/>
Audit Report
</h2>
<p></p><table width="100%" border="0" cellpadding="8" cellspacing="0">
<tr>
  <td width="15%"></td>
  <td width="70%"><table border="1" 
cellpadding="8"
cellspacing="0"
class="table table-bordered table-sm"
width="100%"
>
    <tr>
    <td style="text-align:center;" width="7%">1</td>
    <td width="40%">Date:</td>
    <td width="53%"><input type="text" size="38"
  name="mainDate"
  id="mainDate"
  style=:"width:100%;"
  value=""      
  /></td>
    </tr>
    <tr>
    <td style="text-align:center;">2</td>
    <td>Company Name:</td>
    <td><input type="text" size="38"
  name="mainCompany"
  id="mainCompany"
  style=:"width:100%;"
  value=""
/></td>
  </tr>        
    <tr>
    <td style="text-align:center;">3</td>
    <td>Country of Company:</td>
    <td><input type="text" size="38"
  name="countryOfCompany"
  id="countryOfCompany"
  value=""
  style=:"width:100%;"
  /></td>
    </tr>
    <tr>
    <td style="text-align:center;">4</td>
    <td>Manufacturing Site Address(es)</td>
    <td><input type="text" size="38"
  style=:"width:100%;"
  name="addresses"
  id="addresses"
  value=""
  /></td>
    </tr>
    <tr>
    <td style="text-align:center;">5</td>
    <td>Company ID:</td>
    <td><input type="text" size="38"
  style=:"width:100%;"
  name="companyId"
  id="companyId"
  value=""
  /></td>
    </tr>
    <tr>
    <td style="text-align:center;">6</td>
    <td>Reference (optional):</td>
    <td><input type="text" size="38"
  style=:"width:100%;"
  name="reference"
  id="reference"
  value=""
/></td>
  </tr>        
  </table>
  <p></p>
  <table border="1" 
cellpadding="8"
cellspacing="0"
class="table table-bordered table-sm"
width="100%"
>
    <tr>
    <td style="text-align:center;" width="7%">1</td>
    <td width="40%">Lead Auditor:</td>
    <td width="53%"><input type="text" size="38"
  name="LeadAuditor"
  id="LeadAuditor"
  style=:"width:100%;"
  value=""      
  /></td>
    </tr>
    <tr>
    <td style="text-align:center;">2</td>
    <td>Islamic Affairs Expert:</td>
    <td><input type="text" size="38"
  name="IslamicAffairsExpert"
  id="IslamicAffairsExpert"
  style=:"width:100%;"
  value=""
/></td>
  </tr>        
    <tr>
    <td style="text-align:center;">3</td>
    <td>Accompanying Auditors or Experts:</td>
    <td><input type="text" size="38"
  name="AccompanyingAuditorsOrExperts"
  id="AccompanyingAuditorsOrExperts"
  value=""
  style=:"width:100%;"
  /></td>
    </tr>
    <tr>
    <td style="text-align:center;">4</td>
    <td>Halal Quality Control Office (Country):</td>
    <td><input type="text" size="38"
  style=:"width:100%;"
  name="HalalQualityControlOfficeCountry"
  id="HalalQualityControlOfficeCountry"
  value=""
  /></td>
    </tr>       
  </table>
  
  </td>
  <td width="15%"></td>
</tr>
</table>
<p></p>
<p style="text-align:center;">This document is property of Halal Quality Control</p>
</div>
<br pagebreak="true"/>
<div id="page2" class="page">';
$html .= 
'<p></p>
<table border="1" cellspacing="0" cellpadding="5" width="100%" style="font-size:12px;">
<tr style="background-color:#f2dbdb;">
<td width="4%">No.</td>
<td width="6%">Type of Finding (NCR, OBS)</td>
<td width="24%">NCR/OBS statement</td>
<td width="24%">Root Cause Analysis</td>	
<td width="24%">Proposed Corrective Action</td>		
<td width="8%">Target Date</td>		
<td width="10%">Auditors comment
after 
Implementation</td>			
</tr>';

$counts = array(
'Major' => 0,
'Minor' => 0,
'OBS' => 0);

$num = 0;
foreach ($data["deviations"] as $deviation) {
$num++;
$Type = $deviation['Type'];
$counts[$Type]++;
$Status = $deviation['Status'];
if ($Status == '0') {
  $r_color = '#f2dede';
}
else if ($Status == '1') {
  $r_color = '#dff0d8';
}
$html .= '<tr style="background-color:'.$r_color.';">
<td>'.$num.'</td>
<td>'.$deviation["Type"].'</td>
<td>'.$deviation["Deviation"].'</td>
<td>'.$deviation["RootCause"].'</td>	
<td>'.$deviation["Measure"].'</td>		
<td>'.$deviation["Deadline"].'</td>		
<td>'.($Status=='1'?'Confirmed':'Not Confirmed').'</td>			
</tr>';
}

$html .= '</table>
<h2 style="text-align:center;">Summary</h2>
<table border="1"
cellpadding="8"
cellspacing="0"
width="100%"  class="table table-bordered table-sm">
<tr>
<td style="width: 25%">Total Number of Findings: <strong>'.$num.'</strong></td>
<td style="width: 25%">Total Major: <strong>'.$counts["Major"].'</strong></td>
<td style="width: 25%">Total Minor: <strong>'.$counts["Minor"].'</strong></td>
<td style="width: 25%">Total Observation: <strong>'.$counts["OBS"].'</strong></td>
</tr>
</table>

<h2 style="text-align:center;">Scope Appropriateness</h2>
<table border="1" width="100%" cellspacing="0"
cellpadding="8"class="table table-bordered table-sm mb-5"
>
<tr>
<td width="10%" style="text-align:center;">1</td>
<td width="40%">Scope of Activities</td>
<td width="50%"><input
  class="form-check-input"
  type="radio"
  name="scopeOfActivities"
  id="scopeOfActivities1"
  value="Fullfilled"
/>
<label class="form-check-label" for="scopeOfActivities1">
  Fulfilled</label
><br/>
<input
  class="form-check-input"
  type="radio"
  name="scopeOfActivities"
  id="scopeOfActivities2"
  value="Not Fulfilled"
/>
<label class="form-check-label" for="scopeOfActivities2">
  Not Fulfilled (please mention or see Unresolved Issues
  below)</label
>
</td>
</tr>
<tr>
<td style="text-align:center;">2</td>
<td>Certification Scope (Category)</td>
<td><input
  class="form-check-input"
  type="radio"
  name="certScope"
  id="certScope1"
  value="Fulfilled"
/>
<label class="form-check-label" for="certScope1">
  Fulfilled</label
><br/>
<input
  class="form-check-input"
  type="radio"
  name="certScope"
  id="certScope2"
  value="Not Fulfilled"
/>
<label class="form-check-label" for="certScope2">
  Not Fulfilled (please mention or see Unresolved Issues
  below)</label
>
</td>
</tr>
</table>
</div>
<br pagebreak="true"/>
<div id="page3" class="page">
<p></p>
<table border="1" cellpadding="8" cellspacing="0" width="100%" class="table table-bordered table-sm">
<tr>
<th colspan="2" style="text-align:center;" class="th">Recommendation as a result of the inspection</th>
</tr>
<tr>
<td style="width: 50%">
<input
  class="form-check-input"
  type="checkbox"
  name="obj"
  id="obj"
  value="The audit objective is fulfilled"
/>
<label class="form-check-label" for="regions1">
  The audit objective is fulfilled
</label>
</td>
<td style="width: 50%">
<input
  class="form-check-input"
  type="checkbox"
  name="ar"
  id="ar"
  value="Action Required (Documentation)"
/>
<label class="form-check-label" for="ar">
  Action Required (Documentation)
</label>
</td>
</tr>
<tr>
<td>
<input
  class="form-check-input"
  type="checkbox"
  name="arpi"
  id="arpi"
  value="Action Required (Physical Inspection)"
/>
<label class="form-check-label" for="apa">
  Action Required (Physical Inspection)
</label>
</td>
<td>
<input
  class="form-check-input"
  type="checkbox"
  name="nc"
  id="nc"
  value="Failure / Non-Compliance with the audit objective"
/>
<label class="form-check-label" for="nc">
  Failure / Non-Compliance with the audit objective
</label>
</td>
</tr>
<tr>
<td colspan="2">Conslusion / Summary of the inspection:<br />
<textarea name="conclusion1" id="conclusion1" cols="105" style="width:100%" rows="4" style=:"width:100%;"></textarea><br /><br /><br />
Opportunities for improvements:<br />
<textarea name="conclusion2" id="conclusion2" cols="105" style="width:100%" rows="4" style=:"width:100%;"></textarea><br /><br /><br />
Unresolved issues if any (for example scopes not fulfilled):<br />
<textarea name="conclusion3" id="conclusion3" cols="105" style="width:100%;" rows="4" style=:"width:100%;"></textarea><br /><br />
</td>
</tr>
</table>
<table border="1" cellpadding="8" cellspacing="0" width="100%" class="table table-bordered table-sm">
<tr>
<td style="width: 35%">To be filled in by the Customer:<br /><br />
Date of Action Plan: <input type="text" name="dateOfAction" id="dateOfAction" size="12" value="" style=:"width:100%;" /><br /><br />
Name: <input type="text" name="nameAction" id="nameAction" size="24" value="" style=:"width:100%;" /><br /><br />
<br/><br/><br/><br/><br/>
<small>Please insert a signature within this field</small>
</td>
<td style="width: 35%">To be filled in by the Lead Auditor:<br /><br />
Date of Implementation Closure: <input type="text" name="dateOfClosure" id="dateOfClosure" size="12" value="" style=:"width:100%;" /><br /><br />
<br/><br/><br/><br/><br/><br/><br/>
<small>Please insert a signature within this field</small>
</td>
<td style="width: 30%">Remarks:<br/><br />
<textarea name="main-remarks" id="main-remarks" cols="30" rows="3" style=:"width:100%;"></textarea><br /><br />
</td>
</tr>
</table>
</div>';
?>

<input type="hidden" name="report_filename" id="report_filename" value="" />
<input type="hidden" name="report_signed_filename" id="report_signed_filename" value="" />
<form action="/preview/report.php" method="post" target="_blank" id="frmPreview">
<input type="hidden" name="idclient" id="previewIdClient" value="" />
<input type="hidden" name="idapp" id="previewIdApp" value="" />
</form> 

<div class="row">
  <div class="col-sm-3">
    <h2>Audit Report</h2>
  </div>
  <div class="col-sm-9"> 
  
  <?php if($myuser->userdata['isclient'] != "1"):?> 

    <a href="#" class="btn btn-success pull-right btn-complete"  style="width:175px; margin-left: 10px;" data-state="pop"><span class="fa fa-check"></span> Mark Complete</a>
    <?php if($myuser->userdata['isclient'] != "2"):?> 
    <a href="" class="btn btn-warning pull-right btn-skip" data-state="pop" style="margin-left: 10px;"><span class="fa fa-fast-forward"></span> Skip Step</a>    
    <?php endif; ?> 
    <a href="#" class="btn btn-primary pull-right btn-settings" data-toggle="modal" data-target="#modalauditreportsettings" style="width:175px; margin-left:10px;"><span class="fa fa-cog"></span> Settings </a>    
    <span style="display:block;width:175px;margin-left:10px;" class="pull-right text-center">
    <a href="" class="btn btn-info " id="btn-send-report" style="width:175px; "><span class="fa fa-send"></span> Email Client  </a> 
    <span id="last_report_sent"></span>
    </span>
    <?php endif; ?> 
    
    <a href="" class="btn btn-warning pull-right" id="btn-preview" style="width:200px; margin-left:10px;"><span class="fa fa-download"></span> Download Audit Report </a> 
    
  </div>
</div>
<hr style="margin-top:0;"/>
<div class="alert alert-warning"><i class="fa fa-circle-info"></i> 
<?php if($myuser->userdata['isclient'] == "1"):?>    
<strong>Kindly ask you to fill in the root cause analysis, proposed corrective action and target date using the <span class="glyphicon glyphicon-edit text-primary"  aria-hidden="true"></span> button located next to each deviation.</strong>
<?php else:?>    
  <strong>Once you have added the deviations, click the "Email Client" button to inform them about the update on the audit report.</strong>
<?php endif; ?>  
</div> 
<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> 
<strong>All major findings must be accepted and implemented before the certificate can be issued.</strong>
</div>
<?php if($myuser->userdata['isclient'] != "1"):?> 
  <a href="" class="btn btn-primary pull-right" id="btn-upload"><span class="glyphicon glyphicon-upload"></span> Add Deviation </a> 

<?php endif; ?>
<div class="alert alert-danger" id="errors" style="display:none;"></div>
<p><strong>Major:</strong> <span id="totalMajor"></span> &nbsp;&nbsp;&nbsp; <strong>Minor:</strong> <span id="totalMinor"></span> &nbsp;&nbsp;&nbsp; <strong>OBS:</strong> <span id="totalOBS"></span> &nbsp;&nbsp;&nbsp; <strong style="color:green;">Confirmed:</strong> <span id="totalConfirmed" style="color:green;"></span> &nbsp;&nbsp;&nbsp; <strong style="color:red;">Not Confirmed:</strong> <span id="totalNotConfirmed" style="color:red;"></span></p>
<table id="table_report" class="table table-hover table-striped table-bordered">
  <thead>
    <tr class="tableheader">
      <th style="">Type of Finding (NCR,OBS)</th>
      <th style="">NC/OBS Statement</th>
      <th style="">Reference to Checklist</th>
      <th style="">Root Cause Analysis</th>
      <th style="">Proposed Corrective Action</th>
      <th>Target Date</th>
      <th style="width:175px;">Documents</th>
      <th style="width:175px;">Auditor Comments</th>
      <th style="width:75px;" class="no-sort"></th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
<div id="modaldeviation" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Add Deviation</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors" style="display:none;"></div>
        <form id="frmOffer" data-toggle="validator">
          <input type="hidden" name="ID" id="ID" value="" />
          <div class="form-horizontal row">
			<div class="col-md-12">
              <div class="row form-group text-left">
                <label class="col-sm-12 control-label" style="text-align:left;">Type of Finding (NCR,OBS) <span class="text-danger">*</span></label>
                <div class="col-sm-12">
                    <select id="Type" class="selectpicker form-control"  style="width:100%;" data-live-search="true" title="">
                      <option value="">Select an Option</option>
                      <option value="Major">Major</option>
                      <option value="Minor">Minor</option>
                      <option value="OBS">OBS</option>
                    </select>
                </div>
              </div>
            </div>            
            <div class="col-md-12">
              <div class="row form-group text-left">
                <label class="col-sm-12 control-label" style="text-align:left;">NC/OBS Statement <span class="text-danger">*</span></label>
                <div class="col-sm-12">
                    <select id="Deviation" class="selectpicker form-control"  style="width:100%;" data-live-search="true" title="">
                      <option value=""></option>
                      <option value="addNewDeviation">+ Add New</option>
                    </select>
                  <textarea class="form-control hidden" name="NewDeviation" id="NewDeviation"  tabindex="8"></textarea>
                </div>
              </div>
            </div>
            <div class="col-md-12">
              <div class="row form-group text-left">
                <label class="col-sm-12 control-label" style="text-align:left;">Reference to Checklist <span class="text-danger">*</span></label>
                <div class="col-sm-12">
                    <input type="text" name="Reference" id="Reference" class="form-control" value="" />
                </div>
              </div>
            </div>            
          </div>        
          <div class="row">
            <div class="col-sm-12">
              <button type="button" class="btn btn-primary pull-right" id="btn-submit" tabindex="13"><i class="fa fa-save"></i> Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div id="modalmeasure" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Add Measure</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors" style="display:none;"></div>
        <form id="frmOffer" data-toggle="validator">
          <input type="hidden" name="ID" id="ID" value="" />
          <table id="addMeasureTab" class="table table-bordered">
    <tr>
      <td style="width:35%;">Type of Finding (NCR,OBS)</td>
      <td style=""><div id="lblType"></div></td>
    </tr>
    <tr>
      <td>NC/OBS Statement</td>
      <td><div id="lblDeviation"></div></td>
    </tr>
    <tr>
      <td>Reference to Checklist</td>
      <td><div id="lblReference"></div></td>
    </tr>
    <tr>
      <td>Root Cause Analysis <span class="text-danger">*</span></td>
      <td><textarea class="form-control" name="RootCause" id="RootCause"  tabindex="8"></textarea></td>
    </tr>
    <tr>
      <td>Proposed Corrective Action <span class="text-danger">*</span></td>
      <td><textarea class="form-control" name="Measure" id="Measure"  tabindex="8"></textarea></td>
    </tr>
    <tr>
      <td>Deadline <span class="text-danger">*</span></td>
      <td><div class="input-group date" id="dtpickerdemo">
          <input type="text" class="form-control input-group" name="Deadline" id="Deadline" value="" />
          <span class="input-group-addon">
              <span class="glyphicon glyphicon-calendar"></span>
          </span>
      </div></td>
    </tr>
</table>
          
                  
          <div class="row">
            <div class="col-sm-12">
              <button type="button" class="btn btn-primary pull-right" id="btnsave-measure" tabindex="13"><i class="fa fa-save"></i> Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div id="modaldocs" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Upload Documents</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors" style="display:none;"></div>
        <form id="frmOffer" data-toggle="validator">
          <input type="hidden" name="ID" id="ID" value="" />
          <input type="hidden" name="Title" id="Title" value="" />
          <div class="form-horizontal row">
            <div class="col-md-12">
              <div class="row form-group text-left">
                <div style="padding:0 15px;">
                  <div id="docs-dropzone" class="dropzone">
                  <i class="fa fa-upload" aria-hidden="true"></i>
                  <br/><br/>
                    Drag & Drop to Upload File 
                    <br/><br/>
                    OR
                    <br/>
                    <div class="fileup-btn fileup-btn-small"> Select PDF file
                    <input type="file" name="docs-pdf" class="_3eHqh form-control" id="docs-pdf" tabindex="-1" accept="application/pdf"/>
                    </div> </div>
                  <div id="docs-pdf-queue"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <button type="button" class="btn btn-primary pull-right" id="btn-submit" tabindex="13"><i class="fa fa-save"></i> Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div id="modalauditreportsettings" class="modal" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Audit Report Settings</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors" style="display:none;"></div>
        <form id="frmAuditReportSettings">
          <?php echo $html; ?>
        </form>
      </div>
      <div class="modal-footer modal-footer--sticky">
        <button type="button" id="btnsave-settings" class="btn btn-primary">Submit</button>
      </div>
    </div>
  </div>
</div>