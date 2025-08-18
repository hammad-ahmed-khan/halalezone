<?php
// ajax/faqPublic.php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

// Helper function for JSON response
function json_response($success, $message = '', $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Sanitize input
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Get all active categories
function get_active_categories($dbo) {
    $sql = "SELECT id, name, description 
            FROM faq_categories 
            WHERE is_active = 1 
            ORDER BY name ASC";
    $stmt = $dbo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all active FAQs for public display
function get_public_faqs($dbo, $category_id = null) {
    $sql = "SELECT 
                f.id,
                f.question,
                f.answer,
                f.priority,
                f.created_at,
                f.category_id,
                c.name as category_name
            FROM faqs f
            LEFT JOIN faq_categories c ON f.category_id = c.id
            WHERE f.is_active = 1";
    
    $params = [];
    
    if ($category_id && $category_id !== 'all') {
        $sql .= " AND f.category_id = ?";
        $params[] = $category_id;
    }
    
    $sql .= " ORDER BY f.priority DESC, f.created_at DESC";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Search FAQs
function search_faqs($dbo, $search_term, $category_id = null) {
    $search_term = '%' . $search_term . '%';
    
    $sql = "SELECT 
                f.id,
                f.question,
                f.answer,
                f.priority,
                f.created_at,
                f.category_id,
                c.name as category_name
            FROM faqs f
            LEFT JOIN faq_categories c ON f.category_id = c.id
            WHERE f.is_active = 1
            AND (f.question LIKE ? OR f.answer LIKE ?)";
    
    $params = [$search_term, $search_term];
    
    if ($category_id && $category_id !== 'all') {
        $sql .= " AND f.category_id = ?";
        $params[] = $category_id;
    }
    
    $sql .= " ORDER BY f.priority DESC, f.created_at DESC";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Track FAQ view for analytics
function track_faq_view($dbo, $faq_id) {
    try {
        // Get current date
        $today = date('Y-m-d');
        
        // Check if record exists for today
        $sql = "SELECT id, view_count FROM faq_analytics 
                WHERE faq_id = ? AND date_viewed = ?";
        $stmt = $dbo->prepare($sql);
        $stmt->execute([$faq_id, $today]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing record
            $sql = "UPDATE faq_analytics 
                    SET view_count = view_count + 1,
                        last_viewed = NOW()
                    WHERE id = ?";
            $stmt = $dbo->prepare($sql);
            $stmt->execute([$existing['id']]);
        } else {
            // Insert new record
            $sql = "INSERT INTO faq_analytics (faq_id, date_viewed, view_count, last_viewed) 
                    VALUES (?, ?, 1, NOW())";
            $stmt = $dbo->prepare($sql);
            $stmt->execute([$faq_id, $today]);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("FAQ Analytics Error: " . $e->getMessage());
        return false;
    }
}

// Get FAQ statistics
function get_faq_stats($dbo) {
    $stats = [];
    
    // Total active FAQs
    $sql = "SELECT COUNT(*) as total FROM faqs WHERE is_active = 1";
    $stmt = $dbo->query($sql);
    $stats['total_faqs'] = $stmt->fetchColumn();
    
    // Total categories
    $sql = "SELECT COUNT(*) as total FROM faq_categories WHERE is_active = 1";
    $stmt = $dbo->query($sql);
    $stats['total_categories'] = $stmt->fetchColumn();
    
    // Most viewed FAQ this month
    $sql = "SELECT f.question, SUM(fa.view_count) as total_views
            FROM faq_analytics fa
            JOIN faqs f ON fa.faq_id = f.id
            WHERE MONTH(fa.date_viewed) = MONTH(NOW()) 
            AND YEAR(fa.date_viewed) = YEAR(NOW())
            GROUP BY fa.faq_id, f.question
            ORDER BY total_views DESC
            LIMIT 1";
    $stmt = $dbo->query($sql);
    $mostViewed = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['most_viewed'] = $mostViewed ? $mostViewed : null;
    
    return $stats;
}

// Handle the request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_categories':
        try {
            $categories = get_active_categories($dbo);
            json_response(true, '', $categories);
        } catch (Exception $e) {
            json_response(false, 'Error fetching categories: ' . $e->getMessage());
        }
        break;
        
    case 'get_public_faqs':
        try {
            $category_id = $_GET['category_id'] ?? null;
            $faqs = get_public_faqs($dbo, $category_id);
            json_response(true, '', $faqs);
        } catch (Exception $e) {
            json_response(false, 'Error fetching FAQs: ' . $e->getMessage());
        }
        break;
        
    case 'search_faqs':
        try {
            $search_term = sanitize_input($_GET['q'] ?? '');
            $category_id = $_GET['category_id'] ?? null;
            
            if (empty($search_term)) {
                json_response(false, 'Search term is required');
            }
            
            $faqs = search_faqs($dbo, $search_term, $category_id);
            json_response(true, '', $faqs);
        } catch (Exception $e) {
            json_response(false, 'Error searching FAQs: ' . $e->getMessage());
        }
        break;
        
    case 'track_faq_view':
        try {
            $faq_id = intval($_POST['faq_id'] ?? 0);
            
            if (!$faq_id) {
                json_response(false, 'Invalid FAQ ID');
            }
            
            $result = track_faq_view($dbo, $faq_id);
            json_response($result, $result ? 'View tracked' : 'Error tracking view');
        } catch (Exception $e) {
            json_response(false, 'Error tracking view: ' . $e->getMessage());
        }
        break;
        
    case 'get_stats':
        try {
            $stats = get_faq_stats($dbo);
            json_response(true, '', $stats);
        } catch (Exception $e) {
            json_response(false, 'Error fetching stats: ' . $e->getMessage());
        }
        break;
        
    case 'get_faq_by_id':
        try {
            $faq_id = intval($_GET['id'] ?? 0);
            
            if (!$faq_id) {
                json_response(false, 'Invalid FAQ ID');
            }
            
            $sql = "SELECT f.*, c.name as category_name 
                    FROM faqs f 
                    LEFT JOIN faq_categories c ON f.category_id = c.id 
                    WHERE f.id = ? AND f.is_active = 1";
            $stmt = $dbo->prepare($sql);
            $stmt->execute([$faq_id]);
            $faq = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$faq) {
                json_response(false, 'FAQ not found');
            }
            
            json_response(true, '', $faq);
        } catch (Exception $e) {
            json_response(false, 'Error fetching FAQ: ' . $e->getMessage());
        }
        break;
        
    default:
        json_response(false, 'Invalid action');
        break;
}
?>