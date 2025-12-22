<?php
//show php errors
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// if (!isset($_REQUEST['decid'])) {
//     exit();
// }
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

$_POST['comemids'] = implode(',', $_POST['comemids']);
$certificate_data = array();
$committee_members['DDMC_members'] = '';
if (isset($_REQUEST['crtNr'])) {
    if ($certificate = $amdb->get_row("SELECT * FROM acms_halal_certificates WHERE crtNr='$_REQUEST[crtNr]' AND clid='$_REQUEST[clid]' "));
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
$certificate_data['scope'] = $certificate['scope_of_certification'];
$certificate_data['category'] = '';
$certificate_data['reference_standard'] = '';
if (is_array(json_decode($certificate['category'], true))) {
    $certificate_data['category'] = array();
    $catNr = 1;
    $categories = implode(',', json_decode($certificate['category'], true));

    if ($certificate_categories = $amdb->get_results("SELECT * FROM hqc_categories WHERE catid IN ($categories)")) {
        foreach ($certificate_categories as $category) {
            $certificate_data['category'][] = $catNr++ . '- ' . $category['category'] . ' - ' . $category['category_name'];
        }
    }
    $certificate_data['category'] = implode('<br/>', $certificate_data['category']);
    if ($products = $amdb->get_results("SELECT * FROM acms_hdcs_products WHERE prdid IN ($certificate[products])")) {
        $certificateProducts = array();
        foreach ($products as $product) {
            $certificateProducts[] = $product['product_name'];
        }
        $certificate_data['Products'] = '<H3>PRODUCTS</H3><ol style="margin-left:40px"><li>' . implode('</li><li>', $certificateProducts) . '</li></ol>';
    } else {
        $certificate_data['Products'] = '';
    }
}

// return;
if (is_array(json_decode($certificate['reference_standards'], true))) {
    $certificate_data['reference_standard'] = array();
    $refNr = 1;
    $reference_standards = implode(',', json_decode($certificate['reference_standards'], true));

    if ($certificate_reference_standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE stnid IN ($reference_standards)")) {
        foreach ($certificate_reference_standards as $reference_standard) {
            $certificate_data['reference_standard'][] = $refNr++ . '- ' . $reference_standard['code'] . ' - ' . $reference_standard['description'];
        }
    }
    $certificate_data['reference_standard'] = implode('<br/>', $certificate_data['reference_standard']);
}

$functions = array(
    'ABM' => 'Auditor Board Member',
    'MBM' => 'Management Board Member',
    'SBM' => 'Shariah Board Member',
);

if ($commebers = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE comemid IN ($_POST[comemids]) order by member_name ASC")) {

    $member_title = '<tr>';
    $member_footer = '<tr>';
    foreach ($commebers as $member) {
        $user_signature = get_user_signature($member['comemid']);
        $member_title .= '<th>' . $member['member_name'] . '<br/>' . (isset($functions[$member['member_function']]) ? $functions[$member['member_function']] : $member['member_function']) . '</th>';
        $member_footer .= '<td id="mem_' . $member['comemid'] . '">';
        if ($user_signature != '') {
            $member_footer .= '<img src="' . $user_signature . '" width="200px" height="100px" />';
        }
        $member_footer .= '</td>';
    };
    $member_title .= '</tr>';
    $member_footer .= '</tr>';
    $committee_members['DDMC_members'] = '<table>' . $member_title . $member_footer . '</table>';
}

foreach ($_POST['branch'] as $key => $value) {
    $_POST[$key] = $value;
}

$_POST['userSignature'] = get_user_signature($_POST['bm']);
if (trim($_POST['userSignature']) != '') {
    $_POST['userSignature'] = '<img src="' . $_POST['userSignature'] . '"/>';
}

include "../forms.class.php";

$amdb->connect_portal();
if ($theForm = $amdb->get_row("SELECT * FROM hqc_forms where foid='7'")) {
    $data['theForm'] = $theForm;
    $amdb->close_portal();
    $the_client = get_client($_REQUEST['clid']);
    $data = $data + $_POST + $the_client + $certificate_data + $committee_members;
    $amform->view_form(7, $data, 'pdf', $dmc_file);
}
