<?php
$comemids = array();
$event_details = array();
if (isset($_REQUEST['decid'])) {
    $meeting = $amdb->get_row("SELECT hqc_committee_decision.comemids,hqc_committee_decision.event_details,hqc_committee_decision.hoc,companies.company_name,acms_halal_certificates.crtNr FROM hqc_committee_decision
    JOIN acms_halal_certificates ON acms_halal_certificates.crtNr = hqc_committee_decision.crtNr
    JOIN companies ON companies.clid = acms_halal_certificates.clid WHERE hqc_committee_decision.decid='$_REQUEST[decid]'");
    $comemids = explode(',', $meeting['comemids']);
    $event_details = json_decode($meeting['event_details'], true);
    if (isset($event_details['zoom-link']) && $event_details['zoom-link'] != '')
        $location = '[zoom-link]';
    else
        $location = $event_details['location'];
} else {
    exit();
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

    table#resendInvoiceTable td strong {
        display: inline-block;
        width: 100px;
        float: left;
    }

    table#resendInvoiceTable th {
        white-space: nowrap
    }

    #committeeMembers li {
        position: relative;
    }

    label.hoc {
        position: absolute;
        right: 40px;
    }
</style>
<script>
    function appointNewMember(form) {
        //check if at least one committee member is selected
        if (jQuery("input[name='comemid']:checked").length == 0) {
            top.alert_message("Please select a committee member");
            return false;
        }
        return post_this_form(form);
    }
</script>
<form action="appoint_new_member_save.php" method="post" name="committee_email" id="committee_email" onsubmit="return appointNewMember(this)" target="">
    <input type="hidden" name="act" value="appoint_new_member">
    <input type="hidden" name="decid" value="<?php echo $_REQUEST['decid']; ?>">
    <input type="hidden" name="old_comemid" value="<?php echo $_REQUEST['comemid']; ?>">
    <input type="hidden" name="hoc" value="<?php echo $meeting['hoc']; ?>">
    <div style="padding: 10px;">If you're unable to attend the meeting, you can delegate someone else to represent you.
        <div style="padding:0px 10px;color:#900">Please select another DMC member to represent you</div>
    </div>
    <table style="width: 100%;">
        <tr>
            <th>Meeting Details</th>
            <th>Committee Members</th>
        </tr>
        <tr>
            <td>
                <strong>Meeting Date:</strong>
                <?php echo date("d/m/Y", strtotime($event_details['date'])); ?><br />
                <strong>Meeting Time:</strong>
                <?php echo $event_details['time']; ?><br />
                <strong>Meeting Location:</strong>
                <?php echo $event_details['location']; ?><br />
                <?php if (isset($event_details['requested_by'])) { ?>
                    <strong>Requested by:</strong>
                    <?php echo $event_details['requested_by']; ?><br />
                <?php } ?>
                <br />
                <strong>Company</strong><br />
                <?php echo $meeting['company_name']; ?><br />
            </td>
            <td>
                <ul style="padding: 0px;margin:0px;overflow:auto" id="committeeMembers" class="table table-striped table-bordered">
                    <?php
                    if ($comMembers = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE status='active' ORDER BY member_name ASC")) {
                        foreach ($comMembers as $member) {
                            if ($meeting['hoc'] == $_SESSION['comemid'])
                                $hocClass = 'hocMaster';
                            else
                                $hocClass = 'hocMember';
                    ?>
                            <li>
                                <?php echo ($member['comemid'] == $meeting['hoc'] ? '<span style="color:white;padding:0px 10px;position:absolute;right:10px" class="' . $hocClass . '" >HOC</span>' : '');
                                ?>
                                <label><input value="<?php echo $member['comemid']; ?>" <?php echo in_array($member['comemid'], $comemids) ? 'type="checkbox" name="checked[]" checked="checked" disabled="disabled"' : 'type="radio" name="comemid"'; ?> /><?php echo $member['member_name']; ?> (<?php echo $member['member_function']; ?>)</label>
                            </li>
                    <?php
                        }
                    }
                    ?>
                </ul>
            </td>
        </tr>
    </table>
    <div style="text-align: center;color:green;padding-bottom:10px;">
        Please make sure that the selected member is informed about your decision.<br />
        If you select a new member, all of your privileges will be transferred to the selected member..<br /><br />
        <input type="submit" value="Appoint selected member" /><input type="reset" value="Reset" />
        <input type="button" value="Cancel" onClick="closePopupDialog()" data-type="cancel" /><br />

    </div>
</form>