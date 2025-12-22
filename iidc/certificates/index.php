<?php
if (count($_REQUEST)>0) {
foreach ($_REQUEST as $key => $value) {
$$key = $value;
}
}
date_default_timezone_set(@date_default_timezone_get());
if (isset($inc) and $inc!='')
{
  if ($inc=='register')
  $ttl='Register a company';
  if ($inc=='msgs')
  $ttl='';
  if ($inc=='profile')
  $ttl='Update company profile';
}
else
{
$inc="";
}
include "../home.php";
?>


