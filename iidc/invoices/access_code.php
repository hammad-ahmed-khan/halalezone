<?php
if (!isset($_REQUEST['act']) or isset($_SESSION['user']))
    exit();
//show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
$uid = $_SESSION['user']['uid'];
if ($_POST['act'] == 'sendMeAccessCode') {
    if ($user = $amdb->get_row("SELECT * FROM `hqc_admin_users` WHERE uid = '$uid'")) {
        $access_code = rand(100000, 999999);
        include $prog_path . "/tools/mail/hqc_mail.inc.php";

        $to_email = $user['email'];
        $to_name = $user['username_owner'];
        $from_email = 'info@iidc.eu';
        $from_name = 'Control Office of Halal Slaughtering and Halal Quality Control B.V.';
        $subject = 'Access code for predefined prices';
        $message = 'Your access code for predefined prices is:<br/><br/><b>' . $access_code."</b><br/><br/>Please enter this code in the predefined prices page to access the predefined prices.";

        if (hqc_mail($to_email, $to_name, $from_email, $from_name, $subject, $message)) {
            $_SESSION['access_code'] = $access_code;
            echo 'Access code sent to your email.<br/>Please check your email';
        } else {
            echo 'Failed to send email';
        }
        exit();
    } else {
        echo 'Email is required';
        exit();
    }
}

if ($_POST['act'] == 'checkAccessCode' && isset($_POST['AccessCode'])) {
    if (!isset($_SESSION['access_code'])) {
        echo 'Access code is expired';
        exit();
    }
    if ($_POST['AccessCode'] == $_SESSION['access_code']) {
        setcookie('predefined', true, time() + (30 * 60), '/');
        echo 'success';
    } else {
        echo 'Access code is incorrect';
    }
    exit();
}
