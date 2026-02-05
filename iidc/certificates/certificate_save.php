<?php
session_start();
$username = $_SESSION["username"];
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";
include "../config/get_ip.inc.php";
include "../config/defaults.inc.php";

// Check if this is an AJAX request
$isAjax = isset($_POST['ajax']) && $_POST['ajax'] == '1';

/**
 * Send JSON response for AJAX requests
 */
function sendJsonResponse($success, $message = '', $data = array()) {
    header('Content-Type: application/json');
    $response = array_merge(array(
        'success' => $success,
        'message' => $message
    ), $data);
    echo json_encode($response);
    exit();
}

/**
 * Send redirect response (handles both AJAX and non-AJAX)
 */
function sendRedirect($url, $isAjax, $message = '', $pdfUrl = null) {
    if ($isAjax) {
        $data = array('redirect' => $url);
        if ($pdfUrl) {
            $data['pdf_url'] = $pdfUrl;
        }
        sendJsonResponse(true, $message, $data);
    } else {
        header("Location: $url");
        exit();
    }
}

extract($_POST);
if (isset($tp))
    $tbl = "certificates_$tp";

// Handle draft status
if ($_POST['act'] == 'draft') {
    $_POST['act'] = 'add';
    $_POST['status'] = 'draft';
}
if ($_POST['act'] == 'saveDraft') {
    $_POST['act'] = 'edit';
    $_POST['status'] = 'draft';
}

if ($_POST['act'] == 'edit') {
    $_POST['status'] = 'active';
}

// Handle file deletion
if (isset($act) && $act == 'delete_file' && isset($file) && isset($_POST['nr'])) {
    if (trim($_POST['nr']) == '') {
        if ($isAjax) {
            sendJsonResponse(false, 'Invalid certificate number');
        }
        return;
    }

    if (file_exists($hcp_path . $file))
        unlink($hcp_path . $file);

    if ($certificate = $amdb->get_row("SELECT attachments FROM $tbl WHERE nr='$_POST[nr]'")) {
        $attachments = array_values(json_decode($certificate['attachments'], true));

        if (in_array($file, $attachments)) {
            $file = array_search($file, $attachments);
            unset($attachments[$file]);
            $amdb->update($tbl, array('attachments' => json_encode($attachments)), "nr='$_POST[nr]'");
            
            if ($isAjax) {
                sendJsonResponse(true, 'File deleted successfully');
            }
            echo "ok";
        }
    }
    exit();
}

if (isset($_POST['nr']))
    $crtNr = $_POST['nr'];

// Validate and process options
if (isset($_POST['option'])) {
    $toCheck = array('producer', 'importer', 'exporter');
    foreach ($toCheck as $check) {
        if (isset($_POST['option'][$check])) {
            $_POST['option'][$check] = trim($_POST['option'][$check]);
            if ($_POST[$check] == '0' && $_POST['option'][$check] == '') {
                if ($isAjax) {
                    sendJsonResponse(false, ucfirst($check) . ' is required');
                }
                $amdb->post_results($check . ' is required');
                exit();
            }
        }
    }
    if (isset($_POST['options']))
        $_POST['options'] = $_POST['options'] + $_POST['option'];
    else
        $_POST['options'] = $_POST['option'];
}

if (isset($_POST['extra']))
    $_POST['options'] = $_POST['options'] + array("extra" => $_POST['extra']);

if (isset($_POST['options']))
    $_POST['options'] = json_encode($_POST['options'], JSON_UNESCAPED_UNICODE);

if (!isset($_POST['print_flag']))
    $_POST['print_flag'] = 0;

if (!isset($_POST['eiaci']))
    $_POST['print_eiaci'] = 0;
else
    $_POST['print_eiaci'] = 1;

if (!isset($_POST['shc']))
    $_POST['print_shc'] = 0;
else
    $_POST['print_shc'] = 1;

if (!isset($_POST['hak']))
    $_POST['print_hak'] = 0;
else
    $_POST['print_hak'] = 1;

if (isset($_POST['products'])) {
    $_POST['products'] = serialize($_POST['products']);
}

if (!isset($_POST['tmplid']))
    $_POST['tmplid'] = 0;

// Process weight fields
if (trim($_POST['weight_gross_gram']) == '')
    $_POST['weight_gross_gram'] = '0';

if (isset($_POST['weight_gross']) && trim($_POST['weight_gross_gram']) != '')
    $_POST['weight_gross'] .= '.' . $_POST['weight_gross_gram'];

if (trim($_POST['weight_net_gram']) == '')
    $_POST['weight_net_gram'] = '0';

if (isset($_POST['weight_net']) && trim($_POST['weight_net_gram']) != '')
    $_POST['weight_net'] .= '.' . $_POST['weight_net_gram'];

// Handle add/edit actions
if (isset($_POST['act'])) {
    if ($_POST['act'] == 'add' && check_nonce()) {
        $_POST['date'] = date("d/m/Y");
        $_POST['inserted_on'] = date("Y-m-d H:i:s"); // Add timestamp for filtering
        $_POST['requested_by'] = json_encode($_SESSION['user']);
        
        // Set default status if not already set (e.g., 'draft')
        if (!isset($_POST['status']) || empty($_POST['status'])) {
            $_POST['status'] = 'active';
        }
        
        if (isset($_POST['do']) && $_POST['do'] == 'print')
            $_POST['hcd_process'] = 'printed on: ' . date("d/m/Y H:i:s");
        
        // Generate certificate_nr and reference_number for new certificates
        $office = $amdb->get_row("SELECT * FROM offices WHERE offid = '" . $_POST['offid'] . "'");
        if ($office) {
            // Generate reference_number
            $_POST['reference_number'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($_POST['clid'], 5, "0", STR_PAD_LEFT);
            
            // Generate certificate_nr
            $optionKey = "shipment_crtNr_" . $_POST['tp']; // Different counter for each type (a, b, sa, sb)
            if ($total = get_option($optionKey, $_POST['offid']))
                $total++;
            else
                $total = 1;
            update_option($optionKey, $total, $_POST['offid']);
            $_POST['certificate_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($total, 5, "0", STR_PAD_LEFT);
        }
        
        $crtNr = $amdb->insert($tbl, $_POST);
        $_POST['nr'] = $crtNr;
    }
    if ($_POST['act'] == 'edit') {
        // For edit, only update certificate_nr if it's empty and we're printing
        if (isset($_POST['do']) && $_POST['do'] == 'print') {
            if (!isset($_POST['certificate_nr']) || trim($_POST['certificate_nr']) == '' || $_POST['certificate_nr'] == '0') {
                $office = $amdb->get_row("SELECT * FROM offices WHERE offid = '" . $_POST['offid'] . "'");
                if ($office) {
                    // Generate certificate_nr
                    $optionKey = "shipment_crtNr_" . $_POST['tp'];
                    if ($total = get_option($optionKey, $_POST['offid']))
                        $total++;
                    else
                        $total = 1;
                    update_option($optionKey, $total, $_POST['offid']);
                    $_POST['certificate_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($total, 5, "0", STR_PAD_LEFT);
                }
            } elseif (isset($_POST['keepOldCrtNumber'])) {
                // Keep the existing certificate number - don't change it
            }
        }
        $amdb->update($tbl, $_POST, "nr='$_POST[nr]'");
    }

    // Update CRN for importer if provided
    if (isset($_POST['importer']) && $_POST['importer'] != '' && isset($_POST['CRN']) && trim($_POST['CRN']) != '') {
        $amdb->update('companies', array('CRN' => $_POST['CRN']), "clid='$_POST[importer]'");
    }
}

// Handle file uploads
if (isset($_FILES) && isset($_FILES['attachment']) && count($_FILES['attachment']) > 0) {
    $path = "/client_data/certificates/attachments/" . str_pad($_REQUEST['clid'], 5, "0", STR_PAD_LEFT) . "/" . $crtNr;
    if ($attachments = upload_files($_FILES['attachment'], $path, true)) {
        if ($certificate = $amdb->get_row("SELECT attachments FROM $tbl WHERE nr='$crtNr'")) {
            if (is_array(json_decode($certificate['attachments'], true)))
                $attachments = array_merge(json_decode($certificate['attachments'], true), $attachments);
        }
        $amdb->update($tbl, array('attachments' => json_encode($attachments)), "nr='$crtNr'");
    }
}

// Handle delete action
if (isset($act) && $act == 'del') {
    MYSQL_QUERY("DELETE from $tbl where nr='$nr'");
    
    if ($isAjax) {
        sendJsonResponse(true, 'Certificate deleted successfully');
    }
}

// Handle print action
if (isset($_POST['do']) && $_POST['do'] == 'print') {
    // Ensure certificate_nr is generated if not already set
    if (!isset($_POST['certificate_nr']) || trim($_POST['certificate_nr']) == '' || $_POST['certificate_nr'] == '0') {
        $office = $amdb->get_row("SELECT * FROM offices WHERE offid = '" . $_POST['offid'] . "'");
        if ($office) {
            $optionKey = "shipment_crtNr_" . $_POST['tp'];
            if ($total = get_option($optionKey, $_POST['offid']))
                $total++;
            else
                $total = 1;
            update_option($optionKey, $total, $_POST['offid']);
            $_POST['certificate_nr'] = $office['office_country'] . $office['certificate_prefix'] . str_pad($total, 5, "0", STR_PAD_LEFT);
            
            // Update the database with the new certificate_nr
            $amdb->update($tbl, array('certificate_nr' => $_POST['certificate_nr']), "nr='{$_POST['nr']}'");
        }
    }
    
    // Update status to printed
    $amdb->update($tbl, array(
        'status' => 'printed',
        'printed_on' => time(),
        'hcd_process' => 'printed on: ' . date("d/m/Y H:i:s")
    ), "nr='{$_POST['nr']}'");
    
    // Build query string for PDF generation
    $urlQuery = '';

    if (isset($_POST['keepOldCrtNumber']) && isset($_POST['certificate_nr']) && trim($_POST['certificate_nr']) != '')
        $urlQuery .= '&keepOldCrtNumber=1&certificate_nr=' . urlencode($_POST['certificate_nr']);

    if (isset($_POST['print_flag']) && $_POST['print_flag'] == 1)
        $urlQuery .= '&flag=1';

    if (isset($_POST['eiaci']) && $_POST['eiaci'] == 1)
        $urlQuery .= '&eiaci=1';

    if (isset($_POST['shc']) && $_POST['shc'] == 1)
        $urlQuery .= '&shc=1';

    if (isset($_POST['hak']) && $_POST['hak'] == 1)
        $urlQuery .= '&hak=1';

    // Determine redirect URL based on user type
    if ($_SESSION['user_type'] == "admin") {
        $redirectUrl = "/iidc/admin/?inc=certificates&tp={$_POST['tp']}&offid={$_POST['offid']}";
    } elseif ($_SESSION['user_type'] == "hqc_office") {
        $redirectUrl = '/iidc/offices/home/';
    } else {
        $redirectUrl = '/iidc/company/';
    }

    if ($isAjax) {
        // For AJAX requests, return JSON with PDF URL and redirect info
        // The frontend will open the PDF in a new window and then redirect
        sendJsonResponse(true, 'Certificate generated successfully', array(
            'redirect' => $redirectUrl,
            'pdf_url' => 'pdf_certificate.php?act=print&nr=' . $_POST['nr'] . '&tp=' . $_POST['tp'] . $urlQuery,
            'certificate_nr' => isset($_POST['certificate_nr']) ? $_POST['certificate_nr'] : $crtNr
        ));
    } else {
        // For non-AJAX requests, use self-submitting form approach (no iframe)
        $offid_redirect = isset($_POST['offid']) ? $_POST['offid'] : 0;
        ?>
        <!DOCTYPE html>
        <html>
        <head><title>Certificate</title></head>
        <body style="display:none;">
            <form id="pdfForm" action="pdf_certificate.php" method="post" target="_blank">
                <?php
                // Pass all POST data as hidden fields
                foreach ($_POST as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $subKey => $subValue) {
                            if (is_array($subValue)) {
                                foreach ($subValue as $subSubKey => $subSubValue) {
                                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '[' . htmlspecialchars($subKey) . '][' . htmlspecialchars($subSubKey) . ']" value="' . htmlspecialchars($subSubValue) . '" />';
                                }
                            } else {
                                echo '<input type="hidden" name="' . htmlspecialchars($key) . '[' . htmlspecialchars($subKey) . ']" value="' . htmlspecialchars($subValue) . '" />';
                            }
                        }
                    } else {
                        echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" />';
                    }
                }
                ?>
                <input type="hidden" name="act" value="print" />
            </form>
            <script>
                document.getElementById('pdfForm').submit();
                window.location.href = '<?php echo $redirectUrl; ?>';
            </script>
        </body>
        </html>
        <?php
        exit();
    }
}

// Handle request action (from clients)
if (isset($_POST['do']) && $_POST['do'] == 'request') {
    // Update status to requested
    $amdb->update($tbl, array('status' => 'requested'), "nr='{$_POST['nr']}'");
    
    if ($isAjax) {
        sendJsonResponse(true, 'Certificate request submitted successfully', array(
            'redirect' => '/iidc/company/'
        ));
    } else {
        header("Location: /iidc/company/");
        exit();
    }
}

// Default redirect based on user type
$redirectUrl = '/iidc/company/';
$message = '';

if ($_SESSION['user_type'] == "admin") {
    $redirectUrl = '/iidc/admin/';
    if (isset($_POST['tp']) && isset($_POST['offid'])) {
        $redirectUrl = "/iidc/admin/?inc=certificates&tp={$_POST['tp']}&offid={$_POST['offid']}";
    }
} elseif ($_SESSION['user_type'] == "hqc_office") {
    $redirectUrl = '/iidc/offices/home/';
    if (isset($_POST['tp']) && isset($_POST['offid'])) {
        $redirectUrl = "/iidc/offices/home/?inc=certificates&tp={$_POST['tp']}&offid={$_POST['offid']}";
    }
}

// Set success message based on action
if (isset($_POST['status']) && $_POST['status'] == 'draft') {
    $message = 'Draft saved successfully';
} elseif (isset($_POST['act']) && $_POST['act'] == 'edit') {
    $message = 'Certificate updated successfully';
} elseif (isset($_POST['act']) && $_POST['act'] == 'add') {
    $message = 'Certificate created successfully';
}

if ($isAjax) {
    sendJsonResponse(true, $message, array(
        'redirect' => $redirectUrl,
        'certificate_id' => isset($crtNr) ? $crtNr : null
    ));
} else {
    header("Location: $redirectUrl");
    exit();
}