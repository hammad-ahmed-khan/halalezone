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
    
    // Get parameters
    $ingredientIds = $_POST['ingredient_ids'] ?? [];
    $facilityIds = $_POST['facility_ids'] ?? []; // Support multiple facilities
    $action = $_POST['action'] ?? 'assign'; // 'assign', 'unassign', 'replace'
    $assignToAll = $_POST['assign_to_all'] ?? false; // Bulk assign to all facilities
    
    // Validate input
    if (empty($ingredientIds) || !is_array($ingredientIds)) {
        echo json_encode(generateErrorResponse('No ingredients selected'));
        exit;
    }
    
    if (empty($facilityIds) || !is_array($facilityIds)) {
        echo json_encode(generateErrorResponse('No facilities selected'));
        exit;
    }
    
    // Validate facility IDs
    foreach ($facilityIds as $facilityId) {
        if (!is_numeric($facilityId)) {
            echo json_encode(generateErrorResponse('Invalid facility ID: ' . $facilityId));
            exit;
        }
    }
    
    // Check permissions for all facilities
    $accessibleFacilities = [];
    foreach ($facilityIds as $facilityId) {
        $hasAccess = false;
        
        if ($myuser->userdata['isclient'] == '1') {
            // Client user - can only assign to their own facilities
            $sql = "SELECT id, parent_id, name, prefix FROM tusers WHERE id = :facility_id AND isclient = 1 AND deleted = 0";
            $stmt = $dbo->prepare($sql);
            $stmt->bindParam(':facility_id', $facilityId, PDO::PARAM_INT);
            $stmt->execute();
            $facility = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($facility) {
                $parentId = $facility['parent_id'] ?: $facility['id'];
                $userParentId = $myuser->userdata['parent_id'] ?: $myuser->userdata['id'];
                if ($parentId == $userParentId || $facilityId == $myuser->userdata['id']) {
                    $hasAccess = true;
                    $accessibleFacilities[] = $facility;
                }
            }
        } elseif ($myuser->userdata['isclient'] == '2') {
            // Auditor - check if they have access to this facility
            $clients_audit = $myuser->userdata['clients_audit'];
            if ($clients_audit != "") {
                $ids = json_decode($clients_audit);
                
                $sql = "SELECT id, parent_id, name, prefix FROM tusers WHERE id = :facility_id AND isclient = 1 AND deleted = 0";
                $stmt = $dbo->prepare($sql);
                $stmt->bindParam(':facility_id', $facilityId, PDO::PARAM_INT);
                $stmt->execute();
                $facility = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($facility) {
                    $parentId = $facility['parent_id'] ?: $facility['id'];
                    if (in_array($parentId, $ids)) {
                        $hasAccess = true;
                        $accessibleFacilities[] = $facility;
                    }
                }
            }
        } else {
            // Admin - has access to all
            $sql = "SELECT id, parent_id, name, prefix FROM tusers WHERE id = :facility_id AND isclient = 1 AND deleted = 0";
            $stmt = $dbo->prepare($sql);
            $stmt->bindParam(':facility_id', $facilityId, PDO::PARAM_INT);
            $stmt->execute();
            $facility = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($facility) {
                $hasAccess = true;
                $accessibleFacilities[] = $facility;
            }
        }
        
        if (!$hasAccess) {
            echo json_encode(generateErrorResponse('Access denied to facility ID: ' . $facilityId));
            exit;
        }
    }
    
    // Validate ingredients access (same as before)
    $placeholders = str_repeat('?,', count($ingredientIds) - 1) . '?';
    $sql = "SELECT id, idclient, name, rmcode FROM tingredients WHERE id IN ($placeholders) AND deleted = 0";
    $stmt = $dbo->prepare($sql);
    $stmt->execute($ingredientIds);
    $ingredients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($ingredients) !== count($ingredientIds)) {
        echo json_encode(generateErrorResponse('Some ingredients not found or access denied'));
        exit;
    }
    
    // Validate source ingredient access (same permission logic as before)
    foreach ($ingredients as $ingredient) {
        $sourceClientId = $ingredient['idclient'];
        $hasSourceAccess = false;
        
        if ($myuser->userdata['isclient'] == '1') {
            $sql = "SELECT id, parent_id FROM tusers WHERE id = :client_id";
            $stmt = $dbo->prepare($sql);
            $stmt->bindParam(':client_id', $sourceClientId, PDO::PARAM_INT);
            $stmt->execute();
            $sourceClient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($sourceClient) {
                $sourceParentId = $sourceClient['parent_id'] ?: $sourceClient['id'];
                $userParentId = $myuser->userdata['parent_id'] ?: $myuser->userdata['id'];
                if ($sourceParentId == $userParentId || $sourceClientId == $myuser->userdata['id']) {
                    $hasSourceAccess = true;
                }
            }
        } elseif ($myuser->userdata['isclient'] == '2') {
            $clients_audit = $myuser->userdata['clients_audit'];
            if ($clients_audit != "") {
                $ids = json_decode($clients_audit);
                if (in_array($sourceClientId, $ids)) {
                    $hasSourceAccess = true;
                }
            }
        } else {
            $hasSourceAccess = true;
        }
        
        if (!$hasSourceAccess) {
            echo json_encode(generateErrorResponse('Access denied to ingredient: ' . $ingredient['name']));
            exit;
        }
    }
    
    // Begin transaction
    $dbo->beginTransaction();
    
    try {
        $successCount = 0;
        $results = [];
        $userId = $myuser->userdata['id'];
        
        foreach ($ingredients as $ingredient) {
            $ingredientId = $ingredient['id'];
            $facilityAssignments = [];
            
            // Handle different actions
            switch ($action) {
                case 'assign':
                    // Add facilities to ingredient (keep existing assignments)
                    foreach ($accessibleFacilities as $facility) {
                        $facilityId = $facility['id'];
                        
                        // Check if assignment already exists
                        $sql = "SELECT id FROM tingredient_facilities 
                               WHERE ingredient_id = :ingredient_id AND facility_id = :facility_id";
                        $stmt = $dbo->prepare($sql);
                        $stmt->bindParam(':ingredient_id', $ingredientId, PDO::PARAM_INT);
                        $stmt->bindParam(':facility_id', $facilityId, PDO::PARAM_INT);
                        $stmt->execute();
                        
                        if (!$stmt->fetch()) {
                            // Insert new assignment
                            $sql = "INSERT INTO tingredient_facilities (ingredient_id, facility_id, assigned_by, assigned_at, status) 
                                   VALUES (:ingredient_id, :facility_id, :assigned_by, NOW(), 1)";
                            $stmt = $dbo->prepare($sql);
                            $stmt->bindParam(':ingredient_id', $ingredientId, PDO::PARAM_INT);
                            $stmt->bindParam(':facility_id', $facilityId, PDO::PARAM_INT);
                            $stmt->bindParam(':assigned_by', $userId, PDO::PARAM_INT);
                            
                            if ($stmt->execute()) {
                                $facilityAssignments[] = $facility['name'] . ' (' . $facility['prefix'] . $facility['id'] . ')';
                                
                                // Log history
                                $sql = "INSERT INTO tingredient_facility_history (ingredient_id, facility_id, action, performed_by, performed_at) 
                                       VALUES (:ingredient_id, :facility_id, 'assign', :performed_by, NOW())";
                                $stmt = $dbo->prepare($sql);
                                $stmt->bindParam(':ingredient_id', $ingredientId, PDO::PARAM_INT);
                                $stmt->bindParam(':facility_id', $facilityId, PDO::PARAM_INT);
                                $stmt->bindParam(':performed_by', $userId, PDO::PARAM_INT);
                                $stmt->execute();
                            }
                        } else {
                            // Reactivate if it was deactivated
                            $sql = "UPDATE tingredient_facilities 
                                   SET status = 1, assigned_by = :assigned_by, assigned_at = NOW() 
                                   WHERE ingredient_id = :ingredient_id AND facility_id = :facility_id";
                            $stmt = $dbo->prepare($sql);
                            $stmt->bindParam(':ingredient_id', $ingredientId, PDO::PARAM_INT);
                            $stmt->bindParam(':facility_id', $facilityId, PDO::PARAM_INT);
                            $stmt->bindParam(':assigned_by', $userId, PDO::PARAM_INT);
                            $stmt->execute();
                            
                            $facilityAssignments[] = $facility['name'] . ' (' . $facility['prefix'] . $facility['id'] . ')';
                        }
                    }
                    break;
                    
                case 'unassign':
                    // Remove facilities from ingredient
                    $facilityIdList = implode(',', array_map('intval', $facilityIds));
                    $sql = "UPDATE tingredient_facilities 
                           SET status = 0 
                           WHERE ingredient_id = :ingredient_id AND facility_id IN ($facilityIdList)";
                    $stmt = $dbo->prepare($sql);
                    $stmt->bindParam(':ingredient_id', $ingredientId, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        foreach ($accessibleFacilities as $facility) {
                            $facilityAssignments[] = $facility['name'] . ' (' . $facility['prefix'] . $facility['id'] . ')';
                            
                            // Log history
                            $sql = "INSERT INTO tingredient_facility_history (ingredient_id, facility_id, action, performed_by, performed_at) 
                                   VALUES (:ingredient_id, :facility_id, 'unassign', :performed_by, NOW())";
                            $stmt = $dbo->prepare($sql);
                            $stmt->bindParam(':ingredient_id', $ingredientId, PDO::PARAM_INT);
                            $stmt->bindParam(':facility_id', $facility['id'], PDO::PARAM_INT);
                            $stmt->bindParam(':performed_by', $userId, PDO::PARAM_INT);
                            $stmt->execute();
                        }
                    }
                    break;
                    
                case 'replace':
                    // Replace all existing assignments with new ones
                    // First deactivate all existing assignments
                    $sql = "UPDATE tingredient_facilities SET status = 0 WHERE ingredient_id = :ingredient_id";
                    $stmt = $dbo->prepare($sql);
                    $stmt->bindParam(':ingredient_id', $ingredientId, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    // Then assign to new facilities (same logic as assign)
                    foreach ($accessibleFacilities as $facility) {
                        $facilityId = $facility['id'];
                        
                        $sql = "INSERT INTO tingredient_facilities (ingredient_id, facility_id, assigned_by, assigned_at, status) 
                               VALUES (:ingredient_id, :facility_id, :assigned_by, NOW(), 1)
                               ON DUPLICATE KEY UPDATE 
                               status = 1, assigned_by = :assigned_by, assigned_at = NOW()";
                        $stmt = $dbo->prepare($sql);
                        $stmt->bindParam(':ingredient_id', $ingredientId, PDO::PARAM_INT);
                        $stmt->bindParam(':facility_id', $facilityId, PDO::PARAM_INT);
                        $stmt->bindParam(':assigned_by', $userId, PDO::PARAM_INT);
                        
                        if ($stmt->execute()) {
                            $facilityAssignments[] = $facility['name'] . ' (' . $facility['prefix'] . $facility['id'] . ')';
                        }
                    }
                    break;
            }
            
            if (!empty($facilityAssignments)) {
                $successCount++;
                $results[] = [
                    'id' => $ingredientId,
                    'name' => $ingredient['name'],
                    'code' => $ingredient['rmcode'],
                    'action' => $action,
                    'facilities' => $facilityAssignments,
                    'facility_count' => count($facilityAssignments)
                ];
            }
        }
        
        // Update statistics for all affected facilities
        foreach ($accessibleFacilities as $facility) {
            updateIngredientStats($facility['id']);
        }
        
        // Commit transaction
        $dbo->commit();
        
        $actionText = [
            'assign' => 'assigned to',
            'unassign' => 'unassigned from', 
            'replace' => 'reassigned to'
        ];
        
        $message = $successCount . ' ingredient(s) successfully ' . $actionText[$action] . ' facilities';
        
        echo json_encode(generateSuccessResponse([
            'message' => $message,
            'count' => $successCount,
            'action' => $action,
            'results' => $results
        ]));
        
    } catch (Exception $e) {
        $dbo->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    echo json_encode(generateErrorResponse("Error: " . $e->getMessage()));
}
?>