<?php
@session_start();
include_once '../config/config.php';
include_once '../classes/users.php';
include_once '../includes/func.php';

// Check if user is logged in
$myuser = cuser::singleton();
$myuser->getUserData();

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();

    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        throw new Exception('Invalid training request ID');
    }

    // Get training request data
    $query = "SELECT * FROM training_requests WHERE id = :id AND deleted = 0";
    $stmt = $dbo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception('Training request not found');
    }

    // Setup PDF directory and filename
    $pdfDir = __DIR__ . '/../files/training_requests/';
    $pdfFilename = 'Training_Request_' . $id . '.pdf';
    $pdfPath = $pdfDir . $pdfFilename;

    // Ensure directory exists
    if (!file_exists($pdfDir)) {
        mkdir($pdfDir, 0777, true);
    }
    
    // Always generate/regenerate PDF (overwrite if exists)
    include_once(__DIR__ . '/../includes/training_pdf.php');
    generateTrainingRequestPDF($data, $pdfPath, false);

    // Download PDF
    if (file_exists($pdfPath)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $pdfFilename . '"');
        header('Content-Length: ' . filesize($pdfPath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        readfile($pdfPath);
        exit();
    } else {
        throw new Exception('PDF file generation failed');
    }

} catch (PDOException $e) {
    http_response_code(500);
    die('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    http_response_code(500);
    die($e->getMessage());
}
?>