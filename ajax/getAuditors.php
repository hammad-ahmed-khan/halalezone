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
    
    // Only admin users can access auditors list
    if ($myuser->userdata['isclient'] == '1' || $myuser->userdata['isclient'] == '2') {
        echo json_encode(generateErrorResponse('Access denied'));
        exit;
    }
    
    $sql = "SELECT 
                id, 
                name, 
                email, 
                phone,
                clients_audit
            FROM tusers 
            WHERE isclient = 2 AND deleted = 0 
            ORDER BY name ASC";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute();
    $auditors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add client count for each auditor
    foreach ($auditors as &$auditor) {
        $clientCount = 0;
        if (!empty($auditor['clients_audit'])) {
            $clientIds = json_decode($auditor['clients_audit'], true);
            if (is_array($clientIds)) {
                $clientCount = count($clientIds);
            }
        }
        $auditor['client_count'] = $clientCount;
        
        // Remove the raw JSON for cleaner response
        unset($auditor['clients_audit']);
    }
    
    echo json_encode(generateSuccessResponse(array("auditors" => $auditors)));
    
} catch (Exception $e) {
    echo json_encode(generateErrorResponse("Error loading auditors: " . $e->getMessage()));
}
?>