<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

header('Content-Type: application/json');

$db = acsessDb::singleton();
$dbo = $db->connect();

$clid = isset($_REQUEST['clid']) ? intval($_REQUEST['clid']) : 0;

if ($clid <= 0) {
    echo json_encode([
        'page' => 1,
        'total' => 0,
        'records' => 0,
        'rows' => []
    ]);
    exit;
}

// Get pagination parameters from jqGrid
$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$limit = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 20;
$sidx = isset($_REQUEST['sidx']) ? $_REQUEST['sidx'] : 'site_name';
$sord = isset($_REQUEST['sord']) ? $_REQUEST['sord'] : 'asc';

// Validate sort column
$allowedColumns = ['stid', 'site_name', 'city', 'country', 'email', 'status', 'inserted_on'];
if (!in_array($sidx, $allowedColumns)) {
    $sidx = 'site_name';
}
$sord = ($sord == 'desc') ? 'DESC' : 'ASC';

// Build WHERE clause with filters
$where = "WHERE clid = :clid";
$params = [':clid' => $clid];

// Filter by site_name (from filter toolbar)
if (isset($_REQUEST['site_name']) && trim($_REQUEST['site_name']) != '') {
    $where .= " AND site_name LIKE :site_name";
    $params[':site_name'] = '%' . trim($_REQUEST['site_name']) . '%';
}

// Filter by city (from filter toolbar)
if (isset($_REQUEST['city']) && trim($_REQUEST['city']) != '') {
    $where .= " AND city LIKE :city";
    $params[':city'] = '%' . trim($_REQUEST['city']) . '%';
}

// Filter by country (from filter toolbar)
if (isset($_REQUEST['country']) && trim($_REQUEST['country']) != '') {
    $where .= " AND country LIKE :country";
    $params[':country'] = '%' . trim($_REQUEST['country']) . '%';
}

// Filter by email (from filter toolbar)
if (isset($_REQUEST['email']) && trim($_REQUEST['email']) != '') {
    $where .= " AND email LIKE :email";
    $params[':email'] = '%' . trim($_REQUEST['email']) . '%';
}

// Filter by status (from filter toolbar dropdown)
if (isset($_REQUEST['status']) && trim($_REQUEST['status']) != '') {
    $where .= " AND status = :status";
    $params[':status'] = trim($_REQUEST['status']);
}

// Count total records with filters
$countSql = "SELECT COUNT(*) as total FROM companies_production_sites $where";
$stmt = $dbo->prepare($countSql);
$stmt->execute($params);
$totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Calculate total pages
$totalPages = ($totalRecords > 0) ? ceil($totalRecords / $limit) : 0;
if ($page > $totalPages) $page = $totalPages;
if ($page < 1) $page = 1;

$start = ($page - 1) * $limit;
if ($start < 0) $start = 0;

// Get records
$sql = "SELECT stid, site_name, city, country, email, status, DATE_FORMAT(inserted_on, '%d-%b-%Y') as inserted_on 
        FROM companies_production_sites 
        $where 
        ORDER BY $sidx $sord 
        LIMIT $start, $limit";

$stmt = $dbo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format response for jqGrid
$response = [
    'page' => $page,
    'total' => $totalPages,
    'records' => $totalRecords,
    'rows' => $rows
];

echo json_encode($response);
