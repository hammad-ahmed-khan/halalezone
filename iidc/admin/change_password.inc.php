<?php
if ($_SESSION['user_role'] == "super_admin") {
    include "../checkuser.inc.php";
    include "../config/paths.inc.php";
    include "../config/mysql_ftp.inc.php";
    include "../config/connect.inc.php";
    if (isset($act) and $act == 'update') {
        $result = MYSQL_QUERY("SELECT * FROM hqc_admin_users where  username = '$username'");
        if (@MYSQL_NUM_ROWS($result) == 0)
            MYSQL_QUERY("INSERT INTO hqc_admin_users (username_owner,username,password,email,active) VALUES ('Administrator','admin','$password1','$email','y')");
        else
            MYSQL_QUERY("UPDATE hqc_admin_users SET email='$email',password='$password1' where username='admin'");
        echo "<b>Administrator's password is saved</b>";
    } else {
        $result = MYSQL_QUERY("SELECT * FROM hqc_admin_users where username='admin'");
        if (@MYSQL_NUM_ROWS($result) > 0) {
            $row = MYSQL_FETCH_ARRAY($result);
        }
?>
        <script language="javascript">
            $("#page_title").html("Change Administrator password")

            function checform() {
                var err;
                for (var i = 0; i <= document.forms[0].elements.length - 1; i++) {
                    if (document.forms[0].elements[i].getAttribute('data-req')) {
                        document.forms[0].elements[i].style.backgroundColor = "";
                        if (document.forms[0].elements[i].value == "") {
                            document.forms[0].elements[i].style.backgroundColor = "#FFD9D9";
                            err = "y";
                        }
                    }
                }
                if (err == "y") {
                    alert("Fields with (*) are required")
                    return false;
                }
                if ((document.forms[0].email.value.indexOf("@") <= 0) || (document.forms[0].email.value.indexOf(".") <= 0)) {
                    alert('Please insert a valid email address');
                    return false;
                }
                if ((document.forms[0].password1.value.length < 5) || (document.forms[0].password1.value.length > 12)) {
                    alert('Passowrd should be between (5 and 12) charachters');
                    return false;
                }
                if (document.forms[0].password1.value != document.forms[0].password2.value) {
                    alert('Two passwords are not the same');
                    return false;
                }
                document.forms[0].submit();
            }
        </script>
        <form name='passform' action="index.php?inc=change_password" method="post">
            <input name="username" type="hidden" value="<?php echo @$row['username']; ?>" />
            <input name="act" type="hidden" value="update" />
            <table style="border:1px solid #EEE" id="adminTbl" class="alternate">
                <tr>
                    <th colspan="2" class="sub_title">
                        <center>Change Administrator password</center>
                    </th>
                </tr>
                <tr>
                    <th>Username owner:</th>
                    <td><b>Administrator</b></td>
                </tr>
                <tr>
                    <th>Username:</th>
                    <td>admin</td>
                </tr>
                <tr>
                    <th>Email*:</th>
                    <td><input name="email" type="text" size=20 data-req="y" style="background-color:" value="<?php echo  @$row['email']; ?>"></td>
                </tr>
                <tr>
                    <th>Old passowrd:</th>
                    <td>************</td>
                </tr>
                <tr>
                    <th>New passowrd*:</th>
                    <td><input name="password1" type="password" size=20 data-req="y" style="background-color:"></td>
                </tr>
                <tr>
                    <th>Retype passowrd*:</th>
                    <td><input name="password2" type="password" size=20 data-req="y" style="background-color:"></td>
                </tr>
                <tr>
                    <td colspan="2" class="sub_title">
                        <center><input name="" type="reset" value="Reset"><input name="save" type="button" value="Save" onClick="checform()">
                    </td>
                </tr>
            </table>
        </form>
<?php };
} ?>