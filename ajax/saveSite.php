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
$site_name = isset($_REQUEST['site_name']) ? trim($_REQUEST['site_name']) : '';
$street = isset($_REQUEST['street']) ? trim($_REQUEST['street']) : '';
$telephone = isset($_REQUEST['telephone']) ? trim($_REQUEST['telephone']) : '';
$zipcode = isset($_REQUEST['zipcode']) ? trim($_REQUEST['zipcode']) : '';
$email = isset($_REQUEST['email']) ? trim($_REQUEST['email']) : '';
$city = isset($_REQUEST['city']) ? trim($_REQUEST['city']) : '';
$country = isset($_REQUEST['country']) ? trim($_REQUEST['country']) : '';
$status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : 'active';

// Validate client
if ($clid <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a client.']);
    exit;
}

// Validate site name
if (empty($site_name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required.']);
    exit;
}

// Validate email format if provided
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

// Validate status
if (!in_array($status, ['active', 'inactive'])) {
    $status = 'active';
}

try {
    if ($stid > 0) {
        // Update existing
        $sql = "UPDATE companies_production_sites 
                SET site_name = :site_name, 
                    street = :street,
                    telephone = :telephone,
                    zipcode = :zipcode,
                    email = :email,
                    city = :city,
                    country = :country,
                    status = :status 
                WHERE stid = :stid AND clid = :clid";
        
        $stmt = $dbo->prepare($sql);
        $stmt->execute([
            ':site_name' => $site_name,
            ':street' => $street,
            ':telephone' => $telephone,
            ':zipcode' => $zipcode,
            ':email' => $email,
            ':city' => $city,
            ':country' => $country,
            ':status' => $status,
            ':stid' => $stid,
            ':clid' => $clid
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Production site updated successfully.']);
    } else {
        // Insert new
        $sql = "INSERT INTO companies_production_sites (clid, site_name, street, telephone, zipcode, email, city, country, status) 
                VALUES (:clid, :site_name, :street, :telephone, :zipcode, :email, :city, :country, :status)";
        
        $stmt = $dbo->prepare($sql);
        $stmt->execute([
            ':clid' => $clid,
            ':site_name' => $site_name,
            ':street' => $street,
            ':telephone' => $telephone,
            ':zipcode' => $zipcode,
            ':email' => $email,
            ':city' => $city,
            ':country' => $country,
            ':status' => $status
        ]);
        
        $newId = $dbo->lastInsertId();
        
        echo json_encode(['success' => true, 'message' => 'Production site added successfully.', 'stid' => $newId]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
