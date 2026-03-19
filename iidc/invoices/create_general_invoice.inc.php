<script language="javascript">
    $("#page_title").html("Create General Invoice")
</script>
<style>
/* ============================================
   Create General Invoice - Professional Styling
   ============================================ */
/* Office Selection for Clients */
.office-select-container {
    max-width: 600px;
    margin: 10px auto;
}

.office-select-card {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    border-radius: 16px;
    border: 1px solid #bbf7d0;
    padding: 32px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    text-align: center;
}

.office-select-card .card-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #1a5f4a 0%, #2d8a6e 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 28px;
    margin: 0 auto 20px;
}

.office-select-card h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
}

.office-select-card p {
    margin: 0 0 24px 0;
    font-size: 14px;
    color: #64748b;
}

.office-select-card select {
    width: 100%;
    padding: 16px 48px 16px 20px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
}

.office-select-card select:hover {
    border-color: #1a5f4a;
}

.office-select-card select:focus {
    outline: none;
    border-color: #1a5f4a;
    box-shadow: 0 0 0 4px rgba(26, 95, 74, 0.12);
}

/* Create Invoice Page Header */
.create-invoice-header {
    background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
    border-radius: 12px;
    border: 1px solid #e0e7ff;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.create-invoice-header-content {
    display: flex;
    align-items: center;
    padding: 24px 32px;
    gap: 20px;
    flex-wrap: wrap;
}

.create-invoice-header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.create-invoice-header-info {
    flex: 1;
    min-width: 200px;
}

.create-invoice-header-info h2 {
    margin: 0 0 6px 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.create-invoice-header-info p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
}

/* Invoice Type Badge */
.invoice-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.invoice-type-badge.general {
    background: #e0e7ff;
    color: #4338ca;
}

.invoice-type-badge i {
    font-size: 10px;
}

/* Back Button */
.btn-back-invoices {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #4f46e5;
    background: #ffffff;
    border: 2px solid #e0e7ff;
    border-radius: 8px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-back-invoices:hover {
    background: #f5f3ff;
    border-color: #c7d2fe;
    color: #4338ca;
    text-decoration: none;
}

/* Company Selection Section */
.company-select-section {
    padding: 24px 32px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.company-select-section label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}

.company-select-wrapper {
    display: flex;
    gap: 12px;
    align-items: stretch;
}

.company-select-wrapper select {
    flex: 1;
    padding: 16px 48px 16px 20px;
    font-size: 15px;
    font-weight: 500;
    color: #1e293b;
    background-color: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
}

.company-select-wrapper select:hover {
    border-color: #4f46e5;
    background-color: #fafafe;
}

.company-select-wrapper select:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
}

.company-select-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
    padding: 12px 16px;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    font-size: 13px;
    color: #64748b;
}

.company-select-hint i {
    color: #4f46e5;
}

/* Step Indicator */
.step-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #fefce8;
    border: 1px solid #fde68a;
    border-radius: 8px;
    font-size: 13px;
    color: #92400e;
    margin-left: auto;
}

.step-indicator .step-number {
    width: 24px;
    height: 24px;
    background: #f59e0b;
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}

/* Invoice Company Strip */
.invoice-company-strip {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 32px;
    background: #f0fdf4;
    border-top: 1px solid #bbf7d0;
}

.invoice-company-strip .company-icon {
    width: 40px;
    height: 40px;
    background: #16a34a;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 16px;
}

.invoice-company-strip .company-details .company-name {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}

.invoice-company-strip .company-details .company-meta {
    font-size: 13px;
    color: #64748b;
    margin: 0;
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.invoice-company-strip .company-details .company-meta span i {
    margin-right: 4px;
    color: #94a3b8;
}

/* Invoice Form Styles */
#invoiceTbl {
    width: 100%;
    border-collapse: collapse;
}

#invoiceTbl input[type='text'],
#invoiceTbl textarea {
    width: 100%;
}

#invoiceTbl textarea {
    height: 85px;
}

input.amount {
    width: 100px !important;
}

i.fa-trash-alt {
    margin-left: 10px;
    cursor: pointer;
    color: #dc2626;
}

i.fa-trash-alt:hover {
    color: #991b1b;
}

.invoice-address td {
    padding: 20px;
    border: none;
}

.invoice-address td:after {
    content: none;
}

td#client_address b {
    float: left;
    width: 120px;
}

td#client_address div {
    margin-bottom: 5px;
}

table {
    width: 100%;
}

/* Responsive */
@media (max-width: 768px) {
    .create-invoice-header-content {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    
    .create-invoice-header-info h2 {
        justify-content: center;
        font-size: 18px;
    }
    
    .company-select-section {
        padding: 20px;
    }
    
    .company-select-wrapper {
        flex-direction: column;
    }
    
    .step-indicator {
        margin-left: 0;
        justify-content: center;
    }
    
    .invoice-company-strip {
        flex-direction: column;
        text-align: center;
        padding: 16px 20px;
    }
}

/* Reset */
* {
    box-sizing: border-box;
}

/* Hide jQuery script visual artifacts */
script {
    display: none !important;
}
</style>

<?php
// Define invoice type info
$typeInfo = ['name' => 'General Invoice', 'icon' => 'fa-file-invoice', 'class' => 'general', 'desc' => 'Create a general service invoice'];
$goBack = isset($_GET['goback']) ? $_GET['goback'] : 'invoices&show=all';

// ============================================================
// STEP 1: Company Selection (no clid set)
// ============================================================
if (!isset($_GET['clid']) && !isset($_GET['act'])) {

    $clients_ids = array();
    $clients = array();
    $user_options = get_office_options()['options'];
    if (isset($user_options) and isset($user_options['invoices_create'])) {
        $offid = "and companies.offid='$_SESSION[offid]'";
    } else {
        $offid = '';
    }
    $result = $amdb->get_results("SELECT * FROM companies JOIN users ON companies.clid = users.clid WHERE companies.clof='0' $offid and users.active='y' order by TRIM(companies.company_name)+0 ASC, TRIM(companies.company_name) ASC");
?>

<div class="create-invoice-header">
    <div class="create-invoice-header-content">
        <div class="create-invoice-header-icon">
            <i class="fas <?php echo $typeInfo['icon']; ?>"></i>
        </div>
        
        <div class="create-invoice-header-info">
            <h2>
                Create Invoice
                <span class="invoice-type-badge <?php echo $typeInfo['class']; ?>">
                    <i class="fas <?php echo $typeInfo['icon']; ?>"></i>
                    <?php echo $typeInfo['name']; ?>
                </span>
            </h2>
            <p><?php echo $typeInfo['desc']; ?></p>
        </div>
        
        <div class="step-indicator">
            <span class="step-number">1</span>
            Select Company
        </div>
        
        <a href="index.php?inc=<?php echo $goBack; ?>" class="btn-back-invoices">
            <i class="fas fa-arrow-left"></i>
            Back to Invoices
        </a>
    </div>

    
<div class="office-select-container">
    <div class="office-select-card">
        <div class="card-icon">
            <i class="fas fa-building"></i>
        </div>
        <h3>Select Company</h3>

        <select name="clid" id="companySelect" onchange="if(this.value!='')document.location.href='index.php?inc=create_general_invoice&goback=<?php echo urlencode($goBack); ?>&clid='+this.value">
                <option value="">-- Choose a company --</option>
                <?php 
                if (count($result) > 0) {
                    foreach ($result as $row) {
                        echo "<option value='{$row['clid']}'>{$row['company_name']}";
                        if (in_array($row['clid'], $clients_ids)) {
                            echo " (" . $clients[$row['clid']] . ")";
                        }
                        echo "</option>";
                    }
                }
                ?>
            </select>
    </div>
</div>
 
</div>

<?php 
// ============================================================
// STEP 2: Invoice Form (clid is set)
// ============================================================
} else {

    $_SESSION['offid'] = '0';
    $data = array();
    $invoice_options = array();
    $invoice_data = null;
    $isEdit = false;
    $isDraft = false;

    // Handle edit/draft actions
    if (isset($_GET['act']) and isset($_GET['nr'])) {
        if ($invoice_data = $amdb->get_row("SELECT * FROM invoices WHERE nr='$_GET[nr]'")) {
            $_GET['clid'] = $invoice_data['clid'];
            $invoffid = $invoice_data['offid'];
            $data = decode_json($invoice_data['invoice_data']);
            if (trim($invoice_data['invoice_options']) != '')
                $invoice_options = decode_json($invoice_data['invoice_options']);
            if ($_GET['act'] == 'edit') $isEdit = true;
            if ($_GET['act'] == 'draft') $isDraft = true;
        }
    }

    if (isset($_GET['clid']))
        $clid = $_GET['clid'];

    $result = array();
    $nr = 0;
    $nrIndex = 0;
    $template_name = 'invoice';

    // Get office IDs
    $offids = array(0);
    $offices = array();
    $user_options = get_office_options()['options'];
    if (isset($user_options) and isset($user_options['invoices_create'])) {
        $offids[0] = $_SESSION['offid'];
        $offices[$_SESSION['offid']] = $_SESSION['hqc_title'];
        if (isset($user_options['invoice_office']) and is_array($user_options['invoice_office']))
            $offids = array_merge($offids, array_values($user_options['invoice_office']));
    } else {
        $sql = "SELECT offid,office_name FROM offices WHERE JSON_VALID(options) = 1 AND JSON_EXTRACT(options,'$.invoicing_by') = '0' OR offid=0";
        if ($options = $amdb->get_results($sql)) {
            foreach ($options as $option) {
                $offids[$option['offid']] = $option['offid'];
                $offices[$option['offid']] = $option['office_name'];
            };
        }
    }

    // Get invoice defaults
    $service_type_default = array('general' => 'Halal Services');
    $invoice_item_default = array('general' => 'Halal Services');
    $service_type = array();
    $invoice_item = array();
    if ($defaults = json_decode(get_option('invoice_defaults'), true)) {
        $service_type = $defaults['service_type'];
        $invoice_item = $defaults['invoice_item'];
    }
    foreach ($service_type_default as $key => $value) {
        if (!isset($service_type[$key]))
            $service_type[$key] = $value;
    }
    foreach ($invoice_item_default as $key => $value) {
        if (!isset($invoice_item[$key]))
            $invoice_item[$key] = $value;
    }

    $serviceType = isset($service_type['general']) ? $service_type['general'] : 'Halal Services';

    // ============================================================
    // Populate invoice items
    // ============================================================
    if (isset($invoice_data) and is_array(decode_json($invoice_data['invoice_items']))) {
        // Editing existing invoice - load saved items
        $general_invoice_items = decode_json($invoice_data['invoice_items']);
        $serviceType = $invoice_data['service_type'];
        if (count($general_invoice_items) > 0) {
            foreach ($general_invoice_items as $general_invoice_item) {
                if (isset($general_invoice_item['description']) && trim($general_invoice_item['description']) != '') {
                    $nrIndex++;
                    $result[$nrIndex]['type'] = isset($general_invoice_item['type']) ? $general_invoice_item['type'] : 'General invoice';
                    $result[$nrIndex]['product'] = 'hidden';
                    $result[$nrIndex]['description'] = $general_invoice_item['description'];
                    $result[$nrIndex]['amount'] = str_replace(array('.', ','), array('', '.'), $general_invoice_item['amount']);
                }
            }
        }
    } else {
        // New invoice - pre-populate from toffers using latest tapplications
        if (isset($clid)) {
            $latestApp = $amdb->get_row("SELECT * FROM tapplications WHERE idclient='$clid' ORDER BY id DESC LIMIT 1");
            
            if ($latestApp) {
                $offerRows = $amdb->get_results("SELECT * FROM toffers WHERE idclient='$clid' AND idapp='{$latestApp['id']}'");
                
                if ($offerRows && count($offerRows) > 0) {
                    // Build offer data - the toffers table may store JSON or have direct columns
                    // Try to get user data for placeholder replacements
                    $userData = $amdb->get_row("SELECT * FROM users WHERE clid='$clid'");
                    $companyData = $amdb->get_row("SELECT * FROM companies WHERE clid='$clid'");
                    
                    $prodnumber = '';
                    $ingrednumber = '';
                    if ($userData) {
                        $prodnumber = isset($userData['prodnumber']) ? $userData['prodnumber'] : '';
                        $ingrednumber = isset($userData['ingrednumber']) ? $userData['ingrednumber'] : '';
                    }
                    if ($companyData) {
                        if (empty($prodnumber) && isset($companyData['prodnumber'])) $prodnumber = $companyData['prodnumber'];
                        if (empty($ingrednumber) && isset($companyData['ingrednumber'])) $ingrednumber = $companyData['ingrednumber'];
                    }

                    foreach ($offerRows as $offerRow) {
                        // Check if offer data is stored as JSON
                        $offerData = null;
                        if (isset($offerRow['offer_data']) && trim($offerRow['offer_data']) != '') {
                            $offerData = json_decode($offerRow['offer_data'], true);
                        } elseif (isset($offerRow['data']) && trim($offerRow['data']) != '') {
                            $offerData = json_decode($offerRow['data'], true);
                        }

                        if ($offerData && isset($offerData['offer']) && is_array($offerData['offer'])) {
                            // JSON offer structure with offer array
                            if (isset($offerData['user'])) {
                                if (empty($prodnumber) && isset($offerData['user']['prodnumber'])) $prodnumber = $offerData['user']['prodnumber'];
                                if (empty($ingrednumber) && isset($offerData['user']['ingrednumber'])) $ingrednumber = $offerData['user']['ingrednumber'];
                            }
                            
                            for ($i = 0; $i < count($offerData['offer']); $i++) {
                                $service = $offerData['offer'][$i]['Service'];
                                $service = str_replace('[prodnumber]', $prodnumber, $service);
                                $service = str_replace('[ingrednumber]', $ingrednumber, $service);
                                $fee = $offerData['offer'][$i]['Fee'];
                                
                                if (trim($service) != '') {
                                    $nrIndex++;
                                    $result[$nrIndex]['type'] = 'General invoice';
                                    $result[$nrIndex]['product'] = 'hidden';
                                    $result[$nrIndex]['description'] = $service;
                                    $result[$nrIndex]['amount'] = $fee;
                                }
                            }
                        } else {
                            // Direct column structure - Service and Fee columns in toffers
                            $service = '';
                            $fee = '';
                            
                            if (isset($offerRow['Service'])) $service = $offerRow['Service'];
                            elseif (isset($offerRow['service'])) $service = $offerRow['service'];
                            elseif (isset($offerRow['description'])) $service = $offerRow['description'];
                            
                            if (isset($offerRow['Fee'])) $fee = $offerRow['Fee'];
                            elseif (isset($offerRow['fee'])) $fee = $offerRow['fee'];
                            elseif (isset($offerRow['amount'])) $fee = $offerRow['amount'];
                            elseif (isset($offerRow['price'])) $fee = $offerRow['price'];
                            
                            $service = str_replace('[prodnumber]', $prodnumber, $service);
                            $service = str_replace('[ingrednumber]', $ingrednumber, $service);
                            
                            if (trim($service) != '') {
                                $nrIndex++;
                                $result[$nrIndex]['type'] = 'General invoice';
                                $result[$nrIndex]['product'] = 'hidden';
                                $result[$nrIndex]['description'] = $service;
                                $result[$nrIndex]['amount'] = $fee;
                            }
                        }
                    }
                }
            }
        }

        // Fallback: add one empty row if no offers found
        if ($nrIndex == 0) {
            $result[$nrIndex]['type'] = 'General invoice';
            $result[$nrIndex]['product'] = 'hidden';
            $result[$nrIndex]['description'] = '';
            $result[$nrIndex]['amount'] = '';
        }
    }

    // Get invoice template
    $invoice_template = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='$template_name'");

    // Determine go back URL
    $goBackUrl = '/iidc/invoices/?show=all';
    if (isset($_GET['goback'])) {
        $goBackUrl = 'index.php?inc=' . urldecode($_GET['goback']);
    }

    $pageTtl = "Create General Invoice";
?>
<script>
    $("#page_title").html("<?php echo $pageTtl; ?>")

    var MailPost = 'mail';

    function selectall(obj) {
        $("#invoiceTbl .invoiceItem").prop('checked', $(obj).prop('checked'));
    }

    /**
     * Main function to create/save invoice
     * Uses AJAX for better error handling and user feedback
     */
    async function create_invoice(act) {
        var sel = 0;
        var error = false;

        // Validate form
        if (post_this_form(document.invoice_form) == false) {
            return false;
        }

        // Reset field highlights
        $("#invoiceTbl input[type='text'], #invoiceTbl textarea").css('border-color', '#c0c0c0');

        // Validate selected items
        $(".invoiceItem").each(function(index, element) {
            if ($(this).prop('checked') == true) {
                sel++;
                var id = $(this).data('id');

                if ($('#description_' + id).val() == '') {
                    $('#description_' + id).css('border-color', 'red');
                    error = true;
                }
                if ($('#amount_' + id).val() == '') {
                    $('#amount_' + id).css('border-color', 'red');
                    error = true;
                }
            }
        });

        // Check if any items selected
        if (sel === 0) {
            alert_message("Please select at least one invoice item");
            return false;
        }

        // Check for validation errors
        if (error) {
            alert_message('Some fields are empty!');
            return false;
        }

        // Set the action
        $("input[name='act']").val(act);

        if (act === 'prv') {
            // Preview - use traditional form submit
            $("input[name='ajax']").val('0');
            document.invoice_form.target = '_blank';
            document.invoice_form.submit();
            return;
        }

        // For create/save/test - use AJAX
        $("input[name='ajax']").val('1');
        
        const formData = new FormData(document.invoice_form);

        try {
            // Show loading
            let loadingMsg = 'Creating invoice...';
            if (act === 'save_draft') loadingMsg = 'Saving draft...';
            if (act === 'update_draft') loadingMsg = 'Updating draft...';
            if (act === 'test') loadingMsg = 'Sending test...';

            alert_message('<i class="fas fa-spinner fa-spin"></i> ' + loadingMsg);

            const response = await fetch('pdf/pdf_invoice.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                close_alert();
                alert_message('<i class="fas fa-check-circle" style="color:green"></i> ' + result.message);
                
                if (act === 'crt' || act === 'update_draft') {
                    setTimeout(function() {
                        window.location.href = '<?php echo $goBackUrl; ?>';
                    }, 2000);
                }
            } else {
                close_alert();
                alert_message('<i class="fas fa-exclamation-circle" style="color:red"></i> ' + result.message);
            }
        } catch (error) {
            close_alert();
            alert_message('<i class="fas fa-exclamation-circle" style="color:red"></i> Error: ' + error.message);
        }
    }

    var newItem = <?php echo max($nrIndex + 1, $nr + 1); ?>;

    function addNewItem() {
        newItem++;
        var newInvoiceItem = '<tr>' +
            '<th><input type="checkbox" name="item[' + newItem + '][selected]" class="invoiceItem" data-id="' + newItem + '" value="yes" checked>' +
            '<input type="hidden" name="item[' + newItem + '][type]" value="General invoice">' +
            '<input type="hidden" name="item[' + newItem + '][product]" id="product_' + newItem + '" value="hidden"></th>' +
            '<td><textarea name="item[' + newItem + '][description]" id="description_' + newItem + '"></textarea></td>' +
            '<td><input name="item[' + newItem + '][amount]" class="amount" id="amount_' + newItem + '" type="text" value="">' +
            '<i class="fa fa-trash-alt" onclick="deleteInvoiceItem(this);"></i></td>' +
            '</tr>';
        jQuery("#invoiceItems").append(newInvoiceItem);
        calculateTotal();
        
        // Refresh autosuggest for newly added textarea
        if (window.textareaAutosuggest) {
            setTimeout(function() {
                window.textareaAutosuggest.refresh();
            }, 100);
        }
    }

    function addSubItem() {
        newItem++;
        var newInvoiceItem = '<tr>' +
            '<th><input type="checkbox" name="item[' + newItem + '][selected]" class="invoiceItem" data-id="' + newItem + '" value="yes" checked>' +
            '<input type="hidden" name="item[' + newItem + '][type]" value="General invoice">' +
            '<input type="hidden" name="item[' + newItem + '][product]" id="product_' + newItem + '" value="hidden"></th>' +
            '<td><textarea name="item[' + newItem + '][description]" id="description_' + newItem + '"></textarea></td>' +
            '<td><input name="item[' + newItem + '][amount]" class="amount" id="amount_' + newItem + '" type="text" value="">' +
            '<i class="fa fa-trash-alt" onclick="deleteInvoiceItem(this);"></i></td>' +
            '</tr>';
        jQuery("#invoiceItems").append(newInvoiceItem);
        calculateTotal();
    }

    var selectedItem;
    function deleteInvoiceItem(obj) {
        selectedItem = $(obj).parents('tr');
        alert_confirm('Delete invoice item?');
        jQuery("button#alertYesBtn").click(function() {
            close_alert();
            $(selectedItem).remove();
            calculateTotal();
        })
    }

    function calculateTotal() {
        var total = 0;
        $(".amount").each(function() {
            var val = $(this).val().replace(/\./g, '').replace(',', '.');
            if (!isNaN(parseFloat(val))) {
                total += parseFloat(val);
            }
        });
        $("#totalAmount").html('&euro; ' + total.toFixed(2).replace('.', ','));
    }

    function setInvoiceVatRate() {
        var vatRate = jQuery("#vat_rate").val();
        if (vatRate != undefined) {
            jQuery("#vatRate").html(vatRate + '%');
            jQuery("input[name='vat']").val(vatRate);
        }
    }

    $(document).ready(function() {
        // Calculate total on page load
        calculateTotal();
        
        // Recalculate on amount change
        $(document).on('change keyup', '.amount', function() {
            calculateTotal();
        });

        setInvoiceVatRate();
    });

    <?php if (isset($clid)) { ?>
    function updateVatNumber() {
        vatNr = $("#vat_number").val();
        jQuery.post("invoice_save.php", {
            act: 'update_vat_number',
            clid: '<?php echo $clid; ?>',
            vatNr: vatNr
        }, function(data) {
            if (data != '') alert_message(data);
        });
    }
    <?php }; ?>

    function updateCompanyAddress(obj) {
        if (obj == null || obj == undefined) {
            var companyData = jQuery.parseJSON('<?php echo isset($company_data) ? $company_data : "{}"; ?>');
            var name = companyData.contact_person || '';
            var address = companyData.address || '';
            var vat = companyData.vat_number || '';
            var email = companyData.email || '';
            var tel = companyData.telephone || '';
        } else {
            var billingAddress = jQuery.parseJSON(jQuery("#billing_address_data").val() || '{}');
            var name = 'Att. ' + (billingAddress.name || '');
            var address = (billingAddress.street || '') + "\n" + (billingAddress.zipcode || '') + " " + (billingAddress.city || '') + "\n" + (billingAddress.country || '');
            var vat = '';
            var email = billingAddress.email || '';
            var tel = '';
        }
        jQuery("#company_contact_person").val(name.trim());
        jQuery("#company_address").val(address.trim());
        jQuery("#vat_number").val(vat);
        jQuery("#company_email").html(email);
        jQuery("#company_email").attr('href', 'mailto:' + email);
        jQuery("#company_telephone").html(tel);
        jQuery("#client_emails,#client_email").val(email);
    }
</script>

<!-- Invoice Form Header -->
<div class="create-invoice-header">
    <div class="create-invoice-header-content">
        <div class="create-invoice-header-icon">
            <i class="fas <?php echo $typeInfo['icon']; ?>"></i>
        </div>
        
        <div class="create-invoice-header-info">
            <h2>
                <?php echo $pageTtl; ?>
                <span class="invoice-type-badge <?php echo $typeInfo['class']; ?>">
                    <i class="fas <?php echo $typeInfo['icon']; ?>"></i>
                    <?php echo $typeInfo['name']; ?>
                </span>
                <?php if ($isEdit) { ?>
                    <span class="invoice-type-badge" style="background:#fee2e2; color:#dc2626;">
                        <i class="fas fa-edit"></i>
                        Editing
                    </span>
                <?php } elseif ($isDraft) { ?>
                    <span class="invoice-type-badge" style="background:#fef3c7; color:#92400e;">
                        <i class="fas fa-pencil-alt"></i>
                        Draft
                    </span>
                <?php } ?>
            </h2>
            <p><?php echo $typeInfo['desc']; ?></p>
        </div>
        
        <div class="step-indicator">
            <span class="step-number">2</span>
            Complete Invoice Details
        </div>
        
        <a href="<?php echo $goBackUrl; ?>" class="btn-back-invoices">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
    </div>

    <?php 
    // Show company info strip
    $companyRow = $amdb->get_row("SELECT * FROM companies WHERE clid='$clid'");
    if ($companyRow) { ?>
        <div class="invoice-company-strip">
            <div class="company-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="company-details">
                <p class="company-name"><?php echo htmlspecialchars($companyRow['company_name']); ?></p>
                <p class="company-meta">
                    <?php if (isset($companyRow['city1']) && trim($companyRow['city1']) != '') { ?>
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($companyRow['city1']); ?><?php echo (isset($companyRow['country1']) && trim($companyRow['country1']) != '') ? ', ' . htmlspecialchars($companyRow['country1']) : ''; ?></span>
                    <?php } ?>
                    <?php if (isset($companyRow['email1']) && trim($companyRow['email1']) != '') { ?>
                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($companyRow['email1']); ?></span>
                    <?php } ?>
                    <?php if (isset($companyRow['vatNr']) && trim($companyRow['vatNr']) != '') { ?>
                        <span><i class="fas fa-id-card"></i> VAT: <?php echo htmlspecialchars($companyRow['vatNr']); ?></span>
                    <?php } ?>
                </p>
            </div>
        </div>
    <?php } ?>

    <?php if ($isEdit) { ?>
        <div style="background:#fef2f2; border-top:1px solid #fecaca; padding:16px 32px; display:flex; align-items:center; gap:12px;">
            <i class="fas fa-exclamation-triangle" style="color:#dc2626;"></i>
            <div>
                <strong>You are editing this invoice.</strong> 
                The invoice number and date will not be changed. <span style="color:#dc2626;">Please make a preview first.</span>
            </div>
        </div>
    <?php } ?>
</div>

<!-- Invoice Form -->
<form action="pdf/pdf_invoice.php" method="post" target="_blank" name="invoice_form" autocomplete="off">
    <input type="hidden" name="splittedInvoice" id="splittedInvoice" value="">
    <input type="hidden" name="ajax" value="0">
    
    <?php
    // Get company data for form
    $client_emails = array();
    $client_country = 'Netherlands';
    $row = $companyRow;
    if ($row) {
        $client_country = $row['country1'];
        if ($row['sbsid'] == 0)
            $row['sbsid'] = 1;
        if (isset($data['sbsid']))
            $row['sbsid'] = $data['sbsid'];

        $company_address['company_name'] = $row['company_name'];
        $company_address['contact_person'] = 'Att. ' . trim(htmlspecialchars($row['contact_title1'] . ' ' . $row['contact_name1'] . ' ' . $row['contact_surname1']));
        $company_address['address'] = trim(htmlspecialchars($row['street1'] . "\n" . $row['zip1'] . " " . $row['city1'] . "\n" . $row['country1']));
        $vat_number = $row['vatNr'];
        $company_data = array();
        $company_data['company_name'] = $company_address['company_name'];
        $company_data['contact_person'] = $company_address['contact_person'];
        $company_data['address'] = $company_address['address'];
        $company_data['vat_number'] = $row['vatNr'];
        $company_data['telephone'] = $row['tel1'];
        $company_data['email'] = $row['email1'];
        $company_data = json_encode($company_data);

        if (trim($row['email1']) != '')
            $client_emails['Primary'] = $row['email1'];

        // Check for billing address
        if (is_array(json_decode($row['billing_address'], true))) {
            $billing_address = json_decode($row['billing_address'], true);
            if (isset($billing_address['email']) and trim($billing_address['email']) != '')
                $client_emails['Billing address'] = $billing_address['email'];
        }
    }
    ?>

    <!-- Invoice Category Selection -->
    <?php
    $invoice_categories = array(
        'invoice' => 'Applications - Invoice for certification fees',
        'invoicete' => 'Applications - Invoice for travel expenses',
        'invoiceai' => 'Applications - Invoice for additional items',
        'sfda_first_app_invoice' => 'SFDA - First Application',
        'sfda_shipment_cert_invoice' => 'SFDA - Shipment Certificate',
        'halal_slaughtering_invoice' => 'HBC - Halal Slaughtering',
        'halal_batch_cert_invoice' => 'HBC - Halal Batch Certificate',
        'activity_inbound_invoice' => 'Activity Records - Inbound Invoice',
        'activity_travel_invoice' => 'Activity Records - Travel Expenses Invoice'
    );
    $selected_category = '';
    if (isset($invoice_data) && isset($invoice_data['invoice_category'])) {
        $selected_category = $invoice_data['invoice_category'];
    }
    ?>
 
    <!-- Company Address & Invoice From -->
    <table border=0 width="750" style="margin-bottom: 12px;border:0px" class="alternate invoice-address">
     
        <tr>
            <td valign=top style='width:50%;vertical-align:top' id="client_address">
               <div>
                <b>Invoice Type:</b>
                <select name="invoice_category" id="invoice_category" style="padding:8px 12px;font-size:13px;border:1px solid #c0c0c0;border-radius:6px;min-width:300px;">
                    <option value="">-- Select Type --</option>
                    <?php
                    $category_groups = array(
                        'Applications' => array('invoice', 'invoicete', 'invoiceai'),
                        'SFDA' => array('sfda_first_app_invoice', 'sfda_shipment_cert_invoice'),
                        'HBC' => array('halal_slaughtering_invoice', 'halal_batch_cert_invoice'),
                        'Activity Records' => array('activity_inbound_invoice', 'activity_travel_invoice')
                    );
                    foreach ($category_groups as $group => $keys) {
                        echo '<optgroup label="' . htmlspecialchars($group) . '">';
                        foreach ($keys as $key) {
                            if (isset($invoice_categories[$key])) {
                                $sel = ($selected_category == $key) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($key) . '" ' . $sel . '>' . htmlspecialchars($invoice_categories[$key]) . '</option>';
                            }
                        }
                        echo '</optgroup>';
                    }
                    ?>
                 </select>
                </div>                
                <div>
                    <b style="text-transform: uppercase;">Invoice to:</b> 
                    <input type="text" id="company_name" name="company_address[company_name]" style="width:60%" value="<?php echo htmlspecialchars(trim($company_address['company_name'])); ?>" data-required="yes" />
                </div>
                <div>
                    <b>Contact Person:</b> 
                    <input type="text" name="company_address[contact_person]" id="company_contact_person" value="<?php echo $company_address['contact_person'] ?>" data-required="yes" style="width:60%" />
                </div>
                <div>
                    <b>Address:</b>
                    <textarea name="company_address[address]" id="company_address" data-required="yes" style="width:60%;height:80px"><?php echo $company_address['address']; ?></textarea>
                </div>
                <?php
                $po = '';
                if (isset($row['billing_address']) && is_array(json_decode($row['billing_address'], true))) {
                    $billing_address_data = json_decode($row['billing_address'], true);
                    $po = isset($billing_address_data['po']) ? $billing_address_data['po'] : '';
                }
                ?>
                <div>
                    <b>PO Number:</b> 
                    <input type="text" name="po_number" style="width:60%" value="<?php echo htmlspecialchars($po); ?>" />
                </div>
                <div>
                    <b>VAT Number:</b> 
                    <input type="text" name="vat_number" id="vat_number" style="width:45%" value="<?php echo isset($vat_number) ? htmlspecialchars($vat_number) : ''; ?>" /> 
                    <i class="fas fa-save" style="cursor:pointer; color:#4f46e5;" onclick="updateVatNumber()" title="Update VAT number"></i>
                </div>
                <?php if (isset($row['billing_address']) && trim($row['billing_address']) != '' && is_array(json_decode($row['billing_address'], true))) { ?>
                    <textarea style="display:none" id="billing_address_data"><?php echo $row['billing_address']; ?></textarea>
                    <div style="margin-top:10px">
                        <label><input type="checkbox" name="uba" value="yes" /> Use Billing Address</label>
                    </div>
                <?php } ?>
                <div style="margin-top:15px">
                    <b>Email:</b> <a href="/cdn-cgi/l/email-protection#f2cecd829a82d297919a9dd2d6809d85a9d5979f939b9ec3d5afc9d2cdcc" id="company_email"><?php echo $row['email1']; ?></a>
                    <?php if (isset($row['tel1']) && trim($row['tel1']) != '') { ?>
                        <br/><b>Tel:</b> <span id="company_telephone"><?php echo $row['tel1']; ?></span>
                    <?php } ?>
                </div>
            </td>
            <td valign=top style="vertical-align:top">
                <div>
                    <b>INVOICE FROM:</b>
                    <?php
                    if (!isset($invoffid))
                        $invoffid = $_SESSION['offid'];
                    $invoicing_address = '';
                    $invoicing_offices = $amdb->get_results("SELECT * FROM `hqc_invoicing_offices` WHERE invoice_company_name != '' ORDER BY invoice_company_name");
                    ?>
                    <select size="1" name="invoffid" id="invoffid" style="margin-bottom: 10px;" onchange="jQuery('#invoicing_office').load('/iidc/invoices/get_invoicing_office.php?offid='+this.value)">
                        <?php
                        foreach ($invoicing_offices as $invoicing_office) {
                            if (isset($invoffid) and $invoffid == $invoicing_office['offid']) {
                                $selected = 'selected';
                                $invoicing_address = $invoicing_office['invoice_address'];
                                $vat_rate = $invoicing_office['invoice_vat_rate'];
                                $data['bcc_email'][] = $invoicing_office['invoice_email'];
                            } else {
                                $selected = '';
                            }
                            echo "<option value='$invoicing_office[offid]' $selected>$invoicing_office[invoice_company_name]</option>";
                        }
                        ?>
                    </select>
                    <div id="invoicing_office"><?php echo $invoicing_address; ?>
                        <?php if (isset($invoffid) && $invoffid == '0') { ?>
                            <div style="margin-top: 20px;">
                                <b>Invoice Language:</b>
                                <select name="invoice_lang">
                                    <option value="german">German</option>
                                    <option value="english">English</option>
                                </select>
                            </div>
                        <?php } else { ?>
                            <input type="hidden" name="invoice_lang" value="english" />
                        <?php } ?>
                        <input type="hidden" name="vat_rate" id="vat_rate" value="<?php echo isset($vat_rate) ? $vat_rate : '21'; ?>" />
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Hidden fields -->
    <input type="hidden" name="clid" value="<?php echo $clid; ?>">
    <input type="hidden" name="act" value="">
    <input type="hidden" name="invoice_type" value="general">
    <input type="hidden" name="sbsid" id="sbsid" value="<?php echo isset($row['sbsid']) ? $row['sbsid'] : '1'; ?>">
    <input type="hidden" name="service_type" value="<?php echo htmlspecialchars($serviceType); ?>">
    <?php if (isset($_GET['nr']) && (isset($_GET['act']) && $_GET['act'] == 'edit') && isset($invoice_data)) { ?>
        <input type="hidden" name="nr" value="<?php echo $_GET['nr']; ?>">
        <input type="hidden" name="invoice_number" value="<?php echo $invoice_data['invoice_nr']; ?>">
        <input type="hiddena['date']; ?>">
        <input type="hidden" name="update" value="yes">
    <?php } elseif (isset($_GET['nr']) && isset($_GET['act']) && $_GET['act'] == 'draft') { ?>
        <input type="hidden" name="nr" value="<?php echo $_GET['nr']; ?>">
    <?php } ?>

    <!-- Invoice Items Table (NO Item Code column) -->
    <table border=0 width="750" id="invoiceTbl" class="alternate">
        <tr>
            <th width="20"><input type="checkbox" onclick="selectall(this);"></th>
            <th>Description</th>
            <th style="width:150px">Amount</th>
        </tr>
        <tbody id="invoiceItems">
            <?php
            if (count($result) > 0) {
                foreach ($result as $key => $row_item) {
                    $nr++;
            ?>
                    <tr>
                        <th>
                            <input type="checkbox" name='item[<?php echo $nr; ?>][selected]' value="yes" class="invoiceItem" data-id="<?php echo $nr; ?>" <?php echo (isset($_GET['act']) && $_GET['act'] == 'edit') ? 'checked' : ''; ?>>
                            <input type="hidden" name='item[<?php echo $nr; ?>][type]' value="<?php echo $row_item['type']; ?>">
                            <input type="hidden" name='item[<?php echo $nr; ?>][product]' id="product_<?php echo $nr; ?>" value="hidden">
                        </th>
                        <td>
                            <textarea name="item[<?php echo $nr; ?>][description]" id="description_<?php echo $nr; ?>"><?php echo $row_item['description']; ?></textarea>
                        </td>
                        <td>
                            <input name="item[<?php echo $nr; ?>][amount]" class="amount" id="amount_<?php echo $nr; ?>" type='text' value='<?php echo (trim($row_item['amount']) != '') ? number_format(fix_currency($row_item['amount']), 2, ',', '.') : ''; ?>' />
                            <?php if ($nr > 1) { ?>
                                <i class="fa fa-trash-alt" onclick="deleteInvoiceItem(this);"></i>
                            <?php }; ?>
                        </td>
                    </tr>
            <?php
                };
            };
            ?>
        </tbody>
        <tr>
            <th><input type="checkbox" id="selectAll" data-nr="<?php echo $nr; ?>" onclick="selectall(this);"></th>
            <th>
                <input type="button" onclick="addNewItem()" value="Add item" />
                <input type="button" onclick="addSubItem()" value="Add Subitem" style="width:50%;margin-left:10px;" />
            </th>
            <th id="totalAmount"></th>
        </tr>
    </table>

    <!-- Email Settings -->
    <table cellpadding="2" cellspacing="2" width="750" style="margin-top:20px;">
        <tr>
            <th>Email comment:</th>
            <th></th>
        </tr>
        <?php 
        if (isset($data['email_message'])) {
            $email_message = $data['email_message'];
        } else {
            $email_message = array('body' => '', 'color' => '');
        }; 
        ?>
        <tr>
            <td>
                <textarea name="email_message[body]" style="width:98%;height:55px"><?php echo $email_message['body']; ?></textarea>
                <div style="margin:12px 5px">
                    <b>Comment style:</b> 
                    <select size="1" name="email_message[color]">
                        <option value="">Default color</option>
                        <option value="blue" <?php echo (isset($email_message['color']) && $email_message['color'] == 'blue') ? 'selected' : ''; ?>>Blue</option>
                        <option value="green" <?php echo (isset($email_message['color']) && $email_message['color'] == 'green') ? 'selected' : ''; ?>>Green</option>
                        <option value="red" <?php echo (isset($email_message['color']) && $email_message['color'] == 'red') ? 'selected' : ''; ?>>Red</option>
                    </select>
                    <label><input type="checkbox" name="email_message[font-weight]" value="bold" <?php echo isset($email_message['font-weight']) ? 'checked' : ''; ?> /> Bold</label>
                    <label><input type="checkbox" name="email_message[font-style]" value="italics" <?php echo isset($email_message['font-style']) ? 'checked' : ''; ?> /> Italics</label>
                </div>
            </td>
            <td style="vertical-align:top">
                <ul style="list-style:none;padding:0">
                    <li style="padding:12px">
                        <b>Send to:</b>
                        <select name="client_email" id="client_emails" style="width:70%">
                            <?php foreach ($client_emails as $label => $email) { ?>
                                <option value="<?php echo $email; ?>"><?php echo $email; ?> (<?php echo $label; ?>)</option>
                            <?php } ?>
                        </select>
                        <input type="hidden" name="client_email" id="client_email" value="<?php echo isset($client_emails['Primary']) ? $client_emails['Primary'] : ''; ?>" />
                    </li>
                    <li style="padding:12px">
                        <label><input type="checkbox" name="sendmecopy" value="yes" /> Send me a copy</label>
                    </li>
                    <li style="padding:12px">
                        <b>BCC:</b>
                        <input type="text" name="bcc_email[]" style="width:70%" value="<?php echo isset($invoice_template['email_bcc_address']) ? $invoice_template['email_bcc_address'] : 'info@iidc.eu'; ?>" class="bcc_email" />
                    </li>
                    <li id="testEmail" style="padding:12px;display:none;border: 1px solid #bbb;margin-top: 12px;background: #eceae4;">
                        <b>Test Email</b><br />
                        <input type="text" name="test_email" style="width:70%" value="info@iidc.eu" />
                        <input type="button" onclick="create_invoice('test')" value="Test" style="width:25%" />
                        <br />
                        <span style="font-size:12px;font-style:italic">You will receive exact copy of the invoice, but no data will be saved nor email will be sent to the client</span>
                    </li>
                </ul>
            </td>
        </tr>
    </table>

    <!-- VAT & Action Buttons -->
    <table cellpadding="2" cellspacing="2" width="750" style="margin-top:20px;">
        <tr style="background:#eee">
            <th style="width:50px !important">Vat:</th>
            <td style="background:#eee;width:180px;" id="vatRate"></td>
            <td style="text-align:center">
                <input type="hidden" name="vat" value="<?php echo isset($vat_rate) ? $vat_rate : '21'; ?>" />
                <input type="reset" value="Reset">
                <input type="button" onclick="create_invoice('prv')" value="Preview" />
                <span id="invoiceButtons">
                    <?php if (isset($_GET['act']) and $_GET['act'] == 'draft') { ?>
                        <input type="button" onclick="create_invoice('update_draft')" value="Update draft" />
                    <?php } elseif (!isset($_GET['act'])) { ?>
                        <input type="button" onclick="create_invoice('save_draft')" value="Save draft" />
                    <?php } ?>
                    <?php if (!isset($_GET['act']) || $_GET['act'] != 'scheduled') { ?>
                        <input type="button" onclick="create_invoice('crt')" value="Create">
                    <?php }; ?>
                </span>
                <label style="margin-left:15px"><input type="checkbox" name="post_invoice" value="yes" onclick="if(this.checked){MailPost='post';}else{MailPost='mail';}" /> Don't send email</label>
                <label style="margin-left:10px"><input type="checkbox" onclick="jQuery('#testEmail').slideToggle();" /> Test</label>
            </td>
        </tr>
    </table>
</form>

<script>
    // Update client email hidden field when select changes
    jQuery("#client_emails").on("change", function() {
        jQuery("#client_email").val(jQuery(this).val());
    });
    
    // Set initial vat rate display
    setInvoiceVatRate();
    calculateTotal();
</script>

<?php } ?>