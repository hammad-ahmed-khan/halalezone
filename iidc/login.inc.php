<?php if (!defined("_HQC_")) {
    exit();
}; ?>
<script>
    $(document).ready(function(e) {
        $("#loginTable").height($(this).height())
        $("#loginDiv").css({
            visibility: "visible"
        })
        $("#username").keypress(function(event) {
            if (event.which == 13) {
                doLogIn();
                return false;
            }
        });
        $("#password").keypress(function(event) {
            if (event.which == 13) {
                doLogIn();
                return false;
            }
        });
    });

    function switchLoginPass(showDiv) {
        jQuery("form input[type=text]").val('');
        $("#doLogin,#doSendPassword").css({
            display: "none"
        })
        $("#" + showDiv).css({
            display: "block"
        })
    }

    function inputOnOff(obj, onOff) {
        if (onOff == "on") {
            if (obj.name == "username" && obj.value == "Username")
                obj.value = "";
            if (obj.name == "password" && obj.value == "Password") {
                obj.value = "";
            }
            if (obj.name == "email" && obj.value == "Email address")
                obj.value = "";
        }
        if (onOff == "off") {
            if (obj.name == "username" && obj.value == "")
                obj.value = "Username";
            if (obj.name == "password" && obj.value == "") {
                obj.value = "Password";
            }
            if (obj.name == "email" && obj.value == "")
                obj.value = "Email address";
        }
    }

    function doLogIn() {
        if ($("#username").val() == "Username" || $("#username").val() == "" || $("#password").val() == "Password" || $("#password").val() == "") {
            alert("Please fill in the username and  password");
            return false;
        } else {
            var time = new Date().getTime();
            $.post("/login_out.php?tm=" + time, {
                    act: "logIn",
                    username: $("#username").val(),
                    password: $("#password").val()
                },
                function(data) {
                    if (data != "") {
                        var host = "<?php echo (is_local() == false) ? '' : ''; ?>";
                        if (data.indexOf("success_office") > -1)
                            location = host + "/offices/home/"
                        else if (data.indexOf("success_admin") > -1)
                            location = host + "/admin/";
                        else if (data.indexOf("success_auditor") > -1)
                            location = host + "/audit/";
                        else if (data.indexOf("success_client") > -1)
                            location = host + "/company/";
                        else
                            alert(data);
                    };
                });
        }
        return false;
    }

    function sendMeThePas() {
        if (($("#email").val().indexOf("@") <= 0) || ($("#email").val().indexOf(".") <= 0)) {
            alert("Please enter a valid email address");
            return false;
        } else {
            var time = new Date().getTime();
            $.post("login_out.php?tm=" + time, {
                    act: "sendPasswords",
                    email: $("#email").val()
                },
                function(data) {
                    if (data != "") {
                        if (data.indexOf(':')) {
                            reData = data.split(':');
                            if (reData[0] == "success") {
                                alert(reData[1]);
                                switchLoginPass('doLogin')
                            } else {
                                alert(data);
                            }
                        }
                    };
                });
        }
        return false;
    }
</script>
<style>
    a:hover {
        color: red !important
    }
</style>

<table width="100%" height="100%" id="loginTable">
    <tr>
        <td width="100%" height="100%" align="center" valign="middle" style="vertical-align:middle">
            <div id="loginDiv">
                <div id="logingLogo"><img src="/images/iidc-logo.png" /></div>
                <?php if (isset($underConstruction['login']) and isset($underConstruction['under_construction_message'])) { ?>
                    <div class="loginUnderConstruction">
                        <?php echo $underConstruction['under_construction_message']; ?>
                    </div>
                <?php } else { ?>
                    <div id="doLogin">
                        <form action="" onsubmit="return doLogIn();">
                            <div style="padding:0px 30px 30px 30px">
                                <h1>
                                    <center>IIDC Portal - Log-in</center>
                                </h1>
                                <div><input type="text" name="username" title="Username" id="username" style="width:100%" placeholder="Username" onblur="inputOnOff(this,'off')" onfocus="inputOnOff(this,'on')" /></div>
                                <div><img src="/images/login.png" style="float:right;margin:3px 5px" title="Log-in" onclick="doLogIn()" /><input type="password" name="password" id="password" style="width:200px" title="Password" placeholder="Password" onblur="inputOnOff(this,'off');" onfocus="inputOnOff(this,'on');" /></div>
                                <br />
                                <center><span onclick="switchLoginPass('doSendPassword')">Lost password</span></center>
                            </div>
                        </form>
                    </div>
                    <div id="doSendPassword" style="display:none">
                        <form action="login_out.php" name="sendMeThePassword" id="sendMeThePassword" method="post" onsubmit="return post_this_form(this);">
                            <input type="hidden" name="act" value="sendPassword" />
                            <div style="padding:0px 30px 30px 30px">
                                <h1>
                                    <center>Lost password</center>
                                </h1>
                                <div><img src="/images/email.png" style="float:right;margin:3px 5px" title="Send me the password" onclick="jQuery('#sendMeThePassword').submit();" /><input type="text" name="lost_email" id="email" style="width:200px" placeholder="Username or Email address" onblur="inputOnOff(this,'off')" onfocus="inputOnOff(this,'on')" data-required="yes" /></div>
                                <br />
                                <center><span onclick="switchLoginPass('doLogin');">Log-in</span></center>
                            </div>
                        </form>
                    </div>
                <?php }; ?>
                <center>
                    <?php echo ($_SERVER['REMOTE_ADDR'] == '::1' or $_SERVER['REMOTE_ADDR'] == '127.0.0.1') ? '<div style="color:red;text-align:center">LOCAL</div>' : ''; ?>
                    <?php if (isset($underConstruction['register']) and isset($underConstruction['under_construction_message'])) { ?>
                        <?php echo !isset($underConstruction['login']) ? $underConstruction['under_construction_message'] : ''; ?>
                    <?php } else { ?>
                        <a style="color:#000;font-weight:bold; font-size:14px;display:inline-block" onclick="document.location.href='company/?inc=register'">Register your company</a>
                    <?php }; ?>
                </center>
            </div>

        </td>
    </tr>
</table>