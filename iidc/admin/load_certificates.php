<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

$table = "certificates_" . $_REQUEST['tp'];

// Set default values for required parameters
$_REQUEST['year'] = isset($_REQUEST['year']) ? $_REQUEST['year'] : date('Y');
$_REQUEST['orderBy'] = isset($_REQUEST['orderBy']) ? $_REQUEST['orderBy'] : 'nr';
$_REQUEST['ascDsc'] = isset($_REQUEST['ascDsc']) ? $_REQUEST['ascDsc'] : 'DESC';
$_POST['st'] = isset($_POST['st']) ? intval($_POST['st']) : 0;
$_POST['lmt'] = isset($_POST['lmt']) ? intval($_POST['lmt']) : 50;

$whr_office = '';
$office = array();
if (isset($_REQUEST['offid']) && $_REQUEST['offid'] != '*' && $_REQUEST['offid'] != '') {
	$whr_office = "AND (companies.offid = '$_REQUEST[offid]' OR $table.tmplid = '$_REQUEST[offid]')";
}
if ($offices = $amdb->get_results("SELECT office_name,offid From offices")) {
	foreach ($offices as $off) {
		$office[$off['offid']] = $off['office_name'];
	}
}

if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'load_countries') {
	echo "<option value=''>Select importer by country</option>";
	$country = array();
	$countriesList = "SELECT companies.country1,$table.importer,$table.offid FROM $table
			JOIN companies ON companies.clid = $table.importer
			where $table.date like '%$_REQUEST[year]%' $whr_office GROUP BY $table.importer";
	if ($countries = $amdb->get_results($countriesList)) {
		foreach ($countries as $cont) {
			if (trim($cont['country1']) != '' && !in_array($cont['country1'], $country)) {
				$country[] = trim($cont['country1']);
			}
		};
	}
	sort($country);
	if (count($country) > 0) {
		foreach ($country as $cont) {
			echo '<option value="' . trim($cont) . '">' . trim($cont) . '</option>';
		}
	}
	exit();
}

// Build search/filter conditions
if (isset($_REQUEST['srearchQ']) && trim($_REQUEST['srearchQ']) != "") {
	$whr = "AND $_REQUEST[searchField] like '%$_REQUEST[srearchQ]%'";
} else {
	$whr = "";
}

// Handle filter by status if provided
if (isset($_REQUEST['filterStatus']) && trim($_REQUEST['filterStatus']) != "" && $_REQUEST['filterStatus'] != 'all') {
	if ($_REQUEST['filterStatus'] == 'printed') {
		$whr .= " AND $table.hcd_process != ''";
	} elseif ($_REQUEST['filterStatus'] == 'pending') {
		$whr .= " AND ($table.hcd_process = '' OR $table.hcd_process IS NULL)";
	} elseif ($_REQUEST['filterStatus'] == 'requested') {
		$whr .= " AND $table.status = 'requested'";
	}
}

// Order by handling
if ($_REQUEST['orderBy'] == 'company')
	$orderBy = "companies.company_name";
elseif ($_REQUEST['orderBy'] == 'issue_date')
	$orderBy = "STR_TO_DATE($table.issue_date, '%d/%m/%Y')";
elseif ($_REQUEST['orderBy'] == 'printed_on')
	$orderBy = "$table.printed_on";
elseif ($_REQUEST['orderBy'] == 'inserted_on')
	$orderBy = "$table.inserted_on";
elseif ($_REQUEST['orderBy'] == 'certificate_nr')
	$orderBy = "$table.certificate_nr";
elseif ($_REQUEST['orderBy'] == 'nr')
	$orderBy = "$table.nr";
else
	$orderBy = "$table.nr";

// Importer filter by country
$impwhr = '';
if (isset($_REQUEST['country']) && trim($_REQUEST['country']) != "") {
	$impwhr = "AND companies.country1 like '%$_REQUEST[country]%'";
}
$importers = array();

$importersList = "SELECT companies.company_name, companies.country1, $table.importer, $table.offid 
		FROM $table
		LEFT JOIN companies ON companies.clid = $table.importer
		WHERE ($table.date LIKE '%$_REQUEST[year]%' OR YEAR($table.inserted_on) = '$_REQUEST[year]' OR '$_REQUEST[year]' = '*') 
		$impwhr 
		AND ($table.status IS NULL OR $table.status = '' OR ($table.status != 'draft' AND $table.status != 'deleted'))
		GROUP BY $table.importer";

if ($theImporters = $amdb->get_results($importersList)) {
	foreach ($theImporters as $imp) {
		if ($imp['importer']) {
			$importers[$imp['importer']] = array(
				'company' => $imp['company_name'] ?? 'Unknown', 
				'country' => $imp['country1'] ?? ''
			);
		}
	}
}
if (isset($_REQUEST['country']) && trim($_REQUEST['country']) != "" && count($importers) > 0) {
	$whr .= " AND FIND_IN_SET($table.importer,'" . implode(',', array_keys($importers)) . "')";
}

// Year/date range filter
if ($_REQUEST['year'] == 'd2d' && isset($_REQUEST['fromDate']) && isset($_REQUEST['toDate'])) {
	if (strtotime($_REQUEST['fromDate']) > strtotime($_REQUEST['toDate'])) {
		$fromDate = $_REQUEST['toDate'];
		$toDate = $_REQUEST['fromDate'];
		$_REQUEST['ascDsc'] = 'DESC';
	} else {
		$fromDate = $_REQUEST['fromDate'];
		$toDate = $_REQUEST['toDate'];
		$_REQUEST['ascDsc'] = 'ASC';
	}
	$whr_year = "($table.inserted_on BETWEEN '$fromDate' AND '$toDate')";
} elseif ($_REQUEST['year'] == '*' || $_REQUEST['year'] == 'all' || empty($_REQUEST['year'])) {
	$whr_year = "1=1";
} else {
	$whr_year = "(
		YEAR($table.inserted_on) = '{$_REQUEST['year']}' 
		OR $table.date LIKE '%{$_REQUEST['year']}%' 
		OR $table.inserted_on IS NULL
	)";
}

// Pagination
if (isset($_POST['exportExcel']) && $_POST['exportExcel'] == 'yes') {
	$limit = '';
} else {
	$limit = "LIMIT $_POST[st], $_POST[lmt]";
}
$no = $_POST['st'];
$items = array();

// Main query
$sql = "SELECT $table.*, companies.offid as clOffid, companies.company_name,
		CONCAT(companies.contact_title1, ' ', companies.contact_name1, ' ', companies.contact_surname1) AS contact_person 
		FROM $table
		LEFT JOIN companies ON companies.clid = $table.clid
		WHERE $whr_year $whr_office $whr 
 		ORDER BY $orderBy {$_REQUEST['ascDsc']} $limit";

if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == '1') {
	echo "<!-- DEBUG: $sql -->";
}

if ($result = $amdb->get_results($sql)) {
	foreach ($result as $row) {
		if (isset($_POST['exportExcel']) && $_POST['exportExcel'] == 'yes') {
			$items[] = array(
				$row['certificate_nr'],
				$row['doc_nr'],
				$row['issue_date'],
				$row['weight_net'],
				isset($importers[$row['importer']]) ? $importers[$row['importer']]['company'] . " / " . $importers[$row['importer']]['country'] : '',
				$row['company_name']
			);
		} else {
			$no++;
			
			// Determine certificate status
			$isPending = empty($row['hcd_process']) || $row['status'] == 'pending' || $row['status'] == 'requested';
			$isPrinted = !empty($row['hcd_process']) || $row['status'] == 'printed';
			$isRequested = $row['status'] == 'requested';
			?>
			<tr data-nr="<?php echo $row['nr']; ?>">
				<th data-sNr="<?php echo $no; ?>" class="aunr"><?php echo $no; ?></th>
				<td data-id="certificate_nr" class="nowrap">
					<?php if ($isPending) { ?>
						<span class="label <?php echo $isRequested ? 'label-info' : 'label-warning'; ?>" style="display:inline-block; margin-bottom:4px;">
							<i class="fas <?php echo $isRequested ? 'fa-paper-plane' : 'fa-clock'; ?>"></i> 
							<?php echo $isRequested ? 'Requested' : 'Pending'; ?>
						</span><br>
					<?php } ?>
					
					<?php
					$certificate_url = "/iidc/client_data/certificates/" . $row['url'];
					if (!empty($row['certificate_nr'])) {
						if (file_exists($hcp_path . $certificate_url) && !empty($row['url'])) { ?>
							<a href="<?php echo $certificate_url; ?>" target="_blank"><?php echo htmlspecialchars($row['certificate_nr']); ?></a>
						<?php } else { ?>
							<a href="/iidc/certificates/pdf_certificate.php?tp=<?php echo $_REQUEST['tp']; ?>&nr=<?php echo $row['nr']; ?>&usr=a&ver=<?php echo $row['version']; ?>" target="_blank"><?php echo htmlspecialchars($row['certificate_nr']); ?></a>
						<?php }
					} else { ?>
						<span style="color:#999; font-style:italic;">No certificate number</span>
					<?php } ?>
					
					<?php if ($row['clOffid'] != $row['tmplid']) { ?>
						<br><i class="fas fa-caret-square-left" style="color:orange; font-size:14px;" title="Client of"></i>
						<?php if ($_SESSION['user_type'] == 'admin') { ?>
							<a href="/iidc/admin/?inc=certificates&tp=<?php echo $_REQUEST['tp']; ?>&offid=<?php echo $row['clOffid']; ?>"><?php echo isset($office[$row['offid']]) ? htmlspecialchars($office[$row['offid']]) : ''; ?></a>
						<?php } else { ?>
							<?php echo isset($office[$row['clOffid']]) ? htmlspecialchars($office[$row['clOffid']]) : ''; ?>
						<?php } ?>
					<?php } ?>
					
					<?php if ($row['offid'] != $row['tmplid'] || $_REQUEST['offid'] == '*') { ?>
						<br><i class="fas fa-caret-square-right" style="color:green; font-size:14px;" title="Issued by"></i>
						<?php if ($_SESSION['user_type'] == 'admin') { ?>
							<a href="/iidc/admin/?inc=certificates&tp=<?php echo $_REQUEST['tp']; ?>&offid=<?php echo $row['tmplid']; ?>"><?php echo isset($office[$row['tmplid']]) ? htmlspecialchars($office[$row['tmplid']]) : ''; ?></a>
						<?php } else { ?>
							<?php echo isset($office[$row['tmplid']]) ? htmlspecialchars($office[$row['tmplid']]) : ''; ?>
						<?php } ?>
					<?php } ?>
				</td>
				<td data-id="issue_date"><?php echo htmlspecialchars($row['issue_date'] ?? ''); ?></td>
				<td><?php echo is_numeric($row['weight_net']) ? number_format($row['weight_net'], 2) : htmlspecialchars($row['weight_net'] ?? ''); ?> KG</td>
				<td data-id="importer">
					<?php 
					if (isset($importers[$row['importer']])) {
						$importer = $importers[$row['importer']];
						echo htmlspecialchars($importer['company']) . '<br>';
						echo '<span style="color:#5cb85c;">' . htmlspecialchars($importer['country']) . '</span>';
					}
					?>
				</td>
				<td data-id="company_name"><?php echo htmlspecialchars($row['company_name'] ?? ''); ?></td>
				<td data-id="reference" style="max-width:160px;">
					<?php echo str_replace(array("+", "  +", "/", "  /  "), array(" +", " +", " / ", " / "), htmlspecialchars($row['reference'] ?? '')); ?>
				</td>
				<td data-id="status" class="status">
					<?php if (!empty($row['hcd_process'])) { ?>
						<span>
							<?php echo '<b>' . str_replace(array('on: ', 'Authorised: '), array('on:</b> ', 'Authorised on:</b> '), str_replace('/', '.', $row['hcd_process'])); ?>
						</span>
					<?php } else { ?>
						<span style="color:#999;">
							<i class="fas fa-hourglass-half"></i> Awaiting print
						</span>
					<?php } ?>
					
					<?php if (!empty($row['hcd_process']) && strstr($row['hcd_process'], 'Authorised') && trim($row['printed_on']) != '') { ?>
						<span><b>Printed on:</b> <?php echo str_replace('/', '.', $row['printed_on']); ?></span>
					<?php } elseif (!empty($row['hcd_process']) && strstr($row['hcd_process'], 'Sent') && trim($row['arrived_on']) != '') { ?>
						<span><b>Arrived on:</b> <?php echo str_replace('/', '.', $row['arrived_on']); ?></span>
					<?php } elseif (!empty($row['hcd_process']) && strstr($row['hcd_process'], 'Printed')) { ?>
						<span id="sendCertSpan_<?php echo $row['nr']; ?>">
							<input type="button" class="btn btn-xs btn-default" value="Sent on" onclick="load_html('certificates_save.php?act=sendCertificate&nr=<?php echo $row['nr']; ?>&tp=<?php echo $_REQUEST['tp']; ?>','#sendCertSpan_<?php echo $row['nr']; ?>')" />
						</span>
					<?php } elseif (!empty($row['hcd_process']) && strstr($row['hcd_process'], 'Sent') && trim($row['arrived_on']) == '') {
						$sentOn = fix_date(str_replace('Sent on: ', '', $row['hcd_process']));
						if ($row['clid'] != '1' && strtotime('+2 days', strtotime($sentOn)) < time()) {
							$arrived_on = date("d.m.Y", strtotime('+2 days', strtotime($sentOn)));
							$amdb->query("UPDATE certificates_{$_REQUEST['tp']} SET arrived_on='$arrived_on', done='y' WHERE nr = '{$row['nr']}'"); ?>
							<span><b>Arrived on:</b> <?php echo str_replace('/', '.', $arrived_on); ?></span>
						<?php } else { ?>
							<span id="recievedCertSpan_<?php echo $row['nr']; ?>">
								<input type="button" class="btn btn-xs btn-default" value="Received on" onclick="load_html('certificates_save.php?act=recievedCertificate&nr=<?php echo $row['nr']; ?>&tp=<?php echo $_REQUEST['tp']; ?>','#recievedCertSpan_<?php echo $row['nr']; ?>')" />
							</span>
						<?php }
					} ?>
					
					<?php
					// Requested by info
					if (!empty($row['requested_by']) && is_array(json_decode($row['requested_by'], true))) {
						$request_by = json_decode($row['requested_by'], true);
						if (isset($request_by['clid']) && $request_by['clid'] != '')
							$request_by['name'] = $row['contact_person'] ?? '';
						if (!empty($request_by['name'])) {
							echo '<div style="font-size:11px; color:#666; margin-top:4px;" title="Requested By"><b>RB:</b> ' . htmlspecialchars($request_by['name']) . "</div>";
						}
					}
					// Handled by info
					if (!empty($row['handled_by']) && is_array(json_decode($row['handled_by'], true))) {
						$handled_by = json_decode($row['handled_by'], true);
						if (!empty($handled_by['name'])) {
							echo '<div style="font-size:11px; color:#666;" title="Handled By"><b>HB:</b> ' . htmlspecialchars($handled_by['name']) . "</div>";
						}
					}
					?>
				</td>
				<?php if ($_SESSION['user_type'] == "admin" || $_SESSION['user_type'] == 'hqc_office') { ?>
					<td style="white-space:nowrap;">
						<a href="/iidc/certificates/?inc=certificate_ab&tp=<?php echo $_REQUEST['tp']; ?>&nr=<?php echo $row['nr']; ?>&act=edit&clid=<?php echo $row['clid']; ?>&offid=<?php echo $_REQUEST['offid']; ?>" title="Edit Certificate" style="margin-right:8px;">
							<i class="far fa-edit" style="color:#337ab7;"></i>
						</a>
						<?php
						if (!empty($row['hcd_process'])) {
							$qr_color = '#5cb85c';
							if (!empty($row['qr_status']) && is_array(json_decode($row['qr_status'], true))) {
								$qr_status = json_decode($row['qr_status'], true);
								if (isset($qr_status['status']) && $qr_status['status'] != 'active') {
									$qr_color = ($qr_status['status'] == 'paused') ? '#f0ad4e' : '#d9534f';
								}
							}
							?>
							<i class="fas fa-qrcode load_popup" id="qr_<?php echo $row['nr']; ?>" data-url="/iidc/certificates/shared/qr/qr.php?crtNr=<?php echo $row['nr']; ?>&tp=<?php echo $_REQUEST['tp']; ?>" title="QR Certificate Status" style="color:<?php echo $qr_color; ?>; margin-right:8px; cursor:pointer;"></i>
						<?php } ?>
						<i class="far fa-trash-alt" onclick="delcer('<?php echo $row['nr']; ?>','<?php echo $_REQUEST['tp']; ?>')" title="Delete Certificate" style="color:#d9534f; cursor:pointer;"></i>
						
						<?php 
						// Show attachments
						if (!empty($row['attachments']) && is_array(json_decode($row['attachments'], true))) {
							$attachments = json_decode($row['attachments'], true);
							if (count($attachments) > 0) { ?>
								<fieldset style="background:#f5f5f5; border:1px solid #ddd; border-radius:4px; margin-top:8px; padding:8px;">
									<legend style="font-size:11px; font-weight:600; color:#666; padding:0 6px; width:auto; margin-bottom:4px; border:none;">
										<i class="fas fa-paperclip"></i> Attachments
									</legend>
									<ol style="padding:0 0 0 18px; margin:0; font-size:12px; list-style-type:decimal;">
									<?php foreach ($attachments as $attachment) { ?>
										<li style="max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; padding:2px 0;">
											<a href="/iidc/<?php echo htmlspecialchars($attachment); ?>" target="_blank" title="<?php echo htmlspecialchars(basename($attachment)); ?>" style="color:#337ab7;"><?php echo htmlspecialchars(basename($attachment)); ?></a>
										</li>
									<?php } ?>
									</ol>
								</fieldset>
							<?php }
						}
						?>
					</td>
				<?php } ?>
			</tr>
			<?php
		}
	}
	
	// Handle Excel export
	if (isset($_POST['exportExcel']) && $_POST['exportExcel'] == 'yes') {
		array_unshift($items, array('Certificate Number', 'Doc Nr', 'Issue date', 'Weight', 'Importer/Country', 'Company'));
		echo json_encode($items);
		exit();
	}
} elseif ($_POST['st'] == 0) {
	?>
	<tr>
		<td colspan="9" style="text-align:center; padding:40px; color:#999;">
			<i class="fas fa-inbox" style="font-size:48px; color:#ddd; display:block; margin-bottom:16px;"></i>
			<strong style="display:block; font-size:16px; margin-bottom:8px; color:#666;">No certificates found</strong>
			<span style="font-size:13px;">Try adjusting your filters or create a new certificate</span>
		</td>
	</tr>
	<?php
}