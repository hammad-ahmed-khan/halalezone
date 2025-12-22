<?php
//TODO: update new system

use PhpOffice\PhpSpreadsheet\Shared\OLE\PPS\File;

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include "../../config/paths.inc.php";
if (!defined("__HQC__") and !defined("_HQC_")) {
    exit();
};
$tempDir = $prog_path . "/data/temp";
if (!is_dir($tempDir))
    mkdir($tempDir, true);

if (defined("__client__")) {
    $passed_data = $_POST;
    unset($_POST);
    if (isset($passed_data['crtNr']))
        $_REQUEST['crtnr'] = $passed_data['crtNr'];
}

if (isset($_REQUEST['crtnr'])) {
    if ($content = $amdb->get_row("SELECT * FROM hqc_versions WHERE item_table='acms_halal_certificates' AND item_id='$_REQUEST[crtnr]'")) {

        $_POST = unserialize($content['item_content']);
        $certificateOrg = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates WHERE crtNr='$_REQUEST[crtnr]'");
        if ($_POST['office_address'] != $certificateOrg['office_address'])
            $_POST['office_address'] = $certificateOrg['office_address'];
    }

    if (!isset($_POST) or count($_POST) == 0) {
        $_POST = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates WHERE crtNr='$_REQUEST[crtnr]'");
    }

    foreach ($_POST as $itemKey => $itemValue) {
        if (!is_array($itemValue) && is_array(json_decode($itemValue, true))) {
            $_POST[$itemKey] = json_decode($itemValue, true);
        }
    };

    if (isset($_POST['options']['approval_required']) && $_POST['options']['approval_required'] == 'yes') {
        $_POST['office_address'] = 0;
    }

    $_POST['certificate_option'] = $_POST['options'];
    if (isset($_POST['options']['approval_required']) && isset($_POST['options']['approved']))
        $_POST['certificate_option']['digital'] = 'yes';

    if (!isset($_REQUEST['crtDo']))
        $_POST['crtDo'] = '';
    else
        $_POST['crtDo'] = $_REQUEST['crtDo'];
    //	if (isset($_GET['fp']))
    //	$_POST['crtDo'] = 'view_first_page';
    $_REQUEST = $_POST;
}

if (isset($_GET['fp']) && isset($_REQUEST['certificate_nr']) && $_REQUEST['certificate_nr'] != '0') {
    $_REQUEST['crtDo'] = 'view_first_page';
    $head_foot_options['type'] = 'digital';
}

if (isset($passed_data)) {
    if ($_REQUEST['crtDo'] == 'print') {
        include __DIR__ . '/versions.inc.php';
        save_version($passed_data['crtNr']);
        $pData = array();
        if (isset($passed_data['certificate_option']))
            $pData['options'] = json_encode($passed_data['certificate_option']);
        if (isset($passed_data['annex_options']))
            $pData['annex_options'] = json_encode($passed_data['annex_options']);
        $pData['products'] = $passed_data['products'];
        $amdb->update('acms_halal_certificates', $pData, "crtNr='$passed_data[crtNr]'");
    }
    foreach ($passed_data as $passKey => $passValue) {
        $_REQUEST[$passKey] = $passValue;
        $_POST[$passKey] = $passValue;
    }
}

extract($_REQUEST);

$templatePHP = array();
$templatePHP['firstPageTopMargin'] = 70;
$tempFile = $tempDir . "/annual.php";
$theTemplate = '';
$annexPageOnly = false;
$annexSepareted = false;
$certificate_content = array();
if (!isset($_POST['office_address']) && isset($_POST['offid']))
    $_POST['office_address'] = $_POST['offid'];
if (isset($_POST['tmplid']))
    $_POST['offid'] = $_POST['tmplid'];
$certificateTemplate = $prog_path . '/data/offices/templates/certificate-annual.pdf';
if (isset($_POST['offid'])) {
    $office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$_POST[offid]'");

    $template =  $amdb->get_row("SELECT * FROM office_certificate_templates WHERE offid='0' and type='annual'");
    $templateFile = $prog_path . '/data/offices/' . $_POST['office_address'] . '/templates/certificate-annual.pdf';

    if (file_exists($templateFile))
        $certificateTemplate = $templateFile;
    if (isset($template['content']))
        $theTemplate = $template['content'];
    if (isset($template['php']))
        $templatePHP = json_decode($template['php'], true);
    $template['style'] = $template['style'];
    $certificate_content['office'] = $office;
    if (is_local()) {
        //echo $templateFile;
    }
}

//$theTemplate = str_replace(array('<p>[pdf ', ')]</p>'), array('[pdf ', ')]'), $theTemplate);
$theTemplate = str_replace(array('<p>[pdf ', ')]</p>', '&nbsp;'), array('[pdf ', ')]', ' '), $theTemplate);


if (isset($_POST['certificate_option']['annexPages'])) {
    if ($_POST['certificate_option']['annexPages'] == 'annexPageOnly')
        $annexPageOnly = true;
    elseif ($_POST['certificate_option']['annexPages'] == 'annexSepareted') {
        $annexSepareted = true;
        if (isset($_POST['certificate_option']['annexSeparetedFirstPage']))
            $annexSeparetedFirstPage = $_POST['certificate_option']['annexSeparetedFirstPage'];
    }
    if (isset($_POST['certificate_option']) and ($annexPageOnly == true or $annexSepareted == true)) {
        $templateParts = explode('[annexPage]', $theTemplate);
        if ($annexPageOnly == true)
            $theTemplate = $templateParts[1];
        $theTemplateMargines = $templateParts[1];
        if (preg_match('/\[pdf (.*)\((.*)\)\]/', $theTemplateMargines, $pdfMatch)) {
            if (trim($pdfMatch[1]) == 'setMargins') {
                if (trim($pdfMatch[2]) != '') {
                    $margin = explode(',', $pdfMatch[2]);
                    if (isset($margin[1]))
                        $AutoPageBreak = $margin[1];
                }
            }
        }
    }
}

$option_styles = array();

if (isset($_POST['certificate_option']) and is_array($_POST['certificate_option'])) {

    if (isset($_POST['certificate_option']['lastPageRemarks']) && trim($_POST['certificate_option']['lastPageRemarks']) != '') {
        if (isset($_POST['certificate_option']['remarks']) && trim($_POST['certificate_option']['remarks']) == '')
            $_POST['certificate_option']['remarks'] = 'See Annex for details.';
        else
            $_POST['certificate_option']['remarks'] .= '<br/>See Annex for details.';
    }
    foreach ($_POST['certificate_option'] as $itemKey => $itemValue) {
        if (strstr(strtolower($itemKey), 'remarksstyle')) {
            $style = '<span style="">[remarksContent]<span>';

            if (isset($itemValue['color']) and trim($itemValue['color']) != 'black')
                $style = str_replace('style="', 'style="color:' . $itemValue['color'] . ';', $style);

            if (isset($itemValue['bold']))
                $style = str_replace('style="', 'style="font-weight:bold;', $style);

            if (isset($itemValue['italic']))
                $style = str_replace('[remarksContent]', '<i>[remarksContent]</i>', $style);

            if (trim($style) != '<span style="">[remarksContent]<span>')
                $option_styles[str_replace('Style', '', $itemKey)] = $style;
        }
    }

    foreach ($_POST['certificate_option'] as $itemKey => $itemValue) {
        if (strstr(strtolower($itemKey), 'remarks')) {
            if (isset($option_styles[$itemKey]))
                $itemValue = str_replace('[remarksContent]', $itemValue, $option_styles[$itemKey]);
            $_POST['certificate_option'][$itemKey] = str_replace("\n", "<br/>", $itemValue);
        }
    }
}

if ($contentInserts = parse_shortcode('input', $theTemplate)) {
    foreach ($contentInserts as $kInsert => $insert) {
        if (isset($insert['name'])) {
            $insertName = $insert['name'];
            if (isset($_POST['certificate_option']) and isset($_POST['certificate_option'][$insertName])) {
                if ($_POST['certificate_option'][$insertName] == 'insert-content')
                    $theTemplate = str_replace($insert['element'], $insert['content'], $theTemplate);
                else
                    $theTemplate = str_replace($insert['element'], $_POST['certificate_option'][$insertName], $theTemplate);
            } else {
                $theTemplate = str_replace($insert['element'], '', $theTemplate);
            }
        }
    };
}

$addPage = false;
require_once("$prog_path/pdf/tcpdf/hcp_pdf.inc.php");
$pages = $pdf->setSourceFile($certificateTemplate);
$footerY = -8;
$footerX = 0;
$pdf->SetMargins(25, 55, 25);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->setFontSubsetting(true);
$pdf->SetPrintHeader(true);
$head_foot_options['offid'] = $_POST['offid'];
$head_foot_options['act'] = $crtDo;
$head_foot_options['annex_only'] = $annexPageOnly;
$head_foot_options['annex_separate_pages'] = $annexSepareted;
if (isset($certificate_option['digital']))
    $head_foot_options['type'] = 'digital';
else
    $head_foot_options['type'] = 'print';
$head_foot_options['template'] = 'annual_certificate';
if ($crtDo == 'preview' or isset($certificate_option['digital'])) {
    $head_foot_options['page_template'] = '2,3,3';
    if ($annexPageOnly == true) {
        $head_foot_options['page_template'] = '3';
    } elseif (isset($annexSeparetedFirstPage)) {
        if ($annexSeparetedFirstPage == 'preceded')
            $head_foot_options['page_template'] = '2,3';
        elseif ($annexSeparetedFirstPage == 'major')
            $head_foot_options['page_template'] = '2,3,3';
        else
            $head_foot_options['page_template'] = '3';
    }
} else {
    if ($_POST['office_address'] == 0) {
        $head_foot_options['page_template'] = '1,3,3';
        if ($annexPageOnly == true) {
            $head_foot_options['page_template'] = '3';
        } elseif (isset($annexSeparetedFirstPage)) {
            if ($annexSeparetedFirstPage == 'preceded')
                $head_foot_options['page_template'] = '1,3';
            elseif ($annexSeparetedFirstPage == 'major')
                $head_foot_options['page_template'] = '1,3,3';
            else
                $head_foot_options['page_template'] = '3';
        }
    } else {
        $head_foot_options['page_template'] = '1,3,3';
        if ($annexPageOnly == true) {
            $head_foot_options['page_template'] = '3';
        } elseif (isset($annexSeparetedFirstPage)) {
            if ($annexSeparetedFirstPage == 'preceded')
                $head_foot_options['page_template'] = '1,3';
            elseif ($annexSeparetedFirstPage == 'major')
                $head_foot_options['page_template'] = '1,3,3';
            else
                $head_foot_options['page_template'] = '3';
        }
    }
}

if (isset($annexSeparetedFirstPage)) {
    if ($annexSeparetedFirstPage == 'normal') {
        $head_foot_options['page_number'] = '1';
    } elseif ($annexSeparetedFirstPage == 'preceded') {
        $head_foot_options['page_number'] = '1,2';
    } elseif ($annexSeparetedFirstPage == 'major') {
        $head_foot_options['page_number'] = '1,2,3';
    }
} else {
    $head_foot_options['page_number'] = '123';
}
if (isset($templatePHP['footer'])) {
    $footerData = $templatePHP['footer'];
    if ($annexSepareted == true) {
        $footer_start_page = 1;
    } else {
        if ($footerData['first_page'] == 'true')
            $footer_start_page = 1;
        else
            $footer_start_page = 2;
    }
    if ($footerData['position'])
        $head_foot_options['footer_align'] = $footerData['position'];
    $pdf->SetPrintFooter(true);
} else {
    $pdf->SetPrintFooter(false);
}

if ($crtDo == "preview") {
    $pdf_data = $_POST;
    if ($total = get_option("annual_crtNr", $_POST['office_address']))
        $total++;
    else
        $total = 1;
    if (!isset($pdf_data['certificate_nr']) or $pdf_data['certificate_nr'] == '0')
        $pdf_data['certificate_nr'] = "XXXXXXXXX"; //$office_address['office_country'] . $office_address['certificate_prefix'] . str_pad($total, 5, "0", STR_PAD_LEFT);
    if (!isset($pdf_data['date_of_issue']))
        $pdf_data['date_of_issue'] = date("d/m/Y");
    if (!isset($pdf_data['date_of_expiry']))
        $pdf_data['date_of_expiry'] = date('d/m/Y', strtotime('+1 year'));;
} else {
    //print certificate
    $pdf_data = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates WHERE crtNr='$_POST[crtNr]'");

    $pdf_data['date_of_issue'] = date("d/m/Y", $pdf_data['date_of_issue']);
    $pdf_data['date_of_expiry'] = date("d/m/Y", $pdf_data['date_of_expiry']);
    if (is_array(json_decode($pdf_data['reference_standards'], true)))
        $pdf_data['reference_standards'] = json_decode($pdf_data['reference_standards'], true);
    if (is_array(json_decode($pdf_data['category'], true)))
        $pdf_data['category'] = json_decode($pdf_data['category'], true);
    if (trim($pdf_data['certificate_nr']) == '' or !isset($pdf_data['certificate_nr']) or $pdf_data['certificate_nr'] == '0') {
        if ($total = get_option("annual_crtNr", $_POST['offid']))
            $total++;
        else
            $total = 1;

        $pdf_data['certificate_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($total, 5, "0", STR_PAD_LEFT);
        update_option("annual_crtNr", $total, $_POST['offid']);
    }
}

if (!isset($_POST['downLoadZipFile']) && $protected_pdf = get_option('protected_pdf')) {
    $protected_pdf = json_decode($protected_pdf, true);
    if (isset($protected_pdf['annual']) && isset($protected_pdf['protect']) && trim($protected_pdf['password']) != '') {
        $pdf->SetProtection(array('edit', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble'), '', $protected_pdf['password'], 0, null);
    }
}

$pdf_data['office_address'] = '';
if (isset($_POST['office_address'])) {
    if ($office_address = $amdb->get_row("SELECT offices.*,hqc_office_certificate_data.office_names,hqc_office_certificate_data.certificate_address,hqc_office_certificate_data.annex_footer from offices JOIN hqc_office_certificate_data ON hqc_office_certificate_data.offid = offices.offid WHERE offices.offid = '$_POST[office_address]'")) {
        if (isset($office_address['office_names']) and trim($office_address['office_names']) != '' and is_array(json_decode($office_address['office_names'], true))) {
            $office_names = json_decode($office_address['office_names'], true);
            $office_address['office_name_english'] = $office_names['office_name_english'];
            $office_address['office_name_arabic'] = $office_names['office_name_arabic'];
        }
        if (isset($office_address['certificate_address']) and trim($office_address['certificate_address']) != '' and is_array(json_decode(str_replace(array("\r\n", "\n"), '\n', $office_address['certificate_address']), true))) {
            $certificate_address = json_decode(str_replace(array("\r\n", "\n"), '\n', $office_address['certificate_address']), true);
            $pdf_data['office_address'] = str_replace(array("\r\n\r\n", "\r\n"), array("<div style=\"line-height:15px\"></div>", "<br/>"), $certificate_address['annual']);
            $office_address['shipment_address'] = $certificate_address['annex'];
        } else {
            $pdf_data['office_address'] = str_replace(array("\r\n\r\n", "\r\n"), array("<div style=\"line-height:15px\"></div>", "<br/>"), $office_address['certificate_address']);
        }
        $certificate_content['office_address'] = $office_address;
    }
}

// print_r($certificate_content['office_address']);
// exit();
//getting reference_standards
if (is_array($pdf_data['reference_standards']) and $standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE FIND_IN_SET(stnid,'" . implode(',', $pdf_data['reference_standards']) . "') ORDER BY code ASC")) {
    $standards_array = array();
    foreach ($standards as $standard) {
        if ($standard['organisation'] == 'HQC')
            $standards_array[] = $standard['code'] . ': ' . $standard['description'];
        else
            $standards_array[] = $standard['code'];
        if ($standard['code'] == 'OIC/SMIIC 1: 2019')
            $OIC_SMIIC = true;
        //OIC/SMIIC 1: 2019

    };

    //TODO: update new system for standards on one line
    if (isset($_POST['certificate_option']['standards_on_line']))
        $pdf_data['reference_standards'] = implode(' | ', $standards_array);
    else
        $pdf_data['reference_standards'] = implode('<br/>', $standards_array);
}

if (is_array($pdf_data['category']) and $categories = $amdb->get_results("SELECT CONCAT(code,': ',description) AS  category FROM hqc_categories WHERE status='active' and FIND_IN_SET(catid,'" . implode(',', $pdf_data['category']) . "')")) {
    $categories_array = array();
    foreach ($categories as $category) {
        $categories_array[] = substr($category['category'], 0, 1);
    };
    $pdf_data['category'] = implode(', ', $categories_array);
}


if (!isset($pdf_data['reference_nr']) && isset($pdf_data['url']) && trim($pdf_data['url']) != '') {
    $pdf_data['reference_nr'] = explode('/', $pdf_data['url'])[1];
}


if (!isset($pdf_data['reference_nr']))
    $pdf_data['reference_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($_REQUEST['clid'], 5, "0", STR_PAD_LEFT);

if (isset($_GET['test'])) {
    // echo $pdf_data['reference_nr'];
    // echo time();
    // exit();
}
if (isset($_REQUEST['crtNr'])) {
    //getting version number
    $vr = count($amdb->get_results("SELECT * FROM `hqc_versions` WHERE `item_id` = '$_REQUEST[crtNr]' AND `item_table`='acms_halal_certificates'")) + 1;
} else {
    $vr = 1;
}
$pdf_data['printed_on'] = time();
$QRStyle = array('border' => false, 'padding' => 0, 'fgcolor' => array(0, 0, 0), 'bgcolor' => false);
if ($crtDo == 'preview') {
    $QRLink = 'Certificate is in Preview Mode.';
} else {
    //if($pdf_data['tuid']!='0')
    //$tuid = "&tuid=$pdf_data[tuid]";
    $tuid = "";
    $QRLink = "https://ca.iidc.eu/?crtnr={$pdf_data['certificate_nr']}&vr=$vr";
}

$head_foot_options['QRStyle'] = $QRStyle;
$head_foot_options['QRLink'] = $QRLink;

$pdf_data['main_signature'] = '';
$pdf_data['main_stempel'] = '';
$pdf_data['annex_signature'] = '';
$pdf_data['annex_stempel'] = '';
$pdf_data['setXYAfter'] = array();

if (isset($certificate_option['office_signature']) and $certificate_option['office_signature'] == 'yes') {
    $jsonFile = $prog_path . '/data/offices/' . $office_address['offid'] . '/images/' . 'signature_' . $office_address['offid'] . '.json';
    if (file_exists($jsonFile)) {
        $signature = json_decode(file_get_contents($jsonFile), true);
        $templatePHP = $signature;
    }
}
if (!function_exists('insertImages')) {
    function insertImages($imgs = 'images')
    {
        global $theTemplate, $templatePHP, $prog_path, $prog_url, $pdf, $annexPageOnly, $annexSeparetedFirstPage, $pdf_data, $template, $office_address, $certificate_option;
        if (
            isset($_POST['certificate_option']) and isset($_POST['certificate_option']['image'])
            and isset($templatePHP[$imgs]) and count($templatePHP[$imgs]) > 0
        ) {
            $optionImages = $_POST['certificate_option']['image'];

            if ($annexPageOnly == true or (isset($annexSeparetedFirstPage) && $annexSeparetedFirstPage == 'normal')) {
                if (isset($templatePHP[$imgs]['main_signature']))
                    unset($templatePHP[$imgs]['main_signature']);
                if (isset($templatePHP[$imgs]['main_stempel']))
                    unset($templatePHP[$imgs]['main_stempel']);
            }

            foreach ($templatePHP[$imgs] as $key => $image) {
                if (in_array($key, $optionImages)) {
                    if (isset($image['file']) and isset($image['position']) and strstr($image['position'], ',')) {
                        $position = explode(',', $image['position']);

                        $imageFile = $prog_path . '/data/offices/' . $office_address['offid'] . '/images/' . $image['file'];
                        if (!file_exists($imageFile)) {
                            $imageFile = $prog_path . '/data/offices/0/images/' . $image['file'];
                        }

                        if (file_exists($imageFile)) {
                            if (strstr($theTemplate, '[' . $key . ']')) {
                                $pdf_data['setXYAfter'][] = $key;
                                $imageTag = '[pdf img(' . $imageFile . ',' . $position[0] . ',' . $position[1] . ',' . $image['width'] . ')]';
                                $theTemplate = str_replace('[' . $key . ']', $imageTag, $theTemplate);
                            } else {
                                if ($imgs == 'annex-images')
                                    $position[1] = $pdf->getY() + 40;
                                $pdf->Image($file = $imageFile, $position[0], $position[1], $w = $image['width'], $h = '', $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
                            }
                        } else {
                            $theTemplate = str_replace('[' . $key . ']', '', $theTemplate);
                        }
                    }
                } else {
                }
            }
        }
        if (isset($certificate_option['eiaci']) && !isset($_POST['eiaci'])) {

            if (file_exists($prog_path . '/data/images/eiaci_' . $_REQUEST['offid'] . '.png'))
                $eiaci_logo =  '/data/images/eiaci_' . $_REQUEST['offid'] . '.png';
            elseif (file_exists($prog_path . '/data/offices/' . $_REQUEST['offid'] . '/images/eiaci_' . $_REQUEST['offid'] . '.png'))
                $eiaci_logo =  '/data/offices/' . $_REQUEST['offid'] . '/images/eiaci_' . $_REQUEST['offid'] . '.png';
            else
                $eiaci_logo =  '/data/images/eiaci.png';
            if (file_exists($prog_path . $eiaci_logo)) {
                if (isset($certificate_option['eiaci_align'])) {
                    $eiaci_align = $certificate_option['eiaci_align'];
                } else {
                    $eiaci_align = 'left';
                }

                if ($eiaci_align == 'left')
                    $imgPosition = 25;
                elseif ($eiaci_align == 'right')
                    $imgPosition = 160;
                elseif ($eiaci_align == 'center')
                    $imgPosition = 95;
                $pdf->Image($file = $prog_path . $eiaci_logo, $imgPosition, 225, $w = '25', $h = '');
                $_POST['eiaci'] = 1;
            }
        }
        //$pdf->Image($file = $prog_path . '/data/images/hak.png', $x = 168, $y = 203, $w = 22, $h = '');
    }
}

unset($pdf_data['product']);
$pdf_data['manufacturing_address'] = '';
if ($company = $amdb->get_row("SELECT * FROM companies where companies.clid = '$_REQUEST[clid]'")) {
    //$pdf_data['awarded_to'] = $company['company_name'];
    $pdf_data['company_address'] = "
$company[street1],
$company[zip1] $company[city1], $company[country1]";
    if (trim($company['ec_number']) != "")
        $pdf_data['company_address'] .= "<br/>EC No.: $company[ec_number]";
}
$stids = '';
if (isset($pdf_data['manufacturing_site'])) {
    if (is_array($pdf_data['manufacturing_site']) and count($pdf_data['manufacturing_site']) > 0) {
        $stids = implode(',', $pdf_data['manufacturing_site']);
    } elseif (trim($pdf_data['manufacturing_site']) != '' and trim($pdf_data['manufacturing_site']) != 'yes') {
        $stids = $pdf_data['manufacturing_site'];
    }
}

if (trim($stids) != '') {
    if ($sites = $amdb->get_results("SELECT * FROM companies_production_sites WHERE status!='deleted' AND clid='$_REQUEST[clid]' AND FIND_IN_SET(stid,'$stids')")) {
        $manufacturing_address = array();
        foreach ($sites as $site) {
            if (trim($site['site_address']) != '' and is_array(json_decode($site['site_address'], true))) {
                $site_address = json_decode($site['site_address'], true);
                $address = array();
                for ($i = 0; $i <= 3; $i++)
                    $address[$i] = '';
                if (trim($site['site_name']) != '')
                    $address[0] = $site['site_name'];
                if (trim($site_address['street']) != '')
                    $address[1] = $site_address['street'];
                if (trim($site_address['zipcode']) != '')
                    $address[2] = $site_address['zipcode'];
                if (trim($site_address['city']) != '')
                    $address[2] .= ' ' . $site_address['city'];
                if (trim($site_address['country']) != '')
                    $address[3] = $site_address['country'];
                foreach ($address as $key => $each) {
                    if (trim($each) == '')
                        unset($address[$key]);
                    else
                        $address[$key] = trim($each);
                }
                $manufacturing_address[] = implode(', ', $address);
                if (isset($_POST['certificate_option']['awarded_to_site']))
                    $awarded_to_site = $address;
            }
            if (isset($_POST['certificate_option']['manufacturing_sites_OL']))
                $pdf_data['manufacturing_address'] = implode('. ', $manufacturing_address);
            else
                $pdf_data['manufacturing_address'] = implode('<br/>', $manufacturing_address);
        };
        if (isset($awarded_to_site)) {

            $pdf_data['manufacturing_address'] = $pdf_data['company_address'];
            if (isset($_POST['certificate_option']['awarded_as_site']))
                $pdf_data['manufacturing_address'] = $pdf_data['awarded_to'] . ', ' . $pdf_data['company_address'];
            else
                $pdf_data['manufacturing_address'] = '';

            $pdf_data['awarded_to'] = $awarded_to_site[0];
            unset($awarded_to_site[0]);
            $pdf_data['company_address'] = implode(', ', $awarded_to_site);
        }
    } else {
        $theTemplate = remove_tag("manufacturing_address", $theTemplate, 'tr');
    }
}

if (trim($pdf_data['manufacturing_address']) == '') {
    $theTemplate = remove_tag("manufacturing_address", $theTemplate, 'tr');
}

if (isset($certificate_option['manufacturing_sites_On_annex'])) {
    $manufacturing_sites_On_annex = $pdf_data['manufacturing_address'];
    $pdf_data['manufacturing_address'] = 'See the Annex for the Manufacturing Site Address(es)';
    $manufacturing_sites_On_annex = '<br/><div style="text-align:center;font-size:18px;font-family:freeserif"><strong>Manufacturing Site Address(es)</strong><br/>' . $manufacturing_sites_On_annex . '</div>';
    $theTemplate = str_replace('[certificate_nr]</strong>', '[certificate_nr]</strong>' . $manufacturing_sites_On_annex, $theTemplate);
}
if (isset($certificate_option['insert_additional_title'])) {
    if ((isset($certificate_option['awarded_additional_title']) and trim($certificate_option['awarded_additional_title']) or (isset($certificate_option['awarded_additional_text']) and trim($certificate_option['awarded_additional_text']) != '')) != '') {
        //find the first tr with [awarded_to] in it in $theTemplate using document DOM object
        $dom = new DOMDocument();
        $dom->loadHTML($theTemplate);
        $xpath = new DOMXPath($dom);
        $awarded_additional = '';
        foreach ($xpath->query('//tr') as $tr) {
            $trHTML = $dom->saveHTML($tr);
            if (strpos($trHTML, '[awarded_to]') !== false) {
                if (isset($certificate_option['awarded_additional_title']) and trim($certificate_option['awarded_additional_title']) != '')
                    $awarded_additional = str_replace('[awarded_to]', $certificate_option['awarded_additional_title'], $trHTML);
            }

            if (strpos($trHTML, '[company_address]') !== false) {
                if (isset($certificate_option['awarded_additional_text']) and trim($certificate_option['awarded_additional_text']) != '')
                    $awarded_additional .= str_replace('[company_address]', $certificate_option['awarded_additional_text'], $trHTML);
                $theTemplate = str_replace($trHTML, $trHTML . $awarded_additional, $theTemplate);
                break;
            }
        }
    }
}

if (isset($certificate_option['replace_header_text']) and  (isset($certificate_option['header_text']) and trim($certificate_option['header_text']) != '')) {
    //find the first span with data-replace="header_text" in it in $theTemplate using document DOM object and replace the text of the node keeping span with $certificate_option['header_text']
    $dom = new DOMDocument();
    $dom->loadHTML($theTemplate);
    $xpath = new DOMXPath($dom);
    foreach ($xpath->query('//span') as $span) {
        $spanHTML = $dom->saveHTML($span);
        if ($span->getAttribute('data-replace') == 'header_text') {
            $theTemplate = str_replace($span->nodeValue, $certificate_option['header_text'], $theTemplate);
            break;
        }
    }
}

if (isset($certificate_option['awarded_to_title']))
    $theTemplate = str_replace('[awarded_to_title]', $certificate_option['awarded_to_title'], $theTemplate);
else
    $theTemplate = str_replace('[awarded_to_title]', 'Awarded to', $theTemplate);

if (isset($pdf_data['manufacturing_site']))
    unset($pdf_data['manufacturing_site']);

//adding products
if (isset($_REQUEST['products'])) {
    $nr = 1;
    if ($annexSepareted == true)
        $productsTable = array();
    else
        $productsTable = '';
    $totalWidth = 18;
    $table = '<table border="0" cellpadding="3" style="width:18cm">[tableBody]</table>';
    $tableHead = "<tr>";
    $tableBody = "<tr>";
    $certificate_fonts = array();
    if (isset($certificate_option['fonts']))
        $certificate_fonts = $certificate_option['fonts'];
    $annex_titles = array('AutNr' => 'Nr', 'article_nr' => 'Article code', 'product_name' => 'Product name', 'description' => 'Description', 'brand_name' => 'Brand name');
    foreach ($_POST['annex_options'] as $colKey => $colTitle) {
        if (isset($annex_titles[$colKey]) and (isset($_POST['annex_options']['add_' . $colKey]) or $colKey == 'product_name')) {
            $colWidth = '[colwidth]';
            if (isset($_POST['annex_options'][$colKey . '_width']) and trim($_POST['annex_options'][$colKey . '_width']) != '') {
                $colWidth = str_replace(',', '.', $_POST['annex_options'][$colKey . '_width']);
                $totalWidth = $totalWidth - $colWidth;
            }
            $fontSizeHeight = '';
            if (isset($certificate_fonts['products'])) {
                $fontSizeHeight = "font-size:" . $certificate_fonts['products'] . "px;line-height:" . ($certificate_fonts['products'] + 2) . "px";
            }
            $tableHead .= '<th style="width:' . $colWidth . 'cm;font-weight:bold;background-color:#eee;' . $fontSizeHeight . ';border: 1px solid #000000;">' . $colTitle . '</th>';
            $tableBody .= '<td style="border: 1px solid #000000;' . $fontSizeHeight . ';font-family:freeserif">[' . $colKey . ']</td>';
        }
    }
    $tableHead .= "</tr>";
    $tableBody .= "</tr>";
    if (preg_match_all('/width:(.*)cm;/U', $tableHead, $matches, PREG_SET_ORDER)) {
        $lastWidth = end($matches)[0];
        $lastWidthCm = end($matches)[1];
        if ($lastWidthCm != '[colwidth]' and trim($lastWidthCm != ''))
            $totalWidth = $totalWidth + $lastWidthCm;
        $lastColWidth = 'width:' . $totalWidth . 'cm;';
        $tableHead = preg_replace('/(' . $lastWidth . '(?!.*' . $lastWidth . '))/', $lastColWidth, $tableHead);
    }
    foreach ($annex_titles as $keyTitle => $title) {
        if (isset($_POST['annex_options'][$keyTitle]))
            $annex_titles[$keyTitle] = $_POST['annex_options'][$keyTitle];
    }
    $product_sort_by = 'order by prdid ASC';
    if (trim($_POST['product_sort_by']) != '') {
        $product_sort_by = 'order by TRIM(' . $_POST['product_sort_by'] . ')+0 ASC, TRIM(' . $_POST['product_sort_by'] . ') ASC';
    }


    if ($products = $amdb->get_results("SELECT *, TRIM(product_name) as product_name,trim(article_nr) as article_nr,description,brand_name FROM acms_hdcs_products where  clid = '$_POST[clid]' $product_sort_by")) {

        $_REQUEST['products'] = explode(',', $_REQUEST['products']);
        $productsRows = '';
        $pdf_data['products'] = '';
        if ($annexPageOnly == true) {
            $theTemplateAnnexPages = $theTemplate;
        } elseif ($annexSepareted == true) {
            $templateParts = explode('[annexPage]', $theTemplate);
            $theTemplate = $templateParts[0];
            $theTemplateAnnexPages = $templateParts[1];
        }
        $srNr = 0;
        foreach ($products as $product) {
            $srNr++;

            if (in_array($product['prdid'], $_REQUEST['products'])) {
                $certificate_content['products'][$product['prdid']] = array(
                    'AutNr' => $product['prdid'],
                    'article_nr' => $product['article_nr'],
                    'product_name' => $product['product_name'],
                    'description' => $product['description'],
                    'brand_name' => $product['brand_name']

                );

                if ($annexSepareted == true && isset($theTemplateAnnexPages))
                    $nr = 1;
                $productRow = str_replace(
                    array('[AutNr]', '[article_nr]', '[product_name]', '[description]', '[brand_name]'),
                    array(
                        $nr++,
                        str_replace(array('<', '>'), array('&lt;', '&gt;'), clean_string($product['article_nr'])),
                        str_replace(array('<', '>'), array('&lt;', '&gt;'), clean_string($product['product_name'])),
                        str_replace(array('<', '>'), array('&lt;', '&gt;'), clean_string($product['description'])),
                        str_replace(array('<', '>'), array('&lt;', '&gt;'), clean_string($product['brand_name']))
                    ),
                    $tableBody
                );
                //		$productRow = clean_string($productRow);
                $productsRows .= trim($productRow);
                //	file_put_contents('../../data/temp/certificates/productsRows'. $product['prdid'].'.txt', $productsRows);
                if ($annexSepareted == true && isset($theTemplateAnnexPages)) {
                    $productsTable[] = str_replace('[products]', str_replace('[tableBody]', $tableHead . $productsRows, $table), $theTemplateAnnexPages);
                    $productsRows = '';
                }
            }
        }
        //print_r($productsRows);
        if ($annexSepareted == true) {
            if ($annexSeparetedFirstPage == 'preceded')
                $theTemplate .= implode('[pdf addPage()]' . trim($theTemplate), $productsTable);
            elseif ($annexSeparetedFirstPage == 'major')
                $theTemplate .= implode('[pdf addPage()]', $productsTable);
            else
                $theTemplate = implode('[pdf addPage()]', $productsTable);
        } else {
            $productsTable = str_replace('[tableBody]', $tableHead . $productsRows, $table);
            $pdf_data['products_rows'] = $productsRows;
            $pdf_data['products'] = $productsRows;
        }
    }
}

$pdf_data['annexPage'] = '';
$pdf_data['main_director'] = '';
$pdf_data['authorized_unit'] = '';
$pdf_data['islamic_expert'] = '';
$pdf_data['annex_footer'] = '';

if (isset($pdf_data['signatories'])) {
    if (!is_array($pdf_data['signatories']) and is_array(json_decode($pdf_data['signatories'], true)))
        $pdf_data['signatories'] = json_decode($pdf_data['signatories'], true);
    $signatories = $pdf_data['signatories'];
    unset($pdf_data['signatories']);
    if (isset($signatories) and is_array($signatories) and count($signatories) > 0) {
        foreach ($signatories as $key => $value) {
            $pdf_data[$key] = implode("<br/>", $value);
        }
    }
}

if (isset($certificate_fonts) and count($certificate_fonts) > 0) {
    $dom = new DOMDocument();
    $dom->recover = true;
    $dom->strictErrorChecking = false;
    $dom->preserveWhiteSpace = false;
    @$dom->loadHTML('<?xml encoding="UTF-8"><body>' . $theTemplate . "</body>", LIBXML_HTML_NODEFDTD);
    $trs = $dom->getElementsByTagName('tr');
    foreach ($trs as $tr) {
        if ($tr->getAttribute('style'))
            $tr->removeAttribute('style');
        if ($tr->getAttribute('height'))
            $tr->removeAttribute('height');
    }
    $cells = $dom->getElementsByTagName('td');
    foreach ($cells as $cell) {
        foreach ($certificate_fonts as $fk => $fv) {
            if (strstr($cell->nodeValue, '[' . $fk . ']')) {
                $newStyle = array();
                if ($cell->getAttribute('style')) {
                    $oldStyles = explode(';', $cell->getAttribute('style'));
                    foreach ($oldStyles as $sk => $sv) {
                        if (!strstr($sv, 'size:') and !strstr($sv, 'height:') and trim($sv) != '')
                            $newStyle[] = trim($oldStyles[$sk]);
                    }
                }
                $newStyle[] = 'height: ' . ($fv + 4) . 'px';
                $newStyle[] = 'line-height: ' . ($fv + 4) . 'px';
                $newStyle[] = 'font-size: ' . $fv . 'px';
                $cell->setAttribute('style', implode(';', $newStyle));
            }
        }
    }
    if (preg_match('/<body>(.*)<\/body>/is', $dom->saveHTML(), $text))
        $theTemplate = $text[1];
    else
        $theTemplate = $dom->saveHTML();
}
// $templateFile = '../../data/temp/certificates/theTemplate.txt';
// $dataFile = '../../data/temp/certificates/pdf_data.txt';
// file_put_contents($templateFile, $theTemplate);
// file_put_contents($dataFile, json_encode($pdf_data));
// sleep(60);
// if(file_exists($dataFile)){
// header('Location: certificate.pdf.php?data=cached');
// exit();
// }


$pdf_data['SMIIC'] = '';
if (isset($OIC_SMIIC) && $crtDo == 'preview') {
    if (isset($certificate_option['cert_validity']))
        $cert_validity = $certificate_option['cert_validity'];
    else
        $cert_validity = '1';
    $SMIIC = '<table cellpadding=2>
        <tr><td style="width:4cm;font-weight:bold">Certification cycle:<br/>Issuance Frequency:</td><td>3 Years<br/>Every ' . $cert_validity . ' year' . ($cert_validity != '1' ? 's' : '') . '</td></tr>
        </table>';
    $pdf_data['SMIIC'] = $SMIIC;
}

$revision = array();
if (isset($pdf_data['revision'])) {
    if (is_array($pdf_data['revision'])) {
        $revision = $pdf_data['revision'];
    } elseif (is_array(json_decode($pdf_data['revision'], true))) {
        $revision = json_decode($pdf_data['revision'], true);
    }
}

//annex number and annex revision date and number
if (isset($revision['insert'])) {
    //annex number
    if (isset($certificate_option['annex_number'])) {
        $pdf_data['annex_number'] = $certificate_option['annex_number'];
        unset($certificate_option['annex_number']);
    }
    if (isset($revision['number']))
        $pdf_data['revision_number'] = $revision['number'];
    if (isset($revision['date']))
        $pdf_data['revision_date'] = $revision['date'];
    unset($pdf_data['revision']);
} else {
    $theTemplate = remove_tag("revision", $theTemplate, 'td');
}

$time = time();
if ($_REQUEST['crtDo'] == 'print') {

    $old_certificate_content = $pdf_data['certificate_content'];
    unset($pdf_data['certificate_content']);
    $certificate_content['data'] = $pdf_data;
    unset($certificate_content['data']['products']);
    unset($certificate_content['data']['products_rows']);
    foreach ($certificate_content as $key => $value) {
        foreach ($value as $content_key => $content_value) {
            if (!is_array($content_value) and is_array(json_decode($content_value, true))) {
                $certificate_content[$key][$content_key] = json_decode($content_value, true);
            }
        }
    }

    $certificate_content = array($time => $certificate_content);
    if (trim($old_certificate_content) != '' and is_array(decode_json($old_certificate_content))) {
        $old_certificate_content = decode_json($old_certificate_content);
        $certificate_content = $old_certificate_content + $certificate_content;
    }

    //TODO: change this from json_encode to serialize();
    $certificate_content = $amdb->excape(json_encode($certificate_content, JSON_UNESCAPED_UNICODE));
    $amdb->query("UPDATE $tbl[prefix]_halal_certificates SET certificate_content = '$certificate_content' WHERE crtNr='$_POST[crtNr]'");
}

$pdf->AddPage();
if ($_REQUEST['crtDo'] == 'view_first_page') {
    // $importPage = $pdf->importPage(2);
    // $pdf->useTemplate($importPage, 0, 0);
}

insertImages();
if (!function_exists('print_header_footer')) {
    function print_header_footer()
    {
        global $pdf, $head_foot_options, $hcp_path, $pdf_data, $time, $office_address, $crtDo, $prog_path, $certificate_option, $vr;
        $text_color = '#2F8B43';
        $hqc_logo =  '/data/templates/logo.svg';

        if (file_exists($hcp_path . $hqc_logo)) {
            $y = 12;
            $pdf->ImageSVG($file = $hcp_path . $hqc_logo, (210 - 40) / 2, $y, $w = '40', $h = '');
        }

        if (isset($certificate_option['country_flag'])) {
            $cc = strtolower(trim($office_address['country_flag']) != '' ?
                $office_address['country_flag'] :
                $office_address['office_country']);
            $flagText = $office_address['flag_text'];
            $flag =  '/images/cc/' . $cc . '.svg';

            if (!file_exists($hcp_path . $flag))
                $flag =  '/data/offices/0/images/NL-flag.svg';
            $pdf->ImageSVG($file = $hcp_path . $flag, 15, 15, $w = '34', $h = '');
            $pdf->SetY(38);
            $pdf->SetX(15);
            $pdf->writeHTML('<table style="width:3.4cm;"><tr><td style="text-align:center;font-size:12px;color:' . $text_color . ';font-family:times">' . $flagText . '</td></tr></table>');
        }

        $header = '<table cellpadding=0>
<tr>
<td style="font-size:17px;font-weight:bold;color:' . $text_color . ';width:10cm;font-family:times">Control Office Of Halal Slaughtering and<br>' . $office_address['office_name_english'] . '</td>
<td style="font-size: 19px; font-family: hacenlinerscreenbd;line-height:20px;text-align:right;color:' . $text_color . ';width:8cm">مكتب مراقبة الذبح حسب الشريعة الإسلامية<br>' . $office_address['office_name_arabic'] . '</td>
</tr>
</table>';

        $pdf->SetY(48);
        $pdf->SetX(15);
        $pdf->writeHTML($header);
        if (isset($head_foot_options['QRStyle']) && isset($head_foot_options['QRLink'])) {
            $yx = array(166, 14);
            $pdf->SetMargins(0, 0, 0);

            if ($crtDo == 'preview') {
                $QRLink = "Certificate is in Preview Mode.";
            } else {
                //if ($pdf_data['uid'] != '0')
                //	$tuid = "&tuid=$pdf_data[tuid]";
                $tuid = "";
                $QRLink = $head_foot_options['QRLink'] . "&get=annex&vr=$vr";
            }

            $pdf->write2DBarcode($QRLink, 'QRCODE,H', $yx[0], $yx[1], 28, 28, $head_foot_options['QRStyle'], 'N');

            $pdf->SetY(($yx[1] + 29));
            $pdf->setX($yx[0]);
            $pdf->writeHTML('<table style="width:2.8cm;"><tr><td style="text-align:center;font-size:12px;line-height:12px">Scan to verify</td></tr></table>');
        }

        if (isset($office_address['shipment_address']) && trim($office_address['shipment_address'])) {
            $pdf_data['footer_pos'] = isset($templatePHP['address-position']) ? $templatePHP['address-position'] : 285 - (count(explode("\n", $office_address['shipment_address'])) * 4);

            $pdf->SetY($pdf_data['footer_pos']);
            $pdf->writeHTML('<div style="font-size:14px;line-height:16px;text-align:center;font-family:times;color:' . $text_color . '">' . str_replace("\n", "<br/>", $office_address['shipment_address']) . '</div>');

            if (isset($certificate_option['eiaci_annex'])) {
                if (file_exists($prog_path . '/data/images/eiaci_' . $_REQUEST['offid'] . '.png'))
                    $eiaci_logo =  '/data/images/eiaci_' . $_REQUEST['offid'] . '.png';
                else
                    $eiaci_logo =  '/data/images/eiaci.png';

                if (file_exists($prog_path . $eiaci_logo)) {
                    if (isset($certificate_option['eiaci_annex_align'])) {
                        $eiaci_align = $certificate_option['eiaci_annex_align'];
                    } else {
                        $eiaci_align = 'left';
                    }

                    if ($eiaci_align == 'left')
                        $imgPosition = 15;
                    elseif ($eiaci_align == 'right')
                        $imgPosition = 170;
                    elseif ($eiaci_align == 'center')
                        $imgPosition = 95;
                    if ($eiaci_align == 'center')
                        $imagY = 250;
                    else
                        $imagY = 260;
                    $pdf->Image($file = $prog_path . $eiaci_logo, $imgPosition, $imagY, $w = '25', $h = '');
                    $_POST['eiaci'] = 1;
                }
            }
        }
    }
}

if (isset($office_address) and is_array($office_address)) {
    $pdf_data['office_name_english'] = $office_address['office_name_english'];
    $pdf_data['annex_footer'] = $office_address['annex_footer'];
}

if (count($pdf_data) > 0) {
    foreach ($pdf_data as $key => $value) {
        if ($key != 'certificate_option' && $key != 'annex_options' and !is_array($value) and $key != 'products') {
            $font_size = '';
            if (isset($certificate_fonts[$key])) {
                $font_size = 'font-size:' . $certificate_fonts[$key] . 'px';
            }
            if ($key != 'annex_number' && $key != 'certificate_nr') {
                $value = '<span style="font-family:freeserif;' . $font_size . '">' . trim($value) . '</span>';
                $theTemplate = str_replace('[' . $key . ']', trim($value), $theTemplate);
            }
        }
    }
}
//TODO: save these data for generating certificate using QR check
// echo $productsTable;
// echo $theTemplate;

if ($_REQUEST['crtDo'] == 'view_first_page') {
    //	$importPage = $pdf->importPage(2);
    //	$pdf->useTemplate($importPage, 0, 0);
    $theTemplateParts = explode('[annexPage]', $theTemplate);
    write_pdf($theTemplateParts[0]);
    $pdf->deletePage(2);
    $pdf->Output('certificate', 'I');
    return;
}

$products_pages = '';
if ($annexSepareted == false) {
    $annex_top = 65;
    $annex_bottom = 254;
    $theTemplateParts = explode('[products]', $theTemplate);

    if (isset($certificate_option['insert_HAK_logo']) && $annexPageOnly == false) {
        //	$pdf->Image($file = $prog_path . '/data/images/hak.png', $x = 155, $y = 210, $w = 19, $h = '');
    }

    write_pdf($theTemplateParts[0]);

    $cur_y = $pdf->GetY();
    $pdf->startTransaction();
    $pdf->SetY(0);
    write_pdf(str_replace('</tr>', "</tr>\n", $productsTable));
    $products_height = round($pdf->GetY());
    $pdf = $pdf->rollbackTransaction();

    $pdf->startTransaction();
    $pdf->SetY(0);
    write_pdf($theTemplateParts[1]);
    $annex_height = round($pdf->GetY());
    $pdf = $pdf->rollbackTransaction();

    $products_rows = explode('</tr>', $pdf_data['products_rows']);
    if (($cur_y + $products_height + $annex_height) < $annex_bottom and count($products_rows) < 20) {
        $pdf->writeHTML($productsTable);
        write_pdf($theTemplateParts[1]);
    } else {
        $products_rows = '';
        $total_rows = 0;
        $page_rows = 0;
        $pdf_data['products_rows'] = explode('</tr>', $pdf_data['products_rows']);
        foreach ($pdf_data['products_rows'] as $product_row) {
            if (trim($product_row) != '') {
                $page_rows++;
                $products_rows .= $product_row . "</tr>\n";
                $products_table = str_replace('[tableBody]', $tableHead . $products_rows, $table);
                //if ($page_rows > 26) {
                $tbl_height =  calc_height($products_table, $certificate_fonts['products']);
                if (($cur_y + $tbl_height) >= $annex_bottom - 2) {
                    $pdf->writeHTML($products_table);
                    //	$products_pages .= $products_table . '[pdf addPage(15,' . $annex_top . ')]';
                    $pdf->AddPage();
                    $pdf->SetX(15);
                    $pdf->SetY($annex_top);
                    $cur_y = $pdf->GetY();
                    $products_rows = '';
                    $total_rows = $total_rows + $page_rows;
                    $page_rows = 0;
                }
                //}
            }
        }
        for ($i = 1; $i <= $total_rows; $i++) {
            array_shift($pdf_data['products_rows']);
        }
        if (count($pdf_data['products_rows']) > 1) {
            $products_rows = implode('</tr>', $pdf_data['products_rows']);
            $products_table = str_replace('[tableBody]', $tableHead . $products_rows, $table);
            $pdf->writeHTML($products_table);
            //	$products_pages .= $products_table . '[pdf addPage(15,' . $annex_top . ')]';
        }
        $cur_y = $pdf->GetY();

        if ((round($cur_y) + round($annex_height)) > $annex_bottom) {
            if ((round($cur_y) + round($annex_height)) < ($annex_bottom + 50)) {
                $pdf->SetY($cur_y - 10);
            } else {
                $pdf->AddPage();
                $pdf->SetX(15);
                $pdf->SetY($annex_top);
            }
            //$products_pages .= $products_table . '[pdf addPage(15,' . $annex_top . ')]';
        }
        write_pdf($theTemplateParts[1]);
    }
} else {
    // if (isset($certificate_option['insert_HAK_logo']) && $annexSeparetedFirstPage != 'normal')
    // 	$pdf->Image($file = $prog_path . '/data/images/hak.png', $x = 155, $y = 210, $w = 19, $h = '');
    write_pdf($theTemplate);
}

// echo $products_pages;
// exit();
//echo $theTemplate;
$added_pages = 1;
$certSubNumber = '';
if (!function_exists('write_pdf')) {
    function write_pdf($theTemplate)
    {
        global $pdf, $template, $revision, $QRLink, $pdf_data, $certificate_option, $prog_path, $annexSeparetedFirstPage, $added_pages, $certSubNumber;
        $certificate_auto_number = '';
        if (trim($theTemplate) != '') {
            $theTemplate = str_replace(array('&amp;', '&lsquo;', '&rsquo;'), array('&', '‘', '’'), $theTemplate);
            $parts = preg_match_all('/\[pdf(.*)\]/U', $theTemplate, $thePatrs);
            foreach ($thePatrs[0] as $macth) {
                $theTemplate  = str_replace($macth, "<brkPoint>" . $macth . "<brkPoint>", $theTemplate);
            };
            $theTemplate = str_replace(array('<brkPoint><br>', '<brkPoint><brkPoint><brkPoint>', '<brkPoint><brkPoint>'), '<brkPoint>', $theTemplate);
            $pdfParts = explode('<brkPoint>', $theTemplate);
            $curY = 0;
            $curX = 0;
            ob_start();
            echo $template['style'];
            $pdf->writeHTML(ob_get_contents());
            ob_end_clean();

            foreach ($pdfParts as $key => $part) {
                if (strstr($part, '[pdf ')) {
                    preg_match('/\[pdf (.*)\((.*)\)\]/', $part, $pdfMatch);
                    if (trim($pdfMatch[1]) == 'img') {
                        $imgParts = explode(',', $pdfMatch[2]);
                        if (strstr($imgParts[2], '+'))
                            $imgParts[2] = $curY + str_replace('+', '', $imgParts[2]);
                        if (strstr($imgParts[2], '-'))
                            $imgParts[2] = ($curY - str_replace('-', '', $imgParts[2]));
                        $pdf->Image($file = $imgParts[0], $imgParts[1], $imgParts[2], $w = $imgParts[3], $h = '', $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
                    } elseif (trim($pdfMatch[1]) == 'setY' or trim($pdfMatch[1]) == 'setYAfter') {
                        if (trim($pdfMatch[1]) == 'setYAfter' && strstr($pdfMatch[2], ',')) {
                            $afterParts = explode(',', $pdfMatch[2]);
                            $y = $afterParts[0];
                            $afterY = $afterParts[1];
                        } else {
                            if (strstr($pdfMatch[2], ',')) {
                                $ifParts = explode(',', $pdfMatch[2]);
                                if (strstr($ifParts[1], 'if-')) {
                                    $ifInput = str_replace('if-', '', $ifParts[1]);
                                    if (strstr($ifInput, ':')) {
                                        $ifInputParts = explode(':', $ifInput);
                                        if (isset($certificate_option[$ifInputParts[0]]) and trim($certificate_option[$ifInputParts[0]]) != '')
                                            $y = $ifInputParts[1];
                                        else
                                            $y = $ifParts[0];
                                    };
                                }
                            } else {
                                $y = $pdfMatch[2];
                            }
                        }
                        if (strstr($y, '+'))
                            $y = $pdf->getY() + str_replace('+', '', $y);
                        if (strstr($y, '-'))
                            $y = $pdf->getY() - str_replace('-', '', $y);
                        if (!isset($afterY) or (isset($afterY) && in_array($afterY, $pdf_data['setXYAfter']))) {
                            $pdf->setY($y);
                        }
                    } elseif (trim($pdfMatch[1]) == 'setX' or trim($pdfMatch[1]) == 'setXAfter') {
                        if (trim($pdfMatch[1]) == 'setXAfter' && strstr($pdfMatch[2], ',')) {
                            $afterParts = explode(',', $pdfMatch[2]);
                            $x = $afterParts[0];
                            $afterX = $afterParts[1];
                        } else {
                            $x = $pdfMatch[2];
                        }
                        if (strstr($x, '+'))
                            $x = $curX + str_replace('+', '', $x);
                        if (strstr($x, '-'))
                            $x = $curX - str_replace('-', '', $x);
                        if (!isset($afterX) or (isset($afterX) && in_array($afterX, $pdf_data['setXYAfter']))) {
                            $pdf->setY($x);
                        }
                    } elseif (trim($pdfMatch[1]) == 'addPage') {
                        $marginLeft = 15;
                        $marginTop = 65;
                        $marginRight = 15;
                        $marginBottom = 5;
                        if (trim($pdfMatch[2]) != '') {
                            $margin = explode(',', $pdfMatch[2]);
                            if (count($margin) == 4) {
                                if (isset($margin[0]))
                                    $marginLeft = $margin[0];
                                if (isset($margin[1]))
                                    $marginTop = $margin[1];
                                if (isset($margin[2]))
                                    $marginRight = $margin[2];
                                if (isset($margin[3]))
                                    $marginBottom = $margin[3];
                            }
                        }
                        $pdf->SetAutoPageBreak(TRUE, $marginBottom);
                        $pdf->AddPage();
                        $added_pages++;

                        if (isset($certificate_option['auto_annex_number']) && isset($annex_number)) {
                            if (($annexSeparetedFirstPage == 'preceded' && $added_pages % 2 == 0) or ($annexSeparetedFirstPage == 'major' && $added_pages > 1) or ($annexSeparetedFirstPage == 'normal' && $added_pages > 0))
                                $annex_number = $annex_number + 1;
                        }

                        if (isset($certificate_option['auto_certificate_number'])) {
                            if (($annexSeparetedFirstPage == 'preceded' && $added_pages % 2 != 0) or ($annexSeparetedFirstPage == 'major' && $added_pages > 1) or ($annexSeparetedFirstPage == 'normal' && $added_pages > 0)) {
                                $srAuNr = $srAuNr + 1;
                                $certSubNumber = '.' . $srAuNr;
                            } else {
                                $certSubNumber = '';
                            }
                        }

                        $pdf->SetMargins($marginLeft, $marginTop, $marginRight);
                        $pdf->SetY($marginTop);
                    } elseif (trim($pdfMatch[1]) == 'setMargins') {
                        $marginLeft = 15;
                        $marginTop = 65;
                        $marginRight = 15;
                        $marginBottom = 5;
                        if (trim($pdfMatch[2]) != '') {
                            $margin = explode(',', $pdfMatch[2]);
                            if (count($margin) == 4) {
                                if (isset($margin[0]))
                                    $marginLeft = $margin[0];
                                if (isset($margin[1]))
                                    $marginTop = $margin[1];
                                if (isset($margin[2]))
                                    $marginRight = $margin[2];
                                if (isset($margin[3]))
                                    $marginBottom = $margin[3];
                            }
                        }
                        $pdf->SetAutoPageBreak(TRUE, $marginBottom);
                        $pdf->SetMargins($marginLeft, $marginTop, $marginRight);
                        $pdf->SetY($marginTop);
                    } elseif (trim($pdfMatch[1]) == 'lastPage') {

                        $pdf->SetAutoPageBreak(TRUE, 0);
                        if (trim($pdfMatch[2]) != '') {
                            $margins = explode(',', $pdfMatch[2]);
                            if (isset($margins[0]) and $pdf->getY() > $margins[0]) {
                                $pdf->AddPage();
                                if (isset($margins[1]))
                                    $pdf->setY($margins[1]);
                            }
                        }
                        insertImages('annex-images');

                        if (isset($certificate_option['insert_HAK_logo'])) {
                            // $curY = $pdf->getY();
                            // $curX = $pdf->getX();
                            // $pdf->Image($prog_path . '/data/images/hak.png', 168, $curY + 30, $w = 20, $h = '');
                        }
                    }
                } elseif (trim($part) != '') {
                    if (strstr($part, '[certificate_nr]')) {

                        if (!isset($srAuNr)) {
                            $srAuNr = 0;
                        }

                        if (!isset($annex_number) && isset($pdf_data['annex_number'])) {
                            $annex_number = $pdf_data['annex_number'];
                        }

                        if (isset($annex_number)) {
                            if (strlen($annex_number) < 2)
                                $annex_number = '0' . $annex_number;

                            $part = str_replace('[annex_number]', $annex_number, $part);
                        }
                        $part = str_replace('[certificate_nr]', $pdf_data['certificate_nr'] . $certSubNumber, $part);
                    }
                    ob_start();
                    echo $template['style'];
                    echo trim($part);
                    if (preg_match('/[\p{Old_Turkic}]/u', $part))
                        $pdf->SetFont('freeserif');
                    $pdf->writeHTML(ob_get_contents());
                    ob_end_clean();
                    $curY = $pdf->getY();
                    $curX = $pdf->getX();
                }
            }
        }
    }
}

$pdfFile = $pdf_data['certificate_nr'];
if (isset($_POST['certificate_file_name']))
    $certificate_file_name = $_POST['certificate_file_name'];
if (isset($certificate_option['certificate_file_name'])) {
    $certificate_file_name = $certificate_option['certificate_file_name'];
    $fileName = $certificate_option['certificate_file_name'];
    if ($fileName == 'company_name_crt_number')
        $pdfFile = $pdf_data['awarded_to'] . "-" . $pdf_data['certificate_nr'];
    elseif ($fileName == 'company_name')
        $pdfFile = $pdf_data['awarded_to'];
    elseif ($fileName == 'product_name')
        $pdfFile = $products[0]['product_name'];
}

$pdfFile = trim(preg_replace('/[\@\%\.\;\" "]+/', '', $pdfFile));
$pdfFile = str_replace(array('™', ' '), array('-'), trim($pdfFile) . "-" . $vr . ".pdf");

if (isset($_POST['future_action']) && isset($_POST['future_action']['send_by_email'])) {
    $future_action = array();
    $the_future_action = $_POST['future_action'];

    if (isset($the_future_action['email'])) {

        if (isset($pdf_data['handled_by']) && trim($pdf_data['handled_by']) != '') {
            $future_action['handled_by'] = $pdf_data['handled_by'];
        } else {
            $future_action['handled_by'] = encode_json(array("uid" => $_SESSION['user']['uid'], "name" => $_SESSION['user']['name']));
        }
        $future_action['clid'] = $pdf_data['clid'];
        $future_action['action_id'] = $pdf_data['crtNr'];
        $future_action['action_data'] = json_encode(array('email' => $the_future_action['email'], 'certificate_nr' => $pdf_data['certificate_nr'], 'bcc' => $the_future_action['bcc']));
    }

    if ($_REQUEST['crtDo'] == 'print' && $the_future_action['when'] == 'in-the-future' && isset($the_future_action['action_date'])) {
        $future_action['action_date'] = $the_future_action['action_date'];
        $future_action['action_status'] = 'Planned';
        $future_action['action_type'] = 'annual_certificate';
        if ($future = $amdb->get_row("SELECT * FROM hqc_future_actions WHERE action_id = '$pdf_data[crtNr]' AND action_type = 'annual_certificate'")) {
            $amdb->update('hqc_future_actions', $future_action, "actid = $future[actid]");
        } else {
            $amdb->insert('hqc_future_actions', $future_action);
        }
        $sendInFuture = true;
    } else {
        $sendByEmailNow = true;
    }
};
$prdids = implode(',', $_REQUEST['products']);
if ($_REQUEST['crtDo'] == 'print' or $_REQUEST['crtDo'] == 'authorize') {
    $status_sent_on = '0';
    if ($_REQUEST['crtDo'] == 'print') {
        if (isset($sendByEmailNow)) {
            $status = 'sentByEmail';
            $status_sent_on = time();
        } elseif (isset($sendInFuture)) {
            $status = 'Planned';
        } else {
            $status = 'printed';
        }
    } else {
        $status = 'authorized';
    }

    $certFile = $hcp_path . "/client_data/certificates/$office[office_country]/" . str_replace('-', '/', $pdf_data['reference_nr']) . "/annual/$pdfFile";
    if (!is_dir(dirname($certFile)))
        mkdir(dirname($certFile), 0777, true);
    $pdf->Output($certFile, 'F');
    if (file_exists($certFile)) {
        $pdf_data['url'] = "$office[office_country]/" . str_replace('-', '/', $pdf_data['reference_nr']) . "/annual/$pdfFile";
        $status = "printed_on='" . time() . "',status_sent_on='$status_sent_on', status='$status'";
        $amdb->query("UPDATE $tbl[prefix]_halal_certificates SET $status, certificate_nr='$pdf_data[certificate_nr]', reference_number='$pdf_data[reference_nr]',vr='$vr', url='$pdf_data[url]' WHERE crtNr='$_POST[crtNr]'");

        if (isset($sendByEmailNow)) {
            include __DIR__ . '/../actions/send_by_email.inc.php';
            exit();
        }
        if (isset($_POST['downLoadZipFile'])) {
            header("location: /certificates/annual/download.php?fl=$pdf_data[url]&pgs=$annexSeparetedFirstPage&fn=$certificate_file_name&prdids=$prdids");
            exit();
        }

        header("location: $prog_www/client_data/certificates/$pdf_data[url]?act=print");
    }
    exit();
}

if (isset($_POST['downLoadZipFile'])) {
    $certUrl = "/client_data/certificates/$office[office_country]/" . str_replace('-', '/', $pdf_data['reference_nr']) . "/annual/$pdfFile";
    $certFile = $hcp_path . $certUrl;
    if (!is_dir(dirname($certFile)))
        mkdir(dirname($certFile), 0777, true);
    $pdf->Output($certFile, 'F');

    header("location: /certificates/annual/download.php?fl=$certUrl&pgs=$annexSeparetedFirstPage&fn=$certificate_file_name");

    exit();
}

if (isset($_GET['get']) && $_GET['get'] == 'annex')
    $pdf->deletePage(1);
$pdf->Output($pdfFile, 'I');
