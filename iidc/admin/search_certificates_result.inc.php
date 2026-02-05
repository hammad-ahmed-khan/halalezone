<script>
$("#page_title").html("Search results")
function delcer(tp,nr)
{
if (confirm("Are you sure?")=="1")
document.location.href = "admin_save.php?act=delcer&tp="+tp+"&nr="+nr;
}

$(document).ready(function(e) {
			$("#arrival_date").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat:dateFormat
		});
});

function confirmByadmin(nr,hcNr){
	$("#hcNr").html("<b>"+hcNr+"</b>")
	$("#toBconfirmNr").val(nr)
	$("#arrival_date").val("")
	showPopupDialog('confirmTbl','confirm')
}

function doConfirm()
{
var time= new Date().getTime();

$.post("<?php echo $prog_www?>/company/company_save.php?tm="+time, {act: "cer_arrived",tp:'a',cba:'y',nr:$("#toBconfirmNr").val(),arrival_date:$("#arrival_date").val()},
function(data) {
		if (data!=""){
			if(data.indexOf('success')>-1){
			window.location.reload();
			}
		else
			{
			alert(data);
			}
		}
	 });
}

</script>
<?php
include "$prog_path/popup-dialog.inc.php";
include "../date-picker.inc.php";
include "search_for_certificates.inc.php";
?>
<table border=0 width=750 id="resTable" class="table table-striped table-bordered">
<tr>
<td colspan=6><b>Search results for <FONT COLOR=RED>
<?php
if(isset($searchCer))
{
if ($searchCer=="allCers")
echo "All CERTIFICATES";
if ($searchCer=="cerA")
echo "A CERTIFICATES";
if ($searchCer=="cerB")
echo "B CERTIFICATES";
}
?>
</FONT></b></td>
</tr>
<tr>
<th>No.</th>
<th>HcNr</th>
<th>DocNr</th>
<th>Company</th>
<th>Ref.</th>
<th>Admin</th>
<th>Client</th>
<?php if(in_array("certificates_actions",$user_permissions) or $username=="admin"){?>
<th>Action</th>
<?php } ?>
</tr>
<?php
include "../checkuser.inc.php";
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";
$no=0;

if (isset($_POST['year']))
$theYear = "and date like '%/$year%'";
else
$theYear = "";

if (trim($srch4wt)=="" and $_POST['year']=="")
$limit = "limit 0, 50";
else
$limit = "";

if(isset($searchCer))
{
if ($searchCer=="allCers")
{
getCetificates('certificates_a','Certificates A (Meat)','a');
getCetificates('certificates_b','Certificates B (Non meat)','b');
}
if ($searchCer=="cerA")
getCetificates('certificates_a','','a');
if ($searchCer=="cerB")
getCetificates('certificates_b','','b');
}


function getCetificates($tbl,$ttl,$tp)
{
global $no,$searchby,$srch4wt,$user_permissions,$username,$theYear,$limit;
if (trim($ttl)!="")
echo "<tr><td colspan=8><b><font color=red>$ttl</font></b></td></tr>";
$result = MYSQL_QUERY("SELECT * FROM $tbl where $searchby like '%$srch4wt%' $theYear $limit");
if (@MYSQL_NUM_ROWS($result) > 0){
WHILE ($row = MYSQL_FETCH_ARRAY($result)){
$no++;
echo "<tr>
<th>$no</th>
<td><a href='../certificates/pdf/pdf_certificate.php?tp=$tp&nr=$row[nr]&usr=a' target=_blank>$row[certificate_nr]</a></td>
<td>$row[doc_nr]</td>
<td>$row[company_name]</td>
<td>".str_replace(array("+","  +  ")," + ",$row['reference'])."</td>
<td>$row[hcd_process]</td>
<td>";
if (strstr($row['hcd_process'],'Authorised') and $row['printed_on'])
echo "Printed on: $row[printed_on]";
elseif ((strstr($row['hcd_process'],'Sent') or strstr($row['hcd_process'],'Printed') or strstr($row['hcd_process'],'Authorised')) and $row['arrived_on']){
if ($row['arrived_on_cba']=="y")
echo "<font color=\"red\">Arrived on: $row[arrived_on]</font>";
else
echo "Arrived on: $row[arrived_on]";
}
else{
	if(in_array("certificates_actions",$user_permissions) or $username=="admin"){
	echo "<input type=\"button\" onclick=\"confirmByadmin('$row[nr]','$row[certificate_nr]')\" value=\"Confirm\"/>";
	}
}
echo"</td>";
if(in_array("certificates_actions",$user_permissions) or $username=="admin"){
echo "<td><a  href=\"javascript:delcer('$tp',$row[nr])\"><img  title='Delete certificate' src=\"../images/delete.gif\" border=0></a></td>";
}
echo"</tr>";
}
}
}
?>
</table>

<table border=0 style="border:1px solid #EEE;display:none" id="confirmTbl">
<tr>
<th colspan=2>Please confirm the arrival of the certificate</th>
</tr>
<tr><th>Certificate number:</th><td id="hcNr"></td></tr>
<tr><th>Arrival date:</th><td><input type=text style='width:100px' name='arrival_date' id="arrival_date" />
<input type="hidden" value="" id="toBconfirmNr" /></td>
</tr>
<tr><td colspan=2 align=center class="sub_title"><center><input type="button" onclick="doConfirm()" value=" Save " /></center></td></tr>
</table>