<?php if (!defined("_HQC_")) {
    exit();
};
?>
<style>
    #committeeMembers th {
        width: 150px;
    }

    #committeeMembers td input {
        width: 99%;
    }

    .phoneNumbers li {
        list-style: none;
        margin: 0px;
        padding: 5px;
    }

    .phoneNumbers li strong {
        display: inline-block;
        width: 100px;
    }

    .phoneNumbers li input {
        width: 70% !important;
    }
</style>
<script>
    jQuery("#page_title").html("<?php echo isset($member_account) ? 'My Account' : 'Committee member account'; ?>");

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
        jQuery(phone).val(jQuery(phone).val().replace(/[^0-9\+]/g, '').replace(/\s/g, ''));
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

    function selectFunction(val) {
        if (val == 'other') {
            jQuery("#member_function_other").css('display', 'block').attr('data-required', 'yes');
        } else {
            jQuery("#member_function_other").css('display', 'none').removeAttr('data-required').val('');
        }
    }
</script>
<?php
function get_user_signature($comemid)
{
    global $prog_path;

    $image_file = '/data/DMC/signatures/' . $comemid . '_signature';

    $image_exts = array('.jpg', '.jpeg', '.png', '.svg');
    foreach ($image_exts as $ext) {
        if (file_exists("$prog_path" . $image_file . $ext))
            return $image_file . $ext . '?t=' . time();
    }
}

$functions = array(
    'ABM' => 'Auditor Board Member',
    'MBM' => 'Management Board Member',
    'SBM' => 'Shariah Board Member',
);

if (!isset($_GET['comemid']) && isset($_SESSION['comemid']))
    $_GET['comemid'] = $_SESSION['comemid'];

if (isset($_GET['comemid'])) {
    $row = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE comemid='{$_GET['comemid']}'");
} elseif(isset($member_account)) {
    if (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) {
        $uid = $_SESSION['user']['uid'];
    } else {
        $uid = $_SESSION['offid'];
    }

    if($row = $amdb->get_row("SELECT * FROM hqc_committee_members WHERE uid='$uid' AND offid='$_SESSION[offid]'")) {
        $_GET['comemid'] = $row['comemid'];
    } else {
        echo "<center><h3 style=\"color:red;\">No committee member found. Please ask the administrator for assistance.</h3><br/>If you are the administrator, add yourself as a committee member.</center>";
            return;
    }
 } else {
    $row = $amdb->get_columns("hqc_committee_members", false);
}
$comDir = explode('/', $_SERVER['REQUEST_URI'])[1];
?>
<form id="committeeMemberForm" autocomplete="off" action="<?php echo isset($_GET['comemid']) ? '/admin/committee/' : '' ?>committee_save.php" target="" method="post" onsubmit="return  post_this_form(this);" enctype="multipart/form-data">
    <input type="hidden" name="act" value="<?php echo isset($_GET['comemid']) ? 'update_committee_member' : 'add_committee_member' ?>">
    <?php if (isset($_GET['comemid'])) { ?>
        <input type="hidden" name="comemid" value="<?php echo $_GET['comemid']; ?>">
    <?php } ?>
    <input type="hidden" name="comDir" value="<?php echo $comDir; ?>">
    <div style="width:fit-content;margin:0 auto;">
        <h2 style="text-align:center;background:#f0ede8;padding:10px;margin:0px"><?php echo isset($member_account) ? 'My Account' : 'Committee member account'; ?></h2>
        <div style="float: left;">
            <table id="committeeMembers" class="alternateOn" style="width:550px;min-width:auto">
                <tr>
                    <th>Member name:*</th>
                    <td><input type="text" name="member_name" data-required="yes" value="<?php echo $row['member_name']; ?>" /></td>
                </tr>
                <?php if ($comDir == 'admin') {
                    if (isset($functions[$row['member_function']]))
                        $function = $functions[$row['member_function']];
                    else
                        $function = 'other';
                ?>
                    <tr>
                        <th>Function:*</th>
                        <td>
                            <select name="member_function" data-required="yes" onchange="selectFunction(this.value)">
                                <option value="">Please select</option>
                                <?php foreach ($functions as $funcKey => $funcVal) {
                                     ?>
                                    <option value="<?php echo $funcKey; ?>" <?php echo $function == $funcVal ? 'selected' : ''; ?>><?php echo $funcVal; ?></option>
                                <?php } ?>
                                <option value="other" <?php echo $function == 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <input type="text" style="display:<?php echo $function == 'other' ? 'block' : 'none'; ?>" name="member_function_other" id="member_function_other" value="<?php echo $function == 'other' ? $row['member_function'] : ''; ?>" <?php echo $function == 'other' ? 'data-required="yes"': ''; ?> />
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <th>Email:*</th>
                    <td><input type="email" name="member_email" data-required="yes" value="<?php echo $row['member_email']; ?>" /></td>
                </tr>
                <tr>
                    <th>Mobile Phone(s):</th>
                    <td>
                        <ul style="padding: 0px;margin:0px" class="alternateOff phoneNumbers">
                            <li style="border-bottom:1px dashed #bbb">
                                <strong>Primary:*</strong>
                                <input type="tel" name="member_mobile_phone" id="member_mobile_phone" data-required="yes" value="<?php echo $row['member_mobile_phone']; ?>" pattern="\+?[0-9]+" onkeyup="validatePhone(this)" />
                            </li>
                            <li><strong>Secondary:</strong>
                                <input type="tel" name="member_telephone" id="member_telephone" value="<?php echo $row['member_telephone']; ?>" pattern="\+?[0-9]+" onkeyup="validatePhone(this)" />
                            </li>
                        </ul>
                        <i>Number with international code. No spaces (eg:+316123456)</i>
                    </td>
                </tr>
                <tr>
                    <th>Username:*</th>
                    <td><input type="text" style="width:35%" name="username" placeholder="Username" data-required="yes" value="<?php echo str_replace('co-', '', $row['username']); ?>" onkeyup="committeeUsername(this.value)" /> <span id="committeeUsername"></td>
                </tr>
                <tr>
                    <th>Password:*</th>
                    <td><input type="password" style="width:35%" id="committeePassword" name="password" data-required="yes" value="<?php echo $row['password']; ?>" placeholder="Password" onkeyup="checkPasswordStrength()" /> <i class="fa fa-eye" onclick="showPassword()"></i>
                    <?php if (!isset($member_account)) {?>
                    <label onclick="generatePassword();checkPasswordStrength()" class="passwordColor"><i class="fa fa-key"></i>Generate Password</label><br>
                        <i class="passwordInfo"></i>
                        <?php }; ?>
                    </td>
                </tr>
                <tr>
                    <th>Signature:</th>
                    <td>
                        <input type="file" name="signature" accept="image/*" />
                        <div id="signature_preview" style="height: 120px;">
                            <?php if ($user_signature = get_user_signature(isset($row['comemid']) ? $row['comemid'] : $_SESSION['comemid'])) { ?>

                                <img src="<?php echo $user_signature; ?>" width="200px" height="100px" />
                                <i class="fa fa-trash-alt" style="color:red" onclick="delete_signature(<?php echo isset($row['comemid']) ? $row['comemid'] : $_SESSION['comemid']; ?>)"></i>

                            <?php } else { ?>
                                <i>No signature uploaded</i>
                            <?php }; ?>
                        </div>
                    </td>
            </table>
        </div>

        <div style="float: left;width:500px;margin-top:2px;overflow: auto;" id="officesListDiv">
            <div style="max-width: 100%;"><strong>Responsible for the office(s):</strong>
                <?php if ($comDir == 'admin') { ?>
                    <span style="margin-right:20px;"><input type="text" class="search" data-list="officesList" /></span>
                <?php }; ?>
            </div>
            <div>
                <?php if ($comDir == 'committee' and  ((isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 'no') or $_SESSION['user_type'] == 'hqc_office'))
                    $whr = "AND find_in_set(offid, '{$row['member_offices']}')";
                else
                    $whr = '';

                if ($offices = $amdb->get_results("SELECT * FROM offices WHERE status!='deleted' $whr ORDER BY office_name ASC")) {
                    if (trim($row['member_offices']) != '')
                        $member_offices = explode(',', $row['member_offices']);
                ?>
                    <ul class="alternateOn" id="officesList" style="padding:0px;overflow:auto">
                        <?php foreach ($offices as $office) {
                            if (isset($member_offices) && in_array($office['offid'], $member_offices))
                                $checked = 'checked';
                            else
                                $checked = '';
                        ?>
                            <li><label>
                                    <?php
                                    if ($comDir == 'committee') { ?>
                                        <i class="fas fa-caret-right"></i>
                                    <?php } else { ?>
                                        <input type="checkbox" name="member_office[]" value="<?php echo $office['offid']; ?>" <?php echo $checked ?>>
                                    <?php }; ?>
                                    <?php echo $office['office_name']; ?> <span style="color:green">(<?php echo $office['contact_person']; ?>)</span></label></li>
                        <?php
                        } ?>
                        <?php if (isset($_SESSION['super_admin']) and  $_SESSION['super_admin'] == 'no') { ?>
                            <li style="margin-top:20px;font-style:italic;padding:20px">If you would like to add one or more offices to your account,<br>please contact Mr. Wasim.</li>
                        <?php }; ?>
                    </ul>
                <?php }; ?>
            </div>
        </div>
        <div style="text-align: center;clear:both"><?php if (!isset($member_account)) { ?><input type="button" value="Cancel" onclick="location = '/admin/committee/'" /><?php }; ?> <input type="reset" value="Reset" /><input type="submit" value="Save" /></div>
    </div>
</form>
<script>
    do_document_ready();
    jQuery(document).ready(function() {
        if (jQuery("#officesList")) {
            jQuery("#officesList").height(jQuery("#committeeMembers").height() - 50);
        }
    })
</script>