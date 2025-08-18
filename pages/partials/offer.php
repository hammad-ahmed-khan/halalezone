<input type="hidden" name="offer_filename" id="offer_filename" value="" />
<input type="hidden" name="offer_signed_filename" id="offer_signed_filename" value="" />

<form action="/preview/offer.php" method="post" target="_blank" id="frmPreview">
<input type="hidden" name="idclient" id="previewIdClient" value="" />
<input type="hidden" name="idapp" id="previewIdApp" value="" />
</form> 
<div class="row">
  <div class="col-sm-4">
    <h2>Offer</h2>
  </div>
  <div class="col-sm-8"> 
  <?php if($myuser->userdata['isclient'] != "1"):?>     
  	<a href="" class="btn btn-info pull-right" id="btn-send" style="width:150px; margin-left:10px;"><span class="fa fa-send"></span> Send </a> 
  	<a href="" class="btn btn-warning pull-right" id="btn-preview" style="width:150px; margin-left:10px;"><span class="fa fa-eye"></span> Preview </a> 
	<?php endif; ?>
  <?php if($myuser->userdata['isclient'] != "1"):?> 
    <a href="#" class="btn btn-success pull-right btn-complete" data-state="soffer" style="width:150px; margin-left:10px;"><span class="fa fa-check"></span> Mark Complete</a>
    <?php if($myuser->userdata['isclient'] != "2"):?> 
    <a href="" class="btn btn-warning pull-right btn-skip" data-state="soffer" style="margin-left: 10px;"><span class="fa fa-fast-forward"></span> Skip Step</a>    
    <?php endif; ?> 
    <a href="" class="btn btn-danger pull-right" id="btn-upload-offer" style="margin-left: 10px;"><span class="glyphicon glyphicon-upload"></span> Upload</a>
    <?php endif; ?> 
</div>
</div>
<hr style="margin-top:0;"/>
<div class="row" style="margin-bottom:10px;">
  <div class="col-md-6"> 
    <!--<h4 style="margin-top: 0">Filter</h4>-->
    <div class="input-group my-group search-box">
      <input class="form-control" name="s" id="s" style="width:250px;">
      <span class="input-group-btn pull-left">
      <button class="btn btn-primary" id="btn-search" type="button" style="padding: 2px 10px;"><span class="glyphicon glyphicon-search" aria-hidden="true"> </span> </button>
      </span> </div>
  </div>
  <div class="col-md-6">
  </div>
</div> 
<?php if($myuser->userdata['isclient'] != "1"):?> 
<a href="" class="btn btn-primary pull-right" id="btn-upload" style="margin-top:-15px;"><span class="glyphicon glyphicon-plus"></span> Add Service </a> <a href="" class="btn btn-success pull-right" id="btn-manage" style="margin-top:-15px; margin-right:15px;"><span class="glyphicon glyphicon-plus"></span> Manage Services </a> 
<div class="pull-right offer-setting" style="margin-right: 15px;">
  Office issuing the offer: 
  <select id="offerOffice" name="offerOffice">
    <option value="AT">AT</option>
    <option value="HU">HU</option>
  </select>
</div>  
<div class="pull-right offer-setting" style="margin-right: 15px;">
Ingredients Limit: 
  <input id="ingredientsLimit" name="ingredientsLimit" type="text" style="width:50px;"/>
</div>  
<div class="pull-right offer-setting" style="margin-right: 15px;">
Products Limit : 
  <input id="productsLimit" name="productsLimit" type="text" style="width:50px;"/>
</div>  
<div class="alert alert-danger" id="errors" style="display:none;"></div>
<table id="table_service" class="table table-hover table-striped table-bordered">
  <thead>
    <tr class="tableheader">
      <th style="">Service</th>
      <th style="">Fee</th>
      <th style="width:125px;" class="no-sort"></th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>

 <h2>Sent Offers</h2>
	<?php endif; ?>

  <div class="row" style="margin-bottom:10px; display1:none;">
  <!--
  <div class="col-md-6"> 
     <div class="input-group my-group search-box">
      <input class="form-control" name="s" id="s" style="width:250px;">
      <span class="input-group-btn pull-left">
      <button class="btn btn-primary" id="btn-search" type="button" style="padding: 2px 10px;"><span class="glyphicon glyphicon-search" aria-hidden="true"> </span> </button>
      </span> </div>
  </div>
  -->
  <?php if($myuser->userdata['superadmin'] == "1"):?> 
  <div class="col-md-12">
    <label class="right">
      <input id="filter-actions-deleted" class="ace ace-switch ace-switch-4" type="checkbox">
      <span class="lbl">&nbsp;&nbsp;Show deleted documents</span> </label>
  </div>
  <?php endif; ?> 
</div>

<table id="table_offer" class="table table-hover table-striped table-bordered">
  <thead>
    <tr class="tableheader">
      <th style="width:100px;">ID</th>
      <th >File Name</th>
      <th style="width:21%">Sent By</th>
      <th style="width:21%">Date Sent</th>
      <!--<th style="width:25%">Comments</th>-->
      <th  style="width:125px;" class="no-sort"></th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
<div id="modalmanage" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Manage Services</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors" style="display:none;"></div>
        <form id="frmAddManage" data-toggle="validator">
          <div class="row">
            <div class="col-md-10">
              <textarea class="form-control" name="addservice" id="addservice" placeholder="Description"></textarea>
            </div>
            <div class="col-md-2 text-right">
              <button class="btn btn-primary" style="padding: 2px 15px; height:auto;" id="btnadd-manage">Add</button>
            </div>
          </div>
        </form>
        <form id="frmManage" data-toggle="validator">
          <table id="table_manage" class="table table-hover table-striped table-bordered">
            <thead>
              <tr class="tableheader">
                <th style="">Service</th>
                <th style="width:75px;" class="no-sort"></th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>
<div id="modalservice" class="modal" data-backdrop="static">
  <input type="hidden" name="offerId" id="offerId" value="" />
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Add Service</h4>
        <h4 class="modal-title title-edit" style="display:none;">Edit Service</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors" style="display:none;"></div>
        <form id="frmOffer" data-toggle="validator">
          <input type="hidden" name="ID" id="ID" value="" />
          <div class="form-horizontal row">
            <div class="col-md-12">
              <div class="row form-group text-left">
                <label class="col-sm-12 control-label" style="text-align:left;">Service <span class="text-danger">*</span></label>
                <div class="col-sm-12">
                    <select id="Service" class="selectpicker form-control"  style="width:100%;" data-live-search="true" title="">
                      <option value=""></option>
                      <option value="addNewService">+ Add New</option>
                    </select>
                    <textarea class="form-control hidden" name="NewService" id="NewService" style="height:150px;"  tabindex="8"></textarea>
                    <textarea class="form-control hidden" name="EditService" id="EditService" style="height:150px;"  tabindex="8"></textarea>
                    <div class="alert alert-warning hidden" id="ServiceInfo">You can include <strong>[prodnumber]</strong> and <strong>[ingrednumber]</strong> in the service description. These placeholders will be substituted with the real limits for products and ingredients in the offer document.</div>
                </div>
              </div>
            </div>
            <div class="col-md-12">
              <div class="row form-group text-left">
                <label class="col-sm-12 control-label" style="text-align:left;">Fee <span class="text-danger">*</span></label>
                <div class="col-sm-12">
                    <input type="text" name="Fee" id="Fee" class="form-control" value="" />
                </div>
              </div>
            </div>            
          </div>        
          <div class="row">
            <div class="col-sm-12">
              <button type="button" class="btn btn-primary pull-right" id="btn-submit" tabindex="13"><i class="fa fa-save"></i> Save</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div id="modaloffer" class="modal" data-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Upload Offer</h4>
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
             
                  <div id="offer-pdf-dropzone" class="dropzone">
                  <i class="fa fa-upload" aria-hidden="true"></i>
                  <br/><br/>
                    Drag & Drop to Upload File 
                    <br/><br/>
                    OR
                    <br/>
                    <div class="fileup-btn fileup-btn-small"> Select PDF file

                    <input type="file" name="offer-pdf" class="_3eHqh form-control" id="offer-pdf" tabindex="-1" accept="application/pdf"/>
                    </div>    
                </div>
                  <div id="offer-pdf-queue"></div>
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