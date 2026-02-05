<script language="javascript">
    $("#page_title").html("Create credit note")
</script>
<style>
	/* ============================================
   IIDC Certificate Page - Professional Styling
   Apply to existing HTML without modifications
   ============================================ */


/* Reset & Base */
* {
  box-sizing: border-box;
}


/* Create Credit Note Page Header */
.credit-note-header {
    background: linear-gradient(135deg, #ffffff 0%, #fdf4ff 100%);
    border-radius: 12px;
    border: 1px solid #f5d0fe;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.credit-note-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.credit-note-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #db2777 0%, #ec4899 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.credit-note-header-info {
    flex: 1;
    min-width: 200px;
}

.credit-note-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.credit-note-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Credit Note Badge */
.credit-note-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #fce7f3;
    color: #be185d;
}

.credit-note-badge i {
    font-size: 10px;
}

/* Back Button */
.btn-back-credit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #db2777;
    background: #ffffff;
    border: 2px solid #f5d0fe;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-back-credit:hover {
    background: #fdf4ff;
    border-color: #f0abfc;
    color: #be185d;
    text-decoration: none;
}

/* Company Selection Section */
.credit-select-section {
    padding: 24px 32px;
    background: #fefaff;
    border-top: 1px solid #f5d0fe;
}

.credit-select-section label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.credit-select-wrapper {
    display: flex;
    gap: 12px;
    align-items: stretch;
}

.credit-select-wrapper select {
    flex: 1;
    padding: 16px 48px 16px 20px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 2px solid #f5d0fe;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
}

.credit-select-wrapper select:hover {
    border-color: #db2777;
    background-color: #fffbfe;
}

.credit-select-wrapper select:focus {
    outline: none;
    border-color: #db2777;
    box-shadow: 0 0 0 4px rgba(219, 39, 119, 0.12);
}

.credit-select-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    padding: 12px 16px;
    background: #ffffff;
    border: 1px dashed #f0abfc;
    border-radius: 8px;
    font-size: 13px;
    color: #86198f;
}

.credit-select-hint i {
    color: #db2777;
}

/* Step Indicator */
.credit-step-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #fdf4ff;
    border: 1px solid #f5d0fe;
    border-radius: 8px;
    font-size: 13px;
    color: #86198f;
    margin-left: auto;
}

.credit-step-indicator .step-number {
    width: 24px;
    height: 24px;
    background: #db2777;
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}

/* Info Box */
.credit-info-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 20px;
    background: #fefce8;
    border: 1px solid #fde68a;
    border-radius: 10px;
    margin-top: 16px;
}

.credit-info-box i {
    color: #f59e0b;
    font-size: 18px;
    margin-top: 2px;
}

.credit-info-box .info-content {
    flex: 1;
}

.credit-info-box .info-content strong {
    display: block;
    font-size: 14px;
    color: #92400e;
    margin-bottom: 4px;
}

.credit-info-box .info-content p {
    margin: 0;
    font-size: 13px;
    color: #a16207;
}

/* Responsive */
@media (max-width: 768px) {
    .credit-note-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .credit-note-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .credit-select-section {
        padding: 20px;
    }
    
    .credit-select-wrapper {
        flex-direction: column;
    }
    
    .credit-step-indicator {
        margin-left: 0;
        justify-content: center;
    }
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
    $("#page_title").html("Create Credit Note")
</script>

<?php
if (!isset($act)) {
    $user_options = get_office_options()['options'];
    if (isset($user_options) and isset($user_options['invoices_create'])) {
        $offid = "and companies.offid='$_SESSION[offid]";
    } else {
        $offid = "";
    }

    if ($office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$_SESSION[offid]'")) {
        $cc = $office['office_country'];
    }
    
    $result = $amdb->get_results("SELECT * FROM companies JOIN users ON companies.clid = users.clid WHERE companies.clof='0' $offid and users.active='y' order by companies.company_name ASC");
    
    // Determine go back URL
    $goBackUrl = isset($_GET['goback']) ? $_GET['goback'] : 'invoices&show=all';
?>

<div class="credit-note-header">
    <div class="credit-note-header-content">
        <div class="credit-note-header-icon">
            <i class="fas fa-minus-circle"></i>
        </div>
        
        <div class="credit-note-header-info">
            <h2>
                Create Credit Note
                <span class="credit-note-badge">
                    <i class="fas fa-minus-circle"></i>
                    Credit Note
                </span>
            </h2>
            <p>Issue a credit note to adjust or refund a previous invoice</p>
        </div>
        
        <div class="credit-step-indicator">
            <span class="step-number">1</span>
            Select Company
        </div>
        
        <a href="index.php?inc=invoices&show=all" class="btn-back-credit">
            <i class="fas fa-arrow-left"></i>
            Back to Invoices
        </a>
    </div>
    
    <div class="credit-select-section">
        <label for="companySelect">Select Company</label>
        <div class="credit-select-wrapper">
            <select name="clid" id="companySelect" onchange="if(this.value!='')document.location.href='index.php?inc=create_invoice&type=credit_note&goback=<?php echo $_GET['inc']; ?>&clid='+this.value">
                <option value="">-- Choose a company --</option>
                <?php 
                if (count($result) > 0) {
                    foreach ($result as $row) { 
                ?>
                    <option value="<?php echo $row['clid']; ?>">
                        <?php echo get_client_id($row['clid'], $cc); ?> - <?php echo htmlspecialchars($row['company_name']); ?>
                    </option>
                <?php 
                    }
                } 
                ?>
            </select>
        </div>
        <div class="credit-select-hint">
            <i class="fas fa-info-circle"></i>
            Select a company from the dropdown to create a credit note
        </div>
        
        
    </div>
</div>

<?php } ?>