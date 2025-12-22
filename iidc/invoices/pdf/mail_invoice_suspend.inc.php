<?php
if(!defined("__HQC__"))
exit();
require("class.phpmailer.php");
include "../../config/invoice_email_data.inc.php";

$mail = new PHPMailer();
$client_email = $suspend_my_email[$template];
$mail->isMail();                                      // set mailer to use SMTP
$mail->Host = "localhost";  // specify main and backup server
$mail->SMTPAuth = false;     // turn on SMTP authentication
$mail->Username = "";  // SMTP username
$mail->Password = ""; // SMTP password

$mail->From = $suspend_my_email[$template];
$mail->FromName = $suspend_my_name[$template];

$mail->AddAddress($client_email,$client_name);
$mail->AddBcc($invoice_my_email[$template], $invoice_my_name[$template]);
$mail->AddReplyTo($suspend_my_email[$template], $suspend_my_name[$template]);

$mail->WordWrap = 50;                                 // set word wrap to 50 characters
$mail->AddAttachment(dirname(__FILE__)."/tem/$invoiceNr.pdf");         // add attachments
$mail->AddAttachment("Invoice $invoiceNr");    // optional name
$mail->IsHTML(true);                                  // set email format to HTML

$mail->Subject = $suspend_subject[$template];
$mail->Body    = str_replace("\n","<br>",$suspend_body);

if(!$mail->Send())
{
   echo "Message could not be sent. <p>";
   echo "Mailer Error: " . $mail->ErrorInfo;
   exit;
}
else
{
MYSQL_QUERY("update users set active='n' WHERE clid='$clid'");
MYSQL_QUERY("update companies set active='n' WHERE clid='$clid'");
}
?>