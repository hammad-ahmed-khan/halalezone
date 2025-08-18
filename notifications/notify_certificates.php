<?php

/*
//  cron-job script for email notifications on:
//  - new certificates uploading on the Dashboard page
//  - D0ashboard certificates expiration
*/

require_once(dirname(__FILE__).'/vendor/autoload.php');
require_once(dirname(__FILE__).'/../config/config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs',Psr\Log\LogLevel::DEBUG,array('filename'=>'notifier.log'));
$adminEmailAddress = "halal.ezone@gmail.com";
$ownerEmailAddress = "ovchinnikov.it@gmail.com";

$certExpiryMargins = array(
    'expired' => array(
        'name' => 'expired',
        'notified_status' => 4,
        'strtotimeString' => "+0 days",
    ),
    '30_days' => array(
        'name' => '30_days',
        'notified_status' => 3,
        'strtotimeString' => "+30 days",
    ),
    '60_days' => array(
        'name' => '60_days',
        'notified_status' => 2,
        'strtotimeString' => "+60 days",
    ),
    '90_days' => array(
        'name' => '90_days',
        'notified_status' => 1,
        'strtotimeString' => "+90 days",
    )
);

function getCertificateEmailTemplate($data) {
    ob_start();
    include(dirname(__FILE__) . "/expiring_certificates_email_template.php");
    $template = ob_get_clean();
    return $template;
}

function getNewCertificateEmailTemplate($data) {
    ob_start();
    include(dirname(__FILE__) . "/new_certificates_email_template.php");
    $template = ob_get_clean();
    return $template;
}

function sendCertEmail ($clientData, $body) {
    //$ownerEmailAddress = "halal.ezone@gmail.com";
    $ownerEmailAddress = "communication@hqc.at";
    $fromEmailAddress = "noreply@halalezone.com";
    $mail = new PHPMailer;
    $mail->setFrom($fromEmailAddress, 'HALAL eZone');
    if(isset($_GET['test'])) {
      $mail->addAddress($GLOBALS['ownerEmailAddress']);
    }
    else {      
      $r = $clientData['email'];
      if (trim(strtolower($r)) == 'office@hqc.at') { 
        $r = 'communication@hqc.at';
      }
      $mail->addAddress(trim($r));    
    }
    $mail->addReplyTo($fromEmailAddress, 'HALAL eZone');
    $mail->addBCC($ownerEmailAddress);
    $mail->isHTML(true);
    $mail->addEmbeddedImage('../img/logo_email.png', 'logo');
    $mail->Subject = "Certificate expiry notification";
    $mail->Body    = $body;
    if(isset($_GET['test']))
      echo $body;  
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $GLOBALS['logger']->info("Trying to send email to the client [".$clientData['email']."]" );
    if(!$mail->send()) {
        $GLOBALS['logger']->info('Message could not be sent.');
        $GLOBALS['logger']->info('Mailer Error: ' . $mail->ErrorInfo);
    } else {
        $GLOBALS['logger']->info($mail->Body);
        updateItemStatus($clientData);
        $GLOBALS['logger']->info('Message has been sent');
    }
}

function sendNewCertEmail ($clientData, $body) {
    $fromEmailAddress = "noreply@halalezone.com";
    $ownerEmailAddress = "communication@hqc.at";
    $mail = new PHPMailer;
    $mail->setFrom($fromEmailAddress, 'HALAL eZone');
    if(isset($_GET['test'])) {
        $mail->addAddress($GLOBALS['ownerEmailAddress']);
    }
    else {      
        $r = $clientData['email'];
        if (trim(strtolower($r)) == 'office@hqc.at') { 
            $r = 'communication@hqc.at';
        }
        $mail->addAddress(trim($r));    
    }    
    $mail->addReplyTo($fromEmailAddress, 'HALAL eZone');
    $mail->isHTML(true);
    $mail->addEmbeddedImage('../img/logo_email.png', 'logo');
    $mail->Subject = "New Certificates notification";
    $mail->Body    = $body;
    if(isset($_GET['test']))
      echo $body;  
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $GLOBALS['logger']->info("Trying to send email to the client [".$clientData['email']."]" );
    if(!$mail->send()) {
        $GLOBALS['logger']->info('Message could not be sent.');
        $GLOBALS['logger']->info('Mailer Error: ' . $mail->ErrorInfo);
    } else {
        $GLOBALS['logger']->info($mail->Body);
        updateNewItemStatus($clientData);
        $GLOBALS['logger']->info('Message has been sent');
    }
}

function updateNewItemStatus($clientData) {
    if(isset($_GET['test'])) return;
    foreach ($clientData['data'] as $data) {
        updateNewCertificateStatus($data);
    }
}

function updateNewCertificateStatus($data) {
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tcertificates SET notified = 1 WHERE id = :id";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(":id", $data['id']);
        $stmt->execute();
    } catch (PDOException $e){
        $GLOBALS['logger']->error("Error while connecting to the database");
        die();
    }
}

function updateItemStatus($clientData) {
    if(isset($_GET['test'])) return;
    $expiryMargins = $GLOBALS['expiryMargins'];
    foreach ($expiryMargins as $expMargin) {
        if (isset($clientData[$expMargin['name']]) and $clientData[$expMargin['name']] > 0) {
            foreach ($clientData[$expMargin['name']] as $item) {
                updateCertificateStatus($item);
            }
        }
    }
}

function updateCertificateStatus($client) {
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tcertificates SET status = status+1 WHERE id = :id";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(":id", $client['id']);
        $stmt->execute();
    } catch (PDOException $e){
        $GLOBALS['logger']->error("Error while connecting to the database");
        die();
    }
}

function getNewCertificates(){
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "SELECT c.id, c.filename, c.url, u.name AS u_name, u.email AS u_email, u.id as u_id FROM tcertificates c ".
            " inner join tusers u on c.idclient=u.id ".
            "WHERE c.notified=0";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e){
        $GLOBALS['logger']->error("Error while connecting to the database");
        die();
    }
}

function getCertificatesToBeExpired($expiryMargin){
    $status = $expiryMargin['notified_status'];
    $marginDate = new DateTime();
    $marginDate->setTimestamp(strtotime($expiryMargin['strtotimeString']));
    $marginDateStr = date_format($marginDate,"Y-m-d");
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "SELECT c.id, c.filename, c.status, u.name AS u_name, u.email AS u_email, u.id as u_id FROM tcertificates c ".
                " inner join tusers u on c.idclient=u.id ".
            "WHERE c.status < :status AND c.expdate <= :margin_date";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':margin_date', $marginDateStr);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e){
        $GLOBALS['logger']->error("Error while connecting to the database");
        die();
    }
}

function getClientBasedCertificateData() {
    $expiryMargins = $GLOBALS['certExpiryMargins'];
    $allItems =  array();
    foreach ($expiryMargins as $expiryMargin) {
        $allItems = array_merge($allItems, getCertificatesToBeExpired($expiryMargin));
    }
    $clientBasedItems = array();
    foreach($allItems as $item) {
        $clientBasedItems[$item['u_id']] = isset($clientBasedItems[$item['u_id']]) ? $clientBasedItems[$item['u_id']] :
            array("name"=> $item['u_name'], "email"=> $item['u_email'], 'expired' => array(), '30_days' => array(), '60_days'=> array(), '90_days'=> array());
        switch ($item['status']+1) {
            case 1:
                array_push($clientBasedItems[$item['u_id']]['90_days'], $item);
                break;
            case 2:
                array_push($clientBasedItems[$item['u_id']]['60_days'], $item);
                break;
            case 3:
                array_push($clientBasedItems[$item['u_id']]['30_days'], $item);
                break;
            case 4:
                array_push($clientBasedItems[$item['u_id']]['expired'], $item);
                break;
        }
    }
    return $clientBasedItems;
}

function getClientBasedNewCertificateData() {
    $allItems =  getNewCertificates();
    $clientBasedItems = array();
    foreach($allItems as $item) {
        $clientBasedItems[$item['u_id']] = isset($clientBasedItems[$item['u_id']]) ?
                                            $clientBasedItems[$item['u_id']] :
            array("name"=> $item['u_name'], "email"=> $item['u_email'], 'data' => array());
        array_push($clientBasedItems[$item['u_id']]['data'], $item);

    }
    return $clientBasedItems;
}


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

    // first, get all the newly uploaded certificates
    // and update their status to notified
    $clientBasedData = getClientBasedNewCertificateData();
    foreach ($clientBasedData as $client) {
        sendNewCertEmail($client, getNewCertificateEmailTemplate($client));
    }

    // then, check the expiry status to match real time
    // if not, send notification and ++status
    $expiryMargins = $GLOBALS['certExpiryMargins'];
    $clientBasedData = getClientBasedCertificateData();
    foreach ($clientBasedData as $client) {
        sendCertEmail($client, getCertificateEmailTemplate($client));
    }

    $logger->info('Notification script ended');

    echo 'Notification script ended';

?>
