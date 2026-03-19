<?php
// ajax/faqPublic.php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

// Include notification functions if available
if (file_exists('../notifications/notifyfuncs.php')) {
    include_once "../notifications/notifyfuncs.php";
}

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
            WHERE f.is_active = 1 AND f.answer IS NOT NULL AND f.answer != ''";
    
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
            WHERE f.is_active = 1 AND f.answer IS NOT NULL AND f.answer != ''";
    
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
    // Get total count (all active FAQs with answers)
    $total_sql = "SELECT COUNT(*) as total_count FROM faqs WHERE is_active = 1 AND answer IS NOT NULL AND answer != ''";
    $total_stmt = $dbo->query($total_sql);
    $total_count = $total_stmt->fetch(PDO::FETCH_ASSOC)['total_count'];
    
    // Get visible count (filtered FAQs)
    $visible_sql = "SELECT COUNT(*) as visible_count FROM faqs f WHERE f.is_active = 1 AND f.answer IS NOT NULL AND f.answer != ''";
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
    $all_sql = "SELECT COUNT(*) as count FROM faqs WHERE is_active = 1 AND answer IS NOT NULL AND answer != ''";
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
                LEFT JOIN faqs f ON c.id = f.category_id AND f.is_active = 1 AND f.answer IS NOT NULL AND f.answer != ''";
    
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
        // Check if view_count column exists, if not, create it
        $check_column = "SHOW COLUMNS FROM faqs LIKE 'view_count'";
        $stmt = $dbo->query($check_column);
        if (!$stmt->fetch()) {
            $add_column = "ALTER TABLE faqs ADD COLUMN view_count INT(11) DEFAULT 0";
            $dbo->exec($add_column);
        }
        
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
    try {
        $sql = "SELECT id, question, view_count 
                FROM faqs 
                WHERE is_active = 1 
                ORDER BY view_count DESC 
                LIMIT 1";
        $stmt = $dbo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // If view_count column doesn't exist, return null
        return null;
    }
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

// NEW FUNCTIONS FOR QUESTION SUBMISSION
// =====================================

// Submit a new question
function submit_question($dbo, $user_id, $question, $category_id = null, $context = '') {
    // Check for duplicate questions from the same user within 24 hours
    $check_sql = "
        SELECT id FROM faqs 
        WHERE question = ? AND created_by = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
    ";
    $check_stmt = $dbo->prepare($check_sql);
    $check_stmt->execute([$question, $user_id]);
    
    if ($check_stmt->fetchColumn()) {
        throw new Exception('You have already submitted this question recently');
    }
    
    // Insert the new question
    $sql = "
        INSERT INTO faqs (question, category_id, created_by, is_active, priority, status, additional_context, created_at) 
        VALUES (?, ?, ?, 0, 1, 'pending', ?, NOW())
    ";
    
    $stmt = $dbo->prepare($sql);
    $result = $stmt->execute([$question, $category_id, $user_id, $context]);
    
    if ($result) {
        $question_id = $dbo->lastInsertId();
        
        // Send notification email to admins
        try {
            send_question_notification($dbo, $question_id, $user_id, $question, $category_id, $context);
        } catch (Exception $e) {
            // Log error but don't fail the question submission
            error_log('Failed to send question notification email: ' . $e->getMessage());
        }
        
        return $question_id;
    } else {
        throw new Exception('Failed to submit question');
    }
}

// Send notification email to admins when a new question is submitted
function send_question_notification($dbo, $question_id, $user_id, $question, $category_id = null, $context = '') {
    global $adminEmailAddress;
    
    // Get user details


    $user_sql = "SELECT name, email, isclient FROM tusers WHERE id = ?";
    $user_stmt = $dbo->prepare($user_sql);
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        return false;
    }
    
    // Get category name if provided
    $category_name = 'Uncategorized';
    if ($category_id) {
        $cat_sql = "SELECT name FROM faq_categories WHERE id = ?";
        $cat_stmt = $dbo->prepare($cat_sql);
        $cat_stmt->execute([$category_id]);
        $category_result = $cat_stmt->fetch(PDO::FETCH_ASSOC);
        if ($category_result) {
            $category_name = $category_result['name'];
        }
    }
 
    // Prepare email content
    $user_type = ($user['isclient'] == '2') ? 'Auditor' : 'Admin User';
    
    $body = [
        'name' => 'Halal e-Zone System',
        'email' => 'noreply@halal-e.zone',
        'subject' => 'New FAQ Question Submitted - Halal e-Zone',
        'header' => '',
        'body' => generate_question_notification_body($user, $user_type, $question, $category_name, $context, $question_id)
    ];
 
    try {
        $body['to'] = $adminEmailAddress;
        
        // Use the existing sendEmail function if it exists
        if (function_exists('sendEmail')) {
            sendEmail($body);
            $sent_count++;
        } else {
            // Fallback to basic mail function
            $headers = "From: noreply@halal-e.zone\r\n";
            $headers .= "Reply-To: noreply@halal-e.zone\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            if (mail($admin['email'], $body['subject'], $body['body'], $headers)) {
                $sent_count++;
            }
        }
    } catch (Exception $e) {
        error_log('Failed to send email to admin ' . $admin['email'] . ': ' . $e->getMessage());
    }
    
    return $sent_count > 0;
}

// Generate email body for question notification
function generate_question_notification_body($user, $user_type, $question, $category_name, $context, $question_id) {
    $body = "<h2>New FAQ Question Submitted</h2>";
    $body .= "<p>A new question has been submitted to the FAQ system and requires your attention.</p>";
    
    $body .= "<div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    $body .= "<h3>Question Details:</h3>";
    $body .= "<p><strong>Question:</strong><br>" . nl2br(htmlspecialchars($question)) . "</p>";
    $body .= "<p><strong>Category:</strong> " . htmlspecialchars($category_name) . "</p>";
    
    if (!empty($context)) {
        $body .= "<p><strong>Additional Context:</strong><br>" . nl2br(htmlspecialchars($context)) . "</p>";
    }
    $body .= "</div>";
    
    $body .= "<div style='background-color: #e9ecef; padding: 15px; border-radius: 8px; margin: 20px 0;'>";
    $body .= "<h3>Submitted By:</h3>";
    $body .= "<p><strong>Name:</strong> " . htmlspecialchars($user['name']) . "</p>";
    $body .= "<p><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</p>";
    $body .= "<p><strong>User Type:</strong> " . $user_type . "</p>";
    $body .= "<p><strong>Submission Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
    $body .= "</div>";
    
    $body .= "<div style='margin: 30px 0;'>";
    $body .= "<h3>Next Steps:</h3>";
    $body .= "<ol>";
    $body .= "<li>Review the question and any additional context provided</li>";
    $body .= "<li>Log into the FAQ management system to provide an answer</li>";
    $body .= "<li>The submitter will be automatically notified when you answer</li>";
    $body .= "</ol>";
    $body .= "</div>";
    
    $body .= "<p><a href='https://halal-e.zone/faq_manager' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Answer Question Now</a></p>";
    
    $body .= "<hr style='margin: 30px 0;'>";
    $body .= "<p style='color: #666; font-size: 12px;'>";
    $body .= "This email was sent automatically by the Halal e-Zone FAQ system.<br>";
    $body .= "Question ID: " . $question_id . "<br>";
    $body .= "Do not reply to this email.";
    $body .= "</p>";
    
    return $body;
}

// Get user's submitted questions
function get_user_questions($dbo, $user_id) {
    $sql = "
        SELECT 
            f.id,
            f.question,
            f.answer,
            f.status,
            f.created_at,
            f.updated_at,
            fc.name as category_name
        FROM faqs f
        LEFT JOIN faq_categories fc ON f.category_id = fc.id
        WHERE f.created_by = ?
        ORDER BY f.created_at DESC
    ";
    
    $stmt = $dbo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if user can ask questions
function can_user_ask_questions($user_data) {
    $isAdmin = $user_data['isclient'] == "0";
    $isAuditor = $user_data['isclient'] == "2";
    return ($isAdmin || $isAuditor);
}

// Handle the request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Get user data for question submission features
$myuser = cuser::singleton();
$myuser->getUserData();
$userId = $myuser->userdata['id'] ?? null;
$userData = $myuser->userdata ?? null;
$canAskQuestions = $userData ? can_user_ask_questions($userData) : false;

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
        
    // NEW QUESTION SUBMISSION ACTIONS
    // ===============================
    
    case 'submit_question':
        try {
            if (!$canAskQuestions) {
                json_response(false, 'You do not have permission to submit questions');
            }
            
            if (!$userId) {
                json_response(false, 'User not authenticated');
            }
            
            $question = sanitize_input($_POST['question'] ?? '');
            $category_id = intval($_POST['category_id'] ?? 0) ?: null;
            $context = sanitize_input($_POST['context'] ?? '');
            
            if (empty($question)) {
                json_response(false, 'Question is required');
            }
            
            $question_id = submit_question($dbo, $userId, $question, $category_id, $context);
            json_response(true, 'Question submitted successfully', ['id' => $question_id]);
            
        } catch (Exception $e) {
            json_response(false, 'Error submitting question: ' . $e->getMessage());
        }
        break;
        
    case 'get_my_questions':
        try {
            if (!$canAskQuestions || !$userId) {
                json_response(false, 'Access denied');
            }
            
            $questions = get_user_questions($dbo, $userId);
            json_response(true, '', $questions);
            
        } catch (Exception $e) {
            json_response(false, 'Error loading your questions: ' . $e->getMessage());
        }
        break;
        
    default:
        json_response(false, 'Invalid action specified');
        break;
}
?>
?>