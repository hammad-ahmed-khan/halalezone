<?php
if (!defined("_HQC_"))
    exit();
?>
<style>
    /* Schedule DMC Meeting Header */
.schedule-meeting-header {
    background: linear-gradient(135deg, #ffffff 0%, #fef7f0 100%);
    border-radius: 12px;
    border: 1px solid #fed7aa;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.schedule-meeting-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.schedule-meeting-header-icon {
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

.schedule-meeting-header-icon.reschedule {
    background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
}

.schedule-meeting-header-info {
    flex: 1;
    min-width: 200px;
}

.schedule-meeting-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.schedule-meeting-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Meeting Badge */
.meeting-badge {
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

.meeting-badge.schedule {
    background: #dcfce7;
    color: #166534;
}

.meeting-badge.reschedule {
    background: #e0f2fe;
    color: #0369a1;
}

.meeting-badge i {
    font-size: 10px;
}

/* Header Actions */
.schedule-meeting-header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-meeting-action {
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

.btn-meeting-action.cancel {
    background: #ffffff;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-meeting-action.cancel:hover {
    background: #f1f5f9;
    color: #475569;
}

/* Branch Info Strip */
.branch-info-strip {
    display: flex;
    align-items: center;
    gap: 24px;
    padding: 14px 32px;
    background: #fff7ed;
    border-top: 1px solid #fed7aa;
    flex-wrap: wrap;
}

.branch-info-strip .info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #9a3412;
}

.branch-info-strip .info-item i {
    color: #f97316;
}

.branch-info-strip .info-item strong {
    color: #78350f;
}

/* Form Container */
.meeting-form-container {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

/* Section Headers */
.meeting-section {
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.meeting-section:last-child {
    border-bottom: none;
}

.meeting-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 16px;
}

.meeting-section-title i {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ea580c;
    font-size: 14px;
}

.meeting-section-title .required-note {
    font-size: 12px;
    font-weight: 400;
    color: #64748b;
    margin-left: auto;
}

/* Members Grid */
.members-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.member-column {
    background: #fafafa;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.member-column-header {
    padding: 12px 16px;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    border-bottom: 1px solid #fed7aa;
}

.member-column-header h4 {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
    color: #9a3412;
}

.member-column-header span {
    font-size: 11px;
    color: #78350f;
}

.member-column-header.shariah {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-bottom-color: #bbf7d0;
}

.member-column-header.shariah h4 { color: #166534; }
.member-column-header.shariah span { color: #15803d; }

.member-column-header.auditor {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-bottom-color: #bfdbfe;
}

.member-column-header.auditor h4 { color: #1e40af; }
.member-column-header.auditor span { color: #1d4ed8; }

.member-column-header.management {
    background: linear-gradient(135deg, #fdf4ff 0%, #f5d0fe 100%);
    border-bottom-color: #e9d5ff;
}

.member-column-header.management h4 { color: #7e22ce; }
.member-column-header.management span { color: #9333ea; }

.member-list {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 200px;
    overflow-y: auto;
}

.member-list li {
    padding: 10px 16px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
    position: relative;
}

.member-list li:last-child {
    border-bottom: none;
}

.member-list li:hover {
    background: #fffbeb;
}

.member-list li label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 14px;
    color: #374151;
}

.member-list li input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #f97316;
}

.member-list li .hoc-label {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #ea580c;
    background: #fff7ed;
    padding: 4px 8px;
    border-radius: 4px;
    border: 1px solid #fed7aa;
}

.member-list li .hoc-label input {
    accent-color: #ea580c;
}

.member-list .empty-message {
    color: #94a3b8;
    font-style: italic;
    padding: 16px;
    text-align: center;
}

/* Two Column Layout */
.meeting-two-cols {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 24px;
}

/* Clients Section */
.clients-section {
    background: #fafafa;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 16px;
}

.clients-search {
    margin-bottom: 12px;
}

.clients-search input {
    width: 100%;
    padding: 10px 14px;
    font-size: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.25s ease;
}

.clients-search input:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
}

.clients-list {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 200px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.clients-list li {
    padding: 0px 14px;
    border-bottom: 1px solid #f1f5f9;
    transition: all 0.2s ease;
}

.clients-list li:last-child {
    border-bottom: none;
}

.clients-list li:hover {
    background: #fff7ed;
}

.clients-list li label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 13px;
    color: #374151;
}

.clients-list li input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: #f97316;
}

.clients-list li.scheduled {
    background: #f0fdf4;
    color: #166534;
}

.clients-list li.scheduled .scheduled-badge {
    font-size: 11px;
    color: #16a34a;
    margin-left: auto;
}

.clients-hint {
    font-size: 12px;
    color: #64748b;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.clients-hint i {
    color: #f97316;
}

/* Meeting Details Card */
.meeting-details-card {
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 20px;
}

.meeting-details-card h4 {
    margin: 0 0 16px 0;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}

.meeting-details-card h4 i {
    color: #f97316;
}

.meeting-field {
    margin-bottom: 16px;
}

.meeting-field:last-child {
    margin-bottom: 0;
}

.meeting-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.meeting-field-row {
    display: flex;
    gap: 12px;
}

.meeting-field input[type="text"],
.meeting-field select {    
    font-size: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.25s ease;
}

.meeting-field input:focus,
.meeting-field select:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
}

.meeting-field .date-input {
    flex: 1;
}

.meeting-field .time-select {
    width: 120px;
}

.meeting-field .location-select {
    width: 100%;
}

.zoom-options {
    margin-top: 12px;
    padding: 12px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
}

.zoom-options label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #0369a1;
    cursor: pointer;
}

.zoom-options input[type="checkbox"] {
    accent-color: #0369a1;
}

.zoom-topic-input {
    margin-top: 10px;
}

.zoom-topic-input input {
    width: 100%;
    padding: 10px 14px;
    font-size: 13px;
    border: 1px solid #bae6fd;
    border-radius: 6px;
}

/* Email Section */
.email-section {
    background: #f8fafc;
}

.email-field {
    margin-bottom: 16px;
}

.email-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.email-field input[type="text"] {
    width: 100%;
    padding: 12px 16px;
    font-size: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.25s ease;
}

.email-field input:focus {
    outline: none;
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
}

/* Form Footer */
.meeting-form-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
    padding: 20px 24px;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    border-top: 1px solid #fed7aa;
    flex-wrap: wrap;
}

.footer-options {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.footer-options label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #64748b;
    cursor: pointer;
}

.footer-options input[type="checkbox"] {
    accent-color: #f97316;
}

.footer-buttons {
    display: flex;
    gap: 12px;
}

.btn-form-submit {
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

.btn-form-submit.primary {
    background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
    color: #ffffff;
}

.btn-form-submit.primary:hover {
    background: linear-gradient(135deg, #c2410c 0%, #ea580c 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
}

.btn-form-submit.secondary {
    background: #ffffff;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-form-submit.secondary:hover {
    background: #f1f5f9;
    color: #475569;
}

.test-email-input {
    display: none;
    padding: 8px 12px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    width: 200px;
}

.test-email-input.visible {
    display: inline-block;
}

/* Responsive */
@media (max-width: 1024px) {
    .members-grid {
        grid-template-columns: 1fr;
    }
    
    .meeting-two-cols {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .schedule-meeting-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .schedule-meeting-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .branch-info-strip {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px 20px;
    }
    
    .meeting-section {
        padding: 16px;
    }
    
    .meeting-field-row {
        flex-direction: column;
    }
    
    .meeting-field .time-select {
        width: 100%;
    }
    
    .meeting-form-footer {
        flex-direction: column;
    }
    
    .footer-options {
        justify-content: center;
    }
    
    .footer-buttons {
        width: 100%;
        flex-direction: column;
    }
    
    .btn-form-submit {
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
        if (file_exists($prog_path . $image_file . $ext)) {
            return $image_file . $ext;
        }
    }
    return '';
}

$members = array();
$functions = array(
    'ABM' => 'Auditor Board Member',
    'MBM' => 'Management Board Member',
    'SBM' => 'Shariah Board Member',
);

if ($_SESSION['offid'] == 0) {
    $uid = $_SESSION['halal']['id'];
    $offid = $_SESSION['offid'];
} else {
    $uid = $_SESSION['offid'];
    $offid = $_SESSION['offid'];
}

if ($commebersAll = $amdb->get_results("SELECT comemid,uid,offid,bm,member_name,member_function,member_offices,password FROM hqc_committee_members WHERE status='active' order by member_name ASC")) {
    foreach ($commebersAll as $member) {
        if ($offid == $member['offid']) {
            if ($uid == $member['uid']) {
                $comemid = $member['comemid'];
                $mem_password = $member['password'];
            }
            if ($member['bm'] == 'yes') {
                $BM['member_name'] = $member['member_name'];
                $BM['comemid'] = $member['comemid'];
            }
        }
        if (isset($functions[$member['member_function']]) && get_user_signature($member['comemid']))
            $members[$member['member_function']][$member['comemid']] = $member;
    }
}

$office = get_office_data($_SESSION['offid']);
if ($_SESSION['offid'] != 0) {
    $clients = $office['clients'];
    if (!$companies = $amdb->get_results("Select clid,company_name,country1 FROM companies WHERE `clid` IN ($clients) AND active = 'y' ORDER BY TRIM(company_name) ASC"))
        $companies = array();
} else {
    if (!$companies = $amdb->get_results("Select clid,company_name,country1 FROM companies WHERE offid='$_SESSION[offid]' AND active = 'y' ORDER BY TRIM(company_name) ASC"))
        $companies = array();
}

$companies_list = array();
$data = array();
if (count($companies) > 0) {
    foreach ($companies as $cert) {
        $companies_list[$cert['clid']] = $cert['company_name'] . ' (' . $cert['country1'] . ')';
    }
    if (count($companies) == 1)
        $data['company_name'] = $companies[0]['company_name'];
}

if (!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='decision_committee'"))
    $row = $amdb->get_columns('invoice_templates');

$row['email_body'] = str_replace('<br /><br /><br />', '<br /><br />', $row['email_body']);

$comemids = array();
$event_details = array();
$isReschedule = isset($_GET['act']) && $_GET['act'] == 'reschedule';

if (isset($_REQUEST['decid'])) {
    $oldMeeting = $amdb->get_row("SELECT * FROM hqc_committee_decision WHERE decid='$_REQUEST[decid]'");
    $comemids = explode(',', $oldMeeting['comemids']);
    $event_details = json_decode($oldMeeting['event_details'], true);
    $row['email_body'] = str_replace('[proposed_date]', '<span class="proposed_date">' . date("d/m/Y", strtotime($event_details['date'])) . '</span>', $row['email_body']);
    $row['email_body'] = str_replace('[proposed_time]', '<span class="proposed_time">' . $event_details['time'] . '</span>', $row['email_body']);
    if (isset($event_details['zoom-link']) && $event_details['zoom-link'] != '')
        $location = '[zoom-link]';
    else
        $location = $event_details['location'];
    $row['email_body'] = str_replace('[proposed_location]', '<span class="proposed_location">' . $location . '</span>', $row['email_body']);
} else {
    $oldMeeting = $amdb->get_columns('hqc_committee_decision');
}

if (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) {
    $uid = $_SESSION['user']['uid'];
    $contact_person = isset($_SESSION['user']['username_owner']) ? $_SESSION['user']['username_owner'] : $_SESSION['user']['name'];
} else {
    $uid = $_SESSION['offid'];
    $contact_person = $office['contact_person'];
}

$pending_clids = array();
if ($pendings = $amdb->get_results("SELECT clid,meeting_date FROM hqc_committee_decision WHERE status='pending' AND clid !='0'")) {
    foreach ($pendings as $pending) {
        $pending_clids[$pending['clid']] = $pending['meeting_date'];
    }
}

$last_issued_date = '';
if (isset($_GET['crtNr'])) {
    if ($certificate = $amdb->get_row("SELECT date_of_issue,reissued FROM `acms_halal_certificates` WHERE `crtNr` = $_GET[crtNr] ORDER BY reissued DESC, date_of_issue DESC LIMIT 1")) {
        $last_issued_date = ($certificate['reissued'] > $certificate['date_of_issue']) ? $certificate['reissued'] : $certificate['date_of_issue'];
        $last_issued_date = date('Y-m-d', strtotime(date('Y-m-d', $last_issued_date) . ' -1 day'));
        $event_details['time'] = date('H:00', strtotime('now -1 hour'));
    }
}
?>

<script>
    jQuery(".ui-dialog .ui-dialog-buttonpane", window.parent.document).remove();
    jQuery("#page_title").html("<?php echo $isReschedule ? 'Reschedule' : 'Schedule'; ?> DMC Meeting");

    // Helper function to get the TinyMCE editor safely
    function getEmailEditor() {
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('tinymce_editor')) {
            return tinyMCE.get('tinymce_editor');
        }
        return null;
    }

    function SendEmail(form) {
        if (jQuery(".shariah:checked").length < 2) {
            alert_message('Please select at least 2 Shariah Board Members');
            return false;
        }
        if (jQuery(".auditors:checked").length == 0) {
            alert_message('Please select at least one Auditor');
            return false;
        }
        if (jQuery("#signatories_involved_in_the_audit").val() == '') {
            alert_message('Please answer the question:<br/>Were the signatories involved in the audit?');
            return false;
        }
        if (jQuery("#signatories_involved_in_the_audit").val() == 'Yes') {
            alert_message('Please select another Member not involved in the audit');
            return false;
        }
        <?php if (!isset($_REQUEST['decid'])) { ?>
        if (jQuery(".company:checked").length == 0 || jQuery(".company:checked").length > 5) {
            alert_message('Please select at least 1 company and maximum 5 companies');
            return false;
        }
        <?php } ?>
        return post_this_form(form);
    }

    function setZoomLink() {
        var editor = getEmailEditor();
        if (!editor) return;
        
        var editorContent = editor.getBody();
        if (jQuery(editorContent).find('.proposed_location').length > 0) {
            var locationText = jQuery("#zoom_link").is(":checked") ? '[zoom-link]' : jQuery("#proposed_location").val();
            jQuery("#zoomLink").toggle(jQuery("#zoom_link").is(":checked"));
            jQuery(editorContent).find('.proposed_location').html(locationText);
            editor.setContent(editorContent.innerHTML);
        }
    }

    function changeTinymceContentDateTime(dateTime) {
        var editor = getEmailEditor();
        if (!editor) return;
        
        var editorContent = editor.getBody();
        var value, className;
        
        if (dateTime == 'date') {
            value = jQuery("#proposed_date").val().split('-').reverse().join('/');
            className = 'proposed_date';
        } else if (dateTime == 'time') {
            value = jQuery("#proposed_time").val();
            className = 'proposed_time';
        } else if (dateTime == 'location') {
            value = jQuery("#proposed_location").val();
            if (value == 'Online') value = '[zoom-link]';
            className = 'proposed_location';
            <?php if (isset($_REQUEST['decid'])) { ?>
            jQuery("#oldZoomLink").toggle(jQuery("#proposed_location").val() == 'Online');
            <?php } ?>
        }
        
        if (jQuery(editorContent).find('.' + className).length > 0) {
            jQuery(editorContent).find('.' + className).html(value);
            editor.setContent(editorContent.innerHTML);
        } else {
            var regex = new RegExp('\\[' + className.replace('_', '_') + '\\]', 'g');
            var htmlContent = editor.getContent();
            htmlContent = htmlContent.replace(regex, '<span class="' + className + '">' + value + '</span>');
            editor.setContent(htmlContent);
        }
    }

    function findClients(search) {
        jQuery("#clients li").each(function() {
            jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(search.toLowerCase()) !== -1);
        });
    }
    
    function showTestEmail(obj) {
        jQuery("#testEmailTo").toggleClass('visible', jQuery(obj).is(':checked'));
        if (jQuery(obj).is(':checked')) {
            jQuery("#testEmailTo").attr('data-required', 'yes');
        } else {
            jQuery("#testEmailTo").removeAttr('data-required');
        }
    }
    
    function setHOC(hoc = '') {
        jQuery(".member-list li").find(".hoc-label").remove();
        jQuery(".member-list li input[type='checkbox']:checked").each(function() {
            var checked = (hoc == jQuery(this).val()) ? 'checked' : '';
            jQuery(this).closest('li').append('<label class="hoc-label"><input type="radio" name="hoc" value="' + jQuery(this).val() + '" data-required="yes" ' + checked + '/> HOC</label>');
        });
    }
    
    function updateEditorList(thisClass) {
        var editor = getEmailEditor();
        if (!editor) return;
        
        var editorContent = editor.getBody();
        var theList, targetList, regex;
        
        if (thisClass == 'company') {
            theList = '#clients';
            targetList = 'companies_list';
            regex = /\[companies_list\]/g;
        } else {
            theList = '.member-list';
            targetList = 'committee_members';
            regex = /\[committee_members\]/g;
        }
        
        var checkedItems = jQuery("input:checked", theList).map(function() {
            return jQuery(this).parent('label').text().trim();
        }).get();
        
        if (checkedItems.length > 0) {
            var listHtml = '<li>' + checkedItems.join('</li><li>') + '</li>';
            if (jQuery(editorContent).find('.' + targetList).length > 0) {
                jQuery(editorContent).find('.' + targetList).html(listHtml);
                editor.setContent(editorContent.innerHTML);
            } else {
                var htmlContent = editor.getContent();
                htmlContent = htmlContent.replace(regex, '<ol class="' + targetList + '">' + listHtml + '</ol>');
                editor.setContent(htmlContent);
            }
        } else {
            if (jQuery(editorContent).find('.' + targetList).length > 0) {
                jQuery(editorContent).find('.' + targetList).html('');
                editor.setContent(editorContent.innerHTML);
            }
        }
    }
    
    // Function to initialize editor-dependent features
    function initEditorFeatures() {
        changeTinymceContentDateTime('date');
        changeTinymceContentDateTime('time');
    }
</script>

<div class="schedule-meeting-header">
    <div class="schedule-meeting-header-content">
        <div class="schedule-meeting-header-icon <?php echo $isReschedule ? 'reschedule' : ''; ?>">
            <i class="fas <?php echo $isReschedule ? 'fa-calendar-alt' : 'fa-calendar-plus'; ?>"></i>
        </div>
        
        <div class="schedule-meeting-header-info">
            <h2>
                <?php echo $isReschedule ? 'Reschedule' : 'Schedule'; ?> DMC Meeting
                <span class="meeting-badge <?php echo $isReschedule ? 'reschedule' : 'schedule'; ?>">
                    <i class="fas <?php echo $isReschedule ? 'fa-redo' : 'fa-plus'; ?>"></i>
                    <?php echo $isReschedule ? 'Rescheduling' : 'New Meeting'; ?>
                </span>
            </h2>
            <p><?php echo $isReschedule ? 'Update meeting date, time, and participants' : 'Schedule a new Decision Making Committee meeting'; ?></p>
        </div>
        
        <div class="schedule-meeting-header-actions">
            <button type="button" class="btn-meeting-action cancel" onclick="closePopupDialog()">
                <i class="fas fa-times"></i>
                Cancel
            </button>
        </div>
    </div>
    
    <div class="branch-info-strip">
        <div class="info-item">
            <i class="fas fa-building"></i>
            <span>Branch:</span>
            <strong><?php echo htmlspecialchars($office['company_name_english']); ?></strong>
        </div>
        <div class="info-item">
            <i class="fas fa-user"></i>
            <span>Requested by:</span>
            <strong><?php echo htmlspecialchars($contact_person); ?></strong>
        </div>
        <div class="info-item">
            <i class="fas fa-calendar"></i>
            <span>Request Date:</span>
            <strong><?php echo date('d/m/Y'); ?></strong>
        </div>
    </div>
</div>

<form action="committee_email_save.php" method="post" name="committee_email" id="committee_email" onsubmit="return SendEmail(this)">
    <input type="hidden" name="act" value="<?php echo $isReschedule ? 'reschedule_meeting' : 'send_email'; ?>" />
    <?php if (isset($_REQUEST['decid'])): ?>
        <input type="hidden" name="decid" value="<?php echo $_REQUEST['decid']; ?>" />
    <?php endif; ?>
    <input type="hidden" name="email[from_email]" value="<?php echo $row['email_reply_address']; ?>" />
    <input type="hidden" name="email[from_name]" value="HQC-Headquarter" />
    <input type="hidden" name="branch[Branch]" value="<?php echo $office['company_name_english']; ?>" />
    <input type="hidden" name="branch[BranchManager]" value="<?php echo $office['contact_person']; ?>" />
    <input type="hidden" name="branch[RequestedBy]" value="<?php echo $contact_person; ?>" />
    <input type="hidden" name="branch[RequestDate]" value="<?php echo date('d/m/Y'); ?>" />
    <input type="hidden" name="offid" value="<?php echo $_SESSION['offid']; ?>" />
    <input type="hidden" name="uid" value="<?php echo $uid; ?>" />
    <?php if (isset($_GET['crtNr'])) { ?><input type="hidden" name="crtNr" value="<?php echo $_GET['crtNr']; ?>" /><?php } ?>
    <?php if (isset($_GET['ref'])) { ?><input type="hidden" name="ref" value="<?php echo $_GET['ref']; ?>" /><?php } ?>
    <?php if (isset($_GET['clid'])) { ?><input type="hidden" name="clid" value="<?php echo $_GET['clid']; ?>" /><?php } ?>

    <div class="meeting-form-container">
        <!-- Committee Members Section -->
        <div class="meeting-section" id="committeeMembers">
            <div class="meeting-section-title">
                <i class="fas fa-users"></i>
                Committee Members
                <span class="required-note">Select members who will attend</span>
            </div>
            
            <div class="members-grid">
                <!-- Shariah Board Members -->
                <div class="member-column">
                    <div class="member-column-header shariah">
                        <h4><i class="fas fa-book-reader"></i> Shariah Board Members</h4>
                        <span>At least 2 members required</span>
                    </div>
                    <ul class="member-list">
                        <?php if (!isset($members['SBM']) || count($members['SBM']) == 0) { ?>
                            <li class="empty-message">No Shariah Board Members available</li>
                        <?php } else {
                            foreach ($members['SBM'] as $member) {
                                $checked = in_array($member['comemid'], $comemids) ? 'checked' : '';
                        ?>
                            <li>
                                <label>
                                    <input type="checkbox" class="shariah" name="comemids[]" value="<?php echo $member['comemid']; ?>" <?php echo $checked; ?>>
                                    <?php echo htmlspecialchars($member['member_name']); ?>
                                </label>
                            </li>
                        <?php }
                        } ?>
                    </ul>
                </div>
                
                <!-- Auditor Board Members -->
                <div class="member-column">
                    <div class="member-column-header auditor">
                        <h4><i class="fas fa-user-check"></i> Auditor Board Members</h4>
                        <span>At least 1 member required</span>
                    </div>
                    <ul class="member-list">
                        <?php if (!isset($members['ABM']) || count($members['ABM']) == 0) { ?>
                            <li class="empty-message">No Auditor Board Members available</li>
                        <?php } else {
                            foreach ($members['ABM'] as $member) {
                                $checked = in_array($member['comemid'], $comemids) ? 'checked' : '';
                        ?>
                            <li>
                                <label>
                                    <input type="checkbox" class="auditors" name="comemids[]" value="<?php echo $member['comemid']; ?>" <?php echo $checked; ?>>
                                    <?php echo htmlspecialchars($member['member_name']); ?>
                                </label>
                            </li>
                        <?php }
                        } ?>
                    </ul>
                </div>
                
                <!-- Management Board Members -->
                <div class="member-column">
                    <div class="member-column-header management">
                        <h4><i class="fas fa-user-tie"></i> Management Board Members</h4>
                        <span>Optional</span>
                    </div>
                    <ul class="member-list">
                        <?php if (!isset($members['MBM']) || count($members['MBM']) == 0) { ?>
                            <li class="empty-message">No Management Board Members available</li>
                        <?php } else {
                            foreach ($members['MBM'] as $member) {
                                $checked = in_array($member['comemid'], $comemids) ? 'checked' : '';
                        ?>
                            <li>
                                <label>
                                    <input type="checkbox" class="auditors" name="comemids[]" value="<?php echo $member['comemid']; ?>" <?php echo $checked; ?>>
                                    <?php echo htmlspecialchars($member['member_name']); ?>
                                </label>
                            </li>
                        <?php }
                        } ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Clients & Meeting Details Section -->
        <div class="meeting-section">
            <div class="meeting-two-cols">
                <!-- Clients Selection -->
                <div>
                    <div class="meeting-section-title">
                        <i class="fas fa-building"></i>
                        <?php echo isset($_GET['decid']) ? 'Meeting Client' : 'Select Clients'; ?>
                    </div>
                    
                    <div class="clients-section">
                        <?php if (!isset($_GET['decid'])) { ?>
                        <div class="clients-search">
                            <input type="text" id="search_client" onkeyup="findClients(this.value)" placeholder="Search by company name...">
                        </div>
                        <?php } ?>
                        
                        <ol class="clients-list" id="clients">
                            <?php foreach ($companies_list as $clid => $client) {
                                if (isset($pending_clids[$clid])) { ?>
                                    <li class="scheduled">
                                        <i class="far fa-check-square" style="color: #16a34a;"></i>
                                        <?php echo htmlspecialchars($client); ?>
                                        <span class="scheduled-badge">Scheduled: <?php echo date("d/m/Y H:i", strtotime($pending_clids[$clid])); ?></span>
                                    </li>
                                <?php } else {
                                    $comChecked = (isset($_GET['clid']) && $_GET['clid'] == $clid) ? 'checked' : '';
                                    if ($comChecked) {
                                        $row['email_body'] = str_replace('[companies_list]', '<ol class="companies_list">' . $client . '</ol>', $row['email_body']);
                                    }
                                    if (!isset($_GET['decid'])) { ?>
                                        <li>
                                            <label>
                                                <input type="checkbox" class="company" name="clids[]" value="<?php echo $clid; ?>" <?php echo $comChecked; ?>>
                                                <?php echo htmlspecialchars($client); ?>
                                            </label>
                                        </li>
                                    <?php } else {
                                        $row['email_body'] = str_replace('[companies_list]', '<ol class="companies_list">' . $client . '</ol>', $row['email_body']);
                                    }
                                }
                            } ?>
                        </ol>
                        
                        <?php if (!isset($_GET['decid'])) { ?>
                        <p class="clients-hint">
                            <i class="fas fa-info-circle"></i>
                            Select 1-5 companies for this meeting
                        </p>
                        <?php } ?>
                    </div>
                </div>
                
                <!-- Meeting Details -->
                <div>
                    <div class="meeting-section-title">
                        <i class="fas fa-clock"></i>
                        Meeting Details
                    </div>
                    
                    <div class="meeting-details-card">
                        <div class="meeting-field">
                            <label>Date & Time</label>
                            <div class="meeting-field-row">
                                <input type="text" class="date date-input" name="event_details[date]" id="proposed_date" data-required="yes" onchange="changeTinymceContentDateTime('date');" value="<?php echo isset($event_details['date']) ? $event_details['date'] : $last_issued_date; ?>" placeholder="Select date">
                                <select name="event_details[time]" id="proposed_time" class="time-select" data-required="yes" onchange="changeTinymceContentDateTime('time');">
                                    <option value="">--:--</option>
                                    <?php for ($time_slot = 0; $time_slot <= 23; $time_slot++) {
                                        $hour = str_pad($time_slot, 2, '0', STR_PAD_LEFT) . ':00';
                                        $selected = (isset($event_details['time']) && $event_details['time'] == $hour) ? 'selected' : '';
                                        echo '<option value="' . $hour . '" ' . $selected . '>' . $hour . '</option>';
                                    } ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="meeting-field">
                            <label>Location</label>
                            <select class="location-select" onchange="changeTinymceContentDateTime('location'); jQuery('.zoom-options').toggle(this.value == 'Online');" name="event_details[location]" id="proposed_location" data-required="yes">
                                <option value="">Select location</option>
                                <option value="Online" <?php echo (isset($event_details['location']) && $event_details['location'] == 'Online') ? 'selected' : ''; ?>>Online (Video Conference)</option>
                                <option value="Onsite" <?php echo (isset($event_details['location']) && $event_details['location'] == 'Onsite') ? 'selected' : ''; ?>>Onsite (Physical Meeting)</option>
                            </select>
                            
                            <div class="zoom-options" style="display: <?php echo (isset($event_details['location']) && $event_details['location'] == 'Online') ? 'block' : 'none'; ?>;">
                                <label>
                                    <input type="checkbox" id="zoom_link" name="event_details[zoom-link]" onclick="setZoomLink()">
                                    Create Zoom video conference link
                                </label>
                                <div class="zoom-topic-input" id="zoomLink" style="display: <?php echo (isset($event_details['zoom-link']) && trim($event_details['zoom-link']) != '') ? 'block' : 'none'; ?>;">
                                    <input type="text" name="event_details[zoom-topic]" placeholder="Meeting topic" value="DMC meeting for: <?php echo count($companies_list) > 1 ? 'Multiple companies' : (isset($data['company_name']) ? $data['company_name'] : ''); ?>">
                                </div>
                                
                                <?php if (isset($_REQUEST['decid'])) { ?>
                                <div id="oldZoomLink" style="margin-top: 10px; display: <?php echo (isset($event_details['location']) && $event_details['location'] == 'Online') ? 'block' : 'none'; ?>;">
                                    <label>
                                        <input type="checkbox" id="useOldZoomLink" name="useOldZoomLink">
                                        Use existing Zoom link if available
                                    </label>
                                    <input type="hidden" name="oldZoomLink" value="<?php echo isset($event_details['zoom-link']) ? $event_details['zoom-link'] : ''; ?>">
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Email Section -->
        <div class="meeting-section email-section">
            <div class="meeting-section-title">
                <i class="fas fa-envelope"></i>
                Email Notification
            </div>
            
            <div class="email-field">
                <label>Email Subject</label>
                <input type="text" name="email[subject]" data-required="yes" value="<?php echo htmlspecialchars($row['email_subject']); ?>">
            </div>
            
            <div class="email-field">
                <label>Email Body</label>
                <textarea class="tinymce" id="tinymce_editor" name="email[message]" style="height: 250px; width:100%;"><?php echo $row['email_body']; ?></textarea>
            </div>
        </div>
        
        <!-- Form Footer -->
        <div class="meeting-form-footer">
            <div class="footer-options">
                <label>
                    <input type="checkbox" name="noEmail">
                    Don't send email
                </label>
                <label>
                    <input type="checkbox" name="createReport">
                    Create DMC report after save
                </label>
                <label>
                    <input type="checkbox" name="sendTestEmail" onclick="showTestEmail(this)">
                    Send test email
                </label>
                <input type="text" name="testEmailTo" id="testEmailTo" class="test-email-input" placeholder="Enter test email">
            </div>
            
            <div class="footer-buttons">
                <button type="reset" class="btn-form-submit secondary">
                    <i class="fas fa-undo"></i>
                    Reset
                </button>
                <button type="button" class="btn-form-submit secondary" onclick="closePopupDialog()">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button type="submit" class="btn-form-submit primary">
                    <i class="fas <?php echo $isReschedule ? 'fa-calendar-check' : 'fa-paper-plane'; ?>"></i>
                    <?php echo $isReschedule ? 'Reschedule Meeting' : 'Send Request'; ?>
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    jQuery(document).ready(function() {
        
        setHOC(<?php echo isset($oldMeeting) && isset($oldMeeting['hoc']) ? $oldMeeting['hoc'] : '""'; ?>);
        
        jQuery(".member-list li input[type='checkbox']").click(function() {
            setHOC();
            updateEditorList(jQuery(this).attr('class'));
        });
        
        jQuery("#clients li input").on("click", function() {
            updateEditorList(jQuery(this).attr('class'));
        });
        
        // Wait for TinyMCE to initialize using its callback
        if (typeof tinyMCE !== 'undefined') {
            // Check if editor already exists
            var checkEditor = setInterval(function() {
                var editor = tinyMCE.get('tinymce_editor');
                if (editor) {
                    clearInterval(checkEditor);
                    // Use TinyMCE's init event
                    editor.on('init', function() {
                        initEditorFeatures();
                    });
                    // If already initialized
                    if (editor.initialized) {
                        initEditorFeatures();
                    }
                }
            }, 100);
            
            // Fallback timeout
            setTimeout(function() {
                clearInterval(checkEditor);
                initEditorFeatures();
            }, 3000);
        }
    });
</script>