<input type="hidden" name="app_filename" id="app_filename" value="" />
<div class="row">
  <div class="col-sm-6">
    <h2>Initial Application</h2>
  </div>
  <div class="col-sm-6"> 
   <?php if($myuser->userdata['isclient'] != "1"): ?> 
    <a href="#" class="btn btn-success pull-right btn-complete" data-state="declarations" style="margin-left: 10px;"><span class="fa fa-check"></span> Mark Complete</a>
    <?php if($myuser->userdata['isclient'] != "2"): ?> 
    <a href="" class="btn btn-warning pull-right btn-skip" data-state="declarations" style="margin-left: 10px;"><span class="fa fa-fast-forward"></span> Skip Step</a>
    <?php endif; ?> 
    <a href="" class="btn btn-danger pull-right" id="btn-upload" style="margin-left: 10px;"><span class="glyphicon glyphicon-upload"></span> Upload</a>
    <?php endif; ?> 
    <?php if($myuser->userdata['isclient'] == "1"): ?> 
      <a href="" class="btn btn-danger pull-right" id="btn-upload" style="margin-left: 10px;"><span class="glyphicon glyphicon-upload"></span> Upload</a>
      <a href="/files/docs/F0422 HQC Application Form.pdf" download class="btn btn-primary pull-right" style="margin-left: 10px;"><span class="glyphicon glyphicon-download"></span> Download </a>       
    <?php endif; ?> 
  </div>
</div>
<hr style="margin-top:0;"/>
<div class="row" style="margin-bottom:5px;">
  <div class="col-md-6"> 
    <!--<h4 style="margin-top: 0">Filter</h4>-->
    <div class="input-group my-group search-box">
      <input class="form-control" name="s" id="s" style="width:250px;">
      <span class="input-group-btn pull-left">
      <!--
      <button class="btn btn-primary" id="btn-search" type="button" style="padding: 2px 10px;"><span class="glyphicon glyphicon-search" aria-hidden="true"> </span> </button>-->
      </span> </div>
  </div>
  <div class="col-md-6">
  <?php if($myuser->userdata['superadmin'] == "1"):?> 
    <label class="right">
      <input id="filter-actions-deleted" class="ace ace-switch ace-switch-4" type="checkbox">
      <span class="lbl">&nbsp;&nbsp;Show deleted documents</span> </label>
      <?php endif; ?> 
  </div>
</div>
<table id="table_app" class="table table-hover table-striped table-bordered">
  <thead>
    <tr class="tableheader">
      <th  >File Name</th>
      <th style="width:21%">Uploaded By</th>
      <th style="width:21%">Upload Date</th>
      <!--<th style="width:25%">Comments</th>-->
      <th  style="width:125px;" class="no-sort"></th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
<div id="modalapp" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Upload Application</h4>
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
                        
                  <div id="app-pdf-dropzone" class="dropzone">
                  <i class="fa fa-upload" aria-hidden="true"></i>
                  <br/><br/>
                    Drag & Drop to Upload File 
                    <br/><br/>
                    OR
                    <br/>
                    <div class="fileup-btn fileup-btn-small"> Select PDF file

                    <input type="file" name="app-pdf" class="_3eHqh form-control" id="app-pdf" tabindex="-1" accept="application/pdf"/>
                    </div>    
                
                </div>
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
