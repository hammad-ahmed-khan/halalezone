<?php
define("__HQC__",true);
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
include $prog_path."/tools/mail/hqc_mail.inc.php";
$totalEmail = 0;

	if($_REQUEST['act']=='final')
		$reminderTemplate = 'final_reminder';
		elseif($_REQUEST['act']=='suspend')
		$reminderTemplate = 'account_suspension';
		else
		$reminderTemplate = 'reminder';
	$_POST['nrs'] = implode(',',$_POST['nr']);
	if (!$invoices = $amdb->get_results("SELECT companies.clid,invoices.nr,invoices.invoice_nr,invoices.reminded_on from companies 
											JOIN invoices on companies.clid = invoices.clid
											WHERE FIND_IN_SET(invoices.nr,'$_POST[nrs]')"))
	return;

	foreach($invoices as $invoice){
	$invFile = $prog_path."/client_data/invoices/$invoice[invoice_nr].pdf";
		if (file_exists($invFile)){
			$email = $_POST['email'];
		   	$email['emailmeacopy'] = false;
			$cla = invoice_address($invoice['clid']);
		
			$invoice_address = json_encode($cla,true);
			$data['client_address'] = $cla['address'];
			$data['company_name'] = $cla['company_name'];
			$data['client_name'] = $cla['client_name'];
			$data['client_email'] = $cla['client_email'];
			$data['invoice_nr'] = $invoice['invoice_nr'];
			$email['to_email'] = $data['client_email'];
			$email['to_name'] = $data['client_name'];
		
			foreach($data as $key=>$value){
				$email['subject'] = str_replace('['.$key.']',$value,$email['subject']);
				$email['message'] = str_replace('['.$key.']',$value,$email['message']);
			}
			
			if (isset($_POST['do_test']) and trim($_POST['test_email'])!=''){
				$email['to_email'] = $_POST['test_email'];
				$email['subject'] = '(TEST MESSAGE) ' .$email['subject'];
				$email['emailmeacopy'] = false;
				$_POST['act'] = 'test';
			}
			$email['attachments'] = array('invoice-'.$invoice['invoice_nr'].'.pdf',$invFile);
			$reminded_on = array();
			if(trim($invoice['reminded_on'])!='')
			$reminded_on = explode(',',$invoice['reminded_on']);
			$reminded_on[] = date('d/m/Y');
			$reminded_on = implode(',',$reminded_on);

			if(hqc_mail($email['to_email'],$email['to_name'],$email['from_email'],$email['from_name'],$email['subject'],$email['message'],$email['attachments'],$email['emailmeacopy'])){
			if($_POST['act']=="test"){
				$amdb->post_results('Test email is sent. Please check your email.');
				exit();
			} else {
				$amdb->query("UPDATE invoices SET reminded_on='".$reminded_on."' WHERE nr='$invoice[nr]'");	
					$totalEmail++;			
				}
			}
		}
	}
	
$amdb->post_results('top.closePopup();top.document.getElementById("contents").contentWindow.loadInvoices(\'new\')','function');
$amdb->post_results($totalEmail.' Bulk Reminder email(s) sent.');	
exit();	