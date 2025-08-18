<?php

/*
//  cron-job script for email notifications on:
//  - ingredients' certificates expiration
//  - application expiry date // 19.04.2020 disabled
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once(dirname(__FILE__).'/vendor/autoload.php');
require_once(dirname(__FILE__).'/../config/config.php');

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs',Psr\Log\LogLevel::DEBUG,array('filename'=>'notifier.log'));

$expiryMargins = array(
    'one_week' => array(
        'name' => 'one_week',
        'notified_status' => 3,
        'unnotified_status' => 2,
        'strtotimeString' => "+1 week",
    ),
    'four_week' => array(
        'name' => 'four_week',
        'notified_status' => 2,
        'unnotified_status' => 1,
        'strtotimeString' => "+4 week",
    ),
    'eight_week' => array(
        'name' => 'eight_week',
        'notified_status' => 1,
        'unnotified_status' => 0,
        'strtotimeString' => "+8 week",
    )
);

$certExpiryMargins = array(
    'two_month' => array(
        'name' => 'two_month',
        'notified_status' => 3,
        'unnotified_status' => 2,
        'strtotimeString' => "+2 month",
    ),
    'three_month' => array(
        'name' => 'three_month',
        'notified_status' => 2,
        'unnotified_status' => 1,
        'strtotimeString' => "+3 month",
    ),
    'four_month' => array(
        'name' => 'four_month',
        'notified_status' => 1,
        'unnotified_status' => 0,
        'strtotimeString' => "+4 month",
    )
);

function getEmailTemplate($data) {
    ob_start();
    include(dirname(__FILE__) . "/email_template.php");
    $template = ob_get_clean();
    return $template;
}

function getCertificateEmailTemplate($data) {
    ob_start();
    include(dirname(__FILE__) . "/new_email_template.php");
    $template = ob_get_clean();
    return $template;
}

function sendEmail ($clientData, $body) {
    $ownerEmailAddress = "halal.ezone@gmail.com";
    $fromEmailAddress = "noreply@halal-e.zone";
    $mail = new PHPMailer;
    $mail->setFrom($fromEmailAddress, 'HALAL eZone');

    $recipients = explode(',', $clientData['email']);
    foreach($recipients as $r) {
        if (trim(strtolower($r)) == 'office@hqc.at') {
            $r = 'communication@hqc.at';
        }
        $mail->addAddress(trim($r));
    }
    $mail->addReplyTo($fromEmailAddress, 'HALAL eZone');
    //$mail->addBCC($ownerEmailAddress);
    $mail->isHTML(true);
    $mail->addEmbeddedImage('../img/logo_email.png', 'logo');
    $mail->Subject = "Ingredients' certificates expiry notification";
    $mail->Body    = $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $GLOBALS['logger']->info("Trying to send email to the client [".$clientData['email']."]" );
    foreach ($GLOBALS['expiryMargins'] as $expiryMargin) {
        $count = count($clientData[$expiryMargin['name']]);
        if($count > 0) {
            $GLOBALS['logger']->info("To expire in [".$expiryMargin['name']."]" );
            foreach($clientData[$expiryMargin['name']] as $item) {
                $GLOBALS['logger']->info("-> RMC_".$item['id'].", ".$item['name'].", ".$item['halalexp']);
            }
        }
    }
    if(!$mail->send()) {
        $GLOBALS['logger']->info('Message could not be sent.');
        $GLOBALS['logger']->info('Mailer Error: ' . $mail->ErrorInfo);
    } else {
        updateItemStatus($clientData);
        $GLOBALS['logger']->info('Message has been sent');
    }
}

function sendCertEmail ($clientData, $body) {
    $ownerEmailAddress = "halal.ezone@gmail.com";
    $fromEmailAddress = "noreply@halal-e.zone";
    $mail = new PHPMailer;
    $mail->setFrom($fromEmailAddress, 'HALAL eZone');
    $mail->addAddress($clientData['email'], $clientData['name']);
    $mail->addReplyTo($fromEmailAddress, 'HALAL eZone');
    $mail->addBCC($ownerEmailAddress);
    $mail->isHTML(true);
    $mail->addEmbeddedImage('../img/logo_email.png', 'logo');
    $mail->Subject = "Certificate expiry notification";
    $mail->Body    = getCertificateEmailTemplate($body);
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $GLOBALS['logger']->info("Trying to send email to the client [".$clientData['email']."]" );
    if(!$mail->send()) {
        $GLOBALS['logger']->info('Message could not be sent.');
        $GLOBALS['logger']->info('Mailer Error: ' . $mail->ErrorInfo);
    } else {
        $GLOBALS['logger']->info($mail->Body);
        updateCertificateStatus($clientData);
        $GLOBALS['logger']->info('Message has been sent');
    }
}

function updateItemStatusDB ($itemIds, $status) {
    if( count($itemIds) == 0) { return ; }
    $in = implode(',',  array_fill(0, count($itemIds), '?'));
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tingredients SET status = ? WHERE id IN (".$in.")";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(1, $status);
        foreach ($itemIds as $k => $id){
            $stmt->bindValue(($k+2), $id);
        }
        $stmt->execute();
    } catch (PDOException $e){
        $GLOBALS['logger']->error("Error while connecting to the database");
        die();
    }
}

function updateItemStatus($clientData) {
    $expiryMargins = $GLOBALS['expiryMargins'];
    foreach ($expiryMargins as $expMargin) {
        if (isset($clientData[$expMargin['name']]) and $clientData[$expMargin['name']] > 0) {
            $itemIdsToUpdate = array();
            foreach ($clientData[$expMargin['name']] as $item) {
                array_push($itemIdsToUpdate, $item['id']);
            }
            updateItemStatusDB($itemIdsToUpdate, $expMargin['notified_status']);
        }
    }
}

function updateCertificateStatus($client) {
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "UPDATE tapplications SET notifystatus = :s WHERE id = :id";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(":s", $client['status']);
        $stmt->bindValue(":id", $client['id']);
        $stmt->execute();
    } catch (PDOException $e){
        $GLOBALS['logger']->error("Error while connecting to the database");
        die();
    }
}

function getItemsToBeExpired($expiryMargin){
    $status = $expiryMargin['unnotified_status'];
    $marginDate = new DateTime();
    $marginDate->setTimestamp(strtotime($expiryMargin['strtotimeString']));
    $marginDateStr = date_format($marginDate,"Y-m-d");
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "SELECT i.id, i.name, i.halalexp, i.status, u.name AS u_name, u.email AS u_email, u.id as u_id FROM tingredients AS i, tusers AS u ".
            " WHERE i.status = :status AND i.status < 3 AND i.halalexp <= :margin_date AND i.idclient = u.id;";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':margin_date', $marginDateStr);
        $stmt->execute();
        $res = $stmt->fetchAll();
        //$GLOBALS['logger']->info("Items to be expired for ". $expiryMargin['name']);
        //$GLOBALS['logger']->info(print_r($res, true));
        return $res;
    } catch (PDOException $e){
        $GLOBALS['logger']->error("Error while connecting to the database");
        die();
    }
}

function getCertificatesToBeExpired($expiryMargin){
    $status = $expiryMargin['unnotified_status'];
    $marginDate = new DateTime();
    $marginDate->setTimestamp(strtotime($expiryMargin['strtotimeString']));
    $marginDateStr = date_format($marginDate,"Y-m-d");
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "SELECT a.id, a.name, a.enddt, a.notifystatus, u.name AS u_name, u.email AS u_email, u.id as u_id FROM tapplications a ".
                " inner join tcycles c on c.id=a.idcycle ".
                " inner join tusers u on c.idclient=u.id ".
            "WHERE a.notifystatus = :status AND a.enddt > NOW() AND a.enddt <= :margin_date AND a.state=1";
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

function getClientBasedData() {
    $expiryMargins = $GLOBALS['expiryMargins'];
    $allItems =  array();
    foreach ($expiryMargins as $expiryMargin) {
        $allItems = array_merge($allItems, getItemsToBeExpired($expiryMargin));
    }
    $clientBasedItems = array();
    foreach($allItems as $item) {
        $clientBasedItems[$item['u_id']] = isset($clientBasedItems[$item['u_id']]) ?  $clientBasedItems[$item['u_id']] : array("name"=> $item['u_name'], "email"=> $item['u_email'], 'one_week' => array(), 'four_week' => array(), 'eight_week'=> array() );
        switch ($item['status']) {
            case 0:
                // here we have all expired with current status 0 regardless expiry margin. This may happen
                // if the ingredient has been added with expiring certificat already.
                // to make coloring correct, we need to set correct current status, otherwise it will be
                // considered as eight weeks expiry
                $expiryDate = date_create($item['halalexp']);
                $dateToCheck = new DateTime();
                foreach ($expiryMargins as $expiryMargin) {
                    //$GLOBALS['logger']->info("Exp date: ".$item['halalexp'].", margin: ".$expiryMargin['strtotimeString']);
                    if($expiryDate <= $dateToCheck->setTimestamp(strtotime($expiryMargin['strtotimeString']))) {
                        //$GLOBALS['logger']->info("Old unnotified status: ".$item['status']. ", exptdate: ". $item['halalexp']);
                        $item['status'] = $expiryMargin['unnotified_status']; // set unnotified status for this margin
                        array_push($clientBasedItems[$item['u_id']][$expiryMargin['name']], $item);
                        //$GLOBALS['logger']->info("New unnotified status: ".$item['status']);
                        break;
                    }
                }
                break;
            case 1:
                array_push($clientBasedItems[$item['u_id']]['four_week'], $item);
                break;
            case 2:
                array_push($clientBasedItems[$item['u_id']]['one_week'], $item);
                break;
        }
    }
    $GLOBALS['logger']->info("Result: ".print_r($clientBasedItems, true));
    return $clientBasedItems;
}

function getClientBasedCertificateData() {
    $expiryMargins = $GLOBALS['certExpiryMargins'];
    $allItems =  array();
    foreach ($expiryMargins as $expiryMargin) {
        $allItems = array_merge($allItems, getCertificatesToBeExpired($expiryMargin));
        //if(count($allItems) > 0 ) break;
    }
    $clientBasedItems = array();
    foreach($allItems as $item) {
        $clientBasedItems[$item['u_id']] = isset($clientBasedItems[$item['u_id']]) ?  $clientBasedItems[$item['u_id']] :
            array("name"=> $item['u_name'], "email"=> $item['u_email'], 'id' =>  $item['id'], 'cycle' =>  $item['name'],  'enddt' =>  $item['enddt'], 'status' => $item['notifystatus']+1);
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

    // Ingredients certificares expiry notification
    $clientBasedData = getClientBasedData();
    foreach ($clientBasedData as $client) {
        sendEmail($client, getEmailTemplate($client));
    }

    // Applications expiry date notification
    /*
    $clientBasedData = getClientBasedCertificateData();
    foreach ($clientBasedData as $client) {
        $body['header'] = "Your certificate (".$client['cycle'].") will expire on ".date("d.m.Y", strtotime($client['enddt']));
        $body['body'] = '';
        sendCertEmail($client, $body);
    }
    */

    $logger->info('Notification script ended');

?>
