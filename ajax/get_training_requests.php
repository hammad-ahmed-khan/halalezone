<?php
@session_start();
include_once '../config/config.php';
include_once '../classes/users.php';
include_once '../includes/func.php';

header('Content-Type: application/json');

// Check if user is logged in
$myuser = cuser::singleton();
$myuser->getUserData();

$isAdmin = $myuser->userdata['isclient'] == "0";
$isClient = $myuser->userdata['isclient'] == "1";
$user_id = $myuser->userdata['id'];


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

    // Column mapping - updated to include auditor
    $columns = [
        0 => 'id',
        1 => 'company_name',
        2 => 'contact_person',
        3 => 'email_address',
        4 => 'phone_number',
        5 => 'num_participants',
        6 => 'training_type',
        7 => 'training_cost',
        8 => 'auditor_name',
        9 => 'created_at'
    ];

    $orderColumnName = $columns[$orderColumn] ?? 'id';

    // Base query
    $whereClause = "WHERE tr.deleted = 0";

    if ($isClient) {
        // Clients can only see their own training requests
        // NOTE: You may need to adjust the field name based on your actual database schema
        // Common field names: created_by, user_id, idclient, submitted_by
        $whereClause .= " AND tr.user_id = '".$user_id."'";
    }

    $params = [];

    // Add search filter
    if (!empty($searchValue)) {
        $whereClause .= " AND (
            tr.company_name LIKE :search OR 
            tr.contact_person LIKE :search OR 
            tr.email_address LIKE :search OR 
            tr.phone_number LIKE :search OR 
            tr.address LIKE :search OR
            aud.name LIKE :search
        )";
        $params[':search'] = "%$searchValue%";
    }

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM training_requests tr $whereClause";
    $stmt = $dbo->prepare($countQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get filtered data with auditor information
    $query = "SELECT 
                tr.id, 
                tr.company_name, 
                tr.address, 
                tr.contact_person, 
                tr.phone_number, 
                tr.email_address, 
                tr.language, 
                tr.other_language, 
                tr.preferred_date_1, 
                tr.preferred_date_2, 
                tr.preferred_date_3, 
                tr.num_participants, 
                tr.training_type, 
                tr.training_cost, 
                tr.acceptance_company, 
                tr.acceptance_name_position, 
                tr.acceptance_place_date, 
                tr.idauditor,
                tr.created_at, 
                tr.updated_at,
                aud.name as auditor_name,
                a.training_request_form,
                a.attendance_list,
                a.customer_feedback_form,
                a.attendance_certificates
              FROM training_requests tr
              LEFT OUTER JOIN tusers aud ON aud.id = tr.idauditor AND aud.isclient = 2 AND aud.deleted = 0
              LEFT OUTER JOIN ttrainer_activities a ON a.request_id = tr.id
              $whereClause 
              ORDER BY tr.$orderColumnName $orderDir 
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
            'idauditor' => $row['idauditor'],
            'auditor_name' => $row['auditor_name'],
            'acceptance_company' => $row['acceptance_company'],
            'acceptance_name_position' => $row['acceptance_name_position'],
            'acceptance_place_date' => $row['acceptance_place_date'],
            'created_at' => date('d/m/Y H:i', strtotime($row['created_at'])),
            'updated_at' => date('d/m/Y H:i', strtotime($row['updated_at'])),
            'training_request_form' => $row['training_request_form'],
            'attendance_list' => $row['attendance_list'],
            'customer_feedback_form' => $row['customer_feedback_form'],
            'attendance_certificates' => $row['attendance_certificates'],
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