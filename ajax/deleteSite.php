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

// Validate parameters
if ($stid <= 0 || $clid <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

try {
    $sql = "DELETE FROM companies_production_sites WHERE stid = :stid AND clid = :clid";
    $stmt = $dbo->prepare($sql);
    $stmt->execute([':stid' => $stid, ':clid' => $clid]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Production site deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Site not found or already deleted.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
