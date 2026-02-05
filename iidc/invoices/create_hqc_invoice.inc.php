<script language="javascript">
  $("#page_title").html("Create HQC invoice")
</script>
<style>
/* Create HQC Invoice Page Header */
.hqc-invoice-header {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    border-radius: 12px;
    border: 1px solid #bbf7d0;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.hqc-invoice-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.hqc-invoice-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.hqc-invoice-header-info {
    flex: 1;
    min-width: 200px;
}

.hqc-invoice-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.hqc-invoice-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* HQC Badge */
.hqc-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #dcfce7;
    color: #166534;
}

.hqc-badge i {
    font-size: 10px;
}

/* Back Button */
.btn-back-hqc {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #16a34a;
    background: #ffffff;
    border: 2px solid #bbf7d0;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-back-hqc:hover {
    background: #f0fdf4;
    border-color: #86efac;
    color: #15803d;
    text-decoration: none;
}

/* Company Selection Section */
.hqc-select-section {
    padding: 24px 32px;
    background: #f7fef9;
    border-top: 1px solid #bbf7d0;
}

.hqc-select-section label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.hqc-select-wrapper {
    display: flex;
    gap: 12px;
    align-items: stretch;
}

.hqc-select-wrapper select {
    flex: 1;
    padding: 16px 48px 16px 20px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 2px solid #bbf7d0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
}

.hqc-select-wrapper select:hover {
    border-color: #16a34a;
    background-color: #fafffe;
}

.hqc-select-wrapper select:focus {
    outline: none;
    border-color: #16a34a;
    box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.12);
}

.hqc-select-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    padding: 12px 16px;
    background: #ffffff;
    border: 1px dashed #86efac;
    border-radius: 8px;
    font-size: 13px;
    color: #166534;
}

.hqc-select-hint i {
    color: #16a34a;
}

/* Step Indicator */
.hqc-step-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    font-size: 13px;
    color: #166534;
    margin-left: auto;
}

.hqc-step-indicator .step-number {
    width: 24px;
    height: 24px;
    background: #16a34a;
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}

/* Info Box */
.hqc-info-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 20px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 10px;
    margin-top: 16px;
}

.hqc-info-box i {
    color: #10b981;
    font-size: 18px;
    margin-top: 2px;
}

.hqc-info-box .info-content {
    flex: 1;
}

.hqc-info-box .info-content strong {
    display: block;
    font-size: 14px;
    color: #065f46;
    margin-bottom: 4px;
}

.hqc-info-box .info-content p {
    margin: 0;
    font-size: 13px;
    color: #047857;
}

/* HQC Invoice Form Header (when company selected) */
.hqc-form-header {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    border-radius: 12px;
    border: 1px solid #bbf7d0;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.hqc-form-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.hqc-form-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.hqc-form-header-info {
    flex: 1;
    min-width: 200px;
}

.hqc-form-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.hqc-form-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Company Info Strip for Form */
.hqc-company-strip {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 32px;
    background: #f7fef9;
    border-top: 1px solid #bbf7d0;
    flex-wrap: wrap;
}

.hqc-company-strip .company-icon {
    width: 44px;
    height: 44px;
    background: #ffffff;
    border: 2px solid #bbf7d0;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #16a34a;
    font-size: 18px;
}

.hqc-company-strip .company-details {
    flex: 1;
    min-width: 200px;
}

.hqc-company-strip .company-details .company-name {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 2px 0;
}

.hqc-company-strip .company-details .company-address {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

/* Certificate Info Badge */
.cert-info-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #ffffff;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    font-size: 13px;
    color: #166534;
    font-weight: 500;
}

.cert-info-badge i {
    color: #16a34a;
}

/* Free of Charge Button */
.btn-free-charge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #f59e0b;
    background: #fffbeb;
    border: 2px solid #fde68a;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-free-charge:hover {
    background: #fef3c7;
    border-color: #fcd34d;
    color: #d97706;
}

/* Responsive */
@media (max-width: 768px) {
    .hqc-invoice-header-content,
    .hqc-form-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .hqc-invoice-header-info h2,
    .hqc-form-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .hqc-select-section {
        padding: 20px;
    }
    
    .hqc-select-wrapper {
        flex-direction: column;
    }
    
    .hqc-step-indicator {
        margin-left: 0;
        justify-content: center;
    }
    
    .hqc-company-strip {
        flex-direction: column;
        text-align: center;
        padding: 16px 20px;
    }
}

</style>
<script language="javascript">
    $("#page_title").html("Create HQC Invoice")
</script>

<?php
if (!isset($act)) {
    $user_options = get_office_options()['options'];
    if (isset($user_options) and isset($user_options['invoices_create'])) {
        $offids[0] = $_SESSION['offid'];
        $offices[$_SESSION['offid']] = $_SESSION['hqc_title'];
    } else {
        $offids[0] = 0;
        if ($options = $amdb->get_results("SELECT offid FROM offices WHERE JSON_VALID(options) = 1 AND JSON_EXTRACT(options,'$.invoicing_by') = '0'")) {
            foreach ($options as $option) {
                $offids[$option['offid']] = $option['offid'];
            };
        }
    }
    $offids = implode(',', $offids);

    $clients_ids = array();
    $clients = array();
    $result = $amdb->get_results("SELECT * FROM $tbl[prefix]_halal_certificates LEFT JOIN companies ON $tbl[prefix]_halal_certificates.clid = companies.clid where $tbl[prefix]_halal_certificates.invoice_nr='' and FIND_IN_SET($tbl[prefix]_halal_certificates.offid,'$offids') group by $tbl[prefix]_halal_certificates.clid order by companies.company_name ASC");
?>

<div class="hqc-invoice-header">
    <div class="hqc-invoice-header-content">
        <div class="hqc-invoice-header-icon">
            <i class="fas fa-certificate"></i>
        </div>
        
        <div class="hqc-invoice-header-info">
            <h2>
                Create HQC Invoice
                <span class="hqc-badge">
                    <i class="fas fa-certificate"></i>
                    Annual Certificate
                </span>
            </h2>
            <p>Create invoice for HQC annual halal certificate</p>
        </div>
        
        <div class="hqc-step-indicator">
            <span class="step-number">1</span>
            Select Company
        </div>
        
        <a href="index.php?inc=invoices&show=all" class="btn-back-hqc">
            <i class="fas fa-arrow-left"></i>
            Back to Invoices
        </a>
    </div>
    
    <div class="hqc-select-section">
        <label for="companySelect">Select Company with Pending Invoice</label>
        <div class="hqc-select-wrapper">
            <select name="clid" id="companySelect" onchange="if(this.value!='')document.location.href='index.php?inc=create_invoice&type=annual&goback=<?php echo $_GET['inc']; ?>&clid='+this.value">
                <option value="">-- Choose a company --</option>
                <?php 
                if (count($result) > 0) {
                    foreach ($result as $row) {
                        if (trim($row['company_name']) != '') {
                ?>
                    <option value="<?php echo $row['clid']; ?>">
                        <?php echo htmlspecialchars($row['company_name']); ?>
                        <?php if (in_array($row['clid'], $clients_ids)) echo "(" . $clients[$row['clid']] . ")"; ?>
                    </option>
                <?php 
                        }
                    }
                } 
                ?>
            </select>
        </div>
 
        
       
    </div>
</div>

<?php 
} elseif (isset($act) and $act == 'hqc' and $_GET['clid'] != '') {
    $clid = $_GET['clid'];
    include "../date-picker.inc.php";
    
    // Get company and certificate data
    $row = $amdb->get_row("SELECT * FROM $tbl[prefix]_halal_certificates, companies where $tbl[prefix]_halal_certificates.clid = companies.clid and companies.clid='$clid' order by companies.company_name ASC");
    $crtNr = isset($row['crtNr']) ? $row['crtNr'] : '';
?>

<script language="javascript">
    var MailPost = 'mail';

    function creat_invoice(act) {
        var err;
        for (var i = 0; i <= document.forms[0].elements.length - 1; i++) {
            if (document.forms[0].elements[i].getAttribute('data-req')) {
                document.forms[0].elements[i].style.backgroundColor = "";
                if (document.forms[0].elements[i].value == "") {
                    document.forms[0].elements[i].style.backgroundColor = "#FFD9D9";
                    err = "y";
                }
            }
        }
        if (err == "y") {
            alert("Fields with (*) are required")
            return false;
        }

        document.invoice_form.act.value = act;

        if (act == 'crt') {
            if (MailPost == 'mail')
                document.invoice_form.target = 'invoice_frame';
            else
                document.invoice_form.target = '_blank';
        }
        document.invoice_form.submit();
        if (act == "crt")
            setTimeout("document.location.href='index.php?inc=create_hqc_invoice'", 200);
    }

    $(function() {
        $("#issued_at").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: dateFormat
        });
        $("#valid_until").datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: dateFormat
        });
    });

    function paidBy(nr) {
        if (confirm("Are you sure??") == true) {
            var time = new Date().getTime();
            $.post("<?php echo $prog_www ?>/invoices/certificates_save.php?tm=" + time, {
                act: "paid_by",
                crtNr: nr
            },
            function(data) {
                if (data != "") {
                    if (data.indexOf('ok') > -1) {
                        document.location = "<?php echo $prog_www ?>/invoices/index.php?inc=create_hqc_invoice";
                    } else {
                        alert(data);
                    }
                }
            });
        }
    }
</script>

<div class="hqc-form-header">
    <div class="hqc-form-header-content">
        <div class="hqc-form-header-icon">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        
        <div class="hqc-form-header-info">
            <h2>
                HQC Certificate Invoice
                <span class="hqc-badge">
                    <i class="fas fa-certificate"></i>
                    Annual
                </span>
            </h2>
            <p>Create invoice for HQC annual halal certificate</p>
        </div>
        
        <div class="hqc-step-indicator">
            <span class="step-number">2</span>
            Complete Invoice
        </div>
        
        <button type="button" class="btn-free-charge" onclick="paidBy('<?php echo $crtNr; ?>')" title="Mark as free of charge">
            <i class="fas fa-gift"></i>
            Free of Charge
        </button>
        
        <a href="index.php?inc=create_hqc_invoice" class="btn-back-hqc">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
    </div>
    
    <?php if ($row) { ?>
    <div class="hqc-company-strip">
        <div class="company-icon">
            <i class="fas fa-building"></i>
        </div>
        <div class="company-details">
            <p class="company-name"><?php echo htmlspecialchars($row['company_name']); ?></p>
            <p class="company-address">
                <?php echo htmlspecialchars($row['street1']); ?>, 
                <?php echo htmlspecialchars($row['zip1']); ?> <?php echo htmlspecialchars($row['city1']); ?>
            </p>
        </div>
        
        <?php if (trim($row['certificate_nr']) != '') { ?>
        <span class="cert-info-badge">
            <i class="fas fa-id-card"></i>
            Certificate: <?php echo htmlspecialchars($row['certificate_nr']); ?>
        </span>
        <?php } ?>
        
        <?php if ($row['date_of_issue'] > 0) { ?>
        <span class="cert-info-badge">
            <i class="fas fa-calendar-alt"></i>
            Issued: <?php echo num2date($row['date_of_issue']); ?>
        </span>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<iframe src="" name="invoice_frame" style="position:fixed;left:-10000px;"></iframe>

<form action="pdf/pdf_hqc_invoice.php" method="post" target="_blank" name="invoice_form">
    <input type="hidden" name="clid" value="<?php echo $clid ?>" />
    <input type="hidden" name="act" value="" />
    <input type="hidden" name="crtNr" value="<?php echo $crtNr; ?>" />
    
    <table border=0 width="750" cellpadding="0" cellspacing="0" class="alternate" style="margin-top: 0;">
        <?php
        // Billing client dropdown
        $billingAdress = "";
        if ($resultCl = $amdb->get_results("SELECT * FROM companies where clof='$clid' order by company_name ASC")) {
            $billingAdress = "<p><b>Billing client:</b> <select size=\"1\" name=\"bclid\"><option value=\"\">Select company</option>";
            foreach ($resultCl as $rowCl) {
                $billingAdress .= "<option value=\"$rowCl[clid]\">$rowCl[company_name]</option>";
            }
            $billingAdress .= "</select>";
        }
        
        if ($billingAdress != '') {
        ?>
        <tr>
            <td colspan="2" style="padding: 15px;">
                <?php echo $billingAdress; ?>
                <?php billing_address($row['billing_address']); ?>
            </td>
        </tr>
        <?php } ?>
        
        <tr>
            <td width="200" class="sub_title">HQC Certificate</td>
            <td width="550" class="sub_title">Invoice Items</td>
        </tr>
        <tr>
            <td valign="top">
                <table bgcolor="#eeeeee" cellpadding="5" cellspacing="0" style="width: 100%;">
                    <tr>
                        <th nowrap="nowrap">HQC Nr:*</th>
                        <td><input type="text" name="HQC_Nr" data-req="y" style="width:100px" value="<?php echo $row['certificate_nr']; ?>" /></td>
                    </tr>
                    <tr>
                        <th nowrap="nowrap">Issued at:*</th>
                        <td><input type="text" name="issued_at" id="issued_at" data-req="y" style="width:100px" value="<?php echo num2date($row['date_of_issue']); ?>" /></td>
                    </tr>
                    <tr>
                        <th nowrap="nowrap">Valid Until:*</th>
                        <td><input type="text" name="valid_until" id="valid_until" data-req="y" style="width:100px" value="<?php echo num2date($row['date_of_expiry']); ?>" /></td>
                    </tr>
                </table>
            </td>
            <td>
                <table width="100%" id="tableHQ" cellpadding="5" cellspacing="0">
                    <tr>
                        <th>Description</th>
                        <th width="100"><b>Amount (&euro;)</th>
                    </tr>
                    <tr>
                        <td><input type="text" name="HQCD1" style="width:95%" value="Products Registration" /></td>
                        <td><input type="text" name="HQCA1" style="width:90%" /></td>
                    </tr>
                    <tr>
                        <td><input type="text" name="HQCD2" style="width:95%" value="Transportation" /></td>
                        <td><input type="text" name="HQCA2" style="width:90%" /></td>
                    </tr>
                    <tr>
                        <td><input type="text" name="HQCD3" style="width:95%" /></td>
                        <td><input type="text" name="HQCA3" style="width:90%" /></td>
                    </tr>
                    <tr>
                        <td><input type="text" name="HQCD4" style="width:95%" /></td>
                        <td><input type="text" name="HQCA4" style="width:90%" /></td>
                    </tr>
                    <tr>
                        <td><input type="text" name="HQCD5" style="width:95%" /></td>
                        <td><input type="text" name="HQCA5" style="width:90%" /></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 15px;">
                <table cellpadding="2" cellspacing="2" width="100%">
                    <tr>
                        <td width="140"><b>First page comment:</b></td>
                        <td><input type="text" name="FPC" style="width:100%"></td>
                    </tr>
                    <tr>
                        <td><b>Last page comment:</b></td>
                        <td><input type="text" name="LPC" style="width:100%"></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr style="background-color:#f8fafc">
            <td colspan="2" style="padding: 15px;">
                <table cellpadding="2" cellspacing="2" width="100%">
                    <tr>
                        <th width="140">Invoice template:</th>
                        <td>
                            <?php if (in_array("invoices_show_nl", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                                <label style="margin-right: 20px;">
                                    <input type="radio" name="template" checked value="nl"> NL
                                </label>
                                Vat: <input size="2" name="vat" value="21" style="width: 50px;" />%
                            <?php }; ?>
                            <?php if (in_array("invoices_show_uae", $user_permissions) or $_SESSION['user_type'] == "admin") { ?>
                                <label style="margin-left: 20px;">
                                    <input type="radio" name="template" value="uae"> UAE
                                </label>
                            <?php }; ?>
                        </td>
                        <td rowspan="2" style="width:300px; text-align:center; background:#f1f5f9; padding: 20px; border-radius: 8px;">
                            <input type="button" onclick="creat_invoice('prv')" value="Preview" style="width:120px; margin-bottom: 8px;" />
                            <input type="button" onclick="creat_invoice('crt')" value="Create" style="width:120px; margin-bottom: 8px; background: #16a34a; color: white; border: none;">
                            <input type="reset" value="Reset" style="width:120px">
                        </td>
                    </tr>
                    <tr>
                        <th>Send invoice by:</th>
                        <td valign="top">
                            <label>
                                <input type="radio" name="mail_post" checked value="mail" onclick="
                                    if(this.checked){
                                        emailmeacopy.checked=true;
                                        document.getElementById('sendmecopy').style.display='';
                                        MailPost = 'mail';
                                    }
                                "> E-mail
                            </label>
                            <span id="sendmecopy">
                                <label>
                                    <input type="checkbox" checked="checked" name="emailmeacopy" value='y'>
                                    Email me a copy
                                </label>
                            </span>
                            <br>
                            <label>
                                <input type="radio" name="mail_post" value="post" onclick="
                                    if(this.checked){
                                        emailmeacopy.checked=false;
                                        document.getElementById('sendmecopy').style.display='none';
                                        MailPost = 'post';
                                    }
                                "> Post
                            </label>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>

<?php
} elseif (isset($act) and $act == 'invOk' and $_GET['clid'] != '' and $_GET['invNr'] != '') {
    $clid = $_GET['clid'];
    $invNr = $_GET['invNr'];
    echo $invNr;
}
?>