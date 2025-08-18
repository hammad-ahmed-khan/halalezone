<input type="hidden" name="extension_filename" id="extension_filename" value="" />
<input type="hidden" name="extension_signed_filename" id="extension_signed_filename" value="" />
<div class="row">
  <div class="col-sm-6">
    <h3>Certificate Extension</h3>
  </div>
  <div class="col-sm-6">
  <?php if($myuser->userdata['isclient'] != "1"):?> 
    <a href="" class="btn btn-success  pull-right" id="btn-start-cc" style="margin-left:10px;"> Start New Certification Cycle </a>
    <a href="" class="btn btn-danger  pull-right" id="btn-upload"><span class="glyphicon glyphicon-upload"></span> Upload </a>
    <?php endif; ?> 
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
<table id="table_extension" class="table table-hover table-striped table-bordered">
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
<div id="modalextension" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Upload Certificate Extension</h4>
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
               
                  <div id="extension-pdf-dropzone" class="dropzone">
                  <i class="fa fa-upload" aria-hidden="true"></i>
                  <br/><br/>
                    Drag & Drop to Upload File 
                    <br/><br/>
                    OR
                    <br/>
                    <div class="fileup-btn fileup-btn-small"> Select PDF file

                    <input type="file" name="extension-pdf" class="_3eHqh form-control" id="extension-pdf" tabindex="-1" accept="application/pdf"/>
                    </div>    
                
                </div>
                  <div id="extension-pdf-queue"></div>
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
<!-- Modal -->
<div class="modal fade" id="newCertificationModal" tabindex="-1" role="dialog" aria-labelledby="newCertificationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newCertificationModalLabel">Start New Certification Cycle</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="errors"></div>
        <form>
          <div class="form-group row">
            <label for="client" class="col-md-4 col-form-label">Client:</label>
            <div class="col-md-8">
              <div class="selClientName"></div>
            </div>
          </div>
          <div class="form-group row">
            <label for="cycleName" class="col-md-4 col-form-label">Cycle Name:</label>
            <div class="col-md-8">
              <input type="text" class="form-control" id="cycleName" name="cycleName" placeholder="Enter cycle name">
            </div>
          </div>
          <!--
          <div class="form-group row">
            <label for="certStartDate" class="col-md-4 col-form-label">Start Date:</label>
            <div class="col-md-8">
              <input type="text" class="form-control" id="certStartDate" name="certStartDate">
            </div>
          </div>
          <div class="form-group row">
            <label for="certEndDate" class="col-md-4 col-form-label">End Date:</label>
            <div class="col-md-8">
              <input type="text" class="form-control" id="certEndDate" name="certEndDate">
            </div>
          </div>
          -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-start-cc">Save</button>
      </div>
    </div>
  </div>
  </div>
