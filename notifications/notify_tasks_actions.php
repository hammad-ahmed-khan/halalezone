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

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

$logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs',Psr\Log\LogLevel::DEBUG,array('filename'=>'notifier.log'));
//$httpprefix = 'http://localhost/fl/halal/';
$httpprefix = 'https://halal-e.zone/';
$adminEmailAddress = "halal.ezone@gmail.com";
$ownerEmailAddress = "ovchinnikov.it@gmail.com";


function getTasksToCompleteEmailTemplate($data) {
    ob_start();
    include(dirname(__FILE__) . "/tasks_email_template.php");
    $template = ob_get_clean();
    return $template;
}

function getTasksToConfirmEmailTemplate($data) {
    ob_start();
    include(dirname(__FILE__) . "/tasks_to_confirm_email_template.php");
    $template = ob_get_clean();
    return $template;
}

function getActionsToConfirmEmailTemplate($data) {
    ob_start();
    include(dirname(__FILE__) . "/actions_to_confirm_email_template.php");
    $template = ob_get_clean();
    return $template;
}

function getActionsConfirmedEmailTemplate($data) {
    ob_start();
    include(dirname(__FILE__) . "/actions_confirmed_email_template.php");
    $template = ob_get_clean();
    return $template;
}

function sendTasksToCompleteEmail ($clientData, $body) {
    $fromEmailAddress = "noreply@halal-e.zone";
    $mail = new PHPMailer;
    $mail->setFrom($fromEmailAddress, 'HALAL eZone');
    /*
    if(isset($_GET['test']))
      $mail->addAddress($GLOBALS['ownerEmailAddress']);
    else
      $mail->addAddress($clientData['email'], $clientData['name']);
    */
    // Check if the email address contains multiple emails separated by commas
    if (strpos($clientData['email'], ',') !== false) {
      // Explode the string to get individual email addresses
      $emails = explode(',', $clientData['email']);
      foreach ($emails as $email) {
        $r = $email;
        if (trim(strtolower($r)) == 'office@hqc.at') { 
          $r = 'communication@hqc.at';
        }
        $mail->addAddress(trim($r));    
        //$mail->addAddress(trim($email), $clientData['name']);
      }
    } else {
        $r = $clientData['email'];
        if (trim(strtolower($r)) == 'office@hqc.at') { 
          $r = 'communication@hqc.at';
        }
        $mail->addAddress(trim($r));    
    }
    $mail->addReplyTo($fromEmailAddress, 'HALAL eZone');
    $mail->isHTML(true);
    $mail->addEmbeddedImage('../img/logo_email.png', 'logo');
    $mail->Subject = "New tasks to complete";
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
        $GLOBALS['logger']->info('Message has been sent');
    }
}

function sendTasksToConfirmEmail ($body) {
    $fromEmailAddress = "noreply@halal-e.zone";
    $mail = new PHPMailer;
    $mail->setFrom($fromEmailAddress, 'HALAL eZone');
    if(isset($_GET['test']))
      $mail->addAddress('communication@hqc.at');
    else
      $mail->addAddress('communication@hqc.at');
    
    $mail->addReplyTo($fromEmailAddress, 'HALAL eZone');
    $mail->isHTML(true);
    $mail->addEmbeddedImage('../img/logo_email.png', 'logo');
    $mail->Subject = "Completed tasks waiting for confirmation";
    $mail->Body    = $body;
    if(isset($_GET['test']))
      echo $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $GLOBALS['logger']->info("Trying to send email to admin" );
    if(!$mail->send()) {
        $GLOBALS['logger']->info('Message could not be sent.');
        $GLOBALS['logger']->info('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    } else {
        $GLOBALS['logger']->info($mail->Body);
        $GLOBALS['logger']->info('Message has been sent');
        return true;
    }
}

function sendActionsToConfirmEmail ($body) {
    $fromEmailAddress = "noreply@halal-e.zone";
    $mail = new PHPMailer;
    $mail->setFrom($fromEmailAddress, 'HALAL eZone');
    $mail->addAddress('communication@hqc.at');
    $mail->addReplyTo($fromEmailAddress, 'HALAL eZone');
    $mail->isHTML(true);
    $mail->addEmbeddedImage('../img/logo_email.png', 'logo');
    $mail->Subject = "Actions waiting for confirmation";
    $mail->Body    = $body;
    if(isset($_GET['test']))
      echo $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $GLOBALS['logger']->info("Trying to send email to admin" );
    if(!$mail->send()) {
        $GLOBALS['logger']->info('Message could not be sent.');
        $GLOBALS['logger']->info('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    } else {
        $GLOBALS['logger']->info($mail->Body);
        $GLOBALS['logger']->info('Message has been sent');
        return true;
    }
}

function sendActionsConfirmedEmail ($clientData, $body) {
    $fromEmailAddress = "noreply@halal-e.zone";
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
    $mail->Subject = "New confirmed actions";
    $mail->Body    = $body;
    if(isset($_GET['test']))
      echo $body;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $GLOBALS['logger']->info("Trying to send email to the client [".$clientData['email']."]" );
    if(!$mail->send()) {
        $GLOBALS['logger']->info('Message could not be sent.');
        $GLOBALS['logger']->info('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    } else {
        $GLOBALS['logger']->info($mail->Body);
        $GLOBALS['logger']->info('Message has been sent');
        return true;
    }
}

function getTasksToComplete(){
  try{
      $dbo = &$GLOBALS['dbo'];
      $sql = "select i.idclient, c.name as client, c.email, i.id as idingredient, i.name,  i.supplier, i.producer, di.created_at, d.deviation, ".
             "d.measure, di.status from tingredients i ".
             " inner join td2i di on di.idi=i.id ".
             " inner join tdeviations d on d.id=di.idd ".
             " inner join tusers c on c.id=i.idclient ".
             " where c.deleted=0 AND i.deleted=0 AND di.status = 0";
      $stmt = $dbo->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
      return $stmt->fetchAll();
  } catch (PDOException $e){
      $GLOBALS['logger']->error("Error while connecting to the database");
      die();
  }
}

function getClientBasedTasksToCompleteData() {
    $allItems =  getTasksToComplete();
    $clientBasedItems = array();
    foreach($allItems as $item) {
      // insert new client to the array or do nothing if exists
      $clientBasedItems[$item['idclient']] = isset($clientBasedItems[$item['idclient']]) ? $clientBasedItems[$item['idclient']] :
            array("name"=> $item['client'], "email"=> $item['email'], 'tasks' => array());
      array_push($clientBasedItems[$item['idclient']]['tasks'], $item);
    }
    return $clientBasedItems;
}

// Tasks to confirm
function getTasksToConfirm(){
  try{
      $dbo = &$GLOBALS['dbo'];
      $sql = "select i.idclient, c.name as client, c.email, i.id as idingredient, i.name,  i.supplier, i.producer, di.created_at, d.deviation, ".
             "d.measure, di.status from tingredients i ".
             " inner join td2i di on di.idi=i.id ".
             " inner join tdeviations d on d.id=di.idd ".
             " inner join tusers c on c.id=i.idclient ".
             " where i.deleted=0 AND di.status = 1 and di.notified=0";
      $stmt = $dbo->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
      return $stmt->fetchAll();
  } catch (PDOException $e){
      $GLOBALS['logger']->error("Error while connecting to the database");
      die();
  }
}

function getClientBasedTasksToConfirmData() {
    $allItems =  getTasksToConfirm();
    $clientBasedItems = array();
    foreach($allItems as $item) {
      // insert new client to the array or do nothing if exists
      $clientBasedItems[$item['idclient']] = isset($clientBasedItems[$item['idclient']]) ? $clientBasedItems[$item['idclient']] :
            array("idclient"=> $item['idclient'], "name"=> $item['client'], "email"=> $item['email'], 'tasks' => array());
      array_push($clientBasedItems[$item['idclient']]['tasks'], $item);
    }
    return $clientBasedItems;
}

function updateTasksData($clientData) {
  if(isset($_GET['test'])) return;

  foreach ($clientData as $data) {
    updateTasksNotified($data);
  }
}

function updateTasksNotified($client) {
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "update tingredients INNER join td2i on td2i.idi=tingredients.id set td2i.notified=1 where tingredients.idclient=:id";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(":id", $client['idclient']);
        $stmt->execute();
    } catch (PDOException $e){
        $GLOBALS['logger']->error("Error while connecting to the database");
        die();
    }
}

// Actions to confirm

function getActionsToConfirm(){
  try{
      $dbo = &$GLOBALS['dbo'];
      $sql = "select itemid, itemcode, itemtype, c.id as idclient, itemname, c.name as client, email, action, i.created_at FROM tclientactions i "
  					 ." inner join tusers c on c.id=i.idclient "
             ." where i.status = 0";
      $stmt = $dbo->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
      return $stmt->fetchAll();
  } catch (PDOException $e){
      $GLOBALS['logger']->error("Error while connecting to the database");
      die();
  }
}

function getClientBasedActionsToConfirmData() {
    $allItems =  getActionsToConfirm();
    $clientBasedItems = array();
    foreach($allItems as $item) {
      // insert new client to the array or do nothing if exists
      $clientBasedItems[$item['idclient']] = isset($clientBasedItems[$item['idclient']]) ? $clientBasedItems[$item['idclient']] :
            array("idclient"=> $item['idclient'], "name"=> $item['client'], "email"=> $item['email'], 'actions' => array());
      array_push($clientBasedItems[$item['idclient']]['actions'], $item);
    }
    return $clientBasedItems;
}

// Confirmed Actions for client

function getActionsConfirmed(){
  try{
      $dbo = &$GLOBALS['dbo'];
      $sql = "select itemid, itemcode, itemtype, c.id as idclient, itemname, c.name as client, email, action, i.created_at FROM tclientactions i "
  					 ." inner join tusers c on c.id=i.idclient "
             ." where i.status = 1 and i.notified = 0";
      $stmt = $dbo->prepare($sql);
      $stmt->setFetchMode(PDO::FETCH_ASSOC);
      $stmt->execute();
      return $stmt->fetchAll();
  } catch (PDOException $e){
      $GLOBALS['logger']->error("Error while connecting to the database");
      die();
  }
}

function getClientBasedActionsConfirmedData() {
    $allItems =  getActionsConfirmed();
    $clientBasedItems = array();
    foreach($allItems as $item) {
      // insert new client to the array or do nothing if exists
      $clientBasedItems[$item['idclient']] = isset($clientBasedItems[$item['idclient']]) ? $clientBasedItems[$item['idclient']] :
            array("name"=> $item['client'],"idclient"=> $item['idclient'], "email"=> $item['email'], 'actions' => array());
      array_push($clientBasedItems[$item['idclient']]['actions'], $item);
    }
    return $clientBasedItems;
}


function updateActionsData($data) {
  if(isset($_GET['test'])) return;
  updateActionsNotified($data);
}

function updateActionsNotified($client) {
    try{
        $dbo = &$GLOBALS['dbo'];
        $sql = "update tclientactions set notified = 1 where idclient = :id and status = 1";
        $stmt = $dbo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindValue(":id", $client['idclient']);
        $stmt->execute();
    } catch (PDOException $e){
        $GLOBALS['logger']->error("Error while connecting to the database");
        die();
    }
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

/*
echo "<pre>";
print_r($clientBasedData);
echo "</pre>";
*/

// 1.Clients are notified about tasks to complete every Monday at 9am untill tasks are not completed
// 2.Admin is notified about completed tasks to confirm every day at 9am and every task is notified only once. Once a completed task is notified to admin, it will never be notified again
// 3.Admin is notified about all completed and non-confirmed client actions every day at 9am until an action is confirmed 
// 4.Clients are notified about all confirmed by admin actions every day at 9am and every confirmed action is notified only once 


  // 1.first, get all the uncompleted tasks and send notification to clients
  // once a week on Monday
  if(date('w') == 1 || isset($_GET['test']))
  {
    $clientBasedData = getClientBasedTasksToCompleteData();
    foreach ($clientBasedData as $client) {
      sendTasksToCompleteEmail($client, getTasksToCompleteEmailTemplate($client));
    }
  }

  // 2.send all confirmed and not notified yet task to admin
  $clientBasedData = getClientBasedTasksToConfirmData();
  if(!empty($clientBasedData))
    if(sendTasksToConfirmEmail(getTasksToConfirmEmailTemplate($clientBasedData)))
      updateTasksData($clientBasedData);

  // 3.send all non-confirmed actions to admin
  $clientBasedData = getClientBasedActionsToConfirmData();
  if(!empty($clientBasedData))
    sendActionsToConfirmEmail(getActionsToConfirmEmailTemplate($clientBasedData));

 // 4.send all confirmed and not notified yet actions to clients
  $clientBasedData = getClientBasedActionsConfirmedData();
  foreach ($clientBasedData as $client) {
    if(sendActionsConfirmedEmail($client, getActionsConfirmedEmailTemplate($client))) {
      updateActionsData($client);
    }
  }

$logger->info('Notification script ended');

echo 'Notification script ended';

?>
