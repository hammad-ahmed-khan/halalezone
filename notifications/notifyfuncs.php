<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once(dirname(__FILE__).'/vendor/autoload.php');

function getEmailTemplate($data) {
    ob_start();
    include(dirname(__FILE__) . "/new_email_template.php");
    $template = ob_get_clean();
    return $template;
}

function sendEmail ($body) {
    $logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs',Psr\Log\LogLevel::DEBUG,array('filename'=>'notifier.log'));
    //$ownerEmailAddress = "halal.ezone@gmail.com";
    //$fromEmailAddress = "noreply@halalezone.com";
    $mail = new PHPMailer;
    $mail->setFrom($body['email'], 'HALAL eZone');
    //$mail->addAddress($ownerEmailAddress);
    $mail->addAddress($body['to']);
    $mail->isHTML(true);
    $mail->addEmbeddedImage('../img/logo_email.png', 'logo');
    $mail->Subject = $body['subject'];
    $mail->Body    = getEmailTemplate($body);
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $logger->info("Trying to send email to Halal");
    if(!$mail->send()) {
        $logger->info('Message could not be sent.');
        $logger->info('Mailer Error: ' . $mail->ErrorInfo);
    } else {
        $logger->info('Message has been sent');
        $logger->info($mail->Body);
    }
}

function sendEmailWithAttach ($body) {
    /*
    Array
(
    [name] => alrahmahsolutions@gmail.com
    [email] => alrahmahsolutions@gmail.com
    [to] => alrahmahsolutions@gmail.com
    [cc] => alrahmahsolutions@gmail.com
    [subject] => alrahmahsolutions@gmail.com
    [message] => test
    [attachhostpath] => /home/www/web316.s146.goserver.host/fileupload/partials/../../files/clients/Al-Rahmah Solutions Inc. (220)/application/certificate/F0417_Offer Halal certification_EN_809_1166.pdf
    [attach] => F0417_Offer Halal certification_EN_809_1166.pdf
)

    */
    
    
    $logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs',Psr\Log\LogLevel::DEBUG,array('filename'=>'notifier.log'));
    //$ownerEmailAddress = "ovchinnikov.it@gmail.com";
    $mail = new PHPMailer;
    $mail->setFrom($body['email'], $body['name']);
    $mail->addReplyTo($body['email'], $body['name']);
    $recipients = explode(',', $body['to']);
    foreach($recipients as $r)
        $mail->addAddress(trim($r));
    if(!empty($body['cc'])) {
        $recipients = explode(',', $body['cc']);
        foreach ($recipients as $r)
            $mail->addCC(trim($r));
    }
    $mail->isHTML(true);
    $mail->Subject = $body['subject'];
    $mail->Body    = $body['message'];
    $mail->AltBody = $body['message'];
    if (is_array($body['attach'])) {
        // Loop through each attachment
        foreach ($body['attach'] as $attachment) {
            // Add attachment
            $mail->addAttachment(urldecode($attachment['hostpath']), $attachment['name']);
        }
    } else {
        // Single attachment
        $mail->addAttachment(urldecode($body['attachhostpath']), $body['attach']);
    }
    $logger->info("Trying to send email with attach to ".$body['to']. ((!empty($body['cc'])) ? ", ".$body['cc'] : ""));
    if(!$mail->send()) {
        $logger->info('Message could not be sent.');
        $logger->info('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    } else {
        $logger->info('Email has been sent to '.$body['email']);
        $logger->info($mail->Body);
        return true;
    }
}

function sendEmailCycleNotificationToClient ($clientData, $body) {
    $logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs',Psr\Log\LogLevel::DEBUG,array('filename'=>'notifier.log'));
    $ownerEmailAddress = "halal.ezone@gmail.com";
    $fromEmailAddress = "noreply@halalezone.com";
    $mail = new PHPMailer;
    $mail->setFrom($fromEmailAddress, 'HALAL eZone');
    $mail->addAddress($clientData['email'], $clientData['name']);
    $mail->addReplyTo($fromEmailAddress, 'HALAL eZone');
    $mail->addBCC($ownerEmailAddress);
    $mail->isHTML(true);
    $mail->addEmbeddedImage('../img/logo_email.png', 'logo');
    $mail->Subject = $body['subject'];
    $mail->Body    = getEmailTemplate($body);
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $logger->info("Trying to send email to the client [".$clientData['email']."]" );
    if(!$mail->send()) {
        $logger->info('Message could not be sent.');
        $logger->info('Mailer Error: ' . $mail->ErrorInfo);
    } else {
        $logger->info('Message has been sent');
        $logger->info($mail->Body);
    }
}