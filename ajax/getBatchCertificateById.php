<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
    $myuser = cuser::singleton();
    $myuser->getUserData();
    
    // Get the batch certificate ID
    $id = getGetParam('id');
    
    if (!$id || !is_numeric($id)) {
        echo json_encode(['status' => 0, 'message' => 'Invalid batch certificate ID']);
        exit;
    }
    
    // Build base query
    $sql = "SELECT 
        b.id,
        b.idhalal_slaughtering,
        b.idclient,
        u.name as client_name,
        b.company_name,
        b.company_address,
        b.date,
        b.country_of_origin,
        b.quality,
        b.net_weight_kg,
        b.gross_weight_kg,
        b.transport_by,
        b.awb_voyage_flight_no,
        b.loading_port,
        b.destination,
        b.exporter_name,
        b.exporter_address,
        b.importer_name,
        b.importer_address,
        b.upload_product_information,
        b.upload_consignment_details,
        b.invoice,
        b.proof_of_payment,
        b.halal_batch_certificate,
        b.status,
        b.created_at,
        b.updated_at,
        b.deleted
    FROM thalal_batch_certificates b
    LEFT JOIN tusers u ON b.idclient = u.id
    WHERE b.id = :id";
    
    // Add role-based access control
    if ($myuser->userdata['isclient'] == "1") {
        // Client can only see their own batch certificates
        $sql .= " AND b.idclient = :user_id";
    } elseif ($myuser->userdata['isclient'] == "2") {
        // Auditor can see assigned clients' batch certificates
        if (isset($_SESSION['halal']['clients_audit']) && !empty($_SESSION['halal']['clients_audit'])) {
            $clientIds = implode(',', array_map('intval', $_SESSION['halal']['clients_audit']));
            $sql .= " AND b.idclient IN ($clientIds)";
        } else {
            // No assigned clients
            echo json_encode(['status' => 0, 'message' => 'Access denied']);
            exit;
        }
    }
    // Admin can see all batch certificates (no additional WHERE clause)
    
    $stmt = $dbo->prepare($sql);
    $params = [':id' => $id];
    
    // Add user ID parameter for clients
    if ($myuser->userdata['isclient'] == "1") {
        $params[':user_id'] = $myuser->userdata['id'];
    }
    
    $stmt->execute($params);
    $batchCertificate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$batchCertificate) {
        echo json_encode(['status' => 0, 'message' => 'Batch certificate not found or access denied']);
        exit;
    }
    
    // Return the batch certificate data
    echo json_encode([
        'status' => 1,
        'data' => $batchCertificate,
        'message' => 'Batch certificate loaded successfully'
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['status' => 0, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
}
?>