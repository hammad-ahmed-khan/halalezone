<?php
if(in_array("invoices_search",$user_permissions) or $username=="admin") {
include "../checkuser.inc.php";
include "../config/paths.inc.php";
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";
if(in_array("invoices_show_nl",$user_permissions) or $username=="admin")
$show_nl = true;

if(in_array("invoices_show_uae",$user_permissions) or $username=="admin")
$show_uae = true;
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
$("#serachResTd").attr("colspan",$("#headerTh > th").length)    
</script>
<p>
<?php include "search_engine.inc.php";?>
</p>
<table id="table1" class="alternate">
<td colspan="11" class="sub_title" id="serachResTd"><b>Search result</td>
</tr>
<tr id="headerTh">
<th>Nr</th>
<th>Invoice Nr</th>
<th>Date</td>
<th>Subtotal</th>
<th>VAT</th>
<th>Total</th>
<th>Company</th>
<th>Service</th>
<th>Paid on</th>
<th>Remarks</th>
<th>Office</th>
</tr>
<?php
$nr=0;
$total=0;
$srchq='';
if (isset($srch4wt) and $srch4wt!="")
{
if ($searchby=="invNr")
$srchq.=" and invoice_nr like '%$srch4wt%'";
if ($searchby=="items")
$srchq.=" and invoice_items like '%$srch4wt%'";
if ($searchby=="amount")
$srchq.=" and total='$srch4wt'";
if ($searchby=="service_type")
$srchq.=" and service_type like'%$srch4wt%'";
if ($searchby=="company")
$srchq.=" and company_name like'%$srch4wt%'";
}

if ($searchby=="date")
{
if(isset($date_from) and $date_from!='' and strstr($date_from,"/"))
{
$date_from = explode("/",$date_from);
if (strlen($date_from[0])==1)
$date_from[0] = "0".$date_from[0];
if (strlen($date_from[1])==1)
$date_from[1] = "0".$date_from[1];
$srchq.=" and ymd>=$date_from[2]$date_from[1]$date_from[0]";
}

if(isset($date_to) and $date_to!='' and strstr($date_to,"/"))
{
$date_to = explode("/",$date_to);
if (strlen($date_to[0])==1)
$date_to[0] = "0".$date_to[0];
if (strlen($date_to[1])==1)
$date_to[1] = "0".$date_to[1];
$srchq .=" and ymd<=$date_to[2]$date_to[1]$date_to[0]";
}
}

$subtotal =0;
$vat =0;
$total =0;

$result = MYSQL_QUERY("SELECT * FROM companies,invoices where companies.clid=invoices.clid $srchq order by template");
if (@MYSQL_NUM_ROWS($result) > 0){
while($row = MYSQL_FETCH_ARRAY($result)){
if (($row['template'] == 'uae' && isset($show_uae)) or ($row['template'] == 'nl' && isset($show_nl))) {	
$nr++;
if ($row['service_type']=="CN"){
	$subtotal=$subtotal - $row['subtotal'];
	$vat=$vat - $row['vat'];
	$total=$total - $row['total'];
}
else {
	$subtotal=$subtotal + $row['subtotal'];
	$vat=$vat + $row['vat'];
	$total=$total + $row['total'];
}

if ($row['service_type']=="CN")
	{
	$st = 'CN';
	}
	else
	{
if ($row['service_type'] != 'HQC' and $row['service_type'] != 'COHS')
$st = 'OTHER';
else
$st =  $row['service_type'];
}
?>
<tr>
<th><?php echo $nr?></th>
<td><a href="pdf/show_invoice.php?nr=<?php echo $row['nr'];?>&st=<?php echo $st;?>&tmpl=<?php echo $row['template'];?>" target="_blank"><b><?php echo $row['invoice_nr'];?></td>
<td><?php echo $row['date'];?></td>
<td>
<?php
if ($row['service_type']=="CN")
echo "<font color=red><i>-&euro;".number_format($row['subtotal'],2)."</i></font>";
else
echo "&euro;".number_format($row['subtotal'],2);
?>
</td><td>
<?php
if ($row['service_type']=="CN")
echo "<font color=red><i>-&euro;".number_format($row['vat'],2)."</i></font>";
else
echo "&euro;".number_format($row['vat'],2);
?>
</td><td>
<?php
if ($row['service_type']=="CN")
echo "<font color=red><i>-&euro;".number_format($row['total'],2)."</i></font>";
else
echo "&euro;".number_format($row['total'],2);
?>
</td>
<td><?php echo $row['company_name'];?></td>
<td><?php echo $row['service_type'];?></td>
<td class="cer_td"><?php echo $row['paid_on'];?></td>
<td><?php echo  strtoupper($row['remarks']);?></td>
<td><?php echo  strtoupper($row['template']);?></td>
</tr>
<?php
}
}
if(in_array("invoices_totals",$user_permissions) or $username=="admin") {
?>
<tr>
<td align="right" colspan="3" class="cer_td"><b>Totals:</td>
<th><font color="red">&euro;<?php echo number_format($subtotal,2);?></font></th>
<th><font color="red">&euro;<?php echo number_format($vat,2);?></font></th>
<th colspan="6"><font color="red">&euro;<?php echo number_format($total,2);?></font></th>
</tr>
<?php
}
}
?>
</table>
<?php }?>
			
