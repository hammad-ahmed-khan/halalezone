<?php
if (isset($_GET['tmpl']))
$_SESSION["template"]=$_GET['tmpl'];
else
$_SESSION["template"]="";

if (!isset($inc))
{
$inc="invoices_home";
$ttl='Invoices';
}

if (trim($_SESSION["template"])!="")
$ttl .= " - " . strtoupper($_SESSION['template']);

include "../home.php";
?>
