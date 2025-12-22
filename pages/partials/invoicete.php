<input type="hidden" name="invoicete_filename" id="invoicete_filename" value="" />
<input type="hidden" name="invoicete_signed_filename" id="invoicete_signed_filename" value="" />
<div class="row">
  <div class="col-sm-6">
    <h3>Upload invoice for travel expenses</h3>
  </div>
  <div class="col-sm-6">
  <?php if($myuser->userdata['isclient'] != "1"):?> 
    <a href="#" class="btn btn-success pull-right btn-complete" data-state="popinv" style="width:150px; margin-left:10px;"><span class="fa fa-check"></span> Mark Complete</a> 
    <?php if($myuser->userdata['isclient'] != "2"):?> 
    <a href="" class="btn btn-warning pull-right btn-skip" data-state="popinv" style="margin-left: 10px;"><span class="fa fa-fast-forward"></span> Skip Step</a>    
    <?php endif; ?> 
    <?php endif; ?> 
    <a href="" class="btn btn-danger  pull-right" id="btn-upload"><span class="glyphicon glyphicon-upload"></span> Upload </a>
  </div>
   
</div>
<hr style="margin-top:0;"/>
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

<table id="table_invoicete" class="table table-hover table-striped table-bordered">
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
<div id="modalinvoicete" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Upload invoice for travel expenses</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors" style="display:none;"></div>
        <form id="frmOffer" data-toggle="validator">
          <input type="hidden" name="ID" id="ID" value="" />
          <div class="form-horizontal row">
            <div class="col-md-12">
              <div class="row form-group text-left">
                <div style="padding:0 15px;">
                 
                  <div id="invoicete-pdf-dropzone" class="dropzone">
                  <i class="fa fa-upload" aria-hidden="true"></i>
                  <br/><br/>
                    Drag & Drop to Upload File 
                    <br/><br/>
                    OR
                    <br/>
                    <div class="fileup-btn fileup-btn-small"> Select PDF file

                    <input type="file" name="invoicete-pdf" class="_3eHqh form-control" id="invoicete-pdf" tabindex="-1" accept="application/pdf"/>
                    </div>  
                </div>
                  <div id="invoicete-pdf-queue"></div>
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