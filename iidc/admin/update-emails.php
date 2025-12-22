<?php
include "../config/paths.inc.php";
$row = $amdb->get_row("SELECT * FROM companies
						JOIN users ON companies.clid=users.clid
						WHERE users.clid = $_REQUEST[clid]");
?>
<script>
    function switchEmails() {
        email1 = jQuery("#client_email1").val();
        email2 = jQuery("#client_email2").val();
        jQuery("#client_email1").val(email2);
        jQuery("#client_email2").val(email1);
    }

    function checkClientEmails() {
        jQuery("#errorMessage").html("");
        if (jQuery("#client_email1").val().trim() == '') {
            jQuery("#errorMessage").html("Please enter client email");
            return false;
        }
        email1 = jQuery("#client_email1").val();
        email2 = jQuery("#client_email2").val();
        clid = jQuery("input[name='clid']").val();

        jQuery.post("save_emails.php", {
            email1: email1,
            email2: email2,
            clid: clid,
            act: 'save'
        }, function(data) {
            if (data == 'saved') {
                if (email1.indexOf('iidc.eu') > -1)
                    color = 'red';
                else
                    color = 'green';
                email1 = '<span style="color:' + color + '">' + email1 + '</span>';
                jQuery("#emails_" + clid).html(email1 + '<br/>' + email2)
                top.closePopup();
                return false;
            } else {
                jQuery("#errorMessage").html(data);
            }
        });
        return false;
    }
</script>
<form action="save_emails.php" name="update_emails" id="update_emails" method="post" onsubmit="return checkClientEmails()" target="">
    <input type="hidden" name="clid" value="<?php echo $_REQUEST['clid']; ?>" />
    <input type="hidden" name="act" value="save" />
    <input type="hidden" name="saveBtn" value="Save emails" />
    <table width="550" style="border:0px !important;padding:10px;">
        <tr>
            <td>
                <div class="prevTtl">Company</div>
                <div class="prevVal"><strong><?php echo $row['company_name']; ?></strong></div>
                <div class="prevTtl">Email*</div>
                <div class="prevVal"><input type="email" name="email1" id="client_email1" value="<?php echo $row['email1']; ?>" data-required="yes" required style="width:300px" /><i class="fas fa-arrows-alt-v" style="margin-top:10px;margin-left:10px" onclick="switchEmails();"></i></div>
                <div class="prevTtl">Email2</div>
                <div class="prevVal"><input type="email" name="email2" id="client_email2" value="<?php echo $row['email2']; ?>" style="width:300px" /></div>
                <div id="errorMessage" class="red" style="text-align:center"></div>
            </td>
        </tr>
    </table>
</form>