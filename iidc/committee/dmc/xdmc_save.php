
<?php
session_start();
echo session_id();
ini_set('display_errors', 1);
error_reporting(E_ALL);
print_r($_SESSION);
exit();


if (!isset($_POST['act'])) {
    exit();
}
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($_POST['act'] == 'save') {
    $decision['event_details'] = json_encode(array(
        'date' => date('Y-m-d'),
        'time' => date('H:i', strtotime('+2 hours')),/*now time + 2 hours*/
        'zoom-topic' => 'DCM Meeting For: ' . $_POST['company_name'],
        'zoom-link' => '',
        'location' => 'Online'
    ));
    $decision['decision'] = serialize($_POST);
    $decision['crtNr'] = $_POST['crtNr'];
    $decision['clid'] = $_POST['clid'];
    $decision['uid'] = $_POST['uid'];
    $decision['offid'] = $_POST['offid'];
    $decision['status'] = 'approved';
    $decision['dmr_reference'] = $_POST['dmr_reference'];
    $decision['comemids'] = implode(',', $_POST['comemids']);
    $decision['branch'] = json_encode($_POST['branch']);

    if ($result = $amdb->get_row("SELECT * FROM hqc_committee_decision WHERE decision='$decision[decision]'")) {
        $decid = $result['decid'];
    } else {
        $decid = $amdb->insert("hqc_committee_decision", $decision);
    }

    if (isset($decid)) {

    //    include "dmc.mail.php";

        $dmc_file = $root_path . '/data/DMC/reports/dmc-' . $decid . '.pdf';
       $dmc_file = '';
        include "dmc.pdf.php";
        if (file_exists($dmc_file)) {
            echo $dmc_file;
            //update certificate status
            $amdb->update('acms_halal_certificates', array('approved_by_dmc' => 'yes', 'decid' => $decid), "crtNr = '$_POST[crtNr]' AND clid = '$_POST[clid]'");
            if ($_REQUEST['ref'] == 'list') {
                echo '<script>top.location = "/certificates/annual/?inc=certificates";</script>';
            } else {
                echo "<script>
	window.parent.document.addEditForm.action	= 'certificate.pdf.php';
	window.parent.document.addEditForm.target = '_blank';
	window.parent.document.addEditForm.submit();
    window.parent.location = '/certificates/annual/index.php?inc=certificates&offid=" . $_POST['offid'] . "';
	</script>";
            }
            //  header('location: /certificates/annual/?inc=certificates');
            exit();
        } else {
            echo "error:Decision saving failed!";
        }
    } else {
        echo "error:Decision saving failed!";
    }
    exit();
}
