<?php
include "../../check_user.inc.php";
include "../../config/paths.inc.php";
?>
<style>
    .ui-dialog .ui-dialog-buttonpane {
        display: none;
    }
</style>
<div style="padding:10px;max-width:500px;font-size:14px">
    <?php
    $client = $amdb->get_row("select company_name from companies where clid = '$_GET[clid]'");

    if ($invoices = $amdb->get_results("select invoice_nr,date,total from invoices where clid = '$_GET[clid]' AND (invoice_type='annual' or service_type LIKE '%Annual Certification%' or service_type LIKE '%Halal Certification%') order by inserted_on desc limit 5")) { ?><h3 style="margin:0px">Recent Invoices</h3>
        <div><strong>Client: </strong><?php echo $client['company_name']; ?></div>
        <table style="width:100%;margin-top:10px;" class="alternate">
            <tr>
                <th>Invoice Nr</th>
                <th>Date</th>
                <th>Total</th>
            </tr><?php foreach ($invoices as $invoice) {
                        $invFile = "/client_data/invoices/$invoice[invoice_nr].pdf";
                        if (file_exists($prog_path . $invFile)) {
                            $invoice['invoice_nr'] = "<a href='$invFile' target='_blank'>$invoice[invoice_nr]</a>";
                        }
                    ?><tr>
                    <td><?php echo $invoice['invoice_nr']; ?></td>
                    <td><?php echo $invoice['date']; ?></td>
                    <td>&euro; <?php echo $invoice['total']; ?></td>
                </tr><?php } ?>
        </table>
        <info>Only annual certificate invoices are included here.</info>
    <?php } else {
        echo "No invoices found.";
    }
    ?><diV style="margin:20px 0px">Please select your next action after generating the certificate: <ul>
            <li><label><input type='radio' name='afterPrint' value='certsList' checked>Go to certificates list</label></li>
            <li><label><input type='radio' name='afterPrint' value='invoicesList'>Go to invoices list</label></li>
            <li><label><input type='radio' name='afterPrint' value='createInvoice'>Create Invoice</label></li>
        </ul>
    </diV>
    <div style="text-align:center">
        <button onclick="jQuery('#afterPrint').val(jQuery('input[name=afterPrint]:checked').val());jQuery('#addEditForm').submit();closeDialog();">Generate Certificate</button>
        <button onclick="jQuery('#afterPrint').val('certsList');closeDialog();">Cancel</button>

    </div>
</div>