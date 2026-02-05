<?php
if (!defined("__HQC__"))
    define('__HQC__', 1);

//show php errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_GET['act'] = 'add';
?>
<style>
    ul,
    ol {
        padding: 0px;
        margin: 0px;
    }

    table {
        width: 100%;
    }

    th,
    td {
        vertical-align: top;
    }

    ol#productionSites {
        max-height: 200px;
        overflow: auto;
    }

    .th {
        width: 20px;
        vertical-align: middle;
    }

    .company_data td {
        vertical-align: middle;
    }
</style>
<script>
    $("#page_title").html("Decision Making Report");

    function showCodeLi(id, memid) {
        jQuery("#mem_" + memid).find('li').css('display', 'none')
        jQuery("#mem_" + memid).find("#" + id).css('display', 'block');
    }

    function checkTheForm() {
        //turn date_of_dmcr to timestamp
        var DMCRDate = jQuery("#DateOfDmcr").val();
        DMCRDate = DMCRDate.split('/');
        DMCRDate = DMCRDate[2] + '-' + DMCRDate[1] + '-' + DMCRDate[0];
        DMCRDate = new Date(DMCRDate);
        DMCRDate = DMCRDate.getTime();

        if (DMCRDate - DMCDate < 0) {
            alert_message('Date of DMC Report must be greater or the same Date as DMC meeting date');
            return false;
        }

        //go through all the checkboxes of trReviewed tr and check if they are not checked
        if (jQuery("#trReviewed input[type=checkbox]:checked").length < 8) {
            alert_message('Please review all the documents of the report');
            return false;
        }

        if (jQuery("#trReviewed input[type=radio]:checked").length < 8) {
            alert_message('Please reviewed documents <span style="color:red">results</span> are required');
            return false;
        }

        //count how many of .shariah are checked
        if (jQuery(".shariah:checked").length < 2) {
            alert_message('Please select at least 2 Shariah Board Members');
            return false;
        }
        //count how many of .auditors are checked
        if (jQuery(".auditors:checked").length == 0) {
            alert_message('Please select at least one Auditor');
            return false;
        }

        // count how many of .management are checked
        // if (jQuery(".management:checked").length == 0) {
        //     alert_message('Please select at least one Management board member');
        //     return false;
        // }
        jQuery(".management[value=1]").prop('checked', true);
        if (jQuery("#signatories_involved_in_the_audit").val() == '') {
            alert_message('Please answer the question:<br/>Were the signatories involved in the audit?');
            return false;
        }

        if (jQuery("#signatories_involved_in_the_audit").val() == 'Yes') {
            //please select another another DMC audit member
            alert_message('Please select another Member not involved in the audit');
            return false;
        }

        //check if #Decision Making Report is checked, if not he must confirm the report
        if (!jQuery("#agreeOnApplication").is(':checked')) {
            alert_message('Please confirm that you have reviewed the content of the report');
            return false;
        }

        //check the password if it is empty and if it = decoded base64
        var password = jQuery("#dmc_password").val().trim();
        if (password == '') {
            alert_message('Please enter your password to generate the report');
            return false;
        }

        if (password != mem_password) {
            alert_message('The password is incorrect');
            return false;
        }
        return true;
    }

    function checkUncheckAll(obj) {
        jQuery(obj).closest('table').find('input[type=checkbox]').prop('checked', jQuery(obj).is(':checked'));
    }

    function searchProductionSites(value) {
        jQuery("#productionSites li").each(function() {
            if (jQuery(this).text().toLowerCase().indexOf(value.toLowerCase()) >= 0) {
                jQuery(this).show();
            } else {
                jQuery(this).hide();
            }
        });
    }

    function selectProductionSites(obj) {
        jQuery('#productionSites').find('input[type=checkbox]').prop('checked', jQuery(obj).is(':checked'));
    }
</script>

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

if (isset($_GET['decid'])) {
    $dmc_data = $amdb->get_row("SELECT * FROM hqc_committee_decision WHERE decid ='$_REQUEST[decid]'");
    if (!$dmc_data) {
        return;
    }
    $_GET['clid'] = $dmc_data['clid'];
    if (!isset($_REQUEST['crtNr'])) {
        $_REQUEST['clid'] = $dmc_data['clid'];
        $_REQUEST['crtNr'] = $dmc_data['crtNr'];
        $_REQUEST['offid'] = $dmc_data['offid'];
    }
    $decision = unserialize($dmc_data['decision']);
}
$production_sites = '';
if ($sites = $amdb->get_results("SELECT * FROM companies_production_sites WHERE status!='deleted' AND clid='$_GET[clid]'")) {

    foreach ($sites as $site) {
        $production_sites .= '<li><label><input type="checkbox" name="stids[]" value="' . $site['stid'] . '">' . $site['site_name'] . '</label></li>';
    }
}
if (trim($production_sites) != '')
    $production_sites = '<div><input type="text" onkeyup="searchProductionSites(this.value)" placeholder="Search production site"><label><input type="checkbox" onclick="selectProductionSites(this);" name="selectAllSites">Select all sites</label><br/><ol style="margin-top:10px;" id="productionSites">' . $production_sites . '</ol></div>';

$certificate_data = array();
$committee_members['DDMC_members'] = '';
if (isset($_REQUEST['crtNr'])) {
    if ($certificate = $amdb->get_row("SELECT * FROM acms_halal_certificates
WHERE crtNr='" . $_REQUEST['crtNr'] . "'"));
} else {
    return;
}

$options = json_decode($certificate['options'], true);
$certificate_validity = array();
if ($options['cert_validity'] != 1)
    $certificate_validity[] = 'Valid for: ' . $options['cert_validity'] . ' years';
$certificate_validity[] = 'Expiry date: ' . date("d/m/Y", $certificate['date_of_expiry']);
if (isset($options['recertification']) and is_array($options['recertification']) && $options['cert_validity'] != 1)
    $certificate_validity[] = 'Recertification date: ' . $options['recertification'][0];

$certificate_data['certificate'] = implode('<br/>', $certificate_validity);
$certificate_data['date_of_dmcr'] = date('d/m/Y', $certificate['ordered_on']);
if (isset($decision) and is_array($decision)) {
    foreach ($decision as $key => $value) {
        if ($key != 'comemids')
            $certificate_data[$key] = $value;
    }
} else {
    $certificate_data['objective'] = 'Granting';
}
$certificate_data['scope'] = $certificate['scope_of_certification'];
$certificate_data['category'] = $certificate['category'];
$certificate_data['reference_standard'] = '';
if (is_array(json_decode($certificate['category'], true))) {
    $certificate_data['category'] = '<ol>';
    $categories = implode(',', json_decode($certificate['category'], true));

    if ($certificate_categories = $amdb->get_results("SELECT * FROM hqc_categories WHERE catid IN ($categories)")) {
        foreach ($certificate_categories as $category) {
            $certificate_data['category'] .= '<li>' . $category['category'] . ' - ' . $category['category_name'] . '</li>';
        }
    }
    $certificate_data['category'] .= '</ol>';
}
if (is_array(json_decode($certificate['reference_standards'], true))) {
    $certificate_data['reference_standard'] = '<ol>';
    $reference_standards = implode(',', json_decode($certificate['reference_standards'], true));
    $amdb->connect_portal();
    if ($certificate_reference_standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE stnid IN ($reference_standards)")) {
        foreach ($certificate_reference_standards as $reference_standard) {
            $certificate_data['reference_standard'] .= '<li>' . $reference_standard['code'] . ' - ' . $reference_standard['description'] . '</li>';
        }
    }
    $amdb->close_portal();
    $certificate_data['reference_standard'] .= '</ol>';
}

if (!isset($dmc_data) && !$dmc_data = $amdb->get_row("SELECT comemids,decid,meeting_date FROM hqc_committee_decision WHERE clid ='$_REQUEST[clid]' AND status = 'pending'")) {
    return;
}

$Auditor = 'Auditor Board Member';
$Shariah = 'Shariah Board Member';
$members = array();
$functions = array(
    'ABM' => 'Auditor Board Member',
    'MBM' => 'Management Board Member',
    'SBM' => 'Shariah Board Member',
);

if ($_SESSION['offid'] == 0) {
    $uid = $_SESSION['user']['uid'];
} elseif (isset($_SESSION['user']) && isset($_SESSION['user']['offuid'])) {
    $uid = $_SESSION['user']['offuid'];
} else {
    $uid = $_SESSION['offid'];
}
$offid = $_SESSION['offid'];
$BM = array();
if ($commebersAll = $amdb->get_results("SELECT comemid,uid,offid,bm,member_name,member_function,member_offices,password FROM hqc_committee_members order by member_name ASC")) {

    foreach ($commebersAll as $member) {
        if ($offid == $member['offid'] && $uid == $member['uid']) {
            $contact_person = $member['member_name'];
        }

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

    $member_title = '<tr><th style="width:33%">' . $functions['SBM'] . 's <span style="font-weight:normal">(At least 2 Members)</span></th>' . '<th style="width:33%">' . $functions['ABM'] . 's <span style="font-weight:normal">(At least 1 Member)</span></th><th>' . $functions['MBM'] . 's</th></tr>';
    $member_footer = '<tr id="comemSignatures"><td style="vertical-align:top !important"><ul class="table table-striped table-bordered" style="padding:0px">';
    foreach ($members['SBM'] as $member) {
        $member_footer .= '<li><label><input type="checkbox" class="shariah" name="comemids[]" value="' . $member['comemid'] . '"/>' . $member['member_name'] . '</label></li>';
    }
    $member_footer .= '</ul></td>';

    $member_footer .= '<td style="vertical-align:top !important"><ul class="table table-striped table-bordered" style="padding:0px">';
    foreach ($members['ABM'] as $member) {
        $member_footer .= '<li><label><input type="checkbox" class="auditors" name="comemids[]" value="' . $member['comemid'] . '"/>' . $member['member_name'] . '</label></li>';
    }
    $member_footer .= '</ul></td>';
    $member_footer .= '<td style="vertical-align:top !important"><ul class="table table-striped table-bordered" style="padding:0px">';
    foreach ($members['MBM'] as $member) {
        $member_footer .= '<li><label><input type="checkbox" class="management" name="comemids[]" value="' . $member['comemid'] . '"/>' . $member['member_name'] . '</label></li>';
    }
    $member_footer .= '</ul></td>';
    $member_footer .= '</tr>';
    $committee_members['DDMC_members'] = '<table>' . $member_title . $member_footer . '</table>';
}

if (!isset($contact_person)) {
    if (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) {
        $contact_person = $_SESSION['user']['username_owner'];
    } elseif (isset($_SESSION['user']) && isset($_SESSION['user']['offuid'])) {
        $contact_person = $_SESSION['user']['name'];
    } else {
        $office = get_office_data($_SESSION['offid']);
        $contact_person = $office['contact_person'];
    }
}

include "../forms.class.php";

if ($theForm = file_get_contents("templates/dmc.tmpl.php")) {
    $data['theForm']['the_form'] = $theForm;
    $the_client = get_client($_REQUEST['clid']);
    $the_client['client_id'] = '<span style="cursor:pointer" data-id="' . $the_client['client_id'] . '" class="com com_' . $the_client['clid'] . ' clid load_popup" data-url="../../admin/load_company.php?clid=' . $the_client['clid'] . '" title="' . $the_client['company_name'] . '">' . $the_client['client_id'] . '</span>';
    $data = $data + $the_client + $certificate_data + $committee_members;
    if (trim($production_sites) != '')
        $data['production_sites'] = $production_sites;
    else
        $data['production_sites'] = 'NA';
?>
    <script>
        var mem_password = '<?php echo $mem_password; ?>';
    </script>
    <div style="text-align:center">
        <span style="color:brown">Note: A password is required to generate the report. You can find the password in your account profile.<br />
            Close this window, open new window by clicking on <strong>new window</strong> on the Menubar and under DMC on my account</span>
    </div>
    <form name="appForm" id="appForm" style="width:980px;margin:0 auto" action="dmc_save.php" target="" method="post" onsubmit="return checkTheForm()" target="" autocomplete="off">
        <input type="hidden" name="clid" value="<?php echo $_REQUEST['clid']; ?>" />
        <input type="hidden" name="crtNr" value="<?php echo $_REQUEST['crtNr']; ?>" />
        <input type="hidden" name="offid" value="<?php echo $_SESSION['offid']; ?>" />
        <input type="hidden" name="decid" value="<?php echo $dmc_data['decid']; ?>" />
        <input type="hidden" name="ref" value="<?php echo $_GET['ref']; ?>" />
        <input type="hidden" name="uid" value="<?php echo $uid; ?>" />
        <input type="hidden" name="comemid" id="comemid" value="<?php echo $comemid; ?>" />
        <input type="hidden" name="bm" value="<?php echo $BM['comemid']; ?>" />
        <input type="hidden" name="act" value="save" />
        <input type="hidden" name="branch[Branch]" value="<?php echo $office['company_name_english']; ?>" />
        <input type="hidden" name="branch[BranchManager]" value="<?php echo $office['contact_person']; ?>" />
        <input type="hidden" name="branch[RequestedBy]" value="<?php echo isset($contact_person) ? $contact_person : 'N/A'; ?>" />
        <input type="hidden" name="branch[RequestDate]" value="<?php echo date('d/m/Y'); ?>" />
        <input type="hidden" name="pass" value="<?php echo $mem_password; ?>" />
        <?php
        $data['Branch'] = $office['company_name_english'];
        $data['BranchManager'] = $BM['member_name'];
        $data['RequestedBy'] = $contact_person;
        $data['RequestDate'] = date('d/m/Y', strtotime($dmc_data['meeting_date']));
        $data['userSignature'] = '';
        $data['valid_until'] = $certificate['date_of_expiry'];
        $data['date_valid_until'] = date("d/m/Y", $certificate['date_of_expiry']);
        echo  $amform->get_form(7, $data, 'html');
        ?>
        <div style="margin-top:20px; text-align:center;">
            <input type="password" autocomplete="off" name="dmc_password" id="dmc_password" placeholder="Enter your password to Create the report" value="" />
            <input type="submit" class="btn btn-primary" id="appSaveButton" value="Create DMC report" /><button type="reset" class="btn btn-default">Reset</button><br />
            <span style="color:brown">Note: A password is required to generate the report. You can find the password in your account profile.</span>
        </div>
    </form>
<?php
} ?>
<script>
    var DMCDate = '<?php echo date("Y-m-d", strtotime($dmc_data['meeting_date'])); ?>';
    DMCDate = new Date(DMCDate).getTime();
    var meetingDate = '<?php echo date("d/m/Y", strtotime($dmc_data['meeting_date'])); ?>';
    jQuery(document).ready(function() {
        var comemids = '<?php echo $dmc_data['comemids']; ?>';
        if (comemids != '') {
            var comemids = comemids.split(',');
            jQuery.each(comemids, function(index, value) {
                jQuery("input[name='comemids[]'][value='" + value + "']").prop('checked', true);
            });
        }
        jQuery("#ReviewedTyOfCeDocument,.TyofCeAnnual,.conclusionAgree").prop('checked', true);
        jQuery("#productionSites").find('input[type=checkbox]').prop('checked', false);
    });
</script>