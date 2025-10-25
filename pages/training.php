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
    </style>
</head>
<body>
<?php include_once('pages/navigation.php');
try {
    $db = acsessDb :: singleton();
    $dbo =  $db->connect(); // Create database connection object
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
                    
                        <!-- div.table-responsive -->
                        <!-- div.dataTables_borderWrap -->
                        <div>
                            <table id="activityGrid"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Modal -->
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
                        <label class="col-xs-12 col-md-4">Company Name</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="company-name"/>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Date of Service</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datepicker" id="service-date"/>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Service Type</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="service-type">
                                <option value="On-Site Audit">On-Site Audit</option>
                                <option value="Remote Audit">Remote Audit</option>
                                <option value="In-House Training">In-House Training</option>
                                <option value="Online Training">Online Training</option>
                            </select>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Auditor Type</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="auditor-type">
                                <option value="External">External</option>
                                <option value="Internal">Internal</option>
                            </select>
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
                        <label class="col-xs-12 col-md-4">Invoice Number (Outbound)</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="invoice-number-outbound"/>
                        </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Paid</label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="paid-status">
                                <option value="">Select</option>
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
<!-- page specific plugin scripts -->
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/jquery.jqGrid.min.js"></script>
<script src="js/grid.locale-en.js"></script>
<!-- ace scripts -->
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
    // Function to toggle inbound fields based on auditor type
    function toggleInboundFields() {
        var auditorType = $('#auditor-type').val();
        if (auditorType === 'Internal') {
            $('.inbound-fields').hide();
        } else {
            $('.inbound-fields').show();
        }
    }
    
    // Initial toggle
    toggleInboundFields();
    
    // Bind change event
    $('#auditor-type').change(toggleInboundFields);
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

 // Initialize file uploaders for activity documents
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

    // Acceptable formats: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, JPEG
    if (!/\.(pdf|doc|docx|xls|xlsx|png|jpe?g)$/i.test(uploadFile.name)) {
        return "You can upload files in PDF, Word, Excel, or image formats (PNG, JPG)";
    }

 

    return true;
    },

  afterSuccess: function(e, file) {
    TP.filesUploaded.push({ file: file.name });
    
  }
});
 
 

    // Initialize the activity grid
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
        colModel: [
          { name: "id", label: "ID", width: 50, key: true, hidden: true },
          
          { name: "idauditor", label: "Auditor ID", width: 50,  hidden: true },
          { name: "name", label: "Auditor", width: 155, frozen: true <?php if ($isAuditor): ?>, hidden: true<?php endif; ?> },
          
          { name: "company_name", label: "Company Name", width: 155, frozen: true },
          { name: "date_of_service", label: "Date of Service", width: 105, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" }, frozen: true },
          { name: "service_type", label: "Service Type", width: 105, frozen: true },
          { name: "auditor_type", label: "Auditor Type", width: 100 },
          { name: "invoice_number_inbound", label: "Invoice No. (Inbound)", width: 125 },
          { name: "invoice_date_inbound", label: "Invoice Date (Inbound)", width: 105, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" }, },
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
          { name: "paid_on", label: "Paid On", width: 105, formatter: "date", formatoptions: { srcformat: "ISO8601Long", newformat: "j M Y" }, },
          { name: "invoice_number_outbound", label: "Invoice No. (Outbound)", width: 125 },
          { 
            name: "paid", 
            label: "Paid", 
            width: 100,
            formatter: function(cellvalue) {
              return cellvalue == 'Yes' ? 
                '<span class="label label-success">Yes</span>' : 
                '<span class="label label-danger">No</span>';
            },
            stype: "select",
            searchoptions: { value: ":[All];Yes:Yes;No:No" }
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
          // Initialize file upload areas for document columns
          document.querySelectorAll(".upload-area").forEach((area) => {
            area.addEventListener("dragover", handleDragOver);
            area.addEventListener("dragleave", handleDragLeave);
            area.addEventListener("drop", handleDrop);
          });
        },
        gridComplete: function() {
          // Initialize file uploaders for document columns
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

                // Acceptable formats: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, JPEG
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
 // Ingredient row color
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

      $("#activityGrid").jqGrid("filterToolbar", { 
        searchOperators: true,
        enableClear: false 
      });

 $("#activityGrid").navButtonAdd("#activityGrid_toppager", {
        caption: "",
        title: "Toggle displaying removed records mode",
        buttonicon: "ace-icon fa fa-adjust gridmode-toggle",
        onClickButton: function () {
          TP.onToggleRemovedRecordsMode(event);
        },
      });

     
     
      /*
      $("#activityGrid").navButtonAdd("#activityPager", {
        caption: "",
        title: "Export to Excel",
        buttonicon: "ace-icon fa fa-file-excel-o",
        onClickButton: function() {
          TP.onExportGridToExcel();
        }
      });

      $("#activityGrid").navButtonAdd("#activityGrid_toppager", {
        caption: "",
        title: "Export to Excel",
        buttonicon: "ace-icon fa fa-file-excel-o",
        onClickButton: function() {
          TP.onExportGridToExcel();
        }
      });
      */

      $("#activityGrid").jqGrid("setFrozenColumns");
      resolve("grid initialized");
    });
  },

  // Document link formatters
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
    $("#ulinvoice_inbound").empty(); // Added this line
    $("#ultravel_invoices").empty(); // Added this line
    $("#activity-form input").val("");
    $("#activity-form .ace-switch").prop("checked", false);
    $("#activity-form select").val(null).trigger("change");
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

    $("#activity-form #service-type").val(
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "service_type"
        )
    );

    $("#activity-form #auditor-type").val( // Added this block
        jQuery("#activityGrid").jqGrid(
            "getCell",
            jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
            "auditor_type"
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
    
    var paidValue = jQuery("#activityGrid").jqGrid(
        "getCell",
        jQuery("#activityGrid").jqGrid("getGridParam", "selrow"),
        "paid"
    );
    $("#activity-form #paid-status").val(
        $(document.createElement('div')).html(paidValue).text()
    );

    // Load uploaded files
    Utils.filesToList("ultraining_request_form", "activityGrid", "training_request_form");
    Utils.filesToList("ulattendance_list", "activityGrid", "attendance_list");
    Utils.filesToList("ulcustomer_feedback_form", "activityGrid", "customer_feedback_form");
    Utils.filesToList("ulattendance_certificates", "activityGrid", "attendance_certificates");
    Utils.filesToList("ulinvoice_inbound", "activityGrid", "invoice_inbound"); // Added this line
    Utils.filesToList("ultravel_invoices", "activityGrid", "travel_invoices"); // Added this line

    $("#activityModal").prop("submit", 1); // edit
    TP.filesUploaded = [];
    $("#activityModal").modal("show");
},
toggleFieldEditability: function() {
    if (!TP.isAdmin) {
        // Disable fields for auditors
        $("#paid-on").prop('disabled', true).addClass('disabled-field');
        $("#invoice-number-outbound").prop('disabled', true).addClass('disabled-field');
        $("#paid-status").prop('disabled', true).addClass('disabled-field');
    } else {
        // Enable fields for admins
        $("#paid-on").prop('disabled', false).removeClass('disabled-field');
        $("#invoice-number-outbound").prop('disabled', false).removeClass('disabled-field');
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
    let serviceTypeVal = $("#activity-form #service-type").val();
    let auditorTypeVal = $("#activity-form #auditor-type").val();
    doc.service_type = serviceTypeVal ? serviceTypeVal.trim() : "";
    doc.auditor_type = auditorTypeVal ? auditorTypeVal.trim() : "";
    doc.invoice_number_inbound = $("#activity-form #invoice-number-inbound").val().trim();
    doc.invoice_date_inbound = $("#activity-form #invoice-date-inbound").val().trim();
    doc.travel_expenses = $("#activity-form #travel-expenses").val().trim();
    
    if (TP.isAdmin) {
        doc.paid_on = $("#activity-form #paid-on").val().trim();
        doc.invoice_number_outbound = $("#activity-form #invoice-number-outbound").val().trim();
        doc.paid = $("#activity-form #paid-status").val().trim();
    } else {
        // For non-admin users, get these values from the original record if editing
        var originalRow = null;
        if (doc.id) { // Only if editing existing record
            originalRow = jQuery("#activityGrid").jqGrid(
                "getRowData",
                jQuery("#activityGrid").jqGrid("getGridParam", "selrow")
            );
        }
        
        doc.paid_on = originalRow ? originalRow.paid_on : "";
        doc.invoice_number_outbound = originalRow ? originalRow.invoice_number_outbound : "";
        doc.paid = originalRow ? originalRow.paid : "No";
    }

    doc.training_request_form = Utils.filesToJSON("ultraining_request_form");
    doc.attendance_list = Utils.filesToJSON("ulattendance_list");
    doc.customer_feedback_form = Utils.filesToJSON("ulcustomer_feedback_form");
    doc.attendance_certificates = Utils.filesToJSON("ulattendance_certificates");
    doc.invoice_inbound = Utils.filesToJSON("ulinvoice_inbound"); // Added this line
    doc.travel_invoices = Utils.filesToJSON("ultravel_invoices"); // Added this line
    
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

/*
    if ($("#activity-form #invoice-number-inbound").val().trim() == "") {
        Utils.notifyInput($("#activity-form #invoice-number-inbound"), "Invoice number is required");
        $("#activityModal .form-warning").show();
        return false;
    }
    
    if ($("#activity-form #invoice-date-inbound").val().trim() == "") {
        Utils.notifyInput($("#activity-form #invoice-date-inbound"), "Invoice date is required");
        $("#activityModal .form-warning").show();
        return false;
    }
  */  
    // Validate at least one document is uploaded
    /*
    if ($("#ultraining-request li").length == 0 && 
        $("#ulattendance li").length == 0 && 
        $("#ulfeedback li").length == 0 && 
        $("#ulcertificates li").length == 0) {
        Utils.notifyInput($("#activity-form .upload-area"), "At least one document must be uploaded");
        $("#activityModal .form-warning").show();
        return false;
    }
        */

   // Only validate payment fields if user is admin
    if (TP.isAdmin) {
        if ($("#activity-form #invoice-number-outbound").val().trim() == "") {
            Utils.notifyInput($("#activity-form #invoice-number-outbound"), "Outbound invoice number is required");
            $("#activityModal .form-warning").show();
            return false;
        }
        
        if ($("#activity-form #paid-status").val().trim() == "") {
            Utils.notifyInput($("#activity-form #paid-status"), "Paid status is required");
            $("#activityModal .form-warning").show();
            return false;
        }
        
        // Only require paid_on date if payment status is "Yes"
        var paidStatus = $("#activity-form #paid-status").val().trim();
        if (paidStatus === "Yes" && $("#activity-form #paid-on").val().trim() == "") {
            Utils.notifyInput($("#activity-form #paid-on"), "Paid on date is required when payment status is 'Yes'");
            $("#activityModal .form-warning").show();
            return false;
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

            // Track activity changes
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
                //Common.sendAddActionRequest(d);
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
    
        // If user is not admin, handle restricted fields
    if (!TP.isAdmin) {
        var activityId = $("#activity-form #activity-id").val();
        
        // For existing records (editing)
        if (activityId && activityId !== "") {
            var originalRow = jQuery("#activityGrid").jqGrid(
                "getRowData",
                jQuery("#activityGrid").jqGrid("getGridParam", "selrow")
            );
            doc.paid_on = originalRow.paid_on;
            doc.invoice_number_outbound = originalRow.invoice_number_outbound;
            doc.paid = originalRow.paid;
        } 
        // For new records
        else {
            // Set default values for restricted fields
            doc.paid_on = "";
            doc.invoice_number_outbound = "";
            doc.paid = "No"; // Default value for new records
        }
    }

     
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

  // Initialize when document is ready
  init: function() {
    $(document).ready(function() {
      TP.onDocumentReady();
    });
  }
};

// Initialize the TP object
TP.init();
</script>

</body>
</html>