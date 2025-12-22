<script>
$("#page_title").html("Requested Certificates A (Meat)")

function delcer(nr)
{
if (confirm("Are you sure?")=="1")
document.location.href = "admin_save.php?act=delcer&tp=a&nr="+nr;
}

function badCer(goodBad,nr){
if (confirm("Are you sure?")=="1"){
var time= new Date().getTime();
$.post("<?php echo $prog_www?>/admin/admin_save.php?tm="+time, {act: "badCer",tp:'a',nr:nr,goodBad:goodBad},
function(data) {
		if (data!=""){
			if(data.indexOf('success')>-1){
			document.location = document.location.href
			}
		else
			{
			alert(data);
			}
		}
	 });
}
}

function fixCerNr(nr,doc_nr){
var time= new Date().getTime();
$.post("<?php echo $prog_www?>/admin/certificates_save.php?tm="+time, {act: "fixCerNr",tp:'a',nr:nr,doc_nr:doc_nr},
function(data) {
		if (data!=""){
			if(data.indexOf('success')>-1){
			document.location = document.location.href
			}
		else
			{
			alert(data);
			}
		}
	 });
}

$(document).ready(function(e) {
	alternateColors('#certificatesA');
    switchRowColor('#certificatesA','#FFFFCC')

$(".crtDocNr").css({"cursor":"pointer"});
  $('#certificatesA tr').bind('mouseenter',function() {
 	$('#crtId').val($(this).attr('data-crtNr'));
});

  $('#certificatesA .crtDocNr').bind('click',function() {
 	$('#crtDocNr').val($(this).attr('data-crtDocNr'));
	$('#crtDocNr').css({"width":$(this).width()});
	var position = $(this).position();
	$("#fixDocNrDiv").css({"left":position.left+"px","top":position.top+"px","display":"block"} );
});

$("#crtDocNr").keypress(function(event) {
  if ( event.which == 13 ) {
    fixCerNr($('#crtId').val(),$('#crtDocNr').val());
   }
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
			document.location = document.location.href
			}
		else
			{
			alert(data);
			}
		}
	 });
}
</script>
<?php /*
include "$prog_path/popup-dialog.inc.php";
include "../date-picker.inc.php";
include "../checkuser.inc.php";
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php"; */
?>
<div id="fixDocNrDiv" style="display:none;position:absolute">
<input type="hidden" id="crtId" />
<input type="text" id="crtDocNr" />
</div>
<?php if(isset($_SESSION['offid']) or $_SESSION['offid']=='0'){?>
<center>
<select size="1" name="offid" onchange="document.location='<?php echo $prog_www;?>/admin/?inc=certificates_a&offid='+this.value;">
<option value="">Select office</option>
<?php
$offices = $amdb->query("SELECT * FROM offices WHERE status != 'deleted'");
if (count($offices) > 0){

include "$hcp_path/config/countries.code.php";
$nr=1;
foreach ($offices as $office){ ?>
<option value="<?php echo $office['offid'];?>" <?php echo (isset($_GET['offid']) && $_GET['offid']==$office['offid'])?'selected':'';?>><?php echo $country[$office['office_country']];?> - <?php echo $office['office_name'];?></option>
<?php
}
}
?>
</select>
</center>
<?php
} else {
$_GET['offid'] = $_SESSION['offid'];
}
if (!isset($_GET['offid']) or trim($_GET['offid'])==''){
return;
}
?>
<table border=0 width=750 id="certificatesA" style="border:1px solid #EEE">
<tr>
<td colspan=8 class="sub_title"><b>Requested certificates <FONT COLOR=RED>CERTIFICATES A</FONT></b></td>
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
$no=0;
if (isset($_GET['y']) and trim($_GET['y'])!="")
$whr = "and date like '%$_GET[y]%'";
else
$whr = "and hcd_process!='' and invoice_nr='0'";
$result = MYSQL_QUERY("SELECT * FROM certificates_a where offid='$_GET[offid]' $whr");
if (@MYSQL_NUM_ROWS($result) > 0){
WHILE ($row = MYSQL_FETCH_ARRAY($result)){
$no++;
echo "<tr data-crtNr='$row[nr]'>
<th>$no</th>
<td><a href='../certificates/pdf/pdf_certificate.php?tp=a&nr=$row[nr]&usr=a' target=_blank>$row[certificate_nr]</a></td>
<td class='crtDocNr' data-crtDocNr='$row[doc_nr]'>";
if ($row['is_bad']=="y")
echo "<span style=\"text-decoration:line-through;color:red;cursor:pointer\" onclick=\"badCer('n',$row[nr])\">$row[doc_nr]</span>";
else
echo $row['doc_nr'];
echo "</td>
<td>$row[company_name]</td>
<td>".str_replace(array("+","  +"),array(" +"," +"),$row['reference'])."</td>
<td>$row[hcd_process]</td>
<td>";
if (strstr($row['hcd_process'],'Authorised') and $row['printed_on'])
echo "Printed on: $row[printed_on]";
elseif ((strstr($row['hcd_process'],'Sent') or strstr($row['hcd_process'],'Printed') or strstr($row['hcd_process'],'Authorised')) and trim($row['arrived_on'])!=''){
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
	echo"<td>";
	if ($row['is_bad']=="y")
	echo"<img width='22' title='Not bad certificate' src=\"../images/bad_document32.png\" onclick=\"badCer('n',$row[nr])\">";
	else
	echo"<img width='22' title='Bad certificate' src=\"../images/bad_document32_grey.png\" onclick=\" badCer('y',$row[nr])\">";

	echo "<img  title='Delete certificate' src=\"../images/delete.gif\" onclick=\"delcer($row[nr])\"></td>";
}
echo"</tr>";
}
}
?>
</table>

<div style="border:1px solid #EEE;display: none;width: 350px" id="confirmTbl">
<table border=0>
<tr>
<th colspan=2>Please confirm the arrival of the certificate</th>
</tr>
<tr><th>Certificate number:</th><td id="hcNr"></td></tr>
<tr><th>Arrival date:</th><td><input type=text style='width:100px' name='arrival_date' id="arrival_date" class="date" />
<input type="hidden" value="" id="toBconfirmNr" /></td>
</tr>
<tr><td colspan=2 align=center class="sub_title"><center><input type="button" onclick="doConfirm()" value=" Save " /></center></td></tr>
</table>
</div>
