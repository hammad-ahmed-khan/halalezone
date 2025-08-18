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

error_reporting(E_ALL);
ini_set('display_errors', 1);

$logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs',Psr\Log\LogLevel::DEBUG,array('filename'=>'notifier.log'));
//$httpprefix = 'http://localhost/fl/halal/';
$httpprefix = 'https://halal-e.zone/';
$adminEmailAddress = "halal.ezone@gmail.com";
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

// get all ingredients with expired certificates
$sql = "SELECT * FROM tingredients WHERE sub = 0 AND deleted = 0";
$stmt = $dbo->prepare($sql);
$stmt->execute();
 
// iterate over result set and print out details
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

  $ingredient_id = $row['id'];
  
  $check_sql = "SELECT * 
  FROM ti2i LEFT JOIN tingredients s on (s.id=ti2i.idi2)
  WHERE ti2i.idi1=:ingredient_id";

    $check_stmt = $dbo->prepare($check_sql);
    $check_stmt->bindParam(':ingredient_id', $ingredient_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    $allConfirmed = true;  // Flag to check if all 'conf' values are 1

    if ($check_stmt->rowCount() > 0) {  // Rows exist

        while ($check_row = $check_stmt->fetch(PDO::FETCH_ASSOC)) {
            /*
            echo '<pre>';
            print_r($check_row);
            echo '</pre>';
            */
            if ($check_row['conf'] != 1) {
                $allConfirmed = false;  // Set flag to false if any conf is not 1
                break;
            }
        }

        $update_sql = "UPDATE tingredients SET conf = '".($allConfirmed? 1 : 0)."' WHERE id = :ingredient_id";
        $update_stmt = $dbo->prepare($update_sql);
        $update_stmt->bindParam(':ingredient_id', $ingredient_id, PDO::PARAM_INT);
        $update_stmt->execute();
            
    }

    /*
    $update_stmt = $dbo->prepare($update_sql);
    $update_stmt->bindParam(':ingredient_id', $ingredient_id, PDO::PARAM_INT);
    $update_stmt->execute();
*/  
}

$logger->info('Notification script ended');

echo 'Notification script ended';
?>