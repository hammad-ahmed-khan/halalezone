<?php
if (!isset($_POST['act'])) {
    exit();
}
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($_POST['act'] == 'save') {
    $decision['decision'] = serialize($_POST);
    if (isset($_POST['crtNr']))
        $decision['crtNr'] = $_POST['crtNr'];
    $decision['clid'] = $_POST['clid'];
    $decision['uid'] = $_POST['uid'];
    $decision['offid'] = $_POST['offid'];
    $decision['status'] = 'approved';
    $decision['dmr_reference'] = $_POST['dmr_reference'];
    $decision['comemids'] = implode(',', $_POST['comemids']);
    $decision['branch'] = json_encode($_POST['branch']);
    if (isset($_POST['decid'])) {
        $decid = $_POST['decid'];
        $amdb->update('hqc_committee_decision', $decision, "decid = '$decid'");
    } else {
        $decid = $amdb->insert("hqc_committee_decision", $decision);
    }

    if (isset($decid)) {
        //   include "dmc.mail.php";
        $dmc_file = $root_path . '/data/DMC/reports/dmc-' . $decid . '.pdf';
        // $dmc_file = '';
        include "dmc.pdf.php";

        if (file_exists($dmc_file)) {
            //update certificate status
            if ($_REQUEST['ref'] == 'reprint') {
                echo '<script>parent.location = "/data/DMC/reports/dmc-' . $decid . '.pdf";</script>';
                exit();
            }
            $amdb->update('acms_halal_certificates', array('approved_by_dmc' => 'yes', 'decid' => $decid), "crtNr = '$_POST[crtNr]' AND clid = '$_POST[clid]'");
            if ($_REQUEST['ref'] == 'list') {
                echo '<script>
    top.location = "/certificates/annual/?inc=certificates";
</script>';
            } else {
                echo "<script>
    window.parent.document.addEditForm.action = 'certificate.pdf.php';
    window.parent.document.addEditForm.target = '_blank';
    window.parent.document.addEditForm.submit();
    window.parent.location = '/certificates/annual/index.php?inc=certificates&offid=" . $_POST['
    offid '] . "';
</script>";
            }
            // header('location: /certificates/annual/?inc=certificates');
            exit();
        } else {
            echo "error:Decision saving failed!";
        }
    } else {
        echo "error:Decision saving failed!";
    }
    exit();
}
