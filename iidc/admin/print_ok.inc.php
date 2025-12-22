<script>
$("#page_title").html("Printing a certificate")

function getdate(fld)
	{
var myday = showModalDialog("../certificates/get_date.html", "", "dialogHeight:150px; dialogWidth:120px; scroll: no; status: no; help: no;");
		if (myday){
        document.new_docs_form.sent_date.value=myday;
		}
	}
function send_docs()
{
if (document.new_docs_form.sent_date.value=="")
alert("All fields are required")
else
document.new_docs_form.submit();
}
$(document).ready(function(e) {
			$("#sent_date").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat:dateFormat
		});
});
</script>
<?php
if(in_array("home_ceriticate_actions",$user_permissions) or $username=="admin"){
include "../checkuser.inc.php";
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";

$result = MYSQL_QUERY("SELECT * FROM certificates_$tp where nr='$nr'");
if (@MYSQL_NUM_ROWS($result) > 0){
$row = MYSQL_FETCH_ARRAY($result);
$certificate_nr = $row['certificate_nr'];
$id = $row['clid'];
}

$company_name = '';
if (isset($id))
{
$result = MYSQL_QUERY("SELECT * FROM companies where clid='$id'");
if (@MYSQL_NUM_ROWS($result) > 0){
$row = MYSQL_FETCH_ARRAY($result);
$company_name = $row['company_name'];
}
}
?>
<form action="admin_save.php" name="new_docs_form" method=post>
<input type=hidden value='printok' name='act'>
<input type=hidden value='<?php echo $id?>' name='clid'>
<input type=hidden value='<?php echo  @$certificate_nr;?>' name='certificate_nr'>
<input type=hidden value='<?php echo  @$tp;?>' name='tp'>
<input type=hidden value='<?php echo  @$nr;?>' name='nr'>
<table border=0 id="PrintOk" class="alternate">
<tr>
<td colspan=2 class="sub_title">Please confirm the result of printing of the certificate</td>
</tr>
<tr><th><b>Certificate number:</th><td><b><?php echo  @$certificate_nr;?></td></tr>
<tr><th><b>Document number:</th><td><input type=text style='width:100px' name='doc_nr'></td></tr>
<tr><th><b>Document sent on:</th><td><input type=text style='width:100px' name='sent_date' id='sent_date' value="<?php echo date("d/m/Y");?>"></td>
</tr>
<tr><td colspan=2 class="sub_title"><center><input type="button" onclick="send_docs()" value=" Save " ></center></td></tr>
</form>
</table>
<?php }?>
