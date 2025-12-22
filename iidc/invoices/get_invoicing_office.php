<?php
define("__HQC__", true);
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
$invoicing_office = $amdb->get_row("SELECT * FROM `hqc_invoicing_offices` WHERE invoice_company_name != '' AND offid = '$_GET[offid]' ORDER BY invoice_company_name");
?>
<div><?php echo $invoicing_office['invoice_address']; ?></div>
<?php
if ($_GET['offid'] == '0') { ?>
    <div style="margin-top: 10px;">
        <b>Invoice Language:</b>
        <select name="invoice_lang">
            <option value="german">German</option>
            <option value="english">English</option>
        </select>
    </div>
<?php
} else { ?>
    <input type="hidden" name="invoice_lang" value="english" />
<?php }; ?>
<input type="hidden" name="vat_rate" id="vat_rate" value="<?php echo $invoicing_office['invoice_vat_rate']; ?>" />
<script>
    setInvoiceVatRate();
</script>