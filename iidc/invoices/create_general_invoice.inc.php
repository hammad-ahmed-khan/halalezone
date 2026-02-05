<script language="javascript">
    $("#page_title").html("Create General invoices")
</script>
<style>
	/* ============================================
   IIDC Certificate Page - Professional Styling
   Apply to existing HTML without modifications
   ============================================ */
/* Create Invoice Page Header */
.create-invoice-header {
    background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
    border-radius: 12px;
    border: 1px solid #e0e7ff;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.create-invoice-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.create-invoice-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.create-invoice-header-info {
    flex: 1;
    min-width: 200px;
}

.create-invoice-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.create-invoice-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Invoice Type Badge */
.invoice-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.invoice-type-badge.general {
    background: #e0e7ff;
    color: #4338ca;
}

.invoice-type-badge.credit {
    background: #fce7f3;
    color: #be185d;
}

.invoice-type-badge.annual {
    background: #dcfce7;
    color: #166534;
}

.invoice-type-badge.batch {
    background: #fef3c7;
    color: #92400e;
}

.invoice-type-badge.recurring {
    background: #f0f9ff;
    color: #0369a1;
}

.invoice-type-badge i {
    font-size: 10px;
}

/* Back Button */
.btn-back-invoices {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #4f46e5;
    background: #ffffff;
    border: 2px solid #e0e7ff;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-back-invoices:hover {
    background: #f5f3ff;
    border-color: #c7d2fe;
    color: #4338ca;
    text-decoration: none;
}

/* Company Selection Section */
.company-select-section {
    padding: 24px 32px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.company-select-section label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.company-select-wrapper {
    display: flex;
    gap: 12px;
    align-items: stretch;
}

.company-select-wrapper select {
    flex: 1;
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

.company-select-wrapper select:hover {
    border-color: #4f46e5;
    background-color: #fafafe;
}

.company-select-wrapper select:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
}

.company-select-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    padding: 12px 16px;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    color: #64748b;
}

.company-select-hint i {
    color: #4f46e5;
}

/* Step Indicator */
.step-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #fefce8;
    border: 1px solid #fde68a;
    border-radius: 8px;
    font-size: 13px;
    color: #92400e;
    margin-left: auto;
}

.step-indicator .step-number {
    width: 24px;
    height: 24px;
    background: #f59e0b;
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}

/* Responsive */
@media (max-width: 768px) {
    .create-invoice-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .create-invoice-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .company-select-section {
        padding: 20px;
    }
    
    .company-select-wrapper {
        flex-direction: column;
    }
    
    .step-indicator {
        margin-left: 0;
        justify-content: center;
    }
}

/* Reset & Base */
* {
  box-sizing: border-box;
}



/* Page Title */
h2.content_title {
  font-size: 28px;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 12px 0;
  line-height: 1.3;
  text-align: center;
  position: relative;
  margin-top: 20px;
 }

/* Style the span inside title (IIDC Austria) */
h2.content_title span {
  color: #1a5f4a !important;
  font-weight: 700;
}

/* Center container styling */
center.issue-certificate-for {
  display: block;
  padding: 32px;
  background: linear-gradient(135deg, #f8faf9 0%, #f0fdf4 100%);
  border-radius: 16px;
  border: 1px solid rgba(26, 95, 74, 0.1);
}

/* Label text */
center.issue-certificate-for > b {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  margin-bottom: 16px;
  text-align: left;
}

 
/* Select Dropdown */
select[name="clid"] {
  display: block;
  width: 100%;
  padding: 18px 48px 18px 20px;
  font-family: 'DM Sans', sans-serif;
  font-size: 15px;
  font-weight: 500;
  color: #1e293b;
  background-color: #ffffff;
  border: 2px solid #e2e8f0;  
  cursor: pointer;
  transition: all 0.25s ease;
  
  /* Custom arrow */
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 16px center;
  background-size: 20px;
}

select[name="clid"]:hover {
  border-color: #1a5f4a;
  background-color: #fafffe;
}

select[name="clid"]:focus {
  outline: none;
  border-color: #1a5f4a;
  box-shadow: 0 0 0 4px rgba(26, 95, 74, 0.12);
  background-color: #ffffff;
}

/* Style the options */
select[name="clid"] option { 
  padding: 12px 16px;
  font-size: 14px;
  background: #ffffff;
  color: #1e293b;
}

select[name="clid"] option:first-child {
  color: #94a3b8;
  font-style: italic;
}

select[name="clid"] option:checked {
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  color: #166534;
}

/* Add visual indicator and button after select */
center.issue-certificate-for::after {
  content: 'Select a company from the dropdown above to proceed';
  display: block;
  margin-top: 20px;
  padding: 14px 20px;
  font-size: 13px;
  color: #64748b;
  background: #ffffff;
  border-radius: 10px;
  border: 1px dashed #cbd5e1;
  text-align: center;
}

@keyframes pulse {
  0%, 100% { opacity: 0.85; }
  50% { opacity: 1; }
}

/* Footer info */
.page-content::after {
  content: '';
  display: block;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid rgba(26, 95, 74, 0.1);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .main-content-inner {
    padding: 32px 16px;
  }
  
  .page-content {
    padding: 32px 24px;
    border-radius: 20px;
  }
  
  h2.content_title {
    font-size: 22px;
  }
  
  center {
    padding: 24px 20px;
  }
  
  select[name="clid"] {
    padding: 16px 44px 16px 16px;
    font-size: 14px;
  }
  
  .main-content-inner::before {
    font-size: 10px;
    padding: 12px 16px;
  }
}

/* Hide jQuery script visual artifacts */
script {
  display: none !important;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Selection color */
::selection {
  background: rgba(26, 95, 74, 0.2);
  color: #0f172a;
}

</style>
<script language="javascript">
    $("#page_title").html("Create General Invoice")
</script>

<?php
// Define invoice types for different styling
$invoice_types = [
    'general' => ['name' => 'General Invoice', 'icon' => 'fa-file-invoice', 'class' => 'general', 'desc' => 'Create a general service invoice'],
    'credit' => ['name' => 'Credit Note', 'icon' => 'fa-minus-circle', 'class' => 'credit', 'desc' => 'Issue a credit note for an existing invoice'],
    'credit_note' => ['name' => 'Credit Note', 'icon' => 'fa-minus-circle', 'class' => 'credit', 'desc' => 'Issue a credit note for an existing invoice'],
    'annual' => ['name' => 'Annual Certificate', 'icon' => 'fa-certificate', 'class' => 'annual', 'desc' => 'Invoice for annual halal certificate'],
    'batch' => ['name' => 'Shipment Certificate', 'icon' => 'fa-shipping-fast', 'class' => 'batch', 'desc' => 'Invoice for shipment/batch certificate'],
    'recurring' => ['name' => 'Recurring Invoice', 'icon' => 'fa-sync', 'class' => 'recurring', 'desc' => 'Monthly recurring invoice'],
    'audit' => ['name' => 'Audit Invoice', 'icon' => 'fa-clipboard-check', 'class' => 'general', 'desc' => 'Invoice for audit services'],
    'supervision' => ['name' => 'Supervision Invoice', 'icon' => 'fa-eye', 'class' => 'general', 'desc' => 'Invoice for halal supervision services']
];

$currentType = isset($_GET['type']) ? $_GET['type'] : 'general';
$typeInfo = isset($invoice_types[$currentType]) ? $invoice_types[$currentType] : $invoice_types['general'];
$goBack = isset($_GET['goback']) ? $_GET['goback'] : 'invoices&show=all';

if (!isset($act)) {
    $clients_ids = array();
    $clients = array();
    $user_options = get_office_options()['options'];
    if (isset($user_options) and isset($user_options['invoices_create'])) {
        $offid = "and companies.offid='$_SESSION[offid]'";
    } else {
        $offid = '';
    }
    $result = $amdb->get_results("SELECT * FROM companies JOIN users ON companies.clid = users.clid WHERE companies.clof='0' $offid and users.active='y' order by TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC");
?>

<div class="create-invoice-header">
    <div class="create-invoice-header-content">
        <div class="create-invoice-header-icon">
            <i class="fas <?php echo $typeInfo['icon']; ?>"></i>
        </div>
        
        <div class="create-invoice-header-info">
            <h2>
                Create Invoice
                <span class="invoice-type-badge <?php echo $typeInfo['class']; ?>">
                    <i class="fas <?php echo $typeInfo['icon']; ?>"></i>
                    <?php echo $typeInfo['name']; ?>
                </span>
            </h2>
            <p><?php echo $typeInfo['desc']; ?></p>
        </div>
        
        <div class="step-indicator">
            <span class="step-number">1</span>
            Select Company
        </div>
        
        <a href="index.php?inc=<?php echo $goBack; ?>" class="btn-back-invoices">
            <i class="fas fa-arrow-left"></i>
            Back to Invoices
        </a>
    </div>
    
    <div class="company-select-section">
        <label for="companySelect">Select Company</label>
        <div class="company-select-wrapper">
            <select name="clid" id="companySelect" onchange="if(this.value!='')document.location.href='index.php?inc=create_invoice&type=<?php echo $currentType; ?>&goback=<?php echo $_GET['inc']; ?>&clid='+this.value">
                <option value="">-- Choose a company --</option>
                <?php 
                if (count($result) > 0) {
                    foreach ($result as $row) {
                        echo "<option value='{$row['clid']}'>{$row['company_name']}";
                        if (in_array($row['clid'], $clients_ids)) {
                            echo " (" . $clients[$row['clid']] . ")";
                        }
                        echo "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="company-select-hint">
            <i class="fas fa-info-circle"></i>
            Select a company from the dropdown to proceed with creating the invoice
        </div>
    </div>
</div>

<?php } ?>