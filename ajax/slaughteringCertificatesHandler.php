<?php
/**
 * Slaughtering Certificates - CRUD Handler
 * 
 * Handles create, read, update, delete operations for slaughtering certificates
 * Also handles status updates, printing, and export
 */

// Include configuration
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../classes/db.php');

// Check authentication
session_start();
if (!isset($_SESSION["halal"]["id"])) {
    if (isset($_GET['action']) && in_array($_GET['action'], ['print', 'qr', 'export'])) {
        die('Unauthorized');
    }
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_type = $_SESSION["halal"]["user_type"] ?? 'client';
$user_id = $_SESSION["halal"]["id"];
$user_name = $_SESSION["halal"]["name"] ?? '';
$is_admin = in_array($user_type, ['admin', 'superadmin', 'hqc_office']);

// Get action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// Get certificate type
$cert_type = isset($_REQUEST['cert_type']) && in_array($_REQUEST['cert_type'], ['a', 'b', 'sa', 'sb']) 
    ? $_REQUEST['cert_type'] : 'a';
$table = 'certificates_' . $cert_type;

// Helper functions
function generateSuccessResponse($data = null, $message = 'Success') {
    return json_encode(['success' => true, 'message' => $message, 'data' => $data]);
}

function generateErrorResponse($message = 'Error') {
    return json_encode(['success' => false, 'message' => $message]);
}

// Route to appropriate handler
switch ($action) {
    case 'getCertificate':
        getCertificate();
        break;
    case 'create':
        createCertificate();
        break;
    case 'update':
        updateCertificate();
        break;
    case 'delete':
        deleteCertificate();
        break;
    case 'updateStatus':
        updateStatus();
        break;
    case 'getCompanies':
        getCompanies();
        break;
    case 'getCountries':
        getCountries();
        break;
    case 'print':
        printCertificate();
        break;
    case 'qr':
        showQRCode();
        break;
    case 'export':
        exportToExcel();
        break;
    default:
        // Check if form submission (create/update based on form_action)
        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'create') {
                createCertificate();
            } elseif ($_POST['action'] == 'update') {
                updateCertificate();
            } else {
                echo generateErrorResponse('Invalid action');
            }
        } else {
            echo generateErrorResponse('No action specified');
        }
}

/**
 * Get single certificate details
 */
function getCertificate() {
    global $db, $table, $is_admin, $user_id;
    
    $nr = isset($_POST['nr']) ? intval($_POST['nr']) : 0;
    if (!$nr) {
        echo generateErrorResponse('Invalid certificate ID');
        return;
    }

    $sql = "SELECT c.*, 
                   imp.company_name AS importer_name,
                   imp.country1 AS importer_country,
                   exp.company_name AS exporter_name,
                   off.office_name,
                   cl.company_name AS client_company_name
            FROM $table c
            LEFT JOIN companies imp ON c.importer = imp.clid
            LEFT JOIN companies exp ON c.exporter = exp.clid
            LEFT JOIN offices off ON c.offid = off.offid
            LEFT JOIN companies cl ON c.clid = cl.clid
            WHERE c.nr = ?";
    
    // Non-admin can only view own certificates
    if (!$is_admin) {
        $sql .= " AND c.clid = ?";
    }

    $stmt = $db->prepare($sql);
    if ($is_admin) {
        $stmt->bind_param("i", $nr);
    } else {
        $stmt->bind_param("ii", $nr, $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $cert = $result->fetch_assoc();
    $stmt->close();

    if ($cert) {
        echo generateSuccessResponse($cert);
    } else {
        echo generateErrorResponse('Certificate not found');
    }
}

/**
 * Create new certificate
 */
function createCertificate() {
    global $db, $table, $cert_type, $is_admin, $user_id, $user_name;

    if (!$is_admin) {
        echo generateErrorResponse('Permission denied');
        return;
    }

    // Validate required fields
    $required = ['offid', 'clid', 'importer', 'quality', 'weight_net', 'expiry_date'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo generateErrorResponse("Field '$field' is required");
            return;
        }
    }

    // Get company name
    $stmt = $db->prepare("SELECT company_name FROM companies WHERE clid = ?");
    $clid = intval($_POST['clid']);
    $stmt->bind_param("i", $clid);
    $stmt->execute();
    $companyResult = $stmt->get_result()->fetch_assoc();
    $company_name = $companyResult ? $companyResult['company_name'] : '';
    $stmt->close();

    // Prepare data
    $data = [
        'clid' => intval($_POST['clid']),
        'offid' => intval($_POST['offid']),
        'tmplid' => intval($_POST['offid']),
        'company_name' => $company_name,
        'importer' => intval($_POST['importer']),
        'exporter' => !empty($_POST['exporter']) ? intval($_POST['exporter']) : null,
        'producer' => $_POST['producer'] ?? '',
        'quality' => $_POST['quality'],
        'country_of_origin' => $_POST['country_of_origin'] ?? '',
        'weight_gross' => floatval($_POST['weight_gross'] ?? 0),
        'weight_net' => floatval($_POST['weight_net']),
        'transportation_method' => $_POST['transportation_method'] ?? '',
        'transportation_nr' => $_POST['transportation_nr'] ?? '',
        'slaughtering_date' => $_POST['slaughtering_date'] ?? '',
        'slaughter_house' => $_POST['slaughter_house'] ?? '',
        'slaughterer_name' => $_POST['slaughterer_name'] ?? '',
        'production_date' => $_POST['production_date'] ?? '',
        'expiry_date' => $_POST['expiry_date'],
        'hcd_nr' => $_POST['hcd_nr'] ?? '',
        'issue_date' => $_POST['issue_date'] ?? date('d.m.Y'),
        'reference' => $_POST['reference'] ?? '',
        'date' => date('d/m/Y'),
        'requested_by' => json_encode(['uid' => $user_id, 'name' => $user_name]),
        'status' => 'active'
    ];

    // Build INSERT query
    $fields = array_keys($data);
    $placeholders = array_fill(0, count($fields), '?');
    
    $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    $stmt = $db->prepare($sql);
    
    // Build types string and values array
    $types = '';
    $values = [];
    foreach ($data as $key => $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
        $values[] = $value;
    }
    
    $stmt->bind_param($types, ...$values);
    
    if ($stmt->execute()) {
        $newNr = $stmt->insert_id;
        $stmt->close();

        // If and_print flag is set, generate certificate number
        if (isset($_POST['and_print'])) {
            generateCertificateNumber($newNr);
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Certificate created successfully', 
            'nr' => $newNr
        ]);
    } else {
        echo generateErrorResponse('Error creating certificate: ' . $db->error);
    }
}

/**
 * Update existing certificate
 */
function updateCertificate() {
    global $db, $table, $is_admin, $user_id;

    if (!$is_admin) {
        echo generateErrorResponse('Permission denied');
        return;
    }

    $nr = isset($_POST['nr']) ? intval($_POST['nr']) : 0;
    if (!$nr) {
        echo generateErrorResponse('Invalid certificate ID');
        return;
    }

    // Get company name if clid changed
    $company_name = '';
    if (!empty($_POST['clid'])) {
        $stmt = $db->prepare("SELECT company_name FROM companies WHERE clid = ?");
        $clid = intval($_POST['clid']);
        $stmt->bind_param("i", $clid);
        $stmt->execute();
        $companyResult = $stmt->get_result()->fetch_assoc();
        $company_name = $companyResult ? $companyResult['company_name'] : '';
        $stmt->close();
    }

    // Build UPDATE query
    $updates = [];
    $params = [];
    $types = '';

    $fields = [
        'offid' => 'i',
        'clid' => 'i',
        'importer' => 'i',
        'exporter' => 'i',
        'quality' => 's',
        'country_of_origin' => 's',
        'weight_gross' => 'd',
        'weight_net' => 'd',
        'transportation_method' => 's',
        'transportation_nr' => 's',
        'slaughtering_date' => 's',
        'slaughter_house' => 's',
        'slaughterer_name' => 's',
        'production_date' => 's',
        'expiry_date' => 's',
        'hcd_nr' => 's',
        'issue_date' => 's',
        'reference' => 's',
        'producer' => 's'
    ];

    foreach ($fields as $field => $type) {
        if (isset($_POST[$field])) {
            $updates[] = "$field = ?";
            $params[] = $type === 'i' ? intval($_POST[$field]) : 
                       ($type === 'd' ? floatval($_POST[$field]) : $_POST[$field]);
            $types .= $type;
        }
    }

    // Update company name if clid changed
    if ($company_name) {
        $updates[] = "company_name = ?";
        $params[] = $company_name;
        $types .= 's';
    }

    // Update tmplid if offid changed
    if (isset($_POST['offid'])) {
        $updates[] = "tmplid = ?";
        $params[] = intval($_POST['offid']);
        $types .= 'i';
    }

    if (empty($updates)) {
        echo generateErrorResponse('No fields to update');
        return;
    }

    $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE nr = ?";
    $params[] = $nr;
    $types .= 'i';

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $stmt->close();

        // If and_print flag is set
        if (isset($_POST['and_print'])) {
            generateCertificateNumber($nr);
        }

        echo json_encode([
            'success' => true, 
            'message' => 'Certificate updated successfully',
            'nr' => $nr
        ]);
    } else {
        echo generateErrorResponse('Error updating certificate: ' . $db->error);
    }
}

/**
 * Delete certificate
 */
function deleteCertificate() {
    global $db, $table, $is_admin;

    if (!$is_admin) {
        echo generateErrorResponse('Permission denied');
        return;
    }

    $nr = isset($_POST['nr']) ? intval($_POST['nr']) : 0;
    if (!$nr) {
        echo generateErrorResponse('Invalid certificate ID');
        return;
    }

    // Soft delete by marking is_bad or actually delete
    $stmt = $db->prepare("DELETE FROM $table WHERE nr = ?");
    $stmt->bind_param("i", $nr);

    if ($stmt->execute()) {
        $stmt->close();
        echo generateSuccessResponse(null, 'Certificate deleted successfully');
    } else {
        echo generateErrorResponse('Error deleting certificate');
    }
}

/**
 * Update certificate status (sent, received)
 */
function updateStatus() {
    global $db, $table, $is_admin, $user_id, $user_name;

    if (!$is_admin) {
        echo generateErrorResponse('Permission denied');
        return;
    }

    $nr = isset($_POST['nr']) ? intval($_POST['nr']) : 0;
    $statusType = isset($_POST['status_type']) ? $_POST['status_type'] : '';
    $statusDate = isset($_POST['status_date']) ? $_POST['status_date'] : '';

    if (!$nr || !$statusType || !$statusDate) {
        echo generateErrorResponse('Missing required parameters');
        return;
    }

    $handled_by = json_encode(['uid' => $user_id, 'name' => $user_name]);

    switch ($statusType) {
        case 'sent':
            $hcd_process = 'Sent on: ' . $statusDate;
            $stmt = $db->prepare("UPDATE $table SET hcd_process = ?, handled_by = ? WHERE nr = ?");
            $stmt->bind_param("ssi", $hcd_process, $handled_by, $nr);
            break;
        
        case 'received':
            $stmt = $db->prepare("UPDATE $table SET arrived_on = ?, done = 'y', handled_by = ? WHERE nr = ?");
            $stmt->bind_param("ssi", $statusDate, $handled_by, $nr);
            break;
        
        default:
            echo generateErrorResponse('Invalid status type');
            return;
    }

    if ($stmt->execute()) {
        $stmt->close();
        echo generateSuccessResponse(null, 'Status updated successfully');
    } else {
        echo generateErrorResponse('Error updating status');
    }
}

/**
 * Get companies list for dropdowns
 */
function getCompanies() {
    global $db;

    $sql = "SELECT clid, company_name, country1, city1 
            FROM companies 
            WHERE status != 'deleted' 
            ORDER BY company_name ASC";
    
    $result = $db->query($sql);
    $companies = [];
    
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }

    echo generateSuccessResponse($companies);
}

/**
 * Get countries list
 */
function getCountries() {
    // Standard country list
    $countries = [
        'AT' => 'Austria',
        'BE' => 'Belgium',
        'BR' => 'Brazil',
        'DE' => 'Germany',
        'DK' => 'Denmark',
        'ES' => 'Spain',
        'FR' => 'France',
        'HU' => 'Hungary',
        'IE' => 'Ireland',
        'IT' => 'Italy',
        'NL' => 'Netherlands',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'UK' => 'United Kingdom',
        'US' => 'United States',
        'AU' => 'Australia',
        'NZ' => 'New Zealand',
        'AR' => 'Argentina',
        'UY' => 'Uruguay',
        'SA' => 'Saudi Arabia',
        'AE' => 'United Arab Emirates',
        'MY' => 'Malaysia',
        'ID' => 'Indonesia',
        'PK' => 'Pakistan',
        'IN' => 'India',
        'BD' => 'Bangladesh',
        'TR' => 'Turkey',
        'EG' => 'Egypt',
        'ZA' => 'South Africa'
    ];

    // Try to load from countries config if available
    $countriesFile = __DIR__ . '/../config/countries.code.php';
    if (file_exists($countriesFile)) {
        include $countriesFile;
        if (isset($country) && is_array($country)) {
            $countries = $country;
        }
    }

    echo generateSuccessResponse($countries);
}

/**
 * Generate certificate number
 */
function generateCertificateNumber($nr) {
    global $db, $table, $cert_type;

    // Get office info
    $stmt = $db->prepare("SELECT c.offid, o.certificate_prefix, o.office_country 
                          FROM $table c 
                          JOIN offices o ON c.offid = o.offid 
                          WHERE c.nr = ?");
    $stmt->bind_param("i", $nr);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) return;

    $prefix = strtoupper($result['office_country'] . $result['certificate_prefix']);
    
    // Get next number from hc_numbers or calculate
    $numberResult = $db->query("SELECT certificate_nr FROM hc_numbers");
    if ($numberResult && $row = $numberResult->fetch_assoc()) {
        $nextNum = $row['certificate_nr'] + 1;
        $db->query("UPDATE hc_numbers SET certificate_nr = certificate_nr + 1");
    } else {
        // Count existing certificates for this year/office
        $year = date('Y');
        $stmt = $db->prepare("SELECT MAX(nr) as max_nr FROM $table WHERE offid = ? AND date LIKE ?");
        $yearPattern = "%$year%";
        $stmt->bind_param("is", $result['offid'], $yearPattern);
        $stmt->execute();
        $maxResult = $stmt->get_result()->fetch_assoc();
        $nextNum = ($maxResult['max_nr'] ?? 0) + 1;
        $stmt->close();
    }

    // Generate certificate number format: HAAT2025000001
    $certNumber = $prefix . date('Y') . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    
    // Update certificate
    $hcd_process = 'Printed on: ' . date('d/m/Y H:i:s');
    $stmt = $db->prepare("UPDATE $table SET certificate_nr = ?, hcd_process = ?, printed_on = ? WHERE nr = ?");
    $printed_on = date('d/m/Y');
    $stmt->bind_param("sssi", $certNumber, $hcd_process, $printed_on, $nr);
    $stmt->execute();
    $stmt->close();
}

/**
 * Print certificate (generate PDF view)
 */
function printCertificate() {
    global $db, $table;

    $nr = isset($_GET['nr']) ? intval($_GET['nr']) : 0;
    if (!$nr) {
        die('Invalid certificate ID');
    }

    // Get certificate data
    $sql = "SELECT c.*, 
                   imp.company_name AS importer_name,
                   imp.street1 AS importer_street,
                   imp.zip1 AS importer_zip,
                   imp.city1 AS importer_city,
                   imp.country1 AS importer_country,
                   exp.company_name AS exporter_name,
                   exp.street1 AS exporter_street,
                   exp.zip1 AS exporter_zip,
                   exp.city1 AS exporter_city,
                   exp.country1 AS exporter_country,
                   off.office_name,
                   off.office_country,
                   off.certificate_address,
                   cl.company_name AS client_company_name,
                   cl.street1 AS client_street,
                   cl.zip1 AS client_zip,
                   cl.city1 AS client_city,
                   cl.country1 AS client_country
            FROM $table c
            LEFT JOIN companies imp ON c.importer = imp.clid
            LEFT JOIN companies exp ON c.exporter = exp.clid
            LEFT JOIN offices off ON c.offid = off.offid
            LEFT JOIN companies cl ON c.clid = cl.clid
            WHERE c.nr = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $nr);
    $stmt->execute();
    $cert = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$cert) {
        die('Certificate not found');
    }

    // Output printable HTML
    ?>
<!DOCTYPE html>
<html>
<head>
    <title>Certificate <?php echo htmlspecialchars($cert['certificate_nr'] ?? 'Draft'); ?></title>
    <style>
        @page { margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4a6741; padding-bottom: 10px; }
        .header h1 { color: #4a6741; margin: 0; }
        .header h2 { color: #666; margin: 5px 0; font-size: 14px; }
        .cert-number { font-size: 18px; font-weight: bold; color: #4a6741; text-align: center; margin: 15px 0; }
        .section { margin-bottom: 15px; }
        .section-title { background: #f5f5f5; padding: 5px 10px; font-weight: bold; border-left: 3px solid #4a6741; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f9f9f9; width: 30%; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
        .signature-area { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature-box { width: 45%; text-align: center; }
        .signature-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 5px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()">Print Certificate</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="header">
        <h1>HALAL CERTIFICATE</h1>
        <h2><?php echo htmlspecialchars($cert['office_name'] ?? 'IIDC'); ?></h2>
    </div>

    <div class="cert-number">
        Certificate No: <?php echo htmlspecialchars($cert['certificate_nr'] ?? 'DRAFT'); ?>
    </div>

    <div class="section">
        <div class="section-title">Certificate Information</div>
        <table>
            <tr><th>Issue Date</th><td><?php echo htmlspecialchars($cert['issue_date'] ?? '-'); ?></td></tr>
            <tr><th>Expiry Date</th><td><?php echo htmlspecialchars($cert['expiry_date'] ?? '-'); ?></td></tr>
            <tr><th>Country of Origin</th><td><?php echo htmlspecialchars($cert['country_of_origin'] ?? '-'); ?></td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Company Information</div>
        <table>
            <tr><th>Company</th><td><?php echo htmlspecialchars($cert['company_name'] ?? '-'); ?></td></tr>
            <tr><th>Address</th><td>
                <?php 
                echo htmlspecialchars($cert['client_street'] ?? '');
                if ($cert['client_zip'] || $cert['client_city']) {
                    echo ', ' . htmlspecialchars(trim($cert['client_zip'] . ' ' . $cert['client_city']));
                }
                if ($cert['client_country']) {
                    echo ', ' . htmlspecialchars($cert['client_country']);
                }
                ?>
            </td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Product Information</div>
        <table>
            <tr><th>Quality/Description</th><td><?php echo htmlspecialchars($cert['quality'] ?? '-'); ?></td></tr>
            <tr><th>Gross Weight</th><td><?php echo $cert['weight_gross'] ? number_format($cert['weight_gross'], 2) . ' KG' : '-'; ?></td></tr>
            <tr><th>Net Weight</th><td><?php echo $cert['weight_net'] ? number_format($cert['weight_net'], 2) . ' KG' : '-'; ?></td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Importer / Exporter</div>
        <table>
            <tr><th>Importer</th><td>
                <?php 
                echo htmlspecialchars($cert['importer_name'] ?? '-');
                if ($cert['importer_city'] || $cert['importer_country']) {
                    echo '<br>' . htmlspecialchars(trim($cert['importer_city'] . ', ' . $cert['importer_country']));
                }
                ?>
            </td></tr>
            <tr><th>Exporter</th><td>
                <?php 
                echo htmlspecialchars($cert['exporter_name'] ?? '-');
                if ($cert['exporter_city'] || $cert['exporter_country']) {
                    echo '<br>' . htmlspecialchars(trim($cert['exporter_city'] . ', ' . $cert['exporter_country']));
                }
                ?>
            </td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Transportation & Production</div>
        <table>
            <tr><th>Transportation</th><td><?php echo htmlspecialchars(($cert['transportation_method'] ?? '') . ' ' . ($cert['transportation_nr'] ?? '')); ?></td></tr>
            <tr><th>Health Certificate Nr</th><td><?php echo htmlspecialchars($cert['hcd_nr'] ?? '-'); ?></td></tr>
            <tr><th>Slaughtering Date</th><td><?php echo htmlspecialchars($cert['slaughtering_date'] ?? '-'); ?></td></tr>
            <tr><th>Production Date</th><td><?php echo htmlspecialchars($cert['production_date'] ?? '-'); ?></td></tr>
        </table>
    </div>

    <div class="signature-area">
        <div class="signature-box">
            <div class="signature-line">Authorized Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Official Stamp</div>
        </div>
    </div>

    <div class="footer">
        <p>This certificate is issued by <?php echo htmlspecialchars($cert['office_name'] ?? 'IIDC'); ?></p>
        <p>Printed on: <?php echo date('d.m.Y H:i:s'); ?></p>
    </div>
</body>
</html>
    <?php
    exit();
}

/**
 * Show QR code
 */
function showQRCode() {
    global $db, $table;

    $nr = isset($_GET['nr']) ? intval($_GET['nr']) : 0;
    if (!$nr) {
        die('Invalid certificate ID');
    }

    $stmt = $db->prepare("SELECT certificate_nr, qr FROM $table WHERE nr = ?");
    $stmt->bind_param("i", $nr);
    $stmt->execute();
    $cert = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$cert) {
        die('Certificate not found');
    }

    // Generate QR code using Google Charts API (simple approach)
    $qrData = 'Certificate: ' . $cert['certificate_nr'];
    $qrUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($qrData);

    ?>
<!DOCTYPE html>
<html>
<head>
    <title>QR Code - <?php echo htmlspecialchars($cert['certificate_nr']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
        .qr-container { margin: 20px auto; }
        h2 { color: #4a6741; }
    </style>
</head>
<body>
    <h2>Certificate QR Code</h2>
    <p><strong><?php echo htmlspecialchars($cert['certificate_nr']); ?></strong></p>
    <div class="qr-container">
        <img src="<?php echo $qrUrl; ?>" alt="QR Code">
    </div>
    <p><button onclick="window.print()">Print</button> <button onclick="window.close()">Close</button></p>
</body>
</html>
    <?php
    exit();
}

/**
 * Export to Excel
 */
function exportToExcel() {
    global $db, $table, $cert_type, $is_admin, $user_id;

    // Build query similar to getSlaughteringCertificates
    $where = ["1=1"];
    $params = [];
    $paramTypes = "";

    if (!empty($_GET['filterYear'])) {
        $year = intval($_GET['filterYear']);
        $where[] = "c.date LIKE ?";
        $params[] = "%$year%";
        $paramTypes .= "s";
    }

    if (!empty($_GET['filterOffice'])) {
        $where[] = "c.offid = ?";
        $params[] = intval($_GET['filterOffice']);
        $paramTypes .= "i";
    }

    if (!$is_admin) {
        $where[] = "c.clid = ?";
        $params[] = $user_id;
        $paramTypes .= "i";
    }

    $whereClause = implode(' AND ', $where);

    $sql = "SELECT 
                c.nr,
                c.certificate_nr,
                c.issue_date,
                c.weight_net,
                imp.company_name AS importer_name,
                imp.country1 AS importer_country,
                c.company_name,
                c.reference,
                c.hcd_process,
                c.printed_on,
                c.arrived_on
            FROM $table c
            LEFT JOIN companies imp ON c.importer = imp.clid
            WHERE $whereClause
            ORDER BY c.nr DESC";

    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Set headers for CSV download
    $filename = 'slaughtering_certificates_' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    
    // UTF-8 BOM for Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Header row
    fputcsv($output, [
        'No.',
        'Certificate Nr',
        'Issue Date',
        'Weight (KG)',
        'Importer',
        'Country',
        'Company',
        'Reference',
        'Status',
        'Printed On',
        'Arrived On'
    ]);

    // Data rows
    $rowNum = 0;
    while ($row = $result->fetch_assoc()) {
        $rowNum++;
        fputcsv($output, [
            $rowNum,
            $row['certificate_nr'] ?? 'Draft',
            $row['issue_date'],
            $row['weight_net'],
            $row['importer_name'],
            $row['importer_country'],
            $row['company_name'],
            $row['reference'],
            $row['hcd_process'],
            $row['printed_on'],
            $row['arrived_on']
        ]);
    }

    fclose($output);
    $stmt->close();
    exit();
}
