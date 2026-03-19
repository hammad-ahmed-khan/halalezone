<?php
if (!isset($_POST['act'])) {
    exit();
}

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

if (!session_id()) 
	session_start();
header('Content-Type: text/html; charset=utf-8');
if (!defined('_HQC_'))
	define("_HQC_", 1);


include dirname(__FILE__) . "/../../config/paths.inc.php";
include "$prog_path/config/date_conv.inc.php";
if ($amdb->get_row("SELECT * FROM users where ip='$_SERVER[REMOTE_ADDR]' and active='b'")) {
	echo "You are not allowed to use this site";
	return;
}
include_once dirname(__FILE__) .'/../../../config/config.php';
include_once dirname(__FILE__) .'/../../../classes/users.php';
include_once dirname(__FILE__) .'/../../../includes/func.php';

//include "../../check_user.inc.php";
//include "$prog_path/config/connect.inc.php";

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
        $dmc_file = $root_path . '/iidc/data/DMC/reports/dmc-' . $decid . '.pdf';
        // $dmc_file = '';
        include "dmc.pdf.php";
 
        $amdb->update('acms_halal_certificates', array('status' => 'printed', "printed_on" => time(), 'approved_by_dmc' => 'yes', 'decid' => $decid), "crtNr = '$_POST[crtNr]' AND clid = '$_POST[clid]'");

        if (file_exists($dmc_file)) {
            //update certificate status
            if ($_REQUEST['ref'] == 'reprint') {
                $dmcPdfUrl = '/iidc/data/DMC/reports/dmc-' . $decid . '.pdf';
                echo '<script>
                    var xhr = new XMLHttpRequest();
                    xhr.open("GET", "' . $dmcPdfUrl . '", true);
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            parent.location = "/iidc/certificates/annual/?inc=certificates";
                        }
                    };
                    xhr.send();
                </script>';
                exit();
            }            

            // Generate certificate PDF via AJAX, then redirect to certificates list
            $certPrintUrl = '/iidc/certificates/annual/certificate.pdf.php?crtnr=' . $_POST['crtNr'] . '&crtDo=print';
            echo '<script>
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "' . htmlspecialchars($certPrintUrl) . '", true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        window.location = "/iidc/certificates/annual/?inc=certificates";
                    }
                };
                xhr.send();
            </script>';
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