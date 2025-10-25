<?php
@session_start();
include_once 'config/config.php';
include_once 'classes/users.php';
include_once 'includes/func.php';

$myuser = cuser::singleton();
$myuser->getUserData();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php'); ?>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
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
        .modal-lg {
            width: 900px;
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title title-add">Add Training Request</h4>
                <h4 class="modal-title title-edit" style="display:none;">Edit Training Request</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="errors" style="display:none;"></div>
                <form id="frmTraining" data-toggle="validator">
                    <input type="hidden" name="id" id="id" value="" />
                    
                    <!-- Company Details Section -->
                    <h5 class="header green">Company Details</h5>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Company Name <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="company_name" maxlength="255" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Address <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="address" rows="2" required></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Contact Person <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="contact_person" maxlength="255" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Phone Number <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="tel" class="form-control" id="phone_number" maxlength="50" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Email Address <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" id="email_address" maxlength="255" required />
                            </div>
                        </div>
                    </div>

                    <!-- Training Details Section -->
                    <h5 class="header green">Training Details</h5>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Languages <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="language" value="English"> English
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="language" value="French"> French
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="language" value="German"> German
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="language" value="Italian"> Italian
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Other Language</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="other_language" maxlength="100" 
                                       placeholder="e.g., Serbian, Turkish, Hungarian (€399 additional fee)" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Preferred Date 1 <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="preferred_date_1" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Preferred Date 2</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="preferred_date_2" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Preferred Date 3</label>
                            <div class="col-sm-9">
                                <input type="date" class="form-control" id="preferred_date_3" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Participant Tier <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <label class="radio-inline">
                                    <input type="radio" name="participantTier" value="1|499" required> 1 participant (€499)
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="participantTier" value="3|1190"> Up to 3 (€1,190)
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="participantTier" value="6|1390"> Up to 6 (€1,390)
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="participantTier" value="10|1690"> Up to 10 (€1,690)
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Number of Participants <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" id="num_participants" min="1" required />
                                <span class="help-block">From the 11th participant onward, the fee is €99 per person</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Training Type <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <label class="radio-inline">
                                    <input type="radio" name="training_type" value="Online" required> Online
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="training_type" value="On-site"> On-site
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Acceptance Section -->
                    <h5 class="header green">Acceptance of Terms</h5>
                    <div class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Company <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="acceptance_company" maxlength="255" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Name and Position <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="acceptance_name_position" maxlength="255" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Place, Date <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="acceptance_place_date" maxlength="255" required />
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Signature <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                <canvas id="signaturePad" class="signature-pad" width="600" height="200"></canvas>
                                <div style="margin-top: 10px;">
                                    <button type="button" class="btn btn-sm btn-warning" id="clearSignature">
                                        <i class="ace-icon fa fa-eraser"></i> Clear Signature
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-success" id="btnsave">
                                <i class="ace-icon fa fa-check"></i> Save Training Request
                            </button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                <i class="ace-icon fa fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
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
<script src="js/training.js?v=<?php echo rand(); ?>"></script>

</body>
</html>