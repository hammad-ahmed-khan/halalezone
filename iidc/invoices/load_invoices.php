<?php
//show errors
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

$user_options = get_office_options()['options'];
if ((isset($user_options) and isset($user_options['invoices_create'])) or (in_array("invoices_actions", $user_permissions) or $_SESSION['user_type'] == "admin"))
	$invoice_actions = true;

$reminders = array();
if ($clients_reminders = $amdb->get_results("SELECT * FROM hqc_default_invoice_reminders")) {
	foreach ($clients_reminders as $clients_reminder) {
		$reminders[$clients_reminder['clid']] = $clients_reminder['first_reminder'];
	}
}

function next_reminder($date, $clid)
{
	global $reminders;
	if (isset($reminders[$clid])) {
		$reminder = $reminders[$clid];
	} else {
		$reminder = 21;
	}
	if (is_array($date) and count($date) > 0) {
		if (isset($date[1]))
			$date = $date[1];
		else
			$date = $date[0];
		$date = fix_date($date) . ' 23:59:59';
	}
	if (is_array($date))
		return;
	$diff = time() - strtotime($date);
	return ($reminder - (round($diff / 86400)));
}
$service_types = array('batch' => 'Batch Certificate(s)', 'annual' => 'Annual certificate', 'audit' => 'Audit', 'general' => 'Halal Services', 'credit_note' => 'Credit note', 'recurring' => 'Monthly', 'expenses' => 'Expenses', 'supervision' => 'Halal supervision');
if ($defauls = json_decode(get_option('invoice_defaults'), true)) {
	$service_types = $defauls['service_type'];
	$service_types['recurring'] = 'Monthly';
}

//make an list of 50 default background dark colors using color names
$colors = array('gray', 'red', 'green', 'blue', 'darkorange', 'brown', 'cyan', 'magenta', 'lime', 'indigo', 'teal', 'violet', 'fuchsia', 'aqua', 'maroon', 'navy', 'olive', 'silver', 'gray', 'black', 'white', 'red', 'green', 'blue', 'yellow', 'purple', 'orange', 'pink', 'brown', 'cyan', 'magenta', 'lime', 'indigo', 'teal', 'violet', 'fuchsia', 'aqua', 'maroon', 'navy', 'olive', 'black', 'green', 'brown');

$admin_users = array();
if ($users = $amdb->get_results("SELECT hqc_admin_users.username_owner,hqc_admin_users.uid FROM hqc_admin_users JOIN invoices ON invoices.uid = hqc_admin_users.uid GROUP BY invoices.uid")) {
	foreach ($users as $user) {
		$admin_users[$user['uid']] = $user['username_owner'];
	}
}

$whr = '';
$credit = "AND invoices.invoice_type != 'credit_note' AND invoices.status != 'credited'";
if ($_POST['year'] != 'all' && $_POST['searchFor'] == 'client')
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
	$whr .= "AND invoices.status = 'credited'";
elseif ($_POST['show'] == 'recurring')
	$whr = "AND invoices.invoice_type = 'recurring'";
elseif ($_POST['show'] == 'draft')
	$whr .= "AND invoices.invoice_nr = 'draft'";
elseif ($_POST['show'] == 'scheduled')
	$whr = "AND invoices.invoice_nr = 'scheduled'";
elseif ($_POST['show'] == 'transferpricing')
	$whr = "AND invoices.service_type LIKE '%transferpricing%'";
if ($_POST['show'] != 'draft' && $_POST['show'] != 'scheduled')
	$whr .= "AND invoices.invoice_nr != 'draft' AND invoices.invoice_nr != 'scheduled'";
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
if ($_POST['searchFor'] == 'date') {
	$date_from = fix_date($_POST['date_from']) . " 00:00:00";
	$date_to = fix_date($_POST['date_to']) . " 23:59:59";
	$whr .= "AND invoices.inserted_on BETWEEN '$date_from' AND '$date_to'";
}
if ($_POST['searchFor'] == 'invoice_number') {
	if ($_POST['searchFor'] == 'invoice_number') {
		$_POST['invoice_number'] = trim($_POST['invoice_number']);
		$whr = "AND invoices.invoice_nr LIKE '%$_POST[invoice_number]%'";
	}
}
if (isset($_POST['invoice_type']) && $_POST['invoice_type'] != 'all')
	$whr .= " AND invoices.invoice_type = '$_POST[invoice_type]'";
$whr .= " AND invoices.status != 'hidden'";
if (isset($_POST['item_code']) and trim($_POST['item_code']) != '') {
	$whr .= " AND json_valid(invoice_items)=1 AND `invoice_items` LIKE '%$_POST[item_code]%'";
}
if (isset($_POST['invoice_items']) and trim($_POST['invoice_items']) != '') {
	$whr .= " AND json_valid(invoice_items)=1 AND `invoice_items` LIKE '%$_POST[invoice_items]%'";
}
$IDPrefix = array();
$office_users = array();
if ($offices = $amdb->get_results("SELECT clients,reference_prefix,certificate_prefix,offid,contact_person,company_name_english FROM offices ")) {
	foreach ($offices as $office) {
		$IDPrefix[$office['offid']] = $office['reference_prefix'] . $office['certificate_prefix'];
		$office_users[$office['offid']] = trim(str_replace(array('Mr.', 'Dr.', 'Mrs.', 'Eng.'), '', $office['contact_person'])) . ' - ' . $office['company_name_english'];
		if (isset($_SESSION['offid']) && $_SESSION['offid'] != 0 && $office['offid'] == $_SESSION['offid']) {
			$whr .= "AND FIND_IN_SET(invoices.clid,'$office[clients]')";
		}
	}
};
$sql = "SELECT nr,invoices.sbsid,invoices.clid,bclid,invoice_nr,invoice_type,service_type,invoice_items,invoices.invoice_data,subtotal,vat,total,paid_on,inserted_on,seen_at,reminded_on,resent_on,credit_invnr,invoices.invoice_options,invoices.status,invoices.internal,invoices.intID,invoices.uid,invoices.memo,companies.company_name,companies.active,companies.offid,invoices.offid AS off_id,invoices.sys_error,invoices.mail_error,invoices.exacted FROM `invoices`
				JOIN companies ON companies.clid = invoices.clid
				WHERE 1 AND invoices.template='nl' $whr ORDER BY TRIM(invoices.{$_REQUEST['orderBy']}) {$_REQUEST['ascDsc']}";

$nr = $st;
$subtotal = 0;
$vat = 0;
$total = 0;
$invoices_count = 0;
$subtotal_paid = 0;
$vat_paid = 0;
$total_paid = 0;
$paid_count = 0;
$subtotal_credit = 0;
$vat_credit = 0;
$total_credit = 0;
$credit_count = 0;
$creditedYear = array();
$invoice = array();
// echo $sql;
// exit();
$credited = array();
$invoices_to_credit = array();
if (!$invoices = $amdb->get_results($sql)) {

?>
	<tr>
		<td colspan="12" style="color:red;text-align:center">No invoice found</td>
	</tr>
	<?php
	return;
}

//get credited invoices
if ($_POST['year'] != 'all') {
	foreach ($invoices as $invoiceKey => $invoice) {
		if ($invoice['invoice_type'] != 'credit_note') {
			$invoices_to_credit[$invoice['nr']] = $invoice['invoice_nr'];
		}
	}
	$creditWhr = "AND YEAR(invoices.inserted_on) = '{$_POST['year']}' ";

	if ($credits = $amdb->get_results("SELECT nr,invoice_nr,invoice_items,credit_invnr,exacted FROM invoices WHERE invoice_type = 'credit_note' $creditWhr")) {
		foreach ($credits as $credit) {
			if (trim($credit['credit_invnr']) != '' && !in_array($credit['credit_invnr'], $invoices_to_credit)) {
				$creditedYear[] = $credit['nr'];
			} else {
				if (is_array(json_decode($credit['invoice_items'], true))) {
					$description = array_values(json_decode($credit['invoice_items'], true))[0]['description'];
					if (strstr($description, 'Date:') && !strstr($description, $_POST['year'])) {
						$creditedYear[] = $credit['nr'];
					} else {
						//find invoice number starts with HQC- or SAE- in the description using regex
						if (preg_match('/(HQC-|SAE-|HBA-)(\d+)/', $description, $matches)) {
							$invoiceNumber = $matches[0];
							if (!in_array($invoiceNumber, $invoices_to_credit))
								$creditedYear[] = $credit['nr'];
						}
					}
				}
			}
		}
	}
}

ob_start();
if (isset($_REQUEST['orderBy'])) {
	if ($_REQUEST['orderBy'] == 'company')
		$_REQUEST['orderBy'] = 'company_name';
	if ($_REQUEST['orderBy'] == 'ymd')
		$_REQUEST['orderBy'] = 'inserted_on';
	$invoices = order_by($invoices, $_REQUEST['orderBy'], $_REQUEST['ascDsc']);
}

if (isset($_POST['item_code']) and trim($_POST['item_code']) != '') {
	$invoice_code_included = array();
	foreach ($invoices as $invoice) {
		if (is_array(json_decode($invoice['invoice_items'], true))) {
			$invoice_items = json_decode($invoice['invoice_items'], true);
			foreach ($invoice_items as $item) {
				if (isset($item['product']) && strstr(strtolower($item['product']), strtolower($_POST['item_code']))) {
					$invoice_code_included[] = $invoice;
				}
			}
		}
	}
	$invoices = $invoice_code_included;
}

foreach ($invoices as $row) {
	if (isset($reminders[$row['clid']])) {
		$term = $reminders[$row['clid']];
	} else {
		$term = 21;
	}
	if (($invoice['invoice_type'] == 'credit_note' && in_array($row['nr'], $creditedYear)) or ($invoice['invoice_type'] != 'credit_note' and ($row['status'] == 'active' or $row['status'] == 'credited'))) {
		$nr++;
		$invoices_count++;
		if ($row['invoice_type'] == "credit_note") {
			$subtotal_credit = $subtotal_credit + $row['subtotal'];
			$vat_credit = $vat_credit + $row['vat'];
			$total_credit = $total_credit + $row['total'];
			$credit_count = $credit_count + 1;
		} else {
			$subtotal = $subtotal + $row['subtotal'];
			$vat = $vat + $row['vat'];
			$total = $total + $row['total'];
			if (trim($row['paid_on']) != '' or $row['status'] == 'credited') {
				$paid_count++;
				$subtotal_paid = $subtotal_paid + $row['subtotal'];
				$vat_paid = $vat_paid + $row['vat'];
				$total_paid = $total_paid + $row['total'];
			}
		}
		$st =  $row['service_type'];
		if ($row['invoice_type'] == 'batch')
			$row['invoice_type'] = 'a';
		if (trim($row['reminded_on']) != '')
			$row['reminded_on'] = explode(',', $row['reminded_on']);
		else
			$row['reminded_on'] = array();
		$invoice_data = json_decode($row['invoice_data'], true);
	?>
		<tr id="inv_<?php echo $row['nr']; ?>">
			<th class="invItem"><?php echo $nr ?></th>
			<td <?php if ($row['clid'] != 0) { ?> class="load_popup" data-url="../../admin/load_company.php?clid=<?php echo $row['clid']; ?>&ref=invoice" title="<?php echo $row['company_name']; ?>" <?php }; ?>>
				<?php echo ($row['company_name'] != 'internal') ? $row['company_name'] : $subsidiaries[$row['intID']]; ?>
				<?php if ($row['clid'] != 0) { ?>
					<div style="color:#555;margin-top:5px"><b>ID:</b> <?php echo $IDPrefix[$row['offid']] . str_pad($row['clid'], 5, '0', STR_PAD_LEFT); ?></div>
				<?php }; ?>
			</td>
			<td id="url_<?php echo $row['nr']; ?>" style="white-space:nowrap">
				<?php
				$invFile = "/client_data/invoices/$row[invoice_nr].pdf";
				if (file_exists($prog_path . $invFile)) { ?>
					<a href="<?php echo $prog_www; ?>/client_data/invoices/<?php echo $row['invoice_nr']; ?>.pdf" target="_blank"><?php echo $row['invoice_nr']; ?></a>
					<?php } elseif ($row['invoice_nr'] == 'draft') { ?>DRAFT INVOICE<?php } elseif ($row['invoice_nr'] == 'scheduled') { ?>SCHEDULED INVOICE<?php } else { ?>
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
						<a href="pdf/show_invoice.php?nr=<?php echo $row['nr']; ?>&st=<?php echo $st; ?>&tmpl=nl" target="_blank">**<?php echo $row['invoice_nr']; ?>
						<?php }; ?>
					<?php }; ?>
			</td>
			<td style="white-space: nowrap;">
				<?php
				if ($row['invoice_nr'] == 'scheduled') {
				?>
					<b>Will be sent on:</b><?php
											if ($invoice_options = decode_json($row['invoice_options'])) {
												echo $invoice_options['scheduled']['date'] . ' ' . str_pad($invoice_options['scheduled']['hour'], 2, '0', STR_PAD_LEFT) . ':00';
											}
											?>
				<?php
				} else {
					echo date("d/m/Y", strtotime($row['inserted_on']));
				} ?>
			</td>
			<td class="nowrap costs">
				<?php if (is_mobile()) { ?><b class="wd65">Subtotal:</b> <?php } ?>
				<?php
				if ($row['invoice_type'] == "credit_note")
					echo "<font color=red><i>&euro;" . number_format($row['subtotal'], 2, ',', '.') . "</i></font>";
				else
					echo "&euro;" . number_format($row['subtotal'], 2, ',', '.');
				?>
				<?php if (is_mobile()) { ?><br /><b class="wd65">VAT:</b> <?php } else { ?></td>
			<td> <?php }; ?>
			<?php
			if ($row['invoice_type'] == "credit_note")
				echo "<font color=red><i>&euro;" . number_format($row['vat'], 2, ',', '.') . "</i></font>";
			else
				echo "&euro;" . number_format($row['vat'], 2, ',', '.');
			?>
			<?php if (is_mobile()) { ?><br />
				<hr /><b class="wd65">Total:</b> <?php } else { ?>
			</td>
			<td>
			<?php }; ?>
			<?php
			if ($row['invoice_type'] == "credit_note")
				echo "<font color=red><i>&euro;" . number_format($row['total'], 2, ',', '.') . "</i></font>";
			else
				echo "&euro;" . number_format($row['total'], 2, ',', '.');
			?>
			</td>
			<td style="min-width:190px">
				<?php
				if ($row['sbsid'] == '8' && $row['invoice_type'] != 'credit_note')
					echo 'Halal Training Service';
				else
					echo str_replace('[other]', '', $row['service_type']); ?>
				<?php if ($row['invoice_type'] == 'credit_note' && trim($row['credit_invnr']) != '') {
					//TODO: update the new system
					$invoice_files = explode(',', $row['credit_invnr']);
					echo "for: ";
					foreach ($invoice_files as $file) {
						$invFile = "/client_data/invoices/$file.pdf";
						if (file_exists($prog_path . $invFile)) { ?>
							<a href="<?php echo $prog_www; ?>/client_data/invoices/<?php echo $file; ?>.pdf" target="_blank"><?php echo $file; ?></a>
						<?php } else { ?>
							<?php echo $file; ?><?php };
										};
									}; ?>
							<?php if (isset($invoice_data['dhl_waybill']) && trim($invoice_data['dhl_waybill']) != '') { ?>
								<br /><a href="https://www.dhl.com/nl-en/home/tracking/tracking-express.html?submit=1&tracking-id=<?php echo $invoice_data['dhl_waybill']; ?>" target='_blank'><img src="/images/DHL-logo.svg" style="width:60px;" /></a>
							<?php }; ?>
			</td>
			<?php if (isset($invoice_actions)  or $_SESSION['offid'] == 0) { ?>
				<?php if ($_POST['show'] != 'credit') { ?>
					<td style="min-width:160px" id="status_<?php echo $row['nr']; ?>" class="noCursor<?php echo ($row['status'] != 'credited') ? ' status' : '' ?>">
						<?php
						if ($row['invoice_nr'] != 'draft' && $row['invoice_nr'] != 'scheduled') {
							if ($row['seen_at'] != '0000-00-00 00:00:00' or $row['paid_on'] != '')
								echo '<i class="far fa-envelope-open" style="color:#05b505" title="Seen by client"></i>';
							else
								echo '<i class="far fa-envelope" style="color:#bbb" title="Not seen yet"></i>';
						?>
						<?php
						}; ?>
						<?php if ($row['status'] == 'credited') { ?>
							<span style="color:#bbb">Credited</span><br />
							<?php
							$invFile = "/client_data/invoices/{$row['credit_invnr']}.pdf";
							if (file_exists($prog_path . $invFile)) { ?>
								<a href="<?php echo $prog_www; ?>/client_data/invoices/<?php echo $row['credit_invnr']; ?>.pdf" target="_blank"><?php echo $row['credit_invnr']; ?></a>
							<?php }; ?><span style="display:block;height:5px;"></span>
							<?php } else {
							if ($row['invoice_nr'] == 'draft') { ?>
								<i class="far fa-hourglass" style="color:grey"></i> <span style="color: grey;">Pending</span>
							<?php } elseif ($row['invoice_nr'] == 'scheduled') { ?>
								<i class="far fa-clock"></i> <span style="color: grey;">scheduled</span>
							<?php } elseif (trim($row['invoice_type']) == 'credit_note') {
								echo '<span style="color:#bbb">Credit note</span>';
							} elseif (trim($row['paid_on']) != '') { ?>
								<i class="far fa-calendar-times" style="color:#800;cursor:pointer" onclick="undoPayment('<?php echo $row['nr']; ?>','<?php echo $row['invoice_nr']; ?>')" title="Undo-payment"></i>
								<i class="far fa-calendar-check" title="paid" style="color:green"></i><span style="color:green"><?php echo $row['paid_on']; ?></span>
							<?php
							} else { ?>
								<i class="fas fa-paperclip load_popup" id="qr_<?php echo $row['nr']; ?>" data-url="/invoices/memo_save.php?nr=<?php echo $row['nr']; ?>&act=internalMemo" title="Internal memo" style="color:orange"></i>
								<?php
								$diff = time() - strtotime($row['inserted_on']);
								$invoice_due_date = $term - (round($diff / 86400));
								if ($invoice_due_date < 0) {
									echo '<span style="color:#900"><i class="far fa-calendar-minus" style="color:#900" title="Over Due"></i>' . $invoice_due_date . ' Days</span>';
									if (count($row['reminded_on']) > 0) {
										echo '<br/><span><i class="far fa-bell" title="First reminder"></i>' . $row['reminded_on'][0] . '</span>';
									}
									if (count($row['reminded_on']) > 1) {
										$reminder_count = count($row['reminded_on']) + 1;
										echo '<br/><span><i class="far fa-bell" style="color:orange" title="Reminder (' . $reminder_count . ')"></i>' . $row['reminded_on'][1] . '</span>';
									}
								} elseif ($invoice_due_date <= $term) {
									echo '<i class="far fa-calendar-check" title="Due in" style="color:grey"></i><span style="color:grey">' . $invoice_due_date . ' Days</span>';
								}
								?>
								<?php if (trim($row['memo']) != '') { ?>
									<div class="remarks" id="memo_<?php echo $row['nr']; ?>">
										<i class="fa fa-trash-alt" onclick="deleteMemo(<?php echo $row['nr']; ?>)"></i> <span id="memo_<?php echo $row['nr']; ?>"><?php echo str_replace("\n", "<br/>", $row['memo']); ?></span>
									</div>
								<?php }; ?>

						<?php }
						}
						?>
					</td>
					<?php /*<td><?php echo ($row['exacted'] == 'y') ? '<img src="/images/exact.svg" width=30/>' : ''; ?></td>*/ ?>
					<?php if (isset($invoice_actions)) { ?>
						<td align="left" style="padding-left:5px;white-space:nowrap;position:relative" class="action" data-id="<?php echo $row['nr']; ?>">
							<?php if ($row['invoice_nr'] == 'draft' or $row['invoice_nr'] == 'scheduled') { ?>
								<a href="index.php?inc=create_invoice&act=<?php echo $row['invoice_nr']; ?>&nr=<?php echo $row['nr']; ?>&goback=invoices&show=all" title="Create invoice">
									<i class="<?php echo $row['invoice_nr'] == 'scheduled' ? 'far fa-edit' : 'fas fa-file-invoice'; ?>"></i></a>
								<i class="fa fa-trash-alt" title="Delete <?php echo $row['invoice_nr']; ?> invoice" data-id="<?php echo $row['nr']; ?>" data-act="<?php echo $row['invoice_nr'] == 'draft' ? 'deleteDraft' : 'deleteScheduled'; ?>" data-save="invoice_save.php"></i>
							<?php } else { ?>
								<a href="index.php?inc=resend_invoice&nr=<?php echo $row['nr']; ?>&act=rem" target="iframe" data-width="890" data-height="580" id="resend_<?php echo $row['nr']; ?>" title="Resend the INVOICE: (<?php echo $row['invoice_nr']; ?>)"><i class="far fa-paper-plane" <?php echo trim($row['resent_on']) != '' ? 'style="color:#05b505"' : "" ?>></i></a>
								<?php
								if ($_POST['show'] != 'paid' and !isset($credited[$row['invoice_nr']])) {
									if (trim($row['invoice_type']) == 'credit_note') {
										echo '<img src="../images/file.svg" class="svg" style="opacity:0.2"/>';
									} elseif (trim($row['paid_on']) != '') {
										echo '<img src="../images/thumbs-up.svg" class="svg"/>';
									} else { ?>
										<i class="far fa-calendar-plus" onclick="getdate('<?php echo $row['nr']; ?>','<?php echo $row['clid']; ?>','<?php echo $row['invoice_nr']; ?>')" title="Paid"></i>
										<?php
										$invoice_due_date = next_reminder($row['inserted_on'], $row['clid']);
										if ($invoice_due_date < 0 and count($row['reminded_on']) == 0) { ?>
											<a href="index.php?inc=invoice_reminder&nr=<?php echo $row['nr']; ?>&act=rem" target="iframe" data-width="890" data-height="580" title="Send first reminder for INVOICE: (<?php echo $row['invoice_nr']; ?>)" class="first_remind"><i class="far fa-bell"></i></a>
											<?php } else {
											//TODO: add unlimited reminders to the new system
											$invoice_due_date = next_reminder($row['reminded_on'], $row['clid']);
											if ($invoice_due_date < - ($term)) {
												if (count($row['reminded_on']) > 0) { ?>
													<a href="index.php?inc=invoice_reminder&nr=<?php echo $row['nr']; ?>&act=rem" target="iframe" data-width="890" data-height="580" title="Send Reminder (Nr: <?php echo count($row['reminded_on']) + 1; ?>) for INVOICE: (<?php echo $row['invoice_nr']; ?>)" class="final_remind"><i class="far fa-bell" style="color:orange"></i></a>
												<?php     };
												if (count($row['reminded_on']) > 1) { ?>
													<a href="index.php?inc=invoice_reminder&nr=<?php echo $row['nr']; ?>&act=sus" target="iframe" data-width="890" data-height="580" title="Suspend account" class="suspend_remind"><i class="fas fa-lock" style="color:red"></i></a>
								<?php };
											};
										};
									};
								}
								?>
								<?php if (trim($row['invoice_type']) != 'credit_note') { ?>
									<a href="/invoices/index.php?inc=create_invoice&type=credit_note&clid=<?php echo $row['clid']; ?>&invnr=<?php echo $row['nr']; ?>&goback=invoices&show=<?php echo $_POST['show']; ?>" title="Credit note"><i class="fas fa-file-invoice-dollar"></i></a>
									<i class="fas fa-file-invoice-dollar load_popup" data-url="invoice_save.php?act=credit_noted&nr=<?php echo $row['nr']; ?>&invnr=<?php echo $row['invoice_nr']; ?>" title="Add to already credited invoice."></i>
									<a href="index.php?inc=create_invoice&act=clone&nr=<?php echo $row['nr']; ?>&goback=draft" title="Clone invoice"><i class="far fa-copy"></i></a>
								<?php } ?>
							<?php }; ?>
							<i class="fas fa-exclamation-triangle load_popup sys_error" style="font-size: 16px !important;<?php echo $row['sys_error'] == 1 ? 'color:red;' : 'color:orange'; ?>" data-url="sys_error.php?act=sys_error&nr=<?php echo $row['nr']; ?>" title="Print system error on the invoice."></i>
							<?php if (trim($row['mail_error']) != '') { ?>
								<?php if (strpos($row['mail_error'], 'exact') != false) { ?>
									<img src="/images/exact.svg" style="width:40px" title="Error: email not sent to ExactOnline" />
								<?php } else { ?>
									<i class="far fa-envelope" style="color:red" title="Error: email not sent"></i>
								<?php }; ?>
							<?php }; ?>
							<div><i class="far fa-edit editInvoice" onclick="editInvoice(this)"><span>Edit Invoice</span></i>
								<?php if ($row['sys_error'] == 1) { ?>
									<span style="color:red">System Error</span>
								<?php }; ?>
							</div>
						</td>
						<td class="adminUser">
							<?php
							if (isset($admin_users[$row['uid']])) {
								echo '<span title="' . $admin_users[$row['uid']] . '" style="background:' . $colors[$row['uid']] . ' !important;">' . substr($admin_users[$row['uid']], 0, 1) . '</span>';
							} elseif (isset($office_users[$row['off_id']]) && $row['off_id'] != 0) {
								echo '<span class="office" title="' . $office_users[$row['off_id']] . '" style="background:black !important;">' . substr($office_users[$row['off_id']], 0, 1) . '</span>';
							} else {
								echo '<i class="fas fa-user" style="color:grey;"></i>';
							} ?>
						</td>
						<td>
							<?php if (trim($row['invoice_type']) != 'credit_note' && trim($row['status']) != 'credited' && isset($_POST['clid']) && trim($_POST['clid']) != '' && isset($_POST['sbsid']) && trim($_POST['sbsid']) != '') { ?>
								<input type="checkbox" name="massCredit[]" class="massCredit" value="<?php echo $row['nr']; ?>" />
							<?php }; ?>

						</td>
				<?php };
				}; ?>
			<?php }; ?>
		</tr>
<?php
	}
};
if (isset($invoice_actions) or !isset($_SESSION['offid']))
	$colspan = 'colspan="5"';
else
	$colspan = '';
?>
<tr>
	<th colspan="4" style="text-align:right;">The totals</th>
	<th>Subtotal</th>
	<th>VAT</th>
	<th>Total</th>
	<th colspan="5"></th>
</tr>
<tr>
	<?php
	//fix this problem with the credit notes
	$paid_total =  ($paid_count - $credit_count); ?>
	<th colspan="4" style="text-align:right;color:green;font-weight:normal">Paid invoices (<?php echo $paid_total > 0 ? $paid_total : 0; ?>):</th>
	<th style="color:green;font-weight:normal">&euro;<?php echo $paid_total > 0 ? number_format($subtotal_paid + $subtotal_credit, 2, ',', '.') : '0,00'; ?></th>
	<th style="color:green;font-weight:normal">&euro;<?php echo $paid_total > 0 ? number_format($vat_paid + $vat_credit, 2, ',', '.') : '0,00'; ?></th>
	<th style="color:green;font-weight:normal"><span class="totals">&euro;<?php echo $paid_total > 0 ? number_format($total_paid + $total_credit, 2, ',', '.') : '0,00'; ?></span></th>
	<th <?php echo ($colspan); ?>></th>
</tr>
<tr>
	<th colspan="4" style="text-align:right;color:red;font-weight:normal">Open invoices (<?php echo ($invoices_count - $paid_count - $credit_count); ?>):</th>
	<th style="color:red;font-weight:normal">&euro;<?php echo number_format($subtotal - $subtotal_paid, 2, ',', '.'); ?></th>
	<th style="color:red;font-weight:normal">&euro;<?php echo number_format($vat - $vat_paid, 2, ',', '.'); ?></th>
	<th style="color:red;font-weight:normal"><span class="totals">&euro;<?php echo number_format($total - $total_paid, 2, ',', '.'); ?></span></th>
	<th <?php echo ($colspan); ?>></th>
</tr>
<tr class="totalsx">
	<th colspan="4" style="text-align:right;color:grey;font-weight:normal">Credit notes & Credited invoices (<?php echo ($credit_count); ?>):</th>
	<th style="color:grey;font-weight:normal">&euro;<?php echo number_format($subtotal_credit, 2, ',', '.'); ?></th>
	<th style="color:grey;font-weight:normal">&euro;<?php echo number_format($vat_credit, 2, ',', '.'); ?></th>
	<th style="color:grey;font-weight:normal"><span class="totals">&euro;<?php echo number_format($total_credit, 2, ',', '.'); ?></span></th>
	<th <?php echo ($colspan); ?>></th>
</tr>
<tr>
	<th colspan="4" style="text-align:right">Totals - (<span style="font-weight:normal">Paid & Open Invoices</span> <?php echo $invoices_count - (2 * $credit_count); ?>):</th>
	<th>&euro;<?php echo number_format($subtotal + $subtotal_credit, 2, ',', '.'); ?></th>
	<th>&euro;<?php echo number_format($vat + $vat_credit, 2, ',', '.'); ?></th>
	<th><span class="totals">&euro;<?php echo number_format($total + $total_credit, 2, ',', '.'); ?></span></th>
	<th <?php echo ($colspan); ?>></th>
</tr>
<?php
ob_flush();
