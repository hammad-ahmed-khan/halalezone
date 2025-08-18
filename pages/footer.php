<?php if ($is_login_page == false): ?>
<!-- Fixed Action Buttons Container -->
<div class="action-buttons-container">
    <!-- FAQ Button -->
    <a href="/support" class="action-button faq-button" title="Browse our comprehensive FAQ section to find answers to common questions about raw materials, certification, and system usage.">
        <i class="fa fa-question-circle"></i> <span>FAQs & Help</span>
    </a>

    <!-- Customer Service Button -->
    <div class="action-button customer-service-button" data-toggle="modal" data-target="#customerServiceModal" title="Click here for assistance with all matters related to raw materials, certificate issuance, standards, and more.">
        <i class="fa fa-envelope"></i> <span>Customer Support</span>
    </div>  

    <!-- Report Issue Button -->
    <div class="action-button report-issue-button" data-toggle="modal" data-target="#reportIssueModal" title="Use this button to report any bugs or issues you encounter in the system. We appreciate your help in keeping our system running smoothly.">
        <i class="fa fa-bug"></i> <span>Report Issue</span>
    </div>
</div>
<div class="modal fade" id="customerServiceModal" tabindex="-1" role="dialog" aria-labelledby="customerServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="customerServiceModalLabel">Submit a request <span class="text-primary tclientName"></span></h4>
      </div>
      <div class="modal-body">
        <input type="hidden" name="clientname" id="clientname" value="<?php echo $myuser->userdata['name']," (",$myuser->userdata['id'],")"; ?>" />
        <div class="alert alert-danger" id="customerServiceErrors" style="display: none;"></div>
        <form id="customerServiceForm">

          <div class="form-group">
            <label for="requestType">Request Type</label>
            <select class="form-control" id="requestType">
              <option value="Raw Material">Raw Material</option>
              <option value="Product">Product</option>
              <option value="QM Document">QM Document</option>
              <option value="Certificate Issuance">Certificate Issuance</option>
              <option value="Standards">Standards</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="requestDescription">Description</label>
            <textarea class="form-control" id="requestDescription" rows="5" placeholder="Describe the request in detail."></textarea>
          </div>
          <div class="form-group">
            <label for="currentURL">Current URL</label>
            <input type="text" class="form-control" id="currentURL" value="" disabled>
            <small class="text-muted">This field is automatically populated.</small>
          </div>
        
          <div class="form-group">
            <label for="attachment">Attachment (Screenshot, Excel file, PDF etc.)</label>
            <span class="fileinput-button" id="dropzone233">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload233" type="file" foldertype="addoc233" subfolder="Tickets" infotype="tickets" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="uladdoc233"></ul>
                            <div class="alert-string"></div>         
            </div>          
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" form="customerServiceForm" class="btn btn-primary" id="btnCustomerServiceForm">Submit</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="reportIssueModal" tabindex="-1" role="dialog" aria-labelledby="reportIssueModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="reportIssueModalLabel">Report an Issue</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" name="clientname" id="clientname" value="<?php echo $myuser->userdata['name']," (",$myuser->userdata['id'],")"; ?>" />
        <div class="alert alert-danger" id="reportIssueErrors" style="display: none;"></div>
        <form id="reportIssueForm">
          <div class="form-group">
            <label for="issueType">Issue Type</label>
            <select class="form-control" id="issueType">
              <option value="Bug">Bug</option>
              <option value="Feature Request">Feature Request</option>
              <option value="Usability Issue">Usability Issue</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="issueDescription">Issue Description</label>
            <textarea class="form-control" id="issueDescription" rows="5" placeholder="Describe the issue in detail, including any error messages."></textarea>
          </div>
          <div class="form-group">
            <label for="currentURL">Current URL</label>
            <input type="text" class="form-control" id="currentURL" value="" disabled>
            <small class="text-muted">This field is automatically populated.</small>
          </div>
        
          <div class="form-group">
            <label for="attachment">Attachment (Screenshot, Excel file, PDF etc.)</label>
            <span class="fileinput-button" id="dropzone133">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload133" type="file" foldertype="addoc133" subfolder="Tickets" infotype="tickets" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="uladdoc133"></ul>
                            <div class="alert-string"></div>         
            </div>          
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" form="reportIssueForm" class="btn btn-primary" id="btnReportIssueForm">Submit Report</button>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>
<div class="footer">
    <div class="footer-inner">
        <div class="footer-content">
						<span class="bigger-120">
							&copy;&nbsp;<span class="light-green bolder">Halal e-Zone</span>&nbsp;<?php echo date('Y'); ?> 
						</span>
            &nbsp; &nbsp;<span class="smaller-75 light-grey"><?php echo "ver. 2.".$GLOBALS['appVersion'];?></span>
        </div>
    </div>
</div>
<script src="js/jquery-2.1.4.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tooltipster/4.2.8/js/tooltipster.bundle.min.js" integrity="sha512-ZKNW/Nk1v5trnyKMNuZ6kjL5aCM0kUATbpnWJLPSHFk/5FxnvF9XmpmjGbag6BEgmXiz7rL6o6uJF6InthyTSg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>