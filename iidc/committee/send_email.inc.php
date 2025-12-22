<?php
if (!defined("_HQC_"))
    exit();
?>
<script>
    jQuery(".ui-dialog .ui-dialog-buttonpane", window.parent.document).remove();

    function SendEmail(form) {
        //check if at least one committee member is selected
        if (jQuery("input[name='comemid[]']:checked").length == 0) {
            top.alert_message("Please select at least one committee member");
            return false;
        }
        return post_this_form(form);
    }
</script>
<?php
if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'sendEmail') {

    if (!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='DMC_credentials'"))
        $row = $amdb->get_columns('invoice_templates');
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

        #committeeMembers li {
            position: relative;
        }

        label.hoc {
            position: absolute;
            right: 40px;
        }
    </style>
    <form action="send_email.php" method="post" name="committee_email" id="committee_email" onsubmit="return SendEmail(this)" target="">
        <input type="hidden" name="act" value="sendEmail" />
        <input type="hidden" name="email[from_email]" data-required="yes" value="info@halaloffice.com" />
        <input type="hidden" name="email[from_name]" data-required="yes" value="HQC-Headquarter" />
        <table class="alternate" style="width:100%;" id="sendCommitteeEmail">
            <tr>
                <th style="width:100px">Committee members:</th>
                <td>
                    <ul style="padding: 0px;margin:0px;height:150px;overflow:auto" id="committeeMembers" class="alternateOn">
                        <?php
                        if ($comMembers = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE status='active' ORDER BY member_name ASC")) {
                            foreach ($comMembers as $member) {
                        ?>
                                <li><label><input type="checkbox" name="comemid[]" value="<?php echo $member['comemid']; ?>"><?php echo $member['member_name']; ?> (<?php echo $member['member_function']; ?>)</label></li>
                        <?php
                            }
                        }
                        ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <th>Email Subject:</th>
                <td><input type="text" name="email[subject]" data-required="yes" value="<?php echo $row['email_subject']; ?>" style="width:100%" /></td>
            </tr>
            <tr>
                <th colspan="2">Email body</th>
            </tr>
            <tr>
                <td colspan="2"><textarea class="tinymce_minimum" name="email[message]" style="height:250px;"><?php echo $row['email_body']; ?></textarea></td>
            </tr>
            <tr>
                <th colspan="2">
                    <div style="text-align:center">
                        <input value="Send Email" type="submit" /><input type="reset" value="Reset" />
                        <input type="button" value="Cancel" onClick="closePopupDialog()" data-type="cancel" />
                    </div>
                </th>
            </tr>
        </table>
    </form>
    <script>
        jQuery(document).ready(function() {
            do_tinymce_minimum();
        });
    </script>
<?php
    exit();
};
