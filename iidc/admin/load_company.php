<?php
include "../config/paths.inc.php";
$row = $amdb->get_row("SELECT * FROM companies
						JOIN users ON companies.clid=users.clid
						WHERE users.clid = $_REQUEST[clid]");
$certificate = array();
$certificate = $amdb->get_row("SELECT * FROM acms_halal_certificates WHERE clid=$_REQUEST[clid] ORDER BY date_of_expiry DESC");
$office = $amdb->get_row("SELECT * FROM offices WHERE offid = $row[offid]");
?>
<script>
	jQuery(document).ready(function() {
		jQuery("#payment_term").on('click', function() {
			document.getElementById('savePaymentDisk').style.display = 'inline-block';
		});
		jQuery("#savePaymentDisk").on('click', function() {
			updatePaymentTerm();
		});
	});

	function updatePaymentTerm() {
		var payment_term = jQuery("#payment_term").val();
		jQuery.ajax({
			type: "POST",
			url: "/invoices/reminders/reminder_save.php",
			data: {
				'clid': '<?php echo $_REQUEST['clid']; ?>',
				'first_reminder': payment_term,
				'act': 'update_payment_term'
			},
			success: function(data) {
				if (data == '1') {
					alert_message('Payment term updated!');
					jQuery("#savePaymentDisk").hide();
				} else {
					alert_message('Error updating payment term!');
				}
			}
		});
	}
</script>
<table width="850" style="border:0px !important;padding:10px;">
	<tr>
		<td width="50%">
			<div class="prevTtl">ID</div>
			<div class="prevVal"><?php echo $office['reference_prefix']; ?><?php echo $office['certificate_prefix']; ?><?php echo str_pad($_REQUEST['clid'], 6, '0', STR_PAD_LEFT); ?></div>
			<div class="prevTtl">Contact</div>
			<div class="prevVal contact"> <?php echo $row['contact_title1'] . " " . $row['contact_name1'] . " " . $row['contact_surname1']; ?></div>
			<?php if (trim($row['function1']) != "") { ?>
				<div class="prevTtl">Function</div>
				<div class="prevVal"> <?php echo $row['function1']; ?></div>
			<?php };
			if (trim($row['street1']) != "") { ?>
				<div class="prevTtl">Address</div>
				<div class="prevVal"> <?php echo "$row[street1]<br/>$row[zip1] $row[city1]<br/>$row[country1]"; ?></div>
			<?php };
			if (trim($row['tel1']) != "") { ?>
				<div class="prevTtl">Telephone</div>
				<div class="prevVal"> <?php echo $row['tel1']; ?></div>
			<?php };
			if (trim($row['mobile']) != "") { ?>
				<div class="prevTtl">Mobile</div>
				<div class="prevVal"> <?php echo $row['mobile']; ?></div>
			<?php };
			if (trim($row['email1']) != "") { ?>
				<div class="prevTtl">Email</div>
				<div class="prevVal"><a href="mailto:<?php echo $row['email1']; ?>"><?php echo $row['email1']; ?></a></div>
			<?php };
			if (trim($row['web']) != "") { ?>
				<div class="prevTtl">Website</div>
				<div class="prevVal"><a href="<?php echo $row['web']; ?>" target="_blank"><?php echo $row['web']; ?></a></div>
			<?php
			};
			//billing address
			if (trim($row['billing_address']) != '') {
				$BA = json_decode($row['billing_address'], true);
			?>
				<div style="margin-top:20px;font-weight:bold;margin: 20px 0 10px 0px;font-weight: bold; border-bottom: 1px solid#bbb;">BILLING ADDRESS</div>
				<?php if (trim($BA['name']) != "") { ?>
					<div class="prevTtl">Name</div>
					<div class="prevVal"> <?php echo $BA['name']; ?></div>
				<?php }; ?>
				<?php if (trim($BA['street']) != "") { ?>
					<div class="prevTtl">Address</div>
					<div class="prevVal"> <?php echo $BA['street']; ?>
						<?php if (trim($BA['zipcode']) != "")
							echo "<br>$BA[zipcode]"; ?>
						<?php if (trim($BA['city']) != "")
							echo $BA['city']; ?>
						<?php if (trim($BA['country']) != "")
							echo "<br/>$BA[country]"; ?>
					</div>
				<?php }; ?>
				<?php if (trim($BA['telephone']) != "") { ?>
					<div class="prevTtl">Telephone</div>
					<div class="prevVal"> <?php echo $BA['telephone']; ?></div>
				<?php };
				if (trim($row['email']) != "") { ?>
					<div class="prevTtl">Email</div>
					<div class="prevVal"><a href="mailto:$row[email]"><?php echo $row['email']; ?></a></div>
			<?php };
			}; ?>
			<?php if (isset($_GET['ref']) && $_GET['ref'] == 'invoice') {
				if ($user_term = $amdb->get_row("SELECT * FROM hqc_default_invoice_reminders WHERE clid = $_REQUEST[clid]")) {
					$payment_term = $user_term['first_reminder'];
				} else {
					$payment_term = 21;
				}
			?>
				<div class="prevTtl">Payment term:</div>
				<div class="prevVal"><input type="number" style="width:50px;height:24px;cursor:pointer;float:left;margin-right:10px" name="payment_term" id="payment_term" value="<?php echo $payment_term; ?>" />Days <i class="far fa-save" style="float: left;margin:2px 10px 2px 0px; color: mediumblue; display:none;" id="savePaymentDisk"></i></div>

			<?php } ?>
			<div style="margin-top:20px;">
				<div class="prevTtl">scope of activities</div>
				<div class="prevVal"> <?php echo (trim($row['scope_of_activities']) != "") ? str_replace("\n\r", "<br/>", $row['scope_of_activities']) : "NA"; ?></div>
			</div>
		</td>
		<td>
			<?php
			//second address*/
			if (trim($row['contact_name2']) != "") { ?>
				<div class="prevTtl">Contact</div>
				<div class="prevVal"><?php echo "$row[contact_title2] $row[contact_name2] $row[contact_surname2]"; ?></div>
			<?php };
			if (trim($row['function2']) != "") { ?>
				<div class="prevTtl">Function</div>
				<div class="prevVal"><?php echo $row['function2']; ?></div>
			<?php };
			if (trim($row['pobox']) != "") { ?>
				<div class="prevTtl">P.O.Box</div>
				<div class="prevVal"><?php echo "$row[pobox]<br> $row[zip2] $row[city2]<br/>$row[country2]"; ?></div>
			<?php };
			if (trim($row['tel2']) != "") { ?>
				<div class="prevTtl">Telephone2</div>
				<div class="prevVal"><?php echo $row['tel2']; ?></div>
			<?php };
			if (trim($row['email2']) != "") { ?>
				<div class="prevTtl">Email2</div>
				<div class="prevVal"><a href="mailto:$row[email2]"><?php echo $row['email2']; ?></a></div>
			<?php }; ?>
			<div style="margin-top:10px;border:1px solid #bbb;clear:left;padding:5px;overflow:auto">
				<div><b>C.O.C number: </b><?php echo $row['cocNr']; ?></div>
				<div><b>V.A.T number:</b><?php echo $row['vatNr']; ?></div>
				<div><b>EC number: </b><?php echo $row['ec_number']; ?></div>
				<div><b>Client since: </b><?php echo $row['date']; ?></div>
			</div>
			<?php if (!isset($_REQUEST['full'])) { ?>
				<?php if (isset($certificate) and is_array($certificate)) { /*print_r($certificate);*/ ?>
					<fieldset style="margin-top: 10px;background: beige;border: 1px solid #bbb;">
						<legend style="font-weight:bold">Annual certificate</legend>
						<?php if (isset($certificate['certificate_nr']) && $certificate['certificate_nr'] != '0') { ?>
							<b style="display:inline-block; width:150px;">Certificate nr:</b> <?php echo isset($certificate['certificate_nr']) ? $certificate['certificate_nr'] : 'N/A'; ?><br />
							<b style="display:inline-block; width:150px;">Date of issue:</b> <?php echo isset($certificate['date_of_issue']) ? date("d/m/Y", $certificate['date_of_issue']) : 'N/A'; ?>
							<b style="display:inline-block; width:150px;">Date of expiry:</b>
							<?php if (isset($certificate['date_of_expiry'])) { ?>
								<?php echo date("d/m/Y", $certificate['date_of_expiry']); ?> <?php echo (isset($certificate['date_of_expiry']) and $certificate['date_of_expiry'] < time()) ? '<span style="color:red">Expired</span>' : '<span style="color:green">Expires in ' . round(($certificate['date_of_expiry'] - time()) / 86400) . ' days'; ?>
							<?php } else {
								echo 'N/A';
							}; ?>
						<?php } else {
							echo "N/A";
						}; ?>
					</fieldset>
				<?php }; ?>
				<fieldset style="margin-top: 10px;background: honeydew;border: 1px solid #bbb;">
					<legend style="font-weight:bold">HQC Office</legend>
					<b style="display:inline-block; width:120px;">Office:</b> <?php echo $office['office_name']; ?><br />
					<b style="display:inline-block; width:120px;">Contact person:</b> <?php echo trim($row['hqc_contact_person']) != '' ? '<span style="color:green">' . $row['hqc_contact_person'] . '</span>' : 'N/A'; ?>
				</fieldset>
				<?php
				if (isset($_REQUEST['login'])) {
					$logAsClient = '<input type="button" title="as (' . $row['username'] . ')" value="Log-in as client" onclick="doLogInasCl(\'' . $row['username'] . '\',\'' . $row['password'] . '\')"/>';
				?>
					<div style="margin-top:10px;border:1px solid #bbb;clear:left;padding:5px;background:#EEE;overflow:auto;text-align:center">
						<?php echo $logAsClient; ?>
						<?php /*<div class="prevTtl">Username</div>
<div class="prevVal" style="overflow: initial;"><?php echo "$logAsClient$row[username]";?></div>
<div class="prevTtl">Password</div><div class="prevVal" style="overflow: initial;"><?php echo $row['password'];?></div>*/ ?>
					</div>
					<?php if ($row['last_loged_in'] != 0) { ?>
						<i><b>Last loged in:</b> <?php echo date("d/m/Y h:i:s", $row['last_loged_in']); ?></i>
					<?php }; ?>
				<?php }; ?>
			<?php }; ?>
		</td>
	</tr>
</table>