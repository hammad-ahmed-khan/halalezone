<?php
define("__HQC__",true);
use setasign\Fpdi\Fpdi;
include "../../config/paths.inc.php";
include "../../config/mysql_ftp.inc.php";
include "../../config/connect.inc.php";
require_once($prog_path.'/pdf/fpdf/fpdf.php');
require_once($prog_path.'/pdf/src/autoload.php');
if (count($_POST)>0) {
$values =array();
foreach ($_POST as $key => $value) {
$$key = $value;
}

date_default_timezone_get('Europe/Amsterdam');

$date = date("d/m/Y");
$certificate_nr="HAXXXXX";

$ymd 	= date("Ymd");
$year 	= date("Y");
$month 	= date("m");
$date 	= date("d/m/Y");

//getting invoice number========================================================================
$result = MYSQL_QUERY("SELECT * FROM invoice_nrs");
if (@MYSQL_NUM_ROWS($result) > 0){
$row = MYSQL_FETCH_ARRAY($result);
$invoice_nr=$row['invoice_nr_'.$template]+1;
$tmnr='00000';
$invoiceNr = substr($tmnr,0,-strlen($invoice_nr)).$invoice_nr;
$invoice_prefix = $row['invoice_prefix_'.$template];
}
$invoiceNr = $invoice_prefix.$invoiceNr;
$kenmerk = "$clid-$invoiceNr";
$fak ="Credit note No.:
Credit note Date:";
$fak1 = "$invoiceNr
$date";

//get address=====================================================================
$cla = invoice_address($clid);
$invoice_address = json_encode($cla,true);
$address = $cla['address'];
$company_invoice = $cla['company_invoice'];
$client_name = $cla['client_name'];
$client_email = $cla['client_email'];
$uba = $cla['uba'];

$pdf= new FPDI();

$pagecount = $pdf->setSourceFile("../../client_data/templates/letter_head_$template.pdf");
$tplidx = $pdf->ImportPage(1);
$pdf->addPage();
$pdf->SetMargins(20,30,20,20);
$pdf->useTemplate($tplidx,0,0,210);
$pdf->SetAuthor('Nouras');
$pdf->AliasNbPages();

	if ($act=="crt")
	$ttl = 'CREDIT NOTE';
	else
	$ttl = 'PREVIEW/CREDIT NOTE';

	$pdf->SetFont('Times','B',14);
	$pdf->SetY(62);
	$pdf->SetFillColor(218);
    $pdf->SetX(105 - ($pdf->GetStringWidth($ttl))/2);
	$pdf->Cell(0,0,$ttl);



//inserting contact and address=================================================
	$pdf->SetFont('Times','',12);
    $pdf->SetY(75);
    $pdf->SetX(20);
    $pdf->MultiCell(100,5,iconv('UTF-8', 'windows-1252',$address),0,'0');

//inserting invoice number======================================================
	$cur_post = $pdf->GetY();
	$pdf->SetFont('Times','B',12);
    $pdf->SetY($cur_post);
    $pdf->SetX(120);
    $pdf->MultiCell(100,5,"$fak",0,'0');
    $pdf->SetFont('Times','',12);
    $pdf->SetY($cur_post);
    $pdf->SetX(165);
    $pdf->MultiCell(100,5,"$fak1",0,'0');

	$pdf->SetFont('Times','B',12);
    $pdf->SetY($pdf->GetY() + 10);
    $pdf->SetX(20);
	$pdf->Cell(0,0,"Description");
	$pdf->SetX(170);
	$pdf->Cell(0,0,"Amount");
	$pdf->Line(20,$pdf->GetY()+3,190,$pdf->GetY() +3);
	$pdf->SetFont('Times','',12);
    $pdf->SetY($pdf->GetY() + 8);
    $pdf->SetX(20);
	$pdf->MultiCell(140,5,"$description",0,'0');
$pdf->SetX(168);
$pdf->Cell(0,0,€);
$this_total= number_format(str_replace(',','.',$amount),2,',','.');
$pdf->SetX(186 - $pdf->GetStringWidth($this_total));
$pdf->Cell(0,0,"-$this_total");

$pdf->SetY($pdf->GetY() + 5);
$pdf->Line(20,$pdf->GetY()+3,190,$pdf->GetY() +3);
$pdf->SetY($pdf->GetY() + 6);
$pdf->SetX(140);
if ($template=="uae")
{
$pdf->Cell(0,0,"TOTAL:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);
$this_total=number_format($amount,2);
$pdf->SetX(186 - $pdf->GetStringWidth($this_total));
$pdf->Cell(0,0,"-$this_total");
$invoice_subtotal = $amount;
$invoice_total = $amount;
$invoice_vat = 0;
}
else
{
$pdf->Cell(0,0,"SUBTOTAL:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);

$amount = str_replace(',','.',$amount);

$this_subtotal=number_format($amount,2,',','.');
$pdf->SetX(186 - $pdf->GetStringWidth($this_subtotal));
$pdf->Cell(0,0,"-$this_subtotal");

$pdf->SetY($pdf->GetY() + 5);
$pdf->SetX(140);
$pdf->Cell(0,0,"VAT $vat%:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);
$this_vat = number_format((($amount*$vat)/100),2,',','.');
$pdf->SetX(186 - $pdf->GetStringWidth($this_vat));
$pdf->Cell(0,0,"-$this_vat");

$pdf->Line(140,$pdf->GetY()+3,190,$pdf->GetY() +3);
$pdf->SetY($pdf->GetY() + 6);
$pdf->SetX(140);
$pdf->Cell(0,0,"TOTAL:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);

$this_total=number_format((($amount*$vat)/100) + $amount,2,',','.');
$pdf->SetX(186 - $pdf->GetStringWidth($this_total));
$pdf->Cell(0,0,"-$this_total");

$invoice_subtotal = $amount;
$invoice_vat = (($amount*$vat)/100);
$invoice_total = $amount + $invoice_vat;
}

if ($act=="crt")
{
//updating invoice number
MYSQL_QUERY("UPDATE invoice_nrs set invoice_nr_$template='$invoice_nr'");

MYSQL_QUERY("INSERT INTO invoices (clid,company,invoice_nr,service_type,date,invoice_items,subtotal,vat,total,uba,invoice_address,ymd,month,template)
VALUES ('$clid','".mysql_real_escape_string($company_invoice)."','$invoiceNr','CN','$date','".mysql_real_escape_string($description)."','$invoice_subtotal','$invoice_vat','$invoice_total','$uba','".mysql_real_escape_string($invoice_address)."','$ymd','$month','".mysql_real_escape_string($template)."')");
$invoice_id = mysql_insert_id();

if (isset($mail_post) and $mail_post=='mail')
{
$pdf->Output("F",dirname(__FILE__)."/tem/$invoiceNr.pdf");
include "mail_invoice.inc.php";
if(file_exists(dirname(__FILE__)."/tem/$invoiceNr.pdf"))
unlink(dirname(__FILE__)."/tem/$invoiceNr.pdf");
echo "<script>parent.location.href='../index.php?inc=create_credit_note';</script>";
}
else
{
$pdf->Output();
}
}
else
{
$pdf->Output();
}
}
?>
