<?php
@session_start();
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../includes/func.php";

try {
    $db = acsessDb::singleton();
    $dbo = $db->connect();
    $myuser = cuser::singleton();
    $myuser->getUserData();
    
    // Get pagination and sorting parameters
    $curPage = $_POST['page'] ?? 1;
    $rowsPerPage = $_POST['rows'] ?? 20;
    $sortingField = $_POST['sidx'] ?? 'created_at';
    $sortingOrder = $_POST['sord'] ?? 'desc';

    // Get filter parameters
    $company_name = getPostParam('company_name');
    $type_of_animal = getPostParam('type_of_animal');
    $status = getPostParam('status');
    $start_datetime_from = getPostParam('start_datetime_from');
    $start_datetime_to = getPostParam('start_datetime_to');

    // Handle special idclient values
    $idclient = getGetParam('idclient');
    if (!is_numeric($idclient)) {
        $idclient = -1;
    }

    // Special case: return empty result set
    if ($idclient === -2) {
        $response = new \stdClass();
        $response->page = 1;
        $response->total = 0;
        $response->records = 0;
        $response->rows = [];
        echo json_encode($response);
        die();
    }

    if(!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');

    // Build base filter
    $filter = "WHERE IFNULL(h.company_name, '') <> '' AND h.deleted = $displaymode";

    // Apply client filter
    if ($idclient != -1) {
        $filter .= " AND h.idclient = " . intval($idclient);
    }

    // Handle search parameters
    $searching = $_POST['_search'] ?? false;
    if ($searching) {
        if ($company_name != '') {
            $filter .= ' AND h.company_name LIKE "%' . $company_name . '%"';
        }
        if ($type_of_animal != '') {
            $filter .= ' AND h.type_of_animal = "' . $type_of_animal . '"';
        }
        if ($status != '') {
            $filter .= ' AND h.status = "' . $status . '"';
        }
        if ($start_datetime_from != '') {
            $filter .= ' AND h.start_datetime >= "' . date('Y-m-d H:i:s', strtotime($start_datetime_from)) . '"';
        }
        if ($start_datetime_to != '') {
            $filter .= ' AND h.start_datetime <= "' . date('Y-m-d H:i:s', strtotime($start_datetime_to)) . '"';
        }
    }

    // Get total count of records
    $countSql = 'SELECT COUNT(h.id) AS count FROM thalal_slaughtering h
LEFT JOIN tusers u ON h.idclient = u.id '. $filter;

    $countStmt = $dbo->prepare($countSql);
    $countStmt->execute();
    $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC);

    // Prepare pagination
    $rowsPerPage = intval($rowsPerPage) === 1000000 ? 0 : intval($rowsPerPage);
    $curPage = $rowsPerPage ? $curPage : 1;
    $firstRowIndex = $rowsPerPage ? ($curPage - 1) * $rowsPerPage : 0;
    $limit = $rowsPerPage ? "LIMIT $firstRowIndex, $rowsPerPage" : '';

    // Main query to get Halal Slaughtering data
    $sql = <<<EOL
SELECT 
    h.id,
    h.idclient,
    u.name as client_name, 
    h.company_name,
    h.contact_person_1,
    h.contact_person_2,
    h.start_datetime,
    h.end_datetime,
    h.type_of_animal,
    h.butcher_1,
    h.butcher_2,
    h.butcher_3,
    h.supervisor_1,
    h.supervisor_2,
    h.supervisor_3,
    h.halal_slaughtering_documents,
    h.method_of_stunning,
    h.halal_slaughtering_data,
    h.upload_live_animals_documents,
    h.upload_pictures_after_cleaning,
    h.upload_halal_slaughtering_video,
    h.upload_additional_pictures,
    h.upload_halal_stock,
    h.invoice_travel_expenses,
    h.proof_of_payment,
    h.status,
    h.created_at,
    h.created_by,
    h.deleted
FROM thalal_slaughtering h
LEFT JOIN tusers u ON h.idclient = u.id
{$filter}
ORDER BY {$sortingField} {$sortingOrder}
{$limit}
EOL;

    // Prepare response
    $response = new \stdClass();
    $response->page = $curPage;
    $response->total = $rowsPerPage ? ceil($totalRows['count'] / $rowsPerPage) : 1;
    $response->records = $totalRows['count'];
    $response->rows = [];

    // Execute query and build response
    $stmt = $dbo->prepare($sql);
    if (!$stmt->execute()) {
        die(json_encode(['error' => 'Failed to execute query']));
    }

    $i = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $response->rows[$i]['id'] = $row['id'];
        $response->rows[$i]['cell'] = [
            $row['id'],
            $row['idclient'],
            $row['client_name'],
            $row['contact_person_1'],
            $row['contact_person_2'],
            $row['start_datetime'],
            $row['end_datetime'],
            $row['type_of_animal'],
            $row['butcher_1'],
            $row['butcher_2'],
            $row['butcher_3'],
            $row['supervisor_1'],
            $row['supervisor_2'],
            $row['supervisor_3'],
            $row['halal_slaughtering_documents'],
            $row['method_of_stunning'],
            $row['halal_slaughtering_data'],
            $row['upload_live_animals_documents'],
            $row['upload_pictures_after_cleaning'],
            $row['upload_halal_slaughtering_video'],
            $row['upload_additional_pictures'],
            $row['upload_halal_stock'],
            $row['invoice_travel_expenses'],
            $row['proof_of_payment'],
            $row['status'],
            $row['deleted'],
        ];
        $i++;
    }

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>