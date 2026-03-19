<?php
@session_start();
include_once '../config/config.php';
include_once '../classes/users.php';
include_once '../includes/func.php';

header('Content-Type: application/json');

// Check if user is logged in
$myuser = cuser::singleton();
$myuser->getUserData();

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();

    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        throw new Exception('Invalid training request ID');
    }

    // Soft delete - set deleted flag to 1
    $query = "UPDATE training_requests SET deleted = 1 WHERE id = :id";
    $stmt = $dbo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Training request deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete training request');
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>