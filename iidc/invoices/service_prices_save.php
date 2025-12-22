<?php
if (!isset($_REQUEST['act']) && !isset($_REQUEST['clid'])) {
	exit();
};
include "../checkuser.inc.php";
include "../config/paths.inc.php";
include "../config/connect.inc.php";

if ($_REQUEST['act'] == 'update' and isset($_REQUEST['clid'])) {

	$prices = $_POST;
	unset($prices['act']);
	unset($prices['clid']);

	if ($_REQUEST['clid'] == 0) {
		$data['prices'] = json_encode($prices);
		update_option('default_prices', $data['prices']);
	} else {
		$data['prices'] = json_encode(array('batch' => $prices['batch'], 'annual' => $prices['annual'], 'product' => $prices['product'], 'supervision' => $_POST['supervision']));
		if ($amdb->get_row("SELECT * FROM companies_prices where clid='$_POST[clid]'")) {
			$amdb->update("companies_prices", $data, "clid='$_POST[clid]'");
		} else {
			$data['clid'] = $_POST['clid'];
			$amdb->insert("companies_prices", $data);
		}

		//hqc_supervisions
		/*
		if (trim($_POST['supervision']['perKM']) != '') {

			if ($supervisions = $amdb->query("SELECT * FROM hqc_supervisions WHERE clid='$_POST[clid]'")) {
				foreach ($supervisions as $supervision) {
					if ($kilometers = json_decode($supervision['kilometers'], true)) {
						if (isset($kilometers['rate'])) {
							$kilometers['rate'] = $_POST['supervision']['perKM'];
							if (isset($kilometers['total'])) {
								$kilometers['costs'] = $kilometers['total'] * ($_POST['supervision']['perKM'] / 100);
								$amdb->update("hqc_supervisions", array('kilometers' => json_encode($kilometers)), "auid='$supervision[auid]'");
							}
						};
					}
				}
			}
		} */
	}

	$amdb->post_results('location.reload();', 'function');
	exit();
}

if ($_REQUEST['act'] == 'delete' and isset($_REQUEST['clid'])) {
	if ($amdb->query("DELETE FROM companies_prices where clid='$_REQUEST[clid]'")) {
		$amdb->post_results('tr', 'remove');
		$amdb->post_results("reorderPrices();", 'function');
	}
	exit();
}

$batch = array();
$annual = array();
$product = array();

if ($_REQUEST['act'] == 'get_defaults') {
	if ($_REQUEST['clid'] == 0) {
		$prices = json_decode(get_option('default_prices'), true);

		if (isset($prices['batch']) and is_array($prices['batch']))
			$batch = $prices['batch'];

		if (isset($prices['annual']) and is_array($prices['annual']))
			$annual = $prices['annual'];

		if (isset($prices['product']) and is_array($prices['product']))
			$product = $prices['product'];

		if (isset($prices['supervision']) and is_array($prices['supervision']))
			$supervision = $prices['supervision'];

		$default = true;
	} else {
		if ($client_prices = $amdb->get_row("SELECT * FROM companies_prices where clid='$_REQUEST[clid]'")) {

			if (trim($client_prices['prices']) != '' and is_array(json_decode($client_prices['prices'], true))) {
				$prices = json_decode($client_prices['prices'], true);

				if (isset($prices['batch']) and is_array($prices['batch']))
					$batch = $prices['batch'];

				if (isset($prices['annual']) and is_array($prices['annual']))
					$annual = $prices['annual'];

				if (isset($prices['product']) and is_array($prices['product']))
					$product = $prices['product'];

				if (isset($prices['supervision']) and is_array($prices['supervision']))
					$supervision = $prices['supervision'];
			}
		}
		$company = $amdb->get_row("SELECT * FROM companies where clid='$_REQUEST[clid]'");
		$default = false;
	}
?>
	<style>
		#defaultPriceEdit * {
			white-space: nowrap;
		}

		#defaultPriceEdit li span {
			display: inline-block;
			width: 80px
		}

		#defaultPriceEdit input {
			width: 80px;
		}

		#defaultPriceEdit td div {
			margin: 4px 0px;
		}

		#defaultPriceEdit li {
			padding: 2px 0px;
		}

		#defaultPriceEdit td {
			width: 250px !important
		}
	</style>
	<script>
		function showCustom(obj, val) {
			if (val == 'custom') {
				jQuery('#' + obj).css({
					'position': 'relative',
					'top': 'auto'
				})
			} else {
				jQuery('#' + obj).css({
					'position': 'fixed',
					'top': '-5000px'
				})
			}
		}
	</script>
	<form action="service_prices_save.php" method="post" id="updateDefaultPrices" name="updateDefaultPrices" onsubmit="return post_this_form(this)" target="">
		<input name="act" type="hidden" value="update">
		<?php if ($_REQUEST['clid'] >= 0) { ?>
			<input name="clid" type="hidden" value="<?php echo $_REQUEST['clid']; ?>">
		<?php }; ?>
		<div style="padding:10px;min-width:600px">
			<table id="defaultPriceEdit" class="alternate" style="width:100% !important">
				<tr>
					<th colspan="2" style="color:red;white-space: normal !important;">
						<?php
						if ($_REQUEST['clid'] >= 0) {
							echo ($_REQUEST['clid'] == 0) ? 'Default prices' : $company['company_name'];
						} else {
							if ($companies = $amdb->get_results("SELECT *,companies.clid AS clid FROM companies
                                     JOIN users ON companies.clid = users.clid
									 LEFT JOIN companies_prices ON  companies.clid = companies_prices.clid
                                     WHERE companies.clof='0' and users.active='y' AND companies_prices.clid IS NULL
                                     ORDER BY companies.company_name ASC")) { ?>
								<select size="1" name="clid" data-required="yes" style="width:90%">
									<option value="">Please select a company</option>
									<?php foreach ($companies as $comp) { ?>
										<option value="<?php echo $comp['clid']; ?>"><?php echo $comp['company_name']; ?></option>
							<?php
									}
								}
							};
							?>
					</th>
				</tr>
				<tr>
					<th style="width:150px !important">Batch Certificate</th>
					<th id="batch" style="width:150px !important">
						<?php if ($_REQUEST['clid'] != 0) { ?>
							<select name="batch[type]" id="type_batch" style="width:;" onchange="showCustom('customBatch',this.value)">
								<option value="default">Default price</option>
								<option value="custom" <?php echo (isset($batch['type']) && $batch['type'] == 'custom') ? 'selected' : ''; ?>>Custom price</option>
							</select>
						<?php }; ?>
					</th>
				</tr>
				<tr style="position:<?php echo ((!isset($batch['type']) and isset($company)) or ($_REQUEST['clid'] != 0 and $batch['type'] == 'default')) ? 'fixed;top:-5000px' : 'relative'; ?>" id="customBatch">
					<td>
						<ul style="padding: 0px;margin: 0px;">
							<li><span>Minimum:</span>&euro; <input name="batch[minimum_amount]" type="text" id="minimum_amount" value="<?php echo  @$batch['minimum_amount']; ?>"></li>
							<li><span>Admin:</span>&euro; <input name="batch[admin_costs]" type="text" id="admin_costs" value="<?php echo  @$batch['admin_costs']; ?>"></li>
						</ul>
					</td>
					<td>
						<ul style="padding: 0px;margin: 0px;">
							<li><span>&lt;10.000kg:</span>&euro; <input name="batch[price1]" type="text" id="price1" value="<?php echo  @$batch['price1']; ?>"></li>
							<li><span>&gt;10.001kg:</span>&euro; <input name="batch[price2]" type="text" id="price2" value="<?php echo  @$batch['price2']; ?>"></li>
						</ul>
					</td>
				</tr>
				<tr>
					<th>Annual certificate</th>
					<td>
						<?php if ($_REQUEST['clid'] != 0) { ?>
							<span style="width:100px !important">
								<select name="annual[type]" id="type_annual" style="width:inherit" onchange="showCustom('customAnnual',this.value)">
									<option value="default">Default price</option>
									<option value="custom" <?php echo (isset($annual['type']) && $annual['type'] == 'custom') ? 'selected' : ''; ?>>Custom price</option>
								</select>
							</span>
						<?php }; ?>
						<span id="customAnnual" style="position:<?php echo ((!isset($annual['type']) and isset($company)) or ($_REQUEST['clid'] != 0 && $annual['type'] == 'default')) ? 'fixed;top:-5000px' : 'relative'; ?>">
							&euro; <input name="annual[cost]" type="text" id="annual[cost]" value="<?php echo  @$annual['cost']; ?>">
						</span>
					</td>
				</tr>
				<tr>
				<tr>
					<th>Adding product</th>
					<td>
						<?php if ($_REQUEST['clid'] != 0) { ?>
							<span style="width:100px !important">
								<select name="product[type]" id="type_product" style="width:inherit" onchange="showCustom('customProduct',this.value)">
									<option value="default">Default fees</option>
									<option value="custom" <?php echo (isset($product['type']) && $product['type'] == 'custom') ? 'selected' : ''; ?>>Custom fees</option>
								</select>
							</span>
						<?php }; ?>
						<span id="customProduct" style="position:<?php echo ((!isset($product['type']) and isset($company)) or ($_REQUEST['clid'] != 0 && $product['type'] == 'default')) ? 'fixed;top:-5000px' : 'relative'; ?>">
							&euro; <input name="product[cost]" type="text" id="product[cost]" value="<?php echo  @$product['cost']; ?>">
						</span>
					</td>
				</tr>
				<tr>
					<th>Supervision</th>
					<td>
						<?php if ($_REQUEST['clid'] != 0) { ?>
							<span style="width:100px !important;float:left;margin-right:5px">
								<select name="supervision[type]" id="type_supervision" style="width:inherit" onchange="showCustom('customSupervision',this.value)">
									<option value="default">Default fees</option>
									<option value="custom" <?php echo (isset($supervision['type']) && $supervision['type'] == 'custom') ? 'selected' : ''; ?>>Custom fees</option>
								</select>
							</span>
						<?php }; ?>
						<span id="customSupervision" style="position:<?php echo ((!isset($supervision['type']) and isset($company)) or ($_REQUEST['clid'] != 0 && $supervision['type'] == 'default')) ? 'fixed;top:-5000px' : 'relative'; ?>;float:left;overflow:hidden">
							<b style="display: inline-block;width: 120px;">Costs:</b> &euro; <input type="number" name="supervision[costs]" value="<?php echo isset($supervision) ? $supervision['costs'] : '' ?>" /><br />
							<b style="display: inline-block;width: 120px;">Charges per KM:</b>
							&euro; <input type="number" name="supervision[perKM]" value="<?php echo isset($supervision) ? $supervision['perKM'] : '' ?>" /> Euro cents.
						</span>
					</td>
				</tr>
			</table>
	</form>
	</div>
	<script>
	</script>
<?php } ?>