<input type="hidden" name="additional_items_filename" id="additional_items_filename" value="" />
<input type="hidden" name="additional_items_signed_filename" id="additional_items_signed_filename" value="" />
<div class="row">
  <div class="col-sm-6">
    <h3>Additional Items Application</h3>
  </div>
  <div class="col-sm-6">
    <?php if($myuser->userdata['isclient'] != "1"):?> 
    <a href="#" class="btn btn-success pull-right btn-complete" data-state="popai" style="width:150px; margin-left:10px;"><span class="fa fa-check"></span> Mark Complete</a> 
      <?php if($myuser->userdata['isclient'] != "2"):?> 
        <a href="" class="btn btn-warning pull-right btn-skip" data-state="popai" style="margin-left: 10px;"><span class="fa fa-fast-forward"></span> Skip Step</a>    
      <?php endif; ?> 
    <?php endif; ?> 
  </div>
</div>
<?php if($myuser->userdata['isclient']):?> 
<div class="alert alert-info ui-jqgrid ">
      Go to the <a href="/products" style="text-decoration:underline" target="_blank">Products</a> menu select desired products from grid and press the <span class="ui-pg-div"><span class="ui-icon ace-icon fa fa-file-pdf-o"></span></span> button to generate additonal items pdf. 
</div>
<?php endif; ?>
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
<table id="table_additional_items" class="table table-hover table-striped table-bordered">
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