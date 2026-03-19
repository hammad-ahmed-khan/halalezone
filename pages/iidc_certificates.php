<?php
/**
 * Certificates Management Page
 * Recreated from iidc/admin/certificates.inc.php
 * Using main project code libraries
 */

// Include main project dependencies
include_once('pages/header.php');
include_once('includes/func.php');

error_reporting(E_ALL);
ini_set('display_errors', true);

// Database connection using main project's singleton pattern
try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
    exit;
}

// User authentication and data
$myuser = cuser::singleton();
$myuser->getUserData();

$isAdmin = $myuser->userdata['isclient'] == "0";
$isAuditor = $myuser->userdata['isclient'] == "2";
$isClient = $myuser->userdata['isclient'] == "1";

// Only admin can access this page
if (!$isAdmin) {
    header('HTTP/1.0 403 Forbidden');
    echo '<div class="alert alert-danger">Access Denied. Admin privileges required.</div>';
    exit;
}

// Certificate types definition
$cert_types = [
    'a' => 'HA: Slaughtering Certificate',
    'b' => 'HB: None meat Certificate',
    'sa' => 'SA: Slaughtering Certificate (Saudi Arabia only)',
    'sb' => 'SB: None meat Certificate (Saudi Arabia only)'
];

// Get certificate type from request
$tp = isset($_GET['tp']) ? $_GET['tp'] : 'a';
if (!array_key_exists($tp, $cert_types)) {
    $tp = 'a';
}

// Get office ID from request
$offid = isset($_GET['offid']) ? $_GET['offid'] : '';

// Fetch offices list
$offices = [];
$offIds = [];
$sql = "SELECT * FROM offices WHERE status != 'deleted' ORDER BY office_name";
$stmt = $dbo->prepare($sql);
$stmt->execute();
$officesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($officesResult as $office) {
    $offices[$office['offid']] = $office;
    $offIds[$office['offid']] = $office['offid'];
}

// Fetch countries for dropdown
$countries = [];
/*
$sql = "SELECT code, name FROM countries ORDER BY name";
$stmt = $dbo->prepare($sql);
if ($stmt->execute()) {
    $countriesResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($countriesResult as $country) {
        $countries[$country['code']] = $country['name'];
    }
}
    */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Certificates - Halal e-Zone</title>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.8/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <style>
        #fixDocNrDiv {
            display: none;
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            padding: 5px;
            z-index: 1000;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .certificate-filters {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #e5e5e5;
        }
        .certificate-filters .form-group {
            margin-bottom: 10px;
        }
        .certificate-filters label {
            font-weight: 600;
            margin-right: 10px;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-authorized {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .status-printed {
            background-color: #d9edf7;
            color: #31708f;
        }
        .status-sent {
            background-color: #fcf8e3;
            color: #8a6d3b;
        }
        .status-arrived {
            background-color: #d9edf7;
            color: #31708f;
        }
        .action-icons i {
            cursor: pointer;
            margin-right: 8px;
            font-size: 16px;
        }
        .action-icons i:hover {
            opacity: 0.7;
        }
        .nowrap {
            white-space: nowrap;
        }
        .cert-link {
            color: #337ab7;
            text-decoration: none;
        }
        .cert-link:hover {
            text-decoration: underline;
        }
        .office-indicator {
            font-size: 12px;
            margin-top: 3px;
        }
        .office-indicator i {
            margin-right: 3px;
        }
        .alternateOn tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        .alternateOn tbody tr:hover {
            background-color: #f5f5f5;
        }
        .sub_title {
            background-color: #f5f5f5;
            font-weight: bold;
            padding: 10px;
        }
        .aunr {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include_once('pages/navigation.php'); ?>

<div class="main-container ace-save-state" id="main-container">
    <div class="main-content">
        <div class="main-content-inner">
            <div class="page-content">
                <div class="row no-gutters">
                    <div class="col-xs-12">
                        
                        <!-- Hidden input for document number editing -->
                        <div id="fixDocNrDiv">
                            <input type="hidden" id="crtId" />
                            <input type="text" id="crtDocNr" class="form-control" placeholder="Document Nr" />
                        </div>

                        <?php if (!isset($_GET['offid'])): ?>
                        <h2 style="text-align:center"><?php echo htmlspecialchars($cert_types[$tp]); ?></h2>
                        <?php endif; ?>

                        <!-- Office Selection -->
                        <div class="certificate-filters">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="officeSelect">Select Office:</label>
                                        <select id="officeSelect" class="form-control" onchange="changeOffice(this.value)">
                                            <option value="*" <?php echo ($offid == '*') ? 'selected' : ''; ?>>All offices</option>
                                            <?php foreach ($offices as $office): ?>
                                            <option value="<?php echo $office['offid']; ?>" <?php echo ($offid == $office['offid']) ? 'selected' : ''; ?>>
                                                <?php echo isset($countries[$office['office_country']]) ? $countries[$office['office_country']] . ' - ' : ''; ?>
                                                <?php echo htmlspecialchars($office['office_name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="yearSelect">Year:</label>
                                        <select id="yearSelect" class="form-control" onchange="loadCertificates()">
                                            <?php 
                                            $currentYear = date('Y');
                                            for ($y = $currentYear; $y >= $currentYear - 5; $y--): 
                                            ?>
                                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                            <?php endfor; ?>
                                            <option value="d2d">Date Range</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="countryFilter">Filter by Country:</label>
                                        <select id="countryFilter" class="form-control" onchange="loadCertificates()">
                                            <option value="">All Countries</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <?php if (!empty($offid) && $offid != '*'): ?>
                                        <button type="button" class="btn btn-success btn-block" onclick="issueCertificate()">
                                            <i class="fas fa-plus"></i> Issue Certificate
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Date Range (hidden by default) -->
                            <div class="row" id="dateRangeRow" style="display:none;">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fromDate">From Date:</label>
                                        <input type="date" id="fromDate" class="form-control" onchange="loadCertificates()" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="toDate">To Date:</label>
                                        <input type="date" id="toDate" class="form-control" onchange="loadCertificates()" />
                                    </div>
                                </div>
                            </div>

                            <!-- Search -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="searchField">Search By:</label>
                                        <select id="searchField" class="form-control">
                                            <option value="certificate_nr">Certificate Nr</option>
                                            <option value="doc_nr">Document Nr</option>
                                            <option value="company_name">Company Name</option>
                                            <option value="reference">Reference</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="searchQuery">Search:</label>
                                        <input type="text" id="searchQuery" class="form-control" placeholder="Enter search term..." />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-primary btn-block" onclick="loadCertificates()">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-default btn-block" onclick="resetFilters()">
                                            <i class="fas fa-undo"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($offid)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Please select an office to view certificates.
                        </div>
                        <?php else: ?>
                        
                        <!-- Certificates Table -->
                        <div class="table-responsive">
                            <table id="certificatesTable" class="table table-bordered table-striped alternateOn" style="width:100%">
                                <thead>
                                    <tr class="sub_title">
                                        <th colspan="9">
                                            <b>Issued Shipment <span style="color:red">CERTIFICATES</span></b>
                                            <span id="totalRecords" class="badge" style="margin-left:10px;"></span>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th style="width:50px">No.</th>
                                        <th>Certificate Nr</th>
                                        <th>Issue Date</th>
                                        <th>Weight</th>
                                        <th>Importer</th>
                                        <th>Company</th>
                                        <th>Reference</th>
                                        <th>Status</th>
                                        <th style="width:100px">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="certificatesBody">
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <i class="fas fa-spinner fa-spin"></i> Loading certificates...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-inline">
                                    <label>Show</label>
                                    <select id="pageSize" class="form-control" onchange="loadCertificates()">
                                        <option value="25">25</option>
                                        <option value="50" selected>50</option>
                                        <option value="100">100</option>
                                        <option value="200">200</option>
                                    </select>
                                    <label>entries</label>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <nav aria-label="Certificate pagination">
                                    <ul class="pagination" id="pagination">
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Dialog Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="confirmModalTitle">Confirm Action</h4>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage">Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmModalYes">Yes</button>
            </div>
        </div>
    </div>
</div>

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <p id="alertModalMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<?php include_once('pages/footer.php'); ?>

<!-- Scripts -->
<script src="js/jquery-2.1.4.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
<script src="js/ace-elements.min.js"></script>
<script src="js/ace.min.js"></script>

<script>
// Global variables
var currentPage = 0;
var totalPages = 0;
var certificateType = '<?php echo $tp; ?>';
var currentOffice = '<?php echo $offid; ?>';

// Document ready
$(document).ready(function() {
    // Set page title
    document.title = "Issued Shipment Certificates - Halal e-Zone";
    
    // Load certificates if office is selected
    if (currentOffice !== '') {
        loadCertificates();
        loadCountries();
    }
    
    // Year select change handler
    $('#yearSelect').change(function() {
        if ($(this).val() === 'd2d') {
            $('#dateRangeRow').show();
        } else {
            $('#dateRangeRow').hide();
        }
    });
    
    // Search on Enter key
    $('#searchQuery').keypress(function(e) {
        if (e.which === 13) {
            loadCertificates();
        }
    });
    
    // Document number edit on Enter
    $('#crtDocNr').keypress(function(e) {
        if (e.which === 13) {
            fixCerNr($('#crtId').val(), $(this).val());
        }
    });
    
    // Click outside to close doc nr edit
    $(document).click(function(e) {
        if (!$(e.target).closest('#fixDocNrDiv, .crtDocNr').length) {
            $('#fixDocNrDiv').hide();
        }
    });
});

// Change office
function changeOffice(offid) {
    window.location.href = '?inc=certificates&tp=' + certificateType + '&offid=' + offid;
}

// Issue new certificate
function issueCertificate() {
    window.location.href = '/certificates/?inc=certificate_ab&tp=' + certificateType + '&offid=' + currentOffice;
}

// Load countries for filter
function loadCountries() {
    var year = $('#yearSelect').val();
    $.ajax({
        url: 'ajax/load_certificates.php',
        type: 'POST',
        data: {
            act: 'load_countries',
            tp: certificateType,
            offid: currentOffice,
            year: year
        },
        success: function(response) {
            $('#countryFilter').html('<option value="">All Countries</option>' + response);
        }
    });
}

// Load certificates
function loadCertificates(page) {
    if (typeof page === 'undefined') {
        page = 0;
    }
    currentPage = page;
    
    var year = $('#yearSelect').val();
    var pageSize = $('#pageSize').val();
    var searchField = $('#searchField').val();
    var searchQuery = $('#searchQuery').val();
    var country = $('#countryFilter').val();
    var fromDate = $('#fromDate').val();
    var toDate = $('#toDate').val();
    var orderBy = 'nr';
    var ascDsc = 'DESC';
    
    $('#certificatesBody').html('<tr><td colspan="9" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading certificates...</td></tr>');
    
    $.ajax({
        url: 'ajax/load_certificates.php',
        type: 'POST',
        data: {
            tp: certificateType,
            offid: currentOffice,
            year: year,
            st: page * pageSize,
            lmt: pageSize,
            searchField: searchField,
            srearchQ: searchQuery,
            country: country,
            fromDate: fromDate,
            toDate: toDate,
            orderBy: orderBy,
            ascDsc: ascDsc
        },
        success: function(response) {
            if (response.error) {
                $('#certificatesBody').html('<tr><td colspan="9" class="text-center text-danger">' + response.error + '</td></tr>');
            } else {
                $('#certificatesBody').html(response.html);
                $('#totalRecords').text(response.total + ' records');
                totalPages = Math.ceil(response.total / pageSize);
                updatePagination();
            }
        },
        error: function() {
            $('#certificatesBody').html('<tr><td colspan="9" class="text-center text-danger">Error loading certificates</td></tr>');
        }
    });
}

// Update pagination
function updatePagination() {
    var html = '';
    var startPage = Math.max(0, currentPage - 2);
    var endPage = Math.min(totalPages - 1, currentPage + 2);
    
    if (currentPage > 0) {
        html += '<li><a href="#" onclick="loadCertificates(0); return false;">&laquo;</a></li>';
        html += '<li><a href="#" onclick="loadCertificates(' + (currentPage - 1) + '); return false;">&lsaquo;</a></li>';
    }
    
    for (var i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            html += '<li class="active"><span>' + (i + 1) + '</span></li>';
        } else {
            html += '<li><a href="#" onclick="loadCertificates(' + i + '); return false;">' + (i + 1) + '</a></li>';
        }
    }
    
    if (currentPage < totalPages - 1) {
        html += '<li><a href="#" onclick="loadCertificates(' + (currentPage + 1) + '); return false;">&rsaquo;</a></li>';
        html += '<li><a href="#" onclick="loadCertificates(' + (totalPages - 1) + '); return false;">&raquo;</a></li>';
    }
    
    $('#pagination').html(html);
}

// Reset filters
function resetFilters() {
    $('#yearSelect').val(new Date().getFullYear());
    $('#dateRangeRow').hide();
    $('#fromDate').val('');
    $('#toDate').val('');
    $('#searchField').val('certificate_nr');
    $('#searchQuery').val('');
    $('#countryFilter').val('');
    loadCertificates();
}

// Delete certificate
function delcer(nr, tp) {
    showConfirm('Are you sure you want to delete this certificate?', function() {
        $.ajax({
            url: 'ajax/admin_save.php',
            type: 'POST',
            data: {
                act: 'delcer',
                nr: nr,
                tp: tp
            },
            success: function(response) {
                if (response.trim() === 'success') {
                    $('tr[data-nr="' + nr + '"]').fadeOut(300, function() {
                        $(this).remove();
                    });
                    showAlert('Certificate deleted successfully.');
                } else {
                    showAlert(response);
                }
            },
            error: function() {
                showAlert('Error deleting certificate.');
            }
        });
    });
}

// Undo print/authorize
function undoCer(nr, tp, obj) {
    showConfirm('Are you sure you want to undo print/authorize?', function() {
        $.ajax({
            url: 'ajax/certificates_save.php',
            type: 'POST',
            data: {
                act: 'undoCer',
                nr: nr,
                tp: tp
            },
            success: function(response) {
                if (response.trim() === 'success') {
                    loadCertificates(currentPage);
                    showAlert('Action undone successfully.');
                } else {
                    showAlert(response);
                }
            },
            error: function() {
                showAlert('Error undoing action.');
            }
        });
    });
}

// Mark as bad/good certificate
function badCer(goodBad, nr) {
    showConfirm('Are you sure?', function() {
        $.ajax({
            url: 'ajax/admin_save.php',
            type: 'POST',
            data: {
                act: 'badCer',
                tp: certificateType,
                nr: nr,
                goodBad: goodBad
            },
            success: function(response) {
                if (response.indexOf('success') > -1) {
                    loadCertificates(currentPage);
                } else {
                    showAlert(response);
                }
            },
            error: function() {
                showAlert('Error updating certificate status.');
            }
        });
    });
}

// Fix document number
function fixCerNr(nr, doc_nr) {
    $.ajax({
        url: 'ajax/certificates_save.php',
        type: 'POST',
        data: {
            act: 'fixCerNr',
            tp: certificateType,
            nr: nr,
            doc_nr: doc_nr
        },
        success: function(response) {
            if (response.indexOf('success') > -1) {
                $('#fixDocNrDiv').hide();
                loadCertificates(currentPage);
            } else {
                showAlert(response);
            }
        },
        error: function() {
            showAlert('Error updating document number.');
        }
    });
}

// Show edit document number input
function showDocNrEdit(element) {
    var position = $(element).offset();
    var docNr = $(element).attr('data-crtDocNr');
    var crtNr = $(element).closest('tr').attr('data-nr');
    
    $('#crtId').val(crtNr);
    $('#crtDocNr').val(docNr).css('width', $(element).width() + 20);
    $('#fixDocNrDiv').css({
        left: position.left,
        top: position.top,
        display: 'block'
    });
    $('#crtDocNr').focus();
}

// Show confirm dialog
function showConfirm(message, callback) {
    $('#confirmModalMessage').text(message);
    $('#confirmModalYes').off('click').on('click', function() {
        $('#confirmModal').modal('hide');
        if (typeof callback === 'function') {
            callback();
        }
    });
    $('#confirmModal').modal('show');
}

// Show alert dialog
function showAlert(message) {
    $('#alertModalMessage').text(message);
    $('#alertModal').modal('show');
}

// Export to Excel
function exportToExcel() {
    var year = $('#yearSelect').val();
    var searchField = $('#searchField').val();
    var searchQuery = $('#searchQuery').val();
    var country = $('#countryFilter').val();
    
    var form = $('<form>', {
        'action': 'ajax/export_certificates.php',
        'method': 'POST',
        'target': '_blank'
    }).append(
        $('<input>', {'type': 'hidden', 'name': 'tp', 'value': certificateType}),
        $('<input>', {'type': 'hidden', 'name': 'offid', 'value': currentOffice}),
        $('<input>', {'type': 'hidden', 'name': 'year', 'value': year}),
        $('<input>', {'type': 'hidden', 'name': 'searchField', 'value': searchField}),
        $('<input>', {'type': 'hidden', 'name': 'srearchQ', 'value': searchQuery}),
        $('<input>', {'type': 'hidden', 'name': 'country', 'value': country}),
        $('<input>', {'type': 'hidden', 'name': 'exportExcel', 'value': 'yes'})
    );
    
    $('body').append(form);
    form.submit();
    form.remove();
}
</script>
</body>
</html>
