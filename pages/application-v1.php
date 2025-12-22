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
  $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND deleted = 0  ORDER BY name";
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

  $selCycleId = "";
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
<title>Applications and Documents - Halal e-Zone</title>
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
  font-size: 18px;
}
.nav-tabs i {
}
.nav-tabs i.fa-check {
	color:#090;
	font-size:18px;
}
.nav-tabs li.locked a {
	cursor:not-allowed;
}
.nav-tabs.nav-justified>li {
    vertical-align: bottom;
}
.nav-tabs li  a {
height:50px;
line-height:30px;
white-space:nowrap;
}
.nav-tabs li  a.multiline {
	line-height:normal;
 }
.nav-tabs li.active  a {
	font-weight:bold;
}
.fileup-upload {
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
          <div class="col-xs-12" style="padding-top:25px;">
            <?php if ($isClient && !$hasFacilities): ?>              
              <input type="hidden" id="app-cycleid" value=<?php echo $selCycleId; ?> />
              <input type="hidden" id="app-clientid" value=<?php echo $_SESSION['halal']['id']; ?> data-clientname="<?php echo $myuser->userdata['name']," - ",$myuser->userdata['prefix'],$myuser->userdata['id'],""; ?>"/>
            <?php endif;?>

            <?php if (!$isClient || $hasFacilities): ?>
            <div class="form-inline">
              <div class="form-group">
                <label><?php if ($isClient): ?> Facilities <?php else: ?> Clients <?php endif; ?> &nbsp;&nbsp;
                  <select class="form-control clientslist" id="app-clientid">
                  <?php if (!$isClient): ?>
                    <option value="-1">Select <?php if ($isClient): ?> Facility <?php else: ?> Client <?php endif; ?></option>
                  <?php endif; ?>
                  <?php
                    foreach ($clients as $client) {
                      ?>
                        <option value="<?php echo $client["id"]; ?>" <?php if ($client["id"] == $_GET["idclient"] || $client["id"] == $myuser->userdata['id']):?>selected<?php endif; ?> data-clientname="<?php echo $client['name']," (",$client['prefix'],$client['id'],")"; ?>" ><?php echo $client["name"]; ?> - <?php echo $client["prefix"]; ?><?php echo $client["id"]; ?></option>
                      <?php
                    }
                  ?>
                  </select>
                </label>
              </div>
            </div>
            <?php endif;?>
          </div>
          <div class="col-xs-12">
            <?php if (!$isClient): ?>
              <div id="selectCycle" class="alert alert-info" style="font-size:18px; margin-top:15px; display:none;"><p>No certification cycles have been created yet. Please click <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#certificationModal" style="border:none !important;">here</a> to create a new one.</p></div>
              <?php endif; ?>
              <?php if (!$isClient): ?>
              <div id="selectClient" class="alert alert-warning" style="font-size:18px; margin-top:15px; display:none;">Please select a client from the dropdown above.</div>
            <?php endif; ?>

            <div id="appMain" <?php if(!$isClient || $hasFacilities):?>style="display:none;"<?php endif; ?>>
              <?php if (!$isClient || $hasFacilities): ?>
              <div class="form-inline" style="margin-top:15px">
               <div class="form-group">
                <label>Cycles&nbsp;&nbsp;
                <select class="form-control cycleslist" id="app-cycleid" style="min-width:488px;">
      `              <option value="-1">Select Certification Cycle</option>
                    <?php foreach ($cycles as $cycle): ?>
                        <option value="<?php echo $cycle['id']; ?>" <?php if ($cycle['id'] == $_GET['idcycle'] || (!isset($_GET['idcycle']) && $cycle['state'] == '1')): ?>selected<?php endif; ?>>
                            <?php echo $cycle['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </label>
               </div>
              </div>
              <?php endif; ?>
            <!-- PAGE CONTENT BEGINS -->
            <input type="hidden" name="idapp" id="idapp" value="<?php echo $appData ? $appData["id"] : ""; ?>" />
            <input type="hidden" name="appstate" id="appstate" value="" />
            <ul class="nav nav-tabs nav-justified nav-app" style="margin-top:10px;overflow:scroll;">
              <?php if (!$isAuditor): ?>
                <?php if ($isClient || $myuser->userdata['superadmin'] == "1"): ?>
                <li class="tab_offer"><a data-toggle="tab" href="#offer"><i class="fa"></i> Offer</a></li>
                <li class="tab_soffer"><a data-toggle="tab" href="#soffer"><i class="fa"></i> Signed Offer</a></li>
                <?php endif; ?>
                <li class="tab_app"><a data-toggle="tab" href="#app"><i class="fa"></i> Application</a></li>
              <?php endif; ?>
              <li class="tab_declarations"><a data-toggle="tab" href="#declarations" class="multiline"><i class="fa"></i> Client Questionnaire /<br/>
 Free Form Declarations</a></li>
              <li class="tab_dates"><a data-toggle="tab" href="#dates"><i class="fa"></i> Audit Dates</a></li>
              <li class="tab_audit"><a data-toggle="tab" href="#audit"><i class="fa"></i> Audit Plan</a></li>
              <?php if ($myuser->userdata['isclient'] != "1"): ?>
              <li class="tab_checklist"><a data-toggle="tab" href="#checklist"><i class="fa"></i> Checklist</a></li>
              <?php endif;?>
              <li class="tab_report"><a data-toggle="tab" id="treport" class="multiline" href="#report"><i class="fa"></i> Audit Report / <br/>
Corrective Actions</a></li>
<?php if ($myuser->userdata['isclient'] != "1"): ?>
<li class="tab_review"><a data-toggle="tab" id="treview" class="multiline" href="#review"><i class="fa"></i> Audit Review Report</a></li>
<li class="tab_dm"><a data-toggle="tab" id="tdm" class="multiline" href="#dm"><i class="fa"></i> Decision Making</a></li>
<?php endif; ?>
<?php if ($myuser->userdata['isclient'] != "2"): ?>
<li class="tab_pop"><a data-toggle="tab" id="tpop" class="multiline" href="#pop"><i class="fa"></i> Proof of Payment</a></li>
<?php endif; ?>
              <li class="tab_certificate"><a data-toggle="tab" href="#certificate"><i class="fa"></i> Certificate</a></li>

              <li class="tab_additional_items"><a data-toggle="tab" href="#additional_items" class="multiline"><i class="fa"></i> Additional Items<br/> Application</a></li>

              <?php if ($myuser->userdata['isclient'] != "2"): ?>
                <li class="tab_popai"><a data-toggle="tab" id="tpopai" class="multiline" href="#popai"><i class="fa"></i> Proof of Payment</a></li>
              <?php endif; ?>

              <li class="tab_extension"><a data-toggle="tab" href="#extension" class="multiline"><i class="fa"></i> Certificate Extension</a></li>
            </ul>
            <div class="tab-content">
              <div id="app" class="tab-pane fade in active">
                <?php include('partials/app.php');?>
              </div>
              <div id="offer" class="tab-pane fade">
                <?php include('partials/offer.php');?>
              </div>
              <div id="soffer" class="tab-pane fade">
                <?php include('partials/soffer.php');?> 
                <p></p>
              </div>
              <div id="declarations" class="tab-pane fade">
                <?php include('partials/declarations.php');?> 
                <p></p>
              </div>
              <div id="dates" class="tab-pane fade">
                <?php include('partials/dates.php');?> 
              </div>
              <div id="audit" class="tab-pane fade">
                <?php include('partials/audit.php');?> 
                <p></p>
              </div>
              <div id="checklist" class="tab-pane fade">
                <?php include('partials/checklist.php');?> 
                <p></p>
              </div>
              <div id="report" class="tab-pane fade">
                <?php include('partials/report.php');?> 
                <p></p>
              </div>
              <?php if ($myuser->userdata['isclient'] != "1"): ?>
              <div id="review" class="tab-pane fade">
                <?php include('partials/review.php');?> 
                <p></p>
              </div>
              <div id="dm" class="tab-pane fade">
                <?php include('partials/dm.php');?> 
                <p></p>
              </div>
              <?php endif; ?>
              <div id="pop" class="tab-pane fade">
                <?php include('partials/pop.php');?> 
                <p></p>
              </div>                            
              <div id="certificate" class="tab-pane fade">
                <?php include('partials/certificate.php');?> 
                <p></p>
              </div>
              <div id="popai" class="tab-pane fade">
                <?php include('partials/popai.php');?> 
                <p></p>
              </div>              
              <div id="additional_items" class="tab-pane fade">
                <?php include('partials/additional_items.php');?> 
                <p></p>
              </div>
              <div id="extension" class="tab-pane fade">
                <?php include('partials/extension.php');?> 
                <p></p>
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
<div class="modal fade" id="certificationModal" tabindex="-1" role="dialog" aria-labelledby="certificationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="certificationModalLabel">Create Certification Cycle</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="errors"></div>
        <form>
          <div class="form-group row">
            <label for="client" class="col-md-4 col-form-label">Client:</label>
            <div class="col-md-8">
              <div id="selClientName"></div>
            </div>
          </div>
          <div class="form-group row">
            <label for="cycleName" class="col-md-4 col-form-label">Cycle Name:</label>
            <div class="col-md-8">
              <input type="text" class="form-control" id="cycleName" name="cycleName" placeholder="Enter cycle name">
            </div>
          </div>
          <!--
          <div class="form-group row">
            <label for="certStartDate" class="col-md-4 col-form-label">Start Date:</label>
            <div class="col-md-8">
              <input type="text" class="form-control" id="certStartDate" name="certStartDate">
            </div>
          </div>
          <div class="form-group row">
            <label for="certEndDate" class="col-md-4 col-form-label">End Date:</label>
            <div class="col-md-8">
              <input type="text" class="form-control" id="certEndDate" name="certEndDate">
            </div>
          </div>
          -->
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary btn-save-cc">Save</button>
      </div>
    </div>
  </div>
</div
<!-- Application Modal -->
<div class="modal fade" id="appModal" tabindex="-1" role="dialog"  data-backdrop="static" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
        <h4 class="modal-title" id="appModal-cycle"></h4>
      </div>
      <div class="modal-body row">
        <from id="app-form" class="col-sm-12 form-horizontal">
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4">Initial application</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone1">Drop files here or click to upload
              <input class="fileupload" id="fileupload1" foldertype="app" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulapp">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4">Offer</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone2">Drop files here or click to upload
              <input class="fileupload" id="fileupload2" foldertype="offer" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="uloffer">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4 text-bold">Signed offer</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone3">Drop files here or click to upload
              <input class="fileupload" id="fileupload3" foldertype="soffer" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulsoffer">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4">Audit plan</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone4">Drop files here or click to upload
              <input class="fileupload" id="fileupload4" foldertype="plan" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulplan">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-md-4">Auditor ID/name</label>
            <div class='col-xs-10 col-md-6'>
              <input type="text" class="form-control" id="auditorname" maxlength="100"/>
              <div class="alert-string"></div>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4">Check list</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone5">Drop files here or click to upload
              <input class="fileupload" id="fileupload5" foldertype="checklist" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulchecklist">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4">Audit report</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone6">Drop files here or click to upload
              <input class="fileupload" id="fileupload6" foldertype="report" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulreport">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4 text-bold">Corrective action</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone7">Drop files here or click to upload
              <input class="fileupload" id="fileupload7" foldertype="action" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulaction">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4 text-bold">List of products</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone8">Drop files here or click to upload
              <input class="fileupload" id="fileupload8" foldertype="list" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ullist">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4 text-bold">Proof of payment</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone13">Drop files here or click to upload
              <input class="fileupload" id="fileupload13" foldertype="payment" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulpayment">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4">Certificate</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone9">Drop files here or click to upload
              <input class="fileupload" id="fileupload9" foldertype="cert" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulcert">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-md-4">Issue date</label>
            <div class='col-xs-10 col-md-6'>
              <input type="text" class="form-control datepicker" id="issuedate"/>
              <div class="alert-string"></div>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4 text-bold">New applications</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone10">Drop files here or click to upload
              <input class="fileupload" id="fileupload10" foldertype="newapp" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulnewapp">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4">New certificates</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone11">Drop files here or click to upload
              <input class="fileupload" id="fileupload11" foldertype="newcert" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulnewcert">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4">Halal training</label>
            <div class='col-xs-12 col-sm-8'> <span class="fileinput-button" id="dropzone12">Drop files here or click to upload
              <input class="fileupload" id="fileupload12" foldertype="halaltraining" type="file" name="files[]" multiple>
              </span><span class="loader"></span>
              <ul id="ulhalaltraining">
              </ul>
            </div>
          </div>
          <div class="row form-group">
            <label class="col-xs-12 col-sm-4 text-bold"></label>
            <div class='col-xs-12 col-sm-8'>
              <div id="cycleswitch"><span class="align-tbottom">Cycle compeleted&nbsp;&nbsp;</span>
                <label>
                  <input id="cycleconf" class="ace ace-switch ace-switch-4" type="checkbox" value="0">
                  <span class="lbl"></span> </label>
              </div>
            </div>
          </div>
        </from>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="APP.onSave();" >Save changes</button>
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
<script src="js/partials/app.js?v=<?php echo rand(); ?>"></script> 
<script src="js/partials/offer.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/soffer.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/dates.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/audit.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/checklist.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/declarations.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/report.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/review.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/dm.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/pop.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/certificate.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/popai.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/additional_items.js?v=<?php echo rand(); ?>"></script>
<script src="js/partials/extension.js?v=<?php echo rand(); ?>"></script>
<script>
	var datesForDisable = {};
	var userId = <?php echo $_SESSION['halal']['id'] ?>;
	var approvedText = ' <span class="text-danger" style="font-size:16px;"><i class="fa fa-check-circle"></i> The date <strong>[approvedDate]</strong> is confirmed for audit.</span>';						
  Common.onDocumentReady();
	$(document).ready(function(e) {
 

    $(document).on('keyup', '#certificationModal input', function() {
    // Clear error messages
      $('#certificationModal .errors').empty();
    });
    $('.btn-save-cc').click(function() {
      var doc = {};
		  doc.idclient = $("#app-clientid").val();
      doc.cycleName= $("#certificationModal #cycleName").val();      
      doc.startDate = $("#certificationModal #certStartDate").val();
      doc.endDate = $("#certificationModal #certEndDate").val();
		  $.ajax({
			  type: 'POST',
			  url: "ajax/ajaxHandler.php",
			  data: { rtype: "saveCertCycle", uid: 0, data: doc},
			  dataType:"json",
        beforeSend: function(xhr) {
          $.blockUI();
        },
        complete: function(xhr, status) {
          $.unblockUI();
        },        
			  success: function (response) { 
          if (response.status == '0') { 
            var errors = response.statusDescription;
            var html = "";
            $.each(errors, function(index, error) {
              html += error+'<br/>';
            });

            $('#certificationModal .errors').show().html('<div class="alert alert-danger">'+html+'</div>')
            $.unblockUI();
          }
          else {
            $('#certificationModal').modal('hide');
            window.location.reload();
          }
      	}
      });
      return false;
    });

    $('#certificationModal').on('show.bs.modal', function (event) {
      // Get the client name from the desired input field
      var clientName = $("#app-clientid option:selected").text();
      // Set the value of the "Client" field in the modal form
      $("#selClientName").html(clientName);
      $('#certificationModal .errors').empty();
    });

    $('#certStartDate, #certEndDate').datepicker({
      format: 'dd/mm/yyyy',
      autoclose: true
    });

		Common.setMainMenuItem("appItem");

		//Common.loadClientsData(Common.populateClients);
   
    //var defaultAuditPlanSettings = FormHelper.parseForm("#frmAuditPlanSettings");
    //var defaultAuditReportSettings = FormHelper.parseForm("#frmAuditReportSettings");
    
		$(document).on("click", ".btn-complete", function() {
			if (confirm("Are you sure you want to mark this step as complete?")) {
        changeAppState($(this).data('state'), 0)
			}
			return false;
		});

    $(document).on("click", ".btn-skip", function() {
			if (confirm("Are you sure you want to skip this step?")) {
        changeAppState($(this).data('state'), 1)
			}
			return false;
		});
		
		 <?php // if($myuser->userdata['isclient'] != "1"):?>
			$("#app-clientid").on("change", function() {
        window.location.href='/application?idclient='+$("#app-clientid").val();
        /*
				if ($(this).val()==-1) {
					$("#appMain").hide();
					$("#selectClient").show();
					
				}
				else {
					$("#appMain").show();
					$("#selectClient").hide();
					init();
					//$('a[data-toggle="tab"]').parent().removeClass('active');
			        //$('.tab-pane.active').removeClass('active');					
					//$('a[href="#app"]').tab('show');					
				}
				return false;
        */
			});
      $("#app-cycleid").on("change", function() {
        window.location.href='/application?idclient='+$("#app-clientid").val()+'&idcycle='+$("#app-cycleid").val();
         /*
				if ($(this).val()==-1) {
					$("#appMain").hide();
					$("#selectClient").show();
					
				}
				else {
					$("#appMain").show();
					$("#selectClient").hide();
					init();
					//$('a[data-toggle="tab"]').parent().removeClass('active');
			        //$('.tab-pane.active').removeClass('active');					
					//$('a[href="#app"]').tab('show');					
				}
				return false;
        */
			});
		<?php // else: ?>
			//$("#app #errors").html("").hide();			
			//var table_app = $('#table_app').DataTable(); 
			//table_app.ajax.reload( null, false );
		<?php // endif; ?>

		$('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
			if ($(e.target).find(".fa-lock").length) {
				return false;
			}
			var target = $(e.target).attr("href") // activated tab
      showTab(target);
    });
    
    init();
  });

  function showTab(target) {
		if (target == "#app") {
				$("#app #errors").html("").hide();			
				var table_app = $('#table_app').DataTable(); 
				table_app.ajax.reload( null, false );
			}
			else if (target == "#offer") {
				$("#offer #errors").html("").hide();						  
				getServices();
				var table_service = $('#table_service').DataTable(); 
				table_service.ajax.reload( null, false );
				var table_offer = $('#table_offer').DataTable();
				table_offer.ajax.reload( null, false );
			}
			else if (target == "#soffer") {
				$("#soffer #errors").html("").hide();			
				var table_soffer = $('#table_soffer').DataTable(); 
				table_soffer.ajax.reload( null, false );
			}
			else if (target == "#declarations") {
				$("#declarations #errors").html("").hide();			
				var table_declarations = $('#table_declarations').DataTable(); 
				table_declarations.ajax.reload( null, false );
			}
			else if (target == "#dates") {
				getDisabledDates();
				$("#dates #errors").html("").hide();
				//$("#AuditDate1").data("DateTimePicker").disabledDates(datesForDisable);
				//$("#AuditDate2").data("DateTimePicker").disabledDates(datesForDisable);
				//$("#AuditDate3").data("DateTimePicker").disabledDates(datesForDisable);
			}
			else if (target == "#audit") {
				$("#audit #errors").html("").hide();			
				var table_audit = $('#table_audit').DataTable(); 
				table_audit.ajax.reload( null, false );
        $("#audit #btnsave-settings").click();
			}
			else if (target == "#checklist") {
				$("#checklist #errors").html("").hide();			
				var table_checklist = $('#table_checklist').DataTable(); 
				table_checklist.ajax.reload( null, false );
			}
			else if (target == "#report") {
				getDeviations();
				$("#report #errors").html("").hide();			
				var table_report = $('#table_report').DataTable(); 
				table_report.ajax.reload( null, false );
        $("#report #btnsave-settings").click();
			}
      else if (target == "#review") {
				$("#review #errors").html("").hide();			
				var table_review = $('#table_review').DataTable(); 
				table_review.ajax.reload( null, false );
        $("#review #btnsave-settings").click();
			}
      else if (target == "#dm") {
				$("#dm #errors").html("").hide();			
				var table_dm = $('#table_dm').DataTable(); 
				table_dm.ajax.reload( null, false );
        $("#dm #btnsave-settings").click();
			}
      else if (target == "#pop") {
      	$("#pop #errors").html("").hide();			
				var table_pop = $('#table_pop').DataTable(); 
				table_pop.ajax.reload( null, false );
        $("#pop #btnsave-settings").click();
			}
			else if (target == "#certificate") { 
				$("#certificate #errors").html("").hide();			
				var table_certificate = $('#table_certificate').DataTable(); 
				table_certificate.ajax.reload( null, false );
			}
			else if (target == "#additional_items") { 
				$("#additional_items #errors").html("").hide();			
				var table_additional_items = $('#table_additional_items').DataTable(); 
				table_additional_items.ajax.reload( null, false );
			}
      else if (target == "#popai") {
      	$("#popai #errors").html("").hide();			
				var table_popai = $('#table_popai').DataTable(); 
				table_popai.ajax.reload( null, false );
        $("#popai #btnsave-settings").click();
			}
			else if (target == "#extension") {  
				$("#extension #errors").html("").hide();			
				var table_extension = $('#table_extension').DataTable(); 
				table_extension.ajax.reload( null, false );
			}
  }

  function updateAppState() {
		var state = $("#appstate").val()
		if (state == "") { 
			state = "app";
		}
		$(".nav-app li").removeClass("locked");
		$(".nav-app li i").removeClass("fa-lock");		
		$(".nav-app li i").removeClass("fa-check");		
		$(".tab-content .btn-complete").show();
		$(".tab-content .btn-skip").show();    
		var stateFound =false;
		$(".nav-app li").each(function() { 
			var id = $(this).find('a[data-toggle=tab]').attr('href'); 
			if ($(this).hasClass('tab_'+state)) { 
				 stateFound = true;
			}
			else { 
				if ( stateFound ) { 
					$(this).addClass("locked");
					$(this).find("i").removeClass("fa-check");
					$(this).find("i").addClass("fa-lock");
					$(id).find(".btn-complete").show();					
					$(id).find(".btn-skip").show();									
				}
				else {
					$(this).removeClass("locked");					
					$(this).find("i").removeClass("fa-lock");
					$(this).find("i").addClass("fa-check");
					$(id).find(".btn-complete").hide();									
					$(id).find(".btn-skip").hide();									
				}
			}
		});

		/*
		for (i=1;i<state;i++) {
			//$('#tab_'+i).removeClass("active");
			$('#tab_'+i).find('a').prepend('<i class="fa"></i> ');
		}
		*/
		
    $('a[data-toggle="tab"]').parent().removeClass('active');
		$('.tab-pane.active').removeClass('active');
		$('a[href="#'+state+'"]').tab('show');
    //if (state == 'audit') {
    //  $("#btnsave-settings").click();
    //}
    //showTab("#"+state);
	}

  function init() { 
    if ($("#app-clientid").val() == -1) {
      $("#appMain").hide();
      $("#selectClient").show();
      $("#selectCycle").hide();
    }
    else {
      $("#selectClient").hide();
      if ($("#app-cycleid").val() == -1) {
        $("#selectCycle").show();
        $("#appMain").hide();

      }
      else  { 
        $("#selectCycle").hide();
        $("#appMain").show();
      }
    }

    var doc = {};
      doc.idclient = $("#app-clientid").val();
      doc.idcycle = $("#app-cycleid").val();
      doc._nounce = '<?php echo rand(); ?>';
		  $.ajax({
			  type: 'POST',
			  url: "ajax/ajaxHandler.php",
			  data: { rtype: "getAppData", uid: 0, data: doc},
			  async:false,
			  dataType:"json",
			  success: function (response) {
				 console.log(response);
				  if (response.status == 0) {
					  return;
				  }
				  var clientName = response.data.clientData.name; 
          var clientCountry = response.data.clientData.country; 
          var clientAddress = response.data.clientData.address;
          var ingredientsLimit = response.data.clientData.ingrednumber;  
          var productsLimit = response.data.clientData.prodnumber;  
          var offerOffice = response.data.appData.offerOffice; 
          var auditDate1 = response.data.appData.audit_date_1;
				  var auditDate2 = response.data.appData.audit_date_2;
				  var auditDate3 = response.data.appData.audit_date_3;
				  var approvedDate1 = response.data.appData.approved_date1f;
          var approvedDate1F = response.data.appData.approved_date1f;
				  var approvedBy = response.data.appData.approved_by;
          var countryOfCompany = response.data.appData.countryOfCompany;
          if (approvedDate1 == '0000-00-00') {
            approvedDate1 = "";
          }
          if (approvedDate1F == '00/00/0000') {
            approvedDate1F = "";
          }
          if (auditDate1 == '00/00/0000') {
            auditDate1 = "";
          }
          if (auditDate2 == '00/00/0000') {
            auditDate2 = "";
          }
          if (auditDate3 == '00/00/0000') {
            auditDate3 = "";
          }
          if (countryOfCompany == "") {
            countryOfCompany = clientCountry;
          }
          var addresses = response.data.appData.addresses;
          if (addresses == "") {
            addresses = clientAddress;
          }
          var companyId = response.data.appData.companyId;
          var reference = response.data.appData.reference;
          var LeadAuditor = response.data.appData.LeadAuditor;
          var coAuditor = response.data.appData.coAuditor;
          var IslamicAffairsExpert = response.data.appData.IslamicAffairsExpert;
          var Veterinary = response.data.appData.Veterinary;          
          var auditPlanSettings = response.data.appData.audit_plan_settings;
				  var auditReportSettings = response.data.appData.audit_report_settings;
				  var lastReportSent = response.data.appData.last_report_sent; 
          var certificateNumber = response.data.appData.CertificateNumber;
				  var certificateIssueDate = response.data.appData.CertificateIssueDate;
				  var certificateExpiryDate = response.data.appData.CertificateExpiryDate;
          if (offerOffice) {
            $("#offerOffice").val(offerOffice);
          }
          if (ingredientsLimit) {
            $("#ingredientsLimit").val(ingredientsLimit);
          }
          if (productsLimit) {
            $("#productsLimit").val(productsLimit);
          }
          if (lastReportSent) {
            $("#last_report_sent").html("Last Sent: "+lastReportSent);
          }
          if (certificateIssueDate) {
            var d = new Date(certificateIssueDate),
              month = '' + (d.getMonth() + 1),
              day = '' + d.getDate(),
              year = d.getFullYear();
            if (month.length < 2) 
                month = '0' + month;
            if (day.length < 2) 
                day = '0' + day;
              certificateIssueDate = day+"/"+month+"/"+year;            
          }

          if (certificateExpiryDate) {
            var d = new Date(certificateExpiryDate),
              month = '' + (d.getMonth() + 1),
              day = '' + d.getDate(),
              year = d.getFullYear();

            if (month.length < 2) 
                month = '0' + month;
            if (day.length < 2) 
                day = '0' + day;

                certificateExpiryDate = day+"/"+month+"/"+year;            
          }

          $("#certificateNumber").val(certificateNumber);
				  $("#certificateIssueDate").val(certificateIssueDate);
				  $("#certificateExpiryDate").val(certificateExpiryDate);

          var lastLoginSent = response.data.clientData.last_login_sent;
          if (lastLoginSent) {
            $("#last_login_sent").html("Last Login Sent: " + lastLoginSent);
          }

          if (auditPlanSettings) { 
             $('#frmAuditPlanSettings input[type=checkbox]')
            .prop('checked', false);
            auditPlanSettings = JSON.parse(auditPlanSettings);
            Object.entries(auditPlanSettings).forEach((entry) => {
              const [key, value] = entry;
              if ($('#frmAuditPlanSettings #'+key) && $('#frmAuditPlanSettings #'+key).prop('type') == "checkbox") {
                if (value == "1") {
                  $('#frmAuditPlanSettings #'+key).prop("checked", true);
                }
                else {
                  $('#frmAuditPlanSettings #'+key).prop("checked", false);
                }
              }
              else if ($('#frmAuditPlanSettings input[name='+key+']') && $('#frmAuditPlanSettings input[name='+key+']').prop('type') == "radio") {
                if (value == "1") {
                  $('#frmAuditPlanSettings input[name='+key+'][value="'+value+'"]').prop("checked", true);
                }
              }            
              else {
                $('#frmAuditPlanSettings #'+key).val(value);
              }
            });
          }
            $("#frmAuditPlanSettings #countryOfCompany").val(countryOfCompany);
            $("#frmAuditPlanSettings #addresses").val(addresses);
            $("#frmAuditPlanSettings #companyId").val(companyId);
            $("#frmAuditPlanSettings #reference").val(reference);
            $("#frmAuditPlanSettings #LeadAuditor").val(LeadAuditor);
            $("#frmAuditPlanSettings #coAuditor").val(coAuditor);
            $("#frmAuditPlanSettings #IslamicAffairsExpert").val(IslamicAffairsExpert);
            $("#frmAuditPlanSettings #Veterinary").val(Veterinary);          
            if (auditReportSettings) { 
             $('#frmAuditReportSettings input[type=checkbox]')
            .prop('checked', false);
            auditReportSettings = JSON.parse(auditReportSettings);
            Object.entries(auditReportSettings).forEach((entry) => {
              const [key, value] = entry;
              if ($('#frmAuditReportSettings #'+key) && $('#frmAuditReportSettings #'+key).prop('type') == "checkbox") {
                if (value == "1") {
                  $('#frmAuditReportSettings #'+key).prop("checked", true);
                }
                else {
                  $('#frmAuditReportSettings #'+key).prop("checked", false);
                }
              }
              else if ($('#frmAuditReportSettings input[name='+key+']') && $('#frmAuditReportSettings input[name='+key+']').prop('type') == "radio") {
                if (value == "1") {
                  $('#frmAuditReportSettings input[name='+key+'][value="'+value+'"]').prop("checked", true);
                }
              }            
              else {
                $('#frmAuditReportSettings #'+key).val(value);
              }
            });
          }          
          $("#frmAuditReportSettings #countryOfCompany").val(countryOfCompany);
          $("#frmAuditReportSettings #addresses").val(addresses);
          $("#frmAuditReportSettings #companyId").val(companyId);
          $("#frmAuditReportSettings #reference").val(reference);
          $("#frmAuditReportSettings #LeadAuditor").val(LeadAuditor);
          $("#frmAuditReportSettings #coAuditor").val(coAuditor);
          $("#frmAuditReportSettings #IslamicAffairsExpert").val(IslamicAffairsExpert);
          $("#frmAuditReportSettings #Veterinary").val(Veterinary);          
				  $("#idapp").val(response.data.appData.id);
				  $("#appstate").val(response.data.appData.state);
				  // datepicker values				  
				  $("#AuditDate1").val(auditDate1);
				  $("#AuditDate2").val(auditDate2);
				  $("#AuditDate3").val(auditDate3);
          // radio button values
				  $("#ApprovedDate1").val(auditDate1);
				  $("#ApprovedDate2").val(auditDate2);
				  $("#ApprovedDate3").val(auditDate3);
          // audit plan
          $("#frmAuditPlanSettings #mainDate").val(approvedDate1F);		
          $("#frmAuditPlanSettings #mainCompany").val(clientName);
          //$("#frmAuditPlanSettings #countryOfCompany").val(clientCountry);		
          //$("#frmAuditPlanSettings #addresses").val(clientAddress);		                    
          // audit report
          $("#frmAuditReportSettings #mainDate").val(approvedDate1F);		
          $("#frmAuditReportSettings #mainCompany").val(clientName);		
         // $("#frmAuditReportSettings #countryOfCompany").val(clientCountry);		
         // $("#frmAuditReportSettings #addresses").val(clientAddress);		                    
				  // audit date approved
				  if (approvedBy) {
					  // hide datepickers
				  }
				  else {
					}
				  <?php if ($myuser->userdata['isclient'] == "1"): ?>
				  if (approvedDate1) {
					  $("#dates #AuditDate1").prop('disabled', true);
					  $("#dates #AuditDate2").prop('disabled', true);
					  $("#dates #AuditDate3").prop('disabled', true);
					  $("#dates #btn-submit").prop('disabled', true);
				  }
				  <?php endif; ?>
          var approvedText1 = "";
          if (approvedDate1 && approvedDate1 == auditDate1) {
					  $("#ApprovedDate1").prop('checked', true);
            approvedText1 = approvedText.replace('[approvedDate]', approvedDate1F);
					  $(".SelectedDate").append(approvedText1);
				  }
				  if (approvedDate1 && approvedDate1 == auditDate2) {
					  $("#ApprovedDate2").prop('checked', true);
            approvedText1 = approvedText.replace('[approvedDate]', approvedDate1F);
					  $(".SelectedDate").append(approvedText1)
				  }
				  if (approvedDate1 && approvedDate1 == auditDate3) {
					  $("#ApprovedDate3").prop('checked', true);
            approvedText1 = approvedText.replace('[approvedDate]', approvedDate1F);						  
					  $(".SelectedDate").append(approvedText1)
				  }
				  updateAppState();
				}
		  });
	}

  function getDisabledDates() {
        $.ajax({
          type: 'POST',
          url: "ajax/ajaxHandler.php",
          data: { rtype: "getDisabledDates", uid: 0},
          async:false,
          dataType:"json",
          success: function (response) {
            datesForDisable = response.data.disabledDates;
            console.log(datesForDisable)
          }
      });
	}

function getDeviations() {	
		$.ajax({
    type: "POST",
    url: "ajax/ajaxHandler.php",
    cache: false,
	dataType:"json",
	async:false,
    data: {
		  rtype: "getDeviations",
		  uid: 0,
    },
    success: function (results) {
		$(results.data.deviations).each(function (index, d) {
			var option = $("<option>", {
				value: d.deviation,
				text: d.deviation,
			});
			$("#Deviation").append(option);
		});
		$("#Deviation").trigger("chosen:updated");
	},
    error: function (jqXHR, status, message) {
      },
  });
}

function getServices() {	
		$.ajax({
    type: "POST",
    url: "ajax/ajaxHandler.php",
    cache: false,
	dataType:"json",
	async:false,
    data: {
		  rtype: "getServices",
		  uid: 0,
    },
    success: function (results) {
      $('#Service')
    .find('option')
    .remove()
    .end()
    .append('<option value=""></option><option value="addNewService">+ Add New</option>');
		$(results.data.services).each(function (index, s) {
			var option = $("<option>", {
				value: s.service,
				text: s.service,
			});
			$("#Service").append(option);
		});
		$("#Service").trigger("chosen:updated");
	},
    error: function (jqXHR, status, message) {
      },
  });
}

function changeAppState(state, skip) {
      var doc = {};
			doc.idclient = $("#app-clientid").val();
			doc.idapp = $("#idapp").val();
			doc.state = state;
			doc.skip = skip;
			$.ajax({
			  type: 'POST',
			  url: "ajax/ajaxHandler.php",
			  data: { rtype: "updateAppState", uid: 0, data: doc},
			//  async:false,
			  dataType:"json",
        beforeSend: function(xhr) {
          $.blockUI();
        },
        complete: function(xhr, status) {
          $.unblockUI();
        },
			  success: function (response) {
				  if (response.data.errors == "") {
            var separator = (window.location.href.indexOf('?') > -1) ? '&' : '?';
            var randomNumber = Math. floor(Math. random() * 100) + 1;
            window.location.href = window.location.href + separator + '_n='+randomNumber;
            return; 
					  $("#appstate").val(response.data.state);
					  updateAppState();
				  }
				  else {
					 $(".tab-content").notify(response.data.errors, { position:"top center", className: "error" });
				  }
			  }
			});
}
    //APP.onDocumentReady();
</script> 

<!-- Menu Toggle Script --> 
</body>
</html>