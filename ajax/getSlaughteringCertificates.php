<?php
/**
 * Slaughtering Certificates - jqGrid Data Endpoint
 * 
 * Returns JSON data for jqGrid with pagination, sorting, and filtering
 * Works with certificates_a, certificates_b tables
 */

header('Content-Type: application/json');

// Include configuration
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../classes/db.php');

// Check authentication
session_start();
if (!isset($_SESSION["halal"]["id"])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_type = $_SESSION["halal"]["user_type"] ?? 'client';
$user_id = $_SESSION["halal"]["id"];
$is_admin = in_array($user_type, ['admin', 'superadmin', 'hqc_office']);

// Get certificate type (a, b, sa, sb)
$cert_type = isset($_POST['cert_type']) && in_array($_POST['cert_type'], ['a', 'b', 'sa', 'sb']) 
    ? $_POST['cert_type'] : 'a';
$table = 'certificates_' . $cert_type;

// jqGrid parameters
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 20;
$sidx = isset($_POST['sidx']) ? $_POST['sidx'] : 'nr';
$sord = isset($_POST['sord']) && $_POST['sord'] == 'asc' ? 'ASC' : 'DESC';

// Validate sort column to prevent SQL injection
$validSortColumns = ['nr', 'certificate_nr', 'issue_date', 'weight_net', 'company_name', 'hcd_process', 'reference'];
if (!in_array($sidx, $validSortColumns)) {
    $sidx = 'nr';
}

// Build WHERE clause
$where = ["1=1"];
$params = [];
$paramTypes = "";

// Filter by year
if (!empty($_POST['filterYear'])) {
    $year = intval($_POST['filterYear']);
    $where[] = "c.date LIKE ?";
    $params[] = "%$year%";
    $paramTypes .= "s";
}

// Filter by office
if (!empty($_POST['filterOffice'])) {
    $where[] = "c.offid = ?";
    $params[] = intval($_POST['filterOffice']);
    $paramTypes .= "i";
}

// Filter by status
if (!empty($_POST['filterStatus'])) {
    switch ($_POST['filterStatus']) {
        case 'new':
            $where[] = "(c.hcd_process IS NULL OR c.hcd_process = '')";
            break;
        case 'printed':
            $where[] = "c.hcd_process LIKE '%Printed%'";
            break;
        case 'sent':
            $where[] = "c.hcd_process LIKE '%Sent%'";
            break;
        case 'arrived':
            $where[] = "c.arrived_on IS NOT NULL AND c.arrived_on != ''";
            break;
    }
}

// Search filter
if (!empty($_POST['filterSearch']) && !empty($_POST['filterSearchBy'])) {
    $searchTerm = '%' . $_POST['filterSearch'] . '%';
    $searchBy = $_POST['filterSearchBy'];
    
    switch ($searchBy) {
        case 'certificate_nr':
            $where[] = "c.certificate_nr LIKE ?";
            $params[] = $searchTerm;
            $paramTypes .= "s";
            break;
        case 'company_name':
            $where[] = "c.company_name LIKE ?";
            $params[] = $searchTerm;
            $paramTypes .= "s";
            break;
        case 'importer':
            $where[] = "(imp.company_name LIKE ? OR imp.country1 LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $paramTypes .= "ss";
            break;
        case 'reference':
            $where[] = "c.reference LIKE ?";
            $params[] = $searchTerm;
            $paramTypes .= "s";
            break;
    }
}

// Non-admin users can only see their own certificates
if (!$is_admin) {
    $where[] = "c.clid = ?";
    $params[] = $user_id;
    $paramTypes .= "i";
}

$whereClause = implode(' AND ', $where);

// Count total records
$countSql = "SELECT COUNT(*) as cnt FROM $table c 
             LEFT JOIN companies imp ON c.importer = imp.clid
             WHERE $whereClause";

$stmt = $db->prepare($countSql);
if (!empty($params)) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$countResult = $stmt->get_result()->fetch_assoc();
$totalRecords = $countResult['cnt'];
$stmt->close();

// Calculate pagination
$totalPages = $totalRecords > 0 ? ceil($totalRecords / $rows) : 0;
$page = min($page, max($totalPages, 1));
$start = ($page - 1) * $rows;

// Main query
$sql = "SELECT 
            c.nr,
            c.clid,
            c.offid,
            c.tmplid,
            c.certificate_nr,
            c.issue_date,
            c.weight_gross,
            c.weight_net,
            c.importer,
            c.exporter,
            c.company_name,
            c.quality,
            c.country_of_origin,
            c.transportation_method,
            c.transportation_nr,
            c.hcd_nr,
            c.slaughtering_date,
            c.production_date,
            c.expiry_date,
            c.hcd_process,
            c.printed_on,
            c.arrived_on,
            c.reference,
            c.status,
            c.done,
            c.requested_by,
            c.handled_by,
            c.is_bad,
            c.qr,
            c.url,
            imp.company_name AS importer_name,
            imp.country1 AS importer_country,
            exp.company_name AS exporter_name,
            off.office_name,
            off.office_country,
            tmpl.office_name AS issued_by_name,
            cl_off.office_name AS client_office_name
        FROM $table c
        LEFT JOIN companies imp ON c.importer = imp.clid
        LEFT JOIN companies exp ON c.exporter = exp.clid
        LEFT JOIN offices off ON c.offid = off.offid
        LEFT JOIN offices tmpl ON c.tmplid = tmpl.offid
        LEFT JOIN offices cl_off ON c.offid = cl_off.offid
        WHERE $whereClause
        ORDER BY $sidx $sord
        LIMIT ?, ?";

$params[] = $start;
$params[] = $rows;
$paramTypes .= "ii";

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Build response
$response = [
    'page' => $page,
    'total' => $totalPages,
    'records' => $totalRecords,
    'rows' => []
];

$rowNum = $start;
while ($row = $result->fetch_assoc()) {
    $rowNum++;
    
    // Parse JSON fields
    $requestedBy = json_decode($row['requested_by'], true);
    $handledBy = json_decode($row['handled_by'], true);
    
    // Extract dates from hcd_process
    $sentOn = '';
    $authorizedOn = '';
    if (preg_match('/Sent on[:\s]+(\d{2}[\/\.]\d{2}[\/\.]\d{4})/i', $row['hcd_process'], $matches)) {
        $sentOn = $matches[1];
    }
    if (preg_match('/Authorised[:\s]+(\d{2}[\/\.]\d{2}[\/\.]\d{4})/i', $row['hcd_process'], $matches)) {
        $authorizedOn = $matches[1];
    }

    $response['rows'][] = [
        'nr' => $row['nr'],
        'row_num' => $rowNum,
        'clid' => $row['clid'],
        'offid' => $row['offid'],
        'tmplid' => $row['tmplid'],
        'certificate_nr' => $row['certificate_nr'],
        'issue_date' => $row['issue_date'],
        'weight_gross' => $row['weight_gross'],
        'weight_net' => $row['weight_net'],
        'importer' => $row['importer'],
        'importer_name' => $row['importer_name'],
        'importer_country' => $row['importer_country'],
        'exporter' => $row['exporter'],
        'exporter_name' => $row['exporter_name'],
        'company_name' => $row['company_name'],
        'quality' => $row['quality'],
        'country_of_origin' => $row['country_of_origin'],
        'hcd_process' => $row['hcd_process'],
        'printed_on' => $row['printed_on'],
        'arrived_on' => $row['arrived_on'],
        'sent_on' => $sentOn,
        'authorized_on' => $authorizedOn,
        'reference' => $row['reference'],
        'status' => $row['status'],
        'done' => $row['done'],
        'is_bad' => $row['is_bad'],
        'office_name' => $row['office_name'],
        'office_country' => $row['office_country'],
        'issued_by_name' => $row['issued_by_name'],
        'client_office_name' => $row['client_office_name'],
        'requested_by_name' => isset($requestedBy['name']) ? $requestedBy['name'] : '',
        'handled_by_name' => isset($handledBy['name']) ? $handledBy['name'] : '',
        'qr' => $row['qr'],
        'url' => $row['url']
    ];
}

$stmt->close();

echo json_encode($response);
