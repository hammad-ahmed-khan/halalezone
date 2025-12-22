<?php
if(!defined("_HQC_"))
exit();
?>
<script>
jQuery(".ui-dialog .ui-dialog-buttonpane",window.parent.document).remove();
</script>
<?php
if (isset($_REQUEST['act']) and isset($_REQUEST['nrs'])){
	
	if($_REQUEST['act']=='final')
		$reminderTemplate = 'final_reminder';
		elseif($_REQUEST['act']=='suspend')
		$reminderTemplate = 'account_suspension';
		else
		$reminderTemplate = 'reminder';

	if(!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='$reminderTemplate'"))
	$row = $amdb->get_columns('invoice_templates');	
	
	if (!$invoices = $amdb->get_results("SELECT company_name,companies.clid,invoices.nr,invoices.invoice_nr from companies 
											JOIN invoices on companies.clid = invoices.clid
											WHERE FIND_IN_SET(invoices.nr,'$_REQUEST[nrs]')"))
	return;
?>  
<style>
table#remidenerTable td b {
    display: inline-block;
    width: 100px;
	float:left;
}
table#remidenerTable td input[type='text'] {
    width: 95%;
}
table#remidenerTable th{white-space:nowrap;padding:10px}
</style>
<form action="invoice_bulk_save.php" method="post" name="postReminder" id="postReminder" onsubmit="return post_this_form(this)" target="">
<input type="hidden" name="act" value="<?php echo $_REQUEST['act'];?>"/>
<table class="alternate" style="width:100%;" id="remidenerTable">
<tr><th>Invoices:</th><td colspan="3">
<ol style="padding:0px;margin:0px;max-height:100px;overflow:auto">
<?php foreach($invoices as $invoice){?>
<li><label><input type="checkbox" name="nr[]" checked="checked" value="<?php echo $invoice['nr'];?>"/><?php echo $invoice['invoice_nr'];?> <?php echo $invoice['company_name'];?></label> </li>
<?php };?>
</ol>
</td></tr>
<tr><th>Email Subject:</th><td colspan="3"><input type="text" name="email[subject]" data-required="yes" value="<?php echo $row['email_subject'];?>"/></td></tr>
<tr><th colspan="4">Email body</th></tr>
<tr><td colspan="4"><textarea class="tinymce_minimum" name="email[message]" style="height:250px;"><?php echo $row['email_body'];?></textarea></td></tr>
<tr><th style="width:100px">Reply address:</th><td style="width:200px"><input type="text" name="email[from_email]" data-required="yes" value="<?php echo $row['email_reply_address'];?>"/></td>
<th style="width:100px">Sender name:</th><td><input type="text" name="email[from_name]" data-required="yes" value="<?php echo $row['email_sender_name'];?>"/></td></tr>
<tr><th colspan="4">
<div style="text-align:center"><input value="Send bulk reminder" type="submit"/><input type="reset" value="Reset"/>
<input type="button" value="Cancel" onClick="closePopupDialog()" data-type="cancel"/></div>
</th>
</tr>
</table>
</form>
<script>
do_tinymce_minimum();
</script>
<?php 
exit();};