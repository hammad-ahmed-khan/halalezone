<?php
include_once "config/config.php";
include_once('pages/header.php');
include_once ('includes/func.php');

$db = acsessDb :: singleton();
$dbo =  $db->connect(); // Database connection

$myuser = cuser::singleton();
$myuser->getUserData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" />
<link rel='stylesheet' href='https://cdn.rawgit.com/shabuninil/fileup/master/src/fileup.min.css?ver=6.0.1' type='text/css' media='all' />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel='stylesheet' href='css/fullcalendar/main.css?v=<?php echo rand(); ?>' type='text/css' media='all' />
<link rel='stylesheet' href='css/all.css?v=<?php echo rand(); ?>' type='text/css' media='all' />
<style>
.fc-disabled {
	background: #f1f1f1 !important;
}
.fc-disabled .fc-daygrid-day-events,
.fc-disabled .fc-daygrid-day-events { 
	display: none;
}
.fc-event {
	white-space: normal !important;
}

/* Enhanced Filter Styles */
.calendar-filters {
    b2ackground: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-row {
    margin-bottom: 15px;
}

.filter-row:last-child {
    margin-bottom: 0;
}

.filter-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 5px;
    display: block;
    font-size: 13px;
}

.filter-actions {
    padding-top: 15px;
    border-top: 1px solid #dee2e6;
    margin-top: 15px;
}

.callegend {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 8px;
    border-radius: 3px;
    vertical-align: middle;
}

.calproposed { background-color: #F60; }
.calapproved { background-color: #0C0; }
.calholiday { background-color: #09F; }

.legend-container {
    background: #fff;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #dee2e6;
    margin-bottom: 20px;
}

.btn-filter {
    min-width: 100px;
    margin-right: 10px;
}

.filter-info {
    font-size: 12px;
    color: #6c757d;
    margin-top: 5px;
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
            
            <!-- Hidden client ID for client users -->
            <?php if ($myuser->userdata['isclient'] == "1"): ?>
            <input type="hidden" id="app-clientid" value="<?php echo $_SESSION['halal']['id']; ?>" data-clientname="<?php echo $myuser->userdata['name'],' (',$myuser->userdata['prefix'],$myuser->userdata['id'],')'; ?>"/>
            <?php endif; ?>

            <!-- Page Header -->
            <div class="row">
              <div class="col-sm-6">
                <h2><i class="fa fa-calendar"></i> Calendar</h2>
              </div>
              <div class="col-sm-6"> 
                <?php if (!$myuser->userdata['isclient']): ?>
                <!-- <a href="" class="btn btn-primary pull-right" id="btn-addevent"><i class="fa fa-calendar-plus"></i> Add Event</a> -->
                <?php endif; ?>
              </div>
            </div>
            <hr style="margin-top:0;"/>

            <!-- Enhanced Filters Section -->
            <?php if ($myuser->userdata['isclient'] != '1'): ?>
            <div class="calendar-filters">
              <!--
              <h4 style="margin-top: 0; margin-bottom: 15px;">
                <i class="fa fa-filter"></i> Calendar Filters
              </h4>
            -->
              
              <div class="row filter-row">
                <!-- Client Filter -->
                <div class="<?php echo ($myuser->userdata['isclient'] == '2') ? 'col-md-4' : 'col-md-3'; ?>">
                  <label class="filter-label">Filter by Client</label>
                  <select class="form-control clientslist" id="app-clientid">
                    <option value="-1">All Clients</option>
                    <!-- Options populated by JavaScript -->
                  </select>
                  <div class="filter-info">
                    <?php echo ($myuser->userdata['isclient'] == '2') ? 'Select from your assigned clients' : 'Select a specific client or view all'; ?>
                  </div>
                </div>
                
                <!-- Auditor Filter (Only for Admin users) -->
                <?php if ($myuser->userdata['isclient'] == '0'): ?>
                <div class="col-md-3">
                  <label class="filter-label">Filter by Auditor</label>
                  <select class="form-control" id="app-auditorid">
                    <option value="-1">All Auditors</option>
                    <!-- Options populated by JavaScript -->
                  </select>
                  <div class="filter-info">Show events for clients assigned to auditor</div>
                </div>
                <?php endif; ?>
                
                <!-- Date From -->
                <div class="<?php echo ($myuser->userdata['isclient'] == '2') ? 'col-md-4' : 'col-md-3'; ?>">
                  <label class="filter-label">From Date</label>
                  <div class="input-group date">
                    <input type="text" class="form-control" id="filter-date-from" placeholder="Limit calendar start" />
                    <span class="input-group-addon">
                      <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                  </div>
                  <div class="filter-info">Restrict calendar navigation from this date</div>
                </div>
                
                <!-- Date To -->
                <div class="<?php echo ($myuser->userdata['isclient'] == '2') ? 'col-md-4' : 'col-md-3'; ?>">
                  <label class="filter-label">To Date</label>
                  <div class="input-group date">
                    <input type="text" class="form-control" id="filter-date-to" placeholder="Limit calendar end" />
                    <span class="input-group-addon">
                      <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                  </div>
                  <div class="filter-info">Restrict calendar navigation until this date</div>
                </div>
              </div>
              
              <!-- Filter Actions -->
              <div class="filter-actions">
                <div class="row">
                  <div class="col-md-6">
                    <button type="button" class="btn btn-primary btn-filter" id="btn-apply-filters">
                      <i class="fa fa-search"></i> Apply Filters
                    </button>
                    <button type="button" class="btn btn-default btn-filter" id="btn-clear-filters">
                      <i class="fa fa-times"></i> Clear Filters
                    </button>
                  </div>
                  <div class="col-md-6 text-right">
                    <small class="text-muted">
                      <i class="fa fa-info-circle"></i> 
                      Filters are applied automatically as you change selections
                    </small>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <!-- Legend -->
            <div class="legend-container">
              <strong><i class="fa fa-info-circle"></i> Legend:</strong>
              <span class="callegend calproposed"></span> Proposed Dates &nbsp;&nbsp;
              <span class="callegend calapproved"></span> Approved Dates &nbsp;&nbsp;
              <span class="callegend calholiday"></span> Events / Holidays &nbsp;&nbsp;
            </div>

            <!-- Alert for date selection -->
            <div class="row">
              <div class="col-md-12">
                <div class="alert alert-warning">
                  <i class="fa fa-info-circle"></i> Click on a date in calendar to add event/holiday.
                </div>
              </div>
            </div>

            <!-- Calendar Container -->
            <div id='calendar' style="margin-top: 20px;"></div>

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

<!-- Event Modal -->
<div id="modalevent" class="modal" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content"> 
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h4 class="modal-title title-add">Add Event</h4>
        <h4 class="modal-title title-edit" style="display:none;">Edit Event</h4>
      </div>
      <!--modal header-->
      <div class="modal-body">
        <div class="alert alert-danger" id="errors" style="display:none;"></div>
        <form id="frmEvent" data-toggle="validator">
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
                  <div class="input-group date" id="dtpickerdemo2">
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
                  <div style="text-align:right;"> 
                    <button class="btn btn-success" id="btn-approve" style="display:none;">
                      <span class="fa fa-check"></span> Approve Date
                    </button>
                    <button class="btn btn-primary" id="btnsave-event">
                      <span class="fa fa-save"></span> Save Event
                    </button>
                    <button class="btn btn-danger" id="btndelete-event">
                      <span class="fa fa-trash"></span> Delete Event
                    </button>
                    <button class="btn btn-default" data-dismiss="modal">
                      <span class="fa fa-times"></span> Cancel
                    </button>
                  </div>
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

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script> 
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js?ver=1285677791'></script> 
<script type='text/javascript' src='../js/fileup.js?ver=162459439'></script> 
<script type='text/javascript' src='../js/fullcalendar/main.js?ver=162459439'></script> 

<script>
// Enhanced Calendar JavaScript with Server-Side Filtering
var calendar;

var disabledDates = {};
var userId = <?php echo $_SESSION['halal']['id'] ?>;

// Initialize when document is ready
$(document).ready(function() {
    if (typeof Common !== 'undefined') {
        Common.onDocumentReady();
        Common.setMainMenuItem("calendarItem");
    }
    
    // Initialize date pickers for filters
    initializeDatePickers();
    
    // Load initial data
    loadClientsData();
    loadAuditorsData();
    
    // Initialize calendar
    initializeCalendar();
    
    // Set up filter event handlers
    setupFilterHandlers();
    
    // Set up modal event handlers
    setupModalHandlers();
});

// Initialize date pickers
function initializeDatePickers() {
    // Filter date pickers
    $("#filter-date-from, #filter-date-to").datetimepicker({
        format: "DD/MM/YYYY",
        showTodayButton: true,
        showClear: true,
        showClose: true
    });
    
    // Modal date pickers
    $("#StartDate, #EndDate").datetimepicker({
        format: "DD/MM/YYYY",
        showTodayButton: true,
        showClear: true,
        showClose: true
    });
}

// Load clients data for dropdown
function loadClientsData() {
    $.get("ajax/getClients.php")
        .done(function(response) {
            try {
                if (response.status == '1') {
                    populateClientsDropdown(response.data.clients);
                } else {
                    console.error('Error loading clients:', response.statusDescription);
                }
            } catch (e) {
                console.error('Error parsing clients data:', e);
            }
        })
        .fail(function() {
            console.error('Failed to load clients data');
        });
}

// Populate clients dropdown
function populateClientsDropdown(clients) {
    var $clientsDropdown = $("#app-clientid");
    
    // Only populate if this is not a client user (who has hidden input)
    if ($clientsDropdown.is('select')) {
        $clientsDropdown.empty();
        $clientsDropdown.append('<option value="-1">All Clients</option>');
        
        if (clients && clients.length > 0) {
            clients.forEach(function(client) {
                $clientsDropdown.append(
                    '<option value="' + client.id + '" data-clientname="' + 
                    client.name + ' (' + client.prefix + client.id + ')">' +
                    client.name + ' - ' + client.prefix + client.id +
                    '</option>'
                );
            });
        }
    }
}

// Load auditors data for dropdown
function loadAuditorsData() {
    $.get("ajax/getAuditors.php")
        .done(function(response) {
            try {
                if (response.status == '1') {
                    populateAuditorsDropdown(response.data.auditors);
                } else {
                    //console.error('Error loading auditors:', response.statusDescription);
                    // Add fallback option
                    $("#app-auditorid").append('<option value="-1">All Auditors</option>');
                }
            } catch (e) {
                //console.error('Error parsing auditors data:', e);
                $("#app-auditorid").append('<option value="-1">All Auditors</option>');
            }
        })
        .fail(function() {
            //console.error('Failed to load auditors data');
            $("#app-auditorid").append('<option value="-1">All Auditors</option>');
        });
}

// Populate auditors dropdown
function populateAuditorsDropdown(auditors) {
    var $auditorsDropdown = $("#app-auditorid");
    $auditorsDropdown.empty();
    $auditorsDropdown.append('<option value="-1">All Auditors</option>');
    
    if (auditors && auditors.length > 0) {
        auditors.forEach(function(auditor) {
            $auditorsDropdown.append(
                '<option value="' + auditor.id + '">' +
                auditor.name + (auditor.email ? ' (' + auditor.email + ')' : '') +
                '</option>'
            );
        });
    }
}

// Initialize FullCalendar
function initializeCalendar() {
    var calendarEl = document.getElementById('calendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        navLinks: true,
        selectable: true,
        selectMirror: true,
        initialDate: new Date(),
        height: 'auto',
        
        // Enhanced events loading with server-side filtering
        events: function(info, successCallback, failureCallback) {
            loadCalendarEvents(info, successCallback, failureCallback);
        },
        
        // Event styling and interaction
        eventDisplay: 'block',
        dayMaxEvents: true,
        
        eventsSet: function(events) {
			<?php if ($myuser->userdata['isclient'] == "1"): ?>
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
        
        // Calendar interaction handlers
        select: function(arg) {
            handleDateSelect(arg);
        },
        
        eventClick: function(arg) {
            handleEventClick(arg);
        },
        
        // Loading states
        loading: function(bool) {
            if (bool) {
                
            } else {
                
            }
        },
        
        // Error handling
        eventSourceFailure: function(errorObj) {
            console.error('Calendar error:', errorObj);
            alert('Error loading calendar events. Please try again.');
        }
    });

    calendar.render();
}

// Load calendar events with filtering
function loadCalendarEvents(info, successCallback, failureCallback) {
 
    
    var filterData = {
        start: info.startStr,
        end: info.endStr,
        idclient: $("#app-clientid").val() || "-1",
        idauditor: $("#app-auditorid").val() || "-1"
        // Removed date_from and date_to - these will be handled by calendar navigation limits
    };
    
    $.ajax({
        url: 'ajax/getEvents.php',
        type: 'GET',
        data: filterData,
        timeout: 30000,
        success: function(data) {
            try {
                var events;
                if (typeof data === 'string') {
                    events = JSON.parse(data);
                } else {
                    events = data;
                }
                
                // Handle debug response format
                if (events.events) {
                    events = events.events;
                }
                
                // Validate events array
                if (!Array.isArray(events)) {
                    throw new Error('Invalid events data format');
                }
                
                // Process events for FullCalendar and apply client-side date filtering
                var processedEvents = events.map(function(event) {
                    return {
                        id: event.id,
                        title: event.title,
                        start: event.start,
                        end: event.end || event.start,
                        backgroundColor: event.color || event.backgroundColor,
                        borderColor: event.color || event.borderColor,
                        textColor: '#ffffff',
                        extendedProps: event.extendedProps || {
                            idclient: event.idclient,
                            idapp: event.idapp,
                            status: event.status
                        }
                    };
                });
                
                // Apply client-side date range filtering
                var dateFrom = $("#filter-date-from").val();
                var dateTo = $("#filter-date-to").val();
                
                if (dateFrom || dateTo) {
                    processedEvents = processedEvents.filter(function(event) {
                        var eventDate = moment(event.start);
                        var isWithinRange = true;
                        
                        if (dateFrom) {
                            var fromDate = moment(dateFrom, 'DD/MM/YYYY');
                            if (fromDate.isValid() && eventDate.isBefore(fromDate, 'day')) {
                                isWithinRange = false;
                            }
                        }
                        
                        if (dateTo && isWithinRange) {
                            var toDate = moment(dateTo, 'DD/MM/YYYY');
                            if (toDate.isValid() && eventDate.isAfter(toDate, 'day')) {
                                isWithinRange = false;
                            }
                        }
                        
                        return isWithinRange;
                    });
                }
                
                successCallback(processedEvents);
                
            } catch (e) {
                console.error('Error parsing events data:', e);
                failureCallback(e);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading events:', error);
            failureCallback(error);
        }
    });
}

// Set up filter event handlers
function setupFilterHandlers() {
    // Auto-apply filters when dropdowns change
    $("#app-clientid, #app-auditorid").on("change", function() {
        applyFilters();
    });
    
    // Set up enhanced date picker handlers
    setupDatePickerHandlers();
    
    // Manual filter application
    $("#btn-apply-filters").on("click", function() {
        applyFilters();
    });
    
    // Clear all filters
    $("#btn-clear-filters").on("click", function() {
        clearAllFilters();
    });
}

// Apply filters and refresh calendar
function applyFilters() {
    
    // Add visual feedback
    $("#btn-apply-filters").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Applying...');
    
    // Apply date range restrictions to calendar navigation
    applyDateRangeRestrictions();
    
    // Refresh calendar events
    if (calendar) {
        calendar.refetchEvents();
    }
    
    // Reset button after a short delay
    setTimeout(function() {

        $("#btn-apply-filters").prop("disabled", false).html('<i class="fa fa-search"></i> Apply Filters');
    }, 1000);
}

// Apply date range restrictions to calendar navigation
function applyDateRangeRestrictions() {
    var dateFrom = $("#filter-date-from").val();
    var dateTo = $("#filter-date-to").val();
    
    if (calendar) {
        // Set calendar options for date restrictions
        var calendarOptions = {};
        
        if (dateFrom) {
            var fromDate = moment(dateFrom, 'DD/MM/YYYY');
            if (fromDate.isValid()) {
                calendarOptions.validRange = calendarOptions.validRange || {};
                calendarOptions.validRange.start = fromDate.format('YYYY-MM-DD');
                
                // If current calendar view is before the start date, navigate to start date
               var currentDate = moment(calendar.getDate()); // Convert to moment
			   if (currentDate.isBefore(fromDate, 'month')) {
                    calendar.gotoDate(fromDate.toDate());
                }
            }
        }
        
        if (dateTo) {
            var toDate = moment(dateTo, 'DD/MM/YYYY');
            if (toDate.isValid()) {
                calendarOptions.validRange = calendarOptions.validRange || {};
                calendarOptions.validRange.end = toDate.clone().add(1, 'day').format('YYYY-MM-DD'); // FullCalendar end is exclusive
                
                // If current calendar view is after the end date, navigate to end date
				var currentDate = moment(calendar.getDate()); // Convert to moment
			   if (currentDate.isAfter(toDate, 'month')) {					
                    calendar.gotoDate(toDate.toDate());
                }
            }
        }
        
        // If no date filters, remove restrictions
        if (!dateFrom && !dateTo) {
            calendarOptions.validRange = null;
        }
        
        // Apply the restrictions by reinitializing calendar with new options
        if (calendarOptions.validRange !== undefined) {
            // Update calendar options
            calendar.setOption('validRange', calendarOptions.validRange);
        }
    }
}

// Clear all filters
function clearAllFilters() {
    $("#app-clientid").val("-1");
    $("#app-auditorid").val("-1");
    $("#filter-date-from").val("");
    $("#filter-date-to").val("");
    
    // Clear date picker values
    $("#filter-date-from").data("DateTimePicker").clear();
    $("#filter-date-to").data("DateTimePicker").clear();
    
    // Remove calendar date restrictions
    if (calendar) {
        calendar.setOption('validRange', null);
    }
    
    // Apply the cleared filters
    applyFilters();
}

// Enhanced date picker change handler
function setupDatePickerHandlers() {
    // Date range validation and calendar navigation
    $("#filter-date-from").on("dp.change", function (e) {
        var fromDate = e.date;
        var toDatePicker = $("#filter-date-to").data("DateTimePicker");
        
        if (fromDate) {
            // Set minimum date for "to" picker
            toDatePicker.minDate(fromDate);
            
            // If "to" date is before "from" date, clear it
            var toDate = toDatePicker.date();
            if (toDate && toDate.isBefore(fromDate)) {
                toDatePicker.clear();
            }
        } else {
            // Remove minimum date restriction
            toDatePicker.minDate(false);
        }
        
        // Apply filters after a short delay to avoid rapid firing
        setTimeout(function() {
            applyFilters();
        }, 300);
    });
    
    $("#filter-date-to").on("dp.change", function (e) {
        var toDate = e.date;
        var fromDatePicker = $("#filter-date-from").data("DateTimePicker");
        
        if (toDate) {
            // Set maximum date for "from" picker
            fromDatePicker.maxDate(toDate);
            
            // If "from" date is after "to" date, clear it
            var fromDate = fromDatePicker.date();
            if (fromDate && fromDate.isAfter(toDate)) {
                fromDatePicker.clear();
            }
        } else {
            // Remove maximum date restriction
            fromDatePicker.maxDate(false);
        }
        
        // Apply filters after a short delay to avoid rapid firing
        setTimeout(function() {
            applyFilters();
        }, 300);
    });
}

// Handle date selection on calendar
function handleDateSelect(arg) {
    <?php if (!$myuser->userdata['isclient']): ?>
    openEventModal('add', {
        startDate: arg.start,
        endDate: arg.end
    });
    <?php endif; ?>
    
    calendar.unselect();
}

// Handle event click
function handleEventClick(arg) {
    openEventModal('edit', {
        event: arg.event
    });
}

// Open event modal
function openEventModal(mode, data) {
    $("#modalevent").modal("show");
    $("#errors").hide();
    
    if (mode === 'add') {
        // Add mode
        $(".modal-title.title-add").show();
        $(".modal-title.title-edit").hide();
        $("#btndelete-event").hide();
        $("#btn-approve").hide();
        
        // Clear form
        $("#ID").val("");
        $("#idclient").val("");
        $("#idapp").val("");
        $("#Title").val("");
        
        // Set dates
        if (data.startDate) {
            $("#StartDate").data("DateTimePicker").date(data.startDate);
        }
        if (data.endDate) {
            $("#EndDate").data("DateTimePicker").date(data.endDate);
        }
        
    } else if (mode === 'edit') {
        // Edit mode
        $(".modal-title.title-add").hide();
        $(".modal-title.title-edit").show();
        $("#btndelete-event").show();
        
        var event = data.event;
        
        // Populate form
        $("#ID").val(event.id);
        $("#Title").val(event.title);
        $("#StartDate").data("DateTimePicker").date(event.start);
        $("#EndDate").data("DateTimePicker").date(event.end || event.start);
        
        // Set extended properties
        if (event.extendedProps) {
            $("#idclient").val(event.extendedProps.idclient || "");
            $("#idapp").val(event.extendedProps.idapp || "");
            
            // Show/hide approve button based on status
            if (event.extendedProps.idclient > 0 && event.extendedProps.idapp > 0) {
                if (event.extendedProps.status == 0) {
                    $("#btn-approve").show();
                } else {
                    $("#btn-approve").hide();
                }
            } else {
                $("#btn-approve").hide();
            }
        }
    }
    
    // Enable all form fields
    $("#Title, #StartDate, #EndDate").prop('disabled', false);
}

// Set up modal event handlers
function setupModalHandlers() {
    // Save event handler
    $("#btnsave-event").on("click", function() {
        saveEvent();
    });
    
    // Delete event handler
    $("#btndelete-event").on("click", function() {
        deleteEvent();
    });
    
    // Approve event handler
    $("#btn-approve").on("click", function() {
        approveEvent();
    });
}

// Save event
function saveEvent() {
    var eventData = {
        ID: $("#ID").val(),
        idclient: $("#idclient").val(),
        idapp: $("#idapp").val(),
        Title: $("#Title").val(),
        StartDate: $("#StartDate").val(),
        EndDate: $("#EndDate").val()
    };
    
    // Validate required fields
    if (!eventData.Title || !eventData.StartDate) {
        $("#errors").show().html('<i class="fa fa-exclamation-triangle"></i> Please fill in all required fields.');
        return;
    }
    
    // Disable save button during request
    $("#btnsave-event").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    
    $.ajax({
        url: "ajax/saveEvent.php",
        type: "POST",
        data: {
            data: eventData
        },
        success: function(data) {
            try {
                var response = JSON.parse(data);
                
                if (response.status === 0 || response.data?.errors) {
                    $("#errors").show().html(response.statusDescription || response.data.errors);
                    return;
                }
                
                // Success
                calendar.refetchEvents();
                $("#modalevent").modal('hide');
                $("#errors").hide();
                
                // Show success notification
                showNotification('Event saved successfully!', 'success');
                
            } catch (e) {
                $("#errors").show().html('Error saving event. Please try again.');
            }
        },
        error: function() {
            $("#errors").show().html('Error saving event. Please try again.');
        },
        complete: function() {
            // Re-enable save button
            $("#btnsave-event").prop("disabled", false).html('<i class="fa fa-save"></i> Save Event');
        }
    });
}

// Delete event
function deleteEvent() {
    if (!confirm('Are you sure you want to delete this event?')) {
        return;
    }
    
    var eventId = $("#ID").val();
    if (!eventId) {
        $("#errors").show().html('No event selected for deletion.');
        return;
    }
    
    // Disable delete button during request
    $("#btndelete-event").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Deleting...');
    
    $.ajax({
        url: "ajax/deleteEvent.php",
        type: "POST",
        data: {
            data: { ID: eventId }
        },
        success: function(data) {
            try {
                var response = JSON.parse(data);
                
                if (response.status === 0 || response.data?.errors) {
                    $("#errors").show().html(response.statusDescription || response.data.errors);
                    return;
                }
                
                // Success
                calendar.refetchEvents();
                $("#modalevent").modal('hide');
                $("#errors").hide();
                
                // Show success notification
                showNotification('Event deleted successfully!', 'success');
                
            } catch (e) {
                $("#errors").show().html('Error deleting event. Please try again.');
            }
        },
        error: function() {
            $("#errors").show().html('Error deleting event. Please try again.');
        },
        complete: function() {
            // Re-enable delete button
            $("#btndelete-event").prop("disabled", false).html('<i class="fa fa-trash"></i> Delete Event');
        }
    });
}

// Approve event
function approveEvent() {
    if (!confirm("Are you sure you want to approve this audit date?")) {
        return;
    }
    
    var approvalData = {
        idclient: $("#idclient").val(),
        idapp: $("#idapp").val(),
        ApprovedDate1: $("#StartDate").val()
    };
    
    // Validate required data
    if (!approvalData.idclient || !approvalData.idapp || !approvalData.ApprovedDate1) {
        $("#errors").show().html('Missing required information for approval.');
        return;
    }
    
    // Disable approve button during request
    $("#btn-approve").prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Approving...');
    
    $.ajax({
        url: "ajax/approveAuditDate.php",
        type: "POST",
        data: {
            data: approvalData
        },
        success: function(data) {
            try {
                var response = JSON.parse(data);
                
                if (response.status === 0) {
                    $("#errors").show().html(response.statusDescription);
                    return;
                }
                
                // Success
                calendar.refetchEvents();
                $("#modalevent").modal('hide');
                $("#errors").hide();
                
                // Show success notification
                showNotification('Audit date approved successfully!', 'success');
                
            } catch (e) {
                $("#errors").show().html('Error approving date. Please try again.');
            }
        },
        error: function() {
            $("#errors").show().html('Error approving date. Please try again.');
        },
        complete: function() {
            // Re-enable approve button
            $("#btn-approve").prop("disabled", false).html('<i class="fa fa-check"></i> Approve Date');
        }
    });
}

// Show notification
function showNotification(message, type) {
    type = type || 'info';
    
    // Create notification element
    var notificationHtml = '<div class="alert alert-' + 
        (type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info') + 
        ' alert-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px;">' +
        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
        '<i class="fa fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle') + '"></i> ' +
        message +
        '</div>';
    
    var $notification = $(notificationHtml);
    $('body').append($notification);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        $notification.fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);
}

// Add event function for admin users
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

// Add event button handler
$(document).on("click","#btn-addevent",function(){	
    return addEvent();
});
<?php endif; ?>

// Export for debugging
window.CalendarDebug = {
    getFilters: function() {
        return {
            client: $("#app-clientid").val(),
            auditor: $("#app-auditorid").val(),
            dateFrom: $("#filter-date-from").val(),
            dateTo: $("#filter-date-to").val()
        };
    },
    refreshCalendar: function() {
        if (calendar) {
            calendar.refetchEvents();
        }
    },
    testEventLoad: function() {
        var testData = {
            start: moment().startOf('month').format('YYYY-MM-DD'),
            end: moment().endOf('month').format('YYYY-MM-DD'),
            idclient: $("#app-clientid").val() || "-1",
            idauditor: $("#app-auditorid").val() || "-1",
            date_from: $("#filter-date-from").val() || "",
            date_to: $("#filter-date-to").val() || "",
            debug: 1
        };
        
        return $.get('ajax/getEvents.php', testData);
    }
};

</script> 
</body>
</html>