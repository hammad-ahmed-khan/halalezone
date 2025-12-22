<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

//error_reporting(E_ALL);
//ini_set('display_errors', true);

try {
    $db = acsessDb :: singleton();
    $dbo =  $db->connect(); // Create database connection object
    $myuser = cuser::singleton();
    $myuser->getUserData();

    // Helper function for JSON response
    function json_response($success, $message = '', $data = null, $http_code = 200, $extra = []) {
        // Set appropriate HTTP status code
        http_response_code($http_code);
        
        // Set JSON content type header
        header('Content-Type: application/json; charset=utf-8');
        
        // Prevent caching
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        
        // Build response array
        $response = [
            'success' => (bool) $success,
            'message' => (string) $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'status_code' => $http_code
        ];
        
        // Add data if provided
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        // Add any extra fields
        if (!empty($extra) && is_array($extra)) {
            $response = array_merge($response, $extra);
        }
        
        // Output JSON and exit
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // Sanitize input
    function sanitize_input($str){
        return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
    }

    // Validate required fields
    function validate_required($fields, $data) {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        return $errors;
    }

    // Get all categories
    function get_categories($dbo) {
        $stmt = $dbo->query("SELECT * FROM faq_categories WHERE is_active = 1 ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all tags
    function get_tags($dbo) {
        $stmt = $dbo->query("SELECT * FROM faq_tags ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all FAQs with category and tags
    function get_all_faqs($dbo) {
        $sql = "SELECT 
                    f.id,
                    f.question,
                    f.answer,
                    f.priority,
                    f.training_weight,
                    f.is_active,
                    f.created_at,
                    c.name as category_name,
                    GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ', ') as tags
                FROM faqs f
                LEFT JOIN faq_categories c ON f.category_id = c.id
                LEFT JOIN faq_tag_map ft ON f.id = ft.faq_id
                LEFT JOIN faq_tags t ON ft.tag_id = t.id
                GROUP BY f.id, f.question, f.answer, f.priority, f.training_weight, f.is_active, f.created_at, c.name
                ORDER BY f.priority DESC, f.created_at DESC";
        
        $stmt = $dbo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single FAQ with details
    function get_faq_by_id($dbo, $id) {
        $sql = "SELECT f.*, c.name as category_name 
                FROM faqs f 
                LEFT JOIN faq_categories c ON f.category_id = c.id 
                WHERE f.id = ?";
        $stmt = $dbo->prepare($sql);
        $stmt->execute([$id]);
        $faq = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($faq) {
            // Get tags for this FAQ
            $sql = "SELECT t.id, t.name FROM faq_tags t 
                    JOIN faq_tag_map ft ON t.id = ft.tag_id 
                    WHERE ft.faq_id = ?";
            $stmt = $dbo->prepare($sql);
            $stmt->execute([$id]);
            $faq['selected_tags'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get question variants
            $sql = "SELECT * FROM question_variants WHERE faq_id = ? ORDER BY id";
            $stmt = $dbo->prepare($sql);
            $stmt->execute([$id]);
            $faq['variants'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $faq;
    }

    // Create new FAQ
    function create_faq($dbo, $data) {
        try {
            $dbo->beginTransaction();
            
            // Insert FAQ
            $sql = "INSERT INTO faqs (question, answer, category_id, priority, training_weight, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $dbo->prepare($sql);
            $stmt->execute([
                $data['question'],
                $data['answer'],
                $data['category_id'] ?: null,
                $data['priority'] ?: 1,
                $data['training_weight'] ?: 1.00,
                1 // Default user ID
            ]);
            
            $faq_id = $dbo->lastInsertId();
            
            // Insert tags
            if (!empty($data['tags'])) {
                $sql = "INSERT INTO faq_tag_map (faq_id, tag_id) VALUES (?, ?)";
                $stmt = $dbo->prepare($sql);
                foreach ($data['tags'] as $tag_id) {
                    $stmt->execute([$faq_id, $tag_id]);
                }
            }
            
            // Insert question variants
            if (!empty($data['variants'])) {
                $sql = "INSERT INTO question_variants (faq_id, variant_question, confidence_score) VALUES (?, ?, ?)";
                $stmt = $dbo->prepare($sql);
                foreach ($data['variants'] as $variant) {
                    if (!empty($variant['question'])) {
                        $stmt->execute([
                            $faq_id, 
                            $variant['question'], 
                            $variant['confidence'] ?: 1.00
                        ]);
                    }
                }
            }
            
            $dbo->commit();
            return $faq_id;
            
        } catch (Exception $e) {
            $dbo->rollBack();
            throw $e;
        }
    }

    // Update FAQ
    function update_faq($dbo, $id, $data) {
        try {
            $dbo->beginTransaction();
            
            // Update FAQ
            $sql = "UPDATE faqs SET question = ?, answer = ?, category_id = ?, priority = ?, training_weight = ? WHERE id = ?";
            $stmt = $dbo->prepare($sql);
            $stmt->execute([
                $data['question'],
                $data['answer'],
                $data['category_id'] ?: null,
                $data['priority'] ?: 1,
                $data['training_weight'] ?: 1.00,
                $id
            ]);
            
            // Delete existing tags
            $stmt = $dbo->prepare("DELETE FROM faq_tag_map WHERE faq_id = ?");
            $stmt->execute([$id]);
            
            // Insert new tags
            if (!empty($data['tags'])) {
                $sql = "INSERT INTO faq_tag_map (faq_id, tag_id) VALUES (?, ?)";
                $stmt = $dbo->prepare($sql);
                foreach ($data['tags'] as $tag_id) {
                    $stmt->execute([$id, $tag_id]);
                }
            }
            
            // Delete existing variants
            $stmt = $dbo->prepare("DELETE FROM question_variants WHERE faq_id = ?");
            $stmt->execute([$id]);
            
            // Insert new variants
            if (!empty($data['variants'])) {
                $sql = "INSERT INTO question_variants (faq_id, variant_question, confidence_score) VALUES (?, ?, ?)";
                $stmt = $dbo->prepare($sql);
                foreach ($data['variants'] as $variant) {
                    if (!empty($variant['question'])) {
                        $stmt->execute([
                            $id, 
                            $variant['question'], 
                            $variant['confidence'] ?: 1.00
                        ]);
                    }
                }
            }
            
            $dbo->commit();
            return true;
            
        } catch (Exception $e) {
            $dbo->rollBack();
            throw $e;
        }
    }

    // Delete FAQ
    function delete_faq($dbo, $id) {
        try {
            $dbo->beginTransaction();
            
            // Delete related records first (due to foreign keys)
            $stmt = $dbo->prepare("DELETE FROM faq_tag_map WHERE faq_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $dbo->prepare("DELETE FROM question_variants WHERE faq_id = ?");
            $stmt->execute([$id]);
            
            // Check if faq_analytics table exists
            $stmt = $dbo->prepare("SHOW TABLES LIKE 'faq_analytics'");
            $stmt->execute();
            if ($stmt->fetch()) {
                $stmt = $dbo->prepare("DELETE FROM faq_analytics WHERE faq_id = ?");
                $stmt->execute([$id]);
            }
            
            // Delete FAQ
            $stmt = $dbo->prepare("DELETE FROM faqs WHERE id = ?");
            $stmt->execute([$id]);
            
            $dbo->commit();
            return true;
            
        } catch (Exception $e) {
            $dbo->rollBack();
            throw $e;
        }
    }

    // Toggle FAQ status
    function toggle_faq_status($dbo, $id) {
        $sql = "UPDATE faqs SET is_active = NOT is_active WHERE id = ?";
        $stmt = $dbo->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Create new category
    function create_category($dbo, $name, $description = '') {
        $sql = "INSERT INTO faq_categories (name, description, is_active) VALUES (?, ?, 1)";
        $stmt = $dbo->prepare($sql);
        return $stmt->execute([$name, $description]);
    }

    // Update category
    function update_category($dbo, $id, $name, $description = '') {
        $sql = "UPDATE faq_categories SET name = ?, description = ? WHERE id = ?";
        $stmt = $dbo->prepare($sql);
        return $stmt->execute([$name, $description, $id]);
    }

    // Delete category
    function delete_category($dbo, $id) {
        try {
            $dbo->beginTransaction();
            
            // Check if category is being used by any FAQs
            $stmt = $dbo->prepare("SELECT COUNT(*) FROM faqs WHERE category_id = ?");
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                throw new Exception("Cannot delete category. It is being used by {$count} FAQ(s). Please reassign or delete those FAQs first.");
            }
            
            // Delete category
            $stmt = $dbo->prepare("DELETE FROM faq_categories WHERE id = ?");
            $stmt->execute([$id]);
            
            $dbo->commit();
            return true;
            
        } catch (Exception $e) {
            $dbo->rollBack();
            throw $e;
        }
    }

    // Create new tag
    function create_tag($dbo, $name, $description = '') {
        $sql = "INSERT INTO faq_tags (name, description) VALUES (?, ?)";
        $stmt = $dbo->prepare($sql);
        return $stmt->execute([$name, $description]);
    }

    // Update tag
    function update_tag($dbo, $id, $name, $description = '') {
        $sql = "UPDATE faq_tags SET name = ?, description = ? WHERE id = ?";
        $stmt = $dbo->prepare($sql);
        return $stmt->execute([$name, $description, $id]);
    }

    // Delete tag
    function delete_tag($dbo, $id) {
        try {
            $dbo->beginTransaction();
            
            // Delete tag mappings first
            $stmt = $dbo->prepare("DELETE FROM faq_tag_map WHERE tag_id = ?");
            $stmt->execute([$id]);
            
            // Delete tag
            $stmt = $dbo->prepare("DELETE FROM faq_tags WHERE id = ?");
            $stmt->execute([$id]);
            
            $dbo->commit();
            return true;
            
        } catch (Exception $e) {
            $dbo->rollBack();
            throw $e;
        }
    }
 
    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'get_faqs':
            try {
                $faqs = get_all_faqs($dbo);
                json_response(true, '', $faqs);
            } catch (Exception $e) {
                json_response(false, 'Error fetching FAQs: ' . $e->getMessage());
            }
            break;
            
        case 'get_faq':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) {
                json_response(false, 'Invalid FAQ ID');
            }
            
            try {
                $faq = get_faq_by_id($dbo, $id);
                if (!$faq) {
                    json_response(false, 'FAQ not found');
                }
                json_response(true, '', $faq);
            } catch (Exception $e) {
                json_response(false, 'Error fetching FAQ: ' . $e->getMessage());
            }
            break;
            
        case 'create_faq':
            $required_fields = ['question', 'answer'];
            $errors = validate_required($required_fields, $_POST);
            
            if (!empty($errors)) {
                json_response(false, implode(', ', $errors));
            }
            
            $data = [
                'question' => sanitize_input($_POST['question']),
                'answer' => sanitize_input($_POST['answer']),
                'category_id' => intval($_POST['category_id'] ?? 0) ?: null,
                'priority' => intval($_POST['priority'] ?? 1),
                'training_weight' => floatval($_POST['training_weight'] ?? 1.00),
                'tags' => $_POST['tags'] ?? [],
                'variants' => []
            ];
            
            // Process variants
            if (!empty($_POST['variant_questions'])) {
                foreach ($_POST['variant_questions'] as $index => $question) {
                    if (!empty($question)) {
                        $data['variants'][] = [
                            'question' => sanitize_input($question),
                            'confidence' => floatval($_POST['variant_confidence'][$index] ?? 1.00)
                        ];
                    }
                }
            }
            
            try {
                $faq_id = create_faq($dbo, $data);
                json_response(true, 'FAQ created successfully', ['id' => $faq_id]);
            } catch (Exception $e) {
                json_response(false, 'Error creating FAQ: ' . $e->getMessage());
            }
            break;
            
        case 'update_faq':
            $id = intval($_POST['faq_id'] ?? 0);
            if (!$id) {
                json_response(false, 'Invalid FAQ ID');
            }
            
            $required_fields = ['question', 'answer'];
            $errors = validate_required($required_fields, $_POST);
            
            if (!empty($errors)) {
                json_response(false, implode(', ', $errors));
            }
            
            $data = [
                'question' => sanitize_input($_POST['question']),
                'answer' => sanitize_input($_POST['answer']),
                'category_id' => intval($_POST['category_id'] ?? 0) ?: null,
                'priority' => intval($_POST['priority'] ?? 1),
                'training_weight' => floatval($_POST['training_weight'] ?? 1.00),
                'tags' => $_POST['tags'] ?? [],
                'variants' => []
            ];
            
            // Process variants
            if (!empty($_POST['variant_questions'])) {
                foreach ($_POST['variant_questions'] as $index => $question) {
                    if (!empty($question)) {
                        $data['variants'][] = [
                            'question' => sanitize_input($question),
                            'confidence' => floatval($_POST['variant_confidence'][$index] ?? 1.00)
                        ];
                    }
                }
            }
            
            try {
                update_faq($dbo, $id, $data);
                json_response(true, 'FAQ updated successfully');
            } catch (Exception $e) {
                json_response(false, 'Error updating FAQ: ' . $e->getMessage());
            }
            break;
            
        case 'delete_faq':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) {
                json_response(false, 'Invalid FAQ ID');
            }
            
            try {
                delete_faq($dbo, $id);
                json_response(true, 'FAQ deleted successfully');
            } catch (Exception $e) {
                json_response(false, 'Error deleting FAQ: ' . $e->getMessage());
            }
            break;
            
        case 'toggle_status':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) {
                json_response(false, 'Invalid FAQ ID');
            }
            
            try {
                toggle_faq_status($dbo, $id);
                json_response(true, 'FAQ status updated successfully');
            } catch (Exception $e) {
                json_response(false, 'Error updating FAQ status: ' . $e->getMessage());
            }
            break;
            
        case 'get_categories':
            try {
                $categories = get_categories($dbo);
                json_response(true, '', $categories);
            } catch (Exception $e) {
                json_response(false, 'Error fetching categories: ' . $e->getMessage());
            }
            break;
            
        case 'get_tags':
            try {
                $tags = get_tags($dbo);
                json_response(true, '', $tags);
            } catch (Exception $e) {
                json_response(false, 'Error fetching tags: ' . $e->getMessage());
            }
            break;
            
        case 'create_category':
            $name = sanitize_input($_POST['name'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            
            if (empty($name)) {
                json_response(false, 'Category name is required');
            }
            
            try {
                create_category($dbo, $name, $description);
                json_response(true, 'Category created successfully');
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    json_response(false, 'Category name already exists');
                } else {
                    json_response(false, 'Error creating category: ' . $e->getMessage());
                }
            }
            break;

        case 'update_category':
            $id = intval($_POST['category_id'] ?? 0);
            $name = sanitize_input($_POST['name'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            
            if (!$id) {
                json_response(false, 'Invalid category ID');
            }
            
            if (empty($name)) {
                json_response(false, 'Category name is required');
            }
            
            try {
                update_category($dbo, $id, $name, $description);
                json_response(true, 'Category updated successfully');
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    json_response(false, 'Category name already exists');
                } else {
                    json_response(false, 'Error updating category: ' . $e->getMessage());
                }
            }
            break;

        case 'delete_category':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) {
                json_response(false, 'Invalid category ID');
            }
            
            try {
                delete_category($dbo, $id);
                json_response(true, 'Category deleted successfully');
            } catch (Exception $e) {
                json_response(false, $e->getMessage());
            }
            break;
            
        case 'create_tag':
            $name = sanitize_input($_POST['name'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            
            if (empty($name)) {
                json_response(false, 'Tag name is required');
            }
            
            try {
                create_tag($dbo, $name, $description);
                json_response(true, 'Tag created successfully');
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    json_response(false, 'Tag name already exists');
                } else {
                    json_response(false, 'Error creating tag: ' . $e->getMessage());
                }
            }
            break;

        case 'update_tag':
            $id = intval($_POST['tag_id'] ?? 0);
            $name = sanitize_input($_POST['name'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            
            if (!$id) {
                json_response(false, 'Invalid tag ID');
            }
            
            if (empty($name)) {
                json_response(false, 'Tag name is required');
            }
            
            try {
                update_tag($dbo, $id, $name, $description);
                json_response(true, 'Tag updated successfully');
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    json_response(false, 'Tag name already exists');
                } else {
                    json_response(false, 'Error updating tag: ' . $e->getMessage());
                }
            }
            break;

        case 'delete_tag':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) {
                json_response(false, 'Invalid tag ID');
            }
            
            try {
                delete_tag($dbo, $id);
                json_response(true, 'Tag deleted successfully');
            } catch (Exception $e) {
                json_response(false, 'Error deleting tag: ' . $e->getMessage());
            }
            break;
            
        default:
            json_response(false, 'Invalid action');
    }

} catch (PDOException $e) {
    json_response(false, 'Database error: ' . $e->getMessage(), null, 500);
} catch (Exception $e) {
    json_response(false, 'Server error: ' . $e->getMessage(), null, 500);
}
?>