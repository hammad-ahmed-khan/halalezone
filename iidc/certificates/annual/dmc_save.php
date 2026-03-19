<?php
if (!isset($_POST['act'])) {
    exit();
}
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($_POST['act'] == 'save') {

    // ============================================
    // 1. Save to hqc_dmc_reports (new structured table)
    // ============================================
    $dmc_report = array();
    $dmc_report['crtNr']    = isset($_POST['crtNr']) ? intval($_POST['crtNr']) : 0;
    $dmc_report['clid']     = intval($_POST['clid']);
    $dmc_report['offid']    = intval($_POST['offid']);
    $dmc_report['uid']      = intval($_POST['uid']);

    // Header info
    $dmc_report['dmr_reference'] = isset($_POST['dmr_reference']) ? trim($_POST['dmr_reference']) : '';
    $dmc_report['date_of_dmcr'] = isset($_POST['date_of_dmcr']) ? trim($_POST['date_of_dmcr']) : '';
    $dmc_report['valid_until']  = isset($_POST['valid_until']) ? trim($_POST['valid_until']) : '';
    $dmc_report['company_name'] = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';

    // Production sites
    if (isset($_POST['stids']) && is_array($_POST['stids'])) {
        $dmc_report['production_sites'] = implode(',', $_POST['stids']);
    } else {
        $dmc_report['production_sites'] = '';
    }

    // Section 1: Objective
    $dmc_report['objective'] = isset($_POST['objective']) ? trim($_POST['objective']) : '';
    $dmc_report['objective_others_info'] = isset($_POST['objective_others_info']) ? trim($_POST['objective_others_info']) : '';

    // Section 2: Review (store as JSON)
    $reviewed_data = array();
    if (isset($_POST['reviewed']) && is_array($_POST['reviewed'])) {
        $reviewed_data['reviewed'] = $_POST['reviewed'];
    }
    // Some fields use 'results' key (e.g., AuPl)
    if (isset($_POST['results']) && is_array($_POST['results'])) {
        $reviewed_data['results'] = $_POST['results'];
    }
    // Typo in original form: 'reviewd' for NoCoRe
    if (isset($_POST['reviewd']) && is_array($_POST['reviewd'])) {
        $reviewed_data['reviewd'] = $_POST['reviewd'];
    }
    $dmc_report['reviewed_data'] = json_encode($reviewed_data);

    // Section 3: Conclusion
    $dmc_report['conclusion'] = isset($_POST['conclusion']) ? trim($_POST['conclusion']) : '';
    $dmc_report['conclusion_suspension_period'] = isset($_POST['conclution_time_period_of_suspension']) ? trim($_POST['conclution_time_period_of_suspension']) : '';
    $dmc_report['conclusion_withdrawn_date'] = isset($_POST['conclusion_withdrawn_from_this_date']) ? trim($_POST['conclusion_withdrawn_from_this_date']) : '';
    $dmc_report['conclusion_other_info'] = isset($_POST['conclusion_other_info']) ? trim($_POST['conclusion_other_info']) : '';
    $dmc_report['remarks_on_dmc'] = isset($_POST['remarks_on_the_dmc_report']) ? trim($_POST['remarks_on_the_dmc_report']) : '';

    // Section 4: Committee members
    if (isset($_POST['comemids']) && is_array($_POST['comemids'])) {
        $dmc_report['comemids'] = implode(',', $_POST['comemids']);
    } else {
        $dmc_report['comemids'] = '';
    }
    $dmc_report['signatories_involved_in_audit'] = isset($_POST['signatories_involved_in_the_audit']) ? trim($_POST['signatories_involved_in_the_audit']) : '';

    // Branch info
    if (isset($_POST['branch']) && is_array($_POST['branch'])) {
        $dmc_report['branch']         = isset($_POST['branch']['Branch']) ? trim($_POST['branch']['Branch']) : '';
        $dmc_report['branch_manager'] = isset($_POST['branch']['BranchManager']) ? trim($_POST['branch']['BranchManager']) : '';
        $dmc_report['requested_by']   = isset($_POST['branch']['RequestedBy']) ? trim($_POST['branch']['RequestedBy']) : '';
    }
    $dmc_report['agreed'] = (isset($_POST['agree']) && $_POST['agree'] == '1') ? 1 : 0;
    $dmc_report['status'] = 'approved';

    // ============================================
    // 2. Save/Update hqc_committee_decision (backward compat)
    // ============================================
    $decision = array();
    $decision['decision']      = serialize($_POST);
    if (isset($_POST['crtNr']))
        $decision['crtNr']     = $_POST['crtNr'];
    $decision['clid']          = $_POST['clid'];
    $decision['uid']           = $_POST['uid'];
    $decision['offid']         = $_POST['offid'];
    $decision['status']        = 'approved';
    $decision['dmr_reference'] = isset($_POST['dmr_reference']) ? $_POST['dmr_reference'] : '';
    if (isset($_POST['comemids']) && is_array($_POST['comemids'])) {
        $decision['comemids']  = implode(',', $_POST['comemids']);
    }
    $decision['branch']        = isset($_POST['branch']) ? json_encode($_POST['branch']) : '{}';

    $decid = null;
    if (isset($_POST['decid']) && intval($_POST['decid']) > 0) {
        $decid = intval($_POST['decid']);
        $amdb->update('hqc_committee_decision', $decision, "decid = '$decid'");
    } else {
        $decid = $amdb->insert("hqc_committee_decision", $decision);
    }

    if (isset($decid) && $decid > 0) {
        // Link the decid to the dmc_report
        $dmc_report['decid'] = $decid;

        // Check if a DMC report already exists for this decid
        $existing = $amdb->get_row("SELECT dmcid FROM hqc_dmc_reports WHERE decid = '" . intval($decid) . "'");
        if ($existing) {
            $amdb->update('hqc_dmc_reports', $dmc_report, "dmcid = '" . intval($existing['dmcid']) . "'");
            $dmcid = $existing['dmcid'];
        } else {
            // Also check by crtNr + clid if no decid match
            $existingByCert = $amdb->get_row("SELECT dmcid FROM hqc_dmc_reports WHERE crtNr = '" . intval($dmc_report['crtNr']) . "' AND clid = '" . intval($dmc_report['clid']) . "'");
            if ($existingByCert) {
                $amdb->update('hqc_dmc_reports', $dmc_report, "dmcid = '" . intval($existingByCert['dmcid']) . "'");
                $dmcid = $existingByCert['dmcid'];
            } else {
                $dmcid = $amdb->insert("hqc_dmc_reports", $dmc_report);
            }
        }

        // Generate DMC PDF
        $dmc_file = $root_path . '/data/DMC/reports/dmc-' . $decid . '.pdf';
        include "dmc.pdf.php";

        if (file_exists($dmc_file)) {
            // Update certificate status
            if (isset($_REQUEST['ref']) && $_REQUEST['ref'] == 'reprint') {
                echo '<script>parent.location = "/data/DMC/reports/dmc-' . $decid . '.pdf";</script>';
                exit();
            }

            $amdb->update('acms_halal_certificates', array('approved_by_dmc' => 'yes', 'decid' => $decid), "crtNr = '$_POST[crtNr]' AND clid = '$_POST[clid]'");

            if (isset($_REQUEST['ref']) && $_REQUEST['ref'] == 'list') {
                echo '<script>
    top.location = "/certificates/annual/?inc=certificates";
</script>';
            } else {
                echo "<script>
    window.parent.document.addEditForm.action = 'certificate.pdf.php';
    window.parent.document.addEditForm.target = '_blank';
    window.parent.document.addEditForm.submit();
    window.parent.location = '/certificates/annual/index.php?inc=certificates&offid=" . intval($_POST['offid']) . "';
</script>";
            }
            exit();
        } else {
            echo "error:Decision saving failed! (PDF generation failed)";
        }
    } else {
        echo "error:Decision saving failed! (Database insert failed)";
    }
    exit();
}
