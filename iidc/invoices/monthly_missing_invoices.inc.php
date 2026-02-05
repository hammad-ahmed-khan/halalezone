<script>
	$("#page_title").html("Recurring invoicing")
</script>
<?php
$office = array();
if ($offices = $amdb->get_results("SELECT offid,office_name,reference_prefix,certificate_prefix FROM offices")) {
	foreach ($offices as $off) {
		$office[$off['offid']] = $off['reference_prefix'];
		$offids[$off['offid']] = $off['reference_prefix'] . $off['certificate_prefix'];
	}
}

$client = $amdb->get_row("SELECT monthly_invoices.*,companies.company_name,companies.clid,companies.offid FROM companies
							  JOIN users ON companies.clid = users.clid
							  JOIN monthly_invoices ON  companies.clid = monthly_invoices.clid
							  WHERE  monthly_invoices.imid='$_GET[imid]'");
?>

<style>
	td.companyName {
		white-space: normal !important
	}

	#companyPrices td,
	#companyPrices th {
		white-space: nowrap;
	}

	#companyPrices b {
		display: inline-block;
		width: 85px
	}
</style>
<script>
	function switchInvicing(val) {
		jQuery("#monthlyInvoices div").css("display", "none")
		jQuery("#monthlyInvoices div#" + val).css("display", "block")
	}

	function submitForm(act) {
		if (jQuery(".invoiceItem:checked").length == 0) {
			alert_message('Please select at least one invoice date');
			return false;
		}
		jQuery("input[name=act]").val(act)
		if (act == 'preview') {
			jQuery("#missedInvoicesForm").attr("target", "_new");
			jQuery("#missedInvoicesForm").removeAttr("onsubmit");
		} else {
			jQuery("#missedInvoicesForm").removeAttr("target");
			jQuery("#missedInvoicesForm").attr('onsubmit', 'return post_this_form(this);');
		}
		jQuery("#missedInvoicesForm").submit()
	}
</script>
<?php
function get_diff($start, $end = false)
{
	$start = new DateTime($start);
	if (!isset($end))
		$end = date("Y-m-d", time());
	$end = new DateTime($end);
	$interval = $end->diff($start);
	return $interval->format('%y') * 12 + $interval->format('%m');
}
?>
<h2>
	<center>Missed/Undelivered Invoices</center>
</h2>
<form action="recurring.php" method="post" id="missedInvoicesForm">
	<input type="hidden" name="clid" value="<?php echo $client['clid']; ?>" />
	<input type="hidden" name="missedInvoices" value="yes" />
	<input type="hidden" name="act" name="preview" />
	<input type="hidden" name="imid" value="<?php echo $client['imid']; ?>" />
	<table id="companyPrices" style="border:0px;">
		<thead>
			<tr>
				<th>Company</th>
				<th>Invoice Amount</th>
				<th>Period</th>
				<th>Invoices</th>
				<th>Missed invoices</th>
				<th style="width:110px !important">Action</th>
			</tr>
		</thead>
		<!--default prices-->
		<tbody id="monthlyInvoices">
			<!-- companies prices-->
			<?php
			if (isset($client)) {
			?>
				<tr>
					<td style="min-width:300px" class="load_popup" data-url="../../admin/load_company.php?clid=<?php echo $client['clid']; ?>" title="<?php echo $client['company_name']; ?>"><b><?php echo $offids[$client['offid']]; ?><?php echo str_pad($client['clid'], 6, '0', STR_PAD_LEFT); ?></b><br /><?php echo  $client['company_name']; ?></td>
					<td><?php echo  isset($client['amount']) ? '&euro;' . do_currency($client['amount']) : ''; ?></td>
					<td><?php echo  isset($client['starts_on']) ? date('d/m/Y', strtotime($client['starts_on'])) : ''; ?> - <?php echo  isset($client['ends_on']) ? date('d/m/Y', strtotime($client['ends_on'])) : ''; ?></td>
					<td>
						<ol style="padding:0px;margin:0px;" class="table table-striped table-bordered" id="invoiceItems">
							<?php
							if (trim($client['invoices']) != '' && is_array(json_decode($client['invoices'], true))) {
								$invoices = json_decode($client['invoices'], true);
								ksort($invoices);
								foreach ($invoices as $date => $invoice) { ?>
									<li>
										<?php echo date("d/m/Y", strtotime($date)); ?>
										<?php
										$invFile = "/client_data/invoices/$invoice.pdf";
										if (file_exists($prog_path . $invFile)) { ?>
											- <a href="<?php echo $prog_www; ?>/client_data/invoices/<?php echo $invoice; ?>.pdf" target="_blank"><?php echo $invoice; ?></a>
										<?php } ?>
									</li>
							<?php
								}
							}
							?>
						</ol>
					</td>
					<td>
						<ol style="padding:0px;margin:0px;" class="table table-striped table-bordered">
							<?php
							$invoices = array();
							$diff = get_diff($client['starts_on']);
							if (trim($client['invoices']) != '' && is_array(json_decode($client['invoices'], true))) {
								$invoices = json_decode($client['invoices'], true);
							}
							$missed = 0;
							for ($i = 0; $i <= $diff; $i++) {
								$tot = $i * $client['invoicing_every'];
								$date = "d/m/Y";
								$pastDate =  date('m', strtotime("+$tot MONTHS -2 days", strtotime($client['starts_on'])));
								if ($client['invoice_day'] > 28 && $pastDate == 2) {
									$date = (date('L', time()) ? "29" : "28") . "/02/Y";
								}
								$invoiceDate = fix_date(date($date, strtotime("+$tot MONTHS", strtotime($client['starts_on']))));
								if (!isset($invoices[$invoiceDate]))
									echo '<li><label><input type="checkbox" class="invoiceItem" name="invoiceDate[]" value="' . $invoiceDate . '"/>' . date("d/m/Y", strtotime($invoiceDate)) . '</label></li>';
							}
							?>
						</ol>
					</td>
					<td align="center" id="invoiceAction">
						<select size="1" onchange="switchInvicing(this.value)" style="margin-bottom:20px;">
							<option value="selected">Invoice selected</option>
							<?php /*
<option value="auto">Auto invoice</option>
*/ ?>
						</select>
						<div id="selected">
							<input type="button" value="Preview" onclick="submitForm('preview')" />
							<input type="button" value="Send" onclick="submitForm('crt')" />
						</div>
						<div id="auto" style="display:none">Auto invoice</div>
					</td>
				</tr>
			<?php
			}
			?>
		</tbody>
	</table>
</form>
<center><a class="button" href="index.php?inc=monthly_invoices">Cancel</a></center>