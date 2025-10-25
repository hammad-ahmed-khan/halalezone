<?php
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

header('Content-Type: application/json');

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();

    
    $myuser = cuser::singleton();
    $myuser->getUserData();

 
    
    // Check permissions based on user type
    if ($myuser->userdata['isclient'] == '1') {
        // Clients cannot access this endpoint
        echo json_encode(generateErrorResponse('Access denied'));
        exit;
    } elseif ($myuser->userdata['isclient'] == '2') {
        // Auditors can only see their assigned clients
        $auditorClientsAudit = $myuser->userdata['clients_audit'] ?? '[]';
        
        $sql = "SELECT 
                    u.id, 
                    u.name, 
                    u.email, 
                    u.prefix, 
                    u.phone, 
                    u.city, 
                    u.country

                FROM tusers u
                
                WHERE u.isclient = 1 AND u.deleted = 0 
                AND JSON_CONTAINS(:auditor_clients_audit, JSON_QUOTE(CAST(u.id AS CHAR)), '$')
                
                ORDER BY u.name ASC";
        
        $stmt = $dbo->prepare($sql);
        $stmt->bindParam(':auditor_clients_audit', $auditorClientsAudit);
    } else {
        // Admins can see all clients
        $sql = "SELECT 
                    u.id, 
                    u.name, 
                    u.email, 
                    u.prefix, 
                    u.phone, 
                    u.city, 
                    u.country
                FROM tusers u
                     WHERE u.isclient = 1 AND u.deleted = 0 
                
                ORDER BY u.name ASC";
        
        $stmt = $dbo->prepare($sql);
    }
    
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(generateSuccessResponse(array("clients" => $clients)));
    
} catch (Exception $e) {
    echo json_encode(generateErrorResponse("Error loading clients: " . $e->getMessage()));
}
?>