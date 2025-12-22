<?php
define("__HQC__",true);
use setasign\Fpdi\Fpdi;
include "../../config/paths.inc.php";
include "../../config/mysql_ftp.inc.php";
include "../../config/connect.inc.php";
require_once($prog_path.'/pdf/fpdf/fpdf.php');
require_once($prog_path.'/pdf/src/autoload.php');
if (count($_GET)>0) {
$values =array();
foreach ($_GET as $key => $value) {
$$key = $value;
}

$date = date("d/m/Y");

$result = MYSQL_QUERY("SELECT * FROM invoices where nr='$nr'");
if (@MYSQL_NUM_ROWS($result) > 0){
$row = MYSQL_FETCH_ARRAY($result);
$clid=$row['clid'];
$invoiceNr=$row['invoice_nr'];
$service_type=$row['service_type'];
$date=$row['date'];
$invoice_items=$row['invoice_items'];
$subtotal_invoice=$row['subtotal'];
$vat_invoice=$row['vat'];
$total_invoice=$row['total'];
$template = $row['template'];
$invoice_address = $row['invoice_address'];
}

$kenmerk = "$clid-$invoiceNr";
$fak ="Credit note No.:
Credit note Date:";
$fak1 = "$invoiceNr
$date";

//get address=====================================================================
/*if(trim($invoice_address)!='')
$cla = json_decode($invoice_address,true);
else*/
$cla = invoice_address($clid);
$address = $cla['address'];

$pdf= new FPDI();

$pagecount = $pdf->setSourceFile("../../client_data/templates/letter_head_$template.pdf");
$tplidx = $pdf->ImportPage(1);
$pdf->addPage();
$pdf->SetMargins(20,30,20,20);
$pdf->useTemplate($tplidx,0,0,210);
$pdf->SetAuthor('Nouras');
$pdf->AliasNbPages();

	$ttl = 'CREDIT NOTE';
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
	$pdf->MultiCell(140,5,"$invoice_items",0,'0');
$pdf->SetX(168);
$pdf->Cell(0,0,€);
$this_total=number_format($subtotal_invoice,2);
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
$this_total=number_format($total_invoice,2);
$pdf->SetX(186 - $pdf->GetStringWidth($this_total));
$pdf->Cell(0,0,"-$this_total");
}
else
{
$pdf->Cell(0,0,"SUBTOTAL:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);

$this_subtotal=number_format($subtotal_invoice,2);
$pdf->SetX(186 - $pdf->GetStringWidth($this_subtotal));
$pdf->Cell(0,0,"-$this_subtotal");

$pdf->SetY($pdf->GetY() + 5);
$pdf->SetX(140);
$pdf->Cell(0,0,"VAT:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);
$this_vat = number_format($vat_invoice,2);
$pdf->SetX(186 - $pdf->GetStringWidth($this_vat));
$pdf->Cell(0,0,"-$this_vat");

$pdf->Line(140,$pdf->GetY()+3,190,$pdf->GetY() +3);
$pdf->SetY($pdf->GetY() + 6);
$pdf->SetX(140);
$pdf->Cell(0,0,"TOTAL:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);

$this_total=number_format($subtotal_invoice + $vat_invoice,2);
$pdf->SetX(186 - $pdf->GetStringWidth($this_total));
$pdf->Cell(0,0,"-$this_total");
}
$invFile = $prog_path."/client_data/invoices/{$invoiceNr}.pdf";
$pdf->Output($invFile,'F');
$invoice_url = "$prog_www/client_data/invoices/{$invoiceNr}.pdf";
$pdf->Output();
}

?>
