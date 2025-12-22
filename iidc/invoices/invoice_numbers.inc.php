<?php
if ($_SESSION['user_role'] == "super_admin") {
    include "../config/mysql_ftp.inc.php";
    include "../config/connect.inc.php";
    $offices = array();
    if ($subs = $amdb->get_results("SELECT offid,office_name FROM `offices` WHERE status='active' ORDER BY office_name ASC")) {
        foreach ($subs as $sub) {
            $offices[$sub['offid']] = $sub['office_name'];
        }
    }
?>

    <form method="post" action="invoice_nrs_save.php" name="invoice_nrs" id="invoice_nrs" onsubmit="post_this_form(this)">
        <input type="hidden" name="act" value="update" />
        <table style="border:1px solid #EEE;min-width:auto" id="invoiceTbl" class="alternate">
            <thead>
                <tr>
                    <th>::</th>
                    <th>office</th>
                    <th>Number prefix</th>
                    <th>Invoice Number</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $srNr = 1;
                $invoice_nrs = $amdb->get_results("SELECT * FROM `hqc_invoice_nrs` ORDER BY offid ASC");
                foreach ($invoice_nrs as $invoice_nr) {
                    $offid = $invoice_nr['offid'];
                    $prefix = $invoice_nr['invoice_number_prefix'];
                    $invoice_number = $invoice_nr['invoice_number'];
                ?>
                    <tr>
                        <th><?php echo $srNr++; ?></th>
                        <td><?php echo $offices[$offid]; ?></td>
                        <td><input type="text" name="office[<?php echo $offid; ?>][invoice_number_prefix]" value="<?php echo $prefix; ?>" /></td>
                        <td><input type="text" name="office[<?php echo $offid; ?>][invoice_number]" value="<?php echo $invoice_number; ?>" required /></td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
        <div style="text-align:center"> <input type="submit" value="Save" /></div>
    </form>
<?php }; ?>