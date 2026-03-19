<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

header('Content-Type: application/json');

$db = acsessDb::singleton();
$dbo = $db->connect();

$stid = isset($_REQUEST['stid']) ? intval($_REQUEST['stid']) : 0;
$clid = isset($_REQUEST['clid']) ? intval($_REQUEST['clid']) : 0;

if ($stid <= 0 || $clid <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

$sql = "SELECT stid, clid, site_name, street, telephone, zipcode, email, city, country, status, inserted_on 
        FROM companies_production_sites 
        WHERE stid = :stid AND clid = :clid";

$stmt = $dbo->prepare($sql);
$stmt->execute([':stid' => $stid, ':clid' => $clid]);
$site = $stmt->fetch(PDO::FETCH_ASSOC);

if ($site) {
    echo json_encode(['success' => true, 'data' => $site]);
} else {
    echo json_encode(['success' => false, 'message' => 'Site not found.']);
}
