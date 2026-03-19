<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('pages/header.php');
    include_once ('includes/func.php'); ?>
    <title>Production Sites - Halal Digital</title>
    <style>
        #siteModal .form-group {
            margin-bottom: 10px;
        }
        
        .site-status-active {
            color: #28a745;
            font-weight: bold;
        }
        
        .site-status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php
    $db = acsessDb :: singleton();
    $dbo = $db->connect();
?>
<?php include_once('pages/navigation.php');?>
<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">
                        <div class="widget-box widget-border" style="margin-bottom:15px;">
                            <div class="widget-body">
                                <div class="widget-main">
                                    <?php 
                                    $db = acsessDb :: singleton();
                                    $dbo = $db->connect();
                                    
                                    $myuser = cuser::singleton();
                                    $myuser->getUserData();
                                
                                    $parent_id = $myuser->userdata['id'];
                                    $isClient = $myuser->userdata['isclient'] == "1" ? true : false;
                                    $isAuditor = $myuser->userdata['isclient'] == '2' ? true : false;
                                    $isAdmin = !$isClient && !$isAuditor;
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
                                    else { // Admin
                                        $sql = "SELECT id, name, prefix FROM tusers WHERE isclient=1 AND IFNULL(parent_id,'0') = '0' AND deleted = 0 ORDER BY name";
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
                                            $childClients[$child['parent_id']][] = $child;
                                        }
                                    }

                                    if ($isClient && count($clients) > 1) {
                                        $hasFacilities = true;
                                    }
                                    ?>
                                    
                                    <?php if ($isClient && !$hasFacilities): ?>              
                                        <input type="hidden" id="site-clientid" value="<?php echo $_SESSION['halal']['id']; ?>" data-clientname="<?php echo $myuser->userdata['name']," (",$myuser->userdata['prefix'],$myuser->userdata['id'],")"; ?>"/>
                                    <?php endif;?>

                                    <?php if (!$isClient || $hasFacilities): ?>
                                        <div class="form-inline">
                                            <div class="form-group">
                                                <label><?php if ($isClient): ?> Facilities <?php else: ?> Clients <?php endif; ?> &nbsp;&nbsp;</label>
                                                <select class="form-control clientslist" id="site-clientid">
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
                                            </div>
                                        </div>
                                    <?php endif;?> 
                                </div>
                            </div>    
                        </div>
                    </div>   
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <!-- Select Client Warning -->
                        <div id="selectClient" class="alert alert-warning" style="font-size: 18px; margin-top: 15px; display: none;">Please select a client from the dropdown above.</div>
                        
                        <!-- PAGE CONTENT BEGINS -->
                        <div id="gridContainer" style="display: none; width: 100%;">
                            <table id="siteGrid"></table>
                            <div id="sitePager"></div>
                        </div>
                        <!-- PAGE CONTENT ENDS -->
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.page-content -->
        </div>
    </div><!-- /.main-content -->
</div>
<?php include_once('pages/footer.php');?>
</div><!-- /.main-container -->

<!-- Production Sites Modal -->
<div class="modal fade" id="siteModal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="siteModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="siteModal-label">Add Production Site</h4>
            </div>
            <div class="modal-body">
                <form id="site-form">
                    <input type="hidden" id="site-stid" value="0"/>
                    
                    <table class="table table-bordered" style="background-color: #f9f9f2;">
                        <tbody>
                            <tr>
                                <td style="width: 120px; vertical-align: middle;"><b>Name:</b></td>
                                <td>
                                    <input type="text" class="form-control" id="site-name" maxlength="255" required/>
                                    <div class="alert-string"></div>
                                </td>
                                <td colspan="2" style="vertical-align: middle; color: #666;">Company, branch or person name</td>
                            </tr>
                            <tr>
                                <td style="vertical-align: middle;"><b>Street:</b></td>
                                <td>
                                    <input type="text" class="form-control" id="site-street" maxlength="255"/>
                                    <div class="alert-string"></div>
                                </td>
                                <td style="width: 120px; vertical-align: middle;"><b>Telephone:</b></td>
                                <td>
                                    <input type="text" class="form-control" id="site-telephone" maxlength="50"/>
                                    <div class="alert-string"></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: middle;"><b>Zip code:</b></td>
                                <td>
                                    <input type="text" class="form-control" id="site-zipcode" maxlength="20"/>
                                    <div class="alert-string"></div>
                                </td>
                                <td style="vertical-align: middle;"><b>Email:</b></td>
                                <td>
                                    <input type="email" class="form-control" id="site-email" maxlength="255"/>
                                    <div class="alert-string"></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: middle;"><b>City:</b></td>
                                <td>
                                    <input type="text" class="form-control" id="site-city" maxlength="100"/>
                                    <div class="alert-string"></div>
                                </td>
                                <td style="vertical-align: middle;"><b>Country:</b></td>
                                <td>
                                    <select class="form-control" id="site-country">
                                        <option value="">Select country</option>
                                        <option value="Afghanistan">Afghanistan</option>
                                        <option value="Albania">Albania</option>
                                        <option value="Algeria">Algeria</option>
                                        <option value="Argentina">Argentina</option>
                                        <option value="Australia">Australia</option>
                                        <option value="Austria">Austria</option>
                                        <option value="Bangladesh">Bangladesh</option>
                                        <option value="Belgium">Belgium</option>
                                        <option value="Brazil">Brazil</option>
                                        <option value="Canada">Canada</option>
                                        <option value="China">China</option>
                                        <option value="Czech Republic">Czech Republic</option>
                                        <option value="Denmark">Denmark</option>
                                        <option value="Egypt">Egypt</option>
                                        <option value="Finland">Finland</option>
                                        <option value="France">France</option>
                                        <option value="Germany">Germany</option>
                                        <option value="Greece">Greece</option>
                                        <option value="Hungary">Hungary</option>
                                        <option value="India">India</option>
                                        <option value="Indonesia">Indonesia</option>
                                        <option value="Iran">Iran</option>
                                        <option value="Iraq">Iraq</option>
                                        <option value="Ireland">Ireland</option>
                                        <option value="Italy">Italy</option>
                                        <option value="Japan">Japan</option>
                                        <option value="Jordan">Jordan</option>
                                        <option value="Kuwait">Kuwait</option>
                                        <option value="Lebanon">Lebanon</option>
                                        <option value="Malaysia">Malaysia</option>
                                        <option value="Mexico">Mexico</option>
                                        <option value="Morocco">Morocco</option>
                                        <option value="Netherlands">Netherlands</option>
                                        <option value="New Zealand">New Zealand</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Norway">Norway</option>
                                        <option value="Pakistan">Pakistan</option>
                                        <option value="Philippines">Philippines</option>
                                        <option value="Poland">Poland</option>
                                        <option value="Portugal">Portugal</option>
                                        <option value="Qatar">Qatar</option>
                                        <option value="Romania">Romania</option>
                                        <option value="Russia">Russia</option>
                                        <option value="Saudi Arabia">Saudi Arabia</option>
                                        <option value="Singapore">Singapore</option>
                                        <option value="Slovakia">Slovakia</option>
                                        <option value="South Africa">South Africa</option>
                                        <option value="South Korea">South Korea</option>
                                        <option value="Spain">Spain</option>
                                        <option value="Sweden">Sweden</option>
                                        <option value="Switzerland">Switzerland</option>
                                        <option value="Thailand">Thailand</option>
                                        <option value="Turkey">Turkey</option>
                                        <option value="UAE">UAE</option>
                                        <option value="UK">UK</option>
                                        <option value="Ukraine">Ukraine</option>
                                        <option value="USA">USA</option>
                                        <option value="Vietnam">Vietnam</option>
                                    </select>
                                    <div class="alert-string"></div>
                                </td>
                            </tr>
                            <tr>
                                <td style="vertical-align: middle;"><b>Status:</b></td>
                                <td colspan="3">
                                    <select class="form-control" id="site-status" style="width: 200px;">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <div class="alert-string"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="ProductionSites.onSave();">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- page specific plugin scripts -->
<script src="js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/free-jqgrid/4.15.5/jquery.jqgrid.min.js"></script>
<script src="js/grid.locale-en.js"></script>

<!-- ace scripts -->
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>

<script>
var userId = <?php echo $_SESSION['halal']['id'] ?>;
var isAdmin = <?php echo $isAdmin ? 'true' : 'false' ?>;

// Production Sites Module
var ProductionSites = {
    
    init: function() {
        var self = this;
        
        // Initialize grid on page load if client is selected
        var clientId = $("#site-clientid").val();
        if (clientId && clientId != "-1") {
            $("#selectClient").hide();
            $("#gridContainer").show();
            self.initGrid();
        } else {
            $("#selectClient").show();
            $("#gridContainer").hide();
        }
        
        // Re-initialize grid when client changes
        $("#site-clientid").change(function() {
            var clientId = $(this).val();
            if (clientId && clientId != "-1") {
                $("#selectClient").hide();
                $("#gridContainer").show();
                self.initGrid();
            } else {
                $("#selectClient").show();
                $("#gridContainer").hide();
            }
        });
    },
    
    initGrid: function() {
        var self = this;
        var clientId = $("#site-clientid").val();
        
        // Destroy existing grid if it exists
        if ($("#siteGrid").jqGrid) {
            try {
                $("#siteGrid").jqGrid('GridUnload');
            } catch(e) {}
        }
        
        // Calculate grid height
        //var h = (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight) - 300;
        
        $("#siteGrid").jqGrid({
            url: 'ajax/getList.php?clid=' + clientId,
            datatype: 'json',
            mtype: 'POST',
            width: $("#siteGrid").parent().width(),
          //  height: h,
            
            colModel: [
                { 
                    name: 'stid', 
                    index: 'stid', 
                    label: 'ID',
                    align: 'left', 
                    hidden: true, 
                    key: true
                },
                { 
                    name: 'site_name', 
                    index: 'site_name', 
                    label: 'Name',
                    width: 200, 
                    align: 'left',
                    sortable: true
                },
                { 
                    name: 'city', 
                    index: 'city', 
                    label: 'City',
                    width: 150, 
                    align: 'left',
                    sortable: true
                },
                { 
                    name: 'country', 
                    index: 'country', 
                    label: 'Country',
                    width: 150, 
                    align: 'left',
                    sortable: true
                },
                { 
                    name: 'email', 
                    index: 'email', 
                    label: 'Email',
                    width: 200, 
                    align: 'left',
                    sortable: true
                },
                { 
                    name: 'status', 
                    index: 'status', 
                    label: 'Status',
                    width: 100, 
                    align: 'center',
                    sortable: true,
                    stype: 'select',
                    searchoptions: { value: ':[All];active:Active;inactive:Inactive' },
                    formatter: function(cellValue, options, rowObject) {
                        if (cellValue == 'active') {
                            return '<span class="site-status-active">Active</span>';
                        } else {
                            return '<span class="site-status-inactive">Inactive</span>';
                        }
                    }
                },
                { 
                    name: 'inserted_on', 
                    index: 'inserted_on', 
                    label: 'Created On',
                    width: 120, 
                    align: 'center',
                    sortable: true,
                    search: false
                }
            ],
            
            pager: '#sitePager',
            rowNum: 20,
            rowList: [20, 60, 100, 500],
            sortname: 'site_name',
            sortorder: 'asc',
            viewrecords: true,
            shrinkToFit: false,
            toppager: true,
            hoverrows: true,
            gridview: true,
            
            gridComplete: function() {
                // Update pager icons if Common is available
                if (typeof Common !== 'undefined' && typeof Common.updatePagerIcons === 'function') {
                    Common.updatePagerIcons(this);
                }
            },
            loadError: function(xhr, status, error) {
                console.error('Grid load error:', error);
            }
        });
        
        // Add navigation buttons with clone to top
        $("#siteGrid").jqGrid('navGrid', '#sitePager', {
            cloneToTop: true,
            edit: true,
            add: true,
            del: true,
            search: false,
            refresh: true,
            view: false,
            addfunc: function() {
                ProductionSites.add();
            },
            editfunc: function() {
                var selRow = $("#siteGrid").jqGrid('getGridParam', 'selrow');
                if (selRow) {
                    ProductionSites.edit(selRow);
                } else {
                    alert('Please select a record');
                }
            },
            delfunc: function() {
                var selRow = $("#siteGrid").jqGrid('getGridParam', 'selrow');
                if (selRow) {
                    var siteName = $("#siteGrid").jqGrid('getCell', selRow, 'site_name');
                    ProductionSites.delete(selRow, siteName);
                } else {
                    alert('Please select a record');
                }
            }
        });
        
        // Add filter toolbar
        $("#siteGrid").jqGrid('filterToolbar', {
            enableClear: false,
            searchOnEnter: false
        });
        
        /*
        // Add Export to Excel button - bottom pager

        $("#siteGrid").navButtonAdd('#sitePager', {
            caption: '',
            title: 'Export to Excel',
            buttonicon: 'ace-icon fa fa-file-excel-o',
            onClickButton: function() {
                ProductionSites.exportToExcel();
            }
        });
        
        // Add Export to Excel button - top pager
        $("#siteGrid").navButtonAdd('#siteGrid_toppager', {
            caption: '',
            title: 'Export to Excel',
            buttonicon: 'ace-icon fa fa-file-excel-o',
            onClickButton: function() {
                ProductionSites.exportToExcel();
            }
        });
        */
        
        // Make grid responsive
        $(window).on('resize', function() {
            var width = $('#siteGrid').closest('.ui-jqgrid').parent().width();
            $('#siteGrid').jqGrid('setGridWidth', width);
        });
    },
    
    exportToExcel: function() {
        var clientId = $("#site-clientid").val();
        if (!clientId || clientId == "-1") {
            alert("Please select a client first.");
            return;
        }
        window.location.href = 'ajax/exportExcel.php?clid=' + clientId;
    },
    
    add: function() {
        var clientId = $("#site-clientid").val();
        if (!clientId || clientId == "-1") {
            alert("Please select a client first.");
            return;
        }
        
        // Reset form
        $("#site-form")[0].reset();
        $("#site-stid").val(0);
        $("#site-name").val('');
        $("#site-street").val('');
        $("#site-telephone").val('');
        $("#site-zipcode").val('');
        $("#site-email").val('');
        $("#site-city").val('');
        $("#site-country").val('');
        $("#site-status").val('active');
        
        // Update modal title
        $("#siteModal-label").text("Add Production Site");
        
        // Show modal
        $("#siteModal").modal("show");
    },
    
    edit: function(stid) {
        var self = this;
        var clientId = $("#site-clientid").val();
        
        // Fetch site data
        $.ajax({
            url: 'ajax/getSite.php',
            type: 'POST',
            data: {
                stid: stid,
                clid: clientId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var site = response.data;
                    
                    // Populate form
                    $("#site-stid").val(site.stid);
                    $("#site-name").val(site.site_name);
                    $("#site-street").val(site.street || '');
                    $("#site-telephone").val(site.telephone || '');
                    $("#site-zipcode").val(site.zipcode || '');
                    $("#site-email").val(site.email || '');
                    $("#site-city").val(site.city || '');
                    $("#site-country").val(site.country || '');
                    $("#site-status").val(site.status);
                    
                    // Update modal title
                    $("#siteModal-label").text("Edit Production Site");
                    
                    // Show modal
                    $("#siteModal").modal("show");
                } else {
                    if (typeof Utils !== 'undefined' && typeof Utils.notify === 'function') {
                        Utils.notify('error', response.message);
                    } else {
                        alert("Error loading site: " + response.message);
                    }
                }
            },
            error: function() {
                alert("Error loading site data.");
            }
        });
    },
    
    onSave: function() {
        var self = this;
        
        // Validate required fields
        var siteName = $.trim($("#site-name").val());
        
        if (!siteName) {
            if (typeof Utils !== 'undefined' && typeof Utils.notifyInput === 'function') {
                Utils.notifyInput($("#site-name"), "Name is required");
            } else {
                alert("Name is required.");
            }
            $("#site-name").focus();
            return;
        }
        
        var clientId = $("#site-clientid").val();
        var stid = $("#site-stid").val();
        
        // Save data
        $.ajax({
            url: 'ajax/saveSite.php',
            type: 'POST',
            data: {
                stid: stid,
                clid: clientId,
                site_name: siteName,
                street: $.trim($("#site-street").val()),
                telephone: $.trim($("#site-telephone").val()),
                zipcode: $.trim($("#site-zipcode").val()),
                email: $.trim($("#site-email").val()),
                city: $.trim($("#site-city").val()),
                country: $("#site-country").val(),
                status: $("#site-status").val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $("#siteModal").modal("hide");
                    $("#siteGrid").trigger("reloadGrid");
                    if (typeof Utils !== 'undefined' && typeof Utils.notify === 'function') {
                        Utils.notify('success', response.message);
                    } else {
                        alert(response.message);
                    }
                } else {
                    if (typeof Utils !== 'undefined' && typeof Utils.notify === 'function') {
                        Utils.notify('error', response.message);
                    } else {
                        alert("Error: " + response.message);
                    }
                }
            },
            error: function() {
                alert("Error saving site.");
            }
        });
    },
    
    delete: function(stid, siteName) {
        if (confirm('Delete record "' + siteName + '"?')) {
            this.confirmDelete(stid);
        }
    },
    
    confirmDelete: function(stid) {
        var self = this;
        var clientId = $("#site-clientid").val();
        
        $.ajax({
            url: 'ajax/deleteSite.php',
            type: 'POST',
            data: {
                stid: stid,
                clid: clientId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $("#siteGrid").trigger("reloadGrid");
                    if (typeof Utils !== 'undefined' && typeof Utils.notify === 'function') {
                        Utils.notify('success', response.message);
                    } else {
                        alert(response.message);
                    }
                } else {
                    if (typeof Utils !== 'undefined' && typeof Utils.notify === 'function') {
                        Utils.notify('error', response.message);
                    } else {
                        alert("Error: " + response.message);
                    }
                }
            },
            error: function() {
                alert("Error deleting site.");
            }
        });
    },
    
    escapeHtml: function(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
};

// Initialize on document ready
$(document).ready(function() {
    ProductionSites.init();
    
    // Initialize common functions if available
    if (typeof Common !== 'undefined' && typeof Common.onDocumentReady === 'function') {
        Common.onDocumentReady();
    }
});
</script>

</body>
</html>