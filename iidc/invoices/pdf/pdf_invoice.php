<?php
//start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
define("__HQC__", true);

// Check if this is an AJAX request
$isAjax = isset($_POST['ajax']) && $_POST['ajax'] == '1';

// Error handler for AJAX requests
if ($isAjax) {
    // Prevent any output before our JSON response
    ob_start();
    
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Error: $errstr in $errfile on line $errline"
        ]);
        exit();
    });
    
    set_exception_handler(function($e) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "Exception: " . $e->getMessage()
        ]);
        exit();
    });
}

include "../../config/paths.inc.php";

// Helper function to send JSON response
function sendJsonResponse($success, $message, $data = []) {
    global $isAjax;
    if ($isAjax) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $data));
        exit();
    }
}

//collecting invoice items
$invoiceItems = array();
if (isset($_GET['nr']))
    return;
$nr = 1;
$tableToUpdate = array();

if (!isset($_POST['item'])) {
    sendJsonResponse(false, 'No invoice items provided');
    exit();
}

// Set defaults for missing fields
if (!isset($_POST['sbsid']) || empty($_POST['sbsid'])) {
    $_POST['sbsid'] = '1'; // Default subsidiary ID
}

if (!isset($_POST['service_type']) || empty($_POST['service_type'])) {
    $_POST['service_type'] = 'Halal Services'; // Default service type
}

// Validate invoicing office exists
if (!isset($_POST['invoffid'])) {
    sendJsonResponse(false, 'Invoice office not selected');
    exit();
}

foreach ($_POST['item'] as $itemKey => $item) {
    if (isset($item['selected']) and trim($item['description']) != '' and trim($item['amount']) != '') {
        $invoiceItems[$itemKey] = $item;
        if (isset($item['type']) and isset($item['crtNr'])) {
            $tableToUpdate[$item['type']][] = $item['crtNr'];
        }
    }
}

if (count($invoiceItems) == 0) {
    sendJsonResponse(false, 'No valid invoice items found. Please check that all items have description and amount.');
    exit();
}

if (isset($_POST['splitInvoice']) && isset($_POST['invoiceSender2'])) {
    foreach ($invoiceItems as $itemKey => $item) {
        if ($_POST['splittedInvoice'] == '1' && !in_array($itemKey, $_POST['invoiceSender2']))
            unset($invoiceItems[$itemKey]);
        elseif ($_POST['splittedInvoice'] == '' && in_array($itemKey, $_POST['invoiceSender2']))
            unset($invoiceItems[$itemKey]);
    }
}

$lang['english'] = array(
    'Nr' => 'Nr',
    'Item' => 'Item',
    'Description' => 'Description',
    'Amount' => 'Amount',
    'Subtotal' => 'Subtotal',
    'VAT' => 'VAT',
    'Total' => 'Total'
);
$lang['german'] = array(
    'Nr' => 'Nr',
    'Item' => 'Artikel',
    'Description' => 'Beschreibung',
    'Amount' => 'Betrag',
    'Subtotal' => 'Zwischensumme',
    'VAT' => 'MwSt',
    'Total' => 'Gesamt'
);

$invoice_lang = isset($_POST['invoice_lang']) ? $_POST['invoice_lang'] : 'english';
$items_lang = $lang[$invoice_lang];
$wd['nr'] = '1';
$wd['product'] = '3';
$wd['description'] = '11';
$wd['amount'] = '3';
$totals['subtotal'] = 0;
$pdfItems = '<tr style="background-color:#eee">
            <th width="' . $wd['nr'] . 'cm">Nr</th>
            <th width="' . $wd['product'] . 'cm">' . $items_lang['Item'] . '</th>
            <th width="' . $wd['description'] . 'cm">' . $items_lang['Description'] . '</th>
            <th width="' . $wd['amount'] . 'cm">' . $items_lang['Amount'] . '</th>
            </tr>';
$pdfItem = '<tr>
            <td[doHide]>[nr]</td>
            <td[doHide]>[product]</td>
            <td>[description]</td>
            <td>€ [amount]</td>
            </tr>';
$pdfTotals = '<tr><th width="11.5cm"></th><th width="3.5cm" class="bt"><b>' . $items_lang['Subtotal'] . ':</b></th><th width="3cm" class="bt">€ [subtotal]</th></tr>
                <tr><td></td><td class="bt"><b>' . $items_lang['VAT'] . ':</b> ([vat_rate]%)</td><td>€ [vat]</td></tr>
                <tr><td></td><th><b>' . $items_lang['Total'] . ':</b></th><th>€ [total]</th></tr>';

foreach ($invoiceItems as $inv => $invoiceItem) {
    $invoiceItem['amount'] = str_replace(array('.', ','), array('', '.'), $invoiceItem['amount']);
    $totals['subtotal'] = $totals['subtotal'] + $invoiceItem['amount'];
    $invoiceItem['amount'] = number_format($invoiceItem['amount'], 2, ',', '.');
    $invoiceLine = $pdfItem;
    //handling product with multi items
    if ($invoiceItem['product'] == 'hidden') {
        $invoiceLine = str_replace(array('[doHide]', '[nr]', '[product]'), array(' class="hideB"', '', ''), $invoiceLine);
    } else {
        $invoiceLine = str_replace('[doHide]', '', $invoiceLine);
        $invoiceItem['nr'] = $nr++;
    }
    foreach ($invoiceItem as $key => $value) {
        $invoiceLine = str_replace('[' . $key . ']', str_replace("\r\n", "<br/>", $value), $invoiceLine);
    }
    $pdfItems .= $invoiceLine;
}

if (intval($_POST['vat']) > 0)
    $vat = (intval($_POST['vat']) / 100);
else
    $vat = 0;

$totals['vat'] = ($totals['subtotal'] * $vat);
$totals['total'] = $totals['subtotal'] + $totals['vat'];

foreach ($totals as $key => $value) {
    $pdfTotals = str_replace('[' . $key . ']', number_format($value, 2, ',', '.'), $pdfTotals);
}

ob_start();
?>
<style>
    table#ivoiceItems th {
        font-weight: bold;
        border-top: 1px solid #000000;
        border-bottom: 1px solid #000000;
        height: 24px
    }

    table#ivoiceItems td,
    table#ivoiceTotals th {
        border-top: 1px solid #555555;
        padding: 10px
    }

    table#ivoiceItems td.hideB {
        border-top: none
    }
</style>
<?php
echo '<table cellpadding="4" id="ivoiceItems">' . $pdfItems . '</table>';
echo '<table cellpadding="4" id="ivoiceTotals">' . $pdfTotals . '</table>';
$pdf_data['invoice_items'] = ob_get_contents();
ob_end_clean();

// Start output buffering again for AJAX
if ($isAjax) {
    ob_start();
}

//get address=====================================================================
if (isset($_POST['clid'])) {
    $cla = invoice_address($_POST['clid']);
    $invoice_address = json_encode($cla, true);
    $address = $cla['address'];
    $company_name = $cla['company_name'];
    $client_name = $cla['client_name'];
    $client_email = $cla['client_email'];
    $uba = $cla['uba'];
}

if (isset($_POST['company_address'])) {
    $address = implode("\n", $_POST['company_address']);
    if (isset($_POST['po_number']) && trim($_POST['po_number']) != '')
        $address .= "\nPO No: " . $_POST['po_number'];
    if ($_POST['vat_number'])
        $address .= "\nVAT No: " . $_POST['vat_number'];
    elseif (isset($cla) && trim($cla['row']['vatNr']) != '')
        $address .= "\nVAT No: " . $cla['row']['vatNr'];
    if (intval($_POST['vat']) == 0)
        $address .= "\nVAT Shifted ";

    $company_name = $_POST['company_address']['company_name'];
    $client_name = $_POST['company_address']['contact_person'];
    $client_email = $_POST['client_email'];
}

if (intval($_POST['vat']) > 0)
    $address = str_replace('VAT shifted', '', $address);

if (!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='" . $_POST['invoice_type'] . "'"))
    $row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='invoice'");

if (!$row) {
    sendJsonResponse(false, 'Invoice template not found');
    exit();
}

$theTemplate = $row['pdf_template'];
if (isset($_POST['invoice_lang']))
    $invoice_lang = $_POST['invoice_lang'];
else
    $invoice_lang = 'english';

//extract template language part from $theTemplate using preg_match and $invoice_lang
$regex = '/\[' . $invoice_lang . '\](.*)\[\/' . $invoice_lang . '\]/s';
if (preg_match($regex, $theTemplate, $matches)) {
    $theTemplate = trim($matches[1]);
}

//getting subsidiary
$invoice_footer = '';

$invoicing_office = $amdb->get_row("SELECT * FROM `hqc_invoicing_offices` WHERE invoice_company_name != '' AND offid = '" . $_POST['invoffid'] . "' ORDER BY invoice_company_name");

if (!$invoicing_office) {
    sendJsonResponse(false, 'Invoicing office not found. Please select a valid invoice sender.');
    exit();
}

$pdf_data['office_address'] = $invoicing_office['invoice_address'];

$bank_details = json_decode($invoicing_office['invoice_bank_details'], true);
$pdf_data['account_holder_name'] = trim($bank_details['account_holder']) != '' ? $bank_details['account_holder'] : $invoicing_office['invoice_company_name'];
$pdf_data['bank_name'] = $bank_details['bank_name'];
$pdf_data['bank_address'] = $bank_details['bank_address'];
$pdf_data['BIC'] = $bank_details['BIC'];
$pdf_data['IBAN'] = $bank_details['IBAN'];
if (isset($bank_details['intermediary']) && trim($bank_details['intermediary']) != '')
    $pdf_data['intermediary_BIC'] = 'Intermediary BIC: ' . $bank_details['intermediary'];
else
    $pdf_data['intermediary_BIC'] = '';

$office = $amdb->get_row("SELECT * FROM offices WHERE offid = '" . $_POST['invoffid'] . "'");
$pdf_data['reference_number'] = ($office ? $office['office_country'] . $office['certificate_prefix'] : '') . str_pad($_REQUEST['clid'], 5, "0", STR_PAD_LEFT);

if (isset($_POST['invoice_number']) && trim($_POST['invoice_number']) != '') {
    $invoice_nr = $_POST['invoice_number'];
    $invoice_number = null; // Not updating number when editing
} else {
    $invoice_number_specs = json_decode($invoicing_office['invoice_number_specs'], true);
    
    // Validate invoice number specs
    if (!$invoice_number_specs || !isset($invoice_number_specs['prefix']) || !isset($invoice_number_specs['length'])) {
        sendJsonResponse(false, 'Invoice number specifications not configured for this office. Please configure invoice settings in the admin panel.');
        exit();
    }
    
    $invoice_number = intval($invoicing_office['invoice_number']) + 1;
    $invoice_nr = $invoice_number_specs['prefix'] . str_pad($invoice_number, $invoice_number_specs['length'], "0", STR_PAD_LEFT);
}

$pdf_data['invoice_date'] = isset($_POST['invoice_date']) ? $_POST['invoice_date'] : date("d.m.Y");
$pdf_data['company_address'] = str_replace("\n", "<br/>", $address);
$pdf_data['invoice_number'] = $invoice_nr;
$pdf_data['vat_rate'] = $_POST['vat'];
$pdf_data['service_type'] = isset($_POST['service_type']) ? $_POST['service_type'] : '';

if (isset($_POST['audit'])) {
    $pdf_data['service_type'] = '<b>' . $pdf_data['service_type'] . '</b><br/><table>';
    $audit = $_POST['audit'];
    if (isset($audit['date']) && trim($audit['date']) != '')
        $pdf_data['service_type'] .= "<tr><td style=\"width:2.5cm\">Audit date:</td><td>$audit[date]</td></tr>";
    if (isset($audit['auditors']) && trim($audit['auditors']) != '')
        $pdf_data['service_type'] .= "<tr><td>Auditor(s):</td><td>" . str_replace("\r\n", '<br/>', $audit['auditors']) . "</td></tr>";
    if (isset($audit['type']) && trim($audit['type']) != '')
        $pdf_data['service_type'] .= "<tr><td>Audit type:</td><td>$audit[type]</td></tr>";
    if (isset($audit['place']) && trim($audit['place']) != '')
        $pdf_data['service_type'] .= "<tr><td>Audit place:</td><td>" . str_replace("\r\n", '<br/>', $audit['place']) . "</td></tr>";
    $pdf_data['service_type'] .= "</table>";
}

$pdf_data['invoice_total'] = number_format($totals['total'], 2, ',', '.');

//printing PDF
require_once("$prog_path/tools/pdf/hcp_pdf.inc.php");
$pdf->SetMargins(20, 20, 20);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->SetPrintFooter(false);

//hqc logo
$hqc_logo = '/data/offices/images/logo.png';
if (file_exists($prog_path . $hqc_logo)) {
    $pdf->Image($file = $prog_path . $hqc_logo, 150, 9, $w = '40', $h = '');
}
$header = '<table cellpadding=0><tr><td style="font-size:17px;font-weight:bold;width:10cm">INVOICE</td><td></td></tr></table>';

$pdf->SetY(20);
if (trim($header) != '')
    $pdf->writeHTML($header);
$pdf->SetY(40);

if ($protected_pdf = get_option('protected_pdf')) {
    $protected_pdf = json_decode($protected_pdf, true);
    if (isset($protected_pdf['invoices']) && isset($protected_pdf['protect']) && trim($protected_pdf['password']) != '') {
        $pdf->SetProtection(array('edit', 'modify', 'copy', 'annot-forms', 'fill-forms', 'extract', 'assemble'), '', $protected_pdf['password'], 0, null);
    }
}

if ($_POST['act'] == 'prv' or $_POST['act'] == 'test')
    showExample();

if (count($pdf_data) > 0) {
    foreach ($pdf_data as $key => $value) {
        $theTemplate = str_replace('[' . $key . ']', $value, $theTemplate);
    }
}

//remove the following tags from the template p,div,span and empty lines
$theTemplate = preg_replace('/<(p|div|span)[^>]*>/', '', $theTemplate);
$theTemplate = preg_replace('/<\/(p|div|span)>/', '', $theTemplate);
$theTemplate = preg_replace('/^\s*$/m', '', $theTemplate);

if (trim($theTemplate) != '') {
    $parts = preg_match_all('/\[pdf(.*)\]/U', $theTemplate, $thePatrs);
    foreach ($thePatrs[0] as $macth) {
        $theTemplate = str_replace($macth, "<brkPoint>" . $macth . "<brkPoint>", $theTemplate);
    }
    $pdfParts = explode('<brkPoint>', $theTemplate);
    ob_start();
    $pdf->writeHTML(ob_get_contents());
    ob_end_clean();
    foreach ($pdfParts as $key => $part) {
        if (strstr($part, '[pdf ')) {
            preg_match('/\[pdf (.*)\((.*)\)\]/', $part, $pdfMatch);
            if (trim($pdfMatch[1]) == 'setY') {
                $y = $pdfMatch[2];
                if (strstr($y, '+'))
                    $y = $pdf->getY() + str_replace('+', '', $y);
                $pdf->setY($y);
            } elseif (trim($pdfMatch[1]) == 'setX') {
                $x = $pdfMatch[2];
                if (strstr($x, '+'))
                    $x = $pdf->getX() + str_replace('+', '', $x);
                $pdf->setX($x);
            } elseif (trim($pdfMatch[1]) == 'addPage') {
                $pdf->SetAutoPageBreak(TRUE, 0);
                $pdf->SetY(262);
                $pdf->writeHTML('<div style="text-align:center">' . $invoice_footer . '</div>');
                $pdf->SetAutoPageBreak(TRUE, 25);
                $pdf->AddPage();
                if ($_POST['act'] == 'prv')
                    showExample();
            }
        } elseif (trim($part) != '') {
            ob_start();
            echo trim($part);
            $pdf->writeHTML(ob_get_contents());
            ob_end_clean();
        }
    }
}

if (isset($invoicing_office['invoice_footer']) && trim($invoicing_office['invoice_footer']) != '') {
    $pdf->SetY(280);
    $pdf->writeHTML('<div style="text-align:center;font-size:12px">' . $invoicing_office['invoice_footer'] . '</div>');
}

if ($_POST['act'] == "crt" or $_POST['act'] == 'test' or $_POST['act'] == 'save_draft' or $_POST['act'] == 'update_draft' or $_POST['act'] == 'save_scheduled' or $_POST['act'] == 'update_scheduled') {
    
    $invFile = null;
    
    if ($_POST['act'] == "crt") {
        $invFile = $prog_path . "/client_data/invoices/{$invoice_nr}.pdf";
        $pdf->Output($invFile, 'F');
        
        if (!file_exists($invFile)) {
            sendJsonResponse(false, 'Failed to create PDF file. Please check folder permissions.');
            return;
        }
    }
    
    if ($_POST['act'] != "test") {
        if (!isset($_POST['update'])) {
            // Updating invoice number only for new invoices (not edits)
            if ($_POST['act'] == "crt" && $invoice_number !== null) {
                $amdb->query("UPDATE hqc_invoicing_offices SET invoice_number='$invoice_number' WHERE offid='" . $_POST['invoffid'] . "'");
            }

            if ($_POST['invoice_type'] == 'audit' and isset($_POST['auid'])) {
                $sql = "UPDATE audits SET invoice_nr = '$invoice_nr', invoiced='" . date("Y-m-d") . "' WHERE auid='" . $_POST['auid'] . "'";
                $amdb->query($sql);
            } elseif ($_POST['invoice_type'] == 'supervision' and isset($_POST['auid'])) {
                $sql = "UPDATE hqc_supervisions SET invoice_nr = '$invoice_nr', invoiced='" . date("Y-m-d") . "', status = 'done' WHERE auid='" . $_POST['auid'] . "'";
                $amdb->query($sql);
            } elseif ($_POST['invoice_type'] == 'expenses' and isset($_POST['exid'])) {
                $sql = "UPDATE expenses SET invoice_nr = '$invoice_nr' WHERE exid='" . $_POST['exid'] . "'";
                $amdb->query($sql);
            } else {
                foreach ($tableToUpdate as $key => $value) {
                    if ($key == 'a' or $key == 'b' or $key == 'sa' or $key == 'sb') {
                        $table = 'certificates_' . $key;
                        $crtNr = 'nr';
                    } else {
                        $table = 'acms_halal_certificates';
                        $crtNr = 'crtNr';
                    }
                    $sql = "UPDATE $table SET invoice_nr = '$invoice_nr' WHERE FIND_IN_SET ($crtNr,'" . implode(',', $value) . "')";
                    $amdb->query($sql);
                }
            }

            $data['clid'] = $_POST['clid'];
            $data['company'] = $client_name;
            $data['invoice_nr'] = $invoice_nr;
            $data['invoice_type'] = $_POST['invoice_type'];
            $data['service_type'] = $_POST['service_type'];
            $data['date'] = $pdf_data['invoice_date'];
        }
        
        if (isset($_POST['item']) and count($_POST['item']) > 0) {
            foreach ($_POST['item'] as $key => $value) {
                if (!isset($_POST['item'][$key]['selected']))
                    unset($_POST['item'][$key]);
            }
            $data['invoice_items'] = json_encode($_POST['item'], JSON_UNESCAPED_UNICODE);
        }
        
        $data['subtotal'] = $totals['subtotal'];
        $data['vat'] = $totals['vat'];
        $data['total'] = $totals['total'];
        $data['invoice_address'] = $pdf_data['company_address'];
        
        if (isset($uba))
            $data['uba'] = $uba;

        // Save user id to invoice
        if (isset($_SESSION["offid"]))
            $data['offid'] = $_SESSION["offid"];

        if (isset($_SESSION['halal']) && isset($_SESSION['halal']['id']))
            $data['uid'] = $_SESSION['halal']['id'];
        
        // IMPORTANT: Set template field - required for invoice to show in list
        $data['template'] = 'nl';
        
        // Set status to active for new invoices
        if (!isset($_POST['nr']) || empty($_POST['nr'])) {
            $data['status'] = 'active';
            $data['inserted_on'] = date('Y-m-d H:i:s');
        }

        if ($_POST['invoice_type'] == 'credit_note' && isset($_POST['credit_invnr'])) {
            $data['credit_invnr'] = $_POST['credit_invnr'];
            $amdb->query("UPDATE invoices SET status = 'credited', credit_invnr='$invoice_nr' WHERE FIND_IN_SET(invoice_nr, '" . $_POST['credit_invnr'] . "')");
        }

        $invoice_data = $_POST;

        if (isset($_POST['audit']))
            $invoice_data['audit'] = $_POST['audit'];

        if ($_POST['act'] == 'save_scheduled' or $_POST['act'] == 'update_scheduled') {
            $data['invoice_options'] = json_encode(array('scheduled' => $_POST['scheduled']));
        } else {
            $data['invoice_options'] = '';
        }
        
        $data['invoice_data'] = encode_json($invoice_data, JSON_UNESCAPED_UNICODE);
        
        if (isset($_POST['nr']) && !empty($_POST['nr']))
            $amdb->update("invoices", $data, "nr='" . $_POST['nr'] . "'");
        else
            $_POST['nr'] = $amdb->insert("invoices", $data);
        
        // Handle draft and scheduled saves
        if ($_POST['act'] == 'save_draft' || $_POST['act'] == 'update_draft') {
            sendJsonResponse(true, 'Draft saved successfully!', [
                'invoice_nr' => $invoice_nr,
                'redirect' => '/invoices/?show=draft'
            ]);
            exit();
        }
        
        if ($_POST['act'] == 'save_scheduled' || $_POST['act'] == 'update_scheduled') {
            sendJsonResponse(true, 'Scheduled invoice saved successfully!', [
                'invoice_nr' => $invoice_nr,
                'redirect' => '/invoices/?show=scheduled'
            ]);
            exit();
        }
    }

    // Handle email sending
    if (isset($_POST['mail_post']) and $_POST['mail_post'] == 'mail') {
        if ($_POST['invoice_type'] == 'credit_note')
            $template_name = 'credit_note';
        else
            $template_name = 'invoice';
            
        if ($row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='$template_name'")) {
            unset($row['pdf_template']);
            
            if (trim($client_name) != '')
                $body['client_name'] = $client_name;
            else
                $body['client_name'] = '';
                
            if (isset($_POST['email_message']) && trim($_POST['email_message']['body']) != '') {
                $style = '';
                if (isset($_POST['email_message_style']) && is_array($_POST['email_message_style'])) {
                    foreach ($_POST['email_message_style'] as $key => $value)
                        $style .= $key . ':' . $value . '; ';
                }
                $body['admin_message'] = '<p style="' . $style . '">' . $_POST['email_message']['body'] . '</p>';
            } else {
                $body['admin_message'] = '';
            }
            
            foreach ($body as $key => $value) {
                $row['email_body'] = str_replace('[' . $key . ']', $value, $row['email_body']);
            }
            
            if ($_POST['act'] == "test" and isset($_POST['test_email']) and trim($_POST['test_email']) != '')
                $email['to_email'] = $_POST['test_email'];
            elseif (isset($_POST['client_email']) and trim($_POST['client_email']) != '')
                $email['to_email'] = $_POST['client_email'];
            else
                $email['to_email'] = $client_email;

            $email['to_name'] = $company_name;
            $email['from_email'] = $row['email_reply_address'];
            $email['from_name'] = $row['email_sender_name'];
            $email['subject'] = $row['email_subject'];
            $email['message'] = $row['email_body'];
            $email['attachments'] = array('invoice-' . $invoice_nr . '.pdf', $invFile);
            
            if (isset($_POST['emailmeacopy']) and $_POST['act'] == "crt")
                $email['emailmeacopy'] = true;
            else
                $email['emailmeacopy'] = false;
                
            if ($footer = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='email_footer'"))
                $email['message'] .= '<p><hr/>' . $footer['email_body'] . '<p>';
                
            if ($_POST['act'] == "test" and isset($_POST['test_email']) and trim($_POST['test_email']) != '')
                $email['subject'] = '(TEST MESSAGE) ' . $email['subject'];
                
            include $prog_path . "/tools/mail/hqc_mail.inc.php";
            $mailRes = hqc_mail($email['to_email'], $email['to_name'], $email['from_email'], $email['from_name'], $email['subject'], $email['message'], $email['attachments'], $email['emailmeacopy'], $seen_id = array('type' => 'invoice', 'nr' => $_POST['nr']));

            if ($mailRes === true) {
                $amdb->query("UPDATE invoices SET sent_by_email = 1 WHERE nr= '" . $_POST['nr'] . "'");
                
                if ($_POST['act'] == "test") {
                    if ($invFile && file_exists($invFile)) {
                        unlink($invFile);
                    }
                    sendJsonResponse(true, 'Test email sent successfully! Please check your inbox.');
                    exit();
                } else {
                    // Invoice created and emailed successfully
                    sendJsonResponse(true, 'Invoice #' . $invoice_nr . ' created and sent successfully!', [
                        'invoice_nr' => $invoice_nr,
                        'redirect' => '/invoices/?show=all'
                    ]);
                    exit();
                }
            } else {
                $amdb->query("UPDATE invoices SET mail_error = '" . $mailRes . "' WHERE nr= '" . $_POST['nr'] . "'");
                sendJsonResponse(false, 'Invoice #' . $invoice_nr . ' created but email failed: ' . $mailRes, [
                    'invoice_nr' => $invoice_nr,
                    'redirect' => '/invoices/?show=all'
                ]);
                exit();
            }
        } else {
            sendJsonResponse(false, 'Email template not found');
            exit();
        }
    } else {
        // Print mode - output PDF directly
        if ($isAjax) {
            // For AJAX print requests, return success with PDF URL
            sendJsonResponse(true, 'Invoice #' . $invoice_nr . ' created successfully!', [
                'invoice_nr' => $invoice_nr,
                'pdf_url' => '/client_data/invoices/' . $invoice_nr . '.pdf',
                'redirect' => '/invoices/?show=all'
            ]);
            exit();
        }
        $pdf->Output('invoice-' . $invoice_nr . '.pdf', 'I');
    }
} else {
    // Preview mode
    $pdf->Output('invoice-' . $invoice_nr . '.pdf', 'I');
}
?>