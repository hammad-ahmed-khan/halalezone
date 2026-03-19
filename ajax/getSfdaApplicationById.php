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
    
    // Get the application ID
    $id = getGetParam('id');
    
    if (!$id || !is_numeric($id)) {
        echo json_encode(['status' => 0, 'message' => 'Invalid application ID']);
        exit;
    }
    
    // First query - Get SFDA Application
    $sql1 = "SELECT 
        a.id,
        a.idclient,
        u.name as client_name,
        a.application_name,
        a.company_name,
        a.address,
        a.commercial_registration_certificate,
        a.commercial_registration_no,
        a.vat_number,
        a.accreditation_certificates,
        a.accreditation_certificates_other,
        a.number_of_production_lines,
        a.number_of_critical_points,
        a.number_of_full_time_employees,
        a.number_of_shifts,
        a.number_of_shift_employees,
        a.production_area_space_m2,
        a.additional_branches_of_the_company,
        a.upload_product_information,
        a.validity_of_certificate_period,
        a.invoice,
        a.proof_of_payment,
        a.sfda_facility_certificate,
        a.status,
        a.created_at,
        a.created_by,
        a.deleted
    FROM tsfda_applications a
    LEFT JOIN tusers u ON a.idclient = u.id
    WHERE a.id = :id";
    
    // Add role-based access control for applications
    if ($myuser->userdata['isclient'] == "1") {
        // Client can only see their own applications
        $sql1 .= " AND a.idclient = :user_id";
    } elseif ($myuser->userdata['isclient'] == "2") {
        // Auditor can see assigned clients' applications
        if (isset($_SESSION['halal']['clients_audit']) && !empty($_SESSION['halal']['clients_audit'])) {
            $clientIds = implode(',', array_map('intval', $_SESSION['halal']['clients_audit']));
            $sql1 .= " AND a.idclient IN ($clientIds)";
        } else {
            // No assigned clients
            echo json_encode(['status' => 0, 'message' => 'Access denied']);
            exit;
        }
    }
    // Admin can see all applications (no additional WHERE clause)
    
    $stmt1 = $dbo->prepare($sql1);
    $params1 = [':id' => $id];
    
    // Add user ID parameter for clients
    if ($myuser->userdata['isclient'] == "1") {
        $params1[':user_id'] = $myuser->userdata['id'];
    }
    
    $stmt1->execute($params1);
    $application = $stmt1->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        echo json_encode(['status' => 0, 'message' => 'Application not found or access denied']);
        exit;
    }

    // Second query - Get Shipment Certificate
    $sql2 = "SELECT 
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
    WHERE a.idsfdaapp = :id";
    
    // Add role-based access control for shipment certificates
    if ($myuser->userdata['isclient'] == "1") {
        // Client can only see their own shipment certificates
        $sql2 .= " AND a.idclient = :user_id";
    } elseif ($myuser->userdata['isclient'] == "2") {
        // Auditor can see assigned clients' shipment certificates
        if (isset($_SESSION['halal']['clients_audit']) && !empty($_SESSION['halal']['clients_audit'])) {
            $clientIds = implode(',', array_map('intval', $_SESSION['halal']['clients_audit']));
            $sql2 .= " AND a.idclient IN ($clientIds)";
        } else {
            // No assigned clients for shipment certificates
            $shipment = null;
        }
    }
    // Admin can see all shipment certificates (no additional WHERE clause)
    
    // Execute shipment certificate query
    if ($myuser->userdata['isclient'] != "2" || (isset($_SESSION['halal']['clients_audit']) && !empty($_SESSION['halal']['clients_audit']))) {
        $stmt2 = $dbo->prepare($sql2);
        $params2 = [':id' => $id];
        
        // Add user ID parameter for clients
        if ($myuser->userdata['isclient'] == "1") {
            $params2[':user_id'] = $myuser->userdata['id'];
        }
        
        $stmt2->execute($params2);
        $shipment = $stmt2->fetch(PDO::FETCH_ASSOC);
    } else {
        $shipment = null;
    }

    // Prepare response data
    $data = [
        'app' => $application,
        'shipment' => $shipment
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