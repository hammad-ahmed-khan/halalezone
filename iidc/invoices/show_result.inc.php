<?php
if(in_array("invoices_search",$user_permissions) or $username=="admin") {
include "../checkuser.inc.php";
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";
?>
<script>
$("#page_title").html("Search Results")
function getdate(nr,clid)
	{
var myday = showModalDialog("get_date.html", "", "dialogHeight:175px; dialogWidth:120px; scroll: no; status: no; help: no;");
		if (myday){
        document.location.href='index.php?act=paidOn&nr='+nr+myday;
		}
	}
</script>	
<p>
<table>
<tr><td colspan="9">
<?php include "search_engine.inc.php";?>
</td></tr>
<tr><td colspan="9" height="20"></td></tr>
<tr>
<td colspan="9"><b>Search result</b></td>
</tr>
<tr>
<td class="title"><b>Nr</b></td>
<td class="title"><b>Invoice Nr</b></td>
<td class="title"><b>Date</b></td>
<td class="title"><b>Amount</b></td>
<td class="title"><b>Company</b></td>
<td class="title"><b>Service type</b></td>
<td class="title"><b>Paid on</b></td>
<td class="title"><b>Remarks</b></td>
<td class="title"><b>Office</b></td>
</tr>
<?php
$nr=0;
$total=0;
$srchq='';

if (isset($paid) and $paid!="")
{
if ($paid=="all")
$paidOn="";
if ($paid=="paid")
$paidOn="and paid_on!=''";
if ($paid=="unpaid")
$paidOn="and paid_on=''";
}

if (isset($office) and $office!="")
{
if ($office=="all")
$tmpl="";
else
$tmpl="and template='$office'";
}

if (isset($srch4wt) and $srch4wt!="")
{
if ($searchby=="invNr")
$srchq=" and invoice_nr='$srch4wt'";
if ($searchby=="items")
$srchq=" and invoice_items like '%$srch4wt%'";
if ($searchby=="amount")
$srchq=" and total='$srch4wt'";
if ($searchby=="company")
$srchq=" and company_name like '%$srch4wt%'";
if ($searchby=="service_type")
$srchq=" and service_type like'%$srch4wt%'";
}

if ($searchby=="date")
{
if(isset($date_from) and strstr('/',$date_from))
{
$date_from = explode('/',$date_from);
$srchq=" and ymd=>'$date_from[2]$date_from[1]$date_from[0]'";
}
if(isset($date_to) and strstr('/',$date_to))
{
$date_to = explode('/',$date_to);
$srchq=" and ymd<='$date_to[2]$date_to[1]$date_to[0]'";
}
}
$result = MYSQL_QUERY("SELECT * FROM companies,invoices where companies.clid=invoices.clid $paidOn $srchq $tmpl order by invoice_nr");
if (@MYSQL_NUM_ROWS($result) > 0){
while($row = MYSQL_FETCH_ARRAY($result)){
$nr++;
$total=$total + $row['total'];
?>
<?php
if ($row['service_type'] != 'HQC' and $row['service_type'] != 'COHS')
$st = 'OTHER';
else
$st =  $row['service_type'];
?>
<tr>
<td bgcolor="#eeeeee"><?php echo $nr?></td>
<td bgcolor="#eeeeee"><a href="pdf/show_invoice.php?nr=<?php echo $row['nr'];?>&st=<?php echo $st;?>&tmpl=<?php echo $row['template'];?>" target="_blank"><b><?php echo $row['invoice_nr'];?></b></td>
<td bgcolor="#eeeeee"><?php echo $row['date'];?></td>
<td bgcolor="#eeeeee">&euro;<?php echo number_format($row['total'],2);?></td>
<td bgcolor="#eeeeee"><?php echo $row['company_name'];?></td>
<td bgcolor="#eeeeee"><?php echo $st;?></td>
<td class="cer_td"><?php echo $row['paid_on'];?></td>
<td bgcolor="#eeeeee"><?php echo  strtoupper($row['remarks']);?></td>
<td bgcolor="#eeeeee"><?php echo  strtoupper($row['template']);?></td>
</tr>
<?php
}
?>
<tr>
<td align="right" colspan="3" class="cer_td"><b>Total:</b></td>
<td class="cer_td" colspan="6"><font color="red">&euro;<?php echo number_format($total,2);?></font></td>
</tr>
<?php
}
?>
</table>
<?php }?>
			
