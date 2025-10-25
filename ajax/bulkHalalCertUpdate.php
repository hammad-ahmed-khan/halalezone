<?php
include_once '../config/config.php';
include_once '../classes/users.php';
include_once '../includes/func.php';

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
} catch (PDOException $e) {
    echo json_encode(['status' => 0, 'statusDescription' => 'Database error: ' . $e->getMessage()]);
    exit;
}

$data = $_POST;
$ingredientIds = $data['ingredientIds'];
$certificateFile = $data['certificateFile'];
$producerName = $data['producerName'];
$supplierName = $data['supplierName'];
$certificationBodyName = $data['certificationBodyName'];
$dateTime = DateTime::createFromFormat('d/m/Y', $data['expiryDate']);
$expiryDate = $dateTime->format('Y-m-d');
$result = [
    'total' => count($ingredientIds),
    'success' => 0,
    'failed' => 0,
    'failed_rows' => []
];

foreach ($ingredientIds as $ingredientId) {
    try {
        // Get ingredient name for error reporting
        $nameQuery = "SELECT name FROM tingredients WHERE id = :id";
        $nameStmt = $dbo->prepare($nameQuery);
        $nameStmt->bindParam(':id', $ingredientId, PDO::PARAM_INT);
        $nameStmt->execute();
        $ingredientName = $nameStmt->fetchColumn() ?: "Unknown";
        
        // Update ingredient with certificate information
        $updateQuery = "UPDATE tingredients SET 
                        halalcert = 1,
                        cert = :cert,
                        producer = :producer,
                        supplier = :supplier,
                        cb = :cb,
                        halalexp = :halalexp,
                        conf = 0
                        WHERE id = :id";
        
        $stmt = $dbo->prepare($updateQuery);
        $stmt->bindParam(':cert', $certificateFile, PDO::PARAM_STR);
        $stmt->bindParam(':producer', $producerName, PDO::PARAM_STR);
        $stmt->bindParam(':supplier', $supplierName, PDO::PARAM_STR);
        $stmt->bindParam(':cb', $certificationBodyName, PDO::PARAM_STR);
        $stmt->bindParam(':halalexp', $expiryDate, PDO::PARAM_STR);
         $stmt->bindParam(':id', $ingredientId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $result['success']++;
        } else {
            $result['failed']++;
            $result['failed_rows'][] = [
                'name' => $ingredientName,
                'error' => 'Database update failed'
            ];
        }
        
    } catch (Exception $e) {
        $result['failed']++;
        $result['failed_rows'][] = [
            'name' => $ingredientName,
            'error' => $e->getMessage()
        ];
    }
}

echo json_encode([
    'status' => 1,
    'statusDescription' => 'Bulk certificate update completed',
    'data' => $result
]);
?>