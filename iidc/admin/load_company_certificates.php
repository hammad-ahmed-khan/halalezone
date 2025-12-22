<?php
include "../config/paths.inc.php";
$row = $amdb->get_row("SELECT * FROM companies
						JOIN users ON companies.clid=users.clid
						WHERE users.clid = $_REQUEST[clid]");

$certificate = array();
$certFilesDir = $hcp_path . "/client_data/certificates";
$certificate = $amdb->get_row("SELECT * FROM acms_halal_certificates WHERE clid=$_REQUEST[clid] ORDER BY date_of_expiry DESC");
$office = $amdb->get_row("SELECT * FROM offices WHERE offid = $row[offid]");
?>
<table width="500" style="border:0px !important;padding:10px;">
	<tr>
		<td>
			<?php if (is_array($certificate)) { /*print_r($certificate);*/ ?>
				<fieldset style="margin-top: 10px;background: beige;border: 1px solid #bbb;">
					<legend style="font-weight:bold">Annual certificate</legend>
					<b style="display:inline-block; width:100px;">Certificate nr:</b>
					<?php if (isset($certificate['certificate_nr'])) { ?>
						<?php if (trim($certificate['url']) != '' && file_exists($certFilesDir . '/' . $certificate['url'])) { ?>
							<a target="_new" href="<?php echo $prog_www; ?>/client_data/certificates/<?php echo $certificate['url']; ?>?act=print"><i class="far fa-file-pdf"></i><?php echo $certificate['certificate_nr']; ?></a>
						<?php } else { ?>
							<?php echo $certificate['certificate_nr']; ?>
						<?php }; ?>
						<?php } else { ?>N/A<?php }; ?>
						<br />
						<b style="display:inline-block; width:100px;margin-top:10px;">Date of issue:</b> <?php echo isset($certificate['date_of_issue']) ? date("d/m/Y", $certificate['date_of_issue']) : 'N/A'; ?><br />
						<b style="display:inline-block; width:100px;margin-top:10px;">Date of expiry:</b>
						<?php if (isset($certificate['date_of_expiry'])) { ?>
							<?php echo date("d/m/Y", $certificate['date_of_expiry']); ?>
							<?php if (isset($certificate['date_of_expiry'])) {
								$daysToGo = ceil(($certificate['date_of_expiry'] - time()) / (86400));
								echo ($daysToGo < 0) ? '<span style="color:red">Expired ' . $daysToGo . ' ago</span>' : '<span style="color:green">Expires in ' . $daysToGo . ' days'; ?>
						<?php }
						} else {
							echo 'N/A';
						}; ?>
				</fieldset>
				<center>
					<a href="/certificates/annual/index.php?inc=certificate_add_edit&clid=<?php echo $_REQUEST['clid']; ?>&act=add&offid=0" class="button">Request new certificate</a>
					<?php if (isset($daysToGo) and $daysToGo < 30 and isset($certificate['crtNr'])) { ?>
						<a href="/certificates/annual/index.php?inc=certificate_add_edit&clid=<?php echo $_REQUEST['clid']; ?>&act=reissue&offid=0&crtNr=<?php echo $certificate['crtNr']; ?>" class="button">Reissue the certificate</a>
					<?php }; ?>
				</center>
			<?php }; ?>
		</td>
	</tr>
</table>