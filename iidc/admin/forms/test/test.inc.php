<?php if (!defined("__HQC__") or !isset($_GET['foid'])){exit();}; ?>
<?php
if($form = $hqcdb->get_row("SELECT * FROM hqc_forms WHERE foid='$_GET[foid]'")){
$column_title='Name';
if(trim($form['records_listing'])!='' and is_array(json_decode($form['records_listing'],true))){
	$records_listing = json_decode($form['records_listing'],true);
	if(isset($records_listing['forms_test']))
	$column_title = $records_listing['forms_test']['title'];
}
?>
<table class="alternateOn" style="width:100%">
<thead><tr><th style="width:20px">#</th><th><?php echo $column_title;?></th><th style="width:80px">Date</th><th style="width:180px">Action</th></tr></thead>
<tbody>
<?php
if($tests = $hqcdb->get_results("SELECT tstid,foid,content_name,inserted_on,status FROM hqc_forms_test WHERE foid = '$_GET[foid]' AND status!='deleted'")){
	$nr = 1;
	foreach($tests as $test){ ?>
	<tr>
    	<th><?php echo $nr++;?></th>
        <td><?php echo $test['content_name'];?></td>
        <td><?php echo date("d/m/Y",strtotime($test['inserted_on']));?></td>
        <td>
         <a href="?inc=test_add_edit&foid=<?php echo $_GET['foid'];?>&tstid=<?php echo $test['tstid'];?>&act=view" title="View"><i class="fa fa-eye" aria-hidden="true"></i></a>
        <a href="?inc=test_add_edit&foid=<?php echo $_GET['foid'];?>&tstid=<?php echo $test['tstid'];?>&act=update" title="Edit"><i class="far fa-edit"></i></a>
        <a href="pdf.php?foid=<?php echo $_GET['foid'];?>&tstid=<?php echo $test['tstid'];?>" target="pdfIframe"><i class="far fa-file-pdf"></i></a>
   		<i class="fa fa-trash-alt" data-save="test" data-id="<?php echo $test['tstid'];?>" aria-hidden="true" data-confirm="Are you sure? Delete item" title="Delete"></i>
       <i class="fa fa-toggle-<?php echo($test['status']=="active")?"on":"off";?> status" aria-hidden="true" data-save="test" data-id="<?php echo $test['tstid'];?>" title="Activate / Deactivate"></i>
        </td>
    </tr>
	<?php };

}
?>
</tbody>
<tfoot><tr><th>#</th><th><?php echo $column_title;?></th><th>Date</th><th>Action</th></tr></tfoot>
</table>
<center><a href="../?inc=forms" class="button">Go back</a> <a href="?inc=test_add_edit&foid=<?php echo $_GET['foid'];?>&act=insert" class="button">Add new <?php echo $form['form_name'];?> Item</a>
<?php }?>
