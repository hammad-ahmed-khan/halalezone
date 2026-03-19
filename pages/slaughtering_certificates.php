<?php
/**
 * Slaughtering Certificates Management Page
 * 
 * This page manages IIDC slaughtering (shipment) certificates using jqGrid
 * Migrated from IIDC project to halal-ezone
 */

// Ensure user is logged in
if (!isset($_SESSION["halal"]["id"])) {
    header("location: /login");
    exit();
}

$user_type = $_SESSION["halal"]["user_type"] ?? 'client';
$user_id = $_SESSION["halal"]["id"];
$is_admin = in_array($user_type, ['admin', 'superadmin', 'hqc_office']);

// Get offices for filter dropdown
$offices = [];
$officesQuery = $db->query("SELECT offid, office_name, office_country FROM offices WHERE status != 'deleted' ORDER BY office_name");
if ($officesQuery) {
    while ($row = $officesQuery->fetch_assoc()) {
        $offices[] = $row;
    }
}

// Get certificate type from URL (a = meat/slaughtering, b = non-meat)
$cert_type = isset($_GET['type']) && in_array($_GET['type'], ['a', 'b', 'sa', 'sb']) ? $_GET['type'] : 'a';
$cert_type_labels = [
    'a' => 'Slaughtering Certificate (Meat)',
    'b' => 'Non-Meat Certificate',
    'sa' => 'Slaughtering Certificate (Saudi Arabia)',
    'sb' => 'Non-Meat Certificate (Saudi Arabia)'
];
$page_title = $cert_type_labels[$cert_type] ?? 'Slaughtering Certificates';

// Get importers (companies) for dropdown
$importers = [];
$importersQuery = $db->query("SELECT clid, company_name, country1 FROM companies WHERE status != 'deleted' ORDER BY company_name");
if ($importersQuery) {
    while ($row = $importersQuery->fetch_assoc()) {
        $importers[] = $row;
    }
}
?>

<style>
.certificate-filters {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 15px;
}
.certificate-filters .form-group {
    margin-bottom: 10px;
}
.certificate-filters label {
    font-weight: 600;
    margin-bottom: 5px;
}
.status-sent { color: #28a745; font-weight: bold; }
.status-printed { color: #17a2b8; font-weight: bold; }
.status-arrived { color: #6c757d; }
.status-pending { color: #ffc107; font-weight: bold; }
.certificate-link { color: #007bff; text-decoration: none; }
.certificate-link:hover { text-decoration: underline; }
.office-badge { 
    display: inline-block; 
    padding: 2px 6px; 
    border-radius: 3px; 
    font-size: 11px; 
    margin-top: 3px;
}
.office-badge.client-of { background: #fff3cd; color: #856404; }
.office-badge.issued-by { background: #d4edda; color: #155724; }
.btn-issue-cert {
    background-color: #4a6741;
    border-color: #4a6741;
    color: white;
}
.btn-issue-cert:hover {
    background-color: #3d5636;
    border-color: #3d5636;
    color: white;
}
.btn-export {
    background-color: #6c757d;
    border-color: #6c757d;
}
.action-icons a, .action-icons button {
    margin: 0 2px;
    padding: 3px 6px;
}
.action-icons i { font-size: 14px; }
#certificatesGrid { width: 100% !important; }
.ui-jqgrid-htable th { background: #f5f5f5; }
.modal-lg { max-width: 900px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-certificate"></i> <?php echo $page_title; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/">Home</a></li>
                        <li class="breadcrumb-item">Certificates</li>
                        <li class="breadcrumb-item active">Slaughtering</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <select id="filterOffice" class="form-control">
                                <option value="">All Offices</option>
                                <?php foreach ($offices as $office): ?>
                                <option value="<?php echo $office['offid']; ?>">
                                    <?php echo htmlspecialchars($office['office_country'] . ' - ' . $office['office_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <?php if ($is_admin): ?>
                            <button type="button" class="btn btn-issue-cert" onclick="SLAUGHTER_CERT.issueCertificate()">
                                <i class="fas fa-plus"></i> Issue Certificate
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-export" onclick="SLAUGHTER_CERT.exportToExcel()">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filters Row -->
                    <div class="certificate-filters">
                        <div class="row">
                            <div class="col-md-2">
                                <label>Year</label>
                                <select id="filterYear" class="form-control form-control-sm">
                                    <?php 
                                    $currentYear = date('Y');
                                    for ($y = $currentYear; $y >= $currentYear - 5; $y--): 
                                    ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Search</label>
                                <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="Search Certificates">
                            </div>
                            <div class="col-md-2">
                                <label>Search By</label>
                                <select id="filterSearchBy" class="form-control form-control-sm">
                                    <option value="certificate_nr">Certificate Nr</option>
                                    <option value="company_name">Company</option>
                                    <option value="importer">Importer</option>
                                    <option value="reference">Reference</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Status</label>
                                <select id="filterStatus" class="form-control form-control-sm">
                                    <option value="">All</option>
                                    <option value="new">New/Draft</option>
                                    <option value="printed">Printed</option>
                                    <option value="sent">Sent</option>
                                    <option value="arrived">Arrived</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-sm btn-block" onclick="SLAUGHTER_CERT.applyFilters()">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                            <div class="col-md-1">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-secondary btn-sm btn-block" onclick="SLAUGHTER_CERT.resetFilters()">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- jqGrid Table -->
                    <table id="certificatesGrid"></table>
                    <div id="certificatesPager"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- View Certificate Modal -->
<div class="modal fade" id="viewCertificateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Certificate Details</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="viewCertificateContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="SLAUGHTER_CERT.printCertificate()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Issue/Edit Certificate Modal -->
<div class="modal fade" id="editCertificateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalTitle"><i class="fas fa-edit"></i> Issue Certificate</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="certificateForm">
                    <input type="hidden" name="nr" id="cert_nr">
                    <input type="hidden" name="cert_type" value="<?php echo $cert_type; ?>">
                    <input type="hidden" name="action" id="form_action" value="create">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Office <span class="text-danger">*</span></label>
                                <select name="offid" id="cert_offid" class="form-control" required>
                                    <option value="">Select Office</option>
                                    <?php foreach ($offices as $office): ?>
                                    <option value="<?php echo $office['offid']; ?>">
                                        <?php echo htmlspecialchars($office['office_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Client (Company) <span class="text-danger">*</span></label>
                                <select name="clid" id="cert_clid" class="form-control select2" required>
                                    <option value="">Select Company</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Issue Date <span class="text-danger">*</span></label>
                                <input type="text" name="issue_date" id="cert_issue_date" class="form-control datepicker" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Country of Origin <span class="text-danger">*</span></label>
                                <select name="country_of_origin" id="cert_country" class="form-control" required>
                                    <option value="">Select Country</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Importer <span class="text-danger">*</span></label>
                                <select name="importer" id="cert_importer" class="form-control select2" required>
                                    <option value="">Select Importer</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Exporter</label>
                                <select name="exporter" id="cert_exporter" class="form-control select2">
                                    <option value="">Select Exporter</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Quantity - Quality <span class="text-danger">*</span></label>
                                <input type="text" name="quality" id="cert_quality" class="form-control" placeholder="e.g., Fresh Halal Beef" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Gross Weight (KG) <span class="text-danger">*</span></label>
                                <input type="number" name="weight_gross" id="cert_weight_gross" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Net Weight (KG) <span class="text-danger">*</span></label>
                                <input type="number" name="weight_net" id="cert_weight_net" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Transportation Method</label>
                                <select name="transportation_method" id="cert_transport_method" class="form-control">
                                    <option value="Vessel Container">Vessel Container</option>
                                    <option value="Truck">Truck</option>
                                    <option value="Air freight">Air freight</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Transport Nr</label>
                                <input type="text" name="transportation_nr" id="cert_transport_nr" class="form-control">
                            </div>
                        </div>
                    </div>

                    <?php if (in_array($cert_type, ['a', 'sa'])): ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Slaughtering Date <span class="text-danger">*</span></label>
                                <input type="text" name="slaughtering_date" id="cert_slaughter_date" class="form-control datepicker" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Slaughter House</label>
                                <input type="text" name="slaughter_house" id="cert_slaughter_house" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Slaughtering Supervisor <span class="text-danger">*</span></label>
                                <input type="text" name="slaughterer_name" id="cert_slaughterer" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Production Date</label>
                                <input type="text" name="production_date" id="cert_production_date" class="form-control datepicker">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Expiry Date <span class="text-danger">*</span></label>
                                <input type="text" name="expiry_date" id="cert_expiry_date" class="form-control datepicker" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Health Certificate Nr</label>
                                <input type="text" name="hcd_nr" id="cert_hcd_nr" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Producer</label>
                                <textarea name="producer" id="cert_producer" class="form-control" rows="2" placeholder="Producer name and address"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Reference</label>
                                <input type="text" name="reference" id="cert_reference" class="form-control">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="SLAUGHTER_CERT.saveCertificate()">
                    <i class="fas fa-save"></i> Save
                </button>
                <?php if ($is_admin): ?>
                <button type="button" class="btn btn-primary" onclick="SLAUGHTER_CERT.saveCertificate('print')">
                    <i class="fas fa-print"></i> Save & Print
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Send Certificate Modal -->
<div class="modal fade" id="sendCertificateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-paper-plane"></i> Send Certificate</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="sendCertForm">
                    <input type="hidden" name="nr" id="send_cert_nr">
                    <input type="hidden" name="cert_type" value="<?php echo $cert_type; ?>">
                    <div class="form-group">
                        <label>Sent On Date <span class="text-danger">*</span></label>
                        <input type="text" name="sent_on" id="send_sent_on" class="form-control datepicker" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="SLAUGHTER_CERT.confirmSend()">
                    <i class="fas fa-check"></i> Confirm Send
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receive Certificate Modal -->
<div class="modal fade" id="receiveCertificateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-inbox"></i> Confirm Receipt</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="receiveCertForm">
                    <input type="hidden" name="nr" id="receive_cert_nr">
                    <input type="hidden" name="cert_type" value="<?php echo $cert_type; ?>">
                    <div class="form-group">
                        <label>Arrived On Date <span class="text-danger">*</span></label>
                        <input type="text" name="arrived_on" id="receive_arrived_on" class="form-control datepicker" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="SLAUGHTER_CERT.confirmReceive()">
                    <i class="fas fa-check"></i> Confirm Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var SLAUGHTER_CERT = (function() {
    var currentNr = null;
    var certType = '<?php echo $cert_type; ?>';
    var isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;

    // Initialize jqGrid
    function initGrid() {
        $("#certificatesGrid").jqGrid({
            url: '/ajax/getSlaughteringCertificates.php',
            datatype: 'json',
            mtype: 'POST',
            postData: {
                cert_type: certType,
                filterYear: function() { return $('#filterYear').val(); },
                filterOffice: function() { return $('#filterOffice').val(); },
                filterStatus: function() { return $('#filterStatus').val(); },
                filterSearch: function() { return $('#filterSearch').val(); },
                filterSearchBy: function() { return $('#filterSearchBy').val(); }
            },
            colNames: ['No.', 'Certificate Nr.', 'Issue Date', 'Weight', 'Importer & Country', 'Company', 'Ref.', 'Status', 'Action'],
            colModel: [
                { name: 'row_num', index: 'row_num', width: 40, align: 'center', sortable: false },
                { name: 'certificate_nr', index: 'certificate_nr', width: 130, formatter: formatCertificateNr },
                { name: 'issue_date', index: 'issue_date', width: 90, align: 'center' },
                { name: 'weight_net', index: 'weight_net', width: 80, align: 'right', formatter: formatWeight },
                { name: 'importer_info', index: 'importer_name', width: 200, formatter: formatImporter },
                { name: 'company_name', index: 'company_name', width: 200 },
                { name: 'reference', index: 'reference', width: 100 },
                { name: 'status_info', index: 'hcd_process', width: 180, formatter: formatStatus },
                { name: 'actions', index: 'actions', width: 100, align: 'center', sortable: false, formatter: formatActions }
            ],
            rowNum: 20,
            rowList: [10, 20, 50, 100],
            pager: '#certificatesPager',
            sortname: 'nr',
            sortorder: 'desc',
            viewrecords: true,
            autowidth: true,
            height: 'auto',
            jsonReader: {
                root: 'rows',
                page: 'page',
                total: 'total',
                records: 'records',
                repeatitems: false,
                id: 'nr'
            },
            loadComplete: function(data) {
                // Renumber rows based on current page
                var grid = $(this);
                var ids = grid.getDataIDs();
                var page = grid.getGridParam('page');
                var rowNum = grid.getGridParam('rowNum');
                for (var i = 0; i < ids.length; i++) {
                    grid.setCell(ids[i], 'row_num', (page - 1) * rowNum + i + 1);
                }
            }
        });

        // Responsive resize
        $(window).on('resize', function() {
            var width = $('.card-body').width();
            $('#certificatesGrid').setGridWidth(width);
        }).trigger('resize');
    }

    // Format certificate number with link and office badges
    function formatCertificateNr(cellvalue, options, rowObject) {
        var html = '<a href="javascript:void(0)" class="certificate-link" onclick="SLAUGHTER_CERT.viewCertificate(' + rowObject.nr + ')">' + 
                   (cellvalue || 'Draft') + '</a>';
        
        if (rowObject.client_office && rowObject.client_office != rowObject.tmplid) {
            html += '<br><span class="office-badge client-of"><i class="fas fa-caret-left"></i> ' + 
                    (rowObject.client_office_name || '') + '</span>';
        }
        if (rowObject.offid != rowObject.tmplid || '<?php echo $_GET['offid'] ?? ''; ?>' == '*') {
            html += '<br><span class="office-badge issued-by"><i class="fas fa-caret-right"></i> ' + 
                    (rowObject.issued_by_name || '') + '</span>';
        }
        return html;
    }

    // Format weight
    function formatWeight(cellvalue, options, rowObject) {
        if (!cellvalue) return '-';
        return parseFloat(cellvalue).toFixed(1) + ' KG';
    }

    // Format importer with country
    function formatImporter(cellvalue, options, rowObject) {
        var html = rowObject.importer_name || '';
        if (rowObject.importer_country) {
            html += '<br><span style="color:green">' + rowObject.importer_country + '</span>';
        }
        return html;
    }

    // Format status column
    function formatStatus(cellvalue, options, rowObject) {
        var html = '';
        var process = rowObject.hcd_process || '';
        
        if (process.indexOf('Sent') !== -1) {
            html += '<span class="status-sent"><b>Sent On:</b> ' + (rowObject.sent_on || '') + '</span><br>';
            if (rowObject.arrived_on) {
                html += '<span class="status-arrived"><b>Arrived On:</b> ' + rowObject.arrived_on + '</span><br>';
            }
        } else if (process.indexOf('Printed') !== -1) {
            html += '<span class="status-printed"><b>Printed On:</b> ' + (rowObject.printed_on || '') + '</span><br>';
        } else if (process.indexOf('Authorised') !== -1) {
            html += '<span class="status-printed"><b>Authorised On:</b> ' + (rowObject.authorized_on || '') + '</span><br>';
            if (rowObject.printed_on) {
                html += '<span><b>Printed On:</b> ' + rowObject.printed_on + '</span><br>';
            }
        } else {
            html += '<span class="status-pending">In Process</span><br>';
        }

        // Requested By
        if (rowObject.requested_by_name) {
            html += '<small><b>RB:</b> ' + rowObject.requested_by_name + '</small><br>';
        }
        // Handled By
        if (rowObject.handled_by_name) {
            html += '<small><b>HB:</b> ' + rowObject.handled_by_name + '</small>';
        }

        return html;
    }

    // Format action buttons
    function formatActions(cellvalue, options, rowObject) {
        var html = '<div class="action-icons">';
        
        // Edit button
        if (isAdmin) {
            html += '<a href="javascript:void(0)" title="Edit" onclick="SLAUGHTER_CERT.editCertificate(' + rowObject.nr + ')">' +
                    '<i class="fas fa-edit text-primary"></i></a>';
        }

        // QR/Print button (only if printed)
        if (rowObject.hcd_process && rowObject.hcd_process.indexOf('Printed') !== -1) {
            html += '<a href="javascript:void(0)" title="QR Code" onclick="SLAUGHTER_CERT.showQR(' + rowObject.nr + ')">' +
                    '<i class="fas fa-qrcode text-info"></i></a>';
        }

        // Send/Receive buttons based on status
        if (isAdmin && rowObject.hcd_process) {
            if (rowObject.hcd_process.indexOf('Printed') !== -1 && rowObject.hcd_process.indexOf('Sent') === -1) {
                html += '<a href="javascript:void(0)" title="Mark as Sent" onclick="SLAUGHTER_CERT.sendCertificate(' + rowObject.nr + ')">' +
                        '<i class="fas fa-paper-plane text-success"></i></a>';
            } else if (rowObject.hcd_process.indexOf('Sent') !== -1 && !rowObject.arrived_on) {
                html += '<a href="javascript:void(0)" title="Mark as Received" onclick="SLAUGHTER_CERT.receiveCertificate(' + rowObject.nr + ')">' +
                        '<i class="fas fa-inbox text-warning"></i></a>';
            }
        }

        // Delete button
        if (isAdmin) {
            html += '<a href="javascript:void(0)" title="Delete" onclick="SLAUGHTER_CERT.deleteCertificate(' + rowObject.nr + ')">' +
                    '<i class="fas fa-trash text-danger"></i></a>';
        }

        html += '</div>';
        return html;
    }

    // Apply filters
    function applyFilters() {
        $("#certificatesGrid").trigger("reloadGrid", [{page: 1}]);
    }

    // Reset filters
    function resetFilters() {
        $('#filterYear').val('<?php echo date('Y'); ?>');
        $('#filterOffice').val('');
        $('#filterStatus').val('');
        $('#filterSearch').val('');
        $('#filterSearchBy').val('certificate_nr');
        applyFilters();
    }

    // View certificate details
    function viewCertificate(nr) {
        currentNr = nr;
        $.ajax({
            url: '/ajax/slaughteringCertificatesHandler.php',
            type: 'POST',
            data: { action: 'getCertificate', nr: nr, cert_type: certType },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var cert = response.data;
                    var html = buildViewHtml(cert);
                    $('#viewCertificateContent').html(html);
                    $('#viewCertificateModal').modal('show');
                } else {
                    Utils.notify(response.message || 'Error loading certificate', 'error');
                }
            },
            error: function() {
                Utils.notify('Error loading certificate', 'error');
            }
        });
    }

    // Build view HTML
    function buildViewHtml(cert) {
        return `
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr><th width="40%">Certificate Nr</th><td><strong>${cert.certificate_nr || 'Draft'}</strong></td></tr>
                        <tr><th>Company</th><td>${cert.company_name || '-'}</td></tr>
                        <tr><th>Office</th><td>${cert.office_name || '-'}</td></tr>
                        <tr><th>Issue Date</th><td>${cert.issue_date || '-'}</td></tr>
                        <tr><th>Country of Origin</th><td>${cert.country_of_origin || '-'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-bordered">
                        <tr><th width="40%">Importer</th><td>${cert.importer_name || '-'}</td></tr>
                        <tr><th>Exporter</th><td>${cert.exporter_name || '-'}</td></tr>
                        <tr><th>Gross Weight</th><td>${cert.weight_gross ? cert.weight_gross + ' KG' : '-'}</td></tr>
                        <tr><th>Net Weight</th><td>${cert.weight_net ? cert.weight_net + ' KG' : '-'}</td></tr>
                        <tr><th>Status</th><td>${cert.hcd_process || 'In Process'}</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <table class="table table-sm table-bordered">
                        <tr><th width="20%">Quality</th><td>${cert.quality || '-'}</td></tr>
                        <tr><th>Transportation</th><td>${cert.transportation_method || '-'} ${cert.transportation_nr ? '(' + cert.transportation_nr + ')' : ''}</td></tr>
                        <tr><th>Health Cert Nr</th><td>${cert.hcd_nr || '-'}</td></tr>
                        <tr><th>Slaughtering Date</th><td>${cert.slaughtering_date || '-'}</td></tr>
                        <tr><th>Production Date</th><td>${cert.production_date || '-'}</td></tr>
                        <tr><th>Expiry Date</th><td>${cert.expiry_date || '-'}</td></tr>
                        <tr><th>Reference</th><td>${cert.reference || '-'}</td></tr>
                    </table>
                </div>
            </div>
        `;
    }

    // Issue new certificate
    function issueCertificate() {
        currentNr = null;
        $('#cert_nr').val('');
        $('#form_action').val('create');
        $('#certificateForm')[0].reset();
        $('#editModalTitle').html('<i class="fas fa-plus"></i> Issue Certificate');
        loadCompanies();
        loadCountries();
        $('#editCertificateModal').modal('show');
    }

    // Edit certificate
    function editCertificate(nr) {
        currentNr = nr;
        $.ajax({
            url: '/ajax/slaughteringCertificatesHandler.php',
            type: 'POST',
            data: { action: 'getCertificate', nr: nr, cert_type: certType },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var cert = response.data;
                    $('#cert_nr').val(cert.nr);
                    $('#form_action').val('update');
                    $('#cert_offid').val(cert.offid);
                    $('#cert_issue_date').val(cert.issue_date);
                    $('#cert_country').val(cert.country_of_origin);
                    $('#cert_quality').val(cert.quality);
                    $('#cert_weight_gross').val(cert.weight_gross);
                    $('#cert_weight_net').val(cert.weight_net);
                    $('#cert_transport_method').val(cert.transportation_method);
                    $('#cert_transport_nr').val(cert.transportation_nr);
                    $('#cert_slaughter_date').val(cert.slaughtering_date);
                    $('#cert_slaughter_house').val(cert.slaughter_house);
                    $('#cert_slaughterer').val(cert.slaughterer_name);
                    $('#cert_production_date').val(cert.production_date);
                    $('#cert_expiry_date').val(cert.expiry_date);
                    $('#cert_hcd_nr').val(cert.hcd_nr);
                    $('#cert_producer').val(cert.producer);
                    $('#cert_reference').val(cert.reference);

                    // Load and set company/importer/exporter selects
                    loadCompanies(cert.clid, cert.importer, cert.exporter);
                    loadCountries();
                    
                    $('#editModalTitle').html('<i class="fas fa-edit"></i> Edit Certificate');
                    $('#editCertificateModal').modal('show');
                } else {
                    Utils.notify(response.message || 'Error loading certificate', 'error');
                }
            }
        });
    }

    // Load companies for select2
    function loadCompanies(selectedClid, selectedImporter, selectedExporter) {
        $.ajax({
            url: '/ajax/slaughteringCertificatesHandler.php',
            type: 'POST',
            data: { action: 'getCompanies' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var companies = response.data;
                    var options = '<option value="">Select</option>';
                    companies.forEach(function(c) {
                        options += '<option value="' + c.clid + '">' + c.company_name + 
                                   (c.country1 ? ' (' + c.country1 + ')' : '') + '</option>';
                    });
                    
                    $('#cert_clid').html(options);
                    $('#cert_importer').html(options);
                    $('#cert_exporter').html(options);
                    
                    if (selectedClid) $('#cert_clid').val(selectedClid);
                    if (selectedImporter) $('#cert_importer').val(selectedImporter);
                    if (selectedExporter) $('#cert_exporter').val(selectedExporter);

                    // Initialize Select2
                    $('.select2').select2({ width: '100%', dropdownParent: $('#editCertificateModal') });
                }
            }
        });
    }

    // Load countries
    function loadCountries() {
        $.ajax({
            url: '/ajax/slaughteringCertificatesHandler.php',
            type: 'POST',
            data: { action: 'getCountries' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var countries = response.data;
                    var options = '<option value="">Select Country</option>';
                    for (var code in countries) {
                        options += '<option value="' + code + '">' + countries[code] + '</option>';
                    }
                    $('#cert_country').html(options);
                }
            }
        });
    }

    // Save certificate
    function saveCertificate(andPrint) {
        var formData = $('#certificateForm').serialize();
        if (andPrint) {
            formData += '&and_print=1';
        }

        $.ajax({
            url: '/ajax/slaughteringCertificatesHandler.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Utils.notify(response.message || 'Certificate saved', 'success');
                    $('#editCertificateModal').modal('hide');
                    applyFilters();
                    
                    if (andPrint && response.nr) {
                        printCertificateById(response.nr);
                    }
                } else {
                    Utils.notify(response.message || 'Error saving certificate', 'error');
                }
            },
            error: function() {
                Utils.notify('Error saving certificate', 'error');
            }
        });
    }

    // Delete certificate
    function deleteCertificate(nr) {
        if (!confirm('Are you sure you want to delete this certificate?')) return;

        $.ajax({
            url: '/ajax/slaughteringCertificatesHandler.php',
            type: 'POST',
            data: { action: 'delete', nr: nr, cert_type: certType },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Utils.notify('Certificate deleted', 'success');
                    applyFilters();
                } else {
                    Utils.notify(response.message || 'Error deleting certificate', 'error');
                }
            }
        });
    }

    // Send certificate modal
    function sendCertificate(nr) {
        currentNr = nr;
        $('#send_cert_nr').val(nr);
        $('#send_sent_on').val('');
        $('#sendCertificateModal').modal('show');
    }

    // Confirm send
    function confirmSend() {
        var sentOn = $('#send_sent_on').val();
        if (!sentOn) {
            Utils.notify('Please enter sent on date', 'warning');
            return;
        }

        $.ajax({
            url: '/ajax/slaughteringCertificatesHandler.php',
            type: 'POST',
            data: { 
                action: 'updateStatus', 
                nr: $('#send_cert_nr').val(), 
                cert_type: certType,
                status_type: 'sent',
                status_date: sentOn
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Utils.notify('Certificate marked as sent', 'success');
                    $('#sendCertificateModal').modal('hide');
                    applyFilters();
                } else {
                    Utils.notify(response.message || 'Error updating status', 'error');
                }
            }
        });
    }

    // Receive certificate modal
    function receiveCertificate(nr) {
        currentNr = nr;
        $('#receive_cert_nr').val(nr);
        $('#receive_arrived_on').val('');
        $('#receiveCertificateModal').modal('show');
    }

    // Confirm receive
    function confirmReceive() {
        var arrivedOn = $('#receive_arrived_on').val();
        if (!arrivedOn) {
            Utils.notify('Please enter arrived on date', 'warning');
            return;
        }

        $.ajax({
            url: '/ajax/slaughteringCertificatesHandler.php',
            type: 'POST',
            data: { 
                action: 'updateStatus', 
                nr: $('#receive_cert_nr').val(), 
                cert_type: certType,
                status_type: 'received',
                status_date: arrivedOn
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Utils.notify('Certificate marked as received', 'success');
                    $('#receiveCertificateModal').modal('hide');
                    applyFilters();
                } else {
                    Utils.notify(response.message || 'Error updating status', 'error');
                }
            }
        });
    }

    // Print certificate
    function printCertificate() {
        if (currentNr) {
            printCertificateById(currentNr);
        }
    }

    function printCertificateById(nr) {
        window.open('/ajax/slaughteringCertificatesHandler.php?action=print&nr=' + nr + '&cert_type=' + certType, '_blank');
    }

    // Show QR code
    function showQR(nr) {
        window.open('/ajax/slaughteringCertificatesHandler.php?action=qr&nr=' + nr + '&cert_type=' + certType, '_blank');
    }

    // Export to Excel
    function exportToExcel() {
        var params = $.param({
            action: 'export',
            cert_type: certType,
            filterYear: $('#filterYear').val(),
            filterOffice: $('#filterOffice').val(),
            filterStatus: $('#filterStatus').val(),
            filterSearch: $('#filterSearch').val(),
            filterSearchBy: $('#filterSearchBy').val()
        });
        window.location.href = '/ajax/slaughteringCertificatesHandler.php?' + params;
    }

    // Initialize on document ready
    $(document).ready(function() {
        initGrid();
        
        // Initialize datepickers
        $('.datepicker').datepicker({
            format: 'dd.mm.yyyy',
            autoclose: true,
            todayHighlight: true
        });

        // Set main menu item
        if (typeof Common !== 'undefined' && Common.setMainMenuItem) {
            Common.setMainMenuItem('slaughtering_certificates');
        }

        // Enter key on search
        $('#filterSearch').on('keypress', function(e) {
            if (e.which === 13) {
                applyFilters();
            }
        });
    });

    // Public methods
    return {
        initGrid: initGrid,
        applyFilters: applyFilters,
        resetFilters: resetFilters,
        viewCertificate: viewCertificate,
        editCertificate: editCertificate,
        issueCertificate: issueCertificate,
        saveCertificate: saveCertificate,
        deleteCertificate: deleteCertificate,
        sendCertificate: sendCertificate,
        confirmSend: confirmSend,
        receiveCertificate: receiveCertificate,
        confirmReceive: confirmReceive,
        printCertificate: printCertificate,
        showQR: showQR,
        exportToExcel: exportToExcel
    };
})();
</script>
