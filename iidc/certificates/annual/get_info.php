<?php
include "../../check_user.inc.php";
include "$prog_path/config/connect.inc.php";

if ($category = $amdb->get_row("SELECT * FROM hqc_categories WHERE catid='".$_GET['catid']."' AND status='active'")) {
echo "<b>".$category['code'].": ".$category['description']."</b><br/><br/>".$category['exapmle'];
}