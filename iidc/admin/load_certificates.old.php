<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";
$no=0;
$table = "certificates_".$_REQUEST['tp'];

if(isset($_REQUEST['act']) && $_REQUEST['act']=='load_countries'){
	echo "<option value=''>Select importer by country</option>";
	$country=array();
	$countriesList = "SELECT companies.country1,$table.importer,$table.offid FROM $table
			JOIN companies ON companies.clid = $table.importer
			where $table.offid='$_REQUEST[offid]' AND $table.date like '%$_REQUEST[year]%' GROUP BY $table.importer";

	if( $countries = $amdb->get_results($countriesList)){
		foreach($countries as $cont){
			if(trim($cont['country1'])!='' && !in_array($cont['country1'],$country)){
				$country[] = trim($cont['country1']);
			}
		};
	}
	sort($country);
	if(count($country)>0){
		foreach($country as $cont){
		echo '<option value="'.trim($cont).'">'.trim($cont).'</option>';
		}
	}
	exit();
}

if (isset($_REQUEST['srearchQ']) and trim($_REQUEST['srearchQ'])!=""){
	$whr = "and $_REQUEST[searchField] like '%$_REQUEST[srearchQ]%' AND invoice_nr!='0'";
} else {
	$whr = "and hcd_process!=''";
}

if ($_REQUEST['orderBy']=='company')
	$orderBy = "companies";
	else
	$orderBy = $table;
$orderBy = $orderBy.'.'.$_REQUEST['orderBy'];

$impwhr = '';
if (isset($_REQUEST['country']) and trim($_REQUEST['country'])!=""){
	$impwhr = "and companies.country1 like '%$_REQUEST[country]%'";
}

$importers =array();
$importersList = "SELECT companies.company_name,companies.country1,$table.importer,$table.offid FROM $table
		JOIN companies ON companies.clid = $table.importer
		where $table.offid='$_REQUEST[offid]' AND $table.date like '%$_REQUEST[year]%' $whr $impwhr GROUP BY $table.importer";

if( $theImporters = $amdb->get_results($importersList)){
	foreach($theImporters as $imp){
		$importers[$imp['importer']] = array('company'=>$imp['company_name'],'country'=>$imp['country1']);
	};
}

if (isset($_REQUEST['country']) and trim($_REQUEST['country'])!="" and count($importers)>0){
$whr .= " AND FIND_IN_SET($table.importer,'".implode(',',array_keys($importers))."')";
}
$sql = "SELECT * FROM $table
		JOIN companies ON companies.clid = $table.clid
		where $table.offid='$_REQUEST[offid]' AND $table.date like '%$_REQUEST[year]%' $whr ORDER BY nr DESC, $orderBy {$_REQUEST['ascDsc']}";
if($result = $amdb->get_results($sql)){
foreach($result as $row){
$no++;
?>
<tr data-crtNr='<?php echo $row['nr'];?>'>
<th data-sNr="<?php echo $no;?>"><?php echo $no;?></th>
<td data-id="certificate_nr"><a href='../certificates/pdf/pdf_certificate.php?tp=<?php echo $_REQUEST['tp'];?>&nr=<?php echo $row['nr'];?>&usr=a' target=_blank><?php echo $row['certificate_nr'];?></a><br/>
<b>Issue date:</b> <?php echo $row['issue_date'];?></td>
<td class='crtDocNr' data-id="doc_nr" data-crtDocNr='<?php echo $row['doc_nr'];?>'>
<?php if ($row['is_bad']=="y"){?>
<span style="text-decoration:line-through;color:red;cursor:pointer" onclick="badCer('n',<?php echo $row['nr'];?>)"><?php echo $row['doc_nr'];?></span>
<?php } else {?>
<?php echo $row['doc_nr'];?>
<?php };?>
</td>
<td data-id="importer"><?php if(isset($importers[$row['importer']])){
	$importer = $importers[$row['importer']];
	echo $importer['company'].'<br/>';
	echo '<span style="color:green">'.$importer['country'].'</span>';
	};?></td>
<td data-id="company_name"><?php echo $row['company_name'];?></td>
<td data-id="reference"><?php echo str_replace(array("+","  +","/","  /  "),array(" +"," +"," / "," / "),$row['reference']);?></td>
<td data-id="status" class="nowrap">
<?php echo '<b>'.str_replace(array('on: ','Authorised: '),array('on:</b>','Authorised on:</b>'),$row['hcd_process']);?>
<?php if (strstr($row['hcd_process'],'Authorised') and trim($row['printed_on'])!=''){?>
<br/><b>Printed on:</b><?php echo $row['printed_on'];?>
<?php } elseif(strstr($row['hcd_process'],'Sent') and trim($row['arrived_on'])!=''){?>
<br/><b>Arrived on:</b><?php echo $row['arrived_on'];?>
<?php } elseif(strstr($row['hcd_process'],'Printed')){?>
<span id="sendCertSpan_<?php echo $row['nr'];?>">
	<br/><input type="button" value="Send certificate" onclick="load_html('certificates_save.php?act=sendCertificate&nr=<?php echo $row['nr'];?>&tp=<?php echo $_REQUEST['tp'];?>','#sendCertSpan_<?php echo $row['nr'];?>')"/>
    </span>
<?php } elseif(strstr($row['hcd_process'],'Sent') and trim($row['arrived_on'])==''){
	$sentOn = fix_date(str_replace('Sent on: ','',$row['hcd_process']));
	if($row['clid'] != '1' and strtotime('+2 days',strtotime($sentOn))<time()){
	$arrived_on = date("d/m/Y", strtotime('+2 days',strtotime($sentOn)));
	$amdb->query("update certificates_{$_POST['tp']} set arrived_on='$arrived_on', done='y' where  nr = '$row[nr]'");?>
	<br/><b>Arrived on:</b><?php echo $arrived_on;?>
	<?php
    } else {
	?>
<span id="recievedCertSpan_<?php echo $row['nr'];?>">
	<br/><input type="button" value="Recieved certificate" onclick="load_html('certificates_save.php?act=recievedCertificate&nr=<?php echo $row['nr'];?>&tp=<?php echo $_REQUEST['tp'];?>','#sendCertSpan_<?php echo $row['nr'];?>')"/>
    </span>
<?php };};?>
</td>
<?php
if(in_array("certificates_actions",$user_permissions) or $username=="admin"){ ?>
	<td style="white-space:nowrap !important">
    <img width='22' title='Undo print / authorize' src="../images/undo.svg" onclick="undoCer('<?php echo $row['nr'];?>','<?php echo $_REQUEST['tp'];?>',this)">
	<?php if ($row['is_bad']=="y"){?>
	<img width='22' title='Not bad certificate' src="../images/bad_document32.png" onclick="badCer('n','<?php echo $row['nr'];?>')">
	<?php } else {?>
	<img width='22' title='Bad certificate' src="../images/bad_document32_grey.png" onclick=" badCer('y','<?php echo $row['nr'];?>')">
	<img  title='Delete certificate' src="../images/delete.gif" onclick="delcer('<?php echo $row['nr'];?>')">
<?php } ?>
</td>
<?php };?>
</tr>
<?php };} else {
	echo "error: No certificates found";
	};?>