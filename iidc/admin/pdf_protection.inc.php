<script>
jQuery("#page_title").html("PDF file protection");
function protect_pdf_file(obj){
	if(obj.checked)
		jQuery("#protected_pdf_password").attr("data-required","yes")
	else
	jQuery("#protected_pdf_password").removeAttr("data-required")
}
</script>
<?php
$options =  array();

if($options = get_option('protected_pdf'))
$options = json_decode($options,true);
?>
<div style="width:400px;margin:0 auto">
<form action="pdf_protection_save.php" onSubmit="return post_this_form(this)" id="pdfProtectionForm" name="pdfProtectionForm" data-target="_blank" data-error="Password is required"/>
<table class="alternate" style="width:100%">
<tr>
	<th class="subTitle" colspan="2">PDF file protection</th></tr>
<tr>
	<th>Protect PDF files:</th>
    <td><label><input type="checkbox" name="protected_pdf[protect]" onclick="protect_pdf_file(this)" value="yes" <?php echo (isset($options['protect']))?'checked':'';?>/>Yes</label>
    </td>
</tr>
<tr>
	<th>Password:</th>
    <td>
    <input type="text" name="protected_pdf[password]" id="protected_pdf_password" placeholder="Password" value="<?php echo (isset($options['password']))?$options['password']:'';?>" <?php echo (isset($options['protect']))?'data-required="yes"':'';?>/>
    </td>
    </tr>
<tr><th>Protected files:</th>
	<td>
    	<ul style="margin:0px;padding:0px;">
		<li><label><input type="checkbox" name="protected_pdf[annual]" <?php echo (isset($options['annual']))?'checked':'';?>/>Annual certificates</label></li>
        <li><label><input type="checkbox" name="protected_pdf[batch]" <?php echo (isset($options['batch']))?'checked':'';?>/>Batch certificates</label></li>
        <li><label><input type="checkbox" name="protected_pdf[invoices]" <?php echo (isset($options['invoices']))?'checked':'';?>/>Invoices</label></li>
        </ul>
     </td>
</tr>
</table>
<center><input type="submit" value="Save"/></center><br/>
    Protecting PDF files with password prevents clients from editing, modifying or copying the contents of the file and extracting or adding pages.<br/>
The client can only print or save the PDF file</div>