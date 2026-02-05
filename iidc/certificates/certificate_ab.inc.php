<style>
/* ============================================
   Shipment Certificate Page - Modern Styling
   ============================================ */

/* Company Selection Page */
.cert-select-container {
    
    margin: 0 auto;
}

.cert-select-header {
    background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
    border-radius: 16px;
    border: 1px solid #fecaca;
    padding: 32px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    margin-bottom: 24px;
}

.cert-select-header.type-b {
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
    border-color: #bae6fd;
}

.cert-select-header.type-sa {
    background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
    border-color: #ddd6fe;
}

.cert-select-header.type-sb {
    background: linear-gradient(135deg, #ffffff 0%, #fdf2f8 100%);
    border-color: #fbcfe8;
}

.cert-select-header-top {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #fecaca;
}

.cert-select-header.type-b .cert-select-header-top { border-bottom-color: #bae6fd; }
.cert-select-header.type-sa .cert-select-header-top { border-bottom-color: #ddd6fe; }
.cert-select-header.type-sb .cert-select-header-top { border-bottom-color: #fbcfe8; }

.cert-select-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.cert-select-header.type-b .cert-select-icon {
    background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
}

.cert-select-header.type-sa .cert-select-icon {
    background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
}

.cert-select-header.type-sb .cert-select-icon {
    background: linear-gradient(135deg, #db2777 0%, #ec4899 100%);
}

.cert-select-title-wrap {
    flex: 1;
}

.cert-select-title-wrap h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.cert-select-title-wrap p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

.cert-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cert-type-badge.type-a {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.cert-type-badge.type-b {
    background: #f0f9ff;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.cert-type-badge.type-sa {
    background: #f5f3ff;
    color: #7c3aed;
    border: 1px solid #ddd6fe;
}

.cert-type-badge.type-sb {
    background: #fdf2f8;
    color: #db2777;
    border: 1px solid #fbcfe8;
}

/* Office Tag in Header */
.office-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
}

.office-badge i {
    color: #22c55e;
}

/* Company Dropdown Section */
.cert-select-dropdown {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.cert-select-dropdown label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cert-select-dropdown select {
    width: 100%;
    padding: 16px 48px 16px 20px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
}

.cert-select-dropdown select:hover {
    border-color: #dc2626;
}

.cert-select-header.type-b .cert-select-dropdown select:hover { border-color: #0369a1; }
.cert-select-header.type-sa .cert-select-dropdown select:hover { border-color: #7c3aed; }
.cert-select-header.type-sb .cert-select-dropdown select:hover { border-color: #db2777; }

.cert-select-dropdown select:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12);
}

.cert-select-header.type-b .cert-select-dropdown select:focus {
    border-color: #0369a1;
    box-shadow: 0 0 0 4px rgba(3, 105, 161, 0.12);
}

.cert-select-header.type-sa .cert-select-dropdown select:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12);
}

.cert-select-header.type-sb .cert-select-dropdown select:focus {
    border-color: #db2777;
    box-shadow: 0 0 0 4px rgba(219, 39, 119, 0.12);
}

.cert-select-hint {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 16px;
    padding: 14px 18px;
    background: #fffbeb;
    border: 1px dashed #fbbf24;
    border-radius: 10px;
    font-size: 13px;
    color: #92400e;
}

.cert-select-hint i {
    color: #f59e0b;
    font-size: 16px;
}

/* Empty State */
.cert-empty-state {
    text-align: center;
    padding: 48px 24px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px dashed #cbd5e1;
}

.cert-empty-state i {
    font-size: 48px;
    color: #cbd5e1;
    margin-bottom: 16px;
}

.cert-empty-state h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #64748b;
}

.cert-empty-state p {
    margin: 0;
    font-size: 14px;
    color: #94a3b8;
}

/* ============================================
   Certificate Form Styling
   ============================================ */

/* Validation Error Styles */
.validation-error-box {
    display: none;
    margin: 20px;
    padding: 20px;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border: 1px solid #fca5a5;
    border-left: 4px solid #dc2626;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
}

.validation-error-box.visible {
    display: block;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.validation-error-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #fecaca;
}

.validation-error-header i {
    font-size: 24px;
    color: #dc2626;
}

.validation-error-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #991b1b;
}

.validation-error-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.validation-error-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    margin-bottom: 6px;
    background: #ffffff;
    border-radius: 8px;
    font-size: 14px;
    color: #991b1b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.validation-error-list li:hover {
    background: #fef2f2;
    transform: translateX(4px);
}

.validation-error-list li:last-child {
    margin-bottom: 0;
}

.validation-error-list li i {
    color: #dc2626;
    font-size: 12px;
}

/* Field Error State */
.field-error {
    border-color: #dc2626 !important;
    background-color: #fef2f2 !important;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
}

.field-error-label {
    color: #dc2626 !important;
}

/* Form Container */
.cert-form-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.cert-form-container table#reqCerts {
    margin-bottom: 0;
    border: none !important;
}

.cert-form-container table#reqCerts tr td,
.cert-form-container table#reqCerts tr th {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
}

.cert-form-container table#reqCerts tr:last-child td,
.cert-form-container table#reqCerts tr:last-child th {
    border-bottom: none;
}

.cert-form-container table#reqCerts th {
    font-weight: 600;
    font-size: 13px;
    color: #374151;
    background: #fafafa;
    width: 180px;
    vertical-align: top;
    text-align: left;
}

.cert-form-container table#reqCerts td {
    background: #ffffff;
}

/* Sub Title Rows */
.cert-form-container table#reqCerts td.sub_title,
.cert-form-container table#reqCerts th.sub_title {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%) !important;
    color: #991b1b;
    font-weight: 600;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
    padding: 14px 20px;
}

.cert-form-container.type-b table#reqCerts td.sub_title,
.cert-form-container.type-b table#reqCerts th.sub_title {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
    color: #075985;
}

.cert-form-container.type-sa table#reqCerts td.sub_title,
.cert-form-container.type-sa table#reqCerts th.sub_title {
    background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%) !important;
    color: #5b21b6;
}

.cert-form-container.type-sb table#reqCerts td.sub_title,
.cert-form-container.type-sb table#reqCerts th.sub_title {
    background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%) !important;
    color: #9d174d;
}

/* Form Inputs */
.cert-form-container input[type="text"],
.cert-form-container input[type="number"],
.cert-form-container select,
.cert-form-container textarea {
    padding: 0px 14px;
    font-size: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.25s ease;
    font-family: inherit;
}

.cert-form-container input[type="text"],
.cert-form-container input[type="number"],
.cert-form-container textarea {
    padding: 10px 14px;
}

.cert-form-container input[type="text"]:focus,
.cert-form-container input[type="number"]:focus,
.cert-form-container select:focus,
.cert-form-container textarea:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.cert-form-container.type-b input:focus,
.cert-form-container.type-b select:focus,
.cert-form-container.type-b textarea:focus {
    border-color: #0369a1;
    box-shadow: 0 0 0 3px rgba(3, 105, 161, 0.1);
}

.cert-form-container textarea {
    min-height: 80px;
    resize: vertical;
}

/* Form Footer */
.cert-form-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 24px;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border-top: 1px solid #fecaca;
    flex-wrap: wrap;
}

.cert-form-container.type-b .cert-form-footer {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-top-color: #bae6fd;
}

.btn-form-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.btn-form-action.cancel {
    background: #ffffff;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-form-action.cancel:hover {
    background: #f1f5f9;
    color: #475569;
}

.btn-form-action.reset {
    background: #ffffff;
    color: #f59e0b;
    border: 2px solid #fde68a;
}

.btn-form-action.reset:hover {
    background: #fffbeb;
}

.btn-form-action.preview {
    background: #ffffff;
    color: #7c3aed;
    border: 2px solid #ddd6fe;
}

.btn-form-action.preview:hover {
    background: #f5f3ff;
}

.btn-form-action.draft {
    background: #ffffff;
    color: #0369a1;
    border: 2px solid #bae6fd;
}

.btn-form-action.draft:hover {
    background: #f0f9ff;
}

.btn-form-action.primary {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    color: #ffffff;
}

.btn-form-action.primary:hover {
    background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-form-action:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.cert-form-container.type-b .btn-form-action.primary {
    background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
}

.cert-form-container.type-b .btn-form-action.primary:hover {
    background: linear-gradient(135deg, #075985 0%, #0369a1 100%);
    box-shadow: 0 4px 12px rgba(3, 105, 161, 0.3);
}

/* Loading Overlay */
.form-loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}

.form-loading-overlay.visible {
    display: flex;
}

.form-loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #e2e8f0;
    border-top-color: #dc2626;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.form-loading-text {
    margin-top: 16px;
    font-size: 16px;
    font-weight: 600;
    color: #374151;
}

/* Warning Box */
.sab-warning {
    margin-top: 16px;
    padding: 16px 20px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    color: #dc2626;
    text-align: center;
    font-size: 14px;
}

.sab-warning a {
    color: #dc2626;
    font-weight: 600;
}

/* Certificate Page Header */
.certificate-page-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf9 100%);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.certificate-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
}

.certificate-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.certificate-header-icon.type-b {
    background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);
}

.certificate-header-icon.type-sa {
    background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
}

.certificate-header-icon.type-sb {
    background: linear-gradient(135deg, #db2777 0%, #ec4899 100%);
}

.certificate-header-info {
    flex: 1;
}

.certificate-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.certificate-header-info h2 .action-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.certificate-header-info h2 .action-badge.issue {
    background: #dcfce7;
    color: #166534;
}

.certificate-header-info h2 .action-badge.update {
    background: #fef3c7;
    color: #92400e;
}

.certificate-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

.certificate-header-info p strong {
    color: #1a5f4a;
}

.certificate-header-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.cert-type-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

.cert-type-tag.type-a {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.cert-type-tag.type-b {
    background: #f0f9ff;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.cert-type-tag.type-sa {
    background: #f5f3ff;
    color: #7c3aed;
    border: 1px solid #ddd6fe;
}

.cert-type-tag.type-sb {
    background: #fdf2f8;
    color: #db2777;
    border: 1px solid #fbcfe8;
}

.cert-type-tag i {
    font-size: 14px;
}

.office-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
}

.office-tag i {
    font-size: 14px;
    color: #1a5f4a;
}

/* Responsive */
@media (max-width: 768px) {
    .certificate-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .certificate-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .certificate-header-meta {
        justify-content: center;
    }
    
    .cert-form-footer {
        flex-direction: column;
    }
    
    .btn-form-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
	$("#page_title").html("Requested Certificates A (Meat)")
</script>
<?php
include "../checkuser.inc.php";
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";

$attch_display = 'none';
$companies = array();
$option = array();
$batchCountry = false;
$batchOffices = array();
$offices = array();
$user_type = $_SESSION['user_type'];

if (!isset($_GET['clid']) || $_GET['clid'] == '') {
    if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid='{$_GET['offid']}'")) {
        $user_clients = ($office['clients']);
    } else {
        echo '<div class="cert-empty-state">
            <i class="fas fa-exclamation-circle"></i>
            <h3>Office Not Found</h3>
            <p>The specified office could not be located in the system.</p>
        </div>';
        return;
    }

    if ($companies = $amdb->get_results("SELECT * FROM companies
                               LEFT JOIN users ON companies.clid = users.clid
                               WHERE users.active='y' ORDER BY companies.company_name ASC")) {
        
        // Determine certificate type
        $typeClass = 'type-a';
        $typeIcon = 'fa-drumstick-bite';
        $typeName = 'Slaughtering Certificate';
        $typeShort = 'Type A';
        $typeDesc = 'Raw / Fresh / Frozen Meats (Unprocessed)';
        
        if ($_REQUEST['tp'] == 'b') {
            $typeClass = 'type-b';
            $typeIcon = 'fa-leaf';
            $typeName = 'Non-Meat Certificate';
            $typeShort = 'Type B';
            $typeDesc = 'Non-Fresh Meats / Foods / Beverages / Cosmetics';
        } elseif ($_REQUEST['tp'] == 'sa') {
            $typeClass = 'type-sa';
            $typeIcon = 'fa-drumstick-bite';
            $typeName = 'Saudi Slaughtering Certificate';
            $typeShort = 'Type SA';
            $typeDesc = 'Saudi Arabia - Raw / Fresh / Frozen Meats';
        } elseif ($_REQUEST['tp'] == 'sb') {
            $typeClass = 'type-sb';
            $typeIcon = 'fa-leaf';
            $typeName = 'Saudi Non-Meat Certificate';
            $typeShort = 'Type SB';
            $typeDesc = 'Saudi Arabia - Non-Fresh Meats / Foods / Cosmetics';
        }
?>
<div class="cert-select-container">
    <div class="cert-select-header <?php echo $typeClass; ?>">
        <div class="cert-select-header-top">
            <div class="cert-select-icon">
                <i class="fas <?php echo $typeIcon; ?>"></i>
            </div>
            
            <div class="cert-select-title-wrap">
                <h2>
                    <?php echo $typeName; ?>
                    <span class="cert-type-badge <?php echo $typeClass; ?>">
                        <?php echo $typeShort; ?>
                    </span>
                </h2>
                <p><?php echo $typeDesc; ?></p>
            </div>
            
            <span class="office-badge">
                <i class="fas fa-building"></i>
                <?php echo htmlspecialchars($office['office_name']); ?>
            </span>
        </div>
        
        <div class="cert-select-dropdown">
            <label for="company-select">Select Company to Issue Certificate</label>
            <select name="clid" id="company-select" onchange="if(this.value) document.location='?inc=certificate_ab&tp=<?php echo $_REQUEST['tp']; ?>&offid=<?php echo $_REQUEST['offid']; ?>&clid='+this.value">
                <option value="">-- Choose a company --</option>
                <?php foreach ($companies as $company) { ?>
                    <option value="<?php echo $company['clid']; ?>"><?php echo htmlspecialchars($company['company_name']); ?></option>
                <?php } ?>
            </select>
        </div>
        
        <div class="cert-select-hint">
            <i class="fas fa-info-circle"></i>
            <span>Select a company from the dropdown above to proceed with certificate issuance</span>
        </div>
    </div>
</div>
<?php 
    } else {
?>
<div class="cert-select-container">
    <div class="cert-empty-state">
        <i class="fas fa-building"></i>
        <h3>No Companies Found</h3>
        <p>There are no active companies available for this office.</p>
    </div>
</div>
<?php
    }
    return;
}

$companies = array();
$importers = '';
$exporters = '';
$producers = '';
if ($result = $amdb->get_results("SELECT * FROM companies WHERE clid='$clid' or clof='$clid' ORDER BY company_name ASC")) {
	foreach ($result as $company) {
		if (!isset($companies[$company['clid']])) {
			if (trim($company['country1']) == 'SA' or trim($company['country1']) == 'Saudi Arabia') {
				$SFDA = ' data-sfda="yes"';
			} else {
				$SFDA = '';
			}

			$producers .= "<option value='$company[clid]'>$company[company_name]</option>\n";
			if (in_array($_GET['tp'], ['a', 'b']) or (in_array($_GET['tp'], ['sa', 'sb']) && (trim($company['country1']) == 'SA' or trim($company['country1']) == 'Saudi Arabia')))
				$importers .= "<option data-CRN='$company[CRN]' value='$company[clid]'$SFDA>$company[company_name]" . (trim($company['country1']) != '' ? '(' . $company['country1'] . ')' : '') . "</option>\n";
			$exporters .= "<option value='$company[clid]'>$company[company_name]</option>\n";
			$companies[$company['clid']] = $company['company_name'];
			if ($company['clid'] == $clid) {
				$company_country = $company['country1'];
				$company_name = $company['company_name'];
				$client_extra = $company['client_extra'];
				$company_email = $company['email1'];
				$offid = $company['offid'];
			}
		}
	}
};

if (!isset($act) or $act == "") $act = "add";
if (isset($act) and $act == "edit") {
	if ($row = $amdb->get_row("SELECT * FROM certificates_{$_REQUEST['tp']} where nr='$nr'")) {
		$importer = $row['importer'];
		$exporter = $row['exporter'];
		$producer = $row['producer'];
		$certificate_nr = $row['certificate_nr'];
		if (trim($row['options']) != '' and is_array(json_decode(str_replace("\r\n", '\n', $row['options']), true)))
			$option = json_decode(str_replace("\r\n", '\n', $row['options']), true);
		if ($row['attachment'])
			$attch_display = '';

		if ($_SESSION['user_type'] != 'client' && !isset($office) && isset($row['tmplid']))
			$office = get_office_data($row['tmplid']);
	}
}
$certificate_title = array('a' => 'Certificate type A (Raw / Fresh / Frozen Meats (Unprocessed))', 'b' => 'Certificate type B (Non-Fresh Meats / Foods / Beverages / Cosmetics)', 'sa' => 'Certificate type A (Raw / Fresh / Frozen Meats (Unprocessed)) <span style="color:red">for Saudi Arabia only</span>', 'sb' => 'Certificate type B (Non-Fresh Meats / Foods / Beverages / Cosmetics) <span style="color:red">for Saudi Arabia only</span>');
?>

<!-- Loading Overlay -->
<div class="form-loading-overlay" id="loadingOverlay">
    <div class="form-loading-spinner"></div>
    <div class="form-loading-text">Processing certificate...</div>
</div>

<script>
// ============================================
// Enhanced Validation System
// ============================================

var validationErrors = [];

function clearValidationErrors() {
    validationErrors = [];
    // Remove error styling from all fields
    jQuery('.field-error').removeClass('field-error');
    jQuery('.field-error-label').removeClass('field-error-label');
    // Hide error box
    jQuery('#validationErrorBox').removeClass('visible');
}

function addValidationError(fieldName, fieldLabel, message) {
    validationErrors.push({
        field: fieldName,
        label: fieldLabel,
        message: message
    });
}

function showValidationErrors() {
    if (validationErrors.length === 0) return true;
    
    var errorList = jQuery('#validationErrorList');
    errorList.empty();
    
    validationErrors.forEach(function(error) {
        var li = jQuery('<li></li>');
        li.html('<i class="fas fa-times-circle"></i> <strong>' + error.label + ':</strong> ' + error.message);
        li.on('click', function() {
            scrollToField(error.field);
        });
        errorList.append(li);
        
        // Highlight the field
        var field = jQuery('[name="' + error.field + '"]');
        if (field.length) {
            field.addClass('field-error');
            field.closest('tr').find('th').addClass('field-error-label');
        }
    });
    
    jQuery('#validationErrorBox').addClass('visible');
    
    // Scroll to error box
    jQuery('html, body').animate({
        scrollTop: jQuery('#validationErrorBox').offset().top - 100
    }, 500);
    
    return false;
}

function scrollToField(fieldName) {
    var field = jQuery('[name="' + fieldName + '"]');
    if (field.length) {
        jQuery('html, body').animate({
            scrollTop: field.offset().top - 150
        }, 300, function() {
            field.focus();
        });
    }
}

function validateForm() {
    clearValidationErrors();
    
    var form = document.certificateForm;
    var tp = '<?php echo $_REQUEST['tp']; ?>';
    var userType = '<?php echo $_SESSION['user_type']; ?>';
    
    // Required field validation
    var requiredFields = [
        { name: 'country_of_origin', label: 'Country of Origin', type: 'select' },
        { name: 'quality', label: 'Quantity – Quality' },
        { name: 'weight_gross', label: 'Gross Weight' },
        { name: 'weight_net', label: 'Net Weight' },
        { name: 'loading_port', label: 'Loading Port' },
        { name: 'destination', label: 'Destination' },
        { name: 'exporter', label: 'Exporter', type: 'select' },
        { name: 'importer', label: 'Importer', type: 'select' },
        { name: 'producer', label: 'Producer', type: 'select' },
        { name: 'expiry_date', label: 'Expiry Date' },
        { name: 'slaughterer_name', label: 'Slaughtering Supervisor' }
    ];
    
    // Add slaughtering date for type A and SA
    if (tp === 'a' || tp === 'sa') {
        requiredFields.push({ name: 'slaughtering_date', label: 'Slaughtering Date' });
    }
    
    // Add issue date for admin/office users
    if (userType === 'admin' || userType === 'hqc_office') {
        requiredFields.push({ name: 'issue_date', label: 'Certificate Issuing Date' });
    }
    
    // Validate each required field
    requiredFields.forEach(function(fieldDef) {
        var field = jQuery('[name="' + fieldDef.name + '"]');
        if (field.length) {
            var value = field.val();
            if (!value || value.trim() === '') {
                addValidationError(fieldDef.name, fieldDef.label, 'This field is required');
            }
        }
    });
    
    // Validate weight fields are numeric
    var grossWeight = jQuery('[name="weight_gross"]').val();
    var netWeight = jQuery('[name="weight_net"]').val();
    
    if (grossWeight && !/^[0-9.]+$/.test(grossWeight)) {
        addValidationError('weight_gross', 'Gross Weight', 'Only numbers and decimal point allowed');
    }
    
    if (netWeight && !/^[0-9.]+$/.test(netWeight)) {
        addValidationError('weight_net', 'Net Weight', 'Only numbers and decimal point allowed');
    }
    
    // Validate at least one product in appendix
    if (jQuery('#batchProducts tr').length === 0) {
        addValidationError('products', 'Appendix Items', 'Please add at least one item to the appendix');
    }
    
    // Check CRN for Saudi importers
    if ((tp === 'sa' || tp === 'sb') && jQuery('#CRN').is(':visible')) {
        var crnValue = jQuery('#CRNValue').val();
        if (!crnValue || crnValue.trim() === '') {
            addValidationError('CRN', 'Commercial Registration Number', 'CRN is required for Saudi importers');
        } else if (!/^\d{10}$/.test(crnValue.trim())) {
            addValidationError('CRN', 'Commercial Registration Number', 'CRN must be exactly 10 digits');
        }
    }
    
    return showValidationErrors();
}

// ============================================
// Form Submission via AJAX (No Iframe)
// ============================================

function showLoading(message) {
    jQuery('#loadingOverlay .form-loading-text').text(message || 'Processing...');
    jQuery('#loadingOverlay').addClass('visible');
}

function hideLoading() {
    jQuery('#loadingOverlay').removeClass('visible');
}

function submitFormAjax(action, callback) {
    var formData = new FormData(document.certificateForm);
    formData.append('ajax', '1');
    
    showLoading('Processing certificate...');
    
    jQuery.ajax({
        url: 'certificate_save.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoading();
            if (callback) {
                callback(response);
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            alert_message('Error: ' + error);
        }
    });
}

function preview() {
    if (!validateForm()) {
        return false;
    }
    
    document.certificateForm.action = "pdf_certificate.php";
    document.certificateForm.act.value = "preview";
    document.certificateForm.target = "_blank";
    document.certificateForm.submit();
}

function save_hc(savePrint) {
    if (!validateForm()) {
        return false;
    }
    
    var form = document.certificateForm;
    form.action = "certificate_save.php";
    
    if (savePrint === 'draft' || savePrint === 'saveDraft') {
        form.act.value = savePrint;
    } else {
        form.act.value = "<?php echo $act; ?>";
    }
    
    jQuery('#do').val(savePrint);
    
    var formData = new FormData(form);
    formData.append('ajax', '1');
    
    var loadingMessage = 'Processing...';
    if (savePrint === 'print') {
        loadingMessage = 'Generating certificate...';
    } else if (savePrint === 'draft' || savePrint === 'saveDraft') {
        loadingMessage = 'Saving draft...';
    } else if (savePrint === 'request') {
        loadingMessage = 'Submitting request...';
    }
    
    showLoading(loadingMessage);
    
    jQuery.ajax({
        url: 'certificate_save.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoading();
            
            try {
                var result = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (result.success) {
                    if (result.pdf_url) {
                        // Open PDF in new window
                        window.open(result.pdf_url, '_blank');
                    }
                    
                    if (result.message) {
                        alert_message(result.message, 'success');
                    }
                    
                    // Redirect after short delay
                    if (result.redirect) {
                        setTimeout(function() {
                            window.location.href = result.redirect;
                        }, 1000);
                    }
                } else {
                    alert_message(result.message || 'An error occurred');
                }
            } catch (e) {
                // Handle non-JSON response (legacy behavior)
                if (response.indexOf('success') > -1 || response.indexOf('pdf') > -1) {
                    // Assume success, redirect
                    window.location.href = '?inc=certificates&tp=<?php echo $_REQUEST['tp']; ?>&offid=<?php echo $_GET['offid']; ?>';
                } else if (response.indexOf('error') > -1) {
                    alert_message(response);
                } else {
                    // Check if response contains a redirect script
                    if (response.indexOf('location') > -1) {
                        jQuery('body').append(response);
                    } else {
                        window.location.href = '?inc=certificates&tp=<?php echo $_REQUEST['tp']; ?>&offid=<?php echo $_GET['offid']; ?>';
                    }
                }
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            alert_message('Error submitting form: ' + error);
        }
    });
}

function changeOption(obj) {
    var id = 'option_' + obj.name;
    jQuery('#' + id).css('display', 'none');
    if (obj.value === '0') {
        jQuery('#' + id).css('display', 'inherit');
    }
    if (obj.value === 'newClient') {
        location = '/company/index.php?inc=cl_add_edit&clof=<?php echo $clid; ?>&return=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>';
    }
    
    // Clear error state when field changes
    jQuery(obj).removeClass('field-error');
    jQuery(obj).closest('tr').find('th').removeClass('field-error-label');
}

// Clear error state on field interaction
jQuery(document).on('input change', '.field-error', function() {
    jQuery(this).removeClass('field-error');
    jQuery(this).closest('tr').find('th').removeClass('field-error-label');
});

function adjustColWidth() {
    var width = 0;
    jQuery("#batchHead").find("input[type=number]").each(function() {
        var thisWidth = parseInt(jQuery(this).val());
        var cmToPx = Math.round(18 * 37.795275591);
        var parentWidth = jQuery(this).parents('table').width();
        var tdWidth = Math.round(thisWidth * ((parentWidth / cmToPx) * 37.795275591));
        jQuery(this).parent('td').css('width', tdWidth + 'px');
        width = width + thisWidth;
    });
    
    var decWidth;
    if (jQuery("#option_landscape").is(":checked")) {
        decWidth = 26.5 - width;
    } else {
        decWidth = 18 - width;
    }
    jQuery("#description_width").val(decWidth);
    jQuery(".description_width").html(decWidth);
}

function resetSortable() {
    if (jQuery("#batchProducts tr").length > 1) {
        jQuery('i.fas.fa-arrows-alt').css('display', 'inherit');
    } else {
        jQuery('i.fas.fa-arrows-alt').css('display', 'none');
    }

    if (jQuery("#batchProducts").hasClass('ui-sortable')) {
        jQuery("#batchProducts").sortable('destroy');
        jQuery("#batchProducts").removeClass('ui-sortable');
        jQuery("#batchProducts tr td:last-child .fa-arrows-alt").remove();
        jQuery("#batchProducts tr td:last-child img").css('display', '');
        jQuery("#batchProducts tr td").each(function() {
            jQuery(this).css('width', '');
        });
    }
}

function sortProducts() {
    if (jQuery("#batchProducts").hasClass('ui-sortable')) {
        resetSortable();
    } else {
        if (jQuery("#batchProducts").find('tr').length > 1) {
            jQuery("#batchProducts tr td:last-child img").css('display', 'none');
            jQuery("#batchProducts tr td:last-child").append('<i class="fas fa-arrows-alt"></i>');
            jQuery("#batchProducts").sortable({
                items: "tr",
                cursor: "move",
                opacity: 0.6,
                tolerance: "pointer",
            });

            jQuery("#batchProducts tr td").each(function() {
                jQuery(this).css('width', jQuery(this).width() + 'px');
            });
        }
    }
}

var productsHeader = {
    'description': {
        'english': '<b>Description of Shipped Products or Items</b>',
        'width': 8
    },
    'quantity': {
        'english': '<b>Total Weight</b><br/>in Kilograms',
        'width': 5
    }
};

function insertProductTitle(reset) {
    if (reset === true) {
        jQuery("#batchHeadTitles").html('');
        jQuery("#batchHeadWidths").html('');
    }
    if (jQuery("#batchHeadTitles th").length < 3) {
        var headerTh = '', headerTd = '';
        jQuery.each(productsHeader, function(key, val) {
            headerTh += '<th style="text-align: center;" class="productTitleTh ' + key + '">' +
                '<div id = "' + key + '_english" class = "productTitle english" >' + val.english + '</div></th>';
            if (key === 'description') {
                headerTd += '<td>Width: <input type="hidden" name="option[' + key + '][width]" id="description_width" value="' + val.width + '" /><span class="description_width">' + val.width + '</span> CM</td>';
            } else {
                headerTd += '<td class="extra">width: <input type="number" value="' + val.width + '" name="option[' + key + '][width]" data-required="yes" onchange="adjustColWidth()"/> cm </td>';
            }
        });

        jQuery("#batchHeadTitles").prepend(headerTh);

        if (jQuery("#batchHeadTitles th:last-child").find('i.fas.fa-plus').length === 0) {
            // Add column button placeholder
        }

        jQuery("#batchHeadWidths").prepend(headerTd);

        if (jQuery("#batchHeadTitles th").length !== jQuery("#batchHeadWidths td").length) {
            jQuery("#batchHeadWidths").append('<td><i class="fas fa-arrows-alt" onclick="sortProducts()"></i></td>');
        }
        adjustColWidth();
    }
}

function insertExcelProducts(data) {
    var jsonObjs = JSON.parse(data);
    var thisTime = Date.now();
    jQuery("#batchProducts").html('');
    jQuery("#excelCertificateItems").val('');

    jQuery("#batchHead tr").each(function() {
        jQuery(this).find('.extra').remove();
    });

    jQuery("#batchProducts tr").each(function() {
        jQuery(this).find('.extra').remove();
    });

    insertProductTitle(true);
    jQuery("#batchFooter").prop("colspan", jQuery("#batchHead .productTitleTh").length + 1);

    var defFields = ['description', 'quantity'];

    jQuery.each(jsonObjs, function(objkey, fields) {
        thisTime = thisTime + 60;
        var thisItem = '<tr>';
        jQuery.each(fields, function(key, val) {
            var tdClass = defFields[key] == null ? ' class="extra"' : '';
            var td = '<td' + tdClass + '><input type="text" name="products[' + thisTime + '][' + (defFields[key] != null ? defFields[key] : 'extra_' + (key - 2)) + ']" value="' + (val != null ? val : '') + '"></td>';
            thisItem += td;
        });
        thisItem += '<td> <img title="Delete product" src = "../images/delete.gif" border="0" onclick="deleteProduct(this)"> </td> </tr>';
        jQuery("#batchProducts").append(thisItem);
        adjustColWidth();
        resetSortable();
    });
}

function getExcelCertificateItems(obj) {
    if (obj.value.trim() !== '') {
        var fd = new FormData();
        var files = jQuery(obj)[0].files;
        var inputs = "<?php echo isset($inputItems) ? implode(',', $inputItems) : ''; ?>";
        if (files.length > 0) {
            fd.append('file', files[0]);
            fd.append('inputs', inputs);
            jQuery.ajax({
                url: 'import_excel_file.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(data) {
                    if (data !== '0') {
                        if (data.indexOf('error:') > -1) {
                            alert_message(data.replace('error:', ''));
                        } else {
                            insertExcelProducts(data);
                        }
                    } else {
                        alert_message('File not uploaded');
                    }
                },
            });
        }
    } else {
        alert_message("Please select a file.");
    }
}

function saveExcelCertificateItems() {
    var obj = jQuery("#batchProducts");
    var expItems = [];
    var headerItems = [];
    
    jQuery(obj).parent('table').find('th').each(function() {
        var headerText = jQuery(this).text().replace(/1|2|\*|::/, '');
        if (headerText.trim() !== '') {
            headerItems.push(headerText.trim());
        }
    });
    expItems.push(headerItems);
    
    jQuery(obj).find('tr').each(function() {
        var tableItems = [];
        jQuery(this).find('input').each(function() {
            tableItems.push(this.value.trim());
        });
        expItems.push(tableItems);
    });
    
    expItems = JSON.stringify(expItems);
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "export_excel_file.php");
    var itemInput = document.createElement("input");
    itemInput.setAttribute("type", "hidden");
    itemInput.setAttribute("name", "items");
    itemInput.setAttribute("value", expItems);
    form.appendChild(itemInput);
    document.body.appendChild(form);
    form.submit();
    form.remove();
}

function addProduct() {
    var count = jQuery("#batchProducts tr").length;
    var newRow = jQuery("#batchProducts tr").last().html();
    
    if (!newRow) {
        // Create first row
        var thisTime = Date.now();
        newRow = '<td><input type="text" name="products[' + thisTime + '][description]" value=""></td>' +
                 '<td><input type="text" name="products[' + thisTime + '][quantity]" value=""></td>' +
                 '<td><img title="Delete product" src="../images/delete.gif" border="0" onclick="deleteProduct(this)"></td>';
        jQuery("#batchProducts").append('<tr>' + newRow + '</tr>');
    } else {
        jQuery("#batchProducts").append('<tr>' + newRow + '</tr>');
        jQuery("#batchProducts tr").last().find('input').each(function() {
            jQuery(this).val('');
            jQuery(this).prop('id', jQuery(this).prop('id').replace(count - 1, count));
            jQuery(this).prop('name', jQuery(this).prop('name').replace(count - 1, count));
        });
    }
    resetSortable();
}

function deleteProduct(obj) {
    jQuery(obj).closest('tr').remove();
    resetSortable();
}

function fillProductTitle() {
    resetSortable();
    jQuery("#batchHead .productTitleTh").each(function() {
        if (jQuery(this).find('input').length > 0) {
            jQuery(this).find('input').remove();
        }

        var english = jQuery(this).find('.english');
        var englishTitle = jQuery(english).prop('id').split('_');
        if (englishTitle[0] === 'extra') {
            englishTitle = englishTitle[0] + '_' + englishTitle[1];
        } else {
            englishTitle = englishTitle[0];
        }
        var englishValue = jQuery(english).html();

        jQuery(this).append('<input type="hidden" name="option[' + englishTitle + '][english]" value="' + englishValue + '">');
    });
    jQuery("#batchFooter").prop("colspan", jQuery("#batchHead .productTitleTh").length + 1);
}

function checkFSDA() {
    var tp = '<?php echo $_GET['tp']; ?>';
    if (jQuery("#selectedImporter").val() !== '') {
        if (jQuery("#selectedImporter option:selected").data('sfda') === 'yes') {
            jQuery("#tmplid option").each(function() {
                if (jQuery(this).data('sfda') === 'no') {
                    jQuery(this).css("display", "none").removeAttr('selected');
                }
            });
            if (tp === 'a' || tp === 'b') {
                var sab = "S" + tp.toUpperCase();
                var url = "<a href='/iidc/admin/?inc=certificates&tp=s" + tp + "'>Click here to switch to certificate type " + sab + "</a>";
                jQuery("#CRN").html("Please use certificates for Saudi Arabia type (" + sab + ')<br/>' + url);
                jQuery("#sabWarning").html(jQuery("#CRN").html());
                jQuery("#formActionButtons").hide();
            }
            jQuery("#CRN").show();
            if (jQuery("#CRNValue").length) {
                jQuery("#CRNValue").val(jQuery("#selectedImporter option:selected").data('crn')).attr("data-required", "yes");
            }
        } else {
            jQuery("#tmplid option").css("display", "block");
            jQuery("#CRN").hide();
            jQuery("#formActionButtons").show();
            jQuery("#sabWarning").html('');
            if (jQuery("#CRNValue").length) {
                jQuery("#CRNValue").val('').removeAttr("data-required");
            }
        }
    } else {
        jQuery("#tmplid option").css("display", "block");
        jQuery("#CRN").hide();
        jQuery("#formActionButtons").show();
        jQuery("#sabWarning").html('');
        if (jQuery("#CRNValue").length) {
            jQuery("#CRNValue").val('').removeAttr("data-required");
        }
    }
}

function restoreFontSizes() {
    jQuery("#productsHeadFontSize").val(12);
    jQuery("#productsFontSize").val(12);
}

function printAndSend(obj) {
    if (jQuery(obj).is(':checked')) {
        jQuery("#actionPrint").find('span').text('Email Certificate');
    } else {
        jQuery("#actionPrint").find('span').text('Print');
    }
}

async function deleteAttachment(obj) {
    var objectParent = jQuery(obj).parent('li').find('a');
    var file = jQuery(objectParent).attr('href');
    var fileName = jQuery(objectParent).text();
    
    await confirm_message("Are you sure you want to delete this attachment?<br/><span style='color:red'>" + fileName + '</span>');
    
    jQuery.ajax({
        url: "certificate_save.php",
        type: "POST",
        data: {
            act: 'delete_file',
            file: file,
            nr: '<?php echo isset($_GET['nr']) ? $_GET['nr'] : ''; ?>',
            tp: '<?php echo $_GET['tp']; ?>'
        },
        success: function(data) {
            if (data !== '') {
                jQuery(obj).parent('li').remove();
            }
        }
    });
}

// Initialize on document ready
jQuery(document).ready(function() {
    checkFSDA();
    insertProductTitle();
    fillProductTitle();
    
    jQuery("#selectedImporter").on("change", function() {
        checkFSDA();
    });
    
    // Load products
    jQuery("#batchProducts").load("load_products.php" + location.search, function() {
        resetSortable();
    });
});
</script>

<style>
.newClient {
    color: red;
}

input#excelCertificateItems {
    position: absolute;
    left: -5000px;
}

#batchProducts input[type=text] {
    width: 100%;
}

#batchHead textarea {
    width: 100%;
    height: 50px;
    margin: 5px 0px;
}

#batchHead th {
    font-weight: normal;
    position: relative;
    max-width: 100%;
    width: 20%;
}

#batchHead th:first-child {
    width: 80%;
}

.productTitle.active {
    padding: 5px;
    background: beige;
    border: 1px solid darkgreen;
    margin-bottom: 5px;
}

.columnTools {
    margin-bottom: 10px;
    padding: 5px 0px;
    border-bottom: 1px solid white;
}

.columnTools i {
    margin: 0px 10px;
    cursor: pointer;
    color: grey;
}

div.productTitle {
    white-space: normal;
}

#batchHeadWidths td {
    text-align: center;
    vertical-align: middle;
}

#batchProducts td {
    vertical-align: middle;
}

#batchProducts i.fas.fa-arrows-alt {
    color: cadetblue;
    cursor: move;
}

#batchProducts tr:first-child td:last-child img {
    display: none;
}

#batchHeadWidths td input {
    width: 50px;
}
</style>

<?php
if (isset($_SESSION['offid']) && $_SESSION['offid'] != '0')
	$_GET['offid'] = $_SESSION['offid'];
if (!isset($_GET['offid']))
	$_GET['offid'] = 0;
$batchOffices[] = $_GET['offid'];

if (is_array(json_decode($client_extra, true))) {
	$client_extra = json_decode($client_extra, true);
	if (isset($client_extra['shipment_approval']) && $client_extra['shipment_approval'] == 'yes') {
		$approval_required = 'yes';
	}
}

include "../config/countries.code.php";

$cert_types_info = [
    'a' => ['name' => 'Slaughtering Certificate', 'short' => 'Type A', 'desc' => 'Raw / Fresh / Frozen Meats (Unprocessed)', 'icon' => 'fa-drumstick-bite', 'class' => 'type-a'],
    'b' => ['name' => 'Non-Meat Certificate', 'short' => 'Type B', 'desc' => 'Non-Fresh Meats / Foods / Beverages / Cosmetics', 'icon' => 'fa-leaf', 'class' => 'type-b'],
    'sa' => ['name' => 'Slaughtering Certificate', 'short' => 'Type SA', 'desc' => 'Saudi Arabia - Raw / Fresh / Frozen Meats', 'icon' => 'fa-drumstick-bite', 'class' => 'type-sa'],
    'sb' => ['name' => 'Non-Meat Certificate', 'short' => 'Type SB', 'desc' => 'Saudi Arabia - Non-Fresh Meats / Foods / Cosmetics', 'icon' => 'fa-leaf', 'class' => 'type-sb']
];

$currentType = isset($_REQUEST['tp']) ? $_REQUEST['tp'] : 'a';
$certInfo = isset($cert_types_info[$currentType]) ? $cert_types_info[$currentType] : $cert_types_info['a'];
$isSaudi = in_array($currentType, ['sa', 'sb']);
$isEdit = isset($_GET['act']) && $_GET['act'] == 'edit';
$actionText = $isEdit ? 'Update' : 'Issue';
$actionClass = $isEdit ? 'update' : 'issue';
?>

<div class="certificate-page-header">
    <div class="certificate-header-content">
        <div class="certificate-header-icon <?php echo $certInfo['class']; ?>">
            <i class="fas <?php echo $certInfo['icon']; ?>"></i>
        </div>
        
        <div class="certificate-header-info">
            <h2>
                <?php echo $certInfo['name']; ?>
                <span class="action-badge <?php echo $actionClass; ?>">
                    <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus-circle'; ?>" style="margin-right:4px;"></i>
                    <?php echo $actionText; ?>
                </span>
            </h2>
            <p><?php echo $certInfo['desc']; ?> • For <strong><?php echo $company_name; ?></strong></p>
        </div>
        
        <div class="certificate-header-meta">
            <span class="cert-type-tag <?php echo $certInfo['class']; ?>">
                <i class="fas <?php echo $certInfo['icon']; ?>"></i>
                <?php echo $certInfo['short']; ?>
            </span>
            
            <?php if (isset($office)) { ?>
                <span class="office-tag">
                    <i class="fas fa-building"></i>
                    <?php echo $office['office_name']; ?>
                </span>
            <?php } ?>
        </div>
    </div>
</div>

<div class="cert-form-container <?php echo $certInfo['class']; ?>">
    <!-- Validation Error Box -->
    <div class="validation-error-box" id="validationErrorBox">
        <div class="validation-error-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h4>Please correct the following errors:</h4>
        </div>
        <ul class="validation-error-list" id="validationErrorList"></ul>
    </div>

    <form name="certificateForm" id="certificateForm" method="post" action="" enctype="multipart/form-data">
        <input type="hidden" name="offid" value="<?php echo $_GET['offid']; ?>">
        <input type="hidden" name="clid" value="<?php echo $clid; ?>">
        <input type="hidden" name="act" id="act" value="">
        <input type="hidden" name="tp" id="tp" value="<?php echo $_REQUEST['tp']; ?>">
        <input type="hidden" name="nr" id="nr" value="<?php echo @$nr; ?>">
        <input type="hidden" name="company_name" value="<?php echo @str_replace('"', '&quot;', trim($company_name)); ?>">
        <input type="hidden" name="do" id="do" value="">
        <?php if (isset($approval_required)) { ?>
            <input type="hidden" name="approval_required" value="yes">
        <?php } ?>
        <?php if (isset($certificate_nr)) { ?>
            <input type="hidden" name="certificate_nr" value="<?php echo @$certificate_nr; ?>">
        <?php } ?>
        <?php make_nonce(); ?>
        
        <table id="reqCerts" style="border:1px solid #EEE; width:100%" class="table table-striped">
            <tr>
                <td colspan="2" class="sub_title">
                    <center><?php echo $certificate_title[$_REQUEST['tp']]; ?></center>
                </td>
            </tr>
            
            <?php if ($user_type == 'hqc_office' or $user_type == "admin") { ?>
                <tr>
                    <th>Certificate for:</th>
                    <td>
                        <b><?php echo $company_name; ?></b>
                        <?php if (isset($_GET['offid'])) {
                            if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid='" . $_GET['offid'] . "'")) {
                                echo " <span style='color:green;margin-left:50px'>Issued By Office: <b>" . $office['office_name'] . "</b></span>";
                            }
                        } ?>
                    </td>
                </tr>
                <tr>
                    <th>Certificate Issuing date*:</th>
                    <td><input type="text" class="date" placeholder="Issue date" name="issue_date" value="<?php echo ($act == 'edit' && trim($row['issue_date'])) ? $row['issue_date'] : ''; ?>" data-required="yes" /></td>
                </tr>
            <?php } ?>
            
            <tr>
                <th>Country of origin*:</th>
                <td>
                    <select name="country_of_origin" size="1" style="width: 220px" data-required="y">
                        <option value="">Select country</option>
                        <?php foreach ($country as $key => $value) { ?>
                            <option value="<?php echo $key; ?>" <?php echo (isset($row['country_of_origin']) && $row['country_of_origin'] == $key) ? "selected" : ""; ?>><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th>Quantity – Quality*:</th>
                <td><input type="text" name="quality" value="<?php echo ($act == 'edit' && trim($row['quality'])) ? $row['quality'] : ''; ?>" data-required="yes" size="55" /></td>
            </tr>
            
            <?php
            $weight_gross_gram = '';
            if (isset($row) && isset($row['weight_gross']) && trim($row['weight_gross']) != '') {
                $row['weight_gross'] = explode('.', $row['weight_gross'] . '.');
                $weight_gross_gram = $row['weight_gross'][1];
                $row['weight_gross'] = $row['weight_gross'][0];
            }

            $weight_net_gram = '';
            if (isset($row) && isset($row['weight_net']) && trim($row['weight_net']) != '') {
                $row['weight_net'] = explode('.', $row['weight_net'] . '.');
                $weight_net_gram = $row['weight_net'][1];
                $row['weight_net'] = $row['weight_net'][0];
            }
            ?>
            <tr>
                <th>Weight*:</th>
                <td>
                    <span style="display: inline-block;width:100px;font-weight:bold">Gross weight:</span>
                    <input data-required="y" type="number" class="number" name="weight_gross" value="<?php echo @$row['weight_gross']; ?>" size="15" placeholder="KG [Kilogram]"> ,
                    <input type="number" name="weight_gross_gram" style="width: 100px;" class="number" placeholder="G [Grams]" value="<?php echo $weight_gross_gram; ?>" max="99" />
                    <br />
                    <span style="display: inline-block;width:100px;font-weight:bold">Net weight:</span>
                    <input type="number" name="weight_net" value="<?php echo @$row['weight_net']; ?>" size="15" data-required="y" style="margin-top:10px;" class="number" placeholder="KG [Kilogram]"> ,
                    <input type="number" name="weight_net_gram" style="width: 100px;" class="number" placeholder="G [Grams]" value="<?php echo $weight_net_gram; ?>" max="99" />
                    <br />
                    <span style="color:#666; font-size:12px;">NOTE: Please insert the KILOGRAMS in the left field and the GRAMS in the right field.<br />
                    Maximum 2 decimals can apply within the GRAMS field.<br />
                    EXAMPLE: 1,75 KG = 1 Kilogram and 750 Grams.</span>
                </td>
            </tr>
            
            <tr>
                <th>Transportation:</th>
                <td style="vertical-align:top !important;">
                    <div>
                        <b style="float:left;width:100px">Method:</b>
                        <select name="transportation_method">
                            <option value="Vessel Container">Vessel Container</option>
                            <option value="Truck" <?php echo ($act == "edit" && $row['transportation_method'] == "Truck") ? "selected" : ""; ?>>Truck</option>
                            <option value="Air freight" <?php echo ($act == "edit" && $row['transportation_method'] == "Air freight") ? "selected" : ""; ?>>Air freight</option>
                            <option value="YM Unity Container" <?php echo ($act == "edit" && $row['transportation_method'] == "YM Unity Container") ? "selected" : ""; ?>>YM Unity Container</option>
                            <option value="YM Uniform" <?php echo ($act == "edit" && $row['transportation_method'] == "YM Uniform") ? "selected" : ""; ?>>YM Uniform</option>
                        </select>
                    </div>
                    <div style="margin-top: 10px;">
                        <b style="float:left;width:100px">Details:</b>
                        <textarea name="transportation_nr" style="width:550px"><?php echo @$row['transportation_nr']; ?></textarea>
                    </div>
                </td>
            </tr>
            
            <tr>
                <th>Loading port & destination*:</th>
                <td>
                    <b style="display:inline-block;width:100px;">Loading port:</b>
                    <input type="text" name="loading_port" value="<?php echo @$row['loading_port']; ?>" size="45" data-required="yes" placeholder="Loading port (country or city)">
                    <div style="margin-top:5px;">
                        <b style="display:inline-block;width:100px;">Destination:</b>
                        <input type="text" name="destination" value="<?php echo @$row['destination']; ?>" size="45" data-required="yes" placeholder="Destination (country or city)">
                    </div>
                </td>
            </tr>
            
            <tr>
                <th>Exporter*:</th>
                <td>
                    <select name="exporter" data-required="y" style="float:left;margin-right:10px;width:45%" onchange="changeOption(this)">
                        <option value=''>Select Exporter</option>
                        <!--<option value='newClient' class="newClient">Add Exporter</option>-->
                        <?php if ($_SESSION['user_type'] != 'client') { ?>
                            <option value='0' style="color:red" <?php echo (isset($act) && $act == 'edit' && $exporter == '0') ? 'selected' : ''; ?>>Other</option>
                        <?php } ?>
                        <?php
                        if (isset($act) && $act == 'edit' && $exporter != '0' && isset($companies[$exporter]))
                            $exporters = str_replace("value='$exporter'", "value='$exporter' selected", $exporters);
                        echo $exporters;
                        ?>
                    </select>
                    <?php if ($_SESSION['user_type'] != 'client') { ?>
                        <textarea name="option[exporter]" id="option_exporter" style="width:50%;height:60px;display:<?php echo (isset($act) && $act == 'edit' && $exporter == '0') ? 'inherit' : 'none'; ?>" placeholder="Exporter name and address"><?php echo (isset($option['exporter'])) ? $option['exporter'] : ''; ?></textarea>
                    <?php } ?>
                </td>
            </tr>
            
            <tr>
                <th>Importer*:</th>
                <td>
                    <select name="importer" id="selectedImporter" data-required="y" style="float:left;margin-right:10px;width:45%" onchange="changeOption(this)">
                        <option value=''>Select <?php echo in_array($_GET['tp'], ['sa', 'sb']) ? 'Saudi ' : ''; ?>Importer</option>
                        <!--<option value='newClient' class="newClient">Add Importer</option>-->
                        <?php if ($_SESSION['user_type'] != 'client') { ?>
                            <option value='0' style="color:red" <?php echo (isset($act) && $act == 'edit' && $importer == '0') ? 'selected' : ''; ?>>Other</option>
                        <?php } ?>
                        <?php
                        if (isset($act) && $act == 'edit' && $importer != '0' && isset($companies[$importer]))
                            $importers = str_replace("value='$importer'", "value='$importer' selected", $importers);
                        echo $importers;
                        ?>
                    </select>
                    <span id="CRN" style="display: none;">
                        <b>Commercial registration number*:</b>
                        <input type="text" name="CRN" id="CRNValue" style="width:120px" value="<?php echo @$row['CRN']; ?>"> 10 numbers
                    </span>
                    <?php if ($_SESSION['user_type'] != 'client') { ?>
                        <textarea name="option[importer]" id="option_importer" style="width:50%;height:60px;display:<?php echo (isset($act) && $act == 'edit' && $importer == '0') ? 'inherit' : 'none'; ?>" placeholder="Importer name and address"><?php echo (isset($option['importer'])) ? $option['importer'] : ''; ?></textarea>
                    <?php } ?>
                </td>
            </tr>
            
            <tr>
                <th>Producer/production plant*:</th>
                <td>
                    <select name="producer" data-required="y" style="float:left;margin-right:10px;width:45%;" onchange="changeOption(this)">
                        <option value=''>Select Producer</option>
                        <!--<option value='newClient' class="newClient">Add Producer</option>-->
                        <?php if ($_SESSION['user_type'] != 'client') { ?>
                            <option value='0' style="color:red" <?php echo (isset($act) && $act == 'edit' && $producer == '0') ? 'selected' : ''; ?>>Other</option>
                        <?php } ?>
                        <?php
                        if (isset($act) && $act == 'edit' && $producer != '0' && isset($companies[$producer]))
                            $producers = str_replace("value='$producer'", "value='$producer' selected", $producers);
                        echo $producers;
                        ?>
                    </select>
                    <?php if ($_SESSION['user_type'] != 'client') { ?>
                        <textarea name="option[producer]" id="option_producer" style="width:50%;height:60px;display:<?php echo (isset($act) && $act == 'edit' && $producer == '0') ? 'inherit' : 'none'; ?>" placeholder="Producer name and address"><?php echo (isset($option['producer'])) ? $option['producer'] : ''; ?></textarea>
                    <?php } ?>
                </td>
            </tr>

            <?php if ($_REQUEST['tp'] == 'a' || $_REQUEST['tp'] == 'sa') { ?>
                <tr>
                    <th>Slaughtering date*:</th>
                    <td><input data-required="y" type="text" class="date" name="slaughtering_date" id="slaughtering_date" value="<?php echo @$row['slaughtering_date']; ?>"></td>
                </tr>
            <?php } ?>
            
            <tr>
                <th>Production date:</th>
                <td><input type="text" class="date" name="production_date" id="production_date" value="<?php echo @$row['production_date']; ?>"></td>
            </tr>
            
            <tr>
                <th>Expiry date*:</th>
                <td><input data-required="y" type="text" class="date" name="expiry_date" id="expiry_date" value="<?php echo @$row['expiry_date']; ?>"></td>
            </tr>
            
            <tr>
                <th>Health Certificate No.:</th>
                <td><input type="text" name="hcd_nr" value="<?php echo @$row['hcd_nr']; ?>" size="25"></td>
            </tr>
            
            <tr>
                <th>Slaughter house:</th>
                <td>
                    <textarea name="slaughter_house" style="width:550px"><?php echo @$row['slaughter_house']; ?></textarea>
                    <br /><i>Name & no. of slaughter house</i>
                </td>
            </tr>
            
            <tr>
                <th>Method of slaughtering:</th>
                <td><input type="text" name="method_of_slaughtering" value="<?php echo @$row['method_of_slaughtering']; ?>" size="55"></td>
            </tr>
            
            <tr>
                <th>Slaughtering Supervisor*:</th>
                <td><input type="text" name="slaughterer_name" value="<?php echo @$row['slaughterer_name']; ?>" size="55" data-required="yes"></td>
            </tr>
            
            <tr>
                <th class="sub_title" colspan="2">Appendix (please list your items in this field):</th>
            </tr>
            
            <tr>
                <td colspan="2">
                    <table>
                        <thead id="batchHead">
                            <tr id="batchHeadTitles">
                                <?php
                                foreach ($option as $key => $opValue) {
                                    if ($key == 'artNt') continue;
                                    if (isset($opValue['title']) && $opValue['title'] != '') {
                                        $opValue['english'] = $opValue['title'];
                                    }
                                    if (isset($opValue['english'])) { ?>
                                        <th class="productTitleTh<?php echo strstr($key, 'extra_') ? ' extra' : ''; ?>" style="text-align: center;">
                                            <div id="<?php echo $key; ?>_english" class="productTitle english"><?php echo $opValue['english']; ?></div>
                                        </th>
                                    <?php }
                                } ?>
                                <th style="width:20px;"></th>
                            </tr>
                        </thead>
                        <tbody id="batchProducts"></tbody>
                        <tfoot>
                            <tr>
                                <td id="batchFooter" colspan="<?php echo isset($quantity_titles) ? 5 + count($quantity_titles) : '5'; ?>" style="text-align:center">
                                    <label style="background: gainsboro; padding: 5px 10px; color: darkred;">
                                        <input type="checkbox" name="option[landscape]" id="option_landscape" onchange="adjustColWidth()" value="yes" <?php echo (isset($option['landscape'])) ? 'checked' : ''; ?>> Landscape Appendix
                                    </label>
                                    <b>Font sizes:</b> Table head 
                                    <input min="5" max="20" id="productsHeadFontSize" style="width:50px" type="number" name="option[products-head-font-size]" value="<?php echo isset($option['products-head-font-size']) ? $option['products-head-font-size'] : '12'; ?>" /> point, 
                                    Table products 
                                    <input min="5" max="20" id="productsFontSize" style="width:50px;" type="number" name="option[products-font-size]" value="<?php echo isset($option['products-font-size']) ? $option['products-font-size'] : '12'; ?>" /> point 
                                    <i class="fas fa-undo-alt" style="font-size:14px !important" onclick="restoreFontSizes()" title="Default font sizes"></i>
                                    <br />
                                    <input type="button" onclick="addProduct()" value="Add item" style="margin-left:50px !important" />
                                    <label class="button">Import items from excel file
                                        <input type="file" name="excelCertificateItems" id="excelCertificateItems" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" onchange="getExcelCertificateItems(this)" />
                                    </label>
                                    <input type="button" value="Export items to excel file" onclick="saveExcelCertificateItems()" />
                                    <br /><a href="/data/templates/Certificate-appendix.xlsx">You can use the following Certificate-appendix template to upload the items</a>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>

            <?php if ($_SESSION['user_type'] != 'client') { ?>
                <?php if ($act == 'edit') { ?>
                    <tr>
                        <th>Options:</th>
                        <td>
                            <label><input type="checkbox" name="keepOldCrtNumber" checked><b>Keep old certificate Nr:</b> <?php echo $row['certificate_nr']; ?></label>
                        </td>
                    </tr>
                <?php } ?>

                <?php if (isset($office_options) && isset($office_options['certificates_by_email']) && $office_options['certificates_by_email'] == 'yes') { ?>
                    <tr>
                        <th>HQC options:</th>
                        <td>
                            <label><input type="checkbox" value="1" name="option[HQCstamp]" id="HQCstamp" <?php echo isset($option['HQCstamp']) ? 'checked' : ''; ?> />Print HQC stamp</label>
                            <label><input type="checkbox" value="1" name="option[HQCsignature]" id="HQCsignature" <?php echo isset($option['HQCsignature']) ? 'checked' : ''; ?> />Print HQC signature</label>
                        </td>
                    </tr>
                    <tr>
                        <th>Send by Email:</th>
                        <td>
                            <label><input type="checkbox" name="option[sub_act]" value="email" onclick="printAndSend(this)" <?php echo isset($option['sub_act']) && $option['sub_act'] == 'email' ? 'checked' : ''; ?>>Send certificate by email</label> To:
                            <input type="text" name="option[to_email]" value="<?php echo isset($option['to_email']) ? $option['to_email'] : $company_email; ?>" placeholder="Email address" style="width:40%">
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
            
            <tr>
                <th>Attachments:</th>
                <td>
                    <input type="file" name="attachment[]" multiple>
                    <?php if (isset($row['attachments']) && trim($row['attachments']) != '' && is_array(decode_json($row['attachments']))) {
                        $attachments = decode_json($row['attachments']);
                        if (count($attachments) > 0) {
                            echo "<ol>";
                            foreach ($attachments as $attachment) { ?>
                                <li style="padding:5px">
                                    <a href='<?php echo $attachment; ?>' target='_blank'><?php echo basename($attachment); ?></a>
                                    <i class="far fa-trash-alt" style="font-size:12px !important;color:red;margin-left:20px" onclick="deleteAttachment(this)"><span>Delete</span></i>
                                </li>
                            <?php }
                            echo "</ol>";
                        }
                    } ?>
                </td>
            </tr>
            
            <tr id="formActionButtons">
                <td colspan="2">
                    <div class="cert-form-footer">
                        <button type="button" class="btn-form-action cancel" onclick="history.go(-1)">
                            <i class="fas fa-arrow-left"></i>
                            Cancel
                        </button>
                        <button type="button" class="btn-form-action reset" onclick="document.certificateForm.reset()">
                            <i class="fas fa-undo"></i>
                            Reset
                        </button>
                        <button type="button" class="btn-form-action preview" onclick="preview()">
                            <i class="fas fa-eye"></i>
                            Preview
                        </button>
                        <?php if ($act == 'add') { ?>
                            <button type="button" class="btn-form-action draft" onclick="save_hc('draft')">
                                <i class="fas fa-save"></i>
                                Save Draft
                            </button>
                        <?php } else if ($act == 'edit') {
                            if ($row['status'] == 'draft') { ?>
                                <button type="button" class="btn-form-action draft" onclick="save_hc('saveDraft')">
                                    <i class="fas fa-save"></i>
                                    Update Draft
                                </button>
                            <?php } else { ?>
                                <button type="button" class="btn-form-action draft" onclick="save_hc('save')">
                                    <i class="fas fa-save"></i>
                                    Update
                                </button>
                            <?php }
                        } ?>
                        <?php if ($_SESSION['user_type'] == 'client') { ?>
                            <button type="button" class="btn-form-action primary" onclick="save_hc('request')">
                                <i class="fas fa-paper-plane"></i>
                                Request Certificate
                            </button>
                        <?php } else { ?>
                            <button type="button" class="btn-form-action primary" id="actionPrint" onclick="save_hc('print')">
                                <i class="fas fa-print"></i>
                                <span>Print</span>
                            </button>
                        <?php } ?>
                    </div>
                </td>
            </tr>
        </table>
        
        <div id="sabWarning" class="sab-warning" style="display:none;"></div>
        <input type="hidden" name="tmplid" value="<?php echo $act == 'edit' ? $row['tmplid'] : $offid; ?>">
    </form>
</div>