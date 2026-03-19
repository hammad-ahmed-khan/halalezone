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
    
    $parentClientId = getGetParam('parent_client_id');
    
    if (!is_numeric($parentClientId)) {
        echo json_encode(generateErrorResponse('Invalid parent client ID'));
        exit;
    }
    
    // Check if user has permission to access this parent client
    $hasAccess = false;
    
    if ($myuser->userdata['isclient'] == '1') {
        // Client user - can only access their own facilities
        if ($parentClientId == $myuser->userdata['id'] || $parentClientId == $myuser->userdata['parent_id']) {
            $hasAccess = true;
        }
    } elseif ($myuser->userdata['isclient'] == '2') {
        // Auditor - check if they have access to this client
        $clients_audit = $myuser->userdata['clients_audit'];
        if ($clients_audit != "") {
            $ids = json_decode($clients_audit);
            if (in_array($parentClientId, $ids)) {
                $hasAccess = true;
            }
        }
    } else {
        // Admin - has access to all
        $hasAccess = true;
    }
    
    if (!$hasAccess) {
        echo json_encode(generateErrorResponse('Access denied'));
        exit;
    }
    
    // Get facilities (child clients) for the specified parent
    $sql = "SELECT id, name, prefix, parent_id 
            FROM tusers 
            WHERE parent_id = :parent_id 
            AND isclient = 1 
            AND deleted = 0 
            ORDER BY name";
    
    $stmt = $dbo->prepare($sql);
    $stmt->bindParam(':parent_id', $parentClientId, PDO::PARAM_INT);
    $stmt->execute();
    
    $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(generateSuccessResponse(array("facilities" => $facilities)));
    
} catch (Exception $e) {
    echo json_encode(generateErrorResponse("Error loading facilities: " . $e->getMessage()));
}
?>