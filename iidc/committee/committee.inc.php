<?php
if (!defined("_HQC_")) {
    exit();
};
if (isset($_GET['status']) && $_GET['status']) {
    $_POST['status'] = "hqc_committee_decision.status = 'approved' ";
    $title = 'History of Committee Meetings';
} else {
    $_POST['status'] = "hqc_committee_decision.status = 'pending'";
    $title = 'New Committee Meetings Requests';
}

// if(!isset($_SESSION['sms_code']))
// require_once "committee.func.php";
$functions = array(
    'ABM' => 'Auditor Board Member',
    'MBM' => 'Management Board Member',
    'SBM' => 'Shariah Board Member',
);
?>
<style>
    .hocMaster {
        background: green;
        cursor: default;

    }

    .hocMember {
        background: #900;
        cursor: default;
    }

    #committeeOl li {
        position: relative;
    }

    #committeeOl {
        margin: 0px;
        padding: 0px;
        padding-left: 20px;
    }


    .actions i {
        margin-bottom: 6px !important;
    }

    .Decisions th,
    .Decisions td {
        background: #efe6d7 !important;
        border: none;
        border-bottom: 5px solid white;
    }

    .subdirectory {
        padding: 0px !important;
        text-align: right;
    }

    .subdirectory img {
        width: 22px;
    }
</style>
<script>
    $("#page_title").html("<?php echo $title; ?>");
    async function resetDMCProcess(decid, status) {
        if (status == 'delete')
            await confirm_message("Are you sure you want to cancel the DMC meeting?");
        else
            await confirm_message("Are you sure you want to undo the DMC decision?");
        $.ajax({
            url: "/committee/app_save.php",
            type: "POST",
            data: {
                "act": "resetDMCProcess",
                "decid": decid,
                "status": status
            },
            success: function(data) {
                if (data == "success") {
                    location.reload();
                } else {
                    alert_message(data);
                }
            }
        })
    }

    function selectOffices(offid) {
        if (offid == '*') {
            jQuery("#committeeTable tbody tr").css("display", "table-row");
        } else {
            jQuery("#committeeTable tbody tr").css("display", "none");
            jQuery("#committeeTable tbody tr").each(function() {
                if (jQuery(this).data('offid') == offid)
                    jQuery(this).css("display", "table-row")
            })
        }
    }

    function editDMC(decid) {
        url = '/committee/dmc/?inc=dmc&act=edit&ref=reprint&decid=' + decid;
        jQuery("#DMCUrl").attr('data-href', url);
        jQuery("#DMCUrl").click();

    }
</script>
<?php
$offices = array();
if ($officesAll = get_offices()) {
    foreach ($officesAll as $office) {
        $offices[$office['offid']]['branch'] = $office['company_name_english'];
        $offices[$office['offid']]['manager'] = $office['contact_person'];
    }
    ksort($offices);
};
?>
<input type="hidden" id="DMCUrl" data-href="" title="Edit DMC Report" data-resize="true" onclick="doIframe(this)"></input>
<h2 style="text-align:center; position:relative;"><?php echo $title; ?>
    <?php /*if (!isset($_GET['status']) && ((isset($_SESSION['super_admin']) && $_SESSION['super_admin'] == 'yes') or trim($_SESSION['member_offices']) != '')) { ?>
        <span style="position: absolute;right:50px;top:-15px;font-size:12px;color:grey;"><a href="?inc=certificates" class="button">Call for a meeting</a></span><?php }; */ ?>
</h2>
<?php if ($_SESSION['user_type'] == 'hqc_user') { ?>
    <div><strong>For brach:</strong>
        <select onchange="selectOffices(this.value)">
            <option value="*">All Branches</option>
            <?php foreach ($offices as $offid => $office) { ?>
                <option value="<?php echo $offid; ?>"><?php echo $office['branch']; ?></option>
            <?php }; ?>
        </select>
    </div>
<?php }; ?>

<table class="alternateOn" id="committeeTable" style="width:100%">
    <thead>
        <tr>
            <th style="width:20px">No</th>
            <!-- <th style="width:20px"></th> -->
            <th style="min-width: 300px;">Company <input type="text" class="search" data-search="company" style="width:250px" /></th>
            <th>Branch / Manager <input type="text" class="search" data-search="branch" style="width:100px" /></th>
            <th>Meeting Details <input type="text" class="search" data-search="meeting" style="width:180px" /></th>
            <th>Committee members <input type="text" class="search" data-search="committee" style="width:100px" /></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $committee_member = array();
        $comIds = array();
        if ($committee_members = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE status='active' ORDER BY member_name ASC")) {
            foreach ($committee_members as $member) {
                $title = (isset($functions[$member['member_function']]) ? $functions[$member['member_function']] : $member['member_function']);
                $committee_member[$member['comemid']] = '<b title="' . $title . '">' . $member['member_name'] . '</b> (' . $member['member_function']  . ')';
                $comIds[] = $member['comemid'];
            }
        }
        $srNr = 1;

        if (isset($_SESSION['comemid']) && $_SESSION['super_admin'] != 'yes')
            $comemSql = "AND FIND_IN_SET('$_SESSION[comemid]',comemids)";
        else
            $comemSql = '';

        if ($_SESSION['offid'] != 0)
            $comemSql .= " AND hqc_committee_decision.offid = $_SESSION[offid]";

        if ($meetings = $amdb->get_results("SELECT *,hqc_committee_decision.status AS status FROM hqc_committee_decision
    JOIN companies ON companies.clid = hqc_committee_decision.clid WHERE $_POST[status] $comemSql ORDER BY meeting_date DESC")) {
            foreach ($meetings as $meeting) {
                $decision = unserialize($meeting['decision']);
                if (!is_null($meeting['sms_codes'])) {
                    $sms_codes = json_decode($meeting['sms_codes'], true);
                }
        ?>
                <tr data-offid="<?php echo $meeting['offid']; ?>">
                    <td>
                        <?php echo $srNr++; ?>
                    </td>
                    <?php /* <td>
                        <input type="checkbox" class="Reschedule" name="crtNrs[]" value="<? php // echo $meeting['crtNr'];
                                                                                            ?>" />
                    </td> */ ?>
                    <td class="company">
                        <b>
                            <?php
                            echo '<span style="cursor:pointer;white-space:normal" class="com com_' . $meeting['clid'] . ' clid load_popup" data-url="../../admin/load_company.php?clid=' . $meeting['clid'] . '" title="' . $meeting['company_name'] . '">' . $meeting['company_name'] . '</span>'; ?></b><br />
                        <?php if ($company = get_client($meeting['clid'])) {
                            echo ($company['client_address']);
                        }; ?>
                    </td>
                    <td style="min-width:250px" class="branch">
                        <?php
                        if (trim($meeting['branch']) != '') {
                            $branch = json_decode($meeting['branch'], true);
                            echo '<strong>B:</strong> ' . $branch['Branch'] . '<br/>';
                            echo '<strong>M:</strong> ' . $branch['BranchManager'] . '<br/>';
                            echo '<strong>R:</strong> ' . (!strstr($branch['RequestedBy'], 'Warning') ? $branch['RequestedBy'] : 'N/A') . '<br/>';
                        } elseif (isset($offices[$meeting['offid']])) {
                            echo '<strong>B:</strong>' . $offices[$meeting['offid']]['branch'] . '<br/>';
                            echo '<strong>M:</strong>' . $offices[$meeting['offid']]['manager'] . '<br/>';
                        } ?>
                    </td>
                    <td style="white-space:nowrap" class="meeting">
                        <?php
                        if (is_array(json_decode($meeting['event_details'], true))) {
                            $event_details = json_decode($meeting['event_details'], true);
                            echo '<b>Meeting Date:</b> ' . date("d/m/Y", strtotime($event_details['date'])) . '<br/><b>Meeting Time:</b> ' . $event_details['time'] . '<br/><b>Meeting Location:</b> ' . $event_details['location'];
                            if (isset($event_details['zoom-link']))
                                echo '<br/><b>Zoom Link:</b> <a href="' . $event_details['zoom-link'] . '" target="_new"><img src="/images/zoom.svg" style="width:16px;position:absolute" title="Click here to join the meeting"/></a>';
                            echo '<br/><b>Requested On:</b> ' . date("d M Y", strtotime($meeting['inserted_on']));
                            if (isset($event_details['request_by']))
                                echo '<br/><b>Requested by:</b> ' . $event_details['request_by'];
                        }
                        ?>
                    </td>
                    <td style="white-space: nowrap;" class="committee">
                        <ol id="committeeOl" class="alternateOff">
                            <?php $members = explode(',', $meeting['comemids']);
                            if (count($members)) {
                                $memCount = 1;
                                foreach ($members as $member) {
                                    if (isset($_SESSION['comemid']) && $meeting['hoc'] == $_SESSION['comemid'])
                                        $hocClass = 'hocMaster';
                                    else
                                        $hocClass = 'hocMember';
                                    if (isset($_SESSION['comemid']) && $_SESSION['comemid'] == 20)
                                        $theCode = '<span style="padding:2px 20px;color:#900;position:absolute;right:50px;display:inline-block">' . $sms_codes[$member]['code'] . '</span>';
                                    else
                                        $theCode = '';
                                    if (isset($committee_member[$member])) {
                                        if (isset($sms_codes[$member]) && isset($sms_codes[$member]['approved']))
                                            $signed = true;
                                        else
                                            $signed = true;
                            ?>
                                        <li>
                                            <i class="fas fa-signature" style="color:<?php echo $meeting['status'] == 'approved' ? 'green' : 'lightgray'; ?>"></i>
                                            <?php echo $committee_member[$member];/*. ($member == $meeting['hoc'] ? '<span style="color:white;padding:0px 10px;position:absolute;right:10px" class="' . $hocClass . '" >HOC</span>' : '') . $theCode;
                                            ?>
                                            <?php if ($_SESSION['comemid'] == $member && $signed == false) { ?>
                                                <a href="/committee/index.php?inc=appoint_new_member&decid=<?php echo $meeting['decid']; ?>&comemid=<?php echo $member; ?>" target="iframe" data-width="800" data-height="500" title="Replace a Member"><i class="fas fa-user-edit" style="color:darkcyan"></i></a>

                                            <?php } */ ?>
                                        </li>
                            <?php }
                                }
                            }
                            ?>
                        </ol>
                        <?php
                        if (trim($meeting['internal_memo']) != '' and is_array(unserialize($meeting['internal_memo']))) { ?>
                            <fieldset style="background: azure; border: 1px solid #bbb;white-space:normal">
                                <legend>Internal Memo</legend>
                                <?php
                                $memo = unserialize($meeting['internal_memo']);
                                foreach ($memo as $comemid => $mem) {
                                    if (isset($committee_member[$comemid])) {
                                        echo '<i class="fas fa-thumbtack" style="color:#900;font-size:12px !important"></i> <span style="color:#900">' . $committee_member[$comemid] . '</span><br/>' . $mem . '<br/><br/>';
                                    }
                                }

                                ?>
                            </fieldset>
                        <?php }; ?>
                    </td>
                </tr>
                <tr class="Decisions" data-offid="<?php echo $meeting['offid']; ?>">
                    <th class="subdirectory"><img src="/images/subdirectory.svg" /></th>
                    <td colspan="4">
                        <b><?php echo $meeting['status'] == 'pending' ? 'Actions' : 'Decision'; ?></b>
                        <?php
                        if (isset($decision['crtNr'])) {
                            $crtNr = $decision['crtNr'];
                            $certificate = $amdb->get_row("SELECT url FROM acms_halal_certificates WHERE crtNr = '$crtNr'");
                            if (isset($certificate['url']) && trim($certificate['url']) != '') {
                                $pdfUrl = "/client_data/certificates/$certificate[url]";
                            }
                        }
                        if (!isset($pdfUrl)) {
                            $pdfUrl = "/certificates/annual/?inc=certificate_add_edit&act=" . ($meeting['status'] == 'pending' ? 'add' : 'edit') . "&crtNr=$meeting[crtNr]&clid=$meeting[clid]&offid=$meeting[offid]&decid=$meeting[decid]";
                        }; ?>
                        <a href="<?php echo $pdfUrl; ?>" <?php echo $meeting['status'] != 'pending' ? 'target="_new"' : ''; ?>><i class="fas fa-certificate" style="color:goldenrod"><span><?php echo $meeting['status'] == 'pending' ? 'Request' : 'View'; ?> Certificate</span></i></a>
                        <?php /* <a href="?inc=app&act=edit&clid=<?php echo $meeting['clid']; ?>&crtNr=<?php echo $meeting['crtNr']; ?>&decid=<?php echo $meeting['decid']; ?>" class="application"><i class="fab fa-wpforms" style="color:cadetblue"><span>DMC Report</span></i></a>*/ ?>
                        <?php if ($meeting['status'] == 'pending') { ?>
                            <a href="/committee/index.php?inc=schedule_committee&decid=<?php echo $meeting['decid']; ?>&act=reschedule&crtNr=<?php echo $meeting['crtNr']; ?>&clid=<?php echo $meeting['clid']; ?>" title="Reschedule committee meeting">
                                <i class="fa fa-user-clock"></i> Reschedule a meeting</a>
                            <i class="fas fa-trash-alt" style="color:#900;font-size:14px !important" onclick="resetDMCProcess(<?php echo $meeting['decid']; ?>,'delete');"><span>Cancel DMC meeting</span></i>

                        <?php }; ?>
                        <?php if ($meeting['status'] == 'approved') {
                            $dmc_file = '/data/DMC/reports/dmc-' . $meeting['decid'] . '.pdf';
                            if (file_exists($root_path . $dmc_file)) {
                        ?>
                                <a href="<?php echo $dmc_file ?>" target="_new"><i class="far fa-file-pdf" style="color:red"><span>Download as PDF</span></i></a>
                                <i class="fas fa-edit" style="color:#900;font-size:14px !important" onclick="editDMC(<?php echo $meeting['decid']; ?>);"><span>Edit DMC Report</span></i>
                        <?php }; /*?>
                            <br><i class="fas fa-undo" style="color:#900;font-size:14px !important" onclick="resetDMCProcess(<?php echo $meeting['decid']; ?>,'pending');"><span>Undo DMC Decision</span></i>
                        <?php */
                        }; ?>
                        <span><strong>Reference number:</strong> <?php echo trim($meeting['dmr_reference']) != '' ? $meeting['dmr_reference'] : 'NA'; ?></span>

                    </td>
                </tr>
        <?php };
        }; ?>
    </tbody>
</table>
<div id="RescheduleForMeetingForMass" style="display: none;">
    <a href="" onclick="this.href = getMassInput()" target="iframe" data-width="1080" data-height="580" title="Call for a decision committee meeting"><i class="fa fa-user-clock"></i> Reschedule a meeting for the selected companies.</a>
</div>

<script>
    // function getMassInput() {
    //     crtNrs = [];
    //     jQuery(".Reschedule:checked").each(function() {
    //         crtNrs.push(jQuery(this).val())
    //     })
    //     if (crtNrs.length == 0) {
    //         return false;
    //     }
    //     return '/committee/index.php?inc=email_committee&crtNr=' + crtNrs;
    // }

    // function checkMassCrtNr() {
    //     if (jQuery(".Reschedule:checked").length > 0) {
    //         jQuery("#RescheduleForMeetingForMass").css("display", "inline-block")
    //     } else {
    //         jQuery("#RescheduleForMeetingForMass").css("display", "none")
    //     }
    // }

    jQuery(document).ready(function($) {
        // jQuery(".Reschedule").click(function() {
        //     checkMassCrtNr();
        // })

        <?php /*if ($_SESSION['comemid'] == 2) { ?>
            $(".hocMaster").css("cursor", "pointer");
            $(".hocMaster").each(function() {
                $(this).click(function() {
                    url = $(this).parents("tr").find(".application").attr("href") + '&hoc=1';
                    location = url;
                })
            })
        <?php }; */ ?>
        $(".search").focus(function() {
            $(".search").val('');
            $("#committeeTable tbody tr").css("display", "table-row");
        })
        $(".search").keyup(function() {
            var search = $(this).val();
            var data = $(this).data('search');
            $("#committeeTable tbody tr").css("display", "none");
            $("#committeeTable tbody tr").each(function() {
                if ($(this).find("." + data).text().toLowerCase().indexOf(search.toLowerCase()) > -1) {
                    $(this).css("display", "table-row");
                    //find parent tr and show after it
                    $(this).next().css("display", "table-row");
                }
            })
        })
    })
</script>