<?php
if (!defined("_HQC_")) {
	exit();
}; ?>
<style>
	/* Invoice Page Header */
.invoice-page-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.invoice-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.invoice-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.invoice-header-info {
    flex: 1;
    min-width: 200px;
}

.invoice-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.invoice-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Filter Badge */
.filter-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-badge.all { background: #f1f5f9; color: #475569; }
.filter-badge.paid { background: #dcfce7; color: #166534; }
.filter-badge.unpaid { background: #fef3c7; color: #92400e; }
.filter-badge.overdue { background: #fef2f2; color: #dc2626; }
.filter-badge.credit { background: #fce7f3; color: #be185d; }
.filter-badge.draft { background: #e0e7ff; color: #4338ca; }
.filter-badge.scheduled { background: #f0f9ff; color: #0369a1; }
.filter-badge.annual { background: #f0fdf4; color: #166534; }
.filter-badge.batch { background: #fefce8; color: #a16207; }

.filter-badge i {
    font-size: 10px;
}

/* Quick Stats */
.invoice-quick-stats {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.quick-stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 12px 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    min-width: 100px;
}

.quick-stat-item .stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.quick-stat-item .stat-label {
    font-size: 11px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 4px;
}

.quick-stat-item.paid .stat-value { color: #16a34a; }
.quick-stat-item.unpaid .stat-value { color: #f59e0b; }
.quick-stat-item.overdue .stat-value { color: #dc2626; }

/* Action Buttons */
.invoice-header-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-invoice-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.btn-invoice-action.primary {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.btn-invoice-action.primary:hover {
    background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
    color: #ffffff;
    text-decoration: none;
}

.btn-invoice-action.secondary {
    background: #ffffff;
    color: #4f46e5;
    border: 2px solid #e0e7ff;
}

.btn-invoice-action.secondary:hover {
    background: #f5f3ff;
    border-color: #c7d2fe;
}

/* Search/Filter Bar */
.invoice-filter-bar {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 32px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    flex-wrap: wrap;
}

.invoice-filter-bar .filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.invoice-filter-bar label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    white-space: nowrap;
}

.invoice-filter-bar select,
.invoice-filter-bar input[type="text"] {
    padding: 10px 14px;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #ffffff;
    color: #1e293b;
    transition: all 0.25s ease;
}

.invoice-filter-bar select:focus,
.invoice-filter-bar input[type="text"]:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
}

.invoice-filter-bar .btn-search {
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 600;
    color: #ffffff;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.25s ease;
}

.invoice-filter-bar .btn-search:hover {
    background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
}

/* Totals Page Header */
.totals-header {
    background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
    border-radius: 12px;
    border: 1px solid #e0e7ff;
    margin-bottom: 24px;
    padding: 32px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.totals-header-content {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.totals-header .header-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 26px;
}

.totals-header .header-text h2 {
    margin: 0 0 4px 0;
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
}

.totals-header .header-text p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Totals Cards */
.totals-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin: 24px 0;
}

.total-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 20px;
    transition: all 0.25s ease;
}

.total-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.total-card .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.total-card .card-title {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.total-card .card-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.total-card.paid .card-icon { background: #dcfce7; color: #16a34a; }
.total-card.unpaid .card-icon { background: #fef3c7; color: #f59e0b; }
.total-card.total .card-icon { background: #e0e7ff; color: #4f46e5; }
.total-card.overdue .card-icon { background: #fef2f2; color: #dc2626; }

.total-card .card-amount {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
}

.total-card.overdue .card-amount { color: #dc2626; }

.total-card .card-count {
    font-size: 13px;
    color: #64748b;
}

.total-card .card-breakdown {
    display: flex;
    gap: 16px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
    font-size: 12px;
    color: #64748b;
}

/* Responsive */
@media (max-width: 768px) {
    .invoice-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .invoice-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .invoice-quick-stats {
        justify-content: center;
    }
    
    .invoice-header-actions {
        justify-content: center;
        width: 100%;
    }
    
    .invoice-filter-bar {
        flex-direction: column;
        align-items: stretch;
        padding: 16px 20px;
    }
    
    .invoice-filter-bar .filter-group {
        width: 100%;
    }
    
    .invoice-filter-bar select,
    .invoice-filter-bar input[type="text"] {
        flex: 1;
    }
    
    .totals-header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .totals-cards {
        grid-template-columns: 1fr;
    }
}

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
if ($_SESSION['user_type'] == "admin") {

	if ($_SESSION['user_type'] == "admin")
		$invoice_actions = true;

	if ($_GET['show'] == 'totals') {
		if ($_SESSION['user_type'] == "admin") {

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
			<table border="0" cellpadding="0" style="border:1px solid #EEE" class="table table-striped table-bordered">
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
			<table style="width:auto" class="table table-striped table-bordered">
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

 
	<?php
// Define filter info for badges
$filter_info = [
    'all' => ['icon' => 'fa-layer-group', 'class' => 'all', 'desc' => 'View all invoices in the system'],
    'paid' => ['icon' => 'fa-check-circle', 'class' => 'paid', 'desc' => 'Invoices that have been paid'],
    'unpaid' => ['icon' => 'fa-clock', 'class' => 'unpaid', 'desc' => 'Invoices awaiting payment'],
    'overdue' => ['icon' => 'fa-exclamation-circle', 'class' => 'overdue', 'desc' => 'Invoices past their due date'],
    'credit' => ['icon' => 'fa-minus-circle', 'class' => 'credit', 'desc' => 'Credit notes issued'],
    'credited' => ['icon' => 'fa-exchange-alt', 'class' => 'credit', 'desc' => 'Invoices with credit applied'],
    'recurring' => ['icon' => 'fa-sync', 'class' => 'scheduled', 'desc' => 'Monthly recurring invoices'],
    'scheduled' => ['icon' => 'fa-calendar-alt', 'class' => 'scheduled', 'desc' => 'Scheduled future invoices'],
    'annual' => ['icon' => 'fa-certificate', 'class' => 'annual', 'desc' => 'Annual certificate invoices'],
    'batch' => ['icon' => 'fa-shipping-fast', 'class' => 'batch', 'desc' => 'Shipment certificate invoices'],
    'audit' => ['icon' => 'fa-clipboard-check', 'class' => 'all', 'desc' => 'Audit service invoices'],
    'general' => ['icon' => 'fa-file-invoice', 'class' => 'all', 'desc' => 'General service invoices'],
    'draft' => ['icon' => 'fa-pencil-alt', 'class' => 'draft', 'desc' => 'Draft invoices not yet sent'],
    'supervision' => ['icon' => 'fa-eye', 'class' => 'all', 'desc' => 'Halal supervision invoices'],
    'credit_note' => ['icon' => 'fa-minus-circle', 'class' => 'credit', 'desc' => 'Credit notes']
];

$currentFilter = isset($_GET['show']) ? $_GET['show'] : 'all';
$filterData = isset($filter_info[$currentFilter]) ? $filter_info[$currentFilter] : $filter_info['all'];
?>

<div class="invoice-page-header">
    <div class="invoice-header-content">
        <div class="invoice-header-icon">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        
        <div class="invoice-header-info">
            <h2>
                Invoices
                <span class="filter-badge <?php echo $filterData['class']; ?>" id="currentFilterBadge">
                    <i class="fas <?php echo $filterData['icon']; ?>"></i>
                    <?php echo $service_types[$currentFilter]; ?>
                </span>
            </h2>
            <p id="filterDescription"><?php echo $filterData['desc']; ?></p>
        </div>
        
        <div class="invoice-header-actions">
             
        </div>
    </div>
    
    
</div>

<?php if ($_GET['show'] != 'draft' && $_GET['show'] != 'scheduled') { ?>
    <div style="background:#fefce8; border:1px solid #fde68a; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-size:13px; color:#92400e;">
        <i class="fas fa-info-circle" style="margin-right:8px;"></i>
        To create multiple credit-notes: select a client and subsidiary, load invoices, then select invoices using checkboxes.
    </div>
<?php }; ?>

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
			url = "/iidc/invoices/index.php?inc=create_invoice&type=credit_note&clid=" + clid + "&invnr=" + invNrs + "&goback=invoices&show=<?php echo $_GET['show']; ?>";
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
		
	<?php }; ?>
	<table id="invoicesTable" class="table table-striped table-bordered" style="background:#fff;width:100%">
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
						<?php if ($_SESSION['user_type'] == "admin") { ?>
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