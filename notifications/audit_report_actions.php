<?php

/*
//  cron-job script for email notifications on:
//  - all non complete tasks list sent to a client - on Monday only
//  - email to admin on newly completed task by a client
//  - list of all complete actions and not confirmed sent to admin
//  - list af all confirmed actions to client
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once(dirname(__FILE__).'/vendor/autoload.php');
require_once(dirname(__FILE__).'/../config/config.php');
require_once(dirname(__FILE__).'/../notifications/notifyfuncs.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs',Psr\Log\LogLevel::DEBUG,array('filename'=>'notifier.log'));
//$httpprefix = 'http://localhost/fl/halal/';
$httpprefix = 'https://halal-e.zone/';
$adminEmailAddress = "halal.ezone@gmail.com";
$supportEmailAddress = "communication@hqc.at";
$ownerEmailAddress = "ovchinnikov.it@gmail.com";

// JOB Script
$logger->info('Notification script started');
$db = acsessDb :: singleton();
try {
    $dbo =  $db->connect();
} catch (Exception $e) {
    $GLOBALS['logger']->error($e->getMessage());
    $logger->info('Notification script ended');
    die();
}

// First, get all users who are clients (isclient=1)
$sqlUsers = "SELECT * FROM tusers WHERE deleted = 0 AND isclient = 1";
$stmtUsers = $dbo->prepare($sqlUsers);
$stmtUsers->execute();

// Initialize an array to store results
$results = [];

while ($user = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
    $userId = $user['id'];

    // Iterate over the applications (should be only one due to LIMIT 1)
    $sqlApplications = "SELECT * FROM tapplications WHERE 1 AND idclient = :idclient1 AND idcycle=(SELECT id FROM tcycles WHERE idclient=:idclient2 AND `state`=1) ORDER BY id DESC LIMIT 0, 1";
    $stmtApplications = $dbo->prepare($sqlApplications);
    $stmtApplications->bindParam(':idclient1', $userId, PDO::PARAM_INT);
    $stmtApplications->bindParam(':idclient2', $userId, PDO::PARAM_INT);
    $stmtApplications->execute();

    while ($application = $stmtApplications->fetch(PDO::FETCH_ASSOC)) {
        $applicationId = $application['id'];
        
        // Fetch audit report deviations that have not been notified yet
        $sql = "SELECT * FROM tauditreport WHERE idclient = :idclient AND idapp = :idapp AND AdminNotified = 0";
        $stmt = $dbo->prepare($sql);
        $stmt->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
        $stmt->bindParam(':idclient', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $deviations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($deviations)) {
            
            // Set email parameters
            $ownerEmailAddress = "halal.ezone@gmail.com";
            $fromEmailAddress = "noreply@halal-e.zone";
            $adminEmailAddress = $ownerEmailAddress; // Assuming adminEmailAddress should be set to ownerEmailAddress

            $body = [];
            $body['name'] = 'Halal e-Zone';
            $body['email'] = $fromEmailAddress;
            $body['to'] = $supportEmailAddress;
            $body['subject'] = "Halal e-Zone - Audit Report Update - " . $user["name"];
            $body['header'] = "";
            $body['body'] = "Dear Admin,";
            $body['body'] .= "<br /><br />";
            $body['body'] .= "<strong>" . $user["name"] . "</strong> has added the following corrective actions to the audit report.";
            $body['body'] .= "<br /><br />";
            
            foreach ($deviations as $deviation) {
                $body['body'] .= "Type of Finding: " . $deviation['Type'];
                $body['body'] .= "<br />";
                $body['body'] .= "NCR/OBS Statement: " . $deviation['Deviation'];
                $body['body'] .= "<br />";
                $body['body'] .= "Reference to Checklist: " . $deviation['Reference'];
                $body['body'] .= "<br /><br />";
                $body['body'] .= "Root Cause Analysis: " . $deviation['RootCause'];
                $body['body'] .= "<br />";
                $body['body'] .= "Proposed Corrective Action: " . $deviation['Measure'];
                $body['body'] .= "<br />";
                $body['body'] .= "Deadline: " . $deviation['Deadline'];
                $body['body'] .= "<br /><br />";
            }
            
            $body['body'] .= "Regards,";
            $body['body'] .= "<br/>";
            $body['body'] .= "Halal e-Zone";
            
            // Call function to send email
            sendEmail($body);
            
            // Update audit report to mark as notified
            $sqlUpdate = "UPDATE tauditreport SET AdminNotified = 1 WHERE idclient = :idclient AND idapp = :idapp AND AdminNotified = 0";
            $stmtUpdate = $dbo->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':idclient', $userId, PDO::PARAM_INT);
            $stmtUpdate->execute();
        }
    }
}


$logger->info('Notification script ended');

echo 'Notification script ended';
?>