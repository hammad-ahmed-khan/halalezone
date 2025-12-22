<?php
session_start();
use mikehaertl\pdftk\Pdf;
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include "../../config/paths.inc.php";
if (!defined("__HQC__") and !defined("_HQC_")) {
    exit();
};

require_once('../../pdf/pdftk/vendor/autoload.php');

$passwords = array('27582758', '7913', '@Denhaag25!!'); // Replace with your actual password

function remove_protection($file)
{
    global $passwords;
    foreach ($passwords as $password) {
        $pdf = new Pdf;
        if ($pdf->addFile($file, null, $password)) {
            if (isset($_GET['get']) && $_GET['get'] == 'annex')
                $pdf->cat(2, 'end');
            else
                $pdf->cat(1);
            // $textToAdd = 'This is the added text.';
            // $pdf->stamp();
            $pdf->setPassword($password);
            $pdf->send($file, true);
            exit();
        }
    }
}

// if($certificate = $amdb->get_row("SELECT certificate_nr,url FROM $tbl[prefix]_halal_certificates WHERE certificate_nr='$_GET[crtnr]'")){
//     $file = $hcp_path  . "/client_data/certificates/" . $certificate['url'];
//     // if(file_exists($file)){
//     // remove_protection($file);
//     // return;
//     // }
//     $pdfFile = $certificate['certificate_nr'];
//     $addPage = false;
//     require_once("$prog_path/pdf/tcpdf/hcp_pdf.inc.php");
//     if ($protected_pdf = get_option('protected_pdf')) {
//         $protected_pdf = json_decode($protected_pdf, true);
//         if (isset($protected_pdf['annual']) && isset($protected_pdf['protect']) && trim($protected_pdf['password']) != '') {
//              $pdf->SetProtection(['edit', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble'],  $protected_pdf['password'],null, 0, null);
//         }
//     }
//     $pageCount = $pdf->setSourceFile($file);

//     for ($i = 2; $i <= $pageCount; $i++) {
//         $pdf->AddPage();
//         $pdf->useTemplate($pdf->importPage($i));
//     }
//     $pdf->Output(str_replace('.pdf', '-' . $_GET['tm'] . '.pdf', $file), 'F');
//     $pdf->Output($pdfFile,'I');
//     exit();
// }

$tempDir = $prog_path . "/data/temp";
if (!is_dir($tempDir))
    mkdir($tempDir, true);
extract($_REQUEST);

$templatePHP = array();
$templatePHP['firstPageTopMargin'] = 70;
$tempFile = $tempDir . "/annual.php";
$theTemplate = '';

$certificate_content = array();
$office_address = array();

if (isset($_GET['crtnr'])) {

    if ($content = $amdb->get_row("SELECT * FROM hqc_versions WHERE item_table='acms_halal_certificates' AND tuid='$_REQUEST[tm]'")) {
        if (isset($_GET['test'])) {
            if (trim($content['item_url']) != '') {
                exit();
                $item_url = $hcp_path  . "/client_data/certificates/" . $content['item_url'];

                if (file_exists($item_url)) {
                    require_once("$prog_path/pdf/tcpdf/hcp_pdf.inc.php");
                    echo $item_url;
                    $pdf->SetProtection(array('edit', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble'), '', '27582758', 0, null);
                      $page = $pdf->setSourceFile($item_url);
                    echo "1";
                    $totalPages = $pdf->setSourceFile($item_url);
                    exit();
                    $pdf->AddPage();
                    $pdf->writeHTML($content, true, false, true, false, '');
                    $pdf->Output('certificate.pdf', 'I');
                    // echo $item_url;
                    exit();
                }
            }
        }

        $certificate_url = $content['item_url'];
        $certificate_content = unserialize($content['item_content']);
        $pdf_data = $certificate_content;
        $office_address = $amdb->get_row("SELECT * FROM offices WHERE offid = $certificate_content[office_address]");
        $pdf_data['annex_options'] = decode_json($pdf_data['annex_options']);
        $pdf_data['signatories'] = decode_json($pdf_data['signatories']);
        $pdf_data['main_director'] = $pdf_data['signatories']['main_director'][0];
        $pdf_data['date_of_issue'] = date('d/m/Y', $pdf_data['date_of_issue']);
        $pdf_data['date_of_expiry'] = date('d/m/Y', $pdf_data['date_of_expiry']);
        $pdf_data['options'] = decode_json($pdf_data['options']);
        if (isset($pdf_data['annex_options']['annex_footer']))
            $pdf_data['annex_footer'] = $pdf_data['annex_options']['annex_footer'];
        else
            $pdf_data['annex_footer']  = '';

        if ($client_products = $amdb->get_results("SELECT * FROM acms_hdcs_products WHERE clid = '$pdf_data[clid]'")) {
            $certificate_products = explode(',', $certificate_content['products']);
            $certificate_content['products'] = array();
            foreach ($client_products as $client_product) {
                if (in_array($client_product['prdid'], $certificate_products))
                    $certificate_content['products'][] = $client_product;
            }
        }
    } else {
        $certificate = $amdb->get_row("SELECT certificate_content,url FROM $tbl[prefix]_halal_certificates WHERE certificate_nr='$_GET[crtnr]'");
        $certificate_url = $certificate['url'];

        if (trim($certificate['certificate_content']) != '') {
            $certificate_content = str_replace(array("\r\n", "\n\r", "\n", "\r"), '\n', $certificate['certificate_content']);
            if ($_SERVER['REMOTE_ADDR'] == '169.254.133.3') {
                //  echo($certificate_content);
            };

            if (is_array(json_decode($certificate_content, true))) {
                $certificate_content = json_decode($certificate_content, true);

                if (isset($_GET['tm']) && isset($certificate_content[$_GET['tm']])) {
                    $certificate_content = $certificate_content[$_GET['tm']];
                } else {
                    $certificate_content = end($certificate_content);
                }
            }
        }
        $pdf_data = $certificate_content['data'];
        $office_address = $certificate_content['office_address'];
    }

    $template =  $amdb->get_row("SELECT * FROM office_certificate_templates WHERE offid='$office_address[offid]' and type='annual'");

    $pdf_data['office_address'] = str_replace(array("\r\n\r\n", "\r\n"), array("<div style=\"line-height:15px\"></div>", "<br/>"), $office_address['certificate_address']);

    $theTemplate = $template['content'];
    $office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$pdf_data[offid]'");
    $products = $certificate_content['products'];
    $certificate_option = $pdf_data['options'];
    $templatePHP = json_decode($template['php'], true);
    $template['style'] = $template['style'];
} else {
    exit();
}

$certificateTemplate = $prog_path . '/data/offices/templates/certificate-annual.pdf';
$theTemplate = str_replace(array('<p>[pdf ', ')]</p>', '&nbsp;'), array('[pdf ', ')]', ' '), $theTemplate);
$templateParts = explode('[annexPage]', $theTemplate);

if (isset($_GET['get']) and $_GET['get'] == 'annex') {
    $annexPageOnly = true;
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
} else {
    $theTemplate = $templateParts[0];
    if (isset($certificate_option['image'])) {
        foreach ($certificate_option['image'] as $imgKey => $imgValue) {
            if ($imgValue == 'annex_signature' or $imgValue == 'annex_stempel')
                unset($certificate_option['image'][$imgKey]);
        }
    }
}

if (isset($certificate_option) and is_array($certificate_option)) {
    foreach ($certificate_option as $itemKey => $itemValue) {
        if (strstr(strtolower($itemKey), 'remarks')) {
            $certificate_option[$itemKey] = str_replace("\n", "<br/>", $itemValue);
        }
    }
}

if ($contentInserts = parse_shortcode('input', $theTemplate)) {
    foreach ($contentInserts as $kInsert => $insert) {
        if (isset($insert['name'])) {
            $insertName = $insert['name'];
            if (isset($certificate_option) and isset($certificate_option[$insertName])) {
                if ($certificate_option[$insertName] == 'insert-content')
                    $theTemplate = str_replace($insert['element'], $insert['content'], $theTemplate);
                else
                    $theTemplate = str_replace($insert['element'], $certificate_option[$insertName], $theTemplate);
            } else {
                $theTemplate = str_replace($insert['element'], '', $theTemplate);
            }
        }
    };
}

$crtDo = 'print';
$addPage = false;
require_once("$prog_path/pdf/tcpdf/hcp_pdf.inc.php");
$pages = $pdf->setSourceFile($certificateTemplate);
$footerY = -8;
$footerX = 0;
$pdf->SetMargins(25, 55, 25);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->setFontSubsetting(true);
$pdf->SetPrintHeader(true);
$head_foot_options['offid'] = $pdf_data['offid'];
$head_foot_options['act'] = $crtDo;
$head_foot_options['annex_only'] = isset($annexPageOnly);
$head_foot_options['annex_separate_pages'] = isset($annexSepareted);
$head_foot_options['type'] = 'digital';
$head_foot_options['template'] = 'annual_certificate';
$head_foot_options['page_template'] = '2,3,3';
if (isset($annexPageOnly)) {
    $head_foot_options['page_template'] = '3';
} elseif (isset($annexSeparetedFirstPage)) {
    if ($annexSeparetedFirstPage == 'preceded')
        $head_foot_options['page_template'] = '2,3';
    elseif ($annexSeparetedFirstPage == 'major')
        $head_foot_options['page_template'] = '2,3,3';
    else
        $head_foot_options['page_template'] = '3';
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
    if (isset($annexSepareted)) {
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
$pdf->SetPrintFooter(true);
//print certificate

if ($protected_pdf = get_option('protected_pdf')) {
    $protected_pdf = json_decode($protected_pdf, true);
    if (isset($protected_pdf['annual']) && isset($protected_pdf['protect']) && trim($protected_pdf['password']) != '') {
        $pdf->SetProtection(array('edit', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble'), '', $protected_pdf['password'], 0, null);
    }
}

//getting reference_standards
if (is_array($pdf_data['reference_standards']) and $standards = $amdb->get_results("SELECT code FROM hqc_halal_standards WHERE status='active' and FIND_IN_SET(stnid,'" . implode(',', $pdf_data['reference_standards']) . "')")) {
    $standards_array = array();
    foreach ($standards as $standard) {
        $standards_array[] = $standard['code'];
    };
    $pdf_data['reference_standards'] = implode('<br/>', $standards_array);
}
if (is_array($pdf_data['category']) and $categories = $amdb->get_results("SELECT CONCAT(code,': ',description) AS  category FROM hqc_categories WHERE status='active' and FIND_IN_SET(catid,'" . implode(',', $pdf_data['category']) . "')")) {
    $categories_array = array();
    foreach ($categories as $category) {
        $categories_array[] = substr($category['category'], 0, 1);
    };
    $pdf_data['category'] = implode(', ', $categories_array);
}
if (!isset($pdf_data['reference_nr']))
    $pdf_data['reference_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($pdf_data['clid'], 5, "0", STR_PAD_LEFT);
$pdf_data['printed_on'] = time();
$QRStyle = array('border' => false, 'padding' => 0, 'fgcolor' => array(0, 0, 0), 'bgcolor' => false);
$QRLink = "https://ca.iidc.eu/?crtnr={$pdf_data['certificate_nr']}";
$head_foot_options['QRStyle'] = $QRStyle;
$head_foot_options['QRLink'] = $QRLink;
if (isset($templatePHP['QR']) && !isset($annexPageOnly)) {
    $pdf->write2DBarcode($QRLink, 'QRCODE,H', 158, 26, 28, 28, $QRStyle, 'N');
    $head_foot_options['QRStyle'] = $QRStyle;
    $head_foot_options['QRLink'] = $QRLink;
}

$pdf_data['main_signature'] = '';
$pdf_data['main_stempel'] = '';
$pdf_data['annex_signature'] = '';
$pdf_data['annex_stempel'] = '';
$pdf_data['setXYAfter'] = array();

function insertImages($imgs = 'images')
{
    global $theTemplate, $templatePHP, $prog_path, $pdf, $annexPageOnly, $pdf_data, $certificate_option;

    if (
        isset($certificate_option) and isset($certificate_option['image'])
        and isset($templatePHP[$imgs]) and count($templatePHP[$imgs]) > 0
    ) {

        $optionImages = $certificate_option['image'];
        if (isset($annexPageOnly)) {
            if (isset($templatePHP[$imgs]['main_signature']))
                unset($templatePHP[$imgs]['main_signature']);
            if (isset($templatePHP[$imgs]['main_stempel']))
                unset($templatePHP[$imgs]['main_stempel']);
        }
        foreach ($templatePHP[$imgs] as $key => $image) {
            if (in_array($key, $optionImages)) {
                if (isset($image['file']) and isset($image['position']) and strstr($image['position'], ',')) {
                    $position = explode(',', $image['position']);
                    $imageFile = $prog_path . '/data/offices/' . $pdf_data['offid'] . '/images/' . $image['file'];
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
}

//adding products
$nr = 1;
if (isset($annexSepareted))
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

foreach ($pdf_data['annex_options'] as $colKey => $colTitle) {
    if (isset($annex_titles[$colKey]) and (isset($pdf_data['annex_options']['add_' . $colKey]) or $colKey == 'product_name')) {
        $colWidth = '[colwidth]';
        if (isset($pdf_data['annex_options'][$colKey . '_width']) and trim($pdf_data['annex_options'][$colKey . '_width']) != '') {
            $colWidth = str_replace(',', '.', $pdf_data['annex_options'][$colKey . '_width']);
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
    if (isset($pdf_data['annex_options'][$keyTitle]))
        $annex_titles[$keyTitle] = $pdf_data['annex_options'][$keyTitle];
}
$product_sort_by = 'order by prdid ASC';
if (trim($pdf_data['product_sort_by']) != '') {
    $product_sort_by = 'order by TRIM(' . $pdf_data['product_sort_by'] . ')+0 ASC, TRIM(' . $pdf_data['product_sort_by'] . ') ASC';
}


$productsRows = '';
if (isset($annexPageOnly)) {
    $theTemplateAnnexPages = $theTemplate;
} elseif (isset($annexSepareted)) {
    $templateParts = explode('[annexPage]', $theTemplate);
    $theTemplate = $templateParts[0];
    $theTemplateAnnexPages = $templateParts[1];
}

foreach ($products as $product) {

    if (isset($annexSepareted) && isset($theTemplateAnnexPages))
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
    if (isset($annexSepareted) && isset($theTemplateAnnexPages)) {
        $productsTable[] = str_replace('[products]', str_replace('[tableBody]', $tableHead . $productsRows, $table), $theTemplateAnnexPages);
        $productsRows = '';
    }
}

//print_r($productsRows);
if (isset($annexSepareted)) {
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

$pdf->AddPage();
insertImages();

function print_header_footer()
{
    global $pdf, $head_foot_options, $hcp_path, $pdf_data, $time, $office_address, $crtDo, $prog_path, $certificate_option;
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
<td style="font-size:17px;font-weight:bold;color:' . $text_color . ';width:10cm;font-family:times">Control Office Of Halal Slaughtering and<br>' . $office_address['company_name_english'] . '</td>
<td style="font-size: 19px; font-family: hacenlinerscreenbd;line-height:20px;text-align:right;color:' . $text_color . ';width:8cm">مكتب مراقبة الذبح حسب الشريعة الإسلامية<br>' . $office_address['company_name_arabic'] . '</td>
</tr>
</table>';

    $pdf->SetY(48);
    $pdf->SetX(15);
    $pdf->writeHTML($header);
    if (isset($head_foot_options['QRStyle']) && isset($head_foot_options['QRLink'])) {
        $yx = array(166, 14);
        $pdf->SetMargins(0, 0, 0);
        if (!isset($time))
            $time = $_GET['tm'];
        if ($crtDo == 'preview')
            $QRLink = "Certificate is in Preview Mode.";
        else
            $QRLink = $head_foot_options['QRLink'] . "&get=annex&tm=$time";

        $pdf->write2DBarcode($QRLink, 'QRCODE,H', $yx[0], $yx[1], 28, 28, $head_foot_options['QRStyle'], 'N');

        $pdf->SetY(($yx[1] + 29));
        $pdf->setX($yx[0]);
        $pdf->writeHTML('<table style="width:2.8cm;"><tr><td style="text-align:center;font-size:12px;line-height:12px">Scan to verify</td></tr></table>');
    }

    if (isset($office_address['shipment_address']) && trim($office_address['shipment_address'])) {
        $pdf_data['footer_pos'] = isset($templatePHP['address-position']) ? $templatePHP['address-position'] : 285 - (count(explode("\n", $office_address['shipment_address'])) * 4);

        $pdf->SetY($pdf_data['footer_pos']);
        $pdf->writeHTML('<div style="font-size:14px;line-height:16px;text-align:center;font-family:times;color:' . $text_color . '">' . str_replace("\n", "<br/>", $office_address['shipment_address']) . '</div>');

        if (in_array($office_address['offid'], array(3, 9, 12, 15, 17))) {
            $eiaci_logo =  '/data/images/eiaci.png';
            if (file_exists($prog_path . $eiaci_logo)) {
                $pdf->Image($file = $prog_path . $eiaci_logo, 15, 270, $w = '25', $h = '');
                $_POST['eiaci'] = 1;
            }
        }
    }
}

if (count($pdf_data) > 0) {
    foreach ($pdf_data as $key => $value) {
        if ($key != 'certificate_option' && $key != 'annex_options' and !is_array($value) and $key != 'products') {
            $value = '<span style="font-family:freeserif">' . trim($value) . '</span>';
            $theTemplate = str_replace('[' . $key . ']', trim($value), $theTemplate);
        }
    }
}

if (isset($annexPageOnly)) {
    $annex_top = 65;
    $annex_bottom = 254;
    $theTemplateParts = explode('[products]', $theTemplate);
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
    if (($cur_y + $products_height + $annex_height) < $annex_bottom and count($products_rows) < 40) {
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
                $tbl_height =  calc_height($products_table, $certificate_fonts['products']);
                if (($cur_y + $tbl_height) >= $annex_bottom) {
                    $pdf->writeHTML($products_table);
                    $pdf->AddPage();
                    $pdf->SetX(15);
                    $pdf->SetY($annex_top);
                    $cur_y = $pdf->GetY();
                    $products_rows = '';
                    $total_rows = $total_rows + $page_rows;
                    $page_rows = 0;
                }
            }
        }
        for ($i = 1; $i <= $total_rows; $i++) {
            array_shift($pdf_data['products_rows']);
        }
        if (count($pdf_data['products_rows']) > 1) {
            $products_rows = implode('</tr>', $pdf_data['products_rows']);
            $products_table = str_replace('[tableBody]', $tableHead . $products_rows, $table);
            $pdf->writeHTML($products_table);
        }
        $cur_y = $pdf->GetY();

        if ((round($cur_y) + round($annex_height)) > $annex_bottom) {
            $pdf->AddPage();
            $pdf->SetX(15);
            $pdf->SetY($annex_top);
        }
        write_pdf($theTemplateParts[1]);
    }
} else {
    write_pdf($theTemplate);
}

//echo $theTemplate;
function write_pdf($theTemplate)
{
    global $pdf, $template, $pdf_data, $certificate_option;
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
                    if (isset($annexPageOnly))
                        $pdf->AddPage();
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
                }
            } elseif (trim($part) != '') {
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

$pdfFile = $pdf_data['certificate_nr'];

if (isset($certificate_option['certificate_file_name'])) {
    $fileName = $certificate_option['certificate_file_name'];
    if ($fileName == 'company_name_crt_number')
        $pdfFile = $pdf_data['awarded_to'] . "-" . $pdf_data['certificate_nr'];
    elseif ($fileName == 'company_name')
        $pdfFile = $pdf_data['awarded_to'];
    elseif ($fileName == 'product_name')
        $pdfFile = $products[0]['product_name'];
}

$pdfFile = trim(preg_replace('/[\@\%\.\;\" "]+/', '', $pdfFile));
$pdfFile = str_replace(array('™', ' '), array('-'), trim($pdfFile) . ".pdf");

$pdf->Output($pdfFile, 'I');
