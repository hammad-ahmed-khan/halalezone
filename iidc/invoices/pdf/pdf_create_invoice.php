<?php
define("__HQC__", true);

if (!isset($prog_path))
	include "../../config/paths.inc.php";

//collecting invoice items
$invoiceItems = array();

if (!isset($_GET['nr']) or !$invoice = $amdb->get_row("SELECT * FROM invoices WHERE nr='$_GET[nr]'"))
	return;

$invFile = $prog_path . "/client_data/invoices/{$invoice['invoice_nr']}.pdf";
if (file_exists($invFile)) {
	header("location: $prog_www/client_data/invoices/{$invoice_nr}.pdf");
	exit();
}

$nrIndex = 0;
$invItems = array();

if (trim($invoice['invoice_items']) != '' and is_array(json_decode(str_replace("\r\n", "<br/>", $invoice['invoice_items']), true))) {
	$invItems = json_decode(str_replace("\r\n", "<br/>", $invoice['invoice_items']), true);
} else {

	$invoice_items = explode("\n", $invoice['invoice_items']);

	foreach ($invoice_items as $item) {
		if (trim($item) != '') {
			$nrIndex++;
			$invItem = explode('|', $item);
			$invItems[$nrIndex]['selected'] = $invItem[0];
			$invItems[$nrIndex]['certificate'] = $invItem[1];
			if (isset($invItem[4]))
				$invItems[$nrIndex]['type'] = $invItem[4];
			$invItems[$nrIndex]['product'] = ($invItem[4] == 'a') ? 'Certificate A (meat)' : 'Certificate B (none meat)';
			$invItems[$nrIndex]['description'] = 'Certificate Nr: ' . $invItem[1] . "\r\n" . 'Date: ' . $invItem[2];
			if (trim($invItem[5]) != '')
				$invItems[$nrIndex]['description'] .= "\r\n" . 'reference: ' . str_replace('_', ' ', $invItem[5]);
			$invItems[$nrIndex]['amount'] = $invItem[3];
		}
	};
}

$_POST = $invoice;
$_POST['FPC'] = '';
$_POST['LPC'] = '';
$_POST['clid'] = $invoice['clid'];
$_POST['service_type'] = $invoice['service_type'];
$_POST['item'] = $invItems;





$nr = 1;
$tableToUpdate = array();
if (!isset($_POST['item']))
	exit();

foreach ($_POST['item'] as $item) {
	if (isset($item['selected']) and trim($item['description']) != '' and trim($item['amount']) != '') {
		$invoiceItems[] = $item;

		if (isset($item['type']) and isset($item['crtNr'])) {
			$tableToUpdate[$item['type']][] = $item['crtNr'];
		}
	}
}

$wd['nr'] = '1';
$wd['product'] = '5';
$wd['description'] = '8';
$wd['amount'] = '3';

$totals['subtotal'] = 0;

$pdfItems = '<tr style="background-color:#eee">
			<th width="' . $wd['nr'] . 'cm">Nr</th>
			<th width="' . $wd['product'] . 'cm">Item</th>
			<th width="' . $wd['description'] . 'cm">Description</th>
			<th width="' . $wd['amount'] . 'cm">Amount</th>
			</tr>';

$pdfItem = '<tr>
			<td[doHide]>[nr]</td>
			<td[doHide]>[product]</td>
			<td>[description]</td>
			<td>€ [amount]</td>
			</tr>';

$pdfTotals = 	'<tr><th width="11.5cm"></th><th width="2.5cm" class="bt"><b>Subtotal:</b></th><th width="3cm" class="bt">€ [subtotal]</th></tr>
				<tr><td></td><td class="bt"><b>VAT:</b></td><td>€ [vat]</td></tr>
				<tr><td></td><th><b>Total:</b></th><th>€ [total]</th></tr>';

foreach ($invoiceItems as $inv => $invoiceItem) {
	$invoiceItem['amount'] = str_replace(array('.', ','), array('', '.'), $invoiceItem['amount']);
	$totals['subtotal'] = $totals['subtotal'] + $invoiceItem['amount'];
	$invoiceItem['amount'] = number_format($invoiceItem['amount'], 2, ',', '.');
	$invoiceLine = $pdfItem;

	//handling product with multi items
	if ($invoiceItem['product'] == 'hidden') {
		$invoiceLine = str_replace(array('[doHide]', '[nr]', '[product]'), array(' class="hideB"', '', ''), $invoiceLine);
	} else {
		$invoiceLine = str_replace('[doHide]', '', $invoiceLine);
		$invoiceItem['nr'] = $nr++;
	}

	foreach ($invoiceItem as $key => $value) {
		$invoiceLine = str_replace('[' . $key . ']', str_replace("\r\n", "<br/>", $value), $invoiceLine);
	}
	$pdfItems .= $invoiceLine;
};

$totals['vat'] = ($totals['subtotal'] * 0.21);
$totals['total'] =  $totals['subtotal'] + $totals['vat'];

foreach ($totals as $key => $value) {
	$pdfTotals = str_replace('[' . $key . ']', number_format($value, 2, ',', '.'), $pdfTotals);
}

ob_start();
?>
<style>
	table#ivoiceItems th {
		font-weight: bold;
		border-top: 1px solid #000000;
		border-bottom: 1px solid #000000;
		height: 24px
	}

	table#ivoiceItems td,
	table#ivoiceTotals th {
		border-top: 1px solid #555555;
		padding: 10px
	}

	table#ivoiceItems td.hideB {
		border-top: none
	}
</style>
<?php
echo '<table cellpadding="4" id="ivoiceItems">' . $pdfItems . '</table>';
echo '<table cellpadding="4" id="ivoiceTotals">' . $pdfTotals . '</table>';
$pdf_data['invoice_items'] = ob_get_contents();
ob_end_clean();

//get address=====================================================================
$cla = invoice_address($invoice['clid']);

$invoice_address = json_encode($cla, true);
$address = $cla['address'];

if (intval($_POST['vat']) > 0)
	$address = str_replace('VAT shifted', '', $address);

$company_name = $cla['company_name'];
$client_name = $cla['client_name'];
$client_email = $cla['client_email'];
$uba = $cla['uba'];
$invoice_nr = $_POST['invoice_nr'];

//end get address=================================================================
$pdf_data['account_holder_name'] = 'Control Office of Halal Slaughtering BV';
$pdf_data['bank_name'] = 'ABN AMRO NV (ABN AMRO Group)';
$pdf_data['bank_address'] = 'Koningskade 30, 2596 AA Den Haag';
$pdf_data['BIC'] = 'ABNANL2A';
$pdf_data['IBAN'] = 'NL47ABNA0457665793';


$office = $amdb->get_row("SELECT * FROM offices WHERE offid = '0'");

$pdf_data['reference_number'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($_POST['clid'], 5, "0", STR_PAD_LEFT);
$pdf_data['invoice_date'] = date("d/m/Y", strtotime($_POST['inserted_on']));

$pdf_data['company_address'] = str_replace("\n", "<br/>", $address);
$pdf_data['invoice_number'] = $_POST['invoice_nr'];
$pdf_data['service_type'] = $_POST['service_type'];
$pdf_data['invoice_total'] = number_format($totals['total'], 2, ',', '.');
$pdf_data['FPC'] = $_POST['FPC'];
$pdf_data['LPC'] = $_POST['LPC'];

//end collecting invoice items

//printing PDF
require_once("$prog_path/pdf/tcpdf/hcp_pdf.inc.php");
$pages = $pdf->setSourceFile("../../data/templates/invoice.pdf");
$pdf->SetMargins(20, 20, 20);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->SetPrintFooter(true);
setTemplate(1);

if (!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='$_POST[invoice_type]'"))
	$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='invoice'");

$theTemplate = $row['pdf_template'];

if (count($pdf_data) > 0) {
	foreach ($pdf_data as $key => $value) {
		$theTemplate = str_replace('[' . $key . ']', $value, $theTemplate);
	}
}

if (trim($theTemplate) != '') {
	$parts = preg_match_all('/\[pdf(.*)\]/U', $theTemplate, $thePatrs);
	foreach ($thePatrs[0] as $macth) {
		$theTemplate  = str_replace($macth, "<brkPoint>" . $macth . "<brkPoint>", $theTemplate);
	};

	$pdfParts = explode('<brkPoint>', $theTemplate);

	ob_start();
	$pdf->writeHTML(ob_get_contents());
	ob_end_clean();

	foreach ($pdfParts as $key => $part) {
		if (strstr($part, '[pdf ')) {
			preg_match('/\[pdf (.*)\((.*)\)\]/', $part, $pdfMatch);
			if (trim($pdfMatch[1]) == 'setY') {
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
				$pdf->SetAutoPageBreak(TRUE, 25);
				$pdf->AddPage();
			}
		} elseif (trim($part) != '') {
			ob_start();
			echo trim($part);
			$pdf->writeHTML(ob_get_contents());
			ob_end_clean();
		}
	}
}

$invFile = $prog_path . "/client_data/invoices/{$invoice_nr}.pdf";
$pdf->Output($invFile, 'F');

if (!file_exists($invFile))
	return;
if (!isset($_REQUEST['export']))
	header("location: $prog_www/client_data/invoices/{$invoice_nr}.pdf");
?>