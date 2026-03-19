<?php
/**
 * IIDC Certificates - CRUD Operations Handler
 * Handles all certificate-related operations
 */

@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

// Helper functions
function generateSuccessResponse($data = null) {
    return ['status' => 1, 'statusDescription' => 'Success', 'data' => $data];
}

function generateErrorResponse($message) {
    return ['status' => 0, 'statusDescription' => $message, 'data' => null];
}

// Get action from request
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
    
    // Verify user is logged in
    $myuser = cuser::singleton();
    $myuser->getUserData();
    
    if (!isset($myuser->userdata['id'])) {
        echo json_encode(generateErrorResponse('User not authenticated'));
        exit;
    }
    
    $isAdmin = $myuser->userdata['isadmin'] == 1;
    $userId = $myuser->userdata['id'];
    
    switch ($action) {
        case 'getCertificate':
            getCertificate($dbo);
            break;
            
        case 'createCertificate':
            if (!$isAdmin) {
                echo json_encode(generateErrorResponse('Permission denied'));
                exit;
            }
            createCertificate($dbo);
            break;
            
        case 'updateCertificate':
            if (!$isAdmin) {
                echo json_encode(generateErrorResponse('Permission denied'));
                exit;
            }
            updateCertificate($dbo);
            break;
            
        case 'deleteCertificate':
            if (!$isAdmin) {
                echo json_encode(generateErrorResponse('Permission denied'));
                exit;
            }
            deleteCertificate($dbo);
            break;
            
        case 'getCategories':
            getCategories($dbo);
            break;
            
        case 'getCompanies':
            getCompanies($dbo);
            break;
            
        case 'printCertificate':
            printCertificate($dbo);
            break;
            
        case 'downloadCertificate':
            downloadCertificate($dbo);
            break;
            
        default:
            echo json_encode(generateErrorResponse('Invalid action'));
            break;
    }
    
} catch (PDOException $e) {
    echo json_encode(generateErrorResponse('Database error: ' . $e->getMessage()));
}

/**
 * Get a single certificate by crtNr
 */
function getCertificate($dbo) {
    $crtNr = isset($_POST['crtNr']) ? intval($_POST['crtNr']) : 0;
    
    if ($crtNr <= 0) {
        echo json_encode(generateErrorResponse('Invalid certificate ID'));
        return;
    }
    
    $sql = "SELECT 
                acms_halal_certificates.*,
                companies.company_name,
                companies.street1,
                companies.city1,
                companies.zip1,
                companies.country1,
                companies.email1,
                companies.tel1,
                companies.contact_name1,
                companies.contact_surname1,
                offices.office_name
            FROM acms_halal_certificates 
            JOIN companies ON acms_halal_certificates.clid = companies.clid 
            LEFT JOIN offices ON acms_halal_certificates.offid = offices.offid 
            WHERE acms_halal_certificates.crtNr = :crtNr";
    
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':crtNr', $crtNr, PDO::PARAM_INT);
    $stmt->execute();
    
    $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$certificate) {
        echo json_encode(generateErrorResponse('Certificate not found'));
        return;
    }
    
    echo json_encode(generateSuccessResponse($certificate));
}

/**
 * Create a new certificate
 */
function createCertificate($dbo) {
    $clid = isset($_POST['clid']) ? intval($_POST['clid']) : 0;
    $offid = isset($_POST['offid']) ? intval($_POST['offid']) : 0;
    $certificateNr = isset($_POST['certificate_nr']) ? trim($_POST['certificate_nr']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'pending';
    $scope = isset($_POST['scope_of_certification']) ? trim($_POST['scope_of_certification']) : '';
    $dateOfIssue = isset($_POST['date_of_issue']) ? $_POST['date_of_issue'] : '';
    $dateOfExpiry = isset($_POST['date_of_expiry']) ? $_POST['date_of_expiry'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '[]';
    $referenceStandards = isset($_POST['reference_standards']) ? $_POST['reference_standards'] : '[]';
    
    // Validate required fields
    if ($clid <= 0) {
        echo json_encode(generateErrorResponse('Company is required'));
        return;
    }
    
    // Convert dates to timestamps
    $issueTimestamp = !empty($dateOfIssue) ? strtotime($dateOfIssue) : 0;
    $expiryTimestamp = !empty($dateOfExpiry) ? strtotime($dateOfExpiry) : 0;
    
    $sql = "INSERT INTO acms_halal_certificates 
            (clid, offid, certificate_nr, status, scope_of_certification, 
             date_of_issue, date_of_expiry, category, reference_standards, ordered_on) 
            VALUES 
            (:clid, :offid, :certificate_nr, :status, :scope_of_certification, 
             :date_of_issue, :date_of_expiry, :category, :reference_standards, :ordered_on)";
    
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':clid', $clid, PDO::PARAM_INT);
    $stmt->bindValue(':offid', $offid, PDO::PARAM_INT);
    $stmt->bindValue(':certificate_nr', $certificateNr);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':scope_of_certification', $scope);
    $stmt->bindValue(':date_of_issue', $issueTimestamp, PDO::PARAM_INT);
    $stmt->bindValue(':date_of_expiry', $expiryTimestamp, PDO::PARAM_INT);
    $stmt->bindValue(':category', $category);
    $stmt->bindValue(':reference_standards', $referenceStandards);
    $stmt->bindValue(':ordered_on', time(), PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        echo json_encode(generateErrorResponse('Failed to create certificate'));
        return;
    }
    
    $newCrtNr = $dbo->lastInsertId();
    echo json_encode(generateSuccessResponse(['crtNr' => $newCrtNr]));
}

/**
 * Update an existing certificate
 */
function updateCertificate($dbo) {
    $crtNr = isset($_POST['crtNr']) ? intval($_POST['crtNr']) : 0;
    
    if ($crtNr <= 0) {
        echo json_encode(generateErrorResponse('Invalid certificate ID'));
        return;
    }
    
    $offid = isset($_POST['offid']) ? intval($_POST['offid']) : 0;
    $certificateNr = isset($_POST['certificate_nr']) ? trim($_POST['certificate_nr']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'pending';
    $scope = isset($_POST['scope_of_certification']) ? trim($_POST['scope_of_certification']) : '';
    $dateOfIssue = isset($_POST['date_of_issue']) ? $_POST['date_of_issue'] : '';
    $dateOfExpiry = isset($_POST['date_of_expiry']) ? $_POST['date_of_expiry'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '[]';
    $referenceStandards = isset($_POST['reference_standards']) ? $_POST['reference_standards'] : '[]';
    
    // Convert dates to timestamps
    $issueTimestamp = !empty($dateOfIssue) ? strtotime($dateOfIssue) : 0;
    $expiryTimestamp = !empty($dateOfExpiry) ? strtotime($dateOfExpiry) : 0;
    
    $sql = "UPDATE acms_halal_certificates SET 
                offid = :offid,
                certificate_nr = :certificate_nr,
                status = :status,
                scope_of_certification = :scope_of_certification,
                date_of_issue = :date_of_issue,
                date_of_expiry = :date_of_expiry,
                category = :category,
                reference_standards = :reference_standards
            WHERE crtNr = :crtNr";
    
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':crtNr', $crtNr, PDO::PARAM_INT);
    $stmt->bindValue(':offid', $offid, PDO::PARAM_INT);
    $stmt->bindValue(':certificate_nr', $certificateNr);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':scope_of_certification', $scope);
    $stmt->bindValue(':date_of_issue', $issueTimestamp, PDO::PARAM_INT);
    $stmt->bindValue(':date_of_expiry', $expiryTimestamp, PDO::PARAM_INT);
    $stmt->bindValue(':category', $category);
    $stmt->bindValue(':reference_standards', $referenceStandards);
    
    if (!$stmt->execute()) {
        echo json_encode(generateErrorResponse('Failed to update certificate'));
        return;
    }
    
    echo json_encode(generateSuccessResponse(['crtNr' => $crtNr]));
}

/**
 * Delete a certificate (soft delete)
 */
function deleteCertificate($dbo) {
    $crtNr = isset($_POST['crtNr']) ? intval($_POST['crtNr']) : 0;
    
    if ($crtNr <= 0) {
        echo json_encode(generateErrorResponse('Invalid certificate ID'));
        return;
    }
    
    // Soft delete by setting status to 'deleted'
    $sql = "UPDATE acms_halal_certificates SET status = 'deleted' WHERE crtNr = :crtNr";
    
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':crtNr', $crtNr, PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        echo json_encode(generateErrorResponse('Failed to delete certificate'));
        return;
    }
    
    echo json_encode(generateSuccessResponse(['deleted' => true]));
}

/**
 * Get all active categories
 */
function getCategories($dbo) {
    $sql = "SELECT catid, category, category_name 
            FROM hqc_categories 
            WHERE status = 'active' 
            ORDER BY category ASC";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(generateSuccessResponse(['categories' => $categories]));
}

/**
 * Get all active companies
 */
function getCompanies($dbo) {
    $sql = "SELECT clid, company_name, country1, city1 
            FROM companies 
            WHERE status != 'deleted' 
            ORDER BY company_name ASC";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(generateSuccessResponse(['companies' => $companies]));
}

/**
 * Print certificate - generates HTML view for printing
 */
function printCertificate($dbo) {
    $crtNr = isset($_GET['crtNr']) ? intval($_GET['crtNr']) : 0;
    
    if ($crtNr <= 0) {
        echo '<h1>Invalid certificate ID</h1>';
        return;
    }
    
    $sql = "SELECT 
                acms_halal_certificates.*,
                companies.company_name,
                companies.street1,
                companies.city1,
                companies.zip1,
                companies.country1,
                companies.email1,
                companies.tel1,
                offices.office_name
            FROM acms_halal_certificates 
            JOIN companies ON acms_halal_certificates.clid = companies.clid 
            LEFT JOIN offices ON acms_halal_certificates.offid = offices.offid 
            WHERE acms_halal_certificates.crtNr = :crtNr";
    
    $stmt = $dbo->prepare($sql);
    $stmt->bindValue(':crtNr', $crtNr, PDO::PARAM_INT);
    $stmt->execute();
    
    $cert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cert) {
        echo '<h1>Certificate not found</h1>';
        return;
    }
    
    // Get categories
    $categories = [];
    if (!empty($cert['category'])) {
        $catIds = json_decode($cert['category'], true);
        if (is_array($catIds) && count($catIds) > 0) {
            $placeholders = implode(',', array_fill(0, count($catIds), '?'));
            $catSql = "SELECT category, category_name FROM hqc_categories WHERE catid IN ($placeholders)";
            $catStmt = $dbo->prepare($catSql);
            $catStmt->execute($catIds);
            $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Get standards
    $standards = [];
    if (!empty($cert['reference_standards'])) {
        $stdIds = json_decode($cert['reference_standards'], true);
        if (is_array($stdIds) && count($stdIds) > 0) {
            $placeholders = implode(',', array_fill(0, count($stdIds), '?'));
            $stdSql = "SELECT code, description FROM hqc_halal_standards WHERE stnid IN ($placeholders)";
            $stdStmt = $dbo->prepare($stdSql);
            $stdStmt->execute($stdIds);
            $standards = $stdStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    // Output printable HTML
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Halal Certificate - <?php echo htmlspecialchars($cert['certificate_nr'] ?: 'N/A'); ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .certificate-header { text-align: center; border-bottom: 2px solid #2F8B43; padding-bottom: 20px; margin-bottom: 30px; }
            .certificate-header h1 { color: #2F8B43; margin: 0; }
            .certificate-header h2 { color: #666; margin: 10px 0 0 0; }
            .certificate-body { margin: 30px 0; }
            .info-section { margin-bottom: 30px; }
            .info-section h3 { color: #2F8B43; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
            .info-row { display: flex; margin: 10px 0; }
            .info-label { font-weight: bold; width: 200px; }
            .info-value { flex: 1; }
            .certificate-footer { text-align: center; margin-top: 50px; padding-top: 20px; border-top: 2px solid #2F8B43; }
            @media print {
                body { margin: 20px; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="no-print" style="text-align: center; margin-bottom: 20px;">
            <button onclick="window.print();" style="padding: 10px 20px; font-size: 16px;">Print Certificate</button>
        </div>
        
        <div class="certificate-header">
            <h1>HALAL CERTIFICATE</h1>
            <h2><?php echo htmlspecialchars($cert['certificate_nr'] ?: 'Certificate Number Pending'); ?></h2>
        </div>
        
        <div class="certificate-body">
            <div class="info-section">
                <h3>Company Information</h3>
                <div class="info-row">
                    <span class="info-label">Company Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cert['company_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address:</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars($cert['street1']); ?><br>
                        <?php echo htmlspecialchars($cert['zip1'] . ' ' . $cert['city1']); ?><br>
                        <?php echo htmlspecialchars($cert['country1']); ?>
                    </span>
                </div>
            </div>
            
            <div class="info-section">
                <h3>Certificate Details</h3>
                <div class="info-row">
                    <span class="info-label">Certificate Number:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cert['certificate_nr'] ?: 'N/A'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date of Issue:</span>
                    <span class="info-value"><?php echo $cert['date_of_issue'] > 0 ? date('d M Y', $cert['date_of_issue']) : 'N/A'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date of Expiry:</span>
                    <span class="info-value"><?php echo $cert['date_of_expiry'] > 0 ? date('d M Y', $cert['date_of_expiry']) : 'N/A'; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Issuing Office:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cert['office_name'] ?: 'N/A'); ?></span>
                </div>
            </div>
            
            <?php if (!empty($cert['scope_of_certification'])): ?>
            <div class="info-section">
                <h3>Scope of Certification</h3>
                <p><?php echo nl2br(htmlspecialchars($cert['scope_of_certification'])); ?></p>
            </div>
            <?php endif; ?>
            
            <?php if (count($categories) > 0): ?>
            <div class="info-section">
                <h3>Categories</h3>
                <ul>
                    <?php foreach ($categories as $cat): ?>
                    <li><?php echo htmlspecialchars($cat['category'] . ' - ' . $cat['category_name']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (count($standards) > 0): ?>
            <div class="info-section">
                <h3>Reference Standards</h3>
                <ul>
                    <?php foreach ($standards as $std): ?>
                    <li><?php echo htmlspecialchars($std['code'] . ' - ' . $std['description']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="certificate-footer">
            <p>This certificate is valid until <?php echo $cert['date_of_expiry'] > 0 ? date('d M Y', $cert['date_of_expiry']) : 'N/A'; ?></p>
            <p style="color: #666; font-size: 12px;">Issued by HQC Halal Quality Control</p>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Download certificate as PDF (placeholder - requires PDF library)
 */
function downloadCertificate($dbo) {
    // For now, redirect to print view
    // In production, this would use TCPDF or similar to generate PDF
    $crtNr = isset($_GET['crtNr']) ? intval($_GET['crtNr']) : 0;
    header('Location: ?action=printCertificate&crtNr=' . $crtNr);
    exit;
}
