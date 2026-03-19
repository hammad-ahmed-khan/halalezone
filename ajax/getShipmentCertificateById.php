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
    
    // Get the shipment certificate ID
    $id = getGetParam('id');
    
    if (!$id || !is_numeric($id)) {
        echo json_encode(['status' => 0, 'message' => 'Invalid shipment certificate ID']);
        exit;
    }
    
    // Build base query
    $sql = "SELECT 
        a.id,
        a.idclient,
        u.name as client_name,
        a.company_name,
        a.contact_person,
        a.email,
        a.iidc_certificate_no,
        a.product_name,
        a.article_number,
        a.halal_digital_hcp_no,
        a.commercial_registration_no_importeur,
        a.shipping_method,
        a.shipping_port,
        a.port_of_entry,
        a.quantity,
        a.total_actual_weight_brutto,
        a.production_date,
        a.expiry_date,
        a.additional_documents,
        a.invoice,
        a.proof_of_payment,
        a.sfda_shipment_certificate,
        a.status,
        a.created_at,
        a.updated_at,
        a.deleted
    FROM tsfda_shipment_certificates a
    LEFT JOIN tusers u ON a.idclient = u.id
    WHERE a.id = :id";
    
    // Add role-based access control
    if ($myuser->userdata['isclient'] == "1") {
        // Client can only see their own shipment certificates
        $sql .= " AND a.idclient = :user_id";
    } elseif ($myuser->userdata['isclient'] == "2") {
        // Auditor can see assigned clients' shipment certificates
        if (isset($_SESSION['halal']['clients_audit']) && !empty($_SESSION['halal']['clients_audit'])) {
            $clientIds = implode(',', array_map('intval', $_SESSION['halal']['clients_audit']));
            $sql .= " AND a.idclient IN ($clientIds)";
        } else {
            // No assigned clients
            echo json_encode(['status' => 0, 'message' => 'Access denied']);
            exit;
        }
    }
    // Admin can see all shipment certificates (no additional WHERE clause)
    
    $stmt = $dbo->prepare($sql);
    $params = [':id' => $id];
    
    // Add user ID parameter for clients
    if ($myuser->userdata['isclient'] == "1") {
        $params[':user_id'] = $myuser->userdata['id'];
    }
    
    $stmt->execute($params);
    $shipment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$shipment) {
        echo json_encode(['status' => 0, 'message' => 'Shipment certificate not found or access denied']);
        exit;
    }
    
    // Return the shipment certificate data
    echo json_encode([
        'status' => 1,
        'data' => $shipment,
        'message' => 'Shipment certificate loaded successfully'
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['status' => 0, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
}
?>