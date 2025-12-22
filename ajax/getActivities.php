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
    $sortingField = $_POST['sidx'] ?? 'date_of_service';
    $sortingOrder = $_POST['sord'] ?? 'desc';

    // Get filter parameters
    $company_name = getPostParam('company_name');
    $service_type = getPostParam('service_type');
    $invoice_number = getPostParam('invoice_number');
    $paid_status = getPostParam('paid_status');
    $date_from = getPostParam('date_from');
    $date_to = getPostParam('date_to');

    // Handle special idauditor values
    $idauditor = getGetParam('idauditor');
    if (!is_numeric($idauditor)) {
        $idauditor = -1;
    }

    // Special case: return empty result set
    if ($idauditor === -2) {
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
    $filter .= "WHERE IFNULL(a.company_name, '') <> '' AND a.deleted = $displaymode";

	// Apply auditor filter
    if ($idauditor != -1) {
        $filter .= " AND a.idauditor = " . intval($idauditor);
    }

    // Handle search parameters
    $searching = $_POST['_search'] ?? false;
    if ($searching) {
        if ($company_name != '') {
            $filter .= ' AND a.company_name LIKE "%' . $company_name . '%"';
        }
        if ($service_type != '') {
            $filter .= ' AND a.service_type = "' . $service_type . '"';
        }
        if ($invoice_number != '') {
            $filter .= ' AND (a.invoice_number_inbound LIKE "%' . $invoice_number . '%" OR 
                             a.invoice_number_outbound LIKE "%' . $invoice_number . '%")';
        }
        if ($paid_status != '') {
            $filter .= ' AND a.paid = "' . $paid_status . '"';
        }
        if ($date_from != '') {
            $filter .= ' AND a.date_of_service >= "' . date('Y-m-d', strtotime($date_from)) . '"';
        }
        if ($date_to != '') {
            $filter .= ' AND a.date_of_service <= "' . date('Y-m-d', strtotime($date_to)) . '"';
        }
    }

    // Get total count of records
    $countSql = 'SELECT COUNT(a.id) AS count FROM ttrainer_activities a 
                 LEFT JOIN companies c ON a.idauditor = c.id ' . $filter;
    $countStmt = $dbo->prepare($countSql);
    $countStmt->execute();
    $totalRows = $countStmt->fetch(PDO::FETCH_ASSOC);

    // Prepare pagination
    $rowsPerPage = intval($rowsPerPage) === 1000000 ? 0 : intval($rowsPerPage);
    $curPage = $rowsPerPage ? $curPage : 1;
    $firstRowIndex = $rowsPerPage ? ($curPage - 1) * $rowsPerPage : 0;
    $limit = $rowsPerPage ? "LIMIT $firstRowIndex, $rowsPerPage" : '';

    // Main query to get activity data
    $sql = <<<EOL
SELECT 
    a.id,
    a.idauditor,
    u.name, 
    a.company_name,
    a.date_of_service,
    a.service_type,
    a.auditor_type,
    a.invoice_number_inbound,
    a.invoice_date_inbound,
    a.invoice_inbound,
    a.travel_expenses,
    a.travel_invoices,    
    a.paid_on,
    a.invoice_number_outbound,    
    a.paid,
    a.training_request_form,
    a.attendance_list,
    a.customer_feedback_form,
    a.attendance_certificates,
    a.note,
    a.created_at,
    a.updated_at,
    a.deleted
FROM ttrainer_activities a
LEFT JOIN tusers u ON a.created_by = u.id
{$filter}
ORDER BY {$sortingField} {$sortingOrder}
{$limit}
EOL;

//echo $sql;

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
            $row['idauditor'],
            $row['name'],
            $row['company_name'],
            $row['date_of_service'],
            $row['service_type'],
            $row['auditor_type'], 
            $row['invoice_number_inbound'],
            $row['invoice_date_inbound'],
            $row['invoice_inbound'],
            $row['travel_expenses'],
            $row['travel_invoices'],
            $row['paid_on'],
            $row['invoice_number_outbound'],
            $row['paid'],
            $row['training_request_form'],
            $row['attendance_list'],
            $row['customer_feedback_form'],
            $row['attendance_certificates'],
			$row['deleted'],
        ];
        $i++;
    }

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>