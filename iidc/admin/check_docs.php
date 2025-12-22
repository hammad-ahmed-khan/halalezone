<script>
parent.document.all.usedDocs.style.display='none';
</script>
<?php
if (count($_REQUEST)>0) {
foreach ($_REQUEST as $key => $value) {
$$key = $value;
}
}
if (isset($docs)and $docs!='')
{
include "../config/mysql_ftp.inc.php";
include "../config/connect.inc.php";
include "../config/paths.inc.php";
  if(strstr($docs,"-"))
  {
    $clDocNr = explode("-",$docs);
	$t_number='00000';
	$certificate_nrs = "certificate_nr='".substr($t_number,0,-strlen($clDocNr[0])).$clDocNr[0]."'";
    for($i=trim($clDocNr[0])+1;$i<=trim($clDocNr[1]);$i++)
    {
	  $t_number='00000';
	  $certificate_nr = substr($t_number,0,-strlen($i)) . $i;
     $certificate_nrs  .= " or certificate_nr='$certificate_nr'";
}
$found_nrs ="<B>The following numbers are already taken</B><BR>";
$result = MYSQL_QUERY("SELECT * FROM hc_doc_numbers where $certificate_nrs");
if (@MYSQL_NUM_ROWS($result) > 0){
while($row = MYSQL_FETCH_ARRAY($result)){
$found_nrs .= $row['certificate_nr'] ." ";
}
echo "<script>
parent.document.getElementById('usedDocs').innerHTML=\"$found_nrs\";
parent.document.getElementById('usedDocs').style.display='';
</script>";
}
else
{
echo "<script>
parent.document.forms[0].submit();
</script>";
}
}
}
?>
