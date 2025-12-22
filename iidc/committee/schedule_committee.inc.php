<?php
if (!defined("_HQC_"))
    exit();
?>
<script>
    jQuery(".ui-dialog .ui-dialog-buttonpane", window.parent.document).remove();
    jQuery("#page_title").html("schedule committee meeting")

    function SendEmail(form) {
        //count how many of .shariah are checked
        if (jQuery(".shariah:checked").length < 2) {
            alert_message('Please select at least 2 Shariah Board Members');
            return false;
        }

        //count how many of .auditors are checked
        if (jQuery(".auditors:checked").length == 0) {
            alert_message('Please select at least one Auditor');
            return false;
        }

        //count how many of .management are checked
        // if (jQuery(".management:checked").length == 0) {
        //     alert_message('Please select at least one Management board member');
        //     return false;
        // }

        if (jQuery("#signatories_involved_in_the_audit").val() == '') {
            alert_message('Please answer the question:<br/>Were the signatories involved in the audit?');
            return false;
        }

        if (jQuery("#signatories_involved_in_the_audit").val() == 'Yes') {
            //please select another another DMC audit member
            alert_message('Please select another Member not involved in the audit');
            return false;
        }
        <?php if (!isset($_REQUEST['decid'])) { ?>
            //count how many of .shariah are checked
            if (jQuery(".company:checked").length == 0 || jQuery(".company:checked").length > 5) {
                alert_message('Please select at least 1 company and maximum 5 companies');
                return false;
            }
        <?php }; ?>

        return post_this_form(form);
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
            if (thisLocation == 'Online')
                thisLocationMCE = '[zoom-link]';
            else
                thisLocationMCE = thisLocation;

            if (jQuery(editorContent).find('.proposed_location').length > 0) {
                editorContent = jQuery(editorContent).find('.proposed_location').html(thisLocationMCE);
            } else {
                editorContent = jQuery(editorContent).html().replace(/\[proposed_location\]/g, '<span class="proposed_location">' + thisLocationMCE + '</span>');
            }
            <?php if (isset($_REQUEST['decid'])) { ?>
                if (thisLocation == 'Online') {
                    jQuery("#oldZoomLink").show();
                } else {
                    jQuery("#oldZoomLink").hide();
                }
            <?php }; ?>
        }

        //set the new content to the active tinymce editor
        tinyMCE.activeEditor.setContent(editorContent);

    }

    function findClients(search) {
        jQuery("#clients li").each(function() {
            if (jQuery(this).text().toLowerCase().indexOf(search.toLowerCase()) == -1)
                jQuery(this).hide();
            else
                jQuery(this).show();
        });
    }
</script>
<?php
function get_user_signature($comemid)
{
    global $prog_path;

    $image_file = '/data/DMC/signatures/' . $comemid . '_signature';

    $image_exts = array('.jpg', '.jpeg', '.png', '.svg');
    foreach ($image_exts as $ext) {

        if (file_exists($prog_path . $image_file . $ext)) {
            return $image_file . $ext;
        }
    }
    return '';
}
//show php errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$members = array();
$functions = array(
    'ABM' => 'Auditor Board Member',
    'MBM' => 'Management Board Member',
    'SBM' => 'Shariah Board Member',
);

if ($_SESSION['offid'] == 0) {
    $uid = $_SESSION['user']['uid'];
    $offid = $_SESSION['offid'];
} else {
    $uid = $_SESSION['offid'];
    $offid = $_SESSION['offid'];
}

if ($commebersAll = $amdb->get_results("SELECT comemid,uid,offid,bm,member_name,member_function,member_offices,password FROM hqc_committee_members WHERE status='active' order by member_name ASC")) {
    foreach ($commebersAll as $member) {
        if ($offid == $member['offid']) {
            if ($uid == $member['uid']) {
                $comemid = $member['comemid'];
                $mem_password = $member['password'];
            }
            if ($member['bm'] == 'yes') {
                $BM['member_name'] = $member['member_name'];
                $BM['comemid'] = $member['comemid'];
            }
        }

        if (isset($functions[$member['member_function']]) && get_user_signature($member['comemid']))
            $members[$member['member_function']][$member['comemid']] = $member;
    }
};

$office = get_office_data($_SESSION['offid']);
if ($_SESSION['offid'] != 0) {
    $clients = $office['clients'];
    if (!$companies = $amdb->get_results("Select clid,company_name,country1 FROM companies WHERE `clid` IN ($clients)  AND active = 'y' ORDER BY TRIM(company_name) ASC"))
        return;
} else {
    // COMPANIES LIST
    if (!$companies = $amdb->get_results("Select clid,company_name,country1 FROM companies WHERE offid='$_SESSION[offid]' AND active = 'y' ORDER BY TRIM(company_name) ASC"))
        return;
}
$companies_list = array();
$company = $companies[0];
$data = array();
if (count($companies) > 0) {
    foreach ($companies as $cert) {
        $companies_list[$cert['clid']] = $cert['company_name'] . ' (' . $cert['country1'] . ')';
    }
    if (count($companies) == 1)
        $data['company_name'] = $companies[0]['company_name'];
}
if (!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='decision_committee'"))
    $row = $amdb->get_columns('invoice_templates');

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

$member_title = '<tr><th style="width:33%">' . $functions['SBM'] . 's <span style="font-weight:normal">(At least 2 Members)</span></th>' . '<th style="width:33%">' . $functions['ABM'] . 's <span style="font-weight:normal">(At least 1 Member)</span></th><th>' . $functions['MBM'] . 's</th></tr>';
$member_footer = '<tr id="comemSignatures"><td style="vertical-align:top !important"><ul class="alternateOn" style="padding:0px">';
if (!isset($members['SBM']) || count($members['SBM']) == 0) {
    $member_footer .= '<li>No Shariah Board Members</li>';
} else {
    foreach ($members['SBM'] as $member) {
        if (in_array($member['comemid'], $comemids))
            $checked = 'checked';
        else
            $checked = '';
        $member_footer .= '<li><label><input type="checkbox" class="shariah" name="comemids[]" value="' . $member['comemid'] . '" ' . $checked . '>' . $member['member_name'] . '</label></li>';
    }
}
$member_footer .= '</ul></td>';
$member_footer .= '<td style="vertical-align:top !important"><ul class="alternateOn" style="padding:0px">';
if (!isset($members['ABM']) || count($members['ABM']) == 0) {
    $member_footer .= '<li>No Auditor Board Members</li>';
} else {
    foreach ($members['ABM'] as $member) {
        if (in_array($member['comemid'], $comemids))
            $checked = 'checked';
        else
        $checked = '';
    $member_footer .= '<li><label><input type="checkbox" class="auditors" name="comemids[]" value="' . $member['comemid'] . '" ' . $checked . '>' . $member['member_name'] . '</label></li>';
}
}
$member_footer .= '</ul></td>';
$member_footer .= '<td style="vertical-align:top !important"><ul class="alternateOn" style="padding:0px">';
if (!isset($members['MBM']) || count($members['MBM']) == 0) {
    $member_footer .= '<li>No Management Board Members</li>';
} else {
    foreach ($members['MBM'] as $member) {
        if (in_array($member['comemid'], $comemids))
            $checked = 'checked';
        else
            $checked = '';
        $member_footer .= '<li><label><input type="checkbox" class="auditors" name="comemids[]" value="' . $member['comemid'] . '" ' . $checked . '>' . $member['member_name'] . '</label></li>';
    }
}
$member_footer .= '</ul></td>';
$member_footer .= '</tr>';
$committee_members['DDMC_members'] = '<table>' . $member_title . $member_footer . '</table>';

if (isset($_SESSION['user']) && isset($_SESSION['user']['uid'])) {
    $uid = $_SESSION['user']['uid'];
    if (isset($_SESSION['user']['username_owner']))
        $contact_person = $_SESSION['user']['username_owner'];
    else
        $contact_person = $_SESSION['user']['name'];
} else {
    $uid = $_SESSION['offid'];
    $contact_person = $office['contact_person'];
}
$pending_clids = array();
if ($pendings = $amdb->get_results("SELECT clid,meeting_date FROM hqc_committee_decision WHERE status='pending' AND clid !='0'")) {
    foreach ($pendings as $pending) {
        $pending_clids[$pending['clid']] = $pending['meeting_date'];
    }
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
<h2 style="text-align:center"><?php echo (isset($_GET['act']) && $_GET['act'] == 'reschedule') ? 'Reschedule' : 'Schedule'; ?> DMC meeting </h2>
<form action="committee_email_save.php" style="padding: 10px;" method="post" name="committee_email" id="committee_email" onsubmit="return SendEmail(this)" target="">
    <input type="hidden" name="act" value="<?php echo (isset($_GET['act']) && $_GET['act'] == 'reschedule') ? 'reschedule_meeting' : 'send_email'; ?>" />
    <?php if (isset($_REQUEST['decid'])): ?>
        <input type="hidden" name="decid" value="<?php echo $_REQUEST['decid']; ?>" />
    <?php endif; ?>
    <input type="hidden" name="email[from_email]" data-required="yes" value="<?php echo $row['email_reply_address']; ?>" />
    <input type="hidden" name="email[from_name]" data-required="yes" value="HQC-Headquarter" />
    <input type="hidden" name="branch[Branch]" value="<?php echo $office['company_name_english']; ?>" />
    <input type="hidden" name="branch[BranchManager]" value="<?php echo $office['contact_person']; ?>" />
    <input type="hidden" name="branch[RequestedBy]" value="<?php echo $contact_person; ?>" />
    <input type="hidden" name="branch[RequestDate]" value="<?php echo date('d/m/Y'); ?>" />
    <input type="hidden" name="offid" value="<?php echo $_SESSION['offid']; ?>" />
    <input type="hidden" name="uid" value="<?php echo $uid; ?>" />
    <?php if (isset($_GET['crtNr'])) { ?>
        <input type="hidden" name="crtNr" value="<?php echo $_GET['crtNr']; ?>" />
    <?php }; ?>
    <?php if (isset($_GET['ref'])) { ?>
        <input type="hidden" name="ref" value="<?php echo $_GET['ref']; ?>" />
    <?php }; ?>
    <?php if (isset($_GET['clid'])) { ?>
        <input type="hidden" name="clid" value="<?php echo $_GET['clid']; ?>" />
    <?php }; ?>
    <table class="alternate" style="width:100%;" id="sendCommitteeEmail">
        <tr>
            <td colspan="4">
                <?php
                echo $committee_members['DDMC_members'];
                ?>
            </td>
        </tr>
        <tr>
            <th style="width:100px;height:30px">Client(s):</th>
            <td style="width:60%">
                <?php if (isset($_GET['decid']) && $_GET['decid'] != '') { ?>
                    <strong>Reschedule Meeting for client:</strong>
                <?php } else { ?>
                    <strong>Search: </strong> <input type="text" name="search_client" id="search_client" style="width: 50%" onkeyup="findClients(this.value)" placeholder="Search by company name" />
                    <info>Minimum 1 client and Maximum 5</info>
                <?php }; ?>
                <ol style="height: 200px;overflow-y: scroll;" id="clients">
                    <?php
                    foreach ($companies_list as $clid => $client) {
                        if (isset($pending_clids[$clid])) {
                            echo '<li><i class="far fa-check-square"></i>' . $client . '<span style="color:green"> scheduled on (' . (date("d/m/Y H:i", strtotime($pending_clids[$clid]))) . ')</span> </li>';
                        } else {
                            if (isset($_GET['clid']) && $_GET['clid'] == $clid) {
                                $comChecked = 'checked';
                                $row['email_body'] = str_replace('[companies_list]', '<ol class="companies_list">' . $client . '</ol>', $row['email_body']);
                            } else {
                                $comChecked = '';
                            }
                            if (!isset($_GET['decid'])) {
                                echo '<li><label><input type="checkbox" class="company" name="clids[]" value="' . $clid . '" ' . $comChecked . '/>' . $client . '</label></li>';
                            } else {
                                $row['email_body'] = str_replace('[companies_list]', '<ol class="companies_list">' . $client . '</ol>', $row['email_body']);
                            }
                        }
                    }
                    ?>
                </ol>
            </td>
            <td>
                <div class="th" style="max-width: none;">Meeting Details:</div>
                <div style="margin: 10px 0px;">
                    <strong style="width:80px;float:left;margin-right:10px;">Date/Time:</strong>
                    <?php
                    $last_issued_date = '';
                    if (isset($_GET['crtNr'])) {
                        //ge the last issued annual certificate date
                        if ($certificate = $amdb->get_row("SELECT date_of_issue,reissued FROM `acms_halal_certificates` WHERE `crtNr` = $_GET[crtNr] ORDER BY reissued DESC, date_of_issue DESC LIMIT 1")) {
                            if ($certificate['reissued'] > $certificate['date_of_issue']) {
                                $last_issued_date = $certificate['reissued'];
                            } else {
                                $last_issued_date = $certificate['date_of_issue'];
                            }
                            //one day before the last issued date

                            $last_issued_date = date('Y-m-d', $last_issued_date);
                            $last_issued_date = date('Y-m-d', strtotime($last_issued_date . ' -1 day'));
                            //set the time to now min one hour
                            $event_details['time'] = date('H:00', strtotime('now -1 hour'));
                        }
                    }
                    ?>
                    <input type="text" class="date" name="event_details[date]" id="proposed_date" data-required="yes" onchange="changeTinymceContentDateTime('date');" value="<?php echo isset($event_details['date']) ? $event_details['date'] : $last_issued_date; ?>">
                    <select type="time" name="event_details[time]" id="proposed_time" data-required="yes" onchange="changeTinymceContentDateTime('time');">
                        <option value="">--:--</option>
                        <?php for ($time_slots, $time_slot = 0; $time_slot <= 23; $time_slot++) {
                            $hour = str_pad($time_slot, 2, '0', STR_PAD_LEFT) . ':00';
                            if (isset($event_details['time']) && $event_details['time'] == $hour)
                                echo '<option value="' . $hour . '" selected>' . $hour . '</option>';
                            else
                                echo '<option value="' . $hour . '">' . $hour . '</option>';
                        } ?>
                    </select>

                </div>
                <div>
                    <strong style="width:80px;float:left;margin-right:10px;">Location:</strong>
                    <div style="overflow: hidden;">
                        <select class="meeting-location" onchange="changeTinymceContentDateTime('location');" name="event_details[location]" id="proposed_location" data-required="yes">
                            <option value="">Select location</option>
                            <option value="Online" <?php echo isset($event_details['location']) && $event_details['location'] == 'Online' ? 'selected' : '' ?>>Online</option>
                            <option value="Onsite" <?php echo isset($event_details['location']) && $event_details['location'] == 'Onsite' ? 'selected' : '' ?>>Onsite</option>
                        </select>
                        <div class="meeting-location-zoom" style="display:none;margin-top:10px">
                            <label><input type="checkbox" id="zoom_link" name="event_details[zoom-link]" onclick="setZoomLink()" />Create Zoom video conference link</label>
                            <br />
                            <div id="zoomLink" style="display:<?php echo (isset($event_details['zoom-link']) && trim($event_details['zoom-link']) != '') ? 'block' : 'none'; ?>"><input type="text" name="event_details[zoom-topic]" style="width:100%;" placeholder="Please type here zoom meeting topic" value="DMC meeting for: <?php echo count($companies_list) > 1 ? 'Multiple companies' : $data['company_name']; ?>" />
                            </div>
                        </div>
                        <?php if (isset($_REQUEST['decid'])) { ?>
                            <div id="oldZoomLink" style="display:<?php echo isset($event_details['location']) && $event_details['location'] == 'Online' ? '' : 'none'; ?>">
                                <label><input type="checkbox" id="useOldZoomLink" name="useOldZoomLink" />Use old zoom link, if there is one. Otherwise create new.</label>
                                <input type="hidden" name="oldZoomLink" value="<?php echo isset($event_details['zoom-link']) ? $event_details['zoom-link'] : ''; ?>" />
                            </div>
                        <?php }; ?>


                    </div>
                </div>
                </
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
            <td colspan="4"><textarea class="tinymce_minimum" id="tinymce_minimum" name="email[message]" style="height:250px;"><?php echo $row['email_body']; ?></textarea></td>
        </tr>
        <tr>
            <th colspan="4">
                <div style="text-align:center">
                    <label><input type="checkbox" name="noEmail">Don't send email</label>
                    <label><input type="checkbox" name="createReport">Create DMC report after save</label>
                    <input value="<?php echo (isset($_GET['act']) && $_GET['act'] == 'reschedule') ? 'Reschedule the meeting' : 'Send the Request'; ?>" type="submit" /><input type="reset" value="Reset" />
                    <input type="button" value="Cancel" onClick="closePopupDialog()" data-type="cancel" />
                    <label><input type="checkbox" name="sendTestEmail" onclick="showTestEmail(this);">Send test email</label>
                    <input type="text" name="testEmailTo" id="testEmailTo" style="display: none;" aria-placeholder="Enter email address" />
                </div>
            </th>
        </tr>
    </table>
</form>
<script>
    function showTestEmail(obj) {
        if (jQuery(obj).is(':checked')) {
            jQuery("#testEmailTo").show();
            jQuery("#testEmailTo").attr('data-required', 'yes');
        } else {
            jQuery("#testEmailTo").removeAttr('data-required').hide();
        }
    }

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
        jQuery("#clients li input,#comemSignatures li input").on("click", function() {
            var editor = tinyMCE.get('tinymce_minimum');
            var editorContent = editor.getBody();
            var thisClass = jQuery(this).attr('class');
            if (thisClass == 'company') {
                theList = '#clients';
                targetList = 'companies_list';
                regex = /\[companies_list\]/g;
            } else {
                theList = '#comemSignatures';
                targetList = 'committee_members';
                regex = /\[committee_members\]/g;
            }
            jQuery(this).find('input').prop('checked', true);
            //find all checked clients on the list and make make a list of them
            var checkedClients = jQuery("input:checked", theList).map(function() {
                return jQuery(this).parent('label').text();
            }).get();
            //if the list is not empty, then do the ajax call
            if (checkedClients.length > 0) {

                //make the list of checked clients into a ol list
                checkedClients = '<li>' + checkedClients.join('</li><li>') + '</li>';
                if (jQuery(editorContent).find('.' + targetList).length > 0) {
                    editorContent = jQuery(editorContent).find('.' + targetList).html(checkedClients);
                } else {
                    editorContent = jQuery(editorContent).html().replace(regex, '<ol class="' + targetList + '">' + checkedClients + '</ol>');
                }
                //set the new content to the active tinymce editor
                editor.setContent(editorContent);
            } else {
                if (jQuery(editorContent).find('.' + targetList).length > 0) {
                    editorContent = jQuery(editorContent).find('.' + targetList).html('');
                }
            }

        });
        setTimeout(function() {
            changeTinymceContentDateTime('date');
            changeTinymceContentDateTime('time');
        }, 1000);

    });
</script>
<?php
