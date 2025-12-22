<style>
.modal-footer--sticky {
  position: sticky;
  bottom: 0;
  background-color: inherit; /* [1] */
  z-index: 1055; /* [2] */
}	
</style>
<?php
$auditTypes = ['Stage 1 - Remote<sup>1</sup> Documentation Reviewing', 
                'Stage 1 - On-site<sup>2</sup> Documentation Reviewing',
                'Stage 2 - On-site Audit',
                'Stage 2 - Remote Audit',
                'Pre-Audit - Activities:', 
                'Re-Certification Activities',
                'Surveillance3',
                'Education'];

$auditObjectives = ['Conformance Assessment Documentation',
                'Conformance Assessment Production Process',
                'Conformance Assessment Slaughtering Process',
                'System Validation Process',
                'Animal Welfare Control',
                'Implementation of previous NCR'];

$samplings = ['Documentation', 
                    'Records', 
                    'Traceability',
                    'Witness and Observing',
                    'Interviewing'];

$risks = ['Hazardous Materials', 
          'Animals On-Site',
          'Outbreaks or Pandemics',
          'Personal Presence',
          'Confidentiality and Information Security'];

$foodSafety = ['BRC',
'IFS',
'FSSC',
'ISO',
'To be Determined'];

$languages = ['English (recommended)'];

$ncs = ['Not Applicable'];

$documents = ['Customer Questionnaire',
'Halal Master Table',
'Declaration Forms (optional)',
'Laboratory Analysis Results (optional)',
'Audit Program',
'Education Program (optional)',
'Supporting Documents from the Company'];

$attachments = ['Non-Conformity Report',
'Audit Report',
'Decision Making Committee Results',
'Follow-Ups or Corrective Actions if required upon'];

$activities = ['Opening meeting', 
 'General criteria including management review and quality management system/Halal management team and training', 
 'Quality control criteria including CP’s, CCP’s and work instructions/ Raw materials, specifications, and supplier’s Halal certificate', 
 'Production site tour', 
 'Food safety and hazards criteria/  Labor anaylsis,Traceability and recalls instructions and implementation/Halal internal audits', 
 'Closing meeting'];

$htmlap ='<div id="page1" class="page active">
		<h2 style="text-align:center;">
		Halal Quality Control<br/>
		Audit Plan<br/>
    <span style="font-size:20px;">Form 0401</span></h3>
		<p></p><table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td width="15%"></td>
		  <td width="70%"><table border="1" 
	  cellpadding="6"
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
			class="form-control"
			value=""      
		  /></td>
			  </tr>
			  <tr>
			  <td style="text-align:center;">2</td>
			  <td>Company Name:</td>
			  <td><input type="text" size="38"
		  name="mainCompany"
		  id="mainCompany"
		  class="form-control"
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
		  class="form-control"
		  /></td>
			  </tr>
			  <tr>
				<td style="text-align:center;">4</td>
				<td>Manufacturing Site Address(es)</td>
				<td><input type="text" size="38"
			class="form-control"
			name="addresses"
			id="addresses"
			value=""
		  /></td>
			  </tr>
			  <tr>
				<td style="text-align:center;">5</td>
				<td>Company ID:</td>
				<td><input type="text" size="38"
			class="form-control"
			name="companyId"
			id="companyId"
			value=""
		  /></td>
			  </tr>
			  <tr>
			  <td style="text-align:center;">6</td>
			  <td>Reference (optional):</td>
			  <td><input type="text" size="38"
		  class="form-control"
		  name="reference"
		  id="reference"
		  value=""
		/></td>
			</tr>        
			</table></td>
		  <td width="15%"></td>
		</tr>
	  </table>
	  <p></p>
	  
	  <table class="table table-bordered table-sm" cellspacing="0" border="1" width="100%">
	  <tr>
		<td width="50%" style="text-align:center;" class="th"><strong>Audit Type</strong></td>
		<td width="50%" style="text-align:center;" class="th"><strong>Audit Objective</strong></td>
	  </tr>  
	  <tr>
		<td><textarea style="min-height:225px;" class="form-control" id="auditTypes" name="auditTypes">';
		foreach ($auditTypes as $i => $auditType) {
				$htmlap .= $auditType."\n";
		}
	  $htmlap.= '
	  </textarea>
	  </td>
		<td><textarea style="min-height:225px;" class="form-control" id="auditObjectives" name="auditObjectives">';
		foreach ($auditObjectives as $i => $auditObjective) {
			$htmlap .= $auditObjective."\n";
		}
	  $htmlap.= '
	  </textarea>
	  </td>
		</tr>
	  </table>
	  <p></p><p></p>
	  <p></p><p></p>
	  <p style="text-align:center;font-size:13px;">2021 (C) This document is the sole property of Halal Quality Control. The usage is only permitted by invitation and to be sent by a reliable source. All rights 
	  reserved. No part of this document may be reproduced, distributed, or transmitted in any form or by any means, including photocopying, recording, or other 
	  electronic or mechanical methods for external use, without prior written permission of Halal Quality Control</p>
	  <p></p><p></p>
	  <p></p><p></p>
	  <p><hr style="width:50%"/></p>
	  <p style="font-size:13px;"><sup>1</sup> Remote refers to not being (able to) physically present at the manufacturing facility.<br/>
	  <sup>2</sup> On-site refers to being physically present at the manufacturing facility.<br/>
	  <sup>3</sup> The surveillance includes periodic assessment of the production process or sampling approved products for compliance.
	  </div>
	  
	  <div id="page2" class="page">
	  <p></p>
	  <h2 style="text-align:center">Part 1: Audit Team</h2>
	  <p></p>
	  <table cellpadding="0" border="0" width="100%">
	  <tr>
	  <td width="48%">
	  <table class="table table-bordered table-sm" border="1" width="100%">
	  <tr>
		<td colspan="3" style="text-align:center;" class="th"><strong>Halal Quality Control</strong></td>
	  </tr>
	  <tr>
		<td width="6%" style="text-align:center" class="th"></td>
		<td width="47%" style="text-align:center" class="th"><strong>Name of Auditor</strong></td>
		<td width="47%" style="text-align:center" class="th"><strong>Position</strong></td>
	  </tr>
	  <tr>
		<td style="text-align:center">1</td>
		<td><input type="text" class="form-control" id="LeadAuditor" name="LeadAuditor" value="" size="23" /></td>
		<td>Lead Auditor</td>
	  </tr>
	  <tr>
		<td style="text-align:center">2</td>
		<td><input type="text" class="form-control" id="coAuditor" name="coAuditor" value="" size="23" /></td>
		<td>Co-Auditor</td>
	  </tr>
	  <tr>
		<td style="text-align:center">3</td>
		<td><input type="text" class="form-control" id="IslamicAffairsExpert" name="IslamicAffairsExpert" value="" size="23" /></td>
		<td>Islamic Affairs Expert</td>
	  </tr>
	  <tr>
		<td style="text-align:center">4</td>
		<td><input type="text" class="form-control" id="Veterinary" name="Veterinary" value="" size="23" /></td>
		<td>Veterinary (optional)</td>
	  </tr>
	  <tr>
		<td style="text-align:center">5</td>
		<td><input type="text" class="form-control" id="extra1" name="extra1" value="" size="23" /></td>
		<td><input type="text" class="form-control" id="extra2" name="extra1" value="" size="23" /></td>
	  </tr>
	  </table>
	  </td>
	  <td width="4%"></td>
	  <td width="48%">
	  <table class="table table-bordered table-sm" border="1" width="100%">
	  <tr>
		<td colspan="3" style="text-align:center;" class="th"><strong>Representative(s) of the Company</strong></td>
	  </tr>
	  <tr>
		<td width="6%" style="text-align:center" class="th"></td>
		<td width="47%" style="text-align:center" class="th"><strong>Name</strong></td>
		<td width="47%" style="text-align:center" class="th"><strong>Position</strong></td>
	  </tr>
	  <tr>
		<td style="text-align:center">1</td>
		<td><input type="text" class="form-control" id="extra3" name="extra3" value="" size="23" /></td>
		<td><input type="text" class="form-control" id="extra4" name="extra4" value="" size="23" /></td>
	  </tr>
	  <tr>
		<td style="text-align:center">2</td>
		<td><input type="text" class="form-control" id="extra5" name="extra5" value="" size="23" /></td>
		<td><input type="text" class="form-control" id="extra6" name="extra6" value="" size="23" /></td>
	  </tr>
	  <tr>
		<td style="text-align:center">3</td>
		<td><input type="text" class="form-control" id="extra7" name="extra7" value="" size="23" /></td>
		<td><input type="text" class="form-control" id="extra8" name="extra8" value="" size="23" /></td>
	  </tr>
	  <tr>
		<td style="text-align:center">4</td>
		<td><input type="text" class="form-control" id="veterinary2" name="veterinary2" value="" size="23" /></td>
		<td>Veterinary (optional)</td>
	  </tr>
	  <tr>
		<td style="text-align:center">5</td>
		<td><input type="text" class="form-control" id="abattoirSupervisor" name="abattoirSupervisor" value="" size="23" /></td>
		<td>Abattoir Supervisor 
		(optional)</td>
	  </tr>
	  </table>
	  </td>
	  </tr> 
	  </table>
	  <p></p>
	  <h2 style="text-align:center">Part 2: Criteria</h2>
	  <p></p>
	  <table class="table table-bordered table-sm" border="1" width="100%">
	  <tr>
	  <td width="50%" style="text-align:center;" class="th"><strong>Reference Halal Standard(s)<sup>4</sup></strong></td>
	  <td width="50%" style="text-align:center;" class="th"><strong>Certification Category (see Category Index Table 1):</strong></td>
	  </tr>
	  <tr>
	  <td><input type="text" class="form-control" id="extra9" name="extra9" value="" size="52" /></td>
	  <td><input type="text" class="form-control" id="extra10" name="extra10" value="" size="52" /></td>
	  </tr>
	  <tr>
	  <td><textarea style="min-height:225px;" class="form-control" id="samplings" name="samplings">';
	  foreach ($samplings as $i => $sampling) {
		  $htmlap .= $sampling."\n";
	  }
	$htmlap.= '
	</textarea>
	</td>
	<td><textarea style="min-height:225px;" class="form-control" id="risks" name="risks">';
	foreach ($risks as $i => $risk) {
		$htmlap .= $risk."\n";
	}
  $htmlap.= '
  </textarea>
  </td>	
	  </tr>
	  <tr>
		<td style="text-align:center;" class="th"><strong>Food Safety Management System present at Company:</strong></td>
		<td style="text-align:center;" class="th"><strong>Scope of Activities:</strong></td>
	  </tr>
	  <tr>
	  <td><textarea style="min-height:225px;" class="form-control" id="foodSafety" name="foodSafety">';
	  foreach ($foodSafety as $i => $fs) {
		  $htmlap .= $fs."\n";
	  }
	$htmlap.= '
	</textarea>
	</td>	
    <td><textarea style="min-height:225px;" cols="105" class="form-control" name="scope-of-activities" id="scope-of-activities"></textarea></td>
	  </tr>
	  <tr>
		<td style="text-align:center;" class="th"><strong>Reporting Language:</strong></td>
		<td style="text-align:center;" class="th"><strong>Previous Non-Conformances / Non-Compliances:</strong></td>
	  </tr>
	  <tr>
	  <td><textarea style="min-height:225px;" class="form-control" id="languages" name="languages">';
	  foreach ($languages as $i => $language) {
		  $htmlap .= $language."\n";
	  }
	$htmlap.= '
	</textarea>
	</td>	
	<td><textarea style="min-height:225px;" class="form-control" id="ncs1" name="ncs1">';
	foreach ($ncs as $i => $nc) {
		$htmlap .= $nc."\n";
	}
  $htmlap.= '
  </textarea>
  </td>	
	  </tr>
	  </table>
	  
	  <hr/>
	  <p><sup>4</sup> References: GSO 2055-2:2015, UAE.S 2055-2:2016, SMIIC 1:2019, JAKIM MS 1500:2019, HAS 23000:2</p>
	  </div>
	  
	  <div id="page3" class="page">
	  <p></p>
	  <h2 style="text-align:center">Part 3: Agenda, Objections, and Previous Results</h2>
	  <p></p>
	  <table class="table table-bordered table-sm" cellspacing="0" border="1" width="100%">
	  <tr>
		<td width="50%" style="text-align:center;" class="th"><strong>Assessment Date 1:</strong></td>
		<td width="50%" style="text-align:center;" class="th"><strong>Assessment Date 2 (if any):</strong></td>
	  </tr>
	  <tr>
	  <td><input type="text" class="form-control" id="extra14" name="extra14" value="" size="52" /></td>
	  <td><input type="text" class="form-control" id="extra15" name="extra15" value="" size="52" /></td>
	  </tr>
	  </table>
	  <table class="table table-bordered table-sm" cellspacing="0" border="1" width="100%">
	  <tr>
		<td width="25%" style="text-align:center;" class="th"><strong>Time or Day</strong></td>
		<td width="50%" style="text-align:center;" class="th"><strong>Activity / Phase / Subject</strong></td>
		<td width="25%" style="text-align:center;" class="th"><strong>Date:</strong></td>
	  </tr>';
	  for ($i=1;$i<15;$i++) {
		$htmlap .= '<tr>
				  <td><input type="text" class="form-control" id="tableData'.$i.'-1"  name="tableData'.$i.'-1" value="" size="25" /></td>
				  <td><input type="text" class="form-control" id="tableData'.$i.'-2"  name="tableData'.$i.'-2" value="'.$activities[$i-1].'" size="52" /></td>
				  <td><input type="text" class="form-control" id="tableData'.$i.'-3"  name="tableData'.$i.'-3" value="" size="25" /></td>
				</tr>';
	  }
	  $htmlap .= '</table>
	  <p></p>
	  <table class="table table-bordered table-sm" cellspacing="0" border="1" width="100%">
	  <tr>
		<td width="50%" style="text-align:center;" class="th"><strong>Objections from the Company:</strong></td>
		<td width="50%" style="text-align:center;" class="th"><strong>Previous Non-Conformances / Non-Compliances:</strong></td>
	  </tr>  
	  <tr>
		<td>
		  <table cellspacing="0" border="0" width="100%">
			<tr>
			  <td>
				<span class="form-check">
				<input
				class="form-check-input"
				type="radio" checked="checked"
				name="object1"
				id="object1a"
				value="No"
				/> <label class="form-check-label" for="object1a">No</label>
				</span>
			</td>
		  </tr>
		  <tr>
			<td>
			  <span class="form-check"><input
				class="form-check-input"
				type="radio" 
				name="object1"
				id="objectb"
				value="Yes"
			  /> <label class="form-check-label" for="object1b">Yes</label>
			  </span>
			</td>
		  </tr>
		  <tr>
			<td> Reason: <input
			type="text" size="25"
			class="form-control"
			name="objectReason"
			id="objectReason"
			value=""
		  /></td>
		  </tr>
		</table>
	   </td>
	   <td><textarea style="min-height:225px;" class="form-control" id="ncs2" name="ncs2">';
	   foreach ($ncs as $i => $nc) {
		   $htmlap .= $nc."\n";
	   }
	 $htmlap.= '
	 </textarea>
	 </td>	
	   </tr>
	  </table>
	  <p></p>
	  <p style="text-align:center;"><strong>In case of an objection, you may request our Complaints and Appeals procedures and forms for further handling.</strong></p>
	  </div>
	  
	  <div id="page4" class="page">
	  <p></p>
	  <h2 style="text-align:center;">Part 4: Working Documents and Attachments</h2>
	  <p></p>
	  <table class="table table-bordered table-sm" cellspacing="0" border="1" width="100%">
	  <tr>
		<td width="50%" style="text-align:center;" class="th"><strong>Documents involved before the inspection</strong></td>
		<td width="50%" style="text-align:center;" class="th"><strong>Attachments</strong></td>
	  </tr>  
	  <tr>
	  <td><textarea style="min-height:225px;" class="form-control" id="documents" name="documents">';
	  foreach ($documents as $i => $document) {
		  $htmlap .= $document."\n";
	  }
	$htmlap.= '
	</textarea>
	</td>	
	<td><textarea style="min-height:225px;" class="form-control" id="attachments" name="attachments">';
	foreach ($attachments as $i => $attachment) {
		$htmlap .= $attachment."\n";
	}
  $htmlap.= '
  </textarea>
  </td>		
		</tr>
	  </table>
	  <p></p>
	  <p></p>
	  <p style="text-align:center;"><strong>All Working Documents should be ready and reviewed upon prior the inspection date
	  All Attachments may or should be shared with the company.</strong></p>
	  <p style="text-align:center;"><strong>All other documents requested as Supporting Documents should be sent to Halal Quality 
	  Control when requested upon.</strong></p>
	  <p></p>
	  <table class="table table-bordered table-sm" cellspacing="0" border="1" width="100%">
	  <tr>
		<td style="text-align:center;" class="th"><strong>Remarks</strong></td>
	  </tr>
	  <tr>
		<td height="500">
		<textarea rows="23" cols="105" class="form-control" name="main-remarks" id="main-remarks"></textarea>
		</td>
	  </tr>
	  </table>
	  </div>	  
	  ';
?>
 

<form action="/preview/auditplan.php" method="post" target="_blank" id="frmPreview">
<input type="hidden" name="idclient" id="previewIdClient" value="" />
<input type="hidden" name="idapp" id="previewIdApp" value="" />
</form> 

<input type="hidden" name="audit_filename" id="audit_filename" value="" />
<input type="hidden" name="audit_signed_filename" id="audit_signed_filename" value="" />
<div class="row">
  <div class="col-sm-4">
    <h3>Audit Plan</h3>
  </div>
  <div class="col-sm-8">
  <?php if($myuser->userdata['isclient'] != "1"):?> 

      <a href="#" class="btn btn-success pull-right btn-complete" data-state="report" style="width:150px; margin-left:10px;"><span class="fa fa-check"></span> Mark Complete</a>
	  <?php if($myuser->userdata['isclient'] != "2"):?> 
	  <a href="" class="btn btn-warning pull-right btn-skip" data-state="report" style="margin-left: 10px;"><span class="fa fa-fast-forward"></span> Skip Step</a>    
	  <?php endif; ?>
      <a href="#" class="btn btn-primary pull-right btn-settings" data-toggle="modal" data-target="#modalauditplansettings" style="width:150px; margin-left:10px;"><span class="fa fa-cog"></span> Settings </a>

      <a href="#" class="btn btn-info pull-right" id="btn-sendplan" style="width:150px; margin-left:10px;"><span class="fa fa-paper-plane"></span> Send </a>

      <a href="#" class="btn btn-warning pull-right" id="btn-preview" style="width:150px; margin-left:10px;"><span class="fa fa-eye"></span> Preview </a>

    <?php endif; ?> 

  
   <!--<a href="" class="fileup-btn fileup-btn-small pull-right" id="btn-upload"><span class="glyphicon glyphicon-upload"></span> Upload </a>--> </div>
</div>
<hr style="margin-top:0;"/>
<?php if ($myuser->userdata['isclient']): ?>
  <!--
<div class="alert alert-warning">Please download, sign and upload using the <a href="#" onclick="return false;" data-title=""  class="btn btn-success ">
							   <span class="fa fa-signature" title="Upload Signed Document" aria-hidden="true"></span></a>
                               
                               button next to each document.
                               </div>
-->
<?php endif; ?>
<div class="row" style="margin-bottom:10px; display:none;">
  <div class="col-md-6"> 
    <!--<h4 style="margin-top: 0">Filter</h4>-->
    <div class="input-group my-group search-box">
      <input class="form-control" name="s" id="s" style="width:250px;">
      <span class="input-group-btn pull-left">
      <button class="btn btn-primary" id="btn-search" type="button" style="padding: 2px 10px;"><span class="glyphicon glyphicon-search" aria-hidden="true"> </span> </button>
      </span> </div>
  </div>
  <div class="col-md-6">
    <label class="right">
      <input id="filter-actions-deleted" class="ace ace-switch ace-switch-4" type="checkbox">
      <span class="lbl">&nbsp;&nbsp;Show deleted documents</span> </label>
  </div>
</div>

<div class="alert alert-danger" id="errors" style="display:none;"></div>

<table id="table_audit" class="table table-hover table-striped table-bordered">
  <thead>
    <tr class="tableheader">
      <th style="width:25px;"></th>
      <th style="">Title</th>
      <th style="">File Name</th>
      <th style="">Uploaded by</th>
      <th style="">Upload Date</th>
     <!--<th style="">Date Created</th>
      <th style="">Signature Required</th>
      <th style="width:20%;">Comments</th>-->
      <th  style="width:175px;" class="no-sort"></th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
<div id="modalaudit" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Upload Document</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors" style="display:none;"></div>
        <form id="frmOffer" data-toggle="validator">
          <input type="hidden" name="ID" id="ID" value="" />
          <div class="form-horizontal row">
            <div class="col-md-12">
              <div class="row form-group text-left">
                <label class="col-sm-12 control-label" style="text-align:left;">Title <span class="text-danger">*</span></label>
                <div class="col-sm-12">
                  <input type="text" class="form-control" name="Title" id="Title" />
                </div>
              </div>
            </div>
          </div>
          <div class="form-horizontal row">
            <div class="col-md-12">
              <div class="row form-group text-left">
                <label class="col-sm-12 control-label" style="text-align:left;">Signature Required </label>
                <div class="col-sm-12">
                  <label class="radio-inline"><input type="radio" name="Signature" id="Signature1" value="1">Yes</label>
				  <label class="radio-inline"><input type="radio" name="Signature" id="Signature2" checked value="0">No</label>
                </div>
              </div>
            </div>
          </div>
          <div class="form-horizontal row">
            <div class="col-md-12">
              <div class="row form-group text-left">
                <label class="col-sm-12 control-label" style="text-align:left;">Comments </label>
                <div class="col-sm-12">
                  <textarea class="form-control" name="Comments" id="Comments"  tabindex="8"></textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="form-horizontal row">
            <div class="col-md-12">
              <div class="row form-group text-left">
                <div style="padding:0 15px;">

                  <div id="audit-pdf-dropzone" class="dropzone">
				  <i class="fa fa-upload" aria-hidden="true"></i>
                  <br/><br/>
                    Drag & Drop to Upload File 
                    <br/><br/>
                    OR
                    <br/>
                    <div class="fileup-btn fileup-btn-small"> Select PDF file

                    <input type="file" name="audit-pdf" class="_3eHqh form-control" id="audit-pdf" tabindex="-1" accept="application/pdf"/>
                    </div>
				
				</div>
                  <div id="audit-pdf-queue"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <button type="button" class="btn btn-primary pull-right" id="btn-savesettings" tabindex="13"><i class="fa fa-save"></i> Submit</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div id="modalsignaudit" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add"><span id="Label"></span></h4>
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
                  <div id="sign-dropzone" class="dropzone" style="font-size:18px; padding:50px 0;">
                    <div class="fileup-btn fileup-btn-small"> Select PDF file
                      <input type="file" name="sign-audit-pdf" class="_3eHqh form-control" id="sign-audit-pdf" tabindex="-1" accept="application/pdf"/>
                    </div>
                    <br>
                    or  Drop file here </div>
                  <div id="sign-audit-pdf-queue"></div>
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
<div id="modalauditplansettings" class="modal" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Audit Plan Settings</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <form id="frmAuditPlanSettings">
          <?php echo $htmlap; ?>
        </form>
      </div>
      <div class="modal-footer modal-footer--sticky">
      <button type="button" id="btnsave-settings" class="btn btn-primary">Submit</button>
      </div>
    </div>
  </div>
</div>