<?php
if ((isset($_SESSION['comemid']) and $_SESSION['super_admin'] == 'yes') or (isset($_SESSION['user_type']) and $_SESSION['user_type'] == "admin")) {
?>
<script>
    $("#page_title").html("Email Templates")
</script>

<style>
/* ============================================
   Email Templates Page Styling
   ============================================ */

/* Page Header */
.email-templates-header {
    background: linear-gradient(135deg, #ffffff 0%, #fdf4ff 100%);
    border-radius: 12px;
    border: 1px solid #f0abfc;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.email-templates-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.email-templates-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #a855f7 0%, #c084fc 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.email-templates-header-info {
    flex: 1;
    min-width: 200px;
}

.email-templates-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.email-templates-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

.email-templates-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #fae8ff;
    color: #a855f7;
    border: 1px solid #f0abfc;
}

.email-templates-info-strip {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 32px;
    background: #fdf4ff;
    border-top: 1px solid #f0abfc;
    font-size: 13px;
    color: #86198f;
}

.email-templates-info-strip i {
    color: #c026d3;
}

/* Template Selector Card */
.template-selector-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    margin-bottom: 24px;
}

.template-selector-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
}

.template-selector-header i {
    color: #a855f7;
    font-size: 18px;
}

.template-selector-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #374151;
}

.template-selector-body {
    padding: 24px;
}

.template-select-wrapper {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.template-select-wrapper label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.template-select-wrapper select {
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

.template-select-wrapper select:hover {
    border-color: #a855f7;
    background-color: #fefcff;
}

.template-select-wrapper select:focus {
    outline: none;
    border-color: #a855f7;
    box-shadow: 0 0 0 4px rgba(168, 85, 247, 0.12);
}

.template-select-wrapper select option {
    padding: 12px;
    text-transform: capitalize;
}

.template-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    padding: 12px 16px;
    background: #fefce8;
    border: 1px dashed #fbbf24;
    border-radius: 8px;
    font-size: 13px;
    color: #92400e;
}

.template-hint i {
    color: #f59e0b;
}

/* Template Editor Container */
.template-editor-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    margin-bottom: 24px;
}

.template-editor-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%);
    border-bottom: 1px solid #f0abfc;
}

.template-editor-header .editor-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #a855f7 0%, #c084fc 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 18px;
}

.template-editor-header .editor-info h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 700;
    color: #581c87;
    text-transform: capitalize;
}

.template-editor-header .editor-info p {
    margin: 0;
    font-size: 13px;
    color: #a855f7;
}

.template-editor-header .template-type-badge {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background: #ffffff;
    color: #7c3aed;
    border: 1px solid #ddd6fe;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.template-type-badge.new {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
}

/* Form Fields */
.template-form-section {
    padding: 24px;
    border-bottom: 1px solid #f1f5f9;
}

.template-form-section:last-child {
    border-bottom: none;
}

.template-form-section.highlight {
    background: #fefce8;
    border-bottom: 1px solid #fde68a;
}

.form-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px dashed #e2e8f0;
}

.form-section-title i {
    color: #a855f7;
    font-size: 16px;
}

.form-section-title span {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.form-row:last-child {
    margin-bottom: 0;
}

.form-row.single {
    grid-template-columns: 1fr;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-field label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-field label .required {
    color: #dc2626;
}

.form-field label .optional {
    font-weight: 400;
    color: #94a3b8;
    font-size: 11px;
}

.form-field input[type="text"],
.form-field input[type="email"] {
    width: 100%;
    padding: 12px 16px;
    font-size: 14px;
    color: #1e293b;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.25s ease;
}

.form-field input:focus {
    outline: none;
    border-color: #a855f7;
    box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.12);
}

.form-field .field-hint {
    font-size: 12px;
    color: #64748b;
}

/* Editor Section */
.editor-section {
    padding: 24px;
}

.editor-label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.editor-label i {
    color: #a855f7;
    font-size: 16px;
}

.editor-label span {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.editor-label .required {
    color: #dc2626;
}

.editor-wrapper {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.editor-wrapper .tinymce {
    min-height: 400px;
}

/* PDF Template Section */
.pdf-template-section {
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    border-top: 2px solid #fb923c;
}

.pdf-template-section .form-section-title i {
    color: #ea580c;
}

.pdf-template-section .editor-label i {
    color: #ea580c;
}

/* New Template Field */
.new-template-field {
    padding: 24px;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-top: 2px solid #86efac;
}

.new-template-field .form-field label {
    color: #166534;
}

.new-template-field input {
    border-color: #86efac;
}

.new-template-field input:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.12);
}

/* Form Footer */
.template-form-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 24px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.template-form-footer .footer-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
    margin-right: auto;
}

.template-form-footer .footer-info i {
    color: #f59e0b;
}

.btn-template {
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

.btn-template.reset {
    background: #ffffff;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-template.reset:hover {
    background: #f1f5f9;
    color: #475569;
    border-color: #cbd5e1;
}

.btn-template.save {
    background: linear-gradient(135deg, #a855f7 0%, #c084fc 100%);
    color: #ffffff;
}

.btn-template.save:hover {
    background: linear-gradient(135deg, #9333ea 0%, #a855f7 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(168, 85, 247, 0.3);
}

/* Shortcodes Reference */
.shortcodes-panel {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    margin-top: 24px;
}

.shortcodes-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 24px;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-bottom: 1px solid #bae6fd;
    cursor: pointer;
}

.shortcodes-header i {
    color: #0369a1;
    font-size: 18px;
}

.shortcodes-header h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #0369a1;
    flex: 1;
}

.shortcodes-header .toggle-icon {
    color: #64748b;
    transition: transform 0.3s ease;
}

.shortcodes-header.collapsed .toggle-icon {
    transform: rotate(-90deg);
}

.shortcodes-body {
    padding: 20px 24px;
    display: none;
}

.shortcodes-body.show {
    display: block;
}

.shortcodes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}

.shortcode-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.shortcode-item:hover {
    background: #f0f9ff;
    border-color: #bae6fd;
}

.shortcode-item code {
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 11px;
    padding: 2px 6px;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 4px;
}

.shortcode-item span {
    color: #64748b;
}

/* Responsive */
@media (max-width: 768px) {
    .email-templates-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .email-templates-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .template-selector-body {
        padding: 20px;
    }
    
    .template-editor-header {
        flex-direction: column;
        text-align: center;
    }
    
    .template-editor-header .template-type-badge {
        margin-left: 0;
        margin-top: 12px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .template-form-footer {
        flex-direction: column;
    }
    
    .template-form-footer .footer-info {
        margin-right: 0;
        margin-bottom: 12px;
    }
    
    .btn-template {
        width: 100%;
        justify-content: center;
    }
    
    .shortcodes-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
// Get template counts
$templateCount = 0;
if ($em_templates = $amdb->get_results("SELECT template_name FROM invoice_templates")) {
    $templateCount = count($em_templates);
}

// Determine if editing
$isEditing = isset($_GET['act']) && $_GET['act'] == 'editTemplate' && isset($_GET['tn']) && trim($_GET['tn']) != '';
$isNew = $isEditing && $_GET['tn'] == '0';
$templateName = $isEditing ? ($_GET['tn'] == '0' ? 'New Template' : str_replace('_', ' ', $_GET['tn'])) : '';
?>

<!-- Page Header -->
<div class="email-templates-header">
    <div class="email-templates-header-content">
        <div class="email-templates-header-icon">
            <i class="fas fa-envelope-open-text"></i>
        </div>
        
        <div class="email-templates-header-info">
            <h2>
                Email Templates
                <span class="email-templates-badge">
                    <i class="fas fa-cog"></i>
                    Configuration
                </span>
            </h2>
            <p>Customize email messages sent with invoices, reminders, and notifications</p>
        </div>
    </div>
    
    <div class="email-templates-info-strip">
        <i class="fas fa-info-circle"></i>
        <span>Templates support dynamic shortcodes that are replaced with actual values when emails are sent.</span>
    </div>
</div>

<form action="invoice_templates_save.php" onSubmit="return post_this_form(this)" id="officeForm" name="officeForm" data-error="All fields are required">

    <!-- Template Selector -->
    <div class="template-selector-card">
        <div class="template-selector-header">
            <i class="fas fa-list-alt"></i>
            <h3>Select Template to Edit</h3>
        </div>
        <div class="template-selector-body">
            <div class="template-select-wrapper">
                <label for="template_name">Choose Email Template</label>
                <select name="template_name" id="template_name" onchange="document.location='index.php?inc=<?php echo $_GET['inc']; ?>&act=editTemplate&tn='+this.value">
                    <option value="">-- Select an email template --</option>
                    <?php if ($em_templates) {
                        foreach ($em_templates as $template) {
                            if (trim($template['template_name']) != '') { ?>
                                <option value="<?php echo $template['template_name']; ?>" <?php echo ($isEditing && $_GET['tn'] == $template['template_name']) ? 'selected' : ''; ?>>
                                    <?php echo ucwords(str_replace('_', ' ', $template['template_name'])); ?>
                                </option>
                    <?php };
                        }
                    } ?>
                    <option value="0" <?php echo $isNew ? 'selected' : ''; ?>>➕ Add New Template</option>
                </select>
            </div>
            
            <?php if (!$isEditing): ?>
            <div class="template-hint">
                <i class="fas fa-lightbulb"></i>
                <span>Select a template from the dropdown above to edit its content, or choose "Add New Template" to create a new one.</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isEditing): 
        if (!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='$_GET[tn]'"))
            $row = $amdb->get_columns('invoice_templates');
    ?>
    
    <input type="hidden" name="act" value="update" />
    
    <!-- Template Editor -->
    <div class="template-editor-container">
        <div class="template-editor-header">
            <div class="editor-icon">
                <i class="fas <?php echo $isNew ? 'fa-plus' : 'fa-edit'; ?>"></i>
            </div>
            <div class="editor-info">
                <h3><?php echo $isNew ? 'Create New Template' : 'Edit: ' . $templateName; ?></h3>
                <p><?php echo $isNew ? 'Configure a new email template' : 'Modify template settings and content'; ?></p>
            </div>
            <span class="template-type-badge <?php echo $isNew ? 'new' : ''; ?>">
                <i class="fas <?php echo $isNew ? 'fa-sparkles' : 'fa-file-alt'; ?>"></i>
                <?php echo $isNew ? 'New' : 'Editing'; ?>
            </span>
        </div>
        
        <?php if ($_GET['tn'] != 'email_footer'): ?>
        <!-- Email Settings Section -->
        <div class="template-form-section">
            <div class="form-section-title">
                <i class="fas fa-cog"></i>
                <span>Email Settings</span>
            </div>
            
            <div class="form-row">
                <div class="form-field">
                    <label>
                        <i class="fas fa-reply"></i>
                        Reply-To Address
                        <span class="required">*</span>
                    </label>
                    <input type="email" name="email_reply_address" data-required="yes" value="<?php echo htmlspecialchars($row['email_reply_address']); ?>" placeholder="reply@example.com" />
                    <span class="field-hint">Recipients will reply to this address</span>
                </div>
                
                <div class="form-field">
                    <label>
                        <i class="fas fa-user"></i>
                        Sender Name
                        <span class="required">*</span>
                    </label>
                    <input type="text" name="email_sender_name" data-required="yes" value="<?php echo htmlspecialchars($row['email_sender_name']); ?>" placeholder="Company Name" />
                    <span class="field-hint">Displayed as the sender's name</span>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-field">
                    <label>
                        <i class="fas fa-copy"></i>
                        BCC Address
                        <span class="optional">(Optional)</span>
                    </label>
                    <input type="email" name="email_bcc_address" value="<?php echo htmlspecialchars($row['email_bcc_address']); ?>" placeholder="copy@example.com" />
                    <span class="field-hint">Receive a blind copy of each email sent</span>
                </div>
                
                <div class="form-field">
                    <label>
                        <i class="fas fa-heading"></i>
                        Email Subject
                        <span class="required">*</span>
                    </label>
                    <input type="text" name="email_subject" data-required="yes" value="<?php echo htmlspecialchars($row['email_subject']); ?>" placeholder="Enter email subject line" />
                    <span class="field-hint">Subject line of the email</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Email Body Section -->
        <div class="editor-section">
            <div class="editor-label">
                <i class="fas fa-align-left"></i>
                <span>Email Body <?php echo $_GET['tn'] == 'email_footer' ? '(Footer Content)' : ''; ?></span>
                <span class="required">*</span>
            </div>
            <div class="editor-wrapper">
                <textarea class="tinymce" name="email_body" style="height:400px;"><?php echo $row['email_body']; ?></textarea>
            </div>
        </div>
        
        <?php if ($isNew): ?>
        <!-- New Template ID -->
        <div class="new-template-field">
            <div class="form-field">
                <label>
                    <i class="fas fa-fingerprint"></i>
                    Template ID (Unique Identifier)
                    <span class="required">*</span>
                </label>
                <input type="text" name="template_name" id="new_template_name" data-required="yes" placeholder="e.g., payment_reminder_final" style="max-width: 400px;" />
                <span class="field-hint">Use lowercase letters and underscores only. This ID is used internally to identify the template.</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($_GET['tn'] == 'invoice' || $_GET['tn'] == 'credit_note'): ?>
    <!-- PDF Template Section -->
    <div class="template-editor-container">
        <div class="template-editor-header" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border-color: #fb923c;">
            <div class="editor-icon" style="background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="editor-info">
                <h3 style="color: #9a3412;">PDF Template</h3>
                <p style="color: #ea580c;">Customize the PDF document layout for <?php echo str_replace('_', ' ', $_GET['tn']); ?></p>
            </div>
            <span class="template-type-badge" style="background: #ffedd5; color: #ea580c; border-color: #fdba74;">
                <i class="fas fa-file-pdf"></i>
                PDF Layout
            </span>
        </div>
        
        <div class="editor-section">
            <div class="editor-label">
                <i class="fas fa-file-pdf" style="color: #ea580c;"></i>
                <span>PDF Content Template</span>
            </div>
            <div class="editor-wrapper">
                <textarea class="tinymce" name="pdf_template" style="height:400px;"><?php echo $row['pdf_template']; ?></textarea>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Form Footer -->
    <div class="template-form-footer" style="background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%); border: 1px solid #f0abfc; border-radius: 12px;">
        <div class="footer-info">
            <i class="fas fa-exclamation-circle"></i>
            <span>Fields marked with <span style="color: #dc2626;">*</span> are required</span>
        </div>
        
        <button type="reset" class="btn-template reset">
            <i class="fas fa-undo"></i>
            Reset Changes
        </button>
        
        <button type="submit" class="btn-template save">
            <i class="fas fa-save"></i>
            <?php echo $isNew ? 'Create Template' : 'Save Changes'; ?>
        </button>
    </div>
    
    <!-- Shortcodes Reference Panel -->
    <div class="shortcodes-panel">
        <div class="shortcodes-header" onclick="toggleShortcodes(this)">
            <i class="fas fa-code"></i>
            <h4>Available Shortcodes Reference</h4>
            <i class="fas fa-chevron-down toggle-icon"></i>
        </div>
        <div class="shortcodes-body" id="shortcodesBody">
            <div class="shortcodes-grid">
                <div class="shortcode-item" onclick="copyShortcode('{company_name}')">
                    <code>{company_name}</code>
                    <span>Company Name</span>
                </div>
                <div class="shortcode-item" onclick="copyShortcode('{invoice_number}')">
                    <code>{invoice_number}</code>
                    <span>Invoice Number</span>
                </div>
                <div class="shortcode-item" onclick="copyShortcode('{invoice_date}')">
                    <code>{invoice_date}</code>
                    <span>Invoice Date</span>
                </div>
                <div class="shortcode-item" onclick="copyShortcode('{due_date}')">
                    <code>{due_date}</code>
                    <span>Due Date</span>
                </div>
                <div class="shortcode-item" onclick="copyShortcode('{total_amount}')">
                    <code>{total_amount}</code>
                    <span>Total Amount</span>
                </div>
                <div class="shortcode-item" onclick="copyShortcode('{contact_name}')">
                    <code>{contact_name}</code>
                    <span>Contact Name</span>
                </div>
                <div class="shortcode-item" onclick="copyShortcode('{contact_email}')">
                    <code>{contact_email}</code>
                    <span>Contact Email</span>
                </div>
                <div class="shortcode-item" onclick="copyShortcode('{payment_link}')">
                    <code>{payment_link}</code>
                    <span>Payment Link</span>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</form>

<script>
    <?php if ($isEditing && !$isNew): ?>
    jQuery("#template_name").val('<?php echo $_GET['tn']; ?>');
    <?php endif; ?>
    
    function toggleShortcodes(header) {
        var body = document.getElementById('shortcodesBody');
        header.classList.toggle('collapsed');
        body.classList.toggle('show');
    }
    
    function copyShortcode(code) {
        navigator.clipboard.writeText(code).then(function() {
            alert_message('Shortcode copied: ' + code);
        });
    }
    
    // Initialize shortcodes panel
    document.addEventListener('DOMContentLoaded', function() {
        var header = document.querySelector('.shortcodes-header');
        if (header) {
            header.classList.add('collapsed');
        }
    });
</script>

<?php } ?>