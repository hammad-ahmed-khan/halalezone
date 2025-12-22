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
mb_internal_encoding("UTF-8");

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

//getting invoice number========================================================================
$kenmerk = "$clid-$invoiceNr";
$fak ="Invoice No.:
Invoice Date:";
$fak1 = "$invoiceNr
$date";

//get address=====================================================================
//if(trim($invoice_address)!='')
//$cla = json_decode($invoice_address,true);
//else
$cla = invoice_address($clid);

$address = $cla['address'];


class PDF extends fpdi
{
function Header()
{
global $invoiceNr;
if ($this->PageNo()>1)
{
	$this->SetY(20);
	$this->SetFont('Times','I',12);
    $this->SetX(20);
	$this->Cell(0,0,"Attachment to INVOICE No. ".$invoiceNr);
	$this->SetFont('Times','B',12);
	$this->SetY(30);
	$this->SetFont('Times','B',12);
    $this->SetX(20);
	$this->Cell(0,0,"No.");
	$this->SetX(30);
	$this->Cell(0,0,"Description");
	$this->SetX(170);
	$this->Cell(0,0,"Amount");
	$this->Line(20,$this->GetY()+3,190,$this->GetY() +3);
	$this->SetY($this->GetY() +10);
}
}

function Footer()
{
    $this->SetY(-20);
    $this->SetFont('Times','I',8);
	$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}

$pdf= new PDF();
$pagecount = $pdf->setSourceFile("../../client_data/templates/invoice_$template.pdf");
$tplidx = $pdf->ImportPage(1);
$pdf->addPage();
$pdf->SetMargins(20,30,20,20);
$pdf->useTemplate($tplidx,0,0,210);
$pdf->SetAuthor('Nouras');
$pdf->AliasNbPages();

	$ttl = 'INVOICE';
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
    $pdf->SetX(140);
    $pdf->MultiCell(100,5,"$fak",0,'0');
    $pdf->SetFont('Times','',12);
    $pdf->SetY($cur_post);
    $pdf->SetX(165);
    $pdf->MultiCell(100,5,"$fak1",0,'0');

	$pdf->SetFont('Times','B',12);
    $pdf->SetY($pdf->GetY() + 10);
    $pdf->SetX(20);
	$pdf->Cell(0,0,"Service type");
	$pdf->SetX(170);
	$pdf->Cell(0,0,"Amount");
	$pdf->Line(20,$pdf->GetY()+3,190,$pdf->GetY() +3);
	$pdf->SetFont('Times','',12);
    $pdf->SetY($pdf->GetY() + 8);
    $pdf->SetX(20);
	$pdf->Cell(0,0,str_replace('[other]','',$service_type));

	$pdf->SetX(170);
	$pdf->Cell(0,0,€.number_format($total_invoice,2));

    $pdf->SetFont('Times','I',12);
	$pdf->SetY($pdf->GetY() + 10);
    $pdf->SetX(20);
	$pdf->Cell(0,0,"For specifications see next page(s)");

if (isset($FPC) and $FPC!='')
{
	$pdf->SetFont('Times','B',12);
	$pdf->SetY($pdf->GetY() + 10);
    $pdf->SetX(20);
	$pdf->Cell(0,0,$FPC);
}

//certificates pages===================================================
	$pdf->SetY(297);
	$pdf->SetFont('Times','',12);
	$thisnr = 0;

if (isset($invoice_items) and strstr($invoice_items,"\n"))
{
$items =explode("\n",$invoice_items);
if (count($items)>0)
{
foreach($items as $item)
{
$item = str_replace("\n","",$item);
if (trim($item)!='')
{
$this_item = explode("|",$item);
	$thisnr ++;
	$pdf->SetY($pdf->GetY() + 5);
    $pdf->SetX(20);
	$pdf->Cell(0,0,$thisnr);
	$pdf->SetY($pdf->GetY() - 3);
	$pdf->SetX(30);
	$pdf->MultiCell(120,5,$this_item[0]);
	$pdf->SetX(168);
	$pdf->Cell(0,0,€);
	$HQCA1=number_format($this_item[1],2);
	$pdf->SetX(186 - $pdf->GetStringWidth($HQCA1));
	$pdf->Cell(0,0,$HQCA1);
}
}
}
}

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
$pdf->Cell(0,0,$this_total);
$invoice_subtotal = $total_invoice;
$invoice_total = $total_invoice;
$invoice_vat = 0;
}
else
{
$pdf->Cell(0,0,"SUBTOTAL:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);

$this_subtotal=number_format($subtotal_invoice,2);
$pdf->SetX(186 - $pdf->GetStringWidth($this_subtotal));
$pdf->Cell(0,0,$this_subtotal);

$pdf->SetY($pdf->GetY() + 5);
$pdf->SetX(140);
$pdf->Cell(0,0,"VAT 21%:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);
$this_vat = number_format($vat_invoice,2);
$pdf->SetX(186 - $pdf->GetStringWidth($this_vat));
$pdf->Cell(0,0,$this_vat);

$pdf->Line(140,$pdf->GetY()+3,190,$pdf->GetY() +3);
$pdf->SetY($pdf->GetY() + 6);
$pdf->SetX(140);
$pdf->Cell(0,0,"TOTAL:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);

$this_total=number_format($total_invoice,2);
$pdf->SetX(186 - $pdf->GetStringWidth($this_total));
$pdf->Cell(0,0,$this_total);
}

if (isset($LPC) and $LPC!='')
{
	$pdf->SetFont('Times','B',12);
	$pdf->SetY($pdf->GetY() + 10);
    $pdf->SetX(20);
	$pdf->Cell(0,0,$LPC);
}

if (isset($act) and $act=="rem")
{
$pdf->Output(dirname(__FILE__)."/tem/$invoiceNr.pdf");
include "mail_invoice_reminder.inc.php";
if(file_exists(dirname(__FILE__)."/tem/$invoiceNr.pdf"))
unlink(dirname(__FILE__)."/tem/$invoiceNr.pdf");
echo "<script>parent.location.href='../index.php';</script>";
}
elseif (isset($act) and $act=="sus")
{
$pdf->Output(dirname(__FILE__)."/tem/$invoiceNr.pdf");
include "mail_invoice_suspend.inc.php";
if(file_exists(dirname(__FILE__)."/tem/$invoiceNr.pdf"))
unlink(dirname(__FILE__)."/tem/$invoiceNr.pdf");
echo "<script>parent.location.href='../index.php';</script>";
} elseif(isset($act) and $act=="regen"){
$invFile = $prog_path."/client_data/invoices/{$invoiceNr}.pdf";
$pdf->Output($invFile,'F');
$invoice_url = "$prog_www/client_data/invoices/{$invoiceNr}.pdf";
echo '<a href="'.$invoice_url.'" target="_blank">'.$invoiceNr.'.pdf</a>';
}
else
{
$invFile = $prog_path."/client_data/invoices/{$invoiceNr}.pdf";
$pdf->Output($invFile,'F');
$invoice_url = "$prog_www/client_data/invoices/{$invoiceNr}.pdf";
$pdf->Output();
}
}
?>
