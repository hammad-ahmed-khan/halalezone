<?php
if (!isset($_REQUEST['decid'])) {
    exit();
}
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

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

$certificate_data = array();
$committee_members['DDMC_members'] = '';
if ($certificate = $amdb->get_row("SELECT * FROM acms_halal_certificates
JOIN hqc_committee_decision ON hqc_committee_decision.crtNr = acms_halal_certificates.crtNr
WHERE acms_halal_certificates.crtNr='" . $_REQUEST['crtNr'] . "' AND acms_halal_certificates.clid='" . $_REQUEST['clid'] . "' ")) {
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
        $certificate_data['category'] = implode('<br/>', $certificate_data['category'] );
    }
    if (is_array(json_decode($certificate['reference_standards'], true))) {
        $certificate_data['reference_standard'] = array();
        $refNr =1;
        $reference_standards = implode(',', json_decode($certificate['reference_standards'], true));

        if ($certificate_reference_standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE stnid IN ($reference_standards)")) {
            foreach ($certificate_reference_standards as $reference_standard) {
                $certificate_data['reference_standard'][] = $refNr++ . '- ' . $reference_standard['code'] . ' - ' . $reference_standard['description'];
            }
        }
        $certificate_data['reference_standard'] = implode('<br/>', $certificate_data['reference_standard'] );
    }
    $sms_codes = json_decode($certificate['sms_codes'], true);
    if ($commebers = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE comemid IN ($certificate[comemids]) ")) {
        $member_title = '<tr>';
        $member_footer = '<tr>';
        foreach ($commebers as $member) {
            $user_signature = get_user_signature($member['comemid']);
            $sms_data = $sms_codes[$member['comemid']];
            $member_title .= '<th>' . $member['member_name'] . '<br/>' . $member['member_function'] . '</th>';
            $member_footer .= '<td id="mem_' . $member['comemid'] . '">';
            if ($user_signature != '' && isset($sms_data['approved'])) {
                $member_footer .= '<img src="' . $user_signature . '" width="200px" height="100px" />';
            } else { }
            $member_footer .= '</td>';
        };
        $member_title .= '</tr>';
        $member_footer .= '</tr>';
        $committee_members['DDMC_members'] = '<table>' . $member_title . $member_footer . '</table>';
    }
}



include "forms.class.php";
$amdb->connect_portal();
$_SESSION['offid'] = 0;

if ($theForm = $amdb->get_row("SELECT * FROM hqc_forms where foid='7' ")) {
    $data['theForm'] = $theForm;
    $amdb->close_portal();
    $the_client = get_client($_REQUEST['clid']);
    $data = $data + $the_client + $certificate_data + $committee_members;
    if (is_array(unserialize($certificate['decision']))) {
        $data = $data + unserialize($certificate['decision']);
    }

    echo  $amform->view_form(7, $data, 'pdf');
}
?>