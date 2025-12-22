<?php
if (isset($_SESSION['user_type']) and isset($clid)){
?>
<table border="0" clid="table2" cellspacing="0" >
		<tr class=sub_title>
			<td><b><a href="<?php echo $prog_www?>/certificates/?inc=certificate_a">Certificate A (Meat)</a></b></td>
			<td>|</td>
			<td><b><a href="<?php echo $prog_www?>/certificates/?inc=certificate_b">Certificate B (Non meat)</a></b></td>
			<td>|</td>
			<td><b><a href="<?php echo $prog_www?>/company/index.php?inc=profile&clid=<?php echo $clid?>">My profile</a></b></td>
			<td>|</td>
			<td><b><a href="<?php echo $prog_www?>/company/index.php?inc=clients&clid=<?php echo $clid?>">My clients</a></b></td>
			<td width="4">|</td>
			<td><b><a href="<?php echo $prog_www?>/logout.php" target=_top>Log-out</a></b></td>
		</tr>
</table>
<?php
}
?>
