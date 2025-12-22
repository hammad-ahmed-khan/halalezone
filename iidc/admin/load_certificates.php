<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
//TODO: update new system
$table = "certificates_" . $_REQUEST['tp'];

$whr_office  = '';
$office = array();
if (isset($_REQUEST['offid']) && $_REQUEST['offid'] != '*') {
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
if (isset($_REQUEST['srearchQ']) and trim($_REQUEST['srearchQ']) != "") {
	$whr = "and $_REQUEST[searchField] like '%$_REQUEST[srearchQ]%'";
} else {
	$whr = "and hcd_process!=''";
}

if ($_REQUEST['orderBy'] == 'company')
	$orderBy = "companies";
elseif ($_REQUEST['orderBy'] == 'issue_date')
	$orderBy = "STR_TO_DATE(issue_date, '%d/%m/%Y')";
elseif ($_REQUEST['orderBy'] == 'printed_on')
	$orderBy = "STR_TO_DATE(printed_on, '%d/%m/%Y')";
else
	$orderBy = $table;

if ($_REQUEST['orderBy'] != 'printed_on' && $_REQUEST['orderBy'] != 'issue_date' && $_REQUEST['orderBy'] != 'company')
	$orderBy = $orderBy . '.' . $_REQUEST['orderBy'];

$impwhr = '';
if (isset($_REQUEST['country']) and trim($_REQUEST['country']) != "") {
	$impwhr = "and companies.country1 like '%$_REQUEST[country]%'";
}
$importers = array();

$importersList = "SELECT companies.company_name,companies.country1,$table.importer,$table.offid FROM $table
		JOIN companies ON companies.clid = $table.importer
		where $table.date like '%$_REQUEST[year]%' $impwhr AND $table.status !='draft' AND $table.status !='deleted' GROUP BY $table.importer";

if ($theImporters = $amdb->get_results($importersList)) {
	foreach ($theImporters as $imp) {
		$importers[$imp['importer']] = array('company' => $imp['company_name'], 'country' => $imp['country1']);
	};
}
if (isset($_REQUEST['country']) and trim($_REQUEST['country']) != "" and count($importers) > 0) {
	$whr .= " AND FIND_IN_SET($table.importer,'" . implode(',', array_keys($importers)) . "')";
}

if ($_REQUEST['year'] == 'd2d') {
	//check if todate is smaller than fromdate
	if (strtotime($_REQUEST['fromDate']) > strtotime($_REQUEST['toDate'])) {
		$fromDate = $_REQUEST['toDate'];
		$toDate = $_REQUEST['fromDate'];
		$_REQUEST['ascDsc'] = 'DESC';
	} else {
		$fromDate = $_REQUEST['fromDate'];
		$toDate = $_REQUEST['toDate'];
		$_REQUEST['ascDsc'] = 'ASC';
	}
	$whr_year = "$table.inserted_on between '$fromDate' and '$toDate'";
} else {
	$whr_year = "YEAR($table.inserted_on) = '$_REQUEST[year]'";
}

if (isset($_POST['exportExcel']) && $_POST['exportExcel'] == 'yes') {
	$limit = '';
} else {
	$limit = "limit $_POST[st],$_POST[lmt]";
}
$no = $_POST['st'];
$items = array();

$sql = "SELECT *,companies.offid as clOffid, CONCAT(companies.contact_title1,' ',companies.contact_name1,' ',companies.contact_surname1) AS contact_person FROM $table
		JOIN companies ON companies.clid = $table.clid
		where  $whr_year $whr_office $whr AND $table.status !='draft' AND $table.status !='deleted' ORDER BY $orderBy {$_REQUEST['ascDsc']} $limit";
if ($result = $amdb->get_results($sql)) {
	foreach ($result as $row) {
		if ($_POST['exportExcel'] == 'yes') {
			$items[] = array(
				$row['certificate_nr'],
				$row['doc_nr'],
				$row['issue_date'],
				$row['weight_net'],
				$importers[$row['importer']]['company'] . " / " . $importers[$row['importer']]['country'],
				$row['company_name']
			);
		} else {
			$no++;
?>
			<tr data-nr='<?php echo $row['nr']; ?>'>
				<th data-sNr="<?php echo $no; ?>" class="aunr"><?php echo $no; ?></th>
				<td data-id="certificate_nr" class="nowrap">
					<?php
					$certificate_url = "/client_data/certificates/" . $row['url'];
					if (file_exists($hcp_path . $certificate_url)) { ?>
						<a href='<?php echo $certificate_url; ?>' target=_blank><?php echo $row['certificate_nr']; ?></a>
					<?php } else { ?>
						<a href='/certificates/pdf_certificate.php?tp=<?php echo $_REQUEST['tp']; ?>&nr=<?php echo $row['nr']; ?>&usr=a&ver=<?php echo $row['version']; ?>' target=_blank><?php echo $row['certificate_nr']; ?></a>
					<?php }; ?>
					<?php if ($row['clOffid'] != $row['tmplid']) { ?>
						<br /><i class="fas fa-caret-square-left" style="color:orange;font-size:14px !important" title="Client of"></i>
						<?php if ($_SESSION['user_type'] == 'admin') { ?>
							<a href="/admin/?inc=certificates&tp=<?php echo $_REQUEST['tp']; ?>&offid=<?php echo $row['clOffid']; ?>"><?php echo $office[$row['offid']]; ?></a>
						<?php } else { ?>
							<?php echo $office[$row['clOffid']]; ?>
						<?php }; ?>
					<?php }; ?>
					<?php if ($row['offid'] != $row['tmplid'] or $_REQUEST['offid'] == '*') { ?>
						<br /><i class="fas fa-caret-square-right" style="color:green;font-size:14px !important" title="Issued by"></i>
						<?php if ($_SESSION['user_type'] == 'admin') { ?>
							<a href="/admin/?inc=certificates&tp=<?php echo $_REQUEST['tp']; ?>&offid=<?php echo $row['tmplid']; ?>"><?php echo $office[$row['tmplid']]; ?></a>
						<?php } else { ?>
							<?php echo $office[$row['tmplid']]; ?>
						<?php }; ?>
					<?php }; ?>
				</td>
				<td data-id="issue_date"><?php echo $row['issue_date']; ?></td>
				<td><?php echo is_int($row['weight_net']) ? number_format($row['weight_net'], 2) : $row['weight_net']; ?> KG</td>
				<td data-id="importer"><?php if (isset($importers[$row['importer']])) {
											$importer = $importers[$row['importer']];
											echo $importer['company'] . '<br/>';
											echo '<span style="color:green">' . $importer['country'] . '</span>';
										}; ?></td>
				<td data-id="company_name"><?php echo $row['company_name']; ?></td>
				<td data-id="reference" style="max-width: 160px;"><?php echo str_replace(array("+", "  +", "/", "  /  "), array(" +", " +", " / ", " / "), $row['reference']); ?></td>
				<td data-id="status" class=" status">
					<span>
						<?php echo '<b>' . str_replace(array('on: ', 'Authorised: '), array('on:</b>', 'Authorised on:</b>'), str_replace('/','.',$row['hcd_process'])); ?>
					</span>
					<?php if (strstr($row['hcd_process'], 'Authorised') and trim($row['printed_on']) != '') { ?>
						<span><b>Printed on:</b><?php echo str_replace('/','.', $row['printed_on']); ?></span>
					<?php } elseif (strstr($row['hcd_process'], 'Sent') and trim($row['arrived_on']) != '') { ?>
						<span><b>Arrived on:</b><?php echo str_replace('/','.', $row['arrived_on']); ?></span>
					<?php } elseif (strstr($row['hcd_process'], 'Printed')) { ?>
						<span id="sendCertSpan_<?php echo $row['nr']; ?>">
							<input type="button" value="Sent on" onclick="load_html('certificates_save.php?act=sendCertificate&nr=<?php echo $row['nr']; ?>&tp=<?php echo $_REQUEST['tp']; ?>','#sendCertSpan_<?php echo $row['nr']; ?>')" />
						</span>
						<?php } elseif (strstr($row['hcd_process'], 'Sent') and trim($row['arrived_on']) == '') {
						$sentOn = fix_date(str_replace('Sent on: ', '', $row['hcd_process']));
						if ($row['clid'] != '1' and strtotime('+2 days', strtotime($sentOn)) < time()) {
							$arrived_on = date("d.m.Y", strtotime('+2 days', strtotime($sentOn)));
							$amdb->query("update certificates_{$_POST['tp']} set arrived_on='$arrived_on', done='y' where  nr = '$row[nr]'"); ?>
							<span><b>Arrived on:</b><?php echo str_replace('/','.', $arrived_on); ?></span>
						<?php
						} else {
						?>
							<span id="recievedCertSpan_<?php echo $row['nr']; ?>">
								<input type="button" value="Received on" onclick="load_html('certificates_save.php?act=recievedCertificate&nr=<?php echo $row['nr']; ?>&tp=<?php echo $_REQUEST['tp']; ?>','#recievedCertSpan_<?php echo $row['nr']; ?>')" />
							</span>
					<?php };
					}; ?>
					<?php
					if (trim($row['requested_by']) != '' && is_array(json_decode($row['requested_by'], true))) {
						$request_by = json_decode($row['requested_by'], true);
						if (isset($request_by['clid']) && $request_by['clid'] != '')
							$request_by['name'] = $row['contact_person'];
						echo '<div title="Requested By"><b>RB:</b> ' . $request_by['name'] . "</diV>";
					};
					if (trim($row['handled_by']) != '' && is_array(json_decode($row['handled_by'], true))) {
						$handled_by = json_decode($row['handled_by'], true);
						echo '<div title="Handled By"><b>HB:</b> ' . $handled_by['name']  . "</diV>";
					};
					?>
				</td>
				<?php
				if ((isset($user_permissions) && in_array("certificates_actions", $user_permissions)) or $_SESSION['user_type'] == "admin" or $_SESSION['user_type'] == 'hqc_office') { ?>
					<td style="white-space:nowrap !important">
						<a href="/certificates/?inc=certificate_ab&tp=<?php echo $_REQUEST['tp']; ?>&nr=<?php echo $row['nr']; ?>&act=edit&clid=<?php echo $row['clid']; ?>" title="Edit Certificate">
							<i class="far fa-edit"></i></a>
					<?php }
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
					}
					?>
						<i class="fas fa-qrcode load_popup" id="qr_<?php echo $row['nr']; ?>" data-url="/certificates/shared/qr/qr.php?crtNr=<?php echo $row['nr']; ?>&tp=<?php echo $_REQUEST['tp']; ?>" title="QR Certificate Status" style="color:<?php echo $qr_color; ?>"></i>
						<i class="far fa-trash-alt" onclick="delcer('<?php echo $row['nr']; ?>','<?php echo $_REQUEST['tp']; ?>')"></i>
						<?php if (trim($row['attachments']) != '' && is_array(json_decode($row['attachments'], true))) {
							$attachments = decode_json($row['attachments']);
							if (count($attachments) > 0) {
								echo "<fieldset style='background: whitesmoke;border:1px solid grey;margin-top:10px'><legend>Attached Documents</legend><ol style='padding:0px;margin:0px' class='alternateOff'>";
								foreach ($attachments as $attachment) { ?>
									<li style="max-width: 100px; overflow: hidden;"><a href='<?php echo $attachment; ?>' target='_blank'><?php echo basename($attachment); ?></a>
							<?php };
								echo "</ol></fieldset>";
							}
						}; ?>
					</td>
				<?php }; ?>
			</tr>
<?php };
	};
	if (isset($_POST['exportExcel']) && $_POST['exportExcel'] == 'yes') {
		//add the header to the items array
		array_unshift($items, array('Certificate Number', 'Issue date', 'Weight', 'Importer/Country', 'Company'));
		echo json_encode($items);
		exit();
	}
} elseif ($_POST['st'] == 0) {
	echo "Error: No certificates found";
}
return;
