<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <title>Customer Service - Halal e-Zone</title>
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
    text-align: center;
    margin-bottom: 20px;
}

.centered-tabs .nav-item {
    display: inline-block;
    float: none !important;
    font-size:16px;
    font-weight: bold;
}

.centered-tabs .nav-tabs {
    display: inline-block;
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
    $dbo =  $db->connect();
    $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 ORDER BY name";
    $stmt = $dbo->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    if(!$stmt->execute()) {
        echo json_encode(generateErrorResponse("Getting clients list failed"));
        die();
    }
    $clients = $stmt->fetchAll();

    $sql = "SELECT id, name FROM tcompanies WHERE active=1 ORDER BY name";
    $stmt = $dbo->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    if(!$stmt->execute()) {
        echo json_encode(generateErrorResponse("Getting clients list failed"));
        die();
    }
    $companies = $stmt->fetchAll();

    $statusOptions = ['app', 'offer', 'soffer', 'declarations', 'dates', 'audit', 'checklist', 'report', 'dm', 'pop', 'certificate', 'additional_items', 'extension'];
?>
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">
                     <h3><?php if ($myuser->userdata['isclient'] == "1"): ?> My <?php else: ?> Client <?php endif; ?> Requests</h3>
                     <?php if (!$myuser->userdata['isclient']): ?>
                     <form id="searchForm" style="height:auto">
                        <div class="row" style="height:auto">
                            <div class="form-group col-md-3">
                                <label for="idclient">Client:</label>
                                <select class="form-control clientslist" id="idclient">
                                    <option value="">All Clients</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?php echo $client["id"]; ?>" <?php if ($client["id"] == $_GET["idclient"]):?>selected<?php endif; ?>><?php echo $client["name"]; ?> - <?php echo $client["prefix"]; ?><?php echo $client["id"]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                    <?php elseif ($myuser->userdata['isclient'] == '1'): ?>
                        <input type="hidden" id="idclient" value=<?php echo $_SESSION['halal']['id']; ?> data-clientname="<?php echo $myuser->userdata['name']," (",$myuser->userdata['id'],")"; ?>"/>
                    <?php endif; ?>
                    <input type="hidden" name="customerServiceId" id="customerServiceId" value="" />
                    <input type="hidden" name="ticketStatus" id="ticketStatus" value="1" />
                    <input type="hidden" name="ticketOrigin" id="ticketOrigin" value="all" />
                    
                    <!-- Tabs for All/Client Created/Internal Created - Only show for non-clients -->
                    <?php if ($myuser->userdata['isclient'] != "1"): ?>
                    <div class="centered-tabs">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="active">
                                <a data-toggle="tab" href="#all-tickets" data-origin="all">All Tickets</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#client-tickets" data-origin="client">Client Created</a>
                            </li>
                            <li>
                                <a data-toggle="tab" href="#internal-tickets" data-origin="internal">Internal Created</a>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <div class="tab-content" style="padding:0px !important; border:none !important;">
                        <!-- All Tickets Tab -->
                        <div id="all-tickets" class="tab-pane fade in active">
                            <div class="row gutters">
                                <label class="right">
                                    <input id="filter-all-confirmed" class="ace ace-switch ace-switch-4" type="checkbox">
                                    <span class="lbl">&nbsp;&nbsp;Show closed requests</span>
                                </label>
                            </div>
                            <table id="table_all_tickets" class="table table-hover table-striped table-bordered w-100" style="width:100%;">
                                <thead>
                                    <tr class="tableheader">
                                    <?php if ($myuser->userdata['isclient'] != "1"): ?>
                                        <th style="width:14%;">Client</th>
                                    <?php endif; ?>
                                    <th class="no-wrap">Reference #</th>
                                    <th class="no-wrap">Type</th>                            
                                    <th class="no-wrap">Request</th>                            
                                    <th class="no-wrap">Status</th>                                                        
                                    <th class="no-wrap">Created</th>                            
                                    <th class="no-wrap">Last Updated</th>
                                    <?php if ($myuser->userdata['isclient'] != "1"): ?>
                                    <th class="no-wrap">Origin</th>
                                    <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        
                        <!-- Client Created Tickets Tab - Only show for non-clients -->
                        <?php if ($myuser->userdata['isclient'] != "1"): ?>
                        <div id="client-tickets" class="tab-pane fade">
                            <div class="row gutters">
                                <label class="right">
                                    <input id="filter-client-confirmed" class="ace ace-switch ace-switch-4" type="checkbox">
                                    <span class="lbl">&nbsp;&nbsp;Show closed requests</span>
                                </label>
                            </div>
                            <table id="table_client_tickets" class="table table-hover table-striped table-bordered w-100" style="width:100%;">
                                <thead>
                                    <tr class="tableheader">
                                    <?php if ($myuser->userdata['isclient'] != "1"): ?>
                                        <th style="width:14%;">Client</th>
                                    <?php endif; ?>
                                    <th class="no-wrap">Reference #</th>
                                    <th class="no-wrap">Type</th>                            
                                    <th class="no-wrap">Request</th>                            
                                    <th class="no-wrap">Status</th>                                                        
                                    <th class="no-wrap">Created</th>                            
                                    <th class="no-wrap">Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        
                        <!-- Internal Created Tickets Tab - Only show for non-clients -->
                        <div id="internal-tickets" class="tab-pane fade">
                            <div class="row gutters">
                                <label class="right">
                                    <input id="filter-internal-confirmed" class="ace ace-switch ace-switch-4" type="checkbox">
                                    <span class="lbl">&nbsp;&nbsp;Show closed requests</span>
                                </label>
                            </div>
                            <table id="table_internal_tickets" class="table table-hover table-striped table-bordered w-100" style="width:100%;">
                                <thead>
                                    <tr class="tableheader">
                                    <?php if ($myuser->userdata['isclient'] != "1"): ?>
                                        <th style="width:14%;">Client</th>
                                    <?php endif; ?>
                                    <th class="no-wrap">Reference #</th>
                                    <th class="no-wrap">Type</th>                            
                                    <th class="no-wrap">Request</th>                            
                                    <th class="no-wrap">Status</th>                                                        
                                    <th class="no-wrap">Created</th>                            
                                    <th class="no-wrap">Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <?php endif; ?>
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
            <label for="requestType" style="font-weight: bold;">Request Type: </label>
            <span id="tRequestType">Bug</span>
        </div>

        <div class="form-group">
            <label for="lastUpdated" style="font-weight: bold;">Last Updated: </label>
            <span id="lastUpdated"></span>
      </div>  

    </div>
    <div class="col-md-4">
    <div class="form-group">
            <label for="currentURL" style="font-weight: bold;">URL: </label>
            <span id="currentURL"></span>
            
        </div>
        <div class="form-group">
            <label for="status" style="font-weight: bold;">Status: </label>
            <span id="status"><span class="badge badge-success">Open</span></span>
            <input type="hidden" name="status_val" id="status_val" value="" />
        </div>          

    </div>
    <div class="col-md-4">
    <div class="form-group">
            <label for="dateCreated" style="font-weight: bold;">Created: </label>
            <span id="dateCreated"></span>
        </div>     
         
    </div>
</div>
<div class="row">
    <div class="col-md-12 text-right">
        <a href="#" class="btn btn-danger" id="btnCloseCustomerService" style="display:none;">Close Request</a>
    </div>
</div>

        <label for="requestDescription" style="font-weight: bold;">Messages</label>
        <div class="list-group scrollable" id="replies">
            
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
    Common.setMainMenuItem("customerService");
    var windowHeight = $(window).height();
    var minimumDataTableHeight = 200;
    var calculatedDataTableHeight = Math.max(windowHeight - 295, minimumDataTableHeight);

    // Function to create column definitions
    function getColumns(origin) {
        var columns = [];
        
        <?php if ($myuser->userdata['isclient'] != "1"): ?>
        columns.push({ data: "username" });
        <?php endif; ?>
        
        columns.push({ data: "id" });
        columns.push({ data: "request_type" });
        columns.push({ data: "request_description" });
        columns.push({ 
            data: "status",
            render: function(data, type, row) {
                if (data == '1') {
                    return '<span class="badge badge-success">Open</span>';
                } else {
                    return '<span class="badge badge-danger">Closed</span>';
                }
            }
        });
        columns.push({ data: "date_created" });
        columns.push({ data: "last_updated" });

        
        // Add Origin column only for "all" tab and only for non-clients
        <?php if ($myuser->userdata['isclient'] != "1"): ?>
        if (origin === 'all') {
            columns.push({
                data: null,
                render: function(data, type, row) {
                    return row.is_client_created == '1' ? 
                        '<span class="label label-info">Client</span>' : 
                        '<span class="label label-warning">Internal</span>';
                },
                orderable: false
            });
        }
        <?php endif; ?>
        
        return columns;
    }

    // Function to initialize DataTable
    function initializeDataTable(tableId, origin, filterCheckboxId) {
        var table = $('#' + tableId).DataTable({
            searching: false,
            ordering: true,
            pageLength: 625,
            processing: true,
            serverSide: true,
            scrollX: true,
            <?php if ($myuser->userdata['isclient'] != "1"): ?>
            order: [[1, 'desc']],
            <?php else: ?>
            order: [[0, 'desc']],
            <?php endif; ?>
            ajax: {
                url: "ajax/getCustomerServices.php",
                type: "POST",
                async: true,
                data: function (data) {
                    data.idclient = $('#idclient').val();
                    data.status = $('#' + filterCheckboxId).prop('checked') ? '0' : '1';
                    data.origin = origin;
                }
            },
            columns: getColumns(origin),
            columnDefs: [
                { targets: 'no-sort', orderable: false },
            ],
            createdRow: function(row, data, dataIndex) {
                if (data.viewed == 0) {
                    //$(row).css('background-color', '#f2dede');
                }
            }
        });
        
        return table;
    }

    // Initialize the "All Tickets" table on page load
    var table_all = initializeDataTable('table_all_tickets', 'all', 'filter-all-confirmed');
    var table_client = null;
    var table_internal = null;

    <?php if ($myuser->userdata['isclient'] != "1"): ?>
    // Handle tab clicks - Only for non-clients
    $('.nav-tabs a').on('click', function (e) {
        e.preventDefault();
        var origin = $(this).data('origin');
        $('#ticketOrigin').val(origin);
        
        $(this).tab('show');
        
        // Initialize tables lazily when tabs are clicked
        if (origin === 'client' && table_client === null) {
            table_client = initializeDataTable('table_client_tickets', 'client', 'filter-client-confirmed');
        } else if (origin === 'internal' && table_internal === null) {
            table_internal = initializeDataTable('table_internal_tickets', 'internal', 'filter-internal-confirmed');
        }
        
        // Reload the appropriate table
        setTimeout(function() {
            if (origin === 'all' && table_all) {
                table_all.ajax.reload();
            } else if (origin === 'client' && table_client) {
                table_client.ajax.reload();
            } else if (origin === 'internal' && table_internal) {
                table_internal.ajax.reload();
            }
        }, 100);
    });

    // Handle filter checkbox changes
    $('#filter-client-confirmed').on('change', function() {
        if (table_client) table_client.ajax.reload();
    });
    
    $('#filter-internal-confirmed').on('change', function() {
        if (table_internal) table_internal.ajax.reload();
    });
    <?php endif; ?>
    
    // Handle filter checkbox for all tickets (applies to both clients and non-clients)
    $('#filter-all-confirmed').on('change', function() {
        if (table_all) table_all.ajax.reload();
    });

    // Handle client dropdown change
    $('#idclient').on('change', function() {
        var currentOrigin = $('#ticketOrigin').val();
        if (currentOrigin === 'all' && table_all) {
            table_all.ajax.reload();
        } else if (currentOrigin === 'client' && table_client) {
            table_client.ajax.reload();
        } else if (currentOrigin === 'internal' && table_internal) {
            table_internal.ajax.reload();
        }
    });

    // Close customer service request
    $("#btnCloseCustomerService").on('click', function() {
        if (confirm("Are you sure you want to close this request?")) {
            var id = $("#customerServiceId").val();
            var formData = {
                id: id,
            };      
            $.post('ajax/ajaxHandler.php', {
              rtype: 'closeCustomerService',
              uid: 0,
              data: formData,
            }).done(function (response) {
                // Reload the active table
                var currentOrigin = $('#ticketOrigin').val();
                if (currentOrigin === 'all' && table_all) {
                    table_all.ajax.reload();
                } else if (currentOrigin === 'client' && table_client) {
                    table_client.ajax.reload();
                } else if (currentOrigin === 'internal' && table_internal) {
                    table_internal.ajax.reload();
                }
                $('#postReplyModal').modal('hide');            
            });
         }   		
        return false;
    });

    // Get customer service data
    function getCustomerServiceData(id) {
        var formData = {
            id: id,
        };      
        $.ajax({
          url: 'ajax/getCustomerService.php',
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
              if (response) {
                $('#postReplyModal #clientname').val(response.clientname);
                $('#referenceNo').html(response.id);
                $('#tRequestType').html(response.request_type);
                $('#currentURL').html(response.current_url);
                $('#attachments').html(response.attachments);
                $('#dateCreated').html(response.date_created);
                $('#lastUpdated').html(response.last_updated);
                if (response.status == '1') {
                    $('#status').html('<span class="badge badge-success">Open</span>');
                    $('#btnCloseCustomerService').show();
                } else {
                    $('#status').html('<span class="badge badge-danger">Closed</span>');
                    $('#btnCloseCustomerService').hide();
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
        $("#customerServiceId").val(id);
      getCustomerServiceData(id);
      $('#postReplyModal').modal('show');
      return false;
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
              customerServiceId: $('#customerServiceId').val(),
              message: $("#replyMessage").val(),
              attachments: attachments
          };

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
                var id =  $("#customerServiceId").val();
                getCustomerServiceData(id);                
                $('#alertMessage').removeClass('alert-danger').addClass('alert-success').text('Reply successfully sent!').fadeIn().delay(3000).fadeOut();

                // Reload the active table
                var currentOrigin = $('#ticketOrigin').val();
                if (currentOrigin === 'all' && table_all) {
                    table_all.ajax.reload();
                } else if (currentOrigin === 'client' && table_client) {
                    table_client.ajax.reload();
                } else if (currentOrigin === 'internal' && table_internal) {
                    table_internal.ajax.reload();
                }
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
    alert('You can upload JPG, JPEG, PNG, GIF, Excel, or PDF file(s) only');
    goUpload = false;
}

      if (goUpload == true) {
        data.submit();
      }
    },
    start: function (e) {
      $(this).parent().siblings('.loader').show();
    },
    fail: function (e, data) {
      $(this).parent().siblings('.loader').hide();
      alert('Error uploading file (' + data.errorThrown + ')');
    },
    done: function (e, data) {
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
        $('#ul' + file.folderType).append(filename);
      });
    },
  })
  .prop('disabled', !$.support.fileInput)
  .parent()
  .addClass($.support.fileInput ? undefined : 'disabled');

   
});
 
</script>
</body>
</html>