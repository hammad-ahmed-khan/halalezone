<?php
//TODO: update new system
if (!session_id())
    session_start();
if (!isset($prog_path))
    include "../config/paths.inc.php";
include "../config/countries.code.php";
if (!defined("_HQC_")) {
    exit();
};

if (isset($_GET['vrs'])) {
    $vars = base64_decode($_GET['vrs']);
    $vars = explode("&", $vars);
    foreach ($vars as $var) {
        $var = explode("=", $var);
        if ($var[0] != 'vrs') {
            $_GET[$var[0]] = $var[1];
            $_REQUEST[$var[0]] = $var[1];
        }
    }
}

$tempDir = $prog_path . "/data/temp";
if (!is_dir($tempDir))
    mkdir($tempDir, true);

if (isset($_POST['option']['HQCstamp']) or isset($_GET['HQCstamp']))
    $HQCstamp = true;

if (isset($_POST['option']['HQCsignature']) or isset($_GET['HQCsignature']))
    $HQCsignature = true;
extract($_REQUEST);
require_once("$prog_path/tools/pdf/hcp_pdf.inc.php");
$templatePHP = array();
$office_options = array();
$templatePHP['firstPageTopMargin'] = 70;
if (isset($_REQUEST['tp']))
    $tp = $_REQUEST['tp'];
elseif (isset($_GET['tp']))
    $tp = $_GET['tp'];

if ((isset($_REQUEST['nr']) && isset($_REQUEST['sub_act'])) or (isset($_GET['nr']) and !isset($_POST['act']))) {
    $_POST = $_POST + $amdb->get_row("SELECT * FROM certificates_{$_REQUEST['tp']} WHERE nr='$_REQUEST[nr]'");
    if (isset($_GET['ref']) && $_GET['ref'] == 'home' && !isset($_GET['flag']))
        $_POST['print_flag'] = 0;
    $weight_gross = explode('.', $_POST['weight_gross']);

    if (is_array($weight_gross)) {
        $_POST['weight_gross'] = $weight_gross[0];
        $_POST['weight_gross_gram'] = $weight_gross[1];
    }

    $weight_net = explode('.', $_POST['weight_net']);
    if (is_array($weight_net)) {
        $_POST['weight_net'] = $weight_net[0];
        $_POST['weight_net_gram'] = $weight_net[1];
    }

    if (trim($_POST['options']) != '' and is_array(json_decode(str_replace("\r\n", '\n', $_POST['options']), true)))
        $_POST['option'] = json_decode(str_replace("\r\n", ', ', $_POST['options']), true);
    foreach ($_POST as $key => $value) {
        $_REQUEST[$key] = $value;
    }
    if (is_string($_POST['products']) && is_array(unserialize($_POST['products'])))
        $_POST['products'] = unserialize($_POST['products']);

    extract($_REQUEST);
}

if (isset($_POST['option']))
    $option = $_POST['option'];

if (isset($_REQUEST['ref']) && $_REQUEST['ref'] == 'home') {
    if (!isset($_GET['HQCstamp']) && isset($option['HQCstamp']))
        unset($option['HQCstamp']);
    if (!isset($_GET['HQCsignature']) && isset($option['HQCsignature']))
        unset($option['HQCsignature']);
}

if (isset($_POST['popup_act'])) {
    if (!isset($HQCstamp) && isset($option['HQCstamp']))
        unset($option['HQCstamp']);
    if (!isset($HQCsignature) && isset($option['HQCsignature']))
        unset($option['HQCsignature']);
}


if (isset($_GET['issue']) && trim($_GET['issue']) != '') {
    if ($_GET['act'] != 'preview')
        $amdb->update("certificates_{$tp}", array('issue_date', $_GET['issue']), "nr='$nr'");
    $_POST['issue_date'] = $_GET['issue'];
} elseif (!isset($_POST) || !isset($_POST['issue_date'])) {
    $_POST['issue_date'] = date("d/m/Y");
}

if ($_POST['weight_gross_gram'] == '')
    $_POST['weight_gross_gram'] = '00';

if (isset($_POST['weight_gross']) && trim($_POST['weight_gross_gram']) != '')
    $_POST['weight_gross'] .= ',' . $_POST['weight_gross_gram'] . ' KG';


if ($_POST['weight_net_gram'] == '')
    $_POST['weight_net_gram'] = '00';


if (isset($_POST['weight_net']) && trim($_POST['weight_net_gram']) != '')
    $_POST['weight_net'] .= ',' . $_POST['weight_net_gram'] . ' KG';

$pdf_data = $_POST;

if (isset($_GET['admin_remarks'])) {
    $pdf_data['admin_remarks'] = '<br/><table style="width: ' . (isset($option['landscape']) ? '26.5' : '18') . 'cm;" border="1" cellspacing="0" cellpadding="10"><tr><td>' . str_replace("\n", "\r\n", urldecode($_GET['admin_remarks'])) . '</td></tr></table>';
} else {
    $pdf_data['admin_remarks'] = '';
}
$theTemplate = '';
if (isset($_GET['offid'])) {
    $_POST['offid'] = $_GET['offid'];
}

if (isset($_POST['offid'])) {

    $sql = "SELECT *,offices.certificate_address as offices_certificate_address,offices.shipment_address as offices_shipment_address FROM offices JOIN hqc_office_certificate_data ON hqc_office_certificate_data.offid = offices.offid where offices.offid = '$_POST[offid]'";

    $office = $amdb->get_row($sql);
    $office_names = json_decode($office['office_names'], true);
    $office['company_name_english'] = $office_names['english_' . $tp];
    $office['office_name_english'] = $office_names['english_' . $tp];

    $templates_path = $prog_path . "/data/offices/0/templates";
    $template_content = file_get_contents($templates_path . '/slaughtering.tmpl.php');
    $theTemplate = trim(preg_replace('/<style(.*)\/style>/s', '', $template_content));
    $template['style'] = preg_match('/<style(.*)\/style>/s', $template_content, $styleMatch) ? $styleMatch[0] : '';

    if (isset($office['office_names']) and trim($office['office_names']) != '' and is_array(json_decode($office['office_names'], true))) {
        $office_names = json_decode($office['office_names'], true);
        $office['company_name_english'] = $office_names['office_name_english'];
        $office['company_name_arabic'] = $office_names['office_name_arabic'];
        $office['office_name_english'] = $office_names['english_' . $tp];
        $office['office_name_arabic'] = $office_names['arabic_' . $tp];
    }
    if (isset($office['certificate_number']) && is_array(json_decode($office['certificate_number'], true))) {
        $pdf_data['certificate_number'] = json_decode($office['certificate_number'], true);
        $pdf_data['certificate_number'] = $pdf_data['certificate_number'][$tp] ?? '';
    }

    $signature  = '';

    if (isset($office['signatories']) && trim($office['signatories']) != '' and is_array(json_decode($office['signatories'], true))) {
        $signatories = json_decode($office['signatories'], true);
        if (isset($signatories['shipment_certificate']))
            $signature = $signatories['shipment_certificate'];
    }

    $theTemplate = str_replace(
        array('[office_name_english]', '[office_name_arabic]', '[office_signature]'),
        array($office['office_name_english'], $office['office_name_arabic'], $signature),
        $theTemplate
    );

    $templateHeader = preg_match('/\[header](.*)\[\/header]/s', $theTemplate, $tempHeader) ? trim($tempHeader[1]) : '';
    $theTemplate = trim(preg_replace('/\[header](.*)\[\/header]/s', '', $theTemplate));
    $templateHeader = $template['style'] . $templateHeader;

    if (trim($office['options']) != '' and is_array(json_decode($office['options'], true))) {
        $office_options = json_decode($office['options'], true);
    }
    if (isset($office['certificate_address']) and trim($office['certificate_address']) != '' and is_array(json_decode(str_replace(array("\r\n", "\n"), '\n', $office['certificate_address']), true))) {
        $certificate_address = json_decode($office['certificate_address'], true);
        $office['shipment_address'] = '<div style="text-align:center;font-size:10px;line-height:12px;">' . $certificate_address['shipment'] . "   <b>Certificate No.: [certificate_nr] &nbsp; &nbsp; F 8.26.c / VERSION 1 &nbsp; &nbsp; Page [this_page]/[total_pages]</b></div>";
    }
}

//set dates
if (isset($pdf_data['slaughtering_date'])) {
    $pdf_data['slaughter_day'] = date('d', strtotime($pdf_data['slaughtering_date']));
    $pdf_data['slaughter_month'] = date('m', strtotime($pdf_data['slaughtering_date']));
    $pdf_data['slaughter_year'] = date('Y', strtotime($pdf_data['slaughtering_date']));
}
if (isset($pdf_data['production_date'])) {
    $pdf_data['production_day'] = date('d', strtotime($pdf_data['production_date']));
    $pdf_data['production_month'] = date('m', strtotime($pdf_data['production_date']));
    $pdf_data['production_year'] = date('Y', strtotime($pdf_data['production_date']));
}
if (isset($pdf_data['expiry_date'])) {
    $pdf_data['expiry_day'] = date('d', strtotime($pdf_data['expiry_date']));
    $pdf_data['expiry_month'] = date('m', strtotime($pdf_data['expiry_date']));
    $pdf_data['expiry_year'] = date('Y', strtotime($pdf_data['expiry_date']));
}

if (!isset($option) and isset($_POST['options']))
    $option = $_POST['options'];

function print_header_footer($whr, $pageN = 0)
{
    global $templateHeader, $pdf, $office, $pdf_data;
    if ($whr == 'header') {
        $pdf->SetY(10);
        $pdf->SetX(15);
        $pdf->writeHTML($templateHeader);
        $pdf->SetX(15);
    } elseif ($whr == 'footer') {
        $pdf->SetY(-15);
        $shipment_address = str_replace(array('[certificate_nr]', '[this_page]', '[total_pages]'), array($pdf_data['certificate_nr'], $pageN, $pdf->getAliasNbPages()), $office['shipment_address']);
        $pdf->writeHTML($shipment_address);
    }
}

function insertImages($imgs = 'images')
{
    global $pdf, $prog_path;
    if (is_array(json_decode($imgs, true))) {
        $imgs = json_decode($imgs, true);
        $optionImages = ["main_signature", "main_stempel", "main_halal_stempel"];
        if (isset($_POST['certificate_option']) and isset($_POST['certificate_option']['main_eiaci'])) {
            $optionImages[] = 'main_eiaci';
        }

        $thisY = $pdf->getY();
        foreach ($imgs as $image => $setXY) {
            [$imgX, $imgY, $w] = explode(',', $setXY);
            if (in_array($image, $optionImages)) {
                $image = substr($image, strpos($image, '_') + 1, strlen($image));
                $file_path = $prog_path . '/data/offices/0/images/';
                foreach (['.svg', '.png', '.jpg', '.jpeg'] as $ext) {
                    $file = $file_path . $image . $ext;
                    if (file_exists($file)) {
                        $x = $imgX;
                        if (strstr($imgY, '+')) {
                            $y = $pdf->getY() + str_replace('+', '', $imgY);
                        } elseif (strstr($imgX, '-')) {
                            $y = $pdf->getY() - str_replace('-', '', $imgY);
                        } else {
                            $y = $imgY;
                        }
                        $pdf->Image($file, $x, $y, $w, $h = '', $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
                    }
                }
            }
        }
    }
}

$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->SetPrintHeader(true);
$pdf->SetPrintFooter(true);
print_header_footer('header');
if ($protected_pdf = get_option('protected_pdf')) {
    $protected_pdf = json_decode($protected_pdf, true);
    if (isset($protected_pdf['batch']) && isset($protected_pdf['protect']) && trim($protected_pdf['password']) != '') {
        $pdf->SetProtection(array('edit', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble'), '', $protected_pdf['password'], 0, null);
    }
}
if ($total = get_option($tp . '_crtNr', $_POST['offid']))
    $total++;
else
    $total = 1;

if (!isset($act))
    $act = 'show';

if (isset($_REQUEST['keepOldCrtNumber']) && isset($_REQUEST['certificate_nr'])) {
    $pdf_data['certificate_nr'] = $_REQUEST['certificate_nr'];
} else {
    if ((isset($_REQUEST['ref']) && $_REQUEST['ref'] == 'home') or !isset($pdf_data['certificate_nr']) or trim($pdf_data['certificate_nr']) == '') {
        $certificate_nr = $pdf_data['certificate_number'];
        $pdf_data['certificate_nr'] =  $certificate_nr['prefix'].str_pad($total, $certificate_nr['length'], "0", STR_PAD_LEFT);
    }
}
$qr = $tp . '-' . $pdf_data['certificate_nr'] . '-' . time();


if (!isset($act))
    $act = 'show';

$office_address = str_replace("\r\n", "<br/>", (trim($office['shipment_address']) != '') ? trim($office['shipment_address']) : trim($office['certificate_address']));
$address_pos =
    isset($templatePHP['address-position']) ? $templatePHP['address-position'] : 280 - (count(explode('<br/>', $office_address)) * 4);
$office_address = '<div style="font-size:14px;line-height:16px;text-align:center;">' . $office_address . '</div>';

$_POST['shc'] = 0;
$shc_logo =  '/data/images/shc.png';
if (isset($_REQUEST['shc']) or (isset($_POST['print_shc']) && $_POST['print_shc'] == 1)) {

    if (file_exists($prog_path . $shc_logo)) {
        //	$pdf->Image($file = $prog_path . $shc_logo, 162, 270, $w = '40', $h = '');
        $_POST['shc'] = 1;
    }
}

$hqc_images_path = dirname($prog_path) . "/hqc-images";

$HQCStempel = $hqc_images_path . "/stempel.png";
$HQCshipment_signature = $hqc_images_path . "/shipment-signature.png";

if (isset($option['HQCstamp'])) {
    $pdf->Image($file = $HQCStempel, 65, 220, $w = '45', $h = '');
}

if (isset($option['HQCsignature'])) {
    $pdf->Image($file = $HQCshipment_signature, 120, 233, $w = '60', $h = '');
}

if ($act == "preview") {
    if ($tp == 'sa')
        $pdf->SetY(42);
    else
        $pdf->SetY(46);
    $pdf->writeHTML($header);
    showExample();
    // $pdf->SetY($address_pos);
    //  $pdf->writeHTML($office_address);
} elseif (isset($_REQUEST['act']) and $_REQUEST['act'] == "checkCertificate") {
    if ($tp == 'sa')
        $pdf->SetY(42);
    else
        $pdf->SetY(46);
    $pdf->writeHTML($header);
    showWatermark('authentic.svg');
    // $pdf->SetY($address_pos);
    //  $pdf->writeHTML($office_address);
} else {
    if ($_POST['offid'] != '0' or $_SESSION['user_type'] == 'hqc_office') {
        if ($tp == 'sa')
            $pdf->SetY(42);
        else
            $pdf->SetY(46);
        $pdf->writeHTML($header);
        if ($_SESSION['user_type'] == 'client')
            showWatermark('draft.svg');
        // $pdf->SetY($address_pos);
        // $pdf->writeHTML($office_address);
    }

    if ($act == 'print' or $act == 'authorise') {
        $done = 'n';
        $certificate_nr = $pdf_data['certificate_nr'];
        if ($act == 'authorise') {
            $hcd_process = 'Authorised on: ' . date('d/m/Y');
            $printed_on = '';
        } else {
            if ($_SESSION['user_type'] == 'hqc_office')
                $done = 'y';
            $hcd_process = 'Sent on: ' . date('d/m/Y');
            $printed_on = date('d/m/Y');
        }
        $admin_remarks = trim($pdf_data['admin_remarks']);
        if ($_SESSION['user_type'] == 'client')
            $status = 'draft';
        else
            $status = 'active';
        $handled_by = json_encode($_SESSION['user']);
        $amdb->query("update certificates_$tp set certificate_nr='$certificate_nr', printed_on = '$printed_on',hcd_process='$hcd_process',qr='$qr', admin_remarks='$admin_remarks', done='$done',handled_by='$handled_by',status='$status' WHERE nr='$nr'");
        update_option($tp . '_crtNr', $total, $_POST['offid']);
    }
}
$style = array('border' => false, 'padding' => 0, 'fgcolor' => array(0, 0, 0), 'bgcolor' => false);
$qrSize = 22;
if ($tp == 'sa') {
    $pdf->write2DBarcode("https://ca.iidc.eu/?crtnr={$pdf_data['certificate_nr']}", 'QRCODE,H', 20, 30, $qrSize, $qrSize, $style, 'N');
    //QR box
    $pdf->Rect(164, 9, $qrSize + 2, $qrSize + 2, 2);
} else {
    $pdf->write2DBarcode("https://ca.iidc.eu/?crtnr={$pdf_data['certificate_nr']}", 'QRCODE,H', 16, 30, $qrSize, $qrSize, $style, 'N');
    //QR box
    $pdf->Rect(15, 29, $qrSize + 2, $qrSize + 2, 2);
}

if ($company = $amdb->get_row("SELECT * FROM companies where companies.clid = '$_REQUEST[clid]'")) {
    $pdf_data['company_name'] = $company['company_name'];
    $pdf_data['company_address'] = "
$company[street1]<br/>
$company[zip1] $company[city1], $company[country1]";
    if (trim($company['ec_number']) != "")
        $pdf_data['company_address'] .= "<br/>EC No.: $company[ec_number]";
}
if (isset($importer)) {
    if ($importer == '0') {
        if (isset($option) and isset($option['importer']))
            $pdf_data['importer'] = trim(str_replace("\r\n", ", ", $option['importer']));
        else
            $pdf_data['importer'] = '';
    } else {
        if ($row = $amdb->get_row("SELECT * FROM companies WHERE clid='$importer'")) {
            $importer = "$row[company_name]\n";
            if ($row['street1'])
                $importer .= $row['street1'];
            if ($row['zip1'])
                $importer .= ", " . $row['zip1'];
            if ($row['city1'])
                $importer .= " " . $row['city1'];
            if ($row['country1'])
                $importer .= " " . $row['country1'];
            $pdf_data['importer'] = trim($importer);
            $pdf_data['CRN'] = $row['CRN'];
        }
    }
}
if (isset($producer)) {
    if ($producer == '0') {
        if (isset($option) and isset($option['producer']))
            $pdf_data['producer'] = trim(str_replace("\r\n", ", ", $option['producer']));
        else
            $pdf_data['producer'] = '';
    } else {
        if ($row = $amdb->get_row("SELECT * FROM companies WHERE clid='$producer'")) {
            $producer = "$row[company_name]\n";
            if ($row['street1'])
                $producer .= $row['street1'];
            if ($row['zip1'])
                $producer .= ", " . $row['zip1'];
            if ($row['city1'])
                $producer .= " " . $row['city1'];
            if ($row['country1'])
                $producer .= ", " . $row['country1'];
            $pdf_data['producer'] = trim($producer);
        }
    }
}
if (isset($exporter)) {
    if ($exporter == '0') {
        if (isset($option) and isset($option['exporter']))
            $pdf_data['exporter'] = trim(str_replace("\r\n", ", ", $option['exporter']));
        else
            $pdf_data['exporter'] = '';
    } else {
        if ($row = $amdb->get_row("SELECT * FROM companies WHERE clid='$exporter'")) {
            $exporter = "$row[company_name]\n";
            if ($row['street1'])
                $exporter .= $row['street1'];
            if ($row['zip1'])
                $exporter .= ", " . $row['zip1'];
            if ($row['city1'])
                $exporter .= " " . $row['city1'];
            if ($row['country1'])
                $exporter .= ", " . $row['country1'];
            $pdf_data['exporter'] = trim($exporter);
        }
    }
}

if (!is_array($pdf_data['products']))
    $pdf_data['products'] = str_replace("\t", " ", $pdf_data['products']);
$pdf_data['first_product'] = '';
//find first item in the products array to get the keys
if (count($pdf_data['products']) > 0) {
    $first_product = reset($pdf_data['products']);
    if (is_array($first_product)) {
        $pdf_data['first_product'] = $first_product['description'];

        foreach ($first_product as $key => $value) {
            if (trim($value == '') && $key != 'description' && isset($option[$key])) {
                unset($option[$key]);
            }
        }
    }
}
//handling annex products
$pdf_data['products_list'] = '';

if (isset($option['products-head-font-size']))
    $headerFontSize = intval($option['products-head-font-size']);
else
    $headerFontSize = 13;

if (isset($option['products-font-size']))
    $productFontSize = intval($option['products-font-size']);
else
    $productFontSize = 12;
$artNt_width = isset($option['artNt']['width']) ? str_replace(',', '.', $option['artNt']['width']) : '5';
$description_width = isset($option['description']['width']) ? str_replace(',', '.', $option['description']['width']) : '8';
$quantity_width = isset($option['quantity']['width']) ? str_replace(',', '.', $option['quantity']['width']) : '5';

$productInputs = array();
foreach ($option as $k => $val) {
    if (isset($val['width']) && isset($val['english'])) {
        $productInputs[$k] = array(
            'title' => $val['english'],
            'width' => $val['width'] . 'cm'
        );
    }
}

//TODO: update new system (unserialize quality instead of json_decode)

if ((is_array($pdf_data['products']) or is_array(json_decode($pdf_data['products'], true)) or is_array(unserialize($pdf_data['products'], ['allowed_classes' => false]))) && strstr($theTemplate, '[products_list]')) {
    //  $theTemplate = remove_tag("quality", $theTemplate, 'tr');
    $productTemplate = '<table class="productsList" style="padding: 5px;"><thead><tr><th style="width:1.5cm">No.</th><th style="width:10cm">Description of goods</th><th style="width:3cm">Net Kg</th><th style="width:3.5cm">Gross kg</th></tr></thead><tbody>[products_list]</tbody></table>';

    if (!is_array($pdf_data['products'])) {

        if (is_array(json_decode($pdf_data['products'], true)))
            $products = json_decode($pdf_data['products'], true);
        elseif (is_array(unserialize($pdf_data['products'], ['allowed_classes' => false])))
            $products = unserialize($pdf_data['products'], ['allowed_classes' => false]);
    } else {
        $products = $pdf_data['products'];
    }
    $itemsCount = 1;
    $totalKg = 0;
    foreach ($products as $qualityKey => $qualityValue) {
        $pdf_data['products_list'] .= '<tr><td style="width:1.5cm">' . $itemsCount . '</td>';
        foreach ($qualityValue as $key => $value) {

            // if (isset($productInputs[$key])) {
                if ($key != 'description' && $key != 'quantity') {
                    continue;
                }
                if ($key == 'description')
                    $width  = '10cm';
                else
                    $width = '3cm';
                if ($key == 'quantity') {
                    $value = preg_replace('/[^0-9.,]/', '', $value);
                    if (strstr($value, ','))
                        $value = str_replace(',', '.', $value);
                    $value = floatval($value);

                    if (!is_numeric($value)) {
                        $value = '0.00';
                    }
                    $totalKg += floatval($value);
                    $value = $value . ' KG';
                }
                $pdf_data['products_list'] .= '<td style="width:' . $width . '"><span style="font-family:arial">' . $value . '</span></td>';
            // }
        };
        if ($itemsCount == 1)
            $pdf_data['products_list'] .= '<td style="width:3.5cm;vertical-align:middle" align="middle" rowspan="[rowspan]">[grossKg]</td>';
        $pdf_data['products_list'] .= '</tr>';
        $itemsCount++;
    }
    $pdf_data['products_list'] = str_replace('[rowspan]', $itemsCount - 1, $pdf_data['products_list']);
    $pdf_data['products_list'] = str_replace('[grossKg]', number_format($totalKg, 2, ',', '') . ' KG', $pdf_data['products_list']);
    $productTemplate = str_replace('[products_list]', $pdf_data['products_list'], $productTemplate);
    $theTemplate  = str_replace('[products_list]', $productTemplate, $theTemplate);
};

if (isset($pdf_data['remarks']) && trim($pdf_data['remarks']) != '') {
    $pdf_data['remarks'] = "<br/><table style=\"width:" . (isset($option['landscape']) ? '26.5' : '18') . "cm;\" border=\"1\" cellspacing=\"0\" cellpadding=\"10\"><tr><td style=\"text-align:center\"><b>Remarks or Notes:</b><p style=\"text-align:left\">" . str_replace("\n", "<br/>", trim($pdf_data['remarks']));
    $theTemplate = str_replace('[remarks]', $pdf_data['remarks'], $theTemplate) . "</p></td></tr></table>";
}

if (count($pdf_data) > 0) {
    foreach ($pdf_data as $key => $value) {
        if (!is_array($value)) {
            $value = str_replace(array('<', '>'), array('&lt;', '&gt;'), $value);
            $theTemplate = str_replace('[' . $key . ']', str_replace("\n", "<br/>", '<span style="font-family:freeserif">' . $value . '</span>'), $theTemplate);
        }
    }
}

if (trim($pdf_data['hcd_nr']) == '')
    $theTemplate = remove_tag('hcd_nr', $theTemplate, 'tr');

if (trim($theTemplate) != '') {
    $parts = preg_match_all('/\[pdf(.*)\]/U', $theTemplate, $thePatrs);
    foreach ($thePatrs[0] as $macth) {
        $theTemplate  = str_replace($macth, "<brkPoint>" . $macth . "<brkPoint>", $theTemplate);
    };
    $pdfParts = explode('<brkPoint>', $theTemplate);
    ob_start();
    echo $template['style'];
    $pdf->writeHTML(ob_get_contents());
    ob_end_clean();
    $textBoxs = array();
    foreach ($pdfParts as $key => $part) {
        if (strstr($part, '[pdf ')) {
            preg_match('/\[pdf (.*)\((.*)\)\]/', $part, $pdfMatch);

            if ($pdfMatch[1] == 'textBox') {
                $textBox = $pdfParts[$key + 1];
                // Place the text at the current X and Y position using writeHTMLCell
                if (count($textBoxs) > 0) {
                    $thisY = $textBoxs[$key - 1];
                    $pdf->setRTL(true);
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->writeHTMLCell(85, 0, $pdf->GetX(), $thisY, $textBox, 0, 1, false, true, 'R', false);
                    if (isset($textBoxs['old']) && $textBoxs['old'] > $thisY) {
                        $pdf->setY($textBoxs['old'] - 2);
                    }
                } else {
                    $thisY = $pdf->getY() - 2;
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->writeHTMLCell(95, 0, $pdf->GetX(), $thisY, $textBox, 0, 1, false, true, 'J', false);
                    $textBoxs['old'] = $pdf->getY();
                }
                $textBoxs[$key + 1] = $thisY;
                $pdf->setRTL(false);
            }
            if (trim($pdfMatch[1]) == 'images') {
                insertImages($pdfMatch[2]);
            } elseif (trim($pdfMatch[1]) == 'setY') {
                $y = $pdfMatch[2];
                if (strstr($y, '+'))
                    $y = $pdf->getY() + str_replace('+', '', $y);
                $pdf->setY($y);
            } elseif (trim($pdfMatch[1]) == 'setX') {
                $x = $pdfMatch[2];
                if (strstr($x, '+'))
                    $x = $pdf->getX() + str_replace('+', '', $x);
                $pdf->setX($x);
            } elseif (trim($pdfMatch[1]) == 'addPage') {
                if (isset($option['landscape'])) {
                    $marginTop = 15;
                    $marginBottom = 15;
                } else {
                    $marginTop = 25;
                    $marginBottom = 25;
                }
                if (trim($pdfMatch[2]) != '') {
                    $margin = explode(',', $pdfMatch[2]);
                    if (isset($margin[0]))
                        $marginTop = $margin[0];
                    if (isset($margin[1]))
                        $marginBottom = $margin[1];
                }
                $pdf->SetAutoPageBreak(TRUE, $marginBottom);
                $pdf->SetMargins(15, $marginTop, 15);
                if (isset($option['landscape'])) {
                    $pdf->AddPage('L');
                } else {
                    $pdf->AddPage();
                }
            } elseif (trim($pdfMatch[1]) == 'setMargins') {
                $marginTop = 25;
                $marginBottom = 25;
                if (trim($pdfMatch[2]) != '') {
                    $margin = explode(',', $pdfMatch[2]);
                    if (isset($margin[0])) {
                        $pdf->setY($margin[0]);
                        $marginTop = $margin[0];
                    }
                    if (isset($margin[1]))
                        $marginBottom = $margin[1];
                }
                $pdf->SetAutoPageBreak(TRUE, $marginBottom);
                $pdf->SetMargins(15, $marginTop, 15);
            } elseif (trim($pdfMatch[1]) == 'lastPage') {
                $pdf->SetAutoPageBreak(TRUE, 0);
                if (isset($pdfMatch[2]) && $pdf->getY() > $pdfMatch[2]) {
                    $pdf->AddPage();
                } elseif (trim($pdfMatch[1]) == 'footer') {
                }
                insertImages('annex-images');
            } elseif (trim($pdfMatch[1]) == 'setRTL') {
                if ($pdfMatch[2] == '1') {
                    $pdf->setRTL(true);
                } else {
                    $pdf->setRTL(false);
                }
            }
        } elseif (trim($part) != '' && !isset($textBoxs[$key])) {
            ob_start();
            echo $template['style'];
            $part = str_replace(array('&amp;', '&lsquo;', '&rsquo;'), array('&', '‘', '’'), $part);
            echo trim($part);
            if (preg_match('/[\p{Cyrillic}]/u', $part))
                $pdf->SetFont('freeserif');
            $pdf->writeHTML(ob_get_contents());
            ob_end_clean();
        }
    }
}

if (isset($option['HQCstamp'])) {
    $pdf->Image($file = $HQCStempel, 65, $pdf->getY() + 40, $w = '45', $h = '');
}

if (isset($option['HQCsignature'])) {
    $pdf->Image($file = $HQCshipment_signature, 120, $pdf->getY() + 40, $w = '60', $h = '');
}

if ($_POST['shc'] == 1) {
    if (isset($option['landscape']))
        $pdf->Image($file = $prog_path . $shc_logo, 200, 170, $w = '60', $h = '');
    else
        $pdf->Image($file = $prog_path . $shc_logo, 120, 250, $w = '60', $h = '');
}
$QRY = $pdf->getY() - 2;
$pdf->SetAutoPageBreak(TRUE, 0);

//TODO: add table auto number (nr) to the qr code
if (isset($option['landscape']))
    $qrLeft = 140;
else
    $qrLeft = 90;
$pdf->write2DBarcode("https://ca.iidc.eu/?crtnr={$pdf_data['certificate_nr']}", 'QRCODE,H', $qrLeft, $QRY + 3, $qrSize, $qrSize, $style, 'N');
$pdf->Rect($qrLeft - 1, $QRY + 2, $qrSize + 2, $qrSize + 2, 2);
if ((isset($act) and $act == 'preview') or $act == 'authorise') {
}
if (isset($attachment) and $attachment != '' or isset($annex) and trim($annex) != "") {
    if (isset($option['landscape'])) {
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage('L');
    } else {
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->AddPage();
    }

    if (isset($annex) and trim($annex) != "") {
        $annex = "<table><tr><td><b>ANNEX: $pdf_data[certificate_nr]</b></td></tr><tr><td><br/><br/>" . str_replace("\n", "<br/>", $annex) . "</td></tr></table>";
        $pdf->writeHTML($annex, true, false, true, false);
    }
    if (isset($attachment) and trim($attachment) != "") {
        $theAttachment = "<table><tr><td><b>ATTACHMENT: $pdf_data[certificate_nr]</b></td></tr><tr><td><br/><br/>" . str_replace(array('"', "\n"), array("\"", "<br/>"), $attachment) . "</td></tr></table>";
        $pdf->writeHTML($theAttachment, true, false, true, false);
    }
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == 'checkCertificate') {
    $pdf->Output($_REQUEST['fileName'], 'I');
    exit();
}

$pdf_data['url'] = $office['office_country'] . "/" . $office['certificate_prefix'] . "/" . str_pad($_REQUEST['clid'], 5, "0", STR_PAD_LEFT) . "/$pdf_data[certificate_nr].pdf";
$certFile = $hcp_path . "/client_data/certificates/" . $pdf_data['url'];
if (!is_dir(dirname($certFile)))
    mkdir(dirname($certFile), 0777, true);
// $pdf->Output('shipment_certificate.pdf', 'I');
// exit();
if (isset($_REQUEST['act']) and ($_REQUEST['act'] == 'print' or $_REQUEST['act'] == 'authorise')) {

    $pdf->Output($certFile, 'F');
    if (file_exists($certFile)) {
        if (isset($_SESSION['offid']) and $_SESSION['offid'] != '0')
            $_POST['offid'] = $_SESSION['offid'];
        $options = encode_json($option);

        $amdb->query("UPDATE certificates_{$tp} SET version = 1,print_flag = '$print_flag',print_eiaci='$print_eiaci',print_shc = '$print_shc' ,print_hak = '$print_hak', options='$options', url = '$pdf_data[url]', tmplid = '$_POST[offid]' WHERE nr='$_REQUEST[nr]'");

        if (isset($_REQUEST['sub_act']) && ($_REQUEST['sub_act'] == 'email')) {
            include "../actions/email_shipment_certificate.php";
            exit();
        }
        if ($act == 'print')
            header("location: $prog_www/client_data/certificates/$pdf_data[url]?act=print");
        else
            $amdb->post_results('', 'reload');
    }
    exit();
}

$pdf->setY(80);

$pdf->Output($pdf_data['certificate_nr'].'.pdf', 'I');