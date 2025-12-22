<?php
/*--File name (hqc_admin_users_save.php)--*/
session_start();
if (!isset($_SESSION) or $_SESSION['username']!="admin"){exit();};
require("../config/paths.inc.php");
include_once("$prog_path/checkuser.inc.php");
include "$prog_path/config/mysql_ftp.inc.php";
include "$prog_path/config/connect.inc.php";
$allowed = array();
if (isset($_GET['uid'])){
    if($user = $amdb->get_row("SELECT * FROM hqc_admin_users WHERE uid='$_GET[uid]'")){
        $allowed = explode(',',$user['clients_allowed']);
    };
}
$result = MYSQL_QUERY("SELECT * FROM companies,users WHERE companies.clid=users.clid and clof='0' and users.active='y' order by companies.company_name ASC");
if (@MYSQL_NUM_ROWS($result) > 0){
 WHILE ($row = MYSQL_FETCH_ARRAY($result)){?>
	<li><input type="checkbox" value="<?php echo $row['clid']; ?>" id="cb_<?php echo $row['clid']; ?>" <?php echo (in_array($row['clid'],$allowed))?"checked='checked'":"";?>/>
    <label for="cb_<?php echo $row['clid']; ?>"><?php echo $row['company_name'];?></label></li>
<?php }; }?>