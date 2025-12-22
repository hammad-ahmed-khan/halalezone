<?php
if (!defined("_HQC_")) {
    exit();
};
if (isset($_GET['status']) && $_GET['status']) {
    $_POST['status'] = "hqc_committee_decision.status = 'approved'";
    $title = 'History of Committee Meetings';
} else {
    $_POST['status'] = "hqc_committee_decision.status = 'pending'";
    $title = 'New Committee Meetings Requests';
}
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
        border-bottom: 1px dashed grey;
    }


    .actions i {
        margin-bottom: 6px !important;
    }
</style>
<script>
    $("#page_title").html("<?php echo $title; ?>");
    async function resetDMCProcess(decid, status) {
        if (status == 'delete')
            await confirm_message("Are you sure you want to reset the DMC process?");
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
</script>
<h2 style="text-align:center; position:relative;"><?php echo $title; ?>
    <?php if (isset($_SESSION['super_admin']) && !isset($_GET['status'])) { ?>
        <span style="position: absolute;right:50px;top:-15px;font-size:12px;color:grey;"><a href="?inc=certificates" class="button">Call for a meeting</a></span><?php }; ?>
</h2>
<table class="alternateOn" style="width:100%">
    <thead>
        <tr>
            <th style="width:20px">No</th>
            <!-- <th style="width:20px"></th> -->
            <th>Company</th>
            <th>Meeting Details</th>
            <th>Committee members</th>
            <th><?php echo $_POST['status'] == 'pending' ? 'Actions' : 'Decision'; ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $committee_member = array();
        $comIds = array();
        if ($committee_members = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE status='active' ORDER BY member_name ASC")) {
            foreach ($committee_members as $member) {
                $committee_member[$member['comemid']] = '<b>' . $member['member_name'] . '</b> (' . $member['member_function'] . ')';
                $comIds[] = $member['comemid'];
            }
        }
        $srNr = 1;
        if (isset($_SESSION['comemid']))
            $comemSql = "AND FIND_IN_SET('$_SESSION[comemid]',comemids)";
        else
            $comemSql = '';

        if ($meetings = $amdb->get_results("SELECT *,hqc_committee_decision.status AS status FROM hqc_committee_decision
    JOIN acms_halal_certificates ON acms_halal_certificates.crtNr = hqc_committee_decision.crtNr
    JOIN companies ON companies.clid = acms_halal_certificates.clid WHERE  $_POST[status] $comemSql GROUP BY hqc_committee_decision.crtNr")) {
            foreach ($meetings as $meeting) {
                $sms_codes = json_decode($meeting['sms_codes'], true);
        ?>
                <tr>
                    <td>
                        <?php echo $srNr++; ?>
                    </td>
                    <!-- <td>
                        <input type="checkbox" class="Reschedule" name="crtNrs[]" value="<?php echo $meeting['crtNr']; ?>" />
                    </td> -->
                    <td>
                        <b>
                            <?php
                            echo '<span style="cursor:pointer;white-space:normal" class="com com_' . $meeting['clid'] . ' clid load_popup" data-url="../../admin/load_company.php?clid=' . $meeting['clid'] . '&login=true" title="' . $meeting['company_name'] . '">' . $meeting['company_name'] . '</span>'; ?></b><br />
                        <?php if ($company = get_client($meeting['clid'])) {
                            echo ($company['client_address']);
                        }; ?>
                    </td>
                    <td>
                        <?php
                        if (is_array(json_decode($meeting['event_details'], true))) {
                            $event_details = json_decode($meeting['event_details'], true);
                            echo '<b>Date:</b> ' . date("d/m/Y", strtotime($event_details['date'])) . '<br/><b>Time:</b> ' . $event_details['time'] . '<br/><b>Location:</b> ' . $event_details['location'];
                            if (isset($event_details['zoom-link']))
                                echo '<br/><b>Zoom Link:</b> <a href="' . $event_details['zoom-link'] . '" target="_new">Click here to join the meeting</a><br/>';
                        }
                        ?>
                    </td>
                    <td style="min-width:450px">
                        <ol id="committeeOl" class="alternateOff">
                            <?php $members = explode(',', $meeting['comemids']);
                            if (count($members)) {
                                $memCount = 1;
                                foreach ($members as $member) {
                                    if ($meeting['hoc'] == $_SESSION['comemid'])
                                        $hocClass = 'hocMaster';
                                    else
                                        $hocClass = 'hocMember';
                                    if ($_SESSION['comemid'] == 20)
                                        $theCode = '<span style="padding:2px 20px;color:#900;position:absolute;right:50px;display:inline-block">' . $sms_codes[$member]['code'] . '</span>';
                                    else
                                        $theCode = '';
                                    if (isset($committee_member[$member])) {
                                        if (isset($sms_codes[$member]) && isset($sms_codes[$member]['approved']))
                                            $signed = true;
                                        else
                                            $signed = false;
                            ?>
                                        <li>
                                            <i class="fas fa-signature" style="color:<?php echo $signed == true ? 'green' : 'lightgray'; ?>"></i>
                                            <?php echo $committee_member[$member] . ($member == $meeting['hoc'] ? '<span style="color:white;padding:0px 10px;position:absolute;right:10px" class="' . $hocClass . '" >HOC</span>' : '') . $theCode;
                                            ?>
                                            <?php if ($_SESSION['comemid'] == $member && $signed == false) { ?>
                                                <a href="/committee/index.php?inc=appoint_new_member&decid=<?php echo $meeting['decid']; ?>&comemid=<?php echo $member; ?>" target="iframe" data-width="800" data-height="500" title="Appoint a Member"><i class="fas fa-user-edit" style="color:darkcyan"></i></a>

                                            <?php } ?>
                                        </li>
                            <?php }
                                }
                            }
                            ?>
                        </ol>
                        <?php
                        if (trim($meeting['internal_memo']) != '' and is_array(unserialize($meeting['internal_memo']))) { ?>
                            <fieldset style="background: azure; border: 1px solid #bbb;">
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
                    <td style="white-space: nowrap;" class="actions">
                        <a href="/certificates/annual/?inc=certificate_add_edit&act=edit&crtNr=<?php echo $meeting['crtNr']; ?>&clid=<?php echo $meeting['clid']; ?>&offid=<?php echo $meeting['offid']; ?>"><i class="fas fa-certificate" style="color:goldenrod"><span>Certificate</span></i></a>
                        <br /><a href="?inc=app&act=edit&clid=<?php echo $meeting['clid']; ?>&crtNr=<?php echo $meeting['crtNr']; ?>&decid=<?php echo $meeting['decid']; ?>" class="application"><i class="fab fa-wpforms" style="color:cadetblue"><span>DMC Report</span></i></a>
                        <?php if ($meeting['status'] == 'pending') { ?>
                            <br><a href="/committee/index.php?inc=email_committee&decid=<?php echo $meeting['decid']; ?>&act=reschedule&crtNr=<?php echo $meeting['crtNr']; ?>" target="iframe" data-width="1080" data-height="580" title="Reschedule committee meeting" style="margin-top:20px"><i class="fa fa-user-clock"></i> Reschedule a meeting</a><br />
                            <br /> <i class="fas fa-undo" style="color:#900;font-size:14px !important" onclick="resetDMCProcess(<?php echo $meeting['decid']; ?>,'delete');"><span>Reset DMC Process</span></i>
                        <?php }; ?>
                        <?php if ($meeting['status'] == 'approved') { ?>
                            <br /><a href="/committee/pdf.php?clid=<?php echo $meeting['clid']; ?>&crtNr=<?php echo $meeting['crtNr']; ?>&decid=<?php echo $meeting['decid']; ?>" target="_new"><i class="far fa-file-pdf" style="color:red"><span>Download as PDF</span></i></a><br />
                            <br><i class="fas fa-undo" style="color:#900;font-size:14px !important" onclick="resetDMCProcess(<?php echo $meeting['decid']; ?>,'pending');"><span>Undo DMC Decision</span></i>
                        <?php }; ?>

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

        <?php if ($_SESSION['comemid'] == 2) { ?>
            $(".hocMaster").css("cursor", "pointer");
            $(".hocMaster").each(function() {
                $(this).click(function() {
                    url = $(this).parents("tr").find(".application").attr("href") + '&hoc=1';
                    location = url;
                })
            })
        <?php }; ?>
    })
</script>