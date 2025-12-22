<script>
	function check_span(spn) {
		jQuery("#export_inputs span").css({
			'display': 'none'
		});
		jQuery("#export_invoices").css("display", "none")
		jQuery("#export_inputs span#search_" + spn).css("display", "inline-block")
	}

	function loadInvoices() {
		if (jQuery("#period").val() == 'date') {
			if (jQuery("#date_from").val().trim() == '' || jQuery("#date_to").val().trim() == '') {
				alert_message('One or both of the date fields is empty');
				return false;
			}
		}
		if (jQuery("#seach_form_act").val() == 'export') {
			return true;
		}
		jQuery("#export_invoices").css("display", "none")
		jQuery.post('load_invoices.php', jQuery("#seach_form").serialize()).done(function(data) {
			if (data.trim().length > 0) {
				if (data.indexOf("error:") > -1) {
					alert_message(data.replace('error:', ''));
				} else if (data.indexOf("file:") > -1) {
					window.open(data.replace('file:', ''));
				} else {
					jQuery("#invoiceItems").html(data);
					if (data.indexOf('No invice found') == -1)
						jQuery("#export_invoices").css("display", "inline-block")
				}
			}
		});
		return false;
	}
</script>
<?php
$clients = $amdb->get_results("SELECT companies.clid,companies.company_name FROM companies
								JOIN invoices ON companies.clid = invoices.clid
                                JOIN users ON companies.clid = users.clid
                                WHERE users.active = 'y'
                                group by invoices.clid
                                ORDER BY companies.company_name ASC");
?>
<table border="0" style="border:0px !important;width:100% !important">
	<form method="post" action="load_invoices.php" name="seach_form" id="seach_form" target="_blank" onsubmit="return loadInvoices()">
		<input type="hidden" name="act" id="seach_form_act" value="search" />
		<input type="hidden" name="orderBy" value="inserted_on" />
		<input type="hidden" name="ascDsc" value="DESC" />
		<input type="hidden" name="show" value="" />
		<tr>
			<td id="export_inputs" align="center">
				<b>Client</b> <select size="1" name="clid" style="max-width:250px;">
					<option value="">Select client</option>
					<?php foreach ($clients as $client) { ?>
						<option value="<?php echo $client['clid']; ?>"><?php echo str_replace("\'", "'", $client['company_name']); ?></option>
					<?php } ?>
				</select>
				<b>Invoice type:</b>
				<select size="1" name="show" style="width:140px">
					<option value="all">All Invoices</option>
					<option value="paid">Paid Invoices</option>
					<option value="unpaid">Unpaid Invoices</option>
					<option value="overdue">Over due</option>
					<option value="credit">Credit notes</option>
				</select>
				<b>Year:</b>
				<select name="year" size="1">
					<option value="all">All years</option>
					<?php for ($year = date("Y"); $year >= 2007; $year--) { ?>
						<option value="<?php echo $year; ?>" <?php echo $year == date("Y") ? "selected" : ""; ?>><?php echo $year; ?></option>
					<?php }; ?>
				</select>
				<b>Period:</b>
				<select size="1" name="period" id="searchby" onchange="check_span(this.value)">
					<option value="month">Month</option>
					<option value="quarter">Quarter</option>
					<option value="year">Entire year</option>
					<option value="date">Date</option>
				</select>
				<span id='search_month'>
					<select name="month">
						<?php for ($month = 1; $month <= 12; $month++) { ?>
							<option value="<?php echo $month; ?>" <?php echo ($month == date("m")) ? 'selected' : ''; ?>><?php echo $month; ?> - <?php echo date('F', mktime(0, 0, 0, $month, 10)); ?></option>
						<?php }; ?>
					</select>
				</span>
				<span id='search_quarter' style="display:none">
					<select name="quarter">
						<?php for ($quarter = 1; $quarter <= 4; $quarter++) { ?>
							<option value="<?php echo $quarter; ?>" <?php echo ($quarter == ceil(date("m") / 3)) ? 'selected' : ''; ?>><?php echo $quarter; ?></option>
						<?php }; ?>
					</select>
				</span>
				<span id='search_date' style="display:none">
					From date:<input type="text" name="date_from" id="date_from" size=10 class="date" />
					To date:<input type="text" name="date_to" id="date_to" size=10 class="date" />
				</span>
				<input type="submit" name="get_invoices" value="Get invoices" style="width: 120px" onclick="jQuery('#seach_form_act').val('search')">
				<div style="text-align:center;width:100%" class="sub_title">
					<b>Export to:</b> <label><input type="radio" name="exportTo" value="excel" checked="checked" />Excel file</label> <label><input type="radio" name="exportTo" value="zip" />Zip file</label>
					<input type="submit" name="export_invoices" id="export_invoices" value="Export" onclick="jQuery('#seach_form_act').val('export')" style="display:none">
				</div>
			</td>
	</form>
	<td>
	</td>
	</tr>
</table>
<script>
	jQuery("select").change(function(e) {
		jQuery("#export_invoices").css("display", "none")
	});
</script>