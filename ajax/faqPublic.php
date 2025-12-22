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

// Get filtered FAQs with search and category filtering
function get_filtered_faqs($dbo, $category_id = null, $search_term = null) {
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
    
    // Category filtering
    if ($category_id && $category_id !== 'all') {
        $sql .= " AND f.category_id = ?";
        $params[] = $category_id;
    }
    
    // Search filtering
    if ($search_term && !empty(trim($search_term))) {
        $search_term = '%' . trim($search_term) . '%';
        $sql .= " AND (f.question LIKE ? OR f.answer LIKE ?)";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $sql .= " ORDER BY f.priority DESC, f.created_at DESC";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get FAQ statistics
function get_faq_stats($dbo, $category_id = null, $search_term = null) {
    // Get total count (all active FAQs)
    $total_sql = "SELECT COUNT(*) as total_count FROM faqs WHERE is_active = 1";
    $total_stmt = $dbo->query($total_sql);
    $total_count = $total_stmt->fetch(PDO::FETCH_ASSOC)['total_count'];
    
    // Get visible count (filtered FAQs)
    $visible_sql = "SELECT COUNT(*) as visible_count FROM faqs f WHERE f.is_active = 1";
    $params = [];
    
    // Apply same filters as get_filtered_faqs
    if ($category_id && $category_id !== 'all') {
        $visible_sql .= " AND f.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($search_term && !empty(trim($search_term))) {
        $search_term_count = '%' . trim($search_term) . '%';
        $visible_sql .= " AND (f.question LIKE ? OR f.answer LIKE ?)";
        $params[] = $search_term_count;
        $params[] = $search_term_count;
    }
    
    $visible_stmt = $dbo->prepare($visible_sql);
    $visible_stmt->execute($params);
    $visible_count = $visible_stmt->fetch(PDO::FETCH_ASSOC)['visible_count'];
    
    return [
        'total_count' => $total_count,
        'visible_count' => $visible_count
    ];
}

// Get category counts for current search
function get_category_counts($dbo, $search_term = null) {
    $counts = [];
    
    // Get count for "All Categories"
    $all_sql = "SELECT COUNT(*) as count FROM faqs WHERE is_active = 1";
    $all_params = [];
    
    if ($search_term && !empty(trim($search_term))) {
        $search_term_all = '%' . trim($search_term) . '%';
        $all_sql .= " AND (question LIKE ? OR answer LIKE ?)";
        $all_params[] = $search_term_all;
        $all_params[] = $search_term_all;
    }
    
    $all_stmt = $dbo->prepare($all_sql);
    $all_stmt->execute($all_params);
    $counts['all'] = $all_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get counts for each category
    $cat_sql = "SELECT 
                    c.id,
                    COUNT(f.id) as count
                FROM faq_categories c
                LEFT JOIN faqs f ON c.id = f.category_id AND f.is_active = 1";
    
    $cat_params = [];
    
    if ($search_term && !empty(trim($search_term))) {
        $search_term_cat = '%' . trim($search_term) . '%';
        $cat_sql .= " AND (f.question LIKE ? OR f.answer LIKE ?)";
        $cat_params[] = $search_term_cat;
        $cat_params[] = $search_term_cat;
    }
    
    $cat_sql .= " WHERE c.is_active = 1 GROUP BY c.id";
    
    $cat_stmt = $dbo->prepare($cat_sql);
    $cat_stmt->execute($cat_params);
    $category_counts = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($category_counts as $cat) {
        $counts[$cat['id']] = $cat['count'];
    }
    
    return $counts;
}

// Search FAQs (legacy function for backward compatibility)
function search_faqs($dbo, $search_term, $category_id = null) {
    return get_filtered_faqs($dbo, $category_id, $search_term);
}

// Track FAQ view
function track_faq_view($dbo, $faq_id) {
    try {
        $sql = "UPDATE faqs SET view_count = view_count + 1 WHERE id = ? AND is_active = 1";
        $stmt = $dbo->prepare($sql);
        return $stmt->execute([$faq_id]);
    } catch (Exception $e) {
        error_log('Error tracking FAQ view: ' . $e->getMessage());
        return false;
    }
}

// Get most viewed FAQ
function get_most_viewed_faq($dbo) {
    $sql = "SELECT id, question, view_count 
            FROM faqs 
            WHERE is_active = 1 
            ORDER BY view_count DESC 
            LIMIT 1";
    $stmt = $dbo->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Validate required fields
function validate_required($required_fields, $data) {
    $errors = [];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = "Field '$field' is required";
        }
    }
    return $errors;
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
        
    case 'get_filtered_faqs':
        try {
            $category_id = $_GET['category_id'] ?? null;
            $search_term = sanitize_input($_GET['search'] ?? '');
            
            // Get filtered FAQs
            $faqs = get_filtered_faqs($dbo, $category_id, $search_term);
            
            // Get statistics
            $stats = get_faq_stats($dbo, $category_id, $search_term);
            
            // Get category counts
            $category_counts = get_category_counts($dbo, $search_term);
            
            $response_data = [
                'faqs' => $faqs,
                'stats' => $stats,
                'category_counts' => $category_counts
            ];
            
            json_response(true, '', $response_data);
        } catch (Exception $e) {
            json_response(false, 'Error fetching filtered FAQs: ' . $e->getMessage());
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
            $category_id = $_GET['category_id'] ?? null;
            $search_term = sanitize_input($_GET['search'] ?? '');
            
            $stats = get_faq_stats($dbo, $category_id, $search_term);
            $category_counts = get_category_counts($dbo, $search_term);
            
            $response_data = [
                'stats' => $stats,
                'category_counts' => $category_counts,
                'most_viewed' => get_most_viewed_faq($dbo)
            ];
            
            json_response(true, '', $response_data);
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
        
    case 'get_category_faqs':
        try {
            $category_id = intval($_GET['category_id'] ?? 0);
            
            if (!$category_id) {
                json_response(false, 'Invalid category ID');
            }
            
            $faqs = get_filtered_faqs($dbo, $category_id);
            $stats = get_faq_stats($dbo, $category_id);
            
            $response_data = [
                'faqs' => $faqs,
                'stats' => $stats
            ];
            
            json_response(true, '', $response_data);
        } catch (Exception $e) {
            json_response(false, 'Error fetching category FAQs: ' . $e->getMessage());
        }
        break;
        
    default:
        json_response(false, 'Invalid action specified');
        break;
}
?>