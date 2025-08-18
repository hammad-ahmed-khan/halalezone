<?php
include_once 'config/config.php';
include_once 'classes/users.php';
include_once 'pages/patterns.php';
include_once 'includes/func.php';

if (isset($_GET['avnts'])) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	$db = acsessDb :: singleton();
	$dbo =  $db->connect();

	$sql = "SELECT * FROM `tusers` u WHERE isclient=1 AND (IFNULL(last_login_sent, '')) = '' AND pass IS NOT NULL";
	$stmt = $dbo->prepare($sql);
	$stmt->execute();
	
	// Loop through the selected rows
	while ($row1 = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$sql2 = "SELECT * FROM `tapplications` a WHERE idclient = :idclient ORDER BY id DESC LIMIT 1";
		$stmt2 = $dbo->prepare($sql2);
		$stmt2->bindParam(':idclient', $row1['id'], PDO::PARAM_INT);
		$stmt2->execute();
		
		// Loop through the selected rows from tapplications
		while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
			$sql3 = "SELECT * FROM `tdocs` a WHERE idclient = :idclient AND idapp = :idapp AND category = 'soffer' ORDER BY id DESC LIMIT 1";
			$stmt3 = $dbo->prepare($sql3);
			$stmt3->bindParam(':idclient', $row1['id'], PDO::PARAM_INT);
			$stmt3->bindParam(':idapp', $row2['id'], PDO::PARAM_INT);
			$stmt3->execute();
			
			// Loop through the selected rows from tdocs
			while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
				print $row1["name"] . ' => ' . $row3["created_at"] . '<br/>';
				
				// Update the last_login_sent field in tusers
				$sql4 = "UPDATE `tusers` SET last_login_sent = :last_login_sent WHERE id = :id";
				$stmt4 = $dbo->prepare($sql4);
				$stmt4->bindParam(':last_login_sent', $row3["created_at"], PDO::PARAM_STR);
				$stmt4->bindParam(':id', $row1['id'], PDO::PARAM_INT);
				$stmt4->execute();
			}
		}
	}
	
	exit;

	require __DIR__ . '/vendor/autoload.php';
	$serviceAccountFilePath =  __DIR__ . '/config/google/'.$serviceAccountFileName;
	$client = new Google_Client();
	$client->setAuthConfig($serviceAccountFilePath);
	$client->addScope(Google_Service_Calendar::CALENDAR);				
	// Authenticate with the service account
	if ($client->isAccessTokenExpired()) {
		$client->fetchAccessTokenWithAssertion();
	}
	// Create a new Calendar service
	$service = new Google_Service_Calendar($client);

	// Select all rows from tevents where gcal_id is null or empty
	$sql = "SELECT * FROM tevents WHERE gcal_id IS NULL";
	$stmt = $dbo->prepare($sql);
	$stmt->execute();

	// Loop through the selected rows
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		/*
		try {
		$gcal_id = $row['gcal_id'];

 

		$service->events->delete($calendarId, $gcal_id);
 	}
	catch (Exception $ex) {

	}
		
		continue;
	
	
		// Convert start and end dates to RFC3339 format
	$start_date_rfc3339 = date('c', strtotime($row['start_date'].' 00:00:00'));
		$end_date_rfc3339 = date('c', strtotime($row['start_date'].' 23:59:59'));
	
		echo $start_date_rfc3339 . ' - ' . $end_date_rfc3339 . '<br />';

		$events = $service->events->listEvents($calendarId, array('timeMin'=>$start_date_rfc3339, 'timeMax'=>$end_date_rfc3339));

		echo '<pre>';
		print_r($events);
		echo '</pre>';

		echo '<br/>';

		continue;
		*/

		// Determine colorId based on conditions
		if ($row['idclient'] == -1) {
			$colorId = 1;
		} elseif ($row['status'] == '0') {
			$colorId = 6;
		} else {
			$colorId = 10;
		}
 

		// Create a Google Calendar event
		$event = new Google_Service_Calendar_Event($data = array(
			'summary' => $row['title'],
			'start' => array(
				'date' => $row['start_date'],
				'timeZone' => $defaultTimezone,
			),
			'end' => array(
				'date' => $row['start_date'],
				'timeZone' => $defaultTimezone,
			),
			'colorId' => $colorId, // Set the color ID based on conditions
		));

		echo '<pre>';
		print_r($data);
		echo '</pre>';

		// Insert the event into Google Calendar
		try {
			$event = $service->events->insert($calendarId, $event);
			$gcal_id = $event->id;

			// Update gcal_id in tevents table
			$updateSql = "UPDATE tevents SET gcal_id = :gcal_id WHERE id = :id";
			$updateStmt = $dbo->prepare($updateSql);
			$updateStmt->bindValue(':gcal_id', $gcal_id);
			$updateStmt->bindValue(':id', $row['id']);
			$updateStmt->execute();
		} catch (Exception $e) {
			// Handle any errors that occur during event insertion
			echo 'An error occurred: ' . $e->getMessage();
		}
 	}

}
	$myuser = cuser::singleton();
	$myuser->sec_session_start();

$module = 'Not_Found';
$action = 'main';

//$base = 'fl/halal/';
$base = '';

$params = array();

$url_path = str_replace($base, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
foreach ($routes as $map)
{
	if (preg_match($map['pattern'], $url_path, $matches))
	{
		// Выталкиваем первый элемент - он содержит всю строку URI запроса
		// и в массиве $params он не нужен.
		array_shift($matches);

		// Формируем массив $params с теми названиями ключей переменных,
		// которые мы указали в $routes
 		foreach ($matches as $index => $value)
		{
			$params[$map['aliases'][$index]] = $value;
		}

		$module = $map['class'];
		$action = $map['method'];

		break;
	}
}

$myuser = cuser::singleton();
	switch($action){
			case 'upload':	$myuser->showUpload();
					break;
		case 'login':	$myuser->showIndex();
					break;
		case 'register':  $myuser->showRegister();
					break;
		case 'main':	$myuser->showIndex();
				break;
		case '':	$myuser->showIndex();
				break;
        case 'products':	$myuser->showProducts($_GET);
				break;
 		case 'groups':	$myuser->showGroups();
				break;
		case 'application':	$myuser->showApplication();
				break;
		case 'application1': $myuser->showApplication1();
				break;
		case 'calendar':	$myuser->showCalendar();
				break;
		case 'ingredients':	$myuser->showIngredients($_GET);
					break;
		case 'qm':	$myuser->showQM($_GET);
					break;
		case 'audit':	$myuser->showAudit();
					break;
		case 'administration':	$myuser->showAdministration();
					break;
		case 'companies':	$myuser->showCompanies();
					break;
		case 'paIngreds':	$myuser->showPaIngreds();
					break;
		case 'settings':	$myuser->showSettings();
					break;
		case 'processStatus':	$myuser->showProcessStatus();
					break;
		case 'tickets':	$myuser->showTickets();
					break;
		case 'customerService':  $myuser->showCustomerService();
					break;
		case 'tasks':	$myuser->showTasks();
					break;
		case 'facilities':  $myuser->showFacilities();
				break;
		case 'preferences':  $myuser->showPreferences();
				break;
		case 'branches':	$myuser->showBranches();
				break;
		case 'training':	$myuser->showTraining();
				break;
		case 'faqManager':	$myuser->showFAQManager();
				break;
        default:
				header('HTTP/1.0 404 Not Found');
				include_once ('pages/header.php');
				include_once ('pages/404.php');
				break;
    }

	
?>