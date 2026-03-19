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
    $country_of_origin = getPostParam('country_of_origin');
    $status = getPostParam('status');
    $date_from = getPostParam('date_from');
    $date_to = getPostParam('date_to');

    // Handle special idhalal_slaughtering values
    $idhalal_slaughtering = getGetParam('idhalal_slaughtering');

    if(!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');

    // Build base filter
    $filter = "WHERE b.deleted = $displaymode";

    // Apply parent filter
    if ($idhalal_slaughtering != "") {
        $filter .= " AND b.idhalal_slaughtering = " . intval($idhalal_slaughtering);
    }

    // Handle search parameters
    $searching = $_POST['_search'] ?? false;
    if ($searching) {
        if ($country_of_origin != '') {
            $filter .= ' AND b.country_of_origin LIKE "%' . $country_of_origin . '%"';
        }
        if ($status != '') {
            $filter .= ' AND b.status = "' . $status . '"';
        }
        if ($date_from != '') {
            $filter .= ' AND b.date >= "' . date('Y-m-d', strtotime($date_from)) . '"';
        }
        if ($date_to != '') {
            $filter .= ' AND b.date <= "' . date('Y-m-d', strtotime($date_to)) . '"';
        }
    }

    // Get total count of records
    $countSql = 'SELECT COUNT(b.id) AS count FROM thalal_batch_certificates b
LEFT JOIN tusers u ON b.idclient = u.id '. $filter;

    $countStmt = $dbo->prepare($countSql);
    $countStmt->execute();
    $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC);

    // Prepare pagination
    $rowsPerPage = intval($rowsPerPage) === 1000000 ? 0 : intval($rowsPerPage);
    $curPage = $rowsPerPage ? $curPage : 1;
    $firstRowIndex = $rowsPerPage ? ($curPage - 1) * $rowsPerPage : 0;
    $limit = $rowsPerPage ? "LIMIT $firstRowIndex, $rowsPerPage" : '';

    // Main query to get Halal Batch Certificates data
    $sql = <<<EOL
SELECT 
    b.id,
    b.idhalal_slaughtering,
    b.idclient,
    u.name as client_name,
    b.date,
    b.country_of_origin,
    b.quality,
    b.net_weight_kg,
    b.gross_weight_kg,
    b.transport_by,
    b.awb_voyage_flight_no,
    b.loading_port,
    b.destination,
    b.exporter_name,
    b.exporter_address,
    b.importer_name,
    b.importer_address,
    b.upload_product_information,
    b.upload_consignment_details,
    b.invoice,
    b.proof_of_payment,
    b.halal_batch_certificate,
    b.status,
    b.created_at,
    b.updated_at,
    b.deleted
FROM thalal_batch_certificates b
LEFT JOIN tusers u ON b.idclient = u.id
{$filter}
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
            $row['idhalal_slaughtering'],
            $row['idclient'],
            $row['client_name'],
            $row['date'],
            $row['country_of_origin'],
            $row['quality'],
            $row['net_weight_kg'],
            $row['gross_weight_kg'],
            $row['transport_by'],
            $row['awb_voyage_flight_no'],
            $row['loading_port'],
            $row['destination'],
            $row['exporter_name'],
            $row['exporter_address'],
            $row['importer_name'],
            $row['importer_address'],
            $row['upload_product_information'],
            $row['upload_consignment_details'],
            $row['invoice'],
            $row['proof_of_payment'],
            $row['halal_batch_certificate'],
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