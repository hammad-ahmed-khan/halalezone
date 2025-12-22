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
$sql = "SELECT * FROM tingredients WHERE halalexp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND halalexp < CURDATE()";
$stmt = $dbo->prepare($sql);
$stmt->execute();
 
// iterate over result set and print out details
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    
  $check_sql = "SELECT * FROM td2i WHERE idd = 328 AND idi = :ingredient_id AND status <> 2";
    $check_stmt = $dbo->prepare($check_sql);
    $check_stmt->bindParam(':ingredient_id', $row['id'], PDO::PARAM_INT);
    $check_stmt->execute();

    // if tingredient ID does not exist in td2i table, insert a new row
    if (!$check_stmt->fetch()) {
        echo "sW
        ";
        $insert_sql = "INSERT INTO td2i (idd, idi) VALUES (328, :idi)";
        $insert_stmt = $dbo->prepare($insert_sql);
        $insert_stmt->bindParam(':idi', $row['id'], PDO::PARAM_INT);
        $insert_stmt->execute();
    }
}

$logger->info('Notification script ended');

echo 'Notification script ended';
?>