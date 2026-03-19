<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <title>Activity Records - Halal Digital</title>
    <style>
        .blockUI h1 {
            font-size: 18px;
            margin: 10px auto;
        }
        td.changed {
            background:greenyellow;
        }
        tr.highlighted-conformed .fa-flag {
            display: none !important;
        }
        .mandatory-field::after {
            content: " *";
            color: red;
            font-weight: bold;
        }
        .mandatory-field {
            font-weight: bold;
        }
        .form-note {
            font-size: 11px;
            color: #666;
            font-style: italic;
            margin-top: 15px;
        }
        /* Toggle switch styles */
        .paid-toggle {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
            cursor: pointer;
        }
        .paid-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .paid-toggle .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #d9534f;
            transition: .3s;
            border-radius: 24px;
        }
        .paid-toggle .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }
        .paid-toggle input:checked + .slider {
            background-color: #5cb85c;
        }
        .paid-toggle input:checked + .slider:before {
            transform: translateX(26px);
        }
        .paid-toggle .toggle-label {
            position: absolute;
            font-size: 10px;
            font-weight: bold;
            color: white;
            top: 5px;
        }
        .paid-toggle .toggle-label.yes {
            left: 6px;
            display: none;
        }
        .paid-toggle .toggle-label.no {
            right: 6px;
        }
        .paid-toggle input:checked + .slider .toggle-label.yes {
            display: block;
        }
        .paid-toggle input:checked + .slider .toggle-label.no {
            display: none;
        }
        #service-type-other-container {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<?php include_once('pages/navigation.php');
try {
    $db = acsessDb :: singleton();
    $dbo =  $db->connect();
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}

$myuser = cuser::singleton();
$myuser->getUserData();
$isAdmin = $myuser->userdata['isclient'] == "0";
$isAuditor = $myuser->userdata['isclient'] == "2";
$isClient = $myuser->userdata['isclient'] == "1";
?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12"> 
                        <?php if ($isAdmin): ?>
                        <div class="form-inline">
                            <div class="form-group">
                                <label>Auditors &nbsp;&nbsp;
                                <select class="form-control auditorslist" id="activity-auditorid">
                                    <option value="-1">All Auditors</option>
                                    <?php
                                    $query = "SELECT id, name FROM tusers WHERE isclient=2 AND deleted=0 ORDER BY name";
                                    $stmt = $dbo->prepare($query);
                                    $stmt->execute();
                                    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($companies as $company) {
                                        echo '<option value="'.$company["id"].'">'.$company["name"].'</option>';
                                    }
                                    ?>
                                </select>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($isAuditor): ?>              
                            <input type="hidden" id="activity-auditorid" value=<?php echo $_SESSION['halal']['id']; ?> />
                        <?php endif;?>
                        
                        <div class="clearfix">
                            <div class="pull-right tableTools-container"></div>
                        </div>
                    
                        <div>
                            <table id="activityGrid"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="activityModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="activityModal-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="activityModal-label">Add Activity</h4>
            </div>
            <div class="modal-body">
                <form id="activity-form" class="form-horizontal">
                    <input type="hidden" id="activity-id" value="" />
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4 mandatory-field">Company Name</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="company-name"/>
                            <span class="form-warning"></span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4 mandatory-field">Date of Service</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datepicker" id="service-date"/>
                            <span class="form-warning"></span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4 mandatory-field">Service Type</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="service-type">
                                <option value="">-- Select Service Type --</option>
                                <option value="On-Site Audit">On-Site Audit</option>
                                <option value="Remote Audit">Remote Audit</option>
                                <option value="In-House Training">In-House Training</option>
                                <option value="Online Training">Online Training</option>
                                <option value="Others">Others</option>
                            </select>
                            <span class="form-warning"></span>
                            <div id="service-type-other-container" style="display: none;">
                                <input type="text" class="form-control" id="service-type-other" placeholder="Please specify service type"/>
                            </div>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4 mandatory-field">Auditor Type</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="auditor-type">
                                <option value="">-- Select Auditor Type --</option>
                                <option value="External">External</option>
                                <option value="Internal">Internal</option>
                            </select>
                            <span class="form-warning"></span>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Standards</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="standards"/>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Audit Type</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="audit-type">
                                <option value="">-- Select Audit Type --</option>
                                <option value="Initial Audit">Initial Audit</option>
                                <option value="Control Audit I">Control Audit I</option>
                                <option value="Control Audit II">Control Audit II</option>
                                <option value="Re-Certification">Re-Certification</option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Audit Category</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="audit-category"/>
                        </div>
                    </div>
                    <div class="row form-group inbound-fields">
                        <label class="col-xs-12 col-md-4">Invoice Number (Inbound)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="invoice-number-inbound"/>
                        </div>
                    </div>
                    <div class="row form-group inbound-fields">
                        <label class="col-xs-12 col-md-4">Invoice Date (Inbound)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datepicker" id="invoice-date-inbound"/>
                        </div>
                    </div>
                    <div class="row form-group inbound-fields">
                        <label class="col-xs-12 col-md-4">Upload Invoice (Inbound)</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-invoice-inbound">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-invoice-inbound" type="file" name="files[]" foldertype="invoice_inbound">
                            </span>
                            <ul id="ulinvoice_inbound"></ul>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Travel Expenses</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="travel-expenses"/>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Travel Invoices</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-travel-invoices">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-travel-invoices" type="file" name="files[]" foldertype="travel_invoices">
                            </span>
                            <ul id="ultravel_invoices"></ul>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Paid On</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datepicker" id="paid-on"/>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4 admin-only-">Invoice Number (Outbound)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="invoice-number-outbound"/>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4 admin-only">Paid</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="paid-status">
                                <option value="">-- Select Status --</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Issuing Invoice</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="issuing-invoice-status">
                                <option value="">-- Select Status --</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div> 
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Training Request Form</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-training-request">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-training-request" type="file" name="files[]" foldertype="training_request_form">
                            </span>
                            <ul id="ultraining_request_form"></ul>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Attendance List</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>         
                            <span class="fileinput-button i-dropzone" id="dropzone-attendance">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-attendance" type="file" name="files[]" foldertype="attendance_list">
                            </span>
                            <ul id="ulattendance_list"></ul>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Customer Feedback Form</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-feedback">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-feedback" type="file" name="files[]" foldertype="customer_feedback_form">
                            </span>
                            <ul id="ulcustomer_feedback_form"></ul>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Attendance Certificates</label>
                        <div class='col-xs-12 col-md-8'>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                            </div>
                            <span class="fileinput-button i-dropzone" id="dropzone-certificates">Drop files here or click to upload
                                <input class="fileupload" id="fileupload-certificates" type="file" name="files[]" foldertype="attendance_certificates">
                            </span>
                            <ul id="ulattendance_certificates"></ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <p class="form-note">* Indicates mandatory field</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-xs-12 col-md-6 text-center"><p class="form-warning"><i class="fa fa-warning"></i>&nbsp;&nbsp;Mandatory fields are not specified</p></div>
                    <div class="col-xs-12 col-md-3"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                    <div class="col-xs-12 col-md-3"><button type="button" class="btn btn-primary" onclick="TP.onSave();">Save changes</button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('pages/footer.php');?>
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/jquery.jqGrid.min.js"></script>
<script src="js/grid.locale-en.js"></script>
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>
<script src="js/select2.full.min.js"></script>
<script src="js/vendor/jquery.ui.widget.js"></script>
<script src="js/jquery.iframe-transport.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js?ver=1285677791' id='blockui-js'></script>
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script>

<script>
  $(document).ready(function() {
    function toggleInboundFields() {
        var auditorType = $('#auditor-type').val();
        if (auditorType === 'Internal') {
            $('.inbound-fields').hide();
        } else {
            $('.inbound-fields').show();
        }
    }
    
    function toggleServiceTypeOther() {
        var serviceType = $('#service-type').val();
        if (serviceType === 'Others') {
            $('#service-type-other-container').show();
            $('#service-type-other').attr('required', true);
        } else {
            $('#service-type-other-container').hide();
            $('#service-type-other').attr('required', false).val('');
        }
    }
    
    toggleInboundFields();
    toggleServiceTypeOther();
    
    $('#auditor-type').change(function() {
        toggleInboundFields();
        TP.clearAlerts();
    });
    
    $('#service-type').change(function() {
        toggleServiceTypeOther();
        TP.clearAlerts();
    });
});

var userId = <?php echo $_SESSION['halal']['id'] ?>;
    Common.onDocumentReady();
    
    $(document).ready(function() {
        $('#checkall').click(function() {
            var checked = $(this).prop('checked');
            $('#table_tank').find('input:checkbox').prop('checked', checked);
        });
    });

var TP = {

  isAdmin:<?php echo $isAdmin ? 'true' : 'false'; ?>,

  onDocumentReady: function() {


    $("#logModal").on("shown.bs.modal", function() {
      var table = $("#table_log").DataTable();
      table.ajax.reload(null, false);
    });

    Common.setMainMenuItem("training");

    TP.gridMode = 0;

    $('[data-toggle="tooltip"]').tooltip();

    $("input").focus(function() {
      TP.clearAlerts();
    });

    $("select").change(function() {
      TP.clearAlerts();
    });
 
     $(".datepicker").datepicker({
      autoUpdateInput: false,
      autoclose: true,
      format: "dd M yyyy",
      orientation: "bottom",
    });


    $(".datepicker").on("changeDate", function(e) {
      TP.clearAlerts();
    });

    $("#activity-auditorid").on("change", function() {
      if (jqGridRequest) {
        jqGridRequest.abort();
      }
      const gridParams = {
        url: "ajax/getActivities.php?displaymode=" + TP.gridMode + "&idauditor=" + this.value,
        rowNum: isNaN(parseInt(this.value)) ? 20 : 1000000,
      };

      $(".ui-paging-pager").toggle(isNaN(parseInt(this.value)));

      $("#activity-auditorid").data(
        "clientname",
        $("#activity-auditorid option:selected").text()
      );

      jQuery("#activityGrid").jqGrid("setGridParam", gridParams);
      jQuery("#activityGrid").jqGrid().trigger("reloadGrid");
    });

initFileUploader({
  fileUploadSelector: "#activity-form .fileupload",
  dropzoneSelector: "#activity-form .dropzone",
  progressSelector: "#activity-form .progress",

  dataModifier: function(e, data) {
    data.formData = {
      folderType: $(e.target).attr("foldertype"),
      infoType: "activity",
      auditor: $("#activity-auditorid option:selected").text(),
      idactivity: $("#activity-form #activity-id").val(),
    };
  },

    fileValidator: function (e, data) {
    const uploadFile = data.files[0];

    if (!/\.(pdf|doc|docx|xls|xlsx|png|jpe?g)$/i.test(uploadFile.name)) {
        return "You can upload files in PDF, Word, Excel, or image formats (PNG, JPG)";
    }

 

    return true;
    },

  afterSuccess: function(e, file) {
    TP.filesUploaded.push({ file: file.name });
    
  }
});
 
 

    TP.initGrid();
  },

   initGrid: function() {
    var h = (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight) - 350;

    new Promise(function(resolve) {
      $("#activityGrid").jqGrid({
        url: "ajax/getActivities.php?displaymode=" + TP.gridMode + "&idauditor=" + $("#activity-auditorid").val(),
        datatype: "json",
        mtype: "POST",
        width: $("#activityGrid").parent().width(),
        height: h,
        postData: {
          // These functions read filter toolbar input values on each request
          name: function() { return $("input[name='name']").val() || ""; },
          company_name: function() { return $("input[name='company_name']").val() || ""; },
          date_of_service: function() { return $("input[name='date_of_service']").val() || ""; },
          service_type: function() { return $("input[name='service_type']").val() || ""; },
          auditor_type: function() { return $("input[name='auditor_type']").val() || ""; },
          standards: function() { return $("input[name='standards']").val() || ""; },
          audit_type: function() { return $("input[name='audit_type']").val() || ""; },
          audit_category: function() { return $("input[name='audit_category']").val() || ""; },
          invoice_number_inbound: function() { return $("input[name='invoice_number_inbound']").val() || ""; },
          invoice_date_inbound: function() { return $("input[name='invoice_date_inbound']").val() || ""; },
          travel_expenses: function() { return $("input[name='travel_expenses']").val() || ""; },
          paid_on: function() { return $("input[name='paid_on']").val() || ""; },
          invoice_number_outbound: function() { return $("input[name='invoice_number_outbound']").val() || ""; },
          paid: function() { return $("select[name='paid']").val() || ""; },
          issuing_invoice: function() { return $("select[name='issuing_invoice']").val() || ""; }
        },
        colModel: [
          { name: "id", label: "ID", width: 50, key: true, hidden: true },
          
          { name: "idauditor", label: "Auditor ID", width: 50, hidden: true },
          { name: "name", label: "Auditor", width: 155 <?php if ($isAuditor): ?>, hidden: true<?php endif; ?> },
          
          { name: "company_name", label: "Company Name", width: 155 },
          { name: "date_of_service", label: "Date of Service", width: 105, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" } },
          { name: "service_type", label: "Service Type", width: 105 },
          { name: "auditor_type", label: "Auditor Type", width: 100 },
          { name: "standards", label: "Standards", width: 120 },
          { name: "audit_type", label: "Audit Type", width: 120 },
          { name: "audit_category", label: "Audit Category", width: 120 },
          { name: "invoice_number_inbound", label: "Invoice No. (Inbound)", width: 125 },
          { name: "invoice_date_inbound", label: "Invoice Date (Inbound)", width: 105, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" } },
          { 
              name: "invoice_inbound", 
              index: "invoice_inbound", 
              label: "Upload Invoice (Inbound)", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },          
          { name: "travel_expenses", label: "Travel Expenses", width: 125 },
          { 
              name: "travel_invoices",
              index: "travel_invoices", 
              label: "Travel Invoices", 
              width: 130,
              formatter: formatDoclink,
              unformat: unformatDoclink,
              search: false
          },
          { name: "paid_on", label: "Paid On", width: 105, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" } },
          { name: "invoice_number_outbound", label: "Invoice No. (Outbound)", width: 125 },
          { 
            name: "paid", 
            label: "Paid", 
            width: 80,
            align: "center",
            formatter: function(cellvalue, options, rowObject) {
              var isChecked = cellvalue == 'Yes' ? 'checked' : '';
              var rowId = options.rowId;
              return '<label class="paid-toggle" title="Click to toggle">' +
                     '<input type="checkbox" ' + isChecked + ' onchange="TP.togglePaidStatus(\'' + rowId + '\', this.checked)">' +
                     '<span class="slider">' +
                     '<span class="toggle-label yes">Yes</span>' +
                     '<span class="toggle-label no">No</span>' +
                     '</span></label>';
            },
            unformat: function(cellvalue, options, cell) {
              return $(cell).find('input').is(':checked') ? 'Yes' : 'No';
            },
            stype: "select",
            searchoptions: { value: ":All;Yes:Yes;No:No" }
          },
          { 
            name: "issuing_invoice", 
            label: "Issuing Invoice", 
            width: 100,
            align: "center",
            formatter: function(cellvalue, options, rowObject) {
              var isChecked = cellvalue == 'Yes' ? 'checked' : '';
              var rowId = options.rowId;
              return '<label class="paid-toggle" title="Click to toggle">' +
                     '<input type="checkbox" ' + isChecked + ' onchange="TP.toggleIssuingInvoiceStatus(\'' + rowId + '\', this.checked)">' +
                     '<span class="slider">' +
                     '<span class="toggle-label yes">Yes</span>' +
                     '<span class="toggle-label no">No</span>' +
                     '</span></label>';
            },
            unformat: function(cellvalue, options, cell) {
              return $(cell).find('input').is(':checked') ? 'Yes' : 'No';
            },
            stype: "select",
            searchoptions: { value: ":All;Yes:Yes;No:No" }
          },
          { 
            name: "training_request_form", 
            index: "training_request_form", 
            label: "Training Request", 
            width: 130,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            search: false
          },
          { 
            name: "attendance_list", 
            index: "attendance_list", 
            label: "Attendance List", 
            width: 130,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            search: false
          },
          { 
            name: "customer_feedback_form", 
            index: "customer_feedback_form", 
            label: "Feedback Form", 
            width: 130,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            search: false
          },
          { 
            name: "attendance_certificates", 
            index: "attendance_certificates", 
            label: "Certificates", 
            width: 130,
            formatter: formatDoclink,
            unformat: unformatDoclink,
            search: false
          },
        { name: "deleted", index: "deleted", editable: false, hidden: true },
        ],
        rowNum: 20,
        rowList: [20, 50, 100],
        pager: "#activityPager",
        sortname: "date_of_service",
        viewrecords: true,
        sortorder: "desc",
        shrinkToFit: false,
        toppager: true,
        hoverrows: false,
        gridview: true,
        multiselect: true,
        loadComplete: function() {
          Common.updatePagerIcons(this);
          document.querySelectorAll(".upload-area").forEach((area) => {
            area.addEventListener("dragover", handleDragOver);
            area.addEventListener("dragleave", handleDragLeave);
            area.addEventListener("drop", handleDrop);
          });
        },
        gridComplete: function() {
          initFileUploader({
            fileUploadSelector: "#gbox_activityGrid .fileupload",
            dropzoneSelector: "#gbox_activityGrid .dropzone",
            progressSelector: "#gbox_activityGrid .progress",
            dataModifier: function(e, data) {
              data.formData = {
                folderType: $(e.target).attr("foldertype"),
                infoType: "activity",
                auditor: $("#activity-auditorid option:selected").text(),
                idactivity: $(e.target).closest("tr").attr("id"),
              };
            },

            fileValidator: function (e, data) {
                const uploadFile = data.files[0];

                if (!/\.(pdf|doc|docx|xls|xlsx|png|jpe?g)$/i.test(uploadFile.name)) {
                    return "You can upload files in PDF, Word, Excel, or image formats (PNG, JPG)";
                }           

                return true;
            },

            onSuccess: function(e, data) {
              $(e.target).parent().siblings(".progress").hide();
              
              if (!data.result.files.length) return;
              
              const fileData = {
                name: data.result.files[0].name,
                glink: data.result.files[0].googleDriveUrl,
                hostpath: data.result.files[0].url,
                hostUrl: data.result.files[0].hostUrl,
              };

              const FD = new FormData();
              FD.append("id", $(e.target).closest("tr").attr("id"));
              FD.append("rtype", "addActivityFiles");
              
              const colName = {
                invoice_inbound: "invoice_inbound",
                travel_invoices: "travel_invoices",
                training_request_form: "training_request_form",
                attendance_list: "attendance_list",
                customer_feedback_form: "customer_feedback_form",
                attendance_certificates: "attendance_certificates"
              }[data.result.files[0].folderType];

              FD.append(colName, JSON.stringify(fileData));

              fetch("/ajax/ajaxHandler.php", {
                method: "POST",
                credentials: "include",
                body: FD,
              }).then(r => r.json())
                .then(j => {
                  if (j.status != "1") {
                    alert("There was an error attaching the files.");
                    return;
                  }
                  $("#activityGrid").jqGrid().trigger("reloadGrid");
                });

              TP.filesUploaded?.push({ file: data.result.files[0].name });
            }
          });
        },
        rowattr: function (rd) {
          console.log("DELETED:" + rd.deleted);
           var rowclass = "";
          if (rd.deleted === "1") rowclass += "deleted ";
         
          rowclass = { class: rowclass };
          return rowclass;
        },        
      });
 
       $("#activityGrid").jqGrid("navGrid", "#ingredPager", {
        cloneToTop: true,
        edit: true,
        add: true,
        del: true,
        search: false,
        refresh: true,
        view: false,
        addfunc: function () {
          TP.newActivity();
        },
        editfunc: function () {
          TP.editActivity();
        },
        delfunc: function () {
          TP.deleteActivity();
        },
      });

      // Initialize filterToolbar - same as working page
      $("#activityGrid").jqGrid("filterToolbar", { enableClear: false });

      $("#activityGrid").navButtonAdd("#activityGrid_toppager", {
        caption: "",
        title: "Toggle displaying removed records mode",
        buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
        onClickButton: function () {
          TP.onToggleRemovedRecordsMode(event);
        },
      });

      resolve("grid initialized");
    });
  },
  
  // Filter grid function - similar to working page
  filterGrid: function () {
    new Promise(function (resolve) {
      $("#activityGrid").jqGrid("setGridParam", { search: true });
      resolve("params done");
    }).then(function (res) {
      jQuery("#activityGrid").jqGrid().trigger("reloadGrid");
    });
  },

  togglePaidStatus: function(rowId, isChecked) {
    var newStatus = isChecked ? 'Yes' : 'No';
    
    $.ajax({
      url: "ajax/ajaxHandler.php",
      type: "POST",
      data: {
        rtype: "updatePaidStatus",
        id: rowId,
        paid: newStatus
      },
      dataType: "json",
      beforeSend: function() {
        // Optional: show loading indicator
      },
      success: function(response) {
        if (response.status == 0) {
          Utils.notify("error", response.statusDescription);
          // Revert the toggle if there was an error
          $("#activityGrid").jqGrid().trigger("reloadGrid");
          return;
        }
        Utils.notify("success", "Paid status updated to " + newStatus);
      },
      error: function(xhr, status, error) {
        Utils.notify("error", "Error updating paid status: " + error);
        // Revert the toggle on error
        $("#activityGrid").jqGrid().trigger("reloadGrid");
      }
    });
  },

  toggleIssuingInvoiceStatus: function(rowId, isChecked) {
    var newStatus = isChecked ? 'Yes' : 'No';
    
    $.ajax({
      url: "ajax/ajaxHandler.php",
      type: "POST",
      data: {
        rtype: "updateIssuingInvoiceStatus",
        id: rowId,
        issuing_invoice: newStatus
      },
      dataType: "json",
      beforeSend: function() {
        // Optional: show loading indicator
      },
      success: function(response) {
        if (response.status == 0) {
          Utils.notify("error", response.statusDescription);
          // Revert the toggle if there was an error
          $("#activityGrid").jqGrid().trigger("reloadGrid");
          return;
        }
        Utils.notify("success", "Issuing Invoice status updated to " + newStatus);
      },
      error: function(xhr, status, error) {
        Utils.notify("error", "Error updating issuing invoice status: " + error);
        // Revert the toggle on error
        $("#activityGrid").jqGrid().trigger("reloadGrid");
      }
    });
  },

  formatDoclink: function(cellvalue, options, rowObject) {

    if (!cellvalue) return "";
    try {
      var doc = JSON.parse(cellvalue);
      return '<a href="' + doc.hostpath + '" target="_blank" title="' + doc.name + '">' + 
             '<i class="ace-icon fa fa-file-pdf-o red"></i> ' + 
             (doc.name.length > 15 ? doc.name.substring(0,15)+"..." : doc.name) +
             '</a>' +
             '<span class="fileinput-button dropzone upload-area">' +
             '<input class="fileupload" type="file" name="files[]" foldertype="' + 
             options.colModel.name + '">' +
             '</span>';
    } catch(e) {
      return cellvalue;
    }
  },

  unformatDoclink: function(cellvalue, options, rowObject) {
    return cellvalue;
  },

clearForm: function() {
    TP.clearAlerts();
    $(".datepicker").datepicker("update", "");
    $("#ultraining_request_form").empty();
    $("#ulattendance_list").empty();
    $("#ulcustomer_feedback_form").empty();
    $("#ulattendance_certificates").empty();
    $("#ulinvoice_inbound").empty();
    $("#ultravel_invoices").empty();
    $("#activity-form input").val("");
    $("#activity-form .ace-switch").prop("checked", false);
    $("#activity-form #service-type").val("").trigger("change");
    $("#activity-form #auditor-type").val("").trigger("change");
    $("#activity-form #paid-status").val("").trigger("change");
    $("#activity-form #issuing-invoice-status").val("").trigger("change");
    $("#activity-form #audit-type").val("").trigger("change");
    $("#activity-form #standards").val("");
    $("#activity-form #audit-category").val("");
    $("#activity-form #service-type-other").val("");
    $("#service-type-other-container").hide();
    $("#activityModal .form-warning").hide();
},

clearAlerts: function() {
    $(".alert-string").text("");
},

fillForm: function(data) {
    var response = JSON.parse(data);
    if (response.status == 0) {
        alert(response.statusDescription);
        return;
    }
    if (!response.data.activity) {
        $("#activity-form #activity-id").val(response.data.id);
        $("#activity-form #activity-id").attr("data-id", response.data.id);
        $("#activity-form #activity-id").attr("data-new", 1);
    }
    $("#activityModal").prop("submit", 0);
    TP.filesUploaded = [];
    TP.toggleFieldEditability();
    $("#activityModal").modal("show");
},

getNextActivityId: function(callback) {
    var prod = {};
    $.get("ajax/ajaxHandler.php", {
        uid: 0,
        data: prod,
        rtype: "nextActivityId",
    }).done(callback);
},

newActivity: function() {
    if ($("#activity-auditorid").val() == "" || $("#activity-auditorid").val() == "-1") {
        alert("Please select an auditor");
        return;
    }
    TP.clearForm();
    $("#activityModal-label").text("New Activity");
    TP.getNextActivityId(TP.fillForm);
},

editActivity: function() {
    if (jQuery("#activityGrid").jqGrid("getGridParam", "selrow") == null) {
        alert("Please select activity");
        return;
    }
    TP.clearForm();
    $("#activity-auditorid").val(jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "idauditor"
        ))

    $("#activityModal-label").text("Edit Activity");
    $("#activity-form #activity-id").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "id"
        )
    );
    $("#activity-form #activity-id").attr("data-id", 
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "id"
        )
    );
    $("#activity-form #activity-id").attr("data-new", 0);
    $("#activity-form #company-name").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "company_name"
        )
    );
    
    $("#activity-form #service-date").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "date_of_service"
        )
    );

    var serviceTypeValue = jQuery("#activityGrid").jqGrid(
        "getCell",
        jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
        "service_type"
    );
    
    // Check if it's a predefined service type or "Others"
    var predefinedTypes = ["On-Site Audit", "Remote Audit", "In-House Training", "Online Training"];
    if (predefinedTypes.indexOf(serviceTypeValue) === -1 && serviceTypeValue !== "") {
        // It's a custom "Others" value
        $("#activity-form #service-type").val("Others").trigger("change");
        $("#activity-form #service-type-other").val(serviceTypeValue);
    } else {
        $("#activity-form #service-type").val(serviceTypeValue);
    }

    $("#activity-form #auditor-type").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "auditor_type"
        )
    );

    $("#activity-form #standards").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "standards"
        )
    );

    $("#activity-form #audit-type").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "audit_type"
        )
    );

    $("#activity-form #audit-category").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "audit_category"
        )
    );

    $("#activity-form #invoice-number-inbound").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "invoice_number_inbound"
        )
    );
    $("#activity-form #invoice-date-inbound").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "invoice_date_inbound"
        )
    );
    
    $("#activity-form #paid-on").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "paid_on"
        )
    );

    $("#activity-form #invoice-number-outbound").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "invoice_number_outbound"
        )
    );

    
     $("#activity-form #travel-expenses").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "travel_expenses"
        )
    );
    
    var paidCell = jQuery("#activityGrid").jqGrid(
        "getCell",
        jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
        "paid"
    );
    // Extract value from toggle - check if input is checked
    var paidValue = $(paidCell).find('input').is(':checked') ? 'Yes' : 'No';
    $("#activity-form #paid-status").val(paidValue);

    var issuingInvoiceCell = jQuery("#activityGrid").jqGrid(
        "getCell",
        jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
        "issuing_invoice"
    );
    // Extract value from toggle - check if input is checked
    var issuingInvoiceValue = $(issuingInvoiceCell).find('input').is(':checked') ? 'Yes' : 'No';
    $("#activity-form #issuing-invoice-status").val(issuingInvoiceValue);

    Utils.filesToList("ultraining_request_form", "activityGrid", "training_request_form");
    Utils.filesToList("ulattendance_list", "activityGrid", "attendance_list");
    Utils.filesToList("ulcustomer_feedback_form", "activityGrid", "customer_feedback_form");
    Utils.filesToList("ulattendance_certificates", "activityGrid", "attendance_certificates");
    Utils.filesToList("ulinvoice_inbound", "activityGrid", "invoice_inbound");
    Utils.filesToList("ultravel_invoices", "activityGrid", "travel_invoices");

    $("#activityModal").prop("submit", 1);
    TP.filesUploaded = [];
    $("#activityModal").modal("show");
},

toggleFieldEditability: function() {
    if (!TP.isAdmin) {
        $("#paid-on").prop('disabled', true).addClass('disabled-field');
        //$("#invoice-number-outbound").prop('disabled', true).addClass('disabled-field');
        $("#paid-status").prop('disabled', true).addClass('disabled-field');
        $(".admin-only").removeClass('mandatory-field');
    } else {
        $("#paid-on").prop('disabled', false).removeClass('disabled-field');
        //$("#invoice-number-outbound").prop('disabled', false).removeClass('disabled-field');
        $("#paid-status").prop('disabled', false).removeClass('disabled-field');
    }
},

deleteActivity: function() {
    if (jQuery("#activityGrid").jqGrid("getGridParam", "selrow") == null) {
        alert("Please select activity");
        return;
    }
    if (confirm("Delete the activity?")) {
        TP.sendDeleteActivityRequest();
    }
},

createDocFromInputData: function() {
    var doc = {};
    doc.id = $("#activity-form #activity-id").val();
    doc.idauditor = $("#activity-auditorid").val();
    doc.company_name = $("#activity-form #company-name").val().trim();
    doc.date_of_service = $("#activity-form #service-date").val().trim();
    
    // Handle service type - if "Others" is selected, use the custom description
    let serviceTypeVal = $("#activity-form #service-type").val();
    if (serviceTypeVal === "Others") {
        doc.service_type = $("#activity-form #service-type-other").val().trim();
    } else {
        doc.service_type = serviceTypeVal ? serviceTypeVal.trim() : "";
    }
    
    let auditorTypeVal = $("#activity-form #auditor-type").val();
    doc.auditor_type = auditorTypeVal ? auditorTypeVal.trim() : "";
    
    // New fields: Standards, Audit Type, Audit Category
    doc.standards = $("#activity-form #standards").val().trim();
    let auditTypeVal = $("#activity-form #audit-type").val();
    doc.audit_type = auditTypeVal ? auditTypeVal.trim() : "";
    doc.audit_category = $("#activity-form #audit-category").val().trim();
    
    doc.invoice_number_inbound = $("#activity-form #invoice-number-inbound").val().trim();
    doc.invoice_date_inbound = $("#activity-form #invoice-date-inbound").val().trim();
    doc.travel_expenses = $("#activity-form #travel-expenses").val().trim();
    doc.invoice_number_outbound = $("#activity-form #invoice-number-outbound").val().trim();

    if (TP.isAdmin) {
        doc.paid_on = $("#activity-form #paid-on").val().trim();
        doc.paid = $("#activity-form #paid-status").val().trim();
        doc.issuing_invoice = $("#activity-form #issuing-invoice-status").val().trim();
    } else {
        var originalRow = null;
        if (doc.id) {
            originalRow = jQuery("#activityGrid").jqGrid(
                "getRowData",
                jQuery("#activityGrid").jqGrid("getGridParam", "selrow")
            );
        }
        
        doc.paid_on = originalRow ? originalRow.paid_on : "";
        //doc.invoice_number_outbound = originalRow ? originalRow.invoice_number_outbound : "";
        doc.paid = originalRow ? originalRow.paid : "No";
        doc.issuing_invoice = $("#activity-form #issuing-invoice-status").val().trim();
    }

    doc.training_request_form = Utils.filesToJSON("ultraining_request_form");
    doc.attendance_list = Utils.filesToJSON("ulattendance_list");
    doc.customer_feedback_form = Utils.filesToJSON("ulcustomer_feedback_form");
    doc.attendance_certificates = Utils.filesToJSON("ulattendance_certificates");
    doc.invoice_inbound = Utils.filesToJSON("ulinvoice_inbound");
    doc.travel_invoices = Utils.filesToJSON("ultravel_invoices");
    
    return doc;
},

validateForm: function() {
    $("#activityModal .form-warning").hide();
    setTimeout(function() {
        $("#activityModal .form-warning").hide();
    }, 4000);

    if ($("#activity-form #company-name").val().trim() == "") {
        Utils.notifyInput($("#activity-form #company-name"), "Company name is required");
        $("#activityModal .form-warning").show();
        return false;
    }
    
    if ($("#activity-form #service-date").val().trim() == "") {
        Utils.notifyInput($("#activity-form #service-date"), "Service date is required");
        $("#activityModal .form-warning").show();
        return false;
    }
    
    let serviceTypeVal = $("#activity-form #service-type").val();
    if (!serviceTypeVal || serviceTypeVal.trim() === "") {
        Utils.notifyInput($("#activity-form #service-type"), "Service type is required");
        $("#activityModal .form-warning").show();
        return false;
    }
    
    // Validate "Others" description if "Others" is selected
    if (serviceTypeVal === "Others") {
        let otherDescription = $("#activity-form #service-type-other").val();
        if (!otherDescription || otherDescription.trim() === "") {
            Utils.notifyInput($("#activity-form #service-type-other"), "Please specify the service type");
            $("#activityModal .form-warning").show();
            return false;
        }
    }

    let auditorTypeVal = $("#activity-form #auditor-type").val();
    if (!auditorTypeVal || auditorTypeVal.trim() === "") {
        Utils.notifyInput($("#activity-form #auditor-type"), "Auditor type is required");
        $("#activityModal .form-warning").show();
        return false;
    }

    if (TP.isAdmin) {
        if ($("#activity-form #invoice-number-outbound").val().trim() == "") {
            //Utils.notifyInput($("#activity-form #invoice-number-outbound"), "Outbound invoice number is required");
            //$("#activityModal .form-warning").show();
            //return false;
        }
        
        let paidStatusVal = $("#activity-form #paid-status").val();
        if (!paidStatusVal || paidStatusVal.trim() === "") {
            //Utils.notifyInput($("#activity-form #paid-status"), "Paid status is required");
            //$("#activityModal .form-warning").show();
            //return false;
        }
    }

    return true;
},

  sendModifyActivityRequest: function(doc) {
    $.ajax({
        url: "ajax/ajaxHandler.php",
        type: "POST",
        data: {
            rtype: "saveActivity",
            uid: 0,
            data: doc
        },
        dataType: "json",
        beforeSend: function() {
            Utils.notify("info", "Saving activity...");
            $.blockUI();
        },
        success: function(response) {
            if (response.status == 0) {
                Utils.notify("error", response.statusDescription);
                return;
            }
            Utils.notify("success", "Activity saved successfully");

            var d = {};
            d.itemid = doc.id;
            d.idclient = doc.idclient;
            d.itemcode = $("#activity-form #activity-id").val();
            d.itemtype = "activities";
            d.itemname = doc.company_name + " - " + doc.service_type;
            d.action = ($("#activityModal").prop("submit") == 0) ? "New activity added" : "Activity updated";

            if (TP.filesUploaded.length > 0) {
                d.action = "Activity documents updated";
                d.documents = JSON.stringify(TP.filesUploaded);
            }

            $("#activityModal").prop("submit", 1);
            $("#activityModal").modal("hide");
            jQuery("#activityGrid").trigger("reloadGrid");
        },
        error: function(xhr, status, error) {
            Utils.notify("error", "Error saving activity: " + error);
        },
        complete: function() {
            $.unblockUI();
        }
    });
},

sendRemoveActivityRequest: function() {
    var doc = { id: $("#activity-form #activity-id").attr("data-id") };
    $.post("ajax/ajaxHandler.php", {
        rtype: "removeActivity",
        uid: 0,
        data: doc
    }).done(function(data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
        }
        jQuery("#activityGrid").trigger("reloadGrid");
        Utils.notify("success", "Activity data was removed");
    });
},

sendDeleteActivityRequest: function() {
    var doc = {};
    doc.ids = $("#activityGrid").getGridParam("selarrrow");
    $.post("ajax/ajaxHandler.php", {
        rtype: "markDeletedActivity",
        uid: 0,
        data: doc
    }).done(function(data) {
        var response = JSON.parse(data);
        if (response.status == 0) {
            Utils.notify("error", response.statusDescription);
            return;
        }
        jQuery("#activityGrid").trigger("reloadGrid");
        Utils.notify("success", "Activity was deleted");
    });
},

onSave: function() {
    TP.clearAlerts();
    if (!TP.validateForm()) {
        return;
    }
    var doc = TP.createDocFromInputData();
    
    if (!TP.isAdmin) {
        var activityId = $("#activity-form #activity-id").val();
        
        if (activityId && activityId !== "") {
            var originalRow = jQuery("#activityGrid").jqGrid(
                "getRowData",
                jQuery("#activityGrid").jqGrid("getGridParam", "selrow")
            );
            doc.paid_on = originalRow.paid_on;
            //doc.invoice_number_outbound = originalRow.invoice_number_outbound;
            doc.paid = originalRow.paid;
        } 
        else {
            doc.paid_on = "";
            //doc.invoice_number_outbound = "";
            doc.paid = "No";
        }
    }

     console.log("DOC:", doc)
    TP.sendModifyActivityRequest(doc);
},

  onExportGridToExcel: function() {
    var companyId = $("#activity-auditorid").val();
    window.open("ajax/exportActivities.php?idclient=" + companyId, "_blank");
  },

  onToggleRemovedRecordsMode: function (e) {
    if (TP.gridMode == 1) {
      $(".gridmode-toggle").removeClass("red");
      TP.gridMode = 0;
    } else {
      $(".gridmode-toggle").addClass("red");
      TP.gridMode = 1;
    }
    $("#activity-auditorid").trigger("change");
  },

  init: function() {
    $(document).ready(function() {
      TP.onDocumentReady();
    });
  }
};

TP.init();
</script>

</body>
</html>