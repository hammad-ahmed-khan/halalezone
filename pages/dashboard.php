<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <title>Dashboard - Halal e-Zone</title>
    <style>
    .grid-wrap-text {
    white-space: normal !important;
    word-wrap: break-word;
    }
    </style>
</head>
<body>
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-6">
                        <?php 
                        $db = acsessDb :: singleton();
                        $dbo =  $db->connect(); // Создаем объект подключения к БД
                        
                        $myuser = cuser::singleton();
                        $myuser->getUserData();
                     
                        $parent_id = $myuser->userdata['id'];
                        $isClient = $myuser->userdata['isclient'] == "1" ? true : false;
                        $isAuditor = $myuser->userdata['isclient'] == '2' ? true : false;
                        $isAdmin = !$isclient && !isAuditor;
                        $hasFacilities = false;

                        if ($isAuditor) { // Auditor
                            $ids = [-1];
                            $clients_audit = $myuser->userdata['clients_audit'];
                            if ($clients_audit != "") {
                              $ids = json_decode($clients_audit);
                            }
                               $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND deleted = 0 AND id IN (".implode(",", $ids).") ORDER BY name";
                          }
                          else if ($isClient) {
                            // Get facilities
                            $sql = "SELECT id, name, prefix FROM tusers WHERE (id = '".$parent_id."' OR parent_id = '".$parent_id."') AND isclient = 1 AND deleted = 0 ORDER BY parent_id ASC, name";
                          
                          }
                          else   { // Admin
                            $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND IFNULL(parent_id,'0') = '0' AND deleted = 0  ORDER BY name";
                          }  
                          
                          $clients = [];
                          $stmt = $dbo->prepare($sql);
                          $stmt->setFetchMode(PDO::FETCH_ASSOC);
                          if ($stmt->execute()) { 
                            $clients = $stmt->fetchAll();
                          }
                          
                              // Fetch all child clients and organize them in an array by parent_id
                              $sql = "SELECT id, name, prefix, parent_id FROM tusers WHERE isclient=1 AND IFNULL(parent_id,'0') <> '0' AND deleted = 0 ORDER BY name";

                              $childClients = [];
                              $stmt = $dbo->prepare($sql);
                              $stmt->setFetchMode(PDO::FETCH_ASSOC);
                              if ($stmt->execute()) { 
                                  $allChildren = $stmt->fetchAll();
                                  foreach ($allChildren as $child) {
                                      $childClients[$child['parent_id']][] = $child; // Group children under parent_id
                                  }
                              }
                          
                          if ($isClient && count($clients) > 1) {
                            $hasFacilities = true;
                          }
                          
                        ?>
            <?php if ($isClient && !$hasFacilities): ?>              
                <input type="hidden" id="prod-clientid" data-email="<?php echo $myuser->userdata['email']; ?>" value=<?php echo $_SESSION['halal']['id']; ?> data-clientname="<?php echo $myuser->userdata['name']," (",$myuser->userdata['prefix'],$myuser->userdata['id'],")"; ?>"/>
            <?php endif;?>

            <?php if (!$isClient || $hasFacilities): ?>
            <div class="form-inline">
              <div class="form-group">
                <label><?php if ($isClient): ?> Facilities <?php else: ?> Clients <?php endif; ?> &nbsp;&nbsp;
                  <select class="form-control clientslist" id="prod-clientid">
                  <?php if (!$isClient): ?>
                    <option value="-1">Select <?php if ($isClient): ?> Facility <?php else: ?> Client <?php endif; ?></option>
                  <?php endif; ?>
                  <?php
                    foreach ($clients as $client) {
                      ?>
                        <option value="<?php echo $client["id"]; ?>" <?php if ($client["id"] == $_GET["idclient"] || $client["id"] == $myuser->userdata['id']):?>selected<?php endif; ?> data-clientname="<?php echo $client['name']," (",$client['prefix'],$client['id'],")"; ?>" ><?php echo $client["name"]; ?> - <?php echo $client["prefix"]; ?><?php echo $client["id"]; ?></option>
                            <?php
                      // Check if there are children for this parent and display them with indentation
                      if (isset($childClients[$client['id']])) {
                        foreach ($childClients[$client['id']] as $child) {
                          ?>
                            <option value="<?php echo $child["id"]; ?>" <?php if ($child["id"] == $_GET["idclient"] || $child["id"] == $myuser->userdata['id']):?>selected<?php endif; ?> 
                                    data-clientname="<?php echo $child['name'], " (", $child['prefix'], $child['id'], ")"; ?>" style="padding-left: 40px;">
                              <?php echo "&nbsp;&nbsp;└── "; ?><?php echo $child["name"]; ?> - <?php echo $child["prefix"]; ?><?php echo $child["id"]; ?>
                            </option>
                          <?php
                        }
                      }
                    }
                  ?>
                  </select>
                </label>
              </div>
            </div>
            <?php endif;?>                        
                         
                    </div>
                    <div class="col-xs-12">
                        <div class="widget-box">
                            <div class="widget-header widget-header-flat widget-header-small">
                                <h5 class="widget-title"><i class="ace-icon fa fa-signal"></i>Statistics</h5>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                <div class="row">
                                    <div class="col-xs-12 col-md-6">
                                        <div class="widget-box">
                                            <div class="widget-header widget-header-flat widget-header-small">
                                                <h5 class="widget-title">Ingredients</h5>
                                            </div>
                                            <div class="widget-body">
                                                <div class="widget-main">
                                                    <div class="infobox-container">
                                                        <div class="infobox infobox-green2">
                                                            <div class="infobox-icon">
                                                                <i class="ace-icon fa fa-flask"></i>
                                                            </div>
                                                            <div class="infobox-data">
                                                                <span id="ingredNumber" class="infobox-data-number">0</span>
                                                                <div class="infobox-content">Allowed</div>
                                                            </div>
                                                            <div class="infobox-progress">
                                                                <div id="ingredPublishedChart" class="easy-pie-chart percentage" data-percent="0" data-size="46" style="height: 46px; width: 46px; line-height: 45px;">
                                                                    <span id="ingredPublishedPercent" class="percent">0</span>%
                                                                    <canvas height="46" width="46"></canvas></div>
                                                            </div>
                                                            <div class="infobox-data">
                                                                <span id="ingredPublished" class="infobox-data-number">0</span>
                                                                <div class="infobox-content">Published</div>
                                                            </div>
                                                        </div>
                                                        <div class="infobox infobox-green2">
                                                            <div class="infobox-progress">
                                                                <div id="ingredConfirmedChart" class="easy-pie-chart percentage" data-percent="0" data-size="46" style="height: 46px; width: 46px; line-height: 45px;">
                                                                    <span id="ingredConfirmedPercent" class="percent">0</span>%
                                                                    <canvas height="46" width="46"></canvas></div>
                                                            </div>
                                                            <div class="infobox-data">
                                                                <span id="ingredConfirmed" class="infobox-data-number">0</span>
                                                                <div class="infobox-content">Confirmed</div>
                                                            </div>
                                                        </div>
                                                        <div class="infobox infobox-green2">
                                                            <div class="infobox-progress">
                                                                <div id="ingredRemainedChart" class="easy-pie-chart percentage" data-percent="0" data-size="46" style="height: 46px; width: 46px; line-height: 45px;">
                                                                    <span id="ingredRemainedPercent" class="percent">0</span>%
                                                                    <canvas height="46" width="46"></canvas></div>
                                                            </div>
                                                            <div class="infobox-data">
                                                                <span id="ingredRemained" class="infobox-data-number">0</span>
                                                                <div class="infobox-content">Remained</div>
                                                            </div>
                                                        </div>
                                                        <div class="infobox infobox-red">
                                                            <div class="infobox-progress">
                                                                <div id="ingredExceededChart" class="easy-pie-chart percentage" data-percent="0" data-size="46" style="height: 46px; width: 46px; line-height: 45px;">
                                                                    <span id="ingredExceededPercent" class="percent">0</span>%
                                                                    <canvas height="46" width="46"></canvas></div>
                                                            </div>
                                                            <div class="infobox-data">
                                                                <span id="ingredExceeded" class="infobox-data-number">0</span>
                                                                <div class="infobox-content">Exceeded</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12 col-md-6">
                                        <div class="widget-box">
                                            <div class="widget-header widget-header-flat widget-header-small">
                                                <h5 class="widget-title">Products</h5>
                                            </div>
                                            <div class="widget-body">
                                                <div class="widget-main">
                                                    <div class="infobox-container">
                                                        <div class="infobox infobox-green">
                                                            <div class="infobox-icon">
                                                                <i class="ace-icon fa fa-shopping-cart"></i>
                                                            </div>
                                                            <div class="infobox-data">
                                                                <span id="prodNumber" class="infobox-data-number">0</span>
                                                                <div class="infobox-content">Allowed</div>
                                                            </div>
                                                            <div class="infobox-progress">
                                                                <div id="prodPublishedChart" class="easy-pie-chart percentage" data-percent="0" data-size="46" style="height: 46px; width: 46px; line-height: 45px;">
                                                                    <span id="prodPublishedPercent" class="percent">0</span>%
                                                                    <canvas height="46" width="46"></canvas></div>
                                                            </div>
                                                            <div class="infobox-data">
                                                                <span id="prodPublished" class="infobox-data-number">0</span>
                                                                <div class="infobox-content">Published</div>
                                                            </div>
                                                        </div>
                                                        <div class="infobox infobox-green">
                                                            <div class="infobox-progress">
                                                                <div id="prodConfirmedChart" class="easy-pie-chart percentage" data-percent="0" data-size="46" style="height: 46px; width: 46px; line-height: 45px;">
                                                                    <span id="prodConfirmedPercent" class="percent">0</span>%
                                                                    <canvas height="46" width="46"></canvas></div>
                                                            </div>
                                                            <div class="infobox-data">
                                                                <span id="prodConfirmed" class="infobox-data-number">0</span>
                                                                <div class="infobox-content">Confirmed</div>
                                                            </div>
                                                        </div>
                                                        <div class="infobox infobox-green">
                                                            <div class="infobox-progress">
                                                                <div id="prodRemainedChart" class="easy-pie-chart percentage" data-percent="0" data-size="46" style="height: 46px; width: 46px; line-height: 45px;">
                                                                    <span id="prodRemainedPercent" class="percent">0</span>%
                                                                    <canvas height="46" width="46"></canvas></div>
                                                            </div>
                                                            <div class="infobox-data">
                                                                <span id="prodRemained" class="infobox-data-number">0</span>
                                                                <div class="infobox-content">Remained</div>
                                                            </div>
                                                        </div>
                                                        <div class="infobox infobox-red">
                                                            <div class="infobox-progress">
                                                                <div id="prodExceededChart" class="easy-pie-chart percentage" data-percent="0" data-size="46" style="height: 46px; width: 46px; line-height: 45px;">
                                                                    <span id="prodExceededPercent" class="percent">0</span>%
                                                                    <canvas height="46" width="46"></canvas></div>
                                                            </div>
                                                            <div class="infobox-data">
                                                                <span id="prodExceeded" class="infobox-data-number">0</span>
                                                                <div class="infobox-content">Exceeded</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div><!-- /.widget-main -->
                            </div><!-- /.widget-body -->
                        </div>
                    </div><!-- /.col -->
                </div><!-- /.row -->
                <?php if(!$myuser->userdata['isclient']):?>
                    <div class="hr hr-dotted"></div>
                    <div class="row gutters">
                    <label class="right">
                        <input id="filter-actions-confirmed" class="ace ace-switch ace-switch-4" type="checkbox">
                        <span class="lbl">&nbsp;&nbsp;Show only confirmed actions</span>
                    </label>
                    </div>                    
                    <div class="row"> <!-- clinet actions -->
                        <div class="col-xs-12">
                            <div class="widget-box">
                                <div class="widget-header widget-header-flat widget-header-small">
                                    <h5 class="widget-title"><i class="ace-icon fa fa-bolt"></i>Client actions</h5>
                                    <div class="pull-right action-buttons pt5">
                                        <a href="#" class="blue" onclick="DP.onRefreshClientActions(event)">
                                            <i class="ace-icon fa fa-refresh bigger-130"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="widget-body">
                                    <div id="clientactions-container" class="widget-main">
                                        <table id="clientActionsGrid"></table>
                                        <div id="clientActionsPager"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif;?>
                    <?php if(!$myuser->userdata['isclient']):?>
                        <div class="hr hr-dotted"></div>
                    <div class="row gutters">
                    <label class="right">
                        <input id="filter-process-confirmed" class="ace ace-switch ace-switch-4" type="checkbox">
                        <span class="lbl">&nbsp;&nbsp;Show only confirmed actions</span>
                    </label>
                    </div>                    
                    <div class="row"> <!--process status -->
                        <div class="col-xs-12">
                            <div class="widget-box">
                                <div class="widget-header widget-header-flat widget-header-small">
                                    <h5 class="widget-title"><i class="ace-icon fa fa-bolt"></i>Process status</h5>
                                    <div class="pull-right action-buttons pt5">
                                        <a href="#" class="blue" onclick="DP.onRefreshProcessStatus(event)">
                                            <i class="ace-icon fa fa-refresh bigger-130"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="widget-body">
                                    <div id="process-status-container" class="widget-main">
                                        <table id="processStatusGrid"></table>
                                        <div id="processStatusPager"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>    
                    <div class="hr hr-dotted"></div>
                <?php endif; ?>
                <?php if(!$myuser->userdata['isclient'] || $myuser->userdata['isclient'] == '2'):?>
                    <div class="row gutters">
                    <label class="right">
                        <input id="filter-auditreport-confirmed" class="ace ace-switch ace-switch-4" type="checkbox">
                        <span class="lbl">&nbsp;&nbsp;Show only confirmed tasks</span>
                    </label>
                    </div>   
                    <div class="row"> <!-- Audit report actions -->
                        <div class="col-xs-12">
                            <div class="widget-box">
                                <div class="widget-header widget-header-flat widget-header-small">
                                    <h5 class="widget-title"><i class="ace-icon fa fa-bolt"></i>Audit Report</h5>
                                    <div class="pull-right action-buttons pt5">
                                        <a href="#" class="blue" onclick="DP.onRefreshAuditReport(event)">
                                            <i class="ace-icon fa fa-refresh bigger-130"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="widget-body">
                                    <div id="auditreport-container" class="widget-main">
                                        <table id="auditReportGrid"></table>
                                        <div id="auditReportPager"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif;?>
                <div class="hr hr-dotted"></div>
                <div class="row gutters">
                    <label class="right">
                        <input id="filter-confirmed" class="ace ace-switch ace-switch-4" type="checkbox">
                        <span class="lbl">&nbsp;&nbsp;Show only confirmed tasks</span>
                    </label>
                </div>
                <div class="row"> <!-- tasks -->
                    <div class="col-xs-12">
                        <div class="widget-box">
                            <div class="widget-header widget-header-flat widget-header-small">
                                <h5 class="widget-title"><i class="ace-icon fa fa-tasks"></i>Tasks</h5>
                                <div class="pull-right action-buttons pt5">
                                    <a href="#" class="blue" onclick="DP.onRefreshTasks(event)">
                                        <i class="ace-icon fa fa-refresh bigger-130"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="widget-body">
                                <div id="activetasks-container" class="widget-main">
                                    <table id="activeTasksGrid"></table>
                                    <div id="activeTasksPager"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hr hr-dotted"></div>
                <div class="row">
                    <div class="col-xs-12 col-md-7">
                        <div class="widget-box">
                            <div class="widget-header widget-header-flat widget-header-small">
                                <h5 class="widget-title"><i class="ace-icon fa fa-certificate"></i>Certificates</h5>
                                <div class="pull-right action-buttons pt5">
                                    <a href="#" class="blue" id="refresh-certificates" onclick="DP.onRefreshCertificates(event)">
                                        <i class="ace-icon fa fa-refresh bigger-130"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="widget-body">
                                <div id="certificates-container" class="widget-main">
                                    <span class="fileinput-button" id="dropzone1">Drop certificate here or click to select from disk
                    	                <input class="fileupload" id="fileupload1" type="file" name="files[]" foldertype="certificates" infotype="certificate">
                                    </span><span id="dropzone1-loader" class="width-100 text-center padding-5"><i class="fa fa-spinner fa-spin"></i>&nbsp;&nbsp;Uploading...</span>
                                    <table id="certificatesGrid"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-5">
                        <div class="widget-box">
                            <div class="widget-header widget-header-flat widget-header-small">
                                <h5 class="widget-title"><i class="ace-icon fa fa-file"></i>Files</h5>
                                <div class="pull-right action-buttons pt5">
                                    <a href="#" class="blue" id="refresh-files" onclick="DP.onRefreshFiles(event)">
                                        <i class="ace-icon fa fa-refresh bigger-130"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="widget-body">
                                <div id="files-container" class="widget-main">
                                  <div class="row no-gutters mb6 filinfo-button">
                                    <div class="col-sm-12 col-md-5">
                                      <input id='file-name' class="form-control" placeholder="File name to display" type="text"/>
                                    </div>
                                    <div class="col-sm-12 col-md-5">
                                      <input id='file-link' class="form-control" placeholder="File Google Drive link" type="text"/>
                                    </div>
                                    <div class="col-sm-12 col-md-2">
                                      <div class="hidden" id="file-loader"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></div>
                                      <button type="button" id="file-add" class="btn btn-sm btn-primary pull-right" title="Add file info to the list" onclick="DP.onAddFileInfo();"><i class="fa fa-plus fa-fw"></i>&nbsp;Add</button>
                                    </div>
                                    <div class="col-sm-12">
                                      <div class="alert-string"></div>
                                      <div class="success-string"></div>
                                    </div>
                                  </div>
                                  <table id="filesGrid"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
</div><!-- /.main-container -->
<!-- Certificate upload Modal -->
<div class="modal fade" id="certificateModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="certificateModal-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="certificateModal-label">Set Certificate Expiry Date</h4>
            </div>
            <div class="modal-body row">
                <from id="certificate-form" class="col-md-12 form-horizontal">
                    <input type="hidden" class="id"/>
                    <input type="hidden" class="url"/>
                    <input type="hidden" class="hostpath"/>
                    <input type="hidden" class="gdrivepath"/>
                    <input type="hidden" class="filename"/>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Certificate</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="filename form-control" readonly/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Expiry Date</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="expdate form-control datepicker"/>
                            <div class="alert-string"></div>
                        </div></div>
                </from>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-save="modal" onclick="DP.onSaveCertificateFromModal(event);" >Save changes</button>
            </div>
        </div>
    </div>
</div>
<!--Email printed doc-->
<?php if($myuser->userdata['isclient']):?>
        <div class="modal fade" id="emailModal" tabindex="-1" role="dialog" data-backdrop="static"  aria-labelledby="emailModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                        <h4 class="modal-title" id="emailModal-label">Email certificate</h4>
                    </div>
                    <div class="modal-body row">
                        <form id="email-form" class="col-sm-12">
                            <input id='email-attach-hostpath' class="form-control" type="hidden"/>
                            <div class="form-group"><label>From/User Name</label><input id='email-from' class="form-control" type="text"/>
                                <div class="alert alert-string"></div>
                            </div>
                            <div class="form-group"><label>Email</label><input id='email-email' class="form-control email" type="text"/></div>
                            <div class="form-group"><label>To</label><input id='email-to' class="form-control email" placeholder="Recipient email address. Use comma to separate multiple addresses" type="text"/>
                                <div class="alert alert-string"></div>
                            </div>
                            <div class="form-group"><label>Cc</label><input id='email-cc' class="form-control email" placeholder="Email address to send the message copy to. Use comma to separate multiple addresses" type="text"/>
                                <div class="alert alert-string"></div>
                            </div>
                            <div class="form-group"><label>Subject</label><input id='email-subject' class="form-control" placeholder="Subject of the email" type="text"/>
                                <div class="alert alert-string"></div></div>
                            <div class="form-group"><label>Message</label><textarea id='email-message' class="form-control" placeholder="Your message here.." rows="5"></textarea>
                                <div id='email-formerror' class="alert alert-string"></div></div>
                            <div class="form-group"><label>Attachment</label><input type="text" id='email-attach' class="form-control" readonly/>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <div class="pull-left" id="email-loader"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></div>
                        <button type="button" id="email-send" class="btn btn-primary" onclick="DP.onSendEmail();">Send</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif;?>
    <?php include_once('pages/footer.php');?>

                <!-- page specific plugin scripts -->
                <script src="js/bootstrap-datepicker.min.js"></script>
                <script src="js/jquery.jqGrid.min.js"></script>
                <script src="js/grid.locale-en.js"></script>
                <!-- ace scripts -->
                <script src="js/ace-elements.min.js"></script>
                <script src="js/ace.min.js"></script>
                <script src="js/jquery.easypiechart.min.js"></script>
                <script src="js/vendor/jquery.ui.widget.js"></script>
                <script src="js/jquery.iframe-transport.js"></script>
                <script src="js/jquery.fileupload.js"></script>
                <script src="js/all.js?v=<?php echo rand();?>"></script>
                

                <!-- Menu Toggle Script -->
                <script>
                    var userId = <?php echo $_SESSION['halal']['id'] ?>;
                    Common.onDocumentReady();
                    DP.onDocumentReady();
                </script>
</body>
</html>
