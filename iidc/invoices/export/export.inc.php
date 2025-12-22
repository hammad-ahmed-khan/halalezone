<?php if (!defined("_HQC_")) {
	exit();
}; ?>
<script>
	$("#page_title").html("Export Invoices")
</script>
<?php
if (in_array("menu_invoices", $user_permissions) or $_SESSION['user_type'] == "admin") {
?>
	<style>
		tr.firstHead td {
			background: #eee;
			font-weight: bold;
			text-align: center;
			text-transform: uppercase
		}
	</style>
	<h2 style="text-transform:uppercase;">Export invoices</h2>
	<div>
		<?php include "search_engine.inc.php"; ?>
	</div>
	<script>
		$(document).ready(function(e) {
			$("#dateDialog").append('<div style="margin-top:5px;"><b>Remarks:</b> <input type="text" style="width:160px" id="remarks" value=""/></div>')
		});

		var itemNr
		var clid;
		var st = -1;
		var orderBy = 'inserted_on';
		var ascDsc = 'ASC';

		function getdate(nr, id, invNr) {
			itemNr = nr;
			clid = clid;
			showDateDialog("Invoice nr: " + invNr, 360);
		}

		function getDateData(dt) {
			rem = $("#remarks").val();
			jQuery.post('invoice_save.php', {
				act: 'paidOn',
				paid_on: dt,
				nr: itemNr,
				remarks: rem
			}).done(function(data) {
				if (data.trim().length > 0) {
					if (data.indexOf("error:") > -1) {
						alert(data.replace('error:', ''));
					} else {
						jQuery("#inv_" + itemNr).remove();
						jQuery(".invItem").each(function(index, element) {
							jQuery(this).html(index + 1);
						});
					}
				}
			});
		}

		function clientClik() {
			jQuery(".clientInvoice").click(function(index, element) {
				st = 0;
				jQuery("#loadMoreInvoicesBtn").css("display", "none")
				jQuery("#invoiceItems").html('')
				clid = jQuery(this).attr("data-id");
				loadInvoices(clid)
			});
		}
	</script>
	</p>
	<p>
	<table id="table3" class="alternateOn" style="background:#fff;min-width:1000px;width:100%">
		<thead>
			<tr class="firstHead">
				<td></td>
				<td colspan="2">Company</td>
				<td colspan="6">Invoice</td>
			</tr>
			<tr id="headerTh">
				<th>Nr</th>
				<th>Company ID </th>
				<th>Company Name</th>
				<th>Service type</th>
				<th>Invoice Number</th>
				<th>Date</th>
				<th>Subtotal</th>
				<th>VAT</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody id="invoiceItems">
			<tr id="invoiceItemsLoading">
				<td colspan="9" style="text-align:center;vertical-align:middle;"> </td>
			</tr>
		</tbody>
	</table>

	<script>
		loadInvoices();
	</script>
<?php }; ?>