<?php if (!defined("_HQC_")) {
    exit();
}; ?>

<script>
    $("#page_title").html("Export Invoices")
</script>

<?php
if ( $_SESSION['user_type'] == "admin") {
?>
	<style>
	/* Export Invoices Page Header */
.export-invoice-header {
    background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
    border-radius: 12px;
    border: 1px solid #e0e7ff;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.export-invoice-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.export-invoice-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.export-invoice-header-info {
    flex: 1;
    min-width: 200px;
}

.export-invoice-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.export-invoice-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Export Badge */
.export-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #ede9fe;
    color: #6d28d9;
}

.export-badge i {
    font-size: 10px;
}

/* Header Actions */
.export-header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-export-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.btn-export-action.back {
    background: #ffffff;
    color: #7c3aed;
    border: 2px solid #e0e7ff;
}

.btn-export-action.back:hover {
    background: #f5f3ff;
    border-color: #c4b5fd;
    color: #6d28d9;
    text-decoration: none;
}

.btn-export-action.download {
    background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
    color: #ffffff;
}

.btn-export-action.download:hover {
    background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 100%);
    color: #ffffff;
    text-decoration: none;
}

/* Search/Filter Section */
.export-filter-section {
    padding: 20px 32px;
    background: #faf5ff;
    border-top: 1px solid #e0e7ff;
}

.export-filter-section .filter-title {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.export-filter-section .filter-title i {
    color: #7c3aed;
}

/* Info Box */
.export-info-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 20px;
    background: #ffffff;
    border: 1px dashed #c4b5fd;
    border-radius: 10px;
    margin-top: 16px;
}

.export-info-box i {
    color: #8b5cf6;
    font-size: 16px;
    margin-top: 2px;
}

.export-info-box .info-content {
    flex: 1;
    font-size: 13px;
    color: #6d28d9;
}

.export-info-box .info-content strong {
    color: #5b21b6;
}

/* Table Styling Enhancement */
.export-table-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.export-table-container table {
    margin-bottom: 0;
}

.export-table-container thead tr.firstHead td {
    background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
    font-weight: 600;
    text-align: center;
    text-transform: uppercase;
    color: #5b21b6;
    padding: 14px 16px;
    font-size: 12px;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #c4b5fd;
}

.export-table-container thead tr#headerTh th {
    background: #faf5ff;
    color: #374151;
    font-weight: 600;
    padding: 12px 16px;
    font-size: 13px;
    border-bottom: 1px solid #e0e7ff;
}

.export-table-container tbody tr:hover {
    background: #faf5ff;
}

.export-table-container tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

/* Loading State */
.export-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: #64748b;
}

.export-loading i {
    font-size: 32px;
    color: #c4b5fd;
    margin-bottom: 16px;
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

.export-loading span {
    font-size: 14px;
}

/* Results Summary */
.export-results-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    border-radius: 0 0 12px 12px;
}

.export-results-summary .summary-text {
    font-size: 13px;
    color: #64748b;
}

.export-results-summary .summary-text strong {
    color: #1e293b;
}

.export-results-summary .export-buttons {
    display: flex;
    gap: 8px;
}

.btn-export-file {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.btn-export-file.excel {
    background: #dcfce7;
    color: #166534;
}

.btn-export-file.excel:hover {
    background: #bbf7d0;
}

.btn-export-file.pdf {
    background: #fef2f2;
    color: #dc2626;
}

.btn-export-file.pdf:hover {
    background: #fecaca;
}

.btn-export-file.csv {
    background: #e0f2fe;
    color: #0369a1;
}

.btn-export-file.csv:hover {
    background: #bae6fd;
}

/* Responsive */
@media (max-width: 768px) {
    .export-invoice-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .export-invoice-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .export-header-actions {
        width: 100%;
        justify-content: center;
    }
    
    .export-filter-section {
        padding: 16px 20px;
    }
    
    .export-table-container {
        overflow-x: auto;
    }
    
    .export-results-summary {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
}
	</style>
	
<div class="export-invoice-header">
    <div class="export-invoice-header-content">
        <div class="export-invoice-header-icon">
            <i class="fas fa-file-export"></i>
        </div>
        
        <div class="export-invoice-header-info">
            <h2>
                Export Invoices
                <span class="export-badge">
                    <i class="fas fa-download"></i>
                    Data Export
                </span>
            </h2>
            <p>Search and export invoice data for accounting and reporting</p>
        </div>
        
        <div class="export-header-actions">
            <a href="index.php?inc=invoices&show=all" class="btn-export-action back">
                <i class="fas fa-arrow-left"></i>
                Back to Invoices
            </a>
        </div>
    </div>
    
    <div class="export-filter-section">
        <div class="filter-title">
            <i class="fas fa-filter"></i>
            Filter & Search Invoices
        </div>
        <?php include "search_engine.inc.php"; ?>
        
        <div class="export-info-box">
            <i class="fas fa-lightbulb"></i>
            <div class="info-content">
                <strong>Tip:</strong> Use the filters above to narrow down your results. You can filter by date range, company, invoice type, and payment status before exporting.
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(e) {
        $("#dateDialog").append('<div style="margin-top:5px;"><b>Remarks:</b> <input type="text" style="width:160px" id="remarks" value=""/></div>')
    });

    var itemNr;
    var clid;
    var st = -1;
    var orderBy = 'inserted_on';
    var ascDsc = 'ASC';

    function getdate(nr, id, invNr) {
        itemNr = nr;
        clid = clid;
        showDateDialog("Invoice nr: " + invNr, 360);
    }

    function getDateData(dt) {
        rem = $("#remarks").val();
        jQuery.post('invoice_save.php', {
            act: 'paidOn',
            paid_on: dt,
            nr: itemNr,
            remarks: rem
        }).done(function(data) {
            if (data.trim().length > 0) {
                if (data.indexOf("error:") > -1) {
                    alert(data.replace('error:', ''));
                } else {
                    jQuery("#inv_" + itemNr).remove();
                    jQuery(".invItem").each(function(index, element) {
                        jQuery(this).html(index + 1);
                    });
                }
            }
        });
    }

    function clientClik() {
        jQuery(".clientInvoice").click(function(index, element) {
            st = 0;
            jQuery("#loadMoreInvoicesBtn").css("display", "none");
            jQuery("#invoiceItems").html('');
            clid = jQuery(this).attr("data-id");
            loadInvoices(clid);
        });
    }
</script>

<div class="export-table-container">
    <table id="table3" class="table table-striped table-bordered" style="width:100%; margin-bottom: 0;">
        <thead>
            <tr class="firstHead">
                <td style="width: 50px;"></td>
                <td colspan="2">Company</td>
                <td colspan="6">Invoice Details</td>
            </tr>
            <tr id="headerTh">
                <th style="width: 50px;">Nr</th>
                <th>Company ID</th>
                <th>Company Name</th>
                <th>Service Type</th>
                <th>Invoice Number</th>
                <th>Date</th>
                <th style="text-align: right;">Subtotal</th>
                <th style="text-align: right;">VAT</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody id="invoiceItems">
            <tr id="invoiceItemsLoading">
                <td colspan="9">
                    <div class="export-loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Loading invoices...</span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="export-results-summary" id="exportSummary" style="display: none;">
        <div class="summary-text">
            <i class="fas fa-check-circle" style="color: #16a34a; margin-right: 6px;"></i>
            Showing <strong id="resultCount">0</strong> invoices
        </div>
        <div class="export-buttons">
            <button class="btn-export-file excel" onclick="exportToExcel()">
                <i class="fas fa-file-excel"></i>
                Excel
            </button>
            <button class="btn-export-file csv" onclick="exportToCSV()">
                <i class="fas fa-file-csv"></i>
                CSV
            </button>
            <button class="btn-export-file pdf" onclick="exportToPDF()">
                <i class="fas fa-file-pdf"></i>
                PDF
            </button>
        </div>
    </div>
</div>

<script>
    // Show summary after invoices load
    function showExportSummary(count) {
        jQuery('#resultCount').text(count);
        jQuery('#exportSummary').show();
    }
    
    // Export functions (customize these based on your backend)
    function exportToExcel() {
        // Add your Excel export logic here
        alert_message('Excel export functionality - implement based on your backend');
    }
    
    function exportToCSV() {
        // Add your CSV export logic here
        alert_message('CSV export functionality - implement based on your backend');
    }
    
    function exportToPDF() {
        // Add your PDF export logic here
        alert_message('PDF export functionality - implement based on your backend');
    }
    
    loadInvoices();
</script>

<?php }; ?>