<?php

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

require_once('fpdf/fpdf.php');
require_once('fpdi2/autoload.php');
require_once('tcpdf/config/tcpdf_config.php');
require_once('tcpdf/tcpdf.php');
require_once('fpdm/fpdm.php');
//require_once('tcpdf/tcpdi.php');
//require_once('tcpdf/tcpdi_parser.php');

function slugify($text, string $divider = '')
{
  // replace non letter or digits by divider
  $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, $divider);

  // remove duplicate divider
  $text = preg_replace('~-+~', $divider, $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}

function random_username($string) {
	$pattern = " ";
	$firstPart = strstr(strtolower($string), $pattern, true);
	$secondPart = substr(strstr(strtolower($string), $pattern, false), 0,3);
	$nrRand = rand(0, 100);
	//$username = trim($firstPart).trim($secondPart).trim($nrRand);
	//$username = trim($string).trim($nrRand);
	$username = trim($string);
	if (strlen($username) > 25) {
		// code goes here
		//$username = substr($username, 0, 25);
	}
	return $username;
}

function getToken($length=32){
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    $codeAlphabet.= "~!@#$%^&*";	
    for($i=0;$i<$length;$i++){
        $token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
    }
    return $token;
}

function crypto_rand_secure($min, $max) {
	$range = $max - $min;
	if ($range < 0) return $min; // not so random...
	$log = log($range, 2);
	$bytes = (int) ($log / 8) + 1; // length in bytes
	$bits = (int) $log + 1; // length in bits
	$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
	do {
		$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
		$rnd = $rnd & $filter; // discard irrelevant bits
	} while ($rnd >= $range);
	return $min + $rnd;
}

function shuffle_assoc(&$array) {
    $keys = array_keys($array);

    shuffle($keys);

    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;

    return true;
}

function print_array($array){
    echo '<per>';
    print_r($array);
    echo '</per>';
}

function getDBDateFromNormal($d){
    $date = explode('.', $d);
    return $date[2]."-".$date[1]."-".$date[0];
}

function getDateFromMySQL($date){
    if(!empty($date) && ($date!==0))
        return date('d.m.Y', strtotime($date));
    else return ;
}

function getDateTimeFromMySQL($date){
    if(!empty($date) && ($date!==0))
        return date('d.m.Y G:i', strtotime($date));
    else return;
}

function esc_url($url) {

    if ('' == $url) {
        return $url;
    }

    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);

    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;

    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }

    $url = str_replace(';//', '://', $url);

    $url = htmlentities($url);

    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);

    if ($url[0] !== '/') {
        // We're only interested in relative links from $_SERVER['PHP_SELF']
        return '';
    } else {
        return $url;
    }
}

function getPostParam($param){
    return isset($_POST[$param]) ? $_POST[$param] : "";
}

function getGetParam($param){
    return isset($_GET[$param]) ? $_GET[$param] : "";
}

/**
 * Calculates how many months is past between two timestamps.
 *
 * @param  int $start Start timestamp.
 * @param  int $end   Optional end timestamp.
 *
 * @return int
 */
function get_month_diff($start)
{
    return -floor((time() - $start) / (60 * 60 * 24));
}

function sanitize($value) {
	return strtolower(trim($value));
}

////////////////////////////////////////////////////////////////////
function saveAuditReportPDF($data, $attach='F0417_Offer Halal certification_EN.pdf', $dest_path, $audit_report_settings,$preview=false) {

	$audit_report_settings = @json_decode($audit_report_settings, true);

	class MYTCPDF_AR extends TCPDF {

		public function Header(){
		   $html = '<table border="1"  border="1" cellspacing="0" cellpadding="8"  width="100%">
		<tr>
			<td width="33%" align="left" valign="top">Form: F0436<br/> 
		  Revision Date: 29.4.2021<br/>
	  Page:'.$this->PageNo().' from 5
	  </td>
			<td width="34%" style="text-align:center;"><img src="http://test2022.halal-e.zone/img/pdf/logo.png" width="100" /></td>
			<td width="33%" style="text-align:center;" valign="middle"><strong>Halal Quality Control<br/>
		  Audit Report<br/>
		  Implementation Report
		  </strong>
	  </td>        
		</tr>
	  </table>
	  ';
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
		
		public function Footer(){
			
		   $html = '<table border="1" cellspacing="0" cellpadding="8" width="100%"><tr>
		  <td>Created    by:<br />  
			Shady    Dabshah</td>
		  <td>Reviewed    by:<br />
			Wassiem    Al Chaman</td>
		  <td>Approved    by:<br />
			Ibrahim    Salama</td>
		  <td>Revision    Nr.:<br />
			03</td>
		</tr>
	  </table>';
	  
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
	  }
	  // create new PDF document
	  $pdf = new MYTCPDF_AR('P', 'mm', array(395.732, 279.654), true, 'UTF-8', false);
	  //$pdf = new MYTCPDF();
	  // set header and footer fonts
	  $pdf->setHeaderFont(Array('times', '', 12));
	  $pdf->setFooterFont(Array('times', '', 12));
	  
	  // set default monospaced font
	  //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	  
	  // set margins
	  $pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	  $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
	  $pdf->setFooterMargin(PDF_MARGIN_FOOTER);
	  
	  // set auto page breaks
	  //$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	  
	  // set image scale factor
	  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	  
	  // set some language-dependent strings (optional)
	  if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		  require_once(dirname(__FILE__).'/lang/eng.php');
		  $pdf->setLanguageArray($l);
	  }
	  
	  //$pdf->setFont('freeserif', '', 12);;
	  $pdf->setFont('freeserif', '', 12);;
	  
	  $pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
	  
	  // Add a page
	  // This method has several options, check the source code documentation for more information.
	  $pdf->AddPage();
	  
	  $html = '
	  <style>
	  .th {
		background-color:#d9d9d9;
		color:#bf0000;
		font-weight:bold;
	  }
	  .form-control {
		font-size:17px;
	  }
	  </style>
	  <div class="container">
	  <div id="page1" class="page active">
		<p style="text-align:center;">
		  <img
			src="http://test2022.halal-e.zone/img/pdf/logo.png"
			width="200"
		  />
		</p>
		<h1 style="text-align:center;">
		Halal Quality Control<br/>
		Audit Report
		</h1>
		<p></p><table width="100%" border="0" cellpadding="8" cellspacing="0">
		<tr>
		  <td width="15%"></td>
		  <td width="70%"><table border="1" 
	  cellpadding="8"
	  cellspacing="0"
	  class="table table-bordered table-sm"
	  width="100%"
	  >
			  <tr>
				<td style="text-align:center;" width="7%">1</td>
				<td width="40%">Date:</td>
				<td width="53%">'.($data["app"]["approved_date1"] ? DateTime::createFromFormat('Y-m-d', $data["app"]["approved_date1"])->format('d/m/Y') : "").'</td>
			  </tr>
			  <tr>
			  <td style="text-align:center;">2</td>
			  <td>Company Name:</td>
			  <td>'.$data["user"]["name"].'</td>
			</tr>        
			  <tr>
				<td style="text-align:center;">3</td>
				<td>Country of Company:</td>
				<td>'.$data["user"]["country"].'</td>
			  </tr>
			  <tr>
				<td style="text-align:center;">4</td>
				<td>Manufacturing Site Address(es)</td>
				<td>'.($audit_report_settings["addresses"] == "" ? $data["user"]["address"] : $audit_report_settings["addresses"]).'</td>
			  </tr>
			  <tr>
				<td style="text-align:center;">5</td>
				<td>Company ID:</td>
				<td>'. $audit_report_settings["companyId"].'</td>
			  </tr>
			  <tr>
			  <td style="text-align:center;">6</td>
			  <td>Reference (optional):</td>
			  <td>'. $audit_report_settings["reference"].'</td>
			</tr>        
			</table>
			<p></p>
			<table border="1" 
	  cellpadding="8"
	  cellspacing="0"
	  class="table table-bordered table-sm"
	  width="100%"
	  >
			  <tr>
				<td style="text-align:center;" width="7%">1</td>
				<td width="40%">Lead Auditor:</td>
				<td width="53%">'. $audit_report_settings["LeadAuditor"].'</td>
			  </tr>
			  <tr>
			  <td style="text-align:center;">2</td>
			  <td>Islamic Affairs Expert:</td>
			  <td>'. $audit_report_settings["IslamicAffairsExpert"].'</td>
			</tr>        
			  <tr>
				<td style="text-align:center;">3</td>
				<td>Accompanying Auditors or Experts:</td>
				<td>'. $audit_report_settings["AccompanyingAuditorsOrExperts"].'</td>
			  </tr>
			  <tr>
				<td style="text-align:center;">4</td>
				<td>Halal Quality Control Office (Country):</td>
				<td>'. $audit_report_settings["HalalQualityControlOfficeCountry"].'</td>
			  </tr>       
			</table>
			
			</td>
		  <td width="15%"></td>
		</tr>
	  </table>
	  <p></p>
	  <p style="text-align:center;">This document is property of Halal Quality Control</p>
	  </div>
	  <br pagebreak="true"/>
	  <div id="page2" class="page">';
$html .= 
'<p></p>
<table border="1" cellspacing="0" cellpadding="5" width="100%" style="font-size:12px;">
  <tr style="background-color:#f2dbdb;">
	<td width="4%">No.</td>
	<td width="6%">Type of Finding (NCR, OBS)</td>
	<td width="24%">NCR/OBS statement</td>
	<td width="24%">Root Cause Analysis</td>	
	<td width="24%">Proposed Corrective Action</td>		
	<td width="8%">Target Date</td>		
	<td width="10%">Auditors comment
after 
Implementation</td>			
  </tr>';
  
  $counts = array(
	'Major' => 0,
	'Minor' => 0,
	'OBS' => 0);

  $num = 0;
  foreach ($data["deviations"] as $deviation) {
		$num++;
		$Type = $deviation['Type'];
		$counts[$Type]++;		
		$Status = $deviation['Status'];
		if ($Status == '0') {
			$r_color = '#f2dede';
		}
		else if ($Status == '1') {
			$r_color = '#dff0d8';
		}
$html .= '<tr style="background-color:'.$r_color.';">
	<td>'.$num.'</td>
	<td>'.$deviation["Type"].'</td>
	<td>'.$deviation["Deviation"].'</td>
	<td>'.$deviation["RootCause"].'</td>	
	<td>'.$deviation["Measure"].'</td>		
	<td>'.$deviation["Deadline"].'</td>		
	<td>'.($Status=='1'?'Confirmed':'Not Confirmed').'</td>			
  </tr>';
}

$html .= '</table>
<h1 style="text-align:center;">Summary</h1>
<table border="1"
  cellpadding="8"
  class="table table-bordered table-sm mb-5"
>
  <tr>
	<td style="width: 25%">Total Number of Findings: <strong>'.$num.'</strong></td>
	<td style="width: 25%">Total Major: <strong>'.$counts["Major"].'</strong></td>
	<td style="width: 25%">Total Minor: <strong>'.$counts["Minor"].'</strong></td>
	<td style="width: 25%">Total Observation: <strong>'.$counts["OBS"].'</strong></td>
  </tr>
</table>

<h1 style="text-align:center;">Scope Appropriateness</h1>
<table border="1"
  cellpadding="8"class="table table-bordered table-sm mb-5"
>
  <tr>
	<td width="10%" style="text-align:center;">1</td>
	<td width="40%">Scope of Activities</td>
	<td width="50%">'. $audit_report_settings["scopeOfActivities"].'</td>
  </tr>
  <tr>
	<td style="text-align:center;">2</td>
	<td>Certification Scope (Category)</td>
	<td>'. $audit_report_settings["certScope"].'</td>
  </tr>
</table>
</div>
<br pagebreak="true"/>
<div id="page3" class="page">
<p></p>
<table border="1" cellpadding="8" class="table table-bordered table-sm mb-0">
  <tr>
	<th colspan="2" style="text-align:center;" class="th">Recommendation as a result of the inspection</th>
  </tr>
  <tr>
	<td style="width: 50%">'. $audit_report_settings["obj"].'</td>
	<td style="width: 50%">'. $audit_report_settings["ar"].'</td>
  </tr>
  <tr>
	<td>'. $audit_report_settings["arpi"].'</td>
	<td>'. $audit_report_settings["nc"].'</td>
  </tr>
  <tr>
	<td colspan="2">Conslusion / Summary of the inspection:<br />
	'. $audit_report_settings["conclusion1"].'<br /><br /><br /><br /><br />
	  Opportunities for improvements:<br />
	  '. $audit_report_settings["conclusion2"].'<br /><br /><br /><br /><br />
	  Unresolved issues if any (for example scopes not fulfilled):<br />
	  '. $audit_report_settings["conclusion3"].'<br /><br /><br /><br />
	</td>
  </tr>
</table>
<table border="1" cellpadding="8" class="table table-bordered table-sm mt-0">
  <tr>
	<td style="width: 35%">To be filled in by the Customer:<br /><br />
	  Date of Action Plan: '. $audit_report_settings["dateOfAction"].'<br /><br />
	  Name: '. $audit_report_settings["nameAction"].'<br/><br/><br/>
	  <small>Please insert a signature within this field</small>
	</td>
	<td style="width: 35%">To be filled in by the Lead Auditor:<br /><br />
	  Date of Implementation Closure: '. $audit_report_settings["dateOfClosure"].'<br/><br/><br/><br/><br/><br /><br />
	  <small>Please insert a signature within this field</small>
	</td>
	<td style="width: 30%">Remarks:<br/><br />'. str_replace("\n", "<br/>", $audit_report_settings["main-remarks"]).'<br /><br /><br /><br /><br /><br />
	</td>
  </tr>
</table>
</div>
<br pagebreak="true"/>
<div class="page" id="page3">
<p></p>
<h2>Attachment:</h2>
<strong>A. Audit Findings and Non-conformities</strong><br />
When the initial audit/evaluation has been completed, HQC will inform
the applicant of its result.<br />
If Certification Requirements are fulfilled, the Scheme documents
gathered will be handed to the HQC Decision Making Committee for review
and approval.<br />
If throughout the audit the Auditor finds an error or unsatisfactory
requirements exist, the client will be informed of those aspects in
which the application is deemed non-compliant. The following may be
raised by the auditors:<br /><br />

<strong>A.1 Major Non-Conformity (Major)</strong><br />
The absence of, or the failure to implement and maintain, one or more
management system elements, or a situation which, on the basis of the
available evidence:<br />
• would raise significant doubt as to the capability of the system to
achieve the policy and objectives of the organisation and satisfy legal
and regulatory requirements.<br />
• would raise significant doubt as to the quality of what the
organisation is supplying As the nature of the Major Non-Conformity may
affect the halal integrity of a product, the response.<br /><br />
<strong
  >A.1.2 How to address the Major Non-Conformity is as follows:</strong
><br />
• where the Major Non-Conformity affects the halal integrity of the
product, the client will address the Major Non-Conformity with immediate
effect in consultation with HOC.<br />
elect to withdraw the client’s Halal certification where such a Major
Non-Conformity is not immediately addressed.<br />
• where the Major Non-Conformity does not affect the halal integrity of
the product, the client is required to address and closeout the issue
raised in a period not exceeding one month and to advise HQC of the
proposed action/s to be taken within 7 days.<br />
Major Non- Conformities do not meet the definition of “nonconformity” as
defined in standards:<br />
<div class="ps-4">
  a. TS OIC/SMIIC 1/2011: General Guidelines on Halal Food <br />
  b. UAE.S 2055 -1-2015:Halal products - Part one: General Requirements
  for Halal Food <br />
  c. UAE.S 993 :2018 Animal Slaughtering Requirements According to
  Islamic Rules <br />
  d. UAE.S GSO 713 Hygienic Regulations for Poultry Processing Abattoirs
  and Their Personnel <br />
  e. GSO 9:2013: Labeling of pre-packaged foodstuffs <br />
  f. LPPOM MUI: Indonesian Ulema Council <br />
  g. Jakim: Department of Islamic Development Malaysia <br />
  h. HAS : Halal assurance management system<br />
</div>
<strong>A.2 Minor Non-Conformity (Minor)</strong><br />
A finding (indicative of a weakness in the system) of a process, records
or in the management of a particular activity, or a situation which, if
left without corrective action or attention by the organisation, would
raise significant doubt as to the future capability of the Management
System to achieve the policy and objectives of the organisation and the
quality of what the organisation is supplying.<br />
Note: A number of Minor Non-Conformities raised against the same
provision of the assessment standard or the organisation\'s Management
System can effectively demonstrate a breakdown of the system and can
therefore result in a Major Non-Conformity. Minor Non-Conformities do
not meet the definition of "nonconformity" as defined in standards:<br />
<div class="ps-4">
  a. TS OIC/SMIIC 1/2011: General Guidelines on Halal Food<br />
  b. UAE.S 2055 -1-2015:Halal products - Part one: General Requirements
  for Halal Food <br />
  c. UAE.S 993 :2018 Animal Slaughtering Requirements According to
  Islamic Rules<br />
  d. UAE.S GSO 713 Hygienic Regulations for Poultry Processing Abattoirs
  and Their Personnel<br />
  e. GSO 9:2013: Labeling of pre-packaged foodstuffs<br />
  f. LPPOM MUI: Indonesian Ulema Council<br />
  g. Jakim: Department of Islamic Development Malaysia<br />
  h. MUIS : MAJLIS UGAMA ISLAM SINGAPORE<br />
  i. HAS : Halal assurance management system<br />
</div>
<strong>A.3 Observation -Conformity (Observation)</strong><br />
A weakness, that if not treated, may to lead to minor or major
non-conformity. This may be a recommendation or a reminder or flag for
follow-up/review at the next assessment. 
</div>
';

// Print text using writeHTMLCell() 
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
if ($preview) {
	
	$pdf->Output('F0436_Audit_Report.pdf', 'D');
}
else {
	$pdf->Output($dest_path,'F');
}

//============================================================+
// END OF FILE
//============================================================+
////////////////////////////////////////////////////////////////////
}

function saveAdditionalItemsApplicationPDF($data, $attach='F0417_Offer Halal certification_EN.pdf', $dest_path, $preview=false) {
	class MYTCPDF extends TCPDF {

		public function Header(){
		   $html = '<table border="1"  border="1" cellspacing="0" cellpadding="8"  width="100%">
		<tr>
			<td width="33%" align="left" valign="top">Form: F0436<br/> 
		  Revision Date: 29.4.2021<br/>
	  Page:'.$this->PageNo().' from 1
	  </td>
			<td width="34%" style="text-align:center;"><img src="http://test2022.halal-e.zone/img/pdf/logo.png" width="100" /></td>
			<td width="33%" style="text-align:center;" valign="middle"><strong>Halal Quality Control<br/>
		  Additional Items Application
		  </strong>
	  </td>        
		</tr>
	  </table>
	  ';
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
		
		public function Footer(){
			
		   $html = '<table border="1" cellspacing="0" cellpadding="8" width="100%"><tr>
		  <td>Created    by:<br />  
			Shady    Dabshah</td>
		  <td>Reviewed    by:<br />
			Wassiem    Al Chaman</td>
		  <td>Approved    by:<br />
			Ibrahim    Salama</td>
		  <td>Revision    Nr.:<br />
			03</td>
		</tr>
	  </table>';
	  
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
	  }
	  // create new PDF document
	  $pdf = new MYTCPDF('P', 'mm', array(395.732, 279.654), true, 'UTF-8', false);
	  //$pdf = new MYTCPDF();
	  // set header and footer fonts
	  $pdf->setHeaderFont(Array('times', '', 12));
	  $pdf->setFooterFont(Array('times', '', 12));
	  
	  // set default monospaced font
	  //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	  
	  // set margins
	  $pdf->setMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT);
	  $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
	  $pdf->setFooterMargin(PDF_MARGIN_FOOTER);
	  
	  // set auto page breaks
	  //$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	  
	  // set image scale factor
	  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	  
	  // set some language-dependent strings (optional)
	  if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		  require_once(dirname(__FILE__).'/lang/eng.php');
		  $pdf->setLanguageArray($l);
	  }
	  
	  //$pdf->setFont('freeserif', '', 12);;
	  $pdf->setFont('freeserif', '', 12);;
	  
	  $pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
	  
	  // Add a page
	  // This method has several options, check the source code documentation for more information.
	  $pdf->AddPage();
	  
	  $html = '
	  <style>
	  .th {
		background-color:#d9d9d9;
		color:#bf0000;
		font-weight:bold;
	  }
	  .form-control {
		font-size:17px;
	  }
	  </style>
	  <div class="container">
	  <div id="page1" class="page active"><p>&nbsp;</p>
<table border="1" cellspacing="0" cellpadding="5" width="100%">
  <tr>
    <td width="50%"><strong>Company name/ Firma</strong></td>
    <td width="50%">'.$data["user"]["name"].'</td>
  </tr>
  <tr>
    <td><strong>Contact person/ Ansprechperson</strong></td>
    <td>'.$data["user"]["contact_person"].'</td>
  </tr>
  <tr>
    <td><strong>Tel.</strong></td>
    <td>'.$data["user"]["phone"].'</td>
  </tr>
  <tr>
    <td><strong>E-Mail</strong></td>
    <td>'.$data["user"]["email"].'</td>
  </tr>
  <tr>
    <td><strong>HQC certificate N°/ HQC Zertifikat
Nummer </strong></td>
    <td>'.$data["app"]["CertificateNumber"].'</td>
  </tr>
</table>
<p>Please upload all related documents on Halal e-Zone and add the new items on products tab and fill the following table after that.</p>
<p>Bitte fügen Sie die neuen Artikel in product tab auf Halal e-Zone und laden Sie alle zugehörigen Unterlagen hoch danach füllen Sie bitte die folgende Tabelle 
aus.</p>
<table border="1" cellspacing="0" cellpadding="5" width="100%">
  <tr>
    <td width="50%"><strong>Item name/ Artikelbezeichnung</strong></td>
    <td width="25%"><strong>Item N°/ Artikelnummer</strong></td>
    <td width="25%"><strong>Halal e-Zone HCP N°</strong></td>	
  </tr>
';
foreach ($data["products"] as $product) {
	$html .= '  <tr>
    <td>'.$product['product'].'</td>
    <td><strong>'.$product["ean"].'</strong></td>
    <td><strong>'.$product["hcpid"].'</strong></td>	
  </tr>';
}
$html .= '</table></div></div>';

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------
// Close and output PDF document
// This method has several options, check the source code documentation for more information.

//============================================================+
// END OF FILE
//============================================================+

if ($preview) {
	  $pdf->Output($attach, 'D');
}
else {
	$pdf->Output($dest_path,'F');
}

}

function saveOfferPDF($data, $attach='F0417_Offer Halal certification_EN.pdf', $dest_path, $preview=false) {
	class MYTCPDF extends TCPDF {
		

  public function Header(){
     $html = '<table border="1" cellspacing="0" cellpadding="5"  width="100%">
  <tr>
  	<td width="33%" align="left" valign="top">Form: F0417<br />
Revision Date: 03.01.2025<br />
Page:'.$this->PageNo().' from '.$this->getAliasNumPage().'
</td>
  	<td width="34%" align="center"><img src="http://test2022.halal-e.zone/img/pdf/logo.png" width="100" /></td>
  	<td width="33%" align="center" valign="middle" style="vertical-align:middle;"><strong>Halal quality control '.$this->data["app"]["offerOffice"].'<br />
Financial offer </strong>
</td>        
  </tr>
</table>
';
     $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
  }
  
  public function Footer(){
	  
     $html = '<table border="1" cellspacing="0" cellpadding="5" width="100%"><tr>
    <td>Created    by:<br />
      Shady    Dabshah</td>
    <td>Reviewed    by:<br />
      Wassiem    Al Chaman</td>
    <td>Approved    by:<br />
      Ibrahim    Salama</td>
    <td>Revision    Nr.:<br />
      03</td>
  </tr>
</table>';

     $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
  }
}
// create new PDF document
$pdf = new MYTCPDF('P', 'mm', array(395.732, 279.654), true, 'UTF-8', false);

$pdf->data = $data;

// set header and footer fonts
$pdf->setHeaderFont(Array('times', '', 12));
$pdf->setFooterFont(Array('times', '', 12));

// set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->setHeaderMargin(PDF_MARGIN_HEADER);
$pdf->setFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

$pdf->setFont('times', '', 12, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

$fullAddress = '';

if (isset($data['user']['address']) && !empty($data['user']['address'])) {
    $fullAddress .= $data['user']['address'];
}

if (isset($data['user']['city']) && !empty($data['user']['city'])) {
    $fullAddress .= ', ' . $data['user']['city'];
}

if (isset($data['user']['state']) && !empty($data['user']['state'])) {
    $fullAddress .= ', ' . $data['user']['state'];
}

if (isset($data['user']['zip']) && !empty($data['user']['zip'])) {
    $fullAddress .= ' ' . $data['user']['zip'];
}

if (isset($data['user']['country']) && !empty($data['user']['country'])) {
    $fullAddress .= ', ' . $data['user']['country'];
}

$html =
'<p>&nbsp;</p>
<h1 style="text-align:center;">Offer Halal certification</h1>
<p>&nbsp;</p>
<p><strong>Company: '.$data['user']["name"].'</strong><br />
<strong>Address: '.$fullAddress.'</strong><br />
<strong>Offer N°: '.$data['id'].'</strong><br />
<strong>Offer Date: '.date('d/m/Y').'</strong></p>
<p>HQC is an independent  international accredited Halal certification body. HQC certificate is  accredited and recognized by almost all Halal authorities and accreditation  bodies over the globe.</p>
<p>Our international accreditation and  certification provide recognition to our customers&rsquo; products as they are easily  accepted as Halal nearly worldwide including:</p>
<ul type="disc">
  <li><strong>BPJPH, INDONESIA</strong></li>
  <li><strong>JAKIM,       MALAYSIA</strong></li>
  <li><strong>MUI,       INDONESIA</strong></li>
  <li><strong>CICOT,       THAILAND</strong></li>
  <li><strong>MOIAT, UAE</strong></li>
  <li><strong>SFDA,       Saudi Arabia</strong></li>
  <li><strong>EIAC       UAE</strong></li>
  <li><strong>HAC,       SRI LANCA </strong></li>
  <li><strong>as       well as in&nbsp;almost&nbsp;all Islamic countries in Asia and Africa and       the Muslim Communities in Europe</strong></li>
</ul>
<p><strong>Certification fee: The certification  fees for your company are as follows:</strong></p>';
  $count = 0;
  for ($i=0; $i<count($data["offer"]); $i++){
	  $count++;
	  if ($count == 1) {
$html .= '<table border="1" cellspacing="0" cellpadding="10" width="100%" nobr="true">
  <tr>
    <td width="65%"><strong>Service</strong></td>
    <td width="35%"><strong>Fees in € excl. VAT</strong></td>
  </tr>'; 
     }
	  $service = $data["offer"][$i]['Service'];
	  $service = str_replace('[prodnumber]', $data['user']["prodnumber"], $service);
  	  $service = str_replace('[ingrednumber]', $data['user']["ingrednumber"], $service);
$html .='  
  <tr>
    <td>'.$service.'</td>
    <td>'.$data["offer"][$i]['Fee'].'</td>
  </tr>';
	if ($count == 5) {
		$html .= '</table>';
		$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		$pdf->AddPage();
		$html = '';
		$count = 0;
	}
  }
if ($count > 0) {
	$html .= '</table>';
	$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
	$html = '';
}
$tos_link = ''; 
if ($data["app"]["offerOffice"] == "HU") {
	$tos_link = 'https://drive.google.com/drive/folders/1LZZnkMYKlHoXn3EibX1L0wYhF-uEmEIJ?usp=sharing';
}
else {
	$tos_link = 'https://drive.google.com/drive/folders/16gA9xVR2UKgRpPCpegvAud9MvKZwNr3C?usp=sharing';
}
$html .=' 
<p>&nbsp;</p> 
<p>Transfer and accommodation costs will be invoiced separately in accordance with our updated GTC (01/2025), Point 3.5.</p>
<p>Halal eZone: The document submitting to HQC is without exception via the online portal Halal eZone. This portal allows you to have a fast and efficient certification process and offers you a clear and user-friendly document management system (DMS), with which you can find all documents and information per mouse click. </p>
<p>All offers are non-binding unless specifically agreed otherwise in writing. Our General Terms and conditions are to be applied. Click here to view or save our GTC 
'.$tos_link.'</p>
<table border="0" cellspacing="0" cellpadding="10" width="100%">
<tr> <td width="50%">
Acceptance of GTC and offer:  <br />
Company: '.$data['user']["name"].'<br />                                                                                                        
Name and position:<br />                                                                                            
Place, date:  <br />                                                                                                           
Signature and stamp: <br />
</td>
<td width="50%">
Acceptance of order:<br />
For '.($data["app"]["offerOffice"] == "HU" ? "HQC Kft" : "HQC GmbH").'<br />
Name: <br />
Place, date: <br /> 
Signature and stamp<br />
</td>
</tr>
</table>';

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);


if ($preview) {
	$pdf->Output('F0417_Offer_Halal_Certification.pdf', 'D');
}
else {
	$pdf->Output($dest_path,'F');
}

}

function saveOfferPDF1($data, $attach='F0417_Offer Halal certification_EN.pdf', $dest_path, $preview=false) {
	
	$pdf = new Fpdi();
	
	/* <Virtual loop> */
	$pdf->AddPage();
	$pdf->setSourceFile($attach);
	
	  $tplIdx = $pdf->importPage(1);
	  $pdf->useTemplate($tplIdx, 10, 10, 200);
	  $pdf->SetFont('Times', '', 10);
	  //$pdf->SetTextColor(63,72,204);
	
	  $x = 55;
	  $y = 78;  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, $data['user']["name"]);
	  $y += 7.5;
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, $data['id']);
	
	  $x = 23;
	  $y += 103.5;
	  
	  $service = $data["offer"][0]['Service'];
	  $service = str_replace('[prodnumber]', $data['user']["prodnumber"], $service);
  	  $service = str_replace('[ingrednumber]', $data['user']["ingrednumber"], $service);
	  
	  $pdf->SetFont('Times', '', 10);
	  $pdf->SetXY($x, $y);
	  $pdf->MultiCell(115, 4, str_replace("<br />", "\n", $service));

	  $x += 120;
	  $y += 2;
	  	  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, $data["offer"][0]['Fee']);
	  
	  $pdf->AddPage();
	  $tplIdx = $pdf->importPage(2);
	  $pdf->useTemplate($tplIdx, 10, 10, 200);
	  $x = 23	;
	  $y = 82; 
	  $pdf->SetLineWidth(0.1);


	  $pdf->SetFont('Times', '', 10);
	  $pdf->SetXY($x, $y);
	  for ($i=1; $i<count($data["offer"]); $i++){
	  	  $cx = $x;
	  	  $cy = $y;		  
		  $pdf->SetXY($x, $y);		  
		  $pdf->MultiCell(115, 4, str_replace("<br />", "\n", $data["offer"][$i]['Service']));
	
		//  $x += 120;
		  //$y += 2;
			  
//		  $pdf->SetXY($x+120, $y);
//		  $pdf->Write(0, $data["offer"][0]['Fee']);

		  $y = $pdf->GetY();
			$pdf->Line(21, $y+5, 199.5, $y+5);	  
			$y+=10;

		  $pdf->SetXY($cx+120, $cy);
		  $pdf->Write(0, $data["offer"][0]['Fee']);

		}
	  
	  
	  
	 // $x += 5;
	//  $y = 68;  
	//  $pdf->SetXY($x, $y);
	//  $pdf->Write(0,  $data["offer"][0]['Fee']);
  
	if ($preview) {
		  $pdf->Output($attach, 'I');
	}
	else {
		$pdf->Output($dest_path,'F');
	}
}
function saveHQAccessDataPDF($data, $attach='F0-01 new customer registration.pdf', $dest_path, $offerOffice="", $preview=false) {
	$pdf = new Fpdi();
	
  $pdf->AddPage();
  $pdf->setSourceFile($attach);

  $tplIdx = $pdf->importPage(1);
  $pdf->useTemplate($tplIdx, 10, 10, 200);

  $pdf->SetFont('Arial', '', 10);
  $pdf->SetTextColor(63,72,204);

  if ($offerOffice == "HU") {
	$x = 176;
	$y = 81.5;
}
  else {
  	$x = 176;
  	$y = 71.5;
  }  
  $pdf->SetXY($x, $y);
  $pdf->Write(0, date("d/m/Y"));

  $x = 63;
  $y = 119;  
  if ($offerOffice == "HU") {
	$y = 119;
  }
  $pdf->SetXY($x, $y);
  $pdf->Write(0, $data["name"]);

  if ($offerOffice == "HU") {
	$y +=10;
  }
  else {
  	$y += 8;
  }
  $pdf->SetXY($x, $y);
  $pdf->Write(0, $data["address"].", ".$data["country"]);

  if ($offerOffice == "HU") {
	$y +=10;
  }
  else {
  	$y += 8;
  } 
  $pdf->SetXY($x, $y);
  $pdf->Write(0, strip_tags($data["category"]));

	if ($preview) {
		  $pdf->Output($attach, 'D');
	}
	else {
		$pdf->Output($dest_path,'F');
	}  
}

function truncateString($string, $length = 30) {
    $lines = [];
    if (strlen($string) > $length) {
        // Find the last space before the specified length
        $lastSpace = strrpos(substr($string, 0, $length), ' ');

        if ($lastSpace !== false) {
            // Truncate the string at the last space before the specified length
            $lines[] = substr($string, 0, $lastSpace);
            $lines[] = substr($string, $lastSpace + 1);
        } else {
            // If no space found, simply truncate the string
            $lines[] = substr($string, 0, $length);
            $lines[] = substr($string, $length);
        }
    } else {
        // If the string length is less than or equal to the specified length, return the string as a single line
        $lines[] = $string;
    }
    return $lines;
}

function saveAccessDataPDF($data, $attach='05access data Halal eZone.pdf', $dest_path, $offerOffice="AT", $preview=false) {
	
  $pdf = new Fpdi();

  /* <Virtual loop> */
  $pdf->AddPage();
  $pdf->setSourceFile($attach);

  $tplIdx = $pdf->importPage(1);
  $pdf->useTemplate($tplIdx, 10, 10, 200);

  $pdf->SetFont('helvetica', '', 7);
  $pdf->SetTextColor(63,72,204);

  if ($offerOffice == "HU") {
	$x = 29;
	$y = 105.5;  
}
  else {
  	$x = 29;
  	$y = 93.5;  
  }

  $lines = truncateString(iconv('UTF-8', 'windows-1252', $data["name"]));
  $gap = 0;
  foreach ($lines as $line) {
  	$pdf->SetXY($x, $y + $gap);
  	$pdf->Write(0,  $line);
	$gap += 3;
  }

  $x += 54;
  $pdf->SetXY($x, $y);
  $pdf->Write(0, $data["username"]);

  $x += 54;
  $pdf->SetXY($x, $y);
  $pdf->Write(0, $data["password"]);

	if ($preview) {
		  $pdf->Output($attach, 'D');
	}
	else {
		$pdf->Output($dest_path,'F');
	}
}

function saveApplicationPDF($data, $attach, $dest_path) {

		/*
		$pdf = new Fpdi();
		
		$pdf->AddPage();
		$pdf->setSourceFile('../files/docs/'.$attach);
		
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, 10, 10, 200);
		$pdf->AddPage();
		
		$tplIdx = $pdf->importPage(2);
		$pdf->useTemplate($tplIdx, 10, 10, 200);
		$pdf->AddPage();
		
		$tplIdx = $pdf->importPage(3);
		$pdf->useTemplate($tplIdx, 10, 10, 200); // dynamic parameter based on your page
		$pdf->SetFont('Arial', '', 8);
		$pdf->SetTextColor(63,72,204);
		
		$x = 95;
		$y = 47.5;  
		$pdf->SetXY($x, $y);
		$pdf->Write(0, $data['name']);
		$y += 4;
		$pdf->SetXY($x, $y);
		$pdf->Write(0, "");
		$y += 4;
		$pdf->SetXY($x, $y);
		$pdf->Write(0, $data['category']);
		$y += 4;
		$pdf->SetXY($x, $y);
		$pdf->Write(0, "");
		$y += 4;
		$pdf->SetXY($x, $y);
		$pdf->Write(0, "");
		
		$y += 59;
		$pdf->SetXY($x, $y);
		$pdf->Write(0, $data['name']);
			
		$y += 4;
		$pdf->SetXY($x, $y);
		$pdf->Write(0, $data["contact_person"]);
		
		$y += 4;
		$pdf->SetXY($x, $y);
		$pdf->Write(0, $data["phone"]);
		
		$y += 4;
		$pdf->SetXY($x, $y);
		$pdf->Write(0, $data["email"]);
		
		for ($i=4; $i<=7; $i++) {
			$pdf->AddPage();
			
			$tplIdx = $pdf->importPage($i);
			$pdf->useTemplate($tplIdx, 10, 10, 200); // dynamic parameter based on your page

		}
		
		$pdf->Output($dest_path,'F');
		*/
		
		class MYTCPDF extends TCPDF {

			public function Header(){
			   $html = '<table border="1"  border="1" cellspacing="0" cellpadding="5"  width="100%">
			<tr>
				<td width="33%" align="left" valign="top">Form: F0422<br />
		  Revision Date: 29 April 2021<br />
		  Page:'.$this->PageNo().' from 5
		  </td>
				<td width="34%" align="center"><img src="http://test2022.halal-e.zone/img/pdf/logo.png" width="100" /></td>
				<td width="33%" align="center" valign="middle" style="vertical-align:middle;"><strong>Halal Quality Control<br />
			  Application Form  </strong>
		  </td>        
			</tr>
		  </table>
		  ';
			   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
			}
			
			public function Footer(){
				
			   $html = '<table border="1"  border="1" cellspacing="0" cellpadding="5" width="100%"><tr>
			  <td>Created    by:<br />
				Shady    Dabshah</td>
			  <td>Reviewed    by:<br />
				Wassiem    Al Chaman</td>
			  <td>Approved    by:<br />
				Ibrahim    Salama</td>
			  <td>Revision    Nr.:<br />
				05</td>
			</tr>
		  </table>';
		  
			   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
			}
		  }
		  // create new PDF document
		  $pdf = new MYTCPDF('P', 'mm', array(395.732, 279.654), true, 'UTF-8', false);
		  //$pdf = new MYTCPDF();
		  // set header and footer fonts
		  $pdf->setHeaderFont(Array('times', '', 12));
		  $pdf->setFooterFont(Array('times', '', 12));
		  
		  // set default monospaced font
		  //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		  
		  // set margins
		  $pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		  $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
		  $pdf->setFooterMargin(PDF_MARGIN_FOOTER);
		  
		  // set auto page breaks
		  //$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		  
		  // set image scale factor
		  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		  
		  // set some language-dependent strings (optional)
		  if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			  require_once(dirname(__FILE__).'/lang/eng.php');
			  $pdf->setLanguageArray($l);
		  }
		  
		  $pdf->setFont('freeserif', '', 12);;
		  
		  $pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
		  
		  
		  // Add a page
		  // This method has several options, check the source code documentation for more information.
		  $pdf->AddPage();
		  
		  $html = '
		  <style>
		  .form-control {font-size:16px;}
		  </style>
		  <div class="container">
		  <div id="page1" class="page active">
			<p style="text-align:center;"><img
			  src="http://test2022.halal-e.zone/img/pdf/logo.png"
			  width="200"
			/></p>
			<h1 style="text-align:center;">The Halal Quality Control Group</h1>
			<h2 style="text-align:center;">Application Form</h2>
			<p style="text-align:center;">Please ensure that you provide as much information as possible.<br />
			  In case you are not able to answer a question, please mention
			  ‘n.a.’<br />
			  If a question is unclear, please contact a staff member for assistance
			  before providing an answer</p>
			  <p></p>
			  <table width="100%" border="0" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="20%"></td>
				<td width="60%"><table border="1" 
			cellpadding="8"
			cellspacing="0"
			class="table table-bordered table-sm"
			width="100%"
		  >
					<tr>
					  <td style="text-align:center;" width="8%">1</td>
					  <td width="40%">Company Name:</td>
					  <td width="52%"><input type="text" size="33"
				  name="company"
				  id="company"
				  class="form-control"
				  value="'.$data['name'].'"
				/></td>
					</tr>
					<tr>
					  <td style="text-align:center;">2</td>
					  <td>Company Representative:</td>
					  <td><input type="text" size="33"
				  name="rep"
				  id="rep"
				  value="'.$data['contact_person'].'"
				class="form-control"
				/></td>
					</tr>
					<tr>
					  <td style="text-align:center;">3</td>
					  <td>Halal Manager (if any):</td>
					  <td><input type="text" size="33"
				  class="form-control"
				  name="halalManager"
				  id="halalManager"
				  value=""
				/></td>
					</tr>
					<tr>
					  <td style="text-align:center;">4</td>
					  <td>Date of Request:</td>
					  <td><input type="text" size="33"
				  class="form-control"
				  name="date"
				  id="date"
				  value="'.date('d/m/Y').'"
				/></td>
					</tr>
				  </table></td>
				<td width="20%"></td>
			  </tr>
			</table>
			<p></p><p></p><p></p><p></p><p></p>
			<p></p><p></p><p></p><p></p><p></p>
			<p></p><p></p><p></p><p></p><p></p>
			<p></p><p></p><p></p><p></p><p></p>
			<p></p><p></p><p></p><p></p><p></p>
			<p></p><p></p><p></p><p></p><p></p>
			<p style="text-align:center;font-size:14px;">2022 (C) This document is the sole property of Halal Quality Control.
			  The usage is only permitted by invitation and to be sent by a reliable
			  source. All rights reserved. No part of this document may be
			  reproduced, distributed, or transmitted in any form or by any means,
			  including photocopying, recording, or other electronic or mechanical
			  methods for external use, without prior written permission of Halal
			  Quality Control</p>
		  </div>
		  <br pagebreak="true"/>
		  <div id="page2" class="page"><table border="1" cellpadding="4" cellspacing="0" class="table table-bordered table-sm tabel-hover table-responsive" width="100%">
			  <tr>
				<th colspan="3" style="text-align:center;"> <strong>Section 1: Manufacturing Plant Details</strong> </th>
			  </tr>
			  <tr>
				<td width="4%">1</td>
				<td width="48%">Official Company Name(s)</td>
				<td width="48%"><input type="text" size="50"
				  class="form-control"
				  name="OfficialCompanyName"
				  id="OfficialCompanyName"
				  value="'.$data['name'].'"
				/></td>
			  </tr>
			  <tr>
				<td>2</td>
				<td>Contact person<br />
				  <strong>Name, Email, Telephone number </strong></td>
				<td><table cellpadding="2" cellspacing="0" border="0" width="100%">
				  <tr>
					<td width="32%"><label for="contactName">Name:</label></td>
					<td width="68%"><input type="text" size="35"
				  class="form-control"
				  name="contactName"
				  id="contactName" 
				  placeholder=""
				  value="'.$data['contact_person'].'"
				/></td>
				</tr>
				<tr>
				<td><label for="qdContactEmail">Email:</label></td>
				<td><input type="text" size="35"
				  class="form-control"
				  name="contactEmail"
				  id="contactEmail"
				  placeholder=""
				  value="'.$data['email'].'"
				/></td>
				</tr>
				<tr>
				<td><label for="qdContactPhone">Telephone number:</label></td>
				<td><input type="text" size="35"
				  class="form-control"
				  name="contactPhone"
				  id="contactPhone"
				  placeholder=""
				  value="'.$data['phone'].'"
				/></td></tr></table></td>
			  </tr>
			  <tr>
				<td>3</td>
				<td>Address of the Manufacturing Plant(s)<br />
				  <strong>Street, Postal Code, City/State, Country </strong></td>
				<td>
				<table cellpadding="2" cellspacing="0" border="0" width="100%">
				  <tr>
					<td width="5%"><span>1: </span></td>
					<td>
					<input type="text" size="45"
					class="form-control"
					name="address1"
					id="address1"
					placeholder="Address 1"
					value="'.$data["address"].'"
				  /></td>
				  </tr>
				  <tr>
				  <td><span>2: </span></td>
				  <td>
					<input type="text" size="45"
					class="form-control"
					name="address2"
					id="address2"
					placeholder="Address 2"
					value=""
				  /></td>
				  </tr>
				  <tr>        
				  <td><span>3: </span></td>
				  <td>
					<input type="text" size="45"
					class="form-control"
					name="address3"
					id="address3"
					placeholder="Address 3"
					value=""
				  />
				  </td>
				  </tr>
				  <tr>
				  <td><span>4: </span></td>
				  <td>
					<input type="text" size="45"
					class="form-control"
					name="address4"
					id="address4"
					placeholder="Address 4"
					value=""
				  />
				  </td>
				  </tr>
				  </table>
				  <span>More than 4 (please send a separate annex)</span></td>
			  </tr>
			  <tr>
				<td>4</td>
				<td>Legal Entity Title</td>
				<td><div><input type="text" size="50"
				  class="form-control"
				  name="legalEntity"
				  id="legalEntity"
				  placeholder=""
				  value=""
				/></div>
				  <strong>Example: Ltd, SA, SPA, BV, GMBH, AS, NV</strong></td>
			  </tr>
			  <tr>
				<td>5</td>
				<td>VAT/Tax/BTW Number</td>
				<td><input type="text" size="50"
				  class="form-control"
				  name="vat"
				  id="vat"
				  placeholder=""
				  value="'.$data['vat'].'"
				/></td>
			  </tr>
			  <tr>
				<td>6</td>
				<td>Business Registration Number</td>
				<td><input type="text" size="50"
				  class="form-control"
				  name="regNo"
				  id="regNo"
				  placeholder=""
				  value=""
				/></td>
			  </tr>
			  <tr>
				<td>7</td>
				<td>Your Sector<br />
				  <strong>Please see notes underneath this page</strong></td>
				<td>';
				$categories = getProductCategories();
				foreach ($categories as $i => $category) {
					$html .= '<span class="form-check">
					<input
					class="form-check-input"
					type="radio"
					name="sector"
					id="sector'.$i.'"
					value="'.slugify($category).'" '.($category == $data['category'] ? 'checked="checked"' : '').'
				  />
					<label class="form-check-label" for="sector1"> '.$category.' </label>
				  </span><br/>';
				}
			$html .= '<span class="form-check">
					<input
					class="form-check-input"
					type="radio"
					name="sector"
					id="sector13"
					value="Other"
				  />
					<label class="form-check-label" for="sector13"> Other: </label>
					<input type="text" name="otherSector" value="" class="form-control" size="20"/>
				  </span></td>
			  </tr>
			  <tr>
				<th colspan="3" style="text-align:center;"> <strong>Section 2: General Information</strong> </th>
			  </tr>
			  <tr>
				<td>1</td>
				<td><strong>Certification Category [Annex 1]</strong></td>
				<td><div><input type="text" size="50"
				  class="form-control"
				  name="certCategory"
				  id="certCategory"
				  placeholder=""
				  value=""
				/></div>
				  <strong>Please see Annex 1</strong></td>
			  </tr>
			  <tr>
				<td>2</td>
				<td><strong>Scope of Activities</strong><br />
				  <strong>Please describe your production activities</strong></td>
				<td><input type="text" size="50"
				  class="form-control"
				  name="scopeOfActivities"
				  id="scopeOfActivities"
				  placeholder=""
				  value=""
				/></td>
			  </tr>
			  <tr>
				<td>3</td>
				<td>EG or EC Number(s) if applicable</td>
				<td><input type="text" size="50"
				  class="form-control"
				  name="EGECNumbers"
				  id="EGECNumbers"
				  placeholder=""
				  value=""
				/></td>
			  </tr>
			  <tr>
				<td>4</td>
				<td> Description of the products or materials destined for Halal
				  certification </td>
				<td><input type="text" size="50"
				  class="form-control"
				  name="ProdDesc"
				  id="ProdDesc"
				  placeholder=""
				  value=""
				/></td>
			  </tr>
			  <tr>
				<td>5</td>
				<td>Estimated amount of items required for Halal certification<br />
				  <strong
					>The estimated amount of (end) products<br />
				  which will be mentioned on a Halal certificate.<br />
				  Only per product code or product formulation.</strong
				  ></td>
				<td><span class="form-check">
					<input
					class="form-check-input"
					type="radio"
					name="numItems"
					id="numItems1"
					value="1-10"
				  />
					<label class="form-check-label" for="numItems1"> 1-10 </label>
				  </span><br/><span class="form-check">
					<input
					class="form-check-input"
					type="radio"
					name="numItems"
					id="numItems2"
					value="11-20"
				  />
					<label class="form-check-label" for="numItems2"> 11-20 </label>
				  </span><br/><span class="form-check">
					<input
					class="form-check-input"
					type="radio"
					name="numItems"
					id="numItems3"
					value="21-25"
				  />
					<label class="form-check-label" for="numItems3"> 21-25 </label>
				  </span><br/><span class="form-check">
					<input
					class="form-check-input"
					type="radio"
					name="numItems"
					id="numItems4"
					value="51-100"
				  />
					<label class="form-check-label" for="numItems4"> 51-100 </label>
				  </span><br/><span class="form-check">
					<input
					class="form-check-input"
					type="radio"
					name="numItems"
					id="numItems5"
					value="101-500"
				  />
					<label class="form-check-label" for="numItems5"> 101-500 </label>
				  </span><br/><span class="form-check">
					<input
					class="form-check-input"
					type="radio"
					name="numItems"
					id="numItems6"
					value="Other"
				  />
					<label class="form-check-label" for="numItems6"> Other </label>
				  </span></td>
			  </tr>
			  <tr>
				<td>6</td>
				<td> Estimated quantity in kilogram, tons, or liters of Halal produced
				  goods </td>
				<td><div class="input-group">
					<input
					class="form-control"
					type="text"
					name="halalQty"
					id="halalQty"
					value=""
					size="40"
				  />
					<select name="units" size="0" class="form-select">
					  <option value="KG">KG</option>
					  <option value="Liter">Liter</option>
					  <option value="Ton">Ton</option>
					  <option value="Other">Other</option>
					</select>
				  </div></td>
			  </tr>
			  
			</table>
			<p> <sup>1</sup> <span
			  >Applicable only for slaughtering facilities which are able to
			  sacrifice permissible animals</span
			><br />
			  <sup>2</sup
			><span> Facilities which work with fresh, frozen, treated, dried, cooled, or
			  salted meats deriving from permissible animals</span
			><br />
			  <sup>3</sup
			><span> Facilities which use animal derived materials to create the final
			  product or raw materials-gelatin, collagen, enzyme etc</span
			><br />
			  <sup>4</sup
			><span> Facilities which produce not-fit-for-human products</span
			>5<span> Facilities which produce either edible or non-edible flavoring
			  components, includes intermediate products</span
			> </p>
		  </div>
		  <br pagebreak="true"/>
		  <div id="page3" class="page">
			<table border="1" cellpadding="4" cellspacing="0" class="table table-bordered table-sm tabel-hover table-responsive" width="100%">
		  <tr>
				<td width="4%">7</td>
				<td width="48%">Export Region(s)</td>
				<td width="48%">
				<table border="0" cellpadding="4" cellspacing="0" width="100%">
				<tr>
					<td width="50%"><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions1"
						id="regions1"
						value="1"
					  />
						<label class="form-check-label" for="regions1"> United Arab Emirates </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions2"
						id="regions2"
						value="1"
					  />
						<label class="form-check-label" for="regions2"> Saudi Arabia </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions3"
						id="regions3"
						value="1"
					  />
						<label class="form-check-label" for="regions3"> Qatar </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions4"
						id="regions4"
						value="1"
					  />
						<label class="form-check-label" for="regions4"> Middle East </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions5"
						id="regions5"
						value="1"
					  />
						<label class="form-check-label" for="regions5"> Europe </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions6"
						id="regions6"
						value="1"
					  />
						<label class="form-check-label" for="regions6"> North Africa </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions7"
						id="regions7"
						value="1"
					  />
						<label class="form-check-label" for="regions7"> Other </label>
					  </span></td><td width="50%" valign="top"><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions8"
						id="regions8"
						value="1"
					  />
						<label class="form-check-label" for="regions8"> Turkey </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions9"
						id="regions9"
						value="1"
					  />
						<label class="form-check-label" for="regions9"> Egypt </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions10"
						id="regions10"
						value="1"
					  />
						<label class="form-check-label" for="regions10"> Pakistan </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions11"
						id="regions11"
						value="1"
					  />
						<label class="form-check-label" for="regions11"> Indonesia </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions12"
						id="regions12"
						value="1"
					  />
						<label class="form-check-label" for="regions12"> Malaysia </label>
					  </span><br/><span class="form-check">
						<input
						class="form-check-input"
						type="checkbox"
						name="regions13"
						id="regions13"
						value="1"
					  />
						<label class="form-check-label" for="regions13"> Singapore </label>
					  </span>
					  </td>
					</tr>
					</table>
					
				   </td>
			  </tr>
			  <tr>
				<td>8</td>
				<td>Preferred Language</td>
				<td><input type="text" size="50"
				  class="form-control"
				  name="lang"
				  id="lang"
				  placeholder=""
				  value=""
				/></td>
			  </tr>    
			  <tr>
				<td>9</td>
				<td>Total amount of sites</td>
				<td><input type="text" size="50"
				  class="form-control"
				  name="numSites"
				  id="numSites"
				  placeholder=""
				  value=""
				/></td>
			  </tr>
			  <tr>
				<td>10</td>
				<td>Number of employees including external and temporary staff</td>
				<td> Employees working at the Facility<br />
				  <input type="text" size="50"
				  class="form-control"
				  name="numEmp"
				  id="numEmp"
				  placeholder=""
				  value=""
				/></td>
			  </tr>
			  <tr>
				<td>11</td>
				<td>Outsourced manufacturing activities orprocesses if any</td>
				<td><input type="text" size="50"
				  class="form-control"
				  name="outsourcedActivities"
				  id="outsourcedActivities"
				  placeholder=""
				  value=""
				/></td>
			  </tr>
			  <tr>
				<td>12</td>
				<td>Applicable or preferred Halal Standard if any or if known</td>
				<td height="125"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="checkbox"
					name="halalStandard1"
					id="halalStandard1"
					value="JAKIM-HAS-1500:2019"
				  />
					<label class="form-check-label" for="halalStandard1">JAKIM HAS 1500:2019</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="checkbox"
					name="halalStandard2"
					id="halalStandard2"
					value="SMIIC-1:2019"
				  />
					<label class="form-check-label" for="halalStandard2">SMIIC 1:2019</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="checkbox"
					name="halalStandard3"
					id="halalStandard3"
					value="GSO-2055-1"
				  />
					<label class="form-check-label" for="halalStandard3">GSO 2055-1</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="checkbox"
					name="halalStandard4"
					id="halalStandard4"
					value="MUI-HAS-23201"
				  />
					<label class="form-check-label" for="halalStandard4">MUI HAS 23201</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="checkbox"
					name="halalStandard5"
					id="halalStandard5"
					value="UAE.S-2055-1"
				  />
					<label class="form-check-label" for="halalStandard5">UAE.S 2055-1</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="checkbox"
					name="halalStandard6"
					id="halalStandard6"
					value="Unknown"
				  />
					<label class="form-check-label" for="halalStandard6">Unknown</label> 
				  </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="halalStandardRemarks"
				  id="halalStandardRemarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>13</td>
				<td> Owner or main shareholder of the applicant<br />
				  (if owned by a group or another holding) </td>
				<td><input type="text" size="50"
				  class="form-control"
				  name="owner"
				  id="owner"
				  value=""
				/></td>
			  </tr>
			</table>
		  
		  
			<table border="1" cellpadding="4" cellspacing="0" class="table table-bordered table-sm tabel-hover table-responsive" width="100%">
			  <tr>
				<th colspan="3" style="text-align:center;"> <strong>Section 3: Additional Information</strong> </th>
			  </tr>
			  <tr>
				<td width="4%">1</td>
				<td width="48%"> Contact Person Quality Department<br />
				  <strong>Name, Email, Telephone number </strong></td>
				<td width="48%">
				<table cellpadding="2" cellspacing="0" border="0" width="100%">
				  <tr>
					<td width="32%"><label for="qdContactName">Name:</label></td>
					<td width="68%"><input type="text" size="35"
				  class="form-control"
				  name="qdContactName"
				  id="qdContactName" 
				  placeholder=""
				  value=""
				/></td>
				</tr>
				<tr>
				<td><label for="qdContactEmail">Email:</label></td>
				<td><input type="text" size="35"
				  class="form-control"
				  name="qdContactEmail"
				  id="qdContactEmail"
				  placeholder=""
				  value=""
				/></td>
				</tr>
				<tr>
				<td><label for="qdContactPhone">Telephone number:</label></td>
				<td><input type="text" size="35"
				  class="form-control"
				  name="qdContactPhone"
				  id="qdContactPhone"
				  placeholder=""
				  value=""
				/></td></tr></table>
				</td>
			  </tr>
			  <tr>
				<td>1.1</td>
				<td> Does the company hold any Food Safety Management System
				  certification? </td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-1-1"
					id="q3-1-1-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-1-1-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-1-1"
					id="q3-1-1-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-1-1-no">No</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-1-1-remarks"
				  id="q3-1-1-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>2</td>
				<td>Has the company been awarded a Halal certification before?</td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-2"
					id="q3-2-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-2-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-2"
					id="q3-2-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-2-no">No</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-2-remarks"
				  id="q3-2-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>3</td>
				<td>Is the company currently Halal certified?</td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-3"
					id="q3-3-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-3-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-3"
					id="q3-3-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-3-no">No</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-3-remarks"
				  id="q3-3-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>4</td>
				<td> Has the company received a Halal training or consultancy before? </td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-4"
					id="q3-4-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-4-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-4"
					id="q3-4-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-4-no">No</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-4-remarks"
				  id="q3-4-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>5</td>
				<td>Has the company been consulted on Halal certification?</td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-5"
					id="q3-5-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-5-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-5"
					id="q3-5-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-5-no">No</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-5-remarks"
				  id="q3-5-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>6</td>
				<td>Has the company been rejected Halal certification before?</td>
				<td  height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-6"
					id="q3-6-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-6-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-6"
					id="q3-6-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-6-no">No</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-6-remarks"
				  id="q3-6-Remarks"></textarea></div></td>
			  </tr>
			  </table>
		  </div>
		  <br pagebreak="true"/>
		  <div id="page4" class="page">
		  <table border="1" cellpadding="4" cellspacing="0" class="table table-bordered table-sm tabel-hover table-responsive" width="100%">      
			  <tr>
				<th colspan="3" style="text-align:center;"><strong>Optional Questions (7 – 20)</strong></th>
			  </tr>
			  <tr>
				<td width="4%">7</td>
				<td width="48%"> Is the company free from any type of meats or meat derivatives? </td>
				<td width="48%" height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-7"
					id="q3-7-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-7-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-7"
					id="q3-7-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-7-no">No</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-7-remarks"
				  id="q3-7-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>8</td>
				<td> Does the company handle, process, or distributes any porcine
				  (pork) within its facility? </td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-8"
					id="q3-8-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-8-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-8"
					id="q3-8-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-8-no">No</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-8"
					id="q3-8-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-8-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-8-remarks"
				  id="q3-8-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>9</td>
				<td> If the company does have any fresh, processed, or frozen meats
				  within its facility, which meats are these? </td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-9"
					id="q3-9-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-9-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-9-remarks"
				  id="q3-9-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>10</td>
				<td> Does the company handle, process, or distribute any type of animal
				  derived materials within its facility?<br />
				  If yes, please describe </td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-10"
					id="q3-10-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-10-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-10"
					id="q3-10-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-10-no">No</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-10"
					id="q3-10-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-10-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-10-remarks"
				  id="q3-10-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>11</td>
				<td>Is any amount of alcohol present in your final product?</td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-11"
					id="q3-11-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-11-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-11"
					id="q3-11-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-11-no">No</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-11"
					id="q3-11-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-11-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-11-remarks"
				  id="q3-11-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>12</td>
				<td> Is any amount of alcohol used during the manufacturing of your end
				  product? </td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-12"
					id="q3-12-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-12-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-12"
					id="q3-12-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-12-no">No</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-12"
					id="q3-12-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-12-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-12-remarks"
				  id="q3-12-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>13</td>
				<td>Is any alcohol used during the cleaning process?</td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-13"
					id="q3-13-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-13-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-13"
					id="q3-13-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-13-no">No</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-13"
					id="q3-13-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-13-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-13-remarks"
				  id="q3-13-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>14</td>
				<td> Does the company manufacture its own products? <br />
				  If not, please describe who is the manufacturing party </td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-14"
					id="q3-14-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-14-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-14"
					id="q3-14-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-14-no">No</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-14-remarks"
				  id="q3-14-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>15</td>
				<td> Is the company a private label holder?<br />
				  Applicable if your company outsources the production to another
				  party </td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-15"
					id="q3-15-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-15-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-15"
					id="q3-15-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-15-no">No</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-15"
					id="q3-15-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-15-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-15-remarks"
				  id="q3-15-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td width="4%">16</td>
				<td width="48%">Does the company outsource any production activities?</td>
				<td width="48%" height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-16"
					id="q3-16-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-16-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-16"
					id="q3-16-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-16-no">No</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-16"
					id="q3-16-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-16-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-16-remarks"
				  id="q3-16-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>17</td>
				<td>Does the company have dedicated days for Halalproductions?</td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-17"
					id="q3-17-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-17-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-17"
					id="q3-17-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-17-no">No</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-17"
					id="q3-17-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-17-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-17-remarks"
				  id="q3-17-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>18</td>
				<td>Does the company have a separate area for Halalproductions?</td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-18"
					id="q3-18-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-18-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-18"
					id="q3-18-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-18-no">No</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-18"
					id="q3-18-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-18-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-18-remarks"
				  id="q3-18-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>19</td>
				<td>Does the company have suppliers which are Halal certified?</td>
				<td height="84"><span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-19"
					id="q3-19-yes"
					value="Yes"
				  />
					<label class="form-check-label" for="q3-19-yes">Yes</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-19"
					id="q3-19-no"
					value="No"
				  />
					<label class="form-check-label" for="q3-19-no">No</label>
				  </span>
				  <span class="form-check form-check-inline">
					<input
					class="form-check-input"
					type="radio"
					name="q3-19"
					id="q3-19-na"
					value="NA"
				  />
					<label class="form-check-label" for="q3-19-na">Not Applicable</label> </span>
				  <div>
		  Remarks:<br />
				  <textarea cols="50" 
				  class="form-control"
				  name="q3-19-remarks"
				  id="q3-19-Remarks"></textarea></div></td>
			  </tr>
			  <tr>
				<td>20</td>
				<td height="84"> Do you have a contact person within the Halal Quality Control
				  Group? </td>
				<td>
				<table cellpadding="2" cellspacing="0" border="0" width="100%">
				  <tr>
					<td width="30%">Contact Person:</td>
					<td width="60%">
				  <input type="text" size="35"
				  class="form-control"
				  name="gContactName"
				  id="gContactName"
				  placeholder="Contact Person"
				  value=""
				/></td>
				</tr><tr>
				  <td>Country:</td>
				  <td><input type="text" size="35"
				  class="form-control"
				  name="gCountry"
				  id="gCountry"
				  placeholder="Country"
				  value=""
				/></td>
				</tr>
			  </table></td>
				</tr>
			  </table>
			  <br pagebreak="true"/><p></p><p style="text-align:center;"><strong>Section 4: Approval of the Contact Person / Application Filler [Please
			  check all the Boxes below for approval]</strong> </p>
			  <p></p>
			  <p>
			  <table width="100%" border="0" cellpadding="0" cellspacing="0">
			  <tr>
				<td width="20%"></td>
				<td width="60%">
			<table border="1"
			cellpadding="8" 
			cellspacing="0"
			class="table table-bordered table-sm"
		  >
			  <tr>
				<td width="10%" style="text-align:center;">1</td>
				<td width="45%">Name and Surname:</td>
				<td width="45%"><input type="text" size="25"
				  class="form-control"
				  name="applicantName"
				  id="applicantName"
				  value=""
				/></td>
			  </tr>
			  <tr>
				<td style="text-align:center;">2</td>
				<td>Position/Function/Title:</td>
				<td><input type="text" size="25"
				  class="form-control"
				  name="applicantTitle"
				  id="applicantTitle"
				  value=""
				/></td>
			  </tr>
			</table>
			</td>
			<td width="20%"></td>
			</tr>
			</table>
			</p>
			<table border="1" cellpadding="8" cellspacing="0" class="table table-bordered">
			  <tr>
				<td width="15%" style="text-align:center;">
				<span class="form-check"><input
				  class="form-check-input"
				  type="checkbox"
				  name="terms1"
				  id="terms1"
				  value="1"
				/></span></td>
				<td width="85%"><p><strong>Application Form Commitment</strong><br />
				  By providing this Application Form, I have read and made sure that
				  the information provided is true and to the best of my knowledge.
				  I agree that the service provider may request additional
				  documentation if information is missing or is unclear.</p></td>
			  </tr>
			  <tr>
				<td style="text-align:center;">
				<span class="form-check">
				<input
				  class="form-check-input"
				  type="checkbox"
				  name="terms2"
				  id="terms2"
				  value="1"
				/></span>
				</td>
				<td><p><strong>Data Confidentiality Commitment</strong><br />
				  All the above information is confidential and will be used as such
				  and only for the official purpose of applying a request for Halal
				  Certification trough Halal Quality Control.</p></td>
			  </tr>
			  <tr>
			  <td style="text-align:center;">
			  <span class="form-check">
			  <input
				class="form-check-input"
				type="checkbox"
				name="terms3"
				id="terms3"
				value="1"
			  /></span>
			  </td>
			  <td><p><strong>Acknowledgment of Authority </strong><br />
			  By providing this Application Form, I have read and made sure that
			  the information provided is true and to the best of my knowledge.
			  I agree that the service provider may request additional
			  documentation if information is missing or is unclear.</p></td>
			</tr>  
			</table>
		  </div>
		  
		  <h4>Annex 2: Risk Classes</h4>
		  <table border="1" 
			class="table table-bordered"
		  cellpadding="8"
		  cellspacing="0"
		  width="100%"
		  >
			<tr>
			  <th width="25%">Complexity Class</th>
			  <th width="75%">Example of Sectors</th>
			</tr>
			<tr>
			  <td><strong>Very High Level</strong></td>
			  <td> Chemicals and pharmaceuticals “not elsewhere classified”; processed
				meat products; slaughtering of land animals; genetically modified
				organisms and products; food additives [E400s]; bio cultures;
				processing aids; flavoring and aromas [organic and synthetic];
				fragrances; microorganisms; gelatin; collagen; animal extracts;
				animal skin and hair; animal fats; animal stocks </td>
			</tr>
			<tr>
			  <td><strong>High Level</strong></td>
			  <td> Cheese products; biscuits; snacks; edible oil; beverages; dietary
				supplements; cleaning agents; packaging and wrapping material;
				leather products; processed fish or shellfish products; enzymes;
				vinegars; animal and fish feed; sauces and condiments; [canned]
				soups; </td>
			</tr>
			<tr>
			  <td><strong>Medium Level</strong></td>
			  <td> Dairy products; fish products; egg products; beekeeping; spices;
				horticultural products; preserved fruits; preserved vegetables;
				canned products; pasta; sugar; transportation and storage;
				warehousing; cosmetics [raw materials]; cosmetics [end products] </td>
			</tr>
			<tr>
			  <td><strong>Low Level </strong></td>
			  <td> Fresh line caught fish; egg production; milk production; fishing;
				hunting; fruits; vegetables; grain; fresh fruits and fresh juices;
				drinking water; flour; salt; inorganic components; minerals; plants </td>
			</tr>
		  </table>
		  <p></p>
		  <h4>Annex 3: Terms and Definitions</h4>
		  <table border="1" 
			class="table table-bordered"
		  cellpadding="8"
		  cellspacing="0"
		  width="100%"
		  >
			<tr>
			  <th width="25%">Term</th>
			  <th width="75%">Definition</th>
			</tr>
			<tr>
			  <td>Halal (permissible)</td>
			  <td> simply means the permissible; all matters, foods, beverages,
				medicines, or injectibles <strong>permitted</strong> based on the
				Sharia Law or by a fatwa </td>
			</tr>
			<tr>
			  <td>Haram (forbidden)</td>
			  <td> simply means the prohibited; all matters, foods, beverages,
				medicines, or injectibles prohibited based on the Sharia Law or by a
				fatwa. </td>
			</tr>
			<tr>
			  <td>Non-Halal Meats (non-Halal)</td>
			  <td> Animals which have been sacrificed (slaughtered) in which the name
				of Allah SWT (God) is <strong>not</strong> mentioned during the
				zabah (slaughtering) process. Even if the animal is a Halal animal,
				it shall be deemed as not permissible for consumption in any method. </td>
			</tr>
			<tr>
			  <td>Najis (unclean)</td>
			  <td> simply means the <strong>impure</strong>. Strong examples hereof are
				Halal items that have been <strong>contaminated</strong> with
				non-Halal items or Halal items which come into <strong>direct contact</strong> with non-Halal items. </td>
			</tr>
		  </table>
		  </div>';
		  
		  // Print text using writeHTMLCell()
		  $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
		  
		  //$pdf->Output('', 'I');	
		  $pdf->Output($dest_path,'F');	
	}
	
function saveQuestionnairePDF($data, $attach, $dest_path, $industry) {

	$pageCount = 0;

	if ($industry == "Meat Processing") {
		$pageCount = 21;
	}
	else if ($industry == "Slaughter Houses") {
		$pageCount = 22;
	}
	else  {
		$pageCount = 17;
	}
	
	$pdf = new Fpdi();
	
	 /* <Virtual loop> */
	  $pdf->AddPage();
	  
	  $pdf->setSourceFile($attach);
	
	  $tplIdx = $pdf->importPage(1);
	  $pdf->useTemplate($tplIdx, 10, 10, 200);
	
	  $pdf->SetFont('Arial', '', 9);
	  $pdf->SetTextColor(63,72,204);
	
	  if ($industry == "Meat Processing") {
		  $x = 92;
		  $y = 175.5;  
	  }
	  else if ($industry == "Slaughter Houses") {
		  $x = 92;
		  $y = 170;  
	  }
	  else   {
		  $x = 92;
		  $y = 175.5;  
	  }
	  
	  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, $data["name"]);
	
	  $y += 4.5;  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, $data["country"]);
	
	  $y += 4.5;  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, $data["contact_person"]);
	
		for ($i=2;$i<=$pageCount;$i++) {
		  $pdf->AddPage();
		  $tplIdx = $pdf->importPage($i);
		  $pdf->useTemplate($tplIdx, 10, 10, 200);
		}
	
	
	//if ($preview) {
	//	  $pdf->Output($attach, 'D');
	//}
	//else {
		$pdf->Output($dest_path,'F');
	//}
}

function savePorkFreePDF($data, $attach, $dest_path, $industry) { 

	$pageCount = 3;

	class MYTCPDF extends TCPDF {

		public function Header(){
		   $html = '<table border="1"  border="1" cellspacing="0" cellpadding="5"  width="100%">
		<tr>
			<td width="33%" align="left" valign="top">Form: F0451<br />
	  Revision Date: 28.02.2020<br />
	  Page:'.$this->PageNo().' from 3
	  </td>
			<td width="34%" style="text-align:center;"><img src="http://test2022.halal-e.zone/img/pdf/logo.png" width="100" /></td>
			<td width="33%" style="text-align:center;" valign="middle"><strong>Halal Quality Control<br/> 
		  Porcine Free Facility Declaration<br/>
		  Porcine Free Production Line Declaration</strong>
	  </td>        
		</tr>
	  </table>
	  ';
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
		
		public function Footer(){
			
		   $html = '<table border="1" cellspacing="0" cellpadding="5" width="100%"><tr>
		  <td>Created    by:<br />
			Shady    Dabshah</td>
		  <td>Reviewed    by:<br />
			Wassiem    Al Chaman</td>
		  <td>Approved    by:<br />
			Ibrahim    Salama</td>
		  <td>Revision    Nr.:<br />
			01</td>
		</tr>
	  </table>';
	  
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
	  }
	  // create new PDF document
	  $pdf = new MYTCPDF('P', 'mm', array(395.732, 279.654), true, 'UTF-8', false);
	  //$pdf = new MYTCPDF();
	  // set header and footer fonts
	  $pdf->setHeaderFont(Array('times', '', 12));
	  $pdf->setFooterFont(Array('times', '', 12));
	  
	  // set default monospaced font
	  //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	  
	  // set margins
	  $pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	  $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
	  $pdf->setFooterMargin(PDF_MARGIN_FOOTER);
	  
	  // set auto page breaks
	  //$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	  
	  // set image scale factor
	  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	  
	  // set some language-dependent strings (optional)
	  if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		  require_once(dirname(__FILE__).'/lang/eng.php');
		  $pdf->setLanguageArray($l);
	  }
	  
	  $pdf->setFont('freeserif', '', 12);;
	  //$pdf->setFont('helvetica', '', 11);
	  
	  $pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
	  
	  
	  // Add a page
	  // This method has several options, check the source code documentation for more information.
	  $pdf->AddPage();
	  
	  $html = '
	  <style>
	  .form-control {
		font-size:16px;
	  }
	  </style>
	  <div class="container">
	  <div id="page1" class="page active">
		<p style="text-align:center;">
		  <img
			src="http://test2022.halal-e.zone/img/pdf/logo.png"
			width="200"
		  />
		</p>
		<h1 style="text-align:center;">
		  Halal Quality Control<br /><br />
		  Porcine Free Facility Declaration<br />
		  Porcine Free Production Line Declaration
		</h1>
		<p style="text-align:center;">
		  <strong>This form declares that the (to be) inspected facilty is free from any
		  porcine materials or porcine derivatives, being either fully free or
		  at least free from contamination</strong>
		</p><p></p><table width="100%" border="0">
		  <tr>
			<td width="30%"></td>
			<td width="40%"><table width="100%" cellpadding="5">
		  <tr>
			<td width="30%" style="font-size: 18px">Company:</td>
			<td width="70%"><input
			type="text" size="25"
			class="form-control"
			name="companyName"
			id="companyName"
			value="'.$data["name"].'"
		  /></td>
		  </tr>
		  <tr>
			<td style="font-size: 18px">Date:</td>
			<td><input
			type="text" size="25"
			class="form-control"
			name="date"
			id="date"
			value="'.date('d/m/Y').'"
		  /></td>
		  </tr>
		  </table></td>
		  <td width="30%"></td>
		  </tr>
		</table><p></p>
		<p style="text-align:center;">
		  This document is a supporting file in the event Halal Quality Control
		  could not accept a material or product by default. For many countries,
		  it is a supporting tool to have this as part of evidence.
		</p>
		<p style="text-align:center;">
		  In the event you cannot provide us this declaration, this will be
		  condisered as a potential critical point.
		</p>
		<p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p>
		<p>
		  (C) This document is the sole property of Halal Quality Control. The
		  usage is only permitted by invitation and to be sent by a reliable
		  source. All rights reserved. No part of this document may be
		  reproduced, distributed, or transmitted in any form or by any means,
		  including photocopying, recording, or other electronic or mechanical
		  methods for external use, without prior written permission of Halal
		  Quality Control.
		</p>
	  </div>
	  <br pagebreak="true"/>
	  <div id="page2" class="page"><table
		  border="1"
		  cellspacing="0"
		  cellpadding="10"
		  class="table table-bordered table-sm mb-0"
		  width="100%"
		>
		  <tr class="border-bottom-0">
			<td width="33%" style="text-align:center;">
			  <strong
				>This declaration needs be filled in by a competent
				authority</strong
			  >
			</td>
			<td width="34%" style="text-align:center;">
			  <strong>Porcine Free Facility Declaration</strong>
			</td>
			<td width="33%" style="text-align:center;">
			  <strong>Porcine Free Production Line Declaration</strong>
			</td>
		  </tr>
		</table>
		<table
		  border="1"
		  cellspacing="0"
		  cellpadding="10"
		  width="100%"
		  class="table table-bordered table-sm mb-0"
		>
		  <tr>
			<td width="50%">
			  <table border="0">
				<tr>
				<td width="50%"><label for="regNo">Plant Number / Registration No:</label></td>
				<td width="50%">
				  <input
					type="text" size="20"
					class="form-control"
					name="regNo"
					id="regNo"
					value=""
				  />
				</td>
			  </tr>
			  </table>
			</td>
			<td width="50%" style="text-align:center;"></td>
		  </tr>
		  <tr>
			<td width="50%">
			<table border="0">
			<tr>
			<td width="20%"><label for="regNo">Country:</label></td>
			<td width="80%">
			<input
			type="text" size="20"
			class="form-control"
			name="country"
			id="country"
			value=""
		  />
			</td>
		  </tr>
		  </table>      
			</td>
			<td width="50%">
			<table border="0">
			<tr>
			<td width="35%"><label for="contactPerson">Contact Person:</label>
				</td>
				<td width="65%">
				  <input
					type="text" size="20"
					class="form-control"
					name="contactPerson"
					id="contactPerson"
					value=""
				  />
				</td>
			  </tr>
			  </table>
			</td>
		  </tr>
		  <tr>
			<td colspan="2" style="text-align:center;">
			  <strong>
				This document is a supporting file in the event Halal Quality Control could not accept a material or product by default.
				<br />
				For many countries, it is a supporting tool to have this as part
				of evidence. In the event you cannot provide us this 
				declaration, this will be condisered as a potential critical
				point.
			  </strong>
			</td>
		  </tr>
		</table>
		<p></p>
		<table border="1" width="100%" cellpadding="10" cellspacing="0" class="table table-bordered table-sm mt-5">
		  <tr>
			<td style="text-align:center;" colspan="3">
			  <strong>Please answer the supplementary questions below. </strong>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" colspan="3">
			  <strong>Questionnaire</strong>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" width="5%">01</td>
			<td class="text-left" width="75%" height="93"><div>Are any porcine materials stored on site or handled within the
			  facility?</div>
			  <label for="rem1">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem1" id="rem1"></textarea>
			</td>
			<td width="10%">
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q1"
				  id="q1-1"
				/>
				<label class="form-check-label" for="q1-1">Yes</label>
			  </td>
			  <td width="10%">
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q1"
				  id="q1-0"
				/>
				<label class="form-check-label" for="q1-0">No</label>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" >02</td>
			<td class="text-left" height="93"><div>Are any porcine materials stored on site or handled within the
			  facility?</div>
			  <label for="rem2">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem2" id="rem2"></textarea>
			</td>
			<td>
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q2"
				  id="q2-1"
				/>
				<label class="form-check-label" for="q2-1">Yes</label>
				</td>
				<td width="10%">
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q2"
				  id="q2-0"
				/>
				<label class="form-check-label" for="q2-0">No</label>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" >03</td>
			<td class="text-left" height="93"><div>Do you use separate or segregate production lines if porcine is
			  available within the facility?</div>
			  <label for="rem1">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem3" id="rem3"></textarea>
			</td>
			<td >
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q3"
				  id="q3-1"
				/>
				<label class="form-check-label" for="q3-1">Yes</label>
				</td>
				<td><input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q3"
				  id="q3-0"
				/>
				<label class="form-check-label" for="q3-0">No</label>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" >04</td>
			<td class="text-left" height="93"><div>Do you use separate or segregate tools and equipment if porcine is
			  available within the facility?</div>
			  <label for="rem1">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem4" id="rem4"></textarea>
			</td>
			<td >
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q4"
				  id="q4-1"
				/>
				<label class="form-check-label" for="q4-1">Yes</label>
				</td>
				<td><input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q4"
				  id="q4-0"
				/>
				<label class="form-check-label" for="q4-0">No</label>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" >05</td>
			<td class="text-left" height="93"><div>Are all raw materials (including processing aids and cleaning
			  materials) free from porcine?</div>
			  <label for="rem1">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem5" id="rem5"></textarea>
			</td>
			<td >
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q5"
				  id="q5-1"
				/>
				<label class="form-check-label" for="q5-1">Yes</label>
				</td>
				<td><input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q5"
				  id="q5-0"
				/>
				<label class="form-check-label" for="q5-0">No</label>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" >06</td>
			<td class="text-left" height="93"><div>Do you have any trading products which you buy from third parties
			  on site?</div>
			  <label for="rem1">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem6" id="rem6"></textarea>
			</td>
			<td >
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q6"
				  id="q6-1"
				/>
				<label class="form-check-label" for="q6-1">Yes</label>
				</td>
				<td><input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q6"
				  id="q6-0"
				/>
				<label class="form-check-label" for="q6-0">No</label>
			</td>
		  </tr>
		</table>
	  </div>
	  <br pagebreak="true"/>
	  <div id="page3" class="page">
		<p style="text-align:center;">
		  <strong
			>If any of the critical question is answered with a \'Yes\', you may
			be contacted for further follow-ups or the next steps.</strong
		  >
		</p>
		<p style="text-align:center;">
		  <strong
			>Data Confidentiality Commitment: All the above information is
			confidential and will be used as such and only for the official
			purpose of applying a request for Halal Quality Control</strong
		  >
		</p>
		<p></p>
		<hr />
		<p class="text-center mb-4">
		  <strong
			>I hereby confirm that the information given by me is true to the
			best of my knowledge and beliefs</strong
		  >
		</p>
		<table border="1" cellpadding="10" class="table table-bordered table-sm mb-4">
		  <tr>
			<td>
			  <strong
				>Are you a customer of Halal Quality Control or a supplier?
				Please fill in the box.
			  </strong>
			  <div class="mt-2">
				<div class="form-check form-check-inline">
				  <input
					class="form-check-input"
					type="radio"
	  value="Yes"
					name="type"
					id="type1"
					value="Customer"
				  />
				  <label class="form-check-label" for="type1">Customer</label>
			   
				  <input
					class="form-check-input"
					type="radio"
	  value="Yes"
					name="type"
					id="type2"
					value="Supplier"
				  />
				  <label class="form-check-label" for="type2">Supplier</label>
				</div>
			  </div>
			</td>
		  </tr>
		</table>
		<p></p>
		<table border="0" cellpadding="5" class="table table-bordered table-sm mb-4">
		<tr>
		  <td width="22%">
			<label for="dateF">Date:</label>
		  </td>
		  <td width="78%">
			<input
			  type="text" size="25"
			  class="form-control"
			  name="dateF"
			  id="dateF"
			  value=""
			/>
		  </td>
		</tr>
		<tr>
		  <td>
			<label for="Nameofcompetentperson">Name of competent person:</label>
		  </td>
		  <td>
			<input
			  type="text" size="25"
			  class="form-control"
			  name="Nameofcompetentperson"
			  id="Nameofcompetentperson"
			  value=""
			/>
		  </td>
		</tr>
		<tr>
		  <td>
			<label for="Function">Function:</label>
		  </td>
		  <td>
			<input
			  type="text" size="25"
			  class="form-control"
			  name="Function"
			  id="Function"
			  value=""
			/>
		  </td>
		</tr>
		</table>  
		<p>
		  <strong>Annex (1) : The following materials may not be used: </strong>
		</p>
		<ul>
		  <li>Pork or dog meat and all of its derivatives</li>
		  <li>Reptiles, frogs, insects, or human meat and/or all byproducts</li>
		  <li>Meat from endangered animal species</li>
		  <li>Alcoholic byproducts and its derivatives that are physically
			separated</li>
		  <li>Blood from any creature</li>
		  <li>Carrion of animals or humans</li>
		  <li>Production lines which alternate between forbidden materials and
			suitable materials</li>
		  <li>Materials may not mix with forbidden materials that may be derived
			from additives, processing aids, from the production facility(ies)</li>
		  <li>Animal based materials must be derived from permissible animals</li>
		  <li>All byproducts from permissible animals should be derived from Halal
			slaughtered animals</li>
		</ul>
	  </div>
	  </div>';
	  
	  // Print text using writeHTMLCell()
	  $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);	
	  
	  $pdf->Output($dest_path,'F');
}


	////////////////////////////////////////////////////////////////////
	function saveDecisionMakingReportPDF($data, $attach='F0417_Offer Halal certification_EN.pdf', $dest_path,$preview=false) {

		
		class MYTCPDF6 extends TCPDF {
	
			public function Header(){
			   $html = '<table border="1"  border="1" cellspacing="0" cellpadding="8"  width="100%">
			<tr>
				<td width="33%" align="left" valign="top">Form: F0403<br/> 
			  Revision Date: 29.4.2021<br/>
		  Page:'.$this->PageNo().' from 3
		  </td>
				<td width="34%" style="text-align:center;"><img src="http://test2022.halal-e.zone/img/pdf/logo.png" width="100" /></td>
				<td width="33%" style="text-align:center;" valign="middle"><strong>Halal Quality Control<br/>
				Protocol of the Decision-Making Committee
			  </strong>
		  </td>        
			</tr>
		  </table>
		  ';
			   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
			}
			
			public function Footer(){
				
			   $html = '<table border="1" cellspacing="0" cellpadding="8" width="100%"><tr>
			  <td>Created    by:<br />  
				Shady    Dabshah</td>
			  <td>Reviewed    by:<br />
				Wassiem    Al Chaman</td>
			  <td>Approved    by:<br />
				Ibrahim    Salama</td>
			  <td>Revision    Nr.:<br />
				03</td>
			</tr>
		  </table>';
		  
			   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
			}
		  }
		  // create new PDF document
		  $pdf = new MYTCPDF6('P', 'mm', array(395.732, 279.654), true, 'UTF-8', false);
		  //$pdf = new MYTCPDF();
		  // set header and footer fonts
		  $pdf->setHeaderFont(Array('times', '', 12));
		  $pdf->setFooterFont(Array('times', '', 12));
		  
		  // set default monospaced font
		  //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		  
		  // set margins
		  $pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		  $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
		  $pdf->setFooterMargin(PDF_MARGIN_FOOTER);
		  
		  // set auto page breaks
		  //$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		  
		  // set image scale factor
		  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		  
		  // set some language-dependent strings (optional)
		  if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			  require_once(dirname(__FILE__).'/lang/eng.php');
			  $pdf->setLanguageArray($l);
		  }
		  
		  //$pdf->setFont('freeserif', '', 12);;
		  $pdf->setFont('freeserif', '', 12);;
		  
		  $pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
		  
		  // Add a page
		  // This method has several options, check the source code documentation for more information.
		  $pdf->AddPage();
		  
		  $html = '
		  <style>
		  .th {
			background-color:#d9d9d9;
			color:#bf0000;
			font-weight:bold;
		  }
		  .form-control {
			font-size:17px;
		  }
		  </style>
		  <div class="container">
		  <div id="page1" class="page active">
			<p style="text-align:center;">
			  <img
				src="http://test2022.halal-e.zone/img/pdf/logo.png"
				width="200"
			  />
			</p>
			<h1 style="text-align:center;">
			Halal Quality Control<br/>
			Decision Making Report
			</h1>
			<p></p><table width="100%" border="0" cellpadding="8" cellspacing="0">
			<tr>
			  <td width="20%"></td>
			  <td width="60%"><table border="0" 
		  cellpadding="8"
		  cellspacing="0"
		  class="table table-bordered table-sm"
		  width="100%"
		  >
		  <tr>
 		  <td width="45%">Company reviewed upon:</td>
		  <td width="55%"><input type="text" class="form-control" name="companyName" value="'.$data["user"]["name"].'" size="25" /></td>
		</tr>
		
		<tr>
			<td>Date of Decision Making Meeting:</td>
			<td><input type="text" class="form-control" name="dateOfMeeting" value="'.date('d/m/Y').'" size="25" /></td>
			</tr>
		</table>
		</td>
		<td width="20%"></td>
	</tr>
	</table>
		  <p></p><p></p><p></p><p></p><p></p>
		  <p></p><p></p><p></p><p></p><p></p>
		  <p></p><p></p><p></p><p></p><p></p>
		  <p></p><p></p>		  
				<p></p><p></p><p></p><p></p><p></p>
				<p></p><p></p><p></p><p></p><p></p>
				<p></p><p></p><p></p><p></p><p></p>
				<p></p><p></p><p></p><p></p><p></p>
				<p style="text-align:center;font-size:14px;">2021 (C) This document is the sole property of Halal Quality Control.
				  The usage is only permitted by invitation and to be sent by a reliable
				  source. All rights reserved. No part of this document may be
				  reproduced, distributed, or transmitted in any form or by any means,
				  including photocopying, recording, or other electronic or mechanical
				  methods for external use, without prior written permission of Halal
				  Quality Control</p>
		  </div>
		  <br pagebreak="true"/>
		  <div id="page2" class="page">
		  <br/>
		  <table border="0" 
		  cellpadding="4"
		  cellspacing="0"
		  class="table table-bordered table-sm"
		  width="100%"
		  >
		  <tr>
 		  <td width="18%"><strong>Timing(from / to):</strong></td>
		  <td width="82%"><input type="text" class="form-control" name="timingFrom" value="" size="12" /> - <input type="text" class="form-control" name="timingTo" value="" size="12" /> </td>
		</tr>
		<tr>
			<td><strong>Place:</strong></td>
			<td><input type="text" class="form-control" name="place" value="" size="25" /></td>
			</tr>
		</table>
		 
		  ';
		$options = [
			"Dr. A. M. Al Chaman",
			"Dr. Abdullah Hito",
			"Dr. Ghassan Kheirallah",
			"Dr. Ibrahim Salama",
			"Dr. Wasim Al Shaman",
			"Eng. Falko Evers",
			"Eng. Shady Dabcheh",
			"Mr. Ibrahim Akkari",
			"Mr. Mustafa Al Shaman"
		];
	$html .= 
	'<table border="0" 
		  cellpadding="4"
		  cellspacing="0"
		  class="table table-bordered table-sm"
		  width="100%"
		  ><tr>
		  <td><br/><br/><strong>Participants Involved:</strong></td>
		  </tr>
	</table>
	<table border="0" 
		  cellpadding="4"
		  cellspacing="0"
		  class="table table-bordered table-sm"
		  width="100%"
		  >
		  <tr>
 		  <td width="38%"><select name="p1" size="0" class="form-control">
		  <option value="">-</option>';

		  foreach ($options as $key=>$value) {
			$html .= '<option value="'.$value.'">'.$value.'</option>';
		  }

		  $html .= '</select> </td>

		  <td width="62%"><select name="p2" size="0" class="form-control">
		  <option value="">-</option>';
		  foreach ($options as $key=>$value) {
			$html .= '<option value="'.$value.'">'.$value.'</option>';
		  }

		  $html .= '</select> </td>
		</tr>
		<tr>
		<td><select name="p3" size="0" class="form-control">
	   <option value="">-</option>';

	   foreach ($options as $key=>$value) {
		 $html .= '<option value="'.$value.'">'.$value.'</option>';
	   }

	   $html .= '</select> </td>

	   <td><select name="p4" size="0" class="form-control">
	   <option value="">-</option>';
	   foreach ($options as $key=>$value) {
		 $html .= '<option value="'.$value.'">'.$value.'</option>';
	   }

	   $html .= '</select> </td>
	 </tr>
		</table>
	
		<p></p>
		<table border="0" cellspacing="0" cellpadding="8" width="100%">
		<tr>
		<td width="25%"><strong>Audit Report Advice:</strong></td>
		  <td>
		  <span class="form-check">
			<input
			class="form-check-input"
			type="radio"
			id="Decision1"
			name="Decision1"
			value="Positive" />
			<label class="form-check-label" for="Decision1"> Positive </label>
		  </span> 
		  <span class="form-check">
		  <input
		  class="form-check-input"
		  type="radio"
		  id="Decision2"
		  name="Decision1"
		  value="Negative" />
		  <label class="form-check-label" for="Decision2"> Negative </label>
		</span>
		  </td>
		  </tr>
		</table><br/>
	<table border="0" cellspacing="0" cellpadding="8" width="100%">
	<tr>
	<td width="25%"><strong>Implementation Report Advice:</strong></td>
	  <td>
	  <span class="form-check">
		<input
		class="form-check-input"
		type="radio"
		id="Decision3"
		name="Decision2"
		value="Positive" />
		<label class="form-check-label" for="Decision3"> Positive </label>
	  </span> 
	  <span class="form-check">
	  <input
	  class="form-check-input"
	  type="radio"
	  id="Decision4"
	  name="Decision2"
	  value="Negative" />
	  <label class="form-check-label" for="Decision4"> Negative </label>
	</span>
	  </td>
	  </tr>
    </table>
	<p></p>
	<table border="0" cellspacing="0" cellpadding="8" width="100%">
	<tr>
	<th><strong>Final Decision Taken:</strong></th>
	<th><strong>The Halal Certificate my hereby:</strong></th>
	
	</tr>  
	<tr>
	
	  <td style="height:73px;">
	  <textarea
	  class="form-control"
	  id="DRemarks"
	  name="DRemarks"
	  cols="50" rows="3"> </textarea> 
	  </td>

	  <td>
	<span class="form-check">
		<input
		class="form-check-input"
		type="radio"
		id="Decision10"
		name="Decision10"
		value="Approved" />
		<label class="form-check-label" for="Decision10"> Granted<sup>1</sup> </label>
	  </span><br/>
	  <span class="form-check">
	  <input
	  class="form-check-input"
	  type="radio"
	  id="Decision20"
	  name="Decision10"
	  value="Not_Approved" />
	  <label class="form-check-label" for="Decision20"> Put on Stand-by<sup>2</sup> </label>
	</span><br/>
	<span class="form-check">
		<input
		class="form-check-input"
		type="radio"
		id="Decision30"
		name="Decision10"
		value="Standby" />
		<label class="form-check-label" for="Decision30"> Suspend or Withdrawn<sup>3</sup> </label>
	  </span>  
	</td>
	  </tr>
	</table>

	<p></p>

	<table width="100%" border="0" cellpadding="8" cellspacing="0">
			<tr>
			  <td width="10%"></td>
			  <td width="80%">
	<table border="1" cellspacing="0" cellpadding="18" width="100%">
	<tr>
	<th width="50%"><strong>Signatories</strong></th>
	<th width="50%"><strong>Signature:</strong></th>
	</tr>  
	<tr>
	<td>Dr. Adbul Munim Al Chaman, CEO</td>
	<td></td>
	</tr>
	<tr>
	<td><input type="text" class="form-control" name="sig1" value="" size="25" /></td>
	<td></td>
	</tr>
	<tr>
	<td><input type="text" class="form-control" name="sig2" value="" size="25" /></td>
	<td></td>
	</tr>
	</table>
	</td>
	<td width="10%"></td>
	</tr>
	</table>

	<p style="text-align:center;">Please print this document and add the signatures with a pen or add an E-signature.</p>
	</div>
	<br pagebreak="true"/>
	<div id="page3" class="page">
	<p></p><p></p><p></p>
<p><sup>1</sup> The certificate can be issued since no referenced difference was found. We can certify that the product which is described in the annex to the Halal certificate of the company complies with the Halal food requirements of Islam and the requirements of the referenced Halal standard, and the processing and consumption of these products are allowed for Muslims. The compliance with Halal conformity is ensured with unannounced audits within the period of validity of the certificate. See procedure.</p>
<p><sup>2</sup> The certificate may not be issued or issued upon certain conditions because perhaps non-conformities were present, 
which affect the Halal status of the manufacturing site and/or the requirements of the referenced Halal standard.</p> 
<p><sup>3</sup> The certificate cannot be issued for an undefined time period due to a major lack of implementation or a major finding. The reason and decision should be mentioned clearly and communicated to the client.</p>

<p>General Agenda:</p>
<ol>
  <li>Opening with Koran recitation</li>
  <li>
    Review Documents, Discussion and decision for issuing the Halal Certificate
    <ol>
      <li>Review of the supported Documents, the Audit Report, Checklist, audit findings</li>
      <li>Discussion on the audit findings/non-conformities</li>
      <li>Decision Making</li>
    </ol>
  </li>
  <li>Other topics</li>
  <li>Closure of the meeting with Koran recitation</li>
  <li>Signing the Decision-Protocol</li>
</ol>

</div>

	</div>
	';
	
	// Print text using writeHTMLCell()
	$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
	
	// Close and output PDF document
	// This method has several options, check the source code documentation for more information.
	if ($preview) {
		$pdf->Output('DecisionMakingReport.pdf', 'D');
	}
	else {
		$pdf->Output($dest_path,'F');
	}
	
	//============================================================+
	// END OF FILE
	//============================================================+
	////////////////////////////////////////////////////////////////////
	}

	////////////////////////////////////////////////////////////////////
	function saveAuditReviewReportPDF($data, $attach='F0417_Offer Halal certification_EN.pdf', $dest_path,$preview=false) {

		
		class MYTCPDF_ARR extends TCPDF {
	
			public function Header(){
			   $html = '<table border="1"  border="1" cellspacing="0" cellpadding="8"  width="100%">
			<tr>
				<td width="33%" align="left" valign="top">Form: F0421<br/> 
			  Revision Date: 29.4.2021<br/>
		  Page:'.$this->PageNo().' from 5
		  </td>
				<td width="34%" style="text-align:center;"><img src="http://test2022.halal-e.zone/img/pdf/logo.png" width="100" /></td>
				<td width="33%" style="text-align:center;" valign="middle"><strong>Halal Quality Control<br/>
				Review of Audit Report and related<br/>
				Documents
			  </strong>
		  </td>        
			</tr>
		  </table>
		  ';
			   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
			}
			
			public function Footer(){
				
			   $html = '<table border="1" cellspacing="0" cellpadding="8" width="100%"><tr>
			  <td>Created    by:<br />  
				Shady    Dabshah</td>
			  <td>Reviewed    by:<br />
				Wassiem    Al Chaman</td>
			  <td>Approved    by:<br />
				Ibrahim    Salama</td>
			  <td>Revision    Nr.:<br />
				03</td>
			</tr>
		  </table>';
		  
			   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
			}
		  }
		  // create new PDF document
		  $pdf = new MYTCPDF_ARR('P', 'mm', array(395.732, 279.654), true, 'UTF-8', false);
		  //$pdf = new MYTCPDF();
		  // set header and footer fonts
		  $pdf->setHeaderFont(Array('times', '', 12));
		  $pdf->setFooterFont(Array('times', '', 12));
		  
		  // set default monospaced font
		  //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		  
		  // set margins
		  $pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		  $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
		  $pdf->setFooterMargin(PDF_MARGIN_FOOTER);
		  
		  // set auto page breaks
		  //$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		  
		  // set image scale factor
		  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		  
		  // set some language-dependent strings (optional)
		  if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			  require_once(dirname(__FILE__).'/lang/eng.php');
			  $pdf->setLanguageArray($l);
		  }
		  
		  //$pdf->setFont('freeserif', '', 12);;
		  $pdf->setFont('freeserif', '', 12);;
		  
		  $pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
		  
		  // Add a page
		  // This method has several options, check the source code documentation for more information.
		  $pdf->AddPage();
		  
		  $html = '
		  <style>
		  .th {
			background-color:#d9d9d9;
			color:#bf0000;
			font-weight:bold;
		  }
		  .form-control {
			font-size:17px;
		  }
		  </style>
		  <div class="container">
		  <div id="page1" class="page active">
			<p style="text-align:center;">
			  <img
				src="http://test2022.halal-e.zone/img/pdf/logo.png"
				width="200"
			  />
			</p>
			<h1 style="text-align:center;">
			Halal Quality Control<br/>
			Audit Review Report
			</h1>
			<p></p><table width="100%" border="0" cellpadding="8" cellspacing="0">
			<tr>
			  <td width="15%"></td>
			  <td width="70%"><table border="1" 
		  cellpadding="8"
		  cellspacing="0"
		  class="table table-bordered table-sm"
		  width="100%"
		  >
		  <tr>
		  <td style="text-align:center;" width="7%">1</td>
		  <td width="40%">Name of Reviewer:</td>
		  <td width="53%"><input type="text" class="form-control" name="nameOfReviewer" value="Mona Sherif" size="35" /></td>
		</tr>
		<tr>
		<td style="text-align:center;">2</td>
		<td>Name of Islamic Affairs Expert:</td>
		<td><input type="text" class="form-control" name="IslamicAffairsExpert" value="'.$data["app"]["IslamicAffairsExpert"].'" size="35" /></td>
	  </tr>
		<tr>
		<td style="text-align:center;">3</td>
		<td>Position:</td>
		<td><input type="text" class="form-control" name="position" value="" size="35" /></td>
		</tr>    
		<tr>
		<td style="text-align:center;">4</td>
		<td>Company Name:</td>
		<td><input type="text" class="form-control" name="companyName" value="'.$data["user"]["name"].'" size="35" /></td>
		</tr> 
		<tr>
			<td style="text-align:center;">5</td>
			<td>Date:</td>
			<td><input type="text" class="form-control" name="dateOfReview" value="'.date('d/m/Y').'" size="35" /></td>
			</tr>
		</table>
		</td>
		<td width="15%"></td>
	</tr>
	</table>
		  <p style="text-align:center;">Note: the Reviewers may not have been involved in the evaluation process.</p>
		  <p></p><p></p><p></p><p></p><p></p>
				<p></p><p></p><p></p><p></p><p></p>
				<p></p><p></p><p></p><p></p><p></p>
				<p></p><p></p><p></p><p></p><p></p>
				<p></p><p></p><p></p><p></p><p></p>
				<p></p><p></p><p></p><p></p><p></p>
				<p style="text-align:center;font-size:14px;">2021 (C) This document is the sole property of Halal Quality Control.
				  The usage is only permitted by invitation and to be sent by a reliable
				  source. All rights reserved. No part of this document may be
				  reproduced, distributed, or transmitted in any form or by any means,
				  including photocopying, recording, or other electronic or mechanical
				  methods for external use, without prior written permission of Halal
				  Quality Control</p>
		  </div>
		  <br pagebreak="true"/>
		  <div id="page2" class="page">';
		$options = [
			"Application Form",
			"Application Review",
			"Contractual Documents",
			"Audit Plan",
			"Audit Checklist",
			"Non-Conformance Report",
			"Conclusion written in the NC-Report by the Auditor",
			"Halal Master Table",
			"Product List",
			"External Halal Certificates (optional)",
			"Halal Declarations (optional)",
			"Food Safety Management System Certificates (optional)",
		];
	$html .= 
	'<br/><table border="1" cellspacing="0" cellpadding="3" width="100%">
	  <tr style="background-color:#d9d9d9;">
	  <td style="text-align:center;" width="50%">To be Reviewed Documents</td>
	  <td style="text-align:center;" width="50%">Results</td>
	  </tr>';
	  foreach ($options as $key=>$value) {
	$html .= '<tr>
		<td><span class="form-check">
		<input
		class="form-check-input"
		type="checkbox"
		id="option'.$key.'"
		name="option'.$key.'"
		value="'.slugify($value).'" />
		<label class="form-check-label" for="option'.$key.'"> '.$value.' </label>
	  </span></td>
	
	
		<td style="text-align:center;">
		<span class="form-check">
		<input
		class="form-check-input"
		type="radio"
		name="options_'.$key.'"
		id="options_'.$key.'_1"
		value="Accepted" />
		<label class="form-check-label" for="options_'.$key.'_1" checked> Accepted </label>
	  </span>
	
	  <span class="form-check">
		<input
		class="form-check-input"
		type="radio"
		name="options_'.$key.'"
		id="options_'.$key.'_2"
		value="In_Process" />
		<label class="form-check-label" for="options_'.$key.'_2">In Process</label>
	  </span>
	
	  <span class="form-check">
		<input
		class="form-check-input"
		type="radio"
		name="options_'.$key.'"
		id="options_'.$key.'_3"
		value="Refused" />
		<label class="form-check-label" for="options_'.$key.'_3">Refused</label>
	  </span>
		</td>
	  </tr>';
	}
	
	$html .= '</table>
	<p></p>
	<table border="1" cellspacing="0" cellpadding="3" width="100%">
	  <tr style="background-color:#d9d9d9;">
	  <td style="text-align:center;" width="50%">To be Reviewed Documents</td>
	  <td style="text-align:center;" width="50%">Remarks if ‘In Process’ or ‘Refused’</td>
	  </tr>';
	  foreach ($options as $key=>$value) {
	$html .= '<tr>
		<td><span class="form-check">
		<input
		class="form-check-input"
		type="checkbox"
		id="optionr'.$key.'"
		name="optionr'.$key.'"
		value="'.slugify($value).'" />
		<label class="form-check-label" for="optionr'.$key.'"> '.$value.' </label>
	  </span></td>
	
		<td>
		<input type="text"
		class="form-control"
		id="options_r_'.$key.'"
		name="options_r_'.$key.'"
		size="52" />  
		</td>
	
	  </tr>';
	}
	
	$html .= '</table>
	<p></p>
	<table border="1" cellspacing="0" cellpadding="3" width="100%">
	<tr style="background-color:#d9d9d9;">
	<td>Conclusion of the review</td>
	</tr>
	<tr>
	  <td style="height:90px;"><textarea
	  class="form-control"
	  id="Conclusion"
	  name="Conclusion"
	  cols="105" rows="4"> </textarea></td>
	  </tr>
	</table><br/><h3 style="text-align:center;">Decision by the Reviewers:</h3>
	<table border="1" cellspacing="0" cellpadding="3" width="100%">
	<tr style="background-color:#d9d9d9;">
	<td>To be Reviewed Documents</td>
	  <td>Remarks</td>
	  </tr>
	  <tr>
	<td>
	<span class="form-check">
		<input
		class="form-check-input"
		type="checkbox"
		id="Decision1"
		name="Decision1"
		value="Approved" />
		<label class="form-check-label" for="Decision1"> Approved </label>
	  </span><br/>
	  <span class="form-check">
	  <input
	  class="form-check-input"
	  type="checkbox"
	  id="Decision2"
	  name="Decision2"
	  value="Not_Approved" />
	  <label class="form-check-label" for="Decision2"> Not Approved </label>
	</span><br/>
	<span class="form-check">
		<input
		class="form-check-input"
		type="checkbox"
		id="Decision3"
		name="Decision3"
		value="Standby" />
		<label class="form-check-label" for="Decision3"> Standby </label>
	  </span>  
	</td>
	  <td style="height:73px;">
	  <textarea
	  class="form-control"
	  id="DRemarks"
	  name="DRemarks"
	  cols="50" rows="3"> </textarea> 
	  </td>
	  </tr>
	</table><br /><h3 style="text-align:center;">Signature of the Reviewers:</h3>
	</div>
	</div>
	';
	
	// Print text using writeHTMLCell()
	$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
	
	// Close and output PDF document
	// This method has several options, check the source code documentation for more information.
	if ($preview) {
		$pdf->Output('AuditReviewReport.pdf', 'D');
	}
	else {
		$pdf->Output($dest_path,'F');
	}
	
	//============================================================+
	// END OF FILE
	//============================================================+
	////////////////////////////////////////////////////////////////////
	}

function saveAlcoholFreePDF($data, $attach, $dest_path, $industry) {

	$pageCount = 3;
	/*
	$pdf = new Fpdi();
	
 	  $pdf->AddPage();
	  
	  $pdf->setSourceFile($attach);
	
	  $tplIdx = $pdf->importPage(1);
	  $pdf->useTemplate($tplIdx, 10, 10, 200);
	
	  $pdf->SetFont('Arial', '', 11);
	  $pdf->SetTextColor(63,72,204);
	
	
	  $x = 105;
	  $y = 170.5;  
  
	$pdf->SetXY($x, $y);
	  $pdf->Write(0, $data["name"]);
	
	  $y += 7.5;  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, date("d/m/Y"));
	
		for ($i=2;$i<=$pageCount;$i++) {
		  $pdf->AddPage();
		  $tplIdx = $pdf->importPage($i);
		  $pdf->useTemplate($tplIdx, 10, 10, 200);
		}
	
	
	if ($preview) {
		  $pdf->Output($attach, 'I');
	}
	else {
		$pdf->Output($dest_path,'F');
	}
	*/
	class MYTCPDF3 extends TCPDF {

		public function Header(){
		   $html = '<table border="1"  border="1" cellspacing="0" cellpadding="5"  width="100%">
		<tr>
			<td width="33%" align="left" valign="top">Form: F0453<br />
	  Revision Date: 28.02.2020<br />
	  Page:'.$this->PageNo().' from 3
	  </td>
			<td width="34%" style="text-align:center;"><img src="http://test2022.halal-e.zone/img/pdf/logo.png" width="100" /></td>
			<td width="33%" style="text-align:center;" valign="middle"><strong>Halal Quality Control<br/> 
		  Alcohol (Free) Facility <br/>
		  Declaration Form</strong>
	  </td>        
		</tr>
	  </table>
	  ';
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
		
		public function Footer(){
			
		   $html = '<table border="1" cellspacing="0" cellpadding="5" width="100%"><tr>
		  <td>Created    by:<br />
			Shady    Dabshah</td>
		  <td>Reviewed    by:<br />
			Wassiem    Al Chaman</td>
		  <td>Approved    by:<br />
			Ibrahim    Salama</td>
		  <td>Revision    Nr.:<br />
			01</td>
		</tr>
	  </table>';
	  
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
	  }
	  // create new PDF document
	  $pdf = new MYTCPDF3('P', 'mm', array(395.732, 279.654), true, 'UTF-8', false);
	  //$pdf = new MYTCPDF();
	  // set header and footer fonts
	  $pdf->setHeaderFont(Array('times', '', 12));
	  $pdf->setFooterFont(Array('times', '', 12));
	  
	  // set default monospaced font
	  //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	  
	  // set margins
	  $pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	  $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
	  $pdf->setFooterMargin(PDF_MARGIN_FOOTER);
	  
	  // set auto page breaks
	  //$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	  
	  // set image scale factor
	  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	  
	  // set some language-dependent strings (optional)
	  if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		  require_once(dirname(__FILE__).'/lang/eng.php');
		  $pdf->setLanguageArray($l);
	  }
	  
	  $pdf->setFont('freeserif', '', 12);;
	  //$pdf->setFont('helvetica', '', 11);
	  
	  $pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
	  
	  
	  // Add a page
	  // This method has several options, check the source code documentation for more information.
	  $pdf->AddPage();
	  
	  $html = '
	  <style>
	  .form-control {
		font-size:16px;
	  }
	  </style>
	  <div class="container">
	  <div id="page1" class="page active">
		<p style="text-align:center;">
		  <img
			src="http://test2022.halal-e.zone/img/pdf/logo.png"
			width="200"
		  />
		</p>
		<h1 style="text-align:center;">
		  Halal Quality Control<br /><br />
		  Alcohol (Free) Production Line Declaration Form
		</h1>
		<p style="text-align:center;">
		  <strong>This form declares that the (to be) inspected facilty is free from any intentionally added alcohol with the prupose of
		  intoxication of humans, nor that any materials or products do cross the allowed alcohol limits within foods or beverages.</strong>
		</p><p></p><table width="100%" border="0">
		  <tr>
			<td width="30%"></td>
			<td width="40%"><table width="100%" cellpadding="5">
		  <tr>
			<td width="30%" style="font-size: 18px">Company:</td>
			<td width="70%"><input
			type="text" size="25"
			class="form-control"
			name="companyName"
			id="companyName"
			value="'.$data["name"].'"
		  /></td>
		  </tr>
		  <tr>
			<td style="font-size: 18px">Date:</td>
			<td><input
			type="text" size="25"
			class="form-control"
			name="date"
			id="date"
			value="'.date('d/m/Y').'"
		  /></td>
		  </tr>
		  </table></td>
		  <td width="30%"></td>
		  </tr>
		</table><p></p>
		<p style="text-align:center;">
		This document is a supporting file in the event Halal Quality Control could not accept a material or product by default. For
		many countries, it is a supporting tool to have this as part of evidence.
		</p>
		<p style="text-align:center;">
		In the event you cannot provide us this declaration, this will be condisered as a potential critical point.
		This does not mean that the alcohol family is forbidden during production or processing, however certain conditions apply
		to be able to use any alcohol derivative during production.
		</p>
		<p style="text-align:center;">
		Not-fit-for-human-consumption products are allowed to contain alcohol as long as the source is tolerable and the end
		product is within the approved percentage limits. Grape(skins) have specific conditions to be able to approve.
		Cleaning agents, detergents, and other cleaning materials are allowed to contain ethanol.
		</p>
		<p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p>
		<p>
		  (C) This document is the sole property of Halal Quality Control. The usage is only permitted by invitation and to be sent by a reliable source.
		  All rights reserved. No part of this document may be reproduced, distributed, or transmitted in any form or by any means, including photocopying,
		  recording, or other electronic or mechanical methods for external use, without prior written permission of Halal Quality Control.
		</p>
	  </div>
	  <br pagebreak="true"/>
	  <div id="page2" class="page"><table
		  border="1"
		  cellspacing="0"
		  cellpadding="10"
		  class="table table-bordered table-sm mb-0"
		  width="100%"
		>
		  <tr class="border-bottom-0">
			<td width="33%" style="text-align:center;">
			  <strong
				>This declaration needs be filled in by a competent
				authority</strong
			  >
			</td>
			<td width="34%" style="text-align:center;">
			  <strong>Alcohol(Free)
			  Production Line
			  Declaration Form</strong>
			</td>
			<td width="33%" style="text-align:center;">
			  
			</td>
		  </tr>
		</table>
		<table
		  border="1"
		  cellspacing="0"
		  cellpadding="10"
		  width="100%"
		  class="table table-bordered table-sm mb-0"
		>
		  <tr>
			<td width="50%">
			  <table border="0">
				<tr>
				<td width="50%"><label for="regNo">Plant Number / Registration No:</label></td>
				<td width="50%">
				  <input
					type="text" size="20"
					class="form-control"
					name="regNo"
					id="regNo"
					value=""
				  />
				</td>
			  </tr>
			  </table>
			</td>
			<td width="50%" style="text-align:center;"></td>
		  </tr>
		  <tr>
			<td width="50%">
			<table border="0">
			<tr>
			<td width="20%"><label for="regNo">Country:</label></td>
			<td width="80%">
			<input
			type="text" size="20"
			class="form-control"
			name="country"
			id="country"
			value=""
		  />
			</td>
		  </tr>
		  </table>      
			</td>
			<td width="50%">
			<table border="0">
			<tr>
			<td width="35%"><label for="contactPerson">Contact Person:</label>
				</td>
				<td width="65%">
				  <input
					type="text" size="20"
					class="form-control"
					name="contactPerson"
					id="contactPerson"
					value=""
				  />
				</td>
			  </tr>
			  </table>
			</td>
		  </tr>
		  <tr>
			<td colspan="2" style="text-align:center;">
			  <strong>
			  This document is a supporting file in the event Halal Quality Control could not accept a material or product
			  by default. For many countries, it is a supporting tool to have this as part of evidence. In the event you cannot
			  provide us this declaration, this will be condisered as a potential critical point.
			  </strong>
			</td>
		  </tr>
		</table>
		<p></p>
		<table border="1" width="100%" cellpadding="10" cellspacing="0" class="table table-bordered table-sm mt-5">
		  <tr>
			<td style="text-align:center;" colspan="3">
			  <strong>Please answer the supplementary questions below. </strong>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" colspan="3">
			  <strong>Questionnaire</strong>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" width="5%">01</td>
			<td class="text-left" width="75%" height="93"><div>Is Production completely free from alcohol (except cleaning agents or detergents)? (if Yes, skip all others)?</div>
			  <label for="rem1">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem1" id="rem1"></textarea>
			</td>
			<td width="10%">
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q1"
				  id="q1-1"
				/>
				<label class="form-check-label" for="q1-1">Yes</label>
			  </td>
			  <td width="10%">
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q1"
				  id="q1-0"
				/>
				<label class="form-check-label" for="q1-0">No</label>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" >02</td>
			<td class="text-left" height="93"><div>Are any materials or end products (destined to be Halal certified) above the 1% alcohol content?</div>
			  <label for="rem2">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem2" id="rem2"></textarea>
			</td>
			<td>
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q2"
				  id="q2-1"
				/>
				<label class="form-check-label" for="q2-1">Yes</label>
				</td>
				<td width="10%">
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q2"
				  id="q2-0"
				/>
				<label class="form-check-label" for="q2-0">No</label>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" >03</td>
			<td class="text-left" height="93"><div>Do you use any alcohol derived from alcoholic beverages or grapes destined for wine production?</div>
			  <label for="rem1">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem3" id="rem3"></textarea>
			</td>
			<td >
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q3"
				  id="q3-1"
				/>
				<label class="form-check-label" for="q3-1">Yes</label>
				</td>
				<td><input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q3"
				  id="q3-0"
				/>
				<label class="form-check-label" for="q3-0">No</label>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" >04</td>
			<td class="text-left" height="93"><div>Can you provide laboratory test results when requested by the auditor to proof the contents?</div>
			  <label for="rem1">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem4" id="rem4"></textarea>
			</td>
			<td >
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q4"
				  id="q4-1"
				/>
				<label class="form-check-label" for="q4-1">Yes</label>
				</td>
				<td><input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q4"
				  id="q4-0"
				/>
				<label class="form-check-label" for="q4-0">No</label>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" >05</td>
			<td class="text-left" height="93"><div>Are there any not-fit-for-human-consumption products (flavors, aroma, vinegar, oils) which contain alcohol?</div>
			  <label for="rem1">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem5" id="rem5"></textarea>
			</td>
			<td >
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q5"
				  id="q5-1"
				/>
				<label class="form-check-label" for="q5-1">Yes</label>
				</td>
				<td><input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q5"
				  id="q5-0"
				/>
				<label class="form-check-label" for="q5-0">No</label>
			</td>
		  </tr>
		  <tr>
			<td style="text-align:center;" >06</td>
			<td class="text-left" height="93"><div>Do you produce materials or products which contain alcohol through biotransformation or chemical reactions?</div>
			  <label for="rem1">Remarks:</label><br/>
			  <textarea cols="75" class="form-control" name="rem6" id="rem6"></textarea>
			</td>
			<td >
				<input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q6"
				  id="q6-1"
				/>
				<label class="form-check-label" for="q6-1">Yes</label>
				</td>
				<td><input
				  class="form-check-input"
				  type="radio"
	  value="Yes"
				  name="q6"
				  id="q6-0"
				/>
				<label class="form-check-label" for="q6-0">No</label>
			</td>
		  </tr>
		</table>
	  </div>
	  <br pagebreak="true"/>
	  <div id="page3" class="page">
		<p style="text-align:center;">
		  <strong
			>If any of the critical question is answered with a \'Yes\', you may
			be contacted for further follow-ups or the next steps.</strong
		  >
		</p>
		<p style="text-align:center;">
		  <strong
			>Data Confidentiality Commitment: All the above information is
			confidential and will be used as such and only for the official
			purpose of applying a request for Halal Quality Control</strong
		  >
		</p>
		<p></p>
		<hr />
		<p class="text-center mb-4">
		  <strong
			>I hereby confirm that the information given by me is true to the
			best of my knowledge and beliefs</strong
		  >
		</p>
		<table border="1" cellpadding="10" class="table table-bordered table-sm mb-4">
		  <tr>
			<td>
			  <strong
				>Are you a customer of Halal Quality Control or a supplier?
				Please fill in the box.
			  </strong>
			  <div class="mt-2">
				<div class="form-check form-check-inline">
				  <input
					class="form-check-input"
					type="radio"
	  value="Yes"
					name="type"
					id="type1"
					value="Customer"
				  />
				  <label class="form-check-label" for="type1">Customer</label>
			   
				  <input
					class="form-check-input"
					type="radio"
	  value="Yes"
					name="type"
					id="type2"
					value="Supplier"
				  />
				  <label class="form-check-label" for="type2">Supplier</label>
				</div>
			  </div>
			</td>
		  </tr>
		</table>
		<p></p>
		<table border="0" cellpadding="5" class="table table-bordered table-sm mb-4">
		<tr>
		  <td width="22%">
			<label for="dateF">Date:</label>
		  </td>
		  <td width="78%">
			<input
			  type="text" size="25"
			  class="form-control"
			  name="dateF"
			  id="dateF"
			  value=""
			/>
		  </td>
		</tr>
		<tr>
		  <td>
			<label for="Nameofcompetentperson">Name of competent person:</label>
		  </td>
		  <td>
			<input
			  type="text" size="25"
			  class="form-control"
			  name="Nameofcompetentperson"
			  id="Nameofcompetentperson"
			  value=""
			/>
		  </td>
		</tr>
		<tr>
		  <td>
			<label for="Function">Function:</label>
		  </td>
		  <td>
			<input
			  type="text" size="25"
			  class="form-control"
			  name="Function"
			  id="Function"
			  value=""
			/>
		  </td>
		</tr>
		</table>  
		<p>
		  <strong>Alcohol / Ethanol: </strong>
		</p>
		<ul>
		  <li>Not-fit-for-human-consumption products are allowed to contain alcohol as long
		  as the source is tolerable and the end consumable product is within the
		  approved percentage limits</li>
		  <li>Grape(skins) has to meet specific conditions in order to be approved</li>
		  <li>Cleaning agents, detergents, and other cleaning materials are allowed to
		  contain ethanol as long as its purpose is to disinfect equipment for the purpose
		  of human safety</li>
		  <li>Intermediate products (such as flavors, fragrances, or aroma) are allowed to
		  contain alcohol during the process</li>
		  <li>All alcohol extraced or sourcing from alcoholic beverages is forbidden</li>
		  <li>All consumable foods and beverages should be within the permissible range of
		  percentages</li>
		  <li>All products or beverages should undergo a laboratory test specifying the
		  alcohol residue</li>
		  <li>Biotransformation is permissible as long as the product does not contain any
		  intenionally added forbidden components or as long as the product naming is
		  permissible</li>
		</ul>
	  </div>
	  </div>';
	  
	  // Print text using writeHTMLCell()
	  $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);	
	  
	  $pdf->Output($dest_path,'F');
}

function saveFeedstuffPDF($data, $attach, $dest_path, $industry) {

	$pageCount = 3;
	/*
	$pdf = new Fpdi();
	
 	  $pdf->AddPage();
	  
	  $pdf->setSourceFile($attach);
	
	  $tplIdx = $pdf->importPage(1);
	  $pdf->useTemplate($tplIdx, 10, 10, 200);
	
	  $pdf->SetFont('Arial', '', 11);
	  $pdf->SetTextColor(63,72,204);
	
	
	  $x = 88;
	  $y = 170.5;  
	
	$pdf->SetXY($x, $y);
	  $pdf->Write(0, $data["name"]);
	
	  $y += 7.5;  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, date("d/m/Y"));
	
		for ($i=2;$i<=$pageCount;$i++) {
		  $pdf->AddPage();
		  $tplIdx = $pdf->importPage($i);
		  $pdf->useTemplate($tplIdx, 10, 10, 200);
		}
	
	
	if ($preview) {
		  $pdf->Output($attach, 'I');
	}
	else {
		$pdf->Output($dest_path,'F');
	}
	*/
	class MYTCPDF2 extends TCPDF {

		public function Header(){
		   $html = '<table border="1"  border="1" cellspacing="0" cellpadding="10"  width="100%">
		<tr>
			<td width="33%" align="left" valign="top">Form: F0460<br />
	  Revision Date: 29.04.2021<br />
	  Page:'.$this->PageNo().' from 4
	  </td>
			<td width="34%" style="text-align:center;"><img src="http://test2022.halal-e.zone/img/pdf/logo.png" width="100" /></td>
			<td width="33%" style="text-align:center;" valign="middle"><strong>Halal Quality Control<br />
		  Animal Feedstuff Declaration Form
		  </strong>
	  </td>        
		</tr>
	  </table>
	  ';
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
		
		public function Footer(){
			
		   $html = '<table border="1" cellspacing="0" cellpadding="10" width="100%"><tr>
		  <td>Created    by:<br />
			Shady    Dabshah</td>
		  <td>Reviewed    by:<br />
			Wassiem    Al Chaman</td>
		  <td>Approved    by:<br />
			Ibrahim    Salama</td>
		  <td>Revision    Nr.:<br />
			01</td>
		</tr>
	  </table>';
	  
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
	  }
	  // create new PDF document
	  $pdf = new MYTCPDF2('P', 'mm', array(395.732, 279.654), true, 'UTF-8', false);
	  //$pdf = new MYTCPDF();
	  // set header and footer fonts
	  $pdf->setHeaderFont(Array('times', '', 12));
	  $pdf->setFooterFont(Array('times', '', 12));
	  
	  // set default monospaced font
	  //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	  
	  // set margins
	  $pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	  $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
	  $pdf->setFooterMargin(PDF_MARGIN_FOOTER);
	  
	  // set auto page breaks
	  //$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	  
	  // set image scale factor
	  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	  
	  // set some language-dependent strings (optional)
	  if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		  require_once(dirname(__FILE__).'/lang/eng.php');
		  $pdf->setLanguageArray($l);
	  }
	  
	  $pdf->setFont('freeserif', '', 12);;
	  //$pdf->setFont('helvetica', '', 11);
	  
	  $pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
	  
	  // Add a page
	  // This method has several options, check the source code documentation for more information.
	  $pdf->AddPage();
	  
	  $html = '
	  <style>
	  .form-control { font-size:16px; }
	  </style>
	  <div class="container">
	  <div id="page1" class="page active">
		<p style="text-align:center;">
		  <img
			src="http://test2022.halal-e.zone/img/pdf/logo.png"
			width="200"
		  />
		</p>
		<h1 style="text-align:center;">
		  Halal Quality Control<br /><br />
		  Animal Feedstuff Declaration Form
		</h1>
		<p style="text-align:center;">
		  <strong>This form is used to declare the feeding of the land or sea animals being raised or kept for the 
		  purpose of human consumption. In case it is bought through a third party, the third party 
		  should declare this form.</strong>
		</p><p></p><table width="100%" border="0">
		  <tr>
			<td width="30%"></td>
			<td width="40%"><table width="100%" cellpadding="10">
		  <tr>
			<td width="30%" style="font-size: 18px">Company:</td>
			<td width="70%"><input
			type="text" size="25"
			class="form-control"
			name="companyName"
			id="companyName"
			value="'.$data["name"].'"
		  /></td>
		  </tr>
		  <tr>
			<td style="font-size: 18px">Date:</td>
			<td><input
			type="text" size="25"
			class="form-control"
			name="date"
			id="date"
			value="'.date('d/m/Y').'"
		  /></td>
		  </tr>
		  </table></td>
		  <td width="30%"></td>
		  </tr>
		</table><p></p>
		<p style="text-align:center;">
		This document is a supporting file in the event Halal Quality Control could not trace the animal feedstuff or when 
		more information is requested for feedstuff. All necessary information must be filled in, otherwise the document might 
		be refused.
		</p>
		<p style="text-align:center;">Please see Annex 1 for more information and definitions.</p>
		<p style="text-align:center;">If assistance is required, please contact our office by telephone or e-mail for support.</p>
		<p></p><p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p><p></p>
		<p></p><p></p><p></p><p></p><p></p>
		<p>
		(C) This document is the sole property of Halal Quality Control. The usage is only permitted by invitation and to be sent by a reliable source.
		All rights reserved. No part of this document may be reproduced, distributed, or transmitted in any form or by any means, including photocopying, 
		recording, or other electronic or mechanical methods for external use, without prior written permission of Halal Quality Contro
		</p>
	  </div>
	  <br pagebreak="true"/>
	  <div id="page2" class="page">
	  <table
				border="1"
				cellspacing="0"
				cellpadding="10"
				class="table table-bordered mb-0"
				width="100%"
			  >
				<tr class="border-bottom-0">
				  <td width="33%" align="center">
					<strong
					  >This declaration needs be filled in by a competent
					  authority</strong
					>
				  </td>
				  <td width="34%" align="center">
					<strong>Raw Material or Product Declaration Form</strong>
				  </td>
				  <td width="33%" align="center">
					<strong>Technical Specification available to send? </strong>
				  </td>
				</tr>
			  </table>
			  <table
				border="1"
				cellspacing="0"
				cellpadding="10"
				width="100%"
				class="table table-bordered mb-0"
			  >
				<tr>
				  <td width="33%">          
						<label for="regNo">Product Name:</label>              
						<input
						  type="text"
						  class="form-control"
						  name="prodName"
						  id="prodName"
						  value=""
						  size="20"
						/>               
				  </td>
				  <td width="34%">
						<label for="regNo">Brand:</label>
						<input
						  type="text"
						  class="form-control"
						  name="brand"
						  id="brand"
						  value=""
						  size="20"
						/>
				  </td>
				  <td width="33%" align="center">
						<input
						  class="form-check-input"
						  type="radio"
						  name="tspec"
						  id="tspec1"
						  value="Yes"
						/>
						<label class="form-check-label" for="tspec1">Yes</label>
					  
						<input
						  class="form-check-input"
						  type="radio"
						  name="tspec"
						  id="tspec2"
						  value="No"
						/>
						<label class="form-check-label" for="tspec2">No</label>              
				  </td>
				</tr>
				<tr>
				  <td width="33%">
					 
						<label for="country">Country:</label>
					  
						<input
						  type="text"
						  class="form-control"
						  name="country"
						  id="country"
						  value=""
						  size="20"
						/>
					  
				  </td>
				  <td width="34%">
					 
						<label for="contactPerson">Contact Person:</label>
					 
						<input
						  type="text"
						  class="form-control"
						  name="contactPerson"
						  id="contactPerson"
						  value=""
						  size="20"
						/>
					  
				  </td>
				  <td width="33%"></td>
				</tr>
				<tr>
				  <td colspan="3" style="text-align:center;">
					<strong>
					  This document is a supporting file in the event Halal Quality
					  Control could not trace the animal feedstuff or when more
					  information is requested for feedstuff. All necessary
					  information must be filled in, otherwise the document might be
					  refused.
					</strong>
				  </td>
				</tr>
			  </table>
		<p></p>
		<table border="1" cellpadding="10" class="table table-bordered mt-5">
				<tr>
				  <td style="text-align:center;" colspan="4">
					<strong>Please answer the supplementary questions below. </strong>
				  </td>
				</tr>
				<tr>
				  <td style="text-align:center;" colspan="4">
					<strong>Questionnaire</strong>
				  </td>
				</tr>
				<tr>
				  <td style="text-align:center;" width="5%">01</td>
				  <td  width="75%">Is the feedstuff Halal certified?<br /></td>
				  <td width="10%">
					
					  <input
						class="form-check-input"
						type="radio"
						name="q1"
						id="q1-1"
						value="Yes"
					  />
					  <label class="form-check-label" for="q1-1">Yes</label>
					
				  </td>
				  <td width="10%">
					
					  <input
						class="form-check-input"
						type="radio"
						name="q1"
						id="q1-0"
						value="No"
					  />
					  <label class="form-check-label" for="q1-0">No</label>
					
				  </td>
				</tr>
				<tr>
				  <td style="text-align:center;" >02</td>
				  <td ><table border="0">
					<tr>
					  <td width="55%"><label for="expDate"
						  >Expiry Date of latest certificate:</label>
						<input
						  type="text"
						  class="form-control"
						  name="expDate"
						  id="expDate"
						  value=""
						  size="17"
						/>
						 </td>
						 <td width="45%">
						<label for="agencyName">Name of agency:</label>
						<input
						  type="text"
						  class="form-control"
						  name="agencyName"
						  id="agencyName"
						  value=""
						  size="19"
						/>
						</td>
						</tr>
						</table>
					  
				  </td>
				  <td ></td>
				  <td ></td>
				</tr>
				<tr>
				  <td style="text-align:center;" >03</td>
				  <td height="95">Is the feedstuff fully plant based?<br />
					<label for="rem3">If yes, please specify:</label><br/>
					<textarea cols="65" class="form-control" name="rem3" id="rem3"></textarea>
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q3"
						id="q3-1"
						value="Yes"
					  />
					  <label class="form-check-label" for="q3-1">Yes</label>
					
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q3"
						id="q3-0"
						value="No"
					  />
					  <label class="form-check-label" for="q3-0">No</label>
					
				  </td>
				</tr>
				<tr>
				  <td style="text-align:center;" >04</td>
				  <td height="95">Is the feedstuff (partially) produced with animal derived
					ingredients?
					<br />
					<label for="rem4">If yes, please specify:</label><br/>
					<textarea cols="65" class="form-control" name="rem4" id="rem4"></textarea>
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q4"
						id="q4-1"
						value="Yes"
					  />
					  <label class="form-check-label" for="q4-1">Yes</label>
					
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q4"
						id="q4-0"
						value="No"
					  />
					  <label class="form-check-label" for="q4-0">No</label>
					
				  </td>
				</tr>
				<tr>
				  <td style="text-align:center;" >05</td>
				  <td height="95">Is any type of blood used during the process to realize the
					feedstuff?<br />
					<label for="rem5">If yes, please specify:</label><br/>
					<textarea cols="65" class="form-control" name="rem5" id="rem5"></textarea>
				  </td>
				  <td >
					  <input
						class="form-check-input"
						type="radio"
						name="q5"
						id="q5-1"
						value="Yes"
					  />
					  <label class="form-check-label" for="q5-1">Yes</label>
					
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q5"
						id="q5-0"
						value="No"
					  />
					  <label class="form-check-label" for="q5-0">No</label>
					
				  </td>
				</tr>
				<tr>
				  <td style="text-align:center;" >06</td>
				  <td height="95">Is the farm or production facility free from forbidden animals and
					forbidden meats?<br />
					<label for="rem6">If yes, please specify:</label><br/>
					<textarea cols="65" class="form-control" name="rem6" id="rem6"></textarea>
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q6"
						id="q6-1"
						value="Yes"
					  />
					  <label class="form-check-label" for="q6-1">Yes</label>
					
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q6"
						id="q6-0"
						value="No"
					  />
					  <label class="form-check-label" for="q6-0">No</label>
					
				  </td>
				</tr>
				<tr>
				  <td style="text-align:center;" >07</td>
				  <td height="95">Are separate production lines used in the even forbidden materials
					are present?<br />
					<label for="rem7">If yes, please specify:</label><br/>
					<textarea cols="65" class="form-control" name="rem7" id="rem7"></textarea>
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q7"
						id="q7-1"
						value="Yes"
					  />
					  <label class="form-check-label" for="q7-1">Yes</label>
					
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q7"
						id="q7-0"
						value="No"
					  />
					  <label class="form-check-label" for="q7-0">No</label>
					
				  </td>
				</tr>
				<tr>
				  <td style="text-align:center;" >08</td>
				  <td height="95">Are any (micro) animal origins used during the process?<br />
					<label for="rem8">If yes, please specify:</label><br/>
					<textarea cols="65" class="form-control" name="rem8" id="rem8"></textarea>
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q8"
						id="q8-1"
						value="Yes"
					  />
					  <label class="form-check-label" for="q8-1">Yes</label>
					
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q8"
						id="q8-0"
						value="No"
					  />
					  <label class="form-check-label" for="q8-0">No</label>
					
				  </td>
				</tr>
				<tr>
				  <td style="text-align:center;" >10</td>
				  <td height="95">Do you manufacture or process the animal feedstuff?<br />
					<label for="rem10">If yes, please specify:</label><br/>
					<textarea cols="65" class="form-control" name="rem10" id="rem10"></textarea>
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q10"
						id="q10-1"
						value="Yes"
					  />
					  <label class="form-check-label" for="q10-1">Yes</label>
					
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q10"
						id="q10-0"
						value="No"
					  />
					  <label class="form-check-label" for="q10-0">No</label>
					
				  </td>
				</tr>
	  
				<tr>
				  <td style="text-align:center;" >11</td>
				  <td height="95">Are the animal feedstuff supplied by third parties?<br />
					<label for="rem11">If yes, please specify:</label><br/>
					<textarea cols="65" class="form-control" name="rem11" id="rem11"></textarea>
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q11"
						id="q11-1"
						value="Yes"
					  />
					  <label class="form-check-label" for="q11-1">Yes</label>
					
				  </td>
				  <td >
					
					  <input
						class="form-check-input"
						type="radio"
						name="q11"
						id="q11-0"
						value="No"
					  />
					  <label class="form-check-label" for="q11-0">No</label>
					
				  </td>
				</tr>
			  </table>
		
	  </div>
	  <br pagebreak="true"/>
	  <div id="page3" class="page">
		<p style="text-align:center;">
		  <strong
			>If any of the critical question is answered with a \'Yes\', you may
			be contacted for further follow-ups or the next steps.</strong
		  >
		</p>
		<p style="text-align:center;">
		  <strong
			>Data Confidentiality Commitment: All the above information is
			confidential and will be used as such and only for the official
			purpose of applying a request for Halal Quality Control</strong
		  >
		</p>
		<p></p>
		<hr />
		<p class="text-center mb-4">
		  <strong
			>I hereby confirm that the information given by me is true to the
			best of my knowledge and beliefs</strong
		  >
		</p>
		<table border="0" cellpadding="5" class="table table-bordered table-sm mb-4">
		  <tr>
			<td width="24%">
			  <label for="dateF">Date:</label>
			</td>
			<td width="50%">
			  <input
				type="text" size="25"
				class="form-control"
				name="dateF"
				id="dateF"
				value=""
			  />
			</td>
		  </tr>
		  <tr>
			<td>
			  <label for="Nameofcompetentperson">Name of competent person:</label>
			</td>
			<td>
			  <input
				type="text" size="25"
				class="form-control"
				name="Nameofcompetentperson"
				id="Nameofcompetentperson"
				value=""
			  />
			</td>
		  </tr>
		  <tr>
			<td>
			  <label for="Function">Function:</label>
			</td>
			<td>
			  <input
				type="text" size="25"
				class="form-control"
				name="Function"
				id="Function"
				value=""
			  />
			</td>
		  </tr>
		  </table>
		<table border="0" cellpadding="10" class="table table-bordered table-sm mb-4">
		<tr>
		  <td width="60%"></td>
		  <td width="40%">
		  <table border="1" cellpadding="8" class="table table-bordered table-sm mb-4">
		  <tr>
			<td height="200" style="text-align:center;">
			<strong>Offer acceptance on behalf of</strong>
			<p></p>
			<p></p>
			<p></p>
			<p></p>
			<p></p>
			<p></p>
			<p></p>      
			<div style="border-top:1px dotted #ccc;font-style:italic; color:#bbb;"><strong>(stamp & signature)</strong></div>
			
			</td>
		  </tr>
		
		  </table>
		  </td>
		</tr>
		</table>  
	  </div>
	  <br pagebreak="true"/>
	  <div id="page4" class="page">
			  <h2 class="mb-3">Annex 1: Guidelines</h2>
			  <h3 class="mb-3">Definitions</h3>
			  <p><strong>Feedstuff:</strong> All foods or drinks provided to Human
				Food-Producing Animals and their additives
			  </p>
			  <p><strong>Human Food-Producing animals:</strong> Terrestrial, aquatic
				animals and poultry which humans directly or indirectly consume their
				products as food including meat, eggs, milk, and others
			  </p>
			  <p><strong>Feedstuff Additives:</strong> Components added to feedstuff on
				purpose for technical aims, improving taste, increasing nutritional
				value, or improving animal productivity either these components
				contain nutritional value or not.
			  </p>
			  <p><strong>Halal Feedstuff:</strong> Feedstuff, additives, and allowed
				raw materials prepared in accordance with the Halal requirements. It
				includes all feedstuff served by eating or drinking.
			  </p>
			  <h3 class="mb-3">Norms and Requirements</h3>
			  <p><strong>Halal feedstuff origins</strong><br />
				In principle, all different feedstuff are allowed either sourced from
				plant or animal origins.
			  </p>
			  <p><strong>Plant origins:</strong><br />
				All terrestrial or aquatic plants and their products are allowed to be
				used in manufacturing halal feedstuff except these contaminated by
				prohibited materials.
			  </p>
			  <p>In principle, feedstuff components from animal origins are allowed
				unless evidence is found which prohibits this or if contaminated by
				prohibited materials or meats.
			  </p>
			  <p><strong>General requirements for Halal Feedstuff:</strong></p>
			  <ul>
				<li>Only Halal slaughtered animals are allowed as an animal origin
				  source
				</li>
				<li>All feedstuff shall be free from blood and its byproducts</li>
				<li>By-products resulting from fermentation and alcohol manufacturing
				  may be used during the process unless this are intoxicating
				</li>
				<li>Halal feedstuff shall be prepared or processed in separate
				  production lines. It could be prepared or processed in production
				  lines that were previously used to produce non-halal feedstuff,
				  provided that necessary measures be taken to clean and disinfect
				  production lines to avoid any contact or contamination between halal
				  and non-halal feedstuff
				</li>
				<li>Halal feedstuff can be transported or stored using facilities that
				  were previously used with non-halal feedstuff provided that
				  appropriate cleaning measures be taken to avoid contamination
				</li>
			  </ul>
	  
			</div>
	  
	  </div>';
	  
	  // Print text using writeHTMLCell()
	  $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
	  
	  $pdf->Output($dest_path,'F');
}

function saveAuditPlanPDF($data, $attach='files/docs/F0401 Audit Plan Form 2021.pdf', $dest_path, $audit_plan_settings, $preview=true) {

	$audit_plan_settings = @json_decode($audit_plan_settings, true);

	class MYTCPDF extends TCPDF {

		public function Header(){
		   $html = '<table border="1"  border="1" cellspacing="0" cellpadding="6"  width="100%">
		<tr>
			<td width="33%" align="left" valign="top">Form: F0401<br/> 
		  Revision Date: 29.4.2021<br/>
	  Page:'.$this->PageNo().' from 5
	  </td>
			<td width="34%" style="text-align:center;"><img src="http://test2022.halal-e.zone/img/pdf/logo.png" width="100" /></td>
			<td width="33%" style="text-align:center;" valign="middle"><strong>Halal Quality Control<br />
	  Audit Plan</strong>
	  </td>        
		</tr>
	  </table>
	  ';
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
		
		public function Footer(){
			
		   $html = '<table border="1" cellspacing="0" cellpadding="6" width="100%"><tr>
		  <td>Created    by:<br />
			Shady    Dabshah</td>
		  <td>Reviewed    by:<br />
			Wassiem    Al Chaman</td>
		  <td>Approved    by:<br />
			Ibrahim    Salama</td>
		  <td>Revision    Nr.:<br />
			03</td>
		</tr>
	  </table>';
	  
		   $this->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
		}
	  }
	  // create new PDF document
	  $pdf = new MYTCPDF('P', 'mm', array(395.732, 279.654), true, 'UTF-8', false);
	  //$pdf = new MYTCPDF();
	  // set header and footer fonts
	  $pdf->setHeaderFont(Array('times', '', 12));
	  $pdf->setFooterFont(Array('times', '', 12));
	  
	  // set default monospaced font
	  //$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	  //$pdf->SetFont('freeserif', '', 14, '', true);
	  
	  // set margins
	  $pdf->setMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	  $pdf->setHeaderMargin(PDF_MARGIN_HEADER);
	  $pdf->setFooterMargin(PDF_MARGIN_FOOTER);
	  
	  // set auto page breaks
	  //$pdf->setAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	  
	  // set image scale factor
	  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	  
	  // set some language-dependent strings (optional)
	  if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		  require_once(dirname(__FILE__).'/lang/eng.php');
		  $pdf->setLanguageArray($l);
	  }
	  
	  //$pdf->setFont('freeserif', '', 12);;
	  //$pdf->setFont('freeserif', '', 12);;
	  $pdf->setFont('freeserif', '', 12);

	  
	  $pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
	  
	  // Add a page
	  // This method has several options, check the source code documentation for more information.
	  $pdf->AddPage();
	  
	  $html = '
	  <style>
	  .th {
		background-color:#d9d9d9;
	  }
	  .form-control {
		font-size:16px;
	  }
	  </style>
	  <div class="container">
	  <div id="page1" class="page active">
		<p style="text-align:center;">
		  <img
			src="http://test2022.halal-e.zone/img/pdf/logo.png"
			width="200"
		  />
		</p>
		<h1 style="text-align:center;">
		Halal Quality Control<br/>
		Audit Plan
		</h1><h3 style="text-align:center;">Form 0401</h3>
		<p></p><table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		  <td width="15%"></td>
		  <td width="70%"><table border="1" 
	  cellpadding="6"
	  cellspacing="0"
	  class="table table-bordered table-sm"
	  width="100%"
	  >
			  <tr>
				<td style="text-align:center;" width="7%">1</td>
				<td width="40%">Date:</td>
				<td width="53%">'.($data["app"]["approved_date1"]?DateTime::createFromFormat('Y-m-d', $data["app"]["approved_date1"])->format('d/m/Y'):"").'</td>
			  </tr>
			  <tr>
			  <td style="text-align:center;">2</td>
			  <td>Company Name:</td>
			  <td>'.$data["user"]["name"].'</td>
			</tr>        
			  <tr>
				<td style="text-align:center;">3</td>
				<td>Country of Company:</td>
				<td>'.$audit_plan_settings["countryOfCompany"].'</td>
			  </tr>
			  <tr>
				<td style="text-align:center;">4</td>
				<td>Manufacturing Site Address(es)</td>
				<td>'.($audit_plan_settings["addresses"] ? $audit_plan_settings["addresses"] : $data["user"]["address"]).'</td>
			  </tr>
			  <tr>
				<td style="text-align:center;">5</td>
				<td>Company ID:</td>
				<td>'. $audit_plan_settings["companyId"].'</td>
			  </tr>
			  <tr>
			  <td style="text-align:center;">6</td>
			  <td>Reference (optional):</td>
			  <td>'. $audit_plan_settings["reference"].'</td>
			</tr>        
			</table></td>
		  <td width="15%"></td>
		</tr>
	  </table>
	  <p></p>
	  
	  <table cellpadding="6" cellspacing="0" border="1" width="100%">
	  <tr>
		<td width="50%" style="text-align:center;" class="th"><strong>Audit Type</strong></td>
		<td width="50%" style="text-align:center;" class="th"><strong>Audit Objective</strong></td>
	  </tr>  
	  <tr>
	  	<td>'.str_replace("\r", "<br/>", $audit_plan_settings["auditTypes"]).'</td> 
		<td>'.str_replace("\r", "<br/>", $audit_plan_settings["auditObjectives"]).'</td> 
	  </tr>
	  </table>
	  <p></p><p></p>
	  <p></p><p></p>
	  <p style="text-align:center;font-size:13px;">2021 (C) This document is the sole property of Halal Quality Control. The usage is only permitted by invitation and to be sent by a reliable source. All rights 
	  reserved. No part of this document may be reproduced, distributed, or transmitted in any form or by any means, including photocopying, recording, or other 
	  electronic or mechanical methods for external use, without prior written permission of Halal Quality Control</p>
	  <p></p><p></p>
	  <p></p><p></p>
	  <p><hr style="width:50%"/></p>
	  <p style="font-size:13px;"><sup>1</sup> Remote refers to not being (able to) physically present at the manufacturing facility.<br/>
	  <sup>2</sup> On-site refers to being physically present at the manufacturing facility.<br/>
	  <sup>3</sup> The surveillance includes periodic assessment of the production process or sampling approved products for compliance.
	  </div>
	  <br pagebreak="true"/>
	  <div id="page2" class="page">
	  <p></p>
	  <h1 style="text-align:center">Part 1: Audit Team</h1>
	  <p></p>
	  <table cellpadding="0" border="0" width="100%">
	  <tr>
	  <td width="48%">
	  <table cellpadding="6" border="1" width="100%">
	  <tr>
		<td style="text-align:center;" class="th"><strong>Halal Quality Control</strong></td>
	  </tr>
	  <tr>
		<td width="6%" style="text-align:center" class="th"></td>
		<td width="47%" style="text-align:center" class="th"><strong>Name of Auditor</strong></td>
		<td width="47%" style="text-align:center" class="th"><strong>Position</strong></td>
	  </tr>
	  <tr>
		<td style="text-align:center">1</td>
		<td>'.$audit_plan_settings["LeadAuditor"].'</td>
		<td>Lead Auditor</td>
	  </tr>
	  <tr>
		<td style="text-align:center">2</td>
		<td>'.$audit_plan_settings["coAuditor"].'</td>
		<td>Co-Auditor</td>
	  </tr>
	  <tr>
		<td style="text-align:center">3</td>
		<td>'.$audit_plan_settings["IslamicAffairsExpert"].'</td>
		<td>Islamic Affairs Expert</td>
	  </tr>
	  <tr>
		<td style="text-align:center">4</td>
		<td>'.$audit_plan_settings["Veterinary"].'</td>
		<td>Veterinary (optional)</td>
	  </tr>
	  <tr>
		<td style="text-align:center">5</td>
		<td></td>
		<td></td>
	  </tr>
	  </table>
	  </td>
	  <td width="4%"></td>
	  <td width="48%">
	  <table cellpadding="6" border="1" width="100%">
	  <tr>
		<td style="text-align:center;" class="th"><strong>Representative(s) of the Company</strong></td>
	  </tr>
	  <tr>
		<td width="6%" style="text-align:center" class="th"></td>
		<td width="47%" style="text-align:center" class="th"><strong>Name</strong></td>
		<td width="47%" style="text-align:center" class="th"><strong>Position</strong></td>
	  </tr>
	  <tr>
		<td style="text-align:center">1</td>
		<td><input type="text" class="form-control" name="extra3" value="'.$audit_plan_settings["extra3"].'" size="23" /></td>
		<td><input type="text" class="form-control" name="extra4" value="'.$audit_plan_settings["extra4"].'" size="23" /></td>
	  </tr>
	  <tr>
		<td style="text-align:center">2</td>
		<td><input type="text" class="form-control" name="extra5" value="'.$audit_plan_settings["extra5"].'" size="23" /></td>
		<td><input type="text" class="form-control" name="extra6" value="'.$audit_plan_settings["extra6"].'" size="23" /></td>
	  </tr>
	  <tr>
		<td style="text-align:center">3</td>
		<td><input type="text" class="form-control" name="extra7" value="'.$audit_plan_settings["extra7"].'" size="23" /></td>
		<td><input type="text" class="form-control" name="extra8" value="'.$audit_plan_settings["extra8"].'" size="23" /></td>
	  </tr>
	  <tr>
		<td style="text-align:center">4</td>
		<td><input type="text" class="form-control" name="veterinary2" value="'.$audit_plan_settings["veterinary2"].'" size="23" /></td>
		<td>Veterinary (optional)</td>
	  </tr>
	  <tr>
		<td style="text-align:center">5</td>
		<td><input type="text" class="form-control" name="abattoirSupervisor" value="'.$audit_plan_settings["abattoirSupervisor"].'" size="23" /></td>
		<td>Abattoir Supervisor 
		(optional)</td>
	  </tr>
	  </table>
	  </td>
	  </tr> 
	  </table>
	  <p></p>
	  <h1 style="text-align:center">Part 2: Criteria</h1>
	  <p></p>
	  <table cellpadding="6" border="1" width="100%">
	  <tr>
	  <td width="50%" style="text-align:center;" class="th"><strong>Reference Halal Standard(s)<sup>4</sup></strong></td>
	  <td width="50%" style="text-align:center;" class="th"><strong>Certification Category (see Category Index Table 1):</strong></td>
	  </tr>
	  <tr>
	  <td>'.$audit_plan_settings["extra9"].'</td>
	  <td>'.$audit_plan_settings["extra10"].'</td>
	  </tr>
	  <tr>
	  <td>'.str_replace("\r", "<br/>", $audit_plan_settings["samplings"]).'</td> 
	  <td>'.str_replace("\r", "<br/>", $audit_plan_settings["risks"]).'</td> 
	  </tr>
	  <tr>
		<td style="text-align:center;" class="th"><strong>Food Safety Management System present at Company:</strong></td>
		<td style="text-align:center;" class="th"><strong>Scope of Activities:</strong></td>
	  </tr>
	  <tr>
	  <td>'.str_replace("\r", "<br/>", $audit_plan_settings["foodSafety"]).'</td> 
	  <td>'.str_replace("\r", "<br/>", $audit_plan_settings["scope-of-activities"]).'</td>
	  </tr>
	  <tr>
		<td style="text-align:center;" class="th"><strong>Reporting Language:</strong></td>
		<td style="text-align:center;" class="th"><strong>Previous Non-Conformances / Non-Compliances:</strong></td>
	  </tr>
	  <tr>
	  <td>'.str_replace("\r", "<br/>", $audit_plan_settings["languages"]).'</td> 	  
	  <td>'.str_replace("\r", "<br/>", $audit_plan_settings["ncs1"]).'</td> 	  
	  </tr>
	  </table>
	  
	  <hr/>
	  <p><sup>4</sup> References: GSO 2055-2:2015, UAE.S 2055-2:2016, SMIIC 1:2019, JAKIM MS 1500:2019, HAS 23000:2</p>
	  </div>
	  <br pagebreak="true"/>
	  <div id="page3" class="page">
	  <p></p>
	  <h1 style="text-align:center">Part 3: Agenda, Objections, and Previous Results</h1>
	  <p></p>
	  <table cellpadding="6" cellspacing="0" border="1" width="100%">
	  <tr>
		<td width="50%" style="text-align:center;" class="th"><strong>Assessment Date 1:</strong></td>
		<td width="50%" style="text-align:center;" class="th"><strong>Assessment Date 2 (if any):</strong></td>
	  </tr>
	  <tr>
	  <td>'.$audit_plan_settings["extra14"].'</td>
	  <td>'.$audit_plan_settings["extra15"].'</td>
	  </tr>
	  </table>
	  <table cellpadding="6" cellspacing="0" border="1" width="100%">
	  <tr>
		<td width="25%" style="text-align:center;" class="th"><strong>Time or Day</strong></td>
		<td width="50%" style="text-align:center;" class="th"><strong>Activity / Phase / Subject</strong></td>
		<td width="25%" style="text-align:center;" class="th"><strong>Date:</strong></td>
	  </tr>';
	  for ($i=1;$i<15;$i++) {
		$html .= '<tr>
				  <td>'.$audit_plan_settings["tableData".$i."-1"].'</td>
				  <td>'.$audit_plan_settings["tableData".$i."-2"].'</td>
				  <td>'.$audit_plan_settings["tableData".$i."-3"].'</td>
				</tr>';
	  }
	  $html .= '</table>
	  <p></p>
	  <table cellpadding="6" cellspacing="0" border="1" width="100%">
	  <tr>
		<td width="50%" style="text-align:center;" class="th"><strong>Objections from the Company:</strong></td>
		<td width="50%" style="text-align:center;" class="th"><strong>Previous Non-Conformances / Non-Compliances:</strong></td>
	  </tr>  
	  <tr>
		<td>
		  <table cellpadding="4" cellspacing="0" border="0" width="100%">
		  <tr>
			  <td>
				<label class="form-check-label" for="object1">'.(isset($audit_plan_settings["object1"]) ? $audit_plan_settings["object1"]:"No").'</label>
			</td>
		  </tr>';
		  if ($audit_plan_settings["objectReason"] != "") {
			  $html .='<tr>
					<td> Reason:<br/>'.$audit_plan_settings["objectReason"].'</td>
		  		</tr>';
			}
		$html .='</table>
	   </td>
	   <td>'.str_replace("\r", "<br/>", $audit_plan_settings["ncs2"]).'</td> 	  
	  </tr>
	  </table>
	  <p></p>
	  <p style="text-align:center;"><strong>In case of an objection, you may request our Complaints and Appeals procedures and forms for further handling.</strong></p>
	  </div>
	  <br pagebreak="true"/>
	  <div id="page4" class="page">
	  <p></p>
	  <h1 style="text-align:center;">Part 4: Working Documents and Attachments</h1>
	  <p></p>
	  <table cellpadding="6" cellspacing="0" border="1" width="100%">
	  <tr>
		<td width="50%" style="text-align:center;" class="th"><strong>Documents involved before the inspection</strong></td>
		<td width="50%" style="text-align:center;" class="th"><strong>Attachments</strong></td>
	  </tr>  
	  <tr>
	   <td>'.str_replace("\r", "<br/>", $audit_plan_settings["documents"]).'</td> 	  
	   <td>'.str_replace("\r", "<br/>", $audit_plan_settings["attachments"]).'</td> 	  
		</tr>
	  </table>
	  <p></p>
	  <p></p>
	  <p style="text-align:center;font-size:20px;"><strong>All Working Documents should be ready and reviewed upon prior the inspection date
	  All Attachments may or should be shared with the company.</strong></p>
	  <p style="text-align:center;font-size:20px;"><strong>All other documents requested as Supporting Documents should be sent to Halal Quality 
	  Control when requested upon.</strong></p>
	  <p></p>
	  <table cellpadding="6" cellspacing="0" border="1" width="100%">
	  <tr>
		<td style="text-align:center;" class="th"><strong>Remarks</strong></td>
	  </tr>
	  <tr>
		<td height="500">'.str_replace("\r", "<br/>", $audit_plan_settings["main-remarks"]).'</td>
	  </tr>
	  </table>
	  </div>
	  <br pagebreak="true"/>
	  <div id="page5" class="page">
	  <p></p>
	  <h1 style="text-align:center;"><strong>Part 5: Instructions and Guidelines</strong></h1>
	  <h3><strong>5.1 Audit Timeframe Calculation</strong></h3>
	  <strong>The scheduled time and proposed duration of the audit, including desk audit (Stage 1), site audit (Stage 2) 
	  and delivery of final report.</strong><br/>
	  Both parties need to understand and agree on the time and likely duration of the desk audit, site audit (Stage 2) 
	  and preparation of the report. While the exact times cannot be guaranteed there need to be some guideline 
	  estimate understood between the parties involved.<br/><br/>
	  <strong>Time Calculation Method (TCM)</strong><br/>
	  Site Inspection (Stage 1): 2 Working Days<br/>
	  Recertification/Controlling Inspection: 1 Working Day<br/>
	  Desk/Documentation Inspection: 1 Working Day<br/>
	  Minimum audit time for single site: 1 Working Day<br/>
	  Formula: <strong>Ta Ta = B +H+(PV+FTE)*CC</strong><br/>
	  4 = 1.25 + 0.5 + (0.5 + 1.5)*1
	  <h3><strong>5.2 Principles</strong></h3>
	  Integrity: The Lead Auditors and the Sharia Experts managing an audit programme should:
	  <ul> 
	  <li>perform their work with honesty, diligence, and responsibility; 
	  </li><li>observe and comply with any applicable legal requirements; 
	  </li><li>demonstrate their competence while performing their work; 
	  </li><li>perform their work in an impartial manner, i.e. remain fair and unbiased in all their dealings; 
	  </li><li>be sensitive to any influences that may be exerted on their judgement while carrying out an 
	  audit</li>
	  </ul>
	  <h3><strong>5.3 Confidentiality and Security of Information</strong></h3>
	  The Auditors exercise discretion in the use and protection of information acquired in the course of their duties.
	  The provided information should not be used inappropriately for personal gain by the auditor or the audit client, 
	  or in a manner detrimental to the legitimate interests of the auditee.<br/> 
	  This concept includes the proper handling of sensitive or confidential information as prior agreed upon during 
	  the Contractual Arrangements with Halal Quality Control.
	  <h3><strong>5.4 Independence</strong></h3>
	  The Auditors should be independent of the activity being audited wherever practicable, and should in all cases 
	  act in a manner that is free from bias and conflict of interest. The Auditors should maintain objectivity 
	  throughout the audit process to ensure that the audit findings and conclusions are based only on the audit 
	  evidence.
	  </div>
	  ';
	  
	  // Print text using writeHTMLCell()
	  $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
	  
	
	if ($preview) {
		$pdf->Output('F0401_Audit_Plan_Form_2021.pdf', 'D');
  }
  else {
	  $pdf->Output($dest_path,'F');
  }
}

function saveApplicationPDF1($fields, $attach, $dest_path) {

		// "Name of Reviewer"
		// "Date of Review"

	$pdf = new FPDM($attach);	
	$pdf->Load($fields, false); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
	$pdf->Merge();
	$pdf->Output('F', $dest_path);
}
 
function saveQuestionnairePDF1($data, $attach, $dest_path, $industry) { 
	if (strpos($attach, "manufacturing") === FALSE) {
		$fields = array(
			'Date' => date('d/m/Y'),
			'Company Name' => $data["name"],
			'Country of Company' => $data["country"],
			'Contact Person' => $data["contact_person"],
		);
	}
	else {
		$fields = array(
			'Text8' => date('d/m/Y'),
			'Text14' => $data["name"],
			'Text21' => $data["country"],
			'Text22' => $data["contact_person"],
		);		
	}
	$pdf = new FPDM($attach);	
	$pdf->Load($fields, false); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
	$pdf->Merge();
	$pdf->Output('F', $dest_path);
}

function saveChecklistPDF1($data, $attach, $dest_path, $industry) { 
	$date = $data["app"]["approved_date1"] ? DateTime::createFromFormat('Y-m-d', $data["app"]["approved_date1"])->format('d/m/Y') : "";
	if (strpos($attach, "manufacturing") === FALSE) {	
		$fields = array(
			'Date' => $date,
			'Company Name' => $data["user"]["name"],
			'Country of Company' => $data["user"]["country"],
			'Lead Auditor' => $data["app"]["LeadAuditor"],
			'Islamic Affairs Expert' => $data["app"]["IslamicAffairsExpert"],
		);
	}
	else {
		$fields = array(
			'Text8' => $date,
			'Text14' => $data["user"]["name"],
			'Text21' => $data["user"]["country"],
			'Text22' => $data["user"]["contact_person"],
		);		
	}

	$pdf = new FPDM($attach);	
	$pdf->Load($fields, false); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
	$pdf->Merge();
	$pdf->Output('F', $dest_path);
}

function saveChecklistPDF($data, $attach, $dest_path, $industry) {

	$pageCount = 0;

	if ($industry == "Meat Processing") {
		$pageCount = 21;
	}
	else if ($industry == "Slaughter Houses") {
		$pageCount = 22;
	}
	else  {
		$pageCount = 17;
	}
	
	$pdf = new Fpdi();
	
	 /* <Virtual loop> */
	  $pdf->AddPage();
	  
	  $pdf->setSourceFile($attach);
	
	  $tplIdx = $pdf->importPage(1);
	  $pdf->useTemplate($tplIdx, 10, 10, 200);
	
	  $pdf->SetFont('Arial', '', 9);
	  $pdf->SetTextColor(63,72,204);
	
  if ($industry == "Meat Processing") {
	  $x = 92;
	  $y = 163.5;  
  }
  else if ($industry == "Slaughter Houses") {
	  $x = 92;
	  $y = 165.5;  
  }
  else   {
	  $x = 92;
	  $y = 158;  
  }
  	
 	//$audit_plan_settings = @json_decode($data["app"]["audit_plan_settings"], true);
	$date = $data["app"]["approved_date1"] ? DateTime::createFromFormat('Y-m-d', $data["app"]["approved_date1"])->format('d/m/Y') : "";
  	//DateTime::createFromFormat('Y-m-d', $data["app"]["approved_date1"])->format('d/m/Y')
	  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, $date);

	  $y += 4.5;  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, $data["user"]["name"]);

	  $y += 4.5;  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, $data["user"]["country"]);

	  if (isset($data["app"]["LeadAuditor"])) {
		$y += 4.5;  
		$pdf->SetXY($x, $y);
		$pdf->Write(0, $data["app"]["LeadAuditor"]);
	  }

	  if (isset($data["app"]["IslamicAffairsExpert"])) {
		$y += 4.5;  
		$pdf->SetXY($x, $y);
		$pdf->Write(0, $data["app"]["IslamicAffairsExpert"]);
	  }
		
		/*	
	  $y += 4.5;  
	  $pdf->SetXY($x, $y);
	  $pdf->Write(0, $data["contact_person"]);
	  */
	
		for ($i=2;$i<=$pageCount;$i++) {
		  $pdf->AddPage();
		  $tplIdx = $pdf->importPage($i);
		  $pdf->useTemplate($tplIdx, 10, 10, 200);
		}
	
	
	if ($preview) {
		  $pdf->Output($attach, 'D');
	}
	else {
		$pdf->Output($dest_path,'F');
	}
}

function getClientInfo($idclient)
{
	try {
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "SELECT CONCAT(name,' (',prefix, id,')') as info FROM tusers WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':id', $idclient);
		$stmt->execute();
		
		$info = $stmt->fetch()['info'];
		
		// Replace directory separators with underscore
		$sanitizedInfo = str_replace(['/', '\\'], '|', $info);
		
		return $sanitizedInfo;

	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}
/*
function getClientInfo($idclient)
{
	try {
		$dbo = &$GLOBALS['dbo'];
		$dbo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$sql = "SELECT CONCAT(name,' (',prefix, id,')') as info FROM tusers WHERE id=:id";
		$stmt = $dbo->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->bindValue(':id', $idclient);
		$stmt->execute();
		return $stmt->fetch()['info'];
	} catch (PDOException $e){
		echo json_encode(generateErrorResponse("Error: ".$e->getMessage()));
		die();
	}
}
	*/
function getProductCategories() {
	return ['Meat Abattoir<sup>1</sup>',
	'Meat Processing Plant<sup>2</sup>',
	'Manufacturing including Animal Derived Materials<sup>3</sup>',
	'Dairy and/or Egg Farming or Processing',
	'Bakery and/or Confectionery',
	'Beverages',
	'Oils',
	'Non-Edible Foods or Non-consumable Liquids<sup>4</sup>',
	'Spices and/or Sauces',
	'(Synthetic) Chemicals Cosmetics',
	'Trading or Private Labeling',
	'Warehousing and/or Storage Catering'];
}
function getAppStateName($state) {
    $map = [
        'offer' => 'Offer',
        'soffer' => 'Signed Offer',
        'app' => 'Application',
        'dates' => 'Audit Dates',
        'invoice' => 'Invoice for certification fees',
        'popinv' => 'Proof of Payment – Certification Fees',
        'declarations' => 'Declarations',
        'audit' => 'Audit Plan',
        'checklist' => 'Checklist',
        'report' => 'Audit Report',
        'review' => 'Audit Review Report',
        'dm' => 'Decision Making',
		'invoicete' => 'Invoice for travel expenses',
        'pop' => 'Proof of Payment – Travel Expenses',
        'certificate' => 'Certificate',
        'additional_items' => 'Additional Items Application',
        'invoiceai' => 'Invoice for additional items.',
        'popai' => 'Proof of Payment – Additional Items',
        'extension' => 'Certificate Extension',
    ];

    return isset($map[$state]) ? $map[$state] : "-";
}


function updateProductStats($idclient) {
	$dbo = &$GLOBALS['dbo'];

	$sql = "DELETE from tproducts WHERE idclient=:idclient and item IS NULL AND ean IS NULL AND spec IS NULL AND addoc IS NULL";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();

	$prodPublished = 0;
	$sql = "select count(pp.id) as count from ".
	" (SELECT p.id, IF(count(i.id)-SUM(IF(i.conf is NULL, 0, i.conf))=0 AND count(si.id)-SUM(IF(si.conf is NULL, 0, si.conf))=0, 1, 0) as conf from tproducts p ".
	" left join tp2i on (tp2i.idp=p.id) ".
	" left join tingredients i on (i.id=tp2i.idi) ".
	" left join ti2i on (ti2i.idi1=i.id) ".
	" left join tingredients si on (si.id=ti2i.idi2) ".
	" where p.idclient=:idclient group by p.id ) pp WHERE 1=1";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	if($stmt->execute()) {
		$prodPublished = $stmt->fetch()['count']*1;
	}
  
	$prodConfirmed = 0;
	$sql = "select count(pp.id) as count from ".
	" (SELECT p.id, IF(count(i.id)-SUM(IF(i.conf is NULL, 0, i.conf))=0 AND count(si.id)-SUM(IF(si.conf is NULL, 0, si.conf))=0, 1, 0) as conf from tproducts p ".
	" left join tp2i on (tp2i.idp=p.id) ".
	" left join tingredients i on (i.id=tp2i.idi) ".
	" left join ti2i on (ti2i.idi1=i.id) ".
	" left join tingredients si on (si.id=ti2i.idi2) ".
	" where p.idclient=:idclient group by p.id ) pp WHERE pp.conf=1";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	if($stmt->execute()) {
		$prodConfirmed = $stmt->fetch()['count']*1;
	}

  $sql = "UPDATE tusers SET prodpublished='".$prodPublished."', prodconfirmed='".$prodConfirmed."' WHERE id=:id";
  $stmt = $dbo->prepare($sql);
  $stmt->bindValue(':id', $idclient);
  $stmt->execute();
}

function updateIngredientStats($idclient) {

	$dbo = &$GLOBALS['dbo'];

	$sql = "DELETE from tingredients WHERE idclient=:idclient and rmcode IS NULL AND name IS NULL";
	$stmt = $dbo->prepare($sql);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->bindValue(':idclient', $idclient);
	$stmt->execute();
	    
	$ingredPublished=0;
  $sql = "select count(id) count from tingredients WHERE idclient=:idclient and 1=1";
  $stmt = $dbo->prepare($sql);
  $stmt->setFetchMode(PDO::FETCH_ASSOC);
  $stmt->bindValue(':idclient', $idclient);
  if ($stmt->execute()) {
    $ingredPublished = $stmt->fetch()['count']*1;
  }
  $ingredConfirmed=0;
  $sql = "select count(id) count from tingredients WHERE idclient=:idclient and conf=1";
  $stmt = $dbo->prepare($sql);
  $stmt->setFetchMode(PDO::FETCH_ASSOC);
  $stmt->bindValue(':idclient', $idclient);
  if ($stmt->execute()) {
    $ingredConfirmed = $stmt->fetch()['count']*1;
  }
  $sql = "UPDATE tusers SET ingredpublished='".$ingredPublished."', ingredconfirmed='".$ingredConfirmed."' WHERE id=:id";
  $stmt = $dbo->prepare($sql);
  $stmt->bindValue(':id', $idclient);
  $stmt->execute();
}

function insertActivityLog($clientId,$appId, $userId, $username, $activityDescription) {
    try {
		$dbo = &$GLOBALS['dbo'];
        $sql = "INSERT INTO tactivity_log (idclient, idapp, iduser, username, activity_description) 
                VALUES (:idclient, :idapp, :iduser, :username, :activity_description)";
        $stmt = $dbo->prepare($sql);
        $stmt->bindParam(':idclient', $clientId, PDO::PARAM_INT);
		$stmt->bindParam(':idapp', $appId, PDO::PARAM_INT);
		$stmt->bindParam(':iduser', $userId, PDO::PARAM_INT);
		$stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':activity_description', $activityDescription, PDO::PARAM_STR);
        $stmt->execute();

		$sql = "UPDATE tapplications SET last_activity_desc=:last_activity_desc, last_activity_by=:last_activity_by, last_activity_date = NOW() WHERE idclient=:idclient AND id=:idapp";
        $stmt = $dbo->prepare($sql);
        $stmt->bindParam(':last_activity_by', $username, PDO::PARAM_STR);
        $stmt->bindParam(':last_activity_desc', $activityDescription, PDO::PARAM_STR);		
		$stmt->bindParam(':idclient', $clientId, PDO::PARAM_INT);
		$stmt->bindParam(':idapp', $appId, PDO::PARAM_INT);
		$stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

?>