<?php if (!defined("_HQC_")) {
    exit();
};
?>
<script language="javascript">
    if (typeof pageTitle == 'function')
        pageTitle('change this title');
    var reqs = ['username_owner', 'username', 'email', 'password'];

    function checkForm() {
        var err = "no";
        for (var i = 0; i <= reqs.length - 1; i++) {
            if (document.getElementById(reqs[i]).value == "") {
                document.getElementById(reqs[i]).style.backgroundColor = "#FFD9D9";
                err = "yes";
            } else {
                document.getElementById(reqs[i]).style.backgroundColor = "";
            }
        }
        if (err == "yes") {
            alert("Fields with * are required");
            return false;
        }
        return true;
    }
</script>
<?php
if (isset($act) and $act == "edit") {
    $uid = $_GET['uid'];
    include_once "$prog_path/config/mysql_ftp.inc.php";
    include_once("$prog_path/config/connect.inc.php");
    $row = array();
    if ($row = $amdb->get_row("SELECT * FROM hqc_admin_users where  uid = '$uid'")) {
        $hqc_user_permissions = explode(",", str_replace('"', '', $row['permissions']));
    };
} else {
    $act = "add";
};
?>
<style>
    ul {
        padding: 0px;
        padding-left: 10px
    }
</style>
<script>
    $(document).ready(function(e) {
        <?php if (isset($hqc_user_permissions) and count($hqc_user_permissions) > 0) {
            if (in_array("menu_clients", $hqc_user_permissions))
                echo "jQuery('#clients_permissions input').attr({'disabled':false})\n";
            if (in_array("menu_certificates", $hqc_user_permissions))
                echo "jQuery('#certificates_permissions input').attr({'disabled':false})\n";
            if (in_array("menu_invoices", $hqc_user_permissions))
                echo "jQuery('#invoices_permissions input').attr({'disabled':false})\n";
            if (in_array("menu_ac", $hqc_user_permissions))
                echo "jQuery('#ac_permissions input').attr({'disabled':false})\n";
            if (in_array("menu_ac_products", $hqc_user_permissions))
                echo "jQuery('#ac_products_permissions input').attr({'disabled':false})\n";
            if (in_array("menu_ac_audit", $hqc_user_permissions))
                echo "jQuery('#ac_audit_permissions input').attr({'disabled':false})\n";
            if (in_array("menu_ac_supervisor", $hqc_user_permissions))
                echo "jQuery('#ac_supervisor_permissions input').attr({'disabled':false})\n";

            foreach ($hqc_user_permissions as $value)
                echo "jQuery('#$value').attr({'checked':true})\n";
            if (in_array("clients_allowed", $hqc_user_permissions))
                echo "jQuery('#clients_allowed').val()\n";
        }
        ?>
    });

    function showAdEditDialog(ttl) {
        jQuery.post("user_clients_list.php?uid=<?php echo $_GET['uid']; ?>")
            .done(function(data) {
                if (data.trim().length > 0) {
                    $("#user_allowed_clients").html(data);
                    $("#addEditDialog").attr({
                        "title": ttl
                    });
                    $("#addEditDialog").dialog({
                        resizable: false,
                        height: 420,
                        width: 580,
                        modal: true
                    });
                } else {
                    alert('Something went wrong with the log-in, please try again.');
                }
            });
    }

    function getUserClients() {
        $("#addEditDialog").dialog("destroy");
        var boxesChecked = [];
        $('#user_allowed_clients input[type="checkbox"]').each(function() {
            if ($(this).is(":checked")) {
                boxesChecked.push($(this).val());
            }
        });
        $("#clients_allowed").val(boxesChecked);
    }

    function selectAllUserClients() {
        $('#user_allowed_clients input[type="checkbox"]').each(function() {
            $(this).attr('checked', 'checked');
        });
    }

    function deselectAllUserClients() {
        $('#user_allowed_clients input[type="checkbox"]').each(function() {
            $(this).removeAttr('checked');
        });
    }
</script>
<div id="addEditDialog" style="display:none;overflow:auto !important">
    <ol id="user_allowed_clients" class="table table-striped table-bordered">
    </ol>
    <center>
        <input type='button' onclick="selectAllUserClients()" value="..Select all.." />
        <input type='button' onclick="deselectAllUserClients()" value="..Deselect all.." />
        <input type='button' onclick="getUserClients()" value="..OK.." />
    </center>
</div>
<center>
    <form method="post" action="admin_users_save.php" name="addEditForm" onsubmit="return checkForm()">
        <input type="hidden" name="act" value="<?php echo @$act ?>">
        <input type="hidden" name="goback" value="<?php echo @$_GET['goBack'] ?>">
        <?php
        if (isset($act) and $act == "edit") {
        ?>
            <input type="hidden" name="uid" value="<?php echo @$row['uid']; ?>">
        <?php
        }
        ?>
        <table id="addEditTbl" style="border:1px solid #EEE">
            <tr>
                <td colspan="4" class="sub_title">
                    <center><?php echo ($act == "edit") ? "Edit user" : "Add new user" ?></center>
                </td>
            </tr>
            <tr>
                <th>Username owner:* </th>
                <td><input type="text" name="username_owner" id="username_owner" value="<?php echo ($act == 'edit') ? @$row['username_owner'] : ''; ?>" /></td>
                <th>Username:*</th>
                <td>
                    <input type="text" name="username" id="username" value="<?php echo ($act == 'edit') ? @$row['username'] : ''; ?>" />
                </td>
            </tr>
            <tr>
                <th>Email:*</th>
                <td><input type="text" name="email" id="email" value="<?php echo ($act == 'edit') ? @$row['email'] : ''; ?>" /></td>
                <th>Password:*</th>
                <td><input type="<?php echo (isset($_SESSION['sys_admin'])) ? 'text' : 'password'; ?>" name="password" id="password" value="<?php echo ($act == 'edit') ? @$row['password'] : ''; ?>" /></td>
            </tr>
            <tr>
                <th>
                    User Type:*
                </th>
                <td colspan="3">
                    <select size="1" name="user_role" id="user_type">
                        <option value="admin" <?php echo ($act == 'edit' and $row['user_role'] == "admin") ? "selected" : ""; ?>>Admin User</option>
                        <option value="super_admin" <?php echo ($act == 'edit' and $row['user_role'] == "super_admin") ? "selected" : ""; ?>>Super admin</option>
                        <option value="sys_admin" <?php echo ($act == 'edit' and $row['user_role'] == "sys_admin") ? "selected" : ""; ?>>System Admin</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>
                    Predefined prices:
                </th>
                <td>
                    <label><input type="checkbox" name="permissions[predefined_prices]" <?php echo (in_array('predefined_prices', $hqc_user_permissions)) ? 'checked' : ''; ?> />Permitted to set default prices</label>
                </td>
                <th>Auditor/Supervisor:*</th>
                <td><label><input type="checkbox" id="auditor" name="permissions[auditor]" /> Auditor</label>
                    <label><input type="checkbox" id="supervisor" name="permissions[supervisor]" /> Supervisor</label>
                </td>
            </tr>
            <tr>
                <th>Permissions:</th>
                <td colspan="3">
                    <table width="100%" class="innerTbls">
                        <tr>
                            <td width="50%" nowrap>
                                <div class="sub_title">Home</div>
                                <div id="home_permissions">
                                    <ul class="alternate">
                                        <li><input type="checkbox" id="home_ceriticate_actions" name="permissions[home_ceriticate_actions]" />Ceriticate - (print / authorize / delete / activate)</li>
                                        <!--li><input type="checkbox" id="" name="permissions[home_companie_activat]" disabled/>Companie - activate</li-->
                                        <li><input type="checkbox" id="home_send_documents" name="permissions[home_send_documents]" />Send Documents</li>
                                    </ul>
                                </div>
                            </td>
                            <td nowrap>
                                <div class="sub_title"><input type="checkbox" id="menu_clients" name="permissions[menu_clients]" onClick="if(this.checked){$('#clients_permissions input').attr({'disabled':false})}else{$('#clients_permissions input').attr({'checked':false});$('#clients_permissions input[type=checkbox]').attr({'disabled':true});}" />Clients</div>
                                <div id="clients_permissions">
                                    <ul class="alternate">
                                        <li><input type="hidden" id="clients_allowed" name="clients_allowed" value="<?php echo ($act == "edit") ? $row['clients_allowed'] : ""; ?>" />
                                            <input type="button" value="Clients allowed to work with" onclick="showAdEditDialog('List of clients')" />
                                        </li>
                                        <li><input type="checkbox" id="clients_add" name="permissions[clients_add]" disabled />Add new client</li>
                                        <li><input type="checkbox" id="clients_actions" name="permissions[clients_actions]" disabled />Edit / delete / suspend</li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="sub_title"><input type="checkbox" id="menu_certificates" name="permissions[menu_certificates]" onClick="if(this.checked){$('#certificates_permissions input').attr({'disabled':false})}else{$('#certificates_permissions input').attr({'checked':false});$('#certificates_permissions input').attr({'disabled':true});}" />Batch Certificates</div>
                                <div id="certificates_permissions">
                                    <ul class="alternate">
                                        <li><input type="checkbox" id="certificates_requested" name="permissions[certificates_requested]" disabled />Requested certificates</li>
                                        <li><input type="checkbox" id="certificates_actions" name="permissions[certificates_actions]" disabled />Requested certificates actions</li>
                                        <li><input type="checkbox" id="certificates_sent" name="permissions[certificates_sent]" disabled />Sent documents</li>
                                    </ul>
                                </div>
                            </td>
                            <td>
                                <div class="sub_title"><input type="checkbox" id="menu_invoices" name="permissions[menu_invoices]" onClick="if(this.checked){$('#invoices_permissions input').attr({'disabled':false})}else{$('#invoices_permissions input').attr({'checked':false});$('#invoices_permissions input').attr({'disabled':true});}" />Invoices</div>
                                <div id="invoices_permissions">
                                    <ul class="alternate">
                                        <li><input type="checkbox" id="abn-amro" name="permissions[abn-amro]" disabled />Show ABN-AMRO transactions</li>
                                        <li><input type="checkbox" id="invoices_show_nl" name="permissions[invoices_show_nl]" disabled />Show invoices</li>
                                        <li><input type="checkbox" id="invoices_create" name="permissions[invoices_create]" disabled />Create invoice</li>
                                        <li><input type="checkbox" id="invoices_draft_only" name="permissions[invoices_draft_only]" disabled />Draft only</li>
                                        <li><input type="checkbox" id="invoices_totals" name="permissions[invoices_totals]" disabled />Show totals</li>
                                        <li><input type="checkbox" id="invoices_search" name="permissions[invoices_search]" disabled />Invoice search</li>
                                        <li><input type="checkbox" id="invoices_actions" name="permissions[invoices_actions]" disabled />Actions</li>
                                    </ul>
                                </div>

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="sub_title"><input type="checkbox" id="menu_ac" name="permissions[menu_ac]" id="menu_ac" onClick="if(this.checked){$('#ac_permissions input').attr({'disabled':false})}else{$('#ac_permissions input').attr({'checked':false});$('#ac_permissions input').attr({'disabled':true});}" />Annual Certificates</div>
                                <div id="ac_permissions">
                                    <ul class="alternate">
                                        <li><input type="checkbox" id="ac_request_certificates" name="permissions[ac_request_certificates]" disabled />Request Certificates</li>
                                        <li><input type="checkbox" id="ac_reissue_remove" name="permissions[ac_reissue_remove]" disabled />Reissue / Remove Certificates</li>
                                        <li><input type="checkbox" id="ac_print_certificates" name="permissions[ac_print_certificates]" disabled />Print Certificates</li>
                                    </ul>
                                </div>
                                <div class="sub_title"><input type="checkbox" id="menu_ac_products" name="permissions[menu_ac_products]" onClick="if(this.checked){$('#ac_products_permissions input').attr({'disabled':false})}else{$('#ac_products_permissions input').attr({'checked':false});$('#ac_products_permissions input').attr({'disabled':true});}" />Products</div>
                                <div id="ac_products_permissions">
                                    <ul class="alternate">
                                        <li><input type="checkbox" id="ac_add_products" name="permissions[ac_add_products]" disabled />Add Products</li>
                                        <li><input type="checkbox" id="ac_edit_products" name="permissions[ac_edit_products]" disabled />Edit Products</li>
                                        <li><input type="checkbox" id="ac_delete_products" name="permissions[ac_delete_products]" disabled />Delete Products</li>
                                        <li><input type="checkbox" id="ac_approve_products" name="permissions[ac_approve_products]" disabled />Approve Products</li>
                                    </ul>
                                </div>
                            </td>
                            <!--
TODO: add joined accounts to the new system
                            -->
                            <td>
                                <div class="sub_title">Joined accounts</div>
                                <ul id="joinedOffices" style="max-height:180px;overflow: auto;">
                                    <?php if ($offices = get_offices()) {
                                        $offids = array();
                                        if ($joined = $amdb->get_row("SELECT * FROM hqc_joined_accounts WHERE uid = '$uid' AND id_type='uid'")) {
                                            $offids = explode(',', $joined['accounts']);
                                        }

                                        foreach ($offices as $office) {
                                    ?>
                                            <li><label><input type="checkbox" name="account[]" value="<?php echo $office['offid']; ?>" <?php echo (in_array($office['offid'], $offids)) ? 'checked' : ''; ?>><?php echo $office['office_name']; ?></label></li>
                                    <?php
                                        }
                                    }
                                    ?>
                                </ul>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php if ($_SESSION['uid'] == 4 or $_SESSION['uid'] == 5 or $_SESSION['uid'] == 11) { ?>
                <tr>
                    <th>Reference halal standards:</th>
                    <td>
                        <label>
                            <input type="checkbox" value="yes" name="permissions[MS-standards]" <?php echo (isset($hqc_user_permissions) &&  in_array('MS-standards', $hqc_user_permissions)) ? 'checked' : ''; ?>>MS Reference halal standards enabled?</input>
                        </label>
                    </td>
                </tr>
            <?php }; ?>
            <tr>
                <th>Active:</th>
                <td colspan="3">
                    <select size="1" name="active" id="active">
                        <option value="y">Yes</option>
                        <option value="n" <?php echo ($act == 'edit' and $row['active'] == "n") ? "selected" : ""; ?>>No</option>
                    </select>
            <tr>
                <td colspan="4" class="sub_title">
                    <center><input type="reset" value="Reset" /><input type="submit" value="<?php echo ($act == "edit") ? "Update" : "Add" ?>" /></center>
                </td>
            </tr>
        </table>
    </form>
</center>