<?php if (!defined("_HQC_")) {
	exit();
};
?>
<?php
$whr = '';
if (isset($_GET['type']) && $_GET['type'] == 'auditor') {
	$whr = " where FIND_IN_SET('\"auditor\"',permissions)";
	$auditor = 'y';
}
?>
<style>
	i.far.fa-address-card {
		font-size: 12px !important;
	}
</style>
<script type="text/javascript" language="JavaScript">
	<!--
	$("#page_title").html("<?php echo isset($auditor) ? 'Auditors list' : 'Admin users'; ?>");
	var goBack = "<?php echo isset($auditor) ? 'auditors' : urlencode($_SERVER['REQUEST_URI']); ?>";

	function doAct(doWhat, id) {
		if (doWhat == "delete") {
			if (confirm("Are you sure?") == "1")
				document.location.href = "admin_users_save.php?act=delete&goBack=" + goBack + "&uid=" + id;
		}
		if (doWhat == "edit") {
			document.location.href = "index.php?inc=admin_users_add_edit&act=edit&goBack=" + goBack + "&uid=" + id;
		}
		if (doWhat == "hide") {
			document.location.href = "admin_users_save.php?act=activate&active=n&goBack=" + goBack + "&uid=" + id;
		}
		if (doWhat == "show") {
			document.location.href = "admin_users_save.php?act=activate&active=y&goBack=" + goBack + "&uid=" + id;
		}
	}

	function evaluate_member(id) {
		location = "index.php?inc=user_evaluate&uid=" + id;
	}
	//
	-->
</script>

<center>
	<h2><?php echo isset($auditor) ? 'Auditors List' : 'Admin users List'; ?></h2>
	<table id="usersTbl" class="table table-striped table-bordered">
		<tr>
			<th style="width:20px">Nr</th>
			<th>Username owner</th>
			<th>Username</th>
			<th>Email</th>
			<th style="width:100px"><?php echo isset($auditor) ? 'Evaluation' : 'User type'; ?></th>
			<th style="width:120px">Actions</th>
		</tr>
		<?php
		include_once "$prog_path/config/mysql_ftp.inc.php";
		include_once("$prog_path/config/connect.inc.php");
		$nr = 0;
		if ($result = $amdb->get_results("SELECT * FROM hqc_admin_users $whr")) {
			foreach ($result as $row) {
				if ($row['username'] != "admin") {
					$nr++;
					//find in permissions array if auditor is set
					$is_auditor = '';
					if (strstr($row['permissions'], '"auditor"') == true) {
						$is_auditor = ' <i class="far fa-address-card"><span>Auditor</span></i>';
					}
		?>
					<tr>
						<th><?php echo $nr; ?></th>
						<td><?php echo @$row['username_owner']; ?></td>
						<td><?php echo @$row['username']; ?></td>
						<td><a href="mailto:<?php echo @$row['email']; ?>">Email user</a></td>
						<td>
							<?php if (isset($auditor)) { ?>
								<?php
								$evaluation = trim($row['evaluation']) != '' ? unserialize($row['evaluation']) : [];    // Get the evaluation data
								$stars = $evaluation['finalRating'] ?? 0;
								?>
								<span class="stars_<?php echo $stars; ?> rating-starts" onclick="evaluate_member(<?php echo $row['uid']; ?>)"></span>
							<?php } else { ?>
								<?php echo ($is_auditor); ?>
							<?php } ?>
						</td>
						<td>
							<div class="actionDiv"><img src="<?php echo @$prog_www ?>/images/edit.gif" style="cursor:pointer" onclick="doAct('edit','<?php echo @$row['uid']; ?>')">
								<img src="<?php echo @$prog_www ?>/images/delete.gif" style="cursor:pointer" onclick="doAct('delete','<?php echo @$row['uid']; ?>')">
								<img src="<?php echo @$prog_www ?>/images/<?php echo (@$row['active'] == 'y') ? 'yes.gif' : 'no.gif'; ?>" style="cursor:pointer" onclick="doAct('<?php echo (@$row['active'] == 'y') ? 'hide' : 'show'; ?>','<?php echo @$row['uid']; ?>')">
							</div>
						</td>
					</tr>
		<?php
				}
			}
		}
		?>
	</table>
	<?php if (!isset($auditor)) { ?>
		<p><input type="button" onclick="document.location.href='index.php?inc=admin_users_add_edit&act=add'" value="Add new user" /></p>
	<?php } ?>
</center>