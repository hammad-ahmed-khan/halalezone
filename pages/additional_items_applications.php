<!DOCTYPE html>
<html lang="en">
<head>
<?php
  include_once "config/config.php";
  include_once('pages/header.php');
  include_once ('includes/func.php');
  
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
  $myuser = cuser::singleton();
  $myuser->getUserData();

  $parent_id = $myuser->userdata['id'];
  $isClient = $myuser->userdata['isclient'] == "1" ? true : false;
  $isAuditor = $myuser->userdata['isclient'] == '2' ? true : false;
  $isSuperAdmin = $myuser->userdata['superadmin'] == "1" ? true : false;
  $isAdmin = (!$isClient && !$isAuditor && !$isSuperAdmin);
  
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

if ($isClient && count($clients) > 1) {
  $hasFacilities = true;
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

  $selCycleId = "";
  $selCycleState = "1";
  $selClientId = "";
  $selAppId = "";

  if ($_GET["idclient"] != "") { // Admin or Auditor or Client with Facilities
    $sql = "SELECT * FROM tcycles WHERE idclient = :idclient ORDER BY id";
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':idclient', $_GET['idclient']);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    if ($stmt->execute()) { 
      $cycles = $stmt->fetchAll();
    }
    
    foreach ($cycles as $cycle) {
      if ($cycle['id'] == $_GET['idcycle'] || (!isset($_GET['idcycle']) && $cycle['state'] == '1')) {
        $selCycleId = $cycle['id'];
        $selCycleState = $cycle['state'];
      }
    }
    $sql = "SELECT * FROM tapplications WHERE idclient = :idclient ".($selCycleId != "" ? " AND idcycle = :idcycle" : "" )." ORDER BY id";
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':idclient', $_GET['idclient']);
    if ($selCycleId != "") {
      $stmt->bindValue(':idcycle', $selCycleId);
    }
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    if ($stmt->execute()) { 
      $appData = $stmt->fetch();
    }
  }
  else {
    $app_clientid = $_SESSION['halal']['id'];
    $sql = "SELECT * FROM tcycles WHERE idclient = :idclient ORDER BY id";
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':idclient', $app_clientid);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    if ($stmt->execute()) { 
      $cycles = $stmt->fetchAll();
    }
    
    foreach ($cycles as $cycle) {
      if ($cycle['id'] == $_GET['idcycle'] || (!isset($_GET['idcycle']) && $cycle['state'] == '1')) {
        $selCycleId = $cycle['id'];
        $selCycleState = $cycle['state'];
      }
    }
    
    $sql = "SELECT * FROM tapplications WHERE idclient = :idclient ".($selCycleId != "" ? " AND idcycle = :idcycle" : "" )." ORDER BY id";
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':idclient', $app_clientid);
    if ($selCycleId != "") {
      $stmt->bindValue(':idcycle', $selCycleId);
    }
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    if ($stmt->execute()) { 
      $appData = $stmt->fetch();
    }        
  }

  $disableControls = false;
  
  if ($selCycleState != '1' && $myuser->userdata['isclient']) {
    $disableControls = true;
  }

  /*
  $sql = "DELETE FROM tapplications WHERE  idclient=163";
  $stmt = $dbo->prepare($sql); 
  $stmt->execute();
  $sql = "DELETE FROM toffers WHERE  idclient=163";
  $stmt = $dbo->prepare($sql);   
  $stmt->execute();  
  $sql = "DELETE FROM tdocs WHERE  idclient=163";
  $stmt = $dbo->prepare($sql); 
  $stmt->execute();
  $sql = "SELECT * FROM tusers WHERE id=163";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute(); 
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
  print_r($user);   
  */
   
//  error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>

<link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.css" />
<link rel='stylesheet' id='fileup-css'  href='css/fileup.css?ver=6.0.1' type='text/css' media='all' />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel='stylesheet' id='fileup-css'  href='css/all.css?v=<?php echo rand(); ?>' type='text/css' media='all' />
<title>Applications and Documents - Halal Digital</title>
<style>
  #last_login_sent {
  display:block;
  color:blue;
  padding-right: 15px;
}

  .blockUI.blockOverlay {
    z-index: 99998 !important;
  }
  .blockUI.blockMsg.blockPage {
    z-index: 99999 !important;

	border: none !important; 
	padding: 5px !important;
	background-color: #000 !important; 
	-webkit-border-radius: 10px;
	-moz-border-radius': 10px; 
	opacity: .5; 
	color: #fff;
	text-align:center;
  max-width:300px;
}
div.blockUI.blockMsg.blockPage > h1 {
  color:#fff;
  font-size-: 18px;
}
.nav-pills i {
}
.nav-pills i.fa {
	margin-left:5px;	
}
.nav-pills i.fa-check {
	color:#2ECC71;
	font-size:18px;
}
.nav-pills li.locked a {
	cursor:not-allowed;
}
.nav-pills.nav-justified>li {
    vertical-align: bottom;
}
.nav-pills li  a {
height:50px;
line-height:30px;
white-space:nowrap;
color:#000;
}
.nav-pills li  a.multiline {
	
  /*line-height:normal;*/
 }
.nav-app li.active  a {
	font-weight:bold;
  background-color:#3B82F6;
  color:#fff;
}

.nav-app li.active i {

  color:#fff !important;
}

.nav-app li.locked {
  background: #fff;
}
.nav-app li.locked a,
.nav-app li.locked i {
color:#9ca3af;

}

.fileup-upload {
	display: none !important;
}
  /* Ensure vertical layout */
  .nav-pills {
      width: 100%;
      padding:0px;
      margin:0px;
  }
  .nav-pills > li {
      float: none;
  }
  .tab-content {
      padding: 0px;
      border: 0px solid #ddd;
      min-height: 200px;
  }    

  h2 {
    font-size:24px;
    font-weight: 600;
    margin-left:6px; 
  }

    /* Completed Step */
    .nav-pills > li.completed a {
            color: green;
            font-weight: bold;
        }
        .nav-pills > li.completed a .fa {
            color: green;
        }

        /* Current Step */
        .nav-pills > li.active a {
            color: #3B82F6 ;
            font-weight: bold;
        }
        .nav-pills > li.active a .fa {
            color: #3B82F6;
        }

        /* Locked Step */
        .nav-pills > li.locked a {
            color: gray;
            pointer-events: none; /* Disable clicking */
            cursor: not-allowed;
        }
        .nav-pills > li.locked a .fa {
            color: gray;
        }

.nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover {
    color: #fff;
    background-color: #337ab7;
}        

td[colspan] > div {
  width: 95% !important;
  margin-left: 5% !important;
  position: relative; /* To position the arrow inside */
  height: 20px; /* Adjust the height to fit the arrow */
}

/* L-Shaped Arrow */
td[colspan] > div:before {
    content: '';
    position: absolute;
    top: 10px;
    left: -50px;
    width: 40px;
    height: 40px;
    border-left: 2px solid #ccc;
    border-bottom: 2px solid #ccc;
}

<?php if ($disableControls): ?>
     [id^="btn-"],
    .btn-sign,
    .btn-measure,
    .alert {
      display: none !important;
    }
    #popinv #btn-upload,
    #pop #btn-upload,
    #popai #btn-upload {
      display: block !important;
    }
 <?php endif; ?>

 .fa.fa-dot-circle {
  color:#4a90e2  !important;
 }

 .rename-cycles-button {
    background-color: #3B82F6 !important; /* soft neutral grey */
    padding:7px 12px 6px;
    margin-left: 10px;
    color: white;
     border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s ease-in-out;
}

.rename-cycles-button:hover {
    background-color: #3B82F6 !important;
}

.nav-app {
  padding:0px;
  margin:0px;
}

.nav-app i {
  font-size: 22px;
  
}

.nav-app {
  border-radius: 6px;
}

.nav-app li {
  background: #eafaf1;
  color:#27ae60;
  margin:0px 0px 1px !important;
  
}

.nav-app li a {
    font-size: 16px;
    color: #27ae60;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 25px;
}

#renameCyclesModal .modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  #renameCyclesModal .modal-header .modal-title {
    margin-bottom: 0;
  }

  #renameCyclesModal .modal-header .close {
    margin: 0;
    padding: 0.5rem;
    line-height: 1;
  }

  #renameCyclesModal .modal-header .close::before,
  #renameCyclesModal .modal-header .close::after {
    display: none !important; /* neutralize pseudo-elements if they exist */
    content: none;
  }
  .tab-pane h3 {
    font-weight: 600;
    font-size: 21px;
  }

          .app-sidebar {
            width: 100%;
            background-color: #ffffff; /* White background */
            color: #333333; /* Dark text */            
            padding: 0px 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 0 20px 15px;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 10px;
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 1.3rem;
            color: #333;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: inherit;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }

        /* DEFAULT (Inactive) */
        .sidebar-menu li a {
            color: #666;
        }

        /* COMPLETED (Circle checkmark) */
        .sidebar-menu li.completed a {
            color: #333;
            background-color: rgba(76, 175, 80, 0.1);
            position: relative;
        }
        .sidebar-menu li.completed a::before {
            content: "";
            display: inline-block;
            width: 18px;
            height: 18px;
            margin-right: 10px;
            background-color: #4CAF50;
            border-radius: 50%;
            position: relative;
        }
        .sidebar-menu li.completed a::after {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 10px;
            color: white;
            position: absolute;
            left: 24px;
            top: 51%;
            transform: translateY(-50%);
        }

          /* ACTIVE STATE (When tab is clicked/selected) */
        .sidebar-menu li.active a {
            background-color: rgba(67, 160, 71, 0.2);
            border-left: 4px solid #43a047;
            color: #333;
            font-weight: bold;
        }
        

        /* CURRENT (Blue with arrow) */
        .sidebar-menu li.current a {
            background-color: rgba(76, 201, 240, 0.1); /* Light blue */
            border-left: 4px solid #4cc9f0;
            color: #333;
            font-weight: bold;
        }
        .sidebar-menu li.current a::before {
            content: "\f061"; /* FontAwesome arrow */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 10px;
            color: #4cc9f0; /* Blue */
        }

        /* LOCKED (Gray with padlock) */
        .sidebar-menu li.locked a {
            color: #999;
            cursor: not-allowed;
        }
        .sidebar-menu li.locked a::before {
            content: "\f023"; /* FontAwesome padlock */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 10px;
            color: #999;
        }

        /* Hover effects (excluding locked items) */
        .sidebar-menu li:not(.locked) a:hover {
            background-color: #f0f0f0;
            cursor: pointer;
        }
        
        .sidebar-menu li i {
          display: none !important;
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
          <div class="col-xs-12" style="">
          <div class="widget-box widget-border" style="margin-top:10px;">
                        <div class="widget-body">
                            <div class="widget-main">

            <?php if ($isClient && !$hasFacilities): ?>              
              <!--<input type="hidden" id="app-cycleid" value=<?php echo $selCycleId; ?> />-->
              <input type="hidden" id="app-clientid" value=<?php echo $_SESSION['halal']['id']; ?> data-clientname="<?php echo $myuser->userdata['name']," - ",$myuser->userdata['prefix'],$myuser->userdata['id'],""; ?>"/>
            <?php endif;?>

            <?php if (!$isClient || $hasFacilities): ?>
              
            <div class="form-inline" style="display:inline !important;">
              <div class="form-group">
                <label><?php if ($isClient): ?> Facilities <?php else: ?> Clients <?php endif; ?> &nbsp;&nbsp;
                  <select class="form-control clientslist" id="app-clientid">
                  <?php if (!$isClient): ?>
                    <option value="">Select <?php if ($isClient): ?> Facility <?php else: ?> Client <?php endif; ?></option>
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
            <?php //if (!$isClient || $hasFacilities): ?>
              <div class="form-inline"  style="display:inline !important;">
               <div class="form-group">
                <label>&nbsp;&nbsp;Cycles&nbsp;&nbsp;
                <select class="form-control cycleslist" id="app-cycleid">
      `              <option value="">Select Certification Cycle</option>
                    <?php foreach ($cycles as $cycle): ?>
                        <option value="<?php echo $cycle['id']; ?>" <?php if ($cycle['id'] == $_GET['idcycle'] || (!isset($_GET['idcycle']) && $cycle['state'] == '1')): ?>selected<?php endif; ?>>
                            <?php echo $cycle['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if ($isSuperAdmin): ?>
                <!-- Button to trigger modal -->
 
 

<!-- Modal -->
<div class="modal fade" id="renameCyclesModal" tabindex="-1" role="dialog" aria-labelledby="renameCyclesModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header align-items-center" >
        <h5 class="modal-title mb-0" id="renameCyclesModalLabel">Rename Cycles</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin: 0;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="renameCyclesForm">
          <input type="hidden" name="idclient" value="<?php echo $_GET["idclient"]; ?>" />
          <?php 
          $num = 0;
          foreach ($cycles as $cycle): $num++; ?>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
              <label for="cycle_<?php echo $cycle['id']; ?>" style="white-space: nowrap; min-width: 100px;">Cycle <?php echo $num; ?></label>
              <input type="text" class="form-control cycle-input" id="cycle_<?php echo $cycle['id']; ?>" name="cycle[<?php echo $cycle['id']; ?>]" value="<?php echo $cycle['name']; ?>" style="flex: 1; width: 100%;" />
            </div>
          <?php endforeach; ?>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveCyclesBtn">Save changes</button>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>
                </label>
               </div>
              </div>
              </div>
              </div>
              </div>
              <?php //endif; ?>

          </div>
          <div class="col-xs-12">
        
            <?php if (!$isClient): ?>
              <div id="selectCycle" class="alert alert-info" style="font-size:18px; margin-top:15px; display:none;"><p>No certification cycles have been created yet. Please click <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#certificationModal" style="border:none !important;">here</a> to create a new one.</p></div>
              <?php endif; ?>
              <?php if (!$isClient): ?>
              <div id="selectClient" class="alert alert-warning" style="font-size:18px; margin-top:15px; display:none;">Please select a client from the dropdown above.</div>
            <?php endif; ?>

            <div id="appMain">
              
            <!-- PAGE CONTENT BEGINS -->
            <input type="hidden" name="idapp" id="idapp" value="<?php echo $appData ? $appData["id"] : ""; ?>" />
            <input type="hidden" name="appstate" id="appstate" value="" />
            <div class="row" style="margin-top:20px;">
            <div class="col-md-3">
           
            </div>
          </div>
          <div class="col-md-12">         
                 <?php include('partials/all_additional_items.php');?> 
                <p></p>
              
            
              </div>
			</div> 
              </div>
              </div>
              </div>           
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
<!-- Modal -->
 
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
 
<script src="js/partials/all_additional_items.js?v=<?php echo rand(); ?>"></script>
 <script>
$(document).ready(function () {
    $("#app-clientid").on("change", function() {
      window.location.href='/additional_items_applications?idclient='+$("#app-clientid").val();
    });
});
</script>

<!-- Menu Toggle Script --> 
</body>
</html>