<?php
if ($_SESSION['user_role'] == "super_admin") {
?>
	<script>
		$("#page_title").html("Invoice Defaults")
	</script>

	<style>
		table td b {
			display: inline-block;
			width: 100px;
			float: left;
		}

		table td div {
			margin: 4px;
			display: flex;
		}

		table td input {
			width: 95%;
		}

		table td div input {
			width: 200px;
		}

		table th {
			white-space: nowrap
		}
	</style>
	<?php
	$service_type_default = array('a' => 'Batch Certificate(s)', 'b' => 'Batch Certificate(s)', 'annual' => 'Annual certificate', 'audit' => 'Audit', 'general' => 'Halal Services', 'credit_note' => 'Credit note', 'expenses' => 'Expenses');
	$invoice_item_default = array('a' => 'Certificate A (Meat product)', 'b' => 'Certificate B (Non-meat product)', 'annual' => 'Annual certificate', 'audit' => 'Audit', 'general' => 'Halal Services', 'credit_note' => 'Credit note', 'expenses' => 'Expenses');

	$service_type = array();
	$invoice_item = array();

	if ($defauls = json_decode(get_option('invoice_defaults'), true)) {
		$service_type = $defauls['service_type'];
		$invoice_item = $defauls['invoice_item'];
	}

	foreach ($service_type_default as $key => $value) {
		if (!isset($service_type[$key]))
			$service_type[$key] = $value;
	}

	foreach ($invoice_item_default as $key => $value) {
		if (!isset($invoice_item[$key]))
			$invoice_item[$key] = $value;
	}
	?>
	<h2>Invoice defaults</h2>
	<form action="invoice_defaults_save.php" method="post" name="invoice_defaults" id="invoice_defaults" onSubmit="return post_this_form(this)">
		<table class="alternate" style="width:750px;margin-top:20px">
			<tr>
				<th colspan="4">
					<center>Invoice defaults</center>
				</th>
			</tr>
			<tr>
				<th>Invoice type</th>
				<th>Service type</th>
				<th colspan="2">Invoice item</th>
			</tr>
			<tr>
				<th style="width:100px">Halal Shipment Certificate Service:</th>
				<td style="width:200px"><input type="text" name="service_type[a]" data-required="yes" value="<?php echo $service_type['a']; ?>" /></td>
				<td style="width:200px"><input type="text" name="invoice_item[a]" data-required="yes" value="<?php echo $invoice_item['a']; ?>" /></td>
			<tr>
				<?php /*
			<tr>
				<th style="width:100px">Batch (B Non-Meat products):</th>
				<td style="width:200px"><input type="text" name="service_type[b]" data-required="yes" value="<?php echo $service_type['b']; ?>" /></td>
				<td style="width:200px"><input type="text" name="invoice_item[b]" data-required="yes" value="<?php echo $invoice_item['b']; ?>" /></td>
			<tr>
*/ ?>
			<tr>
				<th style="width:100px">Annual certificate:</th>
				<td style="width:200px"><input type="text" name="service_type[annual]" data-required="yes" value="<?php echo $service_type['annual']; ?>" /></td>
				<td style="width:200px"><input type="text" name="invoice_item[annual]" data-required="yes" value="<?php echo $invoice_item['annual']; ?>" /></td>
			<tr>

			<tr>
				<th style="width:100px">Audit:</th>
				<td style="width:200px"><input type="text" name="service_type[audit]" data-required="yes" value="<?php echo $service_type['audit']; ?>" /></td>
				<td style="width:200px"><input type="text" name="invoice_item[audit]" data-required="yes" value="<?php echo $invoice_item['audit']; ?>" /></td>
			<tr>

			<tr>
				<th style="width:100px">General invoice:</th>
				<td style="width:200px"><input type="text" name="service_type[general]" data-required="yes" value="<?php echo $service_type['general']; ?>" /></td>
				<td style="width:200px"><input type="text" name="invoice_item[general]" data-required="yes" value="<?php echo $invoice_item['general']; ?>" /></td>
			<tr>

			<tr>
				<th style="width:100px">Credit note:</th>
				<td style="width:200px"><input type="text" name="service_type[credit_note]" data-required="yes" value="<?php echo $service_type['credit_note']; ?>" /></td>
				<td style="width:200px"><input type="text" name="invoice_item[credit_note]" data-required="yes" value="<?php echo $invoice_item['credit_note']; ?>" /></td>
			</tr>
			<tr>
				<th style="width:100px">Expenses:</th>
				<td style="width:200px"><input type="text" name="service_type[expenses]" data-required="yes" value="<?php echo $service_type['expenses']; ?>" /></td>
				<td style="width:200px"><input type="text" name="invoice_item[expenses]" data-required="yes" value="<?php echo $invoice_item['expenses']; ?>" /></td>
			</tr>
		</table>
		<div style="text-align:center;margin-top:20px;">
			<input type="reset" value="Reset" style="width:200px">
			<input type="submit" value="Save" style="width:200px">
		</div>
	</form>
<?php }; ?>