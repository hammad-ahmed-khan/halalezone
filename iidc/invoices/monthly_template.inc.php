<script>
$("#page_title").html("Email Template")
</script>

<style>
table td b {
    display: inline-block;
    width: 100px;
	float:left;
}
table td div {
	margin:4px;
	display:flex;
}
table td input {
    width: 95%;
}
table td div input {
    width: 200px;
}

table th{white-space:nowrap}
</style>
<form action="monthly_invoices_save.php" onSubmit="return post_this_form(this)" id="officeForm" name="officeForm" data-error="All fields are required" >
<?php 

	if(!$row = $amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='recurring_invoice'"))
	$row = $amdb->get_columns('invoice_templates');
		
?>
<input type="hidden" name="act" value="update_template"/>
<input type="hidden" name="template_name" value="recurring_invoice"/>
<table class="alternate" style="width:100%;margin-top:20px">
<tr>
<th style="text-transform:capitalize;color:#900" colspan="4"><center>Edit email template</center></th>
</tr>
<tr><th style="width:100px">Reply address:</th><td style="width:400px"><input type="text" name="email_reply_address" data-required="yes" value="<?php echo $row['email_reply_address'];?>"/></td>
<th style="width:100px">Sender name:</th><td><input type="text" name="email_sender_name" data-required="yes" value="<?php echo $row['email_sender_name'];?>"/></td></tr>
<tr><th>Email Subject:</th><td colspan="3"><input type="text" name="email_subject" data-required="yes" value="<?php echo $row['email_subject'];?>"/></td></tr>
<tr><th colspan="4">Email body</th></tr>
<tr>
<tr><td colspan="4">
<textarea class="tinymce" name="email_body" style="height:400px;"><?php echo $row['email_body'];?></textarea>
</td>
</tr>
</table>
<table class="alternate" style="width:100%;margin-top:20px">
<tr>
<th style="text-transform:capitalize;color:#900" colspan="4"><center>Edit pdf template</center></th>
</tr>
<tr><td colspan="4">
<textarea class="tinymce" name="pdf_template" style="height:200px;"><?php echo $row['pdf_template'];?></textarea>
</td>
</tr>
</table>
<center>
<span class="info">All fields are required</span>
<input type="submit" value="Save" /> 
<input type="reset" value="Reset"/>
</center>
</form>