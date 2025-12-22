<?php
include "../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($_POST['act'] == "update") {
	if ($amdb->get_row("SELECT * FROM invoice_templates WHERE template_name='$_POST[template_name]'"))
		$amdb->update("invoice_templates", $_POST, "template_name='$_POST[template_name]'");
	else
		$amdb->insert("invoice_templates", $_POST);
	if (isset($_SESSION['comemid']))
		$amdb->post_results('saved successfully');
	else
		$amdb->post_results('index.php?inc=invoice_template', 'url');
	exit();
}

if ($_POST['act'] == "delete") {
	$amdb->query("UPDATE invoice_templates SET status='deleted' WHERE tmplid='$_POST[id]'");
	echo "reload";
	exit();
}

if ($_REQUEST['act'] == "change_status") {
	$amdb->query("UPDATE invoice_templates SET status='$_POST[status]' WHERE tmplid='$_POST[id]'");
	echo "ok";
	exit();
}
