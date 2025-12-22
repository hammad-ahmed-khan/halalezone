<?php
exit();
include "config/mysql_ftp.inc.php";
include "config/connect.inc.php";
//$result = MYSQL_QUERY("SELECT * FROM $tbl[prefix]_halal_certificates, invoices where  $tbl[prefix]_halal_certificates.clid = invoices.clid  and $tbl[prefix]_halal_certificates.invoice_nr='' and invoices.service_type like'%HQC%'");

$crtNrs=array();
$result = mysql_query("SELECT * FROM acms_halal_certificates WHERE invoice_nr = ''");
if (@MYSQL_NUM_ROWS($result) > 0){
WHILE ($row = MYSQL_FETCH_ARRAY($result)){
$crtNrs[$row['clid']] = $row['crtNr'] ;
}
}

$result = mysql_query("SELECT * FROM invoices WHERE service_type like '%HQC%'");
if (@MYSQL_NUM_ROWS($result) > 0){
WHILE ($row = MYSQL_FETCH_ARRAY($result)){
//$items = explode ("|",$row['invoice_items']);
if (isset($crtNrs[$row['clid']])){
echo $row['clid'].": ".$crtNrs[$row['clid']]."<br/>";
//mysql_query("update acms_halal_certificates  set invoice_nr = '$row[invoice_nr]' WHERE crtNr = '{$crtNrs[$items[0]]}' and invoice_nr = ''");
}
}
}
