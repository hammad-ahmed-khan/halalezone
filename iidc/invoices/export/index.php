<?php
if (isset($_GET['tmpl']))
$_SESSION["template"]=$_GET['tmpl'];
else
$_SESSION["template"]="";

if (!isset($inc))
{
$inc="export";
$ttl='Export invoices';
}

include "../../home.php";
?>



