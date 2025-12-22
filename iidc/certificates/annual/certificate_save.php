<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../../check_user.inc.php";

include "$prog_path/config/connect.inc.php";
include "$prog_path/config/date_conv.inc.php";
if (isset($_REQUEST['act']) and $_REQUEST['act'] == "stopReissue" and isset($_REQUEST['crtNr'])) {
	$amdb->query("update acms_halal_certificates set status='done' where  crtNr = '$_REQUEST[crtNr]'");
	$amdb->post_results("loadCertificates('yes');", "function");
	exit();
}
if (isset($_REQUEST['act']) and $_REQUEST['act'] == "sendCertificate" and isset($_REQUEST['crtNr'])) {
?>
	<form action="/certificates/annual/certificate_save.php" method="post" onsubmit="return post_this_form(this);">
		<input type="hidden" name="act" value="update_status_sent_on" />
		<input type="hidden" name="crtNr" value="<?php echo $_REQUEST['crtNr']; ?>" />
		Sent on: <input type="text" name="status_sent_on" placeholder="Date" class="date" style="width:100px" />
		<input type="submit" value="save" />
	</form>
<?php
	exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == "internalMemo" and isset($_REQUEST['crtNr'])) {
	if ($cert = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates WHERE crtNr = '$_REQUEST[crtNr]'")) {
		$memo = $cert['memo'];
	}
?>
	<form action="/certificates/annual/certificate_save.php" id="internalMemo" method="post" onsubmit="return post_this_form(this);">
		<input type="hidden" name="act" value="saveInternalMemo" />
		<input type="hidden" name="crtNr" value="<?php echo $_REQUEST['crtNr']; ?>" />
		<input type="hidden" name="saveBtn" id="saveBtn" value="Save" />
		<div style="padding:10px">
			<textarea style="width:400px;height:100px" name="memo"><?php echo $memo; ?></textarea>
		</div>
	</form>
<?php
	exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == "saveInternalMemo" and isset($_REQUEST['crtNr'])) {
	if ($amdb->update("$tbl[prefix]_halal_certificates", array("memo" => $_POST['memo']), "crtNr = '$_POST[crtNr]'")) {
		echo "<script>
		window.parent.location.reload();
	</script>";
	};
	exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == "delete_memo" and isset($_REQUEST['crtNr'])) {
	if ($amdb->update("$tbl[prefix]_halal_certificates", array("memo" => ''), "crtNr = '$_POST[crtNr]'")) {
		echo 'done';
	};
	exit();
}

/*--Delete a record--*/
if (isset($act) and $act == "delete" and isset($_REQUEST['crtNr'])) {
	mysql_query("UPDATE $tbl[prefix]_halal_certificates set status='deleted' where  crtNr = '$crtNr'");
	echo "success";
	exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == "update_status_sent_on" and isset($_REQUEST['crtNr'])) {
	if (isset($_POST['status_sent_on']) and trim($_POST['status_sent_on']) != '') {
		$status_sent_on = date2num($_POST['status_sent_on']);
		$amdb->query("update acms_halal_certificates set status_sent_on='$status_sent_on' where  crtNr = '$_POST[crtNr]'");
		$amdb->post_results("<b>Sent on:</b>" . date("d/m/Y", $status_sent_on), 'html', 'sendCertSpan_' . $_REQUEST['crtNr']);
	} else {
		$amdb->post_results("Sent on date is missing!");
	}
	exit();
}
if (isset($_REQUEST['act']) and $_REQUEST['act'] == "recievedCertificate" and isset($_REQUEST['crtNr'])) {
?>
	<form action="/certificates/annual/certificate_save.php" method="post" onsubmit="return post_this_form(this);">
		<input type="hidden" name="act" value="update_status_received_on" />
		<input type="hidden" name="crtNr" value="<?php echo $_REQUEST['crtNr']; ?>" />
		Received on: <input type="text" name="status_received_on" placeholder="Date" class="date" style="width:100px" />
		<input type="submit" value="save" />
	</form>
	<?php
	exit();
}
if (isset($_REQUEST['act']) and $_REQUEST['act'] == "update_status_received_on" and isset($_REQUEST['crtNr'])) {
	if (isset($_POST['status_received_on']) && trim($_POST['status_received_on']) != '') {
		$status_received_on = date2num($_POST['status_received_on']);
		$amdb->query("update acms_halal_certificates set status_received_on='$status_received_on' where  crtNr = '$_POST[crtNr]'");
		$amdb->post_results("<b>Recieved on:</b> " . date("d/m/Y", $status_received_on), 'html', 'recievedCertSpan_' . $_REQUEST['crtNr']);
	} else {
		$amdb->post_results("Received on date is missing!");
	}
	exit();
}
if (isset($_REQUEST['act']) and $_REQUEST['act'] == "justPrint" and isset($_REQUEST['crtNr'])) {
	if ($cert = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates WHERE crtNr = '$_REQUEST[crtNr]'")) {
		$amdb->query("UPDATE $tbl[prefix]_halal_certificates SET printed_on='" . time() . "' WHERE crtNr='$_REQUEST[crtNr]'");
		$certFilesDir = $hcp_path . "/client_data/certificates";
		if (trim($cert['url']) != '' && file_exists($certFilesDir . '/' . $cert['url'])) {
			echo "<script>window.open('$prog_www/certificates/view/$cert[url]?act=print');</script>";
		};
		echo "<script>
		window.parent.location.reload();
	</script>";
	}
	exit();
}
if (isset($_POST['act']) and $_POST['act'] == "reissued" and isset($_POST['crtNr']) and trim($_POST['crtNr']) != "") {
	mysql_query("update $tbl[prefix]_halal_certificates set reissued='" . time() . "' where  crtNr = '$_POST[crtNr]'");
	echo "<script>window.parent.location.replace('index.php?inc=certificates')</script>";
	exit();
}
$_POST['date_of_issue'] = date2num($date_of_issue);
$_POST['date_of_expiry'] = date2num($date_of_expiry);
$_POST['initial_issue_date'] = sql_date($initial_issue_date);

if (!isset($_POST['manufacturing_site']))
	$_POST['manufacturing_site'] = '';
else
	$_POST['manufacturing_site'] = implode(',', $_POST['manufacturing_site']);
if (isset($_POST['reference_standards']) and is_array($_POST['reference_standards']))
	$_POST['reference_standards'] = json_encode($_POST['reference_standards']);
if (isset($_POST['category']) and is_array($_POST['category']))
	$_POST['category'] = json_encode($_POST['category']);
if (isset($_POST['revision']))
	$_POST['revision'] = json_encode($_POST['revision']);

if (!isset($_POST['offid']))
	$_POST['offid'] = 0;

if ($_POST['offid'] == 0) {
	$_POST['requested_by'] = encode_json(array("uid" => $_SESSION['user']['uid'], "name" => $_SESSION['user']['name']));
} else {
	$_POST['requested_by'] = encode_json(array("offid" => $_SESSION['offid'], "name" => $_SESSION['hqc_title']));
}

if (isset($_POST['signatories']))
	$_POST['signatories'] = str_replace('\r\n', "", json_encode($_POST['signatories']));
else
	$_POST['signatories'] = '';

if (isset($_POST['products']))
	$_POST['all_products'] = $_POST['products'];
/*--Insert a record--*/
if (isset($act) and $act == "add") {
	if (isset($_POST['certificate_options']['reprint'])) {
		$office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$_POST[offid]'");
		if (trim($_POST['certificate_nr']) == '' or !isset($_POST['certificate_nr']) or $_POST['certificate_nr'] == '0') {
			if ($total = get_option("annual_crtNr", $_POST['offid']))
				$total++;
			else
				$total = 1;
			update_option("annual_crtNr", $total, $_POST['offid']);
			$_POST['certificate_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($total, 5, "0", STR_PAD_LEFT);
		}
		$_POST['reference_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($_REQUEST['clid'], 5, "0", STR_PAD_LEFT);
		$_POST['status'] = 'printed';
		$_POST['printed_on'] = time();
	} else {
		$_POST['certificate_nr'] = 0;
		$_POST['status'] = 'pending';
	}
	$_POST['ordered_on'] = time();
	if (!isset($_POST['office_address']))
		$_POST['office_address'] = $_POST['offid'];

	if (isset($_POST['certificate_options']))
		$_POST['options'] = json_encode($_POST['certificate_options']);
	if (isset($_POST['annex_options']))
		$_POST['annex_options'] = json_encode($_POST['annex_options']);

	$crtNr = $amdb->insert("$tbl[prefix]_halal_certificates", $_POST);

	if (isset($crtDo) and (trim($crtDo) == "print" or $crtDo == "authorize")) {
		$input = '<input type="hidden" id="crtNr" name="crtNr" value="' . $crtNr . '" />';
	?>
		<script>
			parent.document.addEditForm.insertAdjacentHTML('afterbegin', '<?php echo $input; ?>');
			parent.document.getElementById('act').value = '<?php echo $crtDo; ?>';
			parent.document.addEditForm.submit();
		</script>
<?php
		exit();
	} else {
		$amdb->post_results('index?inc=certificates', 'url');
	}
}
/*--End insert a record--*/

if ($act != 'add' && $data = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates WHERE crtNr = '$_POST[crtNr]'")) {

	unset($data['certificate_content']);
	$old_data['item_id'] = $_POST['crtNr'];
	$old_data['vr'] = $data['vr'];
	$old_data['item_content'] = serialize($data);
	$old_data['item_table'] = "$tbl[prefix]_halal_certificates";
	$old_data['item_action'] = $act;
	$old_data['item_url'] = $data['url'];

	if ($item_content = $amdb->get_row("SELECT item_content,inserted_on FROM hqc_versions WHERE item_id = '$_POST[crtNr]' ORDER BY inserted_on DESC")) {
		//remove all white spaces and compare the two strings
		if (preg_replace('/\s+/', '', $old_data['item_content']) != preg_replace('/\s+/', '', $item_content['item_content'])) {
			$amdb->insert("hqc_versions", $old_data);
		}
	} else {
		$verid = $amdb->insert("hqc_versions", $old_data);
	}
}

/*--Update a record--*/
if (isset($act) and $act == "edit") {
	$amdb->do_unset('ordered_on');
	$_POST['printed_on'] = 0;
	$_POST['status_authorized_on'] = 0;
	if (isset($_POST['certificate_options']))
		$_POST['options'] = json_encode($_POST['certificate_options']);
	else
		$_POST['options'] = '';
	if (isset($_POST['annex_options']))
		$_POST['annex_options'] = json_encode($_POST['annex_options']);
	else
		$_POST['annex_options'] = '';
	$_POST['status'] = 'updated';
	$amdb->update("$tbl[prefix]_halal_certificates", $_POST, "crtNr='$crtNr'");
}
/*--End update a record--*/

/*update reissed certificate*/
if (isset($reissue) and trim($reissue) == "y" and isset($crtNr) and trim($crtNr) != "") {
	mysql_query("update $tbl[prefix]_halal_certificates set reissue='" . time() . "' where  crtNr = '$crtNr'");
}

if (isset($crtDo) and (trim($crtDo) == "print" or $crtDo == "authorize")) {

	if (!isset($_POST['offid']))
		$_POST['offid'] = 0;

	if ($_POST['offid'] == 0) {
		$handled_by = encode_json(array("uid" => $_SESSION['user']['uid'], "name" => $_SESSION['user']['name']));
	} else {
		$handled_by = encode_json(array("offid" => $_SESSION['offid'], "name" => $_SESSION['hqc_title']));
	}

	$office = $amdb->get_row("SELECT * FROM offices WHERE offid = '$_POST[offid]'");
	if (trim($_POST['certificate_nr']) == '' or !isset($_POST['certificate_nr']) or $_POST['certificate_nr'] == '0') {
		if ($total = get_option("annual_crtNr", $_POST['offid']))
			$total++;
		else
			$total = 1;
		update_option("annual_crtNr", $total, $_POST['offid']);
		$_POST['certificate_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($total, 5, "0", STR_PAD_LEFT);
	}
	$_POST['reference_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($_REQUEST['clid'], 5, "0", STR_PAD_LEFT);
	if (trim($crtDo) == "print") {
		$status = "printed_on='" . time() . "', status='printed'";
	} else {
		$status = "status_authorized_on='" . time() . "', status='authorized'";
	}

	//TODO: update handled by on the new system
	$amdb->query("UPDATE $tbl[prefix]_halal_certificates SET handled_by = '$handled_by',$status, certificate_nr='$_POST[certificate_nr]', reference_number='$_POST[reference_nr]' WHERE crtNr='$_POST[crtNr]'");
	echo "<script>
	window.parent.document.addEditForm.action	= 'certificate.pdf.php';
	window.parent.document.addEditForm.target = '_blank';
	window.parent.document.addEditForm.submit();
	</script>";
}

/*--End delete a record--*/
if (isset($_SESSION['user_type']) and $_SESSION['user_type'] == 'client') {
	$amdb->post_results("/company/", 'url');
} else {
	if ($_POST['afterPrint'] == 'invoicesList') {
		$amdb->post_results("/invoices/?show=all", 'url');
	} elseif ($_POST['afterPrint'] == 'createInvoice') {
		$amdb->post_results("/invoices/index.php?inc=create_invoice&type=annual&goback=create_hqc_invoice&clid=$_POST[clid]&crtNr=$crtNr", 'url');
	} else {
		$amdb->post_results('index.php?inc=certificates&offid=' . $_REQUEST['offid'], 'url');
	}
	exit();
}
?>