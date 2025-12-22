<?php
if (!defined("_HQC_"))
    exit();
?>
<script>
    jQuery(".ui-dialog .ui-dialog-buttonpane", window.parent.document).remove();
</script>
<?php

if (isset($_REQUEST['act']) and isset($_REQUEST['nr'])) {
    if (!$invoice = $amdb->get_row("Select * FROM invoices where nr='$_REQUEST[nr]'"))
        return;
    $invFile = "/client_data/invoices/$invoice[invoice_nr].pdf";

    if (file_exists($prog_path . $invFile)) {
        $invoice_url = "$prog_www/client_data/invoices/$invoice[invoice_nr].pdf";
    } else {
        if (trim($invoice['invoice_items']) != '' and is_array(json_decode(str_replace("\r\n", "<br/>", $invoice['invoice_items']), true))) {
            $invoice_url = "pdf/pdf_create_invoice.php?nr=$_REQUEST[nr]";
        } else {
            if ($invoice['service_type'] == "CN") {
                $st = 'CN';
            } else {
                if ($invoice['service_type'] != 'HQC' and $invoice['service_type'] != 'COHS')
                    $st = 'OTHER';
                else
                    $st =  $invoice['service_type'];
            }
            $invoice_url = "pdf/show_invoice.php?nr=$_REQUEST[nr]&st=$st&tmpl=nl";
        };
    };

    $cla = invoice_address($invoice['clid']);

    $invoice_address = json_encode($cla, true);
    $data['client_address'] = $cla['address'];
    $data['company_name'] = $cla['company_name'];
    $data['client_name'] = $cla['client_name'];
    $data['client_email'] = $cla['client_email'];
    $data['invoice_nr'] = $invoice['invoice_nr'];

    $invoice_data = array();
    if (trim($invoice['invoice_data']) != '' and is_array(json_decode(str_replace("\r\n", "<br/>", $invoice['invoice_data']), true))) {
        $invoice_data = json_decode(str_replace("\r\n", "<br/>", $invoice['invoice_data']), true);
    }

    if (trim($invoice_data['email_message']['body']) != '') {
        $style = '';
        foreach ($invoice_data['email_message_style'] as $key => $value)
            $style .= $key . ':' . $value . '; ';
        $data['admin_message'] = '<p style="' . $style . '">' . $invoice_data['email_message']['body'] . '</p>';
    } else {
        $data['admin_message'] = '';
    }

    if (!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='invoice'"))
        $row = $amdb->get_columns('invoice_templates');

    foreach ($data as $key => $value) {
        $row['email_subject'] = str_replace('[' . $key . ']', $value, $row['email_subject']);
        $row['email_body'] = str_replace('[' . $key . ']', $value, $row['email_body']);
    }
    $row['email_body'] = str_replace('<br /><br /><br />', '<br /><br />', $row['email_body']);
?>
    <style>
        table#resendInvoiceTable td b {
            display: inline-block;
            width: 100px;
            float: left;
        }

        table#resendInvoiceTable td input[type='text'] {
            width: 95%;
        }

        table#resendInvoiceTable th {
            white-space: nowrap
        }
    </style>
    <form action="invoice_save.php" method="post" name="postReminder" id="postReminder" onsubmit="return post_this_form(this)" target="">
        <input type="hidden" name="act" value="resendInvoice" />
        <input type="hidden" name="nr" value="<?php echo $_REQUEST['nr']; ?>" />
        <input type="hidden" name="saveBtn" id="saveBtn" value="Send invoice" />
        <table class="alternate" style="width:100%;" id="resendInvoiceTable">
            <tr>
                <th style="width:100px">
                        <label><input type="checkbox" id="client_email" name="client_email" style="float:left" />Client email:</label>
                </th>
                <td style="width:200px"><input type="text" name="email[to_email]" data-required="yes" value="<?php echo $invoice_data['client_email']; ?>" /></td>
                <th style="width:100px">Client name:</th>
                <td><input type="text" name="email[to_name]" data-required="yes" value="<?php echo $data['company_name']; ?>" /></td>
            </tr>
                <tr>
                    <th style="width:100px"><label><input type="checkbox" name="exact_email" style="float:left" />Exact email:</label></th>
                    <td colspan="3"><input type="text" id="bcc_email" name="bcc_email" data-required="yes" value="<?php echo $invoice_data['bcc_email'][0]; ?>" /></td>
                </tr>
            <tr>
                <th>Email Subject:</th>
                <td colspan="3"><input type="text" name="email[subject]" data-required="yes" value="<?php echo $row['email_subject']; ?>" /></td>
            </tr>
            <tr>
                <th colspan="4">Email body</th>
            </tr>
            <tr>
                <td colspan="4"><textarea class="tinymce_minimum" name="email[message]" style="height:250px;"><?php echo $row['email_body']; ?></textarea></td>
            </tr>
            <tr>
                <th style="width:100px">Reply address:</th>
                <td style="width:200px"><input type="text" name="email[from_email]" data-required="yes" value="<?php echo $row['email_reply_address']; ?>" /></td>
                <th style="width:100px">Sender name:</th>
                <td><input type="text" name="email[from_name]" data-required="yes" value="<?php echo $row['email_sender_name']; ?>" /></td>
            </tr>
            <tr>
                <th>Attached invoice:</th>
                <td>
                    <a href="<?php echo $invoice_url; ?>" target="_blank" id="attachedInvoice"><?php echo $data['invoice_nr']; ?>.pdf</a>
                </td>
                <th>Test email</th>
                <td><input type="text" name="test_email" style="width:50%" value="info@<?php echo str_replace('www.', '', $_SERVER['HTTP_HOST']); ?>" />
                    <label><input type="checkbox" value="yes" name="do_test" style="width:auto" />Send test Email</label>
                </td>
            </tr>
            <tr>
                <th colspan="4">
                    <div style="text-align:center"><input value="Resend invoice" type="submit" /><input type="reset" value="Reset" />
                        <input type="button" value="Cancel" onClick="closePopupDialog()" data-type="cancel" />
                    </div>
                </th>
            </tr>
        </table>
    </form>
    <script>
        do_tinymce_minimum();
        <?php if (!file_exists($prog_path . $invFile)) { ?>
            jQuery("#attachedInvoice").load("<?php echo $invoice_url; ?>&act=regen");
        <?php }; ?>
    </script>
<?php
    exit();
};
