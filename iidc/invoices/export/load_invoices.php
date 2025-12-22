<?php
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
function createInvoiceFile($nr, $invoice_nr)
{
?>
	<iframe src="../pdf/pdf_create_invoice.php?nr=<?php echo $nr; ?>&invoice_nr=<?php echo $invoice_nr; ?>&export=yes" style="position:fixed;left:-10000px;"></iframe>
<?php
}
$service_types = array('batch' => 'Batch Certificate(s)', 'annual' => 'Annual certificate', 'audit' => 'Audit', 'general' => 'Halal Services', 'credit_note' => 'Credit note', 'recurring' => 'Monthly', 'expenses' => 'Expenses');

if ($defaults = json_decode(get_option('invoice_defaults'), true)) {
	$service_types = $defaults['service_type'];
	$service_types['recurring'] = 'Monthly';
}
$whr = '';
$credit = "AND invoices.invoice_type != 'credit_note' AND invoices.status != 'credited'";
if ($_POST['year'] != 'all')
	$whr = "AND YEAR(invoices.inserted_on) = '{$_POST['year']}' ";
if ($_POST['show'] == 'paid')
	$whr .= "AND invoices.paid_on!='' $credit";
elseif ($_POST['show'] == 'unpaid')
	$whr .= "AND invoices.paid_on='' $credit";
elseif ($_POST['show'] == 'overdue')
	$whr .= "AND invoices.paid_on='' AND invoices.inserted_on < NOW() - INTERVAL 22 DAY $credit";
elseif ($_POST['show'] == 'credit')
	$whr .= "AND invoices.invoice_type = 'credit_note'";
elseif ($_POST['show'] == 'credited')
	$whr = "AND invoices.status = 'credited'";
elseif ($_POST['show'] == 'recurring')
	$whr = "AND invoices.invoice_type = 'recurring'";
elseif ($_POST['show'] == 'draft')
	$whr = "AND invoices.invoice_nr = 'draft'";
if ($_POST['show'] != 'draft')
	$whr .= "AND invoices.invoice_nr != 'draft'";
if (isset($_POST['clid']) and trim($_POST['clid']) != '')
	$whr .= "AND invoices.clid = '$_POST[clid]'";
$st = 0;
$limit = 10000;
if ($_POST['period'] == 'month') {
	$whr .= "AND MONTH(invoices.inserted_on)='$_POST[month]'";
}
if ($_POST['period'] == 'quarter') {
	$quarters = (($_POST['quarter'] * 3) - 2) . " AND " . ($_POST['quarter'] * 3);
	$whr .= " AND MONTH(invoices.inserted_on) BETWEEN " . $quarters;
}

$whr .= " AND invoices.status != 'hidden' AND invoices.status != 'deleted'";
if (isset($_POST['item_code']) and trim($_POST['item_code']) != '') {
	$whr .= " AND json_valid(invoice_items)=1 AND `invoice_items` LIKE '%$_POST[item_code]%'";
}
$IDPrefix = array();
if ($offices = $amdb->get_results("SELECT clients,reference_prefix,certificate_prefix,offid FROM offices ")) {
	foreach ($offices as $office) {
		$IDPrefix[$office['offid']] = $office['reference_prefix'] . $office['certificate_prefix'];
		if (isset($_SESSION['offid']) && $_SESSION['offid'] != 0 && $office['offid'] == $_SESSION['offid']) {
			$whr .= "AND FIND_IN_SET(invoices.clid,'$office[clients]')";
		}
	}
};

if (isset($_POST['sbsid']) and trim($_POST['sbsid']) != '') {
	$whr .= " AND invoices.sbsid = '$_POST[sbsid]'";
}

$sql = "SELECT nr,invoices.sbsid,invoices.clid,bclid,invoice_nr,invoice_type,service_type,invoice_items,subtotal,vat,total,paid_on,inserted_on,reminded_on,credit_invnr,invoices.status,companies.company_name,companies.country1,companies.active,companies.offid FROM `invoices`
				JOIN companies ON companies.clid = invoices.clid
				WHERE 1 AND invoices.template='nl' $whr ORDER BY TRIM(invoices.{$_REQUEST['orderBy']}) {$_REQUEST['ascDsc']}";

$nr = $st;
$subtotal = 0;
$vat = 0;
$total = 0;
if (!$invoices = $amdb->get_results($sql)) { ?>
	<tr>
		<td colspan="9" style="text-align:center">No invoice found</td>
	</tr>
<?php
	exit();
}
if ($_POST['act'] == 'export') {
	if ($_POST['exportTo'] == 'excel')
		include dirname(__FILE__) . "/export_to_excel.inc.php";
	if ($_POST['exportTo'] == 'zip')
		include dirname(__FILE__) . "/export_to_zip.inc.php";
	exit();
}
foreach ($invoices as $row) {
	$nr++;
	$subtotal = $subtotal + $row['subtotal'];
	$vat = $vat + $row['vat'];
	$total = $total + $row['total'];
	$st =  $row['service_type'];
	if ($row['invoice_type'] == 'batch')
		$row['invoice_type'] = 'a';
?>
	<tr id="inv_<?php echo $row['nr']; ?>">
		<th class="invItem"><?php echo $nr ?></th>
		<td class="clientInvoice" style="cursor:pointer" data-id="<?php echo $row['clid']; ?>"><span style="color:green">NL105105</span><?php echo str_pad($row['clid'], 5, '0', STR_PAD_LEFT); ?></td>
		<td>
			<?php echo $row['company_name']; ?> <span style="color:green"><?php echo $row['country1']; ?></span>
		</td>
		<td style="white-space:nowrap"><?php echo $service_types[$row['invoice_type']]; ?></td>
		<td>
			<?php
			$invFile = "/client_data/invoices/$row[invoice_nr].pdf";
			if (!file_exists($prog_path . $invFile)) {
				createInvoiceFile($row['nr'], $row['invoice_nr']);
			}
			if (file_exists($prog_path . $invFile)) { ?>
				<a href="<?php echo $prog_www; ?>/client_data/invoices/<?php echo $row['invoice_nr']; ?>.pdf" target="_blank"><?php echo $row['invoice_nr']; ?></a>
			<?php } else { ?>
				<?php if (trim($row['invoice_items']) != '' and is_array(json_decode(str_replace("\r\n", "<br/>", $row['invoice_items']), true))) { ?>
					<a href="pdf/pdf_create_invoice.php?nr=<?php echo $row['nr']; ?>" target="_blank"><?php echo $row['invoice_nr']; ?></a>
				<?php } else {
					if ($row['service_type'] == "CN") {
						$st = 'CN';
					} else {
						if ($row['service_type'] != 'HQC' and $row['service_type'] != 'COHS')
							$st = 'OTHER';
						else
							$st =  $row['service_type'];
					}
				?>
					<a href="../pdf/show_invoice.php?nr=<?php echo $row['nr']; ?>&st=<?php echo $st; ?>&tmpl=nl" target="_blank"><?php echo $row['invoice_nr']; ?>
					<?php }; ?>
				<?php }; ?>
		</td>
		<td><?php echo date("d/m/Y", strtotime($row['inserted_on'])); ?></td>
		<td>
			<?php
			if ($row['invoice_type'] == "credit_note")
				echo "<font color=red><i>&euro;" . number_format($row['subtotal'], 2, ',', '.') . "</i></font>";
			else
				echo "&euro;" . number_format($row['subtotal'], 2, ',', '.');
			?>
		</td>
		<td>
			<?php
			if ($row['invoice_type'] == "credit_note")
				echo "<font color=red><i>&euro;" . number_format($row['vat'], 2, ',', '.') . "</i></font>";
			else
				echo "&euro;" . number_format($row['vat'], 2, ',', '.');
			?>
		</td>
		<td>
			<?php
			if ($row['invoice_type'] == "credit_note")
				echo "<font color=red><i>&euro;" . number_format($row['total'], 2, ',', '.') . "</i></font>";
			else
				echo "&euro;" . number_format($row['total'], 2, ',', '.');
			?>
		</td>
	</tr>
<?php
} ?>
<tr>
	<th colspan="6" style="text-align: right;">Totals</th>
	<th>&euro;<?php echo number_format($subtotal, 2, ',', '.'); ?></th>
	<th>&euro;<?php echo number_format($vat, 2, ',', '.'); ?></th>
	<th>&euro;<?php echo number_format($total, 2, ',', '.'); ?></th>
</tr>