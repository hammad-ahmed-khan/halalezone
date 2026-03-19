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
    $product_name = getPostParam('product_name');
    $contact_person = getPostParam('contact_person');
    $status = getPostParam('status');
    $date_from = getPostParam('date_from');
    $date_to = getPostParam('date_to');

    // Handle special idclient values
    $idsfdaapp = getGetParam('idsfdaapp');

    if(!is_numeric(getGetParam('displaymode'))) $displaymode = 0;
    else $displaymode = getGetParam('displaymode');

    // Build base filter
    $filter = "WHERE IFNULL(a.company_name, '') <> '' AND a.deleted = $displaymode";

    // Apply client filter
    if ($idsfdaapp != "") {
        $filter .= " AND a.idsfdaapp = " . intval($idsfdaapp);
    }

    // Handle search parameters
    $searching = $_POST['_search'] ?? false;
    if ($searching) {
        if ($company_name != '') {
            $filter .= ' AND a.company_name LIKE "%' . $company_name . '%"';
        }
        if ($product_name != '') {
            $filter .= ' AND a.product_name LIKE "%' . $product_name . '%"';
        }
        if ($contact_person != '') {
            $filter .= ' AND a.contact_person LIKE "%' . $contact_person . '%"';
        }
        if ($status != '') {
            $filter .= ' AND a.status = "' . $status . '"';
        }
        if ($date_from != '') {
            $filter .= ' AND a.created_at >= "' . date('Y-m-d', strtotime($date_from)) . '"';
        }
        if ($date_to != '') {
            $filter .= ' AND a.created_at <= "' . date('Y-m-d', strtotime($date_to)) . '"';
        }
    }

    // Get total count of records
    $countSql = 'SELECT COUNT(a.id) AS count FROM tsfda_shipment_certificates a
LEFT JOIN tusers u ON a.idclient = u.id '. $filter;

    $countStmt = $dbo->prepare($countSql);
    $countStmt->execute();
    $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC);

    // Prepare pagination
    $rowsPerPage = intval($rowsPerPage) === 1000000 ? 0 : intval($rowsPerPage);
    $curPage = $rowsPerPage ? $curPage : 1;
    $firstRowIndex = $rowsPerPage ? ($curPage - 1) * $rowsPerPage : 0;
    $limit = $rowsPerPage ? "LIMIT $firstRowIndex, $rowsPerPage" : '';

    // Main query to get SFDA shipment certificates data
    $sql = <<<EOL
SELECT 
    a.id,
    a.idclient,
    u.name as client_name, 
    a.company_name,
    a.contact_person,
    a.email,
    a.iidc_certificate_no,
    a.product_name,
    a.article_number,
    a.halal_digital_hcp_no,
    a.commercial_registration_no_importeur,
    a.shipping_method,
    a.shipping_port,
    a.port_of_entry,
    a.quantity,
    a.total_actual_weight_brutto,
    a.production_date,
    a.expiry_date,
    a.additional_documents,
    a.invoice,
    a.proof_of_payment,
    a.sfda_shipment_certificate,
    a.status,
    a.created_at,
    a.updated_at,
    a.deleted
FROM tsfda_shipment_certificates a
LEFT JOIN tusers u ON a.idclient = u.id
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
            $row['idclient'],
            $row['client_name'],
            $row['company_name'],
            $row['contact_person'],
            $row['email'],
            $row['iidc_certificate_no'],
            $row['product_name'],
            $row['article_number'],
            $row['halal_digital_hcp_no'],
            $row['commercial_registration_no_importeur'],
            $row['shipping_method'],
            $row['shipping_port'],
            $row['port_of_entry'],
            $row['quantity'],
            $row['total_actual_weight_brutto'],
            $row['production_date'],
            $row['expiry_date'],
            $row['additional_documents'],
            $row['invoice'],
            $row['proof_of_payment'],
            $row['sfda_shipment_certificate'],
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
