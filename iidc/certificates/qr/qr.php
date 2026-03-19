<?php
if (!session_id()) {
    session_start();
}
if (!isset($_GET['crtNr'])) {
    exit();
}

include $_SESSION['hqc_path'] . '/load.inc.php';
$status = 'active';
$reason = '';

if ($certificate = $hqcdb->get_row("SELECT hqc_certificates_annual.crtNr,hqc_certificates_annual.certificate_nr,hqc_certificates_annual.date_of_expiry, hqc_certificates_annual.qr_status, hqc_companies.company_name FROM hqc_certificates_annual JOIN hqc_companies ON hqc_certificates_annual.clid = hqc_companies.clid WHERE hqc_certificates_annual.crtNr='$_GET[crtNr]'")) {
    if (trim($certificate['qr_status']) != '' and is_array(decode_json($certificate['qr_status'], true))) {
        $qr_status = decode_json($certificate['qr_status'], true);
        $status = $qr_status['status'];
        $reason = $qr_status['reason'];
    };
    if (ceil(($certificate['date_of_expiry'] - time()) / 86400) < 0) {
        $status = 'expired';
    }
} else {
    echo "Certificate Not Found";
}
?>
<form method="post" action="<?php echo this_url(); ?>/qr_save.php" name="qr_status" id="qr_status" onsubmit="return post_this_form(this)">
    <input type="hidden" name="crtNr" value="<?php echo $_GET['crtNr']; ?>" />
    <table class="alternate" style="min-width:500px">
        <tr>
            <th>Certificate QR:</th>
            <td><a href="//ca.halaloffice.com/?crtnr=<?php echo $certificate['certificate_nr']; ?>" target="_blank"> <?php echo $certificate['certificate_nr']; ?></a></td>
        </tr>
        <tr>
            <th>Company:</th>
            <td><?php echo $certificate['company_name']; ?></td>
        </tr>
        <tr>
            <th>Certificate status:</th>
            <td>
                <?php if ($status != 'expired') { ?>
                    <label style="color:green">
                        <input type="radio" value="active" name="qr_status[status]" <?php if ($status == 'active') echo 'checked' ?> /> Active
                    </label><br />
                    <label style="color:orange">
                        <input type="radio" value="paused" name="qr_status[status]" <?php if ($status == 'paused') echo 'checked' ?> /> Paused
                    </label><br />
                    <label style="color:#800">
                        <input type="radio" value="null and void" name="qr_status[status]" <?php if ($status == 'null and void') echo 'checked' ?> /> Null and void
                    </label>
                <?php } else { ?>
                    <label style="color:#800">
                        <input type="radio" value="expired" name="qr_status[status]" <?php if ($status == 'expired') echo 'checked' ?> /> Expired
                    </label>
                <?php }; ?>
            </td>
        </tr>
        <tr>
            <th>Reason:</th>
            <td>
                <textarea name="qr_status[reason]" style="width:350px;height:100px"><?php echo $reason; ?></textarea>
            </td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: center;"><input type="submit" value="<?php _e('Save'); ?>" /></th>
        </tr>
    </table>

</form>