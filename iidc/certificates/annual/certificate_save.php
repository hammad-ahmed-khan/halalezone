<?php
ob_start(); // Buffer output to prevent "headers already sent" errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "../../check_user.inc.php";

include "$prog_path/config/connect.inc.php";
include "$prog_path/config/date_conv.inc.php";

// Fix: Properly get variables from $_REQUEST/$_POST instead of relying on register_globals
$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
$crtNr = isset($_REQUEST['crtNr']) ? $_REQUEST['crtNr'] : '';
$crtDo = isset($_REQUEST['crtDo']) ? $_REQUEST['crtDo'] : '';
$reissue = isset($_REQUEST['reissue']) ? $_REQUEST['reissue'] : '';
$clid = isset($_REQUEST['clid']) ? $_REQUEST['clid'] : '';

// Get date variables from POST
$date_of_issue = isset($_POST['date_of_issue']) ? $_POST['date_of_issue'] : '';
$date_of_expiry = isset($_POST['date_of_expiry']) ? $_POST['date_of_expiry'] : '';
$initial_issue_date = isset($_POST['initial_issue_date']) ? $_POST['initial_issue_date'] : '';

/**
 * Convert various date formats to dd.mm.yyyy format for date2num()
 * Handles: mm/dd/yyyy, yyyy-mm-dd, dd.mm.yyyy
 */
function convertDateFormat($date) {
    if (empty($date)) return '';
    
    $date = trim($date);
    
    // Check if date is in mm/dd/yyyy format
    if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $date, $matches)) {
        // Convert mm/dd/yyyy to dd.mm.yyyy
        return str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '.' . str_pad($matches[1], 2, '0', STR_PAD_LEFT) . '.' . $matches[3];
    }
    
    // Check if date is in yyyy-mm-dd format
    if (preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2})$#', $date, $matches)) {
        // Convert yyyy-mm-dd to dd.mm.yyyy
        return str_pad($matches[3], 2, '0', STR_PAD_LEFT) . '.' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '.' . $matches[1];
    }
    
    // Check if date is in dd/mm/yyyy format
    if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $date, $matches)) {
        // Convert dd/mm/yyyy to dd.mm.yyyy
        return str_pad($matches[1], 2, '0', STR_PAD_LEFT) . '.' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '.' . $matches[3];
    }
    
    // Return as-is if already in correct format or unknown format
    return $date;
}

// Convert date formats before processing
$date_of_issue = convertDateFormat($date_of_issue);
$date_of_expiry = convertDateFormat($date_of_expiry);
$initial_issue_date = convertDateFormat($initial_issue_date);

// Process date fields - convert to database format
$converted_date_of_issue = date2num($date_of_issue);
$converted_date_of_expiry = date2num($date_of_expiry);
$converted_initial_issue_date = sql_date($initial_issue_date);

// Only set dates in POST if they converted successfully (not 0 or empty)
// This prevents overwriting good dates with 0 when editing/printing
if (!empty($converted_date_of_issue) && $converted_date_of_issue != 0) {
	$_POST['date_of_issue'] = $converted_date_of_issue;
} else {
	// Remove from POST so it won't overwrite existing value
	unset($_POST['date_of_issue']);
}

if (!empty($converted_date_of_expiry) && $converted_date_of_expiry != 0) {
	$_POST['date_of_expiry'] = $converted_date_of_expiry;
} else {
	unset($_POST['date_of_expiry']);
}

if (!empty($converted_initial_issue_date) && $converted_initial_issue_date != '0000-00-00' && $converted_initial_issue_date != 0) {
	$_POST['initial_issue_date'] = $converted_initial_issue_date;
} else {
	unset($_POST['initial_issue_date']);
}

if ($act == "stopReissue" && !empty($crtNr)) {
	$amdb->query("UPDATE acms_halal_certificates SET status='done' WHERE crtNr = '$crtNr'");
	echo "<script>window.parent.loadCertificates('yes');</script>";
	exit();
}

if ($act == "sendCertificate" && !empty($crtNr)) {
?>
	<form action="/iidc/certificates/annual/certificate_save.php" method="post" onsubmit="return post_this_form(this);">
		<input type="hidden" name="act" value="update_status_sent_on" />
		<input type="hidden" name="crtNr" value="<?php echo $crtNr; ?>" />
		Sent on: <input type="text" name="status_sent_on" placeholder="Date" class="date" style="width:100px" />
		<input type="submit" value="save" />
	</form>
<?php
	exit();
}

if ($act == "internalMemo" && !empty($crtNr)) {
	$memo = '';
	if ($cert = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates WHERE crtNr = '$crtNr'")) {
		$memo = $cert['memo'];
	}
?>
	<form action="/iidc/certificates/annual/certificate_save.php" id="internalMemo" method="post" onsubmit="return post_this_form(this);">
		<input type="hidden" name="act" value="saveInternalMemo" />
		<input type="hidden" name="crtNr" value="<?php echo $crtNr; ?>" />
		<input type="hidden" name="saveBtn" id="saveBtn" value="Save" />
		<div style="padding:10px">
			<textarea style="width:400px;height:100px" name="memo"><?php echo $memo; ?></textarea>
		</div>
	</form>
<?php
	exit();
}

if ($act == "saveInternalMemo" && !empty($crtNr)) {
	$memo = isset($_POST['memo']) ? $_POST['memo'] : '';
	if ($amdb->update("$tbl[prefix]_halal_certificates", array("memo" => $memo), "crtNr = '$crtNr'")) {
		echo "<script>
		window.parent.location.reload();
	</script>";
	};
	exit();
}

if ($act == "delete_memo" && !empty($crtNr)) {
	if ($amdb->update("$tbl[prefix]_halal_certificates", array("memo" => ''), "crtNr = '$crtNr'")) {
		echo 'done';
	};
	exit();
}

/*--Delete a record--*/
if ($act == "delete" && !empty($crtNr)) {
	$amdb->query("UPDATE $tbl[prefix]_halal_certificates SET status='deleted' WHERE crtNr = '$crtNr'");
	echo "success";
	exit();
}

if ($act == "update_status_sent_on" && !empty($crtNr)) {
	if (isset($_POST['status_sent_on']) && trim($_POST['status_sent_on']) != '') {
		$status_sent_on = date2num(convertDateFormat($_POST['status_sent_on']));
		$amdb->query("UPDATE acms_halal_certificates SET status_sent_on='$status_sent_on' WHERE crtNr = '$crtNr'");
		echo "<script>
			window.parent.document.getElementById('sendCertSpan_" . $crtNr . "').innerHTML = '<b>Sent on:</b>" . date("d/m/Y", $status_sent_on) . "';
			window.parent.closeIframe();
		</script>";
	} else {
		echo "<script>alert('Sent on date is missing!');</script>";
	}
	exit();
}

if ($act == "recievedCertificate" && !empty($crtNr)) {
?>
	<form action="/iidc/certificates/annual/certificate_save.php" method="post" onsubmit="return post_this_form(this);">
		<input type="hidden" name="act" value="update_status_received_on" />
		<input type="hidden" name="crtNr" value="<?php echo $crtNr; ?>" />
		Received on: <input type="text" name="status_received_on" placeholder="Date" class="date" style="width:100px" />
		<input type="submit" value="save" />
	</form>
<?php
	exit();
}

if ($act == "update_status_received_on" && !empty($crtNr)) {
	if (isset($_POST['status_received_on']) && trim($_POST['status_received_on']) != '') {
		$status_received_on = date2num(convertDateFormat($_POST['status_received_on']));
		$amdb->query("UPDATE acms_halal_certificates SET status_received_on='$status_received_on' WHERE crtNr = '$crtNr'");
		echo "<script>
			window.parent.document.getElementById('recievedCertSpan_" . $crtNr . "').innerHTML = '<b>Recieved on:</b> " . date("d/m/Y", $status_received_on) . "';
			window.parent.closeIframe();
		</script>";
	} else {
		echo "<script>alert('Received on date is missing!');</script>";
	}
	exit();
}

if ($act == "justPrint" && !empty($crtNr)) {
	if ($cert = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates WHERE crtNr = '$crtNr'")) {
		$amdb->query("UPDATE $tbl[prefix]_halal_certificates SET printed_on='" . time() . "' WHERE crtNr='$crtNr'");
		$certFilesDir = $hcp_path . "/iidc/client_data/certificates";
		if (trim($cert['url']) != '' && file_exists($certFilesDir . '/' . $cert['url'])) {
			echo "<script>window.open('$prog_www/iidc/certificates/view/$cert[url]?act=print');</script>";
		};
		echo "<script>
		window.parent.location.reload();
	</script>";
	}
	exit();
}

if (isset($_POST['act']) && $_POST['act'] == "reissued" && !empty($_POST['crtNr'])) {
	$amdb->query("UPDATE $tbl[prefix]_halal_certificates SET reissued='" . time() . "' WHERE crtNr = '" . $_POST['crtNr'] . "'");
	echo "<script>window.parent.location.replace('index.php?inc=certificates')</script>";
	exit();
}

// Process manufacturing sites
if (!isset($_POST['manufacturing_site']))
	$_POST['manufacturing_site'] = '';
else
	$_POST['manufacturing_site'] = implode(',', $_POST['manufacturing_site']);

// Process reference standards
if (isset($_POST['reference_standards']) && is_array($_POST['reference_standards']))
	$_POST['reference_standards'] = json_encode($_POST['reference_standards']);

// Process categories
if (isset($_POST['category']) && is_array($_POST['category']))
	$_POST['category'] = json_encode($_POST['category']);

// Process revision
if (isset($_POST['revision']))
	$_POST['revision'] = json_encode($_POST['revision']);

// Set office ID default
if (!isset($_POST['offid']))
	$_POST['offid'] = 0;

// Set requested_by based on office
if ($_POST['offid'] == 0) {
	$uid = isset($_SESSION['halal']['id']) ? $_SESSION['halal']['id'] : (isset($_SESSION['user']['uid']) ? $_SESSION['user']['uid'] : 0);
	$uname = isset($_SESSION['halal']['user']) ? $_SESSION['halal']['user'] : (isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : '');
	$_POST['requested_by'] = encode_json(array("uid" => $uid, "name" => $uname));
} else {
	$offid_val = isset($_SESSION['offid']) ? $_SESSION['offid'] : 0;
	$hqc_title = isset($_SESSION['hqc_title']) ? $_SESSION['hqc_title'] : '';
	$_POST['requested_by'] = encode_json(array("offid" => $offid_val, "name" => $hqc_title));
}

// Process signatories
if (isset($_POST['signatories']))
	$_POST['signatories'] = str_replace('\r\n', "", json_encode($_POST['signatories']));
else
	$_POST['signatories'] = '';

// Store all products
if (isset($_POST['products']))
	$_POST['all_products'] = $_POST['products'];

/*--Insert a record--*/
if ($act == "add") {
	// Get office data for certificate/reference number generation
	$office = $amdb->get_row("SELECT * FROM offices WHERE offid = '" . $_POST['offid'] . "'");
	
	// Always generate reference_number
	$_POST['reference_number'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($clid, 5, "0", STR_PAD_LEFT);
	
	// Generate certificate number for all new certificates
	if (trim($_POST['certificate_nr']) == '' || !isset($_POST['certificate_nr']) || $_POST['certificate_nr'] == '0') {
		if ($total = get_option("annual_crtNr", $_POST['offid']))
			$total++;
		else
			$total = 1;
		update_option("annual_crtNr", $total, $_POST['offid']);
		$_POST['certificate_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($total, 5, "0", STR_PAD_LEFT);
	}
	
	// Set initial status as pending - print/authorize handler will update if needed
	$_POST['status'] = 'pending';
	$_POST['ordered_on'] = time();
	
	if (!isset($_POST['office_address']))
		$_POST['office_address'] = $_POST['offid'];

	if (isset($_POST['certificate_options']))
		$_POST['options'] = json_encode($_POST['certificate_options']);
	if (isset($_POST['annex_options']))
		$_POST['annex_options'] = json_encode($_POST['annex_options']);

	$crtNr = $amdb->insert("$tbl[prefix]_halal_certificates", $_POST);

	if (!empty($crtDo) && ($crtDo == "print" || $crtDo == "authorize")) {
		// Store crtNr in POST for the print/authorize handler below
		$_POST['crtNr'] = $crtNr;
		// Fall through to common print/authorize handler which updates status
	} else {
		header("Location: index.php?inc=certificates");
		exit();
	}
}
/*--End insert a record--*/

// Save version history before update
if ($act != 'add' && !empty($crtNr)) {
	$data = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates WHERE crtNr = '$crtNr'");
	if ($data) {
		unset($data['certificate_content']);
		$old_data['item_id'] = $crtNr;
		$old_data['vr'] = $data['vr'];
		$old_data['item_content'] = serialize($data);
		$old_data['item_table'] = "$tbl[prefix]_halal_certificates";
		$old_data['item_action'] = $act;
		$old_data['item_url'] = $data['url'];

		if ($item_content = $amdb->get_row("SELECT item_content,inserted_on FROM hqc_versions WHERE item_id = '$crtNr' ORDER BY inserted_on DESC")) {
			// Remove all white spaces and compare the two strings
			if (preg_replace('/\s+/', '', $old_data['item_content']) != preg_replace('/\s+/', '', $item_content['item_content'])) {
				$amdb->insert("hqc_versions", $old_data);
			}
		} else {
			$verid = $amdb->insert("hqc_versions", $old_data);
		}
	}
}

/*--Update a record--*/
if ($act == "edit" && !empty($crtNr)) {
	// Skip full update if we're just printing or authorizing - only update specific fields
	if (!empty($crtDo) && ($crtDo == "print" || $crtDo == "authorize")) {
		// For print/authorize, we only need to update options and annex_options
		$updateData = array();
		
		if (isset($_POST['certificate_options']))
			$updateData['options'] = json_encode($_POST['certificate_options']);
			
		if (isset($_POST['annex_options']))
			$updateData['annex_options'] = json_encode($_POST['annex_options']);
		
		if (!empty($updateData)) {
			$amdb->update("$tbl[prefix]_halal_certificates", $updateData, "crtNr='$crtNr'");
		}
	} else {
		// Normal edit/save - update all fields
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
}
/*--End update a record--*/

/*update reissued certificate*/
if ($reissue == "y" && !empty($crtNr)) {
	$amdb->query("UPDATE $tbl[prefix]_halal_certificates SET reissue='" . time() . "' WHERE crtNr = '$crtNr'");
}

// Handle print or authorize actions
if (!empty($crtDo) && ($crtDo == "print" || $crtDo == "authorize")) {

	if (!isset($_POST['offid']))
		$_POST['offid'] = 0;

	if ($_POST['offid'] == 0) {
		$uid = isset($_SESSION['user']['uid']) ? $_SESSION['user']['uid'] : (isset($_SESSION['halal']['id']) ? $_SESSION['halal']['id'] : 0);
		$uname = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : (isset($_SESSION['halal']['user']) ? $_SESSION['halal']['user'] : '');
		$handled_by = encode_json(array("uid" => $uid, "name" => $uname));
	} else {
		$offid_val = isset($_SESSION['offid']) ? $_SESSION['offid'] : 0;
		$hqc_title = isset($_SESSION['hqc_title']) ? $_SESSION['hqc_title'] : '';
		$handled_by = encode_json(array("offid" => $offid_val, "name" => $hqc_title));
	}

	$office = $amdb->get_row("SELECT * FROM offices WHERE offid = '" . $_POST['offid'] . "'");
	
	if (!isset($_POST['certificate_nr']) || trim($_POST['certificate_nr']) == '' || $_POST['certificate_nr'] == '0') {
		if ($total = get_option("annual_crtNr", $_POST['offid']))
			$total++;
		else
			$total = 1;
		update_option("annual_crtNr", $total, $_POST['offid']);
		$_POST['certificate_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($total, 5, "0", STR_PAD_LEFT);
	}
	
	$_POST['reference_number'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($clid, 5, "0", STR_PAD_LEFT);
	
	if ($crtDo == "print") {
		$status = "printed_on='" . time() . "', status='printed'";
	} else {
		$status = "status_authorized_on='" . time() . "', status='authorized'";
	}

	$amdb->query("UPDATE $tbl[prefix]_halal_certificates SET handled_by = '$handled_by', $status, certificate_nr='" . $_POST['certificate_nr'] . "', reference_number='" . $_POST['reference_number'] . "' WHERE crtNr='" . $_POST['crtNr'] . "'");
	
	// Submit form to certificate.pdf.php to generate PDF
	$offid_redirect = isset($_POST['offid']) ? $_POST['offid'] : 0;
	?>
	<!DOCTYPE html>
	<html>
	<head><title>Certificate</title></head>
	<body style="display:none;">
		<form id="pdfForm" action="certificate.pdf.php" method="post" target="_blank">
			<?php
			foreach ($_POST as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $subKey => $subValue) {
						if (is_array($subValue)) {
							foreach ($subValue as $subSubKey => $subSubValue) {
								echo '<input type="hidden" name="' . htmlspecialchars($key) . '[' . htmlspecialchars($subKey) . '][' . htmlspecialchars($subSubKey) . ']" value="' . htmlspecialchars($subSubValue) . '" />';
							}
						} else {
							echo '<input type="hidden" name="' . htmlspecialchars($key) . '[' . htmlspecialchars($subKey) . ']" value="' . htmlspecialchars($subValue) . '" />';
						}
					}
				} else {
					echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" />';
				}
			}
			?>
			<input type="hidden" name="crtDo" value="<?php echo htmlspecialchars($crtDo); ?>" />
		</form>
		<script>
			document.getElementById('pdfForm').submit();
			window.location.href = 'index.php?inc=certificates&offid=<?php echo $offid_redirect; ?>';
		</script>
	</body>
	</html>
	<?php
	exit();
}

/*--End processing--*/

// Redirect based on user type and afterPrint setting
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'client') {
	header("Location: /company/");
	exit();
} else {
	$afterPrint = isset($_POST['afterPrint']) ? $_POST['afterPrint'] : '';
	$offid = isset($_REQUEST['offid']) ? $_REQUEST['offid'] : 0;
	
	if ($afterPrint == 'invoicesList') {
		header("Location: /iidc/invoices/?show=all");
		exit();
	} elseif ($afterPrint == 'createInvoice') {
		header("Location: /iidc/invoices/index.php?inc=create_invoice&type=annual&goback=create_hqc_invoice&clid=" . $_POST['clid'] . "&crtNr=$crtNr");
		exit();
	} else {
		header("Location: index.php?inc=certificates&offid=" . $offid);
		exit();
	}
}

ob_end_flush();
?>