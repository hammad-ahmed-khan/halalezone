<?php
define("__HQC__", true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($prog_path))
	include "../config/paths.inc.php";
date_default_timezone_set('Europe/Amsterdam');

function get_diff($start, $end = false)
{
	$start = new DateTime($start);
	if (!isset($end))
		$end = date("Y-m-d", time());
	$end = new DateTime($end);
	$interval = $end->diff($start);
	return $interval->format('%y') * 12 + $interval->format('%m');
}

$today = date("d/m/Y");
$hour = date("H");
if ($invoices = $amdb->get_results("SELECT * FROM companies
							  JOIN users ON companies.clid = users.clid
							  JOIN invoices ON  companies.clid = invoices.clid
							  WHERE invoices.invoice_nr ='scheduled' AND JSON_VALID(invoice_options) = 1 AND JSON_EXTRACT(invoice_options,'$.scheduled') != '' AND invoices.status = 'active'")) {

	$invoicesToInvoice = array();

	if (isset($invoices) and is_array($invoices) and count($invoices) > 0) {
		foreach ($invoices as $invoice) {
			$scheduled = decode_json($invoice['invoice_options'])['scheduled'];
			if ($scheduled['date'] == $today && $scheduled['date'] >= $hour) {
				$invoicesToInvoice[] = $invoice;
			}
		}
	}

	if (count($invoicesToInvoice) > 0) {
		foreach ($invoicesToInvoice as $invoice) {
			$invoice_data = $invoice;
			$invoice_data['act'] = 'crt';
			include(dirname(__FILE__) . '/pdf/pdf_scheduled.php');
			if (isset($pdf))
				unset($pdf);
		}
	}
}
