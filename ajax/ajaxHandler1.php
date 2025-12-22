<?php
include_once "../config/config.php";
include_once "../classes/users.php";
include_once "../notifications/notifyfuncs.php";
include_once "../includes/func.php";
require('../vendor/autoload.php');

define('LOCAL_FILE_DIR','files');
define('DRIVE_FILE_DIR','CRM');

//ini_set('max_execution_time', 5000);
//ini_set('safe_mode', 'off');

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

if (isset($_GET["daccess"])) {
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Path to your service account JSON file
$serviceAccountFilePath =  __DIR__ . '/../config/google/bold-zoo-418614-92b70bb181ba.json';

if (file_exists($serviceAccountFilePath)) { 
}

// Initialize the Google Client
$client = new Google_Client();
$client->setAuthConfig($serviceAccountFilePath);
$client->addScope(Google_Service_Calendar::CALENDAR);

// Authenticate with the service account
if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithAssertion();
}

// Create a new Calendar service
$service = new Google_Service_Calendar($client);
 

// Define the event details
$event = new Google_Service_Calendar_Event(array(
    'summary' => 'Sample Event',
    'description' => 'This is a sample event description.',
    'start' => array(
        'dateTime' => '2024-03-30T10:00:00', // Start time of the event (in RFC3339 format)
        'timeZone' => 'Europe/Vienna', // Timezone of the event
    ),
    'end' => array(
        'dateTime' => '2024-03-30T12:00:00', // End time of the event (in RFC3339 format)
        'timeZone' => 'Europe/Vienna', // Timezone of the event
    ),
));

$calendarList = $service->calendarList->listCalendarList();

// Iterate through each calendar
foreach ($calendarList->getItems() as $calendarListEntry) {
    // Print out the calendar ID and summary
    echo "Calendar ID: " . $calendarListEntry->getId() . ", Summary: " . $calendarListEntry->getSummary() . "\n";
}

// Specify the calendar ID where you want to add the event
$calendarId = 'hqc.at_lvvl0fvpi0ihtbn0lf02l0subs@group.calendar.google.com'; // Use 'primary' for the primary calendar associated with the service account

// Add the event to the calendar
$event = $service->events->insert($calendarId, $event);


echo $event->id;


//echo 'Event created: ' . $event->htmlLink;	
}