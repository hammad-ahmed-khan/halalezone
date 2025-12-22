<?php
/*--File name (hqc_admin_users_save.php)--*/
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
//TODO: upload the file to the server
if (isset($_REQUEST['act']) and $_REQUEST['act'] == "approveAnnuals") {
    $handled_by = array("uid" => $_SESSION['user']['uid'], "action" => "Approved", "name" => $_SESSION['user']['name']);

    if (isset($_POST['crtNr']) and count($_POST['crtNr']) > 0) {
        foreach ($_POST['crtNr'] as $key => $crtNr) {
            if ($certificate = $amdb->get_row("SELECT * FROM acms_halal_certificates WHERE crtNr='$crtNr'")) {
                $options = json_decode($certificate['options'], true);
                $options['approved'] = 'yes';
                $options['approved_by'] = $handled_by;
                $options = json_encode($options, JSON_UNESCAPED_UNICODE);
                $handled_by = json_encode($handled_by, JSON_UNESCAPED_UNICODE);

                $amdb->query("UPDATE acms_halal_certificates SET options = '$options', status_authorized_on='" . time() . "', handled_by = '$handled_by' WHERE crtNr='$crtNr'");
            }
        }
        $amdb->post_results("", "reload");
    } else {
        $amdb->post_results("No certificates selected!");
    }
    exit();
}

if (isset($_POST['act']) and $_POST['act'] == "approveShipmentCertificate" && isset($_POST['tp']) && isset($_POST['nr'])) {

    $table = "certificates_" . $_POST['tp'];
    $nr = $_POST['nr'];
    if ($amdb->query("UPDATE $table SET approval_required='approved' WHERE nr='$nr'")) {
        echo "success";
    } else {
        echo "Error with approval";
    }
    exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == "sendCertificate" and isset($_REQUEST['nr'])) {
?>
    <form action="certificates_save.php" method="post" onsubmit="return post_this_form(this);" style="white-space:nowrap">
        <input type="hidden" name="act" value="update_status_sent_on" />
        <input type="hidden" name="nr" value="<?php echo $_REQUEST['nr']; ?>" />
        <input type="hidden" name="tp" value="<?php echo $_REQUEST['tp']; ?>" />
        Sent on: <input type="text" name="status_sent_on" placeholder="Date" class="date" style="width:80px" />
        <input type="submit" value="save" />
    </form>
<?php
    exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == "update_status_sent_on" and isset($_REQUEST['nr'])) {
    if (isset($_POST['status_sent_on']) and trim($_POST['status_sent_on']) != '') {
        $hcd_process = 'Sent on: ' . $_POST['status_sent_on'];
        $amdb->query("update certificates_{$_POST['tp']} set hcd_process='$hcd_process' where  nr = '$_POST[nr]'");
        $amdb->post_results("<b>Sent on:</b> " . $_POST['status_sent_on'], 'html', 'sendCertSpan_' . $_REQUEST['nr']);
    } else {
        $amdb->post_results("Sent on date is missing!");
    }
    exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == "recievedCertificate" and isset($_REQUEST['nr'])) {
?>
    <form action="certificates_save.php" method="post" onsubmit="return post_this_form(this);" style="white-space:nowrap">
        <input type="hidden" name="act" value="update_status_received_on" />
        <input type="hidden" name="nr" value="<?php echo $_REQUEST['nr']; ?>" />
        <input type="hidden" name="tp" value="<?php echo $_REQUEST['tp']; ?>" />
        Arrived on: <input type="text" name="arrived_on" placeholder="Date" class="date" style="width:80px" />
        <input type="submit" value="save" />
    </form>
<?php
    exit();
}
if (isset($_REQUEST['act']) and $_REQUEST['act'] == "undo_printed_authorized" and isset($_REQUEST['nr'])) {
    mysql_query("UPDATE certificates_$_REQUEST[tp] set hcd_process='', printed_on='', doc_nr='0', done='n', url='',invoice_nr='0', is_bad='n',qr='',status='active' where nr='$_REQUEST[nr]'");
    echo "success";
    exit();
}

if (isset($_REQUEST['act']) and $_REQUEST['act'] == "update_status_received_on" and isset($_REQUEST['nr'])) {
    if (isset($_POST['arrived_on']) && trim($_POST['arrived_on']) != '') {
        $amdb->query("update certificates_{$_POST['tp']} set arrived_on='$_POST[arrived_on]', done = 'y' where  nr = '$_POST[nr]'");
        $amdb->post_results("<b>Arrived on:</b> " . $_POST['arrived_on'], 'html', 'recievedCertSpan_' . $_REQUEST['nr']);
    } else {
        $amdb->post_results("Receipt date is missing!");
    }
    exit();
}

if (isset($act) and $act == 'fixCerNr' and isset($nr)) {
    mysql_query("UPDATE certificates_$tp set doc_nr='$doc_nr' where nr='$nr'");
    echo "success";
}
?>