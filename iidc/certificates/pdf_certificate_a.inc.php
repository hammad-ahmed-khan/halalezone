<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
if (count($_POST)>0 or count($_GET)>0) {
foreach ($_POST as $key => $value) {
$$key = $value;
}
date_default_timezone_set(date_default_timezone_get());
$date = date("d/m/Y");
include "../../config/paths.inc.php";
include "../../config/mysql_ftp.inc.php";
include "../../config/connect.inc.php";
$certificate_nr="HAXXXXX";

if (isset($nr) and $nr!='')
{
$result = MYSQL_QUERY("SELECT * FROM certificates_a WHERE nr='$nr'");
if (@MYSQL_NUM_ROWS($result) > 0){
$row = MYSQL_FETCH_ARRAY($result);
$clid =$row['clid'];
$importer = $row['importer'];
$exporter = $row['exporter'];
$producer = $row['producer'];
$quality = $row['quality'];
$weight_gross = $row['weight_gross'];
$weight_net = $row['weight_net'];
$slaughtering_date = $row['slaughtering_date'];
$production_date = $row['production_date'];
$transportation_method = $row['transportation_method'];
$transportation_nr = $row['transportation_nr'];
if ($row['certificate_nr'])
$certificate_nr = $row['certificate_nr'];
$hcd_nr = $row['hcd_nr'];
$expiry_date = $row['expiry_date'];
$issue_date = $row['issue_date'];
$attachment = $row['attachment'];
$signed_by = $row['signed_by'];
}
}

$result = MYSQL_QUERY("SELECT * FROM companies WHERE clid='$clid'");
if (@MYSQL_NUM_ROWS($result) > 0){
$row = MYSQL_FETCH_ARRAY($result);
$company_name =$row['company_name'];
}

//require_once("$prog_path/pdf/phpqrcode/qrlib.php");
define('FPDF_FONTPATH','font/');
require('fpdi.php');

$pdf= new fpdi();

if (isset($_GET['tmpl']) and trim($_GET['tmpl'])!=''){
	if($_GET['tmpl']=='be')
	$pagecount = $pdf->setSourceFile("../../client_data/templates/belgium_certificate_a.pdf");
	if($_GET['tmpl']=='de')
	$pagecount = $pdf->setSourceFile("../../client_data/templates/germany_certificate_a.pdf");
	if($_GET['tmpl']=='dk')
	$pagecount = $pdf->setSourceFile("../../client_data/templates/denmark_certificate_a.pdf");
} else {
$pagecount = $pdf->setSourceFile("../../client_data/templates/holland_certificate_a.pdf");
}

if (isset($act) and $act=='crt')
{ $tplidx = $pdf->ImportPage(2);}
else
{ $tplidx = $pdf->ImportPage(1);}

$pdf->addPage();
$pdf->SetMargins(0,0,0,0);
$pdf->useTemplate($tplidx,0,0,210);
$pdf->SetKeywords("nouras");

if ((isset($act) and $act=='crt') and $certificate_nr=='HAXXXXX')
{
$certificate_nr='HA00000';
MYSQL_QUERY("update hc_numbers set certificate_nr=certificate_nr+1");
$result_nr = MYSQL_QUERY("SELECT * FROM hc_numbers");
if (@MYSQL_NUM_ROWS($result_nr) > 0){
$row_nr = MYSQL_FETCH_ARRAY($result_nr);
$certificate_nr = substr($certificate_nr,0,-strlen($row_nr['certificate_nr']));
$certificate_nr .= $row_nr['certificate_nr'];
MYSQL_QUERY("update certificates_$tp set certificate_nr='$certificate_nr' WHERE nr='$nr'");
}
}

//putting QR  on the certificate
$qrFile = 'QR-'.time().'.png';
//QRcode::png('http://iidc.eu/aca/?crtNr='.$certificate_nr,"$prog_path/tem/$qrFile",QR_ECLEVEL_L, 10, 4);
//$pdf->Image('../../tem/'.$qrFile,165,10,32);
//unlink("$prog_path/tem/$qrFile");

$stHead = 0;
$stPr = 0;
$stFooter = 0;
if (isset($_GET['tmpl'])){
		$stHead = 12;
		$stPr = 17;
		$stFooter = 10;
}

$place_y = 75;
$place_x = 90;

if (isset($_GET['issue']) and trim($_GET['issue'])!=''){
	MYSQL_QUERY("update certificates_a set issue_date='$_GET[issue]' WHERE nr='$nr'");
	$issue_date = $_GET['issue'];
}

if (isset($importer))
{
$result = MYSQL_QUERY("SELECT * FROM companies WHERE clid='$importer'");
if (@MYSQL_NUM_ROWS($result) > 0){
$row = MYSQL_FETCH_ARRAY($result);
$importer ="$row[company_name]\n";
if ($row['street1'])
$importer .=$row['street1'];
if ($row['zip1'])
$importer .= ", ".$row['zip1'];
if ($row['city1'])
$importer .= " ".$row['city1'];
if ($row['country1'])
$importer .= " ".$row['country1'];
}
}

if (isset($producer))
{
$result = MYSQL_QUERY("SELECT * FROM companies WHERE clid='$producer'");
if (@MYSQL_NUM_ROWS($result) > 0){
$row = MYSQL_FETCH_ARRAY($result);
$producer ="$row[company_name]\n";
if ($row['street1'])
$producer .= $row['street1'];
if ($row['zip1'])
$producer .= ", ".$row['zip1'];
if ($row['city1'])
$producer .= " ".$row['city1'];
if ($row['country1'])
$producer .= ", ". $row['country1'];
}
}

if (isset($exporter))
{
$result = MYSQL_QUERY("SELECT * FROM companies WHERE clid='$exporter'");
if (@MYSQL_NUM_ROWS($result) > 0){
$row = MYSQL_FETCH_ARRAY($result);
$exporter ="$row[company_name]\n";
if ($row['street1'])
$exporter .=$row['street1'];
if ($row['zip1'])
$exporter .= ", ".$row['zip1'];
if ($row['city1'])
$exporter .= " ". $row['city1'];
if ($row['country1'])
$exporter .= ", ". $row['country1'];
}
}
	$pdf->SetFont('Arial','B',16);
    $pdf->setY($stHead+$place_y);
    $pdf->SetX(105 - ($pdf->GetStringWidth($certificate_nr))/2);
	$pdf->Cell(0,0,$certificate_nr);

$pos_company_name = array($stHead+84,60);
$pos_quality = array($stPr+160,35);
$pos_gross_weight = array($stPr+174,35);
$pos_net_weight = array($stPr+178,35);

$pos_producer = array($stPr+180.5,35);
$pos_importer = array($stPr+189.5,35);
$pos_exporter = array($stPr+198.5,35);

$pos_transportation = array($stPr+209,60);
$pos_transportation_nr = array($stPr+213.4,60);
$pos_health_certificate_nr = array($stPr+217.5,60);
$pos_slaughtering_date = array($stPr+221.5,60);
$pos_production_date = array($stPr+225.5,60);
$pos_expiry_date = array($stPr+229.6,60);
$pos_issue_date = array($stPr+233.7,60);

    $pdf->SetFont('Times','',12);

	$company_name = iconv('UTF-8', 'windows-1252', $company_name);
    $pdf->setY($pos_company_name[0]);
    $pdf->SetX(105 - ($pdf->GetStringWidth($company_name)/2));
	$pdf->Cell(0,0,$company_name);

	$quality = iconv('UTF-8', 'windows-1252', $quality);
    $pdf->SetFont('Times','',10);
    $pdf->setY($pos_quality[0]);
	$pdf->setX($pos_quality[1]);
	$pdf->MultiCell(140,4,$quality,0,'0');

    $pdf->setY($pos_gross_weight[0]);
	$pdf->setX($pos_gross_weight[1]);
	if (isset($weight_gross) and trim($weight_gross)!="")
	$pdf->Cell(0,0,number_format($weight_gross,2,'.',',')." KG");

    $pdf->setY($pos_net_weight[0]);
	$pdf->setX($pos_net_weight[1]);
	$pdf->Cell(0,0,number_format($weight_net,2,'.',',')." KG");

//producer
	$producer = iconv('UTF-8', 'windows-1252', $producer);
	if($pdf->GetStringWidth($producer)>140){
	$fontSize=9; $lineHeight = 3;}
	else
	{$fontSize=10;$lineHeight = 4;};

	$pdf->SetFont('Times','',$fontSize);
    $pdf->setY($pos_producer[0]);
	$pdf->setX($pos_producer[1]);
	$pdf->MultiCell(140,$lineHeight,$producer,0,'0');

//importer
	$importer = iconv('UTF-8', 'windows-1252', $importer);
	if($pdf->GetStringWidth($importer)>140){
	$fontSize=9; $lineHeight = 3;}
	else
	{$fontSize=10;$lineHeight = 4;};

	$pdf->SetFont('Times','',$fontSize);
    $pdf->setY($pos_importer[0]);
	$pdf->setX($pos_importer[1]);
	$pdf->MultiCell(140,$lineHeight,$importer,0,'0');

//exporter
	$exporter = iconv('UTF-8', 'windows-1252', $exporter);
	if($pdf->GetStringWidth($exporter)>140){
	$fontSize=9; $lineHeight = 3;}
	else
	{$fontSize=10;$lineHeight = 4;};

	$pdf->SetFont('Times','',$fontSize);
    $pdf->setY($pos_exporter[0]);
	$pdf->setX($pos_exporter[1]);
	$pdf->MultiCell(140,$lineHeight,$exporter,0,'0');

	$pdf->SetFont('Times','',10);
	$pdf->setY($pos_slaughtering_date[0]);
	$pdf->setX($pos_slaughtering_date[1]);
	$pdf->Cell(0,0,$slaughtering_date);

	$pdf->setY($pos_production_date[0]);
	$pdf->setX($pos_production_date[1]);
	$pdf->Cell(0,0,$production_date);

    $pdf->setY($pos_transportation[0]);
	$pdf->setX($pos_transportation[1]);
	$pdf->Cell(0,0,$transportation_method);

    $pdf->setY($pos_transportation_nr[0]);
	$pdf->setX($pos_transportation_nr[1]);
	$pdf->Cell(0,0,$transportation_nr);

    $pdf->setY($pos_health_certificate_nr[0]);
	$pdf->setX($pos_health_certificate_nr[1]);
	$pdf->Cell(0,0,$hcd_nr);

    $pdf->setY($pos_expiry_date[0]);
	$pdf->setX($pos_expiry_date[1]);
	$pdf->Cell(0,0,$expiry_date);

    $pdf->setY($pos_issue_date[0]);
	$pdf->setX($pos_issue_date[1]);
	$pdf->Cell(0,0,$issue_date);

	$pdf->SetFont('Times','',9);
	$checkCertificate = "To check the authenticity of this certificate go to www.iidc.eu/aca and use the certificate number and document number";
	$pdf->setY($stFooter+265);
	if(!isset($_GET['tmpl'])){
	$pdf->SetX(105 - ($pdf->GetStringWidth($checkCertificate))/2);
	$pdf->Cell(0,0,$checkCertificate);
	}

if (!isset($usr) and (isset($act) and $act=='crt'))
{
$pdf->SetFont('Times','I',10);
if ($signed_by=='i'){
    $pdf->Image('../../client_data/images/iyad.png',75,258,70);
    $pdf->Image('../../client_data/images/hc_a.png',25,242,40);
	$pdf->setY(270);
	$pdf->setX(75);
	$pdf->Cell(0,0,"This certificate is digitally signed");
	}
if ($signed_by=='b'){
    $pdf->Image('../../client_data/images/munim.png',85,258,50);
    $pdf->Image('../../client_data/images/hc_a.png',25,242,40);
	$pdf->setY(270);
	$pdf->setX(75);
	$pdf->Cell(0,0,"This certificate is digitally signed");
	}
}

if (isset($attachment) and $attachment !='')
{
$pdf->addPage();
    $pdf->SetFont('Times','B',12);
    $pdf->setY(20);
	$pdf->setX(20);
	$pdf->Cell(0,0,"ATTACHMENT: $certificate_nr");
    $pdf->SetFont('Times','',12);
    $pdf->setY(25);
	$pdf->setX(20);
	$pdf->MultiCell(175,4,$attachment,0,'0');
}
$pdf->Output();
}
?>
