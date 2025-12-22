<?php
if(!defined("__HQC__"))
exit();
require("class.phpmailer.php");
include "../../config/invoice_email_data.inc.php";

$mail = new PHPMailer();
//$client_email = "info.nouras@gmail.com";
$mail->isMail();                                      // set mailer to use SMTP
$mail->Host = "localhost";  // specify main and backup server
$mail->SMTPAuth = false;     // turn on SMTP authentication
$mail->Username = "";  // SMTP username
$mail->Password = ""; // SMTP password

$mail->From = $invoice_my_email[$template];
$mail->FromName = $invoice_my_name[$template];

$mail->AddAddress($client_email,$client_name);
if (isset($emailmeacopy))
$mail->AddBcc($invoice_my_email[$template], $invoice_my_name[$template]);

$mail->AddReplyTo($invoice_my_email[$template], $invoice_my_name[$template]);

$mail->WordWrap = 50;                                 // set word wrap to 50 characters
$mail->AddAttachment(dirname(__FILE__)."/tem/$invoiceNr.pdf");         // add attachments
$mail->AddAttachment("Invoice $invoiceNr");    // optional name
$mail->IsHTML(true);                                  // set email format to HTML

$mail->Subject = $invoice_subject[$template];
$mail->Body    = str_replace("\n","<br>",$invoice_body[$template]);

if(!$mail->Send())
{
   echo "Message could not be sent. <p>";
   echo "Mailer Error: " . $mail->ErrorInfo;
   exit;
}
?>