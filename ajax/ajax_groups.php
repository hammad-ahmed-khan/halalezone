<?php
/**
 * Product Groups AJAX Handler
 * Handles all group-related operations: create, read, update, delete
 */

@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type
header('Content-Type: application/json');

try {
    // Database connection
    $db = acsessDb::singleton();
    $dbo = $db->connect();
    
    // User authentication
    $myuser = cuser::singleton();
    $myuser->getUserData();
    
    
    // Get client ID
    $idclient = $myuser->userdata['id'];
    if ($myuser->userdata['isclient'] == 0 && isset($_POST['idclient'])) {
        $idclient = $_POST['idclient'];
    }
    
    // Get action
    $action = $_POST['action'] ?? '';
    
    // Route to appropriate function
    switch ($action) {
        case 'getGroups':
            getGroups($dbo, $idclient);
            break;
            
        case 'saveGroup':
            saveGroup($dbo, $idclient, $_POST);
            break;
            
        case 'deleteGroup':
            deleteGroup($dbo, $idclient, $_POST);
            break;

case 'saveProductGroups':
    try {
        $product_id = (int)$_POST['product_id'];
        $group_ids = isset($_POST['group_ids']) ? $_POST['group_ids'] : [];
        $idclient = (int)$_POST['idclient'];
        
        // Validate input
        if (!$product_id) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }
        
        // Start transaction
        $dbo->beginTransaction();
        
        // Delete existing assignments for this product
        $sql = "DELETE FROM tproduct_group_assignments WHERE product_id = ?";
        $stmt = $dbo->prepare($sql);
        $stmt->execute([$product_id]);
        
        // Insert new assignments
        if (!empty($group_ids) && is_array($group_ids)) {
            $sql = "INSERT INTO tproduct_group_assignments (product_id, group_id) VALUES (?, ?)";
            $stmt = $dbo->prepare($sql);
            
            foreach ($group_ids as $group_id) {
                if (is_numeric($group_id) && $group_id > 0) {
                    $stmt->execute([$product_id, (int)$group_id]);
                }
            }
        }
        
        $dbo->commit();
        echo json_encode(['success' => true, 'message' => 'Product groups updated successfully']);
        
    } catch (Exception $e) {
        $dbo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error updating product groups: ' . $e->getMessage()]);
    }
    break;

case 'getProductGroups':
    try {
        $product_id = (int)$_POST['product_id'];
        
        $sql = "SELECT group_id FROM tproduct_group_assignments WHERE product_id = ?";
        $stmt = $dbo->prepare($sql);
        $stmt->execute([$product_id]);
        $group_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(['success' => true, 'data' => $group_ids]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error getting product groups: ' . $e->getMessage()]);
    }
    break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action specified']);
    }
    
} catch (Exception $e) {
    error_log("Groups AJAX Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}

/**
 * Get all groups for a client
 */
function getGroups($dbo, $idclient) {
    try {
        $sql = "SELECT id, name, description, created_at 
                FROM tproduct_groups 
                WHERE idclient = ? AND deleted = 0 
                ORDER BY name ASC";
        
        $stmt = $dbo->prepare($sql);
        $stmt->execute([$idclient]);
        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $groups]);
        
    } catch (PDOException $e) {
        error_log("Get Groups Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to load groups']);
    }
}

/**
 * Create or update a group
 */
function saveGroup($dbo, $idclient, $data) {
    try {
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $id = !empty($data['id']) ? (int)$data['id'] : 0;
        
        // Validation
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Group name is required']);
            return;
        }
        
        if (strlen($name) > 100) {
            echo json_encode(['success' => false, 'message' => 'Group name is too long (max 100 characters)']);
            return;
        }
        
        // Check for duplicate name
        $checkSql = "SELECT id FROM tproduct_groups 
                     WHERE name = ? AND idclient = ? AND deleted = 0";
        $checkParams = [$name, $idclient];
        
        if ($id > 0) {
            $checkSql .= " AND id != ?";
            $checkParams[] = $id;
        }
        
        $stmt = $dbo->prepare($checkSql);
        $stmt->execute($checkParams);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'A group with this name already exists']);
            return;
        }
        
        if ($id > 0) {
            // Update existing group
            $sql = "UPDATE tproduct_groups 
                    SET name = ?, description = ?, updated_at = NOW() 
                    WHERE id = ? AND idclient = ?";
            $params = [$name, $description, $id, $idclient];
            $message = 'Group updated successfully';
        } else {
            // Create new group
            $sql = "INSERT INTO tproduct_groups (idclient, name, description) 
                    VALUES (?, ?, ?)";
            $params = [$idclient, $name, $description];
            $message = 'Group created successfully';
        }
        
        $stmt = $dbo->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save group']);
        }
        
    } catch (PDOException $e) {
        error_log("Save Group Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
}

/**
 * Delete a group (soft delete)
 */
function deleteGroup($dbo, $idclient, $data) {
    try {
        $id = !empty($data['id']) ? (int)$data['id'] : 0;
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
            return;
        }
        
        // Start transaction
        $dbo->beginTransaction();
        
        try {
            // Remove group association from products (set to NULL)
            $updateProductsSql = "UPDATE tproducts 
                                  SET group_id = NULL 
                                  WHERE group_id = ? AND idclient = ?";
            $stmt = $dbo->prepare($updateProductsSql);
            $stmt->execute([$id, $idclient]);
            
            // Soft delete the group
            $deleteGroupSql = "UPDATE tproduct_groups 
                               SET deleted = 1, deleted_at = NOW() 
                               WHERE id = ? AND idclient = ?";
            $stmt = $dbo->prepare($deleteGroupSql);
            $result = $stmt->execute([$id, $idclient]);
            
            if ($result && $stmt->rowCount() > 0) {
                $dbo->commit();
                echo json_encode(['success' => true, 'message' => 'Group deleted successfully']);
            } else {
                $dbo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Group not found or already deleted']);
            }
            
        } catch (Exception $e) {
            $dbo->rollBack();
            throw $e;
        }
        
    } catch (PDOException $e) {
        error_log("Delete Group Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to delete group']);
    }
}
?>
