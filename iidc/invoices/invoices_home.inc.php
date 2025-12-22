<?php
if (!defined("_HQC_")) {
	exit();
}; ?>
<style>
	.remarks {
		position: relative;
		background: lightcyan;
		border: 1px solid darkgrey;
		min-height: 20px;
		padding: 10px;
		margin: 10px -150px 10px 0px;
	}

	i.fas.fa-paperclip {
		color: darkorange !important;
		cursor: pointer;
	}


	.remarks i.fa.fa-trash-alt {
		right: 5px !important;
		bottom: 5px;
		position: absolute;
		color: red;
		font-size: 12px !important;
		cursor: pointer;
		z-index: 1;
	}

	.adminUser {
		text-align: center;
	}

	.adminUser i {
		cursor: default;
		font-size: 14px !important;
	}

	.adminUser span {
		border-radius: 10px !important;
		width: 17px;
		height: 17px;
		display: block;
		color: white;
		margin: 0 auto;
		cursor: default;
		text-transform: capitalize;
	}

	span.office {
		background: brown;
	}

	span.totals {
		position: absolute;
	}

	i.editInvoice {
		margin-top: 10px;
		color: red;
		display: none;
	}

	tr:hover i.editInvoice {
		color: red;
		/* display: inline-block; */
	}
</style>
<script>
	$("#page_title").html("Invoices");
	async function deleteMemo(nr) {
		await confirm_message('Remove memo?');
		jQuery.post('/invoices/memo_save.php', {
			act: 'delete_memo',
			nr: nr
		}).done(function(data) {
			jQuery("#memo_" + nr).remove();
		})
	}
</script>
<?php
if (isset($user_options['invoices_view']) or in_array("menu_invoices", $user_permissions) or $_SESSION['user_type'] == "admin") {

	if ((isset($user_options) and isset($user_options['invoices_create'])) or (in_array("invoices_actions", $user_permissions) or $_SESSION['user_type'] == "admin"))
		$invoice_actions = true;

	if ($_GET['show'] == 'totals') {
		if (in_array("invoices_totals", $user_permissions) or $_SESSION['user_type'] == "admin") {

			$totals = $amdb->query("SELECT SUM(subtotal) AS amount,COUNT(clid) AS invoices FROM `invoices` WHERE 1 AND template='nl'");
			$totals = $totals[0];
			$paid = $amdb->query("SELECT SUM(subtotal) AS amount,COUNT(clid) AS invoices FROM `invoices` WHERE 1 AND template='nl' AND paid_on!=''");
			$paid = $paid[0];
			$over_due = $amdb->query("SELECT SUM(subtotal) AS amount,COUNT(clid) AS invoices FROM `invoices`
							WHERE 1 AND template='nl'
							AND paid_on=''
							AND inserted_on < NOW() - INTERVAL 21 DAY");
			$over_due = $over_due[0];


			$nlInvoices = $totals['invoices'];
			$nl_Invoices['total'] = $totals['invoices'];
			$nl_Invoices['total_amount'] = $totals['amount'];
			$nl_Invoices['vat_total_amount'] = ($totals['amount'] * 0.21);

			$nl_Invoices['paid'] = $paid['invoices'];
			$nl_Invoices['paid_amount'] = $paid['amount'];
			$nl_Invoices['vat_paid_amount'] = ($paid['amount'] * 0.21);

			$nl_Invoices['unpaid'] = ($totals['invoices'] - $paid['invoices']);
			$nl_Invoices['unpaid_amount'] = ($totals['amount'] - $paid['amount']);
			$nl_Invoices['vat_unpaid_amount'] = ($nl_Invoices['unpaid_amount'] * 0.21);

			$nl_Invoices['over_due'] = $over_due['invoices'];
			$nl_Invoices['over_due_amount'] = $over_due['amount'];
			$nl_Invoices['vat_over_due_amount'] = ($over_due['amount'] * 0.21);

?>
			<script>
				$("#page_title").html("Invoices")
			</script>
			<style>
				table {
					width: 60%
				}

				table tr td {
					border-bottom: 1px solid #eee;
				}
			</style>
			<h2 style="text-align:center;text-transform:uppercase">invoices - Totals</h2>
			<table border="0" cellpadding="0" style="border:1px solid #EEE" class="alternateOn">
				<tr>
					<td class="sub_title" colspan="5"> <strong>Payments</strong></td>
				</tr>
				<tr>
					<th></th>
					<th>Subtotal</th>
					<th>VAT</th>
					<th>Amount</th>
					<th>Invoices</th>
				</tr>
				<tr>
					<th>Paid</th>
					<td>&euro;<?php echo number_format($nl_Invoices['paid_amount'], 2, ',', '.') ?></td>
					<td>&euro;<?php echo number_format($nl_Invoices['vat_paid_amount'], 2, ',', '.') ?></td>
					<td>&euro;<?php echo number_format(($nl_Invoices['paid_amount'] + $nl_Invoices['vat_paid_amount']), 2, ',', '.') ?></td>
					<td><?php echo number_format($nl_Invoices['paid'], 0, ',', '.'); ?></td>
				</tr>
				<tr>
					<th>Unpaid</th>
					<td>&euro;<?php echo number_format($nl_Invoices['unpaid_amount'], 2, ',', '.') ?></td>
					<td>&euro;<?php echo number_format($nl_Invoices['vat_unpaid_amount'], 2, ',', '.') ?></td>
					<td>&euro;<?php echo number_format(($nl_Invoices['unpaid_amount'] + $nl_Invoices['vat_unpaid_amount']), 2, ',', '.') ?></td>
					<td><?php echo number_format($nl_Invoices['unpaid'], 0, ',', '.') ?></td>
				</tr>
				</tr>
				<tr>
					<th>Total</th>
					<td style="font-weight:bold">&euro;<?php echo number_format($nl_Invoices['total_amount'], 2, ',', '.') ?></td>
					<td style="font-weight:bold">&euro;<?php echo number_format($nl_Invoices['vat_total_amount'], 2, ',', '.') ?></td>
					<td style="font-weight:bold">&euro;<?php echo number_format(($nl_Invoices['total_amount'] + $nl_Invoices['vat_total_amount']), 2, ',', '.') ?></td>
					<td style="font-weight:bold"><?php echo number_format($nl_Invoices['total'], 0, ',', '.'); ?></td>
				</tr>

				<tr>
					<th style="color:red">Over due</th>
					<td>
						<font color="#FF0000">&euro;<?php echo number_format($nl_Invoices['over_due_amount'], 2) ?></font>
					</td>
					<td>
						<font color="#FF0000">&euro;<?php echo number_format($nl_Invoices['vat_over_due_amount'], 2) ?></font>
					</td>
					<td>
						<font color="#FF0000">&euro;<?php echo number_format(($nl_Invoices['over_due_amount'] + $nl_Invoices['vat_over_due_amount']), 2) ?></font>
					</td>
					<td style="color:red"><?php echo number_format($nl_Invoices['over_due'], 0, ',', '.'); ?></td>
			</table>

			<h3 style="text-align:center;text-transform:uppercase">invoices - Per service type</h3>
			<?php
			$service_types = array('batch' => 'Batch Certificate(s)', 'annual' => 'Annual certificate', 'audit' => 'Audit', 'general' => 'Halal Services', 'credit' => 'Credit note', 'recurring' => 'Monthly', 'scheduled' => 'Scheduled invoices');
			if ($defauls = json_decode(get_option('invoice_defaults'), true)) {
				$service_types = $defauls['service_type'];
			}
			?>
			<table style="width:auto" class="alternateOn">
				<tr>
					<th style="width:200px">Service type</th>
					<th>Total invoices</th>
				</tr>
				<?php
				$invoice_type = $amdb->query("SELECT invoice_type,COUNT(invoice_type) AS types FROM `invoices` WHERE 1 AND template='nl' GROUP BY invoice_type");
				foreach ($invoice_type as $key => $value) {
					if ($value['invoice_type'] == 'batch')
						$value['invoice_type'] = 'a';
				?>
					<?php if (trim($value['invoice_type']) != '' and isset($service_types[$value['invoice_type']])) { ?>
						<tr>
							<th><?php echo $service_types[$value['invoice_type']]; ?>:</th>
							<td><?php echo number_format($value['types'], 0, ',', '.'); ?></td>
						</tr>
					<?php }; ?>
				<?php }; ?>
			</table>

	<?php
		};
		return;
	}
	include "../date-dialog.inc.php";
	$service_types = array(
		"all" => "All Invoices",
		"paid" => "Paid Invoices",
		"unpaid" => "Unpaid Invoices",
		"overdue" => "Over due",
		"credit" => "Credit notes",
		"credited" => "Credited invoices",
		"recurring" => "Recurring Invoices (Monthly)",
		'scheduled' => "Scheduled invoices",
		"annual" => "Annual certificates",
		"batch" => "Shipment certificates",
		"audit" => "Audits",
		"general" => "General invoices",
		"recurring" => "Monthly invoices",
		"supervision" => "Halal supervision",
		"credit_note" => "Credit_note",
		"draft" => "Draft invoices"
	);
	?>
	<style>
		tr.firstHead td {
			background: #eee;
			font-weight: bold;
			text-align: center;
			text-transform: uppercase
		}

		td.status div {
			margin-bottom: 5px
		}

		div#massCredit {
			position: fixed;
			top: 100px;
			background: beige;
			padding: 20px;
			box-shadow: 1px 1px 1px #bbb;
			border: 1px solid #eee;
			z-index: 1000;
			width: 220px;
		}

		div#massCredit:hover {
			text-shadow: none;
		}

		ol#selectedInvoicesOl {
			padding: 0px 0px 0px 20px;
		}

		ol#selectedInvoicesOl li {
			list-style-type: decimal;
			background: beige !important;
		}

		div#massCreditTitle {
			position: absolute;
			top: 0px;
			left: 0px;
			right: 0px;
			background: burlywood;
			color: beige;
			font-weight: normal;
			text-align: left;
			padding: 2px 10px;
		}

		div#massCreditTitle i.far.fa-window-close {
			position: absolute;
			right: 10px;
			color: currentcolor;
			font-weight: 100;
		}

		b.wd65 {
			display: inline-block;
			width: 65px;
		}

		td hr {
			border-top: 1px dashed #bbb;
			color: azure;
		}
	</style>
	<div style="position: absolute; left: -1000; top: -1000;display:none;visibility:hidden">
		<iframe src="<?php echo $prog_www; ?>/top.html" name="invoice_frame" style="width:0px;height:0px"></iframe>
	</div>

	<h2 style="text-transform:uppercase;text-align:center" id="invoiceTypeTitle"><?php echo $service_types[$_GET['show']]; ?></h2>
	<div style="text-align:center !important;width:100%;background:#f0ede8 !important">
		<?php include "search_engine.inc.php"; ?>
	</div>

	<?php if ($_GET['show'] == 'overdue') { ?>
		<script>
			function selectReminderItems(obj) {
				items = [];
				jQuery("#invoiceItems .remindItemInput:checked").each(function(index, element) {
					items.push(jQuery(this).parents('td').data('id'));
				});
				reminderUrl = 'index.php?inc=invoice_bulk_reminder&act=' + jQuery("#bulkReminder").val();
				if (items.length > 0) {
					reminderUrl += '&nrs=' + items.join();
					jQuery("#remindClients").attr("data-url", reminderUrl).css("display", "inline-block");
				} else {
					jQuery("#remindClients").css("display", "none");
				}
			}

			function getReminders(val) {
				jQuery("#remindClients").css("display", "none");
				jQuery("#invoiceItems .remindItemInput").remove();
				if (val != 'all') {
					jQuery("#remindClients").val("Send reminder(s)");
					jQuery("#remindClients").attr("title", "Send reminder(s)");
					jQuery("#invoiceItems tr").css({
						"visibility": "hidden",
						"position": "fixed",
						"top": "-5000px"
					})
					jQuery("." + val + "_remind").parents("tr").css({
						"visibility": "visible",
						"position": "relative"
					})
					jQuery("." + val + "_remind").after('<input type="checkbox" class="remindItemInput" onclick="selectReminderItems()"/>')
				} else {
					jQuery("#invoiceItems tr").css({
						"visibility": "visible",
						"position": "relative"
					})
				}
			}
		</script>

		<div style="background:#eee;padding:5px;text-align:right">
			<form id="bulkRemindersForm">
				<b>Bulk reminders:</b>
				<select size="1" id="bulkReminder" onchange="getReminders(this.value)">
					<option value="all">Show all invoices</option>
					<option value="first">First time reminder</option>
					<option value="final">Final reminder</option>
					<option value="suspend">Suspend account reminder</option>
				</select>
				<input type="button" id="remindClients" data-url="" style="display:none" value="" class="iframe" data-width="890" data-height="580" title="" />
			</form>
		</div>
	<?php }; ?>
	<script>
		$(document).ready(function(e) {
			$("#dateDialog").append('<div style="margin-top:5px;"><b>Remarks:</b> <input type="text" style="width:160px" id="remarks" value=""/></div>')
		});

		var itemNr
		var clid;
		var st = -1;
		var orderBy = 'inserted_on';
		var ascDsc = 'DESC';

		function getdate(nr, id, invNr) {
			itemNr = nr;
			clid = clid;
			showDateDialog("Invoice nr: " + invNr, 360);
		}

		async function undoPayment(nr, invNr) {
			await confirm_message('Are you sure you want to undo payment<br/> for the invoice <b style="color:red;">' + invNr + '</b>...?');

			jQuery.post('invoice_save.php', {
				act: 'undoPayment',
				nr: nr,
				invNr: invNr
			}).done(function(data) {
				if (data.trim().length > 0) {
					if (data.indexOf("error:") > -1) {
						alert(data.replace('error:', ''));
					} else {
						location.reload();
					}
				}
			});
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
						paid = '<i class="far fa-calendar-check" title="paid" style="color:green"></i><span style="color:green">' + dt + '</span>'
						<?php if ($_GET['show'] == 'all') { ?>
							jQuery("#inv_" + itemNr).find('.status').html(paid);
							jQuery("#inv_" + itemNr).find('.action').html('<img src="../images/thumbs-up.svg" class="svg"/>');
						<?php } else { ?>
							jQuery("#inv_" + itemNr).remove();
							jQuery(".invItem").each(function(index, element) {
								jQuery(this).html(index + 1);
							});
						<?php }; ?>
					}
				}
			});
		}

		function doMassCredit() {
			clid = jQuery("select#clid").val();
			invNrs = jQuery("#invoicesToBeCredited").val();
			url = "/invoices/index.php?inc=create_invoice&type=credit_note&clid=" + clid + "&invnr=" + invNrs + "&goback=invoices&show=<?php echo $_GET['show']; ?>";
			document.location = url;
		}

		function closeMassCredit() {
			jQuery("div#massCredit").css("display", "none");
			jQuery("table#invoicesTable tr").css("background-color", "");
			jQuery("input.massCredit").prop("checked", "")
		}

		function massCredit() {
			jQuery("input.massCredit").on("click", function() {
				invoices = [];
				$invoicesList = '';
				selected = jQuery("input.massCredit:checked").length;
				if (selected > 0) {
					jQuery("div#massCredit").css("display", "block");
					jQuery("span#selectedInvoices").html(selected);
				} else {
					jQuery("div#massCredit").css("display", "none");
				}
				jQuery("table#invoicesTable tr").css("background-color", "")
				jQuery("input.massCredit:checked").each(function() {
					jQuery(this).parents("tr").css("background-color", "bisque")
					invoices.push(jQuery(this).val());
					$invoicesList += '<li>' + jQuery("td#url_" + jQuery(this).val()).html().trim() + '</li>';
				})
				jQuery("ol#selectedInvoicesOl").html($invoicesList);
				jQuery("#invoicesToBeCredited").val(invoices.join(','));
			})

		}

		function loadInvoices() {
			if (jQuery("#searchFor").val() == 'invoice_number' && jQuery("#invoice_number").val() == '') {
				alert_message('Invoice number is required');
				return false;
			}
			loading = '<tr id="invoiceItemsLoading"><td colspan="12" style="text-align:center;vertical-align:middle;"><img src="../images/loading.gif" style="height:50px;"/></td></tr>';
			jQuery("#invoiceItems").html(loading);
			jQuery.post('load_invoices.php', jQuery("#seach_form").serialize()).done(function(data) {
				if (data.trim().length > 0) {
					if (data.indexOf("error:") > -1) {
						jQuery("#invoiceItemsLoading").remove();
						alert_message(data.replace('error:', ''));
					} else {
						if (jQuery("#invoiceItemsLoading")) {
							jQuery("#invoiceItemsLoading").remove();
						}
						jQuery("#invoiceItems").html(data);
						post_links();
						load_popup();
						massCredit();
						do_document_ready();
						//get selected option text using jquery
						var selectedText = jQuery("#invoice_type option:selected").text();
						if (selectedText != 'All Invoices') {
							jQuery("#invoiceTypeTitle").html('<?php echo $_GET['show']; ?> - ' + selectedText)
							if (jQuery("#invoiceTypeTitle").html() == 'all - All invoices')
								jQuery("#invoiceTypeTitle").html('All invoices')
						} else {
							jQuery("#invoiceTypeTitle").html(selectedText)
						}
						//	jQuery("#invoiceTypeTitle").html(selectedText)
					}
				}
			});
			return false;
		}

		function removeResent(id) {
			jQuery(id).remove();
		}

		function editInvoice(obj) {
			var invNr = jQuery(obj).closest('tr').attr('id').replace('inv_', '');
			window.location.href = 'index.php?inc=create_invoice&act=edit&nr=' + invNr;

		}
	</script>
	<?php if ($_GET['show'] != 'draft' && $_GET['show'] != 'scheduled') { ?>
		<div>To make multiple credit-notes select a client, a subsidiary and get invoices. Then select invoice by checkboxes</div>
	<?php }; ?>
	<table id="invoicesTable" class="alternateOn" style="background:#fff;width:100%">
		<thead>
			<tr class="firstHead">
				<td></td>
				<td>Company</td>
				<td colspan="<?php echo is_mobile() ? 4 : 6; ?>">Invoice</td>
				<?php if (isset($invoice_actions)) { ?>
					<td colspan="2">
						<div id="massCredit" style="display:none">
							<div id="massCreditTitle"> selected invoices: <span id="selectedInvoices" style="text-decoration: none;"></span>
								<i class="far fa-window-close" onclick="closeMassCredit()"></i>
							</div>
							<ol id="selectedInvoicesOl"></ol>
							<input type="hidden" id="invoicesToBeCredited" />
							<center><input type="button" onclick="doMassCredit()" value="Credit select invoices" /></center>
						</div><?php echo ($_GET['show'] != 'credit') ? 'Payment' : ''; ?>
					</td><?php echo ($_GET['show'] != 'credit' && isset($invoice_actions)) ? '<td colspan="3"></td>' : ''; ?>
				<?php }; ?>
			</tr>
			<tr id="headerTh">
				<th>Nr</th>
				<th data-id="company">Company Name</th>
				<th data-id="invoice_nr">Number</th>
				<th data-id="ymd">Date</th>
				<?php if (is_mobile()) { ?>
					<th>Amount</th>
				<?php } else { ?>
					<th>Subtotal</th>
					<th>VAT</th>
					<th>Total</th>
				<?php }; ?>
				<th>Service type</th>
				<?php if (isset($invoice_actions)) { ?>
					<?php if ($_GET['show'] != 'credit') { ?>
						<th>Status</th>
						<?php /*<th><img src="/images/exact.svg" width="30" /></th>*/?>
						<?php if ((isset($user_options) and isset($user_options['invoices_create'])) or in_array("invoices_actions", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
							<th style="width:<?php echo ($_GET['show'] != 'draft' or $_GET['show'] != 'scheduled') ? '80px' : '180px'; ?>px !important">Action</th>
							<th style="text-align:center"><i class="far fa-user" style="font-size:14px !important"></i></th>
							<th style="width:20px !important">::</th>
					<?php };
					}; ?>
				<?php }; ?>
			</tr>
		</thead>
		<tbody id="invoiceItems">
		</tbody>
	</table>
	<div style="max-width:1000px;margin:0 auto">*AS: Account status</div>

	<?php if ($_GET['show'] != 'all') {/*?>
<center><input type="button" onclick="loadInvoices()" id="loadMoreInvoicesBtn" value="Load more invoices"/></center>
<?php */
	} ?>
	<script>
		$("#headerTh th").each(function(index, element) {
			if (jQuery(this).data('id')) {
				jQuery(this).append('<i class="fa fa-caret-down"></i><i class="fa fa-caret-up"></i>');
				jQuery(this).css('white-space', 'nowrap')
			}
		});

		$("#headerTh th i.fa").click(function(e) {
			$("#headerTh th i.fa").css('color', 'black');
			$(this).css('color', 'red');
			jQuery("#orderBy").val(jQuery(this).parent('th').data('id'))
			if (jQuery(this).hasClass('fa-caret-up'))
				jQuery("#ascDsc").val('ASC');
			else
				jQuery("#ascDsc").val('DESC');
			st = 0;
			loadInvoices();
		});

		loadInvoices();

		$("#unpaidInv").attr("colspan", $("#headerTh > th").length)
	</script>
<?php }; ?>