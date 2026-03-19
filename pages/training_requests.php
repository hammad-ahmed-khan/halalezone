<?php
@session_start();
include_once 'config/config.php';
include_once 'classes/users.php';
include_once 'includes/func.php';

$myuser = cuser::singleton();
$myuser->getUserData();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php'); ?>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <title>Training Requests - Halal e-Zone</title>
    <style>
        .signature-preview {
            max-width: 200px;
            max-height: 100px;
            border: 1px solid #ddd;
            padding: 5px;
            background: white;
        }
        .signature-pad {
            border: 2px solid #ddd;
            border-radius: 4px;
            cursor: crosshair;
            background: #fff;
        }
        .auditor-panel {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .selected-row {
            background-color: #d4edda !important;
        }
        .auditor-panel h6 {
            margin: 0 0 15px 0;
            font-weight: bold;
            color: #495057;
        }
        .auditor-panel .form-control {
            height: 34px;
        }
        .auditor-panel .btn {
            height: 34px;
            line-height: 1.4;
        }
    </style>
</head>
<body class="no-skin">
<?php include_once('pages/navigation.php'); ?>

<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="page-header">
                            <h1>
                                <i class="ace-icon fa fa-graduation-cap"></i>
                                Training Requests Management
                            </h1>
                        </div>

                        <!-- Auditor Assignment Panel - Top Position for Admins -->
                        <?php if($myuser->userdata['isclient'] == '0'): ?>
                        <div class="auditor-panel">
                            <h6>
                                <i class="ace-icon fa fa-user-tie"></i> Assign Auditor to Training Request
                            </h6>
                            <div class="row">
                                <div class="col-sm-6">
                                    <label>Selected Training Request:</label>
                                    <input type="text" id="selectedTrainingInfo" class="form-control" readonly 
                                           placeholder="Click on a row in the table below to select..." />
                                    <input type="hidden" id="selectedTrainingId" value="" />
                                </div>
                                <div class="col-sm-3">
                                    <label>Assign Auditor:</label>
                                    <select id="selectAuditor" class="form-control" disabled>
                                        <option value="">-- Select Auditor --</option>
                                    </select>
                                </div>
                                <div class="col-sm-3" style="padding-top: 25px;">
                                    <button type="button" id="btnAssignAuditor" class="btn btn-primary btn-block" disabled>
                                        <i class="ace-icon fa fa-check"></i> Assign
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-xs-12">
                                <button type="button" class="btn btn-success" id="btnAdd">
                                    <i class="ace-icon fa fa-plus"></i> New Training Request
                                </button>
                                <div class="space-10"></div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="trainingTable" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr class="tableheader">
                                        <th>ID</th>
                                        <th>Company Name</th>
                                        <th>Contact Person</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Participants</th>
                                        <th>Type</th>
                                        <th>Cost</th>
                                        <th>Assigned Auditor</th>
                                        <th>Training Request Form</th>
                                        <th>Attendance List</th>
                                        <th>Customer Feedback Form</th>
                                        <th>Attendance Certificates </th>
                                        <th>Created</th>
                                        <th style="width:110px;" class="no-sort"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="modalTraining" class="modal" data-backdrop="static">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">
          <i class="ace-icon fa fa-plus-circle"></i> Add Training Request
        </h4>
        <h4 class="modal-title title-edit" style="display:none;">
          <i class="ace-icon fa fa-edit"></i> Edit Training Request
        </h4>
      </div>

      <!-- FORM starts here and wraps body + footer -->
      <form id="frmTraining" class="modern-form">
        <input type="hidden" name="id" id="id" value="" />

        <div class="modal-body">
          <div class="alert alert-danger" id="errors" style="display:none;"></div>

          <!-- Company Information Section -->
          <div class="form-section">
            <div class="section-header">
              <i class="ace-icon fa fa-building text-primary"></i>
              <span class="section-title">Company Information</span>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Company Name <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-lg" id="company_name" name="company_name" maxlength="255" required />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Contact Person <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-lg" id="contact_person" name="contact_person" maxlength="255" required />
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Address <span class="required">*</span></label>
              <textarea class="form-control" id="address" name="address" rows="3" required placeholder="Enter complete company address..."></textarea>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Phone Number <span class="required">*</span></label>
                  <input type="tel" class="form-control form-control-lg" id="phone_number" name="phone_number" maxlength="50" required />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Email Address <span class="required">*</span></label>
                  <input type="email" class="form-control form-control-lg" id="email_address" name="email_address" maxlength="255" required />
                </div>
              </div>
            </div>
          </div>

          <!-- Training Configuration Section -->
          <div class="form-section">
            <div class="section-header">
              <i class="ace-icon fa fa-cogs text-success"></i>
              <span class="section-title">Training Configuration</span>
            </div>

            <div class="form-group">
              <label class="form-label">Training Languages <span class="required">*</span></label>
              <div class="checkbox-group">
                <div class="row">
                  <div class="col-md-3 col-sm-6">
                    <label class="checkbox-styled">
                      <input type="checkbox" name="language[]" value="English">
                      <span class="checkmark"></span>
                      English
                    </label>
                  </div>
                  <div class="col-md-3 col-sm-6">
                    <label class="checkbox-styled">
                      <input type="checkbox" name="language[]" value="French">
                      <span class="checkmark"></span>
                      French
                    </label>
                  </div>
                  <div class="col-md-3 col-sm-6">
                    <label class="checkbox-styled">
                      <input type="checkbox" name="language[]" value="German">
                      <span class="checkmark"></span>
                      German
                    </label>
                  </div>
                  <div class="col-md-3 col-sm-6">
                    <label class="checkbox-styled">
                      <input type="checkbox" name="language[]" value="Italian">
                      <span class="checkmark"></span>
                      Italian
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">
                Other Language 
                <span class="text-muted">(Additional Cost: +€399)</span>
              </label>
              <input type="text" class="form-control form-control-lg" id="other_language" name="other_language" maxlength="100"
                     placeholder="e.g., Serbian, Turkish, Hungarian..." />
            </div>

            <div class="form-group">
              <label class="form-label">Preferred Training Dates</label>
              <div class="row">
                <div class="col-md-4">
                  <label class="sub-label">Date 1 <span class="required">*</span></label>
                  <input type="date" class="form-control form-control-lg" id="preferred_date_1" name="preferred_date_1" required />
                </div>
                <div class="col-md-4">
                  <label class="sub-label">Date 2 (Alternative)</label>
                  <input type="date" class="form-control form-control-lg" id="preferred_date_2" name="preferred_date_2" />
                </div>
                <div class="col-md-4">
                  <label class="sub-label">Date 3 (Alternative)</label>
                  <input type="date" class="form-control form-control-lg" id="preferred_date_3" name="preferred_date_3" />
                </div>
              </div>
            </div>
          </div>

          <!-- Pricing & Participants Section -->
          <div class="form-section">
            <div class="section-header">
              <i class="ace-icon fa fa-users text-info"></i>
              <span class="section-title">Participants & Pricing</span>
            </div>

            <div class="form-group">
              <label class="form-label">Participant Tier <span class="required">*</span></label>
              <div class="radio-group">
                <div class="row">
                  <div class="col-md-3 col-sm-6">
                    <label class="radio-styled">
                      <input type="radio" name="participantTier" value="1|499">
                      <span class="radio-mark"></span>
                      <span class="radio-content">
                        <strong>1 Participant</strong>
                        <span class="price">€499</span>
                      </span>
                    </label>
                  </div>
                  <div class="col-md-3 col-sm-6">
                    <label class="radio-styled">
                      <input type="radio" name="participantTier" value="3|1190">
                      <span class="radio-mark"></span>
                      <span class="radio-content">
                        <strong>2-3 Participants</strong>
                        <span class="price">€1,190</span>
                      </span>
                    </label>
                  </div>
                  <div class="col-md-3 col-sm-6">
                    <label class="radio-styled">
                      <input type="radio" name="participantTier" value="6|1390">
                      <span class="radio-mark"></span>
                      <span class="radio-content">
                        <strong>4-6 Participants</strong>
                        <span class="price">€1,390</span>
                      </span>
                    </label>
                  </div>
                  <div class="col-md-3 col-sm-6">
                    <label class="radio-styled">
                      <input type="radio" name="participantTier" value="10|1690">
                      <span class="radio-mark"></span>
                      <span class="radio-content">
                        <strong>7-10 Participants</strong>
                        <span class="price">€1,690</span>
                      </span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Number of Participants <span class="required">*</span></label>
                  <input type="number" class="form-control form-control-lg" id="num_participants" name="num_participants" min="1" required />
                  <small class="form-text text-muted">
                    <i class="ace-icon fa fa-info-circle"></i>
                    For 11+ participants: €99 per additional person
                  </small>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Training Type <span class="required">*</span></label>
                  <div class="training-type-radios" style="margin-top: 8px;">
                    <label class="training-type-option">
                      <input type="radio" name="training_type" value="Online">
                      <span class="radio-custom"></span>
                      <span class="radio-text">Online Training</span>
                    </label>
                    <label class="training-type-option">
                      <input type="radio" name="training_type" value="On-site">
                      <span class="radio-custom"></span>
                      <span class="radio-text">On-site Training</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <div class="alert alert-info pricing-info">
              <h6><i class="ace-icon fa fa-info-circle"></i> Pricing Information</h6>
              <ul class="pricing-notes">
                <li>On-site training available at the same base cost with additional travel and accommodation charges</li>
                <li>All pricing is subject to confirmation and may vary based on specific requirements</li>
              </ul>
            </div>
          </div>

          <!-- Acceptance & Authorization Section -->
          <div class="form-section">
            <div class="section-header">
              <i class="ace-icon fa fa-file-signature text-warning"></i>
              <span class="section-title">Authorization & Signature</span>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Company <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-lg" id="acceptance_company" name="acceptance_company" maxlength="255" required />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Name and Position <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-lg" id="acceptance_name_position" name="acceptance_name_position" maxlength="255" required />
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Place, Date <span class="required">*</span></label>
                  <input type="text" class="form-control form-control-lg" id="acceptance_place_date" name="acceptance_place_date" maxlength="255" required />
                </div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Digital Signature <span class="required">*</span></label>
              <div class="signature-container">
                <div class="signature-wrapper">
                  <canvas id="signaturePad" class="signature-pad" width="600" height="120"></canvas>
                  
                </div>
                <div class="signature-controls">
                  <button type="button" class="btn btn-sm btn-warning" id="clearSignature">
                    <i class="ace-icon fa fa-eraser"></i> Clear Signature
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div> <!-- /.modal-body -->

        <!-- Modal Footer -->
        <div class="modal-footer modern-footer">
          <button type="submit" class="btn btn-success btn-lg" id="btnsave">
            <i class="ace-icon fa fa-check"></i> Save Training Request
          </button>
          <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">
            <i class="ace-icon fa fa-times"></i> Cancel
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- View Modal -->
<div id="modalView" class="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">Training Request Details</h4>
            </div>
            <div class="modal-body" id="viewModalBody">
                <div class="text-center">
                    <i class="ace-icon fa fa-spinner fa-spin fa-3x"></i>
                    <p>Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery and Scripts -->
<script src="js/jquery-2.1.4.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="js/training_requests.js?v=<?php echo rand(); ?>"></script>

<style>
/* Modern Modal Styling - Reduced Spacing */
.modal-xl {
    width: 95%;
    max-width: 1200px;
}

.modern-form {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.modal-body {
    padding: 20px 30px;
    max-height: calc(100vh - 140px);
    overflow-y: auto;
}

/* Form Sections - Reduced spacing */
.form-section {
    margin-bottom: 25px;
    border: 1px solid #e8e8e8;
    border-radius: 6px;
    padding: 18px;
    background: #fafafa;
}

.form-section:last-child {
    margin-bottom: 15px;
}

.section-header {
    display: flex;
    align-items: center;
    margin-bottom: 18px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e8e8e8;
}

.section-header i {
    font-size: 18px;
    margin-right: 10px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Form Controls - Reduced spacing */
.form-group {
    margin-bottom: 18px;
}

.form-label {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 6px;
    color: #555;
    display: block;
}

.sub-label {
    font-weight: 500;
    font-size: 12px;
    margin-bottom: 4px;
    color: #666;
    display: block;
}

.form-control-lg {
    height: 38px;
    padding: 8px 12px;
    font-size: 13px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-control:focus {
    border-color: #5a8a3c;
    box-shadow: 0 0 0 2px rgba(90, 138, 60, 0.1);
}

textarea.form-control {
    height: auto;
    padding: 10px 12px;
    resize: vertical;
}

.required {
    color: #e74c3c;
}

.form-text {
    margin-top: 5px;
}

/* Custom Checkboxes - Reduced spacing */
.checkbox-group {
    margin-top: 8px;
}

.checkbox-styled {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    margin-bottom: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.checkbox-styled:hover {
    border-color: #5a8a3c;
    background: #f8f9fa;
}

.checkbox-styled input[type="checkbox"] {
    display: none;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 3px;
    margin-right: 8px;
    position: relative;
}

.checkbox-styled input:checked + .checkmark {
    background: #5a8a3c;
    border-color: #5a8a3c;
}

.checkbox-styled input:checked + .checkmark:after {
    content: "✓";
    position: absolute;
    color: white;
    font-size: 12px;
    font-weight: bold;
    left: 2px;
    top: -2px;
}

/* Custom Radio Buttons - Reduced spacing */
.radio-group {
    margin-top: 8px;
}

.radio-styled {
    display: flex;
    align-items: flex-start;
    padding: 12px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    background: white;
}

.radio-styled:hover {
    border-color: #5a8a3c;
    background: #f8f9fa;
}

.radio-styled input[type="radio"] {
    display: none;
}

.radio-mark {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 50%;
    margin-right: 12px;
    position: relative;
    flex-shrink: 0;
    margin-top: 2px;
}

.radio-styled input:checked + .radio-mark {
    border-color: #5a8a3c;
}

.radio-styled input:checked + .radio-mark:after {
    content: "";
    position: absolute;
    width: 8px;
    height: 8px;
    background: #5a8a3c;
    border-radius: 50%;
    left: 3px;
    top: 3px;
}

.radio-content {
    display: flex;
    flex-direction: column;
}

.radio-content strong {
    color: #333;
    margin-bottom: 2px;
}

.price {
    color: #5a8a3c;
    font-weight: 600;
    font-size: 14px;
}

/* Training Type Radio Buttons - Simple Working Version */
.training-type-radios {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.training-type-option {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 10px 15px;
    border: 2px solid #ddd;
    border-radius: 6px;
    background: white;
    transition: all 0.2s;
    min-width: 140px;
}

.training-type-option:hover {
    border-color: #5a8a3c;
    background: #f8f9fa;
}

.training-type-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.radio-custom {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 50%;
    margin-right: 10px;
    position: relative;
    transition: all 0.2s;
}

.training-type-option input[type="radio"]:checked + .radio-custom {
    border-color: #5a8a3c;
    background: white;
}

.training-type-option input[type="radio"]:checked + .radio-custom:after {
    content: "";
    position: absolute;
    width: 10px;
    height: 10px;
    background: #5a8a3c;
    border-radius: 50%;
    left: 2px;
    top: 2px;
}

.training-type-option input[type="radio"]:checked ~ .radio-text {
    color: #5a8a3c;
    font-weight: 600;
}

.training-type-option input[type="radio"]:checked {
    border-color: #5a8a3c;
}

.training-type-option:has(input[type="radio"]:checked) {
    border-color: #5a8a3c;
    background: #f0f8f0;
}

.radio-text {
    font-size: 14px;
    transition: all 0.2s;
}

/* Inline Radio Buttons - Fixed */
.radio-group-inline {
    display: flex;
    gap: 25px;
}

.radio-styled-inline {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    transition: all 0.2s;
}

.radio-styled-inline:hover {
    border-color: #5a8a3c;
    background: #f8f9fa;
}

.radio-styled-inline input[type="radio"] {
    display: none;
}

.radio-styled-inline .radio-mark {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 50%;
    margin-right: 8px;
    position: relative;
    flex-shrink: 0;
}

.radio-styled-inline input:checked + .radio-mark {
    border-color: #5a8a3c;
}

.radio-styled-inline input:checked + .radio-mark:after {
    content: "";
    position: absolute;
    width: 8px;
    height: 8px;
    background: #5a8a3c;
    border-radius: 50%;
    left: 3px;
    top: 3px;
}

.radio-styled-inline input:checked ~ span:not(.radio-mark) {
    color: #5a8a3c;
    font-weight: 600;
}

/* Pricing Info - Reduced spacing */
.pricing-info {
    border: none;
    background: #e8f4f8;
    border-left: 4px solid #5bc0de;
    padding: 12px;
    margin-top: 12px;
}

.pricing-info h6 {
    margin-bottom: 8px;
    color: #31708f;
}

.pricing-notes {
    margin-bottom: 0;
    padding-left: 18px;
}

.pricing-notes li {
    margin-bottom: 4px;
    color: #31708f;
}

/* Signature Styling - Reduced height */
.signature-container {
    position: relative;
}

.signature-wrapper {
    position: relative;
    border: 2px dashed #ddd;
    border-radius: 6px;
    background: white;
    overflow: hidden;
}

.signature-pad {
    display: block;
    width: 100%;
    border: none;
    cursor: crosshair;
    height: 120px;
}

.signature-placeholder {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    pointer-events: none;
    z-index: 1;
}

.signature-placeholder.hidden {
    display: none;
}

.signature-controls {
    margin-top: 8px;
    text-align: right;
}

/* Footer - Reduced spacing */
.modern-footer {
    padding: 15px 30px;
    border-top: 1px solid #e8e8e8;
    background: #f8f9fa;
}

.modern-footer .btn {
    min-width: 130px;
    margin-left: 10px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-xl {
        width: 98%;
        margin: 10px auto;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .form-section {
        padding: 15px;
    }
    
    .radio-group-inline {
        flex-direction: column;
        gap: 12px;
    }
    
    .modern-footer {
        padding: 12px 15px;
    }
    
    .modern-footer .btn {
        min-width: auto;
        width: 100%;
        margin: 5px 0;
    }
}
</style>

</body>
</html>