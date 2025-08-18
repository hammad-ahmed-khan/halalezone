<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php');?>
    <title>Process Status - Halal e-Zone</title>
    <style>

.tclientName {
    background: #4a90e2;
    color: white;
    padding: 6px 10px;
    border-radius: 4px;
    font-size: 14px !important;
    font-weight: bold;
    margin-left: 10px;
}

.no-gutters {
    margin-right: 0;
    margin-left: 0;
}        
.no-gutters > [class*='col-'] {
    padding-right: 0;
    padding-left: 0;
}        
    .rel {
		display:none;
	}
.chosen-container {
    min-width:100%;
}	
.bold {
    font-weight: bold;
}

.small {
    font-size: smaller;
    line-height: 1.5;
}
.flex-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.flex-item {
    text-align: center;
    margin: 0 10px;
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #ccc; /* Light border */
    width: 85px; /* Set a fixed width */
    color: white;
}

.handshake {
    color: #3498db; /* Blue */
}

.wrench {
    color: #34495e; /* Dark Gray */
}

.flask {
    color: #16a085; /* Dark Green */
}

.certificate {
    color: #e74c3c; /* Dark Red */
}

.infobox {
     padding: 1px;
} 

.infobox>.infobox-data>.infobox-data-number {
    font-size: 14px;
    margin: 0;
}

.infobox>.infobox-data {
    min-width: 37px;
    padding-left: 0px;
}
table.dataTable th,
table.dataTable td {
    font-size: 14px;

}
.no-wrap {
    white-space: nowrap; /* Prevents text from wrapping */
}
    
/* Apply background colors for even and odd rows */
.table-striped:not(#activityLogTable):not(#table_tasks):not(#table_tickets) tbody tr:nth-of-type(odd) td {
    background-color: #e8f5e0;
    background-color: #ffffff;
}

/* Even rows, excluding table_tasks and table_tickets */
.table-striped:not(#activityLogTable):not(#table_tasks):not(#table_tickets) tbody tr:nth-of-type(even) td {
    background-color: #b3d9f0;
    background-color: #ffffff;
}


.highlight-audit-date-approval td {
    background-color: #ffcccc !important; /* Light red */
}
.highlight-audit-plan td {
    background-color: #ffebcc !important; /* Light orange */
}
.highlight-signed-offer td {
    background-color: #ffffcc !important; /* Light yellow */
}
.highlight-certificate-expiring td {
    background-color: #f0e6ff !important; /* Light purple */
}
.highlight-awaiting-upload td {
    background-color: #d2b48c !important; /* Lime green */
}
.highlight-new-registration td {
    background-color: #00ffff !important; /* Aqua */
}

#legend {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-right: 15px;
}

.legend-color {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 5px;
    vertical-align: middle;
}

.legend-color.highlight-audit-date-approval {
    background-color: #ffcccc; /* Light red */
}
.legend-color.highlight-audit-plan {
    background-color: #ffebcc; /* Light orange */
}
.legend-color.highlight-signed-offer {
    background-color: #ffffcc; /* Light yellow */
}
.legend-color.highlight-certificate-expiring {
    background-color: #f0e6ff; /* Light purple */
}
.legend-color.highlight-client-activity {
    background-color: #cce5ff; /* Light blue */
}
.legend-color.highlight-awaiting-upload {
    background-color: #d2b48c; /* Lime green */
}
.legend-color.highlight-new-registration {
    background-color: #00ffff; /* Aqua */
}

.d-flex {
    display: flex;
}

.justify-content-between {
    justify-content: space-between;
}

.align-items-center {
    align-items: center;
}
.badge-red {
  background-color: #dc3545;  /* Bootstrap red */
  color: white;
  padding: 2px 6px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: bold;
  display: inline-block;
  min-width: 20px;
  text-align: center;
}

</style>
    
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.12/css/fixedHeader.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.2.2/css/fixedColumns.bootstrap.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.3.0/css/scroller.dataTables.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
      

</head>

<body>
<?php

    global $statusOptions, $country_list;
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Создаем объект подключения к БД
    $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND IFNULL(parent_id,'0') = '0' AND deleted = 0  ORDER BY name";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	if(!$stmt->execute()) {
		echo json_encode(generateErrorResponse("Getting clients list failed"));
		die();
	}
	$clients = $stmt->fetchAll();

    $sql = "SELECT id, name, email  FROM tusers WHERE isclient=2 AND deleted = 0  ORDER BY name";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	if(!$stmt->execute()) {
		echo json_encode(generateErrorResponse("Getting auditors list failed"));
		die();
	}
	$auditors = $stmt->fetchAll();

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
    
?> 
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner"> 
            <div class="page-content">
                <div class="row">
                    <div class="col-xs-12">
                     <h3>Process Status</h3>

                     <form id="searchForm" style="height:auto">
                        <input type="hidden" name="tidclient" id="tidclient" value="-1" />
                        <input type="hidden" name="tclientName" id="tclientName" value="" />
                        <input type="hidden" name="customerServiceId" id="customerServiceId" value="" />
                        <input type="hidden" name="taskId" id="taskId" value="" />
                        

    <div class="row" style="height:auto">
        <div class="form-group col-md-2">
            <label for="idclient">Client:</label>
            <select class="form-control clientslist" id="idclient">
                <option value="">-- All Clients-- </option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client["id"]; ?>" <?php if ($client["id"] == $_GET["idclient"]):?>selected<?php endif; ?>><?php echo $client["name"]; ?> - <?php echo $client["prefix"]; ?><?php echo $client["id"]; ?></option>
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
                  ?>
                 <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-2">
        
    <label for="last_activity">Last Activity:</label>
    <select id="last_activity" class="form-control">
        <option value="">-- All Activities --</option>
        <option value="today">Today</option>
        <option value="yesterday">Yesterday</option>
        <option value="last7">Last 7 Days</option>
        <option value="last30">Last 30 Days</option>
        <option value="last2months">Last 2 Months</option>
        <option value="last6months">Last 6 Months</option>
        <option value="dateRange">Specific Date Range</option>
    </select>
    <div id="dateRangePicker" class="form-group" style="display: none; margin-top: 5px;">
        <div class="row  no-gutters">
            <div class="col-sm-6">
                <input type="text" id="startDate" class="form-control" placeholder="From">
            </div>
            <div class="col-sm-6">
                <input type="text" id="endDate" class="form-control" placeholder="To">
            </div>
        </div>
    </div>
        </div>
        <div class="form-group col-md-2">
            <label for="industry">Industry:</label>
            <select class="form-control" name="industry" id="industry" placeholder="Industry">
                <option value="">-- All Industries --</option>
                <option value="Slaughter Houses">Slaughter Houses</option>
                <option value="Meat Processing">Meat Processing</option>
                <option value="All Other">All Other</option>
            </select>
        </div>
        <div class="form-group col-md-2">
            <label for="category">Category:</label>
            <?php $categories = getProductCategories(); ?>
            <select class="form-control" id="category" name="category" placeholder="Category">
                <option value="">-- All Categories --</option>
                <?php foreach ($categories as $i => $category): ?>
                    <option value="<?php echo $category; ?>"><?php echo preg_replace ("/<sup>(.*?)<\/sup>/i", "", $category); ?></option>
                <?php endforeach; ?>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="form-group col-md-2">
            <label for="state">Process Status:</label>
            <select class="form-control" id="state" name="state" placeholder="State">
                <option value="">-- All Statuses -- </option>
                <?php foreach ($statusOptions as $option): ?>
                    <option value="<?php echo $option; ?>"><?php echo getAppStateName($option); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-2"> 
            <label>Cert Expires:</label>
            <div class="row no-gutters">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="cert-from" id="cert-from" placeholder="From">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="cert-to" id="cert-to" placeholder="To">
                </div>
            </div>
        </div>    
         <div class="form-group col-md-2"> 
            <label>Auditor:</label>
            <select class="form-control" name="idauditor" id="idauditor" placeholder="idauditor">
               <option value="">-- All Auditors -- </option>
                <?php foreach ($auditors as $auditor): ?>
                    <option value="<?php echo $auditor["id"]; ?>" <?php if ($auditor["id"] == $_GET["idauditor"]):?>selected<?php endif; ?>><?php echo $auditor["name"]; ?></option>
                 <?php endforeach; ?>
            </select>
        </div>    
        <div class="form-group col-md-2"> 
            <label>Need Attention <i class="fa fa-question-circle" data-toggle="modal" data-target="#attentionModal" style="cursor: pointer; "></i></label>
            <select class="form-control" name="need_attention" id="need_attention" placeholder="need_attention">
                <option value="">-- None --</option>
                <option value="audit_date_approval_needed">Audit Date Approval Needed</option>
                <option value="audit_plan_not_sent">Audit Plan Not Sent</option>
                <option value="signed_offer_awaiting_response">Signed Offer Awaiting Response</option>
                <option value="certificate_expiring">Certificate Expiring Within 3 Months</option>
                <option value="awaiting_signed_offer_upload">Waiting for upload of signed offer</option>
                <option value="new_registration">New Registration</option>                
                <!--<option value="client_last_activity_awaiting_response">Client Last Activity Awaiting Response</option>-->
            </select>
        </div>
        <!--
        <div class="form-group col-md-1">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
        -->
    </div>
</form>

<div id="attentionModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Need Attention Explanations</h4>
            </div>
            <div class="modal-body">
                <p><strong>Audit Date Approval Needed:</strong> Client proposed audit dates more than a week ago. We have not approved a date.</p>
                <p><strong>Audit Plan Not Sent:</strong> Less than 10 days left in the approved audit date but the audit plan has not been sent to the client.</p>
                <p><strong>Signed Offer Awaiting Response:</strong> Client uploaded the signed offer a week ago. We have not responded.</p>
                <p><strong>Certificate Expiring Within 3 Months:</strong> Certificate is set to expire within the next three months.</p>
                <p><strong>Waiting for upload of signed offer:</strong> Offer has been sent but the client has not yet uploaded the signed offer.</p>
                <p><strong>New Registration:</strong> This is a newly registered client account and requires initial review or assignment.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="legend" class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="legend-item"><span class="legend-color highlight-audit-date-approval"></span> Audit Date Approval Needed</span>
                <span class="legend-item"><span class="legend-color highlight-audit-plan"></span> Audit Plan Not Sent</span>
                <span class="legend-item"><span class="legend-color highlight-signed-offer"></span> Signed Offer Awaiting Response</span>
                <span class="legend-item"><span class="legend-color highlight-certificate-expiring"></span> Certificate Expiring</span>
    <span class="legend-item"><span class="legend-color highlight-awaiting-upload"></span> Waiting for Upload of Signed Offer</span>
    <span class="legend-item"><span class="legend-color highlight-new-registration"></span> New Registration</span>

                          </div>
        </div>

                    <table id="table_process" class="table table-hover- table-striped- table-bordered w-100" style="width:100%;">
                        <thead>
                            <tr class="tableheader">
                            <th style="width:200px;">Client</th>
                            <th class="no-wrap" style="width:100px;">Last Activity</th>
                             <th class="no-wrap" style="width:80px;">Industry</th>
                            <th class="no-wrap" style="width:80px;">Category</th>
                            <th class="no-wrap" style="width:80px;">Process Status</th>
                            <th class="no-wrap" style="width:220px;">Products</th>
                            <th class="no-wrap" style="width:220px;">Ingredients</th>
                            <th class="no-wrap" style="width:65px;">Cert. Exp</th>
                            <th class="no-wrap" style="width:65px;">Auditors</th>
                            <th class="no-wrap no-sort" style="width:25px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
</div><!-- /.main-container -->

<div class="modal fade" id="adminModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="prodModal-label">
    <form id="admin-form" method="post">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                <h4 class="modal-title" id="adminModal-label">Edit Client</h4>
            </div>
            <div class="modal-body">
                
                    <input type="text" hidden id="adminid"/>
                    <div class="row form-group">
                    <label class="col-xs-12 col-md-4">User Name</label>
                    <div class='col-xs-12 col-md-8'>
                        <input type="text" class="form-control" id="name" maxlength="50"/>
                        <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Email</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="email" maxlength="500"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Prefix</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="prefix" maxlength="15"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Login</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="login" maxlength="20"/>
                            <div class="alert-string"></div>
                        </div></div>


                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Password</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="pass" maxlength="10"/>
                            <div class="alert-string"></div>
                        </div></div>

 
                    <div class="row form-group">
  <label class="col-xs-12 col-md-4">Company Address </label>
  <div class='col-xs-12 col-md-8'>
    <input type="text" class="form-control" name="address" id="address" value="" />
    <div class="alert-string address"></div>
  </div>
</div>

<div class="row form-group">
  <label class="col-xs-12 col-md-4">City </label>
  <div class='col-xs-12 col-md-8'>
    <input type="text" class="form-control" name="city" id="city" value="" />
    <div class="alert-string city"></div>
  </div>
</div>
<input type="hidden" name="state" id="state" value="" />

<div class="row form-group">
  <label class="col-xs-12 col-md-4">Zip Code </label>
  <div class='col-xs-12 col-md-8'>
    <input type="text" class="form-control" name="zip" id="zip" value="" />
    <div class="alert-string zip"></div>
  </div>
</div>

<div class="row form-group">
  <label class="col-xs-12 col-md-4">Country </label>
  <div class='col-xs-12 col-md-8'>
    <select name="country" id="country" class="form-control">
      <option value="">Please Select</option>
      <?php foreach ($country_list as $country): ?>
        <option value="<?php echo $country; ?>"><?php echo $country; ?></option>
      <?php endforeach; ?>
    </select>
    <div class="alert-string country"></div>
  </div>
</div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-4">Industry </label>
                      <div class='col-xs-12 col-md-8'>
                        <select class="form-control" name="industry" id="industry">
                        	<option value=""></option>
                        	<option value="Slaughter Houses">Slaughter Houses</option>
                            <option value="Meat Processing">Meat Processing</option>
                            <option value="All Other">All Other</option>
                        </select>
                        <div class="alert-string industry"></div>
                      </div>
                    </div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-4">Product Category </label>
                      <div class='col-xs-12 col-md-8'>
                        <?php $categories = getProductCategories(); ?>
                          <select name="category" id="category" class="form-control">
                            <option value=""></option>  
                            <?php foreach ($categories as $i => $category): ?>
                              <option value="<?php echo $category; ?>"><?php echo preg_replace ("/<sup>(.*?)<\/sup>/i", "", $category); ?></option>
                            <?php endforeach; ?>
                            <option value="other">Other</option>
                          </select>                          
                          <input type="text" class="form-control" name="other-category" id="other-category" placeholder="Other Category" style="display: none; margin-top:5px;" value="" />
                        <div class="alert-string category"></div>
                      </div>
                    </div>
                    
                     <div class="row form-group">
                      <label class="col-xs-12 col-md-4">VAT Number </label>
                      <div class='col-xs-12 col-md-8'>
                        <input type="text" class="form-control" name="vat" id="vat" value="" />
                        <div class="alert-string vat"></div>
                      </div>
                    </div>
                   
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-4">Contact Person Name  </label>
                      <div class='col-xs-12 col-md-8'>
                        <input type="text" class="form-control" name="contact_person" id="contact_person" value="" />
                        <div class="alert-string contact_person"></div>
                      </div>
                    </div>
                    <div class="row form-group">
                      <label class="col-xs-12 col-md-4">Phone Number</label>
                      <div class='col-xs-12 col-md-8'>
                        <input type="text" class="form-control" name="phone" id="phone" value="" />
                        <div class="alert-string phone"></div>
                      </div>
                    </div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Ingredients Limit</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="number" class="form-control" id="ingrednumber" max="1000000" min="0"/>
                            <div class="alert-string"></div>
                        </div></div>
                    <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Products Limit</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="number" class="form-control" id="prodnumber" max="1000000" min="0"/>
                            <div class="alert-string"></div>
                        </div></div>

                        <div class="row form-group">
                        <label class="col-xs-12 col-md-4">Cert Expires:</label>
                        <div class='col-xs-12 col-md-8'>
                            <input type="text" class="form-control" id="CertificateExpiryDate" name="CertificateExpiryDate"/>
                            <div class="alert-string"></div>
                        </div></div>
                        
                        <div class="row form-group">
    <label class="col-xs-12 col-md-4">Is your facility a pork-free facility?</label>
    <div class='col-xs-12 col-md-8'>
        <label><input type="radio" name="pork_free_facility" value="Yes"> Yes</label>
        <label><input type="radio" name="pork_free_facility" value="No"> No</label>
        <div class="alert-string pork_free_facility"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-4">Do you have dedicated lines for Halal production?</label>
    <div class='col-xs-12 col-md-8'>
        <label><input type="radio" name="dedicated_halal_lines" value="Yes"> Yes</label>
        <label><input type="radio" name="dedicated_halal_lines" value="No"> No</label>
        <label><input type="radio" name="dedicated_halal_lines" value="Not applicable"> Not applicable</label>
        <div class="alert-string dedicated_halal_lines"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-4">What are your target export regions?</label>
    <div class='col-xs-12 col-md-8'>
        <input type="text" class="form-control" name="export_regions" id="export_regions" value="" />
        <div class="alert-string export_regions"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-4">Are the products to be Halal certified, produced by a third party?</label>
    <div class='col-xs-12 col-md-8'>
        <label><input type="radio" name="third_party_products" value="Yes"> Yes</label>
        <label><input type="radio" name="third_party_products" value="No"> No</label>
        <div class="alert-string third_party_products"></div>
    </div>
</div>
<div class="row form-group">
    <label class="col-xs-12 col-md-4">Is this third party Halal certified?</label>
    <div class='col-xs-12 col-md-8'>
        <label><input type="radio" name="third_party_halal_certified" value="Yes"> Yes</label>
        <label><input type="radio" name="third_party_halal_certified" value="No"> No</label>
        <label><input type="radio" name="third_party_halal_certified" value="Not applicable"> Not applicable</label>
        <div class="alert-string third_party_halal_certified"></div>
    </div>
</div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-save" >Save changes</button>
            </div>
        </div>
    </div>
    </form>
</div>

<div class="modal fade" id="notesModal" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="notesModalLabel">Edit Notes</h4>
            </div>
            <div class="modal-body">
                <div style="margin-bottom:20px; font-size:18px;"><strong>Client:</strong> <span class="clientname"></span></div>
                <input type="hidden" name="idclient" class="idclient" value=""/>
                <input type="hidden" name="idapp" class="idapp" value=""/>
                <textarea class="notes form-control" style="width:100%; height:150px;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary save-notes">Save changes</button>
            </div>
        </div>
    </div>
</div>

<div id="activityLogModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Activity Log</h4>
                </div>
                <div class="modal-body">
                    <div id="activityLogContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_tickets" tabindex="-1" aria-labelledby="modalTicketsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h5 class="modal-title" id="modalTicketsLabel">Customer Support <span class="text-primary tclientName"></span></h5>
        </div>
        <div class="modal-body">
                    
                    <input type="hidden" name="ticketStatus" id="ticketStatus" value="1" />
                    <?php //if (!$myuser->userdata['isclient']): ?>
                    <div class="row gutters">
                    <label class="right">
                        <input id="filter-tickets-confirmed" class="ace ace-switch ace-switch-4" type="checkbox">
                        <span class="lbl">&nbsp;&nbsp;Show closed requests</span>
                                    <a href="#" id="create-ticket-btn" class="btn btn-primary" data-toggle="modal" data-target="#customerServiceModal">
                <i class="fa fa-plus"></i> Create Ticket
            </a>

                    </label>
                    </div>              
            <table id="table_tickets" class="table table-hover table-striped table-bordered w-100">
            <thead>
                <tr class="tableheader">
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
        </div>
    </div>
    </div>

<div class="modal fade" id="modal_tasks" tabindex="-1" aria-labelledby="modalTasksLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h5 class="modal-title" id="modalTasksLabel">Assigned Tasks <span class="text-primary tclientName"></span></h5>
      </div>
      <div class="modal-body">
                            <input type="hidden" name="taskStatus" id="taskStatus" value="1" />

        <div class="row gutters">
                        <div class="col-md-4">
              
                    </div>
                        <div class="col-md-8">
                    <label class="right">
                        <input id="filter-tasks-confirmed" class="ace ace-switch ace-switch-4" type="checkbox">
                        <span class="lbl">&nbsp;&nbsp;Show completed tasks</span>

                        
        
            <a href="#" id="create-task-btn" class="btn btn-primary" data-toggle="modal" data-target="#tasksModal">
                <i class="fa fa-plus"></i> Create Task
            </a>
        
    
                    </label>
                        </div>
                    </div>         
        <table id="table_tasks" class="table table-hover table-striped table-bordered w-100">
          <thead>
            <tr class="tableheader">
              <th class="no-wrap">Reference #</th>
              <th class="no-wrap">Assigned to</th>  
              <th class="no-wrap">Category</th>                            
              <th class="no-wrap">Task</th>                            
              <th class="no-wrap">Status</th>     
              <th class="no-wrap">Created by</th>                            
              <th class="no-wrap">Created On</th>                            
              <th class="no-wrap">Last Updated</th>                            
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="postReplyModalTickets" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel">
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
            <span id="tRequestType">Bug</span> <!-- Replace "Bug" with the actual request type value -->
        </div>

        <div class="form-group">
            <label for="lastUpdated" style="font-weight: bold;">Last Updated: </label>
            <span id="lastUpdated"></span> <!-- Replace with actual URL -->
      </div>  

    </div>
    <div class="col-md-4">
    <div class="form-group">
            <label for="currentURL" style="font-weight: bold;">URL: </label>
            <span id="currentURL"></span> <!-- Replace with actual URL -->
            
        </div>
        <div class="form-group">
            <label for="status" style="font-weight: bold;">Status: </label>
            <span id="status"><span class="badge badge-success">Open</span></span> <!-- Replace with actual URL -->
            <input type="hidden" name="status_val" id="status_val" value="" />
        </div>          

    </div>
    <div class="col-md-4">
    <div class="form-group">
            <label for="dateCreated" style="font-weight: bold;">Created: </label>
            <span id="dateCreated"></span> <!-- Replace with actual URL -->
        </div>     
         
    </div>
</div>
<div class="row">
    <div class="col-md-12 text-right">
        <a href="#" class="btn btn-danger" id="btnCloseCustomerService" style="display:none;">Close Request</a>
    </div>
</div>
<!--
<div class="form-group">
            <label for="requestDescription" style="font-weight: bold;">Request Description</label>
            <p id="requestDescription"> </p> 
            <span id="attachments"></span> 
        </div>
                            -->
        <label for="requestDescription" style="font-weight: bold;">Messages</label>
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
            <label for="attachment">Attachment (Screenshot, Excel file etc.)</label>
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

<div class="modal fade" id="postReplyModalTasks" tabindex="-1" role="dialog" aria-labelledby="notesModalLabel">
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
            <span class="fileinput-button" id="dropzone145">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload145" type="file" foldertype="addoc145" subfolder="Tickets" infotype="tickets" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="uladdoc145"></ul>
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
        <h4 class="modal-title" id="tasksModal">Create Task <span class="text-primary tclientName"></span></h4>
      </div>
      <div class="modal-body">
          <!--<p class="text-muted">
          Use this form to assign a task to a team member or auditor. Select the recipient, choose the relevant client, and provide task details.
          </p>-->

         <div class="alert alert-danger" id="taskErrors" style="display: none;"></div>
        <form id="taskForm">        

        <div class="form-group">
            <label for="tauditor">Assign to Auditor</label>
            <select class="form-control" id="tidauditor">
                <option value="">Please Select</option>
         <?php foreach ($auditors as $auditor): ?>
                 <option value="<?php echo $auditor["id"]; ?>" 
                    <?php if (isset($_GET["idauditor"]) && $auditor["id"] == $_GET["idauditor"]) echo 'selected'; ?>>
                    <?php echo $auditor["name"]; ?> &lt;<?php echo $auditor["email"]; ?>&gt;
                </option>
         <?php endforeach; ?>
 </select>

           </div>
         
        <div class="form-group">
            <label for="issueType">Category</label>
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
            <label for="issueDescription">Description</label>
            <textarea class="form-control" id="issueDescription" rows="5" placeholder="Describe the request in detail."></textarea>
          </div>
          
        
          <div class="form-group">
          <label for="attachment">Attachment (Screenshot, Excel, PDF file etc.)</label>
            <span class="fileinput-button" id="dropzone245">Drop files here or click to upload
                    	<input class="fileupload" id="fileupload245" type="file" foldertype="addoc245" subfolder="Tickets" infotype="tickets" name="files[]" multiple="">
               			 </span><span class="loader"></span>
                            <ul id="uladdoc245"></ul>
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
    var windowHeight = $(window).height();
    var minimumDataTableHeight = 200;
    var calculatedDataTableHeight = Math.max(windowHeight - 295, minimumDataTableHeight);
    var table_process = $('#table_process').DataTable({
    paging: true,
    searching: false,
    ordering: true,
    pageLength: 625,
    processing: true,
    serverSide: true,
    scrollY: calculatedDataTableHeight, // Set scrollY to the calculated height
    scrollX: true,
    ajax: {
        url: "ajax/getProcessStatus.php",
        type: "POST",
        async: true,
        data: function (data) {
            data.idclient = $('#idclient').val();
            data.industry = $('#industry').val();
            data.last_activity = $('#last_activity').val();
            data.start_date = $('#startDate').val();
            data.end_date = $('#endDate').val();
            data.category = $('#category').val();
            data.state = $('#state').val();
            data.cert_from = $('#cert-from').val();
            data.cert_to = $('#cert-to').val();
            data.idauditor = $('#idauditor').val();
            data.need_attention = $('#need_attention').val();
        }
    },
    columns: [
        { data: "user_data" },
        { data: "last_activity_date" },
        { data: "industry" },
        { data: "category" },
        { data: "process_status" },
        { data: "products" },
        { data: "ingredients" },
        { data: "days" },
        { data: "auditors" },
        { data: "notes", className: 'text-center' },
        // Add these fields to the returned data
        { data: "audit_date_approval_needed", visible: false },
        { data: "audit_plan_not_sent", visible: false },
        { data: "signed_offer_awaiting_response", visible: false },
        { data: "certificate_expiring", visible: false }
    ],
    columnDefs: [
        { targets: 'no-sort', orderable: false },
    ],
    order: [[1, 'desc']], // Sort by the second column in descending order
    createdRow: function(row, data, dataIndex) {
        if (data.audit_date_approval_needed == 1) {
            $(row).addClass('highlight-audit-date-approval');
        }
        if (data.audit_plan_not_sent == 1) {
            $(row).addClass('highlight-audit-plan');
        }
        if (data.signed_offer_awaiting_response == 1) {
            $(row).addClass('highlight-signed-offer');
        }
        if (data.certificate_expiring == 1) {
            $(row).addClass('highlight-certificate-expiring');
        }
        if (data.awaiting_signed_offer_upload == 1) {
            $(row).addClass('highlight-awaiting-upload');
        }
        if (data.new_registration == 1) {
            $(row).addClass('highlight-new-registration');
        }
    }
});

 var ticketsInitialized = false;
    var tasksInitialized = false;
// Initialize table after modal is shown
    $('#modal_tickets').on('shown.bs.modal', function () {
        if (!ticketsInitialized) {
 var table_tickets = $('#table_tickets').DataTable({
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
      
        ajax: {
            url: "ajax/getCustomerServices.php",
            type: "POST",
            async: true,
            data: function (data) {
                data.idclient = $('#tidclient').val();
                data.status = $('#ticketStatus').val();
            }
        },
        columns: [
 
            { data: "id" },
            { data: "request_type" },
            { data: "request_description" },
            { data: "status" },
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
            ticketsInitialized = true;
        } else {
            $('#table_tickets').DataTable().ajax.reload(null, false);
        }
    });

    $('#modal_tasks').on('shown.bs.modal', function () {
        if (!tasksInitialized) {
             
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
      
        ajax: {
            url: "ajax/getTasks.php",
            type: "POST",
            async: true,
            data: function (data) {
                data.status = $('#taskStatus').val();
                data.idclient = $('#tidclient').val();                
            }
        },
        columns: [
            { data: "id" },
             { data: "auditorname" },
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
            tasksInitialized = true;
        } else {
            $('#table_tasks').DataTable().ajax.reload(null, false);
        }
    });

    $('#filter-tickets-confirmed').on('change', function (e) {
        $("#ticketStatus").val($(this).is(":checked") ? '0' : '1');
        $('#table_tickets').DataTable().ajax.reload(null, false);
    });

    $('#filter-tasks-confirmed').on('change', function (e) {
        $("#taskStatus").val($(this).is(":checked") ? '0' : '1');
        $('#table_tasks').DataTable().ajax.reload(null, false);
    });


     $(document).on('click', '.client-tickets', function (e) {
        e.preventDefault();
        var clientId = $(this).attr('id');
        var clientName = $(this).data('name');
         $("#tidclient").val(clientId);
         $(".tclientName").html(clientName);
         $("#tclientName").val(clientName);
         $('#modal_tickets').modal('show');
    });

    // Assign Tasks click
    $(document).on('click', '.client-tasks', function (e) {
        e.preventDefault();
        var clientId = $(this).attr('id');
        var clientName = $(this).data('name');
        $("#tidclient").val(clientId);
        $(".tclientName").html(clientName);
        $("#tclientName").val(clientName);
        // Get the list of allowed auditor IDs from the <i> tag inside the clicked .client-tasks
        var auditorIDs = $(this).data('auditors'); // e.g., "294,322"

        if (auditorIDs) {
            var allowedIDs = auditorIDs.toString().split(',').map(id => id.trim());
            // Reset and rebuild the <select> options
            $('#tidauditor option').each(function () {
                const optionValue = $(this).val();
                const shouldShow = allowedIDs.includes(optionValue) || optionValue === "";
                $(this).toggle(shouldShow);
            });
        }

        // Optionally reset the selected value
        $('#idauditor').val('');
        
         $('#modal_tasks').modal('show');
    });

    
    $("#btnSubmitTask").on("click", function () {
        var texts = [];

        $("#tasksModal #uladdoc245 li").each(function () {
        var spanText = $(this).find("span:first").text();
        texts.push(spanText);
        });

        var attachments = texts.join(", ");
        var formData = {
        uid: 0,
        rtype: "createTask",
        idauditor: $("#tasksModal #tidauditor").val(),
        idclient: $("#tidclient").val(),
        issueType: $("#tasksModal #issueType").val(),
        issueDescription: $("#tasksModal #issueDescription").val(),
        taskType: "client",
        attachments: attachments,
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
            //alert('Issue reported successfully with ID: ' + jsonResponse.data.id);
            $("#tasksModal").modal("hide");
            $("#taskForm")[0].reset();
            var table_tasks = $('#table_tasks').DataTable(); 
            table_tasks.ajax.reload( null, false );

            alert(
                "Task created successfully!"
            );

                $('#tasksModal').modal('hide');            

            }
        },
        });
    });

    $('#idclient').on('change', function() {
		table_process.ajax.reload(null, false);
	});
    
    $('#industry').on('change', function() {
		table_process.ajax.reload(null, false);
	});
    
    $('#category').on('change', function() {
		table_process.ajax.reload(null, false);
	});

    $('#state').on('change', function() {
		table_process.ajax.reload(null, false);
	});

     $('#idauditor').on('change', function() {
		table_process.ajax.reload(null, false);
	});

    $('#need_attention').on('change', function() {
		table_process.ajax.reload(null, false);
	});

    $("#CertificateExpiryDate").datepicker({
        dateFormat: 'dd/mm/yy', // Date format
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true, // Show button panel for easy navigation
    });

    $("#cert-from, #cert-to").datepicker({
        dateFormat: 'yy-mm-dd', // Date format
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true, // Show button panel for easy navigation
        onSelect: function(selectedDate) {
            var option = this.id == "cert-from" ? "minDate" : "maxDate",
                instance = $(this).data("datepicker"),
                date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings);
            $("#cert-from, #cert-to").not(this).datepicker("option", option, date);
            table_process.ajax.reload(null, false);
        }
    });
    
    $('#table_process tbody').on('click', '.edit-notes', function() {
		var idapp = $(this).data('idapp');
        var idclient = $(this).data('idclient');
		$.ajax({
			type: 'POST',
			url: "ajax/getProcessNotes.php",
			data: {
				idapp: idapp,
                idclient: idclient
			},
			dataType: 'json',
			success: function(response) {
				if (response) {
					// Populate form fields with retrieved data
                    $('#notesModal .idclient').val(idclient);					
					$('#notesModal .idapp').val(idapp);					                    
					$('#notesModal .clientname').html(response.name);					
                    $('#notesModal .notes').val(response.notes);					
					$('#notesModal').modal('show');
				} else {
					// Handle error if necessary
					console.error('Error fetching details.');
				}
			},
			error: function(xhr, status, error) {
				console.error(error);
			}
		});

        return false;
	});

    $('.save-notes').on('click', function() {
		// Disable the button to prevent double-click
		// Collect data from form fields
		var processData = {
			idclient: $('#notesModal .idclient').val(),
            idapp: $('#notesModal .idapp').val(),
            notes: $('#notesModal .notes').val(),
		};
		$.ajax({
			type: 'POST',
			url: 'ajax/saveProcessNotes.php',
			data: processData,
			dataType: 'json',
			success: function(response) {
				if (response.status === 'success') {
					$('#notesModal').modal('hide'); 
                    table_process.ajax.reload(null, false);
				}
			},
			error: function(xhr, status, error) {
				// Handle other errors, if needed
				console.error(error);
			},
			complete: function() {
			}
		});
	});

    $(document).on('click', '.edit-client', function() {
        var id = $(this).attr('id');
        $.post('ajax/ajaxHandler.php', {
            rtype: 'getAdmin',
            uid: 0,
            id: id,
            }).done(function (data) {
            
            var response = JSON.parse(data);
            
            var data = response.data;
            $('#admin-form #adminid').val(data.id);
            $('#admin-form #name').val(data.name);
            $('#admin-form #email').val(data.email);
            $('#admin-form #prefix').val(data.prefix);
            $('#admin-form #login').val(data.login);
            $('#admin-form #ingrednumber').val(data.ingrednumber);
            $('#admin-form #prodnumber').val(data.prodnumber);
            $('#admin-form #address').val(data.address);
            $('#admin-form #city').val(data.city);
            $('#admin-form #zip').val(data.zip);
            $('#admin-form #country').val(data.country);
            $('#admin-form #vat').val(data.vat);      
            $('#admin-form #industry').val(data.industry);      
            $('#admin-form #category').val(data.category);      
            $('#admin-form #contact_person').val(data.contact_person);      
            $('#admin-form #phone').val(data.phone);      
            $('#admin-form #prodnumber').val(data.prodnumber);
            $('#admin-form #CertificateExpiryDate').val(data.CertificateExpiryDate);            
            $("input[name='pork_free_facility'][value='" + data.pork_free_facility + "']").prop('checked', true);
            $("input[name='dedicated_halal_lines'][value='" + data.dedicated_halal_lines + "']").prop('checked', true);
            $('#admin-form #export_regions').val(data.export_regions);
            $("input[name='third_party_products'][value='" + data.third_party_products + "']").prop('checked', true);
            $("input[name='third_party_halal_certified'][value='" + data.third_party_halal_certified + "']").prop('checked', true);

            $('#adminModal').modal('show');
        });

        return false;
    });

    $(document).on('click', '.btn-save', function() {
        var doc = {};
        doc.id = $('#admin-form #adminid').val();
        doc.company_id = $('#admin-form #company_id').val();
        doc.company_admin = $("#admin-form #company_admin").is(":checked") ? '1' : '0';
        doc.name = $('#admin-form #name').val();
        doc.email = $('#admin-form #email').val();
        doc.prefix = $('#admin-form #prefix').val();
        doc.login = $('#admin-form #login').val();
        doc.address=  $('#admin-form #address').val();
        doc.city=  $('#admin-form #city').val();
        doc.zip=  $('#admin-form #zip').val();
        doc.country=  $('#admin-form #country').val();
        doc.vat=  $('#admin-form #vat').val();      
        doc.industry=  $('#admin-form #industry').val();      
        doc.category=  $('#admin-form #category').val();      
        doc.contact_person=  $('#admin-form #contact_person').val();      
        doc.phone=  $('#admin-form #phone').val();      
        doc.pork_free_facility = $("input[name='pork_free_facility']:checked").val();
        doc.dedicated_halal_lines = $("input[name='dedicated_halal_lines']:checked").val();
        doc.export_regions = $('#admin-form #export_regions').val();
        doc.third_party_products = $("input[name='third_party_products']:checked").val();
        doc.third_party_halal_certified = $("input[name='third_party_halal_certified']:checked").val();
        if ($('#admin-form #pass').val().trim() != '')
        doc.pass = hex_sha512($('#admin-form #pass').val());
        doc.ingrednumber = $('#admin-form #ingrednumber').val();
        doc.prodnumber = $('#admin-form #prodnumber').val();
        doc.CertificateExpiryDate = $('#admin-form #CertificateExpiryDate').val();
        doc.isclient = 1;
        $.post('ajax/ajaxHandler.php', {
            rtype: 'saveAdmin',
            uid: 0,
            data: doc,
            }).done(function (data) {
                var response = JSON.parse(data);
                if (response.status == 0) {
                    Utils.notify('error', response.statusDescription);
                    return;
                }
                Utils.notify('success', 'Changes were submitted');
                table_process.ajax.reload(null, false);
                $('#adminModal').modal('hide');
        });
    });

    $(document).on('click', '.activity-log', function() {
            var idapp = $(this).data('idapp');

            $.ajax({
                url: 'ajax/getActivityLog.php',
                type: 'POST',
                data: { idapp: idapp },
                success: function(response) {
                    $('#activityLogContent').html(response);
                    $('#activityLogModal').modal('show');
                },
                error: function() {
                    alert('Error loading activity log.');
                }
            });

            return false;
        });

    $('#startDate, #endDate').datepicker({
        format: 'mm/dd/yyyy',
        dateFormat: 'yy-mm-dd', // Date format
        autoclose: true,
        onSelect: function(selectedDate) {
            var option = this.id == "startDate" ? "minDate" : "maxDate",
                instance = $(this).data("datepicker"),
                date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings);
            $("#startDate, #endDate").not(this).datepicker("option", option, date);
            table_process.ajax.reload(null, false);
        }        
    });

    $('#last_activity').on('change', function() {
        var selectedFilter = $(this).val();
        if (selectedFilter === 'dateRange') {
            $('#dateRangePicker').show();
        } else {            
            $('#dateRangePicker').hide();
            table_process.ajax.reload(null, false);
        }
    });
});

$(document).ready(function() {

    
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
                    table_tickets.ajax.reload(null, false);
                    $('#postReplyModalTickets').modal('hide');            
            });
         }   		
        return false;
	});

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
                // Populate form fields with retrieved data
                $('#postReplyModalTickets #clientname').val(response.clientname);
                $('#postReplyModalTickets #referenceNo').html(response.id);
                $('#postReplyModalTickets #tRequestType').html(response.request_type);
                //$('#requestDescription').html(response.request_description);
                $('#postReplyModalTickets #currentURL').html(response.current_url);
                $('#postReplyModalTickets #attachments').html(response.attachments);
                $('#postReplyModalTickets #dateCreated').html(response.date_created);
                $('#postReplyModalTickets #lastUpdated').html(response.last_updated);
                if (response.status == '1') {
                    $('#postReplyModalTickets #status').html('<span class="badge badge-success">Open</span>');
                    $('#postReplyModalTickets #btnCloseCustomerService').show();
                } else {
                    $('#postReplyModalTickets #status').html('<span class="badge badge-danger">Closed</span>');
                    $('#postReplyModalTickets #btnCloseCustomerService').hide();
                }
                $('#postReplyModalTickets #status_val').html(response.status);
                $('#postReplyModalTickets #replies').html(response.replies);
                var scrollDiv = $('#postReplyModalTickets #replies');
                scrollDiv.scrollTop(scrollDiv[0].scrollHeight);                
              }
            }
      });        
     }

      $(document).on('click', '#table_tickets .post-reply', function() {
        var id = $(this).attr("id");
        $("#customerServiceId").val(id);
      getCustomerServiceData(id);
      $('#postReplyModalTickets').modal('show');
      return false;
    });

    $(document).on('click', '#table_tasks .post-reply', function() {
        var id = $(this).attr("id");
        $("#taskId").val(id);
      getTaskData(id);
      $('#postReplyModalTasks').modal('show');
      return false;
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
                    $('#postReplyModalTasks').modal('hide');            
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
                $('#postReplyModalTasks #clientname').val(response.clientname);
                $('#postReplyModalTasks #referenceNo').html(response.id);
                $('#postReplyModalTasks #tusername').html(response.username);
                $('#postReplyModalTasks #tauditorname').html(response.auditorname);
                $('#postReplyModalTasks #tclientname').html(response.clientname);
                $('#postReplyModalTasks #tIssueType').html(response.issue_type);
                //$('#issueDescription').html(response.issue_description);
                 $('#postReplyModalTasks #attachments').html(response.attachments);
                $('#postReplyModalTasks #dateCreated').html(response.date_created);
                $('#postReplyModalTasks #lastUpdated').html(response.last_updated);
                if (response.iscreator == "1") {
                    if (response.status == '1') {
                        $('#postReplyModalTasks #status').html('<span class="badge badge-success">Open</span>');
                        $('#postReplyModalTasks #btnClosetTask').show();
                    } else {
                        $('#postReplyModalTasks #status').html('<span class="badge badge-danger">Closed</span>');
                        $('#postReplyModalTasks #btnClosetTask').hide();
                    }
                }
                $('#postReplyModalTasks #status_val').html(response.status);
                $('#postReplyModalTasks #replies').html(response.replies);
                var scrollDiv = $('#postReplyModalTasks #replies');
                scrollDiv.scrollTop(scrollDiv[0].scrollHeight);                
              }
            }
      });        
     }
  

    $('#postReplyModalTasks').on('shown.bs.modal', function () {
        var scrollDiv = $('#postReplyModalTasks #replies');
        scrollDiv.scrollTop(scrollDiv[0].scrollHeight);
        $("#postReplyModalTasks #replyMessage").val("");
    });


    $('#postReplyModalTickets').on('shown.bs.modal', function () {
        var scrollDiv = $('#postReplyModalTickets #replies');
        scrollDiv.scrollTop(scrollDiv[0].scrollHeight);
        $("#postReplyModalTickets #replyMessage").val("");
    });

   
    $('#postReplyModalTasks #btnPostReply').on('click', function(e) {
          e.preventDefault();

          var texts = [];

      $('#postReplyModalTasks #uladdoc145 li').each(function() {
          var spanText = $(this).find('span:first').text();
          texts.push(spanText);
      });
      var attachments = texts.join(', ');

          var formData = {
              taskId: $('#taskId').val(), // Assuming you have a hidden input field with id 'taskId' to store task ID
              message: $("#postReplyModalTasks #replyMessage").val(),
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
             
                $('#postReplyModalTasks #alertMessage').removeClass('alert-success').addClass('alert-danger').html("<ul>"+response.data.errors+"</ul>").fadeIn();
            } else {
   // Show error message in alert div
   var id =  $("#taskId").val();
                getTaskData(id);                
                // Show success message
                $('#postReplyModalTasks #alertMessage').removeClass('alert-danger').addClass('alert-success').text('Reply successfully sent!').fadeIn().delay(3000).fadeOut();
                $("#postReplyModalTasks #replyMessage").val("");
                $('#postReplyModalTasks #uladdoc145').empty();
                // Reload or update the DataTable, assuming you have a DataTable instance called table_tasks
                table_tasks.ajax.reload(null, false);
            }
          }).fail(function (xhr, status, error) {
              // Handle Ajax error here
              
          });
          return false;
    });

    $('#postReplyModalTasks #fileupload145')
  .fileupload({
    url: 'fileupload/ProcessFiles.php',
    dataType: 'json',
    dropZone: $('#postReplyModalTasks #dropzone145'),
    add: function (e, data) {
      data.formData = {
        folderType: $(this).attr('foldertype'),
        infoType: $(this).attr('infotype'),
        subFolder: $(this).attr('subfolder'),
        client: $('#tclientName').val(),
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


$("#tasksModal #fileupload245")
    .fileupload({
      url: "fileupload/ProcessFiles.php",
      dataType: "json",
      dropZone: $("#tasksModal #dropzone245"),
      add: function (e, data) {
        data.formData = {
          folderType: $(this).attr("foldertype"),
          infoType: $(this).attr("infotype"),
          subFolder: $(this).attr("subfolder"),
          client: $("#tclientName").val(),
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
          $("#postReplyModalTasks #ul" + file.folderType).append(filename);
        });
      },
    })
    .prop("disabled", !$.support.fileInput)
    .parent()
    .addClass($.support.fileInput ? undefined : "disabled");



    $('#postReplyModalTickets #btnPostReply').on('click', function(e) {
          e.preventDefault();

          var texts = [];

      $('#postReplyModalTickets #uladdoc144 li').each(function() {
          var spanText = $(this).find('span:first').text();
          texts.push(spanText);
      });
      var attachments = texts.join(', ');

          var formData = {
              customerServiceId: $('#customerServiceId').val(), // Assuming you have a hidden input field with id 'customerServiceId' to store ticket ID
              message: $("#postReplyModalTickets #replyMessage").val(),
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
             
                $('#postReplyModalTickets #alertMessage').removeClass('alert-success').addClass('alert-danger').html("<ul>"+response.data.errors+"</ul>").fadeIn();
            } else {
                // Show error message in alert div
                var id =  $("#customerServiceId").val();
                getCustomerServiceData(id);                
                // Show success message
                $('#postReplyModalTickets #alertMessage').removeClass('alert-danger').addClass('alert-success').text('Reply successfully sent!').fadeIn().delay(3000).fadeOut();
                $("#postReplyModalTickets #replyMessage").val("");
                $('#postReplyModalTickets #uladdoc144').empty();

                // Reload or update the DataTable, assuming you have a DataTable instance called table_tickets
                table_tickets.ajax.reload(null, false);
            }
          }).fail(function (xhr, status, error) {
              // Handle Ajax error here
              
          });
          return false;
    });

    $('#postReplyModalTickets #fileupload144')
  .fileupload({
    url: 'fileupload/ProcessFiles.php',
    dataType: 'json',
    dropZone: $('#postReplyModalTickets #dropzone144'),
    add: function (e, data) {
      data.formData = {
        folderType: $(this).attr('foldertype'),
        infoType: $(this).attr('infotype'),
        subFolder: $(this).attr('subfolder'),
        client: $('#tclientName').val(),
      };
      var goUpload = true;
      var uploadFile = data.files[0];
      if (!/\.(jpg|jpeg|png|gif|xls|xlsx)$/i.test(uploadFile.name)) {
    alert('You can upload JPG, JPEG, PNG, GIF, or Excel file(s) only');
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
        $('#postReplyModalTickets #ul' + file.folderType).append(filename);
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