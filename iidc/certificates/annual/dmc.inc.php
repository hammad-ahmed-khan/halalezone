<?php
/**
 * DMC Report Form (dmc.inc.php)
 * 
 * Handles both new and edit modes.
 * Loads saved data from hqc_dmc_reports (preferred) or falls back to hqc_committee_decision.
 * Prepopulates all form fields when editing.
 */

// ============================================
// Helper: Get user signature path
// ============================================
if (!function_exists('get_user_signature')) {
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
}

// ============================================
// 1. Load existing DMC data if editing
// ============================================
$dmc_data = array();
$dmc_report = null;
$decision = null;
$is_edit = false;

if (isset($_GET['decid']) && intval($_GET['decid']) > 0) {
    $dmc_data = $amdb->get_row("SELECT * FROM hqc_committee_decision WHERE decid = '" . intval($_REQUEST['decid']) . "'");
    if (!$dmc_data) {
        echo '<div style="color:red;text-align:center;padding:20px;">DMC decision record not found.</div>';
        return;
    }
    $_GET['clid'] = $dmc_data['clid'];
    if (!isset($_REQUEST['crtNr'])) {
        $_REQUEST['clid'] = $dmc_data['clid'];
        $_REQUEST['crtNr'] = $dmc_data['crtNr'];
        $_REQUEST['offid'] = $dmc_data['offid'];
    }
    $decision = unserialize($dmc_data['decision']);
    $is_edit = true;
}

if (isset($_GET['decid']) && intval($_GET['decid']) > 0) {
    $dmc_report = $amdb->get_row("SELECT * FROM hqc_dmc_reports WHERE decid = '" . intval($_GET['decid']) . "'");
} elseif (isset($_REQUEST['crtNr']) && isset($_REQUEST['clid'])) {
    $dmc_report = $amdb->get_row("SELECT * FROM hqc_dmc_reports WHERE crtNr = '" . intval($_REQUEST['crtNr']) . "' AND clid = '" . intval($_REQUEST['clid']) . "' ORDER BY dmcid DESC LIMIT 1");
}

if ($dmc_report) {
    $is_edit = true;
}

// ============================================
// 2. Build prepopulation data from saved report
// ============================================
$saved = array();
if ($dmc_report) {
    $saved = $dmc_report;
    $saved['reviewed'] = json_decode($dmc_report['reviewed_data'], true);
} elseif ($decision && is_array($decision)) {
    $saved = $decision;
    $saved['reviewed'] = array();
    if (isset($decision['reviewed'])) $saved['reviewed']['reviewed'] = $decision['reviewed'];
    if (isset($decision['results']))  $saved['reviewed']['results'] = $decision['results'];
    if (isset($decision['reviewd']))  $saved['reviewed']['reviewd'] = $decision['reviewd'];
    $saved['remarks_on_dmc'] = isset($decision['remarks_on_the_dmc_report']) ? $decision['remarks_on_the_dmc_report'] : '';
    $saved['conclusion_suspension_period'] = isset($decision['conclution_time_period_of_suspension']) ? $decision['conclution_time_period_of_suspension'] : '';
    $saved['conclusion_withdrawn_date'] = isset($decision['conclusion_withdrawn_from_this_date']) ? $decision['conclusion_withdrawn_from_this_date'] : '';
    $saved['signatories_involved_in_audit'] = isset($decision['signatories_involved_in_the_audit']) ? $decision['signatories_involved_in_the_audit'] : '';
    if (isset($decision['comemids']) && is_array($decision['comemids'])) {
        $saved['comemids'] = implode(',', $decision['comemids']);
    } elseif (isset($dmc_data['comemids'])) {
        $saved['comemids'] = $dmc_data['comemids'];
    }
}

// ============================================
// 3. Load certificate data
// ============================================
$certificate_data = array();
if (!isset($_REQUEST['crtNr'])) {
    echo '<div style="color:red;text-align:center;padding:20px;">Certificate number is required.</div>';
    return;
}

$certificate = $amdb->get_row("SELECT * FROM acms_halal_certificates WHERE crtNr='" . intval($_REQUEST['crtNr']) . "'");
if (!$certificate) {
    echo '<div style="color:red;text-align:center;padding:20px;">Certificate not found.</div>';
    return;
}

$options = json_decode($certificate['options'], true);

$certificate_validity = array();
if (isset($options['cert_validity']) && $options['cert_validity'] != 1)
    $certificate_validity[] = 'Valid for: ' . $options['cert_validity'] . ' years';
if (isset($certificate['date_of_expiry']) && $certificate['date_of_expiry'] > 0)
    $certificate_validity[] = 'Expiry date: ' . date("d/m/Y", $certificate['date_of_expiry']);
if (isset($options['recertification']) && is_array($options['recertification']) && isset($options['cert_validity']) && $options['cert_validity'] != 1)
    $certificate_validity[] = 'Recertification date: ' . $options['recertification'][0];

$certificate_data['certificate'] = implode('<br/>', $certificate_validity);
$certificate_data['date_of_dmcr'] = isset($certificate['ordered_on']) && $certificate['ordered_on'] > 0 ? date('d/m/Y', $certificate['ordered_on']) : '';
$certificate_data['scope'] = isset($certificate['scope_of_certification']) ? $certificate['scope_of_certification'] : '';
$certificate_data['valid_until'] = isset($certificate['date_of_expiry']) ? $certificate['date_of_expiry'] : 0;
$certificate_data['date_valid_until'] = (isset($certificate['date_of_expiry']) && $certificate['date_of_expiry'] > 0) ? date("d/m/Y", $certificate['date_of_expiry']) : '';

$certificate_data['category'] = '';
if (isset($certificate['category']) && is_array(json_decode($certificate['category'], true))) {
    $categories = implode(',', json_decode($certificate['category'], true));
    if ($certificate_categories = $amdb->get_results("SELECT * FROM hqc_categories WHERE catid IN ($categories)")) {
        $catList = '<ol>';
        foreach ($certificate_categories as $category) {
            $catList .= '<li>' . $category['category'] . ' - ' . $category['category_name'] . '</li>';
        }
        $catList .= '</ol>';
        $certificate_data['category'] = $catList;
    }
}

$certificate_data['reference_standard'] = '';
if (isset($certificate['reference_standards']) && is_array(json_decode($certificate['reference_standards'], true))) {
    $reference_standards = implode(',', json_decode($certificate['reference_standards'], true));
    $amdb->connect_portal();
    if ($certificate_reference_standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE stnid IN ($reference_standards)")) {
        $refList = '<ol>';
        foreach ($certificate_reference_standards as $reference_standard) {
            $refList .= '<li>' . $reference_standard['code'] . ' - ' . $reference_standard['description'] . '</li>';
        }
        $refList .= '</ol>';
        $certificate_data['reference_standard'] = $refList;
    }
}

// ============================================
// 4. Load production sites
// ============================================
$production_sites_html = '';
$saved_sites = array();
if ($dmc_report && !empty($dmc_report['production_sites'])) {
    $saved_sites = explode(',', $dmc_report['production_sites']);
} elseif ($decision && isset($decision['stids']) && is_array($decision['stids'])) {
    $saved_sites = $decision['stids'];
}

if ($sites = $amdb->get_results("SELECT * FROM companies_production_sites WHERE status!='deleted' AND clid='" . intval($_GET['clid']) . "'")) {
    $production_sites_html = '<div class="dmc-sites-toolbar">
        <input type="text" onkeyup="searchProductionSites(this.value)" placeholder="Search production site">
        <label><input type="checkbox" onclick="selectProductionSites(this);" name="selectAllSites"> Select all sites</label>
    </div>
    <ol id="productionSites">';
    foreach ($sites as $site) {
        $checked = in_array($site['stid'], $saved_sites) ? 'checked' : '';
        $production_sites_html .= '<li><label><input type="checkbox" name="stids[]" value="' . $site['stid'] . '" ' . $checked . '> ' . htmlspecialchars($site['site_name']) . '</label></li>';
    }
    $production_sites_html .= '</ol>';
}
if (trim($production_sites_html) == '') {
    $production_sites_html = '<span style="color:#94a3b8;">NA</span>';
}

// ============================================
// 5. Load committee members
// ============================================
$saved_comemids = array();
if ($dmc_report && !empty($dmc_report['comemids'])) {
    $saved_comemids = explode(',', $dmc_report['comemids']);
} elseif (isset($dmc_data['comemids']) && !empty($dmc_data['comemids'])) {
    $saved_comemids = explode(',', $dmc_data['comemids']);
}

$members = array('SBM' => array(), 'ABM' => array(), 'MBM' => array());
if (isset($_SESSION['offid'])) {
    $offid_filter = intval($_SESSION['offid']);
    if ($all_members = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE status='active' AND (offid='$offid_filter' OR offid='0') ORDER BY member_name ASC")) {
        foreach ($all_members as $member) {
            $func = trim($member['member_function']);
            if ($func == 'SBM' || $func == 'Shariah Board Member') {
                $members['SBM'][] = $member;
            } elseif ($func == 'ABM' || $func == 'Auditor Board Member') {
                $members['ABM'][] = $member;
            } else {
                $members['MBM'][] = $member;
            }
        }
    }
}

// ============================================
// 6. Load office/branch info
// ============================================
$office = array('company_name_english' => '', 'contact_person' => '');
if (isset($_SESSION['offid'])) {
    $office_data = get_office_data($_SESSION['offid']);
    if ($office_data) $office = $office_data;
}

$BM = array('member_name' => '', 'comemid' => '');
if (isset($_SESSION['offid'])) {
    $bm_row = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE bm='yes' AND offid='" . intval($_SESSION['offid']) . "' AND status='active' LIMIT 1");
    if ($bm_row) $BM = $bm_row;
}

if (!isset($contact_person)) {
    if (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) {
        $contact_person = $_SESSION['user']['username_owner'];
    } elseif (isset($_SESSION['user']) && isset($_SESSION['user']['offuid'])) {
        $contact_person = $_SESSION['user']['name'];
    } else {
        $contact_person = $office['contact_person'];
    }
}

$mem_password = '';
if (isset($_SESSION['comemid'])) {
    $mem_row = $amdb->get_row("SELECT pass FROM hqc_committee_members WHERE comemid='" . intval($_SESSION['comemid']) . "'");
    if ($mem_row) $mem_password = base64_decode($mem_row['pass']);
}

$the_client = array('company_name' => '', 'client_id' => '');
if (function_exists('get_client')) {
    $the_client = get_client($_REQUEST['clid']);
}

// ============================================
// 7. Prepare prepopulation values
// ============================================
$val = array();
$val['date_of_dmcr']  = !empty($saved['date_of_dmcr']) ? $saved['date_of_dmcr'] : $certificate_data['date_of_dmcr'];
$val['company_name']  = !empty($saved['company_name']) ? $saved['company_name'] : $the_client['company_name'];
$val['dmr_reference'] = isset($saved['dmr_reference']) ? $saved['dmr_reference'] : '';
$val['objective'] = isset($saved['objective']) ? $saved['objective'] : 'Granting';
$val['objective_others_info'] = isset($saved['objective_others_info']) ? $saved['objective_others_info'] : '';

$reviewed = array();
if (isset($saved['reviewed']) && is_array($saved['reviewed'])) {
    $reviewed = $saved['reviewed'];
}

$val['conclusion']           = isset($saved['conclusion']) ? $saved['conclusion'] : '';
$val['suspension_period']    = isset($saved['conclusion_suspension_period']) ? $saved['conclusion_suspension_period'] : '';
$val['withdrawn_date']       = isset($saved['conclusion_withdrawn_date']) ? $saved['conclusion_withdrawn_date'] : '';
$val['conclusion_other']     = isset($saved['conclusion_other_info']) ? $saved['conclusion_other_info'] : '';
$val['remarks']              = isset($saved['remarks_on_dmc']) ? $saved['remarks_on_dmc'] : '';
$val['signatories_in_audit'] = isset($saved['signatories_involved_in_audit']) ? $saved['signatories_involved_in_audit'] : '';
$val['agreed'] = (isset($saved['agreed']) && $saved['agreed']) ? true : false;

if (!function_exists('is_reviewed_checked')) {
    function is_reviewed_checked($reviewed, $section, $key, $field) {
        if (isset($reviewed['reviewed'][$key][$field])) return true;
        if ($section == 'results' && isset($reviewed['results'][$key][$field])) return true;
        if ($section == 'reviewd' && isset($reviewed['reviewd'][$key][$field])) return true;
        return false;
    }
}
if (!function_exists('get_reviewed_value')) {
    function get_reviewed_value($reviewed, $section, $key, $field) {
        if (isset($reviewed['reviewed'][$key][$field])) return $reviewed['reviewed'][$key][$field];
        if ($section == 'results' && isset($reviewed['results'][$key][$field])) return $reviewed['results'][$key][$field];
        if ($section == 'reviewd' && isset($reviewed['reviewd'][$key][$field])) return $reviewed['reviewd'][$key][$field];
        return '';
    }
}
?>

<!-- ====== DMC REPORT - SCOPED CSS ====== -->
<style>
.company_data { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 13px; color: #1e293b; line-height: 1.5; }
.company_data *, .company_data *::before, .company_data *::after { box-sizing: border-box; }
.company_data .dmc-wrap { max-width: 960px; margin: 0 auto; padding: 0 10px; }
.company_data .dmc-note { text-align: center; padding: 5px 10px; color: #92400e; background: #fffbeb; border: 1px solid #fde68a; border-radius: 4px; margin-bottom: 10px; font-size: 12px; line-height: 1.5; }
.company_data .dmc-note strong { color: #78350f; }
.company_data .dmc-hdr { text-align: center; padding: 4px 0 2px; }
.company_data .dmc-hdr img { display: block; margin: 0 auto 2px; }
.company_data .dmc-hdr h2 { margin: 0; font-size: 15px; font-weight: 600; color: #1e293b; line-height: 1.3; }
.company_data .dmc-hdr h1 { margin: 2px 0 6px; font-size: 19px; font-weight: 700; color: #0f172a; }
.company_data .dmc-sec { font-size: 14px; font-weight: 700; color: #0f172a; margin: 10px 0 4px; padding: 5px 10px; background: #f1f5f9 !important; border-left: 3px solid #1a5f4a; border-radius: 0 4px 4px 0; }

/* Global overrides for all tables inside dmcReport */
.company_data table { border-collapse: collapse !important; border-spacing: 0 !important; width: 100% !important; }
.company_data table td { padding: 2px 5px !important; text-align: left !important; vertical-align: middle !important; font-size: 12px !important; line-height: 17px !important; border-bottom: 1px solid #e2e8f0 !important; }
.company_data table td:after { content: none !important; border: none !important; }
.company_data table th { padding: 3px 5px !important; text-align: left !important; vertical-align: middle !important; font-size: 12px !important; font-weight: bold !important; background: #e9f0e8 !important; border: 1px solid #ddd !important; }

/* All labels - reset global padding */
input[type=checkbox], input[type=radio] {
    
    margin-top: 0 !important;
    line-height: normal;
}

td>label {
    padding: 0 !important;
    margin: 0 !important;
    border-radius: 0 !important;
    font-size: 12px !important;
    color: inherit !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-weight: normal !important;
    cursor: pointer !important;
    transition: none !important;
}

/* All radios and checkboxes */
.company_data input[type="radio"],
.company_data input[type="checkbox"] {
    width: 13px !important;
    height: 13px !important;
    margin: 0 !important;
    vertical-align: middle !important;
    cursor: pointer !important;
    flex-shrink: 0 !important;
}

/* Company data table */
.company_data .tbl-data { border: 1px solid #ccc !important; }
.company_data .tbl-data td { border: 1px solid #ddd !important; padding: 2px 5px !important; }
.company_data .tbl-data tr:nth-child(odd) { background: #fff !important; }
.company_data .tbl-data tr:nth-child(even) { background: #f8fdec !important; }
.company_data .tbl-data .rn { width: 25px !important; text-align: center !important; font-weight: bold !important; background: #e9f0e8 !important; }
.company_data .tbl-data .rl { width: 170px !important; font-weight: normal !important; white-space: nowrap !important; }
.company_data .tbl-data input[type="text"] { padding: 2px 5px !important; border: 1px solid #ccc !important; font-size: 12px !important; }

/* Radio list tables (Objective / Conclusion) */
.company_data .tbl-radio { border: 1px solid #ccc !important; }
.company_data .tbl-radio td { padding: 2px 8px !important; border-bottom: 1px solid #eee !important; border-left: none !important; border-right: none !important; }
.company_data .tbl-radio tr:nth-child(odd) { background: #fff !important; }
.company_data .tbl-radio tr:nth-child(even) { background: #f8fdec !important; }
.company_data .tbl-radio tr:first-child td { font-weight: bold !important; background: #e9f0e8 !important; border-bottom: 1px solid #ccc !important; padding: 3px 8px !important; }
.company_data .tbl-radio tr:last-child td { border-bottom: none !important; }
.company_data .tbl-radio input[type="text"] { padding: 2px 5px !important; border: 1px solid #ccc !important; font-size: 12px !important; }

/* Review table */
.company_data .tbl-rev { border: 1px solid #ccc !important; }
.company_data .tbl-rev th { padding: 3px 5px !important; background: #e9f0e8 !important; border: 1px solid #ddd !important; }
.company_data .tbl-rev th:nth-child(2) { text-align: center !important; }
.company_data .tbl-rev td { padding: 2px 5px !important; border: 1px solid #ddd !important; }
.company_data .tbl-rev td:first-child { width: 200px !important; }
.company_data .tbl-rev td:nth-child(n+2) { text-align: center !important; }
.company_data .tbl-rev tbody tr:nth-child(odd) { background: #fff !important; }
.company_data .tbl-rev tbody tr:nth-child(even) { background: #f8fdec !important; }

/* Remarks */
.company_data .tbl-rem { border: 1px solid #ccc !important; }
.company_data .tbl-rem td { padding: 2px 5px !important; border: 1px solid #ddd !important; }
.company_data .tbl-rem tr:first-child td { font-weight: bold !important; background: #e9f0e8 !important; }
.company_data .tbl-rem textarea { width: 100% !important; min-height: 46px !important; padding: 3px 5px !important; border: 1px solid #ccc !important; font-size: 12px !important; resize: vertical !important; }

/* Members table */
.company_data .tbl-mem { border: 1px solid #ccc !important; }
.company_data .tbl-mem th { padding: 3px 6px !important; background: #e9f0e8 !important; border: 1px solid #ddd !important; width: 33.33% !important; font-size: 12px !important; }
.company_data .tbl-mem th .sub { font-weight: normal !important; font-size: 10px !important; color: #888 !important; }
.company_data .tbl-mem td { padding: 3px 6px !important; border: 1px solid #ddd !important; vertical-align: top !important; }
.company_data .tbl-mem ul { list-style: none !important; margin: 0 !important; padding: 0 !important; }
.company_data .tbl-mem li { padding: 1px 0 !important; font-size: 12px !important; line-height: 17px !important; }

/* Production sites */
.company_data .dmc-sites-toolbar { margin-bottom: 3px; }
.company_data .dmc-sites-toolbar input[type="text"] { padding: 2px 5px !important; border: 1px solid #ccc !important; font-size: 12px !important; width: 160px !important; }
.company_data ol#productionSites { list-style: decimal !important; padding-left: 18px !important; margin: 0 !important; }
.company_data ol#productionSites li { padding: 1px 0 !important; font-size: 12px !important; }

/* Info blocks */
.company_data .dmc-info { margin-top: 6px; font-size: 12px; line-height: 18px; }
.company_data .dmc-info select { padding: 2px 5px !important; border: 1px solid #ccc !important; font-size: 12px !important; }
.company_data .dmc-info .hint { font-size: 11px; color: #888; font-style: italic; }
.company_data .dmc-info .dmr-input { padding: 2px 5px !important; border: 1px solid #ccc !important; font-size: 12px !important; width: 150px; }
.company_data .dmc-stamp { text-align: center; margin: 8px 0; }
.company_data .dmc-agree { margin-top: 8px; padding: 5px 8px; background: #f8fdec; border: 1px solid #e9f0e8; font-size: 12px; line-height: 18px; }
.company_data .dmc-copy { margin-top: 8px; font-size: 10px; color: #888; line-height: 14px; }
.company_data .dmc-submit { margin-top: 10px; text-align: center; padding: 8px; background: #f0f5e5; border: 1px solid #e9f0e8; }
.company_data .dmc-submit input[type="password"] { padding: 4px 8px !important; border: 1px solid #ccc !important; font-size: 12px !important; width: 260px; margin-right: 5px; }
.company_data .dmc-submit .btn-go { padding: 4px 16px; background: #435940; color: #fff; border: 1px solid #3a4d37; font-size: 12px; font-weight: bold; cursor: pointer; }
.company_data .dmc-submit .btn-go:hover { background: #3a4d37; }
.company_data .dmc-submit .btn-rst { padding: 4px 12px; background: #f5f5f5; border: 1px solid #ccc; font-size: 12px; cursor: pointer; margin-left: 3px; }
.company_data .dmc-submit .sub-note { display: block; margin-top: 4px; font-size: 11px; color: brown; }
</style>

<!-- ====== DMC REPORT - JS ====== -->
<script>
var mem_password = '<?php echo $mem_password; ?>';
function checkTheForm() {
    if (!jQuery("input[name='objective']:checked").length) { alert_message('Please select an Objective'); return false; }
    if (!jQuery("input[name='conclusion']:checked").length) { alert_message('Please select a Conclusion'); return false; }
    if (!jQuery("input[name='comemids[]']:checked").length) { alert_message('Please select at least one committee member'); return false; }
    if (jQuery("#signatories_involved_in_the_audit").val() == '') { alert_message('Please select if signatories were involved in the audit'); return false; }
    if (jQuery("#signatories_involved_in_the_audit").val() == 'Yes') { alert_message('Please select another Member not involved in the audit'); return false; }
    if (!jQuery("#agreeOnApplication").is(':checked')) { alert_message('Please confirm that you have reviewed the content of the report'); return false; }
    var password = jQuery("#dmc_password").val().trim();
    if (password == '') { alert_message('Please enter your password to generate the report'); return false; }
    if (password != mem_password) { alert_message('The password is incorrect'); return false; }
    return true;
}
function checkUncheckAll(obj) { jQuery(obj).closest('table').find('input[type=checkbox]').not(obj).prop('checked', jQuery(obj).is(':checked')); }
function searchProductionSites(value) { jQuery("#productionSites li").each(function() { jQuery(this).toggle(jQuery(this).text().toLowerCase().indexOf(value.toLowerCase()) >= 0); }); }
function selectProductionSites(obj) { jQuery('#productionSites').find('input[type=checkbox]').prop('checked', jQuery(obj).is(':checked')); }
</script>

<!-- ====== DMC REPORT - HTML ====== -->
<div id="dmcReport">
<div class="dmc-wrap">

    <div class="dmc-note">
        Note: A password is required to generate the report. You can find the password in your account profile.<br/>
        Close this window, open new window by clicking on <strong>new window</strong> on the Menubar and under DMC on my account
    </div>

    <form name="appForm" id="appForm" action="dmc_save.php" method="post" onsubmit="return checkTheForm()" autocomplete="off">
        <input type="hidden" name="clid" value="<?php echo intval($_REQUEST['clid']); ?>" />
        <input type="hidden" name="crtNr" value="<?php echo intval($_REQUEST['crtNr']); ?>" />
        <input type="hidden" name="offid" value="<?php echo intval($_SESSION['offid']); ?>" />
        <input type="hidden" name="decid" value="<?php echo isset($dmc_data['decid']) ? intval($dmc_data['decid']) : 0; ?>" />
        <input type="hidden" name="ref" value="<?php echo isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : ''; ?>" />
        <input type="hidden" name="uid" value="<?php echo isset($_SESSION['uid']) ? intval($_SESSION['uid']) : 0; ?>" />
        <input type="hidden" name="comemid" id="comemid" value="<?php echo isset($_SESSION['comemid']) ? intval($_SESSION['comemid']) : 0; ?>" />
        <input type="hidden" name="bm" value="<?php echo isset($BM['comemid']) ? intval($BM['comemid']) : 0; ?>" />
        <input type="hidden" name="act" value="save" />
        <input type="hidden" name="branch[Branch]" value="<?php echo htmlspecialchars($office['company_name_english']); ?>" />
        <input type="hidden" name="branch[BranchManager]" value="<?php echo htmlspecialchars($BM['member_name']); ?>" />
        <input type="hidden" name="branch[RequestedBy]" value="<?php echo htmlspecialchars($contact_person); ?>" />
        <input type="hidden" name="branch[RequestDate]" value="<?php echo date('d/m/Y'); ?>" />
        <input type="hidden" name="valid_until" value="<?php echo $certificate_data['valid_until']; ?>" />
        <input type="hidden" name="pass" value="<?php echo $mem_password; ?>" />

        <div class="dmc-hdr">
            <img src="/data/offices/0/images/uploads/logo.png" alt="logo" width="140" height="104">
            <h2>Halal Quality Control</h2>
            <h1>Decision Making Report</h1>
        </div>

        <table class="tbl-data">
            <tr><td class="rn">1</td><td class="rl">Date of DMCR:</td><td><input type="text" class="date" name="date_of_dmcr" id="DateOfDmcr" data-required="yes" value="<?php echo htmlspecialchars($val['date_of_dmcr']); ?>" style="width:105px;"> &nbsp;Valid Until: <?php echo $certificate_data['date_valid_until']; ?></td></tr>
            <tr><td class="rn">2</td><td class="rl">Company name(s) reviewed:</td><td><input type="text" name="company_name" id="company_name" data-required="yes" style="width:96%" value="<?php echo htmlspecialchars($val['company_name']); ?>"></td></tr>
            <tr><td class="rn">3</td><td class="rl">Client ID:</td><td><?php echo $the_client['client_id']; ?></td></tr>
            <tr><td class="rn">4</td><td class="rl">Production sites:</td><td><?php echo $production_sites_html; ?></td></tr>
            <tr><td class="rn">5</td><td class="rl">Scope:</td><td><?php echo $certificate_data['scope']; ?></td></tr>
            <tr><td class="rn">6</td><td class="rl">Category:</td><td><?php echo $certificate_data['category']; ?></td></tr>
            <tr><td class="rn">7</td><td class="rl">Reference Standard:</td><td><?php echo $certificate_data['reference_standard']; ?></td></tr>
            <tr><td class="rn">8</td><td class="rl">Certificate validity:</td><td><?php echo $certificate_data['certificate']; ?></td></tr>
        </table>

        <h3 class="dmc-sec">Section 1: Objective</h3>
        <table class="tbl-radio">
            <tr><td><strong>Objective of the Decision-Making Committee Report</strong></td></tr>
            <tr><td><label><input type="radio" name="objective" value="Granting" data-required="yes" <?php echo ($val['objective'] == 'Granting') ? 'checked' : ''; ?>> Granting Certification [New Customers]</label></td></tr>
            <tr><td><label><input type="radio" name="objective" value="Renewing or Extending" <?php echo ($val['objective'] == 'Renewing or Extending') ? 'checked' : ''; ?>> Renewing or Extending Certification [Existing customers]</label></td></tr>
            <tr><td><label><input type="radio" name="objective" value="Expanding" <?php echo ($val['objective'] == 'Expanding') ? 'checked' : ''; ?>> Expanding Certification</label></td></tr>
            <tr><td><label><input type="radio" name="objective" value="Suspending" <?php echo ($val['objective'] == 'Suspending') ? 'checked' : ''; ?>> Suspending Certification</label></td></tr>
            <tr><td><label><input type="radio" name="objective" value="Restoring" <?php echo ($val['objective'] == 'Restoring') ? 'checked' : ''; ?>> Restoring Certification</label></td></tr>
            <tr><td><label><input type="radio" name="objective" value="Withdrawing" <?php echo ($val['objective'] == 'Withdrawing') ? 'checked' : ''; ?>> Withdrawing Certification</label></td></tr>
            <tr><td><label><input type="radio" name="objective" value="Others" <?php echo ($val['objective'] == 'Others') ? 'checked' : ''; ?>> Others: <input type="text" name="objective_others_info" id="ObjectiveOthersInfo" style="width:70%;" value="<?php echo htmlspecialchars($val['objective_others_info']); ?>"></label></td></tr>
        </table>

        <h3 class="dmc-sec">Section 2: Review</h3>
        <table class="tbl-rev">
            <thead><tr><th><label><input type="checkbox" onclick="checkUncheckAll(this)"> To be Reviewed Documents</label></th><th colspan="3" style="text-align:center;">Results</th></tr></thead>
            <tbody>
            <?php
            $review_items = array(
                array('TyOfCe', 'reviewed', 'TyofCe', 'Type of Certificate', array('Annual', 'Shipment', 'Not appl')),
                array('ApFoanRe', 'reviewed', 'ApFoanRe', 'Application Form and Review', array('Accepted', 'In Process', 'Refused')),
                array('contracts', 'reviewed', 'contracts', 'Contracts', array('Accepted', 'In Process', 'Refused')),
                array('PrAuAc', 'reviewed', 'PrAuAc', 'Pre-Audit Activities', array('Accepted', 'In Process', 'Refused')),
                array('AuPl', 'results', 'AuPl', 'Audit Planning', array('Accepted', 'In Process', 'Refused')),
                array('NoCoRe', 'reviewd', 'NoCoRe', 'Non-Conformance Reporting', array('Accepted', 'In Process', 'Refused')),
                array('CoAcAnEv', 'reviewed', 'CoAcAnEv', 'Corrective Actions and Evidence', array('Accepted', 'In Process', 'Refused')),
                array('Invoicing', 'reviewed', 'Invoicing', 'Invoicing', array('Accepted', 'In Process', 'Refused')),
                array('PlOfNeAu', 'reviewed', 'PlOfNeAu', 'Planning of Next Audits', array('Accepted', 'In Process', 'Refused')),
            );
            foreach ($review_items as $item) {
                $doc_key = $item[0]; $section = $item[1]; $result_key = $item[2]; $label = $item[3]; $result_options = $item[4];
                $doc_name = $section . '[' . $doc_key . '][document]';
                $result_name = $section . '[' . $result_key . '][results]';
                $doc_checked = is_reviewed_checked($reviewed, $section, $doc_key, 'document') ? 'checked' : '';
                $saved_result = get_reviewed_value($reviewed, $section, $result_key, 'results');
            ?>
            <tr>
                <td><label><input type="checkbox" name="<?php echo $doc_name; ?>" value="<?php echo $label; ?>" <?php echo $doc_checked; ?>> <?php echo $label; ?></label></td>
                <?php foreach ($result_options as $opt) { ?>
                <td><label><input type="radio" name="<?php echo $result_name; ?>" value="<?php echo $opt; ?>" <?php echo ($saved_result == $opt) ? 'checked' : ''; ?>> <?php echo $opt; ?></label></td>
                <?php } ?>
            </tr>
            <?php } ?>
            </tbody>
        </table>

        <h3 class="dmc-sec">Section 3: Conclusion</h3>
        <table class="tbl-radio">
            <tr><td><strong>Conclusion of the DMC Report</strong></td></tr>
            <tr><td><label><input type="radio" name="conclusion" value="The Halal Certificate may be granted" data-required="yes" <?php echo ($val['conclusion'] == 'The Halal Certificate may be granted') ? 'checked' : ''; ?>> The Halal Certificate may be granted</label></td></tr>
            <tr><td><label><input type="radio" name="conclusion" value="The Halal Certificate may be expanded [adding of more certified products]" <?php echo ($val['conclusion'] == 'The Halal Certificate may be expanded [adding of more certified products]') ? 'checked' : ''; ?>> The Halal Certificate may be expanded [adding of more certified products]</label></td></tr>
            <tr><td><label><input type="radio" name="conclusion" value="The Halal Certificate may be renewed or temporarily extended [max 3 months]" <?php echo ($val['conclusion'] == 'The Halal Certificate may be renewed or temporarily extended [max 3 months]') ? 'checked' : ''; ?>> The Halal Certificate may be renewed or temporarily extended [max 3 months]</label></td></tr>
            <tr><td><label><input type="radio" name="conclusion" value="The Halal Certificate is suspended" <?php echo ($val['conclusion'] == 'The Halal Certificate is suspended') ? 'checked' : ''; ?>> The Halal Certificate is suspended [time period of suspension: <input type="text" name="conclution_time_period_of_suspension" id="ConclutionTimePeriodOfSuspension" style="width:140px;" value="<?php echo htmlspecialchars($val['suspension_period']); ?>">]</label></td></tr>
            <tr><td><label><input type="radio" name="conclusion" value="The Halal Certificate shall be withdrawn" <?php echo ($val['conclusion'] == 'The Halal Certificate shall be withdrawn') ? 'checked' : ''; ?>> The Halal Certificate shall be withdrawn from this date <input type="text" class="date" name="conclusion_withdrawn_from_this_date" id="ConclusionWithdrawnFromThisDate" style="width:105px;" value="<?php echo htmlspecialchars($val['withdrawn_date']); ?>"></label></td></tr>
            <tr><td><label><input type="radio" name="conclusion" value="Others" <?php echo ($val['conclusion'] == 'Others') ? 'checked' : ''; ?>> Others: <input type="text" name="conclusion_other_info" id="ConclusionOtherInfo" style="width:50%;" value="<?php echo htmlspecialchars($val['conclusion_other']); ?>"></label></td></tr>
        </table>

        <table class="tbl-rem">
            <tr><td><strong>Remarks on the DMC Report</strong></td></tr>
            <tr><td><textarea name="remarks_on_the_dmc_report" id="RemarksOnTheDmcReport" class="auto-height"><?php echo htmlspecialchars($val['remarks']); ?></textarea></td></tr>
        </table>

        <h3 class="dmc-sec">Section 4: Undersigning and Stamping by DMC Members</h3>
        <table class="tbl-mem">
            <thead><tr>
                <th>Shariah Board Members <span class="sub">(At least 2 Members)</span></th>
                <th>Auditor Board Members <span class="sub">(At least 1 Member)</span></th>
                <th>Management Board Members</th>
            </tr></thead>
            <tbody><tr>
                <td><ul><?php foreach ($members['SBM'] as $m) { $ck = in_array($m['comemid'], $saved_comemids) ? 'checked' : ''; ?>
                    <li><label><input type="checkbox" class="shariah" name="comemids[]" value="<?php echo $m['comemid']; ?>" <?php echo $ck; ?>> <?php echo htmlspecialchars($m['member_name']); ?></label></li>
                <?php } ?></ul></td>
                <td><ul><?php foreach ($members['ABM'] as $m) { $ck = in_array($m['comemid'], $saved_comemids) ? 'checked' : ''; ?>
                    <li><label><input type="checkbox" class="auditors" name="comemids[]" value="<?php echo $m['comemid']; ?>" <?php echo $ck; ?>> <?php echo htmlspecialchars($m['member_name']); ?></label></li>
                <?php } ?></ul></td>
                <td><ul><?php foreach ($members['MBM'] as $m) { $ck = in_array($m['comemid'], $saved_comemids) ? 'checked' : ''; ?>
                    <li><label><input type="checkbox" class="management" name="comemids[]" value="<?php echo $m['comemid']; ?>" <?php echo $ck; ?>> <?php echo htmlspecialchars($m['member_name']); ?></label></li>
                <?php } ?></ul></td>
            </tr></tbody>
        </table>

        <div class="dmc-info">
            <p style="margin:8px 0 2px;">Were any of the above mentioned signatories involved in the audit? *
                <select name="signatories_involved_in_the_audit" id="signatories_involved_in_the_audit" data-required="yes">
                    <option value="">Please Select</option>
                    <option value="Yes" <?php echo ($val['signatories_in_audit'] == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                    <option value="No" <?php echo ($val['signatories_in_audit'] == 'No') ? 'selected' : ''; ?>>No</option>
                </select>
            </p>
            <span class="hint">(Please note that the signatories are not allowed to be involved in the audit)</span>
            <p style="margin:10px 0 4px;"><strong>DMR Reference:</strong> <input type="text" class="dmr-input" name="dmr_reference" id="dmr_reference" placeholder="DMR Reference" value="<?php echo htmlspecialchars($val['dmr_reference']); ?>"></p>
        </div>

        <div class="dmc-stamp">
            <strong>Stamp:</strong><br>
            <img src="/data/offices/0/images/stempel.png" alt="stempel" width="140" style="margin-top:4px;">
        </div>

        <div class="dmc-info">
            <strong>Branch:</strong> <?php echo htmlspecialchars($office['company_name_english']); ?><br>
            <strong>Branch Manager:</strong> <?php echo htmlspecialchars($BM['member_name']); ?><br>
            <strong>Requested By:</strong> <?php echo htmlspecialchars($contact_person); ?>
        </div>

        <div class="dmc-agree">
            <label>
                <input type="checkbox" name="agree" id="agreeOnApplication" value="1" <?php echo $val['agreed'] ? 'checked' : ''; ?>>
                <span>Hereby, I <strong>(<?php echo htmlspecialchars($contact_person); ?>)</strong> confirm that all the information provided in this report is accurate, and the committee members have reviewed, signed, and approved the content of the report.</span>
            </label>
        </div>

        <div class="dmc-copy">&copy; This document is the sole property of Halal Quality Control. The usage is only permitted by invitation and to be sent by a reliable source. All rights reserved.</div>

        <div class="dmc-submit">
            <input type="password" autocomplete="off" name="dmc_password" id="dmc_password" placeholder="Enter your password to create the report">
            <input type="submit" class="btn-go" id="appSaveButton" value="<?php echo $is_edit ? 'Update DMC Report' : 'Create DMC Report'; ?>">
            <button type="reset" class="btn-rst">Reset</button>
            <span class="sub-note">Note: A password is required to generate the report. You can find the password in your account profile.</span>
        </div>
    </form>

</div>
</div>

<script>
jQuery(document).ready(function() {
    jQuery("#DateOfDmcr, #ConclusionWithdrawnFromThisDate, .date").datepicker({
        changeMonth: true, changeYear: true,
        dateFormat: typeof dateFormat !== 'undefined' ? dateFormat : 'dd/mm/yy'
    });
    <?php if (!$is_edit) { ?>
    jQuery("input[name='reviewed[TyOfCe][document]']").prop('checked', true);
    jQuery("input[name='reviewed[TyofCe][results]'][value='Annual']").prop('checked', true);
    jQuery("input[name='conclusion'][value='The Halal Certificate may be granted']").prop('checked', true);
    <?php } ?>
});
</script>