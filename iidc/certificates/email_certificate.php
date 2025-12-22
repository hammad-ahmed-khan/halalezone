<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
if ($certificate = $amdb->get_row("SELECT certificates_{$_POST['tp']}.certificate_nr,companies.email1,companies.contact_title1,companies.contact_name1,companies.contact_surname1,companies.company_name FROM certificates_{$_POST['tp']} JOIN companies ON  certificates_{$_POST['tp']}.clid = companies.clid where nr='$_POST[nr]'")) {
    $office = $amdb->get_row("SELECT company_name_english,office_email,contact_person FROM offices WHERE offid='{$_SESSION['offid']}'");

    $client_name = trim($certificate['contact_title1']) != '' ? $certificate['contact_title1'] . '' : '';
    $client_name .= trim($certificate['contact_name1']) != '' ? ' ' . $certificate['contact_name1'] : '';
    $client_name .= trim($certificate['contact_surname1']) != '' ? ' ' . $certificate['contact_surname1'] : '';
    $email_data['client_name'] = trim($client_name) != '' ? $client_name : $certificate['company_name'];

    $email_data['contact_person'] = $office['contact_person'];

    if ($emailMessage = $row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='shipment-certificate'")) {
        $subject = $emailMessage['email_subject'];
        $message_body = $emailMessage['email_body'];
    }

    foreach ($email_data as $key => $value) {
        $subject = str_replace('[' . $key . ']', $value, $subject);
        $message_body = str_replace('[' . $key . ']', $value, $message_body);
    }
?>
    <style>
        #shipmentCertificateEmailForm input {
            font-size: 14px !important;
        }

        #inAction {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            display: none;
        }

        #inActionBackground {
            position: absolute;
            width: 100%;
            height: 100%;
            background: white;
            opacity: 0.8;
        }

        #inActionLoading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 18px;
            z-index: 10;
            color: #900;
            white-space: nowrap;
        }

        @keyframes horizontal-shaking {
            0% {
                transform: translateX(0)
            }

            25% {
                transform: translateX(2px)
            }

            50% {
                transform: translateX(-2px)
            }

            75% {
                transform: translateX(2px)
            }

            100% {
                transform: translateX(0)
            }
        }

        @keyframes tilt-shaking {
            0% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(5deg);
            }

            50% {
                transform: rotate(0eg);
            }

            75% {
                transform: rotate(-5deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        i.fas.fa-blender {
            animation: horizontal-shaking 0.3s infinite;
            color: #900;
            font-size: 50px !important;
            margin-right: 20px
        }
    </style>
    <script>
        function inActionLoader(text) {
            document.getElementById('inAction').style.display = 'flex';
            document.getElementById('inActionLoading').innerHTML = '<i class="fas fa-blender constant-tilt-shake"></i>' + text;
        }
    </script>
    <div id="inAction">
        <div id="inActionBackground"></div>
        <span id="inActionLoading"></span>
    </div>
    <form action="/certificates/pdf/pdf_certificate.php" method="post" id="emailCertificate" target="" onsubmit="inActionLoader('Generating the certificate & Sending Email. Please wait...');return post_this_form(this)">
        <input type="hidden" name="saveBtn" id="saveBtn" value="Email certificate" />
        <input type="hidden" name="tp" value="<?php echo $_POST['tp']; ?>" />
        <input type="hidden" name="tmplid" value="<?php echo $_POST['tmplid']; ?>" />
        <input type="hidden" name="nr" value="<?php echo $_POST['nr']; ?>" />
        <input type="hidden" name="act" value="print" />
        <input type="hidden" name="sub_act" value="email" />
        <input type="hidden" name="popup_act" value="yes" />
        <input type="hidden" name="offid" id="offid" value="<?php echo $_SESSION['offid']; ?>">
        <table style="width:850px;" class="alternate" id="shipmentCertificateEmailForm">
            <tr>
                <th>Email To:</th>
                <td><input type="text" style="width:100%" name="to_name" value="<?php echo $certificate['company_name']; ?>" /></td>
                <th style="width:100px">To email:</th>
                <td><input type="text" style="width:100%" name="to_email" value="<?php echo $certificate['email1']; ?>" /></td>
            </tr>
            <tr>
                <th>Email From:</th>
                <td><input type="text" style="width:100%" name="from_name" value="<?php echo $office['company_name_english']; ?>" /></td>
                <th style="width:100px">From email:</th>
                <td>info@iidc.eu</td>
            </tr>
            <tr>
                <th>Subject:</th>
                <td colspan="3"><input type="text" style="width:100%" name="subject" value="<?php echo $subject; ?>" /></td>
            </tr>
            <tr>
                <th>Message:</th>
                <td colspan="3"><textarea name="message" class="tinymce" style="width:100%;height:200px;"><?php echo str_replace("\n", "<br/>", $message_body); ?></textarea>
                </td>
            </tr>
            <tr>
                <th>
                    Issue Date:
                </th>
                <td colspan="3">
                    <input type="date" class="date" id="issue_date" style="width:100px" placeholder="Issue date" value="<?php echo fix_date($_POST['issue']); ?>" />
                </td>
            </tr>
            <tr>
                <th>Options:</th>
                <td>
                    <label><input type="checkbox" value="1" name="flag" id="flag" <?php echo isset($_POST['flag']) ? 'checked' : ''; ?> />Print country flag</label><br />
                    <label><input type="checkbox" value="1" name="eiaci" id="eiaci" <?php echo isset($_POST['eiaci']) ? 'checked' : ''; ?> />Print EIAC logo</label><br />
                    <label><input type="checkbox" value="1" name="shc" id="shc" <?php echo isset($_POST['shc']) ? 'checked' : ''; ?> />Print SFDA logo</label><br />
                    <?php if (trim($certificate['certificate_nr'])) { ?>
                        <label style="background:beige;padding:5px;display:block;color:#900;white-space:nowrap;">
                            <input type="checkbox" value="1" name="keepOldCrtNumber" id="keepOldCrtNumber" <?php echo isset($_POST['keepOldCrtNumber']) ? 'checked' : ''; ?> />Keep old Cert. Nr. <b>(<?php echo $certificate['certificate_nr']; ?>)</b>?</label>
                    <?php }; ?>
                </td>
                <th>HQC options:</th>
                <td>
                    <label><input type="checkbox" value="1" name="option[HQCstamp]" id="HQCstamp" />Print HQC stamp</label><br />
                    <label><input type="checkbox" value="1" name="option[HQCsignature]" id="HQCsignature" />Print HQC signature</label>
                </td>
            </tr>
        </table>
    </form>
<?php }; ?>