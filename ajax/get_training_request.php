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

    // Get parameters for DataTables/jqGrid
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $orderColumn = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';

    // Column mapping
    $columns = [
        0 => 'id',
        1 => 'company_name',
        2 => 'contact_person',
        3 => 'email_address',
        4 => 'phone_number',
        5 => 'num_participants',
        6 => 'training_type',
        7 => 'training_cost',
        8 => 'created_at'
    ];

    $orderColumnName = $columns[$orderColumn] ?? 'id';

    // Base query
    $whereClause = "WHERE deleted = 0";
    $params = [];

    // Add search filter
    if (!empty($searchValue)) {
        $whereClause .= " AND (
            company_name LIKE :search OR 
            contact_person LIKE :search OR 
            email_address LIKE :search OR 
            phone_number LIKE :search OR 
            address LIKE :search
        )";
        $params[':search'] = "%$searchValue%";
    }

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM training_requests $whereClause";
    $stmt = $dbo->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get filtered data
    $query = "SELECT 
                id, 
                company_name, 
                address, 
                contact_person, 
                phone_number, 
                email_address, 
                language, 
                other_language, 
                preferred_date_1, 
                preferred_date_2, 
                preferred_date_3, 
                num_participants, 
                training_type, 
                training_cost, 
                acceptance_company, 
                acceptance_name_position, 
                acceptance_place_date, 
                created_at, 
                updated_at 
              FROM training_requests 
              $whereClause 
              ORDER BY $orderColumnName $orderDir 
              LIMIT :start, :length";

    $stmt = $dbo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format dates
        $preferredDates = [];
        if (!empty($row['preferred_date_1'])) {
            $preferredDates[] = date('d/m/Y', strtotime($row['preferred_date_1']));
        }
        if (!empty($row['preferred_date_2'])) {
            $preferredDates[] = date('d/m/Y', strtotime($row['preferred_date_2']));
        }
        if (!empty($row['preferred_date_3'])) {
            $preferredDates[] = date('d/m/Y', strtotime($row['preferred_date_3']));
        }

        $data[] = [
            'id' => $row['id'],
            'company_name' => $row['company_name'],
            'contact_person' => $row['contact_person'],
            'email_address' => $row['email_address'],
            'phone_number' => $row['phone_number'],
            'address' => $row['address'],
            'language' => $row['language'],
            'other_language' => $row['other_language'],
            'preferred_dates' => implode(', ', $preferredDates),
            'num_participants' => $row['num_participants'],
            'training_type' => $row['training_type'],
            'training_cost' => '€' . number_format($row['training_cost'], 2),
            'acceptance_company' => $row['acceptance_company'],
            'acceptance_name_position' => $row['acceptance_name_position'],
            'acceptance_place_date' => $row['acceptance_place_date'],
            'created_at' => date('d/m/Y H:i', strtotime($row['created_at'])),
            'updated_at' => date('d/m/Y H:i', strtotime($row['updated_at']))
        ];
    }

    // Return DataTables format
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $data
    ]);

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