<?php
session_start();
if (!isset($_SESSION["username"])) {
    exit();
};

if (isset($_POST['act'])) {
    include "../../config/paths.inc.php";
    include "../../config/mysql_ftp.inc.php";
    include "../../config/connect.inc.php";

    if ($_POST['act'] == "update_payment_term" and isset($_POST['clid']) && isset($_POST['first_reminder'])) {
        if ($amdb->update("hqc_default_invoice_reminders", $_POST, "clid = '" . $_POST['clid'] . "'")) {
            echo true;
        } elseif ($amdb->insert("hqc_default_invoice_reminders", $_POST, "clid = '" . $_POST['clid'] . "'")) {
            echo true;
        } else {
            echo 'Error: Unable to update payment term.';
        }
        exit();
    }

    if ($_POST['act'] == "updateReminder" and isset($_POST['clid'])) {
        if ($amdb->get_row("SELECT * FROM `hqc_default_invoice_reminders` WHERE clid = '" . $_POST['clid'] . "'")) {
            $amdb->update("hqc_default_invoice_reminders", $_POST, "clid = '$_POST[clid]'");
        } else {
            $amdb->insert("hqc_default_invoice_reminders", $_POST);
        };
        $posted = json_encode($_POST);
        echo '<script>parent.reminderUpdated("' . $_POST['clid'] . '",\'' . $posted . '\')</script>';
        exit();
    }

    if ($_POST['act'] == "changeReminderStatus" and isset($_POST['clid']) and isset($_POST['status'])) {
        if ($amdb->get_row("SELECT * FROM `hqc_default_invoice_reminders` WHERE clid = '" . $_POST['clid'] . "'")) {
            if ($amdb->query("UPDATE `hqc_default_invoice_reminders` SET status = '" . $_POST['status'] . "' WHERE clid = '" . $_POST['clid'] . "'")) {
                echo 'ok';
            }
        } else {
            if ($amdb->query("INSERT INTO `hqc_default_invoice_reminders` SET clid = '" . $_POST['clid'] . "', status = '" . $_POST['status'] . "'")) {
                echo 'ok';
            }
        }
    }

    if ($_POST['act'] == "changeBulkReminderStatus" and isset($_POST['clids']) and isset($_POST['status'])) {
        if (is_array($_POST['clids']) and count($_POST['clids']) > 0) {
            foreach ($_POST['clids'] as $clid) {
                if ($amdb->get_row("SELECT * FROM `hqc_default_invoice_reminders` WHERE clid = '$clid'")) {
                    $amdb->query("UPDATE `hqc_default_invoice_reminders` SET status = '" . $_POST['status'] . "' WHERE clid = '$clid'");
                } else {
                    $amdb->query("INSERT INTO `hqc_default_invoice_reminders` SET clid = '" . $clid . "', status = '" . $_POST['status'] . "'");
                }
            }
            echo 'ok';
        }
    }

    if ($_POST['act'] == "massUpdateReminders" and isset($_POST['clid']) and isset($_POST['status'])) {
        $clids = explode(',', $_POST['clid']);
        if (is_array($clids)  &&  count($clids) > 0) {
            foreach ($clids as $clid) {
                $_POST['clid'] = $clid;
                if ($amdb->get_row("SELECT * FROM `hqc_default_invoice_reminders` WHERE clid = '$clid'")) {
                    $amdb->update("hqc_default_invoice_reminders", $_POST, "clid = '$clid'");
                } else {
                    $amdb->insert("hqc_default_invoice_reminders", $_POST);
                };
            }
            $amdb->post_results('', 'reload');
        }
    }
}
