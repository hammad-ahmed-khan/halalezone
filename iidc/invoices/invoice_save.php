<?php
define("__HQC__", true);
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if (isset($_REQUEST['act']) and $_REQUEST['act'] == 'credit_noted' and isset($_REQUEST['invnr'])) { ?>
	<script>
		function getCreditNotes(val) {
			if (val.length > 5) {
				jQuery("#creditNotesSearchResults").load("invoice_save.php?act=get_credit_notes_list&nr=<?php echo $_REQUEST['nr']; ?>&invnr=<?php echo $_REQUEST['invnr']; ?>&sq=" + val);
			} else {
				jQuery("#creditNotesSearchResults").html('');
			}
		}
	</script>
	<div style="width:800px;margin:20px;">
		<center>
			<b>Search for credit note:</b> <input type="text" onkeyup="getCreditNotes(this.value)" placeholder="Credit note number" />
		</center>
	</div>
	<div id="creditNotesSearchResults"></div>
	<script>
		getCreditNotes('checkNumber')
	</script>
	<?php
	exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == 'get_credit_notes_list' and isset($_REQUEST['sq'])) {

	if ($_REQUEST['sq'] == 'checkNumber')
		$whr = "AND invoice_items LIKE '%$_REQUEST[invnr]%'";
	else
		$whr = "AND invoice_nr LIKE '%$_REQUEST[sq]%'";

	$sql = "SELECT nr,invoices.sbsid,invoices.clid,invoice_nr,invoice_type,subtotal,vat,total,inserted_on,invoices.status,companies.company_name,companies.active,companies.offid FROM `invoices`
				JOIN companies ON companies.clid = invoices.clid
				WHERE invoice_type = 'credit_note' $whr ORDER BY invoice_nr ASC LIMIT 0,5";
	if ($credit_notes = $amdb->get_results($sql)) { ?>
		<form method="POST" action="invoice_save.php" onsubmit="return post_this_form(this);" data-error="Select a credit note number">
			<input type="hidden" name="act" value="attach_credit_note" />
			<input type="hidden" name="nr" value="<?php echo $_REQUEST['nr']; ?>" />
			<input type="hidden" name="invnr" value="<?php echo $_REQUEST['invnr']; ?>" />
			<table class="alternateOn" style="width: 100%;">
				<thead>
					<tr>
						<th>Number</th>
						<th>Company</th>
						<th>Date</th>
						<th>Subtotal</th>
						<th>VAT</th>
						<th>Total</th>
					</tr>
				</thead>
				<?php
				foreach ($credit_notes as $credit) { ?>
					<tr>
						<td style="white-space:nowrap"><label><input type="radio" name="credit_invnr" value="<?php echo $credit['nr']; ?>" data-required="yes"> <?php echo $credit['invoice_nr']; ?></label></td>
						<td><?php echo $credit['company_name']; ?></td>
						<td><?php echo date("d/m/Y", strtotime($credit['inserted_on'])); ?></td>
						<td style="color:red" class="nowrap"><?php echo "- &euro;" . number_format($credit['subtotal'], 2, ',', '.'); ?></td>
						<td style="color:red" class="nowrap"><?php echo "- &euro;" . number_format($credit['vat'], 2, ',', '.'); ?></td>
						<td style="color:red" class="nowrap"><?php echo "- &euro;" . number_format($credit['total'], 2, ',', '.'); ?></td>
					</tr>

				<?php }; ?>
			</table>
			<center><input type="submit" value="Save" class="center" /></center>
		<?php }
		?>
		</form>
	<?php
	exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == 'attach_credit_note' and isset($_REQUEST['nr']) and isset($_REQUEST['credit_invnr'])) {
	if ($amdb->query("UPDATE invoices SET credit_invnr = '$_REQUEST[credit_invnr]', status = 'credited' WHERE nr = '$_REQUEST[nr]'")) {
		$amdb->post_results('', 'reload');
	} else {
		$amdb->post_results('error:could not save the invoice. Please call the system admin.');
	}
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'deleteDraft' and isset($_POST['id'])) {

	if ($invoice = $amdb->get_row("SELECT * FROM invoices WHERE nr='$_POST[id]'")) {
		if ($invoice['invoice_type'] == 'audit' and isset($invoice['auid'])) {
			$sql = "UPDATE audits SET invoice_nr = '0',invoiced='0000-00-00' WHERE auid='$invoice[auid]' ";
			$amdb->query($sql);
		} elseif ($invoice['invoice_type'] == 'expenses' and isset($invoice['exid'])) {
			$sql = "UPDATE expenses SET invoice_nr = '0' WHERE exid='$invoice[exid]' ";
			$amdb->query($sql);
		} elseif ($invoice['invoice_type'] == 'batch' or $invoice['invoice_type'] == 'annual') {
			if (is_array($invoice_items = decode_json($invoice['invoice_items']))) {

				foreach ($invoice_items as $item) {
					if ($item['type'] == 'a' or $item['type'] == 'b') {
						$table = 'certificates_' . $item['type'];
						$crtNr = 'nr';
					} else {
						$table = 'acms_halal_certificates';
						$crtNr = 'crtNr';
					}
					$sql = "UPDATE $table SET invoice_nr = '0' WHERE $crtNr='$item[crtNr]'";
					$amdb->query($sql);
				};
			}
		}
		$amdb->query("UPDATE invoices SET status='deleted' WHERE nr='$_POST[id]'");
	}
	echo "done";
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'deleteScheduled' and isset($_POST['id'])) {
	if ($invoice = $amdb->get_row("SELECT * FROM invoices WHERE nr='$_POST[id]'")) {
		$amdb->query("UPDATE invoices SET status='deleted' WHERE nr='$_POST[id]'");
	}
	echo "done";
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'changeStatus' and isset($_POST['nr']) and isset($_POST['status']) and isset($_POST['tp'])) {
	if ($amdb->query("UPDATE certificates_{$_POST['tp']} SET status='$_POST[status]' WHERE nr='$_POST[nr]'")) {
		echo "ok";
	} else {
		echo "error: error updating status";
	}
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'changeStatusAll' and isset($_POST['nrs']) and isset($_POST['status']) and isset($_POST['tp'])) {
	$nrs = implode(',', $_POST['nrs']);
	if ($amdb->query("UPDATE certificates_{$_POST['tp']} SET status='$_POST[status]' WHERE FIND_IN_SET(nr,'$nrs')")) {
		echo "ok";
	} else {
		echo "error: error updating status";
	}
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'update_vat_number' and isset($_POST['vatNr']) and isset($_POST['clid'])) {
	if ($amdb->query("UPDATE companies SET vatNr='$_POST[vatNr]' where clid='$_POST[clid]'"))
		echo "Vat number updated";
	else
		echo "error: could not update VAT number";
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'paidOn') {
	if ($amdb->query("UPDATE invoices SET paid_on='$_POST[paid_on]', remarks='$_POST[remarks]' where nr='$_POST[nr]'"))
		echo "done";
	else
		echo "error: could not upade the invoice";
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'undoPayment' && isset($_POST['nr'])) {
	if ($amdb->query("UPDATE invoices SET paid_on='' where nr='$_POST[nr]'"))
		echo "done";
	else
		echo "error: could not update the invoice";
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'sendEmailReminder' and isset($_POST['nr'])) {
	if (!$invoice = $amdb->get_row("Select * FROM invoices where nr='$_POST[nr]'"))
		return;
	$email = $_POST['email'];
	$email['emailmeacopy'] = false;
	$invFile = $prog_path . "/client_data/invoices/$invoice[invoice_nr].pdf";

	if (file_exists($invFile)) {
		if (isset($_POST['do_test']) and trim($_POST['test_email']) != '') {
			$email['to_email'] = $_POST['test_email'];
			$email['subject'] = '(TEST MESSAGE) ' . $email['subject'];
			$email['emailmeacopy'] = false;
			$_POST['act'] = 'test';
		}

		$email['attachments'] = array('invoice-' . $invoice['invoice_nr'] . '.pdf', $invFile);

		$email['clid'] = $invoice['clid'];
		$email['message_group'] = 'invoice_reminder';
		$email['owner'] = '{"table":"invoices","aunr":"nr","nr":"' . $_REQUEST['nr'] . '"}';

		if (trim($invoice['reminded_on']) != '')
			$reminded_on = explode(',', $invoice['reminded_on']);

		$reminded_on[] = date('d/m/Y', time());
		$reminded_on = implode(',', $reminded_on);
		include $prog_path . "/tools/mail/hqc_mail.inc.php";
		if (hqc_mail($email['to_email'], $email['to_name'], $email['from_email'], $email['from_name'], $email['subject'], $email['message'], $email['attachments'], $email['emailmeacopy'], $seen_id = array('type' => 'invoice', 'nr' => $_POST['nr']))) {
			if ($_POST['act'] == "test") {
				$amdb->post_results('Test email is sent. Please check your email.');
			} else {
				$amdb->query("UPDATE invoices SET reminded_on='" . $reminded_on . "' WHERE nr='$_POST[nr]'");
				$amdb->post_results('top.closePopup();top.document.getElementById("contents").contentWindow.loadInvoices(\'new\')', 'function');
				$amdb->post_results('Reminder email sent.');
			}
		} else {
			$amdb->post_results('Error: Could not send the Reminder. Please contact SYS Admin');
		}
	}
	exit();
}

if (isset($_POST['act']) and $_POST['act'] == 'resendInvoice' and isset($_POST['nr'])) {
	if (!$invoice = $amdb->get_row("Select * FROM invoices where nr='$_POST[nr]'"))
		return;
	if (!isset($_POST['client_email']) && !isset($_POST['exact_email'])) {
		echo "Error: Please select at least one email address";
		$amdb->post_results('Error: Please select at least one email address');
		exit();
	}
	$email = $_POST['email'];
	if (isset($_POST['client_email'])) {
		$email['to_email'] = $_POST['client_email'];
		if (isset($_POST['exact_email']) && $_POST['bcc_email'] != '') {
			$bcc_email = $_POST['bcc_email'];
			unset($_POST['bcc_email']);
			$_POST['bcc_email'][0] = $bcc_email;
			$email['emailmeacopy'] = true;
		} else {
			$email['emailmeacopy'] = false;
		}
	} elseif (isset($_POST['exact_email']) && $_POST['bcc_email'] != '') {
		$email['to_email'] = $_POST['bcc_email'];
	}

	$invFile = $prog_path . "/client_data/invoices/$invoice[invoice_nr].pdf";

	if (file_exists($invFile)) {
		if (isset($_POST['do_test']) and trim($_POST['test_email']) != '') {
			$email['to_email'] = $_POST['test_email'];
			$email['subject'] = '(TEST MESSAGE) ' . $email['subject'];
			$email['emailmeacopy'] = false;
			$_POST['act'] = 'test';
		}

		$email['attachments'] = array('invoice-' . $invoice['invoice_nr'] . '.pdf', $invFile);

		$email['clid'] = $invoice['clid'];
		$email['message_group'] = 'invoice_reminder';
		$email['owner'] = '{"table":"invoices","aunr":"nr","nr":"' . $_REQUEST['nr'] . '"}';

		if (trim($invoice['resent_on']) != '')
			$resent_on = explode(',', $invoice['resent_on']);

		$resent_on[] = date('d/m/Y', time());
		$resent_on = implode(',', $resent_on);

		include $prog_path . "/tools/mail/hqc_mail.inc.php";

		$mailRes = hqc_mail($email['to_email'], $email['to_name'], $email['from_email'], $email['from_name'], $email['subject'], $email['message'], $email['attachments'], $email['emailmeacopy']);
		if ($mailRes === true) {
			if ($_POST['act'] == "test") {
				$amdb->post_results('Test email is sent. Please check your email.');
			} else {
				$amdb->query("UPDATE invoices SET resent_on='" . $resent_on . "', mail_error='' WHERE nr = '$_POST[nr]'");
				$amdb->post_results('top.closePopup();removeResent("#resend_' . $_POST['nr'] . '")', 'function');
				$amdb->post_results('Invoice is sent.');
			}
		} else {
			$amdb->query("UPDATE invoices set mail_error = '$mailRes'  WHERE nr= '$_POST[nr]'");
			$amdb->post_results('Error: Could not resend the invoice to ' . $mailRes . '. Please contact SYS Admin');
		}
	}
	exit();
}
