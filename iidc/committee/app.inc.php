<?php
if (!isset($user_type) or $user_type != 'committee_member' or !isset($_SESSION['comemid'])) {
    exit();
}

if (!isset($_SESSION['comemid']))
    $comemid = 2;
else
    $comemid = $_SESSION['comemid'];

include "committee.func.php";
$verification_codes = get_sms_codes();
?>
<script>
    $("#page_title").html("Application form");

    function showCodeLi(id, memid) {
        jQuery("#mem_" + memid).find('li').css('display', 'none')
        jQuery("#mem_" + memid).find("#" + id).css('display', 'block');
    }

    function sendVerification(verType, id) {

        jQuery.post('verify.php?act=sendVerificationCode&comemid=<?php echo $comemid; ?>&decid=<?php echo $_REQUEST['decid']; ?>', function(data) {
            if (data == 'EmailSent') {
                showCodeLi('theCodeInput', <?php echo $comemid; ?>);
                alert_message('Verification code sent successfully!<br/>Please check your mail.');
            } else {
                alert_message('Verification code is could not be sent!');
            };
        });

    }

    function verifyTheCode(id, byAdmin) {
        if (byAdmin == true)
            var code = jQuery("#admin_sms_code_" + id).val();
        else
        var code = jQuery("#sms_code_" + id).val();
        if (code.trim() == '') {
            alert_message('Please enter the verification code!');
            return false;
        }
        jQuery.post('verify.php?act=verify_code&comemid=' + id + '&decid=<?php echo $_REQUEST['decid']; ?>&code=' + code + '&byAdmin=' + byAdmin, function(data) {
            if (data != 'ERROR') {
                jQuery("#mem_" + id).html('<img src="' + data + '" width="200px" height="100px" />');
            } else {
                alert_message('Verification code not correct!');
            }
        });
    }

    function approveReport(id, decid) {
        jQuery.post('send_sms.php?act=approve_report&comemid=' + id + '&decid=' + decid, function(data) {
            if (data != 'ERROR') {
                jQuery("#mem_" + id).html('<img src="' + data + '" width="200px" height="100px" />');
            } else {
                alert_message('Verification code not correct!');
            }
        });
    }

    function checkTheForm() {
        save = true;
        if (jQuery("#agreeOnApplication").prop('checked') == true) {
            jQuery("#comemSignatures td").each(function() {
                if ($(this).html().indexOf('<img') == -1) {
                    alert_message('Not all committee members approved the report!');
                    save = false;
                    return false;
                }
            });
        }
        if (save == false) {
            return false;
        } else {
            if (jQuery("#agreeOnApplication").prop('checked') == true)
                return post_this_form(document.appForm);
            else
                return true;
        }

    }

    function showSMSEmail(obj, type) {
        jQuery(obj).parent().find('ul li').css('display', 'none');
        jQuery(obj).parent().find('li.' + type).css('display', 'block');
    }

    function checkAppSaveButton(obj) {
        if (jQuery(obj).prop('checked') == true) {
            jQuery("#appSaveButton").val('Save final decision');
        } else {
            jQuery("#appSaveButton").val('Save draft');
        }
    }
    <?php if ($_SESSION['comemid'] == 2) { ?>

        function theCodeInputByAdmin() {
            jQuery(".signature_holder li").css("display", "none")
            jQuery(".signature_holder li.theCodeInputByAdmin").css("display", "block")
            jQuery("#comemSignatures td span.error").css("display", "none");
        }
    <?php }; ?>
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

$certificate_data = array();
$committee_members['DDMC_members'] = '';
if ($certificate = $amdb->get_row("SELECT * FROM acms_halal_certificates
JOIN hqc_committee_decision ON hqc_committee_decision.crtNr = acms_halal_certificates.crtNr
WHERE acms_halal_certificates.crtNr='" . $_REQUEST['crtNr'] . "' AND acms_halal_certificates.clid='" . $_REQUEST['clid'] . "' AND hqc_committee_decision.decid='" . $_REQUEST['decid'] . "'")) {
    $sms_codes = json_decode($certificate['sms_codes'], true);
    $hoc = ($certificate['hoc'] == $_SESSION['comemid']) ? true : false;
    $certificate_data['scope'] = $certificate['scope_of_certification'];
    $certificate_data['category'] = '';
    $certificate_data['reference_standard'] = '';
    if (is_array(json_decode($certificate['category'], true))) {
        $certificate_data['category'] = '<ol>';
        $categories = implode(',', json_decode($certificate['category'], true));

        if ($certificate_categories = $amdb->get_results("SELECT * FROM hqc_categories WHERE catid IN ($categories)")) {
            foreach ($certificate_categories as $category) {
                $certificate_data['category'] .= '<li>' . $category['category'] . ' - ' . $category['category_name'] . '</li>';
            }
        }
        $certificate_data['category'] .= '</ol>';
    }
    if (is_array(json_decode($certificate['reference_standards'], true))) {
        $certificate_data['reference_standard'] = '<ol>';
        $reference_standards = implode(',', json_decode($certificate['reference_standards'], true));

        if ($certificate_reference_standards = $amdb->get_results("SELECT * FROM hqc_halal_standards WHERE stnid IN ($reference_standards)")) {
            foreach ($certificate_reference_standards as $reference_standard) {
                $certificate_data['reference_standard'] .= '<li>' . $reference_standard['code'] . ' - ' . $reference_standard['description'] . '</li>';
            }
        }
        $certificate_data['reference_standard'] .= '</ol>';
    }

    $event_details = json_decode($certificate['event_details'], true);
    $committee_members['date_of_dmcr'] = date("d/m/Y", strtotime($event_details['date']));
    if ($commebersAll = $amdb->get_results("SELECT * FROM hqc_committee_members WHERE comemid IN ($certificate[comemids]) ")) {
        foreach ($commebersAll as $member) {
            $commebers[$member['comemid']] = $member;

        }

        $member_title = '<tr>';
        $member_footer = '<tr id="comemSignatures">';

        foreach ($commebers as $member) {
            $user_signature = get_user_signature($member['comemid']);

            if (isset($sms_codes[$member['comemid']]))
                $sms_data = $sms_codes[$member['comemid']];
            else
                $sms_data = [];

            $member_title .= '<th style="width:' . (100 / count($commebers)) . '%">' . $member['member_name'] . '<br/>' . $member['member_function'] . '</th>';
            $member_footer .= '<td id="mem_' . $member['comemid'] . '" style="vertical-align:top !important">';
            if ($user_signature != '' && isset($sms_data['approved'])) {
                $member_footer .= '<img src="' . $user_signature . '" width="200px" height="100px" />';
            } else {
                if (trim($user_signature) == '') {
                    if ($comemid == $member['comemid']) {
                        $member_footer .= 'Signature not found!<br/>Please click on <a href="?inc=account">MY ACCOUNT</a> and upload your signature.';
                    } else {
                        $Signature_not_found = true;
                        $member_footer .= 'Signature not found!';
                    }
                } else {
                    if ($comemid != $member['comemid']) {
                        $member_footer .= '<span class="error">Waiting for approval!</span>';
                    }
                    ob_start() ?>
                    <ul style="padding:0px;" class="signature_holder">
                        <?php if ($comemid == $member['comemid']) { ?>

                            <li id="sendTheCode" style="text-align:center;">
                                <input type="button" onclick="sendVerification('email')" value="Email me verification code" style="height:20px !important;padding:0 20px;" /><br />
                                <label onclick="showCodeLi('theCodeInput',<?php echo $member['comemid']; ?>)"><span style="cursor:pointer !important">I already have the code</span></label>

                            </li>
                            <li id="theCodeInput" style="display:none;text-align:center;">
                                <input type="number" name="sms_code" id="sms_code_<?php echo $member['comemid']; ?>" placeholder="Verification code" value="<?php echo (isset($_GET['hoc']) && $hoc == true) ? $sms_data['code'] : ''; ?>" />
                                <input type="button" onclick="verifyTheCode(<?php echo $member['comemid']; ?>)" value="Verify" />
                                <br />
                                <label onclick="showCodeLi('sendTheCode',<?php echo $member['comemid']; ?>);"><span style="cursor:pointer !important">Send verification code</span></label>
                            </li>
                        <?php
                        };
                        ?>
                        <?php if ($_SESSION['comemid'] == 2 && !isset($Signature_not_found)) { ?>
                            <li class="theCodeInputByAdmin" style="display:none;text-align:center;">
                                <input type="number" name="sms_code" id="admin_sms_code_<?php echo $member['comemid']; ?>" placeholder="Verification code" value="<?php echo $verification_codes[$member['comemid']]['code']; ?>" />
                                <input type="button" onclick="verifyTheCode(<?php echo $member['comemid']; ?>,true)" value="Verify" />
                            </li>
                        <?php }; ?>
                    </ul>
    <?php $member_footer .= ob_get_clean();
                }
            }
            $member_footer .= '</td>';
        };
        $member_title .= '</tr>';
        $member_footer .= '</tr>';
        $committee_members['DDMC_members'] = '<table>' . $member_title . $member_footer . '</table>';
    }
}

include "forms.class.php";
$amdb->connect_portal();
$_SESSION['offid'] = 0;

if ($theForm = $amdb->get_row("SELECT * FROM hqc_forms where foid='7' ")) {
    $data['theForm'] = $theForm;
    $amdb->close_portal();
    $the_client = get_client($_REQUEST['clid']);
    $the_client['client_id'] = '<span style="cursor:pointer" data-id="' . $the_client['client_id'] . '" class="com com_' . $the_client['clid'] . ' clid load_popup" data-url="../../admin/load_company.php?clid=' . $the_client['clid'] . '" title="' . $the_client['company_name'] . '">' . $the_client['client_id'] . '</span>';
    $data = $data + $the_client + $certificate_data + $committee_members;

    $all_memos = '';

    if (trim($certificate['internal_memo']) != '' && is_array(unserialize($certificate['internal_memo']))) {
        $internal_memos = unserialize($certificate['internal_memo']);
        foreach ($internal_memos as $key => $memo) {
            if ($key == $_SESSION['comemid'])
                $internal_memo = trim($memo);
            else
                $all_memos .= '<li style="border:1px solid #ccc;padding:5px;margin:5px 0"><b>' . $commebers[$key]['member_name'] . ':</b> ' . $memo . '</li>';
        }
    } else {
        $internal_memo = '';
    }
    if (is_array(unserialize($certificate['decision']))) {
        $data = $data + unserialize($certificate['decision']);
    }
    ?>
    <form name="appForm" id="appForm" style="width:980px;margin:0 auto" action="app_save.php" method="post" onsubmit="return checkTheForm()">
        <input type="hidden" name="clid" value="<?php echo $_REQUEST['clid']; ?>" />
        <input type="hidden" name="crtNr" value="<?php echo $_REQUEST['crtNr']; ?>" />
        <input type="hidden" name="decid" value="<?php echo $_REQUEST['decid']; ?>" />
        <input type="hidden" name="comemid" value="<?php echo $comemid; ?>" />
        <input type="hidden" name="act" value="save" />
        <?php
        if ($certificate['hoc'] == $comemid) {
        ?> <?php
            echo  $amform->get_form(7, $data, 'html');
            ?>
            <div>
                <fieldset>
                    <legend>Internal Memo(s)</legend>
                    <?php if (trim($all_memos) != '') { ?>
                        <ul style="padding:0px">
                            <?php echo $all_memos; ?>
                        </ul>
                    <?php }; ?>
                    <textarea name="internal_memo" id="internal_memo" style="width:100%;height:50px"><?php echo trim($internal_memo) ?></textarea>

                </fieldset>
            </div>
            <div style="margin-top:20px">
                <strong>DMR Reference:</strong> <input type="text" name="dmr_reference" id="dmr_reference" placeholder="DMR Reference" value="<?php echo isset($data['dmr_reference']) ? $data['dmr_reference'] : ''; ?>" style="width:150px" />
            </div>
            <div style="text-align:center;margin-top:20px">
                <label><input type="checkbox" onclick="checkAppSaveButton(this)" name="agree" id="agreeOnApplication" value="1" <?php echo $certificate['status'] == 'approved' ? 'checked' : ''; ?> /> All committee members signed and agreed to the report..</label><br /><br />
                <input type="submit" class="btn btn-primary" id="appSaveButton" value="Save draft" /><button type="reset" class="btn btn-default">Reset</button>
                <input type="button" class="btn btn-default" onclick="window.location.href='index.php?inc=committee'" value="Cancel" />
            </div>
        <?php
        } else {
            echo  $amform->view_form(7, $data, 'html');
        ?>
            <input type="hidden" name="saveMemo" value="yes" />
            <div>
                <fieldset>
                    <legend>Internal Memo(s)</legend>
                    <?php if (trim($all_memos) != '') { ?>
                        <ul style="padding:0px">
                            <?php echo $all_memos; ?>
                        </ul>
                    <?php }; ?>
                    <textarea name="internal_memo" id="internal_memo" style="width:100%;height:50px"><?php echo isset($internal_memo) != '' ? $internal_memo : ''; ?></textarea>

                </fieldset>
            </div>
            <div style="text-align: center;"> <button type="submit" class="btn btn-primary">Save Memo</button><button type="reset" class="btn btn-default">Reset</button></div>
        <?php }; ?>
    </form>
<?php
} ?>
<script>
    jQuery(document).ready(function() {

        if (jQuery(this).prop('checked') == true) {
            jQuery("#appSaveButton").val('Save final decision');
        }

        jQuery("input[data-id=member_mobile_phone]").prop("checked", true)
        jQuery("#sms_code").css('disabled', false);
        //create interval to check if the code is approved
        setInterval(function() {
            jQuery("#comemSignatures td").each(function() {
                if ($(this).html().indexOf('<img') == -1) {
                    jQuery.post('check-approved.php?act=getApproved&decid=<?php echo $_GET['decid']; ?>', function(data) {
                        img = '<img src="[img]" width="200px" height="100px">';
                        if (data.trim() != '') {
                            data = JSON.parse(data);
                            for (var key in data) {
                                if (data.hasOwnProperty(key)) {
                                    imgToInsert = img.replace('[img]', data[key]);
                                    if (jQuery("#mem_" + key).html() != imgToInsert)
                                        jQuery("#mem_" + key).html(imgToInsert);
                                }
                            }
                        }
                    });
                    return false;
                }
            })
        }, 5000);
    })
</script>