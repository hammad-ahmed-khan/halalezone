<?php
if (!defined("_HQC_"))
    exit();
?>
<script>
    jQuery(".ui-dialog .ui-dialog-buttonpane", window.parent.document).remove();

    function SendEmail(form) {
        //check if at least one committee member is selected
        if (jQuery("input[name='comemid[]']:checked").length == 0) {
            top.alert_message("Please select at least one committee member");
            return false;
        }
        return post_this_form(form);
    }

    function changeTinymceContentDateTime(dateTime = 'date') {
        var editorContent = tinyMCE.activeEditor.getBody();
        // replace the proposed date and time with the new values
        if (dateTime == 'date') {
            thisDate = jQuery("#proposed_date").val();
            //change date format
            thisDate = thisDate.split('-').reverse().join('/');

            if (jQuery(editorContent).find('.proposed_date').length > 0) {
                editorContent = jQuery(editorContent).find('.proposed_date').html(thisDate);
            } else {
                editorContent = jQuery(editorContent).html().replace(/\[proposed_date\]/g, '<span class="proposed_date">' + thisDate + '</span>');
            }
        } else if (dateTime == 'time') {
            thisTime = jQuery("#proposed_time").val();
            if (jQuery(editorContent).find('.proposed_time').length > 0) {
                editorContent = jQuery(editorContent).find('.proposed_time').html(thisTime);
            } else {
                editorContent = jQuery(editorContent).html().replace(/\[proposed_time\]/g, '<span class="proposed_time">' + thisTime + '</span>');
            }
        } else if (dateTime == 'location') {
            thisLocation = jQuery("#proposed_location").val();
            if (jQuery(editorContent).find('.proposed_location').length > 0) {
                editorContent = jQuery(editorContent).find('.proposed_location').html(thisLocation);
            } else {
                editorContent = jQuery(editorContent).html().replace(/\[proposed_location\]/g, '<span class="proposed_location">' + thisLocation + '</span>');
            }
            if (thisLocation == 'Online') {
                jQuery(".meeting-location-zoom").show();
            } else {
                //uncheck zoom_link checkbox
                jQuery("#zoom_link").prop('checked', false);
                jQuery(".meeting-location-zoom").hide();
            }
        }

        //set the new content to the active tinymce editor
        tinyMCE.activeEditor.setContent(editorContent);

    }

    function setZoomLink() {
        var editorContent = tinyMCE.activeEditor.getBody();
        if (jQuery(editorContent).find('.proposed_location').length > 0) {
            if (jQuery("#zoom_link").is(":checked")) {
                locationText = '[zoom-link]';
                jQuery("#zoomLink").css('display', 'block');
            } else {
                locationText = jQuery("#proposed_location").val();
                jQuery("#zoomLink").css('display', 'none');
            }

            editorContent = jQuery(editorContent).find('.proposed_location').html(locationText);
            tinyMCE.activeEditor.setContent(editorContent);
        }
    }
</script>
<?php

if (isset($_REQUEST['crtNr'])) {
    $whr = "FIND_IN_SET(acms_halal_certificates.crtNr,'$_REQUEST[crtNr]')";
    if (!$certificates = $amdb->get_results("Select *,companies.company_name FROM acms_halal_certificates JOIN companies ON acms_halal_certificates.clid=companies.clid where $whr"))
        return;
    $clist = array();
    $certificate = $certificates[0];
    if (count($certificates) > 1) {

        foreach ($certificates as $cert) {
            $clist[$cert['clid']] = $cert['company_name'];
        }
        $certificate['companies_list'] = implode('<br/>', $clist);
    } else {
        $certificate['companies_list'] = $certificate['company_name'];
    }
    if (!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='decision_committee'"))
        $row = $amdb->get_columns('invoice_templates');
    $data = $certificate;
    foreach ($data as $key => $value) {
        $row['email_subject'] = str_replace('[' . $key . ']', $value, $row['email_subject']);
        $row['email_body'] = str_replace('[' . $key . ']', $value, $row['email_body']);
    }
    $row['email_body'] = str_replace('<br /><br /><br />', '<br /><br />', $row['email_body']);

    $comemids = array();
    $event_details = array();
    if (isset($_REQUEST['decid'])) {
        $oldMeeting = $amdb->get_row("SELECT * FROM hqc_committee_decision WHERE decid='$_REQUEST[decid]'");
        $comemids = explode(',', $oldMeeting['comemids']);
        $event_details = json_decode($oldMeeting['event_details'], true);
        $row['email_body'] = str_replace('[proposed_date]', '<span class="proposed_date">' . date("d/m/Y", strtotime($event_details['date'])) . '</span>', $row['email_body']);
        $row['email_body'] = str_replace('[proposed_time]', '<span class="proposed_time">' . $event_details['time'] . '</span>', $row['email_body']);
        if (isset($event_details['zoom-link']) && $event_details['zoom-link'] != '')
            $location = '[zoom-link]';
        else
            $location = $event_details['location'];
        $row['email_body'] = str_replace('[proposed_location]', '<span class="proposed_location">' . $location . '</span>', $row['email_body']);
    } else {
        $oldMeeting = $amdb->get_columns('hqc_committee_decision');
    }
?>
    <style>
        table#resendInvoiceTable td b {
            display: inline-block;
            width: 100px;
            float: left;
        }

        table#resendInvoiceTable td input[type='text'] {
            width: 95%;
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
    <form action="committee_email_save.php" method="post" name="committee_email" id="committee_email" onsubmit="return SendEmail(this)" target="">
        <input type="hidden" name="act" value="<?php echo (isset($_GET['act']) && $_GET['act'] == 'reschedule') ? 'reschedule_meeting' : 'send_email'; ?>" />
        <input type="hidden" name="email[from_email]" data-required="yes" value="info@halaloffice.com" />
        <input type="hidden" name="email[from_name]" data-required="yes" value="HQC-Headquarter" />
        <input type="hidden" name="crtNr" value="<?php echo $_REQUEST['crtNr']; ?>" />
        <?php if (isset($_REQUEST['decid'])) {  ?>
            <input type="hidden" name="decid" value="<?php echo $_REQUEST['decid']; ?>" />
        <?php }; ?>
        <table class="alternate" style="width:100%;" id="sendCommitteeEmail">
            <tr>
                <th style="width:100px" rowspan="2">Committee members:</th>
                <td style="width:50%" rowspan="2">
                    <ul style="padding: 0px;margin:0px;height:150px;overflow:auto" id="committeeMembers" class="table table-striped table-bordered">
                        <?php
                        if ($comMembers = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE status='active' ORDER BY member_name ASC")) {
                            foreach ($comMembers as $member) {
                        ?>
                                <li><label><input type="checkbox" name="comemid[]" value="<?php echo $member['comemid']; ?>" <?php echo in_array($member['comemid'], $comemids) ? 'checked' : ''; ?> /><?php echo $member['member_name']; ?> (<?php echo $member['member_function']; ?>)</label></li>
                        <?php
                            }
                        }
                        ?>
                    </ul>
                </td>
                <th style="width:100px;height:30px">Date/Time:</th>
                <td>
                    <input type="date" name="event_details[date]" id="proposed_date" data-required="yes" onchange="changeTinymceContentDateTime('date');" value="<?php echo isset($event_details['date']) ? $event_details['date'] : ''; ?>">
                    <input type="time" name="event_details[time]" id="proposed_time" data-required="yes" onchange="changeTinymceContentDateTime('time');" value="<?php echo isset($event_details['time']) ? $event_details['time'] : ''; ?>">
                </td>
            </tr>
            <tr>
                <th style="width:100px">Location:</th>
                <td>
                    <select class="meeting-location" onchange="changeTinymceContentDateTime('location');" name="event_details[location]" id="proposed_location" data-required="yes">
                        <option value="">Select location</option>
                        <option value="Online" <?php echo isset($event_details['location']) && $event_details['location'] == 'Online' ? 'selected' : '' ?>>Online</option>
                        <option value="Onsite" <?php echo isset($event_details['location']) && $event_details['location'] == 'Onsite' ? 'selected' : '' ?>>Onsite</option>
                    </select>
                    <div class="meeting-location-zoom" style="display:none;margin-top:10px">
                        <label><input type="checkbox" id="zoom_link" name="event_details[zoom-link]" onclick="setZoomLink()" />Create Zoom video conference link</label>
                        <br />
                        <div id="zoomLink" style="display:<?php echo (isset($event_details['zoom-link']) && trim($event_details['zoom-link'])!='')?'block':'none'; ?>"><input type="text" name="event_details[zoom-topic]" style="width:100%;" placeholder="Please type here zoom meeting topic" value="DMC meeting for: <?php echo count($clist) > 1 ? 'Multiple companies' : $data['company_name']; ?>" />
                            <?php if (isset($_REQUEST['decid'])) { ?>
                                <label><input type="checkbox" id="useOldZoomLink" name="useOldZoomLink" />Use old zoom link, if there is one. Otherwise create new.</label>
                                <input type="hidden" name="oldZoomLink" value="<?php echo isset($event_details['zoom-link']) ? $event_details['zoom-link'] : ''; ?>" />
                            <?php }; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Email Subject:</th>
                <td colspan=" 3"><input type="text" name="email[subject]" data-required="yes" value="<?php echo $row['email_subject']; ?>" style="width:100%" /></td>
            </tr>
            <tr>
                <th colspan="4">Email body</th>
            </tr>
            <tr>
                <td colspan="4"><textarea class="tinymce_minimum" name="email[message]" style="height:250px;"><?php echo $row['email_body']; ?></textarea></td>
            </tr>
            <tr>
                <th colspan="4">
                    <div style="text-align:center">
                        <label><input type="checkbox" name="noEmail">Don't send email</label>
                        <input value="<?php echo (isset($_GET['act']) && $_GET['act'] == 'reschedule') ? 'Reschedule the meeting' : 'Send the Request'; ?>" type="submit" /><input type="reset" value="Reset" />
                        <input type="button" value="Cancel" onClick="closePopupDialog()" data-type="cancel" />
                    </div>
                </th>
            </tr>
        </table>
    </form>
    <script>
        function setHOC(hoc = '') {
            jQuery("#committeeMembers li").find(".hoc").remove();

            jQuery("#committeeMembers li input[type='checkbox']:checked").each(function() {
                if (hoc == jQuery(this).val())
                    checked = 'checked';
                else
                    checked = '';
                jQuery(this).parents('li').append('<label class="hoc" title="Head of the committee."><input type="radio" name="hoc" value="' + jQuery(this).val() + '" data-required="yes"' + checked + '/> HOC</label>');
            });
        }

        jQuery(document).ready(function() {
            do_tinymce_minimum();
            setHOC(<?php echo isset($oldMeeting) && isset($oldMeeting['hoc']) ? $oldMeeting['hoc'] : ''; ?>);
            jQuery("#committeeMembers li input[type='checkbox']").click(function() {
                setHOC();
            });
        });
    </script>
<?php
    exit();
};
