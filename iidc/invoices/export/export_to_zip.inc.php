<?php
if(count($invoices)>0){
	$zip = new ZipArchive;

	$zipFile = $prog_path.'/data/temp/invoices.zip';
	if(file_exists($zipFile))
	unlink($zipFile);
if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE)
{
	foreach($invoices as $invoice){
		$invFile = "/client_data/invoices/$invoice[invoice_nr].pdf";
	if (!file_exists($prog_path.$invFile)){
	if (trim($invoice['invoice_items'])!='' and is_array(json_decode(str_replace("\r\n","<br/>",$invoice['invoice_items']),true))){
		creatInvoiceFile($invoice['nr'],$invoice['invoice_nr']);
	}
	}
	$zip->addFile($prog_path.$invFile,$invoice['invoice_nr'].'.pdf');
	}
$zip->close();

if($_POST['show']=='credit')
$zipTitle = 'credit notes - '.$_POST['year'];
else
$zipTitle = $_POST['show']." invoices - ".$_POST['year'];

if($_POST['period']=='month')
$zipTitle .= ' - month '.$_POST['month'].'';

if($_POST['period']=='quarter')
$zipTitle .= ' - quarter '.$_POST['quarter'];

if($_POST['period']=='date')
$zipTitle = str_replace('-20','-','invoices '.str_replace('/','-',$_POST['date_from']).' to '.str_replace('/','-',$_POST['date_to']));

header('Content-Type: application/zip');
header('Content-disposition: attachment; filename='.$zipTitle.'.zip');
header('Content-Length: ' . filesize($zipFile));
readfile($zipFile);
};
}