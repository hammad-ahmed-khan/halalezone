<?php
define("__HQC__", true);
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
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

if (isset($_REQUEST['act']))
	$invoice_data['act'] = $_REQUEST['act'];
else
	$invoice_data['act'] = 'crt';
if (isset($_REQUEST['clid'])) {
	$invoice_data['clid'] = $_REQUEST['clid'];
	if ($client = $amdb->get_row("SELECT * FROM companies
							  JOIN users ON companies.clid = users.clid
							  JOIN monthly_invoices ON  companies.clid = monthly_invoices.clid
							  WHERE monthly_invoices.status!='deleted' AND monthly_invoices.clid='$_REQUEST[clid]'")) {
		$invoice_data['subtotal'] = $client['amount'];
		$invoice_data['vat'] = $client['vat'];
		include(dirname(__FILE__) . '/pdf/pdf_recurring.inc.php');
	} else {
		return;
	};
} else {
	$curDay = date('d');
	$curTime = date('H');
	$curDate = date('Y-m-d');
	//get last day of month
	$lastDay = date('t');
	if ($curDay == $lastDay)
		$thisDay = '31';
	else
		$thisDay = $curDay;

	$clients = $amdb->get_results("SELECT monthly_invoices.*,companies.company_name,companies.clid,companies.offid FROM companies
							  JOIN users ON companies.clid = users.clid
							  JOIN monthly_invoices ON  companies.clid = monthly_invoices.clid
							  WHERE monthly_invoices.status != 'deleted'
							  AND monthly_invoices.starts_on <= NOW()
							  AND monthly_invoices.ends_on >= NOW()
							  AND FIND_IN_SET ('$thisDay',monthly_invoices.invoice_day)
							  AND monthly_invoices.invoice_hour <= '$curTime'
							  AND monthly_invoices.invoices NOT LIKE '%$curDate%'");

	$clientsToInvoice = array();

	if (isset($clients) and is_array($clients) and count($clients) > 0) {
		foreach ($clients as $client) {
			$client['starts_on'] = date('Y-m', strtotime($client['starts_on'])) . '-' . $curDay;
			$diff =  get_diff($client['starts_on']);

			for ($i = 0; $i <= $diff; $i++) {
				$tot = $i * $client['invoicing_every'];
				$invoiceDate = date("Y-m-".$curDay, strtotime("+$tot MONTHS", strtotime($client['starts_on'])));
				if ($invoiceDate == $curDate) {
					$clientsToInvoice[] = array('name' => $client['company_name'], 'clid' => $client['clid'], 'month' => $invoiceDate, 'amount' => $client['amount'], 'vat' => $client['vat'], 'uid' => $client['uid']);
				}
			}
		}
	}

	if (count($clientsToInvoice) > 0) {
		foreach ($clientsToInvoice as $client) {
			$invoice_data['clid'] = $client['clid'];
			$invoice_data['subtotal'] = $client['amount'];
			$invoice_data['act'] = 'crt';
			$invoice_data['invoice_month'] = $client['month'];
			$invoice_data['vat'] = $client['vat'];
			$invoice_data['uid'] = $client['uid'];
			include(dirname(__FILE__) . '/pdf/pdf_recurring.inc.php');
			if (isset($pdf))
				unset($pdf);
		}
	}
}
