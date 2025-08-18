-<!DOCTYPE html>
<html lang="en">
<head>
<?php
	include_once "config/config.php";
    include_once('pages/header.php');
    include_once ('includes/func.php');

	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
?>
<link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" />
<link rel='stylesheet' id='fileup-css'  href='https://cdn.rawgit.com/shabuninil/fileup/master/src/fileup.min.css?ver=6.0.1' type='text/css' media='all' />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel='stylesheet' id='fileup-css'  href='css/fullcalendar/main.css?v=<?php echo rand(); ?>' type='text/css' media='all' />
<link rel='stylesheet' id='fileup-css'  href='css/all.css?v=<?php echo rand(); ?>' type='text/css' media='all' />
<style>
.fc-disabled {
	background: #f1f1f1 !important;
}
.fc-disabled .fc-daygrid-day-events,
.fc-disabled .fc-daygrid-day-events { 
	display: none;;
}
.fc-event {
	white-space: normal !important;
}
</style>
<title>Calendar - Halal e-Zone</title>
</head>
<body>
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
  <div class="main-content">
    <div class="main-content-inner">
      <div class="page-content">
        <div class="row no-gutters">
          <div class="col-xs-12" style="padding-top:25px;">
            <?php $myuser = cuser::singleton();
                        $myuser->getUserData();?>
            <?php if ($myuser->userdata['isclient']): ?>
            <input type="hidden" id="app-clientid" value=<?php echo $_SESSION['halal']['id']; ?> data-clientname="<?php echo $myuser->userdata['name']," (",$myuser->userdata['prefix'],$myuser->userdata['id'],")"; ?>"/>
            <?php else: ?>
            <div class="form-inline">
              <div class="form-group">
                <label>Clients&nbsp;&nbsp;
                  <select class="form-control clientslist" id="app-clientid">
                  </select>
                </label>
              </div>
            </div>
            <?php endif;?>
          </div>
          <div class="col-xs-12"> 
          <div class="row">
  <div class="col-sm-6">
    <h2>Calendar</h2>
  </div>
  <div class="col-sm-6"> 
  <!--<a href="" class="btn btn-primary pull-right" id="btn-addevent"><i class="fa fa-calendar-o"></i> Add Event</a> --></div>
</div>
<hr style="margin-top:0;"/>

            <div id="appMain" style="margin-top:20px;">
            <!-- PAGE CONTENT BEGINS -->
            <div class="row">
              <div class="col-md-12">
                <div class="alert alert-warning">Click on a date in calendar to add event/holiday.</div>
              </div>
              <div class="col-md-12">
                <div style="margin-bottom:20px;">
                  <span class="callegend calproposed"></span> Proposed Dates &nbsp;&nbsp;
                  <span class="callegend calapproved"></span> Approved Dates &nbsp;&nbsp;
                  <span class="callegend calholiday"></span> Event / Holiday &nbsp;&nbsp;
                </div>
              </div>
            </div>
			<?php
			/*
            $sessionTimeLimit = ini_get('session.gc_maxlifetime');
echo "Session time limit: " . $sessionTimeLimit . " seconds";
*/
?>
            <div id='calendar'></div>
            <!-- PAGE CONTENT ENDS --> 
          </div>
          <!-- /.col --> 
        </div>
        <!-- /.row --> 
      </div>
      <!-- /.page-content --> 
    </div>
  </div>
  <!-- /.main-content -->
  <?php include_once('pages/footer.php');?>
</div>
<!-- /.main-container --> 
<!-- Application Modal -->
<div id="modalevent" class="modal" data-backdrop="static">
          <div class="modal-dialog modal-lg-">
            <div class="modal-content"> 
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title title-add">Add Event</h4>
                <h4 class="modal-title title-edit" style="display:none;">Edit Event</h4>
              </div>
              <!--modal header-->
              <div class="modal-body">
                <div class="alert alert-danger" id="errors"></div>
                <form id="frmTenure" data-toggle="validator">
                  <input type="hidden" name="ID" id="ID" value="" />
                  <input type="hidden" name="idclient" id="idclient" value="" />
                  <input type="hidden" name="idapp" id="idapp" value="" />                  
                  <div class="form-horizontal row"> 
                     <div class="col-md-12">
                      <div class="row form-group">
                        <label class="col-sm-3 control-label">Title <span>*</span></label>
                        <div class="col-sm-9">
                          <input type="text" class="form-control" name="Title" id="Title" value="" required />
                        </div>
                      </div>
                      <div class="row form-group">
                         <label class="col-sm-3 control-label">Start Date <span>*</span></label>
                         <div class="col-sm-9">
                          <div class="input-group date" id="dtpickerdemo">
                             <input type="text" class="form-control" name="StartDate" id="StartDate" value="" required />
                             <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                             </span>
                           </div>
                         </div>
                        </div>
                      
                      <div class="row form-group">
                        <label class="col-sm-3 control-label">End Date</label>
                        <div class="col-sm-9">
                          <div class="input-group date" id="dtpickerdemo">
                              <input type="text" class="form-control input-group" name="EndDate" id="EndDate" value="" />
                              <span class="input-group-addon">
                                 <span class="glyphicon glyphicon-calendar"></span>
                              </span>
                          </div>
                        </div>
                      </div>
                                           
                      <div class="row form-group">
                        <div class="col-sm-12">
						<?php if (!$myuser->userdata['isclient']): ?>
                          <button type="button" class="btn btn-success pull-right" id="btn-approve" style="margin-left:10px; display:none;"><i class="fa fa-check"></i> Approve</button>
                          <button type="button" class="btn btn-primary pull-right" id="btnsave-event" style="margin-left:10px;"><i class="fa fa-save"></i> Save</button>
                          <button type="button" class="btn btn-danger pull-right" id="btndelete-event" style="display:none;"><i class="fa fa-trash"></i> Delete</button>
						<?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

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
<script src="js/notify.min.js"></script> 
<script src="js/all.js?v=<?php echo $GLOBALS['appVersion']?>"></script> 
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script> 
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script> 
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js?ver=1285677791' id='blockui-js'></script> 
<script type='text/javascript' src='../js/fileup.js?ver=162459439' id='fileup-js'></script> 
<script type='text/javascript' src='../js/fullcalendar/main.js?ver=162459439' id='fileup-js'></script> 
<script>
  					
	var disabledDates = {};
	var userId = <?php echo $_SESSION['halal']['id'] ?>;
    Common.onDocumentReady();
	$(document).ready(function(e) {

		Common.setMainMenuItem("calendarItem");

		Common.loadClientsData(Common.populateClients);
  
    });
	
 document.addEventListener('DOMContentLoaded', function() {
	var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek'
      },
      navLinks: true, // can click day/week names to navigate views
      selectable: true,
      selectMirror: true,
	  initialDate: new Date(),
	  events: 'ajax/getEvents.php',
	  eventsSet: function(events) {
		<?php if ($myuser->userdata['isclient']): ?>
			var eventDates = [];
			events.forEach(function(event) {
				if (event._def.extendedProps.idclient != '<?php echo $_SESSION["halal"]["id"]; ?>') {
					var start = moment(event.start);
					var end = moment(event.end === null ? event.start : event.end);
					while (event.end === null ? start.isSameOrBefore(end) : start.isBefore(end)) {
						eventDates.push(start.format('YYYY-MM-DD'));
						start.add(1, 'days');
					}
				}
			});
			$('#calendar').find('.fc-day').each(function() {
				var date = $(this).attr('data-date');
				if ($.inArray(date, eventDates) !== -1) {
					$(this).addClass('fc-disabled');
				}
			});
		<?php endif; ?>
	  },
      select: function(arg) {
		 <?php if (!$myuser->userdata['isclient']): ?>
		  	addEvent();
		  <?php endif; ?>
		  $('#StartDate').data("DateTimePicker").date(arg.start);
		  $('#EndDate').data("DateTimePicker").date(arg.end);		  
		  /*
			var title = prompt('Event Title:');
			if (title) {
			calendar.addEvent({
				title: title,
				start: arg.start,
				end: arg.end,
				allDay: arg.allDay
			})
			}
		  */
          calendar.unselect()
      },
      eventClick: function(arg) { 
		$("#modalevent").modal("show");
		$(".modal-title.title-add").hide();
		$(".modal-title.title-edit").show();
		$("#errors").hide();
		$("#ID").val(arg.event.id);
		$("#Title").val(arg.event.title);
		$("#StartDate").data("DateTimePicker").date(arg.event.start);
		$("#EndDate").data("DateTimePicker").date(arg.event.end ? arg.event.end : arg.event.start);
		if (arg.event.extendedProps.idclient>0 && arg.event.extendedProps.idapp>0) { 
			$("#idclient").val(arg.event.extendedProps.idclient);
			$("#idapp").val(arg.event.extendedProps.idapp);
			if (arg.event.extendedProps.status==0) {
				$("#btn-approve").show();
			}
			else {
				$("#btn-approve").hide();
			}
			//$("#btnsave-event").hide();
			//$("#btndelete-event").hide();
			$("#btnsave-event").show();
			$("#btndelete-event").show();			
			$("#Title").prop('disabled', false);			
			$("#StartDate").prop('disabled', false);			
			$("#EndDate").prop('disabled', false);						
		}
		else {
			$("#idclient").val("");
			$("#idapp").val("");
			$("#btn-approve").hide();
			$("#btnsave-event").show();
			$("#btndelete-event").show();			
			$("#Title").prop('disabled', false);			
			$("#StartDate").prop('disabled', false);			
			$("#EndDate").prop('disabled', false);						
		}
		return false;
		  
        //if (confirm('Are you sure you want to delete this event?')) {
         // arg.event.remove()
       // }
	   	
		
	   
      },
	  
      editable: false,
      dayMaxEvents: true, // allow "more" link when too many events
      
    }); 

    calendar.render();
	
	$("#StartDate").datetimepicker({format : "DD/MM/YYYY"});
	$("#EndDate").datetimepicker({format : "DD/MM/YYYY"});

	$('#btn-approve').on('click', function(e) {
		if (!confirm("Are you sure you want to approve?")) {
			return false;
		}
			var ApprovedDate1 = $('#StartDate').val();
//			var ApprovedDate2 = $('input[name="ApprovedDate2"]:checked').val();
//			var ApprovedDate3 = $('input[name="ApprovedDate3"]:checked').val();
			var doc = {};
			doc.idclient = $("#idclient").val();
			doc.idapp = $("#idapp").val();
			doc.ApprovedDate1 = ApprovedDate1 ? ApprovedDate1 : "";
			//doc.ApprovedDate2 = ApprovedDate2 ? ApprovedDate2 : "";
			//doc.ApprovedDate3 = ApprovedDate3 ? ApprovedDate3 : "";
		  $.post("ajax/ajaxHandler.php", {
			  rtype: "approveAuditDates",
			  uid: 0,
			  data: doc,
			}).done(function (data) {
			  var response = JSON.parse(data);

			  if (response.status == 0) {
				$("#errors").html(response.statusDescription).show();
				return;
			  }

				calendar.refetchEvents();
				$("#modalevent").notify( "Audit date approved.", { position:"top right", className: "success" });
				  $("#modalevent").modal('toggle');
				  $("#errors").hide();
			  //$("#ingredGrid").jqGrid().trigger("reloadGrid");
			});
			return false;

    });

    $(document).on("click","#btn-addevent",function(){	
		return addEvent();
	});
	 $(document).on("click","#btnsave-event",function(){
		//if (!$("#btnsave-event").hasClass("disabled")) {
    	  var data = {
			ID: $("#ID").val(),
			idclient: $("#idclient").val(),
			idapp: $("#idapp").val(),
			Title: $("#Title").val(),
			StartDate: $("#StartDate").val(),
			EndDate: $("#EndDate").val(),
		  };
		  $.ajax({
			url: "ajax/ajaxHandler.php",
			type: "POST",
			data : {uid: 0, rtype: "save_event", data: data},
			success: function(data, textStatus, jqXHR) {
				response = JSON.parse(data);
			  if (response.data.errors) {
				$("#errors").show().html(response.data.errors);
			  	return;
			  }
			  /*
			  var table = $('#table_event').DataTable(); 
			  table.ajax.reload( null, false );
			  */
			  calendar.refetchEvents()
			  $("#modalevent").modal('toggle');
			  $("#errors").hide();
			},
			error: function(jqXHR, textStatus, errorThrown) {}
		  });
		return false;
    });
	
	 $(document).on("click","#btndelete-event",function(){
		//if (!$("#btnsave-event").hasClass("disabled")) {
		if (confirm('Are you sure you want to delete?')) {
			  var data = {
				ID: $("#ID").val(),
			  };
			  $.ajax({
				url: "ajax/ajaxHandler.php",
				type: "POST",
				data : {uid: 0, rtype: "delete_event", data: data},
				success: function(data, textStatus, jqXHR) {
					response = JSON.parse(data);

				  if (response.data.errors) {
					$("#modalevent #errors").show().html(response.data.errors);
					return;
				  }
				  /*
				  var table = $('#table_event').DataTable(); 
				  table.ajax.reload( null, false );
				  */
				  calendar.refetchEvents()
				  $("#modalevent").modal('toggle');
				  $("#modalevent #errors").hide();
				},
				error: function(jqXHR, textStatus, errorThrown) {}
			  });
		}
			return false;
			

		});
	
});

<?php if (!$myuser->userdata['isclient']): ?>
	function addEvent() {	  
		$("#modalevent").modal("show");
		$(".modal-title.title-add").show();
		$(".modal-title.title-edit").hide();
		$("#btndelete-event").hide();
		$("#errors").hide();
		$("#ID").val("");
		$("#Title").prop('disabled', false);			
		$("#StartDate").prop('disabled', false);			
		$("#EndDate").prop('disabled', false);						
		$("#Title").val("");
		$("#StartDate").val("");
		$("#EndDate").val("");
		$("#btnsave-event").show();
		return false;
	}
<?php endif; ?>
   

</script> 
<!-- Menu Toggle Script --> 
</body>
</html>