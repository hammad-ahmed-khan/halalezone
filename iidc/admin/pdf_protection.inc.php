<script>
jQuery("#page_title").html("PDF File Protection");

function protect_pdf_file(obj) {
    if (obj.checked) {
        jQuery("#protected_pdf_password").attr("data-required", "yes");
        jQuery("#password-field-row").addClass("required-active");
        jQuery("#protected-files-section").slideDown(300);
    } else {
        jQuery("#protected_pdf_password").removeAttr("data-required");
        jQuery("#password-field-row").removeClass("required-active");
        jQuery("#protected-files-section").slideUp(300);
    }
}

// Initialize on page load
jQuery(document).ready(function() {
    var isProtected = jQuery("input[name='protected_pdf[protect]']").is(":checked");
    if (!isProtected) {
        jQuery("#protected-files-section").hide();
    }
});
</script>

<?php
$options = array();
if ($options = get_option('protected_pdf'))
    $options = json_decode($options, true);

$isProtected = isset($options['protect']);
?>

<style>
/* ============================================
   PDF Protection Page Styling
   ============================================ */

/* Page Header */
.pdf-protection-header {
    background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
    border-radius: 12px;
    border: 1px solid #fecaca;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.pdf-protection-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.pdf-protection-header-icon {
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

.pdf-protection-header-info {
    flex: 1;
    min-width: 200px;
}

.pdf-protection-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.pdf-protection-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

.pdf-protection-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.pdf-protection-info-strip {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 32px;
    background: #fef2f2;
    border-top: 1px solid #fecaca;
    font-size: 13px;
    color: #991b1b;
    line-height: 1.5;
}

.pdf-protection-info-strip i {
    color: #dc2626;
    margin-top: 2px;
}

/* Form Container */
.pdf-protection-form-container {
    max-width: 600px;
    margin: 0 auto;
}

.pdf-protection-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.pdf-protection-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
}

.pdf-protection-card-header i {
    color: #dc2626;
    font-size: 18px;
}

.pdf-protection-card-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #374151;
}

.pdf-protection-card-body {
    padding: 24px;
}

/* Toggle Row */
.protection-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
    margin: -24px -24px 24px -24px;
}

.toggle-info {
    display: flex;
    align-items: center;
    gap: 14px;
}

.toggle-icon-box {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 18px;
}

.toggle-text h4 {
    margin: 0 0 2px 0;
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

.toggle-text p {
    margin: 0;
    font-size: 12px;
    color: #64748b;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 56px;
    height: 30px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: 0.3s;
    border-radius: 30px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 24px;
    width: 24px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.toggle-switch input:focus + .toggle-slider {
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);
}

/* Form Fields */
.form-field-row {
    margin-bottom: 24px;
}

.form-field-row:last-child {
    margin-bottom: 0;
}

.form-field-row label.field-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 10px;
}

.form-field-row label.field-label i {
    color: #dc2626;
    font-size: 14px;
}

.form-field-row label.field-label .required {
    color: #dc2626;
    font-size: 14px;
}

.form-field-row label.field-label .optional {
    font-weight: 400;
    color: #94a3b8;
    font-size: 11px;
}

.password-input-wrapper {
    position: relative;
}

.password-input-wrapper input {
    width: 100%;
    padding: 14px 48px 14px 16px;
    font-size: 14px;
    color: #1e293b;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    transition: all 0.25s ease;
}

.password-input-wrapper input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12);
}

.password-input-wrapper input::placeholder {
    color: #94a3b8;
}

.password-input-wrapper .toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
    transition: color 0.2s ease;
}

.password-input-wrapper .toggle-password:hover {
    color: #64748b;
}

.form-field-row.required-active label.field-label {
    color: #dc2626;
}

.form-field-row.required-active input {
    border-color: #fca5a5;
    background: #fef2f2;
}

.field-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    font-size: 12px;
    color: #64748b;
}

.field-hint i {
    font-size: 12px;
    color: #94a3b8;
}

/* Protected Files Section */
.protected-files-section {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px dashed #e2e8f0;
}

.section-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 14px;
}

.section-label i {
    color: #dc2626;
    font-size: 14px;
}

.files-checkbox-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.files-checkbox-list li {
    padding: 0;
}

.file-checkbox-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.file-checkbox-item:hover {
    background: #fef2f2;
    border-color: #fecaca;
}

.file-checkbox-item input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: #dc2626;
    cursor: pointer;
}

.file-checkbox-item .file-info {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
}

.file-checkbox-item .file-icon {
    width: 36px;
    height: 36px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.file-checkbox-item .file-icon.annual {
    color: #16a34a;
}

.file-checkbox-item .file-icon.batch {
    color: #d97706;
}

.file-checkbox-item .file-icon.invoices {
    color: #0369a1;
}

.file-checkbox-item .file-text {
    font-size: 14px;
    font-weight: 500;
    color: #374151;
}

.file-checkbox-item input:checked ~ .file-info .file-icon {
    background: #fef2f2;
    border-color: #fecaca;
}

/* Form Footer */
.pdf-protection-form-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 20px 24px;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border-top: 1px solid #fecaca;
}

.btn-pdf-protection {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.btn-pdf-protection.save {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    color: #ffffff;
}

.btn-pdf-protection.save:hover {
    background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-pdf-protection.reset {
    background: #ffffff;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-pdf-protection.reset:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #475569;
}

/* Info Card */
.protection-info-card {
    margin-top: 24px;
    padding: 20px;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 1px solid #bae6fd;
    border-radius: 12px;
}

.protection-info-card h4 {
    margin: 0 0 12px 0;
    font-size: 14px;
    font-weight: 600;
    color: #0369a1;
    display: flex;
    align-items: center;
    gap: 8px;
}

.protection-info-card h4 i {
    font-size: 16px;
}

.protection-info-card ul {
    margin: 0;
    padding: 0 0 0 20px;
    list-style: none;
}

.protection-info-card ul li {
    position: relative;
    padding: 6px 0;
    font-size: 13px;
    color: #0369a1;
    padding-left: 20px;
}

.protection-info-card ul li::before {
    content: "\f00c";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    left: 0;
    color: #22c55e;
    font-size: 11px;
}

.protection-info-card ul li.blocked::before {
    content: "\f00d";
    color: #dc2626;
}

/* Status Indicator */
.protection-status {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

.protection-status.enabled {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.protection-status.disabled {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.protection-status i {
    font-size: 14px;
}

/* Responsive */
@media (max-width: 640px) {
    .pdf-protection-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .pdf-protection-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .pdf-protection-form-container {
        padding: 0 16px;
    }
    
    .protection-toggle-row {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }
    
    .toggle-info {
        flex-direction: column;
    }
    
    .pdf-protection-form-footer {
        flex-direction: column;
    }
    
    .btn-pdf-protection {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Page Header -->
<div class="pdf-protection-header">
    <div class="pdf-protection-header-content">
        <div class="pdf-protection-header-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <div class="pdf-protection-header-info">
            <h2>
                PDF File Protection
                <span class="pdf-protection-badge">
                    <i class="fas fa-lock"></i>
                    Security
                </span>
            </h2>
            <p>Protect PDF documents with password encryption to prevent unauthorized modifications</p>
        </div>
        
        <div class="protection-status <?php echo $isProtected ? 'enabled' : 'disabled'; ?>">
            <i class="fas <?php echo $isProtected ? 'fa-lock' : 'fa-lock-open'; ?>"></i>
            <?php echo $isProtected ? 'Protection Enabled' : 'Protection Disabled'; ?>
        </div>
    </div>
    
    <div class="pdf-protection-info-strip">
        <i class="fas fa-info-circle"></i>
        <span>Password protection prevents clients from editing, modifying, copying contents, or extracting/adding pages. Clients can still print and save the PDF files.</span>
    </div>
</div>

<!-- Form Container -->
<div class="pdf-protection-form-container">
    <form action="pdf_protection_save.php" onSubmit="return post_this_form(this)" id="pdfProtectionForm" name="pdfProtectionForm" data-target="_blank" data-error="Password is required">
        
        <div class="pdf-protection-card">
            <div class="pdf-protection-card-body">
                
                <!-- Protection Toggle -->
                <div class="protection-toggle-row">
                    <div class="toggle-info">
                        <div class="toggle-icon-box">
                            <i class="fas fa-file-shield"></i>
                        </div>
                        <div class="toggle-text">
                            <h4>Enable PDF Protection</h4>
                            <p>Apply password protection to generated PDF files</p>
                        </div>
                    </div>
                    
                    <label class="toggle-switch">
                        <input type="checkbox" name="protected_pdf[protect]" onclick="protect_pdf_file(this)" value="yes" <?php echo $isProtected ? 'checked' : ''; ?> />
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <!-- Password Field -->
                <div class="form-field-row <?php echo $isProtected ? 'required-active' : ''; ?>" id="password-field-row">
                    <label class="field-label">
                        <i class="fas fa-key"></i>
                        Protection Password
                        <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" 
                               name="protected_pdf[password]" 
                               id="protected_pdf_password" 
                               placeholder="Enter a strong password" 
                               value="<?php echo isset($options['password']) ? htmlspecialchars($options['password']) : ''; ?>" 
                               <?php echo $isProtected ? 'data-required="yes"' : ''; ?> />
                        <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility(this)"></i>
                    </div>
                    <div class="field-hint">
                        <i class="fas fa-info-circle"></i>
                        <span>This password will be required to modify protected PDF files</span>
                    </div>
                </div>
                
                <!-- Protected Files Selection -->
                <div class="protected-files-section" id="protected-files-section">
                    <div class="section-label">
                        <i class="fas fa-file-pdf"></i>
                        Select Files to Protect
                    </div>
                    
                    <ul class="files-checkbox-list">
                        <li>
                            <label class="file-checkbox-item">
                                <input type="checkbox" name="protected_pdf[annual]" <?php echo isset($options['annual']) ? 'checked' : ''; ?> />
                                <div class="file-info">
                                    <div class="file-icon annual">
                                        <i class="fas fa-certificate"></i>
                                    </div>
                                    <span class="file-text">Annual Certificates</span>
                                </div>
                            </label>
                        </li>
                        <li>
                            <label class="file-checkbox-item">
                                <input type="checkbox" name="protected_pdf[batch]" <?php echo isset($options['batch']) ? 'checked' : ''; ?> />
                                <div class="file-info">
                                    <div class="file-icon batch">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <span class="file-text">Batch Certificates</span>
                                </div>
                            </label>
                        </li>
                        <li>
                            <label class="file-checkbox-item">
                                <input type="checkbox" name="protected_pdf[invoices]" <?php echo isset($options['invoices']) ? 'checked' : ''; ?> />
                                <div class="file-info">
                                    <div class="file-icon invoices">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </div>
                                    <span class="file-text">Invoices</span>
                                </div>
                            </label>
                        </li>
                    </ul>
                </div>
                
            </div>
            
            <!-- Form Footer -->
            <div class="pdf-protection-form-footer">
                <button type="reset" class="btn-pdf-protection reset">
                    <i class="fas fa-undo"></i>
                    Reset
                </button>
                <button type="submit" class="btn-pdf-protection save">
                    <i class="fas fa-save"></i>
                    Save Settings
                </button>
            </div>
        </div>
        
    </form>
    
    <!-- Info Card -->
    <div class="protection-info-card">
        <h4>
            <i class="fas fa-shield-alt"></i>
            What does PDF protection do?
        </h4>
        <ul>
            <li>Clients can view and print protected PDFs</li>
            <li>Clients can save PDF files to their computer</li>
            <li class="blocked">Editing or modifying content is prevented</li>
            <li class="blocked">Copying text or images is prevented</li>
            <li class="blocked">Extracting or adding pages is prevented</li>
        </ul>
    </div>
</div>

<script>
function togglePasswordVisibility(icon) {
    var input = document.getElementById('protected_pdf_password');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>