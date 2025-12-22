<?php
if (!isset($_SESSION))
    include "../../check_user.inc.php";
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] != 'committee_member') {
    include "$prog_path/config/connect.inc.php";
};
?>
<style>
    #committeeMembers th {
        width: 150px;
    }

    #committeeMembers td input {
        width: 99%;
    }
</style>
<script>
    function committeeUsername(val) {
        if (val.length > 0) {
            //remove all characters except letters and numbers
            val = val.replace(/[^a-zA-Z0-9]/g, '');
            jQuery("#committeeMemberForm input[name='username']").val(val);
            jQuery("#committeeUsername").html('Final username: <b>co-' + val + '</b>');
        }
    }

    function showPassword() {
        //switch between password and text
        if (jQuery("#committeeMemberForm input[name='password']").attr('type') == 'text')
            jQuery("#committeeMemberForm input[name='password']").attr('type', 'password');
        else
            jQuery("#committeeMemberForm input[name='password']").attr('type', 'text');
    }

    jQuery(document).ready(function() {
        //prevent inputs from autocomplete and auto fill
        jQuery("#committeeMemberForm input").attr('autocomplete', 'off').attr('autofill', 'off');
        //disable input auto suggest

    })

    //function to validate telephone number return number after removing charterers and spaces
    function validatePhone(phone) {
        jQuery("#committeeMemberForm input[name='member_mobile_phone']").val(phone.replace(/[^0-9\+]/g, '').replace(/\s/g, ''));
    }

    //function to check password strength
    function checkPasswordStrength() {
        password = jQuery("#committeeMemberForm input[name='password']").val();

        jQuery(".passwordInfo").css('color', 'green');
        jQuery(".passwordInfo").html('Password is strong');
        //remove spaces
        password = password.replace(/\s/g, '');
        //check if password is empty
        if (password.length == 0) {
            jQuery(".passwordInfo").html('');
            jQuery(".passwordInfo").css('color', '#000');
            return;
        }
        //check if password is less than 8 characters
        if (password.length < 8) {
            jQuery(".passwordInfo").html('Password is too short');
            jQuery(".passwordInfo").css('color', '#f00');
            return;
        }
        //check if password contains at least one number
        if (!/\d/.test(password)) {
            jQuery(".passwordInfo").html('Password must contain at least one number');
            jQuery(".passwordInfo").css('color', '#f00');
            return;
        }
        //check if password contains at least one uppercase letter
        if (!/[A-Z]/.test(password)) {
            jQuery(".passwordInfo").html('Password must contain at least one uppercase letter');
            jQuery(".passwordInfo").css('color', '#f00');
            return;
        }
        //check if password contains at least one lowercase letter
        if (!/[a-z]/.test(password)) {
            jQuery(".passwordInfo").html('Password must contain at least one lowercase letter');
            jQuery(".passwordInfo").css('color', '#f00');
            return;
        }
        //check if password contains at least one special character
        if (!/[!@#$%&*]/.test(password)) {
            jQuery(".passwordInfo").html('Password must contain at least one special character (!@#$%&*)');
            jQuery(".passwordInfo").css('color', '#f00');
        }
    }


    //function to generate random password contains letters, numbers and special characters

    function generatePassword() {
        let password = '';
        var length = 8;
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';

        for (let i = 0; i < length; i++) {
            password += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        //if the password does not contain at least one number, one uppercase letter, one lowercase letter and one special character, generate another password
        if (!/\d/.test(password) || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[!@#$%&*]/.test(password)) {
            generatePassword();
        } else {
            jQuery(".passwordInfo").html('Password is strong');
            jQuery(".passwordInfo").css('color', 'green');
            jQuery("#committeeMemberForm input[name='password']").val(password);
        }
    }

    jQuery(document).ready(function() {
        committeeUsername(jQuery("#committeeMemberForm input[name='username']").val())
    })

    function sendSMS() {
        telephone = jQuery("#committeeMemberForm input[name='member_mobile_phone']").val();
        alert(telephone)
    }
    async function delete_signature(comemid) {
        await confirm_message('Are you sure you want to delete this signature?');

        jQuery.post('/admin/committee/committee_save.php', {
            act: 'delete_signature',
            comemid: comemid
        }, function(data) {
            if (data == 'success')
                jQuery("#signature_preview").remove();
            else
                alert_message('Error deleting signature');

        })
    }
</script>
<?php
if (!isset($_GET['comemid']) && (isset($_SESSION['comemid']) && isset($_GET['inc'])))
    $_GET['comemid'] = $_SESSION['comemid'];

if (isset($_GET['comemid'])) {
    $row = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE comemid='{$_GET['comemid']}'");
} else {
    $row = $amdb->get_columns("hqc_committee_members", false);
}
?>
<form id="committeeMemberForm" autocomplete="off" action="<?php echo isset($_GET['comemid']) ? '/admin/committee/' : '' ?>committee_save.php" target="" method="post" onsubmit="return  post_this_form(this);" enctype="multipart/form-data">
    <input type="hidden" name="act" value="<?php echo isset($_GET['comemid']) ? 'update_committee_member' : 'add_committee_member' ?>">
    <?php if (isset($_GET['comemid'])) { ?>
        <input type="hidden" name="comemid" value="<?php echo $_GET['comemid']; ?>">
    <?php } ?>
    <?php $tableTitle = isset($_GET['comemid']) ? 'update committee member' : 'add committee member'; ?>
    <table id="committeeMembers" class="alternateOn" style="width:550px;min-width:auto">
        <tr>
            <th colspan="2" style="text-align: center;text-transform:capitalize"><?php echo isset($_GET['inc']) ? 'Update My data' : $tableTitle ?> </th>
        </tr>
        <tr>
            <th>Member name:*</th>
            <td><input type="text" name="member_name" data-required="yes" value="<?php echo $row['member_name']; ?>" /></td>
        </tr>
        <?php if (isset($_GET['act'])) { ?>
            <tr>
                <th>Function:*</th>
                <td><input type="text" name="member_function" data-required="yes" value="<?php echo $row['member_function']; ?>" /></td>
            </tr>
        <?php } ?>
        <tr>
            <th>Email:*</th>
            <td><input type="email" name="member_email" data-required="yes" value="<?php echo $row['member_email']; ?>" /></td>
        </tr>
        <tr>
            <th>Mobile Phone:*</th>
            <td>
                <input type="tel" name="member_mobile_phone" id="member_mobile_phone" data-required="yes" value="<?php echo $row['member_mobile_phone']; ?>" pattern="\+?[0-9]+" title="European phone number format: +xxxxxxxxxx. Only numbers and + are allowed." onkeyup="validatePhone(this.value)" style="<?php echo ($_SESSION['user_type'] == 'committee_member' ? 'width:50%' : ''); ?>" />
                <?php echo ($_SESSION['user_type'] == 'committee_members' ? '<label onclick="sendSMS()"><i class="fas fa-sms"></i>Send SMS test</label>' : ''); ?>
                <br />
                <i>Number with international code. No spaces (eg:+316123456)</i>
            </td>
        </tr>
        <tr>
            <th>Username:*</th>
            <td><input type="text" style="width:45%" name="username" placeholder="Username" data-required="yes" value="<?php echo str_replace('co-', '', $row['username']); ?>" onkeyup="committeeUsername(this.value)" /> <span id="committeeUsername"></td>
        </tr>
        <tr>
            <th>Password:*</th>
            <td><input type="password" style="width:45%" id="committeePassword" name="password" data-required="yes" value="<?php echo $row['password']; ?>" placeholder="Password" onkeyup="checkPasswordStrength()" /> <i class="fa fa-eye" onclick="showPassword()"></i> <label onclick="generatePassword();checkPasswordStrength()" class="passwordColor"><i class="fa fa-key"></i>Generate Password</label><br>
                <i class="passwordInfo"></i>
            </td>
        </tr>
        <tr>
            <th>Signature:</th>
            <td>
                <input type="file" name="signature" accept="image/*" />
                <?php if ($user_signature = get_dmc_signature(isset($row['comemid']) ? $row['comemid'] : $_SESSION['comemid'])) { ?>
                    <div id="signature_preview">
                        <img src="<?php echo $user_signature; ?>" width="200px" height="100px" />
                        <i class="fa fa-trash-alt" style="color:red" onclick="delete_signature(<?php echo isset($row['comemid']) ? $row['comemid'] : $_SESSION['comemid']; ?>)"></i>
                    </div>
                <?php } ?>
            </td>
        <tr>
            <?php if (isset($user_type) && $user_type == 'committee_member') { ?>
                <th colspan="2" style="text-align: center;"><input type="reset" value="Reset" /><input type="submit" value="Save" /></th>
        </tr>
    <?php }; ?>
    </table>
</form>