<?php
$clients_ids=array();
$result = MYSQL_QUERY("SELECT clid FROM invoices where paid_on!='' GROUP BY clid");
if (@MYSQL_NUM_ROWS($result) > 0){
WHILE ($row = MYSQL_FETCH_ARRAY($result)){
$clients_ids[] = $row['clid'];
}
}
mysql_free_result($result);
$result = MYSQL_QUERY("SELECT * FROM companies LEFT JOIN users ON  companies.clid = users.clid WHERE companies.clof='0' and users.active='y' order by companies.company_name ASC");
if (@MYSQL_NUM_ROWS($result) > 0){
$com_list = "<select size=1 name='comid'>\n<option value=''>Select a company</option>";
WHILE ($row = MYSQL_FETCH_ARRAY($result)){
if (in_array($row['clid'], $clients_ids))
{
$com_list .= "<option value='$row[clid]'>$row[company_name]</option>";
$clients[$row['clid']] = $row['company_name'];
}
}
$com_list .= "</select>";
}
?>
<script>
function check_span(spn)
{
document.all.search_date.style.display='none';
document.all.search_com.style.display='none';
if (spn=='date')
document.all.search_date.style.display='';
}
</script>
<table border="0" class=cer_td width=100%>
<form method="post" action="index.php?inc=search_result" name="seach_form">
<tr><td style="white-space: nowrap;">
<b>Search:<input type="text" size="15" name="srch4wt" />
<select size="1" name="searchby" onchange="check_span(this.value)" style="width:130px">
<option value="invNr">Invoice Number</option>
<option value="company">Company</option>
<option value="items">Items & description</option>
<option value="date">Date</option>
<option value="service_type">Service type</option>
<option value="amount">Amount</option>
</select>
<select name="paid" size="1">
<option value="all">All invoices</option>
<option value="paid">Paid invoices</option>
<option value="unpaid">Unpaid invoices</option>
</select>
<select name="office" size="1">
<option value="all">Both offices</option>
<option value="nl">NL office</option>
<option value="uae">UAE office</option>
</select>
<span id='search_date' style="display:none">
From:<input type="text" name="date_from" size=10 />
To:<input type="text" name="date_to" size=10 />
</span>
<input type="submit" value="Search"  style="width: 100">
</td>
</form>
<td>
</td></tr>
</table>