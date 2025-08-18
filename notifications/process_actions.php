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

// First, get all users who are clients (isclient=2)
$sqlUsers = "SELECT * FROM tusers WHERE deleted = 0 AND isclient=1";
$stmtUsers = $dbo->prepare($sqlUsers);
$stmtUsers->execute();

// Initialize an array to store results
$results = [];
$missingInvoices = [];
$missingTravelExpInvoices = [];
$expiringCertificates = [];

while ($user = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
    $userId = $user['id'];
  //  $json_id = json_encode([$userId]);
//    $sqlAuditors = "SELECT * FROM tusers WHERE isclient = 2 AND JSON_CONTAINS(clients_audit, :json_id) LIMIT 1";

    $json_id = json_encode((string) $userId); // Convert to string and JSON encode
    $sqlAuditors = "SELECT *
            FROM tusers 
            WHERE isclient = 2 
            AND deleted = 0  
            AND JSON_CONTAINS(clients_audit, :json_id, '$') LIMIT 1";		

    $stmtAuditors = $dbo->prepare($sqlAuditors);
    $stmtAuditors->bindParam(':json_id', $json_id, PDO::PARAM_STR);
    $stmtAuditors->execute();
    $auditor = $stmtAuditors->fetch(PDO::FETCH_ASSOC);
    // For each client, get the most recent application
    $sqlApplications = "SELECT * FROM tapplications WHERE 1 AND idclient = :idclient1 AND idcycle=(SELECT id FROM tcycles WHERE idclient=:idclient2 AND `state`=1) ORDER BY id DESC LIMIT 0, 1";
    $stmtApplications = $dbo->prepare($sqlApplications);
    $stmtApplications->bindParam(':idclient1', $userId, PDO::PARAM_INT);
    $stmtApplications->bindParam(':idclient2', $userId, PDO::PARAM_INT);
    $stmtApplications->execute();
    while ($application = $stmtApplications->fetch(PDO::FETCH_ASSOC)) {
        $applicationId = $application['id'];
        $applicationState = $application['state'];
        $proposedDate1 = $application['audit_date_1'];
        $proposedDate2 = $application['audit_date_2'];
        $proposedDate3 = $application['audit_date_3'];
        $approvedDate = $application['approved_date1'];
        $lastModified = $application['last_activity_date'];            

        // Check if approved_date is null or blank and one week has passed since last_modified
        if (empty($approvedDate) && ($proposedDate1 != "" || $proposedDate2 != "" || $proposedDate3 != "") && (strtotime($lastModified) < strtotime('-1 week'))) {
            $ownerEmailAddress = "halal.ezone@gmail.com";
            $fromEmailAddress = "noreply@halal-e.zone";
            $body = [];
            $body['name'] = 'Halal e-Zone';
            $body['email'] =  $fromEmailAddress;
            $body['to'] = $auditor ? $auditor["email"] : $supportEmailAddress;
            // sending notification
            $body['subject'] = "Halal e-Zone - Pending Approval of Audit Date - " . $user["name"];
            $body['header'] = "";
            $body['body'] = "Dear " . ($auditor ? $auditor["name"] : "Admin") . ",";
            $body['body'] .= "<br /><br />";
            $body['body'] .= "This is a reminder that it has been one week since the client \"".$user["name"]."\" submitted their preferred audit dates through Halal e-Zone, and the audit date has not yet been approved. Please review the submitted dates and confirm at your earliest convenience to ensure a smooth process for the client.";
            $body['body'] .= "<br /><br />";
            $body['body'] .= "The proposed audit dates by the client are as follows:";
            $body['body'] .= "<br />";
            $body['body'] .= "1. " . date('m/d/Y', strtotime($proposedDate1));
            $body['body'] .= "<br />";
            $body['body'] .= "2. " . date('m/d/Y', strtotime($proposedDate2));
            $body['body'] .= "<br />";
            $body['body'] .= "3. " . date('m/d/Y', strtotime($proposedDate3));
            $body['body'] .= "<br /><br />";                
            $body['body'] .= "Kind Regards,";
            $body['body'] .= "<br/>";
            $body['body'] .= "Your HQC supporting Team";
            sendEmail($body);
        }

        if ($applicationState == 'dates' || $applicationState == 'audit'|| $applicationState == 'checklist') {

          if (!empty($approvedDate)) {
                $sqlDocs = "SELECT * FROM tdocs WHERE deleted = 0 AND idclient = :idclient AND idapp = :idapp AND category = 'audit' LIMIT 0, 1";
                $stmtDocs = $dbo->prepare($sqlDocs);
                $stmtDocs->bindParam(':idclient', $userId, PDO::PARAM_INT);
                $stmtDocs->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
                $stmtDocs->execute();            
                $docs = $stmtDocs->fetch(PDO::FETCH_ASSOC);
                if ( !$docs ) {
                    $approvedTimestamp = strtotime($approvedDate);
                    $tenDaysBeforeApproved = strtotime('-10 days', $approvedTimestamp);
                    if (time() >= $tenDaysBeforeApproved) {
                        $ownerEmailAddress = "halal.ezone@gmail.com";
                        $fromEmailAddress = "noreply@halal-e.zone";
                        $body = [];
                        $body['name'] = 'Halal e-Zone';
                        $body['email'] =  $fromEmailAddress;
                        $body['to'] = $auditor ? $auditor["email"] : $adminEmailAddress;
                        $body['subject'] = "Halal e-Zone - Audit Plan Pending - " . $user["name"];
                        $body['header'] = "";
                        $body['body'] = "Dear " . ($auditor ? $auditor["name"] : "Admin"). ",";
                        $body['body'] .= "<br /><br />";
                        $body['body'] .= "This is a reminder that the audit plan for the upcoming audit of client \"".$user["name"]."\" on " . date('m/d/Y', $approvedTimestamp) . " should be sent to the customer at least 10 days in advance, i.e., by " . date('m/d/Y', $tenDaysBeforeApproved) . ".";
                        $body['body'] .= "<br /><br />";
                        $body['body'] .= "Please ensure that the audit plan is uploaded and sent to the customer to avoid any delays.";
                        $body['body'] .= "<br /><br />";
                        $body['body'] .= "Kind Regards,";
                        $body['body'] .= "<br/>";
                        $body['body'] .= "Your HQC Supporting Team";
                        
                        // Send email to the auditor
                        sendEmail($body);

                        //$body['to'] = 'alrahmahsolutions@gmail.com';
                        //sendEmail($body);                    
                    }
                }
            }
        }
        else if ($application['CertificateExpiryDate'] != "") {
            $currentDate = new DateTime();
            $expiryDateThreshold = new DateTime();
            $expiryDateThreshold->modify('+2 months');
            $certificateExpiryDate = new DateTime($application['CertificateExpiryDate']);
            if ($certificateExpiryDate <= $expiryDateThreshold) {

                $expiringCertificates[] = ["name" => $user["name"],
                                          "CertificateNumber" => $application['CertificateNumber'],
                                          "CertificateIssueDate" => $application['CertificateIssueDate'],
                                          "CertificateExpiryDate" => $application['CertificateExpiryDate']
                                          ];
                
                /*
                $ownerEmailAddress = "halal.ezone@gmail.com";
                $fromEmailAddress = "noreply@halal-e.zone";

                $body = [];
                $body['name'] = 'Halal e-Zone';
                $body['email'] = $fromEmailAddress;
                $body['to'] = $supportEmailAddress;
                
                // sending notification
                $body['subject'] = "Halal e-Zone - Certificate Expiry Reminder - " . $user["name"];
                $body['header'] = "";
                $body['body'] = "Dear Administrator,";
                $body['body'] .= "<br /><br />";
                $body['body'] .= "This is a reminder that the Halal certificate with the following details is approaching its expiry date:";
                $body['body'] .= "<br /><br />";
                $body['body'] .= "<strong>Client:</strong> " . $user['name'];
                $body['body'] .= "<br />";
                $body['body'] .= "<strong>Certificate Number:</strong> " . $application['CertificateNumber'];
                $body['body'] .= "<br />";
                $body['body'] .= "<strong>Issue Date:</strong> " . date('m/d/Y', strtotime($application['CertificateIssueDate']));
                $body['body'] .= "<br />";
                $body['body'] .= "<strong>Expiry Date:</strong> " . date('m/d/Y', strtotime($application['CertificateExpiryDate']));
                $body['body'] .= "<br /><br />";
                $body['body'] .= "Please take the necessary actions to renew or update this certificate.";
                $body['body'] .= "<br /><br />";
                $body['body'] .= "Kind Regards,";
                $body['body'] .= "<br/>";
                $body['body'] .= "Your HQC supporting Team";
        
                sendEmail($body);
                */
            }
        }

        // Send a reminder email to the admin if the invoice is not uploaded within one week after the signed offer is uploaded. 
        $sqlDocs = "SELECT * FROM tdocs WHERE deleted = 0 AND idclient = :idclient AND idapp = :idapp AND category = 'invoice' LIMIT 1"; 
        $stmtDocs = $dbo->prepare($sqlDocs);
        $stmtDocs->bindParam(':idclient', $userId, PDO::PARAM_INT);
        $stmtDocs->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
        $stmtDocs->execute();            
        $docs = $stmtDocs->fetch(PDO::FETCH_ASSOC);

        if (!$docs) {
            // Invoice not uploaded, check for signed offer (soffer)
            $sqlSoffer = "SELECT created_at FROM tdocs WHERE deleted = 0 AND idclient = :idclient AND idapp = :idapp AND category = 'soffer' LIMIT 1";
            $stmtSoffer = $dbo->prepare($sqlSoffer);
            $stmtSoffer->bindParam(':idclient', $userId, PDO::PARAM_INT);
            $stmtSoffer->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
            $stmtSoffer->execute();
            $soffer = $stmtSoffer->fetch(PDO::FETCH_ASSOC);

            if ($soffer) {
                $createdAt = strtotime($soffer['created_at']); // Convert datetime to timestamp
                $twoWeeksAgo = strtotime('-2 week');

                if ($createdAt <= $twoWeeksAgo) {

                    $missingInvoices[] = $user['name'];

                    /*
                    // Send reminder email
                    $ownerEmailAddress = "halal.ezone@gmail.com";
                    $fromEmailAddress = "noreply@halal-e.zone";

                    $body['name'] = 'Halal e-Zone';
                    $body['email'] = $fromEmailAddress;
                    $body['to'] = $adminEmailAddress;

                    // Email content
                    $body['subject'] = "Reminder: Invoice Not Uploaded - " . $user["name"];
                    $body['header'] = "";
                    $body['body'] = "Dear Admin,";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "A signed offer was uploaded for client \"" . $user["name"] . "\" more than a week ago, but the invoice has not been uploaded yet.";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "Please ensure the invoice is uploaded as soon as possible.";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "Kind Regards,";
                    $body['body'] .= "<br/>";
                    $body['body'] .= "Your HQC Supporting Team";

                    sendEmail($body);
                    $body['to'] = 'alrahmahsolutions@gmail.com';
                    sendEmail($body); 
                    */                   
                }
            }
        }

        // Check if corrective actions are missing one week after audit date
        $oneWeekAfterAudit = strtotime($approvedDate) + (7 * 24 * 60 * 60); // Add 1 week
        $currentTime = time();

        if (($currentTime >= $oneWeekAfterAudit) ) { 
            $sqlAudit = "SELECT id FROM tauditreport WHERE idclient = :idclient AND idapp = :idapp 
                        AND ((RootCause IS NULL OR RootCause = '') 
                        OR (Measure IS NULL OR Measure = '') 
                        OR (Deadline IS NULL OR Deadline = '')) AND Status = 0";
            $stmtAudit = $dbo->prepare($sqlAudit);
            $stmtAudit->bindParam(':idclient', $userId, PDO::PARAM_INT);
            $stmtAudit->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
            $stmtAudit->execute();
            $missingActions = $stmtAudit->fetch(PDO::FETCH_ASSOC);

            if ($missingActions) {
                $ownerEmailAddress = "halal.ezone@gmail.com";
                $fromEmailAddress = "noreply@halal-e.zone";

                $body['name'] = 'Halal e-Zone';
                $body['email'] = $fromEmailAddress;
                $body['to'] = $user["email"];

                // Email content
                $body['subject'] = "Reminder: Fill in Corrective Actions - " . $user["name"];
                $body['header'] = "";

                $body['body'] = "Dear Customer,<br/><br/>";
                $body['body'] .= "It has been over a week since the audit was conducted, and we have noticed that corrective actions and root cause analysis for some deviations are still incomplete.";
                $body['body'] .= "<br/><br/>Please ensure all necessary details, including Root Cause, Measure, and Deadline, are filled in as soon as possible.";
                $body['body'] .= "<br/><br/>If you have any questions, feel free to reach out.<br/><br/>";
                $body['body'] .= "Kind Regards,<br/>Your HQC Supporting Team";

                sendEmail($body);
                //$body['to'] = 'alrahmahsolutions@gmail.com';
                //sendEmail($body);                
            }
        }

        // Check if corrective actions are provided but Status = 0 for more than 3 days
        $sqlCheckApproval = "SELECT id, created_at FROM tauditreport 
        WHERE idclient = :idclient AND idapp = :idapp 
        AND Status = 0 
        AND (RootCause IS NOT NULL AND RootCause <> '') 
        AND (Measure IS NOT NULL AND Measure <> '') 
        AND (Deadline IS NOT NULL AND Deadline <> '')";
        $stmtCheckApproval = $dbo->prepare($sqlCheckApproval);
        $stmtCheckApproval->bindParam(':idclient', $userId, PDO::PARAM_INT);
        $stmtCheckApproval->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
        $stmtCheckApproval->execute();
        $pendingApproval = $stmtCheckApproval->fetch(PDO::FETCH_ASSOC);

        if ($pendingApproval) {
            $createdAt = strtotime($pendingApproval['created_at']); // Convert created_at datetime to timestamp
            $threeDaysAfter = $createdAt + (3 * 24 * 60 * 60); // Add 3 days

            if ($currentTime >= $threeDaysAfter) {
                $ownerEmailAddress = "halal.ezone@gmail.com";
                $fromEmailAddress = "noreply@halal-e.zone";

                // Send reminder email to auditor
                $body['name'] = 'Halal e-Zone';
                $body['email'] = "noreply@halal-e.zone";
                $body['to'] = $auditor ? $auditor["email"] : $supportEmailAddress;

                $body['subject'] = "Reminder: Confirm Corrective Actions - " . $user["name"];
                $body['header'] = "";
                $body['body'] = "Dear ".($auditor ? $auditor["name"] : "Admin").",<br/><br/>";
                $body['body'] .= "All corrective actions for client \"" . $user["name"] . "\" have been provided, but they are still awaiting confirmation.";
                $body['body'] .= "<br/><br/>Please review and update the status as soon as possible.";
                $body['body'] .= "<br/><br/>If you have any concerns, kindly reach out.<br/><br/>";
                $body['body'] .= "Kind Regards,<br/>Your HQC Supporting Team";

                sendEmail($body);
                //$body['to'] = 'alrahmahsolutions@gmail.com';
                //sendEmail($body);                
            }
        }

        // Check if one day has passed after the Deadline and Status is still 0
        $sqlCheckDeadline = "SELECT id, Deadline FROM tauditreport 
        WHERE idclient = :idclient AND idapp = :idapp 
        AND Status = 0 
        AND Deadline IS NOT NULL AND Deadline <> ''";
        $stmtCheckDeadline = $dbo->prepare($sqlCheckDeadline);
        $stmtCheckDeadline->bindParam(':idclient', $userId, PDO::PARAM_INT);
        $stmtCheckDeadline->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
        $stmtCheckDeadline->execute();
        $pendingDeadline = $stmtCheckDeadline->fetch(PDO::FETCH_ASSOC);

        if ($pendingDeadline) {
            $deadlineDate = strtotime($pendingDeadline['Deadline']);
            $oneDayAfterDeadline = $deadlineDate + (1 * 24 * 60 * 60);

            if ($currentTime >= $oneDayAfterDeadline) {
                $ownerEmailAddress = "halal.ezone@gmail.com";
                $fromEmailAddress = "noreply@halal-e.zone";

                // Send reminder email to auditor to check if customer closed the issue
                $body['name'] = 'Halal e-Zone';
                $body['email'] = "noreply@halal-e.zone";
                $body['to'] = $auditor ? $auditor["email"] : $adminEmailAddress;

                $body['subject'] = "Reminder: Verify Corrective Actions - " . $user["name"];
                $body['header'] = "";
                $body['body'] = "Dear Auditor,<br/><br/>";
                $body['body'] .= "The deadline for corrective actions set by the customer for client \"" . $user["name"] . "\" has passed. ";
                $body['body'] .= "Please verify if the customer has resolved the issue and update the audit status accordingly.";
                $body['body'] .= "<br/><br/>If you have any concerns, kindly reach out.<br/><br/>";
                $body['body'] .= "Kind Regards,<br/>Your HQC Supporting Team";

                sendEmail($body);
                //$body['to'] = 'alrahmahsolutions@gmail.com';
                //sendEmail($body);                         
            }
        }

        // Check if an offer exists
        $sqlOffer = "SELECT created_at FROM tdocs WHERE deleted = 0 AND idclient = :idclient AND idapp = :idapp AND category = 'offer' LIMIT 1";
        $stmtOffer = $dbo->prepare($sqlOffer);
        $stmtOffer->bindParam(':idclient', $userId, PDO::PARAM_INT);
        $stmtOffer->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
        $stmtOffer->execute();
        $offer = $stmtOffer->fetch(PDO::FETCH_ASSOC);

        if ($offer) {
            $offerCreatedAt = strtotime($offer['created_at']); // Convert datetime to timestamp
            $oneWeekAgo = strtotime('-1 week');

            if ($offerCreatedAt <= $oneWeekAgo) {
                // Check if a signed offer (soffer) exists
                $sqlSoffer = "SELECT id FROM tdocs WHERE deleted = 0 AND idclient = :idclient AND idapp = :idapp AND category = 'soffer' LIMIT 1";
                $stmtSoffer = $dbo->prepare($sqlSoffer);
                $stmtSoffer->bindParam(':idclient', $userId, PDO::PARAM_INT);
                $stmtSoffer->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
                $stmtSoffer->execute();
                $soffer = $stmtSoffer->fetch(PDO::FETCH_ASSOC);

                if (!$soffer) {
                    // Send reminder email
                    $ownerEmailAddress = "halal.ezone@gmail.com";
                    $fromEmailAddress = "noreply@halal-e.zone";
    
                    $body['name'] = 'HQC Office';
                    $body['email'] = $ownerEmailAddress;
                    $body['to'] = $user["email"];

                    // Email content
                    $body['subject'] = "Reminder: Signed Offer Not Uploaded - " . $user["name"];
                    $body['header'] = "";
                    $body['body'] = "Dear HQC Team,";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "A new offer was uploaded for client \"" . $user["name"] . "\" on " . date("Y-m-d", $offerCreatedAt) . ".";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "However, a signed offer (soffer) has not been uploaded within one week.";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "Please ensure the signed offer is uploaded as soon as possible.";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "Kind Regards,";
                    $body['body'] .= "<br/>";
                    $body['body'] .= "Your HQC Supporting Team";

                    sendEmail($body);
                }
            }
        }

        // Travel Expenses Invoice Notification (1 day after audit)
        if (!empty($approvedDate)) {
            $auditTimestamp = strtotime($approvedDate);
            $oneDayAfterAudit = strtotime('+1 day', $auditTimestamp);
            
            if (time() >= $oneDayAfterAudit) {
                // Check if travel expenses invoice already exists
                $sqlTravelExp = "SELECT id FROM tdocs WHERE deleted = 0 AND idclient = :idclient AND idapp = :idapp AND category = 'invoicete' LIMIT 1";
                $stmtTravelExp = $dbo->prepare($sqlTravelExp);
                $stmtTravelExp->bindParam(':idclient', $userId, PDO::PARAM_INT);
                $stmtTravelExp->bindParam(':idapp', $applicationId, PDO::PARAM_INT);
                $stmtTravelExp->execute();
                $travelExpExists = $stmtTravelExp->fetch(PDO::FETCH_ASSOC);
                
                if (!$travelExpExists) {

                    $missingTravelExpInvoices[] = ["name" => $user["name"], "date" => date('m/d/Y', $auditTimestamp)];
                    /*
                    $ownerEmailAddress = "halal.ezone@gmail.com";
                    $fromEmailAddress = "noreply@halal-e.zone";
                    
                    $body = [];
                    $body['name'] = 'Halal e-Zone';
                    $body['email'] = $fromEmailAddress;
                    $body['to'] = $adminEmailAddress;
                    
                    $body['subject'] = "Reminder: Travel Expenses Invoice Due - " . $user["name"];
                    $body['header'] = "";
                    $body['body'] = "Dear Admin,";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "This is a reminder that the travel expenses invoice for client \"" . $user["name"] . "\" should be issued and uploaded to Halal e-Zone.";
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "The audit was conducted on: " . date('m/d/Y', $auditTimestamp);
                    $body['body'] .= "<br /><br />";
                    $body['body'] .= "Kind Regards,";
                    $body['body'] .= "<br/>";
                    $body['body'] .= "Your HQC Supporting Team";
                    
                    sendEmail($body);
                    $body['to'] = 'alrahmahsolutions@gmail.com';
                    sendEmail($body);
                    */
                }
            }
        }        
    }
}

if (!empty($expiringCertificates)) {

    $ownerEmailAddress = "halal.ezone@gmail.com";
    $fromEmailAddress = "noreply@halal-e.zone";

    $body = [];
    $body['name'] = 'Halal e-Zone';
    $body['email'] = $fromEmailAddress;
    $body['to'] = $supportEmailAddress;
    
    // sending notification
    $body['subject'] = "Halal e-Zone - Certificate Expiry Reminder" ;
    $body['header'] = "";
    $body['body'] = "Dear Administrator,";
    $body['body'] .= "<br /><br />";
    $body['body'] .= "This is a reminder that the Halal certificates with the following details are approaching their expiry date:";
    $body['body'] .= "<br /><br />";

    foreach ($expiringCertificates as $certificate) {
        $body['body'] .= "<strong>Client:</strong> " . $certificate['name'];
        $body['body'] .= "<br />";
        $body['body'] .= "<strong>Certificate Number:</strong> " . $certificate['CertificateNumber'];
        $body['body'] .= "<br />";
        $body['body'] .= "<strong>Issue Date:</strong> " . date('m/d/Y', strtotime($certificate['CertificateIssueDate']));
        $body['body'] .= "<br />";
        $body['body'] .= "<strong>Expiry Date:</strong> " . date('m/d/Y', strtotime($certificate['CertificateExpiryDate']));
        $body['body'] .= "<hr />";
    } 
    
    $body['body'] .= "Please take the necessary actions to renew or update these certificates.";
    $body['body'] .= "<br /><br />";
    $body['body'] .= "Kind Regards,";
    $body['body'] .= "<br/>";
    $body['body'] .= "Your HQC supporting Team";

    sendEmail($body);
    //$body['to'] = 'alrahmahsolutions@gmail.com';
    //sendEmail($body); 
}

if (!empty($missingInvoices)) {
    $body = [];
    // Send reminder email
    $ownerEmailAddress = "halal.ezone@gmail.com";
    $fromEmailAddress = "noreply@halal-e.zone";

    $body['name'] = 'Halal e-Zone';
    $body['email'] = $fromEmailAddress;
    $body['to'] = $adminEmailAddress;

    // Email content
    $body['subject'] = "Reminder: Invoices Not Uploaded";
    $body['header'] = "";
    $body['body'] = "Dear Admin,";
    $body['body'] .= "<br /><br />";
    $body['body'] .= "A signed offer was uploaded for the following clients more than a week ago, but the invoice has not been uploaded yet.";
    $body['body'] .= "<br /><br />";
    foreach ($missingInvoices as $clientName) {
        $body['body'] .= "- " . $clientName . "<br />";
    }
    $body['body'] .= "<br />Please ensure the invoice is uploaded as soon as possible.";
    $body['body'] .= "<br /><br />";
    $body['body'] .= "Kind Regards,";
    $body['body'] .= "<br/>";
    $body['body'] .= "Your HQC Supporting Team";

    sendEmail($body);
    //$body['to'] = 'alrahmahsolutions@gmail.com';
    //sendEmail($body); 
}

if (!empty($missingTravelExpInvoices)) {

    // Send reminder email
     $ownerEmailAddress = "halal.ezone@gmail.com";
    $fromEmailAddress = "noreply@halal-e.zone";
    
    $body = [];
    $body['name'] = 'Halal e-Zone';
    $body['email'] = $fromEmailAddress;
    $body['to'] = $adminEmailAddress;
    
    $body['subject'] = "Reminder: Travel Expense Invoices Are Due";
    $body['header'] = "";
    $body['body'] = "Dear Admin,";
    $body['body'] .= "<br /><br />";
    $body['body'] .= "This is a reminder that the travel expenses invoice for the following clients should be issued and uploaded to Halal e-Zone.";
    $body['body'] .= "<br /><br />";
    foreach ($missingTravelExpInvoices as $invoice) {
        $body['body'] .= "Client: " . $invoice["name"] . "<br />";
        $body['body'] .= "Audit Date: " . $invoice["date"] . "<br /><br />";
    }
    $body['body'] .= "Kind Regards,";
    $body['body'] .= "<br/>";
    $body['body'] .= "Your HQC Supporting Team";
    
    sendEmail($body);
    //$body['to'] = 'alrahmahsolutions@gmail.com';
    //sendEmail($body);
}

$logger->info('Notification script ended');

echo 'Notification script ended';
?>