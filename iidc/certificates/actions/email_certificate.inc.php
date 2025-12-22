<?php
if (!defined("_HQC_"))
    exit();
?>
<script>
    //   jQuery(".ui-dialog .ui-dialog-buttonpane", window.parent.document).remove();
</script>
<?php
if (isset($_REQUEST['act']) and isset($_REQUEST['crtNr'])) {
    if (!$certificate = $amdb->get_row("Select * FROM acms_halal_certificates where crtNr='$_REQUEST[crtNr]'"))
        return;

    $certFiles = $hcp_path . "/client_data/certificates/$certificate[url]";
    $certificate_url = "/client_data/certificates/$certificate[url]";

    if (!$client = get_client($certificate['clid']))
        return;

    $client_data['company_name'] = $client['company_name'];
    $client_data['client_name'] = $client['contact_name'];
    $client_data['client_email'] = $client['email'];
    $client_data['company_address'] = $client['company_address'];
    $client_data['certificate_nr'] = $certificate['certificate_nr'];
    $email_message = array();
    if ($email_message = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name = 'scheduled_annual_certificate'")) {
        if (strstr($email_message['email_body'], '[email_footer]') && $email_footer = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name = 'email_footer'")) {
            $email_message['email_body'] = str_replace("[email_footer]", $email_footer['email_body'], $email_message['email_body']);
        }
        foreach ($client_data as $key => $value) {
            $email_message['email_subject'] = str_replace("[" . $key . "]", $value, $email_message['email_subject']);
            $email_message['email_body'] = str_replace("[" . $key . "]", $value, $email_message['email_body']);
        }
    }
?>
    <style>
        table#resendCertificateTable th,
        table#resendCertificateTable td {
            vertical-align: middle;
        }

        table#resendCertificateTable td b {
            display: inline-block;
            width: 100px;
            float: left;
        }

        table#resendCertificateTable td input[type='text'] {
            width: 95%;
        }

        table#resendCertificateTable th {
            white-space: nowrap
        }
    </style>
    <form action="../actions/email_certificate.php" method="post" name="postReminder" id="postReminder" onsubmit="return post_this_form(this)" target="">
        <input type="hidden" name="act" value="resendCertificate" />
        <input type="hidden" name="crtNr" value="<?php echo $_REQUEST['crtNr']; ?>" />
        <input type="hidden" name="saveBtn" id="saveBtn" value="Send Certificate again" />
        <input type="hidden" name="email[from_email]" data-required="yes" value="<?php echo $email_message['email_reply_address']; ?>" />
        <input type="hidden" name="email[certificateFile]" value="<?php echo $certFiles?>" />
        <table class="alternate" style="width:100%;" id="resendCertificateTable">
            <tr>
                <th style="width:100px">Client email:</th>
                <td style="width:200px"><input type="text" name="email[to_email]" data-required="yes" value="<?php echo $client_data['client_email']; ?>" /></td>
                <th style="width:100px">Client name:</th>
                <td><input type="text" name="email[to_name]" data-required="yes" value="<?php echo $client_data['company_name']; ?>" /></td>
            </tr>
            <tr>
                <th>Email Subject:</th>
                <td colspan="3"><input type="text" name="email[subject]" data-required="yes" value="<?php echo $email_message['email_subject']; ?>" /></td>
            </tr>
            <tr>
                <th colspan="4">Email body</th>
            </tr>
            <tr>
                <td colspan="4"><textarea class="tinymce_minimum" name="email[message]" style="height:250px;"><?php echo $email_message['email_body']; ?></textarea></td>
            </tr>
            <tr>
                <th style="width:100px">Sender address:</th>
                <td style="width:200px"><?php echo $email_message['email_reply_address']; ?></td>
                <th style="width:100px">Sender name:</th>
                <td><input type="text" name="email[from_name]" data-required="yes" value="<?php echo $email_message['email_sender_name']; ?>" /></td>
            </tr>
            <tr>
                <th>Attached Certificate:</th>
                <td>
                    <a href="<?php echo $certificate_url; ?>" target="_blank" id="attachedCertificate"><?php echo $certificate['certificate_nr']; ?>.pdf</a>
                </td>
                <th>BCC:</th>
                <td><input type="text" name="bcc" />
                </td>
            </tr>
            <tr>
                <th colspan="4">
                    <div style="text-align:center"><input value="Send Certificate" type="submit" /><input type="reset" value="Reset" />
                        <input type="button" value="Cancel" onClick="closePopupDialog()" data-type="cancel" />
                    </div>
                </th>
            </tr>
        </table>
    </form>
    <script>
        do_tinymce_minimum();
    </script>
<?php
    exit();
};
