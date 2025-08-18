<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <title>Tasks - Halal Digital</title>
    <style>

    </style>
    
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.12/css/fixedHeader.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.2.2/css/fixedColumns.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.3.0/css/scroller.dataTables.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
<style>      
.mt-4 {
            margin-top: 20px;
        }
        .mb-4 {
            margin-bottom: 20px;
        }
        .mb-1 {
            margin-bottom: 10px;
        }
        .scrollable {
            max-height: 185px;
            overflow-y: auto;
        }
        .list-group-item:nth-child(odd) {
            background-color: #f9f9f9;
        }

        .centered-tabs {
    text-align: center; /* Centers the tabs */
}

.centered-tabs .nav-item {
    display: inline-block; /* Makes the list items inline */
    float: none !important;
    font-size:16px;
    font-weight: bold;
}

.centered-tabs .nav-tabs {
    display: inline-block; /* Keeps the tab list as inline block */
}

.nav-tabs>li>a, .nav-tabs>li>a:focus {
   
    margin-right: 5px;
   
    padding: 10px 20px;
}
.tab-pane {
    padding:10px;
    border :1px #ccc solid;
}

.nav-tabs>li>a.active {
    background-color: #FFF;
    color: #4C8FBD;
    border-color: #C5D0DC;
    border-bottom: 1px #fff solid;
}

    </style>
</head>

<body>
<?php
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
    $myuser = cuser::singleton();
    $myuser->getUserData();
  
  $isClient = $myuser->userdata['isclient'] == "1" ? true : false;
  $isAuditor = $myuser->userdata['isclient'] == '2' ? true : false;
  //$isSuperAdmin = $myuser->userdata['superadmin'] == "1" ? true : false;
  $isAdmin = (!$isClient && !$isAuditor);

    //$sql = "SELECT id, name, email, prefix, isclient FROM tusers WHERE (isclient=0 || isclient=2) AND id <> '".$myuser->userdata["id"]."' AND name <> '' ORDER BY isclient, name";
    $sql = "SELECT id, name, email, prefix, isclient FROM tusers WHERE (isclient=0 || isclient=2) AND name <> '' ORDER BY isclient, name";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	if(!$stmt->execute()) {
		//echo json_encode(generateErrorResponse("Getting auditors list failed"));
		//die();
	}
	$auditors = $stmt->fetchAll();

	//$sql = "SELECT id, name, email, prefix, isclient FROM tusers WHERE isclient=1 AND id <> '".$myuser->userdata["id"]."' AND  name <> '' ORDER BY isclient, name";
    
    $sql = "SELECT id, name, email, prefix, isclient FROM tusers WHERE isclient=1 AND  name <> '' ORDER BY isclient, name";
	

    
if ($isAuditor) { // Auditor
    $ids = [-1];
    $clients_audit = $myuser->userdata['clients_audit'];
    if ($clients_audit != "") {
      $ids = json_decode($clients_audit);
    }
     $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND  name <> '' AND deleted = 0 AND id IN (".implode(",", $ids).") ORDER BY name";
}
 
else   { // Admin
  $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND  name <> '' AND deleted = 0  ORDER BY name";
}
$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	if(!$stmt->execute()) {
		//echo json_encode(generateErrorResponse("Getting clients list failed"));
		//die();
	}
	$clients = $stmt->fetchAll();

    if (!isset($_GET["idauditor"]) && $isAuditor) {
        $_GET["idauditor"] = $myuser->userdata["id"];
    }
?>
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">
                    <?php if ($isAdmin): ?>
    <h3>All Assigned Tasks</h3>
    <p class="text-muted">Manage and track all tasks assigned to team members and auditors.</p>
<?php else: ?>
    <h3>My Tasks</h3>
    <p class="text-muted">View and track the tasks assigned to you.</p>
<?php endif; ?>    
                    <input type="hidden" name="taskId" id="taskId" value="" />
                    <input type="hidden" name="taskStatus" id="taskStatus" value="1" />
                    <input type="hidden" name="tmytasks" id="tmytasks" value="<?php echo $isAdmin ? "0" : "1"; ?>" />
                    <?php // if (!$myuser->userdata['isclient']): ?>
                    <div class="row gutters">
                        <div class="col-md-4">
              
                    </div>
                        <div class="col-md-8">
                    <label class="right">
                        <input id="filter-actions-confirmed" class="ace ace-switch ace-switch-4" type="checkbox">
                        <span class="lbl">&nbsp;&nbsp;Show completed tasks</span>

                        
        
            <a href="#" id="create-task-btn" class="btn btn-primary" data-toggle="modal" data-target="#tasksModal">
                <i class="fa fa-plus"></i> Create Task
            </a>
        
    
                    </label>
                        </div>
                    </div>  
                    <?php // endif; ?>

                    <?php  //if ($myuser->userdata['isclient'] == "2"): 
  
 // Assuming the database connection is stored in $dbo
 $auditorId = $myuser->userdata["id"]; // Assuming the auditor ID is stored in session
 
 // Query for "Assigned to Me"
 $assignedQuery = "SELECT COUNT(*) AS assigned_count FROM ttasks WHERE status = 1 AND idauditor = :auditor_id";
 $assignedStmt = $dbo->prepare($assignedQuery);
 $assignedStmt->bindValue(':auditor_id', $auditorId, PDO::PARAM_INT);
 $assignedStmt->execute();
 $assignedResult = $assignedStmt->fetch(PDO::FETCH_ASSOC);
 $assignedCount = (int)$assignedResult['assigned_count'];
 
 // Query for "Created by Me"
 $createdQuery = "SELECT COUNT(*) AS created_count FROM ttasks WHERE status = 1 AND user_id = :auditor_id";
 $createdStmt = $dbo->prepare($createdQuery);
 $createdStmt->bindValue(':auditor_id', $auditorId, PDO::PARAM_INT);
 $createdStmt->execute();
 $createdResult = $createdStmt->fetch(PDO::FETCH_ASSOC);
 $createdCount = (int)$createdResult['created_count'];

                        ?>
                       <ul class="nav nav-tabs centered-tabs" id="taskTabs" role="tablist">
    <?php if ($isAdmin): ?>
        <li class="nav-item active" role="presentation">
            <a class="nav-link" id="createdByMe-tab" data-toggle="tab" href="#createdByMe" role="tab" aria-controls="createdByMe" aria-selected="true">
                Created by Me 
                <span class="badge badge-info"><?php echo $createdCount; ?></span> 
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="assignedToMe-tab" data-toggle="tab" href="#assignedToMe" role="tab" aria-controls="assignedToMe" aria-selected="false">
                Assigned to Me 
                <span class="badge badge-info"><?php echo $assignedCount; ?></span> 
            </a>
        </li>
    <?php else: ?>
        <li class="nav-item active" role="presentation">
            <a class="nav-link" id="assignedToMe-tab" data-toggle="tab" href="#assignedToMe" role="tab" aria-controls="assignedToMe" aria-selected="true">
                Assigned to Me 
                <span class="badge badge-info"><?php echo $assignedCount; ?></span> 
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="createdByMe-tab" data-toggle="tab" href="#createdByMe" role="tab" aria-controls="createdByMe" aria-selected="false">
                Created by Me 
                <span class="badge badge-info"><?php echo $createdCount; ?></span> 
            </a>
        </li>
    <?php endif; ?>
</ul>

<?php //endif; ?>
<div class="tab-pane" >

<div class="row">
    <div class="form-group col-xs-12 col-sm-6 col-md-3" id="tfilter" <?php echo $isAdmin ? '' : 'style="display:none !important;"'; ?>>
        <label for="tidauditor">Filter by Auditor or Team Member</label>
        <select class="form-control" id="tidauditor">
            <option value="">Please Select</option>        
            <optgroup label="Team Members">
                <?php foreach ($auditors as $auditor): ?>
                    <?php if ($auditor["isclient"] == 0): ?>
                        <option value="<?php echo $auditor["id"]; ?>" 
                            <?php if (isset($_GET["idauditor"]) && $auditor["id"] == $_GET["idauditor"]) echo 'selected'; ?>>
                            <?php echo $auditor["name"]; ?> <?php if($auditor["id"] == $myuser->userdata['id']) echo "(You)"; ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="Auditors">
                <?php foreach ($auditors as $auditor): ?>
                    <?php if ($auditor["isclient"] == 2): ?>
                        <option value="<?php echo $auditor["id"]; ?>" 
                            <?php if (isset($_GET["idauditor"]) && $auditor["id"] == $_GET["idauditor"]) echo 'selected'; ?>>
                            <?php echo $auditor["name"]; ?> <?php if($auditor["id"] == $myuser->userdata['id']) echo "(You)"; ?> 
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </optgroup>
        </select>
    </div>
</div>
                    <table id="table_tasks" class="table table-hover table-striped table-bordered w-100" style="width:100%;">
                        <thead>
                            <tr class="tableheader">
                            <th class="no-wrap">Reference #</th>
                            <th class="no-wrap">Task for</th>
                            <th class="no-wrap">Auditor</th>  
                            <th class="no-wrap">Client</th>                            
                            <th class="no-wrap">Category</th>                            
                            <th class="no-wrap">Task</th>                            
                            <th class="no-wrap">Status</th>     
                            <th class="no-wrap">Created by</th>                            
                            <th class="no-wrap">Created On</th>                            
                            <th class="no-wrap">Last Updated</th>                            
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                            </div>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
</div><!-- /.main-container -->
<div class="modal fade" id="postReplyModal" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="notesModalLabel">Reference #<span id="referenceNo"></span></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="clientname" id="clientname" value="" />
            <div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="issueType" style="font-weight: bold;">Category: </label>
            <span id="tIssueType">Bug</span> <!-- Replace "Bug" with the actual issue type value -->
        </div>
        <div class="form-group">
        <label for="tusername" style="font-weight: bold;">Created by: </label>
        <span id="tusername"></span> <!-- Replace with actual URL -->
        </div>     

    </div>
    <div class="col-md-4">
    <div class="form-group">
            <label for="tauditorname" style="font-weight: bold;">Auditor: </label>
            <span id="tauditorname"></span> <!-- Replace with actual URL -->
            
        </div>
        <div class="form-group">
        <label for="dateCreated" style="font-weight: bold;">Created On: </label>
        <span id="dateCreated"></span> <!-- Replace with actual URL -->
        </div>     


    </div>
    <div class="col-md-4">
    <div class="form-group">
            <label for="tclientname" style="font-weight: bold;">Client: </label>
            <span id="tclientname"></span> <!-- Replace with actual URL -->
        </div>     

        <div class="form-group">
        <label for="lastUpdated" style="font-weight: bold;">Last Updated: </label>
        <span id="lastUpdated"></span> <!-- Replace with actual URL -->
        </div>          

         
    </div>
    <div class="col-md-4">
    

        <div class="form-group">
            <label for="status" style="font-weight: bold;">Status: </label>
            <span id="status"><span class="badge badge-success">Open</span></span> <!-- Replace with actual URL -->
            <input type="hidden" name="status_val" id="status_val" value="" />
        </div>          

         
    </div>
</div>

<div class="row">
    <div class="col-md-12 text-right">
        <a href="#" class="btn btn-danger" id="btnClosetTask" style="display:none;">Complete Task</a>
    </div>
</div>

<!--
<div class="form-group">
            <label for="issueDescription" style="font-weight: bold;">Issue Description</label>
            <p id="issueDescription"> </p> 
            <span id="attachments"></span> 
        </div>
                            -->
        <label for="issueDescription" style="font-weight: bold;">Messages</label>
        <div class="list-group scrollable" id="replies">
            
            <!-- Additional replies can be added here -->
        </div>
                <div id="postReplyForm">
                    <div id="alertMessage"></div>
                    <form id="replyForm">
                        <div class="form-group">
                            <label for="replyMessage" style="font-weight: bold;">Reply</label>
                            <textarea class="form-control" id="replyMessage" rows="3" placeholder="Enter your reply here"></textarea>
                        </div>
                        <div class="form-group">
            <label for="attachment">Attachment (Screenshot, Excel, PDF file etc.)</label>
            <span class="fileinput-button" id="dropzone144">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload144" type="file" foldertype="addoc144" subfolder="Tickets" infotype="tickets" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="uladdoc144"></ul>
                            <div class="alert-string"></div>         
            </div>                         
                        <button type="button" class="btn btn-primary" id="btnPostReply">Post Reply</button>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
             </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tasksModal" tabindex="-1" role="dialog" aria-labelledby="tasksModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="tasksModal">Create Task <!--→ Assign Task to Auditor--></h4>
      </div>
      <div class="modal-body">
  <p class="text-muted">
    First select who this task is for, then provide the details.
  </p>
  
  <!-- Step 1: Select Task Type -->
<div class="form-group">
  <label><strong>This task is for:</strong></label><br>
  <div style="display: inline-block; margin-right: 15px;">
    <input type="radio" id="taskTypeAuditor" name="task_type" value="auditor" checked>
    <label for="taskTypeAuditor">An Auditor/Team Member</label>
  </div>
  <div style="display: inline-block;">
    <input type="radio" id="taskTypeClient" name="task_type" value="client">
    <label for="taskTypeClient">A Client</label>
  </div>
</div>

  <!-- Step 2: Dynamic Fields Based on Selection -->
  <div id="auditorTaskFields">
    <div class="form-group">
      <label for="idauditor"><strong>Assign to:</strong></label>
      <select class="form-control" id="idauditor">
        <option value="">Please Select</option>
        
        <optgroup label="Team Members">
            <?php foreach ($auditors as $auditor): ?>
                <?php if ($auditor["isclient"] == 0): ?>
                    <option value="<?php echo $auditor["id"]; ?>" 
                        <?php if (isset($_GET["idauditor"]) && $auditor["id"] == $_GET["idauditor"]) echo 'selected'; ?>>
                        <?php echo $auditor["name"]; ?> <?php if($auditor["id"] == $myuser->userdata['id']) echo "(You)"; ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </optgroup>

        <optgroup label="Auditors">
            <?php foreach ($auditors as $auditor): ?>
                <?php if ($auditor["isclient"] == 2): ?>
                    <option value="<?php echo $auditor["id"]; ?>" 
                        <?php if (isset($_GET["idauditor"]) && $auditor["id"] == $_GET["idauditor"]) echo 'selected'; ?>>
                        <?php echo $auditor["name"]; ?> <?php if($auditor["id"] == $myuser->userdata['id']) echo "(You)"; ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </optgroup>
      </select>
    </div>
    <div class="form-group">
      <label for="idclient"><strong>Related Client:</strong></label>
      <select class="form-control" id="idclient">
        <option value="">Select Client</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client["id"]; ?>" <?php if ($client["id"] == $_GET["idclient"]):?>selected<?php endif; ?>><?php echo $client["name"]; ?> - <?php echo $client["prefix"]; ?><?php echo $client["id"]; ?></option>
                <?php endforeach; ?>
      </select>
    </div>
  </div>
  
  <div id="clientTaskFields" style="display:none;">
    <div class="form-group">
      <label for="idclient"><strong>Client:</strong></label>
      <select class="form-control" id="idclient_for_client">
        <option value="">Select Client</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client["id"]; ?>" <?php if ($client["id"] == $_GET["idclient"]):?>selected<?php endif; ?>><?php echo $client["name"]; ?> - <?php echo $client["prefix"]; ?><?php echo $client["id"]; ?></option>
                <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label for="assigned_auditor"><strong>Assigned Auditor:</strong></label>
      <select class="form-control" id="assigned_auditor">
         <option value="">Please Select</option>
        
        <optgroup label="Team Members">
            <?php foreach ($auditors as $auditor): ?>
                <?php if ($auditor["isclient"] == 0): ?>
                    <option value="<?php echo $auditor["id"]; ?>" 
                        <?php if (isset($_GET["idauditor"]) && $auditor["id"] == $_GET["idauditor"]) echo 'selected'; ?>>
                        <?php echo $auditor["name"]; ?> <?php if($auditor["id"] == $myuser->userdata['id']) echo "(You)"; ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </optgroup>

        <optgroup label="Auditors">
            <?php foreach ($auditors as $auditor): ?>
                <?php if ($auditor["isclient"] == 2): ?>
                    <option value="<?php echo $auditor["id"]; ?>" 
                        <?php if (isset($_GET["idauditor"]) && $auditor["id"] == $_GET["idauditor"]) echo 'selected'; ?>>
                        <?php echo $auditor["name"]; ?> <?php if($auditor["id"] == $myuser->userdata['id']) echo "(You)"; ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </optgroup>
      </select>
    </div>
  </div>
      
        <div class="form-group">
            <label for="issueType"><strong>Category</strong></label>
            <select class="form-control" id="issueType">
            <option value="">Select Category</option>
            <option value="Invoicing">Invoicing</option>
            <option value="Book keeping">Book keeping</option>
                <option value="Audit">Audit</option>
                <option value="Ezone">Ezone</option>
                <option value="Customer Contact / Service">Customer Contact / Service</option>
                <option value="Training">Training</option>
                <option value="Certificate">Certificate</option>
                <option value="Shipment certificate">Shipment certificate</option>
                <option value="Others">Others</option>
            </select>
          </div>
          <div class="form-group">
            <label for="issueDescription"><strong>Description</strong></label>
            <textarea class="form-control" id="issueDescription" rows="5" placeholder="Describe the request in detail."></textarea>
          </div>
          
        
          <div class="form-group">
          <label for="attachment"><strong>Attachment (Screenshot, Excel, PDF file etc.)</strong></label>
            <span class="fileinput-button" id="dropzone244">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload244" type="file" foldertype="addoc244" subfolder="Tickets" infotype="tickets" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="uladdoc244"></ul>
                            <div class="alert-string"></div>         
            </div>          
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" form="taskForm" class="btn btn-primary" id="btnSubmitTask">Submit</button>
      </div>
    </div>
  </div>
</div>

<?php include_once('pages/footer.php');?>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>  
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js  "></script>  
<script src="https://cdn.datatables.net/fixedcolumns/4.2.2/js/dataTables.fixedColumns.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.3.0/js/dataTables.scroller.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="js/all.js"></script>

<script>

    
$(document).ready(function() {

    $('input[name="task_type"]').change(function() {
    if ($(this).val() === 'auditor') {
      $('#auditorTaskFields').show();
      $('#clientTaskFields').hide();
    } else {
      $('#auditorTaskFields').hide();
      $('#clientTaskFields').show();
    }
  });
$("#btnSubmitTask").on("click", function () {
    var texts = [];
    $("#uladdoc244 li").each(function () {
        var spanText = $(this).find("span:first").text();
        texts.push(spanText);
    });

    // Determine task type based on which radio is selected
    var taskType = $("input[name='task_type']:checked").val();
    var idauditor = "";
    var idclient = "";

    if (taskType === "auditor") {
        // Task is for auditor - use selected auditor and optional client
        idauditor = $("#idauditor").val();
        idclient = $("#idclient").val(); // This remains optional
    } else {
        // Task is for client - we need both client and assigned auditor
        idclient = $("#idclient_for_client").val();
        idauditor = $("#assigned_auditor").val();
    }

    var formData = {
        uid: 0,
        rtype: "createTask",
        idauditor: idauditor,
        idclient: idclient,
        issueType: $("#issueType").val(),
        issueDescription: $("#issueDescription").val(),
        attachments: texts.join(", "),
        taskType: taskType // Send task type to server for additional validation if needed
    };

    $.ajax({
        url: "ajax/ajaxHandler.php",
        type: "POST",
        data: formData,
        success: function (response) {
            var jsonResponse = JSON.parse(response);
            if (jsonResponse.data.errors.length > 0) {
                $("#taskErrors")
                    .show()
                    .html("<ul>" + jsonResponse.data.errors + "</ul>");
            } else {
                $("#tasksModal").modal("hide");
                $("#taskForm")[0].reset();
                var table_tasks = $('#table_tasks').DataTable(); 
                table_tasks.ajax.reload(null, false);
                alert("Task created successfully!");
                $('#tasksModal').modal('hide');            
            }
        },
    });
});

    Common.setMainMenuItem("tasks");

    var windowHeight = $(window).height();
    var minimumDataTableHeight = 200;
    var calculatedDataTableHeight = Math.max(windowHeight - 295, minimumDataTableHeight);

    var table_tasks = $('#table_tasks').DataTable({
       // paging: true,
        //lengthChange: false,
        searching: false,
        ordering: true,
         //info: true,
         pageLength: 625,

         /*
         scrollCollapse: true,
        scroller: {
        loadingIndicator: false},
        */
        processing: true,
        serverSide: true,
        scrollY: calculatedDataTableHeight, // Set scrollY to the calculated height
        scrollX: true,
      
        ajax: {
            url: "ajax/getTasks.php",
            type: "POST",
            async: true,
            data: function (data) {
                data.status = $('#taskStatus').val();
                data.idauditor = $('#tidauditor').val();
                data.mytasks = $('#tmytasks').val();
            }
        },
        columns: [
            { data: "id" },
            { data: "task_type" },
             { data: "auditorname" },
             { data: "clientname" },
            { data: "issue_type" },
            { data: "issue_description" },
            { data: "status" },
            { data: "username" },
            { data: "date_created" },
            { data: "last_updated" },
        ],
        columnDefs: [
            { targets: 'no-sort', orderable: false },
        ],
        createdRow: function(row, data, dataIndex) {
            if (data.viewed == 0) {
                $(row).css('background-color', '#f2dede'); // Change this color as needed
            }
        }        
    });

    $('#filter-actions-confirmed').on('change', function (e) {
        $("#taskStatus").val($(this).is(":checked") ? '0' : '1');
        table_tasks.ajax.reload(null, false);
    });


    $('#idclient').on('change', function() {
		table_tasks.ajax.reload(null, false);
	});

    $("#btnClosetTask").on('click', function() {
        if (confirm("Are you sure you want to close this task?")) {
            var id = $("#taskId").val();
            var formData = {
                id: id,
            };      
            $.post('ajax/ajaxHandler.php', {
              rtype: 'closeTask',
              uid: 0,
              data: formData,
            }).done(function (response) {
                    table_tasks.ajax.reload(null, false);
                    $('#postReplyModal').modal('hide');            
            });
         }   		
        return false;
	});

    function getTaskData(id) {
        var formData = {
            id: id,
        };      
        $.ajax({
          url: 'ajax/getTask.php',
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
              if (response) {
                // Populate form fields with retrieved data
                $('#postReplyModal #clientname').val(response.clientname);
                $('#referenceNo').html(response.id);
                $('#tusername').html(response.username);
                $('#tauditorname').html(response.auditorname);
                                $('#tclientname').html(response.clientname);
                $('#tIssueType').html(response.issue_type);
                //$('#issueDescription').html(response.issue_description);
                 $('#attachments').html(response.attachments);
                $('#dateCreated').html(response.date_created);
                $('#lastUpdated').html(response.last_updated);
                if (response.iscreator == "1") {
                    if (response.status == '1') {
                        $('#status').html('<span class="badge badge-success">Open</span>');
                        $('#btnClosetTask').show();
                    } else {
                        $('#status').html('<span class="badge badge-danger">Closed</span>');
                        $('#btnClosetTask').hide();
                    }
                }
                $('#status_val').html(response.status);
                $('#replies').html(response.replies);
                var scrollDiv = $('#replies');
                scrollDiv.scrollTop(scrollDiv[0].scrollHeight);                
              }
            }
      });        
     }
    $(document).on('click', '.post-reply', function() {
        var id = $(this).attr("id");
        $("#taskId").val(id);
      getTaskData(id);
      $('#postReplyModal').modal('show');
      return false;
    });

    $('#tidauditor').on('change', function () {
        var table_tasks = $('#table_tasks').DataTable(); 
        table_tasks.ajax.reload(null, false);
    });

    $('#postReplyModal').on('shown.bs.modal', function () {
        var scrollDiv = $('#replies');
        scrollDiv.scrollTop(scrollDiv[0].scrollHeight);
        $("#replyMessage").val("");
    });

 
    $('#btnPostReply').on('click', function(e) {
          e.preventDefault();

          var texts = [];

      $('#uladdoc144 li').each(function() {
          var spanText = $(this).find('span:first').text();
          texts.push(spanText);
      });
      var attachments = texts.join(', ');

          var formData = {
              taskId: $('#taskId').val(), // Assuming you have a hidden input field with id 'taskId' to store task ID
              message: $("#replyMessage").val(),
              attachments: attachments
          };

          // Send Ajax request
          $.post('ajax/ajaxHandler.php', {
              rtype: 'postReply',
              uid: 0,
              data: formData,
          }).done(function (response) {
              response = JSON.parse(response);
              console.log(response);
              if (response.data.errors) {
             
                $('#alertMessage').removeClass('alert-success').addClass('alert-danger').html("<ul>"+response.data.errors+"</ul>").fadeIn();
            } else {
   // Show error message in alert div
   var id =  $("#taskId").val();
                getTaskData(id);                
                // Show success message
                $('#alertMessage').removeClass('alert-danger').addClass('alert-success').text('Reply successfully sent!').fadeIn().delay(3000).fadeOut();

                // Reload or update the DataTable, assuming you have a DataTable instance called table_tasks
                table_tasks.ajax.reload(null, false);
            }
          }).fail(function (xhr, status, error) {
              // Handle Ajax error here
              
          });
          return false;
    });

    $('#fileupload144')
  .fileupload({
    url: 'fileupload/ProcessFiles.php',
    dataType: 'json',
    dropZone: $('#dropzone144'),
    add: function (e, data) {
      data.formData = {
        folderType: $(this).attr('foldertype'),
        infoType: $(this).attr('infotype'),
        subFolder: $(this).attr('subfolder'),
        client: $('#postReplyModal #clientname').val(),
      };
      var goUpload = true;
      var uploadFile = data.files[0];
      if (!/\.(jpg|jpeg|png|gif|xls|xlsx|pdf)$/i.test(uploadFile.name)) {
    alert('You can upload JPG, JPEG, PNG, GIF, Excel or PDF file(s) only');
    goUpload = false; // Prevent form submission
}

      if (goUpload == true) {
        data.submit();
      }
    },
    start: function (e) {
      $(this).parent().siblings('.loader').show();
    },
    fail: function (e, data) {
      // kill all progress bars awaiting for showing
      $(this).parent().siblings('.loader').hide();
      alert('Error uploading file (' + data.errorThrown + ')');
    },
    done: function (e, data) {
      // hide loader and add new li with new file info
      $(this).parent().siblings('.loader').hide();
      $.each(data.result.files, function (index, file) {
        var jsonstring =
          '{"name":"' +
          file.name +
          '","glink":"' +
          file.googleDriveUrl +
          '","hostpath":"' +
          file.url +
          '","hostUrl":"' +
          file.hostUrl +
          '"}';
        var ell;
        /*
        if (file.name.length > 35) ell = file.name.substr(0, 30) + '...';
        else ell = file.name;
        */
        ell = file.name;
        var filename = $(
          '<li class="uploaded-file-name" originalname="' +
            encodeURI(jsonstring) +
            '"></li>'
        );
        filename.append($('<span>', { text: ell }));
        filename.append(
          $(
            '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
              'fileid=' +
              file.googleDriveId +
              ' hostpath=' +
              encodeURI(file.url) +
              ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
          ).bind('click', function (e) {
            delDocClick(e);
          })
        );
        // add li to the list of the appropriate ul - class from folderType
        $('#ul' + file.folderType).append(filename);
      });
    },
  })
  .prop('disabled', !$.support.fileInput)
  .parent()
  .addClass($.support.fileInput ? undefined : 'disabled');

  $('#taskTabs a').on('shown.bs.tab', function (e) {
    var targetTab = $(e.target).attr("href"); // Get the target tab ID
    if (targetTab == "#assignedToMe") {
        $("#tmytasks").val("1");
        <?php if ($isAdmin): ?>
        $("#tfilter").hide();
        <?php endif; ?>
    }
    else {
        $("#tmytasks").val("0");
        <?php if ($isAdmin): ?>
        $("#tfilter").show();
        <?php endif; ?>
    }    
    var table_tasks = $('#table_tasks').DataTable(); 
        table_tasks.ajax.reload(null, false);

    return false;
    // You can put your logic here for when a tab is shown
});

$("#fileupload244")
    .fileupload({
      url: "fileupload/ProcessFiles.php",
      dataType: "json",
      dropZone: $("#dropzone244"),
      add: function (e, data) {
        data.formData = {
          folderType: $(this).attr("foldertype"),
          infoType: $(this).attr("infotype"),
          subFolder: $(this).attr("subfolder"),
          client: $("#tasksModal #clientname").val(),
        };
        var goUpload = true;
        var uploadFile = data.files[0];
        if (!/\.(jpg|jpeg|png|gif|xls|xlsx|pdf)$/i.test(uploadFile.name)) {
            alert('You can upload JPG, JPEG, PNG, GIF, Excel or PDF file(s) only');
          goUpload = false; // Prevent form submission
        }
        if (goUpload == true) {
          data.submit();
        }
      },
      start: function (e) {
        $(this).parent().siblings(".loader").css("display", "block").show();
      },
      fail: function (e, data) {
        // kill all progress bars awaiting for showing
        $(this).parent().siblings(".loader").hide();
        alert("Error uploading file (" + data.errorThrown + ")");
      },
      done: function (e, data) {
        // hide loader and add new li with new file info
        $(this).parent().siblings(".loader").hide();
        $.each(data.result.files, function (index, file) {
          var jsonstring =
            '{"name":"' +
            file.name +
            '","glink":"' +
            file.googleDriveUrl +
            '","hostpath":"' +
            file.url +
            '","hostUrl":"' +
            file.hostUrl +
            '"}';
          var ell;
          /*
        if (file.name.length > 35) ell = file.name.substr(0, 30) + '...';
        else ell = file.name;
        */
          ell = file.name;
          var filename = $(
            '<li class="uploaded-file-name" originalname="' +
              encodeURI(jsonstring) +
              '"></li>'
          );
          filename.append($("<span>", { text: ell }));
          filename.append(
            $(
              '<span class="btn btn-danger delete uploaded-file-name-close remove-doc" type="button" ' +
                "fileid=" +
                file.googleDriveId +
                " hostpath=" +
                encodeURI(file.url) +
                ' title="Remove the document"><i class="glyphicon glyphicon-remove"></i>&nbsp;Delete</span>'
            ).bind("click", function (e) {
              delDocClick(e);
            })
          );
          // add li to the list of the appropriate ul - class from folderType
          $("#ul" + file.folderType).append(filename);
        });
      },
    })
    .prop("disabled", !$.support.fileInput)
    .parent()
    .addClass($.support.fileInput ? undefined : "disabled");

});
 
</script>
</body>
</html>