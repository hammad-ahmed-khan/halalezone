<?php
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
extract($_SESSION);

$certFilesDir = $hcp_path . "/iidc/client_data/certificates";
$dmc_file = '/iidc/data/DMC/reports/dmc-';
include_once("$prog_path/config/connect.inc.php");
if (!isset($_GET['oc']))
	$status_completed = "and $tbl[prefix]_halal_certificates.invoice_nr = ''";
elseif ($_GET['oc'] == "a")
	$status_completed = "";
else
	$status_completed = "and $tbl[prefix]_halal_certificates.invoice_nr != ''";
$expiry = ceil(time() - (30 * 86400));
$expiryNext = ceil(time() + (30 * 86400));
$nr = $_POST['st'];
$whr = "AND acms_halal_certificates.date_of_expiry > $expiry
		AND acms_halal_certificates.date_of_expiry < $expiryNext
		AND acms_halal_certificates.printed_on != '0'";
$processDays = ceil(time() - (90 * 86400));

$whr1 = "AND acms_halal_certificates.printed_on = '0'
		 AND acms_halal_certificates.ordered_on > $processDays";
if ($_REQUEST['limit'] != 'all') {
	$lastYear = " AND acms_halal_certificates.ordered_on > " . strtotime("-{$_POST['limit']} month -1 day");
} else {
	$lastYear = '';
}

$audits = array();
if ($client_audits = $amdb->get_results("SELECT clid,audit_date FROM audits WHERE status != 'deleted'")) {
	foreach ($client_audits as $audit) {
		$audits[$audit['clid']] = $audit['audit_date'];
	}
}

$office = array();
if ($offices = $amdb->get_results("SELECT offid,office_name FROM offices WHERE status != 'deleted'")) {
	foreach ($offices as $off) {
		$office[$off['offid']] = $off['office_name'];
	}
}

if (trim($_POST['subSearchField']) != '')
	$_POST['searchField'] = $_POST['subSearchField'];
if (trim($_POST['subSearchQ']) != '')
	$_POST['srearchQ'] = $_POST['subSearchQ'];

//TODO: fix sql on the new system
if (isset($_REQUEST['offid'])) {
	$offid = "AND acms_halal_certificates.offid ='$_REQUEST[offid]'";
} else {
	$offid = '';
}
if (isset($_POST['exportExcel']) && $_POST['exportExcel'] == 'yes') {
	$limit = '';
} elseif (isset($_POST['lmt']) && $_POST['lmt'] != 'all') {
	$limit = "limit $_POST[st],$_POST[lmt]";
} else {
	$limit = "limit 0,10000";
}

$sql = "SELECT acms_halal_certificates.*,companies.clid,companies.company_name,companies.city1,companies.country1,companies.contact_title1,companies.contact_name1,companies.contact_surname1,companies.tel1,companies.email1,offices.offid,offices.office_name
		FROM acms_halal_certificates
		JOIN companies ON acms_halal_certificates.clid = companies.clid
		JOIN offices ON acms_halal_certificates.offid = offices.offid
		WHERE acms_halal_certificates.status!='deleted' $offid $lastYear ";

if (!isset($_POST['searchField']) or $_POST['searchField'] == '') {
	$sql .= $whr . " AND acms_halal_certificates.reissued = '0' order by acms_halal_certificates.date_of_expiry ASC, acms_halal_certificates.date_of_issue DESC";
	$certs = $amdb->get_results($sql . $whr1 . " AND acms_halal_certificates.reissued = '0' order by acms_halal_certificates.ordered_on ASC $limit");
} else {
	$searchField = $_POST['searchField'];
	if ($searchField == 'all_certificates') {
		$sql .= " ORDER BY acms_halal_certificates.ordered_on DESC $limit";
	} elseif ($searchField == 'controlled_certificates') {
		$sql .= $whr1 . " AND JSON_EXTRACT(acms_halal_certificates.options,'$.controlled_by') = 'yes' AND (acms_halal_certificates.status = 'pending' OR acms_halal_certificates.status = 'updated') order by acms_halal_certificates.ordered_on ASC $limit";
	} elseif ($searchField == 'certificates_not_sent') {
		$sql .= " AND status_sent_on = '0' AND printed_on !='0' $limit";
	} elseif ($searchField == 'new_certificates' or (isset($_REQUEST['ref']) && $_REQUEST['ref'] == 'committee')) {
		if (isset($_REQUEST['ref']) && $_REQUEST['ref'] == 'committee') {
			$sql .= " AND acms_halal_certificates.approved_by_dmc = 'no' order by acms_halal_certificates.ordered_on ASC $limit";
		} else {
			$sql .= $whr1 . " AND acms_halal_certificates.status = 'pending' order by acms_halal_certificates.ordered_on ASC $limit";
		}
	} elseif ($searchField == 'crtNr') {
		if (trim($_POST['srearchQ']) == '') {
			echo "Error: Certificate number is required.";
			exit();
		} else {
			$limit = "limit 0,10000";
			$sql .= " AND certificate_nr LIKE '%$_POST[srearchQ]%' $limit";
		}
	} elseif ($searchField == 'company_name') {
		if (trim($_POST['srearchQ']) == '') {
			echo "Error: Company name is required.";
			exit();
		} else {
			$limit = "limit 0,10000";
			$sql .= " AND company_name LIKE '%$_POST[srearchQ]%'  order by acms_halal_certificates.date_of_issue DESC, acms_halal_certificates.crtNr DESC  $limit";
		}
	} elseif ($searchField == 'all_expired') {
		$sql .= "AND reissued='0' AND acms_halal_certificates.date_of_expiry <" . time() . " $limit";
	} elseif ($searchField == 'date_of_expiry') {
		if (trim($_POST['fromDate']) == '' and trim($_POST['toDate']) == '') {
			echo "Error: one of the dates is required.";
			exit();
		}
		$whrDates = '';
		if (trim($_POST['fromDate']) != '') {
			$fromDate = strtotime(fix_date($_POST['fromDate']));
			$whrDates .= " AND acms_halal_certificates.date_of_expiry >= $fromDate";
		}
		if (trim($_POST['toDate']) != '') {
			$toDate = strtotime(fix_date($_POST['toDate']));
			$whrDates .= " AND acms_halal_certificates.date_of_expiry > 0 AND acms_halal_certificates.date_of_expiry <= $toDate AND reissued='0'";
		}
		$sql .= $whrDates . " order by acms_halal_certificates.date_of_expiry ASC, acms_halal_certificates.crtNr ASC $limit";
	} elseif ($searchField == 'order_date') {
		if (trim($_POST['fromDate']) == '' and trim($_POST['toDate']) == '') {
			echo "Error: one of the dates is required.";
			exit();
		}
		$whrDates = '';
		if (trim($_POST['fromDate']) != '') {
			$fromDate = strtotime(fix_date($_POST['fromDate']));
			$whrDates .= " AND acms_halal_certificates.ordered_on >= $fromDate";
		}
		if (trim($_POST['toDate']) != '') {
			$toDate = strtotime(fix_date($_POST['toDate']));
			$whrDates .= " AND acms_halal_certificates.ordered_on <= $toDate";
		}
		$sql .= $whrDates . " order by acms_halal_certificates.ordered_on DESC, acms_halal_certificates.crtNr DESC $limit";
	} elseif ($searchField == 'reference_standards' && trim($_POST['halal_standards']) != '') {
		//check if the standard value is in the json string of the reference_standards field
		$whrStandards = " AND acms_halal_certificates.reference_standards LIKE '%\"$_POST[halal_standards]\"%'";
		$sql .= $whrStandards . " order by acms_halal_certificates.ordered_on DESC, acms_halal_certificates.crtNr DESC $limit";
	}
}

$result = $amdb->get_results($sql);
if (isset($certs) && count($certs) > 0) {
	$result = array_merge($certs, $result);
}

$old_versions = array();
if ($versions = $amdb->get_results("SELECT verid,item_id,inserted_on,item_url FROM hqc_versions WHERE item_table = 'acms_halal_certificates'")) {
	foreach ($versions as $version) {
		$old_versions[$version['item_id']][] = $version;
	}
}
if (isset($_SESSION['member_offices'])) {
	$member_offices = explode(',', $_SESSION['member_offices']);
} else {
	$member_offices = array();
}

//ob_start();
if (isset($result) and count($result) > 0) {
	$items = array();
	foreach ($result as $row) {

		if ($_SESSION['user_type'] == "admin" or $user_type == 'hqc_office'  or (isset($user_clients) and in_array($row['clid'], $user_clients)) or (isset($_SESSION['comemid']) && ($_SESSION['super_admin'] == 'yes' or in_array($row['offid'], $member_offices)))) {

			if (trim($row['options']) != '' and is_array(json_decode($row['options'], true)))
				$options = json_decode($row['options'], true);
			else
				$options = array();
			$nr++;
			if (isset($_POST['exportExcel']) && $_POST['exportExcel'] == 'yes') {
				$items[] = array(
					$row['certificate_nr'] == "0" ? "N/A" : $row['certificate_nr'],
					$row['company_name'] . "\n" . $row['country1'] . " | " . $row['city1'],
					"Issue date: " . ($row['date_of_issue'] == "0" ? "N/A" : date("d.m.Y", $row['date_of_issue'])) . "\nExpiry date: " . ($row['date_of_expiry'] == "0" ? "N/A" : date("d.m.Y", $row['date_of_expiry'])),
					"Requested By: " . $office[$row['offid']] . "\nRequested on: " .	date("d.m.Y", $row['ordered_on'])
				);
			} else {

?>
				<tr id="tr_<?php echo $row['crtNr']; ?>" <?php echo (isset($_POST['referer']) && $nr > 1) ? 'style="display:none;"' : ''; ?>>
					<th class="aunr"><?php echo $nr; ?></th>
					<?php if (!isset($_POST['referer'])) { ?>
						<td data-id="certificate_nr">
							<?php if ($row['certificate_nr'] == "0") {
								echo  "N/A";
							} else {
								if (trim($row['url']) != '' && file_exists($certFilesDir . '/' . $row['url'])) { ?>
									<a target="_new" href="<?php echo $prog_www; ?>/client_data/certificates/<?php echo $row['url']; ?>?act=print&tm=<?php echo time(); ?>"><?php echo $row['certificate_nr']; ?></a>
								<?php
								} else {
									echo $row['certificate_nr'];
								}
									if (isset($old_versions[$row['crtNr']])) {
									$verNr = 1; ?>
									<fieldset class="old_versions">
										<legend>Old versions</legend>
										<ul>
											<?php
											foreach ($old_versions[$row['crtNr']] as $certVersions) {
											?>
												<li>
													<span class="ver-num"><?php echo $verNr++; ?>.</span>
													<?php if (trim($certVersions['item_url']) != '') { ?>
														<i class="far fa-file-pdf"></i>
														<a target="_new" href="<?php echo $prog_www; ?>/client_data/certificates/<?php echo $certVersions['item_url']; ?>">
															<?php echo date("d.m.Y", strtotime($certVersions['inserted_on'])); ?>
														</a>
													<?php } else {
														echo date("d.m.Y", strtotime($certVersions['inserted_on']));
													}; ?>
												</li>
											<?php }; ?>
										</ul>
									</fieldset>
							<?php
								}
							} ?>
						</td>
					<?php }; ?>
					<td data-id="company_name" class="load_popup" data-url="../../admin/load_company.php?clid=<?php echo $row['clid']; ?>" title="<?php echo $row['company_name']; ?>"><?php echo $row['company_name']; ?>
						<?php //TODO: add this to the new system
						?>
						<div style="color:green">
							<?php echo $row['contact_title1'] . ' ' . $row['contact_name1'] . ' ' . $row['contact_surname1']; ?><br />
							<?php echo $row['country1']; ?> | <?php echo $row['city1']; ?><br />
							<i class="fas fa-phone-alt" style="font-size: 12px !important; color: grey;"></i><?php echo $row['tel1']; ?>
						</div>
					</td>
					<td class="nowrap" data-id="issue_expiry" data-sort="<?php echo $row['date_of_expiry'] . str_pad($row['crtNr'], 7, '0', STR_PAD_LEFT); ?>"> <b>Expiry date: </b><?php echo ($row['date_of_expiry'] == "0") ? "--" : date("d.m.Y", $row['date_of_expiry']); ?><br /><b>Issue Date: </b><?php echo ($row['date_of_issue'] == "0") ? "--" : date("d.m.Y", $row['date_of_issue']); ?>
						<?php if (($row['date_of_expiry'] != "0")) {
							echo "<br/>";
							if ($row['reissued'] != '0') { ?>
								<b>Re-issued on:</b> <?php echo date("d.m.Y", $row['reissued']); ?>
								<?php } else {
								if ($row['date_of_expiry'] < time()) { ?>
									<span style="color:#800">Expired <?php echo ceil(($row['date_of_expiry'] - time()) / 86400); ?> Days ago</span>
									<i class="fa fa-times-circle post_this_link" style="color:#800" data-url="certificate_save.php?act=stopReissue&crtNr=<?php echo $row['crtNr']; ?>" data-confirm="Stop re-issuing this certificate for<br/><br/><b><?php echo trim(str_replace('"', '&quot;', $row['company_name'])); ?></b>"></i>
								<?php } else { ?>
									<span style="color:#080">Expires in <?php echo ceil(($row['date_of_expiry'] - time()) / 86400); ?> Days</span>
						<?php }
							};
						}
						?>
					</td>
					<td data-id="ordered_on" data-sort="<?php echo $row['ordered_on'] . str_pad($row['crtNr'], 7, '0', STR_PAD_LEFT); ?>">
						<b>Requested by:</b> <?php echo $office[$row['offid']]; ?>
						<div class="nowrap"> <b>Requested on:</b> <?php echo date("d.m.Y", $row['ordered_on']); ?></div>

						<?php if (isset($audits[$row['clid']])) { ?>
							<br />
							<fieldset>
								<legend>Audit</legend> <b>Date: </b><?php echo date("d.m.Y", strtotime($audits[$row['clid']])); ?>
							</fieldset>
						<?php }; ?>
					</td>
					<td class="status" data-id="status" id="status_<?php echo $row['crtNr']; ?>">
						<?php
						//TODO: add handled by to the new system
						// 						if (trim($row['handled_by']) != '' and is_array(decode_json($row['handled_by']))) {
						// echo $row['handled_by'];
						// 							$handled_by = decode_json($row['handled_by']);
						// 							if (trim($handled_by['name']) != '') {
						// 								if (isset($handled_by['action']))
						// 									$action = $handled_by['action'];
						// 								else
						// 									$action = 'Handled';
						// 								echo "<div style='white-space:nowrap'><b>$action by:</b> " . $handled_by['name'] . "</div>";
						// 							}
						// 						}
						?>
						<?php
						if ($row['status_authorized_on'] > 0) {
							if (isset($handled_by['action']) and $handled_by['action'] == 'Approved') {
								echo "<b>Approved On:</b> " . date("d.m.Y", $row['status_authorized_on']) . '<br/>';
							} else {

								echo "<b>Authorized On:</b> " . date("d.m.Y", $row['status_authorized_on']) . '<br/>';
							}
							if ($row['printed_on'] > 0) {
								if ($row['printed_on'] < $row['ordered_on'])
									$row['printed_on'] = $row['ordered_on'];
								echo "<b>Printed On:</b>" . date("d.m.Y", $row['printed_on']);
							}
						} elseif ($row['printed_on'] > 0 and !isset($_SESSION['comemid'])) {
							if ($row['printed_on'] < $row['ordered_on'])
								$row['printed_on'] = $row['ordered_on'];
							echo "<b>Printed On:</b>" . date("d.m.Y", $row['printed_on']) . '<br/>';
							if ($row['status_sent_on'] == "0" && $user_type == "admin") { ?>
								<?php if (isset($futures[$row['crtNr']])) { ?>
									<span id="sendCertSpan_<?php echo $row['crtNr']; ?>">
										<input type="button" value="Send certificate" onclick="load_html('certificate_save.php?act=sendCertificate&crtNr=<?php echo $row['crtNr']; ?>','#sendCertSpan_<?php echo $row['crtNr']; ?>')" />
									</span>
								<?php }; ?>
								<?php } else {
								if ($row['status_sent_on'] < $row['printed_on'])
									$row['status_sent_on'] = $row['printed_on'];
								echo "<b>Sent on:</b>" . date("d.m.Y", $row['status_sent_on']);
								echo "<br/>";
								if ($row['status_received_on'] == "0") { ?>
									<span id="recievedCertSpan_<?php echo $row['crtNr']; ?>">
										<input type="button" value="Confirm received" onclick="load_html('certificate_save.php?act=recievedCertificate&crtNr=<?php echo $row['crtNr']; ?>','#recievedCertSpan_<?php echo $row['crtNr']; ?>')" />
									</span>
						<?php } else {
									if ($row['status_received_on'] <= $row['status_sent_on'])
										$row['status_received_on'] = $row['status_sent_on'] + 172800;
									echo "<b>Received on:</b>" . date("d.m.Y", $row['status_received_on']);
								}
							}
						} else {
							if (isset($options['approval_required']) && $options['approval_required'] == 'yes') {
								echo 'Waiting for HQC approval';
							} else {
								echo "In process";
							}
							if (is_local()) {
								$today = time() - strtotime("2025-02-01 00:00:00");
								if (isset($_SESSION['comemid']) && $today > 0) {
									if ($row['approved_by_dmc'] == 'pending')
										echo "<br/><b>DMC process started</b>";
									if ($row['approved_by_dmc'] == 'rejected')
										echo "<br/><b>Rejected by DMC</b>";
									if ($row['approved_by_dmc'] == 'approved')
										echo "<br/><b>Approved by DMC</b>";

									if ($row['approved_by_dmc'] == 'no')
										echo '<div style="margin-top:10px;white-space:nowrap" id="_' . $row['crtNr'] . '">
					</div>';
								};
							}
						}
						?>
						<?php if ($user_type != 'hqc_office' && trim($row['memo']) != '') { ?>
							<div class="remarks">
								<i class="fas fa-paperclip"></i>
								<i class="fa fa-trash-alt" onclick="deleteMemo(<?php echo $row['crtNr']; ?>)"></i> <span id="remark_<?php echo $row['crtNr']; ?>"><?php echo str_replace("\n", "<br/>", $row['memo']); ?></span>
							</div>
						<?php }; ?>
						<?php /*if (isset($options['prohibited'])) { ?>
							<div style="color:#900;background: #fdd;padding: 5px;border-radius: 5px;margin-top: 5px;">
								<i class='fas fa-exclamation-triangle prohibited' style='color:red'></i> Certificate has some suspicious product name(s).
							<?php }*/ ?>
						<?php if (isset($options['reprint'])) { ?>
							<div style="margin-top: 10px; padding: 5px; background: beige; color: #900;"><i class="fas fa-print" style="color:#900"></i> Authorized for print.</div>
						<?php }; ?>
					</td>
					<?php
					if ((isset($user_permissions) && (in_array("certificates_actions", $user_permissions) or in_array("ac_reissue_remove", $user_permissions))) or $_SESSION['user_type'] == "admin" or $_SESSION['user_type'] == 'hqc_office') { ?>
						<td class="nowrap actions" >
							<?php if ($row['date_of_expiry'] != "0" and $row['date_of_expiry'] < time()) { ?>
							<?php	};	?>

							<?php
							//TODO: update new link add tm to new system
							if (trim($row['url']) != '') {
								if (file_exists($certFilesDir . '/' . str_replace('.pdf', '.zip', $row['url']))) { ?>
									<a target="_new" href="<?php echo $prog_www; ?>/client_data/certificates/<?php echo str_replace('.pdf', '.zip', $row['url']); ?>"><i class="far fa-file-archive" style="color:red"></i></a>
								<?php };
								if (file_exists($certFilesDir . '/' . $row['url'])) { ?>
									<a target="_new" href="<?php echo $prog_www; ?>/client_data/certificates/<?php echo $row['url']; ?>?act=print&tm=<?php echo time(); ?>"><img src="../../images/view.svg" height="16px" /></a>
								<?php };
							} elseif ($row['status_authorized_on'] > 0) { ?>
								<i class="fas fa-eye" onclick="window.open('/certificates/annual/certificate.pdf.php?crtnr=<?php echo $row['crtNr']; ?>&crtDo=preview')"></i>
								<i class="fas fa-print" onclick="window.open('/certificates/annual/certificate.pdf.php?crtnr=<?php echo $row['crtNr']; ?>&crtDo=print')"></i>

							<?php };
							$qr_color = 'green';
							if ($row['printed_on'] > 0) {
								if (trim($row['qr_status']) != '' and is_array(json_decode($row['qr_status'], true))) {
									$qr_status = json_decode($row['qr_status'], true);
									if ($qr_status['status'] != 'active') {
										if ($qr_status['status'] == 'paused')
											$qr_color = 'orange';
										else
											$qr_color = '#800';
									}
								} else if (ceil(($row['date_of_expiry'] - time()) / 86400) < 0) {
									$status = 'expired';
									$qr_color = '#800';
								}
							?>
								<i class="fa fa-th-large load_popup" data-id="company_name" data-url="download_products_list.php?clid=<?php echo $row['clid']; ?>&crtNr=<?php echo $row['crtNr']; ?>" title="Download Certificate's Products list" data-height="180" data-width="500"></i>
								<i class="fas fa-qrcode load_popup" id="qr_<?php echo $row['crtNr']; ?>" data-url="/iidc/certificates/shared/qr/qr.php?crtNr=<?php echo $row['crtNr']; ?>" title="QR Certificate Status" style="color:<?php echo $qr_color; ?>"></i>
								<?php /* if ($row['country1'] != 'Israel') { ?>
									<i class="fas fa-recycle" onClick="document.location.href='?inc=certificate_add_edit&act=reissue&crtNr=<?php echo $row['crtNr']; ?>&clid=<?php echo $row['clid']; ?>&offid=<?php echo $row['offid']; ?>'" title="Reissue Certificate"></i>
								<?php }; */ ?>
							<?php } ?>
							<?php if ($user_type != 'hqc_office') { ?>
								<i class="fas fa-paperclip load_popup" id="qr_<?php echo $row['crtNr']; ?>" data-url="/iidc/certificates/annual/certificate_save.php?crtNr=<?php echo $row['crtNr']; ?>&act=internalMemo" title="Internal memo" style="color:<?php echo $qr_color; ?>"></i>
							<?php }; ?>

							<img src="/iidc/images/edit.gif" onClick="document.location.href='/iidc/certificates/annual/?inc=certificate_add_edit&act=edit&crtNr=<?php echo $row['crtNr']; ?>&clid=<?php echo $row['clid']; ?>&offid=<?php echo $row['offid']; ?><?php echo (isset($options['verid'])) ? '&verid=' . $options['verid'] : ''; ?><?php echo (isset($options['stid'])) ? '&stid=' . $options['stid'] : ''; ?>'" title="Edit Certificate" />

							<img src="/iidc/images/delete.gif" onClick="deleteCert('<?php echo $row['crtNr']; ?>')" title="Delete Certificate" />
						</td>
					<?php }; ?>
				</tr>
			<?php }; ?>
<?php }
	}
	if (isset($_POST['exportExcel']) && $_POST['exportExcel'] == 'yes') {
		//add the header to the items array
		array_unshift($items, array('Certificate Number', 'Company/Country/city', 'Issue/Expiry Date', 'Requested by'));
		echo json_encode($items);
		exit();
	}
} elseif ($_POST['st'] == 0) {
	echo "Error: No certificates found";
} else {
	echo "Error: No more certificates found";
}
return;
$certificates = ob_get_contents();
ob_end_clean();
if (trim($certificates) != '') {
	echo $certificates;
} else {
	echo "Error:No certificates found";
}
?>