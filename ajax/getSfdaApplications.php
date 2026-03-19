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
    $application_name = getPostParam('application_name');
    $company_name = getPostParam('company_name');
    $status = getPostParam('status');
    $validity_period = getPostParam('validity_period');
    $date_from = getPostParam('date_from');
    $date_to = getPostParam('date_to');

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
    $filter = "WHERE IFNULL(a.company_name, '') <> '' AND a.deleted = $displaymode";

    // Apply client filter
    if ($idclient != -1) {
        $filter .= " AND a.idclient = " . intval($idclient);
    }

    // Handle search parameters
    $searching = $_POST['_search'] ?? false;
    if ($searching) {
        if ($application_name != '') {
            $filter .= ' AND a.application_name LIKE "%' . $application_name . '%"';
        }
         
        if ($status != '') {
            $filter .= ' AND a.status = "' . $status . '"';
        }
        if ($validity_period != '') {
            $filter .= ' AND a.validity_of_certificate_period = "' . $validity_period . '"';
        }
        if ($date_from != '') {
            $filter .= ' AND a.created_at >= "' . date('Y-m-d', strtotime($date_from)) . '"';
        }
        if ($date_to != '') {
            $filter .= ' AND a.created_at <= "' . date('Y-m-d', strtotime($date_to)) . '"';
        }
    }

    // Get total count of records
    $countSql = 'SELECT COUNT(a.id) AS count FROM tsfda_applications a
LEFT JOIN tusers u ON a.idclient = u.id '. $filter;

    $countStmt = $dbo->prepare($countSql);
    $countStmt->execute();
    $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC);

    // Prepare pagination
    $rowsPerPage = intval($rowsPerPage) === 1000000 ? 0 : intval($rowsPerPage);
    $curPage = $rowsPerPage ? $curPage : 1;
    $firstRowIndex = $rowsPerPage ? ($curPage - 1) * $rowsPerPage : 0;
    $limit = $rowsPerPage ? "LIMIT $firstRowIndex, $rowsPerPage" : '';

    // Main query to get SFDA applications data
    $sql = <<<EOL
SELECT 
    a.id,
    a.idclient,
    u.name as client_name, 
    a.application_name,
    a.company_name,
    a.address,
    a.commercial_registration_certificate,
    a.commercial_registration_no,
    a.vat_number,
    a.accreditation_certificates,
    a.accreditation_certificates_other,
    a.number_of_production_lines,
    a.number_of_critical_points,
    a.number_of_full_time_employees,
    a.number_of_shifts,
    a.number_of_shift_employees,
    a.production_area_space_m2,
    a.additional_branches_of_the_company,
    a.upload_product_information,
    a.validity_of_certificate_period,
    a.invoice,
    a.proof_of_payment,
    a.sfda_facility_certificate,
    a.status,
    a.created_at,
    a.created_by,
    a.deleted
FROM tsfda_applications a
LEFT JOIN tusers u ON a.idclient = u.id
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
            $row['company_name'],
            $row['address'],
            $row['commercial_registration_certificate'],
            $row['commercial_registration_no'],
            $row['vat_number'],
            $row['accreditation_certificates'],
            $row['accreditation_certificates_other'],
            $row['number_of_production_lines'],
            $row['number_of_critical_points'],
            $row['number_of_full_time_employees'],
            $row['number_of_shifts'],
            $row['number_of_shift_employees'],
            $row['production_area_space_m2'],
            $row['additional_branches_of_the_company'],
            $row['upload_product_information'],
            $row['validity_of_certificate_period'],
            $row['invoice'],
            $row['proof_of_payment'],
            $row['sfda_facility_certificate'],
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
