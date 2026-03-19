<?php
/**
 * IIDC Certificates - jqGrid Data Endpoint
 * This file handles fetching certificate data for the jqGrid component
 */

@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
    
    // Get jqGrid parameters
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $rowsPerPage = isset($_POST['rows']) ? intval($_POST['rows']) : 20;
    $sortField = isset($_POST['sidx']) ? $_POST['sidx'] : 'date_of_expiry';
    $sortOrder = isset($_POST['sord']) ? $_POST['sord'] : 'asc';
    
    // Validate sort field to prevent SQL injection
    $allowedSortFields = ['crtNr', 'certificate_nr', 'company_name', 'country1', 'city1', 
                          'office_name', 'date_of_issue', 'date_of_expiry', 'status', 'ordered_on'];
    if (!in_array($sortField, $allowedSortFields)) {
        $sortField = 'date_of_expiry';
    }
    
    // Validate sort order
    $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';
    
    // Get filter parameters
    $filterStatus = isset($_POST['filterStatus']) ? trim($_POST['filterStatus']) : '';
    $filterOffice = isset($_POST['filterOffice']) ? trim($_POST['filterOffice']) : '';
    $filterStandard = isset($_POST['filterStandard']) ? trim($_POST['filterStandard']) : '';
    
    // Search parameters from jqGrid toolbar
    $searchCertNr = isset($_POST['certificate_nr']) ? trim($_POST['certificate_nr']) : '';
    $searchCompany = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
    $searchCountry = isset($_POST['country1']) ? trim($_POST['country1']) : '';
    $searchCity = isset($_POST['city1']) ? trim($_POST['city1']) : '';
    
    // Build WHERE clause
    $whereClauses = ["acms_halal_certificates.status != 'deleted'"];
    $params = [];
    
    // Status filter
    if ($filterStatus !== '') {
        if ($filterStatus === 'expired') {
            // Get certificates expiring within 30 days
            $expiryDate = time() + (30 * 86400);
            $whereClauses[] = "acms_halal_certificates.date_of_expiry > 0 AND acms_halal_certificates.date_of_expiry < :expiryDate";
            $params[':expiryDate'] = $expiryDate;
        } else {
            $whereClauses[] = "acms_halal_certificates.status = :status";
            $params[':status'] = $filterStatus;
        }
    }
    
    // Office filter
    if ($filterOffice !== '') {
        $whereClauses[] = "acms_halal_certificates.offid = :offid";
        $params[':offid'] = $filterOffice;
    }
    
    // Standard filter
    if ($filterStandard !== '') {
        $whereClauses[] = "acms_halal_certificates.reference_standards LIKE :standard";
        $params[':standard'] = '%"' . $filterStandard . '"%';
    }
    
    // Search filters from toolbar
    if ($searchCertNr !== '') {
        $whereClauses[] = "acms_halal_certificates.certificate_nr LIKE :certNr";
        $params[':certNr'] = '%' . $searchCertNr . '%';
    }
    
    if ($searchCompany !== '') {
        $whereClauses[] = "companies.company_name LIKE :companyName";
        $params[':companyName'] = '%' . $searchCompany . '%';
    }
    
    if ($searchCountry !== '') {
        $whereClauses[] = "companies.country1 LIKE :country";
        $params[':country'] = '%' . $searchCountry . '%';
    }
    
    if ($searchCity !== '') {
        $whereClauses[] = "companies.city1 LIKE :city";
        $params[':city'] = '%' . $searchCity . '%';
    }
    
    $whereSQL = implode(' AND ', $whereClauses);
    
    // Count total records
    $countSQL = "SELECT COUNT(DISTINCT acms_halal_certificates.crtNr) AS total 
                 FROM acms_halal_certificates 
                 JOIN companies ON acms_halal_certificates.clid = companies.clid 
                 LEFT JOIN offices ON acms_halal_certificates.offid = offices.offid 
                 WHERE $whereSQL";
    
    $countStmt = $dbo->prepare($countSQL);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Calculate pagination
    $totalPages = $totalRecords > 0 ? ceil($totalRecords / $rowsPerPage) : 0;
    if ($page > $totalPages) $page = $totalPages;
    if ($page < 1) $page = 1;
    
    $offset = ($page - 1) * $rowsPerPage;
    
    // Fetch records
    $sql = "SELECT 
                acms_halal_certificates.crtNr,
                acms_halal_certificates.clid,
                acms_halal_certificates.certificate_nr,
                acms_halal_certificates.date_of_issue,
                acms_halal_certificates.date_of_expiry,
                acms_halal_certificates.status,
                acms_halal_certificates.offid,
                acms_halal_certificates.scope_of_certification,
                acms_halal_certificates.category,
                acms_halal_certificates.reference_standards,
                acms_halal_certificates.ordered_on,
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
            WHERE $whereSQL 
            ORDER BY $sortField $sortOrder 
            LIMIT :offset, :limit";
    
    $stmt = $dbo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $rowsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    
    $rows = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = [
            'id' => $row['crtNr'],
            'cell' => [
                $row['crtNr'],
                $row['certificate_nr'] ?: 'N/A',
                $row['company_name'],
                $row['country1'],
                $row['city1'],
                $row['office_name'],
                $row['date_of_issue'],
                $row['date_of_expiry'],
                $row['status'],
                '', // Actions column - formatted by JavaScript
                $row['clid'],
                $row['offid']
            ]
        ];
    }
    
    // Build jqGrid response
    $response = new stdClass();
    $response->page = $page;
    $response->total = $totalPages;
    $response->records = $totalRecords;
    $response->rows = $rows;
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'page' => 0,
        'total' => 0,
        'records' => 0,
        'rows' => [],
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
