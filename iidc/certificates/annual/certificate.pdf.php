<?php
// include_once "certificate.v1.pdf.php";
// exit();
//TODO: update new system

use PhpOffice\PhpSpreadsheet\Shared\OLE\PPS\File;

session_start();
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', '1');
//error_reporting(E_ALL);
include "../../config/paths.inc.php";
if (!defined("__HQC__") and !defined("_HQC_")) {
	exit();
};
$tempDir = $prog_path . "/data/temp";
$images_path = $prog_path . "/data/offices/images";
$images_url = $prog_url . "/data/offices/images";
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

	$_POST['certificate_options'] = $_POST['options'];
	if (isset($_POST['options']['approval_required']) && isset($_POST['options']['approved']))
		$_POST['certificate_options']['digital'] = 'yes';

	if (!isset($_REQUEST['crtDo']))
		$_POST['crtDo'] = '';
	else
		$_POST['crtDo'] = $_REQUEST['crtDo'];
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
		if (isset($passed_data['certificate_options']))
			$pData['options'] = json_encode($passed_data['certificate_options']);
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

if (isset($_POST['offid'])) {
	$office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$_POST[offid]'");
	$templates_path = $prog_path . "/data/offices/" . $office['offid'] . "/templates";
	if (!is_dir($templates_path)) {
		$templates_path = $prog_path . "/data/offices/0/templates";
	}

	ob_start();
	include_once $templates_path . '/annual-' . $certificate_options['language'] . '.tmpl.php';
	$template_content = ob_get_contents();
	ob_end_clean();

	$theTemplate = trim(preg_replace('/<style(.*)\/style>/s', '', $template_content));
	$template['style'] = preg_match('/<style(.*)\/style>/s', $template_content, $styleMatch) ? $styleMatch[0] : '';
	if (file_exists($templates_path . '/annual.json')) {
		$templatePHP = json_decode(file_get_contents($templates_path . '/annual.json'), true);
	} else {
		$templatePHP = array();
	}
	$certificate_content['office'] = $office;
}

//$theTemplate = str_replace(array('<p>[pdf ', ')]</p>'), array('[pdf ', ')]'), $theTemplate);
$theTemplate = str_replace(array('<p>[pdf ', ')]</p>', '&nbsp;'), array('[pdf ', ')]', ' '), $theTemplate);

if (isset($_POST['certificate_options']['lastPageRemarks']) && trim($_POST['certificate_options']['lastPageRemarks']) != '') {
	$lastPageRemarks = $_POST['certificate_options']['lastPageRemarks'];
	if (isset($_POST['certificate_options']['lastPageRemarksStyle'])) {
		$lastPageRemarkStyle = $_POST['certificate_options']['lastPageRemarksStyle'];
		$lastPageStyle = '';
		if (isset($lastPageRemarkStyle['bold'])) {
			$lastPageStyle .= 'font-weight: bold;';
		}
		if (isset($lastPageRemarkStyle['italic'])) {
			$lastPageStyle .= 'font-style: italic;';
		}
		if (isset($lastPageRemarkStyle['color'])) {
			$lastPageStyle .= 'color: ' . $lastPageRemarkStyle['color'] . ';';
		}
		$lastPageRemarks = '<div style="' . $lastPageStyle . '">' . $lastPageRemarks . '</div>';
	}
	$theTemplate = str_replace('[lastPageRemarks]', $lastPageRemarks, $theTemplate);
} else {
	$theTemplate = str_replace('[lastPageRemarks]', '', $theTemplate);
}

if (isset($_POST['certificate_options']['remarks']) && trim($_POST['certificate_options']['remarks']) != '') {
	$remarks = $_POST['certificate_options']['remarks'];
	if (isset($_POST['certificate_options']['remarksStyle'])) {
		$remarksStyle = $_POST['certificate_options']['remarksStyle'];

		$remarkStyle = '';
		if (isset($remarksStyle['bold'])) {
			$remarkStyle .= 'font-weight: bold;';
		}
		if (isset($remarksStyle['italic'])) {
			$remarkStyle .= 'font-style: italic;';
		}
		if (isset($remarksStyle['color'])) {
			$remarkStyle .= 'color: ' . $remarksStyle['color'] . ';';
		}
		$remarks = '<div style="' . $remarkStyle . '">' . $remarks . '</div>';
	}
	$theTemplate = str_replace('[remarks]', $remarks, $theTemplate);
} else {
	$theTemplate = str_replace('[remarks]', '', $theTemplate);
}

if (isset($_POST['certificate_options']['annexPages'])) {
	if ($_POST['certificate_options']['annexPages'] == 'annexPageOnly')
		$annexPageOnly = true;
	elseif ($_POST['certificate_options']['annexPages'] == 'annexSepareted') {
		$annexSepareted = true;
		if (isset($_POST['certificate_options']['annexSeparetedFirstPage']))
			$annexSeparetedFirstPage = $_POST['certificate_options']['annexSeparetedFirstPage'];
	}

	if (isset($_POST['certificate_options']) and ($annexPageOnly == true or $annexSepareted == true)) {


		$templateParts = explode('[annexPage]', $theTemplate);
		$theAnnexTemplate = $templateParts[1];
		//find first[pdf setMargins(25,20)]
		if (preg_match('/\[pdf (.*?)\((.*?)\)\]/', $theAnnexTemplate, $pdfMatch)) {
			if (trim($pdfMatch[1]) == 'setMargins') {
				if (trim($pdfMatch[2]) != '') {
					$margin = explode(',', $pdfMatch[2]);
					if (isset($margin[1]))
						$AutoPageBreak = $margin[1];
				}
				$theAnnexTemplate = str_replace($pdfMatch[0], '[pdf setMargins(60,20)]', $theAnnexTemplate);
			}
		}
		if ($annexSepareted == true && $annexSeparetedFirstPage != 'normal') {
			$theTemplate = $templateParts[0] . '[annexPage]' . $theAnnexTemplate;
		} else {
			$theTemplate = $theAnnexTemplate;
		}
	}
}
$option_styles = array();

if ($contentInserts = parse_shortcode('input', $theTemplate)) {
	foreach ($contentInserts as $kInsert => $insert) {
		if (isset($insert['name'])) {
			$insertName = $insert['name'];
			if (isset($_POST['certificate_options']) and isset($_POST['certificate_options'][$insertName])) {
				if ($_POST['certificate_options'][$insertName] == 'insert-content')
					$theTemplate = str_replace($insert['element'], $insert['content'], $theTemplate);
				else
					$theTemplate = str_replace($insert['element'], $_POST['certificate_options'][$insertName], $theTemplate);
			} else {
				$theTemplate = str_replace($insert['element'], '', $theTemplate);
			}
		}
	};
}

$addPage = false;
require_once("$prog_path/tools/pdf/tcpdf/tcpdf.php");
require_once("$prog_path/tools/pdf/FPDI/src/autoload.php");
require_once("$prog_path/tools/pdf/tcpdf/config/tcpdf_config.php");
require_once("$prog_path/tools/pdf/hcp_pdf.inc.php");
$footerY = -8;
$footerX = 0;
$pdf->SetMargins(20, 20, 20);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->setFontSubsetting(true);
$pdf->SetPrintHeader(true);
$pdf->SetPrintFooter(true);
$head_foot_options['offid'] = $_POST['offid'];
$head_foot_options['act'] = $crtDo;
$head_foot_options['annex_only'] = $annexPageOnly;
$head_foot_options['annex_separate_pages'] = $annexSepareted;
if (isset($certificate_options['digital']))
	$head_foot_options['type'] = 'digital';
else
	$head_foot_options['type'] = 'print';
$head_foot_options['template'] = 'annual_certificate';

if ($totalCertificates = get_option("annual_crtNr", $_POST['office_address']))
	$totalCertificates++;
else
	$totalCertificates = 1;


if ($crtDo == "preview") {
	$pdf_data = $_POST;
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
	//	update_option("annual_crtNr", $totalCertificates, $_POST['offid']);
}
if (isset($_POST['certificate_options']['first_issue_date']))
	$pdf_data['first_issue_date'] = $_POST['certificate_options']['first_issue_date'];
else
	$pdf_data['first_issue_date'] = $pdf_data['date_of_issue'];

$pdf_data['first_issue_date'] = date("F d Y", strtotime(fix_date($pdf_data['first_issue_date'])));
$pdf_data['date_of_issue'] = date("F d Y", strtotime(fix_date($pdf_data['date_of_issue'])));
$pdf_data['date_of_expiry'] = date("F d Y", strtotime(fix_date($pdf_data['date_of_expiry'])));

if (!isset($_POST['downLoadZipFile']) && $protected_pdf = get_option('protected_pdf')) {
	$protected_pdf = json_decode($protected_pdf, true);
	if (isset($protected_pdf['annual']) && isset($protected_pdf['protect']) && trim($protected_pdf['password']) != '') {
		$pdf->SetProtection(array('edit', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble'), '', $protected_pdf['password'], 0, null);
	}
}

$pdf_data['office_address'] = '';
if (isset($_POST['office_address'])) {
	if ($office_address = $amdb->get_row("SELECT * from offices JOIN hqc_office_certificate_data ON hqc_office_certificate_data.offid = offices.offid WHERE offices.offid = '$_POST[office_address]'")) {

		if (isset($office_address['certificate_number']) && is_array(json_decode($office_address['certificate_number'], true))) {
			$pdf_data['certificate_number'] = json_decode($office_address['certificate_number'], true);
			$pdf_data['certificate_number'] = $pdf_data['certificate_number']['annual'] ?? '';
		}
		$office_address = $office_address + json_decode($office_address['certificate_address'], true);
	}
}

if (isset($_REQUEST['certificate_nr'])) {
	$pdf_data['certificate_nr'] = $_REQUEST['certificate_nr'];
} else {
	if (!isset($pdf_data['certificate_nr']) or $pdf_data['certificate_nr'] == '0' or trim($pdf_data['certificate_nr']) == '') {
		$certificate_nr = $pdf_data['certificate_number'];
		$pdf_data['certificate_nr'] =  $certificate_nr['prefix'] . str_pad($totalCertificates, $certificate_nr['length'], "0", STR_PAD_LEFT);
	}
}

//getting reference_standards
if (is_array($pdf_data['reference_standards']) and $standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE FIND_IN_SET(stnid,'" . implode(',', $pdf_data['reference_standards']) . "') ORDER BY code ASC")) {
	$standards_array = array();
	foreach ($standards as $standard) {
		$standards_array[] = $standard['code'];
	};

	$pdf_data['reference_standards'] = implode(', ', $standards_array);
}

if (is_array($pdf_data['category']) and $categories = $amdb->get_results("SELECT CONCAT(code,': ',description) AS  category FROM hqc_categories WHERE status='active' and FIND_IN_SET(catid,'" . implode(',', $pdf_data['category']) . "')")) {
	$categories_array = array();
	foreach ($categories as $category) {
		$categories_array[] = trim(substr($category['category'], 0, 1));
	};
	$pdf_data['category'] = implode(', ', $categories_array);
}


if (!isset($pdf_data['reference_nr']) && isset($pdf_data['url']) && trim($pdf_data['url']) != '') {
	$pdf_data['reference_nr'] = explode('/', $pdf_data['url'])[1];
}


if (!isset($pdf_data['reference_nr']))
	$pdf_data['reference_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($_REQUEST['clid'], 5, "0", STR_PAD_LEFT);

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
	$QRLink = "https://iidc.halal-digital.net/ca/?crtnr={$pdf_data['certificate_nr']}&vr=$vr";
}

$head_foot_options['QRStyle'] = $QRStyle;
$head_foot_options['QRLink'] = $QRLink;

$pdf_data['main_signature'] = '';
$pdf_data['main_stempel'] = '';
$pdf_data['annex_signature'] = '';
$pdf_data['annex_stempel'] = '';
$pdf_data['setXYAfter'] = array();
$imgCount = 0;
function insertImages($imgs = 'images')
{
	global $pdf, $prog_path, $certificate_options, $signatories_url, $imgTitleSpecs, $pdf_data, $imgCount;

	if (isset($certificate_options['image']) && is_array($certificate_options['image']) && count($certificate_options['image']) > 0)
		$insertImages = $certificate_options['image'];
	else
		$insertImages = array();
	if (is_array(json_decode($imgs, true))) {
		$imgs = json_decode($imgs, true);
		$optionImages = ["main_signature", "main_stempel", "main_halal_stempel", "main_eiaci", "annex_signature", "annex_stempel", "annex_halal_stempel", "annex_eiaci"];

		$thisY = $pdf->getY();
		foreach ($imgs as $image => $data) {
			if (in_array($image, $optionImages) && in_array($image, $insertImages)) {
				$imgData = explode(',', $data);
				$imageName = $image;
				if (isset($imgData[0]) && isset($imgData[1]) && isset($imgData[2])) {
					$imgX = $imgData[0];
					$imgY = $imgData[1];
					$w = $imgData[2];
					$title = '';


					$image = substr($image, strpos($image, '_') + 1, strlen($image));
					$file_path = $prog_path . '/data/offices/' . $_POST['offid'] . '/images/';
					foreach (['.svg', '.png', '.jpg', '.jpeg'] as $ext) {

						if ($image == 'signature' && isset($pdf_data['signature']) && isset($pdf_data['signature']['signature']) && $pdf_data['signature']['signature'] != '') {
							$file = $prog_path.'/'.$signatories_url . '/' . $pdf_data['signature']['signature'];
						} else {
							$file = $file_path . $image . $ext;
						}
						if (file_exists($file)) {
							$x = $imgX;
							$y = $thisY + $imgY;
							$pdf->Image($file, $x, $y, $w, $h = '', $link = '', $align = '', $palign = '', $border = 0, $fitonpage = false);
							if(isset($pdf_data['signature']['title']))
								$title = str_replace('/', "<br/>", trim($pdf_data['signature']['title']));
							elseif (isset($imgData[3])) {
									$title = str_replace('/', "<br/>", trim($imgData[3]));
							}
							if (trim($title) != '') {
								if (isset($imgData[4])) {
									$titleY = $thisY + $imgData[4];
								} else {
									list($widthPx, $heightPx) = getimagesize($file);
									$dpi = 72; // or use your own image DPI
									$widthMm = ($widthPx / $dpi) * 25.4;
									$heightMm = ($heightPx / $dpi) * 25.4;
									// Calculate proportional height
									$titleY = $y + (($w / $widthMm) * $heightMm);
								}
								//print_r($imgTitleSpecs);
								if (isset($imgTitleSpecs[$imageName])) {
									if (isset($imgTitleSpecs[$imageName]['style'])) {
										$imageStyle = $imgTitleSpecs[$imageName]['style'];
									}
									if (isset($imgTitleSpecs[$imageName]['wrap'])) {
										$imageWrap = $imgTitleSpecs[$imageName]['wrap'];
										$title = $imageWrap[0] . $title . $imageWrap[1];
									}
								}

								$pdf->setXY($x, $titleY); // Move below the image
								$title = '<table style="width:' . $w . 'mm;"><tr><td style="text-align: center;' . $imageStyle . '">' . $title . '</td></tr></table>';
								$pdf->writeHtml($title);
							}
						}
					}
				}
			}
		}
	}
}

unset($pdf_data['product']);
$pdf_data['manufacturing_address'] = '';
if ($company = $amdb->get_row("SELECT * FROM companies where companies.clid = '$_REQUEST[clid]'")) {
	$pdf_data['annex_company_name'] = '<b>' . $company['company_name'] . '</b>';
	if (isset($certificate_options['clientAddress']))
		$pdf_data['annex_company_name'] = "<br/><b>$company[company_name]</b><br/>$company[street1]<br/>$company[zip1] $company[city1]<br/>$company[country1]";

	$pdf_data['company_address'] = "<span>$company[company_name]</span><br/><span>$company[street1]</span><br/><span>$company[zip1] $company[city1]</span><br/><span>$company[country1]</span>";
	if (trim($company['ec_number']) != "")
		$pdf_data['company_address'] .= "<br/>EC No.: $company[ec_number]";
	if (isset($company['company_logo']) and trim($company['company_logo']) != '') {
		$pdf_data['company_logo'] = '<img src="' . $images_url . '/' . $company['company_logo'] . '" style="width: 100px; height: 100px;"/>';
	} else {
		$pdf_data['company_logo'] = '';
	}
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
				if (isset($_POST['certificate_options']['awarded_to_site']))
					$awarded_to_site = $address;
			}
			if (isset($_POST['certificate_options']['manufacturing_sites_OL']))
				$pdf_data['manufacturing_address'] = implode('. ', $manufacturing_address);
			else
				$pdf_data['manufacturing_address'] = '<span>' . implode('</span><br/><span>', $manufacturing_address) . '</span>';
		};
		if (isset($awarded_to_site)) {

			$pdf_data['manufacturing_address'] = $pdf_data['company_address'];
			if (isset($_POST['certificate_options']['awarded_as_site']))
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

if (isset($certificate_options['replace_header_text']) and  (isset($certificate_options['header_text']) and trim($certificate_options['header_text']) != '')) {
	//find the first span with data-replace="header_text" in it in $theTemplate using document DOM object and replace the text of the node keeping span with $certificate_options['header_text']
	$dom = new DOMDocument();
	$dom->loadHTML($theTemplate);
	$xpath = new DOMXPath($dom);
	foreach ($xpath->query('//span') as $span) {
		$spanHTML = $dom->saveHTML($span);
		if ($span->getAttribute('data-replace') == 'header_text') {
			$theTemplate = str_replace($span->nodeValue, $certificate_options['header_text'], $theTemplate);
			break;
		}
	}
}

if (isset($certificate_options['awarded_to_title']))
	$theTemplate = str_replace('[awarded_to_title]', $certificate_options['awarded_to_title'], $theTemplate);
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
	$totalWidth = 17;
	$table = '<table border="0" cellpadding="3" style="width:17cm">[tableBody]</table>';
	$tableHead = "<tr>";
	$tableBody = "<tr>";
	$certificate_fonts = array();
	if (isset($certificate_options['fonts']))
		$certificate_fonts = $certificate_options['fonts'];
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
			if (isset($templateParts[1]))
				$theTemplateAnnexPages = $templateParts[1];
			else
				$theTemplateAnnexPages = $templateParts[0];
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
$pdf_data['annex_footer'] = '';

$signatories_url = "/data/offices/signatories";
$signatories_path = $prog_path . "/data/offices/signatories";
$signatories_file = $signatories_path . "/signatories.json";
if (isset($certificate_options['image'])) {
	$signatories = json_decode(file_get_contents($signatories_file), true);
	if (!is_array($signatories)) {
		$signatories = [];
	}
	$signatory = $pdf_data['signatory'];
	if (isset($signatories[$signatory])) {
		$signatoryData = $signatories[$signatory];
		if (isset($signatoryData['certificates']) && isset($signatoryData['certificates'][$pdf_data['offid']])) {
			if (isset($signatoryData['certificates'][$pdf_data['offid']]['annual'])) {
				unset($signatoryData['certificates']);
				$pdf_data['signature'] = $signatoryData;
				$theTemplate = str_replace('[main_director]', $signatoryData['name'].'/'.$signatoryData['position'], $theTemplate);
			}
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
	if (isset($certificate_options['annex_number'])) {
		$pdf_data['annex_number'] = $certificate_options['annex_number'];
		unset($certificate_options['annex_number']);
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

function total_pages() {}
$pdf->AddPage();
$total_pages = 0;

function print_header_footer($whr = 'header', $pgNr = 1)
{
	global $total_pages, $pdf, $head_foot_options, $hcp_path, $office_address, $crtDo, $pdf_data, $vr, $template, $annexSepareted, $annexSeparetedFirstPage;
	// $total_pages++;
	//create border
	$pdf->SetDrawColor(11, 148, 68);
	$pdf->SetLineWidth(1);
	$pdf->Rect(10, 10, 190, 277, 'D');
	if ($whr == 'header') {
		if ($crtDo == 'preview') {
			showExample();
		}
		if ($pgNr == 1 || $annexSepareted == true) {
			$hqc_logo =  '/data/offices/0/images/logo.png';

			if (file_exists($hcp_path . $hqc_logo)) {
				$y = 20;
				$pdf->Image($file = $hcp_path . $hqc_logo, 140, $y, $w = '50', $h = '');
			}

			if (isset($head_foot_options['QRStyle']) && isset($head_foot_options['QRLink'])) {
				$yx = array(20, 20);
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
		}
	}
	if ($whr == 'footer') {
		if (isset($office_address['annual']) && trim($office_address['annual'])) {
			$page_footer = '<table><tr><td style="text-align:right;font-size:12px;line-height:18px;width:[width]cm;font-weight:bold;">Certificate No.: ' . $pdf_data['certificate_nr'] . ' Page [this_page]/[total_pages]</td></tr><tr><td style="text-align:right;font-size:12px;line-height:16px;width:16.5cm;font-weight:bold;">F 8.28 / VERSION 3</td></tr></table>';
			$width = '17.3';
			$thisPage = $pdf->pageNo();
			$total_pages = trim($pdf->getAliasNbPages());
			// $total_pages = 2;
			if ($annexSepareted == true) {
				if ($annexSeparetedFirstPage == 'normal') {
					$thisPage = 1;
					$total_pages = 1;
				}
				if ($annexSeparetedFirstPage == 'major') {
					if ($pdf->pageNo() == 1) {
						$thisPage = 1;
						$total_pages = 2;
					} else {
						$thisPage = 2;
						$total_pages = 2;
					}
				}
				if ($annexSeparetedFirstPage == 'preceded') {
					//check if the page is odd or even
					if ($pdf->pageNo() % 2 == 1) {
						$thisPage = 1;
						$total_pages = 2;
					} else {
						$thisPage = 2;
						$total_pages = 2;
					}
				}
				$width ='16.5';
			}
			$page_footer = str_replace(array('[this_page]', '[total_pages]','[width]'), array($thisPage, $total_pages, $width), $page_footer);
			$pdf->SetY(235);
			$pdf->SetX(20);
			$pdf->writeHTML($template['style'] . $office_address['annual'] . $page_footer, true, false, true, false);
		}
	}
	$pdf->setY(60);
}

if (count($pdf_data) > 0) {
	foreach ($pdf_data as $key => $value) {
		if ($key != 'certificate_options' && $key != 'annex_options' and !is_array($value) and $key != 'products') {
			$font_size = '';
			if (isset($certificate_fonts[$key])) {
				$font_size = 'font-size:' . $certificate_fonts[$key] . 'px';
			}
			if ($key != 'annex_number' && $key != 'certificate_nr') {
				$theTemplate = str_replace('[' . $key . ']', trim($value), $theTemplate);
			}
		}
	}
}
//TODO: save these data for generating certificate using QR check
// echo $productsTable;

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
	$annex_top = 20;
	$annex_bottom = 230;
	$theTemplateParts = explode('[products]', $theTemplate);

	if (isset($certificate_options['insert_HAK_logo']) && $annexPageOnly == false) {
		//	$pdf->Image($file = $prog_path . '/data/images/hak.png', $x = 155, $y = 210, $w = 19, $h = '');
	}
	write_pdf($theTemplateParts[0]);
	ob_start();
	$pdf->startTransaction();
	$pdf->writeHTML(trim($productsTable));
	write_pdf($theTemplateParts[1]);
	$annex_height = round($pdf->GetY());
	$pdf = $pdf->rollbackTransaction();
	ob_end_clean();
	$products_rows = explode('</tr>', $pdf_data['products_rows']);
	if ($annex_height < $annex_bottom && count($products_rows) < 20) {
		$pdf->writeHTML(trim($productsTable));
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
					$pdf->SetX(20);
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
			$pdf->writeHTML(trim($products_table));

			//	$products_pages .= $products_table . '[pdf addPage(15,' . $annex_top . ')]';
		}
		$cur_y = $pdf->GetY();

		if ((round($cur_y) + round($annex_height)) > $annex_bottom) {
			if ((round($cur_y) + round($annex_height)) < ($annex_bottom + 50)) {
				$pdf->SetY($cur_y - 10);
			} else {
				$pdf->AddPage();
				$pdf->SetX(20);
				$pdf->SetY($annex_top);
			}
			//$products_pages .= $products_table . '[pdf addPage(15,' . $annex_top . ')]';
		}
		write_pdf($theTemplateParts[1]);
	}
} else {
	// if (isset($certificate_options['insert_HAK_logo']) && $annexSeparetedFirstPage != 'normal')
	// 	$pdf->Image($file = $prog_path . '/data/images/hak.png', $x = 155, $y = 210, $w = 19, $h = '');
	// echo $theTemplate;
	write_pdf($theTemplate);
}

// echo $products_pages;
// exit();
//echo $theTemplate;
$added_pages = 1;
$certSubNumber = '';

function write_pdf($theTemplate)
{
	global $pdf, $template, $revision, $QRLink, $pdf_data, $certificate_options, $prog_path, $annexSeparetedFirstPage, $added_pages, $certSubNumber;
	$certificate_auto_number = '';
	if (trim($theTemplate) != '') {
		$theTemplate = str_replace(array('&amp;', '&lsquo;', '&rsquo;'), array('&', '‘', '’'), $theTemplate);
		preg_match_all('/\[pdf(.*)\]/U', $theTemplate, $thePatrs);
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
				preg_match('/\[pdf (.*?)\((.*?)\)\]/', $part, $pdfMatch);
				if (trim($pdfMatch[1]) == 'setFontSize') {
					$pdf->setFontSize($pdfMatch[2]);
				} elseif (trim($pdfMatch[1]) == 'images' || trim($pdfMatch[1]) == 'img') {
					insertImages($pdfMatch[2]);
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
									if (isset($certificate_options[$ifInputParts[0]]) and trim($certificate_options[$ifInputParts[0]]) != '')
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
					$marginLeft = 20;
					$marginTop = 20;
					$marginRight = 20;
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

					if (isset($certificate_options['auto_annex_number']) && isset($annex_number)) {
						if (($annexSeparetedFirstPage == 'preceded' && $added_pages % 2 == 0) or ($annexSeparetedFirstPage == 'major' && $added_pages > 1) or ($annexSeparetedFirstPage == 'normal' && $added_pages > 0))
							$annex_number = $annex_number + 1;
					}

					if (isset($certificate_options['auto_certificate_number'])) {
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
					$marginLeft = 20;
					$marginTop = 20;
					$marginRight = 20;
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
						} elseif (count($margin) == 2) {
							if (isset($margin[0]))
								$marginTop = $margin[0];
							if (isset($margin[1]))
								$marginRight = $marginLeft = $margin[1];
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
				else
					$pdf->SetFont('freesans');
				$pdf->writeHTML(ob_get_contents());
				ob_end_clean();
				$curY = $pdf->getY();
				$curX = $pdf->getX();
			}
		}
	}
}

$pdfFile = $pdf_data['certificate_nr'];
if (isset($_POST['certificate_file_name']))
	$certificate_file_name = $_POST['certificate_file_name'];
if (isset($certificate_options['certificate_file_name'])) {
	$certificate_file_name = $certificate_options['certificate_file_name'];
	$fileName = $certificate_options['certificate_file_name'];
	if ($fileName == 'company_name_crt_number')
		$pdfFile = $pdf_data['awarded_to'] . "-" . $pdf_data['certificate_nr'];
	elseif ($fileName == 'company_name')
		$pdfFile = $pdf_data['awarded_to'];
	elseif ($fileName == 'product_name')
		$pdfFile = $products[0]['product_name'];
}

$pdfFile = trim(preg_replace('/[\@\%\.\;\" "]+/', '', $pdfFile));
$pdfFile = str_replace(array('™', ' '), array('-'), trim($pdfFile) . "-" . $vr . ".pdf");
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

		header("location: $prog_www/client_data/certificates/$pdf_data[url]");
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

// if (isset($_GET['get']) && $_GET['get'] == 'annex')
// 	$pdf->deletePage(1);
$pdf->Output($pdfFile, 'I');
