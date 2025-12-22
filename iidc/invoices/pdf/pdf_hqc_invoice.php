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

$invoice_items ="";
$totals =0;
$invoice_items .="$HQC_Nr|$issued_at|$valid_until\n";
foreach($_POST as $key=>$value){
	if (strstr($key,'HQCA'))
	$$key = str_replace(array('.',','),array('','.'),$value);
	};

if (isset($HQCD1) and $HQCD1!='' and isset($HQCA1) and $HQCA1!='')
{
$invoice_items .="$HQCD1|$HQCA1\n";
$totals= $totals + $HQCA1;
}

if (isset($HQCD2) and $HQCD2!='' and isset($HQCA2) and $HQCA2!='')
{
$invoice_items .="$HQCD2|$HQCA2\n";
$totals= $totals + $HQCA2;
}
if (isset($HQCD3) and $HQCD3!='' and isset($HQCA3) and $HQCA3!='')
{
$invoice_items .="$HQCD3|$HQCA3\n";
$totals= $totals + $HQCA3;
}
if (isset($HQCD4) and $HQCD4!='' and isset($HQCA4) and $HQCA4!='')
{
$invoice_items .="$HQCD4|$HQCA4\n";
$totals= $totals + $HQCA4;
}
if (isset($HQCD5) and $HQCD5!='' and isset($HQCA5) and $HQCA5!='')
{
$invoice_items .="$HQCD5|$HQCA5\n";
$totals= $totals + $HQCA5;
}

$total_invoice = $totals;

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
$fak ="Invoice No.:
Invoice Date:";
$fak1 = "$invoiceNr
$date";

//get address=====================================================================
if (isset($bclid) and trim($bclid)!="")
$invClid = $bclid;
else
$invClid = $clid;

$cla = invoice_address($invClid);
$invoice_address = json_encode($cla,true);
$address = $cla['address'];
$company_invoice = $cla['company_invoice'];
$client_name = $cla['client_name'];
$client_email = $cla['client_email'];
$uba = $cla['uba'];

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

	if ($act=="crt")
	$ttl = 'INVOICE';
	else
	$ttl = 'PREVIEW/INVOICE';

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

	$pdf->SetX(60);
	$pdf->Cell(0,0,"HQC No");

	$pdf->SetX(90);
	$pdf->Cell(0,0,"Issued at");

	$pdf->SetX(120);
	$pdf->Cell(0,0,"Valid until");

	$pdf->SetX(170);
	$pdf->Cell(0,0,"Amount");
	$pdf->Line(20,$pdf->GetY()+3,190,$pdf->GetY() +3);
	$pdf->SetFont('Times','',12);
    $pdf->SetY($pdf->GetY() + 8);
    $pdf->SetX(20);
	$pdf->Cell(0,0,"HQC Certificates");

	$pdf->SetX(60);
	$pdf->Cell(0,0,$HQC_Nr);

	$pdf->SetX(90);
	$pdf->Cell(0,0,$issued_at);

	$pdf->SetX(120);
	$pdf->Cell(0,0,$valid_until);

	$pdf->SetX(170);

	if ($template=="uae")
	{
	$pdf->Cell(0,0,€.number_format($total_invoice,2,',','.'));
	}
	else
	{
	$pdf->Cell(0,0,€.number_format(((($total_invoice * $vat)/100) + $total_invoice),2,',','.'));
	}

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

	$nr = 0;

if (isset($HQCD1) and $HQCD1!='' and isset($HQCA1) and $HQCA1!='')
{
	$nr ++;
	$pdf->SetY($pdf->GetY() + 5);
    $pdf->SetX(20);
	$pdf->Cell(0,0,$nr);
	$pdf->SetX(30);
	$pdf->Cell(0,0,$HQCD1);
	$pdf->SetX(168);
	$pdf->Cell(0,0,€);
	$HQCA1=number_format($HQCA1,2,',','.');
	$pdf->SetX(186 - $pdf->GetStringWidth($HQCA1));
	$pdf->Cell(0,0,$HQCA1);
}

if (isset($HQCD2) and $HQCD2!='' and isset($HQCA2) and $HQCA2!='')
{
	$nr ++;
	$pdf->SetY($pdf->GetY() + 5);
    $pdf->SetX(20);
	$pdf->Cell(0,0,$nr);
	$pdf->SetX(30);
	$pdf->Cell(0,0,$HQCD2);
	$pdf->SetX(168);
	$pdf->Cell(0,0,€);
	$HQCA2=number_format($HQCA2,2);
	$pdf->SetX(186 - $pdf->GetStringWidth($HQCA2));
	$pdf->Cell(0,0,$HQCA2);
}

if (isset($HQCD3) and $HQCD3!='' and isset($HQCA3) and $HQCA3!='')
{
	$nr ++;
	$pdf->SetY($pdf->GetY() + 5);
    $pdf->SetX(20);
	$pdf->Cell(0,0,$nr);
	$pdf->SetX(30);
	$pdf->Cell(0,0,$HQCD3);
	$pdf->SetX(168);
	$pdf->Cell(0,0,€);
	$HQCA3=number_format($HQCA3,2,',','.');
	$pdf->SetX(186 - $pdf->GetStringWidth($HQCA3));
	$pdf->Cell(0,0,$HQCA3);
}

if (isset($HQCD4) and $HQCD4!='' and isset($HQCA4) and $HQCA4!='')
{
	$nr ++;
	$pdf->SetY($pdf->GetY() + 5);
    $pdf->SetX(20);
	$pdf->Cell(0,0,$nr);
	$pdf->SetX(30);
	$pdf->Cell(0,0,$HQCD4);
	$pdf->SetX(168);
	$pdf->Cell(0,0,€);
	$HQCA4=number_format($HQCA4,2,',','.');
	$pdf->SetX(186 - $pdf->GetStringWidth($HQCA4));
	$pdf->Cell(0,0,$HQCA4);
}

if (isset($HQCD5) and $HQCD5!='' and isset($HQCA5) and $HQCA5!='')
{
	$nr ++;
	$pdf->SetY($pdf->GetY() + 5);
    $pdf->SetX(20);
	$pdf->Cell(0,0,$nr);
	$pdf->SetX(30);
	$pdf->Cell(0,0,$HQCD5);
	$pdf->SetX(168);
	$pdf->Cell(0,0,€);
	$HQCA5=number_format($HQCA5,2,',','.');
	$pdf->SetX(186 - $pdf->GetStringWidth($HQCA5));
	$pdf->Cell(0,0,$HQCA5);
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
$this_total=number_format($total_invoice,2,',','.');
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

$this_subtotal=number_format($total_invoice,2,',','.');
$pdf->SetX(186 - $pdf->GetStringWidth($this_subtotal));
$pdf->Cell(0,0,$this_subtotal);

$pdf->SetY($pdf->GetY() + 5);
$pdf->SetX(140);
$pdf->Cell(0,0,"VAT $vat%:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);
$this_vat = number_format((($total_invoice*$vat)/100),2,',','.');
$pdf->SetX(186 - $pdf->GetStringWidth($this_vat));
$pdf->Cell(0,0,$this_vat);

$pdf->Line(140,$pdf->GetY()+3,190,$pdf->GetY() +3);
$pdf->SetY($pdf->GetY() + 6);
$pdf->SetX(140);
$pdf->Cell(0,0,"TOTAL:");
$pdf->SetX(168);
$pdf->Cell(0,0,€);

$this_total=number_format((($total_invoice*$vat)/100) + $total_invoice,2,',','.');
$pdf->SetX(186 - $pdf->GetStringWidth($this_total));
$pdf->Cell(0,0,$this_total);

$invoice_subtotal = $total_invoice;
$invoice_vat = (($total_invoice*$vat)/100);
$invoice_total = $total_invoice + $invoice_vat;
}

if (isset($LPC) and $LPC!='')
{
	$pdf->SetFont('Times','B',12);
	$pdf->SetY($pdf->GetY() + 10);
    $pdf->SetX(20);
	$pdf->Cell(0,0,$LPC);
}

if ($act=="crt")
{

//updating invoice number
MYSQL_QUERY("UPDATE invoice_nrs set invoice_nr_$template='$invoice_nr'");

MYSQL_QUERY("INSERT INTO invoices (clid,bclid,company,invoice_nr,service_type,date,invoice_items,subtotal,vat,total,uba,invoice_address,ymd,month,template)
VALUES ('$clid','$invClid','".mysql_real_escape_string($company_invoice)."','$invoiceNr','HQC','$date','$invoice_items','$invoice_subtotal','$invoice_vat','$invoice_total','".mysql_real_escape_string($invoice_address)."','$uba','$ymd','$month','".mysql_real_escape_string($template)."')");
$invoice_id = mysql_insert_id();
mysql_query("update $tbl[prefix]_halal_certificates set invoice_nr='$invoiceNr' where clid='$clid' and crtNr='$HQC_Nr'");
if (isset($mail_post) and $mail_post=='mail')
{
$pdf->Output("F",dirname(__FILE__)."/tem/$invoiceNr.pdf");
include "mail_invoice.inc.php";
if(file_exists(dirname(__FILE__)."/tem/$invoiceNr.pdf"))
unlink(dirname(__FILE__)."/tem/$invoiceNr.pdf");
echo "<script>parent.location.href='../index.php?inc=create_hqc_invoice';</script>";
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
