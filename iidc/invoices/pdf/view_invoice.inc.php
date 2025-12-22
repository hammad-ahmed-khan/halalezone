<?php
if (isset($_GET['nr'])){
$nrIndex = 0;
$invItems = array();
if($invoice = $amdb->get_row("SELECT * FROM invoices WHERE nr='$_GET[nr]'")){
	if (trim($invoice['invoice_items'])!='' and is_array(json_decode(str_replace("\r\n","<br/>",$invoice['invoice_items']),true))){
		$invItems = json_decode(str_replace("\r\n","<br/>",$invoice['invoice_items']),true);
	} else {
	
	$invoice_items = explode("\n",$invoice['invoice_items']);
	
	foreach($invoice_items as $item){
		if(trim($item)!=''){
			$nrIndex++;
			$invItem = explode('|',$item);
			$invItems[$nrIndex]['selected'] = $invItem[0];
			$invItems[$nrIndex]['certificate'] = $invItem[1];
			$invItems[$nrIndex]['type'] = $invItem[4];
			$invItems[$nrIndex]['product'] = ($invItem[4]=='a')?'Certificate A (meat)':'Certificate B (none meat)';
			$invItems[$nrIndex]['description'] = 'Certificate Nr: '.$invItem[1]."\r\n".'Date: '.$invItem[2];
			if(trim($invItem[5])!='')
			$invItems[$nrIndex]['description'] .= "\r\n".'reference: '.str_replace('_',' ',$invItem[5]);
			$invItems[$nrIndex]['amount'] = $invItem[3];
		}
	};
	}
	
	$_POST['FPC'] = '';
	$_POST['LPC'] = '';
	
	
	$_POST = $invoice;
	$_POST['clid'] = $invoice['clid'];
	$_REQUEST['clid'] = $_POST['clid'];
	$_POST['service_type'] = $invoice['service_type'];
	$_POST['item'] = $invItems;
	$_POST['act'] = 'preview';
	} else {
		exit();
	}
}