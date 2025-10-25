<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <title>Ingredients - Halal Digital</title>
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
	$dbo =  $db->connect(); // Создаем объект подключения к БД
}
catch (PDOException $e) {
    echo 'Database error: '.$e->getMessage();
}
	$query = "
        SELECT * FROM tproducers WHERE active=1 ORDER BY name";

		$stmt = $dbo->prepare($query);
		$stmt->execute();
	$producers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">
                    <div class="widget-box widget-border" style="margin: 15px 0;">
                        <div class="widget-body">
                            <div class="widget-main">
                        <?php
						$myuser = cuser::singleton();
                        $myuser->getUserData();
						$sources = [];
						if ($myuser->userdata['isclient'] == '2') {
							$sources = json_decode($myuser->userdata['sources_audit']);
						}
                     
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

                          if ( $myuser->userdata['ingredients_preference'] == '1') {
                               $hasFacilities = false;
                          }

						?>
                        <input type="hidden" id="filter-rmid" <?php echo 'value="'.(isset($_GET['id']) ? $_GET['id'] : '').'"'; ?> />
                        <input type="hidden" id="filter-idclient" <?php echo 'value="'.(isset($_GET['idclient']) ? $_GET['idclient'] : '').'"'; ?> />
                        <?php if ($isClient && !$hasFacilities): ?>              
                            <input type="hidden" id="ingred-clientid" data-email="<?php echo $myuser->userdata['email']; ?>" value=<?php echo $_SESSION['halal']['id']; ?> data-clientname="<?php echo $myuser->userdata['name']," (",$myuser->userdata['prefix'],$myuser->userdata['id'],")"; ?>"/>
                        <?php endif;?>

                        <?php if (!$isClient || $hasFacilities): ?>
                        <div class="form-inline">
                        <div class="form-group">
                            <label><?php if ($isClient): ?> Facilities <?php else: ?> Clients <?php endif; ?> &nbsp;&nbsp;
                            <select class="form-control clientslist" id="ingred-clientid">
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
                        </div>
                        </div>
                    </div>

                    
                            <div class="col-xs-12">
 
                        <!-- PAGE CONTENT BEGINS -->
                        <table id="ingredGrid"></table>
                        <div id="ingredPager"></div>
                        <ul class="legend">
                            <li><span class="highlighted-conformed">Confirmed</span> </li>
                            <li><span class="highlighted-preconformed">Pre Confirmed</span></li>
                            <li><span class="highlighted-nonconformed">Non-Confirmed</span></li>
                            <li><span class="highlighted-expired">Expired</span></li>
                            <li><span class="highlighted-week">Expire in 1 Week</span></li>
                            <li><span class="highlighted-4week">Expire in 4 Weeks</span></li>
                            <li><span class="highlighted-8week">Expire in 8 Weeks</span></li>
                        </ul>
                        <!-- PAGE CONTENT ENDS -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
</div><!-- /.main-container -->
<!-- Ingredients Modal -->
<div class="modal fade" id="ingredModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="prodModal-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="ingredModal-label">Add Product</h4>
            </div>
            <div class="modal-body">
                <div id="activeTasksGridBox" class="row"> <!-- tasks -->
                    <div class="col-xs-12">
                        <div class="widget-box">
                            <div class="widget-header widget-header-flat widget-header-small">
                                <h5 class="widget-title"><i class="ace-icon fa fa-tasks"></i>Tasks</h5>
                            </div>
                            <div class="widget-body">
                                <div id="activetasks-container" class="widget-main">
                                    <table id="activeTasksGrid"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                <from id="ingred-form" class="col-md-12 form-horizontal">
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">RM ID</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" readonly id="rmid"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4"><b>RM Code</b>&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please add your internal raw material number if you have any"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="code" maxlength="50"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4"><b>Name</b>&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please add the name of the ingredient"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="name" maxlength="100"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4"><b>Supplier Name</b>&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please add the name of your supplier"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="supplier" maxlength="100"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4"><b>Producer Name</b>&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please add the name of your producer"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="producer" maxlength="100"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4"><b>Source of Raw Material</b>&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please choose the source of the raw material from the dropdown list"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control" id="material" title="Select material">
                                <option role="option" value="">Not specified</option>
                                <?php if ($myuser->userdata['isclient'] == '2'):?>
                                    <?php if (in_array('Animal', $sources)): ?>
                                    <option role="option" value="Animal">Animal</option>
                                    <?php endif; ?>
                                    <?php if (in_array('Plant', $sources)): ?>
                                    <option role="option" value="Plant">Plant</option>
                                    <?php endif; ?>
                                    <?php if (in_array('Synthetic', $sources)): ?>
                                    <option role="option" value="Synthetic">Synthetic</option>
                                    <?php endif; ?>
                                    <?php if (in_array('Mineral', $sources)): ?>
                                    <option role="option" value="Mineral">Mineral</option>
                                    <?php endif; ?>
                                    <?php if (in_array('Cleaning agents', $sources)): ?>
                                    <option role="option" value="Cleaning agents">Cleaning agents</option>
                                    <?php endif; ?>
                                    <?php if (in_array('Other agents', $sources)): ?>
                                    <option role="option" value="Other agents">Other agents</option>
                                    <?php endif; ?>
                                <?php else: ?>
                                <option role="option" value="Animal">Animal</option>
                                <option role="option" value="Plant">Plant</option>
                                <option role="option" value="Synthetic">Synthetic</option>
                                <option role="option" value="Mineral">Mineral</option>
                                <option role="option" value="Cleaning agents">Cleaning agents</option>
                                <option role="option" value="Packaging Material">Packaging Material</option>
                                <option role="option" value="Other agents">Other agents</option>
                                <?php endif;?>
                            </select>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Subingredient&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please activate this function if required by auditor"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <label>
                                <input id="subingredient" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                                <span class="lbl"></span>
                            </label>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Ingredients&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please use this function only if required by auditor"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <select class="form-control selectpicker" id="ingredients" multiple
                                    title="Choose ingredients"></select>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Specification&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload the ingredient specification"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                        </div>
                        <span class="fileinput-button i-dropzone" id="dropzone1"><span class="spinner-border spinner-border-sm"></span>Drop files here or click to upload
                        <input class="fileupload" id="fileupload1" type="file" name="files[]" multiple foldertype="spec" infotype="ingredient">
                            </span>
                            <ul id="ulspec"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Supplier Questionnaire&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload the questionnaire only if required by auditor"></sup></label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                        </div>
                        <span class="fileinput-button i-dropzone" id="dropzone2">Drop files here or click to upload
                        <input class="fileupload" id="fileupload2" type="file" name="files[]" multiple foldertype="quest" infotype="ingredient">
                            </span>
                            <ul id="ulquest"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Supplier Statement&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload the statement only if required by auditor"></sup></label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                        </div>
                        <span class="fileinput-button i-dropzone" id="dropzone3">Drop files here or click to upload
                        <input class="fileupload" id="fileupload3" type="file" name="files[]" multiple foldertype="state" infotype="ingredient">
                            </span>
                            <ul id="ulstate"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Halal Certified&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please set certified if Halal certificate is available"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <label>
                                <input id="certified" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                                <span class="lbl"></span>
                            </label>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Halal Certificate&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please upload Halal certifiate if available"></sup></label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                        </div>
                        <span class="fileinput-button cert-dropzone" id="dropzone4">Drop files here or click to upload
                        <input class="cert-fileupload" id="fileupload4" disabled type="file" name="files[]" multiple foldertype="cert" infotype="ingredient">
                         </span>
                            <ul id="ulcert"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">HC Body Name&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please specify Halal certifiation body name"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" disabled id="cb" maxlength="100"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">HC Expiry Date&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please specify Halal certifiate expiry date"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datepicker" disabled id="date"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">RM Position&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please specify raw material position in the certificate"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" disabled id="rmposition" maxlength="50"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Additional Documents&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please add any additional related documents"></sup></label>
                        <div class='col-xs-12 col-md-8'><!-- The fileinput-button span is used to style the file input field as button -->
                        <div class="cert-progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: %"></div>
                        </div>
                        <span class="fileinput-button i-dropzone" id="dropzone5">Drop files here or click to upload
                        <input class="fileupload" id="fileupload5" type="file" name="files[]" multiple foldertype="add" infotype="ingredient">
                         </span>
                            <ul id="uladd"></ul>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Note&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please write any notes you would like to inform your auditor about"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <textarea class="form-control input" id="note" rows="3" maxlength="500"></textarea>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Halal Conformed</label>
                        <div class='col-xs-12 col-md-8'>
                            <label>
                                <input id="conformed" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                                <span class="lbl"></span>
                            </label>
                            <div class="alert-string"></div>
                        </div></div>
                </from></div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-xs-12 col-md-6 text-center"><p class="form-warning"><i class="fa fa-warning"></i>&nbsp;&nbsp;Mandatory fields are not specified</p></div>
                    <div class="col-xs-12 col-md-3"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                    <div class="col-xs-12 col-md-3"><button type="button" class="btn btn-primary" onclick="IP.onSave();" >Save changes</button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Email printed doc-->
<?php if(!$myuser->userdata['isclient'] || $myuser->userdata['isclient'] == '2'):?>
    <div class="modal fade" id="tasksModal" tabindex="-1" role="dialog" data-backdrop="static"  aria-labelledby="tasksModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                    <h4 class="modal-title" id="tasksModal-label">Tasks for the ingredient</h4>
                </div>
                <div class="modal-body row">
                <div class="col-xs-12 tasks-container">
                    <input type="hidden" class="id" id="task-id" name="id" value="" />
                    <div class="row no-gutters mb6">
                      <div class="col-sm-12 col-md-5">
                        <textarea id='task-deviation' class="form-control" style="height: 50px;" placeholder="Deviation"></textarea>
                      </div>
                      <div class="col-sm-12 col-md-5">
                      <textarea id='task-measure' class="form-control" style="height: 50px;" placeholder="Measure"></textarea>
                      </div>
                      <div class="col-sm-12 col-md-2">
                        <div class="hidden" id="task-loader"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i></div>
                        <button style="width:100%;" type="button" id="task-add" class="btn btn-sm btn-primary pull-right" title="Add new task to the list" onclick="IP.onAddTask();"><i class="fa fa-plus fa-fw"></i>&nbsp;Add</button>
                        <button style="width:100%; margin-bottom:5px;display:none;" type="button" id="task-edit"  class="btn btn-sm btn-primary pull-right" title="Edit task" onclick="IP.onUpdateTask();"><i class="fa fa-edit fa-fw"></i>&nbsp;Update</button>
                        <button style="width:100%;display:none;" type="button" id="task-cancel"  class="btn btn-sm btn-danger pull-right" title="Cancel Edit" onclick="IP.onCancelTask();"><i class="fa fa-close fa-fw"></i>&nbsp;Cancel</button>
                      </div>
                      <div class="col-sm-12">
                        <div class="alert-string"></div>
                        <div class="success-string"></div>
                      </div>
                    </div>


                    <div class="input-group my-group" style="padding: 10px 0;">

    <input type="text" class="form-control" name="s" id="s" onkeyup="IP.onSearchTasks();"  style="width:300px;" placeholder="Search..."/>

    <span class="input-group-btn pull-left">
        <button class="btn btn-primary my-group-button btn-filter" type="button" style="padding:3px 12px; height:34px;" onclick="IP.onSearchTasks();"><span class="glyphicon glyphicon-search"></span> </button>
    </span>
</div>
                     <table id="tasksGrid"></table>

                  </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endif;?>
<div class="modal fade" id="infoModal" tabindex="-1" role="dialog" data-backdrop="static"  aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="infoModal-label">Information</h4>
            </div>
            <div class="modal-body row">
              <div class="col-xs-12"><i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>&nbsp;Creating Excel report...</div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="logModal" tabindex="-1" role="dialog" data-backdrop="static"  aria-labelledby="logModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="ingredModal-label">Ingredient History Log</h4>
            </div>
            <div class="modal-body row">
            <div class="table-responsive">
            <table class="table table-bordered" id="table_log">
                <thead>
  <tr>
    <th>Updated By</th>
    <th>Updated On</th>
    <th>RMC_ID</th>
    <th>RM Code</th>
    <th>Name</th>
    <th>Tasks</th>
    <th>Halal Conformed</th>
    <!--<th>tasksnumber</th>-->
    <th>Subingredient</th>
    <th>Supplier</th>
    <th>Producer</th>
    <th>Raw Material</th>
    <th>Halal Certified</th>
    <th>Halal Certificate</th>
    <th>Halal Certification Body</th>
    <th>Cert. Exp. Date</th>
    <th>RM Position</th>
    <th>Ingredients</th>
    <th>Product Specification</th>
    <th>Supplier Questionnaire</th>
    <th>Supplier Statement</th>
    <th>Additional Documents</th>
    <th>Note</th>
    <th>status</th>
    <th>Deleted</th>
  </tr>
                </thead>
</table>
            </div>

            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="paModal" tabindex="-1" role="dialog" data-backdrop="static"  aria-labelledby="logModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="ingredModal-label">Pre-Approved Ingredients</h4>
            </div>
            <div class="modal-body">
            <div class="row">
                      <div class="col-md-3">
                        <select class="form-control" name="sproducer_id" id="sproducer_id">
                          <option value="">Select Producer</option>
                          <?php foreach ($producers as $producer): ?>
                            <option value="<?php echo $producer["id"]; ?>"><?php echo $producer["name"]; ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-3"><input type="text" class="form-control" name="srmcode" id="srmcode" placeholder="RM Code"/></div>
                      <div class="col-md-3"><input type="text" class="form-control" name="sname" id="sname" placeholder="Name"/></div>
                      <div class="col-md-3"><button class="btn btn-primary btn-select-pa" style="padding: 2px 10px;">Add Selected Ingredients</button></div>
                    </div>

            <div class="table-responsive">
             <table id="table_tank" class="table table-hover table-striped table-bordered">
                      <thead>
                        <tr class="tableheader">
                        <th>Producer</th>
                        <th>RM Code</th>
                        <th>RM Name</th>
                        <th>Halal Certification Body</th>
                        <th>Cert. Exp. Date</th>
                        <th>RM Position</th>
                        <th style="width:25px;" class="no-sort"><input type="checkbox" name="checkall" id="checkall" value="1" /></th>
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



<div class="modal fade" id="ingredModals" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="prodModal-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="ingredModal-label">Add Product</h4>
            </div>
            <div class="modal-body">
                <div id="activeTasksGridBox" class="row"> <!-- tasks -->
                    <div class="col-xs-12">
                        <div class="widget-box">
                            <div class="widget-header widget-header-flat widget-header-small">
                                <h5 class="widget-title"><i class="ace-icon fa fa-tasks"></i>Tasks</h5>
                            </div>
                            <div class="widget-body">
                                <div id="activetasks-container" class="widget-main">
                                    <table id="activeTasksGrid"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
               </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-xs-12 col-md-6 text-center"><p class="form-warning"><i class="fa fa-warning"></i>&nbsp;&nbsp;Mandatory fields are not specified</p></div>
                    <div class="col-xs-12 col-md-3"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                    <div class="col-xs-12 col-md-3"><button type="button" class="btn btn-primary" onclick="IP.onSave();" >Save changes</button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal window -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal window contents -->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Halal certificate info</h4>
      </div>
      <div class="modal-body">

         <from id="ingred-forms" class="col-md-12 form-horizontal">


                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">HC Body Name&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please specify Halal certifiation body name"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control"  id="cb" maxlength="100"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">HC Expiry Date&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please specify Halal certifiate expiry date"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control datepicker"  id="date"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">RM Position&nbsp;<sup class="fa fa-info-circle tooltip-info" data-toggle="tooltip" data-placement="right" title="Please specify raw material position in the certificate"></sup></label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control"  id="rmposition" maxlength="50"/>
                            <div class="alert-string"></div>
                        </div></div>

                </from>
      </div>
      <div class="modal-footer">

              <button type="submit" class="btn btn-default submit" >Send</button>
      </div>
    </div>

  </div>
</div>

<div id="bulkingredient" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal window contents -->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Bulk import ingredients</h4>
            </div>
            <div class="modal-body">

                <!-- Step 1: Select file for upload -->
                <div id="step1" class="step">
                    <h4>Step 1. Bulk upload</h4>
                    <div class="form-group">
                        <label>1. Choose from one of the following options</label>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="documentType" value="certificate" checked> Halal certificate
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="documentType" value="statement"> Supplier statement / Halal
                                declaration
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="documentType" value="none"> None of them
                            </label>
                        </div>

                        <div class="upload-box" id="group-document-upload-box">
                            <div class="progress" style="display: none; width: 100%">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                            </div>
                            <span class="fileinput-button bulkingred-dropzone" id="group-document-dropzone">Drop files here or click to upload
                                <input class="bulkingred-fileupload" id="group-document-fileupload" type="file" name="files[]">
                            </span>
                            <ul class="uploaded-files" style="display: none"></ul>
                            <input type="hidden" name="document" id="documentFile" class="uploaded-file-hidden-input">
                        </div>

                    </div>
                    <div class="form-group certificate-fields">
                        <label>2. Enter the certificate information</label>

                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Producer name" name="producerName">
                        </div>


                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Supplier name (if different from producer name)" name="supplierName">
                        </div>

                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Halal certification body name" name="certificationBodyName">
                        </div>

                        <div class="form-group">
                            <input type="text" class="form-control datepicker" placeholder="Expiry date" name="expiryDate">
                        </div>
                    </div>

                    <div class="form-group statement-fields" style="display: none">
                        <label>2. Enter the supplier statement information</label>

                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Supplier name" name="statementSupplierName">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <div class="buttons">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary nextBtn">Next</button>
                        </div>
                    </div>
                </div>


                <!-- Step 2: Ingredient upload -->
                <div id="step2" class="step" style="display: none;">
                    <p>Please upload an Excel or CSV file with ingredients, one row per ingredient.</p>

                    <div class="upload-box" id="ingredient-spreadsheet-upload-box">
                        <div class="progress" style="display: none; width: 100%">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                        </div>
                        <span class="fileinput-button bulkingred-dropzone" id="ingredient-spreadsheet-dropzone">Drop files here or click to upload
                            <input class="bulkingred-fileupload" id="ingredient-spreadsheet-fileupload" type="file" name="files[]">
                        </span>
                        <ul class="uploaded-files" style="display: none"></ul>
                        <input type="hidden" name="ingredientList" id="spreadsheetFile" class="uploaded-file-hidden-input">
                    </div>

                    <div>
                      <a id="document-certificate" href="/files/example-ingredient-bulk-upload-with-certificate.xlsx" target="_blank">Download an example Excel file</a>
                      <a id="document-statement" href="/files/example-ingredient-bulk-upload-with-statement.xlsx" target="_blank" style="display: none">Download an example Excel file</a>
                      <a id="document-none" href="/files/example-ingredient-bulk-upload-without-documents.xlsx" target="_blank" style="display: none">Download an example Excel file</a>
                    </div>

                    <div class="instruction-box">
                        <p>Instructions:</p>
                        <p>Your CSV or Excel file must contain the following columns:</p>
                        <table class="table table-bordered">
                            <tr>
                                <td>Name</td>
                                <td>Ingredient name</td>
                            </tr>
                            <tr>
                                 <td>Code</td>
                                 <td>Your internal code of raw material</td>
                             </tr>
                            <tr class="document-on-show hide">
                                <td>Position</td>
                                <td>Ingredient position in the certificate or statement file</td>
                            </tr>
                            <tr>
                                <td>Source</td>
                                <td>Source of raw material (must be one of: Animal, Plant, Synthetic, Mineral,
                                    Cleaning agents, Packaging material, Others)</td>
                            </tr>
                            <tr class="document-off-show hide">
                                 <td>Producer name</td>
                                 <td>Name of raw material producer</td>
                             </tr>
                        </table>
                        <p>The first row of the CSV or Excel file must contain the column names exactly as specified above.</p>
                    </div>
                    <div class="modal-footer">
                        <div class="buttons">
                            <button type="button" class="btn btn-default prevBtn">Back</button>
                            <button type="button" class="btn btn-primary nextBtn">Next</button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Upload results -->
                <div id="step3" class="step" style="display: none;">
                    <div id="bulk-ingredient-upload-progress" style="display: none">loading...</div>

                    <div id="bulk-ingredient-upload-message"></div>

                    <div id="bulk-ingredient-upload-result">
                        <p>Your data was uploaded with the following result:</p>

                        <div class="results-section">
                            <div class="alert alert-success">
                                <strong class="total"></strong> Total uploaded
                            </div>
                            <div class="alert alert-info">
                                <strong class="success"></strong> Successfully imported
                            </div>
                            <div class="alert alert-danger">
                                <strong class="failed"></strong> Errors
                            </div>
                        </div>
                        <div style="display: none" class="import-error-list">
                            <p>The records below were not imported because of the errors:</p>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Ingredient name</th>
                                    <th>Error description</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="buttons">
                            <button type="button" class="btn btn-default resetBtn">New Upload</button>
                            <button type="button" class="btn btn-primary finishBtn">Finish</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Bulk Halal Certificate Update Modal -->
<div id="bulkHalalCertUpdate" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-certificate"></i> Update Halal Certificates</h4>
            </div>
            <div class="modal-body">
                
                <div id="selected-ingredients-info" class="alert alert-info">
                    <strong><span id="selected-count">0</span> ingredients selected</strong> for certificate update.
                </div>

                <div class="form-group">
                    <label>Upload Halal Certificate</label>
                    <div class="upload-box" id="bulk-halal-cert-upload-box">
                        <div class="progress" style="display: none; width: 100%">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                        </div>
                        <span class="fileinput-button bulk-halal-cert-dropzone" id="bulk-halal-cert-dropzone">
                            Drop certificate file here or click to upload
                            <input class="bulk-halal-cert-fileupload" id="bulk-halal-cert-fileupload" type="file" name="files[]" accept=".pdf,.jpg,.jpeg,.png">
                        </span>
                        <ul class="uploaded-files" style="display: none"></ul>
                        <input type="hidden" name="halalCertificateFile" id="halalCertificateFile" class="uploaded-file-hidden-input">
                    </div>
                </div>

                <div class="form-group">
                    <label>Producer Name *</label>
                    <input type="text" class="form-control" id="bulkProducerName" placeholder="Enter producer name" required>
                </div>

                <div class="form-group">
                    <label>Supplier Name</label>
                    <input type="text" class="form-control" id="bulkSupplierName" placeholder="Enter supplier name (if different from producer)">
                </div>

                <div class="form-group">
                    <label>Halal Certification Body *</label>
                    <input type="text" class="form-control" id="bulkCertificationBody" placeholder="Enter certification body name" required>
                </div>

                <div class="form-group">
                    <label>Certificate Expiry Date *</label>
                    <input type="text" class="form-control datepicker" id="bulkExpiryDate" placeholder="Select expiry date" required>
                </div>

                <!-- Progress Section -->
                <div id="bulk-cert-progress" style="display: none">
                    <h5>Processing Updates...</h5>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">
                            <span class="progress-text">0%</span>
                        </div>
                    </div>
                    <div id="progress-details"></div>
                </div>

                <!-- Results Section -->
                <div id="bulk-cert-results" style="display: none">
                    <div class="alert alert-success">
                        <h5><i class="fa fa-check-circle"></i> Update Complete</h5>
                        <p>
                            <strong>Total:</strong> <span id="result-total">0</span> | 
                            <strong>Success:</strong> <span id="result-success">0</span> | 
                            <strong>Failed:</strong> <span id="result-failed">0</span>
                        </p>
                    </div>
                    
                    <div id="error-details" style="display: none">
                        <h5>Failed Updates:</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th>Ingredient</th>
                                        <th>Error</th>
                                    </tr>
                                </thead>
                                <tbody id="error-list"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="processBulkCertBtn">
                    <i class="fa fa-upload"></i> Update Certificates
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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

<!-- Menu Toggle Script -->
<script>
    var userId = <?php echo $_SESSION['halal']['id'] ?>;
    Common.onDocumentReady();
    IP.onDocumentReady();
    $(document).ready(function() {

        $('#checkall').click(function() {
            var checked = $(this).prop('checked');
            $('#table_tank').find('input:checkbox').prop('checked', checked);
        });

        $(document).on("click", ".btn-select-pa", function () {
            if ($("#table_tank tbody input:checked").length == 0) {
                alert("Please select at least one ingredient.");
                return false;
            }
            var doc = {};
            doc.idclient = $("#ingred-clientid").val();
            doc.ids = $('#table_tank tbody input:checked').map(function() {return this.value;}).get().join(',');

            $.post("ajax/ajaxHandler.php", {
            rtype: "savePAIngredient",
            uid: 0,
            data: doc,
            }).done(function (data) {
            var response = JSON.parse(data);
            if (response.status == 0) {
                Utils.notify("error", response.statusDescription);
                return;
            }
            $("#paModal").modal("hide");
            jQuery("#ingredGrid").jqGrid().trigger("reloadGrid");
            });
            return false;
        });


        $(document).on("keyup", "#gs_rmid", function() {
            $("#filter-rmid").val($(this).val());
        });
        //return;
        var table_log = $('#table_log').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": true,
        "responsive": true,
        "autoWidth": false,
        "pageLength": 25,
		"pagingType": "full_numbers",
		"processing": true,
        "serverSide": true,
        "drawCallback" : function(settings) {

            // reset style before re-checking
            $('#table_log td.changed').removeClass('changed');

                // get rows as an array of array
                var rows = $('#table_log tbody>tr').map(function(elem,i){
                    return [$(this).children('td').toArray()];
                }).toArray();

                // only 2 rows, so no need to loop them
                // start at j=1 to skip first column
                for(var i = 0; i < rows.length; i++){
                    for(var j = 0; j < rows[i].length; j++){
                        if (i < rows.length - 1 && j != 1) {
                            if (rows[i][j].innerText != rows[i+1][j].innerText)
                            {
                                $(rows[i][j]).addClass('changed');
                                $(rows[i+1][j]).addClass('changed');
                            }
                        }
                    }
                  }
        },
		"createdRow": function( row, data, dataIndex){
			//if ( data['deleted'] ==  '1'){
				//$(row).addClass('strikeout');
			//}
		},
        "ajax": {
          "url": "../ajax/getIngredientsLog.php",
          "type": "POST",
		   "async": true,
		   "data": function(data) {
                var idingredient = -1;
                if ($("#ingredGrid").getGridParam("selarrrow")) {
                    idingredient = $("#ingredGrid").getGridParam("selarrrow")[0];
                }
			   data.idingredient=idingredient;
		  }
        },
        "columns": [
            { "data": "created_by"},
            { "data": "created_at"},

	{ "data": "id"},
	{ "data": "rmcode"},
	{ "data": "name"},
	{ "data": "tasks"},
	{ "data": "conf"},
	{ "data": "sub"},
	{ "data": "supplier"},
	{ "data": "producer"},
	{ "data": "material"},
	{ "data": "halalcert"},
	{ "data": "cert"},
	{ "data": "cb"},
	{ "data": "halalexp"},
	{ "data": "rmposition"},
	{ "data": "ingredients"},
	{ "data": "spec"},
	{ "data": "quest"},
	{ "data": "statement"},
	{ "data": "addoc"},
	{ "data": "note"},
	{ "data": "status"},
	{ "data": "deleted"},
        ],
		"columnDefs": [{
		"targets"  : 'no-sort',
      	"orderable": false,
     	}],
      });

    });

</script>
<script src="pages/pa-ingreds/ingred.js"></script>
</body>
</html>
