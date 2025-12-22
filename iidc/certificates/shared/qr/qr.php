<?php
if (!isset($_GET['crtNr'])) {
    exit();
}
include "../../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
$status = 'active';
$reason = '';

if(isset($_REQUEST['tp'])){
    $table = "certificates_" . $_REQUEST['tp'];
$status = "active";
    if ($certificate = $amdb->get_row("SELECT $table.nr,$table.certificate_nr, $table.qr_status, companies.company_name FROM $table JOIN companies ON $table.clid = companies.clid WHERE $table.nr='$_GET[crtNr]'")) {
        if (trim($certificate['qr_status']) != '' and is_array(decode_json($certificate['qr_status'], true))) {
            $qr_status = decode_json($certificate['qr_status'], true);
            $status = $qr_status['status'];
            $reason = $qr_status['reason'];
        };
        $certificate['certificate_nr'] = $certificate['certificate_nr'];
    }
} else{
if ($certificate = $amdb->get_row("SELECT acms_halal_certificates.crtNr,acms_halal_certificates.certificate_nr,acms_halal_certificates.date_of_expiry, acms_halal_certificates.qr_status, companies.company_name FROM acms_halal_certificates JOIN companies ON acms_halal_certificates.clid = companies.clid WHERE acms_halal_certificates.crtNr='$_GET[crtNr]'")) {
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
}
?>
<form method="post" action="/certificates/shared/qr/qr_save.php" name="qr_status" id="qr_status" onsubmit="return post_this_form(this)">
    <input type="hidden" name="crtNr" value="<?php echo $_GET['crtNr']; ?>" />
    <input type="hidden" name="saveBtn" value="Save" />
    <?php if(isset($_REQUEST['tp']))
    {?>
    <input type="hidden" name="tp" value="<?php echo $_REQUEST['tp']; ?>" />
    <?php } ?>
    <table class="alternate">
        <tr>
            <th>Certificate QR:</th>
            <td><a href="//ca.iidc.eu/?crtnr=<?php echo $certificate['certificate_nr']; ?>" target="_blank"> <?php echo $certificate['certificate_nr']; ?></a></td>
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
    </table>
</form>