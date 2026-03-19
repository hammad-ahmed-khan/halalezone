<?php if (!defined("_HQC_")) {
    exit();
};
?>
<style>
/* Committee Member Form Header */
.committee-form-header {
    background: linear-gradient(135deg, #ffffff 0%, #fef7f0 100%);
    border-radius: 12px;
    border: 1px solid #fed7aa;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.committee-form-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.committee-form-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.committee-form-header-icon.edit {
    background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
}

.committee-form-header-icon.account {
    background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
}

.committee-form-header-info {
    flex: 1;
    min-width: 200px;
}

.committee-form-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.committee-form-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Action Badge */
.committee-action-badge {
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

.committee-action-badge.add {
    background: #dcfce7;
    color: #166534;
}

.committee-action-badge.edit {
    background: #e0f2fe;
    color: #0369a1;
}

.committee-action-badge.account {
    background: #ede9fe;
    color: #6d28d9;
}

.committee-action-badge i {
    font-size: 10px;
}

/* Header Actions */
.committee-form-header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-committee-action {
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

.btn-committee-action.back {
    background: #ffffff;
    color: #ea580c;
    border: 2px solid #fed7aa;
}

.btn-committee-action.back:hover {
    background: #fff7ed;
    border-color: #fdba74;
    color: #c2410c;
    text-decoration: none;
}

/* Member Info Strip (for edit mode) */
.committee-member-strip {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 32px;
    background: #fff7ed;
    border-top: 1px solid #fed7aa;
    flex-wrap: wrap;
}

.committee-member-strip .member-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #c2410c;
    font-size: 20px;
    font-weight: 700;
}

.committee-member-strip .member-details {
    flex: 1;
    min-width: 200px;
}

.committee-member-strip .member-details .member-name {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 2px 0;
}

.committee-member-strip .member-details .member-meta {
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.committee-member-strip .member-details .member-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
}

.committee-member-strip .member-details .member-meta i {
    color: #f97316;
}

/* Form Container */
.committee-form-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.committee-form-body {
    display: flex;
    flex-wrap: wrap;
}

.committee-form-left {
    flex: 1;
    min-width: 400px;
    padding: 24px;
    border-right: 1px solid #f1f5f9;
}

.committee-form-right {
    width: 400px;
    padding: 24px;
    background: #fafafa;
}

/* Section Titles */
.form-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 2px solid #fed7aa;
}

.form-section-title i {
    color: #f97316;
}

/* Enhanced Table Styling */
.committee-form-table {
    width: 100%;
    border-collapse: collapse;
}

.committee-form-table th {
    width: 150px;
    text-align: left;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    background: #fafafa;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}

.committee-form-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
}

.committee-form-table input[type="text"],
.committee-form-table input[type="email"],
.committee-form-table input[type="tel"],
.committee-form-table input[type="password"],
.committee-form-table select {
    width: 100%;
    padding: 10px 14px;
    font-size: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.25s ease;
}

.committee-form-table input:focus,
.committee-form-table select:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
}

.committee-form-table input[type="file"] {
    padding: 8px;
    border: 1px dashed #e2e8f0;
    border-radius: 8px;
    width: 100%;
    cursor: pointer;
}

.committee-form-table input[type="file"]:hover {
    border-color: #f97316;
    background: #fff7ed;
}

/* Phone Numbers */
.phone-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.phone-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px dashed #e2e8f0;
}

.phone-list li:last-child {
    border-bottom: none;
}

.phone-list li strong {
    min-width: 80px;
    font-size: 13px;
    color: #64748b;
}

.phone-list li input {
    flex: 1;
}

.phone-hint {
    font-size: 12px;
    color: #94a3b8;
    font-style: italic;
    margin-top: 8px;
}

/* Username Preview */
.username-preview {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 6px;
    font-size: 13px;
    color: #166534;
    margin-top: 8px;
}

.username-preview i {
    color: #16a34a;
}

/* Password Section */
.password-wrapper {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.password-wrapper input {
    flex: 1;
    min-width: 150px;
}

.password-toggle {
    padding: 10px 14px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.password-toggle:hover {
    background: #e2e8f0;
}

.password-generate {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 8px;
    color: #ea580c;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.password-generate:hover {
    background: #ffedd5;
}

.password-info {
    display: block;
    margin-top: 8px;
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 6px;
}

.password-info.strong {
    background: #f0fdf4;
    color: #166534;
}

.password-info.weak {
    background: #fef2f2;
    color: #dc2626;
}

/* Signature Preview */
.signature-preview {
    margin-top: 12px;
    padding: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    text-align: center;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 12px;
}

.signature-preview img {
    max-width: 200px;
    max-height: 100px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
}

.signature-preview .delete-signature {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 6px;
    color: #dc2626;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.signature-preview .delete-signature:hover {
    background: #fee2e2;
}

.signature-preview .no-signature {
    color: #94a3b8;
    font-style: italic;
}

/* Offices List */
.offices-section {
    height: 100%;
}

.offices-list {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 400px;
    overflow-y: auto;
}

.offices-list li {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
}

.offices-list li:hover {
    background: #fff7ed;
}

.offices-list li label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 14px;
    color: #374151;
}

.offices-list li label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #f97316;
}

.offices-list li .contact-person {
    font-size: 12px;
    color: #16a34a;
    margin-left: 4px;
}

.offices-search {
    margin-bottom: 12px;
}

.offices-search input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.offices-search input:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
}

.offices-note {
    margin-top: 16px;
    padding: 12px;
    background: #fff7ed;
    border: 1px dashed #fed7aa;
    border-radius: 8px;
    font-size: 13px;
    color: #9a3412;
    font-style: italic;
}

/* Form Footer */
.committee-form-footer {
    display: flex;
    justify-content: center;
    gap: 12px;
    padding: 20px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
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
    background: #fef2f2;
    color: #dc2626;
    border: 2px solid #fecaca;
}

.btn-form-action.reset:hover {
    background: #fee2e2;
}

.btn-form-action.save {
    background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
    color: #ffffff;
}

.btn-form-action.save:hover {
    background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
}

/* Responsive */
@media (max-width: 900px) {
    .committee-form-body {
        flex-direction: column;
    }
    
    .committee-form-left {
        min-width: auto;
        border-right: none;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .committee-form-right {
        width: auto;
    }
}

@media (max-width: 768px) {
    .committee-form-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .committee-form-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .committee-form-header-actions {
        width: 100%;
        justify-content: center;
    }
    
    .committee-member-strip {
        flex-direction: column;
        text-align: center;
        padding: 16px 20px;
    }
    
    .committee-member-strip .member-details .member-meta {
        justify-content: center;
    }
    
    .committee-form-table th {
        width: 100px;
    }
    
    .committee-form-footer {
        flex-direction: column;
    }
    
    .btn-form-action {
        width: 100%;
        justify-content: center;
    }
}
</style>
<?php 
function get_user_signature($comemid)
{
    global $prog_path;
    $image_file = '/data/DMC/signatures/' . $comemid . '_signature';
    $image_exts = array('.jpg', '.jpeg', '.png', '.svg');
    foreach ($image_exts as $ext) {
        if (file_exists("$prog_path" . $image_file . $ext))
            return $image_file . $ext . '?t=' . time();
    }
}

$functions = array(
    'ABM' => 'Auditor Board Member',
    'MBM' => 'Management Board Member',
    'SBM' => 'Shariah Board Member',
);

if (!isset($_GET['comemid']) && isset($_SESSION['comemid']))
    $_GET['comemid'] = $_SESSION['comemid'];

if (isset($_GET['comemid'])) {
    $row = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE comemid='{$_GET['comemid']}'");
    $isEdit = true;
} elseif(isset($member_account)) {
    if (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) {
        $uid = $_SESSION['user']['uid'];
    } else {
        $uid = $_SESSION['offid'];
    }

    if($row = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE uid='$uid' AND offid='$_SESSION[offid]'")) {
        $_GET['comemid'] = $row['comemid'];
        $isEdit = true;
    } else {
        echo '<div style="text-align: center; padding: 60px 20px; background: #fef2f2; border-radius: 12px; border: 1px solid #fecaca; margin: 20px;">
            <i class="fas fa-exclamation-circle" style="font-size: 48px; color: #dc2626; margin-bottom: 16px;"></i>
            <h3 style="color: #991b1b; margin: 0 0 8px 0;">No Committee Member Found</h3>
            <p style="color: #b91c1c; margin: 0;">Please ask the administrator for assistance.<br/>If you are the administrator, add yourself as a committee member.</p>
        </div>';
        return;
    }
} else {
    $row = $amdb->get_columns("hqc_committee_members", false);
    $isEdit = false;
}
$comDir = explode('/', $_SERVER['REQUEST_URI'])[1];

// Determine page mode
$isMyAccount = isset($member_account);
$pageTitle = $isMyAccount ? 'My Account' : ($isEdit ? 'Edit Committee Member' : 'Add Committee Member');
$pageDescription = $isMyAccount ? 'Update your account information and settings' : ($isEdit ? 'Update member information and permissions' : 'Add a new member to the Decision Making Committee');
$iconClass = $isMyAccount ? 'account' : ($isEdit ? 'edit' : '');
$badgeClass = $isMyAccount ? 'account' : ($isEdit ? 'edit' : 'add');
$badgeText = $isMyAccount ? 'My Account' : ($isEdit ? 'Editing' : 'New Member');
$badgeIcon = $isMyAccount ? 'fa-user-circle' : ($isEdit ? 'fa-edit' : 'fa-user-plus');
?>

<script>
    jQuery("#page_title").html("<?php echo $pageTitle; ?>");

    function committeeUsername(val) {
        if (val.length > 0) {
            val = val.replace(/[^a-zA-Z0-9]/g, '');
            jQuery("#committeeMemberForm input[name='username']").val(val);
            jQuery("#committeeUsername").html('<i class="fas fa-check-circle"></i> co-' + val);
        } else {
            jQuery("#committeeUsername").html('');
        }
    }

    function showPassword() {
        var input = jQuery("#committeeMemberForm input[name='password']");
        var icon = jQuery("#passwordToggleIcon");
        if (input.attr('type') == 'text') {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    }

    jQuery(document).ready(function() {
        jQuery("#committeeMemberForm input").attr('autocomplete', 'off').attr('autofill', 'off');
        committeeUsername(jQuery("#committeeMemberForm input[name='username']").val());
    });

    function validatePhone(phone) {
        jQuery(phone).val(jQuery(phone).val().replace(/[^0-9\+]/g, '').replace(/\s/g, ''));
    }

    function checkPasswordStrength() {
        var password = jQuery("#committeeMemberForm input[name='password']").val();
        var info = jQuery(".password-info");
        
        password = password.replace(/\s/g, '');
        
        if (password.length == 0) {
            info.html('').removeClass('strong weak');
            return;
        }
        
        if (password.length < 8) {
            info.html('<i class="fas fa-times-circle"></i> Password is too short (min 8 characters)').removeClass('strong').addClass('weak');
            return;
        }
        if (!/\d/.test(password)) {
            info.html('<i class="fas fa-times-circle"></i> Must contain at least one number').removeClass('strong').addClass('weak');
            return;
        }
        if (!/[A-Z]/.test(password)) {
            info.html('<i class="fas fa-times-circle"></i> Must contain at least one uppercase letter').removeClass('strong').addClass('weak');
            return;
        }
        if (!/[a-z]/.test(password)) {
            info.html('<i class="fas fa-times-circle"></i> Must contain at least one lowercase letter').removeClass('strong').addClass('weak');
            return;
        }
        if (!/[!@#$%&*]/.test(password)) {
            info.html('<i class="fas fa-times-circle"></i> Must contain at least one special character (!@#$%&*)').removeClass('strong').addClass('weak');
            return;
        }
        
        info.html('<i class="fas fa-check-circle"></i> Password is strong').removeClass('weak').addClass('strong');
    }

    function generatePassword() {
        let password = '';
        var length = 12;
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';

        for (let i = 0; i < length; i++) {
            password += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        
        if (!/\d/.test(password) || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[!@#$%&*]/.test(password)) {
            generatePassword();
        } else {
            jQuery("#committeeMemberForm input[name='password']").val(password);
            checkPasswordStrength();
        }
    }

    async function delete_signature(comemid) {
        await confirm_message('Are you sure you want to delete this signature?');
        jQuery.post('/iidc/admin/committee/committee_save.php', {
            act: 'delete_signature',
            comemid: comemid
        }, function(data) {
            if (data == 'success') {
                jQuery("#signature_preview").html('<span class="no-signature"><i class="fas fa-signature"></i> No signature uploaded</span>');
            } else {
                alert_message('Error deleting signature');
            }
        });
    }

    function selectFunction(val) {
        if (val == 'other') {
            jQuery("#member_function_other").css('display', 'block').attr('data-required', 'yes');
        } else {
            jQuery("#member_function_other").css('display', 'none').removeAttr('data-required').val('');
        }
    }
</script>

<div class="committee-form-header">
    <div class="committee-form-header-content">
        <div class="committee-form-header-icon <?php echo $iconClass; ?>">
            <i class="fas <?php echo $isMyAccount ? 'fa-user-cog' : ($isEdit ? 'fa-user-edit' : 'fa-user-plus'); ?>"></i>
        </div>
        
        <div class="committee-form-header-info">
            <h2>
                <?php echo $pageTitle; ?>
                <span class="committee-action-badge <?php echo $badgeClass; ?>">
                    <i class="fas <?php echo $badgeIcon; ?>"></i>
                    <?php echo $badgeText; ?>
                </span>
            </h2>
            <p><?php echo $pageDescription; ?></p>
        </div>
        
        <div class="committee-form-header-actions">
            <?php if (!$isMyAccount) { ?>
            <a href="/iidc/admin/committee/" class="btn-committee-action back">
                <i class="fas fa-arrow-left"></i>
                Back to Members
            </a>
            <?php } ?>
        </div>
    </div>
    
    <?php if ($isEdit && isset($row['member_name']) && !$isMyAccount) { 
        $initials = '';
        $nameParts = explode(' ', $row['member_name']);
        foreach ($nameParts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        $initials = substr($initials, 0, 2);
    ?>
    <div class="committee-member-strip">
        <div class="member-avatar">
            <?php echo $initials; ?>
        </div>
        <div class="member-details">
            <p class="member-name"><?php echo htmlspecialchars($row['member_name']); ?></p>
            <p class="member-meta">
                <?php if (isset($functions[$row['member_function']])) { ?>
                <span><i class="fas fa-briefcase"></i> <?php echo $functions[$row['member_function']]; ?></span>
                <?php } ?>
                <?php if (trim($row['member_email']) != '') { ?>
                <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($row['member_email']); ?></span>
                <?php } ?>
            </p>
        </div>
    </div>
    <?php } ?>
</div>

<form id="committeeMemberForm" autocomplete="off" action="<?php echo isset($_GET['comemid']) ? '/iidc/admin/committee/' : '' ?>committee_save.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="act" value="<?php echo isset($_GET['comemid']) ? 'update_committee_member' : 'add_committee_member' ?>">
    <?php if (isset($_GET['comemid'])) { ?>
        <input type="hidden" name="comemid" value="<?php echo $_GET['comemid']; ?>">
    <?php } ?>
    <input type="hidden" name="comDir" value="<?php echo $comDir; ?>">
    
    <div class="committee-form-container">
        <div class="committee-form-body">
            <div class="committee-form-left">
                <div class="form-section-title">
                    <i class="fas fa-user"></i>
                    Member Information
                </div>
                
                <table class="committee-form-table">
                    <tr>
                        <th>Member Name *</th>
                        <td><input type="text" name="member_name" data-required="yes" value="<?php echo htmlspecialchars($row['member_name']); ?>" placeholder="Enter full name" /></td>
                    </tr>
                    
                    <?php 
                        if (isset($functions[$row['member_function']]))
                            $function = $functions[$row['member_function']];
                        else
                            $function = 'other';
                    ?>
                    <tr>
                        <th>Function *</th>
                        <td>
                            <select name="member_function" data-required="yes" onchange="selectFunction(this.value)">
                                <option value="">-- Select function --</option>
                                <?php foreach ($functions as $funcKey => $funcVal) { ?>
                                    <option value="<?php echo $funcKey; ?>" <?php echo $function == $funcVal ? 'selected' : ''; ?>><?php echo $funcVal; ?></option>
                                <?php } ?>
                                <option value="other" <?php echo $function == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <input type="text" style="display:<?php echo $function == 'other' ? 'block' : 'none'; ?>; margin-top: 10px;" name="member_function_other" id="member_function_other" value="<?php echo $function == 'other' ? htmlspecialchars($row['member_function']) : ''; ?>" <?php echo $function == 'other' ? 'data-required="yes"': ''; ?> placeholder="Specify function" />
                        </td>
                    </tr>
                     
                    <tr>
                        <th>Email *</th>
                        <td><input type="email" name="member_email" data-required="yes" value="<?php echo htmlspecialchars($row['member_email']); ?>" placeholder="email@example.com" /></td>
                    </tr>
                    
                    <tr>
                        <th>Phone Numbers</th>
                        <td>
                            <ul class="phone-list">
                                <li>
                                    <strong>Primary *</strong>
                                    <input type="tel" name="member_mobile_phone" id="member_mobile_phone" data-required="yes" value="<?php echo htmlspecialchars($row['member_mobile_phone']); ?>" placeholder="+31612345678" />
                                </li>
                                <li>
                                    <strong>Secondary</strong>
                                    <input type="tel" name="member_telephone" id="member_telephone" value="<?php echo htmlspecialchars($row['member_telephone']); ?>" placeholder="+31612345678" />
                                </li>
                            </ul>
                            <p class="phone-hint"><i class="fas fa-info-circle"></i> Include international code, no spaces (e.g., +31612345678)</p>
                        </td>
                    </tr>
                    
                    <?php /* Username and Password fields removed
                    <tr>
                        <th>Username *</th>
                        <td>
                            <input type="text" style="width: 50%;" name="username" placeholder="Username" data-required="yes" value="<?php echo str_replace('co-', '', $row['username']); ?>" onkeyup="committeeUsername(this.value)" />
                            <div id="committeeUsername" class="username-preview"></div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th>Password *</th>
                        <td>
                            <div class="password-wrapper">
                                <input type="password" name="password" data-required="yes" value="<?php echo $row['password']; ?>" placeholder="Enter password" onkeyup="checkPasswordStrength()" />
                                <span class="password-toggle" onclick="showPassword()" title="Toggle visibility">
                                    <i id="passwordToggleIcon" class="fa fa-eye"></i>
                                </span>
                                <?php if (!$isMyAccount) { ?>
                                <span class="password-generate" onclick="generatePassword()">
                                    <i class="fa fa-key"></i> Generate
                                </span>
                                <?php } ?>
                            </div>
                            <span class="password-info"></span>
                        </td>
                    </tr>
                    */ ?>
                    <!-- Hidden fields to satisfy form validation -->
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($row['username']); ?>" />
                    <input type="hidden" name="password" value="<?php echo htmlspecialchars($row['password']); ?>" />
                    
                    <tr>
                        <th>Signature</th>
                        <td>
                            <input type="file" name="signature" accept="image/*" />
                            <div class="signature-preview" id="signature_preview">
                                <?php if ($user_signature = get_user_signature(isset($row['comemid']) ? $row['comemid'] : (isset($_SESSION['comemid']) ? $_SESSION['comemid'] : 0))) { ?>
                                    <img src="<?php echo $user_signature; ?>" alt="Signature" />
                                    <span class="delete-signature" onclick="delete_signature(<?php echo isset($row['comemid']) ? $row['comemid'] : $_SESSION['comemid']; ?>)">
                                        <i class="fa fa-trash-alt"></i> Delete
                                    </span>
                                <?php } else { ?>
                                    <span class="no-signature"><i class="fas fa-signature"></i> No signature uploaded</span>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="committee-form-right">
                <div class="form-section-title">
                    <i class="fas fa-building"></i>
                    Responsible Offices
                </div>
                
                <?php if ($comDir == 'admin') { ?>
                <div class="offices-search">
                    <input type="text" class="search" data-list="officesList" placeholder="Search offices..." />
                </div>
                <?php } ?>
                
                <?php 
                if ($comDir == 'committee' and ((isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 'no') or $_SESSION['user_type'] == 'hqc_office'))
                    $whr = "AND find_in_set(offid, '{$row['member_offices']}')";
                else
                    $whr = '';

                if ($offices = $amdb->get_results("SELECT * FROM offices WHERE status!='deleted' $whr ORDER BY office_name ASC")) {
                    if (trim($row['member_offices']) != '')
                        $member_offices = explode(',', $row['member_offices']);
                ?>
                <ul class="offices-list" id="officesList">
                    <?php foreach ($offices as $office) {
                        $checked = (isset($member_offices) && in_array($office['offid'], $member_offices)) ? 'checked' : '';
                    ?>
                    <li>
                        <label>
                            <?php if ($comDir == 'committee') { ?>
                                <i class="fas fa-caret-right" style="color: #f97316;"></i>
                            <?php } else { ?>
                                <input type="checkbox" name="member_office[]" value="<?php echo $office['offid']; ?>" <?php echo $checked; ?>>
                            <?php } ?>
                            <?php echo htmlspecialchars($office['office_name']); ?>
                            <?php if (trim($office['contact_person']) != '') { ?>
                                <span class="contact-person">(<?php echo htmlspecialchars($office['contact_person']); ?>)</span>
                            <?php } ?>
                        </label>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
                
                <?php if (isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 'no') { ?>
                <div class="offices-note">
                    <i class="fas fa-info-circle"></i>
                    To add more offices to your account, please contact the administrator.
                </div>
                <?php } ?>
            </div>
        </div>
        
        <div class="committee-form-footer">
            <?php if (!$isMyAccount) { ?>
            <button type="button" class="btn-form-action cancel" onclick="location='<?php echo $comDir == 'admin' ? '/iidc/admin/committee/' : '/committee/'; ?>'">
                <i class="fas fa-times"></i>
                Cancel
            </button>
            <?php } ?>
            <button type="reset" class="btn-form-action reset">
                <i class="fas fa-undo"></i>
                Reset
            </button>
            <button type="submit" class="btn-form-action save">
                <i class="fas fa-save"></i>
                <?php echo $isEdit ? 'Update Member' : 'Save Member'; ?>
            </button>
        </div>
    </div>
</form>

<script>
    // Override functions related to removed username/password fields
    function committeeUsername() {}
    function checkPasswordStrength() {}
    function showPassword() {}
    function generatePassword() {}
    function validatePhone() { return true; }
    do_document_ready();

    // AJAX form submission
    jQuery('#committeeMemberForm').on('submit', function(e) {
        e.preventDefault();

        var form = jQuery(this);
        var error = false;
        var error_color = '#fee';

        // Validate required fields
        form.find('[data-required]').each(function() {
            if (jQuery(this).attr('data-color'))
                jQuery(this).css('background-color', jQuery(this).attr('data-color'));
            else
                jQuery(this).attr('data-color', jQuery(this).css('background-color'));

            if (jQuery.trim(jQuery(this).val()) === '') {
                jQuery(this).css('background-color', error_color);
                error = true;
            }
        });

        // Validate email
        form.find('input[type="email"][data-required]').each(function() {
            var val = jQuery(this).val();
            if (val.indexOf('@') <= 0 || val.indexOf('.') <= 0) {
                jQuery(this).css('background-color', error_color);
                if (typeof alert_message === 'function')
                    alert_message('Please use a valid email address!');
                else
                    alert('Please use a valid email address!');
                error = true;
                return false;
            }
        });

        if (error) {
            if (typeof alert_message === 'function')
                alert_message('All fields with (*) are required.');
            else
                alert('All fields with (*) are required.');
            return false;
        }

        var formData = new FormData(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalBtnHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        jQuery.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Server response:', response);
                var data = response.trim();

                if (data.indexOf('alert:') > -1) {
                    if (typeof alert_message === 'function')
                        alert_message(data.replace('alert:', ''));
                    else
                        alert(data.replace('alert:', ''));
                } else if (data.indexOf('reload:') > -1) {
                    location.reload();
                } else if (data.indexOf('topReload:') > -1) {
                    top.location.reload();
                } else if (data.indexOf('url:') > -1) {
                    document.location = data.replace('url:', '');
                } else if (data.indexOf('urlReplace:') > -1) {
                    document.location.replace(data.replace('urlReplace:', ''));
                } else if (data.indexOf('topUrl:') > -1) {
                    top.location = data.replace('topUrl:', '');
                } else if (data.indexOf('function:') > -1) {
                    do_function(data.replace('function:', ''));
                } else if (data.length > 0) {
                    // Show any unexpected response for debugging
                    console.log('Unhandled response:', data);
                    if (typeof alert_message === 'function')
                        alert_message(data);
                    else
                        alert(data);
                } else {
                    if (typeof alert_message === 'function')
                        alert_message('Something went wrong, please try again.');
                    else
                        alert('Something went wrong, please try again.');
                }
            },
            error: function(xhr, status, err) {
                console.error('AJAX error:', status, err);
                if (typeof alert_message === 'function')
                    alert_message('Error: ' + err);
                else
                    alert('Error: ' + err);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });
</script>