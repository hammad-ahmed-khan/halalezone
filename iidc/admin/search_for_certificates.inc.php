<form method="post" action="index.php?inc=search_certificates_result" name="seach_form">
<table border="0" class=cer_td>
<tr><td>
<b>Search for:</b><input type="text" size="15" name="srch4wt" value="<?php echo  @$_POST['srch4wt']?>" onfocus="this.value=''"/>
<select size="1" name="searchby">
<option value="doc_nr">Document Number</option>
<option value="certificate_nr" <?php echo  (@$_POST['searchby']=='certificate_nr')?'selected':'';?>>Certificate Number</option>
<option value="company_name" <?php echo  (@$_POST['searchby']=='company_name')?'selected':'';?>>Company</option>
</select>
<select size="1" name="searchCer">
<option value="allCers">Both types</option>
<option value="cerA" <?php echo  (@$_POST['searchCer']=='cerA')?'selected':'';?>>Certificates A (Meat)</option>
<option value="cerB" <?php echo  (@$_POST['searchCer']=='cerB')?'selected':'';?>>Certificates B (Non meat)</option>
</select>
<select name="year" size="1">
<option value="">year</option>
<?php
for ($y=intval(date("Y"));$y>=2007;$y--){
if (isset($_POST['year']) and $_POST['year']==$y)
echo "<option value=\"$y\" selected=\"selected\">$y</option>";
else
echo "<option value=\"$y\">$y</option>";
}
?>
</select>

<input type="submit" value="Search"  style="width: 100px">
</td></tr>
</table>
</form>