<style>
td.details-control div {
	background: url('/img/details_open.png') no-repeat center center;
	cursor: pointer;
	width:100%;
	height:40px;
}
tr.shown td.details-control div {
	background: url('/img/details_close.png') no-repeat center center;
}
tr.shown div > td {
	font-weight: bold;
}

</style>
<input type="hidden" name="declarations_filename" id="declarations_filename" value="" />
<input type="hidden" name="declarations_signed_filename" id="declarations_signed_filename" value="" />
<div class="row">
  <div class="col-sm-6">
    <h3>Client Questionnaire / Free Form Declarations</h3>
  </div>
  <div class="col-sm-6">
  <?php if($myuser->userdata['isclient'] != "1"):?> 
    <a href="#" class="btn btn-success pull-right btn-complete" data-state="audit" style="width:150px; margin-left:10px;"><span class="fa fa-check"></span> Mark Complete</a>
    <?php if($myuser->userdata['isclient'] != "2"):?> 
    <a href="" class="btn btn-warning pull-right btn-skip" data-state="audit" style="margin-left: 10px;"><span class="fa fa-fast-forward"></span> Skip Step</a>    
    <?php endif; ?> 
  <?php endif; ?> 
   <!--<a href="" class="fileup-btn fileup-btn-small pull-right" id="btn-upload"><span class="glyphicon glyphicon-upload"></span> Upload </a>--> </div>
</div>
<hr style="margin-top:0;"/>
<?php if ($myuser->userdata['isclient']): ?>
<div class="alert alert-warning">
Kindly download and sign each document, and then use the <a href="#" onclick="return false;" data-title=""  class="btn btn-success ">
<span class="fa fa-signature" title="Upload Signed Document" aria-hidden="true"></span></a> button located next to each document to upload your signed copies.
</div>
<?php endif; ?>
<div class="alert alert-warning">
  <strong>Click the <img src="/img/details_open.png" /> icon to view the signed documents <?php if ($myuser->userdata['isclient']): ?>you<?php else: ?>client<?php endif; ?> uploaded.</strong>
</div>
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
<table id="table_declarations" class="table table-hover table-striped table-bordered">
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
<div id="modaldeclarations" class="modal" data-backdrop="static">
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
                  <div id="declarations-pdf-dropzone" class="dropzone">
                    
                  <i class="fa fa-upload" aria-hidden="true"></i>
                  <br/><br/>
                    Drag & Drop to Upload File 
                    <br/><br/>
                    OR
                    <br/>
                    <div class="fileup-btn fileup-btn-small"> Select PDF file

                    <input type="file" name="declarations-pdf" class="_3eHqh form-control" id="declarations-pdf" tabindex="-1" accept="application/pdf"/>
                    </div>
                </div>
                  <div id="declarations-pdf-queue"></div>
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
<div id="modalsign" class="modal" data-backdrop="static">
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
                  <div id="sign-pdf-dropzone" class="dropzone">
                  <i class="fa fa-upload" aria-hidden="true"></i>
                  <br/><br/>
                    Drag & Drop to Upload File 
                    <br/><br/>
                    OR
                    <br/>
                    <div class="fileup-btn fileup-btn-small"> Select PDF file
                    <input type="file" name="sign-pdf" class="_3eHqh form-control" id="sign-pdf" tabindex="-1" accept="application/pdf"/>
                    </div>    
                </div>
                  <div id="sign-pdf-queue"></div>
                </div>
              </div>
            </div>
          </div>
          <!--
          <div class="row">
            <div class="col-sm-12">
              <button type="button" class="btn btn-primary pull-right" id="btn-submit" tabindex="13"><i class="fa fa-save"></i> Submit</button>
            </div>
          </div>
          -->
        </form>
      </div>
    </div>
  </div>
</div>