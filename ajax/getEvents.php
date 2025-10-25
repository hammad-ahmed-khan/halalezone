<?php
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../notifications/notifyfuncs.php";
include_once "../includes/func.php";
include_once "../reports/reports.php";


try {
	$db = acsessDb :: singleton();
	$dbo =  $db->connect(); // Database connection
	
	$myuser = cuser::singleton();
	$myuser->getUserData();

	$criteria = "";
	$params = array();
	
	// Get filter parameters from both POST and GET
	$idclient = $_POST["idclient"] ?? $_GET["idclient"] ?? "";
	if ($idclient === "" || $idclient === "-1") {
		$idclient = -1; // Default to show all
	}

    if ($myuser->userdata['isclient'] == '1') { 
        // Client users: only their events + general events
        $criteria .= " AND (e.idclient = :user_client_id OR e.idclient <= 0 OR e.idclient IS NULL)";
        $params[':user_client_id'] = $myuser->userdata['id'];
    } elseif ($myuser->userdata['isclient'] == '2') {
        // Auditor users: only assigned clients + general events
       if ($idclient > 0) {
			$criteria .= " AND (e.idclient IN (
				SELECT u.id FROM tusers u 
				WHERE u.isclient = 1 
				AND JSON_CONTAINS(:auditor_clients_audit, JSON_QUOTE(CAST(u.id AS CHAR)), '$')
			) OR e.idclient <= 0 OR e.idclient IS NULL)";
			$params[':auditor_clients_audit'] = $myuser->userdata['clients_audit'] ?? '[]';
		}
    } else {
        // Admin users: apply client filter if specified
        if ($idclient > 0) {
            $criteria .= " AND e.idclient = :filter_client_id";
            $params[':filter_client_id'] = intval($idclient);
        }
    }
	
	// **NEW: Auditor filter**
	$idauditor = $_POST["idauditor"] ?? $_GET["idauditor"] ?? "";
	if ($idauditor === "" || $idauditor === "-1") {
		$idauditor = -1; // Default to show all
	}
	
	// **NEW: Time period filters**
	$date_from = $_POST["date_from"] ?? $_GET["date_from"] ?? "";
	$date_to = $_POST["date_to"] ?? $_GET["date_to"] ?? "";
	
	// FullCalendar also sends start/end parameters
	$calendar_start = $_POST["start"] ?? $_GET["start"] ?? "";
	$calendar_end = $_POST["end"] ?? $_GET["end"] ?? "";
	
	$idapp = $_POST["idapp"] ?? $_GET["idapp"] ?? "";
	if ($idapp === "" || $idapp === "-1") {
		$idapp = -1;
	}
	
	$category = trim($_POST["category"] ?? $_GET["category"] ?? "");
	$actions = trim($_POST["actions"] ?? $_GET["actions"] ?? "");
	$deleted = $_POST["deleted"] ?? $_GET["deleted"] ?? '0';		
	
	

	// **ENHANCED: Apply client-specific filtering logic**
	if ($myuser->userdata['isclient'] == '1') { 
		// If user is a client, only show their events or general events
		$criteria .= " AND (e.idclient = :user_client_id OR e.idclient <= 0 OR e.idclient IS NULL)";
		$params[':user_client_id'] = $myuser->userdata['id'];
	} else {
		// If user is admin/auditor, apply the client filter if specified
		if ($idclient > 0) {
			$criteria .= " AND e.idclient = :filter_client_id";
			$params[':filter_client_id'] = intval($idclient);
		}
		// If idclient is -1 or not specified, show all events (no additional criteria)
	}

	// **NEW: Apply auditor filter**
	if ($idauditor > 0) {
		// Find events for clients that are assigned to this auditor
		$criteria .= " AND e.idclient IN (
			SELECT u.id FROM tusers u 
			WHERE u.isclient = 1 
			AND EXISTS (
				SELECT 1 FROM tusers auditor 
				WHERE auditor.id = :auditor_id 
				AND auditor.isclient = 2 
				AND JSON_CONTAINS(auditor.clients_audit, JSON_QUOTE(CAST(u.id AS CHAR)), '$')
			)
		)";
		$params[':auditor_id'] = intval($idauditor);
	}

	// **NEW: Apply time period filters**
	// Priority: Custom date range > FullCalendar range
	
	if (!empty($date_from) && !empty($date_to)) {
		// Custom date range filter
		$criteria .= " AND e.start_date >= :date_from AND e.start_date <= :date_to";
		$params[':date_from'] = $date_from;
		$params[':date_to'] = $date_to;
	} elseif (!empty($calendar_start) && !empty($calendar_end)) {
		// FullCalendar sends ISO dates, convert to MySQL date format
		$start_date = date('Y-m-d', strtotime($calendar_start));
		$end_date = date('Y-m-d', strtotime($calendar_end));
		$criteria .= " AND e.start_date >= :calendar_start AND e.start_date <= :calendar_end";
		$params[':calendar_start'] = $start_date;
		$params[':calendar_end'] = $end_date;
	}
	
	// **ENHANCED: Main query with LEFT JOIN to get auditor info**
 $query = "SELECT 
		e.id, 
		e.idapp, 
		e.idclient, 
		e.title, 
		e.start_date, 
		e.end_date, 
		e.status,
		u.name as client_name,
		u.prefix as client_prefix
	FROM tevents AS e
	LEFT JOIN tusers u ON e.idclient = u.id AND u.isclient = 1
	WHERE 1 = 1
	$criteria
	ORDER BY e.start_date ASC";
	
	$stmt = $dbo->prepare($query);
	
	// Bind all parameters
	foreach ($params as $key => $value) {
		$stmt->bindValue($key, $value);
	}
	
	$stmt->execute();
	$events = $stmt->fetchAll(PDO::FETCH_ASSOC);  
	
	$data = array();
	foreach($events as $e) {
		// Determine event color based on status and type
		if ($e["idclient"] == '-1' || $e["idapp"] == '-1') {
			$color = '#09F'; // Blue for general events/holidays
		}
		else if ($e["status"] == '1') {
			$color = '#0C0'; // Green for approved events
		}
		else {
			$color = '#F60'; // Orange for proposed events
		}
		
		// **ENHANCED: Get auditor information more efficiently**
		$auditor_name = "";
		$client_display = "";
		
		if ($e["idclient"] != '-1') {
			$json_id = json_encode([$e["idclient"]]);
			$sql = "SELECT name FROM tusers WHERE isclient=2 AND JSON_CONTAINS(clients_audit, :json_id) > 0 LIMIT 0, 1";
			$stmt = $dbo->prepare($sql);
			$stmt->bindParam(':json_id', $json_id);
			$stmt->execute();
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			// Check if a result was found
			if ($result) {
				$auditor_name = $result['name'] . ' - ';
			}		
		}

		// **ENHANCED: Event data with additional information**
		$data[] = array(
			'id' => $e["id"],
			'idclient' => $e["idclient"],
			'idapp' => $e["idapp"],
			'title' => str_replace("Auditor_", "", str_replace("Auditor ", "", $auditor_name)) . $e["title"],
			'start' => $e["start_date"],
			'end' => $e["end_date"],
			'status' => $e["status"],
			'color' => $color,
			'backgroundColor' => $color,
			'borderColor' => $color,
			// Additional properties for filtering/display
			'extendedProps' => array(
				'idclient' => $e["idclient"],
				'idapp' => $e["idapp"],
				'status' => $e["status"],
				'auditor_name' => trim(str_replace(" - ", "", $auditor_name)),
				'client_name' => $client_display,
				'original_title' => $e["title"]
			)
		);	
	}
	
	// **NEW: Add debug information if needed**
	if (isset($_GET['debug']) && $_GET['debug'] == '1') {
		$debug_info = array(
			'query' => $query,
			'params' => $params,
			'filters' => array(
				'idclient' => $idclient,
				'idauditor' => $idauditor,
				'date_from' => $date_from,
				'date_to' => $date_to,
				'calendar_start' => $calendar_start,
				'calendar_end' => $calendar_end
			),
			'user_type' => $myuser->userdata['isclient'],
			'user_id' => $myuser->userdata['id']
		);
		echo json_encode(array('events' => $data, 'debug' => $debug_info));
	} else {
		echo json_encode($data);
	}
}
catch (PDOException $e) {
    echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
}
catch (Exception $e) {
    echo json_encode(array('error' => 'Error: ' . $e->getMessage()));
}
?>