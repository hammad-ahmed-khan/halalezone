<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
    $myuser = cuser::singleton();
    $myuser->getUserData();
    
    // Get the halal slaughtering ID
    $id = getGetParam('id');
    
    if (!$id || !is_numeric($id)) {
        echo json_encode(['status' => 0, 'message' => 'Invalid halal slaughtering ID']);
        exit;
    }
    
    // First query - Get Halal Slaughtering record
    $sql1 = "SELECT 
        h.id,
        h.idclient,
        u.name as client_name,
        h.company_name,
        h.contact_person_1,
        h.contact_person_2,
        h.start_datetime,
        h.end_datetime,
        h.type_of_animal,
        h.butcher_1,
        h.butcher_2,
        h.butcher_3,
        h.supervisor_1,
        h.supervisor_2,
        h.supervisor_3,
        h.halal_slaughtering_documents,
        h.method_of_stunning,
        h.halal_slaughtering_data,
        h.upload_live_animals_documents,
        h.upload_pictures_after_cleaning,
        h.upload_halal_slaughtering_video,
        h.upload_additional_pictures,
        h.upload_halal_stock,
        h.invoice_travel_expenses,
        h.proof_of_payment,
        h.status,
        h.created_at,
        h.created_by,
        h.deleted
    FROM thalal_slaughtering h
    LEFT JOIN tusers u ON h.idclient = u.id
    WHERE h.id = :id";
    
    // Add role-based access control for halal slaughtering records
    if ($myuser->userdata['isclient'] == "1") {
        // Client can only see their own records
        $sql1 .= " AND h.idclient = :user_id";
    } elseif ($myuser->userdata['isclient'] == "2") {
        // Auditor can see assigned clients' records
        if (isset($_SESSION['halal']['clients_audit']) && !empty($_SESSION['halal']['clients_audit'])) {
            $clientIds = implode(',', array_map('intval', $_SESSION['halal']['clients_audit']));
            $sql1 .= " AND h.idclient IN ($clientIds)";
        } else {
            // No assigned clients
            echo json_encode(['status' => 0, 'message' => 'Access denied']);
            exit;
        }
    }
    // Admin can see all records (no additional WHERE clause)
    
    $stmt1 = $dbo->prepare($sql1);
    $params1 = [':id' => $id];
    
    // Add user ID parameter for clients
    if ($myuser->userdata['isclient'] == "1") {
        $params1[':user_id'] = $myuser->userdata['id'];
    }
    
    $stmt1->execute($params1);
    $slaughtering = $stmt1->fetch(PDO::FETCH_ASSOC);
    
    if (!$slaughtering) {
        echo json_encode(['status' => 0, 'message' => 'Halal slaughtering record not found or access denied']);
        exit;
    }

    // Second query - Get Batch Certificate (if exists)
    $sql2 = "SELECT 
        b.id,
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
    WHERE b.idhalal_slaughtering = :id";
    
    // Add role-based access control for batch certificates
    if ($myuser->userdata['isclient'] == "1") {
        // Client can only see their own batch certificates
        $sql2 .= " AND b.idclient = :user_id";
    } elseif ($myuser->userdata['isclient'] == "2") {
        // Auditor can see assigned clients' batch certificates
        if (isset($_SESSION['halal']['clients_audit']) && !empty($_SESSION['halal']['clients_audit'])) {
            $clientIds = implode(',', array_map('intval', $_SESSION['halal']['clients_audit']));
            $sql2 .= " AND b.idclient IN ($clientIds)";
        } else {
            // No assigned clients for batch certificates
            $batchCertificate = null;
        }
    }
    // Admin can see all batch certificates (no additional WHERE clause)
    
    // Execute batch certificate query
    if ($myuser->userdata['isclient'] != "2" || (isset($_SESSION['halal']['clients_audit']) && !empty($_SESSION['halal']['clients_audit']))) {
        $stmt2 = $dbo->prepare($sql2);
        $params2 = [':id' => $id];
        
        // Add user ID parameter for clients
        if ($myuser->userdata['isclient'] == "1") {
            $params2[':user_id'] = $myuser->userdata['id'];
        }
        
        $stmt2->execute($params2);
        $batchCertificate = $stmt2->fetch(PDO::FETCH_ASSOC);
    } else {
        $batchCertificate = null;
    }

    // Prepare response data
    $data = [
        'slaughtering' => $slaughtering,
        'batch_certificate' => $batchCertificate
    ];
    
    // Return the combined data
    echo json_encode([
        'status' => 1,
        'data' => $data,
        'message' => 'Data loaded successfully'
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['status' => 0, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
}
?>